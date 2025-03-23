<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../db_connect.php");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    if (isset($_POST['save_prerequisite'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $pre_requisite = $conn->real_escape_string($_POST['pre_requisite']);

        $sql = "INSERT INTO pre_requisite (course_code, pre_requisite) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $course_code, $pre_requisite);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Prerequisite added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding prerequisite: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: prerequisite.php");
        exit();
    }

    
    if (isset($_POST['update_prerequisite'])) {
        $id = intval($_POST['id']);
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $pre_requisite = $conn->real_escape_string($_POST['pre_requisite']);

        $sql = "UPDATE pre_requisite SET course_code=?, pre_requisite=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $course_code, $pre_requisite, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Prerequisite updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating prerequisite: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: prerequisite.php");
        exit();
    }

    
    if (isset($_POST['delete_prerequisite'])) {
        $id = intval($_POST['id']);

        $sql = "DELETE FROM pre_requisite WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Prerequisite deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting prerequisite: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: prerequisite.php");
        exit();
    }
}


$sql = "SELECT * FROM pre_requisite ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre Requisite</title>
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
        }
        .button-container {
            justify-content: flex-end;
            margin-bottom: 3rem;
            margin-top: 6rem;
        }

        
        #example {
            width: 100% !important;
            max-width: 100%;
        }

        
        .modal-content {
            width: 600px; 
        }

        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .close-icon {
            float: right;
            margin-right: 11px;
            font-size: 27px;
            font-weight: bolder;
            cursor: pointer;
            }

        .content-area {
            margin-left: 20vw;
            margin-right: 2vw;
            padding: 2rem;
            width: 71rem !important;
        }

        
        #example {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 28px !important;
        }

        #example thead {
            background: var(--primary-color);
            color: white;
        }

        #example th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
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

        #example_filter input {
            height: 36px !important;
            width: 165px;
        }

        #example_length {
            margin-bottom: 36px;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        

        .btn-primary {
            background: var(--primary-color) !important;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(239, 71, 111, 0.3);
        }

        
        .modal {
            display: none; 
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            width: 90%;
            max-width: 530px;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            animation: slideIn 0.3s ease-out forwards;
        }
        .modal-form {
            margin-top: 50px;
            margin-right: 42px;
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

        
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-family: inherit;
            transition: var(--transition);
            margin-bottom: 30px;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .formbtn {
            padding-top: 15px;
            padding-right: 30px;
            padding-bottom: 15px;
            padding-left: 30px;
            margin-top: 33px;
            border-radius: 13px;
            
        }
        
        
        @keyframes modalFadeIn {
            from { opacity: 0; backdrop-filter: blur(0); }
            to { opacity: 1; backdrop-filter: blur(4px); }
        }

        @keyframes modalSlideIn {
            to { transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .content-area {
                margin-left: 0;
                padding: 1rem;
            }

            .modal-content {
                width: 90%;
                padding: 1.5rem;
            }

            #example {
                display: block;
                overflow-x: auto;
            }
        }
        /* margin-bottom:3rem;
        } */
        #example{
            padding:3rem;
        }

    </style>
</head>
<body>

<div class="container">
        <?php include('../sidebar.php'); ?>
        <div class="content-area" id="content-area">
            
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>

            <div class="button-container">
                <button id="addNewBtn" class="btn btn-primary">Add New</button>
            </div>

            <table id="example" class="table table-striped display nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Pre-requisite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['pre_requisite']) ?></td>
                                <td class="action-buttons">
                                    <button class='btn btn-primary editBtn' 
                                            data-id='<?= $row['id'] ?>' 
                                            data-code='<?= $row['course_code'] ?>' 
                                            data-pre='<?= $row['pre_requisite'] ?>'>
                                        Edit
                                    </button>
                                    <form method='post' style='display:inline;'>
                                        <input type='hidden' name='id' value='<?= $row['id'] ?>'>
                                        <button type='submit' name='delete_prerequisite' 
                                                class='btn btn-danger' 
                                                onclick='return confirmDelete()'>
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No prerequisites found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            
            <div id="addNewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Add Pre Requisite</h2>
                        <span class="close-icon">&times;</span>
                    </div>
                    <form method="post" class="modal-form">
                        <div class="form-group">
                            <label>Course Code:</label>
                            <input type="text" name="course_code" class="input-field" required>
                        </div>
                        <div class="form-group">
                            <label>Pre-requisite:</label>
                            <textarea name="pre_requisite" class="textarea-field" required></textarea>
                        </div>
                        <button type="submit" name="save_prerequisite" class="btn-primary formbtn">Save</button>
                    </form>
                </div>
            </div>

            
            <div id="editNewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Edit Pre Requisite</h2>
                        <span class="close-icon">&times;</span>
                    </div>
                    <form method="post">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label>Course Code:</label>
                            <input type="text" id="edit_course_code" name="course_code" required>
                        </div>
                        <div class="form-group">
                            <label>Pre-requisite:</label>
                            <textarea id="edit_pre_requisite" name="pre_requisite" required></textarea>
                        </div>
                        <button type="submit" name="update_prerequisite" class="btn-primary formbtn">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
    
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    $(document).ready(function () {
        
        $('#example').DataTable();

        
        $('#addNewBtn').click(function () {
            showModal('addNewModal');
        });

        
        $('.close, .close-icon').click(function () {
            hideModal('addNewModal');
            hideModal('editNewModal');
        });

        
        $('.editBtn').click(function () {
            $('#edit_id').val($(this).data('id'));
            $('#edit_course_code').val($(this).data('code'));
            $('#edit_pre_requisite').val($(this).data('pre'));
            showModal('editNewModal');
        });

        
        $(document).on('click', function (e) {
            if ($(e.target).hasClass('modal')) {
                hideModal('addNewModal');
                hideModal('editNewModal');
            }
        });
    });

    function confirmDelete() {
        return confirm("Are you sure you want to delete this entry?");
    }
</script>

</body>
</html>
