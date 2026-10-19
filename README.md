# PHP Practical Assignment - Complete Laboratory Suite

This repository contains the complete solution for the PHP Practical Assignment covering all 6 questions with full database integration, responsive user interfaces, and live REST APIs.

---

## 📋 Table of Contents

1. [Quick Start & Setup](#-quick-start--setup)
2. [Task 1: Shopping Cart Application](#-task-1-shopping-cart-application)
   - [Admin Site](#admin-site)
   - [User Site](#user-site)
3. [Task 2: XML to MySQL Database](#-task-2-xml-to-mysql-database)
4. [Task 3: MySQL Database to XML File](#-task-3-mysql-database-to-xml-file)
5. [Task 4: Directory File Lister](#-task-4-directory-file-lister)
6. [Task 5: Call Express REST API in PHP](#-task-5-call-express-rest-api-in-php)
7. [Task 6: Call PHP REST API in Express](#-task-6-call-php-rest-api-in-express)
8. [Database Schema & Seed Records](#-database-schema--seed-records)
9. [Pre-configured Credentials](#-pre-configured-credentials)

---

## 🚀 Quick Start & Setup

### Prerequisites
- **XAMPP** (Apache & MySQL running on port 3306).
- **PHP 8.x** with `gd` and `pdo_mysql` extensions enabled.
- **Node.js** (v18+) & **npm** for Express REST API servers.

### 1. Database Initialization
1. Ensure MySQL is started in XAMPP.
2. Run the database migration script via CLI or browser:
   ```bash
   php config/setup_db.php
   ```
   Or visit in browser: `http://localhost/php_assignment/config/setup_db.php`
3. Alternatively, import `database.sql` directly using **phpMyAdmin** (`http://localhost/phpmyadmin`).

### 2. Launch Express REST API Servers
Open two terminal windows:

**Terminal 1 (Task 5: Express REST API on Port 5000):**
```bash
cd c:\xampp\htdocs\php_assignment\task5_php_call_express\express_api
npm install
node server.js
```

**Terminal 2 (Task 6: Express Client on Port 5001):**
```bash
cd c:\xampp\htdocs\php_assignment\task6_express_call_php\express_client
npm install
node server.js
```

### 3. Open the Assignment Portal
Open your web browser and navigate to:
**`http://localhost/php_assignment/`**

---

## 🛒 Task 1: Shopping Cart Application

### Admin Site (`/shopping_cart/admin/`)
- **Login (`login.php`)**: Secure admin authentication using `password_verify()`.
- **Manage Categories CRUD (`categories.php`)**:
  - Create, Read, Update, Delete categories.
  - Pre-seeded with *Garments*, *Mobiles*, *Laptops*.
  - Displays product counts per category.
- **Manage Products CRUD (`products.php`)**:
  - Create, Read, Update, Delete products with title, category, price, stock, description.
  - **GD Image Resizing**: Automatic resampling & resizing of uploaded product pictures to max 400x300 while maintaining aspect ratio and preserving PNG transparency.
- **Dashboard (`dashboard.php`)**: Visual metrics for inventory, categories, users, and recent products.
- **Logout (`logout.php`)**: Session invalidation and redirect.

### User Site (`/shopping_cart/user/`)
- **Categories Navigation**: Hyperlinks for *Garments*, *Mobiles*, *Laptops*, and *All Categories*.
- **AJAX Product Display (`fetch_products.php`)**:
  - Clicking any category hyperlink asynchronously queries and loads matching products without page reload.
  - Responsive product grid displaying Picture (`Pic`), Name (`pname`), and Price (`price`) in a modern grid card layout.
- **AJAX Pagination**:
  - Dynamically calculates pages (3 items per page to showcase pagination clearly).
  - Smooth page navigation without reloading the browser.
- **User Registration with CAPTCHA (`register.php` & `captcha.php`)**:
  - Dynamic image generated using PHP GD library with random background noise lines, pixels, and distorted characters.
  - Session verification of case-insensitive security code before account creation.
- **User Login (`login.php`)**: Customer authentication with session management.
- **Shopping Cart & Checkout (`cart.php` & `checkout.php`)**:
  - Asynchronous "Add to Cart" with instant navbar badge updates.
  - Modify quantity, calculate subtotals and grand total, remove items, clear cart.
  - Simulated checkout with order receipt and unique order ID.

---

## 📄 Task 2: XML to MySQL Database (`/task2_xml_to_db/`)

- Source file: `students.xml` containing records for **Vignesh, Sahil, Kais, Harsh, Manan**.
- Script: `task2_xml_to_db/index.php`.
- Features:
  - Uses PHP `simplexml_load_file()` to parse elements.
  - Inserts/upserts records into MySQL `students` table using PDO prepared statements.
  - Displays side-by-side view of the XML source and the live MySQL database table.

---

## 💾 Task 3: MySQL Database to XML File (`/task3_db_to_xml/`)

- Script: `task3_db_to_xml/index.php`.
- Features:
  - Queries records from MySQL `students` table.
  - Generates structured, indented XML via PHP `DOMDocument` (`formatOutput = true`).
  - Saves file as `students_export.xml`.
  - Provides in-browser XML syntax preview and one-click direct file download.
  - Includes a quick form to append new students directly into MySQL.

---

## 📁 Task 4: Directory File Lister (`/task4_list_files/`)

- Script: `task4_list_files/index.php`.
- Features:
  - Uses PHP `scandir()`, `is_dir()`, `filesize()`, `filemtime()`.
  - Distinguishes folders and files with visual icons (🐘 PHP, 📜 JS, 🎨 CSS, 📄 XML, 🖼️ Images).
  - Displays formatted file sizes (Bytes, KB, MB) and formatted timestamps.
  - Interactive breadcrumb navigation to drill down into subdirectories safely.
  - In-browser file previewer for source files.

---

## ⚡ Task 5: Call Express REST API in PHP (`/task5_php_call_express/`)

- **Express Server (`express_api/server.js`)**:
  - Running on `http://localhost:5000`.
  - REST endpoints:
    - `GET /api/health`: Server health check.
    - `GET /api/students`: Lists student records (pre-seeded with Vignesh, Sahil, Kais, Harsh, Manan).
    - `POST /api/students`: Creates a new student record.
    - `DELETE /api/students/:id`: Deletes a student record.
- **PHP Client (`index.php`)**:
  - Uses PHP `cURL` with `CURLOPT_CUSTOMREQUEST` (GET, POST, DELETE).
  - Features live server status badge, student table display, and POST submission form.

---

## 🟢 Task 6: Call PHP REST API in Express (`/task6_express_call_php/`)

- **PHP REST API (`php_api/api.php`)**:
  - URL: `http://localhost/php_assignment/task6_express_call_php/php_api/api.php`
  - Returns JSON data from MySQL `students` table with CORS headers (`Access-Control-Allow-Origin: *`).
  - Supports `GET ?endpoint=students`, `GET ?endpoint=products`, `GET ?endpoint=status`, and `POST`.
- **Express Client (`express_client/server.js`)**:
  - Running on `http://localhost:5001`.
  - Uses Node.js native `fetch()` to call the PHP REST API endpoint.
  - Renders an HTML dashboard at `http://localhost:5001` showing the data returned by PHP.
  - Includes a form that submits a POST request to Express, which then forwards the data via HTTP POST to the PHP REST API to save into MySQL.

---

## 🗄️ Database Schema & Seed Records

The database `php_assignment_db` contains:
1. `admins`: Admin login credentials.
2. `categories`: Garments, Mobiles, Laptops.
3. `products`: 15 products with GD-generated product images across all categories.
4. `users`: Pre-seeded user accounts.
5. `students`: Required student records:
   - **Roll 101**: Vignesh (`vignesh@example.com`, MCA, 95 Marks)
   - **Roll 102**: Sahil (`sahil@example.com`, MCA, 91 Marks)
   - **Roll 103**: Kais (`kais@example.com`, MCA, 88 Marks)
   - **Roll 104**: Harsh (`harsh@example.com`, MCA, 93 Marks)
   - **Roll 105**: Manan (`manan@example.com`, MCA, 89 Marks)

---

## 🔑 Pre-configured Credentials

| Role / Entity | Username / Email | Password | URL |
|---|---|---|---|
| **Admin Panel** | `admin` | `admin123` | `http://localhost/php_assignment/shopping_cart/admin/login.php` |
| **Customer 1** | `vignesh@example.com` | `user123` | `http://localhost/php_assignment/shopping_cart/user/login.php` |
| **Customer 2** | `sahil@example.com` | `user123` | `http://localhost/php_assignment/shopping_cart/user/login.php` |
| **Customer 3** | `kais@example.com` | `user123` | `http://localhost/php_assignment/shopping_cart/user/login.php` |
| **Customer 4** | `harsh@example.com` | `user123` | `http://localhost/php_assignment/shopping_cart/user/login.php` |
| **Customer 5** | `manan@example.com` | `user123` | `http://localhost/php_assignment/shopping_cart/user/login.php` |
| **MySQL Root** | `root` | `root` | Port `3306` |
