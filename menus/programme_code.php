
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once '../db_connect.php';
session_start();

// Handle different actions based on the request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    // Fetch course details for editing
    if ($action === 'fetch_course' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM courses WHERE id = $id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $course = $result->fetch_assoc();
            echo json_encode($course);
        } else {
            echo json_encode(['error' => 'Course not found.']);
        }
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for adding a course
    if (isset($_POST['save'])) {
        // Sanitize inputs
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $course_title = $conn->real_escape_string($_POST['course_title']);
        $category = $conn->real_escape_string($_POST['category']);
        $l = intval($_POST['l']);
        $t = intval($_POST['t']);
        $p = intval($_POST['p']);
        $credit = intval($_POST['credit']);
        $year = $conn->real_escape_string($_POST['year']);
        $semester = $conn->real_escape_string($_POST['semester']);
        $internal = intval($_POST['internal']);
        $external = intval($_POST['external']);
        $total = intval($_POST['total']);

        // Insert query
        $sql = "INSERT INTO courses (course_code, course_title, category, l, t, p, credit, year, semester, internal, external, total)
                VALUES ('$course_code', '$course_title', '$category', $l, $t, $p, $credit, '$year', '$semester', $internal, $external, $total)";

        if ($conn->query($sql)) {
            $_SESSION['message'] = "Course added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: programme_code.php");
        exit();
    }

    // Handle form submission for updating a course
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        error_log("Update request received: " . print_r($_POST, true)); // Debugging
    
        $id = intval($_POST['id']);
        $course_code = $conn->real_escape_string($_POST['course_code']);
        $course_title = $conn->real_escape_string($_POST['course_title']);
        $category = $conn->real_escape_string($_POST['category']);
        $l = intval($_POST['l']);
        $t = intval($_POST['t']);
        $p = intval($_POST['p']);
        $credit = intval($_POST['credit']);
        $year = $conn->real_escape_string($_POST['year']);
        $semester = $conn->real_escape_string($_POST['semester']);
        $internal = intval($_POST['internal']);
        $external = intval($_POST['external']);
        $total = intval($_POST['total']);
    
        $sql = "UPDATE courses SET
                course_code = '$course_code',
                course_title = '$course_title',
                category = '$category',
                l = $l,
                t = $t,
                p = $p,
                credit = $credit,
                year = '$year',
                semester = '$semester',
                internal = $internal,
                external = $external,
                total = $total
                WHERE id = $id";
    
        error_log("Executing SQL Query: " . $sql); // Debugging
    
        if ($conn->query($sql)) {
            echo json_encode(['success' => 'Course updated successfully!']);
        } else {
            error_log("Database Error: " . $conn->error); // Debugging
            echo json_encode(['error' => 'Error: ' . $conn->error]);
        }
        exit();
    }

    // Handle form submission for deleting a course
    if (isset($_POST['delete'])) {
        $id = intval($_POST['id']);

        if ($id <= 0) {
            echo json_encode(['error' => 'Invalid course ID.']);
            exit();
        }

        $sql = "DELETE FROM courses WHERE id = $id";

        if ($conn->query($sql)) {
            echo json_encode(['success' => 'Course deleted successfully!']);
        } else {
            echo json_encode(['error' => 'Error: ' . $conn->error]);
        }
        exit();
    }
}

