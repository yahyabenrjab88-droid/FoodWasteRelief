-- Sets plain passwords for the three role accounts (if rows already exist).

USE `foodwaste_db`;

UPDATE `users` SET `password` = 'passadmin'   WHERE `email` = 'admin.role@fwr.local';
UPDATE `users` SET `password` = 'passbusiness' WHERE `email` = 'business.role@fwr.local';
UPDATE `users` SET `password` = 'passclient'  WHERE `email` = 'client.role@fwr.local';
