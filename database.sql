-- PHP Practical Assignment Database Dump
-- Database: php_assignment_db

CREATE DATABASE IF NOT EXISTS `php_assignment_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `php_assignment_db`;

-- Drop existing tables
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `students`;

-- Table structure for `admins`
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC'); -- admin123

-- Table structure for `categories`
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Garments', 'Trendy apparel, formal wear, casual clothes and jackets'),
(2, 'Mobiles', 'Latest flagship and budget smartphones and mobile gadgets'),
(3, 'Laptops', 'High performance ultrabooks, gaming laptops and workstations');

-- Table structure for `products`
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT,
    `image` VARCHAR(255) DEFAULT 'default.png',
    `stock` INT DEFAULT 10,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `description`, `image`, `stock`) VALUES
(1, 1, 'Cotton Casual Shirt', 24.99, 'Premium 100% breathable cotton slim fit casual shirt.', 'shirt_cotton.png', 15),
(2, 1, 'Denim Jacket', 59.99, 'Classic blue denim trucker jacket with button closure.', 'denim_jacket.png', 15),
(3, 1, 'Slim Fit Chinos', 39.99, 'Comfortable stretch cotton chinos for casual or office wear.', 'chinos.png', 15),
(4, 1, 'Winter Hooded Sweatshirt', 45.00, 'Fleece lined pullover hoodie with kangaroo pocket.', 'hoodie.png', 15),
(5, 1, 'Formal Navy Blazer', 89.50, 'Tailored fit modern navy blue blazer suitable for business.', 'navy_blazer.png', 15),
(6, 2, 'iPhone 15 Pro', 999.00, 'Titanium design with A17 Pro chip and 48MP camera system.', 'iphone_15_pro.png', 15),
(7, 2, 'Samsung Galaxy S24 Ultra', 1199.00, '200MP camera, built-in S-Pen, and Galaxy AI capabilities.', 'galaxy_s24.png', 15),
(8, 2, 'Google Pixel 8 Pro', 899.00, 'Google Tensor G3, extraordinary computational photography.', 'pixel_8_pro.png', 15),
(9, 2, 'OnePlus 12', 799.00, 'Snapdragon 8 Gen 3, Hasselblad Camera with 100W charging.', 'oneplus_12.png', 15),
(10, 2, 'Xiaomi 14 Pro', 699.00, 'Leica Summilux optical lenses and Snapdragon 8 Gen 3.', 'xiaomi_14.png', 15),
(11, 3, 'MacBook Air M2', 1099.00, 'Strikingly thin design, 13.6-inch Liquid Retina display, 18-hour battery.', 'macbook_air.png', 15),
(12, 3, 'Dell XPS 15', 1499.00, 'OLED InfinityEdge touch display with Intel Core i7 and RTX graphics.', 'dell_xps15.png', 15),
(13, 3, 'ThinkPad X1 Carbon', 1399.00, 'Legendary durability, ultralight carbon fiber build, superb keyboard.', 'thinkpad_x1.png', 15),
(14, 3, 'ASUS ROG Zephyrus G14', 1599.00, 'Compact 14-inch powerhouse for high-frame-rate gaming and creators.', 'rog_g14.png', 15),
(15, 3, 'HP Spectre x360', 1299.00, 'Gem-cut 2-in-1 convertible laptop with OLED touchscreen and stylus support.', 'hp_spectre.png', 15);

-- Table structure for `users`
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'Vignesh', 'vignesh@example.com', '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC'),
(2, 'Sahil',   'sahil@example.com',   '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC'),
(3, 'Kais',    'kais@example.com',    '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC'),
(4, 'Harsh',   'harsh@example.com',   '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC'),
(5, 'Manan',   'manan@example.com',   '$2y$10$U0mY/eBq6.d7G.c8.Rlh3u/s7E.eO3GjN3.5eZ3fO7W6XQhM9j/sC');

-- Table structure for `students`
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `course` VARCHAR(100) NOT NULL,
    `marks` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `students` (`id`, `roll_no`, `name`, `email`, `course`, `marks`) VALUES
(1, '101', 'Vignesh', 'vignesh@example.com', 'MCA', 95),
(2, '102', 'Sahil',   'sahil@example.com',   'MCA', 91),
(3, '103', 'Kais',    'kais@example.com',    'MCA', 88),
(4, '104', 'Harsh',   'harsh@example.com',   'MCA', 93),
(5, '105', 'Manan',   'manan@example.com',   'MCA', 89);
