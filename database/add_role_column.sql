ALTER TABLE `users`
ADD COLUMN `role` ENUM('Superadmin', 'Admin', 'Agent') NOT NULL DEFAULT 'Agent';