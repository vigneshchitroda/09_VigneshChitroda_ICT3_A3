<?php
// index.php - Unified Practical Assignment Portal & Dashboard
require_once __DIR__ . '/config/db.php';

$dbStatus = 'UNKNOWN';
$studentCount = 0;
$productCount = 0;
$categoryCount = 0;
$gdStatus = extension_loaded('gd') ? 'Active' : 'Disabled';

try {
    $pdo = getDBConnection();
    $dbStatus = 'Connected';
    $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM `students`")->fetchColumn();
    $productCount = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
    $categoryCount = (int)$pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
} catch (Exception $e) {
    $dbStatus = 'Error: ' . $e->getMessage();
}

$pageTitle = "PHP Practical Assignment - Master Portal";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            🎓 <span>PHP Practical Assignment</span>
        </a>
        <ul class="navbar-nav">
            <li><a class="nav-link" href="shopping_cart/user/index.php" target="_blank">🛒 Storefront</a></li>
            <li><a class="nav-link" href="shopping_cart/admin/dashboard.php" target="_blank">⚙️ Admin</a></li>
            <li><a class="nav-link btn btn-danger btn-sm" style="color:#fff;" href="09_chitroda_vignesh_ICT3.pdf" target="_blank">📄 Download PDF</a></li>
            <li><a class="nav-link btn btn-primary btn-sm" style="color:#fff;" href="09_chitroda_vignesh_ICT3.docx">📝 Word (.docx)</a></li>
            <li><a class="nav-link btn btn-secondary btn-sm" href="config/setup_db.php" target="_blank">🔄 Reset DB</a></li>
        </ul>
    </div>
</nav>

