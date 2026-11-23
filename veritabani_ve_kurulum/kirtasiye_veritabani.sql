-- ========================================================
-- KIRTASİYE STOK VE SATIŞ YÖNETİM SİSTEMİ VERİTABANI ŞEMASI
-- Versiyon: 2.0 (Kırtasiye & POS & E-Ticaret Entegrasyonu)
-- ========================================================

SET NAMES utf8mb4;
SET time_zone = '+03:00';

-- 1. ROLLER (Kullanıcı Rolleri)
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    permissions JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. KULLANICILAR (Yöneticiler, Kasiyerler, Depocular)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NULL,
    title VARCHAR(100) DEFAULT 'Kasiyer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role_id),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. KATEGORİLER (Kırtasiye Ürün Ağacı)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) UNIQUE NOT NULL,
    description TEXT NULL,
    icon VARCHAR(80) DEFAULT 'bi-bookmark',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cat_parent (parent_id),
    CONSTRAINT fk_cat_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MARKALAR (Kırtasiye Markaları: Faber-Castell, Rotring, Adel vb.)
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    country VARCHAR(80) NULL,
    website VARCHAR(200) NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ÜRÜNLER (Kırtasiye Stok Kalemleri)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    brand_id INT NULL DEFAULT NULL,
    code VARCHAR(80) UNIQUE NOT NULL,
    barcode VARCHAR(100) UNIQUE NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit VARCHAR(30) DEFAULT 'Adet',
    purchase_price DECIMAL(12,2) DEFAULT 0.00,
    sale_price DECIMAL(12,2) DEFAULT 0.00,
    wholesale_price DECIMAL(12,2) DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 20.00,
    current_stock DECIMAL(12,2) DEFAULT 0.00,
    min_stock DECIMAL(12,2) DEFAULT 5.00,
    shelf_location VARCHAR(80) NULL,
    image_url VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_prod_barcode (barcode),
    INDEX idx_prod_code (code),
    INDEX idx_prod_category (category_id),
    INDEX idx_prod_brand (brand_id),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT,
    CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. MÜŞTERİLER / CARİLER (Öğrenciler, Veliler, Öğretmenler, Okullar, Kurumsal)
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_type ENUM('individual', 'student', 'teacher', 'school', 'corporate') DEFAULT 'individual',
    company_name VARCHAR(255) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    tax_office VARCHAR(100) NULL,
    tax_number VARCHAR(50) NULL,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    credit_limit DECIMAL(12,2) DEFAULT 0.00,
    balance DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('active', 'passive') DEFAULT 'active',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_customer_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TEDARİKÇİLER (Toptancılar & Dağıtıcılar)
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(100) NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    address TEXT NULL,
    tax_office VARCHAR(100) NULL,
    tax_number VARCHAR(50) NULL,
    balance DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('active', 'passive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. SATIŞLAR (Kırtasiye POS Satışları, Web Siparişleri ve Faturalar)
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(60) UNIQUE NOT NULL,
    customer_id INT NULL DEFAULT NULL,
    user_id INT NOT NULL,
    sale_type ENUM('pos', 'online', 'wholesale') DEFAULT 'pos',
    payment_method ENUM('cash', 'credit_card', 'bank_transfer', 'open_account', 'split') DEFAULT 'cash',
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    change_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('completed', 'refunded', 'partially_refunded', 'cancelled') DEFAULT 'completed',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_sale_receipt (receipt_no),
    INDEX idx_sale_customer (customer_id),
    INDEX idx_sale_user (user_id),
    INDEX idx_sale_created (created_at),
    CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SATIŞ KALEMLERİ (Satılan Ürün Detayları)
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    barcode VARCHAR(100) NULL,
    unit VARCHAR(30) DEFAULT 'Adet',
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    cost_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_rate DECIMAL(5,2) DEFAULT 20.00,
    tax_amount DECIMAL(12,2) DEFAULT 0.00,
    discount_amount DECIMAL(12,2) DEFAULT 0.00,
    line_total DECIMAL(12,2) NOT NULL,
    returned_quantity DECIMAL(12,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sale_item_sale (sale_id),
    INDEX idx_sale_item_prod (product_id),
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. SATIŞ İADELERİ
CREATE TABLE IF NOT EXISTS sale_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_no VARCHAR(60) UNIQUE NOT NULL,
    sale_id INT NOT NULL,
    sale_item_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_refund DECIMAL(12,2) NOT NULL,
    reason TEXT NULL,
    refund_method ENUM('cash', 'card', 'credit_note') DEFAULT 'cash',
    processed_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_return_sale (sale_id),
    CONSTRAINT fk_returns_sale FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE RESTRICT,
    CONSTRAINT fk_returns_item FOREIGN KEY (sale_item_id) REFERENCES sale_items (id) ON DELETE RESTRICT,
    CONSTRAINT fk_returns_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
    CONSTRAINT fk_returns_user FOREIGN KEY (processed_by) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. STOK HAREKETLERİ (Giriş / Çıkış / Satış / İade / Fire / Sayım)
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    movement_type ENUM('purchase', 'sale', 'sale_return', 'purchase_return', 'adjustment', 'waste', 'count') NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    prev_stock DECIMAL(12,2) NOT NULL,
    next_stock DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) DEFAULT 0.00,
    total_cost DECIMAL(12,2) DEFAULT 0.00,
    reference_type VARCHAR(50) DEFAULT 'manual',
    reference_id VARCHAR(100) NULL,
    notes TEXT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mov_product (product_id),
    INDEX idx_mov_created (created_at),
    CONSTRAINT fk_movements_prod FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
    CONSTRAINT fk_movements_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. FİZİKSEL STOK SAYIMLARI
CREATE TABLE IF NOT EXISTS stock_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    count_no VARCHAR(60) UNIQUE NOT NULL,
    counted_by INT NOT NULL,
    approved_by INT NULL,
    status ENUM('in_progress', 'completed', 'approved', 'cancelled') DEFAULT 'in_progress',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME NULL,
    CONSTRAINT fk_counts_counted FOREIGN KEY (counted_by) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_counts_approved FOREIGN KEY (approved_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_count_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_count_id INT NOT NULL,
    product_id INT NOT NULL,
    system_stock DECIMAL(12,2) NOT NULL,
    physical_stock DECIMAL(12,2) NOT NULL,
    difference DECIMAL(12,2) NOT NULL,
    notes TEXT NULL,
    CONSTRAINT fk_count_items_parent FOREIGN KEY (stock_count_id) REFERENCES stock_counts (id) ON DELETE CASCADE,
    CONSTRAINT fk_count_items_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. KASA & NAKİT/POS HAREKETLERİ (Gün Sonu / Z Raporu Motoru)
CREATE TABLE IF NOT EXISTS cash_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movement_type ENUM('sale_cash', 'sale_card', 'customer_payment', 'refund_cash', 'refund_card', 'expense', 'opening_balance', 'closing_balance') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    reference_id VARCHAR(100) NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cash_created (created_at),
    CONSTRAINT fk_cash_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. DENETİM VE GÜVENLİK GÜNLÜĞÜ (Audit Log)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INT NULL,
    old_values LONGTEXT NULL,
    new_values LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- 15. ÜNİVERSİTELER & KAMPÜSLER (CampusStock Çoklu Kampüs Altyapısı)
CREATE TABLE IF NOT EXISTS universities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    city VARCHAR(80) NOT NULL,
    domain VARCHAR(100) NULL,
    code VARCHAR(50) UNIQUE NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. KAMPÜS KIRTASİYELERİ / MAĞAZALARI
CREATE TABLE IF NOT EXISTS stores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    user_id INT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    phone VARCHAR(30) NULL,
    location_desc VARCHAR(255) NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1,
    opens_at TIME DEFAULT '08:30:00',
    closes_at TIME DEFAULT '19:00:00',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_univ (university_id),
    CONSTRAINT fk_store_univ FOREIGN KEY (university_id) REFERENCES universities (id) ON DELETE CASCADE,
    CONSTRAINT fk_store_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. API ERİŞİM TOKENLARI (Frontend / Mobil İçin Bearer Token)
CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(128) UNIQUE NOT NULL,
    name VARCHAR(100) DEFAULT 'auth_token',
    last_used_at DATETIME NULL,
    expires_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. ÖĞRENCİ ÇEVRİMİÇİ SİPARİŞLERİ (Campus Online Orders)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(60) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    store_id INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    final_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
    payment_method ENUM('online_card', 'cash_pickup', 'card_pickup') DEFAULT 'cash_pickup',
    order_status ENUM('received', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'received',
    pickup_code VARCHAR(10) NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_order_student (student_id),
    INDEX idx_order_store (store_id),
    INDEX idx_order_pickup (pickup_code),
    CONSTRAINT fk_order_student FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_order_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. SİPARİŞ KALEMLERİ
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    INDEX idx_ord_item_ord (order_id),
    CONSTRAINT fk_ord_item_parent FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_ord_item_prod FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. FOTOKOPİ VE BELGE BASKI TALEPLERİ (Print & Copy Jobs)
CREATE TABLE IF NOT EXISTS print_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    store_id INT NOT NULL DEFAULT 1,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NULL,
    page_count INT NOT NULL DEFAULT 1,
    color_mode ENUM('bw', 'color') DEFAULT 'bw',
    double_sided TINYINT(1) DEFAULT 1,
    binding ENUM('none', 'staple', 'spiral') DEFAULT 'none',
    copies INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending', 'printing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    pickup_code VARCHAR(10) NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_print_student (student_id),
    INDEX idx_print_store (store_id),
    CONSTRAINT fk_print_student FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_print_store FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
