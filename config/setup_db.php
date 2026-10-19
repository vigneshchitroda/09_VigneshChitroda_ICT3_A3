<?php
// config/setup_db.php - Database Migration and Seeder

require_once __DIR__ . '/db.php';

$pdo = getDBConnection();

// Create Tables
$pdo->exec("
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255) DEFAULT 'default.png',
    `stock` INT DEFAULT 10,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `course` VARCHAR(100) NOT NULL,
    `marks` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "[OK] Tables created successfully.\n";

// 1. Seed Admin
$adminUser = 'admin';
$adminPass = password_hash('admin123', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `password` = ?");
$stmt->execute([$adminUser, $adminPass, $adminPass]);
echo "[OK] Admin user seeded (admin / admin123).\n";

// 2. Seed Categories
$categories = [
    ['name' => 'Garments', 'description' => 'Trendy apparel, formal wear, casual clothes and jackets'],
    ['name' => 'Mobiles', 'description' => 'Latest flagship and budget smartphones and mobile gadgets'],
    ['name' => 'Laptops', 'description' => 'High performance ultrabooks, gaming laptops and workstations']
];

$catMap = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("INSERT INTO `categories` (`name`, `description`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `description` = ?");
    $stmt->execute([$cat['name'], $cat['description'], $cat['description']]);
    
    $fetchStmt = $pdo->prepare("SELECT id FROM `categories` WHERE `name` = ?");
    $fetchStmt->execute([$cat['name']]);
    $catMap[$cat['name']] = $fetchStmt->fetchColumn();
}
echo "[OK] Categories seeded: Garments, Mobiles, Laptops.\n";

// Helper function to generate sample product images using GD
$uploadDir = dirname(__DIR__) . '/shopping_cart/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function generatePlaceholderImage($path, $text, $bgColor, $textColor) {
    if (file_exists($path)) {
        return;
    }
    $width = 400;
    $height = 300;
    $im = imagecreatetruecolor($width, $height);
    
    $bg = imagecolorallocate($im, $bgColor[0], $bgColor[1], $bgColor[2]);
    $textCol = imagecolorallocate($im, $textColor[0], $textColor[1], $textColor[2]);
    $borderCol = imagecolorallocate($im, 200, 200, 200);

    imagefilledrectangle($im, 0, 0, $width, $height, $bg);
    imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderCol);

    // Draw centered text
    $font = 5; // Built-in font
    $fontWidth = imagefontwidth($font);
    $fontHeight = imagefontheight($font);
    $textX = (int)(($width - ($fontWidth * strlen($text))) / 2);
    $textY = (int)(($height - $fontHeight) / 2);

    imagestring($im, $font, $textX, $textY, $text, $textCol);
    imagestring($im, 3, 20, $height - 30, "PHP Assignment - Resized", $textCol);

    imagepng($im, $path);
    imagedestroy($im);
}

// 3. Seed Products
$products = [
    // Garments
    [
        'category' => 'Garments',
        'name' => 'Cotton Casual Shirt',
        'price' => 24.99,
        'description' => 'Premium 100% breathable cotton slim fit casual shirt.',
        'image' => 'shirt_cotton.png',
        'bg' => [230, 240, 255],
        'text' => [30, 70, 150]
    ],
    [
        'category' => 'Garments',
        'name' => 'Denim Jacket',
        'price' => 59.99,
        'description' => 'Classic blue denim trucker jacket with button closure.',
        'image' => 'denim_jacket.png',
        'bg' => [210, 230, 250],
        'text' => [20, 50, 120]
    ],
    [
        'category' => 'Garments',
        'name' => 'Slim Fit Chinos',
        'price' => 39.99,
        'description' => 'Comfortable stretch cotton chinos for casual or office wear.',
        'image' => 'chinos.png',
        'bg' => [245, 240, 230],
        'text' => [120, 80, 40]
    ],
    [
        'category' => 'Garments',
        'name' => 'Winter Hooded Sweatshirt',
        'price' => 45.00,
        'description' => 'Fleece lined pullover hoodie with kangaroo pocket.',
        'image' => 'hoodie.png',
        'bg' => [235, 235, 240],
        'text' => [60, 60, 80]
    ],
    [
        'category' => 'Garments',
        'name' => 'Formal Navy Blazer',
        'price' => 89.50,
        'description' => 'Tailored fit modern navy blue blazer suitable for business.',
        'image' => 'navy_blazer.png',
        'bg' => [220, 225, 245],
        'text' => [25, 35, 95]
    ],

    // Mobiles
    [
        'category' => 'Mobiles',
        'name' => 'iPhone 15 Pro',
        'price' => 999.00,
        'description' => 'Titanium design with A17 Pro chip and 48MP camera system.',
        'image' => 'iphone_15_pro.png',
        'bg' => [240, 240, 245],
        'text' => [40, 40, 40]
    ],
    [
        'category' => 'Mobiles',
        'name' => 'Samsung Galaxy S24 Ultra',
        'price' => 1199.00,
        'description' => '200MP camera, built-in S-Pen, and Galaxy AI capabilities.',
        'image' => 'galaxy_s24.png',
        'bg' => [230, 245, 255],
        'text' => [10, 80, 140]
    ],
    [
        'category' => 'Mobiles',
        'name' => 'Google Pixel 8 Pro',
        'price' => 899.00,
        'description' => 'Google Tensor G3, extraordinary computational photography.',
        'image' => 'pixel_8_pro.png',
        'bg' => [255, 240, 235],
        'text' => [180, 60, 40]
    ],
    [
        'category' => 'Mobiles',
        'name' => 'OnePlus 12',
        'price' => 799.00,
        'description' => 'Snapdragon 8 Gen 3, Hasselblad Camera with 100W charging.',
        'image' => 'oneplus_12.png',
        'bg' => [255, 235, 235],
        'text' => [200, 30, 30]
    ],
    [
        'category' => 'Mobiles',
        'name' => 'Xiaomi 14 Pro',
        'price' => 699.00,
        'description' => 'Leica Summilux optical lenses and Snapdragon 8 Gen 3.',
        'image' => 'xiaomi_14.png',
        'bg' => [255, 245, 230],
        'text' => [220, 100, 20]
    ],

    // Laptops
    [
        'category' => 'Laptops',
        'name' => 'MacBook Air M2',
        'price' => 1099.00,
        'description' => 'Strikingly thin design, 13.6-inch Liquid Retina display, 18-hour battery.',
        'image' => 'macbook_air.png',
        'bg' => [245, 245, 248],
        'text' => [50, 50, 60]
    ],
    [
        'category' => 'Laptops',
        'name' => 'Dell XPS 15',
        'price' => 1499.00,
        'description' => 'OLED InfinityEdge touch display with Intel Core i7 and RTX graphics.',
        'image' => 'dell_xps15.png',
        'bg' => [235, 240, 245],
        'text' => [20, 60, 110]
    ],
    [
        'category' => 'Laptops',
        'name' => 'ThinkPad X1 Carbon',
        'price' => 1399.00,
        'description' => 'Legendary durability, ultralight carbon fiber build, superb keyboard.',
        'image' => 'thinkpad_x1.png',
        'bg' => [230, 230, 235],
        'text' => [180, 20, 20]
    ],
    [
        'category' => 'Laptops',
        'name' => 'ASUS ROG Zephyrus G14',
        'price' => 1599.00,
        'description' => 'Compact 14-inch powerhouse for high-frame-rate gaming and creators.',
        'image' => 'rog_g14.png',
        'bg' => [245, 230, 240],
        'text' => [120, 20, 90]
    ],
    [
        'category' => 'Laptops',
        'name' => 'HP Spectre x360',
        'price' => 1299.00,
        'description' => 'Gem-cut 2-in-1 convertible laptop with OLED touchscreen and stylus support.',
        'image' => 'hp_spectre.png',
        'bg' => [240, 245, 235],
        'text' => [40, 100, 50]
    ]
];

