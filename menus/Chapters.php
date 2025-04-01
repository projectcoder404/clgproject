<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_chapter'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $units = $_POST['unit'];
        $chapters = $_POST['chapter'];
        $books = $_POST['book'];
        
        try {
            $conn->autocommit(FALSE);
            $stmt = $conn->prepare("INSERT INTO chapter (course_code, unit, chapter, book) VALUES (?, ?, ?, ?)");
            
            foreach($units as $index => $unit) {
                $chapter = $chapters[$index];
                $book = $books[$index];
                $stmt->bind_param("ssss", $course_code, $unit, $chapter, $book);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['message'] = "Chapters added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['update_chapter'])) {
        $id = $_POST['id'];
        $unit = $conn->real_escape_string($_POST['unit']);
        $chapter = $conn->real_escape_string($_POST['chapter']);
        $book = $conn->real_escape_string($_POST['book']);

        $stmt = $conn->prepare("UPDATE chapter SET unit=?, chapter=?, book=? WHERE id=?");
        $stmt->bind_param("sssi", $unit, $chapter, $book, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Chapter updated successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_chapter'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM chapter WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Chapter deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM chapter ORDER BY id DESC");
$grouped_chapters = [];
while ($row = $result->fetch_assoc()) {
    $course_code = $row['course_code'];
    if (!isset($grouped_chapters[$course_code])) {
        $grouped_chapters[$course_code] = [];
    }
    $grouped_chapters[$course_code][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
            margin-left: 20.3%;
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
            cursor: pointer;
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

        .chapter-group {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            background: #f8f9fa;
        }

        .remove-chapter {
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

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        i {
            margin-right: 4px;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    
    <div class="content-area">
        <button class="btn btn-primary add_items" onclick="openModal()">
            <i class="fas fa-plus"></i> Add New Chapters
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
                        <th>Chapter</th>
                        <th>Book</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped_chapters as $course_code => $chapters): ?>
                        <?php foreach ($chapters as $index => $row): ?>
                            <tr>
                                <?php if ($index === 0): ?>
                                    <td rowspan="<?= count($chapters) ?>"><?= htmlspecialchars($course_code) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars($row['chapter']) ?></td>
                                <td><?= htmlspecialchars($row['book']) ?></td>
                                <td class="action">
                                    <button class="btn btn-primary" onclick="openEditModal(
                                        '<?= $row['id'] ?>',
                                        '<?= htmlspecialchars($row['course_code']) ?>',
                                        '<?= htmlspecialchars($row['unit']) ?>',
                                        '<?= htmlspecialchars($row['chapter']) ?>',
                                        '<?= htmlspecialchars($row['book']) ?>'
                                    )">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-danger" onclick="confirmDelete(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Add New Chapters</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" name="course_code" required>
                        </div>
                    </div>
                    
                    <div id="chapterContainer">
                        <div class="chapter-group">
                            <button type="button" class="remove-chapter" onclick="removeChapter(this)" style="display: none;">&times;</button>
                            <div class="form-row">
                                <div class="form-column">
                                    <label>Unit</label>
                                    <input type="text" name="unit[]" required>
                                </div>
                                <div class="form-column">
                                    <label>Chapter</label>
                                    <input type="text" name="chapter[]" required>
                                </div>
                                <div class="form-column">
                                    <label>Book</label>
                                    <input type="text" name="book[]" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="addChapter()">
                            <i class="fas fa-plus"></i> Add Another Chapter
                        </button>
                        <button type="submit" name="save_chapter" class="btn btn-success">
                            <i class="fas fa-save"></i> Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h3>Edit Chapter</h3>
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
                            <label>Chapter</label>
                            <input type="text" name="chapter" id="edit_chapter" required>
                        </div>
                        <div class="form-column">
                            <label>Book</label>
                            <input type="text" name="book" id="edit_book" required>
                        </div>
                    </div>
                    <button type="submit" name="update_chapter" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                </form>
            </div>
        </div>

        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeDeleteModal()">&times;</span>
                <h3>Confirm Delete</h3>
                <p>Are you sure you want to delete this chapter?</p>
                <form method="POST">
                    <input type="hidden" name="id" id="delete_id">
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="delete_chapter" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        function openModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(id, course_code, unit, chapter, book) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_course_code').value = course_code;
            document.getElementById('edit_unit').value = unit;
            document.getElementById('edit_chapter').value = chapter;
            document.getElementById('edit_book').value = book;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function addChapter() {
            const chapterContainer = document.getElementById('chapterContainer');
            const newChapter = document.createElement('div');
            newChapter.className = 'chapter-group';
            newChapter.innerHTML = `
                <button type="button" class="remove-chapter" onclick="removeChapter(this)">&times;</button>
                <div class="form-row">
                    <div class="form-column">
                        <label>Unit</label>
                        <input type="text" name="unit[]" required>
                    </div>
                    <div class="form-column">
                        <label>Chapter</label>
                        <input type="text" name="chapter[]" required>
                    </div>
                    <div class="form-column">
                        <label>Book</label>
                        <input type="text" name="book[]" required>
                    </div>
                </div>
            `;
            chapterContainer.appendChild(newChapter);
            chapterContainer.lastElementChild.scrollIntoView({ behavior: 'smooth' });
        }

        function removeChapter(btn) {
            const chapterGroups = document.querySelectorAll('.chapter-group');
            if (chapterGroups.length > 1) {
                btn.closest('.chapter-group').remove();
            }
        }

        function confirmDelete(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        document.getElementById('courseSearch').addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const courseCode = row.cells[0].textContent.toLowerCase();
                row.style.display = courseCode.includes(searchTerm) ? '' : 'none';
            });
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
                closeEditModal();
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>