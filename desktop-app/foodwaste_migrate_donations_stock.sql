-- Run once on an existing DB created before stock-based donations.
-- Donations may reference produit_id only; new app expects nullable produit_id + optional storage_item_id.

SET NAMES utf8mb4;
USE `foodwaste_db`;

-- Drop FK to allow NULL produit_id (name may vary; ignore error if already migrated)
ALTER TABLE `donations` DROP FOREIGN KEY `fk_donations_produit`;

ALTER TABLE `donations`
  MODIFY `produit_id` INT NULL;

ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_produit` FOREIGN KEY (`produit_id`) REFERENCES `produits`(`id`);

-- Storage link (ignore duplicate constraint error if already present)
ALTER TABLE `donations`
  ADD CONSTRAINT `fk_donations_storage` FOREIGN KEY (`storage_item_id`) REFERENCES `storage_items`(`id`);
