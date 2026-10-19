<?php
// task2_xml_to_db/index.php - Read data from students.xml and store into MySQL students table
require_once __DIR__ . '/../config/db.php';
$pdo = getDBConnection();

$msg = '';
$error = '';
$xmlFilePath = __DIR__ . '/students.xml';

// Handle Import Trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_xml'])) {
    if (!file_exists($xmlFilePath)) {
        $error = "XML file 'students.xml' not found at: {$xmlFilePath}";
    } else {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($xmlFilePath);

        if ($xml === false) {
            $error = "Failed to parse XML file: ";
            foreach (libxml_get_errors() as $err) {
                $error .= $err->message . " ";
            }
            libxml_clear_errors();
        } else {
            $count = 0;
            $stmt = $pdo->prepare("
                INSERT INTO `students` (`roll_no`, `name`, `email`, `course`, `marks`)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    `name` = VALUES(`name`),
                    `email` = VALUES(`email`),
                    `course` = VALUES(`course`),
                    `marks` = VALUES(`marks`)
            ");

            foreach ($xml->student as $student) {
                $rollNo = trim((string)$student->roll_no);
                $name = trim((string)$student->name);
                $email = trim((string)$student->email);
                $course = trim((string)$student->course);
                $marks = (int)$student->marks;

                if (!empty($rollNo) && !empty($name)) {
                    $stmt->execute([$rollNo, $name, $email, $course, $marks]);
                    $count++;
                }
            }

            $msg = "Successfully processed {$count} student record(s) from 'students.xml' into the MySQL 'students' table.";
        }
    }
}

// Fetch current records in MySQL students table
$dbStudents = $pdo->query("SELECT * FROM `students` ORDER BY `roll_no` ASC")->fetchAll();
$xmlRawContent = file_exists($xmlFilePath) ? file_get_contents($xmlFilePath) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 2: XML to MySQL Database - PHP Practical</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">📄 Task 2: XML &rarr; MySQL</a>
        <ul class="navbar-nav">
            <li><a class="nav-link" href="../index.php">🏠 Assignment Portal</a></li>
            <li><a class="nav-link" href="../task3_db_to_xml/index.php">Task 3: MySQL &rarr; XML &rarr;</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="padding-top: 30px;">
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Task 2: Read Data from XML and Store in MySQL</h1>
        <p style="color: var(--text-muted);">
            Reads structured student records from <code>students.xml</code> and stores them into the MySQL <code>students</code> table using PHP's SimpleXML and PDO prepared statements.
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
        <form method="POST" action="index.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <button type="submit" name="import_xml" value="1" class="btn btn-primary" style="padding: 10px 22px;">
                📥 Import / Sync 'students.xml' to MySQL
            </button>
            <span style="color: var(--text-muted); font-size: 0.9rem;">
                Target Table: <code>php_assignment_db.students</code> | Source File: <code>students.xml</code>
            </span>
        </form>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- XML Source View -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📄 Source: students.xml</h3>
                <span class="badge badge-primary">XML Format</span>
            </div>
            <pre style="background: #0f172a; color: #38bdf8; padding: 16px; border-radius: 6px; overflow-x: auto; font-size: 0.88rem; line-height: 1.5; max-height: 400px;"><?= htmlspecialchars($xmlRawContent) ?></pre>
        </div>

        <!-- Live MySQL Database Table View -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🗄️ Destination: MySQL students Table (<?= count($dbStudents) ?>)</h3>
                <span class="badge badge-success">Live MySQL Data</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dbStudents)): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 20px;">Table is currently empty. Click "Import" above.</td></tr>
                        <?php else: ?>
                            <?php foreach ($dbStudents as $s): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td><small><?= htmlspecialchars($s['email']) ?></small></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($s['course']) ?></span></td>
                                    <td><strong style="color: var(--primary);"><?= $s['marks'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> PHP Practical Assignment - Task 2: XML to Database</p>
    </div>
</footer>
</body>
</html>
