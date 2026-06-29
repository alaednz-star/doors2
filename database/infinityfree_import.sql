
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `occurred_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`admin_user_id`,`occurred_at`),
  KEY `idx_action` (`action`),
  KEY `idx_date` (`occurred_at`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 13:27:10'),(2,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 17:16:40'),(3,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 19:56:49'),(4,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 20:54:24'),(5,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 22:54:39'),(6,2,'logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:11:51'),(7,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:11:56'),(8,2,'logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:12:06'),(9,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:13:29'),(10,2,'logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:14:45'),(11,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:14:50'),(12,2,'logout','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:15:27'),(13,2,'login.success','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36',NULL,'2026-06-27 23:15:30');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_login_count` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'Super Admin','admin@showroom.dz','$2y$12$dGF74vrgSq565d0DKy4XuuUYZz5KnOgIorWw.Hzy0GxjHdV1J6lcu','super_admin',1,0,NULL,NULL,NULL,'2026-06-27 13:22:26','2026-06-27 13:22:26'),(2,'Alaeddine','alaeddine@gmail.com','$2y$12$gGkEu7FUvqpTIBzeDzBy8OZg1nHWajnC1.mwCtjkR7NGizvc/Txk2','admin',1,0,NULL,'2026-06-27 23:15:30','::1','2026-06-27 13:26:46','2026-06-27 23:15:30'),(3,'ADK Admin','admin@adk.site.je','$2y$12$8kHvMcxleIQmTdUdCH7.cOVCE6AjYWROOFarBqweU50Fz/1XPqRxS','super_admin',1,0,NULL,NULL,NULL,'2026-06-28 00:00:00','2026-06-28 00:00:00');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_slug` (`slug`),
  KEY `idx_categories_active_order` (`is_active`,`display_order`),
  KEY `idx_categories_created_by` (`created_by`),
  CONSTRAINT `fk_categories_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `collection_room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collection_room_types` (
  `collection_id` int(10) unsigned NOT NULL,
  `room_type_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`collection_id`,`room_type_id`),
  KEY `idx_crt_room` (`room_type_id`),
  CONSTRAINT `fk_crt_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crt_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `collection_room_types` WRITE;
/*!40000 ALTER TABLE `collection_room_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `collection_room_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `image_filename` varchar(260) DEFAULT NULL,
  `hero_image` varchar(260) DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_collections_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
INSERT INTO `collections` VALUES (1,'Heritage','heritage','Timeless designs drawing from classical architectural traditions.','47eff6905cb0bbbd31e14db7ff2508fd.png',NULL,1,1,'2026-06-27 13:22:28','2026-06-27 20:03:55'),(2,'Moderne','moderne','Clean lines and minimalist forms for contemporary spaces.','09f6e53d14b172d571527550c186c722.png',NULL,2,1,'2026-06-27 13:22:28','2026-06-27 20:54:44'),(3,'Prestige','prestige','Ultra-premium bespoke doors for landmark projects.',NULL,NULL,3,1,'2026-06-27 13:22:28','2026-06-27 13:22:28');
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collection_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `hex` char(7) DEFAULT NULL COMMENT 'e.g. #2C2C2C',
  `description` text DEFAULT NULL,
  `image_filename` varchar(260) DEFAULT NULL,
  `texture_filename` varchar(260) DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_colors_collection_name` (`collection_id`,`name`),
  KEY `idx_colors_collection` (`collection_id`),
  CONSTRAINT `fk_colors_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `colors` WRITE;
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` VALUES (1,3,'Marron Prestige','#5A3A24',NULL,NULL,'swatch-marron-prestige.jpg',1,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(2,3,'Gris Prestige','#6E6E6E',NULL,NULL,'swatch-gris-prestige.jpg',2,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(3,2,'Scuro','#2E2622',NULL,NULL,'swatch-scuro.jpg',1,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(4,2,'Simza','#9A9389',NULL,NULL,'swatch-simza.jpg',2,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(5,2,'Madera','#7A4E2D',NULL,NULL,'swatch-madera.jpg',3,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(6,2,'Wengue','#3B2314',NULL,NULL,'swatch-wengue.jpg',4,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(7,2,'Serya','#C9B79C',NULL,NULL,'swatch-serya.jpg',5,1,'2026-06-27 13:23:57','2026-06-27 20:09:47'),(8,1,'Chêne','#B98E54',NULL,NULL,'swatch-chene.jpg',1,1,'2026-06-27 13:23:57','2026-06-27 20:11:12'),(9,1,'Gris','#8A8A8A',NULL,NULL,'swatch-gris.jpg',2,1,'2026-06-27 13:23:57','2026-06-27 20:09:47');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `construction_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `construction_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image_filename` varchar(260) DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ct_slug` (`slug`),
  KEY `idx_ct_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `construction_types` WRITE;
/*!40000 ALTER TABLE `construction_types` DISABLE KEYS */;
INSERT INTO `construction_types` VALUES (1,'Nédabaile','nedabaile',NULL,'20d9704ab320bca8d099d020743663c1.png',1,1,'2026-06-27 13:22:34','2026-06-27 17:18:25'),(2,'Tebelaire','tebelaire',NULL,NULL,2,1,'2026-06-27 13:22:34','2026-06-27 13:22:34');
/*!40000 ALTER TABLE `construction_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(180) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(160) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cm_status` (`status`),
  KEY `idx_cm_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `door_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `door_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image_filename` varchar(260) DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_door_types_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `door_types` WRITE;
/*!40000 ALTER TABLE `door_types` DISABLE KEYS */;
INSERT INTO `door_types` VALUES (1,'Chambre','chambre',NULL,'eaeb60be121f940897565f9282230b9b.png',1,1,'2026-06-27 13:23:57'),(2,'Sanitaire','sanitaire',NULL,NULL,2,1,'2026-06-27 13:23:57'),(3,'Salon','salon',NULL,NULL,3,1,'2026-06-27 13:23:57'),(4,'Porte d\'Entrée','porte-entree',NULL,NULL,4,1,'2026-06-27 13:23:57');
/*!40000 ALTER TABLE `door_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_materials_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `materials` WRITE;
/*!40000 ALTER TABLE `materials` DISABLE KEYS */;
INSERT INTO `materials` VALUES (1,'Solid Oak',1,1,'2026-06-27 13:22:28'),(2,'Walnut',2,1,'2026-06-27 13:22:28'),(3,'Steel',3,1,'2026-06-27 13:22:28'),(4,'Aluminium',4,1,'2026-06-27 13:22:28'),(5,'Glass',5,1,'2026-06-27 13:22:28'),(6,'Composite',6,1,'2026-06-27 13:22:28');
/*!40000 ALTER TABLE `materials` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(260) NOT NULL,
  `original_name` varchar(260) NOT NULL DEFAULT '',
  `mime_type` varchar(80) NOT NULL DEFAULT '',
  `file_size` int(10) unsigned NOT NULL DEFAULT 0,
  `width` smallint(5) unsigned DEFAULT NULL,
  `height` smallint(5) unsigned DEFAULT NULL,
  `alt_text` varchar(500) DEFAULT NULL,
  `entity_type` enum('product','collection','color') DEFAULT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `optional_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `optional_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_features_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `optional_features` WRITE;
/*!40000 ALTER TABLE `optional_features` DISABLE KEYS */;
INSERT INTO `optional_features` VALUES (1,'Smart Lock','smart-lock','Electronic smart lock with app control',180.00,'fixed',1,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(2,'Double Glazing','double-glazing','Thermal double-glazed glass panel',220.00,'fixed',2,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(3,'Acoustic Seals','acoustic-seals','Sound-dampening perimeter seals',95.00,'fixed',3,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(4,'Security Bar','security-bar','Reinforced security bar kit',140.00,'fixed',4,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(5,'Soft Close','soft-close','Hydraulic soft-close hinge set',60.00,'fixed',5,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(6,'Custom RAL Color','custom-ral','Any RAL color, matched to spec',150.00,'fixed',6,1,'2026-06-27 13:22:29','2026-06-27 13:22:29'),(7,'Premium Finish','premium-finish','Upgrade to premium surface treatment',10.00,'percent',7,1,'2026-06-27 13:22:29','2026-06-27 13:22:29');
/*!40000 ALTER TABLE `optional_features` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `price_calculations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_calculations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_token` char(64) NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `material_id` int(10) unsigned DEFAULT NULL,
  `color_id` int(10) unsigned DEFAULT NULL,
  `door_type_id` int(10) unsigned DEFAULT NULL,
  `width_mm` smallint(5) unsigned DEFAULT NULL,
  `height_mm` smallint(5) unsigned DEFAULT NULL,
  `features_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_json`)),
  `rules_applied` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rules_applied`)),
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `options_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'DZD',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_calc_token` (`session_token`),
  KEY `idx_calc_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `price_calculations` WRITE;
/*!40000 ALTER TABLE `price_calculations` DISABLE KEYS */;
/*!40000 ALTER TABLE `price_calculations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `price_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `price_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `dimension_type` enum('fixed','per_sqm','per_lm','reference_scaled') NOT NULL DEFAULT 'reference_scaled',
  `product_id` int(10) unsigned DEFAULT NULL COMMENT 'NULL = applies to all',
  `material_id` int(10) unsigned DEFAULT NULL,
  `color_id` int(10) unsigned DEFAULT NULL,
  `door_type_id` int(10) unsigned DEFAULT NULL,
  `construction_type_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `collection_id` int(10) unsigned DEFAULT NULL,
  `width_min_mm` smallint(5) unsigned DEFAULT NULL,
  `width_max_mm` smallint(5) unsigned DEFAULT NULL,
  `height_min_mm` smallint(5) unsigned DEFAULT NULL,
  `height_max_mm` smallint(5) unsigned DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_modifier` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Additive amount',
  `multiplier` decimal(6,4) NOT NULL DEFAULT 1.0000 COMMENT 'Multiplicative factor',
  `priority` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Higher wins when rules overlap',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pr_product` (`product_id`),
  KEY `idx_pr_material` (`material_id`),
  KEY `idx_pr_color` (`color_id`),
  KEY `idx_pr_door_type` (`door_type_id`),
  KEY `idx_pr_priority` (`priority`),
  KEY `idx_pr_active` (`is_active`),
  KEY `fk_pr_category` (`category_id`),
  KEY `fk_pr_created_by` (`created_by`),
  KEY `idx_pr_construction` (`construction_type_id`),
  CONSTRAINT `fk_pr_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_construction` FOREIGN KEY (`construction_type_id`) REFERENCES `construction_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_door_type` FOREIGN KEY (`door_type_id`) REFERENCES `door_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pr_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `price_rules` WRITE;
/*!40000 ALTER TABLE `price_rules` DISABLE KEYS */;
INSERT INTO `price_rules` VALUES (1,'Prestige · Chambre · Nédabaile','reference_scaled',NULL,NULL,NULL,1,1,NULL,3,NULL,NULL,NULL,NULL,44000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(2,'Prestige · Chambre · Tebelaire','reference_scaled',NULL,NULL,NULL,1,2,NULL,3,NULL,NULL,NULL,NULL,49000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(3,'Prestige · Sanitaire · Nédabaile','reference_scaled',NULL,NULL,NULL,2,1,NULL,3,NULL,NULL,NULL,NULL,46000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(4,'Prestige · Sanitaire · Tebelaire','reference_scaled',NULL,NULL,NULL,2,2,NULL,3,NULL,NULL,NULL,NULL,51000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(5,'Prestige · Porte d\'Entrée · Nédabaile','reference_scaled',NULL,NULL,NULL,4,1,NULL,3,NULL,NULL,NULL,NULL,0.00,0.0000,1.0000,100,1,0,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(6,'Prestige · Porte d\'Entrée · Tebelaire','reference_scaled',NULL,NULL,NULL,4,2,NULL,3,NULL,NULL,NULL,NULL,65000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(7,'Moderne · Chambre · Nédabaile','reference_scaled',NULL,NULL,NULL,1,1,NULL,2,NULL,NULL,NULL,NULL,34000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(8,'Moderne · Chambre · Tebelaire','reference_scaled',NULL,NULL,NULL,1,2,NULL,2,NULL,NULL,NULL,NULL,39000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(9,'Moderne · Sanitaire · Nédabaile','reference_scaled',NULL,NULL,NULL,2,1,NULL,2,NULL,NULL,NULL,NULL,36000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(10,'Moderne · Sanitaire · Tebelaire','reference_scaled',NULL,NULL,NULL,2,2,NULL,2,NULL,NULL,NULL,NULL,41000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(11,'Moderne · Porte d\'Entrée · Nédabaile','reference_scaled',NULL,NULL,NULL,4,1,NULL,2,NULL,NULL,NULL,NULL,0.00,0.0000,1.0000,100,1,0,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(12,'Moderne · Porte d\'Entrée · Tebelaire','reference_scaled',NULL,NULL,NULL,4,2,NULL,2,NULL,NULL,NULL,NULL,54000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(13,'Heritage · Chambre · Nédabaile','reference_scaled',NULL,NULL,NULL,1,1,NULL,1,NULL,NULL,NULL,NULL,38000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(14,'Heritage · Chambre · Tebelaire','reference_scaled',NULL,NULL,NULL,1,2,NULL,1,NULL,NULL,NULL,NULL,43000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(15,'Heritage · Sanitaire · Nédabaile','reference_scaled',NULL,NULL,NULL,2,1,NULL,1,NULL,NULL,NULL,NULL,40000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(16,'Heritage · Sanitaire · Tebelaire','reference_scaled',NULL,NULL,NULL,2,2,NULL,1,NULL,NULL,NULL,NULL,45000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(17,'Heritage · Porte d\'Entrée · Nédabaile','reference_scaled',NULL,NULL,NULL,4,1,NULL,1,NULL,NULL,NULL,NULL,0.00,0.0000,1.0000,100,1,0,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57'),(18,'Heritage · Porte d\'Entrée · Tebelaire','reference_scaled',NULL,NULL,NULL,4,2,NULL,1,NULL,NULL,NULL,NULL,58000.00,0.0000,1.0000,100,1,1,NULL,NULL,NULL,'2026-06-27 13:23:57','2026-06-27 13:23:57');
/*!40000 ALTER TABLE `price_rules` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_colors` (
  `product_id` int(10) unsigned NOT NULL,
  `color_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`color_id`),
  KEY `fk_pc_color` (`color_id`),
  CONSTRAINT `fk_pc_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_colors` WRITE;
/*!40000 ALTER TABLE `product_colors` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_colors` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `filename` varchar(260) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pimages_product` (`product_id`),
  KEY `idx_pimages_cover` (`product_id`,`is_cover`),
  CONSTRAINT `fk_pimages_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'909df000fe0e68a524611a23c1b08ca7.png',NULL,0,0,'2026-06-27 20:12:44');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_materials` (
  `product_id` int(10) unsigned NOT NULL,
  `material_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`material_id`),
  KEY `fk_pm_material` (`material_id`),
  CONSTRAINT `fk_pm_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pm_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_materials` WRITE;
/*!40000 ALTER TABLE `product_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_materials` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `dimensions` varchar(120) DEFAULT NULL COMMENT 'e.g. W900 × H2100 mm',
  `width_mm` int(10) unsigned DEFAULT NULL,
  `height_mm` int(10) unsigned DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `category_id` int(10) unsigned DEFAULT NULL,
  `collection_id` int(10) unsigned DEFAULT NULL,
  `color_id` int(10) unsigned DEFAULT NULL,
  `door_type_id` int(10) unsigned DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `construction_type_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_products_slug` (`slug`),
  UNIQUE KEY `uk_products_sku` (`sku`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_collection` (`collection_id`),
  KEY `idx_products_active_order` (`is_active`,`display_order`),
  KEY `idx_products_featured` (`is_featured`),
  KEY `fk_products_created_by` (`created_by`),
  KEY `fk_products_construction` (`construction_type_id`),
  KEY `fk_products_color` (`color_id`),
  KEY `fk_products_doortype` (`door_type_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_construction` FOREIGN KEY (`construction_type_id`) REFERENCES `construction_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_doortype` FOREIGN KEY (`door_type_id`) REFERENCES `door_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Heritage Chêne · Chambre · Nédabaile','heritage-chne-chambre-ndabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 20:12:44'),(2,'Heritage Chêne · Chambre · Tebelaire','heritage-chene-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,1,38000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(3,'Heritage Chêne · Sanitaire · Nédabaile','heritage-chene-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,2,35500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(4,'Heritage Chêne · Sanitaire · Tebelaire','heritage-chene-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,2,39500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(5,'Heritage Chêne · Salon · Nédabaile','heritage-chene-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,3,54000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(6,'Heritage Chêne · Salon · Tebelaire','heritage-chene-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,3,58000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(7,'Heritage Chêne · Porte d\'Entrée · Tebelaire','heritage-chene-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,4,54000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(8,'Heritage Gris · Chambre · Nédabaile','heritage-gris-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(9,'Heritage Gris · Chambre · Tebelaire','heritage-gris-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,1,38000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(10,'Heritage Gris · Sanitaire · Nédabaile','heritage-gris-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,2,35500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(11,'Heritage Gris · Sanitaire · Tebelaire','heritage-gris-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,2,39500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(12,'Heritage Gris · Salon · Nédabaile','heritage-gris-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,3,54000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(13,'Heritage Gris · Salon · Tebelaire','heritage-gris-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,3,58000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(14,'Heritage Gris · Porte d\'Entrée · Tebelaire','heritage-gris-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,1,NULL,4,54000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(15,'Moderne Scuro · Chambre · Nédabaile','moderne-scuro-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(16,'Moderne Scuro · Chambre · Tebelaire','moderne-scuro-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,40000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(17,'Moderne Scuro · Sanitaire · Nédabaile','moderne-scuro-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,33500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(18,'Moderne Scuro · Sanitaire · Tebelaire','moderne-scuro-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,42500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(19,'Moderne Scuro · Salon · Nédabaile','moderne-scuro-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,57000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(20,'Moderne Scuro · Salon · Tebelaire','moderne-scuro-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,60000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(21,'Moderne Scuro · Porte d\'Entrée · Tebelaire','moderne-scuro-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,4,56000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(22,'Moderne Simza · Chambre · Nédabaile','moderne-simza-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(23,'Moderne Simza · Chambre · Tebelaire','moderne-simza-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,40000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(24,'Moderne Simza · Sanitaire · Nédabaile','moderne-simza-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,33500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(25,'Moderne Simza · Sanitaire · Tebelaire','moderne-simza-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,42500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(26,'Moderne Simza · Salon · Nédabaile','moderne-simza-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,57000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(27,'Moderne Simza · Salon · Tebelaire','moderne-simza-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,60000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(28,'Moderne Simza · Porte d\'Entrée · Tebelaire','moderne-simza-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,4,56000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(29,'Moderne Madera · Chambre · Nédabaile','moderne-madera-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(30,'Moderne Madera · Chambre · Tebelaire','moderne-madera-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,40000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(31,'Moderne Madera · Sanitaire · Nédabaile','moderne-madera-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,33500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(32,'Moderne Madera · Sanitaire · Tebelaire','moderne-madera-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,42500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(33,'Moderne Madera · Salon · Nédabaile','moderne-madera-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,57000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(34,'Moderne Madera · Salon · Tebelaire','moderne-madera-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,60000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(35,'Moderne Madera · Porte d\'Entrée · Tebelaire','moderne-madera-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,4,56000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(36,'Moderne Wengue · Chambre · Nédabaile','moderne-wengue-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(37,'Moderne Wengue · Chambre · Tebelaire','moderne-wengue-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,40000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(38,'Moderne Wengue · Sanitaire · Nédabaile','moderne-wengue-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,33500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(39,'Moderne Wengue · Sanitaire · Tebelaire','moderne-wengue-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,42500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(40,'Moderne Wengue · Salon · Nédabaile','moderne-wengue-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,57000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(41,'Moderne Wengue · Salon · Tebelaire','moderne-wengue-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,60000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(42,'Moderne Wengue · Porte d\'Entrée · Tebelaire','moderne-wengue-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,4,56000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(43,'Moderne Serya · Chambre · Nédabaile','moderne-serya-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,34000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(44,'Moderne Serya · Chambre · Tebelaire','moderne-serya-chambre-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,1,40000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(45,'Moderne Serya · Sanitaire · Nédabaile','moderne-serya-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,33500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(46,'Moderne Serya · Sanitaire · Tebelaire','moderne-serya-sanitaire-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,2,42500.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(47,'Moderne Serya · Salon · Nédabaile','moderne-serya-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,57000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(48,'Moderne Serya · Salon · Tebelaire','moderne-serya-salon-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,3,60000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(49,'Moderne Serya · Porte d\'Entrée · Tebelaire','moderne-serya-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,2,NULL,4,56000.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(50,'Prestige Marron · Chambre · Nédabaile','prestige-marron-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,1,44000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(51,'Prestige Marron · Sanitaire · Nédabaile','prestige-marron-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,2,41500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(52,'Prestige Marron · Salon · Nédabaile','prestige-marron-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,3,58000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(53,'Prestige Marron · Porte d\'Entrée · Tebelaire','prestige-marron-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,4,0.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(54,'Prestige Gris · Chambre · Nédabaile','prestige-gris-chambre-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,1,44000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(55,'Prestige Gris · Sanitaire · Nédabaile','prestige-gris-sanitaire-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,2,41500.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(56,'Prestige Gris · Salon · Nédabaile','prestige-gris-salon-nedabaile',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,3,58000.00,1,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30'),(57,'Prestige Gris · Porte d\'Entrée · Tebelaire','prestige-gris-porte-entree-tebelaire',NULL,NULL,NULL,900,2100,0,1,0,NULL,3,NULL,4,0.00,2,NULL,'2026-06-27 13:22:38','2026-06-27 13:31:30');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quote_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quote_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_email` varchar(180) DEFAULT NULL,
  `customer_company` varchar(160) DEFAULT NULL,
  `customer_country` varchar(100) DEFAULT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `project_type` enum('residential','commercial','hospitality','architectural') DEFAULT NULL,
  `install_date` date DEFAULT NULL,
  `quantity` smallint(5) unsigned NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `collection_id` int(10) unsigned DEFAULT NULL,
  `material_id` int(10) unsigned DEFAULT NULL,
  `color_id` int(10) unsigned DEFAULT NULL,
  `door_type_id` int(10) unsigned DEFAULT NULL,
  `room_type_id` int(10) unsigned DEFAULT NULL,
  `width_mm` smallint(5) unsigned DEFAULT NULL,
  `height_mm` smallint(5) unsigned DEFAULT NULL,
  `handle` varchar(120) DEFAULT NULL,
  `features_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_json`)),
  `config_hash` char(40) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'DZD',
  `status` enum('new','contacted','quotation_sent','in_progress','confirmed','completed','cancelled') NOT NULL DEFAULT 'new',
  `status_notes` text DEFAULT NULL,
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_qr_reference` (`reference`),
  KEY `idx_qr_status` (`status`),
  KEY `idx_qr_submitted` (`submitted_at`),
  KEY `idx_qr_customer_phone` (`customer_phone`),
  KEY `idx_qr_assigned` (`assigned_to`),
  KEY `fk_qr_product` (`product_id`),
  KEY `fk_qr_material` (`material_id`),
  KEY `fk_qr_color` (`color_id`),
  KEY `fk_qr_door_type` (`door_type_id`),
  KEY `idx_qr_room` (`room_type_id`),
  KEY `idx_qr_collection` (`collection_id`),
  KEY `idx_qr_dedup` (`customer_email`,`config_hash`,`submitted_at`),
  CONSTRAINT `fk_qr_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_collection` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_color` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_door_type` FOREIGN KEY (`door_type_id`) REFERENCES `door_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_material` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qr_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quote_requests` WRITE;
/*!40000 ALTER TABLE `quote_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `quote_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quote_status_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quote_status_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_qsl_quote` (`quote_id`),
  KEY `idx_qsl_changed` (`changed_at`),
  KEY `fk_qsl_changed_by` (`changed_by`),
  CONSTRAINT `fk_qsl_changed_by` FOREIGN KEY (`changed_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qsl_quote` FOREIGN KEY (`quote_id`) REFERENCES `quote_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quote_status_log` WRITE;
/*!40000 ALTER TABLE `quote_status_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `quote_status_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL DEFAULT 1,
  `window_start` datetime NOT NULL,
  `blocked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`key`),
  KEY `idx_blocked_until` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remember_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(10) unsigned NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token_hash` (`token_hash`),
  KEY `idx_user` (`admin_user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_rt_user` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `remember_tokens` WRITE;
/*!40000 ALTER TABLE `remember_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_room_slug` (`slug`),
  KEY `idx_room_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `room_types` WRITE;
/*!40000 ALTER TABLE `room_types` DISABLE KEYS */;
INSERT INTO `room_types` VALUES (1,'Bedroom Door','bedroom',NULL,1,1,'2026-06-27 13:22:31'),(2,'Bathroom Door','bathroom',NULL,2,1,'2026-06-27 13:22:31'),(3,'Kitchen Door','kitchen',NULL,3,1,'2026-06-27 13:22:31'),(4,'Living Room Door','living-room',NULL,4,1,'2026-06-27 13:22:31'),(5,'Office Door','office',NULL,5,1,'2026-06-27 13:22:31'),(6,'Entrance Door','entrance',NULL,6,1,'2026-06-27 13:22:31'),(7,'Commercial Door','commercial',NULL,7,1,'2026-06-27 13:22:31'),(8,'Other','other',NULL,8,1,'2026-06-27 13:22:31');
/*!40000 ALTER TABLE `room_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `saved_configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_configurations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` char(64) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `config_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config_json`)),
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'DZD',
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_saved_configs_token` (`token`),
  KEY `idx_sc_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `saved_configurations` WRITE;
/*!40000 ALTER TABLE `saved_configurations` DISABLE KEYS */;
INSERT INTO `saved_configurations` VALUES (1,'8a3feb9157810f7006cd916948dd4a5be0068e6320c3834963df17354599e96a','Moderne Serya Door','{\"collection_id\":2,\"color_id\":7,\"door_type_id\":2,\"construction_type_id\":1,\"width_mm\":900,\"height_mm\":2070}',35485.71,'DZD','::1','2026-06-27 17:06:24','2026-07-27 17:06:24'),(2,'891841a1bcc63180153a076ac8d1ca85e66c92cb372ec6c1ca2ef2a5f0395ce1','Heritage Gris Door','{\"collection_id\":1,\"color_id\":9,\"door_type_id\":2,\"construction_type_id\":2,\"width_mm\":900,\"height_mm\":2100}',45000.00,'DZD','::1','2026-06-27 20:11:50','2026-07-27 20:11:50');
/*!40000 ALTER TABLE `saved_configurations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL DEFAULT '',
  `label` varchar(200) NOT NULL DEFAULT '',
  `group_name` varchar(50) NOT NULL DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','PORTES','Site Name','general'),(2,'contact_email','contact@portes.dz','Contact Email','general'),(3,'contact_phone','','Contact Phone','general'),(4,'contact_address','','Address','general'),(5,'notification_email','admin@portes.dz','Notification Email','notifications'),(6,'quote_email_notify','0','Email on New Quote','notifications'),(7,'pricing_ref_width_mm','900','Reference Width (mm)','pricing'),(8,'pricing_ref_height_mm','2100','Reference Height (mm)','pricing'),(9,'vat_percent','0','VAT Percent','advanced'),(10,'maintenance_mode','0','Maintenance Mode','advanced');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

