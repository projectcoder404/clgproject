<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../db_connect.php';
require_once '../vendor/autoload.php'; // Include Composer autoload
session_start();

// PDF Generation Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_codes'])) {
    $courseCodes = explode(',', $_POST['course_codes']);
    $coursesData = [];

    foreach ($courseCodes as $code) {
        $code = trim($code);
        $courseData = [];

        // Fetch basic course info
        $stmt = $conn->prepare("SELECT * FROM courses WHERE course_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $courseData['basic'] = $stmt->get_result()->fetch_assoc();

        // Fetch related data from other tables
        $tables = [
            'preamble', 'pre_requisite', 'course_outcomes', 'mapping_pos',
            'MappingPSOs', 'bloomy', 'content', 'chapter', 'text_book',
            'reference_book', 'web_resources', 'course_designer', 'department'
        ];

        foreach ($tables as $table) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE course_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $courseData[$table] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $coursesData[$code] = $courseData;
    }

    // Generate PDF
    $html = '<h1>Course Report</h1>';
    foreach ($coursesData as $code => $data) {
        $html .= "<h2>Course Code: $code</h2>";
        
        // Basic Information
        $html .= '<h3>Basic Information</h3>';
        $html .= '<table border="1"><tr>';
        foreach ($data['basic'] as $key => $value) {
            $html .= "<th>$key</th>";
        }
        $html .= '</tr><tr>';
        foreach ($data['basic'] as $value) {
            $html .= "<td>$value</td>";
        }
        $html .= '</tr></table>';

        // Other Sections
        foreach ($tables as $table) {
            if (!empty($data[$table])) {
                $html .= "<h3>" . ucfirst(str_replace('_', ' ', $table)) . "</h3>";
                $html .= '<table border="1"><tr>';
                foreach ($data[$table][0] as $key => $value) {
                    $html .= "<th>$key</th>";
                }
                $html .= '</tr>';
                foreach ($data[$table] as $row) {
                    $html .= '<tr>';
                    foreach ($row as $value) {
                        $html .= "<td>$value</td>";
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
            }
        }
    }

    // Configure DomPDF
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("course-report.pdf");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <style>
        /* Add download section styles */
        #downloadSection {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #course-code {
            width: 400px;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #download-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }
        #download-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include('../sidebar.php'); ?>

            <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4 content-area">
                <div id="downloadSection">
                    <label for="course-code">Enter Course Code(s):</label>
                    <input type="text" id="course-code" 
                           placeholder="Type course code(s) separated by commas"
                           value="<?= isset($_POST['course_codes']) ? htmlspecialchars($_POST['course_codes']) : '' ?>">
                    <button id="download-btn" class="btn btn-primary" disabled>Generate PDF</button>
                </div>

                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-info alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php unset($_SESSION['message']); ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Keep existing scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const input = document.getElementById("course-code");
            const button = document.getElementById("download-btn");
            
            button.disabled = !input.value.trim();
            
            input.addEventListener("input", () => {
                button.disabled = !input.value.trim();
            });

            button.addEventListener("click", () => {
                const courseCodes = input.value.trim();
                if (courseCodes) {
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "";
                    const hiddenField = document.createElement("input");
                    hiddenField.type = "hidden";
                    hiddenField.name = "course_codes";
                    hiddenField.value = courseCodes;
                    form.appendChild(hiddenField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>