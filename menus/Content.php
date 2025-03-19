<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_content'])) {
        // Handle save logic
        $course_code = $_POST['course_code'];
        $unit = $_POST['unit'];
        $content = $_POST['content'];
        $hour = $_POST['hour'];
        
        $stmt = $conn->prepare("INSERT INTO content (course_code, unit, content, hour) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $course_code, $unit, $content, $hour);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New content added successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['update_content'])) {
        // Handle update logic
        $id = $_POST['id'];
        $unit = $_POST['unit'];
        $content = $_POST['content'];
        $hour = $_POST['hour'];
        
        $stmt = $conn->prepare("UPDATE content SET unit=?, content=?, hour=? WHERE id=?");
        $stmt->bind_param("sssi", $unit, $content, $hour, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Content updated successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_content'])) {
        // Handle delete logic
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM content WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Content deleted successfully!";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM content");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management</title>
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
                    <i class="fas fa-plus"></i> Add New Item
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
                                <th>Content</th>
                                <th>Hours</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                                    <td><?= htmlspecialchars($row['unit']) ?></td>
                                    <td><?= htmlspecialchars($row['content']) ?></td>
                                    <td><?= htmlspecialchars($row['hour']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-action edit-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-code="<?= $row['course_code'] ?>"
                                            data-unit="<?= $row['unit'] ?>"
                                            data-content="<?= $row['content'] ?>"
                                            data-hour="<?= $row['hour'] ?>">
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
                                    <input type="text" name="unit" required>
                                </div>
                                <div class="mb-3">
                                    <label>Content</label>
                                    <textarea name="content" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Hours</label>
                                    <input type="text" name="hour" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="save_content" class="btn btn-success">
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
                        <h4 class="mb-4"><i class="fas fa-edit"></i> Edit Entry</h4>
                        <form method="POST">
                            <input type="hidden" name="id" id="edit_id">
                            <div class="form-grid">
                                <div class="mb-3">
                                    <label>Course Code</label>
                                    <input type="text" id="edit_course_code" disabled>
                                </div>
                                <div class="mb-3">
                                    <label>Unit</label>
                                    <input type="text" name="unit" id="edit_unit" required>
                                </div>
                                <div class="mb-3">
                                    <label>Content</label>
                                    <textarea name="content" id="edit_content" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Hours</label>
                                    <input type="text" name="hour" id="edit_hour" required>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" name="update_content" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Modal (Keep original structure from style code) -->
                <div id="deleteModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h5>
                            <span class="popupclose">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to permanently delete this entry?</p>
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="id" id="delete_id">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" name="delete_content" class="btn btn-danger">
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

 <!-- PHP portion remains the same as in original code -->
<!-- Focus on JavaScript fixes below -->

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#dataTable').DataTable({
            responsive: true,
            dom: '<"top"<"d-flex justify-content-between align-items-center"fB>>rt<"bottom"lip>',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
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

        // Close modal function
        function closeModals() {
            $('.modal').removeClass('active');
        }

        // Add New Button
        $('#addNewBtn').click(() => showModal(modals.add));

        // Edit Button
        $(document).on('click', '.edit-btn', function() {
            const data = $(this).data();
            $('#edit_id').val(data.id);
            $('#edit_course_code').val(data.code);
            $('#edit_unit').val(data.unit);
            $('#edit_content').val(data.content);
            $('#edit_hour').val(data.hour);
            showModal(modals.edit);
        });

        // Delete Button
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            $('#delete_id').val(id);
            showModal(modals.delete);
        });

        // Close buttons
        $('.close, .popupclose').click(closeModals);

        // Close modal on background click
        $(window).click(function(e) {
            if ($(e.target).hasClass('modal')) {
                closeModals();
            }
        });

        // Close modal on ESC key
        $(document).keyup(function(e) {
            if (e.key === "Escape") closeModals();
        });
    });
</script>
</body>
</html>