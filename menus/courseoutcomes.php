<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("../db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    if (isset($_POST['save_course_outcomes'])) {
        $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
        $course_outcomes = mysqli_real_escape_string($conn, $_POST['course_outcomes']);

        $stmt = $conn->prepare("INSERT INTO course_outcomes (course_code, course_outcomes) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_code, $course_outcomes);
        
        if ($stmt->execute()) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error adding record: " . $stmt->error;
        }
    }

 
    if (isset($_POST['update_course_outcomes'])) {
        $id = (int)$_POST['id'];
        $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
        $course_outcomes = mysqli_real_escape_string($conn, $_POST['course_outcomes']);

        $stmt = $conn->prepare("UPDATE course_outcomes SET course_code=?, course_outcomes=? WHERE id=?");
        $stmt->bind_param("ssi", $course_code, $course_outcomes, $id);
        
        if ($stmt->execute()) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating record: " . $stmt->error;
        }
    }

    
    if (isset($_POST['delete_course_outcomes'])) {
        $id = (int)$_POST['id'];

        $stmt = $conn->prepare("DELETE FROM course_outcomes WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error deleting record: " . $stmt->error;
        }
    }
}


$course_outcomes = [];
$result = $conn->query("SELECT * FROM course_outcomes ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $course_outcomes[] = $row;
    }
}
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
</head>
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

        .content-area {
            margin-left: 19vw;
            margin-right: 2vw;
            padding: 2rem;
        }

       
        #example {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1.5rem;
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
            background: var(--primary-color);
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

        .editBtn {
            margin-right: 15px;
            }

        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 600px;
            max-width: 95%;
            transform: translateY(-50px);
            animation: modalSlideIn 0.3s ease-out forwards;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        #example_filter {
            margin-bottom: 22px;
            }

            #addNewBtn {
                margin-top: 6rem;
                margin-bottom: 5rem !important;
                }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-family: inherit;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
    </style>
<body>

<script>
    
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.style.opacity = '1';
            modal.style.visibility = 'visible'; 
        } else {
            console.error('Modal not found:', modalId);
        }
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
        $('#modeltable').DataTable();

        
        $('#addNewBtn').click(() => {
            console.log('Add New button clicked'); 
            showModal('addNewModal');
        });
        $('#addNewRowBtn').click(() => showModal('addNewRowModal'));

        
        $('.editBtn').click(function() {
            $('#edit_id').val($(this).data('id'));
            $('#edit_course_code').val($(this).data('code'));
            $('#edit_course_outcomes').val($(this).data('course_outcomes'));
            showModal('editNewModal');
        });

        $('.editRowBtn').click(function() {
            $('#edit_course_id').val($(this).data('id'));
            $('#edit_course_outcome').val($(this).data('course_outcome'));
            $('#edit_expected_proficiency').val($(this).data('expected_proficiency'));
            $('#edit_expected_attainment').val($(this).data('expected_attainment'));
            showModal('editNewRowModal');
        });

        
        $('.delete-button').click(function() {
            $('#deleteItemId').val($(this).data('item-id'));
            showModal('deleteModal');
        });

        $('.delete-row-button').click(function() {
            $('#deleteRowItemId').val($(this).data('row-id'));
            showModal('deleteRowModal');
        });

        
        $('.close, .close-icon').click(function() {
            hideModal($(this).closest('.modal').attr('id'));
        });

        
        $(document).click((e) => {
            if ($(e.target).hasClass('modal')) {
                hideModal($(e.target).attr('id'));
            }
        });
    });

    function confirmDelete() {
        return confirm("Are you sure you want to delete this entry?");
    }
</script>

</script>
<div class="container">
    <?php include('../sidebar.php'); ?>
    <div class="content-area" id="content-area">
        <button id="addNewBtn" class="btn" style="margin-left: 10px; margin-bottom: 10px;">Add New</button>
        <table id="example" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Outcomes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sql = "SELECT * FROM course_outcomes";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['course_code']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['course_outcomes']) . "</td>";
                                echo "<td>";
                                    echo "<button class='btn btn-sm btn-primary editBtn' data-id='".$row['id']."' data-code='".$row['course_code']."' data-course_outcomes='".$row['course_outcomes']."'><i class='bi bi-pencil'></i>Edit</button>";
                                    echo "<button class='btn btn-sm btn-danger ms-2 delete-button' data-item-id='".$row['id']."'><i class='bi bi-trash'></i> Delete</button>";
                                echo "</td>";
                            echo "</tr>";
                        }
                    }
                ?>
            </tbody>
        </table>

        <!-- Modals -->
        <div id="addNewModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add Course Outcomes</h2>
                    <span class="close">&times;</span>
                </div>
                <form method="post">
                    <div class="form-row">
                        <div class="form-column">
                            <label for="course_code">Course Code:</label>
                            <input type="text" id="course_code" name="course_code" required>
                        </div>
                        <div class="form-column">
                            <label for="course_outcomes">Course Outcomes:</label>
                            <textarea name="course_outcomes" id="course_outcomes" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" name="save_course_outcomes">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="addNewRowModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Course Outcome Row</h2>
                <span class="close">&times;</span>
            </div>
            <form method="post">
                <div class="form-row">
                    <div class="form-column">
                        <label for="course_code">Course Code:</label>
                        <input type="text" name="course_code" required>
                    </div>
                    <div class="form-column">
                        <label for="course_outcome">Course Outcome:</label>
                        <textarea name="course_outcome" required></textarea>
                    </div>
                    <div class="form-column">
                        <label for="expected_proficiency">Expected Proficiency:</label>
                        <input type="text" name="expected_proficiency" required>
                    </div>
                    <div class="form-column">
                        <label for="expected_attainment">Expected Attainment:</label>
                        <input type="text" name="expected_attainment" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="save_course_rows">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editNewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Course Outcomes</h2>
                <span class="close">&times;</span>
            </div>
            <form method="post">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-row">
                    <div class="form-column">
                        <label for="edit_course_code">Course Code:</label>
                        <input type="text" id="edit_course_code" name="course_code" required>
                    </div>
                    <div class="form-column">
                        <label for="edit_course_outcomes">Course Outcomes:</label>
                        <textarea id="edit_course_outcomes" name="course_outcomes" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="update_course_outcomes">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <span class="close">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="deleteItemId">
                <div class="modal-body">
                    <!-- Are you sure you want to delete this entry? -->
                </div>
                <br><br>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('deleteModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger" name="delete_course_outcomes">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteRowModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <span class="close">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id" id="deleteRowItemId">
                <div class="modal-body">
                    Are you sure you want to delete this row?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideModal('deleteRowModal')">Cancel</button>
                    <button type="submit" class="btn btn-danger" name="delete_row">Delete</button>
                </div>
            </form>
        </div>
    </div>
        
    
    
    </div>
</div>



</body>
</html>