<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_text_book'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $text_books = $_POST['text_book'];
        
        try {
            $conn->autocommit(FALSE);
            $stmt = $conn->prepare("INSERT INTO text_book (course_code, text_book) VALUES (?, ?)");
            
            foreach($text_books as $text_book) {
                $text_book = $conn->real_escape_string($text_book);
                $stmt->bind_param("ss", $course_code, $text_book);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['message'] = "Text books added successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['update_text_book'])) {
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $text_books = $_POST['text_book'];
        
        try {
            $conn->autocommit(FALSE);
            
            $delete_stmt = $conn->prepare("DELETE FROM text_book WHERE course_code = ?");
            $delete_stmt->bind_param("s", $course_code);
            $delete_stmt->execute();
            
            $insert_stmt = $conn->prepare("INSERT INTO text_book (course_code, text_book) VALUES (?, ?)");
            foreach($text_books as $text_book) {
                $text_book = $conn->real_escape_string($text_book);
                $insert_stmt->bind_param("ss", $course_code, $text_book);
                $insert_stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['message'] = "Text books updated successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    
    if (isset($_POST['delete_text_book'])) {
        $course_code = $_POST['course_code'];
        $stmt = $conn->prepare("DELETE FROM text_book WHERE course_code = ?");
        $stmt->bind_param("s", $course_code);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Text books deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$result = $conn->query("SELECT * FROM text_book ORDER BY id DESC");
$grouped_books = [];
while ($row = $result->fetch_assoc()) {
    $course_code = $row['course_code'];
    if (!isset($grouped_books[$course_code])) {
        $grouped_books[$course_code] = [];
    }
    array_unshift($grouped_books[$course_code], $row);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Book Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            margin: 0;
            padding: 0;
        }

        .content-area {
            margin-left: 19vw;
            padding: 2rem;
            margin-top: 68px;
        }

        .table-container {
            max-width: 122%;
            margin-left: 2rem;
            overflow-x: auto;
            margin-top: 24px;
            padding-bottom: 50px !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 0.5rem;
        }

        th, td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            margin: 0 2px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 800px;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-column {
            flex: 1;
        }

        .close {
            margin-top: -66px !important;
            font-size: 27px;
            cursor: pointer;
        }

        .form-column label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-column input,
        .form-column textarea,
        .form-column select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: var(--transition);
            font-family: 'Poppins', sans-serif;
        }

        .form-column input:focus,
        .form-column textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .book-group {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            background: #f8f9fa;
        }

        .edit-book-group {
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            background: #f8f9fa;
        }

        .remove-book {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .action {
            width: 176px;
        }

        .add_items {
            padding: 13px;
        }

        #courseSearch {
            padding: 0.8rem;
            width: 300px;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
            margin-bottom: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    
    <div class="content-area">
        <button class="btn btn-primary add_items" onclick="openModal()">
            <i class="fas fa-plus"></i> Add New Text Books
        </button>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                <?= $_SESSION['message'] ?>
                <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <input type="text" id="courseSearch" placeholder="Search by course code...">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Text Books</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped_books as $course_code => $books): ?>
                        <tr>
                            <td><?= htmlspecialchars($course_code) ?></td>
                            <td>
                                <ul style="margin: 0; padding-left: 1.5rem;">
                                    <?php foreach ($books as $book): ?>
                                        <li><?= htmlspecialchars($book['text_book']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td class="action">
                                <button class="btn btn-primary" onclick="openEditModal(
                                    '<?= htmlspecialchars($course_code) ?>',
                                    <?= htmlspecialchars(json_encode(array_column($books, 'text_book'))) ?>
                                )">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger" onclick="confirmDelete('<?= htmlspecialchars($course_code) ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Add New Text Books</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" name="course_code" required>
                        </div>
                    </div>
                    
                    <div id="bookContainer">
                        <div class="book-group">
                            <button type="button" class="remove-book" onclick="removeBook(this)" style="display: none;">&times;</button>
                            <div class="form-row">
                                <div class="form-column">
                                    <label>Text Book</label>
                                    <input type="text" name="text_book[]" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="addBook()">
                            <i class="fas fa-plus"></i> Add Another Book
                        </button>
                        <button type="submit" name="save_text_book" class="btn btn-success">
                            <i class="fas fa-save"></i> Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h3>Edit Text Books</h3>
                <form method="POST">
                    <input type="hidden" name="course_code" id="edit_course_code">
                    <div class="form-row">
                        <div class="form-column">
                            <label>Course Code</label>
                            <input type="text" id="display_course_code" disabled>
                        </div>
                    </div>
                    
                    <div id="editBookContainer"></div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" onclick="addEditBook()">
                            <i class="fas fa-plus"></i> Add Another Book
                        </button>
                        <button type="submit" name="update_text_book" class="btn btn-success">
                            <i class="fas fa-save"></i> Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeDeleteModal()">&times;</span>
                <h3>Confirm Delete</h3>
                <p>Are you sure you want to delete all text books for this course?</p>
                <form method="POST">
                    <input type="hidden" name="course_code" id="delete_course_code">
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" name="delete_text_book" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        function openEditModal(course_code, text_books) {
            document.getElementById('edit_course_code').value = course_code;
            document.getElementById('display_course_code').value = course_code;
            
            const container = document.getElementById('editBookContainer');
            container.innerHTML = '';
            
            text_books.forEach((book, index) => {
                const bookGroup = document.createElement('div');
                bookGroup.className = 'edit-book-group';
                bookGroup.innerHTML = `
                    <button type="button" class="remove-book" onclick="removeEditBook(this)">×</button>
                    <div class="form-row">
                        <div class="form-column">
                            <label>Text Book</label>
                            <input type="text" name="text_book[]" value="${book}" required>
                        </div>
                    </div>
                `;
                container.appendChild(bookGroup);
            });
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function addBook() {
            const bookContainer = document.getElementById('bookContainer');
            const newBook = document.createElement('div');
            newBook.className = 'book-group';
            newBook.innerHTML = `
                <button type="button" class="remove-book" onclick="removeBook(this)">×</button>
                <div class="form-row">
                    <div class="form-column">
                        <label>Text Book</label>
                        <input type="text" name="text_book[]" required>
                    </div>
                </div>
            `;
            bookContainer.appendChild(newBook);
            bookContainer.lastElementChild.scrollIntoView({ behavior: 'smooth' });
        }

        function addEditBook() {
            const container = document.getElementById('editBookContainer');
            const bookGroup = document.createElement('div');
            bookGroup.className = 'edit-book-group';
            bookGroup.innerHTML = `
                <button type="button" class="remove-book" onclick="removeEditBook(this)">×</button>
                <div class="form-row">
                    <div class="form-column">
                        <label>Text Book</label>
                        <input type="text" name="text_book[]" required>
                    </div>
                </div>
            `;
            container.appendChild(bookGroup);
            container.lastElementChild.scrollIntoView({ behavior: 'smooth' });
        }

        function removeBook(btn) {
            const bookGroups = document.querySelectorAll('.book-group');
            if (bookGroups.length > 1) {
                btn.closest('.book-group').remove();
            }
        }

        function removeEditBook(btn) {
            const bookGroups = document.querySelectorAll('.edit-book-group');
            if (bookGroups.length > 1) {
                btn.closest('.edit-book-group').remove();
            }
        }

        function confirmDelete(course_code) {
            document.getElementById('delete_course_code').value = course_code;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        document.getElementById('courseSearch').addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const courseCode = row.cells[0].textContent.toLowerCase();
                row.style.display = courseCode.includes(searchTerm) ? '' : 'none';
            });
        });

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
                closeEditModal();
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>