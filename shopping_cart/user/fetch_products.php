<?php
// shopping_cart/user/fetch_products.php - AJAX Endpoint for Products & Pagination
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDBConnection();

// Handle Add to Cart action via AJAX
if (isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['quantity'] ?? 1));

    if ($productId > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $qty;
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }

        $totalCartCount = array_sum($_SESSION['cart']);
        echo json_encode([
            'status' => 'success',
            'message' => 'Product added to cart!',
            'cart_count' => $totalCartCount
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit;
}

// Handle Fetch Products with Category Filter and Pagination
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 3; // 3 items per page to showcase pagination effectively
$offset = ($page - 1) * $limit;

// Count total matching products
if ($categoryId > 0) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `products` WHERE `category_id` = ?");
    $countStmt->execute([$categoryId]);
} else {
    $countStmt = $pdo->query("SELECT COUNT(*) FROM `products`");
}
$totalItems = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalItems / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

// Fetch products
if ($categoryId > 0) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM `products` p 
        JOIN `categories` c ON p.category_id = c.id 
        WHERE p.category_id = ? 
        ORDER BY p.id ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM `products` p 
        JOIN `categories` c ON p.category_id = c.id 
        ORDER BY p.id ASC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
}
$products = $stmt->fetchAll();

// Build Product Grid HTML
ob_start();
if (empty($products)): ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: 8px;">
        <h3>No products found in this category.</h3>
        <p style="color: var(--text-muted);">Please select another category or check back later.</p>
    </div>
<?php else:
    foreach ($products as $p): ?>
        <div class="product-card">
            <div class="product-img-wrapper">
                <img src="../uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                <span class="product-badge"><?= htmlspecialchars($p['category_name']) ?></span>
            </div>
            <div class="product-body">
                <h3 class="product-title"><?= htmlspecialchars($p['name']) ?></h3>
                <p class="product-desc"><?= htmlspecialchars($p['description']) ?></p>
                <div class="product-footer">
                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                    <button type="button" class="btn btn-primary btn-sm add-cart-btn" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                        🛒 Add to Cart
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach;
endif;
$productsHtml = ob_get_clean();

// Build Pagination HTML
ob_start();
if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <button class="page-btn <?= ($page <= 1) ? 'disabled' : '' ?>" <?= ($page <= 1) ? 'disabled' : '' ?> onclick="loadProducts(<?= $categoryId ?>, <?= $page - 1 ?>)">
            &laquo; Prev
        </button>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <button class="page-btn <?= ($i === $page) ? 'active' : '' ?>" onclick="loadProducts(<?= $categoryId ?>, <?= $i ?>)">
                <?= $i ?>
            </button>
        <?php endfor; ?>

        <button class="page-btn <?= ($page >= $totalPages) ? 'disabled' : '' ?>" <?= ($page >= $totalPages) ? 'disabled' : '' ?> onclick="loadProducts(<?= $categoryId ?>, <?= $page + 1 ?>)">
            Next &raquo;
        </button>
    </div>
<?php endif;
$paginationHtml = ob_get_clean();

echo json_encode([
    'status' => 'success',
    'category_id' => $categoryId,
    'page' => $page,
    'totalPages' => $totalPages,
    'totalItems' => $totalItems,
    'products_html' => $productsHtml,
    'pagination_html' => $paginationHtml
]);
