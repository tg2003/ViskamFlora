<?php
// viskam_flora_full/admin/products.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Ensure user is admin
if (!is_admin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success = "Product deleted successfully.";
    } else {
        $error = "Failed to delete product.";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    $name = sanitize_input($conn, $_POST['name']);
    $cat_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_qty'];
    $short_desc = sanitize_input($conn, $_POST['short_desc']);
    $long_desc = sanitize_input($conn, $_POST['long_desc']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Simplistic image handling (just using text input for placeholder)
    $image = sanitize_input($conn, $_POST['image']);
    if(empty($image)) $image = 'placeholder.jpg';

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, short_desc, long_desc, price, image, stock_qty, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssdssi", $cat_id, $name, $short_desc, $long_desc, $price, $image, $stock, $is_featured);
        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Failed to add product.";
        }
    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE products SET category_id=?, name=?, short_desc=?, long_desc=?, price=?, image=?, stock_qty=?, is_featured=? WHERE id=?");
        $stmt->bind_param("isssdssii", $cat_id, $name, $short_desc, $long_desc, $price, $image, $stock, $is_featured, $id);
        if ($stmt->execute()) {
            $success = "Product updated successfully.";
        } else {
            $error = "Failed to update product.";
        }
    }
}

// Fetch products
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_by = " ORDER BY p.id DESC";
switch($sort) {
    case 'price_asc':  $order_by = " ORDER BY p.price ASC"; break;
    case 'price_desc': $order_by = " ORDER BY p.price DESC"; break;
    case 'name_asc':   $order_by = " ORDER BY p.name ASC"; break;
    case 'name_desc':  $order_by = " ORDER BY p.name DESC"; break;
    case 'oldest':     $order_by = " ORDER BY p.id ASC"; break;
    case 'newest':     
    default:           $order_by = " ORDER BY p.id DESC"; break;
}

$products = $conn->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id" . $order_by);
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$cat_options = '';
while($c = $categories->fetch_assoc()) {
    $cat_options .= "<option value='{$c['id']}'>{$c['name']}</option>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 50px auto; padding: 30px; border-radius: 8px; max-width: 600px; height: 80vh; overflow-y:auto; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar" style="min-height: calc(100vh - 70px);">
            <h3 style="margin-bottom:20px; color:var(--accent-color); padding-left:10px;">Admin Panel</h3>
            <nav>
                <a href="dashboard.php">Dashboard Summary</a>
                <a href="products.php" class="active">Manage Products</a>
                <a href="orders.php">Manage Orders</a>
                <a href="users.php">Manage Users</a>
                <a href="arrangements.php">Wedding Requests</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2>Manage Products</h2>
                <div style="display:flex; gap:15px; align-items:center;">
                    <form action="products.php" method="GET" style="margin:0;">
                        <select name="sort" class="form-control" style="padding:8px 12px; font-size:0.9rem; border-radius:8px; display:inline-block; width:auto; cursor:pointer;" onchange="this.form.submit()">
                            <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Sort: Newest First</option>
                            <option value="oldest" <?= $sort==='oldest'?'selected':'' ?>>Sort: Oldest First</option>
                            <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Sort: Price Low to High</option>
                            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Sort: Price High to Low</option>
                            <option value="name_asc" <?= $sort==='name_asc'?'selected':'' ?>>Sort: Name A-Z</option>
                            <option value="name_desc" <?= $sort==='name_desc'?'selected':'' ?>>Sort: Name Z-A</option>
                        </select>
                    </form>
                    <button onclick="openModal('add')" class="btn btn-primary">+ Add New Product</button>
                </div>
            </div>
            
            <?php if ($error): ?><div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px;"><?= $success ?></div><?php endif; ?>

            <div style="background:#fff; border-radius:var(--border-radius-md); box-shadow:var(--shadow-sm); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead style="background:#f4f6f9; border-bottom:1px solid #ddd;">
                        <tr>
                            <th style="padding:15px; width:60px;">Image</th>
                            <th style="padding:15px;">Name</th>
                            <th style="padding:15px;">Category</th>
                            <th style="padding:15px;">Price</th>
                            <th style="padding:15px;">Stock</th>
                            <th style="padding:15px;">Status</th>
                            <th style="padding:15px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = $products->fetch_assoc()): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <td style="padding:15px;"><img src="<?= htmlspecialchars(get_image_url($p['image'])) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:5px;"></td>
                                <td style="padding:15px; font-weight:500;"><?= htmlspecialchars($p['name']) ?></td>
                                <td style="padding:15px; color:var(--text-muted);"><?= htmlspecialchars($p['cat_name']) ?></td>
                                <td style="padding:15px;"><?= format_price($p['price']) ?></td>
                                <td style="padding:15px;"><?= $p['stock_qty'] ?></td>
                                <td style="padding:15px;"><?= $p['is_featured'] ? '<span style="color:red;font-weight:bold;">Featured</span>' : 'Standard' ?></td>
                                <td style="padding:15px; text-align:right;">
                                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)" class="btn btn-outline" style="padding:4px 10px; font-size:0.8rem;">Edit</button>
                                    <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?');" style="color:red; margin-left:10px; font-size:0.9rem;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Add Product</h2>
            <form action="" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="prodId" value="">
                
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="name" id="prodName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" id="prodCat" class="form-control" required>
                        <?= $cat_options ?>
                    </select>
                </div>
                <div style="display:flex; gap:20px;">
                    <div class="form-group" style="flex:1;">
                        <label>Price (LKR) *</label>
                        <input type="number" step="0.01" name="price" id="prodPrice" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Stock Qty *</label>
                        <input type="number" name="stock_qty" id="prodStock" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Short Description</label>
                    <input type="text" name="short_desc" id="prodShortDesc" class="form-control">
                </div>
                <div class="form-group">
                    <label>Long Description</label>
                    <textarea name="long_desc" id="prodLongDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Image Filename</label>
                    <input type="text" name="image" id="prodImage" class="form-control" value="placeholder.jpg">
                    <small class="text-muted">Place image in /uploads/ directory</small>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured" id="prodFeatured" value="1">
                        Display in 'Featured' section on Homepage
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%;">Save Product</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('productModal');
        
        function openModal(action) {
            document.getElementById('modalTitle').innerText = 'Add New Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('prodId').value = '';
            document.querySelector('form').reset();
            modal.style.display = 'block';
        }
        
        function openEditModal(product) {
            document.getElementById('modalTitle').innerText = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            
            document.getElementById('prodId').value = product.id;
            document.getElementById('prodName').value = product.name;
            document.getElementById('prodCat').value = product.category_id;
            document.getElementById('prodPrice').value = product.price;
            document.getElementById('prodStock').value = product.stock_qty;
            document.getElementById('prodShortDesc').value = product.short_desc;
            document.getElementById('prodLongDesc').value = product.long_desc;
            document.getElementById('prodImage').value = product.image;
            document.getElementById('prodFeatured').checked = product.is_featured == 1;
            
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

