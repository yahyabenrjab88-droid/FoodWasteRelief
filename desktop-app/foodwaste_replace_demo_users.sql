-- Resets demo users to the three role accounts (plain passwords).
-- Run in phpMyAdmin on database foodwaste_db.

SET NAMES utf8mb4;

USE `foodwaste_db`;

SET FOREIGN_KEY_CHECKS = 0;

DELETE b FROM `business` b
INNER JOIN `users` u ON u.id = b.user_id
WHERE u.email IN (
  'commerce@foodwaste.local',
  'admin@foodwaste.com',
  'business.role@fwr.local',
  'admin@foodwaste.local',
  'admin.role@fwr.local'
);

DELETE FROM `users` WHERE `email` IN (
  'admin@foodwaste.com',
  'admin@foodwaste.local',
  'commerce@foodwaste.local',
  'client@foodwaste.local',
  'admin.role@fwr.local',
  'business.role@fwr.local',
  'client.role@fwr.local'
);

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`email`,`password`,`role`,`nom`,`prenom`,`telephone`,`created_at`) VALUES
('admin.role@fwr.local', 'passadmin', 'admin', 'Admin', 'Système', '20000001', NOW()),
('business.role@fwr.local', 'passbusiness', 'business', 'Ben', 'Commerce', '20000002', NOW()),
('client.role@fwr.local', 'passclient', 'client', 'Sami', 'Client', '20000003', NOW());

INSERT INTO `business` (`user_id`, `nom_business`, `localisation`, `ville`, `patente`, `status`)
SELECT `id`, 'Boulangerie Verte', 'Avenue Habib Bourguiba', 'Tunis', 'PAT-DEMO-001', 'valide'
FROM `users`
WHERE `email` = 'business.role@fwr.local'
LIMIT 1;
