-- MySQL dump 10.13  Distrib 8.0.41, for Linux (x86_64)
--
-- Host: localhost    Database: tenant68
-- ------------------------------------------------------
-- Server version	8.0.41-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `annotation_videos`
--

DROP TABLE IF EXISTS `annotation_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annotation_videos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructor_id` bigint unsigned NOT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `annotation_videos_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `annotation_videos_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `annotation_videos`
--

LOCK TABLES `annotation_videos` WRITE;
/*!40000 ALTER TABLE `annotation_videos` DISABLE KEYS */;
INSERT INTO `annotation_videos` VALUES (1,'c43877cd-25c7-4eea-9fc0-8d5c6e5c79f0',2,'AnnotationVideos/dWRAjlc9jS7kvp8hXPYXpbOZVllCe8cVjEfdNUkp.mp4','2025-04-10 15:16:21','2025-04-10 15:16:36');
/*!40000 ALTER TABLE `annotation_videos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ch_favorites`
--

DROP TABLE IF EXISTS `ch_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ch_favorites` (
  `id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `favorite_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ch_favorites`
--

LOCK TABLES `ch_favorites` WRITE;
/*!40000 ALTER TABLE `ch_favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `ch_favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ch_messages`
--

DROP TABLE IF EXISTS `ch_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ch_messages` (
  `id` bigint NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_id` bigint NOT NULL,
  `to_id` bigint NOT NULL,
  `body` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ch_messages`
--

LOCK TABLES `ch_messages` WRITE;
/*!40000 ALTER TABLE `ch_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `ch_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` double(8,2) NOT NULL DEFAULT '0.00',
  `limit` int NOT NULL DEFAULT '0',
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_genrators`
--

DROP TABLE IF EXISTS `document_genrators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_genrators` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '1',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_log_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_log_json` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_genrators`
--

LOCK TABLES `document_genrators` WRITE;
/*!40000 ALTER TABLE `document_genrators` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_genrators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_menus`
--

DROP TABLE IF EXISTS `document_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `json` text COLLATE utf8mb4_unicode_ci,
  `document_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint DEFAULT NULL,
  `position` int DEFAULT '0',
  `html` longtext COLLATE utf8mb4_unicode_ci,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_menus`
--

LOCK TABLES `document_menus` WRITE;
/*!40000 ALTER TABLE `document_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `user` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expo_token`
--

DROP TABLE IF EXISTS `expo_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expo_token` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expo_token_student_id_foreign` (`student_id`),
  KEY `expo_token_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `expo_token_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expo_token_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expo_token`
--

LOCK TABLES `expo_token` WRITE;
/*!40000 ALTER TABLE `expo_token` DISABLE KEYS */;
INSERT INTO `expo_token` VALUES (1,2,NULL,'ExponentPushToken[GbzaVaKAfqbbfj4AMSnCGY]'),(2,NULL,3,'ExponentPushToken[oycvfPFjvX0akByDbbJIG1]'),(3,NULL,5,'ExponentPushToken[Jykaj1MVkPHB2ye8CiMkvN]'),(4,NULL,6,'ExponentPushToken[cY5YJ6AvMrWTVXU4YJRH3X]'),(5,NULL,8,'ExponentPushToken[UPI-4JBhJxIJ83UJTKdofS]');
/*!40000 ALTER TABLE `expo_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quetion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'How do I sign up for a tenant account?','To sign up for a tenant account, click the Sign Up button on the homepage, fill out the required information, and follow the on-screen instructions.','1','2025-03-15 21:37:13','2025-03-15 21:37:13'),(2,'What types of subscription plans do you offer?','We offer a range of subscription plans to accommodate various needs. Our plans include Basic, Pro, and Enterprise tiers, each with different features and pricing.','2','2025-03-15 21:37:13','2025-03-15 21:37:13'),(3,'How can I customize the appearance of my dashboard?','To customize your dashboard, navigate to the \"Settings\" section in your dashboard.','3','2025-03-15 21:37:13','2025-03-15 21:37:13'),(4,'What is Full Tenancy Laravel Saas?','Full Tenancy Laravel Saas is a software-as-a-service (SaaS) solution built on the Laravel framework. It is designed to provide a multi-tenant architecture for Laravel applications, allowing you to easily create and manage multiple independent instances of your application within a single codebase.','4','2025-03-15 21:37:13','2025-03-15 21:37:13');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feedback_content`
--

DROP TABLE IF EXISTS `feedback_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback_content` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_video_id` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'video',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `feedback_content_purchase_video_id_foreign` (`purchase_video_id`),
  CONSTRAINT `feedback_content_purchase_video_id_foreign` FOREIGN KEY (`purchase_video_id`) REFERENCES `purchasevideos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feedback_content`
--

LOCK TABLES `feedback_content` WRITE;
/*!40000 ALTER TABLE `feedback_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `feedback_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follows`
--

DROP TABLE IF EXISTS `follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `follows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint unsigned NOT NULL,
  `isPaid` tinyint(1) NOT NULL,
  `active_status` tinyint(1) DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `follows_student_id_index` (`student_id`),
  CONSTRAINT `follows_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follows`
--

LOCK TABLES `follows` WRITE;
/*!40000 ALTER TABLE `follows` DISABLE KEYS */;
INSERT INTO `follows` VALUES (1,2,0,1,NULL,NULL,'2025-04-04 00:21:41','2025-04-04 00:21:41',3),(2,2,0,1,NULL,NULL,'2025-04-05 17:05:46','2025-04-05 17:05:46',5),(3,2,0,1,NULL,NULL,'2025-04-05 18:01:56','2025-04-05 18:01:56',6);
/*!40000 ALTER TABLE `follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `footer_settings`
--

DROP TABLE IF EXISTS `footer_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `footer_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `page_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `footer_settings`
--

LOCK TABLES `footer_settings` WRITE;
/*!40000 ALTER TABLE `footer_settings` DISABLE KEYS */;
INSERT INTO `footer_settings` VALUES (1,'Company','company',0,NULL,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(2,'Product','product',0,NULL,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(3,'Download','download',0,NULL,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(4,'Support','support',0,NULL,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(5,'About Us','about-us',1,1,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(6,'Our Team','our-team',1,2,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(7,'Products','products',1,3,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(8,'Contact','contact',1,4,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(9,'Feature','feature',2,5,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(10,'Pricing','pricing',2,6,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(11,'Credit','Credit',2,7,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(12,'News','news',2,8,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(13,'iOS','ios',3,9,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(14,'Android','android',3,10,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(15,'Microsoft','microsoft',3,11,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(16,'Desktop','desktop',3,12,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(17,'Help','help',4,13,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(18,'Terms','terms',4,14,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(19,'FAQ','fAQ',4,15,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(20,'Privacy','privacy',4,16,'2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `footer_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `header_settings`
--

DROP TABLE IF EXISTS `header_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `header_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `header_settings`
--

LOCK TABLES `header_settings` WRITE;
/*!40000 ALTER TABLE `header_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `header_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instructors`
--

DROP TABLE IF EXISTS `instructors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instructors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_status` tinyint(1) NOT NULL DEFAULT '1',
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` text COLLATE utf8mb4_unicode_ci,
  `dp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instructors_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instructors`
--

LOCK TABLES `instructors` WRITE;
/*!40000 ALTER TABLE `instructors` DISABLE KEYS */;
/*!40000 ALTER TABLE `instructors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lessons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesson_price` decimal(8,2) NOT NULL,
  `lesson_duration` double DEFAULT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lesson_quantity` int unsigned NOT NULL,
  `required_time` int unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `active_status` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('inPerson','online') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  `is_package_lesson` tinyint(1) NOT NULL DEFAULT '0',
  `payment_method` enum('online','cash','both') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `max_students` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `lessons_created_by_foreign` (`created_by`),
  CONSTRAINT `lessons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,'1 Hour Private Lesson','One Hour Private Lesson',100.00,1,'68',1,0,2,1,'inPerson',0,'online','2025-03-15 21:52:11','2025-03-15 21:52:11',1),(2,'2 Hour Private Lesson','2 Hour Private Lesson',200.00,2,'68',1,0,2,1,'inPerson',0,'online','2025-03-15 21:53:07','2025-03-15 21:53:07',1),(3,'9 Hole Playing Lesson','9 Hole Playing Lesson',250.00,2,'68',1,0,2,1,'inPerson',0,'online','2025-03-15 21:53:51','2025-03-15 21:53:51',1),(4,'18 Hole Playing Lesson','18 Hole Playing Lesson',300.00,3,'68',1,0,2,1,'inPerson',0,'online','2025-03-15 21:54:38','2025-03-15 21:54:38',1),(5,'Online Swing Analysis','Online Swing Analysis - Please submit a video to receive feedback',50.00,NULL,'68',1,1,2,1,'online',0,'online','2025-03-15 21:55:51','2025-03-15 21:55:51',1);
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `like_posts`
--

DROP TABLE IF EXISTS `like_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `like_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `like_posts_student_id_foreign` (`student_id`),
  KEY `like_posts_instructor_id_foreign` (`instructor_id`),
  KEY `like_posts_post_id_foreign` (`post_id`),
  CONSTRAINT `like_posts_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `like_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  CONSTRAINT `like_posts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `like_posts`
--

LOCK TABLES `like_posts` WRITE;
/*!40000 ALTER TABLE `like_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `like_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_securities`
--

DROP TABLE IF EXISTS `login_securities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_securities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `google2fa_enable` tinyint(1) NOT NULL DEFAULT '0',
  `google2fa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_securities`
--

LOCK TABLES `login_securities` WRITE;
/*!40000 ALTER TABLE `login_securities` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_securities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_templates`
--

DROP TABLE IF EXISTS `mail_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mailable` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci,
  `html_template` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `text_template` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_templates`
--

LOCK TABLES `mail_templates` WRITE;
/*!40000 ALTER TABLE `mail_templates` DISABLE KEYS */;
INSERT INTO `mail_templates` VALUES (1,'App\\Mail\\Admin\\PurchaseCreated','Purchase created for {{ name }}','<p>Hello, {{ name }}, a purchase has been created for {{ammount}} against your account.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(2,'App\\Mail\\Admin\\StudentPaymentLink','Purchase created for {{ name }}','<p>Hi,  {{ name }},  I hope you enjoyed your lesson with {{instructorName}}. Please click on the link to complete the payment process : {{ link }}.</p>','Hi,  {{ name }},  I hope you enjoyed your lesson with {{instructorName}}. Please click on the link to complete the payment process : {{ link }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(3,'App\\Mail\\Admin\\VideoAdded','Video added by {{student_name}}.','<p>Hello, {{ name }}, <br> {{student_name}} added video for their purchase for your lesson. Please click the link below to add feedback {{link}}.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(4,'App\\Mail\\Admin\\PurchaseCreatedInsructor','Purchase created for lesson id : {{ id }}','<p>Hello, {{ name }}, a purchase has been created for {{ammount}} for your lesson id : {{id}}.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(5,'App\\Mail\\Admin\\PurchaseFeedback','Feedback for your purchase is added by the instructor','<p>Hello, {{ name }}, feedback for your purchase {{id}} has has been added by the instructor.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(6,'App\\Mail\\Admin\\PurchaseCompleted','Feedback for your purchase is completed','<p>Hello, {{ name }}, Payment was successful against your purchase : {{id}}, please proceed to add videos to recieve feedback by the instructor via app or portal.<br>Regards, Tuneup Management.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(7,'App\\Mail\\Admin\\WelcomeMail','Welcome, {{ name }}','<p>Hello, {{ name }}. We are excited to welcome you to TuneUp! Our platform is designed to help you streamline your operation, grow your brand, and maximize your earnings. You can login with your credentials at : {{ link }} with your password : {{password}}.</p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(8,'App\\Mail\\Admin\\WelcomeMailStudent','Welcome, {{ name }}','<p>Hello, {{ name }}. Welcome to tuneup. You can login in  with your password : {{password}} at {{link}} </p>','Hello, {{ name }}.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(9,'App\\Mail\\Admin\\TestMail','Mail send for testing purpose','<p><strong>This Mail For Testing</strong></p>\n            <p><strong>Thanks</strong></p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(10,'App\\Mail\\Admin\\ApproveOfflineMail','Offline Payment Request Verified','<p><strong>Hi&nbsp;&nbsp;{{ name }}</strong></p>\n            <p><strong>Your Plan Update Request {{ email }}&nbsp;is Verified By Super Admin</strong></p>\n            <p><strong>Please Check</strong></p>\n            <p>&nbsp;</p>\n            <p>&nbsp;</p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(11,'App\\Mail\\Admin\\OfflineMail','Offline Payment Request Unverified','<p><strong>Hi&nbsp;{{ name }}</strong></p>\n            <p><strong>Your Request Payment {{ email }}&nbsp;Is Disapprove By Super Admin</strong></p>\n            <p><strong>Because&nbsp;{{ disapprove_reason }}</strong></p>\n            <p><strong>Please Contact to Super Admin</strong></p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(12,'App\\Mail\\Admin\\ConatctMail','New Enquiry Details','<p><strong>Name : {{name}}</strong></p>\n            <p><strong>Email : </strong><strong>{{email}}</strong></p>\n            <p><strong>Contact No : {{ contact_no }}&nbsp;</strong></p>\n            <p><strong>Message :&nbsp;</strong><strong>{{ message }}</strong></p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(13,'App\\Mail\\Admin\\PasswordResets','Reset Password Notification','<p><strong>Hello!</strong></p><p>You are receiving this email because we received a password reset request for your account. To proceed with the password reset please click on the link below:</p><p><a href=\"{{url}}\">Reset Password</a></p><p>This password reset link will expire in 60 minutes.&nbsp;<br>If you did not request a password reset, no further action is required.</p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(14,'App\\Mail\\Admin\\RegisterMail','Register Mail','<p><strong>Hi {{name}}</strong></p>\n            <p><strong>Email {{email}}</strong></p>\n            <p><strong>Register Successfully</strong></p>',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `mail_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2017_08_24_000000_create_settings_table',1),(4,'2018_10_10_000000_create_mail_templates_table',1),(5,'2019_08_19_000000_create_failed_jobs_table',1),(6,'2019_09_22_192348_create_messages_table',1),(7,'2019_10_16_211433_create_favorites_table',1),(8,'2019_10_18_223259_add_avatar_to_users',1),(9,'2019_10_20_211056_add_messenger_color_to_users',1),(10,'2019_10_22_000539_add_dark_mode_to_users',1),(11,'2019_10_25_214038_add_active_status_to_users',1),(12,'2019_12_14_000001_create_personal_access_tokens_table',1),(13,'2020_05_15_000010_create_tenant_user_impersonation_tokens_table',1),(14,'2021_08_21_034558_create_posts_table',1),(15,'2021_08_25_050952_add_lang_field_in_users_table',1),(16,'2021_08_31_085000_create_permission_tables',1),(17,'2021_09_03_102633_create_modules_table',1),(18,'2021_09_07_111604_create_login_securities_table',1),(19,'2021_09_22_104844_create_plans_table',1),(20,'2021_09_29_044829_add_plan_id_to_users_table',1),(21,'2021_09_29_113922_create_orders_table',1),(22,'2022_01_20_090330_create_categories_table',1),(23,'2022_03_17_063246_add_slug',1),(24,'2022_05_12_035508_add_paymet_type',1),(25,'2022_05_13_101414_create_offline_requests_table',1),(26,'2022_05_17_070120_social_type',1),(27,'2022_05_17_070800_create_social_logins_table',1),(28,'2022_07_19_095632_add_column_to_users_table',1),(29,'2022_08_15_110649_add_active_status_to_plans_table',1),(30,'2022_10_07_120249_create_faqs_table',1),(31,'2022_11_30_055340_create_coupons_table',1),(32,'2022_11_30_072240_create_user_coupons_table',1),(33,'2022_12_02_040120_add_coupon_id_to_offline_requests_table',1),(34,'2022_12_15_103653_add_coupon_code_to_orders_table',1),(35,'2023_01_10_043003_add_plan_description_to_plans_table',1),(36,'2023_04_21_050339_create_notifications_table',1),(37,'2023_05_17_051236_create_testimonials_table',1),(38,'2023_05_19_040211_create_events_table',1),(39,'2023_05_31_054722_create_notifications_settings_table',1),(40,'2023_05_31_060920_create_sms_templates_table',1),(41,'2023_05_31_101129_create_user_codes_table',1),(42,'2023_07_21_040100_create_document_genrators_table',1),(43,'2023_07_21_040220_create_document_menus_table',1),(44,'2023_08_29_040231_add_created_by_to_posts_table',1),(45,'2023_09_01_063412_create_footer_settings_table',1),(46,'2023_09_01_064016_create_page_settings_table',1),(47,'2023_09_01_102340_create_header_settings_table',1),(48,'2023_09_15_035749_add_dark_layout_to_users_table',1),(49,'2023_12_05_131757_create_users_logo_column',1),(50,'2023_12_11_095137_create_instructors_table',1),(51,'2023_12_14_105501_create_students_table',1),(52,'2023_12_15_083348_create_lessons_table',1),(53,'2023_12_15_130717_create_purchases_table',1),(54,'2023_12_15_131309_create_purchasevideos_table',1),(55,'2023_12_19_171649_create_payment_table',1),(56,'2023_12_28_213717_create_follows_table',1),(57,'2024_01_01_103524_create_posts_table',1),(58,'2024_03_26_171541_add_note_to_purchase_videos',1),(59,'2024_03_27_094122_add_statusto_follow',1),(60,'2024_03_28_115655_addsub_price_to_user',1),(61,'2024_03_28_153407_add_session_id_to_follow',1),(62,'2024_03_28_154646_add_stripe_customer_id_to_students_table',1),(63,'2024_04_01_121843_add_sub_idto_follows',1),(64,'2024_05_02_091723_edit_pk_to_cascade_delete',1),(65,'2024_05_02_092710_cascade_delete_on_student_id',1),(66,'2024_05_13_100955_create_purchase_post_table',1),(67,'2024_07_05_102457_remove_fields_from_lessons_table',1),(68,'2024_07_05_142244_add_feedback_complete_flag_to_purchases',1),(69,'2024_07_05_142754_add_feedback_complete_flag_to_purchase_videos',1),(70,'2024_07_11_100719_add_golf_course_to_users',1),(71,'2024_07_31_143640_add_active_status_to_lessons',1),(72,'2024_08_04_115042_create_like_posts_table',1),(73,'2024_08_07_103300_add_thumbnail_to_purchase_videos\'',1),(74,'2024_08_08_082037_add_social_url_to_users',1),(75,'2024_08_08_082444_add_social_url_to_users',1),(76,'2024_08_09_153438_change_experience_to_float',1),(77,'2024_08_19_125045_add_type_to_lessons',1),(78,'2024_08_20_094250_add_slots_table',1),(79,'2024_08_26_082832_remove_detaield_description_from_lesson',1),(80,'2024_08_28_102401_add_lesson_duration_to_lessons',1),(81,'2024_08_30_145841_remove_short_description_from_post',1),(82,'2024_09_17_110734_add_video_2_url_to_purchasevideos',1),(83,'2024_09_23_091532_make_expo_token_table',1),(84,'2024_11_03_130915_create_report_posts_table',1),(85,'2024_11_11_142706_create_report_user_table',1),(86,'2024_11_12_111628_\'add_cancelled_to_slot\'',1),(87,'2024_11_14_151059_add_tenant_id_to_slots',1),(88,'2024_11_21_071319_create_reviews_table',1),(89,'2024_11_21_124257_add_avg_rate_to_users;',1),(90,'2024_11_22_101052_add_stripe_account_id_to_users',1),(91,'2024_11_28_091231_\'add_is_stripe_connected_to_users\'',1),(92,'2024_12_05_121215_add_feedback_videos_to_purchasevideos',1),(93,'2024_12_05_130306_add_feedback_as_text_to_purchasevideos',1),(94,'2024_12_12_122418_remove_feedback_from_purchasevideos',1),(95,'2024_12_18_094556_add_feedback_video_table',1),(96,'2024_12_18_103949_remove_feedback_url_from_purchasevideos',1),(97,'2024_12_18_212826_add_feedback_to_purchasevideos',1),(98,'2024_12_20_140408_add_uuid_to_users',1),(99,'2024_12_24_130809_create_annotation_videos',1),(100,'2025_01_03_122634_add_optional_slot_id_in_purchases',1),(101,'2025_01_09_201952_add_type_to_students',1),(102,'2025_01_28_072507_create_student_slots_table',1),(103,'2025_01_28_075638_add_max_students_to_lessons_table',1),(104,'2025_01_28_091532_remove_student_id_from_slots',1),(105,'2025_02_03_063426_add_is_friend_boolean_in_student_slots',1),(106,'2025_03_10_175406_add_package_lesson_to_lessons',1),(107,'2025_03_11_213700_add_friend_names_to_purchases_table',1),(108,'2025_03_14_115623_add_country_field_to_users',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (3,'App\\Models\\Student',1),(1,'App\\Models\\User',1),(3,'App\\Models\\Student',2),(2,'App\\Models\\User',2),(3,'App\\Models\\Student',3),(3,'App\\Models\\Student',4),(3,'App\\Models\\Student',5),(3,'App\\Models\\Student',6),(3,'App\\Models\\Student',7),(3,'App\\Models\\Student',8);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'role','2025-03-15 21:37:10','2025-03-15 21:37:10'),(2,'user','2025-03-15 21:37:10','2025-03-15 21:37:10'),(3,'instructors','2025-03-15 21:37:10','2025-03-15 21:37:10'),(4,'students','2025-03-15 21:37:10','2025-03-15 21:37:10'),(5,'lessons','2025-03-15 21:37:10','2025-03-15 21:37:10'),(6,'purchases','2025-03-15 21:37:10','2025-03-15 21:37:10'),(7,'setting','2025-03-15 21:37:10','2025-03-15 21:37:10'),(8,'transaction','2025-03-15 21:37:10','2025-03-15 21:37:10'),(9,'landingpage','2025-03-15 21:37:10','2025-03-15 21:37:10'),(10,'chat','2025-03-15 21:37:10','2025-03-15 21:37:10'),(11,'plan','2025-03-15 21:37:10','2025-03-15 21:37:10'),(12,'blog','2025-03-15 21:37:11','2025-03-15 21:37:11'),(13,'category','2025-03-15 21:37:11','2025-03-15 21:37:11'),(14,'email-template','2025-03-15 21:37:11','2025-03-15 21:37:11'),(15,'sms-template','2025-03-15 21:37:11','2025-03-15 21:37:11'),(16,'testimonial','2025-03-15 21:37:11','2025-03-15 21:37:11'),(17,'event','2025-03-15 21:37:11','2025-03-15 21:37:11'),(18,'faqs','2025-03-15 21:37:11','2025-03-15 21:37:11'),(19,'coupon','2025-03-15 21:37:11','2025-03-15 21:37:11'),(20,'document','2025-03-15 21:37:11','2025-03-15 21:37:11'),(21,'page-setting','2025-03-15 21:37:11','2025-03-15 21:37:11'),(22,'support-ticket','2025-03-15 21:37:11','2025-03-15 21:37:11');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications_settings`
--

DROP TABLE IF EXISTS `notifications_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_notification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '1-On 0-Off',
  `sms_notification` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '1-On 0-Off',
  `notify` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '1-On 0-Off',
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications_settings`
--

LOCK TABLES `notifications_settings` WRITE;
/*!40000 ALTER TABLE `notifications_settings` DISABLE KEYS */;
INSERT INTO `notifications_settings` VALUES (1,'Testing Purpose','1','0','0',2,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(2,'Register Mail','1','2','1',1,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(3,'New Enquiry Details','1','2','1',2,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(4,'Send Ticket Reply','1','2','1',2,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(5,'Offline Payment Request Verified','1','2','1',2,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(6,'Offline Payment Request Unverified','1','2','1',2,'2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `notifications_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offline_requests`
--

DROP TABLE IF EXISTS `offline_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `offline_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_id` bigint NOT NULL,
  `order_id` bigint NOT NULL,
  `coupon_id` bigint DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `status` smallint NOT NULL DEFAULT '0',
  `disapprove_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offline_requests`
--

LOCK TABLES `offline_requests` WRITE;
/*!40000 ALTER TABLE `offline_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `offline_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint NOT NULL,
  `plan_id` bigint NOT NULL,
  `amount` double(8,2) NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` double(8,2) DEFAULT NULL,
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` smallint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_settings`
--

DROP TABLE IF EXISTS `page_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `friendly_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_settings`
--

LOCK TABLES `page_settings` WRITE;
/*!40000 ALTER TABLE `page_settings` DISABLE KEYS */;
INSERT INTO `page_settings` VALUES (1,'About Us','desc',NULL,NULL,NULL,'At Full Tenancy Laravel Admin Saas, we understand the importance of data privacy and security. Thats why we offer robust privacy settings\n            to ensure the protection of your sensitive information. Here&#39;s how our privacy settings work:\\r\\n\\r\\n\\r\\n\\r\\nData Encryption: We employ industry-standard\n            encryption protocols to safeguard your data during transit and storage. Your form submissions and user information are encrypted, making it extremely difficult\n            for unauthorized parties to access or tamper with the data.\\r\\n\\r\\n\\r\\nUser Consent Management: Our privacy settings include options for managing user consents.\n            You can configure your forms to include consent checkboxes for users to agree to your data handling practices. This helps you ensure compliance with privacy\n            regulations and builds trust with your users.\\r\\n\\r\\n\\r\\nData Retention Controls: Take control of how long you retain user data with our data retention settings.\n            Define retention periods that align with your business needs or regulatory requirements. Once the specified retention period expires, the data is automatically\n            and permanently deleted from our servers.\\r\\n\\r\\n\\r\\nAccess Controls: Grant specific access permissions to team members or clients based on their roles and\n            responsibilities. With our access control settings, you can limit who can view, edit, or export form data, ensuring that only authorized individuals can access\n            sensitive information.\\r\\n\\r\\n\\r\\nThird-Party Integrations: If you integrate third-party services or applications with Full Tenancy Laravel Admin Saas, our privacy\n            settings enable you to manage the data shared with these services. You have the flexibility to control which data is shared, providing an extra layer of privacy\n            and control.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(2,'Our Team','desc',NULL,NULL,NULL,'Meet Our Team provides a grid layout to show all the team members into single page. To display the members information for more attractive by\n            using jQuery effects. The viewers can easily identify the hierarchical structure of organization and who are all involved in which designation and their names.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(3,'Products','desc',NULL,NULL,NULL,'The Products module is a catalogue of the products and services you are offering. Users have the possibility to create an enumerated database\n            with descriptions and prices or to synchronize this with Pohoda (the accounting system).','2025-03-15 21:37:13','2025-03-15 21:37:13'),(4,'Contact','link','internal link','https://tuneup.golf/contactus','https://tuneup.golf/contactus',NULL,'2025-03-15 21:37:13','2025-03-15 21:37:13'),(5,'Feature','desc',NULL,NULL,NULL,'A feature module delivers a cohesive set of functionality focused on a specific application need such as a user workflow, routing, or forms.\n            While you can do everything within the root module, feature modules help you partition the application into focused areas.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(6,'Pricing','desc',NULL,NULL,NULL,'The prices module, also called Pricing, is a system responsible for the creation, editing and storing of your SKU pricing data. For a product\n            to be sold, your customer needs to know the cost of each item displayed in your store.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(7,'Credit','desc',NULL,NULL,NULL,'One credit is typically described as being equal to 10 hours of notional learning. A module that involves 150 notional hours of learning will\n            be assigned 15 credits. One that involves 400 notional hours of learning will be assigned 40 credits.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(8,'News','desc',NULL,NULL,NULL,'The News module allows you to feature timely content on your website, such as announcements, special messages, or even your own blog articles.\n            Before adding plain text to your homepage using the Text/HTML module, consider using the News module instead!','2025-03-15 21:37:13','2025-03-15 21:37:13'),(9,'iOS','desc',NULL,NULL,NULL,'iOS Module Project\n            Provides in-depth information about the structure of a module project as well as using Studio and the CLI to manage the projects. Also provides information about\n            adding assets and third-party frameworks to the module.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(10,'Android','desc',NULL,NULL,NULL,'Modules provide a container for your apps source code, resource files, and app level settings, such as the module-level build file and Android\n            manifest file. Each module can be independently built, tested, and debugged. Android Studio uses modules to make it easy to add new devices to your project.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(11,'Microsoft','desc',NULL,NULL,NULL,'This article presents an overview of the Microsoft Dynamics 365 Commerce module library. The Dynamics 365 Commerce module library is a collection\n             of modules that can be used to build an e-Commerce website. Modules have both user interface (UI) aspects and functional behavior aspects.','2025-03-15 21:37:13','2025-03-15 21:37:13'),(12,'Desktop','desc',NULL,NULL,NULL,'A module is a distinct assembly of components that can be easily added, removed or replaced in a larger system. Generally, a module is not\n            functional on its own. In computer hardware, a module is a component that is designed for easy replacement. In computer software, a module is an extension to\n            a main program dedicated to a specific function. In programming, a module is a section of code that is added in as a whole or is designed for easy reusability.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(13,'Help','desc',NULL,NULL,NULL,'Help, aid, assist, succor agree in the idea of furnishing another with something needed, especially when the need comes at a particular time.\n            Help implies furnishing anything that furthers ones efforts or relieves ones wants or necessities. Aid and assist, somewhat more formal, imply especially a\n            furthering or seconding of anothers efforts. Aid implies a more active helping; assist implies less need and less help. To succor, still more formal and literary,\n             is to give timely help and relief in difficulty or distress: Succor him in his hour of need.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(14,'Terms','desc',NULL,NULL,NULL,'Full Tenancy Laravel Admin SaasTerms and Conditions\n                Acceptance of Terms By accessing and using [Full Tenancy Laravel Admin Saas] (the &quot;Service&quot;), you agree to be bound by these terms and conditions.\n                If you do not agree with any part of these terms, please refrain from using the Service.\n                Intellectual Property Rights All content and materials provided on the Service are the property of [Full Tenancy Laravel Admin Saas- Saas]&nbsp;and protected\n                by applicable intellectual property laws. You may not use, reproduce, distribute, or modify any content from the Service without prior written consent\n                from [Full Tenancy Laravel Admin Saas].\n                User Responsibilities a. You are solely responsible for any content you submit or upload on the Service. You agree not to post, transmit, or share any\n                material that is unlawful, harmful, defamatory, obscene, or infringes upon the rights of others. b. You agree not to interfere with or disrupt the Service\n                or its associated servers and networks. c. You are responsible for maintaining the confidentiality of your account information and agree to notify\n                [Full Tenancy Laravel Admin Saas- Saas] immediately of any unauthorized use of your account.\n                Disclaimer of Warranties The Service is provided on an &quot;as-is&quot; and &quot;as available&quot; basis. [Full Tenancy Laravel Admin Saas] makes no warranties,\n                expressed or implied, regarding the accuracy, reliability, or availability of the Service. Your use of the Service is at your own risk.\n                Limitation of Liability In no event shall [Full Tenancy Laravel Admin Saas] be liable for any direct, indirect, incidental, consequential, or punitive damages\n                arising out of or in connection with the use of the Service. This includes but is not limited to any errors or omissions in the content, loss of data, or\n                any other loss or damage. Indemnification You agree to indemnify and hold&nbsp; harmless from any claims, damages, liabilities, or expenses arising out of\n                your use of the Service, your violation of these terms and conditions, or your infringement of any rights of a third party.\n                Modification and Termination [Full Tenancy Laravel Admin Saas- Saas] reserves the right to modify or terminate the Service at any time, without prior notice.\n                We also reserve the right to update these terms and conditions from time to time. It is your responsibility to review the most current version regularly.\n                Governing Law These terms and conditions shall be governed by and construed in accordance with the laws of India. Any disputes arising out of these terms\n                shall be subject to the exclusive jurisdiction of the courts located in india.','2025-03-15 21:37:14','2025-03-15 21:37:14'),(15,'FAQ','link','internal link','https://tuneup.golf/all/faqs','https://tuneup.golf/all/faqs',NULL,'2025-03-15 21:37:14','2025-03-15 21:37:14'),(16,'Privacy','desc',NULL,NULL,NULL,'\n                Acceptance of Terms By accessing and using [Full Tenancy Laravel Admin Saas] (the &quot;Service&quot;), you agree to be bound by these terms and conditions. If you do not agree with any part of these terms, please refrain from using the Service.\n                Intellectual Property Rights All content and materials provided on the Service are the property of [Full Tenancy Laravel Admin Saas- Saas]&nbsp;and protected by applicable intellectual property laws. You may not use, reproduce, distribute, or modify any content from the Service without prior written consent from [Full Tenancy Laravel Admin Saas].\n                User Responsibilities a. You are solely responsible for any content you submit or upload on the Service. You agree not to post, transmit, or share any material that is unlawful, harmful, defamatory, obscene, or infringes upon the rights of others. b. You agree not to interfere with or disrupt the Service or its associated servers and networks. c. You are responsible for maintaining the confidentiality of your account information and agree to notify [Full Tenancy Laravel Admin Saas- Saas] immediately of any unauthorized use of your account.\n                Disclaimer of Warranties The Service is provided on an &quot;as-is&quot; and &quot;as available&quot; basis. [Full Tenancy Laravel Admin Saas] makes no warranties, expressed or implied, regarding the accuracy, reliability, or availability of the Service. Your use of the Service is at your own risk.\n                Limitation of Liability In no event shall [Full Tenancy Laravel Admin Saas] be liable for any direct, indirect, incidental, consequential, or punitive damages arising out of or in connection with the use of the Service. This includes but is not limited to any errors or omissions in the content, loss of data, or any other loss or damage.\n                Indemnification You agree to indemnify and hold&nbsp; harmless from any claims, damages, liabilities, or expenses arising out of your use of the Service, your violation of these terms and conditions, or your infringement of any rights of a third party.\n                Modification and Termination [Full Tenancy Laravel Admin Saas- Saas] reserves the right to modify or terminate the Service at any time, without prior notice. We also reserve the right to update these terms and conditions from time to time. It is your responsibility to review the most current version regularly.\n                Governing Law These terms and conditions shall be governed by and construed in accordance with the laws of India. Any disputes arising out of these terms shall be subject to the exclusive jurisdiction of the courts located in india.','2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `page_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint unsigned DEFAULT NULL,
  `purchase_id` bigint unsigned NOT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_purchase_id_foreign` (`purchase_id`),
  CONSTRAINT `payment_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage-role','web','2025-03-15 21:37:09','2025-03-15 21:37:09'),(2,'create-role','web','2025-03-15 21:37:09','2025-03-15 21:37:09'),(3,'edit-role','web','2025-03-15 21:37:09','2025-03-15 21:37:09'),(4,'delete-role','web','2025-03-15 21:37:09','2025-03-15 21:37:09'),(5,'show-role','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(6,'manage-user','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(7,'create-user','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(8,'edit-user','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(9,'delete-user','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(10,'impersonate-user','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(11,'manage-lessons','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(12,'create-lessons','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(13,'edit-lessons','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(14,'delete-lessons','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(15,'impersonate-lessons','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(16,'manage-instructors','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(17,'create-instructors','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(18,'edit-instructors','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(19,'delete-instructors','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(20,'impersonate-instructors','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(21,'manage-students','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(22,'create-students','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(23,'edit-students','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(24,'delete-students','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(25,'impersonate-students','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(26,'manage-setting','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(27,'manage-transaction','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(28,'manage-landingpage','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(29,'manage-chat','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(30,'manage-plan','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(31,'create-plan','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(32,'edit-plan','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(33,'delete-plan','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(34,'manage-blog','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(35,'create-blog','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(36,'edit-blog','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(37,'delete-blog','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(38,'manage-category','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(39,'create-category','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(40,'edit-category','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(41,'delete-category','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(42,'manage-email-template','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(43,'edit-email-template','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(44,'manage-sms-template','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(45,'edit-sms-template','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(46,'manage-testimonial','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(47,'create-testimonial','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(48,'edit-testimonial','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(49,'delete-testimonial','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(50,'manage-event','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(51,'create-event','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(52,'edit-event','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(53,'delete-event','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(54,'manage-faqs','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(55,'create-faqs','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(56,'edit-faqs','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(57,'delete-faqs','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(58,'manage-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(59,'create-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(60,'edit-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(61,'delete-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(62,'show-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(63,'mass-create-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(64,'upload-coupon','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(65,'manage-document','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(66,'create-document','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(67,'edit-document','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(68,'delete-document','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(69,'manage-page-setting','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(70,'create-page-setting','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(71,'edit-page-setting','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(72,'delete-page-setting','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(73,'manage-support-ticket','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(74,'create-support-ticket','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(75,'edit-support-ticket','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(76,'delete-support-ticket','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(77,'manage-purchases','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(78,'create-purchases','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(79,'edit-purchases','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(80,'delete-purchases','web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(81,'manage-follow','web','2025-03-15 21:37:12','2025-03-15 21:37:12'),(82,'create-follow','web','2025-03-15 21:37:12','2025-03-15 21:37:12'),(83,'delete-follow','web','2025-03-15 21:37:12','2025-03-15 21:37:12');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',2,'moeed','afa8a5eacf23df58343e8273296d9bf77410bd846739f4b89e028f79b911a9fc','[\"*\"]',NULL,'2025-03-15 21:44:31','2025-03-15 21:44:20','2025-03-15 21:44:31'),(2,'App\\Models\\User',2,'moeed','f6f40a3f3f5c13eed4a4d48d0c5715894319176ce6e62eef5e97591f734bdb3e','[\"*\"]',NULL,'2025-03-15 21:45:50','2025-03-15 21:45:07','2025-03-15 21:45:50'),(3,'App\\Models\\User',2,'moeed','9de0ce38b151d0813d15c58a8f31d7ff220703dee084df71bc325b61c764617f','[\"*\"]',NULL,'2025-03-31 15:23:44','2025-03-31 14:28:31','2025-03-31 15:23:44'),(4,'App\\Models\\Student',2,'moeed','ceb4b0eb815427ab35dfcc148ef0aac76f42e330d70ac833eeba32fab73330fa','[\"*\"]',NULL,'2025-04-14 21:55:55','2025-04-03 22:04:08','2025-04-14 21:55:55'),(5,'App\\Models\\User',2,'moeed','dd6601ee2f84f698c1cc4783ac2d04104fcba2985b6f95f470ad3da9e43ad67d','[\"*\"]',NULL,'2025-04-10 15:16:21','2025-04-03 22:12:59','2025-04-10 15:16:21'),(6,'App\\Models\\Student',3,'moeed','ac76396e9e03ed6b56e9fc9f67b9c5fba7222fd081f21508d868bb9bc3b178aa','[\"*\"]',NULL,'2025-04-04 00:21:52','2025-04-04 00:20:12','2025-04-04 00:21:52'),(7,'App\\Models\\Student',5,'moeed','4f7c32768a37f16180ef10bf0b0078f39ab10f84a9e32d3f0c6b34bd0a278b00','[\"*\"]',NULL,'2025-04-05 17:05:58','2025-04-05 17:04:39','2025-04-05 17:05:58'),(8,'App\\Models\\Student',6,'moeed','4fc57fce3bcc29ab78adcc5378816386f4b324bd0acfd246a9522a360f3ab0a1','[\"*\"]',NULL,'2025-04-05 18:02:07','2025-04-05 18:01:35','2025-04-05 18:02:07'),(9,'App\\Models\\Student',8,'moeed','d6034dad359c29bac37868030c944355ce82df8ed5ed988e9a588d4f7a10574d','[\"*\"]',NULL,'2025-04-08 16:45:35','2025-04-08 16:44:33','2025-04-08 16:45:35');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double(8,2) NOT NULL DEFAULT '0.00',
  `duration` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `durationtype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `max_users` int NOT NULL DEFAULT '0',
  `max_instructors` int NOT NULL DEFAULT '0',
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active_status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Free',0.00,'1','Year',NULL,10,10,NULL,1,'2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `price` decimal(8,2) DEFAULT NULL,
  `isStudentPost` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `file_type` enum('image','video') COLLATE utf8mb4_unicode_ci DEFAULT 'image',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_instructor_id_foreign` (`instructor_id`),
  KEY `post_student_id_foreign` (`student_id`),
  CONSTRAINT `post_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `post_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post`
--

LOCK TABLES `post` WRITE;
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
/*!40000 ALTER TABLE `post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchasepost`
--

DROP TABLE IF EXISTS `purchasepost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchasepost` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `post_id` bigint unsigned NOT NULL,
  `active_status` tinyint(1) NOT NULL,
  `session_id` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchasepost_post_id_foreign` (`post_id`),
  KEY `purchasepost_student_id_foreign` (`student_id`),
  CONSTRAINT `purchasepost_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchasepost_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchasepost`
--

LOCK TABLES `purchasepost` WRITE;
/*!40000 ALTER TABLE `purchasepost` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchasepost` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `friend_names` json DEFAULT NULL,
  `instructor_id` bigint unsigned NOT NULL,
  `lesson_id` bigint unsigned NOT NULL,
  `slot_id` bigint unsigned DEFAULT NULL,
  `coupon_id` bigint unsigned DEFAULT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lessons_used` bigint unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `total_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isFeedbackComplete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `purchases_instructor_id_foreign` (`instructor_id`),
  KEY `purchases_lesson_id_foreign` (`lesson_id`),
  KEY `purchases_coupon_id_foreign` (`coupon_id`),
  KEY `purchases_student_id_foreign` (`student_id`),
  KEY `purchases_slot_id_foreign` (`slot_id`),
  CONSTRAINT `purchases_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  CONSTRAINT `purchases_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `purchases_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`),
  CONSTRAINT `purchases_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `slots` (`id`),
  CONSTRAINT `purchases_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,3,NULL,2,1,6,NULL,'68',0,'incomplete',100.00,NULL,'2025-04-08 13:20:31','2025-04-08 13:20:31',0),(2,7,NULL,2,1,5,NULL,'68',0,'incomplete',100.00,NULL,'2025-04-08 13:31:12','2025-04-08 13:31:12',0);
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchasevideos`
--

DROP TABLE IF EXISTS `purchasevideos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchasevideos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_id` bigint unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `isFeedbackComplete` tinyint(1) NOT NULL DEFAULT '0',
  `video_url_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `purchasevideos_purchase_id_foreign` (`purchase_id`),
  CONSTRAINT `purchasevideos_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchasevideos`
--

LOCK TABLES `purchasevideos` WRITE;
/*!40000 ALTER TABLE `purchasevideos` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchasevideos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_posts`
--

DROP TABLE IF EXISTS `report_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint unsigned NOT NULL,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_posts_student_id_foreign` (`student_id`),
  KEY `report_posts_instructor_id_foreign` (`instructor_id`),
  KEY `report_posts_post_id_foreign` (`post_id`),
  CONSTRAINT `report_posts_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_posts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_posts`
--

LOCK TABLES `report_posts` WRITE;
/*!40000 ALTER TABLE `report_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_user`
--

DROP TABLE IF EXISTS `report_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `report_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `report_user_student_id_foreign` (`student_id`),
  KEY `report_user_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `report_user_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `report_user_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_user`
--

LOCK TABLES `report_user` WRITE;
/*!40000 ALTER TABLE `report_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `instructor_id` bigint unsigned DEFAULT NULL,
  `student_id` bigint unsigned DEFAULT NULL,
  `review` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` double(3,2) NOT NULL DEFAULT '1.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_student_id_foreign` (`student_id`),
  KEY `reviews_instructor_id_foreign` (`instructor_id`),
  CONSTRAINT `reviews_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(11,2),(12,2),(13,2),(14,2),(15,2),(21,2),(22,2),(23,2),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,2),(41,2),(42,2),(43,2),(44,2),(45,2),(46,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(11,3),(26,3),(27,3),(28,3),(29,3),(34,3),(35,3),(38,3),(39,3),(40,3),(41,3),(46,3),(47,3),(48,3),(49,3),(54,3),(55,3),(56,3),(57,3),(77,3),(78,3),(79,3),(81,3),(82,3),(83,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin',NULL,'web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(2,'Instructor',NULL,'web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(3,'Student',NULL,'web','2025-03-15 21:37:10','2025-03-15 21:37:10'),(4,'User',NULL,'web','2025-03-15 21:37:13','2025-03-15 21:37:13');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'app_name','TUNEUP-Analyzing Golf','68'),(2,'app_logo','logo/app-logo.png','68'),(3,'favicon_logo','logo/app-favicon-logo.png','68'),(4,'default_language','en','68'),(5,'currency','USD','68'),(6,'currency_symbol','$','68'),(7,'date_format','M j, Y','68'),(8,'time_format','g:i A','68'),(9,'color','theme-2','68'),(10,'storage_type','local','68'),(11,'dark_mode','off','68'),(12,'transparent_layout','1','68'),(13,'landing_page_status','1','68'),(14,'roles','User','68'),(15,'plan_setting','{\"plan_id\":1,\"name\":\"Free\",\"price\":0,\"duration\":\"1\",\"durationtype\":\"Month\",\"description\":\"A payment plan an organized payment schedule.\",\"max_users\":50,\"max_roles\":5,\"max_documents\":5,\"max_blogs\":5,\"discount_setting\":\"off\",\"discount\":null,\"tenant_id\":null,\"active_status\":1,\"created_at\":\"2024-01-16T15:09:18.000000Z\",\"updated_at\":\"2024-01-16T15:09:18.000000Z\"}','68'),(16,'apps_setting_enable','on','68'),(17,'apps_name','TUNEUP','68'),(18,'apps_bold_name','Analyzing Golf','68'),(19,'app_detail','Master Your Swing, One Lesson at a Time.','68'),(20,'apps_image','logo/app-logo.png','68'),(21,'apps_multiple_image_setting','[\n                {\"apps_multiple_image\":\"logo/app-logo.png\"},\n            ]','68'),(22,'enable_sms_notification','on','68'),(23,'enable_email_notification','on','68'),(24,'feature_setting_enable','off','68'),(25,'feature_name','Full Multi Tenancy Laravel Admin Saas','68'),(26,'feature_bold_name','Features','68'),(27,'feature_detail','A Full Tenancy Laravel Admin Saas features collectively create a robust and flexible SaaS platform that serves multiple businesses efficiently while maintaining security, scalability, and compliance with legal requirements.','68'),(28,'feature_setting','[\n                {\"feature_image\":\"seeder-image/active.svg\",\"feature_name\":\"Email Notification\",\"feature_bold_name\":\"On From Submit\",\"feature_detail\":\"You can send a notification email to someone in your organization when a contact submits a form. You can use this type of form processing step so that...\"},\n                {\"feature_image\":\"seeder-image/security.svg\",\"feature_name\":\"Two Factor\",\"feature_bold_name\":\"Authentication\",\"feature_detail\":\"Security is our priority. With our robust two-factor authentication (2FA) feature, you can add an extra layer of protection to your Full Tenancy Form\"},\n                {\"feature_image\":\"seeder-image/secretary.svg\",\"feature_name\":\"Multi Users With\",\"feature_bold_name\":\"Role & permission\",\"feature_detail\":\"Assign roles and permissions to different users based on their responsibilities and requirements. Admins can manage user accounts, define access level\"},\n                {\"feature_image\":\"seeder-image/documents.svg\",\"feature_name\":\"Document builder\",\"feature_bold_name\":\"Easy and fast\",\"feature_detail\":\"Template Library: Offer a selection of pre-designed templates for various document types (e.g., contracts, reports).Template Creation: Allow users to create custom templates with placeholders for dynamic content.\\r\\n\\r\\nTemplate Library: Offer a selection of pre-designed templates for various document types (e.g., contracts, reports).Template Creation: Allow users to create custom templates with placeholders for dynamic content.\"}\n            ]','68'),(29,'menu_setting_section1_enable','off','68'),(30,'menu_name_section1','Dashboard','68'),(31,'menu_bold_name_section1','Apexchart','68'),(32,'menu_detail_section1','ApexChart enables users to create and customize dynamic, visually engaging charts for data visualization. Users can select chart types, configure data sources, apply filters, customize appearance, and interact with data through various chart-related features. ','68'),(33,'menu_image_section1','seeder-image/menusection1.png','68'),(34,'menu_setting_section2_enable','off','68'),(35,'menu_name_section2','Support System With','68'),(36,'menu_bold_name_section2','Issue Resolution','68'),(37,'menu_detail_section2','The Support System section is your gateway to comprehensive assistance. It provides access to a knowledge base, FAQs, and direct contact options for user inquiries and assistance.','68'),(38,'menu_image_section2','seeder-image/menusection2.png','68'),(39,'menu_setting_section3_enable','off','68'),(40,'menu_name_section3','Setting Features With','68'),(41,'menu_bold_name_section3','Multiple Section settings','68'),(42,'menu_detail_section3','A settings page is a crucial component of many digital products, allowing users to customize their experience according to their preferences. Designing a settings page with dynamic data enhances user satisfaction and engagement. Here s a guide on how to create such a page.','68'),(43,'menu_image_section3','seeder-image/menusection3.png','68'),(44,'business_growth_setting_enable','off','68'),(45,'business_growth_front_image','seeder-image/thumbnail.png','68'),(46,'business_growth_video','seeder-image//video.webm','68'),(47,'business_growth_name','Makes Quick','68'),(48,'business_growth_bold_name','Business Growth','68'),(49,'business_growth_detail','Offer unique products, services, or solutions that stand out in the market. Innovation and differentiation can attract customers and give you a competitive edge.','68'),(50,'business_growth_view_setting','[\n                {\"business_growth_view_name\":\"Positive Reviews\",\"business_growth_view_amount\":\"20 k+\"},\n                {\"business_growth_view_name\":\"Total Sales\",\"business_growth_view_amount\":\"300 +\"},\n                {\"business_growth_view_name\":\"Happy Users\",\"business_growth_view_amount\":\"100 k+\"}\n            ]','68'),(51,'business_growth_setting','[\n                {\"business_growth_title\":\"User Friendly\"},\n                {\"business_growth_title\":\"Products Analytic\"},\n                {\"business_growth_title\":\"Manufacturers\"},\n                {\"business_growth_title\":\"Order Status Tracking\"},\n                {\"business_growth_title\":\"Supply Chain\"},\n                {\"business_growth_title\":\"Chatting Features\"},\n                {\"business_growth_title\":\"Workflows\"},\n                {\"business_growth_title\":\"Transformation\"},\n                {\"business_growth_title\":\"Easy Payout\"},\n                {\"business_growth_title\":\"Data Adjustment\"},\n                {\"business_growth_title\":\"Order Status Tracking\"},\n                {\"business_growth_title\":\"Store Swap Link\"},\n                {\"business_growth_title\":\"Manufacturers\"},\n                {\"business_growth_title\":\"Order Status Tracking\"}\n            ]','68'),(52,'contactus_setting_enable','off','68'),(53,'contactus_name','Enterprise','68'),(54,'contactus_bold_name','Custom pricing','68'),(55,'contactus_detail','Offering tiered pricing options based on different levels of features or services allows customers.','68'),(56,'faq_setting_enable','off','68'),(57,'faq_name','Frequently asked questions','68'),(58,'start_view_setting_enable','off','68'),(59,'start_view_name','Start Using Full Multi Tenancy Laravel Admin Saas','68'),(60,'start_view_detail','a Full Multi Tenancy Laravel Admin Saas application is a complex process that requires careful planning and development.','68'),(61,'start_view_link_name','Register','68'),(62,'start_view_link','https://tuneup.golf/register','68'),(63,'start_view_image','seeder-image/startview.png','68'),(64,'login_setting_enable','on','68'),(65,'login_image','seeder-image/login.svg','68'),(66,'login_name','Master Your Swing, One Lesson at a Time.','68'),(67,'login_detail','Discover the perfect golf instructor tailored to your needs on our dedicated platform. With an array of experts at your fingertips, elevate your golf game seamlessly. Start today and embrace the journey towards your best swing.','68'),(68,'testimonial_setting_enable','off','68'),(69,'testimonial_name','Full Tenancy Laravel Admin Saas','68'),(70,'testimonial_bold_name','Testimonial','68'),(71,'testimonial_detail','A testimonial is an honest endorsement of your product or service that usually comes from a customer, colleague, or peer who has benefited from or experienced success as a result of the work you did for them.','68'),(72,'footer_setting_enable','off','68'),(73,'footer_description','Analyzing Golf','68'),(74,'application_fee_percentage','10','68');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slots`
--

DROP TABLE IF EXISTS `slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint unsigned NOT NULL,
  `date_time` timestamp NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slots_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `slots_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slots`
--

LOCK TABLES `slots` WRITE;
/*!40000 ALTER TABLE `slots` DISABLE KEYS */;
INSERT INTO `slots` VALUES (1,1,'2025-03-31 08:00:00','Lionhead Golf',0,1,0,'68'),(2,1,'2025-03-31 09:00:00','Lionhead Golf',0,1,0,'68'),(3,1,'2025-04-10 16:00:00','Indoor Pro Golf',0,1,0,'68'),(4,1,'2025-04-10 17:00:00','Indoor Pro Golf',0,1,0,'68'),(5,1,'2025-04-10 18:00:00','Indoor Pro Golf',0,1,0,'68'),(6,1,'2025-04-10 19:00:00','Indoor Pro Golf',0,1,0,'68'),(7,1,'2025-04-10 20:00:00','Indoor Pro Golf',0,1,0,'68'),(8,1,'2025-04-12 10:00:00','Indoor Pro Golf',0,1,0,'68'),(9,1,'2025-04-12 11:00:00','Indoor Pro Golf',0,1,0,'68'),(10,1,'2025-04-12 12:00:00','Indoor Pro Golf',0,1,0,'68'),(11,1,'2025-04-12 13:00:00','Indoor Pro Golf',0,1,0,'68'),(12,1,'2025-04-12 14:00:00','Indoor Pro Golf',0,1,0,'68');
/*!40000 ALTER TABLE `slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_templates`
--

DROP TABLE IF EXISTS `sms_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` text COLLATE utf8mb4_unicode_ci,
  `variables` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_templates`
--

LOCK TABLES `sms_templates` WRITE;
/*!40000 ALTER TABLE `sms_templates` DISABLE KEYS */;
INSERT INTO `sms_templates` VALUES (1,'verification code sms','Hello :name, Your verification code is :code','name,code','2025-03-15 21:37:14','2025-03-15 21:37:14');
/*!40000 ALTER TABLE `sms_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_logins`
--

DROP TABLE IF EXISTS `social_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_logins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `social_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_logins`
--

LOCK TABLES `social_logins` WRITE;
/*!40000 ALTER TABLE `social_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_slots`
--

DROP TABLE IF EXISTS `student_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slot_id` bigint unsigned NOT NULL,
  `student_id` bigint unsigned NOT NULL,
  `isFriend` tinyint(1) NOT NULL DEFAULT '0',
  `friend_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_slots_slot_id_foreign` (`slot_id`),
  KEY `student_slots_student_id_foreign` (`student_id`),
  CONSTRAINT `student_slots_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_slots_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_slots`
--

LOCK TABLES `student_slots` WRITE;
/*!40000 ALTER TABLE `student_slots` DISABLE KEYS */;
INSERT INTO `student_slots` VALUES (1,6,3,0,NULL,'2025-04-08 13:20:31','2025-04-08 13:20:31'),(2,5,7,0,NULL,'2025-04-08 13:31:12','2025-04-08 13:31:12');
/*!40000 ALTER TABLE `student_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isGuest` tinyint(1) NOT NULL DEFAULT '0',
  `active_status` tinyint(1) NOT NULL DEFAULT '1',
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` text COLLATE utf8mb4_unicode_ci,
  `dp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `stripe_cus_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `social_url_ig` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_url_fb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_url_x` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'b99079ad-879a-4920-930b-3a37dd1dbb12','Matthew Cholod','mcholod@clubup.com','$2y$10$eh3IRDhy7DGcqbvzpybdHu7XdFWrRr1uh/pDI/G3aAJKzIbJxhNTG','68','Student',0,1,'us','1','(859)757-7480','dp/IdrcTgb7BW3PXFpEjbvzuQMlYc1JuYQOai6hkMro.jpg','1','2025-03-15 22:10:25','2025-03-15 22:10:25',NULL,NULL,NULL,'2025-03-15 22:10:25','2025-03-15 22:10:25',NULL,NULL,NULL),(2,NULL,'Sunju Hans','sunjuhans41@gmail.com','$2y$10$Q9Fw05t9sXBaSsly8kg1huhljk2Cru.FBibpb4Wy6yw9qRemi5Sza','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-03 22:03:06','2025-04-03 22:03:06',NULL,NULL,NULL,'2025-04-03 22:03:06','2025-04-03 22:03:06',NULL,NULL,NULL),(3,NULL,'Giuliano Cesario','giulianoc1962@gmail.com','$2y$10$7bP2R.BIu689UT.fAuhMAeKcIBxsZLC3U6hnUsIisnDOcCxKCgK8i','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-04 00:16:32','2025-04-04 00:16:32',NULL,NULL,NULL,'2025-04-04 00:16:32','2025-04-04 00:16:32',NULL,NULL,NULL),(4,NULL,'Steve Chito','stchito@rogers.com','$2y$10$jTMi3AK9rO/xWOKXCPt/1.ZTpz3x6Ws8P7Vz1hr2EahKdnacpbkH6','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-04 00:23:00','2025-04-04 00:23:00',NULL,NULL,NULL,'2025-04-04 00:23:00','2025-04-04 00:23:00',NULL,NULL,NULL),(5,NULL,'Han Lin Yang','gta.estates@live.com','$2y$10$AE6NWf7T0TBwYQWFVxZ0luAs6ww/VUoc8xbxkiXdwAg8fsYxdwFeu','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-05 17:04:14','2025-04-05 17:04:14',NULL,NULL,NULL,'2025-04-05 17:04:14','2025-04-05 17:04:14',NULL,NULL,NULL),(6,NULL,'Reyaz Yahya','reyazyahya@gmail.com','$2y$10$1.KK.N.fBZo5z7/pAQv5eeeENwR0GeJKNPgohk2hhJ1T65WKTqUrS','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-05 18:01:17','2025-04-05 18:01:17',NULL,NULL,NULL,'2025-04-05 18:01:17','2025-04-05 18:01:17',NULL,NULL,NULL),(7,'88062ec5-9c24-49c3-9365-9f70e6b58269','Hailey Hughes','haileyyhughes@gmail.com','$2y$10$nFmLxeW/kkHl2/Usu0zIDeNOlJU4cIoouMHvJbSaBtCydF66ajmZC','68','Student',0,1,'ca','1','(647)234-4199',NULL,'2','2025-04-08 13:30:41','2025-04-08 13:30:41',NULL,NULL,NULL,'2025-04-08 13:30:41','2025-04-08 13:30:41',NULL,NULL,NULL),(8,NULL,'Sue Soamboonsrup','Sue.soamboonsrup@gmail.com','$2y$10$xZakWU.uj5kNp8XyuJ5UMOjfzBK29oft0FgLDj0xBkHDdJx63.Y6e','68','Student',0,1,'+01','xxx','xxxxx80',NULL,'signup','2025-04-08 16:44:00','2025-04-08 16:44:00',NULL,NULL,NULL,'2025-04-08 16:44:00','2025-04-08 16:44:00',NULL,NULL,NULL);
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_user_impersonation_tokens`
--

DROP TABLE IF EXISTS `tenant_user_impersonation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_user_impersonation_tokens` (
  `token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_guard` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_user_impersonation_tokens`
--

LOCK TABLES `tenant_user_impersonation_tokens` WRITE;
/*!40000 ALTER TABLE `tenant_user_impersonation_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_user_impersonation_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` double(10,1) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'Jeny','Customer Support Specialist','As a Customer Support Specialist for Full Tenancy Laravel Admin Saas, I have had the incredible opportunity to assist our valued customers in their journey of utilizing this revolutionary form-building solution.','seeder-image/13.png','Support Specialist',5.0,'1','2025-03-15 21:37:13','2025-03-15 21:37:13'),(2,'Johnsi','A Journey of Growth and Transformation','As the Lead Developer for Full Tenancy Laravel Admin Saas, I have had the privilege of being at the forefront of developing a cutting-edge product that revolutionizes form-building.','seeder-image/14.png','Lead Developer',5.0,'1','2025-03-15 21:37:13','2025-03-15 21:37:13'),(3,'Trisha','Customer Support Specialist','As a Customer Support Specialist for Full Tenancy Laravel Admin Saas, I have had the incredible opportunity to assist our valued customers in their journey of utilizing this revolutionary form-building solution.','seeder-image/15.png','Support Specialist',5.0,'1','2025-03-15 21:37:13','2025-03-15 21:37:13'),(4,'Trusha','A Remarkable Journey of Collaboration and Success','As a Project Manager, my primary responsibility has been to ensure that projects are delivered on time, within budget. I have had the opportunity to work closely with cross-functional teams, marketers, and stakeholders, initiation to completion.','seeder-image/16.png','Project Manager',5.0,'1','2025-03-15 21:37:13','2025-03-15 21:37:13');
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_codes`
--

DROP TABLE IF EXISTS `user_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_codes`
--

LOCK TABLES `user_codes` WRITE;
/*!40000 ALTER TABLE `user_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_coupons`
--

DROP TABLE IF EXISTS `user_coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user` int DEFAULT NULL,
  `coupon` int NOT NULL,
  `domainrequest` int DEFAULT NULL,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_coupons`
--

LOCK TABLES `user_coupons` WRITE;
/*!40000 ALTER TABLE `user_coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` double(8,2) DEFAULT '0.00',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dial_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avg_rate` double DEFAULT NULL,
  `sub_price` decimal(8,2) DEFAULT NULL,
  `is_stripe_connected` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'avatar.png',
  `messenger_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1B3768',
  `dark_mode` tinyint(1) NOT NULL DEFAULT '0',
  `dark_layout` tinyint(1) NOT NULL DEFAULT '0',
  `rtl_layout` tinyint(1) NOT NULL DEFAULT '0',
  `transprent_layout` tinyint(1) NOT NULL DEFAULT '1',
  `theme_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'theme-2',
  `lang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `plan_id` bigint DEFAULT NULL,
  `social_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_expired_date` datetime DEFAULT NULL,
  `active_status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `golf_course` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_url_ig` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_url_fb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_url_x` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_country` enum('usa','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'usa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,'Marcus Rodrigues','marcusrodriguespga@gmail.com','$2y$10$4E3UO4SzG.lXYKZOz9DeSO21bbvGRF0NZckIZWXJl/r2A.zAFHsLi','68',0.00,NULL,'India','ca','1','(905)220-1029','Admin',NULL,NULL,NULL,NULL,0,NULL,'2025-03-15 21:36:55','2025-03-15 21:36:55',NULL,'avatar/avatar.png','#1B3768',0,0,0,1,'theme-2','en',3,NULL,NULL,1,'2025-03-15 21:37:13','2025-03-15 21:37:13',NULL,NULL,NULL,NULL,NULL,'usa'),(2,'2509fa44-8c6a-495a-b91d-34510328535f','Marcus Rodrigues','marcusrodrigues2108@gmail.com','$2y$10$iN3GSiWiKar4Y2Y4dN7g6.vaBT1EC2fmZ0UwIBgVD5.Eqz7Zi5Mr6','68',5.00,NULL,'India','ca','1','9052201029','Instructor','1',NULL,NULL,NULL,1,'acct_1R8jtCPmXZcvyZLa','2025-03-15 21:41:52','2025-03-15 21:41:52',NULL,'avatar.png','#1B3768',0,0,0,1,'theme-2','en',NULL,NULL,NULL,1,'2025-03-15 21:41:52','2025-04-03 14:57:48','dp/R89jViwbSwj734ep2bYhlHCIlrTGDetZhAcGc3HF.jpg',NULL,NULL,NULL,NULL,'usa');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-16  9:28:51
