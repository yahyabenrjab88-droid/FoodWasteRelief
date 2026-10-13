-- FoodWasteRelief — One-shot import (schema + demo data)
-- Import this single file in phpMyAdmin to create and populate `foodwaste_db`.

-- ------------------------------------------------------------
-- SCHEMA (from `foodwaste_db.sql`)
-- ------------------------------------------------------------

DROP DATABASE IF EXISTS `foodwaste_db`;
CREATE DATABASE `foodwaste_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `foodwaste_db`;

SET NAMES utf8mb4;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(30) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `telephone` VARCHAR(30) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Pending',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `business` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nom_business` VARCHAR(150) NOT NULL,
  `localisation` VARCHAR(255) NOT NULL,
  `ville` VARCHAR(100) NOT NULL,
  `patente` VARCHAR(255) NOT NULL,
  `status` VARCHAR(30) DEFAULT 'en_attente',
  `logo_url` VARCHAR(500),
  `description` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_business_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE `associations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `nom` VARCHAR(150) NOT NULL,
  `ville` VARCHAR(100) NOT NULL,
  `telephone` VARCHAR(30),
  `status` VARCHAR(30) DEFAULT 'en_attente',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_associations_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE `storage_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT NOT NULL,
  `item_name` VARCHAR(100) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(20),
  `expiry_date` DATE,
  `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_storage_items_business` FOREIGN KEY (`business_id`) REFERENCES `business`(`id`)
);

CREATE TABLE `produits` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT NOT NULL,
  `storage_item_id` INT NULL,
  `nom` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `categorie` VARCHAR(80) NULL,
  `prix` DECIMAL(10,2) NOT NULL,
  `quantite` INT NOT NULL DEFAULT 0,
  `date_expiration` DATETIME NOT NULL,
  `type` VARCHAR(20) NOT NULL DEFAULT 'vente',
  `image_url` VARCHAR(500),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_produits_business` FOREIGN KEY (`business_id`) REFERENCES `business`(`id`)
);

CREATE TABLE `commandes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `business_id` INT NOT NULL,
  `date_commande` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(30) DEFAULT 'en_attente',
  CONSTRAINT `fk_commandes_client` FOREIGN KEY (`client_id`) REFERENCES `users`(`id`),
  CONSTRAINT `fk_commandes_business` FOREIGN KEY (`business_id`) REFERENCES `business`(`id`)
);

