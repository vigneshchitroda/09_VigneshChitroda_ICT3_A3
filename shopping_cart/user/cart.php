<?php
// shopping_cart/user/cart.php - Shopping Cart Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

$msg = '';

if (isset($_GET['demo'])) {
    $_SESSION['cart'] = [1 => 2, 6 => 1, 11 => 1];
}

// Handle Remove item
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    unset($_SESSION['cart'][$removeId]);
    header("Location: cart.php?msg=item_removed");
    exit;
}

// Handle Clear Cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php?msg=cart_cleared");
    exit;
}

// Handle Update Quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    if (!empty($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $pId => $qty) {
            $pId = (int)$pId;
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pId]);
            } else {
                $_SESSION['cart'][$pId] = $qty;
            }
        }
    }
    header("Location: cart.php?msg=cart_updated");
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'item_removed') $msg = "Item removed from cart.";
    if ($_GET['msg'] === 'cart_cleared') $msg = "Cart has been cleared.";
    if ($_GET['msg'] === 'cart_updated') $msg = "Cart quantities updated successfully.";
}

// Fetch Cart Products
$cartItems = [];
$totalAmount = 0.0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    if (!empty($productIds)) {
        $inQuery = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name 
            FROM `products` p 
            JOIN `categories` c ON p.category_id = c.id 
            WHERE p.id IN ($inQuery)
        ");
        $stmt->execute($productIds);
        $products = $stmt->fetchAll();

        foreach ($products as $p) {
            $qty = (int)($_SESSION['cart'][$p['id']] ?? 1);
            $subtotal = $qty * (float)$p['price'];
            $totalAmount += $subtotal;
            $cartItems[] = [
                'product' => $p,
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
    }
}

$pageTitle = "My Shopping Cart - ApexStore";
require_once __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">🛒 Shopping Cart</h1>
        <p style="color: var(--text-muted);">Review selected products, modify quantities, and proceed to checkout.</p>
    </div>
    <a href="index.php" class="btn btn-secondary">&larr; Continue Shopping</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if (empty($cartItems)): ?>
    <div class="card" style="text-align: center; padding: 60px 20px;">
        <div style="font-size: 3.5rem; margin-bottom: 12px;">🛍️</div>
        <h3 style="font-size: 1.4rem; color: var(--dark); margin-bottom: 8px;">Your Cart is Empty</h3>
        <p style="color: var(--text-muted); margin-bottom: 24px;">Explore our categories (Garments, Mobiles, Laptops) to find great deals!</p>
        <a href="index.php" class="btn btn-primary" style="padding: 10px 24px;">Browse Products Now</a>
    </div>
<?php else: ?>

    <form method="POST" action="cart.php">
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Picture</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th style="width: 120px;">Quantity</th>
                            <th>Subtotal</th>
                            <th style="width: 90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): 
                            $prod = $item['product'];
                        ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?= htmlspecialchars($prod['image']) ?>" alt="thumbnail" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($prod['name']) ?></strong>
                                </td>
                                <td><span class="badge badge-primary"><?= htmlspecialchars($prod['category_name']) ?></span></td>
                                <td>$<?= number_format($prod['price'], 2) ?></td>
                                <td>
                                    <input type="number" min="1" max="99" name="quantities[<?= $prod['id'] ?>]" value="<?= $item['quantity'] ?>" class="cart-qty-input">
                                </td>
                                <td><strong style="color: var(--primary);">$<?= number_format($item['subtotal'], 2) ?></strong></td>
                                <td>
                                    <a href="cart.php?remove=<?= $prod['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove item from cart?');">✕ Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="update_qty" value="1" class="btn btn-secondary">
                        🔄 Update Quantities
                    </button>
                    <a href="cart.php?clear=1" class="btn btn-danger" onclick="return confirm('Clear all items from your cart?');">
                        🗑️ Clear Cart
                    </a>
                </div>

                <div style="text-align: right;">
                    <div style="font-size: 0.95rem; color: var(--text-muted);">Estimated Total:</div>
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--dark);">$<?= number_format($totalAmount, 2) ?></div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <a href="checkout.php" class="btn btn-success" style="padding: 12px 28px; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.3);">
                Proceed to Checkout &rarr;
            </a>
        </div>
    </form>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
