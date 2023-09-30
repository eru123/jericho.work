CREATE TABLE `newsletter` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(255) NULL DEFAULT NULL,
	`subscriptions` JSON NULL DEFAULT NULL,
	`verified` TINYINT(3) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`update_at` DATETIME NULL DEFAULT NULL,
	`deleted_at` DATETIME NULL DEFAULT NULL,
	`disabled_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE,
	UNIQUE INDEX `email` (`email`)
) COLLATE='utf8mb4_bin';