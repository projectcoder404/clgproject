<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_chapter'])) {
        $course_code = $_POST['course_code'];
        $unit = $_POST['unit'];
        $chapter = $_POST['chapter'];
        $book = $_POST['book'];
        
        $stmt = $conn->prepare("INSERT INTO chapter (course_code, unit, chapter, book) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $course_code, $unit, $chapter, $book);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New chapter added successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_chapter'])) {
        $id = $_POST['id'];
        $unit = $_POST['unit'];
        $chapter = $_POST['chapter'];
        $book = $_POST['book'];
        
        $stmt = $conn->prepare("UPDATE chapter SET unit=?, chapter=?, book=? WHERE id=?");
        $stmt->bind_param("sssi", $unit, $chapter, $book, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Chapter updated successfully!";
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
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM chapter");
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
        
        .table-container { margin-top: 1rem; max-width: 74vw; overflow-x: auto; }
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

        @keyframes float {
            0%, 100% { transform: translateY(-2px); }
            50% { transform: translateY(2px); }
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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
                <button id="addNewBtn" class="btn btn-primary mb-3">
                    <i class="fas fa-plus"></i> Add New Chapter
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
                                <th>Unit</th>
                                <th>Chapter</th>
                                <th>Book</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['unit']) ?></td>
                                    <td><?= htmlspecialchars($row['chapter']) ?></td>
                                    <td><?= htmlspecialchars($row['book']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-action edit-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-code="<?= $row['course_code'] ?>"
                                            data-unit="<?= $row['unit'] ?>"
                                            data-chapter="<?= $row['chapter'] ?>"
                                            data-book="<?= $row['book'] ?>">
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
                                    <label>Unit</label>
                                    <input type="text" name="unit" class="restricted-input" required>
                                </div>
                                <div class="mb-3">
                                    <label>Chapter</label>
                                    <input type="number" name="chapter" required>
                                </div>
                                <div class="mb-3">
                                    <label>Book</label>
                                    <input type="number" name="book" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="save_chapter" class="btn btn-success">
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
                        <h4 class="mb-4"><i class="fas fa-edit"></i> Edit Chapter</h4>
                        <form method="POST">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-grid">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" id="edit_course_code" disabled>
                                </div>
                                <div class="mb-3">
                                    <label>Unit</label>
                                    <input type="text" name="unit" id="edit_unit" class="restricted-input" required>
                                </div>
                                <div class="mb-3">
                                    <label>Chapter</label>
                                    <input type="number" name="chapter" id="edit_chapter" required>
                                </div>
                                <div class="mb-3">
                                    <label>Book</label>
                                    <input type="number" name="book" id="edit_book" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update_chapter" class="btn btn-primary">
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
                            <p>Are you sure you want to permanently delete this chapter?</p>
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="id" id="delete_id">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" name="delete_chapter" class="btn btn-danger">
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
                    searchPlaceholder: "Search chapters..."
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
                $('#edit_unit').val(data.unit);
                $('#edit_chapter').val(data.chapter);
                $('#edit_book').val(data.book);
                showModal(modals.edit);
            });

            // Input validation for unit field
            document.querySelectorAll('.restricted-input').forEach(input => {
                input.addEventListener('input', function () {
                    let value = this.value.toUpperCase();
                    if (!/^$|^[IVX]*$/.test(value)) { 
                        this.value = value.replace(/[^IVX]/g, '');
                    } else {
                        this.value = value; 
                    }
                });
            });

            // Rest of the original JavaScript from style code
            // ... (modal handling and other functions)
        });
    </script>
</body>
</html>