<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_course_designer'])) {
        $course_code = $_POST['course_code'];
        $course_designer = $_POST['course_designer'];
        
        $stmt = $conn->prepare("INSERT INTO course_designer (course_code, course_designer) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_code, $course_designer);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New course designer added successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_course_designer'])) {
        $id = $_POST['id'];
        $course_designer = $_POST['course_designer'];
        
        $stmt = $conn->prepare("UPDATE course_designer SET course_designer=? WHERE id=?");
        $stmt->bind_param("si", $course_designer, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Course designer updated successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_course_designer'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM course_designer WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Course designer deleted successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM course_designer ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Designer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        .table-container { margin-top: 1rem; max-width: 54vw; overflow-x: auto; }
        .close { margin-left: 95%; font-size: 25px; font-weight: bolder; cursor: pointer; }
        .popupclose { margin-left: 65%; font-size: 25px; font-weight: bolder; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        #dataTable th {
            white-space: nowrap;
            color: #f8f9fa;
        }
        thead {
            background-color: #4361ee;
            color: #f8f9fa;
        }
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
            padding: 2rem;
            transition: var(--transition);
        }
        #addNewBtn {
            margin-top: 9rem;
            transform: translateY(-50%);
            animation: float 3s ease-in-out infinite;
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
            opacity: 0;
            transition: var(--transition);
        }
        .modal.active {
            display: flex;
            opacity: 1;
        }
        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            width: 60rem;
            max-width: 90%;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.2);
            transform: translateY(-50px);
            transition: var(--transition);
        }
        .modal.active .modal-content {
            transform: translateY(0);
        }
        .btn-action {
            transition: var(--transition) !important;
            transform: scale(1);
        }
        .btn-action:hover {
            transform: scale(1.1);
        }
        #dataTable {
            --bs-table-bg: none;
        }
        .search-container {
            margin-bottom: 15px;
        }
        .search-container input {
            padding: 8px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
                <button id="addNewBtn" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Designer
                </button>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search by course code...">
                    </div>
                    <table id="dataTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Designer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="data-row">
                                    <td class="course-code"><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['course_designer']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-action edit-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-code="<?= $row['course_code'] ?>"
                                            data-course_designer="<?= $row['course_designer'] ?>">
                                            <i class="fas fa-edit"></i>Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action delete-btn" 
                                            data-id="<?= $row['id'] ?>">Delete
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Modal -->
                <div id="addModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <form method="POST">
                            <div class="form-grid">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" required>
                                </div>
                                <div class="mb-3">
                                    <label>Course Designer</label>
                                    <input type="text" name="course_designer" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="save_course_designer" class="btn btn-success">
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div id="editModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h4 class="mb-4"><i class="fas fa-edit"></i> Edit Course Designer</h4>
                        <form method="POST">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-grid">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" id="edit_course_code" disabled>
                                </div>
                                <div class="mb-3">
                                    <label>Course Designer</label>
                                    <input type="text" name="course_designer" id="edit_course_designer" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update_course_designer" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div id="deleteModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h5>
                            <span class="popupclose">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to permanently delete this course designer?</p>
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="id" id="delete_id">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" name="delete_course_designer" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Simple search functionality
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                $('.data-row').each(function() {
                    const courseCode = $(this).find('.course-code').text().toLowerCase();
                    if (courseCode.includes(searchText)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Modal Handling
            const modals = {
                add: $('#addModal'),
                edit: $('#editModal'),
                delete: $('#deleteModal')
            };

            // Show modal function
            function showModal(modal) {
                $('.modal').removeClass('active');
                modal.addClass('active');
            }

            // Close modal function
            function closeModals() {
                $('.modal').removeClass('active');
            }

            // Add New Button Click Handler
            $('#addNewBtn').click(() => showModal(modals.add));

            // Edit Button Click Handler
            $(document).on('click', '.edit-btn', function() {
                const data = $(this).data();
                $('#edit_id').val(data.id);
                $('#edit_course_code').val(data.code);
                $('#edit_course_designer').val(data.course_designer);
                showModal(modals.edit);
            });

            // Delete Button Click Handler
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                $('#delete_id').val(id);
                showModal(modals.delete);
            });

            // Close Modal Handlers
            $('.close, .popupclose').click(closeModals);
            
            // Close modal when clicking outside
            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModals();
                }
            });

            // Close modal with ESC key
            $(document).keyup(function(e) {
                if (e.key === "Escape") closeModals();
            });
        });
    </script>
</body>
</html>