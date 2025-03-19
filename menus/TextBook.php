<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_text_book'])) {
        $course_code = $_POST['course_code'];
        $text_book = $_POST['text_book'];
        
        $stmt = $conn->prepare("INSERT INTO text_book (course_code, text_book) VALUES (?, ?)");
        $stmt->bind_param("ss", $course_code, $text_book);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New text book added successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_text_book'])) {
        $id = $_POST['id'];
        $course_code = $_POST['course_code']; // Added course_code in form
        $text_book = $_POST['text_book'];
        
        $stmt = $conn->prepare("UPDATE text_book SET course_code=?, text_book=? WHERE id=?");
        $stmt->bind_param("ssi", $course_code, $text_book, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Text book updated successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_text_book'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM text_book WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Text book deleted successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM text_book");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Book Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-container { margin: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php include('../sidebar.php'); ?>

        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
            <button id="addNewBtn" class="btn btn-primary mb-3 mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Add New Text Book
            </button>

            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="table-container">
                <table id="dataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Text Book</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['course_code']) ?></td>
                                <td><?= htmlspecialchars($row['text_book']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        data-id="<?= $row['id'] ?>"
                                        data-code="<?= $row['course_code'] ?>"
                                        data-text_book="<?= $row['text_book'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
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
            <div class="modal fade" id="addModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add New Text Book</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Text Book</label>
                                    <input type="text" name="text_book" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="save_text_book" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Text Book</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" name="course_code" id="edit_course_code" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Text Book</label>
                                    <input type="text" name="text_book" id="edit_text_book" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update_text_book" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="delete_id">
                                <p>Are you sure you want to delete this text book?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="delete_text_book" class="btn btn-danger">Delete</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="../public/css/sidebar.css">

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();

            // Edit Modal Handling
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const code = $(this).data('code');
                const textBook = $(this).data('text_book');
                $('#edit_id').val(id);
                $('#edit_course_code').val(code);
                $('#edit_text_book').val(textBook);
            });

            // Delete Modal Handling
            $('.delete-btn').click(function() {
                $('#delete_id').val($(this).data('id'));
            });
        });
    </script>
</body>
</html>