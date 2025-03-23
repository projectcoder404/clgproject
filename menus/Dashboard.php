<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
session_start();

// Initialize variables
$departments = [];
$totalCourses = 0;
$selectedDepartment = $_GET['department'] ?? null;
$courseData = [];
$departmentNames = [];
$departmentCounts = [];

try {
    // Get department statistics
    $stmt = $conn->prepare("
        SELECT department, COUNT(*) AS course_count 
        FROM department
        GROUP BY department
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $departments[$row['department']] = $row['course_count'];
        $departmentNames[] = $row['department'];
        $departmentCounts[] = $row['course_count'];
    }

    // Get total number of courses
    $totalStmt = $conn->query("SELECT COUNT(*) AS total FROM department");
    $totalCourses = $totalStmt->fetch_assoc()['total'];

    // Get course data if department selected
    if ($selectedDepartment) {
        $courseStmt = $conn->prepare("
            SELECT * FROM courses 
            WHERE department = ?
        ");
        $courseStmt->bind_param("s", $selectedDepartment);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result();
        
        while ($row = $courseResult->fetch_assoc()) {
            $courseData[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
}

// Handle PDF download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_pdf'])) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="courses.pdf"');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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

        .content-area {
            margin-left: 20.3%;
            margin-right: 15%;
            padding: 2rem;
            transition: var(--transition);
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            background: var(--background-color);
            border: 1px solid #e0e0e0;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: var(--transition);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .card p {
            color: var(--text-color);
            font-size: 1.1rem;
            margin: 0;
        }

        .statistics {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            margin: 2rem 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            margin: 2rem 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            margin: auto;
        }

        table {
            width: 100%;
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            white-space: nowrap;
        }

        table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        .btn-action {
            transition: var(--transition) !important;
            transform: scale(1);
        }

        .btn-action:hover {
            transform: scale(1.05);
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
                <h2 class="mb-4">Course Dashboard</h2>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard-cards">
                    <?php foreach ($departments as $department => $count): ?>
                        <div class="card" onclick="location.href='?department=<?= urlencode($department) ?>'">
                            <h5><?= htmlspecialchars($department) ?></h5>
                            <hr>
                            <p><?= $count ?> Courses</p>
                            <button class="btn btn-primary btn-action">View Details</button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="statistics">
                    <h4>Total Courses: <?= $totalCourses ?></h4>
                </div>

                <?php if (!$selectedDepartment): ?>
                    <div class="chart-container">
                        <canvas id="courseChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <h4 class="mb-3">Courses in <?= htmlspecialchars($selectedDepartment) ?></h4>
                        <?php if (!empty($courseData)): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Category</th>
                                        <th>Year</th>
                                        <th>Semester</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courseData as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['course_code']) ?></td>
                                            <td><?= htmlspecialchars($course['course_title']) ?></td>
                                            <td><?= htmlspecialchars($course['category']) ?></td>
                                            <td><?= htmlspecialchars($course['year']) ?></td>
                                            <td><?= htmlspecialchars($course['semester']) ?></td>
                                            <td><?= htmlspecialchars($course['total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <form method="POST">
                                <button type="submit" name="download_pdf" class="btn btn-primary btn-action mt-3">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">No courses found for this department.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        <?php if (!$selectedDepartment): ?>
            const ctx = document.getElementById('courseChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($departmentNames) ?>,
                    datasets: [{
                        label: 'Courses per Department',
                        data: <?= json_encode($departmentCounts) ?>,
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        <?php else: ?>
            // Initialize DataTable
            $('table').DataTable({
                dom: '<"top"<"d-flex justify-content-between align-items-center"fB>>rt<"bottom"lip>',
                buttons: ['copy', 'csv', 'excel', 'pdf'],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search courses..."
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>