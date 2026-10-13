USE `foodwaste_db`;

CREATE TABLE IF NOT EXISTS `contact_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `sender_role` VARCHAR(30) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `body` TEXT NOT NULL,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_read` TINYINT(1) DEFAULT 0
);

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `prenom` VARCHAR(100) NULL AFTER `nom`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `telephone` VARCHAR(20) NULL AFTER `prenom`;
ALTER TABLE `produits` ADD COLUMN IF NOT EXISTS `categorie` VARCHAR(100) NULL AFTER `description`;

-- Migration mot de passe: valeur hash BCRYPT pour mot de passe temporaire "ChangeMe123!"
UPDATE `users`
SET `password` = '$2y$10$Yv8wzQGxJX9CUXxQ9X3hAewCA5xS4Q2xQ6L/2A4nq8l.U6ycbEJmS'
WHERE `password` NOT LIKE '$2y$%';

CREATE INDEX `idx_users_email` ON `users` (`email`);
CREATE INDEX `idx_users_role` ON `users` (`role`);
CREATE INDEX `idx_business_status` ON `business` (`status`);
CREATE INDEX `idx_produits_type` ON `produits` (`type`);
CREATE INDEX `idx_produits_exp` ON `produits` (`date_expiration`);
CREATE INDEX `idx_commandes_status` ON `commandes` (`status`);
CREATE INDEX `idx_donations_status` ON `donations` (`status`);
CREATE INDEX `idx_contact_is_read` ON `contact_requests` (`is_read`);
