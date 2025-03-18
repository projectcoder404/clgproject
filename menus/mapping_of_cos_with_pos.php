
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
        $po1 = $conn->real_escape_string($_POST['po1']);
        $po2 = $conn->real_escape_string($_POST['po2']);
        $po3 = $conn->real_escape_string($_POST['po3']);
        $po4 = $conn->real_escape_string($_POST['po4']);
        $po5 = $conn->real_escape_string($_POST['po5']);
        $po6 = $conn->real_escape_string($_POST['po6']);
        $po7 = $conn->real_escape_string($_POST['po7']);

        $sql = "INSERT INTO mapping_pos (course_code, po1, po2, po3, po4, po5, po6, po7)
                VALUES ('$course_code', '$po1', '$po2', '$po3', '$po4', '$po5', '$po6', '$po7')";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: mapping_of_cos_with_pos.php");
        exit();
    }

    // Update Mapping
    if (isset($_POST['update_mapping_cos'])) {
        $id = intval($_POST['id']);
        $po1 = $conn->real_escape_string($_POST['po1']);
        $po2 = $conn->real_escape_string($_POST['po2']);
        $po3 = $conn->real_escape_string($_POST['po3']);
        $po4 = $conn->real_escape_string($_POST['po4']);
        $po5 = $conn->real_escape_string($_POST['po5']);
        $po6 = $conn->real_escape_string($_POST['po6']);
        $po7 = $conn->real_escape_string($_POST['po7']);

        $sql = "UPDATE mapping_pos SET 
                po1='$po1', po2='$po2', po3='$po3', po4='$po4', 
                po5='$po5', po6='$po6', po7='$po7' 
                WHERE id=$id";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Mapping updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO-PO Mapping</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        .content-area {
            margin-left: 19vw;
            margin-right: 2vw;
            padding: 20px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
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

        .btn-success {
            background: #4361ee;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
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
                <table id="mappingTable" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>Course Code</th>
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
                        <?php
                        $result = $conn->query("SELECT * FROM mapping_pos");
                        while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['po1']) ?></td>
                                <td><?= htmlspecialchars($row['po2']) ?></td>
                                <td><?= htmlspecialchars($row['po3']) ?></td>
                                <td><?= htmlspecialchars($row['po4']) ?></td>
                                <td><?= htmlspecialchars($row['po5']) ?></td>
                                <td><?= htmlspecialchars($row['po6']) ?></td>
                                <td><?= htmlspecialchars($row['po7']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary editBtn" 
                                        data-id="<?= $row['id'] ?>"
                                        data-code="<?= $row['course_code'] ?>"
                                        data-po1="<?= $row['po1'] ?>"
                                        data-po2="<?= $row['po2'] ?>"
                                        data-po3="<?= $row['po3'] ?>"
                                        data-po4="<?= $row['po4'] ?>"
                                        data-po5="<?= $row['po5'] ?>"
                                        data-po6="<?= $row['po6'] ?>"
                                        data-po7="<?= $row['po7'] ?>">
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
                    <h2>Add CO-PO Mapping</h2>
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
                        <div class="form-row">
                            <div class="form-column">
                                <label for="po6">PO6</label>
                                <input type="text" name="po6" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="po7">PO7</label>
                                <input type="text" name="po7" class="restricted-input" required>
                            </div>
                        </div>
                        <button type="submit" name="save_mapping_cos" class="btn btn-success mt-3">Save Mapping</button>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Edit CO-PO Mapping</h2>
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
                        <div class="form-row">
                            <div class="form-column">
                                <label for="edit_po6">PO6</label>
                                <input type="text" id="edit_po6" name="po6" class="restricted-input" required>
                            </div>
                            <div class="form-column">
                                <label for="edit_po7">PO7</label>
                                <input type="text" id="edit_po7" name="po7" class="restricted-input" required>
                            </div>
                        </div>
                        <button type="submit" name="update_mapping_cos" class="btn btn-success mt-3">Update Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#mappingTable').DataTable({
                dom: '<"top"f>rt<"bottom"lip>',
                responsive: true
            });

            // Modal Handling
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

            // Input Validation
            $('.restricted-input').on('input', function() {
                let value = $(this).val().toUpperCase();
                value = value.replace(/[^SM]/g, '');
                $(this).val(value);
            });

            // Edit Button Handling
            $('.editBtn').click(function() {
                const data = $(this).data();
                $('#edit_id').val(data.id);
                $('#edit_course_code').val(data.code);
                $('#edit_po1').val(data.po1);
                $('#edit_po2').val(data.po2);
                $('#edit_po3').val(data.po3);
                $('#edit_po4').val(data.po4);
                $('#edit_po5').val(data.po5);
                $('#edit_po6').val(data.po6);
                $('#edit_po7').val(data.po7);
                editModal.css('display', 'flex');
            });

            // Delete Button Handling
            $('.deleteBtn').click(function() {
                if (confirm('Are you sure you want to delete this mapping?')) {
                    const id = $(this).data('id');
                    $.post('mapping_of_cos_with_pos.php', {
                        delete_mapping_pos: true,
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
        }

        #addNewBtn{
            margin-top: 9rem;
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
            height: 52rem;
            max-width: 90%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .close {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }

        .close:hover {
            color: #000;
        }

        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            column-gap: 130px;
            row-gap: 10px;
            margin-right: 10px;
            padding: 40px;
        }

        
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
        }


        .formbtn {
            margin-top: 1rem;
        }

    
        .btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            #example {
                display: block;
                overflow-x: auto;
            }
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px; 
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 8px 12px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-size: 14px; 
            transition: border-color 0.3s ease; 
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #28a745; 
            outline: none; 
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); 
        }

        .btn-success {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(67, 97, 238, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
            background: #3650c7;
        }

    
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
            padding: 1rem;
        }

        #example {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        #example thead {
            background: var(--primary-color);
            color: white;
        }

        #example th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            border-bottom: none;
        }

        #example td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        #example tr:last-child td {
            border-bottom: none;
        }

        #example tbody tr:hover {
            background-color: #f8f9fa;
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

        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: var(--transition);
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            margin: 0 0.25rem;
            transition: var(--transition) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--primary-color) !important;
            color: white !important;
        }
    </style>
