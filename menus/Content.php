<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_content'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $units = $_POST['unit'];
        $contents = $_POST['content'];
        $hours = $_POST['hour'];
        
        try {
            $conn->autocommit(FALSE);
            $stmt = $conn->prepare("INSERT INTO content (course_code, unit, content, hour) VALUES (?, ?, ?, ?)");
            
            foreach($units as $index => $unit) {
                $content = $contents[$index];
                $hour = $hours[$index];
                $stmt->bind_param("ssss", $course_code, $unit, $content, $hour);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['message'] = "Content added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['update_content'])) {
        $id = intval($_POST['id']);
        $unit = $conn->real_escape_string($_POST['unit']);
        $content = $conn->real_escape_string($_POST['content']);
        $hour = $conn->real_escape_string($_POST['hour']);

        $stmt = $conn->prepare("UPDATE content SET unit=?, content=?, hour=? WHERE id=?");
        $stmt->bind_param("sssi", $unit, $content, $hour, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Content updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating record: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['delete_content'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM content WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Content deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Modified query to order by id DESC to show newest first
$result = $conn->query("SELECT * FROM content ORDER BY id DESC");
$content_items = [];
while ($row = $result->fetch_assoc()) {
    $content_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --text-color: #2b2d42;
            --background-color: #f8f9fa;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }

        .content-area {
            margin-left: 19vw;
            padding: 2rem;
            margin-top: 68px;
        }

        .table-container {
            max-width: 122%;
            margin-left: 2rem;
            overflow-x: auto;
            margin-top: 24px;
            padding-bottom: 50px !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }

        th, td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            margin: 0 2px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 800px;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-column {
            flex: 1;
        }
        .close {
            margin-top: -66px !important;
            font-size: 27px;
            }

        .form-column label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-column input,
        .form-column textarea,
        .form-column select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .form-column input:focus,
        .form-column textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .unit-group {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            background: #f8f9fa;
        }

        .remove-unit {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .empty-cell {
            color: #6c757d;
            font-style: italic;
        }

        .action {
            width: 176px;
        }
        .add_items {
            padding: 13px;
            }
            
        #courseSearch {
            padding: 0.8rem;
            width: 300px;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
            margin-bottom: 1rem;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    
    <div class="content-area">
        <button class="btn btn-primary add_items" onclick="openModal()">
            <i class="fas fa-plus"></i> Add New Content
        </button>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                <?= $_SESSION['message'] ?>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <input type="text" id="courseSearch" placeholder="Search by course code...">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Unit</th>
                        <th>Content</th>
                        <th>Hours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($content_items as $row): ?>
                        <tr data-course-code="<?= htmlspecialchars($row['course_code']) ?>">
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= htmlspecialchars($row['content']) ?></td>
                            <td><?= htmlspecialchars($row['hour']) ?></td>
                            <td class="action">
                                <button class="btn btn-primary" onclick="openEditModal(
                                    '<?= $row['id'] ?>',
                                    '<?= htmlspecialchars($row['course_code']) ?>',
                                    '<?= htmlspecialchars($row['unit']) ?>',
                                    '<?= htmlspecialchars($row['content']) ?>',
                                    '<?= htmlspecialchars($row['hour']) ?>'
                                )">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_content" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Content</h3>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" name="course_code" required>
                        </div>
                    </div>

                    <div id="unitContainer">
                        <div class="unit-group">
                            <button type="button" class="remove-unit" onclick="removeUnit(this)">&times;</button>
                            <div class="form-row">
                                <div class="form-column">
                                    <label>Unit</label>
                                    <input type="text" name="unit[]" required>
                                </div>
                                <div class="form-column">
                                    <label>Hours</label>
                                    <input type="text" name="hour[]" required>
                                </div>
                            </div>
                            <div class="form-column">
                                <label>Content</label>
                                <textarea name="content[]" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="button" class="btn btn-primary" onclick="addUnit()">
                            <i class="fas fa-plus"></i> Add Unit
                        </button>
                        <button type="submit" name="save_content" class="btn btn-success">
                            <i class="fas fa-save"></i> Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Content</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" id="edit_course_code" disabled>
                        </div>
                        <div class="form-column">
                            <label>Unit</label>
                            <input type="text" name="unit" id="edit_unit" required>
                        </div>
                        <div class="form-column">
                            <label>Hours</label>
                            <input type="text" name="hour" id="edit_hour" required>
                        </div>
                    </div>
                    <div class="form-column">
                        <label>Content</label>
                        <textarea name="content" id="edit_content" required></textarea>
                    </div>
                    <button type="submit" name="update_content" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let unitCount = 1;

        function openModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(id, code, unit, content, hour) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_course_code').value = code;
            document.getElementById('edit_unit').value = unit;
            document.getElementById('edit_content').value = content;
            document.getElementById('edit_hour').value = hour;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function addUnit() {
            unitCount++;
            const unitContainer = document.getElementById('unitContainer');
            const newUnit = document.createElement('div');
            newUnit.className = 'unit-group';
            newUnit.innerHTML = `
                <button type="button" class="remove-unit" onclick="removeUnit(this)">&times;</button>
                <div class="form-row">
                    <div class="form-column">
                        <label>Unit</label>
                        <input type="text" name="unit[]" required>
                    </div>
                    <div class="form-column">
                        <label>Hours</label>
                        <input type="text" name="hour[]" required>
                    </div>
                </div>
                <div class="form-column">
                    <label>Content</label>
                    <textarea name="content[]" required></textarea>
                </div>
            `;
            unitContainer.appendChild(newUnit);
            unitContainer.lastElementChild.scrollIntoView({ behavior: 'smooth' });
        }

        function removeUnit(btn) {
            if (document.querySelectorAll('.unit-group').length > 1) {
                btn.closest('.unit-group').remove();
                unitCount--;
            }
        }

        // Course search functionality
        document.getElementById('courseSearch').addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const courseCode = row.getAttribute('data-course-code').toLowerCase();
                row.style.display = courseCode.includes(searchTerm) ? '' : 'none';
            });
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
                closeEditModal();
            }
        }
    </script>
</body>
</html>