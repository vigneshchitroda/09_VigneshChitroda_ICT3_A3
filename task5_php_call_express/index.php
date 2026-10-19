<?php
// task5_php_call_express/index.php - Call Express REST API from PHP
$expressApiUrl = 'http://localhost:5000/api/students';
$expressHealthUrl = 'http://localhost:5000/api/health';

$msg = '';
$error = '';

// Helper function for cURL HTTP calls
function callApi($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($data !== null) {
        $jsonData = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'response' => $response,
        'error' => $curlError
    ];
}

// Handle Add Student via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $payload = [
        'roll_no' => trim($_POST['roll_no'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'course' => trim($_POST['course'] ?? 'MCA'),
        'marks' => (int)($_POST['marks'] ?? 0)
    ];

    $res = callApi($expressApiUrl, 'POST', $payload);
    if ($res['code'] === 201) {
        $msg = "Successfully sent POST request to Express REST API! New student created.";
    } else {
        $error = "Failed to create student via Express API (HTTP Code {$res['code']}): " . ($res['error'] ?: $res['response']);
    }
}

// Handle Delete Student via DELETE
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $res = callApi($expressApiUrl . '/' . $delId, 'DELETE');
    if ($res['code'] === 200) {
        $msg = "Successfully sent DELETE request to Express REST API for Student ID {$delId}.";
    } else {
        $error = "Failed to delete student via Express API: " . ($res['error'] ?: $res['response']);
    }
}

// Fetch all students from Express REST API
$apiResult = callApi($expressApiUrl, 'GET');
$isOnline = ($apiResult['code'] === 200);
$students = [];
$rawJson = $apiResult['response'];

if ($isOnline) {
    $decoded = json_decode($rawJson, true);
    if (!empty($decoded['data'])) {
        $students = $decoded['data'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task 5: Call Express REST API in PHP - PHP Practical</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="../index.php">⚡ Task 5: PHP &rarr; Express REST API</a>
        <ul class="navbar-nav">
            <li><a class="nav-link" href="../index.php">🏠 Assignment Portal</a></li>
            <li><a class="nav-link" href="../task4_list_files/index.php">&larr; Task 4</a></li>
            <li><a class="nav-link" href="../task6_express_call_php/php_api/api.php" target="_blank">Task 6: PHP API &rarr;</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="padding-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Task 5: Call Express REST API in PHP</h1>
            <p style="color: var(--text-muted);">
                PHP application invoking an external <strong>Express.js REST API</strong> via cURL (GET, POST, DELETE).
            </p>
        </div>
        <div>
            <?php if ($isOnline): ?>
                <span class="badge badge-success" style="font-size: 0.9rem; padding: 8px 14px;">🟢 Express API Online (Port 5000)</span>
            <?php else: ?>
                <span class="badge badge-danger" style="font-size: 0.9rem; padding: 8px 14px;">🔴 Express API Offline</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$isOnline): ?>
        <div class="card" style="border-left: 4px solid var(--danger); background: #fef2f2;">
            <h3 style="color: #991b1b; margin-bottom: 8px;">Express Server is Not Running on Port 5000</h3>
            <p style="color: #7f1d1d; margin-bottom: 12px; font-size: 0.95rem;">
                To start the Express REST API server, open a terminal in <code>c:\xampp\htdocs\php_assignment\task5_php_call_express\express_api</code> and run:
            </p>
            <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 6px; font-size: 0.9rem;">cd c:\xampp\htdocs\php_assignment\task5_php_call_express\express_api
npm install
node server.js</pre>
            <div style="margin-top: 14px;">
                <a href="index.php" class="btn btn-primary btn-sm">🔄 Check Connection Again</a>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
        <!-- Form to POST to Express API -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">➕ POST to Express API</h3>
            </div>
            <form method="POST" action="index.php">
                <div class="form-group">
                    <label class="form-label" for="roll_no">Roll Number *</label>
                    <input type="text" id="roll_no" name="roll_no" class="form-control" placeholder="106" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="name">Student Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Student Name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="course">Course</label>
                    <input type="text" id="course" name="course" class="form-control" placeholder="MCA" value="MCA">
                </div>
                <div class="form-group">
                    <label class="form-label" for="marks">Marks</label>
                    <input type="number" id="marks" name="marks" class="form-control" placeholder="90" value="90">
                </div>
                <button type="submit" name="add_student" value="1" class="btn btn-primary" style="width: 100%; margin-top: 12px;" <?= !$isOnline ? 'disabled' : '' ?>>
                    🚀 Send POST Request via cURL
                </button>
            </form>
        </div>

        <!-- Express API Data Display -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📦 Data Received from Express API (GET /api/students)</h3>
                <a href="index.php" class="btn btn-secondary btn-sm">🔄 Refresh</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Roll</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Marks</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="7" style="text-align:center; padding: 20px;">No students returned from Express API.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($s['roll_no']) ?></strong></td>
                                    <td>
                                        <?= htmlspecialchars($s['name']) ?>
                                        <?php if (in_array(strtolower($s['name']), ['vignesh', 'sahil', 'kais', 'harsh', 'manan'])): ?>
                                            <span class="badge badge-success" style="font-size: 0.7rem;">Requirement</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($s['email']) ?></small></td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($s['course']) ?></span></td>
                                    <td><strong style="color: var(--primary);"><?= $s['marks'] ?></strong></td>
                                    <td>
                                        <a href="index.php?delete=<?= $s['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete via Express DELETE endpoint?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Raw JSON Preview -->
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <h4 style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 8px;">Raw JSON Response from Express:</h4>
                <pre style="background: #0f172a; color: #38bdf8; padding: 14px; border-radius: 6px; font-size: 0.85rem; overflow-x: auto; max-height: 220px;"><?= htmlspecialchars($rawJson ?: 'No response') ?></pre>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> PHP Practical Assignment - Task 5: Call Express REST API in PHP</p>
    </div>
</footer>
</body>
</html>
