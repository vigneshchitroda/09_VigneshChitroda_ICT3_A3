<?php
// task6_express_call_php/php_api/api.php - PHP REST API Server
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

$endpoint = $_GET['endpoint'] ?? 'students';
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if ($endpoint === 'students') {
            $stmt = $pdo->query("SELECT * FROM `students` ORDER BY `roll_no` ASC");
            $students = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'source' => 'PHP REST API (MySQL php_assignment_db.students)',
                'count' => count($students),
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $students
            ], JSON_PRETTY_PRINT);
            exit;
        } elseif ($endpoint === 'products') {
            $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM `products` p JOIN `categories` c ON p.category_id = c.id ORDER BY p.id ASC");
            $products = $stmt->fetchAll();

            echo json_encode([
                'status' => 'success',
                'source' => 'PHP REST API (MySQL php_assignment_db.products)',
                'count' => count($products),
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $products
            ], JSON_PRETTY_PRINT);
            exit;
        } elseif ($endpoint === 'status') {
            echo json_encode([
                'status' => 'ONLINE',
                'server' => 'PHP REST API',
                'php_version' => PHP_VERSION,
                'timestamp' => date('Y-m-d H:i:s')
            ], JSON_PRETTY_PRINT);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => "Unknown endpoint: {$endpoint}"]);
            exit;
        }
    } elseif ($method === 'POST') {
        // Read JSON input
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!$input) {
            $input = $_POST;
        }

        $roll = trim($input['roll_no'] ?? '');
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $course = trim($input['course'] ?? 'MCA');
        $marks = (int)($input['marks'] ?? 0);

        if (empty($roll) || empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'roll_no and name are required fields']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO `students` (`roll_no`, `name`, `email`, `course`, `marks`)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `email` = VALUES(`email`), `course` = VALUES(`course`), `marks` = VALUES(`marks`)
        ");
        $stmt->execute([$roll, $name, $email, $course, $marks]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Record successfully saved into MySQL via PHP REST API',
            'data' => [
                'roll_no' => $roll,
                'name' => $name,
                'email' => $email,
                'course' => $course,
                'marks' => $marks
            ]
        ], JSON_PRETTY_PRINT);
        exit;
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
