<?php
session_start();
include('../db_connect.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        if (isset($_POST['save_preamble'])) {
            $course_code = trim($_POST['course_code']);
            $preamble = trim($_POST['preamble']);

            if (empty($course_code) || empty($preamble)) {
                throw new Exception("All fields are required");
            }

            $stmt = $conn->prepare("INSERT INTO preamble (course_code, preamble) VALUES (?, ?)");
            $stmt->bind_param("ss", $course_code, $preamble);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Preamble added successfully!";
            } else {
                throw new Exception("Error adding preamble: " . $stmt->error);
            }
        }

       
        if (isset($_POST['delete_id'])) {
            $id = intval($_POST['delete_id']);
            
            if ($id <= 0) {
                throw new Exception("Invalid preamble ID");
            }

            $stmt = $conn->prepare("DELETE FROM preamble WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error deleting preamble: " . $stmt->error);
            }
            $_SESSION['message'] = "Preamble deleted successfully!";
        }

        
        if (isset($_POST['update_preamble'])) {
            $id = intval($_POST['id']);
            $course_code = trim($_POST['course_code']);
            $preamble = trim($_POST['preamble']);

            if ($id <= 0 || empty($course_code) || empty($preamble)) {
                throw new Exception("Invalid data provided");
            }

            $stmt = $conn->prepare("UPDATE preamble SET course_code = ?, preamble = ? WHERE id = ?");
            $stmt->bind_param("ssi", $course_code, $preamble, $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Preamble updated successfully!";
            } else {
                throw new Exception("Error updating preamble: " . $stmt->error);
            }
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preamble Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(6, 214, 160, 0.3);
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

     
        #preambleTable {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
            border-collapse: separate;
            border-spacing: 0;
            width: 55rem;
        }

        #preambleTable thead {
            background: var(--primary-color);
            color: white;
        }

        #preambleTable th {
            padding: 1rem;
            font-weight: 600;
            text-align: left;
        }

        #preambleTable td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        #preambleTable tr:last-child td {
            border-bottom: none;
        }

        #preambleTable tbody tr:hover {
            background-color: #f8f9fa;
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

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                backdrop-filter: blur(0);
            }
            to {
                opacity: 1;
                backdrop-filter: blur(4px);
            }
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 500px;
            max-width: 95%;
            transform: translateY(-50px);
            animation: modalSlideIn 0.3s ease-out forwards;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        @keyframes modalSlideIn {
            to {
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--text-color);
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
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

      
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-success {
            background: #e6fcf5;
            color: #099268;
            border: 1px solid #96f2d7;
        }

        .alert-error {
            background: #fff5f5;
            color: #f03e3e;
            border: 1px solid #ffa8a8;
        }

        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
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

            #preambleTable {
                display: block;
                overflow-x: auto;
            }
        }
        #addNewBtn {   
            margin-top: 9rem;   
            background: #3650c7; 
            margin-bottom:3rem;
        }
        table{
            padding:3rem;
        }
    </style>

    <script>
        // Enhanced Modal Handling with Animation
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                document.querySelectorAll('.modal').forEach(modal => {
                    hideModal(modal.id);
                });
            }
        }

        // Smooth scroll for alerts
        window.addEventListener('DOMContentLoaded', () => {
            const alert = document.querySelector('.alert');
            if (alert) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
</head>
<body>
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

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            document.querySelectorAll('.modal').forEach(modal => {
                hideModal(modal.id);
            });
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const alert = document.querySelector('.alert');
        if (alert) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });

    </script>
    <div class="container">
        <?php include('../sidebar.php'); ?>
        
        <div class="content-area">
            <!-- Messages Display -->
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <button id="addNewBtn" onclick="showModal('addNewModal')" class="btn btn-success">Add New</button>

            <!-- Add New Modal -->
            <div id="addNewModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Add Preamble</h3>
                        <span class="close" onclick="hideModal('addNewModal')">&times;</span>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="save_preamble" value="1">
                        <div class="form-group">
                            <label>Course Code:</label>
                            <input type="text" name="course_code" required>
                        </div>
                        <div class="form-group">
                            <label>Preamble:</label>
                            <textarea name="preamble" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit Preamble</h3>
                        <span class="close" onclick="hideModal('editModal')">&times;</span>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="update_preamble" value="1">
                        <input type="hidden" name="id" id="editId">
                        <div class="form-group">
                            <label>Course Code:</label>
                            <input type="text" name="course_code" id="editCourseCode" required>
                        </div>
                        <div class="form-group">
                            <label>Preamble:</label>
                            <textarea name="preamble" id="editPreamble" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>

            <!-- Main Table -->
            <table id="preambleTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Preamble</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM preamble");
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= htmlspecialchars($row['preamble']) ?></td>
                        <td>
                            <button class="btn btn-primary" 
                                    onclick="openEditModal(
                                        <?= $row['id'] ?>, 
                                        '<?= $row['course_code'] ?>', 
                                        `<?= addslashes($row['preamble']) ?>`
                                    )">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

  
        function openEditModal(id, courseCode, preamble) {
            document.getElementById('editId').value = id;
            document.getElementById('editCourseCode').value = courseCode;
            document.getElementById('editPreamble').value = preamble;
            showModal('editModal');
        }

        
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        }
    </script>
</body>
</html>