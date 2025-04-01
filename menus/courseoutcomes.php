<?php
include("../db_connect.php");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save new course outcomes
    if (isset($_POST['save_course_outcomes'])) {
        $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
        
        foreach ($_POST['course_outcomes'] as $index => $co) {
            $co_clean = mysqli_real_escape_string($conn, $co);
            $levels = isset($_POST['knowledge_levels'][$index]) ? 
                implode(',', $_POST['knowledge_levels'][$index]) : '';
            $levels_clean = mysqli_real_escape_string($conn, $levels);
            
            $stmt = $conn->prepare("INSERT INTO course_outcomes (course_code, course_outcomes, knowledge_level) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $course_code, $co_clean, $levels_clean);
            $stmt->execute();
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Delete individual CO
    if (isset($_POST['delete_co'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM course_outcomes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Update existing CO
    if (isset($_POST['update_co'])) {
        $id = (int)$_POST['id'];
        $outcome = mysqli_real_escape_string($conn, $_POST['outcome']);
        $knowledge_level = isset($_POST['knowledge_level']) ? 
            implode(',', $_POST['knowledge_level']) : '';
        $knowledge_level = mysqli_real_escape_string($conn, $knowledge_level);
        $stmt = $conn->prepare("UPDATE course_outcomes SET course_outcomes = ?, knowledge_level = ? WHERE id = ?");
        $stmt->bind_param("ssi", $outcome, $knowledge_level, $id);
        $stmt->execute();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all outcomes ordered by latest first
$result = $conn->query("SELECT * FROM course_outcomes ORDER BY id DESC");
$grouped_outcomes = [];
while ($row = $result->fetch_assoc()) {
    $course_code = $row['course_code'];
    if (!isset($grouped_outcomes[$course_code])) {
        $grouped_outcomes[$course_code] = [];
    }
    array_unshift($grouped_outcomes[$course_code], $row);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Outcomes</title>
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --text-color: #2b2d42;
            --background-color: #f8f9fa;
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
        }

        .add-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .search-container {
            margin-bottom: 1rem;
        }
        
        #searchInput {
            padding: 0.8rem;
            width: 300px;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            transition: all 0.3s ease;
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
            width: 500px;
            max-width: 95%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .co-input-group {
            margin-bottom: 1rem;
        }

        .co-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            font-family: inherit;
        }

        .course-group {
            display: table-row-group;
        }

        .knowledge-level {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }

        .knowledge-level label {
            display: inline-block;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <?php include('../sidebar.php'); ?>
    <div class="content-area">
        
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Search by course code...">
        </div>
        <button class="add-btn" onclick="openModal()">Add New Course</button>
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Outcomes</th>
                    <th>Knowledge Level (RBT)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="courseTableBody">
    <?php foreach ($grouped_outcomes as $course_code => $outcomes): ?>
        <tbody class="course-group" data-course-code="<?= htmlspecialchars($course_code) ?>">
            <?php foreach ($outcomes as $index => $outcome): ?>
                <tr>
                    <?php if ($index === 0): ?>
                        <td rowspan="<?= count($outcomes) ?>"><?= htmlspecialchars($course_code) ?></td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($outcome['course_outcomes']) ?></td>
                    <td><?= htmlspecialchars($outcome['knowledge_level']) ?></td>
                    <td>
                        <button class="btn btn-primary" onclick="openEditModal(
                            '<?= $outcome['id'] ?>',
                            '<?= htmlspecialchars($outcome['course_outcomes']) ?>',
                            '<?= htmlspecialchars($outcome['knowledge_level']) ?>'
                        )">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $outcome['id'] ?>">
                            <button type="submit" name="delete_co" class="btn btn-danger"
                                onclick="return confirm('Delete this outcome?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    <?php endforeach; ?>
</tbody>
        </table>

        <!-- Add Course Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <h2>Add Course Outcomes</h2>
                <form method="POST">
                    <div class="co-input-group">
                        <label>Course Code:</label>
                        <input type="text" name="course_code" required>
                    </div>

                    <div id="outcomes-container">
                        <div class="co-input-group">
                            <label>CO1:</label>
                            <textarea name="course_outcomes[]" required></textarea>
                            <div class="knowledge-level">
                                <label>Knowledge Level (RBT):</label>
                                <?php for ($i=1; $i<=6; $i++): ?>
                                    <label><input type="checkbox" name="knowledge_levels[0][]" value="K<?= $i ?>"> K<?= $i ?></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="button" onclick="addCO()" class="btn btn-primary">Add CO</button>
                        <button type="submit" name="save_course_outcomes" class="btn btn-primary">Save</button>
                        <button type="button" onclick="closeModal()" class="btn btn-danger">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit CO Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h2>Edit Course Outcome</h2>
                <form method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="co-input-group">
                        <label>Course Outcome:</label>
                        <textarea id="edit_outcome" name="outcome" required></textarea>
                    </div>
                    <div class="co-input-group">
                        <label>Knowledge Level (RBT):</label>
                        <?php for ($i=1; $i<=6; $i++): ?>
                            <label><input type="checkbox" name="knowledge_level[]" value="K<?= $i ?>" class="k-level-checkbox"> K<?= $i ?></label>
                        <?php endfor; ?>
                    </div>
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="submit" name="update_co" class="btn btn-primary">Update</button>
                        <button type="button" onclick="closeEditModal()" class="btn btn-danger">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let coCount = 1;
            let coIndex = 0;
            const maxCO = 6;

            function openModal() {
                document.getElementById('addModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('addModal').style.display = 'none';
                coCount = 1;
                coIndex = 0;
                document.getElementById('outcomes-container').innerHTML = `
                    <div class="co-input-group">
                        <label>CO1:</label>
                        <textarea name="course_outcomes[]" required></textarea>
                        <div class="knowledge-level">
                            <label>Knowledge Level (RBT):</label>
                            <?php for ($i=1; $i<=6; $i++): ?>
                                <label><input type="checkbox" name="knowledge_levels[0][]" value="K<?= $i ?>"> K<?= $i ?></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                `;
            }

            function openEditModal(id, outcome, knowledgeLevel) {
                document.getElementById('editModal').style.display = 'flex';
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_outcome').value = outcome;
                
                // Clear all checkboxes first
                document.querySelectorAll('.k-level-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Check the appropriate checkboxes
                if (knowledgeLevel) {
                    const levels = knowledgeLevel.split(',');
                    document.querySelectorAll('.k-level-checkbox').forEach(checkbox => {
                        if (levels.includes(checkbox.value)) {
                            checkbox.checked = true;
                        }
                    });
                }
            }

            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }

            function addCO() {
                if (coCount < maxCO) {
                    coCount++;
                    coIndex++;
                    const container = document.getElementById('outcomes-container');
                    const newCO = document.createElement('div');
                    newCO.className = 'co-input-group';
                    newCO.innerHTML = `
                        <label>CO${coCount}:</label>
                        <textarea name="course_outcomes[]" required></textarea>
                        <div class="knowledge-level">
                            <label>Knowledge Level (RBT):</label>
                            <?php for ($i=1; $i<=6; $i++): ?>
                                <label><input type="checkbox" name="knowledge_levels[${coIndex}][]" value="K<?= $i ?>"> K<?= $i ?></label>
                            <?php endfor; ?>
                        </div>
                    `;
                    container.appendChild(newCO);
                } else {
                    alert('Maximum of 6 COs allowed');
                }
            }

            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function() {
                const searchTerm = this.value.trim().toUpperCase();
                const groups = document.querySelectorAll('.course-group');
                
                groups.forEach(group => {
                    const courseCode = group.getAttribute('data-course-code').toUpperCase();
                    if (courseCode.includes(searchTerm)) {
                        group.style.display = '';
                    } else {
                        group.style.display = 'none';
                    }
                });
            });

            window.onclick = function(event) {
                const addModal = document.getElementById('addModal');
                const editModal = document.getElementById('editModal');
                if (event.target === addModal) closeModal();
                if (event.target === editModal) closeEditModal();
            }
        </script>
    </div>
</body>
</html>