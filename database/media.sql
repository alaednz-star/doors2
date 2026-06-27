USE `door_showroom`;

CREATE TABLE IF NOT EXISTS `media` (
    `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `filename`      VARCHAR(260)     NOT NULL,
    `original_name` VARCHAR(260)     NOT NULL DEFAULT '',
    `mime_type`     VARCHAR(80)      NOT NULL DEFAULT '',
    `file_size`     INT UNSIGNED     NOT NULL DEFAULT 0,
    `width`         SMALLINT UNSIGNED         DEFAULT NULL,
    `height`        SMALLINT UNSIGNED         DEFAULT NULL,
    `alt_text`      VARCHAR(500)              DEFAULT NULL,
    `entity_type`   ENUM('product','collection','color','finish') DEFAULT NULL,
    `entity_id`     INT UNSIGNED              DEFAULT NULL,
    `uploaded_by`   INT UNSIGNED              DEFAULT NULL,
    `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
