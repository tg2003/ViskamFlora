<?php
// viskam_flora_full/index.php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Fetch Categories
$cat_query = "SELECT * FROM categories ORDER BY id ASC";
$categories = $conn->query($cat_query);

// Fetch Featured Products
$feat_query = "SELECT p.*, c.name as category_name 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id 
               WHERE p.is_featured = 1 
               LIMIT 8";
$featured_products = $conn->query($feat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viskam Flora | Premium Flowers & Gifts</title>
    <meta name="description" content="Viskam Flora provides fresh flowers, premium gifts, chocolates, and bespoke arrangements for your loved ones.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;900&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Test%20by%20antigravity/viskam_flora_full/assets/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <!-- ========== HERO CAROUSEL ========== -->
    <div class="hero-carousel" id="heroCarousel">


        <!-- Slide 1 (Eager load) -->
        <div class="carousel-slide active" style="background-image: url('https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=60&w=1000&auto=format&fit=crop');">
            <div class="carousel-overlay"></div>
            <div class="carousel-legend">
                <span class="carousel-tag">🌸 New Arrivals</span>
                <h1>Fresh Bouquets, <br>Delivered Daily</h1>
                <p>Handpicked flowers for every occasion — birthdays, anniversaries, and more.</p>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=flower-bouquets" class="carousel-btn">Shop Now →</a>
            </div>
        </div>

        <!-- Slide 2 (Lazy load via generic JS or CSS bg images load when visible... wait, bg images are loaded by browser. Let's keep them small) -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=60&w=1000&auto=format&fit=crop');">
            <div class="carousel-overlay"></div>
            <div class="carousel-legend">
                <span class="carousel-tag">🎁 Best Sellers</span>
                <h1>Premium Gift Boxes <br>For Every Moment</h1>
                <p>Curated gift sets for him, her, and everyone you love.</p>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=gift-boxes" class="carousel-btn">Shop Now →</a>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1549007994-cb92caebd54b?q=60&w=1000&auto=format&fit=crop');">
            <div class="carousel-overlay"></div>
            <div class="carousel-legend">
                <span class="carousel-tag">🍫 Sweet Treats</span>
                <h1>Premium Chocolates <br>They'll Adore</h1>
                <p>Ferrero Rocher, Lindt, Godiva and more — imported & fresh.</p>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=chocolates" class="carousel-btn">Shop Now →</a>
            </div>
        </div>

        <!-- Slide 4 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1612817288484-6f916006741a?q=60&w=1000&auto=format&fit=crop');">
            <div class="carousel-overlay"></div>
            <div class="carousel-legend">
                <span class="carousel-tag">💝 For Her</span>
                <h1>Gifts She'll <br>Never Forget</h1>
                <p>Jewelry, spa sets, perfumes & more — because she deserves the best.</p>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=gifts-for-women" class="carousel-btn">Shop Now →</a>
            </div>
        </div>

        <!-- Slide 5 -->
        <div class="carousel-slide" style="background-image: url('https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=60&w=1000&auto=format&fit=crop');">
            <div class="carousel-overlay"></div>
            <div class="carousel-legend">
                <span class="carousel-tag">💌 Cards</span>
                <h1>Say It With <br>a Card</h1>
                <p>Beautiful birthday cards — popup, musical, handcrafted options.</p>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=birthday-cards" class="carousel-btn">Shop Now →</a>
            </div>
        </div>


        <!-- Arrows -->
        <button class="carousel-arrow carousel-prev" onclick="moveCarousel(-1)">&#8249;</button>
        <button class="carousel-arrow carousel-next" onclick="moveCarousel(1)">&#8250;</button>

        <!-- Dots -->
        <div class="carousel-dots">
            <span class="dot active" onclick="goToSlide(0)"></span>
            <span class="dot" onclick="goToSlide(1)"></span>
            <span class="dot" onclick="goToSlide(2)"></span>
            <span class="dot" onclick="goToSlide(3)"></span>
            <span class="dot" onclick="goToSlide(4)"></span>
        </div>
    </div>

    <script>
    (function() {
        let current = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots   = document.querySelectorAll('.dot');
        let timer    = setInterval(() => moveCarousel(1), 5000);

        window.goToSlide = function(n) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = (n + slides.length) % slides.length;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
            clearInterval(timer);
            timer = setInterval(() => moveCarousel(1), 5000);
        };

        window.moveCarousel = function(dir) {
            goToSlide(current + dir);
        };
    })();
    </script>

    <!-- Categories Section -->
    <?php
    $cat_images = [
        'flower-bouquets'  => 'https://images.unsplash.com/photo-1563241527-2804ec6fc970?q=60&w=150&auto=format&fit=crop',
        'gifts-for-men'    => 'https://images.unsplash.com/photo-1536031232-e7bb4d0f52ee?q=60&w=150&auto=format&fit=crop',
        'gifts-for-women'  => 'https://images.unsplash.com/photo-1612817288484-6f916006741a?q=60&w=150&auto=format&fit=crop',
        'chocolates'       => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?q=60&w=150&auto=format&fit=crop',
        'birthday-cards'   => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?q=60&w=150&auto=format&fit=crop',
        'gift-boxes'       => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=60&w=150&auto=format&fit=crop',
    ];
    $fallback_cat = 'https://images.unsplash.com/photo-1490750967868-88df5691cc85?q=60&w=150&auto=format&fit=crop';
    ?>
    <section class="categories-preview container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <?php while($cat = $categories->fetch_assoc()): ?>
                <a href="/Test%20by%20antigravity/viskam_flora_full/products/index.php?cat=<?= $cat['slug'] ?>" class="category-item">
                    <div class="category-circle">
                        <img src="<?= $cat_images[$cat['slug']] ?? $fallback_cat ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy">
                    </div>
                    <h4><?= htmlspecialchars($cat['name']) ?></h4>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="products-section container" style="padding-top:20px;">
        <h2 class="section-title" style="margin-bottom:20px;">Hot & Featured</h2>
        <p class="text-center mb-4 text-muted">Handpicked favorites loved by our customers.</p>
        
        <div class="product-grid">
            <?php while($product = $featured_products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-badge">Hot</div>
                    <img src="<?= htmlspecialchars(get_image_url($product['image'])) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image" loading="lazy">
                    
                    <div class="product-info">
                        <span style="font-size:0.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px;"><?= htmlspecialchars($product['category_name']) ?></span>
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-price"><?= format_price($product['price']) ?></p>
                        
                        <div class="product-actions">
                            <a href="/Test%20by%20antigravity/viskam_flora_full/products/detail.php?id=<?= $product['id'] ?>" class="btn btn-outline" style="flex: 1;">View Details</a>
                            <form action="/Test%20by%20antigravity/viskam_flora_full/cart/cart_page.php" method="POST" style="flex: 1; display:flex;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary" style="width:100%;">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