CREATE TABLE `commandes_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `commande_id` INT NOT NULL,
  `produit_id` INT NOT NULL,
  `quantite` INT NOT NULL,
  `prix_unitaire` DECIMAL(10,2) NOT NULL,
  CONSTRAINT `fk_commandes_details_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commandes_details_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`)
);

CREATE TABLE `donations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `produit_id` INT NULL,
  `association_id` INT NOT NULL,
  `quantite` INT NOT NULL,
  `date_donation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` VARCHAR(20) DEFAULT 'Pending',
  `storage_item_id` INT NULL,
  `collected_at` DATETIME NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_donations_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`),
  CONSTRAINT `fk_donations_association` FOREIGN KEY (`association_id`) REFERENCES `associations`(`id`),
  CONSTRAINT `fk_donations_storage` FOREIGN KEY (`storage_item_id`) REFERENCES `storage_items`(`id`)
);

CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `added_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_cart` (`client_id`, `product_id`),
  CONSTRAINT `fk_cart_items_client` FOREIGN KEY (`client_id`) REFERENCES `users`(`id`),
  CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `produits`(`id`)
);

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `user_role` VARCHAR(30),
  `username` VARCHAR(100),
  `action` VARCHAR(255),
  `details` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `contact_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT,
  `sender_role` VARCHAR(30),
  `sender_name` VARCHAR(100),
  `subject` VARCHAR(100),
  `body` TEXT,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_read` TINYINT(1) DEFAULT 0
);

-- ------------------------------------------------------------
-- DEMO DATA (from `foodwaste_seed_full_test_data.sql`)
-- ------------------------------------------------------------

USE `foodwaste_db`;

DELETE FROM `donations`;
DELETE FROM `commandes_details`;
DELETE FROM `commandes`;
DELETE FROM `cart_items`;
DELETE FROM `produits`;
DELETE FROM `storage_items`;
DELETE FROM `associations`;
DELETE FROM `business`;
DELETE FROM `users`;

INSERT INTO `users` (`id`,`email`,`password`,`role`,`nom`,`prenom`,`telephone`,`created_at`) VALUES
(1, 'admin.role@fwr.local',    'passadmin',    'admin',    'Admin',     'Systeme',   '20000001', NOW()),
(2, 'business.role@fwr.local', 'passbusiness', 'business', 'Ben',       'Commerce',  '20000002', NOW()),
(3, 'client.role@fwr.local',   'passclient',   'client',   'Sami',      'Client',    '20000003', NOW()),
(10,'association.role@fwr.local','passassoc',  'association','Association','Demo',    '20000010', NOW()),
(4, 'fsegt@fwr.local',         'passbusiness', 'business', 'Ben Amor',  'Moez',      '20000004', NOW()),
(5, 'bakery@fwr.local',        'passbusiness', 'business', 'Trabelsi',  'Nour',      '20000005', NOW()),
(6, 'client2@fwr.local',       'passclient',   'client',   'Hammami',   'Salma',     '20000006', NOW()),
(7, 'client3@fwr.local',       'passclient',   'client',   'Mansouri',  'Karim',     '20000007', NOW()),
(8, 'assoc.user1@fwr.local',   'passclient',   'client',   'Romdhani',  'Nasser',    '20000008', NOW()),
(9, 'assoc.user2@fwr.local',   'passclient',   'client',   'Brahmi',    'Ines',      '20000009', NOW());

INSERT INTO `business` (`id`,`user_id`,`nom_business`,`localisation`,`ville`,`patente`,`status`) VALUES
(1, 2, 'Boulangerie Verte', 'Avenue Habib Bourguiba', 'Tunis',  'PAT-DEMO-001.pdf', 'valide'),
(2, 4, 'FSEGT Cantine',     'Campus FSEGT',           'Tunis',  'PAT-DEMO-002.pdf', 'valide'),
(3, 5, 'Sousse Market',     'Rue de la Corniche',     'Sousse', 'PAT-DEMO-003.pdf', 'en_attente');

INSERT INTO `associations` (`id`,`user_id`,`nom`,`ville`,`telephone`,`status`) VALUES
(1, 8,  'Nasser Romdhani',   'Tunis',  '22000001', 'valide'),
(2, 9,  'Association Rahma', 'Ariana', '22000002', 'en_attente'),
(3, 10, 'Association Demo',  'Tunis',  '22000003', 'valide');

INSERT INTO `storage_items` (`id`,`business_id`,`item_name`,`quantity`,`unit`,`expiry_date`) VALUES
(1, 1, 'Stock démo boulangerie', 457.00, 'units', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 2, 'Stock démo FSEGT',       158.00, 'kg',    DATE_ADD(CURDATE(), INTERVAL 14 DAY));

INSERT INTO `produits` (`id`,`business_id`,`storage_item_id`,`nom`,`description`,`categorie`,`prix`,`quantite`,`date_expiration`,`type`) VALUES
(1, 1, 1, 'Panier Viennoiseries', 'Croissants et pains au chocolat invendus du jour', 'Boulangerie', 6.50, 18, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'vente'),
(2, 1, 1, 'Sandwich Poulet',      'Sandwichs prepares le matin',                      'Plats',       4.00, 10, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'vente'),
(3, 1, 1, 'Pain du Jour (Don)',   'Pains restants pour distribution solidaire',       'Boulangerie', 0.00, 15, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'don'),
(4, 2, 2, 'Salade Mixte',         'Salades fraiches en surplus',                       'Plats',       5.50, 12, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'vente'),
(5, 2, 2, 'Plat Riz Poulet',      'Portions pretes a emporter',                        'Plats',       8.00, 8,  DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'vente'),
(6, 2, 2, 'Fruits (Don)',         'Fruits non vendus mais consommables',               'Fruits',      0.00, 20, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'don');

INSERT INTO `commandes` (`id`,`client_id`,`business_id`,`date_commande`,`total`,`status`) VALUES
(1, 3, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 13.00, 'livree'),
(2, 6, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 16.00, 'confirmee'),
(3, 7, 1, DATE_SUB(NOW(), INTERVAL 6 HOUR), 6.50, 'en_attente');

INSERT INTO `commandes_details` (`id`,`commande_id`,`produit_id`,`quantite`,`prix_unitaire`) VALUES
(1, 1, 1, 2, 6.50),
(2, 2, 5, 2, 8.00),
(3, 3, 1, 1, 6.50);

INSERT INTO `donations` (`id`,`produit_id`,`association_id`,`quantite`,`date_donation`,`status`,`storage_item_id`) VALUES
(1, NULL, 3, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), 'Pending', 1),
(2, NULL, 3, 7, DATE_SUB(NOW(), INTERVAL 3 HOUR), 'Pending', 2);

UPDATE `storage_items` SET `quantity` = `quantity` - 5 WHERE `id` = 1;
UPDATE `storage_items` SET `quantity` = `quantity` - 7 WHERE `id` = 2;

ALTER TABLE `users` AUTO_INCREMENT = 10;
ALTER TABLE `business` AUTO_INCREMENT = 4;
ALTER TABLE `associations` AUTO_INCREMENT = 4;
ALTER TABLE `storage_items` AUTO_INCREMENT = 3;
ALTER TABLE `produits` AUTO_INCREMENT = 7;
ALTER TABLE `commandes` AUTO_INCREMENT = 4;
ALTER TABLE `commandes_details` AUTO_INCREMENT = 4;
ALTER TABLE `donations` AUTO_INCREMENT = 3;
