<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add New Mapping
    if (isset($_POST['save_mapping_cos'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $cos = $_POST['co'];

        // Convert all PO values to uppercase
        foreach($cos as &$co) {
            foreach($co as &$po_value) {
                $po_value = strtoupper($po_value);
            }
        }

        try {
            $conn->autocommit(FALSE);
            
            foreach($cos as $co_num => $po_values) {
                $co_number = $co_num + 1;
                $stmt = $conn->prepare("INSERT INTO mapping_pos 
                    (course_code, co_number, po1, po2, po3, po4, po5, po6, po7)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("sisssssss", 
                    $course_code,
                    $co_number,
                    $po_values['po1'],
                    $po_values['po2'],
                    $po_values['po3'],
                    $po_values['po4'],
                    $po_values['po5'],
                    $po_values['po6'],
                    $po_values['po7']
                );
                $stmt->execute();
            }

            $conn->commit();
            $_SESSION['message'] = "Mapping added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        
        header("Location: mapping_of_cos_with_pos.php");
        exit();
    }

    // Update Mapping
    if (isset($_POST['update_mapping_cos'])) {
        $id = intval($_POST['id']);
        $po1 = strtoupper($conn->real_escape_string($_POST['po1']));
        $po2 = strtoupper($conn->real_escape_string($_POST['po2']));
        $po3 = strtoupper($conn->real_escape_string($_POST['po3']));
        $po4 = strtoupper($conn->real_escape_string($_POST['po4']));
        $po5 = strtoupper($conn->real_escape_string($_POST['po5']));
        $po6 = strtoupper($conn->real_escape_string($_POST['po6']));
        $po7 = strtoupper($conn->real_escape_string($_POST['po7']));

        $stmt = $conn->prepare("UPDATE mapping_pos SET 
                po1=?, po2=?, po3=?, po4=?, po5=?, po6=?, po7=?
                WHERE id=?");
        $stmt->bind_param("sssssssi", $po1, $po2, $po3, $po4, $po5, $po6, $po7, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Mapping updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating record: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: mapping_of_cos_with_pos.php");
        exit();
    }

    // Delete Mapping
    if (isset($_POST['delete_mapping_pos'])) {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM mapping_pos WHERE id=$id";
        
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: mapping_of_cos_with_pos.php");
        exit();
    }
}

// Fetch all outcomes ordered by latest first
$result = $conn->query("SELECT * FROM mapping_pos ORDER BY created_at DESC");
$grouped_mappings = [];
while ($row = $result->fetch_assoc()) {
    $course_code = $row['course_code'];
    if (!isset($grouped_mappings[$course_code])) {
        $grouped_mappings[$course_code] = [];
    }
    array_unshift($grouped_mappings[$course_code], $row);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO-PO Mapping</title>
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
        }

        .search-container {
            margin-bottom: 1rem;
        }

        #searchInput {
            padding: 0.8rem;
            width: 300px;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .add-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
            overflow: hidden;
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
            width: 600px;
            max-width: 95%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding-bottom: 2rem;
            
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .close {
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            transition: var(--transition);
        }

        .close:hover {
            color: var(--text-color);
            transform: rotate(90deg);
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-column {
            flex: 1;
        }

        .form-column label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-column input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: var(--transition);
        }

        .form-column input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .co-section {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background: #f8f9fa;
            position: relative;
        }
        .co-section {
            margin-bottom: 1rem; 
        }

        #co-sections {
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 0.5rem; /* Add scrollbar space */
        }

        .remove-co-btn {
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
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    <div class="content-area">
        
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by course code...">
        </div>
        <button class="add-btn" onclick="openModal()">Add New Mapping</button>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                <?= $_SESSION['message'] ?>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>CO</th>
                    <th>PO1</th>
                    <th>PO2</th>
                    <th>PO3</th>
                    <th>PO4</th>
                    <th>PO5</th>
                    <th>PO6</th>
                    <th>PO7</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped_mappings as $course_code => $mappings): ?>
                    <tbody class="course-group" data-course-code="<?= htmlspecialchars($course_code) ?>">
                        <?php foreach ($mappings as $index => $mapping): ?>
                            <tr>
                                <?php if ($index === 0): ?>
                                    <td rowspan="<?= count($mappings) ?>"><?= htmlspecialchars($course_code) ?></td>
                                <?php endif; ?>
                                <td>CO<?= $mapping['co_number'] ?></td>
                                <td><?= htmlspecialchars($mapping['po1']) ?></td>
                                <td><?= htmlspecialchars($mapping['po2']) ?></td>
                                <td><?= htmlspecialchars($mapping['po3']) ?></td>
                                <td><?= htmlspecialchars($mapping['po4']) ?></td>
                                <td><?= htmlspecialchars($mapping['po5']) ?></td>
                                <td><?= htmlspecialchars($mapping['po6']) ?></td>
                                <td><?= htmlspecialchars($mapping['po7']) ?></td>
                                <td>
                                    <button class="btn btn-primary" onclick="openEditModal(
                                        '<?= $mapping['id'] ?>',
                                        '<?= $mapping['po1'] ?>',
                                        '<?= $mapping['po2'] ?>',
                                        '<?= $mapping['po3'] ?>',
                                        '<?= $mapping['po4'] ?>',
                                        '<?= $mapping['po5'] ?>',
                                        '<?= $mapping['po6'] ?>',
                                        '<?= $mapping['po7'] ?>'
                                    )">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $mapping['id'] ?>">
                                        <button type="submit" name="delete_mapping_pos" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Modal -->
        <div id="addNewModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add CO-PO Mapping</h2>
                    <span class="close">&times;</span>
                </div>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" name="course_code" required>
                        </div>
                    </div>

                    <div id="co-sections">
                        <div class="co-section">
                            <button type="button" class="remove-co-btn" onclick="removeCOSection(this)">&times;</button>
                            <h3>CO1</h3>
                            <div class="form-row">
                                <?php foreach (range(1, 7) as $po): ?>
                                <div class="form-column">
                                    <label>PO<?= $po ?></label>
                                    <input type="text" name="co[0][po<?= $po ?>]" 
                                           class="restricted-input" 
                                           maxlength="1" 
                                           pattern="[SMLsml]" 
                                           title="Only S, M, or L (case insensitive)"
                                           required>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <button type="button" id="add-co-btn" class="btn btn-primary">Add CO</button>
                        <button type="submit" name="save_mapping_cos" class="btn btn-success">Save Mapping</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit CO-PO Mapping</h2>
                    <span class="close">&times;</span>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-row">
                        <?php foreach (range(1, 7) as $po): ?>
                        <div class="form-column">
                            <label>PO<?= $po ?></label>
                            <input type="text" id="edit_po<?= $po ?>" name="po<?= $po ?>" 
                                   class="restricted-input" 
                                   maxlength="1" 
                                   pattern="[SMLsml]" 
                                   title="Only S, M, or L (case insensitive)"
                                   required>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" name="update_mapping_cos" class="btn btn-success">Update Mapping</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let coCount = 0;
        const maxCO = 5;

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.trim().toUpperCase();
            const groups = document.querySelectorAll('.course-group');
            
            groups.forEach(group => {
                const courseCode = group.getAttribute('data-course-code').toUpperCase();
                group.style.display = courseCode.includes(searchTerm) ? '' : 'none';
            });
        });

        // Modal Handling
        function openModal() {
            document.getElementById('addNewModal').style.display = 'flex';
        }

        function openEditModal(id, ...pos) {
            document.getElementById('edit_id').value = id;
            pos.forEach((value, index) => {
                document.getElementById(`edit_po${index + 1}`).value = value;
            });
            document.getElementById('editModal').style.display = 'flex';
        }

        document.querySelectorAll('.close').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });

        // CO Section Management
        document.getElementById('add-co-btn').addEventListener('click', function() {
            if (coCount < maxCO - 1) {
                coCount++;
                const container = document.getElementById('co-sections');
                const newSection = document.createElement('div');
                newSection.className = 'co-section';
                newSection.innerHTML = `
                    <button type="button" class="remove-co-btn" onclick="removeCOSection(this)">&times;</button>
                    <h3>CO${coCount + 1}</h3>
                    <div class="form-row">
                        ${Array.from({length: 7}, (_, i) => `
                        <div class="form-column">
                            <label>PO${i + 1}</label>
                            <input type="text" name="co[${coCount}][po${i + 1}]" 
                                   class="restricted-input" 
                                   maxlength="1" 
                                   pattern="[SMLsml]" 
                                   title="Only S, M, or L (case insensitive)"
                                   required>
                        </div>`).join('')}
                    </div>
                `;
                container.appendChild(newSection);
            } else {
                alert('Maximum of 5 COs allowed');
            }
        });

        function removeCOSection(btn) {
            const sections = document.getElementById('co-sections');
            if (sections.children.length > 1) {
                const section = btn.closest('.co-section');
                section.remove();
                Array.from(sections.children).forEach((sec, index) => {
                    sec.querySelector('h3').textContent = `CO${index + 1}`;
                    sec.querySelectorAll('input').forEach(input => {
                        input.name = input.name.replace(/\[\d+\]/g, `[${index}]`);
                    });
                });
                coCount = sections.children.length - 1;
            }
        }

        // Input Validation - Auto uppercase and restrict to S/M/L
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('restricted-input')) {
                // Convert to uppercase
                e.target.value = e.target.value.toUpperCase();
                
                // Only allow S, M, or L
                if (!/^[SML]?$/.test(e.target.value)) {
                    e.target.value = e.target.value.replace(/[^SML]/g, '');
                }
            }
        });
    </script>
</body>
</html>