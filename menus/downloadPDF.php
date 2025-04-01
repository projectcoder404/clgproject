<?php
session_start();
require_once '../db_connect.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_codes'])) {
    $courseCodes = is_array($_POST['course_codes']) ? $_POST['course_codes'] : explode(',', $_POST['course_codes']);
    $html = '';

    foreach ($courseCodes as $code) {
        $code = trim($code);

        // Fetch course data
        $course = $conn->query("SELECT * FROM courses WHERE course_code = '$code'")->fetch_assoc();

        // Fetch preamble
        $stmt = $conn->prepare("SELECT preamble FROM preamble WHERE course_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $preamble = $result->fetch_assoc();
        $stmt->close();

        // Fetch pre_requisite
        $stmt = $conn->prepare("SELECT pre_requisite FROM pre_requisite WHERE course_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $pre_requisite = $result->fetch_assoc();
        $stmt->close();
        
        // Fetch course outcomes with knowledge levels
        $cos = $conn->query("SELECT * FROM course_outcomes WHERE course_code = '$code'")->fetch_all(MYSQLI_ASSOC);
        
        // Fetch PO mappings
        $mappings = $conn->query("SELECT * FROM mapping_pos WHERE course_code = '$code' ORDER BY co_number")->fetch_all(MYSQLI_ASSOC);
        
        // Fetch PSO mappings
        $psoMappings = $conn->query("SELECT * FROM MappingPSOs WHERE course_code = '$code'")->fetch_assoc();

        // Fetch course content
        $content = $conn->query("SELECT * FROM content WHERE course_code = '$code' ORDER BY unit")->fetch_all(MYSQLI_ASSOC);

        if ($course) {
            // Header
            $html .= '<div style="text-align: center; font-family: Times New Roman, serif;">
                <h2 style="margin: 0; font-size: 18px;">THIAGARAJAR COLLEGE, MADURAI - 9.</h2>
                <h3 style="margin: 0; font-size: 16px;">(Re-Accredited with "A++" Grade by NAAC)</h3>
                <h3 style="margin: 0; font-size: 16px;">DEPARTMENT OF COMPUTER SCIENCE</h3>
                <p style="margin: 0; font-size: 14px; font-style: italic;">
                    (For those joined M.Sc. Computer Science on or after June 2020)
                </p>
              </div><br>';

            $html .= '<h3 style="text-align: center; font-size: 16px; font-weight: bold;">'.$course['course_code'].'</h3>';
    
            $html .= '<table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                      <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
                          <th>Course Code</th>
                          <th>Course Title</th>
                          <th>Category</th>
                          <th>Lecture</th>
                          <th>Tutorial</th>
                          <th>Practical</th>
                          <th>Credit</th>
                      </tr>
                      <tr>
                          <td>'.$course['course_code'].'</td>
                          <td>'.$course['course_title'].'</td>
                          <td>'.$course['category'].'</td>
                          <td>'.$course['l'].'</td>
                          <td>'.$course['t'].'</td>
                          <td>'.$course['p'].'</td>
                          <td>'.$course['credit'].'</td>
                      </tr>
                    </table><br>';
      
            // Marks Table
            $html .= '<table border="1" cellspacing="0" cellpadding="5" style="width: 100%; border-collapse: collapse;">
                      <tr>
                          <th>Year</th>
                          <th>Semester</th>
                          <th>Internal Marks (CIA)</th>
                          <th>External Marks (ESE)</th>
                          <th>Total Marks</th>
                      </tr>
                      <tr>
                          <td>'.$course['year'].'</td>
                          <td>'.$course['semester'].'</td>
                          <td>'.$course['internal'].'</td>
                          <td>'.$course['external'].'</td>
                          <td>'.$course['total'].'</td>
                      </tr>
                    </table><br>';
  
            // Preamble
            $preamble_text = $preamble['preamble'] ?? "No preamble available.";
            $html .= "<h4>Preamble</h4><p>{$preamble_text}</p>";
  
            // Pre-requisite
            $pre_requisite_text = $pre_requisite['pre_requisite'] ?? "No pre-requisite available.";
            $html .= "<h4>Pre-requisite</h4><p>{$pre_requisite_text}</p>";

            // Course Outcomes Table
            $html .= '<h4 style="text-align: center;">Course Outcomes (COs)</h4>
            <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr style="background-color: #f2f2f2;">
                    <th style="width: 15%; text-align: center;">CO</th>
                    <th style="width: 60%; text-align: center;">Course Outcome</th>
                    <th style="width: 25%; text-align: center;">Knowledge Level (RBT)</th>
                </tr>';

            foreach ($cos as $index => $co) {
                $html .= '<tr>
                    <td style="text-align: center;"><b>CO'.($index+1).'</b></td>
                    <td>'.$co['course_outcomes'].'</td>
                    <td style="text-align: center;">'.$co['knowledge_level'].'</td>
                </tr>';
            }
            
            $html .= '</table>
            <p style="margin: 5px 0; font-family: Times New Roman;">
                K1–Remember; K2–Understand; K3–Apply; K4–Analyze; K5–Evaluate; K6–Create
            </p><br>';

            // CO-PO Mapping Table
            $html .= '<h4 style="text-align: center; margin: 20px 0;">CO-PO Mapping (Course Articulation Matrix)</h4>
            <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f2f2f2;">
                    <th rowspan="2" style="text-align: center;">COs</th>
                    <th colspan="7" style="text-align: center;">POs</th>
                    <th rowspan="2" style="text-align: center;">Total</th>
                </tr>
                <tr style="background-color: #f2f2f2;">
                    <th style="text-align: center;">PO1</th>
                    <th style="text-align: center;">PO2</th>
                    <th style="text-align: center;">PO3</th>
                    <th style="text-align: center;">PO4</th>
                    <th style="text-align: center;">PO5</th>
                    <th style="text-align: center;">PO6</th>
                    <th style="text-align: center;">PO7</th>
                </tr>';

            $totalPOs = array_fill(0, 7, 0);
            $maxScore = count($mappings) * 3;

            foreach ($mappings as $mapping) {
                $html .= '<tr style="text-align: center;">';
                $html .= '<td>CO'.$mapping['co_number'].'</td>';
                
                for ($i = 1; $i <= 7; $i++) {
                    $value = $mapping["po$i"] ?? '-';
                    $html .= '<td>'.$value.'</td>';
                    if (is_numeric($value)) {
                        $totalPOs[$i-1] += $value;
                    }
                }
                
                $html .= '<td>'.array_sum(array_slice($mapping, 2, 7)).'</td>';
                $html .= '</tr>';
            }

            // Total Contribution Row
            $html .= '<tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
                <td>Total Contribution</td>';
            foreach ($totalPOs as $total) {
                $html .= '<td>'.$total.'</td>';
            }
            $html .= '<td>'.array_sum($totalPOs).'</td></tr>';

            // Weighted Percentage Row
            $html .= '<tr style="text-align: center; font-weight: bold;">
                <td>Weighted Percentage</td>';
            foreach ($totalPOs as $total) {
                $percentage = ($total / $maxScore) * 100;
                $html .= '<td>'.number_format($percentage, 2).'%</td>';
            }
            $html .= '<td></td></tr>';

            $html .= '</table>
            <p style="margin: 10px 0; font-family: Times New Roman;">
                (3-Strong, 2-Medium, 1-Low, -No Correlation)
            </p><br>';

            // Mapping PSOs Table
            $psoMappings = $conn->query("SELECT * FROM MappingPSOs WHERE course_code = '$code' ORDER BY co_number")->fetch_all(MYSQLI_ASSOC);

            if (!empty($psoMappings)) {
                $html .= '<h4 style="text-align: center; margin: 20px 0;">Mapping PSOs</h4>
                <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; border-collapse: collapse;">
                    <tr style="background-color: #f2f2f2;">
                        <th style="text-align: center; width: 15%;">COs</th>
                        <th style="text-align: center;">PSO1</th>
                        <th style="text-align: center;">PSO2</th>
                        <th style="text-align: center;">PSO3</th>
                        <th style="text-align: center;">PSO4</th>
                        <th style="text-align: center;">PSO5</th>
                    </tr>';

                foreach ($psoMappings as $index => $mapping) {
                    $html .= '<tr style="text-align: center;">
                        <td>CO' . ($index + 1) . '</td>
                        <td>' . htmlspecialchars($mapping['po1']) . '</td>
                        <td>' . htmlspecialchars($mapping['po2']) . '</td>
                        <td>' . htmlspecialchars($mapping['po3']) . '</td>
                        <td>' . htmlspecialchars($mapping['po4']) . '</td>
                        <td>' . htmlspecialchars($mapping['po5']) . '</td>
                    </tr>';
                }

                $html .= '</table><br>';
            }

            // Course Content
            if (!empty($content)) {
                $html .= '<h4 style="margin-top: 20px;">Course Content</h4>';
                foreach ($content as $unit) {
                    $html .= '<div style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                    <span>UNIT '.$unit['unit'].'</span>
                                    <span>'.$unit['hour'].' Hours</span>
                                </div>
                                <div style="margin-top: 5px; margin-left: 10px;">
                                    '.$unit['content'].'
                                </div>
                              </div>';
                }
            }

            // Text Books
            $textbooks = $conn->query("SELECT text_book FROM text_book WHERE course_code = '$code'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($textbooks)) {
                $html .= '<h4 style="margin-top: 20px;">Text Books</h4>';
                $html .= '<ol style="margin-left: 20px; padding-left: 0;">';
                
                foreach ($textbooks as $book) {
                    $html .= '<li style="margin-bottom: 8px;">' . htmlspecialchars($book['text_book']) . '</li>';
                }
                
                $html .= '</ol><br>';
            }

            // Chapter References
            $chapters = $conn->query("SELECT unit, chapter, book FROM chapter WHERE course_code = '$code' ORDER BY unit")->fetch_all(MYSQLI_ASSOC);
            if (!empty($chapters)) {
                $html .= '<h4 style="margin-top: 20px;">Chapter References</h4>';
                
                // Group chapters by unit and book
                $unitGroups = [];
                foreach ($chapters as $chapter) {
                    $unit = $chapter['unit'];
                    $book = $chapter['book'];
                    if (!isset($unitGroups[$unit])) {
                        $unitGroups[$unit] = [];
                    }
                    if (!isset($unitGroups[$unit][$book])) {
                        $unitGroups[$unit][$book] = [];
                    }
                    $unitGroups[$unit][$book][] = $chapter['chapter'];
                }
                
                // Display the grouped chapters
                foreach ($unitGroups as $unit => $books) {
                    foreach ($books as $book => $chaptersList) {
                        $html .= '<div style="margin-bottom: 8px;">';
                        $html .= '<span style="font-weight: bold;">Unit ' . $unit . ' : </span>';
                        $html .= implode(', ', $chaptersList);
                        $html .= ' (<span style="font-style: italic;">' . $book . '</span>)';
                        $html .= '</div>';
                    }
                }
                
                $html .= '<br>';
            }

            // Reference Books
            $referenceBooks = $conn->query("SELECT reference_book FROM reference_book WHERE course_code = '$code'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($referenceBooks)) {
                $html .= '<h4 style="margin-top: 20px;">Reference Books</h4>';
                $html .= '<ol style="margin-left: 20px; padding-left: 0;">';
                
                foreach ($referenceBooks as $book) {
                    $html .= '<li style="margin-bottom: 8px;">' . htmlspecialchars($book['reference_book']) . '</li>';
                }
                
                $html .= '</ol><br>';
            }

            // Web Resources
            $webResources = $conn->query("SELECT web_resources FROM web_resources WHERE course_code = '$code'")->fetch_all(MYSQLI_ASSOC);
            if (!empty($webResources)) {
                $html .= '<h4 style="margin-top: 20px;">Web Resources</h4>';
                $html .= '<ul style="margin-left: 20px; padding-left: 0; list-style-type: none;">';
                
                foreach ($webResources as $resource) {
                    $url = $resource['web_resources'];
                    if (preg_match('/https?:\/\//i', $url)) {
                        $displayText = parse_url($url, PHP_URL_HOST) ?: $url;
                        $html .= '<li style="margin-bottom: 8px;">';
                        $html .= '<a href="' . htmlspecialchars($url) . '" style="color: #0066cc; text-decoration: none;">';
                        $html .= htmlspecialchars($displayText);
                        $html .= '</a>';
                        $html .= '</li>';
                    } else {
                        $html .= '<li style="margin-bottom: 8px;">' . htmlspecialchars($url) . '</li>';
                    }
                }
                
                $html .= '</ul><br>';
            }

            // Course Designer
            $designer = $conn->query("SELECT course_designer FROM course_designer WHERE course_code = '$code' LIMIT 1")->fetch_assoc();
            if (!empty($designer)) {
                $html .= '<h4 style="margin-top: 20px;">Course Designer</h4>';
                $html .= '<div style="margin-left: 20px; margin-bottom: 20px;">';
                $html .= '<p style="margin-top: -5px">,<b>' . htmlspecialchars($designer['course_designer']) . '</b></p>';
                $html .= '</div>';
            }

            // Add page break between courses if multiple are selected
            if (count($courseCodes) > 1) {
                $html .= '<div style="page-break-after: always;"></div>';
            }
        }
    }

    if (!empty($html)) {
        $options = new Options();
        $options->set('defaultFont', 'Times New Roman');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        ob_end_clean();
        $dompdf->stream("course-report.pdf", ["Attachment" => true]);
        exit;
    } else {
        $_SESSION['message'] = "No valid courses selected for PDF generation.";
        $_SESSION['message_type'] = "error";
        header("Location: Dashboard.php");
        exit;
    }
} else {
    header("Location: Dashboard.php");
    exit;
}
?>