<?php
// shopping_cart/admin/dashboard.php
require_once __DIR__ . '/../../config/db.php';
$pageTitle = "Admin Dashboard - Shopping Cart";
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Fetch metrics
$catCount = $pdo->query("SELECT COUNT(*) FROM `categories`")->fetchColumn();
$prodCount = $pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
$totalStock = $pdo->query("SELECT SUM(stock) FROM `products`")->fetchColumn() ?? 0;

// Fetch latest products
$recentProducts = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM `products` p 
    JOIN `categories` c ON p.category_id = c.id 
    ORDER BY p.id DESC LIMIT 5
")->fetchAll();
?>

<div style="margin-bottom: 24px;">
    <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">Admin Dashboard</h1>
    <p style="color: var(--text-muted);">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_user']) ?></strong>. Manage your store inventory and categories.</p>
</div>

<!-- Metrics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card" style="margin-bottom:0; border-left: 4px solid var(--primary);">
        <div style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Categories</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--dark); margin: 6px 0;"><?= $catCount ?></div>
        <a href="categories.php" class="btn btn-secondary btn-sm" style="display:inline-block; margin-top:4px;">Manage Categories &rarr;</a>
    </div>

    <div class="card" style="margin-bottom:0; border-left: 4px solid var(--success);">
        <div style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Products</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--dark); margin: 6px 0;"><?= $prodCount ?></div>
        <a href="products.php" class="btn btn-secondary btn-sm" style="display:inline-block; margin-top:4px;">Manage Products &rarr;</a>
    </div>

    <div class="card" style="margin-bottom:0; border-left: 4px solid var(--warning);">
        <div style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Registered Users</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--dark); margin: 6px 0;"><?= $userCount ?></div>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Registered Accounts</span>
    </div>

    <div class="card" style="margin-bottom:0; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Inventory Units</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--dark); margin: 6px 0;"><?= $totalStock ?></div>
        <span style="font-size: 0.85rem; color: var(--text-muted);">Across all categories</span>
    </div>
</div>

<!-- Quick Actions -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h3 class="card-title">⚡ Quick Management Actions</h3>
    </div>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="categories.php" class="btn btn-primary">➕ Add / Manage Category</a>
        <a href="products.php" class="btn btn-success">📦 Add / Manage Product with Image Resize</a>
        <a href="../user/index.php" target="_blank" class="btn btn-secondary">🌐 Open Storefront (User View)</a>
        <a href="../../index.php" class="btn btn-secondary">🏠 Back to Assignment Portal</a>
    </div>
</div>

<!-- Recent Products Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Recently Added Products</h3>
        <a href="products.php" class="btn btn-secondary btn-sm">View All (<?= $prodCount ?>)</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 70px;">Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th style="width: 140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentProducts)): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 20px;">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($recentProducts as $prod): ?>
                        <tr>
                            <td>
                                <img src="../uploads/<?= htmlspecialchars($prod['image']) ?>" alt="thumbnail" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                            </td>
                            <td><strong><?= htmlspecialchars($prod['name']) ?></strong></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($prod['category_name']) ?></span></td>
                            <td><strong>$<?= number_format($prod['price'], 2) ?></strong></td>
                            <td><?= $prod['stock'] ?> units</td>
                            <td>
                                <a href="products.php?edit=<?= $prod['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="products.php?delete=<?= $prod['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
