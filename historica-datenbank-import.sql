SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

/*M!999999\- enable the sandbox mode */ 
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_08_03_205735_add_is_admin_to_users_table',1),
(5,'2026_08_03_205735_create_categories_table',1),
(6,'2026_08_03_205735_create_locations_table',1),
(7,'2026_08_03_205735_create_people_table',1),
(8,'2026_08_03_205735_create_photos_table',1),
(9,'2026_08_03_205736_create_contact_messages_table',1),
(10,'2026_08_03_205736_create_membership_applications_table',1),
(11,'2026_08_03_205736_create_photo_person_tags_table',1),
(12,'2026_08_03_205736_create_site_pages_table',1),
(13,'2026_08_04_081220_add_thumbnail_and_index_to_photos_table',1);
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `users` VALUES
(1,'Webmaster','webmaster@historica-deing.de','2026-08-04 08:26:30','$2y$12$mvKKxfWdXsx.qCSzCKuVBeWOEXcj2ulEmNZIlIxYAEGh22Qw08K1y',1,NULL,'2026-08-04 08:26:30','2026-08-04 08:26:30');
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `categories` VALUES
(1,'Ortsansichten','ortsansichten','Historische Ansichten von Teugn und seinen Ortsteilen.',1,'2026-08-04 08:26:30','2026-08-04 08:26:30'),
(2,'Vereine','vereine','Fotos rund um das Vereinsleben in Teugn.',2,'2026-08-04 08:26:30','2026-08-04 08:26:30'),
(3,'Landwirtschaft','landwirtschaft','Landwirtschaft und dörfliches Arbeitsleben.',3,'2026-08-04 08:26:30','2026-08-04 08:26:30'),
(4,'Personen & Familien','personen-familien','Portraits und Familienfotos.',4,'2026-08-04 08:26:30','2026-08-04 08:26:30'),
(5,'Feste & Feiern','feste-feiern','Kirchweih, Umzüge und andere Feierlichkeiten.',5,'2026-08-04 08:26:30','2026-08-04 08:26:30');
DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `latitude` decimal(9,6) DEFAULT NULL,
  `longitude` decimal(9,6) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_name_unique` (`name`),
  UNIQUE KEY `locations_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `people` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `maiden_name` varchar(255) DEFAULT NULL,
  `birth_year` smallint(5) unsigned DEFAULT NULL,
  `death_year` smallint(5) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `location_id` bigint(20) unsigned DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `date_text` varchar(100) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `inventory_number` varchar(50) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photos_slug_unique` (`slug`),
  KEY `photos_category_id_foreign` (`category_id`),
  KEY `photos_location_id_foreign` (`location_id`),
  KEY `photos_uploaded_by_foreign` (`uploaded_by`),
  KEY `photos_is_published_created_at_index` (`is_published`,`created_at`),
  CONSTRAINT `photos_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `photos_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `photos_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `photo_person_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `photo_person_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` bigint(20) unsigned NOT NULL,
  `person_id` bigint(20) unsigned NOT NULL,
  `x_percent` decimal(5,2) DEFAULT NULL,
  `y_percent` decimal(5,2) DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'approved',
  `suggested_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `photo_person_tags_photo_id_person_id_unique` (`photo_id`,`person_id`),
  KEY `photo_person_tags_person_id_foreign` (`person_id`),
  KEY `photo_person_tags_suggested_by_foreign` (`suggested_by`),
  KEY `photo_person_tags_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `photo_person_tags_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `photo_person_tags_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `photo_person_tags_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `photo_person_tags_suggested_by_foreign` FOREIGN KEY (`suggested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `site_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `site_pages` VALUES
(1,'impressum','Impressum','<p>Historica Deing e.V.<br>Vorsitzende(r): [Name einfügen]<br>Teugn</p><p>E-Mail: info@historica-deing.de</p><p>Vertreten durch den Vorstand gemäß § 26 BGB. Vereinsregister: [Registergericht, Registernummer einfügen].</p><p><em>Dieser Text kann im Verwaltungsbereich unter „Seiten“ bearbeitet werden.</em></p>',NULL,'2026-08-04 08:26:30'),
(2,'datenschutz','Datenschutz','<p>Der Schutz Ihrer personenbezogenen Daten ist uns wichtig. Informationen zur Verarbeitung Ihrer Daten (z. B. bei Nutzung des Kontaktformulars oder bei der Registrierung) finden Sie hier.</p><p><em>Dieser Text kann im Verwaltungsbereich unter „Seiten“ bearbeitet werden.</em></p>',NULL,'2026-08-04 08:26:30'),
(3,'satzung','Satzung','<p>Die Satzung des Historica Deing e.V. regelt Zweck, Organisation und Mitgliedschaft des Vereins.</p><p><em>Bitte laden Sie im Verwaltungsbereich die aktuelle Satzung als PDF hoch, damit sie hier zum Download angeboten wird.</em></p>',NULL,'2026-08-04 08:26:30'),
(4,'aufnahmeantrag','Aufnahmeantrag','<p>Wir freuen uns über Ihr Interesse an einer Mitgliedschaft bei Historica Deing e.V. Sie können den Aufnahmeantrag online ausfüllen oder als PDF herunterladen und postalisch einreichen.</p>',NULL,'2026-08-04 08:26:30');
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `handled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `membership_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `street` varchar(200) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `city` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `message` text DEFAULT NULL,
  `handled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;
