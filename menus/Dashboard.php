<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Initialize variables
$departments = [];
$totalCourses = 0;
$selectedDepartment = $_GET['department'] ?? null;
$selectedCourse = $_GET['course_code'] ?? null;
$courseData = [];
$courseDetails = [];

try {
    // Get unique departments and their course counts
    $deptStmt = $conn->query("
        SELECT department, COUNT(*) as course_count 
        FROM department 
        GROUP BY department
        ORDER BY department ASC
    ");
    
    while ($row = $deptStmt->fetch_assoc()) {
        $departments[$row['department']] = $row['course_count'];
    }

    // Get total courses
    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM department");
    $totalCourses = $totalStmt->fetch_assoc()['total'];

    // Get courses for selected department
    if ($selectedDepartment) {
        $courseStmt = $conn->prepare("
            SELECT course_code, department 
            FROM department 
            WHERE department = ?
            ORDER BY course_code ASC
        ");
        $courseStmt->bind_param("s", $selectedDepartment);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result();
        
        while ($row = $courseResult->fetch_assoc()) {
            $courseData[] = $row;
        }
    }

    // Get details for selected course
    if ($selectedCourse) {
        $detailsStmt = $conn->prepare("
            SELECT * FROM department 
            WHERE course_code = ?
        ");
        $detailsStmt->bind_param("s", $selectedCourse);
        $detailsStmt->execute();
        $detailsResult = $detailsStmt->get_result();
        $courseDetails = $detailsResult->fetch_assoc();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #4a5568;
            --light-bg: #f8f9fa;
        }

        .content-area {
            margin-left: 20.3%;
            padding: 1.5rem;
            background-color: var(--light-bg);
            min-height: 100vh;
            max-width: 1200px;
            margin-right: auto;
        }

        .department-card {
            background: white;
            border-radius: 10px;
            padding: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .department-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }

        .department-card.active {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
        }

        .course-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }

        .course-details {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .course-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .course-row:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .back-btn {
            margin-bottom: 1rem;
        }

        .checkbox-cell {
            width: 40px;
            text-align: center;
        }
        
        .checkbox-lg {
            width: 18px;
            height: 18px;
        }

        .download-btn-container {
            margin: 15px 0;
            text-align: right;
        }
        
        #downloadBtn {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="content-area">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Course Dashboard</h2>
                    <span class="badge bg-primary">
                        <i class="fas fa-book me-1"></i> 
                        <?= $totalCourses ?> Total Courses
                    </span>
                </div>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] === 'error' ? 'danger' : 'info' ?> alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if ($selectedCourse): ?>
                    <a href="?department=<?= urlencode($selectedDepartment) ?>" class="btn btn-primary back-btn">
                        <i class="fas fa-arrow-left me-1"></i> Back to Courses
                    </a>
                    
                    <div class="course-details">
                        <h4 class="text-primary mb-4"><?= htmlspecialchars($selectedCourse) ?> Details</h4>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th width="30%">Course Code</th>
                                        <td><?= htmlspecialchars($courseDetails['course_code'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Department</th>
                                        <td><?= htmlspecialchars($courseDetails['department'] ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Content</th>
                                        <td><?= htmlspecialchars($courseDetails['content'] ?? 'No content available') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif ($selectedDepartment): ?>
                    <div class="course-table">
                        <div class="p-3 bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <?= htmlspecialchars(strtoupper($selectedDepartment)) ?> COURSE CODES
                                <span class="badge bg-light text-primary ms-2">
                                    <?= count($courseData) ?> Courses
                                </span>
                            </h4>
                            <div class="download-btn-container">
                                <button id="downloadBtn" class="btn btn-success">
                                    <i class="fas fa-download me-1"></i> Download Selected PDFs
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($courseData)): ?>
                            <form id="pdfForm" method="post" action="downloadPDF.php">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="checkbox-cell">Select</th>
                                                <th>#</th>
                                                <th>Course Code</th>
                                                <th>Department</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courseData as $index => $course): ?>
                                                <tr class="course-row" onclick="event.stopPropagation();">
                                                    <td class="checkbox-cell">
                                                        <input type="checkbox" class="checkbox-lg course-checkbox" 
                                                               name="course_codes[]" 
                                                               value="<?= htmlspecialchars($course['course_code']) ?>"
                                                               onclick="event.stopPropagation(); updateDownloadButton();">
                                                    </td>
                                                    <td><?= $index + 1 ?></td>
                                                    <td class="fw-bold" 
                                                        onclick="window.location.href='?department=<?= urlencode($selectedDepartment) ?>&course_code=<?= urlencode($course['course_code']) ?>'">
                                                        <?= htmlspecialchars($course['course_code']) ?>
                                                    </td>
                                                    <td onclick="window.location.href='?department=<?= urlencode($selectedDepartment) ?>&course_code=<?= urlencode($course['course_code']) ?>'">
                                                        <?= htmlspecialchars($course['department']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="p-5 text-center">
                                <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                                <h5>No Courses Found</h5>
                                <p class="text-muted">No course codes available for <?= htmlspecialchars($selectedDepartment) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="row g-4 mb-4">
                        <?php foreach ($departments as $dept => $count): ?>
                            <div class="col-md-4">
                                <div class="department-card"
                                     onclick="window.location.href='?department=<?= urlencode($dept) ?>'">
                                    <h5 class="text-primary"><?= htmlspecialchars(strtoupper($dept)) ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-muted">Courses</span>
                                        <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateDownloadButton() {
            const checkboxes = document.querySelectorAll('.course-checkbox:checked');
            const downloadBtn = document.getElementById('downloadBtn');
            downloadBtn.style.display = checkboxes.length > 0 ? 'inline-block' : 'none';
        }

        document.getElementById('downloadBtn').addEventListener('click', function() {
            document.getElementById('pdfForm').submit();
        });

        document.querySelectorAll('.checkbox-lg').forEach(checkbox => {
            checkbox.addEventListener('click', function(e) {
                e.stopPropagation();
                updateDownloadButton();
            });
        });
    </script>
</body>
</html>