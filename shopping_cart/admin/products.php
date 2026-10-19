<?php
// shopping_cart/admin/products.php - Product CRUD with Image Upload & Resize
require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

$msg = '';
$error = '';
$uploadDir = dirname(__DIR__) . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Function to resize image using PHP GD
function resizeAndSaveImage($sourceTmpPath, $destinationPath, $targetWidth = 400, $targetHeight = 300) {
    $info = getimagesize($sourceTmpPath);
    if (!$info) {
        return false;
    }

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg':
            $sourceImg = imagecreatefromjpeg($sourceTmpPath);
            break;
        case 'image/png':
            $sourceImg = imagecreatefrompng($sourceTmpPath);
            break;
        case 'image/gif':
            $sourceImg = imagecreatefromgif($sourceTmpPath);
            break;
        case 'image/webp':
            $sourceImg = imagecreatefromwebp($sourceTmpPath);
            break;
        default:
            return false;
    }

    if (!$sourceImg) {
        return false;
    }

    $origWidth = imagesx($sourceImg);
    $origHeight = imagesy($sourceImg);

    // Calculate aspect ratio
    $ratio = min($targetWidth / $origWidth, $targetHeight / $origHeight);
    $newWidth = (int)round($origWidth * $ratio);
    $newHeight = (int)round($origHeight * $ratio);

    // Create blank truecolor canvas
    $canvas = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve PNG/GIF/WEBP transparency
    if ($mime == 'image/png' || $mime == 'image/webp' || $mime == 'image/gif') {
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
    } else {
        // White background for JPEG
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $white);
    }

    // Resample original image into resized canvas
    imagecopyresampled($canvas, $sourceImg, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Save as PNG if original is PNG, otherwise JPEG
    if ($mime == 'image/png') {
        imagepng($canvas, $destinationPath, 8);
    } else {
        imagejpeg($canvas, $destinationPath, 85);
    }

    imagedestroy($sourceImg);
    imagedestroy($canvas);
    return true;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT `image` FROM `products` WHERE `id` = ?");
    $stmt->execute([$deleteId]);
    $prod = $stmt->fetch();

    if ($prod) {
        $stmtDel = $pdo->prepare("DELETE FROM `products` WHERE `id` = ?");
        $stmtDel->execute([$deleteId]);
        
        // Remove image if file exists and is not default
        if (!empty($prod['image']) && file_exists($uploadDir . $prod['image']) && !str_starts_with($prod['image'], 'default')) {
            @unlink($uploadDir . $prod['image']);
        }
        $msg = "Product deleted successfully.";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodId = isset($_POST['prod_id']) ? (int)$_POST['prod_id'] : 0;
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $stock = (int)($_POST['stock'] ?? 10);

    if (empty($name) || $categoryId <= 0 || $price < 0) {
        $error = "Please fill in all required fields (Name, Category, valid Price).";
    } else {
        $imageFilename = null;

        // Check if an image was uploaded
        if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['product_image']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($fileExt, $allowedExts)) {
                $error = "Invalid image file type. Allowed: JPG, PNG, GIF, WEBP.";
            } else {
                // Generate unique filename
                $imageFilename = 'prod_' . time() . '_' . rand(1000, 9999) . '.' . ($fileExt === 'png' ? 'png' : 'jpg');
                $destination = $uploadDir . $imageFilename;

                // Perform GD Image Resize
                if (!resizeAndSaveImage($tmpFile, $destination, 400, 300)) {
                    $error = "Failed to resize and upload the image. Please try a valid image.";
                    $imageFilename = null;
                }
            }
        }

        if (empty($error)) {
            if ($prodId > 0) {
                // Update
                if ($imageFilename) {
                    $stmt = $pdo->prepare("UPDATE `products` SET `category_id` = ?, `name` = ?, `price` = ?, `description` = ?, `stock` = ?, `image` = ? WHERE `id` = ?");
                    $stmt->execute([$categoryId, $name, $price, $description, $stock, $imageFilename, $prodId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE `products` SET `category_id` = ?, `name` = ?, `price` = ?, `description` = ?, `stock` = ? WHERE `id` = ?");
                    $stmt->execute([$categoryId, $name, $price, $description, $stock, $prodId]);
                }
                $msg = "Product '{$name}' updated successfully (image resized to 400x300 max).";
            } else {
                // Insert
                $finalImage = $imageFilename ?? 'shirt_cotton.png';
                $stmt = $pdo->prepare("INSERT INTO `products` (`category_id`, `name`, `price`, `description`, `stock`, `image`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $name, $price, $description, $stock, $finalImage]);
                $msg = "Product '{$name}' added successfully with resized picture.";
            }
        }
    }
}

// If editing, load product details
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM `categories` ORDER BY `name` ASC")->fetchAll();

// Fetch products with category names
$products = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM `products` p 
    JOIN `categories` c ON p.category_id = c.id 
    ORDER BY p.id DESC
")->fetchAll();

$pageTitle = "Manage Products - Admin";
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Manage Products (CRUD + GD Image Resize)</h1>
        <p style="color: var(--text-muted);">Upload product photos, automatically resize them using GD, and manage inventory.</p>
    </div>
    <a href="products.php" class="btn btn-secondary">Clear / Reset Form</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Product Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $editProduct ? "✏️ Edit Product" : "➕ Add Product" ?></h3>
        </div>

        <form method="POST" action="products.php" enctype="multipart/form-data">
            <?php if ($editProduct): ?>
                <input type="hidden" name="prod_id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($editProduct && $editProduct['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="name">Product Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Denim Jacket, iPhone 15" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Price ($) *</label>
                <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" placeholder="0.00" required value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="stock">Stock Quantity</label>
                <input type="number" min="0" id="stock" name="stock" class="form-control" placeholder="10" value="<?= htmlspecialchars($editProduct['stock'] ?? '15') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Product details..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="product_image">Product Picture (GD Resized)</label>
                <input type="file" id="product_image" name="product_image" class="form-control" accept="image/*">
                <small style="color: var(--text-muted); font-size: 0.8rem; display: block; margin-top: 4px;">
                    Supports JPG, PNG, GIF, WEBP. Uploaded images are automatically resampled and resized to max 400x300.
                </small>
                <?php if ($editProduct && !empty($editProduct['image'])): ?>
                    <div style="margin-top: 8px;">
                        <span style="font-size:0.8rem; color:var(--text-muted);">Current Image:</span><br>
                        <img src="../uploads/<?= htmlspecialchars($editProduct['image']) ?>" alt="current" style="height: 60px; border-radius: 4px; border: 1px solid var(--border);">
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <?= $editProduct ? "Update Product" : "Upload & Save" ?>
                </button>
                <?php if ($editProduct): ?>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Product Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📦 All Products (<?= count($products) ?>)</h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Picture</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="thumb" style="width: 55px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 45)) ?>...</div>
                                </td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($p['category_name']) ?></span></td>
                                <td><strong style="color: var(--primary);">$<?= number_format($p['price'], 2) ?></strong></td>
                                <td><?= $p['stock'] ?> units</td>
                                <td>
                                    <a href="products.php?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete product <?= htmlspecialchars(addslashes($p['name'])) ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
