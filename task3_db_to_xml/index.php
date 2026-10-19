<?php
// task3_db_to_xml/index.php - Read data from students table of MySQL and store them in students.xml file
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$msg = '';
$error = '';
$exportFile = __DIR__ . '/students_export.xml';

// Handle Add Custom Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $roll = trim($_POST['roll_no'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? 'MCA');
    $marks = (int)($_POST['marks'] ?? 80);

    if (!empty($roll) && !empty($name)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `students` (`roll_no`, `name`, `email`, `course`, `marks`)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `email` = VALUES(`email`), `course` = VALUES(`course`), `marks` = VALUES(`marks`)
            ");
            $stmt->execute([$roll, $name, $email, $course, $marks]);
            $msg = "Student '{$name}' added/updated in MySQL database.";
        } catch (PDOException $e) {
            $error = "Error adding student: " . $e->getMessage();
        }
    }
}

// Handle Export to XML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_xml'])) {
    try {
        $stmt = $pdo->query("SELECT * FROM `students` ORDER BY `roll_no` ASC");
        $students = $stmt->fetchAll();

        // Create DOMDocument with pretty printing
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('students');
        $dom->appendChild($root);

        foreach ($students as $row) {
            $student = $dom->createElement('student');

            $student->appendChild($dom->createElement('roll_no', htmlspecialchars($row['roll_no'])));
            $student->appendChild($dom->createElement('name', htmlspecialchars($row['name'])));
            $student->appendChild($dom->createElement('email', htmlspecialchars($row['email'])));
            $student->appendChild($dom->createElement('course', htmlspecialchars($row['course'])));
            $student->appendChild($dom->createElement('marks', (string)$row['marks']));
            $student->appendChild($dom->createElement('created_at', htmlspecialchars($row['created_at'])));

            $root->appendChild($student);
        }

        $dom->save($exportFile);
        $msg = "Successfully exported " . count($students) . " record(s) from MySQL table into 'students_export.xml'!";
    } catch (Exception $e) {
        $error = "Export failed: " . $e->getMessage();
    }
}

// Handle Direct Download
if (isset($_GET['download']) && file_exists($exportFile)) {
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="students.xml"');
    header('Content-Length: ' . filesize($exportFile));
    readfile($exportFile);
    exit;
}

// Fetch current records from MySQL
$dbStudents = $pdo->query("SELECT * FROM `students` ORDER BY `roll_no` ASC")->fetchAll();
$exportedXmlContent = file_exists($exportFile) ? file_get_contents($exportFile) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 3: MySQL to XML File - PHP Practical</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">💾 Task 3: MySQL &rarr; XML</a>
        <ul class="navbar-nav">
            <li><a class="nav-link" href="../index.php">🏠 Assignment Portal</a></li>
            <li><a class="nav-link" href="../task2_xml_to_db/index.php">&larr; Task 2: XML &rarr; MySQL</a></li>
            <li><a class="nav-link" href="../task4_list_files/index.php">Task 4: List Files &rarr;</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="padding-top: 30px;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Task 3: Read from MySQL and Store in XML</h1>
        <p style="color: var(--text-muted);">
            Fetches all records from the MySQL <code>students</code> table (including Vignesh, Sahil, Kais, Harsh, Manan) and creates a formatted <code>students.xml</code> document using PHP DOMDocument.
        </p>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Action Bar -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; gap: 14px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
            <form method="POST" action="index.php" style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" name="export_xml" value="1" class="btn btn-primary" style="padding: 10px 22px;">
                    ⚡ Export MySQL students &rarr; XML File
                </button>
            </form>

            <?php if (file_exists($exportFile)): ?>
                <a href="index.php?download=1" class="btn btn-success">
                    📥 Download Generated students.xml (<?= filesize($exportFile) ?> bytes)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- MySQL Source Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🗄️ MySQL Database Records (<?= count($dbStudents) ?>)</h3>
                <span class="badge badge-primary">MySQL Table</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Roll</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dbStudents)): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 20px;">No students found in MySQL.</td></tr>
                        <?php else: ?>
                            <?php foreach ($dbStudents as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($s['name']) ?>
                                        <?php if (in_array(strtolower($s['name']), ['vignesh', 'sahil', 'kais', 'harsh', 'manan'])): ?>
                                            <span class="badge badge-success" style="font-size:0.7rem;">Req</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($s['email']) ?></small></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($s['course']) ?></span></td>
                                    <td><strong style="color: var(--primary);"><?= $s['marks'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add new record form -->
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <h4 style="margin-bottom: 12px; font-size: 1rem; color: var(--dark);">➕ Add Another Student</h4>
                <form method="POST" action="index.php">
                    <div style="display: grid; grid-template-columns: 80px 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                        <input type="text" name="roll_no" class="form-control" placeholder="Roll No" required>
                        <input type="text" name="name" class="form-control" placeholder="Student Name" required>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 100px auto; gap: 8px;">
                        <input type="text" name="course" class="form-control" placeholder="Course (e.g. MCA)" value="MCA">
                        <input type="number" name="marks" class="form-control" placeholder="Marks" value="90">
                        <button type="submit" name="add_student" value="1" class="btn btn-secondary">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Generated XML View -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📄 Generated XML Output</h3>
                <span class="badge badge-success">students_export.xml</span>
            </div>

            <?php if (empty($exportedXmlContent)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Click <strong>"Export MySQL students &rarr; XML File"</strong> above to generate the XML document.
                </div>
            <?php else: ?>
                <pre style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 6px; overflow-x: auto; font-size: 0.88rem; line-height: 1.5; max-height: 480px;"><?= htmlspecialchars($exportedXmlContent) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> PHP Practical Assignment - Task 3: MySQL to XML</p>
    </div>
</footer>
</body>
</html>
