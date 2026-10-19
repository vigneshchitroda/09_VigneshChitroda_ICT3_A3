<?php
// shopping_cart/admin/categories.php - Category CRUD Management
require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

$msg = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM `categories` WHERE `id` = ?");
        $stmt->execute([$deleteId]);
        $msg = "Category deleted successfully (along with any associated products).";
    } catch (PDOException $e) {
        $error = "Error deleting category: " . $e->getMessage();
    }
}

// Handle Add or Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catId = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        try {
            if ($catId > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE `categories` SET `name` = ?, `description` = ? WHERE `id` = ?");
                $stmt->execute([$name, $description, $catId]);
                $msg = "Category '{$name}' updated successfully.";
            } else {
                // Create
                $stmt = $pdo->prepare("INSERT INTO `categories` (`name`, `description`) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                $msg = "Category '{$name}' created successfully.";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "A category with the name '{$name}' already exists.";
            } else {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// If editing, load category details
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM `categories` WHERE `id` = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}

// Fetch all categories with product count
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM `categories` c 
    LEFT JOIN `products` p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

$pageTitle = "Manage Categories - Admin";
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Manage Categories (CRUD)</h1>
        <p style="color: var(--text-muted);">Create, view, update, and delete product categories.</p>
    </div>
    <a href="categories.php" class="btn btn-secondary">Clear / Reset Form</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">
    <!-- Category Form (Add / Edit) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= $editCategory ? "✏️ Edit Category" : "➕ Add New Category" ?></h3>
        </div>

        <form method="POST" action="categories.php">
            <?php if ($editCategory): ?>
                <input type="hidden" name="cat_id" value="<?= $editCategory['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="name">Category Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Garments, Mobiles, Laptops" required value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Brief category summary..."><?= htmlspecialchars($editCategory['description'] ?? '') ?></textarea>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <?= $editCategory ? "Save Changes" : "Create Category" ?>
                </button>
                <?php if ($editCategory): ?>
                    <a href="categories.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Category List Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📁 All Categories (<?= count($categories) ?>)</h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="width: 110px;">Products</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">No categories found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($cat['name']) ?></strong>
                                    <?php if (in_array($cat['name'], ['Garments', 'Mobiles', 'Laptops'])): ?>
                                        <span class="badge badge-primary" style="margin-left: 4px;">Requirement</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                                <td>
                                    <span class="badge badge-success"><?= $cat['product_count'] ?> products</span>
                                </td>
                                <td>
                                    <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="categories.php?delete=<?= $cat['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Deleting this category will also delete its products. Proceed?');">Delete</a>
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
