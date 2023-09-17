CREATE TABLE `reports` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`type` VARCHAR(50) NULL,
	`data` JSON NOT NULL,
	`created_at` DATETIME NULL,
	`updated_at` DATETIME NULL,
	PRIMARY KEY (`id`)
) COLLATE='utf8mb4_bin';
