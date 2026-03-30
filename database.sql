-- Viskam Flora Database Setup SQL

CREATE DATABASE IF NOT EXISTS viskam_flora;
USE viskam_flora;

-- Drop existing tables to ensure clean schema and avoid duplicate entries or missing column errors
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS wedding_arrangements;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, slug) VALUES 
('Flower Bouquets', 'flower-bouquets'),
('Gifts for Men', 'gifts-for-men'),
('Gifts for Women', 'gifts-for-women'),
('Chocolates', 'chocolates'),
('Birthday Cards', 'birthday-cards'),
('Gift Boxes', 'gift-boxes');

-- 3. Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    short_desc VARCHAR(255),
    long_desc TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'placeholder.jpg',
    stock_qty INT DEFAULT 10,
    is_featured BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Seed Products

-- 15 Flower Bouquets (Cat ID: 1, Price LKR 1500 - 5000)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(1, 'Crimson Love Rose Bouquet', 'A classic bundle of deep red roses.', 4500.00, 'bouquet_1.jpg', 1),
(1, 'Sunshine Lily Arrangement', 'Bright yellow lilies to brighten the day.', 3200.00, 'bouquet_2.jpg', 0),
(1, 'Pastel Carnation Delight', 'Soft pastel carnations in an elegant wrap.', 2500.00, 'bouquet_3.jpg', 1),
(1, 'Tropical Orchid Elegance', 'Exotic orchids for a premium gift.', 5000.00, 'bouquet_4.jpg', 0),
(1, 'Sweetheart Tulip Bundle', 'Imported fresh tulips in romantic pink.', 4800.00, 'bouquet_5.jpg', 0),
(1, 'White Whisper Daisies', 'Simple, elegant white daisies.', 1500.00, 'bouquet_6.jpg', 0),
(1, 'Midnight Blue Roses', 'Unique, specially dyed blue roses.', 4900.00, 'bouquet_7.jpg', 1),
(1, 'Spring Symphony Mixed Bouquet', 'A vibrant mix of seasonal spring flowers.', 3500.00, 'bouquet_8.jpg', 0),
(1, 'Rustic Sunflower Charm', 'Sunny sunflowers wrapped in rustic paper.', 2800.00, 'bouquet_9.jpg', 0),
(1, 'Blush Peony Perfection', 'Luxurious soft pink peonies.', 4200.00, 'bouquet_10.jpg', 0),
(1, 'Golden Anniversary Bouquet', 'Yellow and golden hues for special moments.', 3800.00, 'bouquet_1.jpg', 0),
(1, 'Purple Majesty Iris', 'Striking purple irises.', 2900.00, 'bouquet_2.jpg', 0),
(1, 'Wildflower Meadow Mix', 'A casual, organic wildflower arrangement.', 2100.00, 'bouquet_3.jpg', 0),
(1, 'Classic White Roses', 'Pure white roses for pure love.', 4500.00, 'bouquet_4.jpg', 0),
(1, 'Baby Breath Cloud', 'A massive, dreamy bundle of gypsophila.', 1800.00, 'bouquet_5.jpg', 0);

-- Gifts for Men (Cat ID: 2, 12 items)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(2, 'Classic Leather Wallet', 'Genuine black leather wallet.', 3000.00, 'gift_men_1.jpg', 1),
(2, 'Men\'s Grooming Kit', 'Complete grooming essentials.', 4500.00, 'gift_men_2.jpg', 0),
(2, 'Stainless Steel Watch', 'Elegant analog wristwatch.', 8500.00, 'gift_men_1.jpg', 0),
(2, 'Matte Black Pen Set', 'Premium pen set for professionals.', 2500.00, 'gift_men_2.jpg', 0),
(2, 'Sports Sunglasses', 'UV protected stylish shades.', 3200.00, 'gift_men_1.jpg', 0),
(2, 'Cologne & Body Wash Set', 'Refreshing masculine fragrance.', 5500.00, 'gift_men_2.jpg', 1),
(2, 'Engraved Keychain', 'Customizable metal keychain.', 800.00, 'gift_men_1.jpg', 0),
(2, 'Men\'s Leather Belt', 'Reversible formal belt.', 2200.00, 'gift_men_2.jpg', 0),
(2, 'Travel Neck Pillow', 'Comfortable memory foam neck pillow.', 1500.00, 'gift_men_1.jpg', 0),
(2, 'Smart Fitness Band', 'Activity and heart rate tracker.', 6000.00, 'gift_men_2.jpg', 0),
(2, 'Whiskey Glass Set', 'Two crystal rock glasses.', 2800.00, 'gift_men_1.jpg', 0),
(2, 'Cufflinks Box', 'Silver plated formal cufflinks.', 1800.00, 'gift_men_2.jpg', 0);

