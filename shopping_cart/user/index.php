<?php
// shopping_cart/user/index.php - User Storefront
require_once __DIR__ . '/../../config/db.php';
$pdo = getDBConnection();

// Fetch categories from DB (Garments, Mobiles, Laptops, etc.)
$categories = $pdo->query("SELECT * FROM `categories` ORDER BY `id` ASC")->fetchAll();

$pageTitle = "Online Store - ApexStore";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner / Introduction -->
<div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 30px; border-radius: var(--radius); margin-bottom: 24px; box-shadow: var(--shadow);">
    <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 8px;">Explore Products & Tech</h1>
    <p style="color: #94a3b8; font-size: 1rem; max-width: 700px;">
        Browse through categories (Garments, Mobiles, Laptops). Click any category hyperlink to load items dynamically via AJAX with instant pagination.
    </p>
</div>

<!-- Toast Notification for Add to Cart -->
<div id="cart-toast" style="position: fixed; bottom: 20px; right: 20px; background: #0f172a; color: #fff; padding: 14px 20px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 9999; display: none; align-items: center; gap: 10px; border-left: 4px solid var(--success);">
    <span style="font-size: 1.2rem;">🛒</span>
    <span id="cart-toast-msg">Product added to cart!</span>
</div>

<!-- 1) List Categories Bar -->
<div class="category-bar">
    <span class="category-bar-title">📂 Categories:</span>
    <a href="javascript:void(0)" class="cat-btn active" data-cat="0" onclick="selectCategory(0, this)">
        ✨ All Products
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="javascript:void(0)" class="cat-btn" data-cat="<?= $cat['id'] ?>" onclick="selectCategory(<?= $cat['id'] ?>, this)">
            🏷️ <?= htmlspecialchars($cat['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Loading Spinner -->
<div id="loading-spinner" style="display: none; text-align: center; padding: 40px;">
    <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #e2e8f0; border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
    <p style="color: var(--text-muted); margin-top: 10px; font-weight: 500;">Loading products via AJAX...</p>
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- 2) Display products of clicked category (Grid & AJAX Pagination) -->
<div id="product-grid-container" class="product-grid">
    <!-- Loaded dynamically via AJAX -->
</div>

<!-- Pagination container -->
<div id="pagination-container">
    <!-- Loaded dynamically via AJAX -->
</div>

<script>
let currentCategoryId = 0;
let currentPage = 1;

function selectCategory(catId, element) {
    // Update active style on category hyperlinks
    document.querySelectorAll('.cat-btn').forEach(btn => btn.classList.remove('active'));
    if (element) {
        element.classList.add('active');
    }
    currentCategoryId = catId;
    currentPage = 1;
    loadProducts(currentCategoryId, currentPage);
}

function loadProducts(catId, page) {
    currentCategoryId = catId;
    currentPage = page;

    const spinner = document.getElementById('loading-spinner');
    const grid = document.getElementById('product-grid-container');
    const pagination = document.getElementById('pagination-container');

    spinner.style.display = 'block';
    grid.style.opacity = '0.4';

    fetch(`fetch_products.php?category_id=${encodeURIComponent(catId)}&page=${encodeURIComponent(page)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            spinner.style.display = 'none';
            grid.style.opacity = '1';

            if (data.status === 'success') {
                grid.innerHTML = data.products_html;
                pagination.innerHTML = data.pagination_html;
                attachAddToCartListeners();
            } else {
                grid.innerHTML = '<div style="color:red; text-align:center;">Failed to load products.</div>';
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            grid.style.opacity = '1';
            grid.innerHTML = '<div style="color:red; text-align:center;">Error loading products via AJAX: ' + err.message + '</div>';
        });
}

function attachAddToCartListeners() {
    document.querySelectorAll('.add-cart-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const prodId = this.getAttribute('data-id');
            const prodName = this.getAttribute('data-name');

            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', prodId);
            formData.append('quantity', 1);

            fetch('fetch_products.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    // Update cart counter badge in navbar
                    const counter = document.getElementById('cart-counter');
                    if (counter) counter.innerText = res.cart_count;

                    // Show toast notification
                    showToast(`"${prodName}" added to cart!`);
                }
            })
            .catch(err => console.error('Cart add error:', err));
        });
    });
}

function showToast(message) {
    const toast = document.getElementById('cart-toast');
    const msg = document.getElementById('cart-toast-msg');
    msg.innerText = message;
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 2800);
}

// Initial load on page ready
document.addEventListener('DOMContentLoaded', function() {
    loadProducts(0, 1);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
