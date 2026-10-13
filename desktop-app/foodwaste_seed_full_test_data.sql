-- FoodWaste Relief - Full test data seed (plain passwords)
-- Import this file after schema exists.

SET NAMES utf8mb4;
USE `foodwaste_db`;

-- FK-safe cleanup (TRUNCATE fails on referenced tables in many MySQL setups)
DELETE FROM `donations`;
DELETE FROM `commandes_details`;
DELETE FROM `commandes`;
DELETE FROM `cart_items`;
DELETE FROM `produits`;
DELETE FROM `storage_items`;
DELETE FROM `associations`;
DELETE FROM `business`;
DELETE FROM `users`;

-- USERS (plain passwords for local testing)
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

-- BUSINESSES
INSERT INTO `business` (`id`,`user_id`,`nom_business`,`localisation`,`ville`,`patente`,`status`) VALUES
(1, 2, 'Boulangerie Verte', 'Avenue Habib Bourguiba', 'Tunis',  'PAT-DEMO-001.pdf', 'valide'),
(2, 4, 'FSEGT Cantine',     'Campus FSEGT',           'Tunis',  'PAT-DEMO-002.pdf', 'valide'),
(3, 5, 'Sousse Market',     'Rue de la Corniche',     'Sousse', 'PAT-DEMO-003.pdf', 'en_attente');

-- ASSOCIATIONS
INSERT INTO `associations` (`id`,`user_id`,`nom`,`ville`,`telephone`,`status`) VALUES
(1, 8,  'Nasser Romdhani',   'Tunis',  '22000001', 'valide'),
(2, 9,  'Association Rahma', 'Ariana', '22000002', 'en_attente'),
(3, 10, 'Association Demo',  'Tunis',  '22000003', 'valide');

INSERT INTO `storage_items` (`id`,`business_id`,`item_name`,`quantity`,`unit`,`expiry_date`) VALUES
(1, 1, 'Stock démo boulangerie', 457.00, 'units', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 2, 'Stock démo FSEGT',       158.00, 'kg',    DATE_ADD(CURDATE(), INTERVAL 14 DAY));

-- PRODUCTS (business_id 1 & 2 are validated)
INSERT INTO `produits` (`id`,`business_id`,`storage_item_id`,`nom`,`description`,`categorie`,`prix`,`quantite`,`date_expiration`,`type`) VALUES
(1, 1, 1, 'Panier Viennoiseries', 'Croissants et pains au chocolat invendus du jour', 'Boulangerie', 6.50, 18, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'vente'),
(2, 1, 1, 'Sandwich Poulet',      'Sandwichs prepares le matin',                      'Plats',       4.00, 10, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'vente'),
(3, 1, 1, 'Pain du Jour (Don)',   'Pains restants pour distribution solidaire',       'Boulangerie', 0.00, 15, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'don'),
(4, 2, 2, 'Salade Mixte',         'Salades fraiches en surplus',                       'Plats',       5.50, 12, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'vente'),
(5, 2, 2, 'Plat Riz Poulet',      'Portions pretes a emporter',                        'Plats',       8.00, 8,  DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'vente'),
(6, 2, 2, 'Fruits (Don)',         'Fruits non vendus mais consommables',               'Fruits',      0.00, 20, DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'don');

-- ORDERS
INSERT INTO `commandes` (`id`,`client_id`,`business_id`,`date_commande`,`total`,`status`) VALUES
(1, 3, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 13.00, 'livree'),
(2, 6, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 16.00, 'confirmee'),
(3, 7, 1, DATE_SUB(NOW(), INTERVAL 6 HOUR), 6.50, 'en_attente');

INSERT INTO `commandes_details` (`id`,`commande_id`,`produit_id`,`quantite`,`prix_unitaire`) VALUES
(1, 1, 1, 2, 6.50),
(2, 2, 5, 2, 8.00),
(3, 3, 1, 1, 6.50);

-- DONATIONS
INSERT INTO `donations` (`id`,`produit_id`,`association_id`,`quantite`,`date_donation`,`status`,`storage_item_id`) VALUES
(1, NULL, 3, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), 'Pending', 1),
(2, NULL, 3, 7, DATE_SUB(NOW(), INTERVAL 3 HOUR), 'Pending', 2);

UPDATE `storage_items` SET `quantity` = `quantity` - 5 WHERE `id` = 1;
UPDATE `storage_items` SET `quantity` = `quantity` - 7 WHERE `id` = 2;

-- Optional: keep auto increment clean
ALTER TABLE `users` AUTO_INCREMENT = 10;
ALTER TABLE `business` AUTO_INCREMENT = 4;
ALTER TABLE `associations` AUTO_INCREMENT = 4;
ALTER TABLE `storage_items` AUTO_INCREMENT = 3;
ALTER TABLE `produits` AUTO_INCREMENT = 7;
ALTER TABLE `commandes` AUTO_INCREMENT = 4;
ALTER TABLE `commandes_details` AUTO_INCREMENT = 4;
ALTER TABLE `donations` AUTO_INCREMENT = 3;

-- QUICK LOGIN CREDENTIALS
-- admin.role@fwr.local    / passadmin
-- business.role@fwr.local / passbusiness
-- client.role@fwr.local   / passclient
-- association.role@fwr.local / passassoc
