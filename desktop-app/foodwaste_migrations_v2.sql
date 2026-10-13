-- MySQL migrations for new role/features (ADO.NET architecture compatible)
USE `foodwaste_db`;

ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending';
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

ALTER TABLE business ADD COLUMN IF NOT EXISTS logo_url VARCHAR(500);
ALTER TABLE business ADD COLUMN IF NOT EXISTS description TEXT;
ALTER TABLE business ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

ALTER TABLE produits ADD COLUMN IF NOT EXISTS image_url VARCHAR(500);
ALTER TABLE produits ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1;

ALTER TABLE donations ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending';
ALTER TABLE donations ADD COLUMN IF NOT EXISTS association_id INT;
ALTER TABLE donations ADD COLUMN IF NOT EXISTS storage_item_id INT;
ALTER TABLE donations ADD COLUMN IF NOT EXISTS collected_at DATETIME;

ALTER TABLE commandes ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Pending';

CREATE TABLE IF NOT EXISTS storage_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  item_name VARCHAR(100) NOT NULL,
  quantity DECIMAL(10,2) NOT NULL,
  unit VARCHAR(20),
  expiry_date DATE,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT DEFAULT 1,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cart (client_id, product_id)
);

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  user_role VARCHAR(30),
  username VARCHAR(100),
  action VARCHAR(255),
  details TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT,
  sender_role VARCHAR(30),
  sender_name VARCHAR(100),
  subject VARCHAR(100),
  body TEXT,
  sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_read TINYINT(1) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS associations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  name VARCHAR(100),
  address VARCHAR(255),
  registration_number VARCHAR(50),
  contact_email VARCHAR(100),
  phone VARCHAR(20),
  is_approved TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
