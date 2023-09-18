CREATE TABLE `tokens` (
	`token` VARCHAR(512) NOT NULL,
	`user_id` INT(10) NULL DEFAULT NULL,
	`type` VARCHAR(50) NULL DEFAULT NULL,
	`expired_at` DATETIME NULL DEFAULT NULL,
	INDEX `tokens_type_index` (`type`),
	INDEX `tokens_user_id_index` (`user_id`),
	INDEX `tokens_token_index` (`token`)
) COLLATE=utf8mb4_bin;
