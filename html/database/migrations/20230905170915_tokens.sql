CREATE TABLE `tokens` (
	`token` TEXT NOT NULL COLLATE 'utf8mb4_bin',
	`user_id` INT(10) NULL DEFAULT NULL,
	`type` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`expired_at` DATETIME NULL DEFAULT NULL,
    INDEX `tokens_type_index` (`type`),
	INDEX `tokens_user_id_index` (`user_id`),
	INDEX `tokens_token_index` (`token`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
