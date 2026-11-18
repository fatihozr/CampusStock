SET NAMES utf8mb4;
SET time_zone = '+03:00';

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    permissions TEXT DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    department_id INT NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active',
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id),
    FOREIGN KEY (department_id) REFERENCES departments (id)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    module VARCHAR(50) DEFAULT 'cleaning'
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    barcode VARCHAR(100) UNIQUE,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(255),
    unit VARCHAR(50) DEFAULT 'Adet',
    specifications TEXT,
    description TEXT,
    image_path VARCHAR(255),
    min_stock DECIMAL(10,2) DEFAULT 0.00,
    current_stock DECIMAL(10,2) DEFAULT 0.00,
    unit_price DECIMAL(10,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
    module VARCHAR(50) DEFAULT 'cleaning',
    FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB;

CREATE TABLE procurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_no VARCHAR(100) UNIQUE NOT NULL,
    supplier VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users (id)
) ENGINE=InnoDB;

CREATE TABLE procurement_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    delivered_quantity DECIMAL(10,2) DEFAULT 0.00,
    expected_specifications TEXT,
    FOREIGN KEY (procurement_id) REFERENCES procurements (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    procurement_id INT NOT NULL,
    document_no VARCHAR(100),
    received_by INT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending_approval',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (procurement_id) REFERENCES procurements (id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users (id)
) ENGINE=InnoDB;

CREATE TABLE delivery_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT NOT NULL,
    procurement_item_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    is_compliant VARCHAR(50) DEFAULT 'yes',
    non_compliance_reason TEXT,
    FOREIGN KEY (delivery_id) REFERENCES deliveries (id) ON DELETE CASCADE,
    FOREIGN KEY (procurement_item_id) REFERENCES procurement_items (id)
) ENGINE=InnoDB;

CREATE TABLE stock_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_no VARCHAR(100) UNIQUE NOT NULL,
    department_id INT NOT NULL,
    requested_by INT NOT NULL,
    approved_by INT,
    delivered_to VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    rejection_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME,
    delivered_at DATETIME,
    FOREIGN KEY (department_id) REFERENCES departments (id),
    FOREIGN KEY (requested_by) REFERENCES users (id),
    FOREIGN KEY (approved_by) REFERENCES users (id)
) ENGINE=InnoDB;

CREATE TABLE stock_request_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_request_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    description TEXT,
    FOREIGN KEY (stock_request_id) REFERENCES stock_requests (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'approved',
    quantity DECIMAL(10,2) NOT NULL,
    prev_stock DECIMAL(10,2) NOT NULL,
    next_stock DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    department_id INT,
    stock_request_id INT,
    procurement_id INT,
    performed_by INT NOT NULL,
    approved_by INT,
    rejection_reason TEXT,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME,
    module VARCHAR(50) DEFAULT 'cleaning', 
    delivered_by VARCHAR(255) DEFAULT NULL, 
    received_by VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id),
    FOREIGN KEY (department_id) REFERENCES departments (id),
    FOREIGN KEY (performed_by) REFERENCES users (id),
    FOREIGN KEY (stock_request_id) REFERENCES stock_requests (id) ON DELETE SET NULL,
    FOREIGN KEY (procurement_id) REFERENCES procurements (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE stock_counts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    count_no VARCHAR(100) UNIQUE NOT NULL,
    counted_by INT NOT NULL,
    approved_by INT,
    status VARCHAR(50) DEFAULT 'pending_approval',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME,
    FOREIGN KEY (counted_by) REFERENCES users (id),
    FOREIGN KEY (approved_by) REFERENCES users (id)
) ENGINE=InnoDB;

CREATE TABLE stock_count_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_count_id INT NOT NULL,
    product_id INT NOT NULL,
    system_stock DECIMAL(10,2) NOT NULL,
    physical_stock DECIMAL(10,2) NOT NULL,
    difference DECIMAL(10,2) NOT NULL,
    reason TEXT,
    FOREIGN KEY (stock_count_id) REFERENCES stock_counts (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255) NOT NULL,
    entity_id INT,
    old_values LONGTEXT,
    new_values LONGTEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;