// Fetch all courses
$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme Code</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="./public/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <style>
        /* General Styles */
        .content-area {
            margin-left: 19vw;
            margin-right: 2vw;
        }

        /* Modal Styles */
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

        /* Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            column-gap: 130px;
            row-gap: 10px;
            margin-right: 10px;
            padding: 40px;
        }

        /* Form Group Styling */
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

        /* Button Styling */
        .btn-success {
            background: #28a745;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            margin-top: 6rem;
            margin-bottom: 2rem;
        }

        .btn-success:hover {
            background: #218838;
        }

        .formbtn {
            margin-top: 1rem;
        }

        /* Table Container */
        .table-container {
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Table Styling */
        #example {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10rem;
        }

        #example th,
        #example td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        #example th {
            background-color: #28a745;
            color: white;
            font-weight: 600;
        }

        #example tr:hover {
            background-color: #f5f5f5;
        }

        /* Buttons Styling */
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

        /* Responsive Table */
        @media (max-width: 768px) {
            #example {
                display: block;
                overflow-x: auto;
            }
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 20px; /* Add gap between search input and table */
        }

        .dataTables_wrapper .dataTables_filter input {
            padding: 8px 12px; /* Add padding to the search input */
            border: 1px solid #ddd; /* Add a border */
            border-radius: 5px; /* Rounded corners */
            font-size: 14px; /* Adjust font size */
            transition: border-color 0.3s ease; /* Smooth transition */
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #28a745; /* Change border color on focus */
            outline: none; /* Remove default outline */
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5); /* Add a subtle shadow */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include('../sidebar.php'); ?>

        <div class="content-area" id="content-area">
            <button id="addNewBtn" class="btn btn-success">Add Item</button>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['message_type'] ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            endif; ?>


            <div class="table-container">
                <table id="example" class="display nowrap" style="width:100%">
                    
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Category</th>
                            <th>L</th>
                            <th>T</th>
                            <th>P</th>
                            <th>Credit</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Internal</th>
                            <th>External</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['course_code'] ?></td>
                                <td><?= $row['course_title'] ?></td>
                                <td><?= $row['category'] ?></td>
                                <td><?= $row['l'] ?></td>
                                <td><?= $row['t'] ?></td>
                                <td><?= $row['p'] ?></td>
                                <td><?= $row['credit'] ?></td>
                                <td><?= $row['year'] ?></td>
                                <td><?= $row['semester'] ?></td>
                                <td><?= $row['internal'] ?></td>
                                <td><?= $row['external'] ?></td>
                                <td><?= $row['total'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary editBtn" data-id="<?= $row['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteBtn" data-id="<?= $row['id'] ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add New Modal -->
            <div id="addNewModal" class="modal">
                <div class="modal-content">
                    <span id="closeModal" class="close">&times;</span>
                    <h2>Add New Course</h2>
                    <form method="post" id="addCourseForm">
                        <div class="form-grid">
                                <!-- Row 1 -->
                                <div class="form-group">
                                    <label for="course_code">Course Code</label>
                                    <input type="text" id="course_code" name="course_code" placeholder="Enter course code" required>
                                </div>
                                <div class="form-group">
                                    <label for="course_title">Course Title</label>
                                    <input type="text" id="course_title" name="course_title" placeholder="Enter course title" required>
                                </div>

                                <!-- Row 2 -->
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <input type="text" id="category" name="category" placeholder="Enter category" required>
                                </div>
                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <input type="text" id="year" name="year" placeholder="Enter year" required>
                                </div>

                                <!-- Row 3 -->
                                <div class="form-group">
                                    <label for="semester">Semester</label>
                                    <input type="text" id="semester" name="semester" placeholder="Enter semester" required>
                                </div>
                                <div class="form-group">
                                    <label for="credit">Credit</label>
                                    <input type="number" id="credit" name="credit" readonly>
                                </div>

                                <!-- Row 4 -->
                                <div class="form-group">
                                    <label for="l">L</label>
                                    <input type="number" id="l" name="l" placeholder="Enter L value" required>
                                </div>
                                <div class="form-group">
                                    <label for="t">T</label>
                                    <input type="number" id="t" name="t" placeholder="Enter T value" required>
                                </div>

                                <!-- Row 5 -->
                                <div class="form-group">
                                    <label for="p">P</label>
                                    <input type="number" id="p" name="p" placeholder="Enter P value" required>
                                </div>
                                <div class="form-group">
                                    <label for="internal">Internal</label>
                                    <input type="number" id="internal" name="internal" placeholder="Enter internal marks" required>
                                </div>

                                <!-- Row 6 -->
                                <div class="form-group">
                                    <label for="external">External</label>
                                    <input type="number" id="external" name="external" placeholder="Enter external marks" required>
                                </div>
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="number" id="total" name="total" readonly>
                                </div>
                        </div>
                        <div style="text-align: center; margin-top: 20px;">
                                <button type="submit" name="save" class="btn btn-success formbtn">Save Course</button>
                        </div>                    
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span id="closeEditModal" class="close">&times;</span>
                    <h2>Edit Course</h2>
                    <form method="post" id="editCourseForm">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="edit_course_code">Course Code:</label>
                                <input type="text" id="edit_course_code" name="course_code" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_course_title">Course Title:</label>
                                <input type="text" id="edit_course_title" name="course_title" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_category">Category:</label>
                                <input type="text" id="edit_category" name="category" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_l">L:</label>
                                <input type="number" id="edit_l" name="l" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_t">T:</label>
                                <input type="number" id="edit_t" name="t" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_p">P:</label>
                                <input type="number" id="edit_p" name="p" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_credit">Credit:</label>
                                <input type="number" id="edit_credit" name="credit" readonly>
                            </div>
                            <div class="form-group">
                                <label for="edit_year">Year:</label>
                                <input type="text" id="edit_year" name="year" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_semester">Semester:</label>
                                <input type="text" id="edit_semester" name="semester" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_internal">Internal:</label>
                                <input type="number" id="edit_internal" name="internal" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_external">External:</label>
                                <input type="number" id="edit_external" name="external" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_total">Total:</label>
                                <input type="number" id="edit_total" name="total" readonly>
                            </div>
                        </div>
                        <button type="submit" name="update" class="btn btn-success">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable with advanced features
            $('#example').DataTable({
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                responsive: true, // Enable responsive design
                paging: true, // Enable pagination
                pageLength: 10, // Set number of rows per page
                lengthMenu: [10, 25, 50, 100], // Set page length options
                order: [[0, 'asc']], // Default sorting by the first column
                columnDefs: [
                    { responsivePriority: 1, targets: 0 }, // Make ID column responsive
                    { responsivePriority: 2, targets: 1 }, // Make Course Code column responsive
                    { responsivePriority: 3, targets: 2 }, // Make Course Title column responsive
                    { responsivePriority: 4, targets: -1 } // Make Actions column responsive
                ]
            });

            // Modal handling
            const addModal = $("#addNewModal");
            const editModal = $("#editModal");
            const addBtn = $("#addNewBtn");
            const closeAddModal = $("#closeModal");
            const closeEditModal = $("#closeEditModal");

            // Open Add Modal
            addBtn.on("click", function() {
                addModal.css("display", "flex");
            });

            // Close Add Modal
            closeAddModal.on("click", function() {
                addModal.css("display", "none");
            });

            // Close Edit Modal
            closeEditModal.on("click", function() {
                editModal.css("display", "none");
            });

            // Close modals when clicking outside
            $(window).on("click", function(event) {
                if (event.target === addModal[0]) {
                    addModal.css("display", "none");
                }
                if (event.target === editModal[0]) {
                    editModal.css("display", "none");
                }
            });

            // Real-time calculations for credit and total
            $("#l, #t, #p").on("input", function() {
                const l = parseFloat($("#l").val()) || 0;
                const t = parseFloat($("#t").val()) || 0;
                const p = parseFloat($("#p").val()) || 0;
                $("#credit").val(l + t + p);
            });

            $("#internal, #external").on("input", function() {
                const internal = parseFloat($("#internal").val()) || 0;
                const external = parseFloat($("#external").val()) || 0;
                $("#total").val(internal + external);
            });

            // Edit Button
            $(document).on("click", ".editBtn", function() {
                const id = $(this).data("id");

                // Fetch course details via AJAX
                $.ajax({
                    url: "programme_code.php?action=fetch_course&id=" + id,
                    type: "GET",
                    success: function(response) {
                        const course = JSON.parse(response);
                        $("#edit_id").val(course.id);
                        $("#edit_course_code").val(course.course_code);
                        $("#edit_course_title").val(course.course_title);
                        $("#edit_category").val(course.category);
                        $("#edit_l").val(course.l);
                        $("#edit_t").val(course.t);
                        $("#edit_p").val(course.p);
                        $("#edit_credit").val(course.credit);
                        $("#edit_year").val(course.year);
                        $("#edit_semester").val(course.semester);
                        $("#edit_internal").val(course.internal);
                        $("#edit_external").val(course.external);
                        $("#edit_total").val(course.total);
                        $("#editModal").css("display", "flex");
                    },
                    error: function(xhr) {
                        alert("Error fetching course details.");
                    }
                });
            });

            // Submit Edit Form
            $("#editCourseForm").on("submit", function(e) {
                e.preventDefault(); // Prevent form from submitting normally
                console.log("Update button clicked!"); // Debugging: Check if this message appears in the console

                const formData = $(this).serialize();
                console.log("Form Data:", formData); // Debugging: Check if data is being collected correctly

                $.ajax({
                    url: "programme_code.php",
                    type: "POST",
                    data: formData + "&update=true", // Ensure 'update' is included in POST data
                    success: function(response) {
                        console.log("Server Response:", response); // Debugging: Check server response
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert(result.success);
                                location.reload(); // Reload page after successful update
                            } else if (result.error) {
                                alert(result.error);
                            }
                        } catch (error) {
                            console.error("JSON Parse Error:", error);
                            console.log("Raw Response:", response);
                        }
                    },
                    error: function(xhr) {
                        console.log("AJAX Error:", xhr.responseText);
                        alert("Error updating course.");
                    }
                });
            });

            // Delete Button
            $(document).on("click", ".deleteBtn", function() {
                const id = $(this).data("id");

                if (confirm("Are you sure you want to delete this course?")) {
                    $.ajax({
                        url: "programme_code.php",
                        type: "POST",
                        data: { id: id, delete: true },
                        success: function(response) {
                            const result = JSON.parse(response);
                            if (result.success) {
                                alert(result.success);
                                location.reload();
                            } else if (result.error) {
                                alert(result.error);
                            }
                        },
                        error: function(xhr) {
                            alert("Error deleting course.");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>