foreach ($products as $prod) {
    $imgFile = $prod['image'];
    $imgPath = $uploadDir . $imgFile;
    generatePlaceholderImage($imgPath, $prod['name'], $prod['bg'], $prod['text']);

    $catId = $catMap[$prod['category']];
    $checkStmt = $pdo->prepare("SELECT id FROM `products` WHERE `name` = ?");
    $checkStmt->execute([$prod['name']]);
    $existingId = $checkStmt->fetchColumn();

    if ($existingId) {
        $updateStmt = $pdo->prepare("UPDATE `products` SET `category_id` = ?, `price` = ?, `description` = ?, `image` = ? WHERE `id` = ?");
        $updateStmt->execute([$catId, $prod['price'], $prod['description'], $imgFile, $existingId]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO `products` (`category_id`, `name`, `price`, `description`, `image`, `stock`) VALUES (?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([$catId, $prod['name'], $prod['price'], $prod['description'], $imgFile, 15]);
    }
}
echo "[OK] Products seeded across Garments, Mobiles, and Laptops with generated images.\n";

// 4. Seed Users (Vignesh, Sahil, Kais, Harsh, Manan)
$users = [
    ['name' => 'Vignesh', 'email' => 'vignesh@example.com'],
    ['name' => 'Sahil',   'email' => 'sahil@example.com'],
    ['name' => 'Kais',    'email' => 'kais@example.com'],
    ['name' => 'Harsh',   'email' => 'harsh@example.com'],
    ['name' => 'Manan',   'email' => 'manan@example.com']
];

$userPass = password_hash('user123', PASSWORD_BCRYPT);
foreach ($users as $u) {
    $stmt = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `name` = ?");
    $stmt->execute([$u['name'], $u['email'], $userPass, $u['name']]);
}
echo "[OK] Users seeded: Vignesh, Sahil, Kais, Harsh, Manan (password: user123).\n";

// 5. Seed Students (Vignesh, Sahil, Kais, Harsh, Manan)
$students = [
    ['roll_no' => '101', 'name' => 'Vignesh', 'email' => 'vignesh@example.com', 'course' => 'MCA', 'marks' => 95],
    ['roll_no' => '102', 'name' => 'Sahil',   'email' => 'sahil@example.com',   'course' => 'MCA', 'marks' => 91],
    ['roll_no' => '103', 'name' => 'Kais',    'email' => 'kais@example.com',    'course' => 'MCA', 'marks' => 88],
    ['roll_no' => '104', 'name' => 'Harsh',   'email' => 'harsh@example.com',   'course' => 'MCA', 'marks' => 93],
    ['roll_no' => '105', 'name' => 'Manan',   'email' => 'manan@example.com',   'course' => 'MCA', 'marks' => 89]
];

foreach ($students as $s) {
    $stmt = $pdo->prepare("
        INSERT INTO `students` (`roll_no`, `name`, `email`, `course`, `marks`)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE `name` = ?, `email` = ?, `course` = ?, `marks` = ?
    ");
    $stmt->execute([
        $s['roll_no'], $s['name'], $s['email'], $s['course'], $s['marks'],
        $s['name'], $s['email'], $s['course'], $s['marks']
    ]);
}
echo "[OK] Students seeded: Vignesh, Sahil, Kais, Harsh, Manan.\n";
echo "\nAll database migrations and seed records initialized successfully!\n";
