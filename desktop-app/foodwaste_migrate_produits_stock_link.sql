-- Run once on an existing `foodwaste_db` (phpMyAdmin) if you already imported an older schema.
USE `foodwaste_db`;

ALTER TABLE `produits` ADD COLUMN `storage_item_id` INT NULL AFTER `business_id`;
ALTER TABLE `produits` ADD COLUMN `categorie` VARCHAR(80) NULL AFTER `description`;
