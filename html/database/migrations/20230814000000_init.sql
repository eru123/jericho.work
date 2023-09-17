CREATE TABLE IF NOT EXISTS `cdn` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL DEFAULT '0',
  `user_id` int NOT NULL DEFAULT '0',
  `name` text NOT NULL,
  `mime` varchar(255) NOT NULL,
  `size` bigint NOT NULL,
  `sri` varchar(64) NOT NULL,
  `r2key` text NOT NULL,
  `url` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cdn_parent_id_index` (`parent_id`),
  KEY `cdn_user_id_index` (`user_id`)
) COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `envs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL COMMENT 'For naming ENV key-pair',
  `ekey` varchar(255) DEFAULT NULL,
  `eval` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `envs_user_id_index` (`user_id`)
) COLLATE=utf8mb4_bin ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `mails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT '0',
  `user_id` int DEFAULT '0',
  `sender_id` int DEFAULT '0',
  `message_id` varchar(128) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'transactional',
  `subject` varchar(998) DEFAULT NULL,
  `to` json DEFAULT NULL,
  `cc` json DEFAULT NULL,
  `bcc` json DEFAULT NULL,
  `body` text,
  `attachments` json DEFAULT NULL,
  `priority` int DEFAULT '0',
  `meta` json DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `response` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mails_parent_id_index` (`parent_id`),
  KEY `mails_user_id_index` (`user_id`),
  KEY `mails_message_id_index` (`message_id`),
  KEY `mails_sender_id_index` (`sender_id`)
) COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `mail_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT '0',
  `code` varchar(255) DEFAULT NULL,
  `template` text NOT NULL,
  `active` tinyint(1) DEFAULT '1',
  `default` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mail_templates_user_id_index` (`user_id`),
  KEY `mail_templates_code_index` (`code`)
) COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `smtps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL COMMENT 'User ID of smtp owner',
  `code` varchar(255) DEFAULT NULL COMMENT 'Code to use for smtp account lookup',
  `alias` varchar(255) NOT NULL COMMENT 'Data alias, will be shown instead of Credentials',
  `host` varchar(255) NOT NULL COMMENT 'SMTP Host',
  `from_name` varchar(255) NOT NULL COMMENT 'Sender Name',
  `from_email` varchar(255) NOT NULL COMMENT 'Sender Email',
  `username` varchar(255) NOT NULL COMMENT 'SMTP Username',
  `password` varchar(255) NOT NULL COMMENT 'SMTP Password',
  `secure` varchar(255) DEFAULT 'tls' COMMENT 'Can be tls or ssl',
  `port` varchar(255) DEFAULT '587' COMMENT 'SMTP Port. 587 on STARTTLS/TLS or 465 on SSL/TLS',
  `limit` int DEFAULT (14),
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smtps_user_id_index` (`user_id`),
  KEY `smtps_code_index` (`code`)
) COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `roles` json DEFAULT NULL,
  `alias` varchar(255) DEFAULT 'User',
  `fname` varchar(255) DEFAULT NULL,
  `mname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `pronoun` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `addresses` json DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT '0',
  `emails` json DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `mobile_verified` tinyint(1) DEFAULT '0',
  `mobiles` json DEFAULT NULL,
  `providers` json DEFAULT NULL,
  `default_smtp` int DEFAULT NULL,
  `hash_h` json DEFAULT NULL,
  `user_h` json DEFAULT NULL,
  `alias_h` json DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `locked_at` datetime DEFAULT NULL,
  `disabled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) COLLATE=utf8mb4_bin;

CREATE TABLE IF NOT EXISTS `verifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `identifier` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `hash` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `status` tinyint DEFAULT '0' COMMENT '0 For unused 1 for used',
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verifications_user_id_index` (`user_id`)
) COLLATE=utf8mb4_bin;