-- Gifts for Women (Cat ID: 3, 13 items)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(3, 'Rose Gold Pendant Necklace', 'Delicate necklace with a floral pendant.', 3500.00, 'gift_women_1.jpg', 1),
(3, 'Scented Candle Set', 'Aromatic soy candles (Vanilla & Lavender).', 2200.00, 'gift_women_2.jpg', 0),
(3, 'Luxury Spa Bath Set', 'Bath bombs, salts, and scrubs.', 4000.00, 'gift_women_1.jpg', 0),
(3, 'Silk Sleeping Mask', 'Soft silk eye mask for beauty sleep.', 1200.00, 'gift_women_2.jpg', 0),
(3, 'Designer Handbag', 'Chic faux leather tote bag.', 6500.00, 'gift_women_1.jpg', 1),
(3, 'Makeup Brush Kit', 'Professional 10-piece brush set.', 2800.00, 'gift_women_2.jpg', 0),
(3, 'Silver Charm Bracelet', 'Elegant bracelet with cute charms.', 3200.00, 'gift_women_1.jpg', 0),
(3, 'Floral Perfume Spray', 'Sweet and fresh everyday fragrance.', 4500.00, 'gift_women_2.jpg', 0),
(3, 'Plush Teddy Bear (Large)', 'Soft cuddly teddy bear.', 3800.00, 'gift_women_1.jpg', 0),
(3, 'Ceramic Jewelry Holder', 'Cute dish for rings and trinkets.', 950.00, 'gift_women_2.jpg', 0),
(3, 'Pocket Compact Mirror', 'Engraved vintage style mirror.', 750.00, 'gift_women_1.jpg', 0),
(3, 'Skincare Travel Kit', 'Miniature cleanser, toner, and moisturizer.', 5000.00, 'gift_women_2.jpg', 0),
(3, 'Satin Robe', 'Luxurious pink satin lounging robe.', 3400.00, 'gift_women_1.jpg', 0);

