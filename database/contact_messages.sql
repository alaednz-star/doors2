CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `email`       VARCHAR(180) NOT NULL,
  `phone`       VARCHAR(30)  DEFAULT NULL,
  `subject`     VARCHAR(160) DEFAULT NULL,
  `message`     TEXT NOT NULL,
  `status`      ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `ip`          VARCHAR(45)  DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cm_status`  (`status`),
  KEY `idx_cm_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
