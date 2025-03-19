<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add New Mapping
    if (isset($_POST['save_mapping_psos'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $po1 = $conn->real_escape_string($_POST['po1']);
        $po2 = $conn->real_escape_string($_POST['po2']);
        $po3 = $conn->real_escape_string($_POST['po3']);
        $po4 = $conn->real_escape_string($_POST['po4']);
        $po5 = $conn->real_escape_string($_POST['po5']);

        $sql = "INSERT INTO MappingPSOs (course_code, po1, po2, po3, po4, po5)
                VALUES ('$course_code', '$po1', '$po2', '$po3', '$po4', '$po5')";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: Mapping_PSOs.php");
        exit();
    }

    // Update Mapping
    if (isset($_POST['update_mapping_psos'])) {
        $id = intval($_POST['id']);
        $po1 = $conn->real_escape_string($_POST['po1']);
        $po2 = $conn->real_escape_string($_POST['po2']);
        $po3 = $conn->real_escape_string($_POST['po3']);
        $po4 = $conn->real_escape_string($_POST['po4']);
        $po5 = $conn->real_escape_string($_POST['po5']);

        $sql = "UPDATE MappingPSOs SET 
                po1='$po1', po2='$po2', po3='$po3', po4='$po4', po5='$po5'
                WHERE id=$id";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: Mapping_PSOs.php");
        exit();
    }

    // Delete Mapping
    if (isset($_POST['delete_mapping_psos'])) {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM MappingPSOs WHERE id=$id";
        
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: Mapping_PSOs.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO-PSOs Mapping</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

        .content-area {
            margin-left: 20.3%;
            margin-right: 15%;
            padding: 20px;
        }

        #addNewBtn {
            margin-top: 2rem;
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
        }

        #addNewBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
            background: #3650c7;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 60rem;
            max-width: 90%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-column {
            flex: 1;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
            padding: 1rem;
        }

        #mappingTable {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        #mappingTable thead {
            background: var(--primary-color);
            color: white;
        }

        #mappingTable th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
        }

        #mappingTable td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .content-area {
                margin-left: 2vw;
                margin-right: 2vw;
            }
            
            .modal-content {
                width: 95%;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../sidebar.php'); ?>

        <div class="content-area">
            <button id="addNewBtn" class="btn btn-success">Add Mapping</button>

            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?> mt-3">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                endif; ?>

            <div class="table-container mt-3">
                <table id="mappingTable" class="display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>PO1</th>
                            <th>PO2</th>
                            <th>PO3</th>
                            <th>PO4</th>
                            <th>PO5</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM MappingPSOs");
                        while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['po1']) ?></td>
                                <td><?= htmlspecialchars($row['po2']) ?></td>
                                <td><?= htmlspecialchars($row['po3']) ?></td>
                                <td><?= htmlspecialchars($row['po4']) ?></td>
                                <td><?= htmlspecialchars($row['po5']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary editBtn" 
                                        data-id="<?= $row['id'] ?>"
                                        data-code="<?= $row['course_code'] ?>"
                                        data-po1="<?= $row['po1'] ?>"
                                        data-po2="<?= $row['po2'] ?>"
                                        data-po3="<?= $row['po3'] ?>"
                                        data-po4="<?= $row['po4'] ?>"
                                        data-po5="<?= $row['po5'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Modal -->
            <div id="addNewModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Add CO-PSO Mapping</h2>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-column">
                                <label for="course_code">Course Code</label>
                                <input type="text" name="course_code" required>
                            </div>
                            <div class="form-column">
                                <label for="po1">PO1</label>
                                <input type="text" name="po1" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="po2">PO2</label>
                                <input type="text" name="po2" class="restricted-input" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-column">
                                <label for="po3">PO3</label>
                                <input type="text" name="po3" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="po4">PO4</label>
                                <input type="text" name="po4" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="po5">PO5</label>
                                <input type="text" name="po5" class="restricted-input" required>
                            </div>
                        </div>
                        <button type="submit" name="save_mapping_psos" class="btn btn-success mt-3">Save Mapping</button>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Edit CO-PSO Mapping</h2>
                    <form method="POST">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-row">
                            <div class="form-column">
                                <label for="edit_course_code">Course Code</label>
                                <input type="text" id="edit_course_code" name="course_code" disabled>
                            </div>
                            <div class="form-column">
                                <label for="edit_po1">PO1</label>
                                <input type="text" id="edit_po1" name="po1" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="edit_po2">PO2</label>
                                <input type="text" id="edit_po2" name="po2" class="restricted-input" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-column">
                                <label for="edit_po3">PO3</label>
                                <input type="text" id="edit_po3" name="po3" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="edit_po4">PO4</label>
                                <input type="text" id="edit_po4" name="po4" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="edit_po5">PO5</label>
                                <input type="text" id="edit_po5" name="po5" class="restricted-input" required>
                            </div>
                        </div>
                        <button type="submit" name="update_mapping_psos" class="btn btn-success mt-3">Update Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#mappingTable').DataTable({
                dom: '<"top"f>rt<"bottom"lip>',
                responsive: true
            });

            const addModal = $('#addNewModal');
            const editModal = $('#editModal');
            
            $('#addNewBtn').click(() => addModal.css('display', 'flex'));
            $('.close').click(() => {
                addModal.css('display', 'none');
                editModal.css('display', 'none');
            });

            $(window).click((e) => {
                if (e.target === addModal[0]) addModal.css('display', 'none');
                if (e.target === editModal[0]) editModal.css('display', 'none');
            });

            $('.restricted-input').on('input', function() {
                let value = $(this).val().toUpperCase();
                value = value.replace(/[^SM]/g, '');
                $(this).val(value);
            });

            $('.editBtn').click(function() {
                const data = $(this).data();
                $('#edit_id').val(data.id);
                $('#edit_course_code').val(data.code);
                $('#edit_po1').val(data.po1);
                $('#edit_po2').val(data.po2);
                $('#edit_po3').val(data.po3);
                $('#edit_po4').val(data.po4);
                $('#edit_po5').val(data.po5);
                editModal.css('display', 'flex');
            });

            $('.deleteBtn').click(function() {
                if (confirm('Are you sure you want to delete this mapping?')) {
                    const id = $(this).data('id');
                    $.post('Mapping_PSOs.php', {
                        delete_mapping_psos: true,
                        id: id
                    }, function() {
                        location.reload();
                    });
                }
            });
        });
    </script>
</body>
</html>