-- Chocolates (Cat ID: 4, 25 items)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(4, 'KitKat 4-Finger (x5)', 'Bundle of 5 classic KitKats.', 1000.00, 'choc_1.jpg', 0),
(4, 'Toblerone Gold 100g', 'Swiss milk chocolate with honey & almond.', 1200.00, 'choc_2.jpg', 1),
(4, 'Snickers Bar (x5)', 'Bundle of 5 peanut caramel bars.', 1100.00, 'choc_1.jpg', 0),
(4, 'Mars Bar (x5)', 'Bundle of 5 caramel nougat bars.', 1100.00, 'choc_2.jpg', 0),
(4, 'Ferrero Rocher 16-Piece', 'Hazelnut and milk chocolate truffles.', 3500.00, 'choc_1.jpg', 1),
(4, 'Ferrero Rocher 24-Piece', 'Large box of hazelnut truffles.', 5000.00, 'choc_2.jpg', 0),
(4, 'Lindt Excellence Dark 70%', 'Premium 70% cocoa dark chocolate.', 1800.00, 'choc_1.jpg', 0),
(4, 'Lindt Swiss Classic Milk', 'Smooth Swiss milk chocolate.', 1800.00, 'choc_2.jpg', 0),
(4, 'Cadbury Dairy Milk 160g', 'Classic creamy milk chocolate block.', 900.00, 'choc_1.jpg', 0),
(4, 'Cadbury Roast Almond 160g', 'Milk chocolate with roasted almonds.', 950.00, 'choc_2.jpg', 0),
(4, 'Bounty Bar (x5)', 'Coconut filled chocolate bars.', 1000.00, 'choc_1.jpg', 0),
(4, 'Twix Twin Bar (x5)', 'Caramel shortbread chocolate.', 1100.00, 'choc_2.jpg', 0),
(4, 'M&Ms Peanut Share Bag', 'Colorful coated peanut chocolates.', 1300.00, 'choc_1.jpg', 0),
(4, 'M&Ms Milk Chocolate Bag', 'Classic chocolate buttons.', 1300.00, 'choc_2.jpg', 0),
(4, 'Milka Alpine Milk', 'Smooth Alpine milk chocolate.', 1400.00, 'choc_1.jpg', 0),
(4, 'Rittersport Marzipan', 'Dark chocolate with marzipan filling.', 1200.00, 'choc_2.jpg', 0),
(4, 'Rittersport Hazelnut', 'Milk chocolate with whole hazelnuts.', 1400.00, 'choc_1.jpg', 0),
(4, 'Hersheys Kisses Milkpack', 'Bag of classic Hersey\'s Kisses.', 1800.00, 'choc_2.jpg', 0),
(4, 'Kinder Bueno (x3)', 'Crispy wafer with creamy hazelnut filling.', 1050.00, 'choc_1.jpg', 0),
(4, 'Kinder Joy (x3)', 'Chocolate treat with a surprise toy.', 900.00, 'choc_2.jpg', 0),
(4, 'Ghirardelli Dark Caramel', 'Dark chocolate squares with caramel.', 2200.00, 'choc_1.jpg', 0),
(4, 'Godiva Assorted Truffles', 'Premium assorted chocolate truffles (Small).', 4500.00, 'choc_2.jpg', 0),
(4, 'Moser Roth Mint', 'German dark chocolate with mint.', 1100.00, 'choc_1.jpg', 0),
(4, 'Reeses Peanut Butter Cups', 'Chocolate cups with peanut butter.', 1300.00, 'choc_2.jpg', 0),
(4, 'Maltesers Share Box', 'Crispy malt centers covered in milk chocolate.', 1600.00, 'choc_1.jpg', 0);

-- Birthday Cards (Cat ID: 5, 8 items)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(5, 'Pop-up Birthday Cake Card', '3D popup greeting card.', 600.00, 'card_1.jpg', 1),
(5, 'Elegant Gold Foil Card', 'Minimalist card with gold details.', 450.00, 'card_2.jpg', 0),
(5, 'Funny Birthday Wishes Card', 'Humorous card for friends.', 350.00, 'card_1.jpg', 0),
(5, 'Romantic Love Birthday Card', 'Heartwarming wishes for your partner.', 500.00, 'card_2.jpg', 0),
(5, 'Floral Watercolor Card', 'Beautiful painted flower design.', 400.00, 'card_1.jpg', 0),
(5, 'Musical Happy Birthday Card', 'Plays a tune when opened.', 850.00, 'card_2.jpg', 0),
(5, 'Blank Kraft Paper Card', 'Rustic blank card for custom messages.', 250.00, 'card_1.jpg', 0),
(5, 'Giant Milestone Card', 'Oversized card for 18th/21st/50th.', 1200.00, 'card_2.jpg', 0);

-- Gift Boxes (Cat ID: 6, 4 items)
INSERT INTO products (category_id, name, short_desc, price, image, is_featured) VALUES
(6, 'Ultimate Romance Box', 'Red roses, Ferrero, plush bear, and a card.', 8500.00, 'box_1.jpg', 1),
(6, 'Gentleman\'s Classic Combo', 'Wallet, pen, cologne, and dark chocolate.', 9500.00, 'box_2.jpg', 1),
(6, 'Sweet Cravings Box', 'Assortment of imported chocolates & sweets.', 5500.00, 'box_1.jpg', 0),
(6, 'Spa Retreat Gift Box', 'Candles, bath bombs, robe, and skincare.', 7500.00, 'box_2.jpg', 0);


-- 4. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending', 'Paid', 'Shipped', 'Delivered') DEFAULT 'Pending',
    delivery_method ENUM('Standard', 'Express', 'Pickup') DEFAULT 'Standard',
    payment_method ENUM('COD', 'Card') DEFAULT 'COD',
    shipping_address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 6. Wedding Arrangements Table
CREATE TABLE IF NOT EXISTS wedding_arrangements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(255),
    details TEXT,
    status ENUM('Pending', 'Reviewed', 'Accepted', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