<div class="container" style="padding-top: 30px; flex: 1;">
    <!-- Hero Banner -->
    <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 36px 30px; border-radius: var(--radius); margin-bottom: 24px; box-shadow: var(--shadow-lg);">
        <span class="badge badge-primary" style="background: rgba(37,99,235,0.3); color: #93c5fd; padding: 6px 14px; font-size: 0.85rem; margin-bottom: 12px; display: inline-block;">
            Computer Science Practical Examination
        </span>
        <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">PHP Practical Assignment Suite</h1>
        <p style="color: #cbd5e1; font-size: 1.05rem; max-width: 850px; line-height: 1.6;">
            A complete, interactive implementation of all 6 practical questions: E-Commerce Shopping Cart with GD Image Resizing & AJAX Pagination, XML/MySQL Data Interchange, Directory File Lister, and Bidirectional REST APIs between PHP and Express.js.
        </p>

        <!-- System Status Bar -->
        <div style="display: flex; gap: 16px; margin-top: 24px; flex-wrap: wrap; font-size: 0.9rem;">
            <div style="background: rgba(255,255,255,0.08); padding: 8px 16px; border-radius: 6px;">
                🗄️ MySQL: <strong style="color: #4ade80;"><?= $dbStatus ?></strong>
            </div>
            <div style="background: rgba(255,255,255,0.08); padding: 8px 16px; border-radius: 6px;">
                🖼️ GD Library: <strong style="color: #4ade80;"><?= $gdStatus ?></strong>
            </div>
            <div style="background: rgba(255,255,255,0.08); padding: 8px 16px; border-radius: 6px;">
                👥 Students: <strong style="color: #60a5fa;"><?= $studentCount ?> records</strong>
            </div>
            <div style="background: rgba(255,255,255,0.08); padding: 8px 16px; border-radius: 6px;">
                📦 Store Items: <strong style="color: #60a5fa;"><?= $productCount ?> products</strong> in <?= $categoryCount ?> categories
            </div>
        </div>
    </div>

    <!-- Assignment Tasks Grid -->
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin-bottom: 16px;">
        📋 Practical Questions Index
    </h2>

    <div class="hub-grid">
        <!-- Task 1: Shopping Cart Admin -->
        <div class="hub-card">
            <span class="hub-badge badge-primary">Task 1 - Admin Site</span>
            <h3 class="hub-title">🛒 Shopping Cart - Admin Module</h3>
            <p class="hub-desc">
                Admin authentication, category CRUD (Garments, Mobiles, Laptops), and product CRUD featuring automatic <strong>GD image upload & resizing</strong>.
            </p>
            <div style="display: flex; gap: 8px; margin-top: auto;">
                <a href="shopping_cart/admin/dashboard.php" class="btn btn-primary" style="flex:1;">Open Admin Panel &rarr;</a>
                <a href="shopping_cart/admin/products.php" class="btn btn-secondary btn-sm">Products</a>
            </div>
        </div>

        <!-- Task 1: Shopping Cart User -->
        <div class="hub-card">
            <span class="hub-badge badge-success">Task 1 - User Site</span>
            <h3 class="hub-title">🛍️ Shopping Cart - User Storefront</h3>
            <p class="hub-desc">
                Category hyperlinks (Garments, Mobiles, Laptops), responsive product grid (Pic, pname, price), <strong>AJAX-loaded products with pagination</strong>, user registration with <strong>dynamic GD CAPTCHA</strong>, login, and cart checkout.
            </p>
            <div style="display: flex; gap: 8px; margin-top: auto;">
                <a href="shopping_cart/user/index.php" class="btn btn-success" style="flex:1;">Open User Store &rarr;</a>
                <a href="shopping_cart/user/register.php" class="btn btn-secondary btn-sm">Register</a>
            </div>
        </div>

        <!-- Task 2: XML to MySQL -->
        <div class="hub-card">
            <span class="hub-badge badge-warning">Task 2</span>
            <h3 class="hub-title">📄 XML &rarr; MySQL Database</h3>
            <p class="hub-desc">
                Parses <code>students.xml</code> and inserts student records into the MySQL <code>students</code> table using PHP SimpleXML and PDO prepared statements with duplicate prevention.
            </p>
            <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted);">
                Records: <strong>Vignesh, Sahil, Kais, Harsh, Manan</strong>
            </div>
            <div style="margin-top: auto;">
                <a href="task2_xml_to_db/index.php" class="btn btn-primary" style="width:100%;">Run XML to MySQL &rarr;</a>
            </div>
        </div>

        <!-- Task 3: MySQL to XML -->
        <div class="hub-card">
            <span class="hub-badge badge-warning">Task 3</span>
            <h3 class="hub-title">💾 MySQL Database &rarr; XML File</h3>
            <p class="hub-desc">
                Reads the MySQL <code>students</code> table and generates a structured, formatted <code>students.xml</code> file using PHP DOMDocument with instant in-browser preview and file download.
            </p>
            <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted);">
                Includes student addition form and direct XML download.
            </div>
            <div style="margin-top: auto;">
                <a href="task3_db_to_xml/index.php" class="btn btn-primary" style="width:100%;">Run MySQL to XML &rarr;</a>
            </div>
        </div>

        <!-- Task 4: List Directory Files -->
        <div class="hub-card">
            <span class="hub-badge badge-secondary">Task 4</span>
            <h3 class="hub-title">📁 Directory File Lister</h3>
            <p class="hub-desc">
                Interactive PHP directory explorer showcasing <code>scandir()</code>, file type detection with icons, formatted file sizes (KB/MB), last modification timestamps, and code previews.
            </p>
            <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted);">
                Features safe directory traversal and file previewer.
            </div>
            <div style="margin-top: auto;">
                <a href="task4_list_files/index.php" class="btn btn-primary" style="width:100%;">Explore Files &rarr;</a>
            </div>
        </div>

        <!-- Task 5: Call Express REST API in PHP -->
        <div class="hub-card">
            <span class="hub-badge badge-primary">Task 5</span>
            <h3 class="hub-title">⚡ Call Express REST API in PHP</h3>
            <p class="hub-desc">
                PHP client executing cURL requests (GET, POST, DELETE) against an <strong>Express.js REST API</strong> running on Node.js port 5000 with student data records.
            </p>
            <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted);">
                Target: <code>http://localhost:5000/api/students</code>
            </div>
            <div style="margin-top: auto;">
                <a href="task5_php_call_express/index.php" class="btn btn-primary" style="width:100%;">Open PHP &rarr; Express Client &rarr;</a>
            </div>
        </div>

        <!-- Task 6: Call PHP REST API in Express -->
        <div class="hub-card">
            <span class="hub-badge badge-primary">Task 6</span>
            <h3 class="hub-title">🟢 Call PHP REST API in Express</h3>
            <p class="hub-desc">
                Express.js web application (port 5001) calling a custom <strong>PHP REST API</strong> on Apache via HTTP <code>fetch()</code>, querying MySQL data and submitting records.
            </p>
            <div style="margin-bottom: 16px; font-size: 0.85rem; color: var(--text-muted);">
                PHP Endpoint: <code>http://localhost/php_assignment/task6_express_call_php/php_api/api.php</code>
            </div>
            <div style="display: flex; gap: 8px; margin-top: auto;">
                <a href="http://localhost:5001" target="_blank" class="btn btn-primary" style="flex:1;">Open Express App (5001) &rarr;</a>
                <a href="task6_express_call_php/php_api/api.php?endpoint=students" target="_blank" class="btn btn-secondary btn-sm">Direct PHP API</a>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        <p>&copy; <?= date('Y') ?> PHP Practical Assignment - University & College Laboratory Evaluation</p>
    </div>
</footer>
</body>
</html>
