-- Viskam Flora Database Schema
CREATE DATABASE IF NOT EXISTS viskam_flora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE viskam_flora;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_date DATE,
    delivery_time VARCHAR(50),
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cod',
    payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Wedding arrangements table
CREATE TABLE IF NOT EXISTS wedding_arrangements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    contact_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(150) NOT NULL,
    contact_phone VARCHAR(20) NOT NULL,
    wedding_date DATE NOT NULL,
    venue VARCHAR(255),
    guest_count INT,
    budget_range VARCHAR(50),
    arrangement_types TEXT,
    color_preferences TEXT,
    special_requests TEXT,
    status ENUM('new','contacted','quoted','confirmed','completed','cancelled') DEFAULT 'new',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@viskamflora.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert categories
INSERT INTO categories (name, slug, description) VALUES
('Fresh Flowers', 'fresh-flowers', 'Beautiful fresh cut flowers for every occasion'),
('Bouquets', 'bouquets', 'Handcrafted bouquets for gifting'),
('Gift Baskets', 'gift-baskets', 'Curated gift baskets with flowers and goodies'),
('Wedding', 'wedding', 'Special wedding flower arrangements'),
('Plants', 'plants', 'Indoor and outdoor plants');

-- Insert sample products
INSERT INTO products (category_id, name, slug, description, price, stock, is_featured) VALUES
(1, 'Red Rose Bunch', 'red-rose-bunch', 'Fresh red roses, perfect for romance. Pack of 12 stems.', 1500.00, 50, 1),
(1, 'White Lily Bundle', 'white-lily-bundle', 'Pure white lilies, elegant and fragrant. Pack of 6 stems.', 1200.00, 30, 1),
(2, 'Mixed Bouquet', 'mixed-bouquet', 'A vibrant mix of seasonal flowers beautifully arranged.', 2500.00, 25, 1),
(2, 'Sunflower Bouquet', 'sunflower-bouquet', 'Bright sunflowers to light up any room. Pack of 5.', 1800.00, 20, 0),
(3, 'Love Gift Basket', 'love-gift-basket', 'Flowers, chocolates and a heartfelt card in a beautiful basket.', 3500.00, 15, 1),
(3, 'Birthday Hamper', 'birthday-hamper', 'Flowers, balloons and sweets for a perfect birthday.', 4000.00, 10, 0),
(5, 'Peace Lily Plant', 'peace-lily-plant', 'Easy-care indoor plant that purifies air. Comes in a decorative pot.', 2200.00, 40, 0),
(5, 'Succulent Set', 'succulent-set', 'Set of 3 beautiful succulents in matching pots.', 1600.00, 35, 1);
