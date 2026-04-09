UPDATE `users` SET `role` = 'Superadmin' WHERE `email` = 'admin@example.com';
UPDATE `users` SET `role` = 'Admin' WHERE `email` LIKE '%@company.com';
UPDATE `users` SET `role` = 'Agent' WHERE `role` IS NULL;