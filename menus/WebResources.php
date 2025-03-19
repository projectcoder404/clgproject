<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_web_resources'])) {
        $course_code = $_POST['course_code'];
        $web_resources = $_POST['web_resources'];
        
        $stmt = $conn->prepare("INSERT INTO web_resources (course_code, web_resources) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_code, $web_resources);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New web resource added successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_web_resources'])) {
        $id = $_POST['id'];
        $web_resources = $_POST['web_resources'];
        
        $stmt = $conn->prepare("UPDATE web_resources SET web_resources=? WHERE id=?");
        $stmt->bind_param("si", $web_resources, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Web resource updated successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_web_resources'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM web_resources WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Web resource deleted successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM web_resources");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Resources Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        /* Original style code */
        .table-container { margin-top: 1rem; max-width: 74vw; overflow-x: auto; }
        .close { margin-left: 95%; font-size: 25px; font-weight: bolder; cursor: pointer; }
        .popupclose { margin-left: 65%; font-size: 25px; font-weight: bolder; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        /* ... rest of the original styles ... */
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
                <button id="addNewBtn" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Web Resource
                </button>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table id="dataTable" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Web Resources</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['web_resources']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-action edit-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-code="<?= $row['course_code'] ?>"
                                            data-web_resources="<?= $row['web_resources'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-action delete-btn" 
                                            data-id="<?= $row['id'] ?>">
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
                                    <label>Web Resources</label>
                                    <input type="text" name="web_resources" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="save_web_resources" class="btn btn-success">
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
                        <h4 class="mb-4"><i class="fas fa-edit"></i> Edit Web Resource</h4>
                        <form method="POST">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-grid">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" id="edit_course_code" disabled>
                                </div>
                                <div class="mb-3">
                                    <label>Web Resources</label>
                                    <input type="text" name="web_resources" id="edit_web_resources" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update_web_resources" class="btn btn-primary">
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
                            <p>Are you sure you want to permanently delete this web resource?</p>
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="id" id="delete_id">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" name="delete_web_resources" class="btn btn-danger">
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#dataTable').DataTable({
                responsive: true,
                dom: '<"top"<"d-flex justify-content-between align-items-center"fB>>rt<"bottom"lip>',
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search web resources..."
                }
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

            // Edit Button
            $(document).on('click', '.edit-btn', function() {
                const data = $(this).data();
                $('#edit_id').val(data.id);
                $('#edit_course_code').val(data.code);
                $('#edit_web_resources').val(data.web_resources);
                showModal(modals.edit);
            });

            // Delete Button
            $(document).on('click', '.delete-btn', function() {
                $('#delete_id').val($(this).data('id'));
                showModal(modals.delete);
            });

            // Close Modals
            $(document).on('click', '.close, .modal-close, .btn-secondary', () => {
                $('.modal').removeClass('active');
            });

            // Add Button
            $('#addNewBtn').click(() => showModal(modals.add));
        });
    </script>
</body>
</html>