-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS supermarket;
USE supermarket;

-- جدول المستخدمين
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    purchase_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    min_quantity INT DEFAULT 5,
    expiry_date DATE,
    category VARCHAR(100),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المبيعات
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    tax DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL,
    change_amount DECIMAL(10,2) DEFAULT 0,
    payment_method ENUM('cash', 'card', 'mobile') DEFAULT 'cash',
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- جدول تفاصيل المبيعات
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- إدخال بيانات تجريبية
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', MD5('admin123'), 'مدير النظام', 'admin'),
('cashier', MD5('cashier123'), 'الكاشير', 'cashier');

INSERT INTO products (barcode, name, selling_price, purchase_price, quantity, category) VALUES
('6221000012345', 'خبز أبيض', 5.00, 3.50, 100, 'مخبوزات'),
('6221000012346', 'حليب كامل الدسم', 12.00, 9.00, 50, 'ألبان'),
('6221000012347', 'زيت زيتون', 45.00, 35.00, 30, 'زيوت'),
('6221000012348', 'أرز بسمتي', 25.00, 18.00, 80, 'مواد تموينية'),
('6221000012349', 'سكر أبيض', 15.00, 11.00, 60, 'مواد تموينية'),
('6221000012350', 'شاي أحمر', 28.00, 22.00, 40, 'مشروبات'),
('6221000012351', 'قهوة سريعة التحضير', 35.00, 28.00, 25, 'مشروبات'),
('6221000012352', 'صلصة طماطم', 8.00, 5.50, 90, 'مواد غذائية'),
('6221000012353', 'دقيق أبيض', 18.00, 13.00, 70, 'مواد تموينية'),
('6221000012354', 'زبدة', 22.00, 17.00, 45, 'ألبان'),
('6221000012355', 'جبنة بيضاء', 30.00, 24.00, 35, 'ألبان'),
('6221000012356', 'بيض (30 بيضة)', 45.00, 38.00, 40, 'بيض'),
('6221000012357', 'مكرونة', 12.00, 8.50, 100, 'مواد غذائية'),
('6221000012358', 'عدس أصفر', 20.00, 15.00, 55, 'بقوليات'),
('6221000012359', 'فول مجروش', 18.00, 13.00, 60, 'بقوليات'),
('6221000012360', 'كاتشب', 15.00, 11.00, 80, 'صوص'),
('6221000012361', 'مايونيز', 18.00, 13.50, 75, 'صوص'),
('6221000012362', 'عصير برتقال', 10.00, 7.00, 120, 'مشروبات'),
('6221000012363', 'مياه معدنية', 3.00, 2.00, 200, 'مشروبات'),
('6221000012364', 'شيبس', 5.00, 3.50, 150, 'وجبات خفيفة');
