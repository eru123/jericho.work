CREATE TABLE `reports` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`user_id` INT NOT NULL DEFAULT 0,
	`type` VARCHAR(50) NULL,
	`data` JSON NOT NULL,
	`created_at` DATETIME NULL,
	PRIMARY KEY (`id`),
	KEY `reports_user_id_index` (`user_id`),
	KEY `reports_type_index` (`type`)
) COLLATE='utf8mb4_bin';
