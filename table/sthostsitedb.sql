/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sthostsitedb
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

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

--
-- Table structure for table `cart_domains`
--

DROP TABLE IF EXISTS `cart_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) NOT NULL COMMENT 'ID сесії користувача',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID користувача (якщо авторизований)',
  `domain_name` varchar(255) NOT NULL COMMENT 'Повна назва домену',
  `domain_zone` varchar(50) NOT NULL COMMENT 'Доменна зона',
  `registration_period` int(11) NOT NULL DEFAULT 1 COMMENT 'Період реєстрації в роках',
  `price` decimal(10,2) NOT NULL COMMENT 'Ціна за період',
  `whois_privacy` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Захист WHOIS',
  `auto_renewal` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Автопродовження',
  `status` enum('cart','ordered','cancelled') NOT NULL DEFAULT 'cart' COMMENT 'Статус товару',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cart_session` (`session_id`,`status`),
  KEY `idx_cart_user` (`user_id`,`status`),
  KEY `idx_cart_domain` (`domain_name`),
  KEY `idx_cart_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Домени в кошику користувачів';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_domains`
--

LOCK TABLES `cart_domains` WRITE;
/*!40000 ALTER TABLE `cart_domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_files`
--

DROP TABLE IF EXISTS `chat_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `uploaded_by` varchar(100) DEFAULT 'operator',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_files`
--

LOCK TABLES `chat_files` WRITE;
/*!40000 ALTER TABLE `chat_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `sender_type` enum('user','operator','system') NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','file','image','system') DEFAULT 'text',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_messages_session_created` (`session_id`,`created_at`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES
(2,2,'system',NULL,'Чат створено! Очікуйте підключення оператора...','system',0,'2025-09-21 10:32:46'),
(3,2,'user',NULL,'У мене проблеми з хостингом','text',0,'2025-09-21 10:32:46'),
(4,2,'user',NULL,'оператор','text',1,'2025-09-21 10:32:53'),
(5,2,'user',NULL,'оператор','text',1,'2025-09-21 12:15:19'),
(6,2,'user',NULL,'Оператор','text',0,'2025-09-21 12:23:02'),
(7,2,'system',NULL,'Оператор Олександр підключився до чату','system',0,'2025-09-21 12:23:26'),
(8,2,'operator',5,'Доброго дня','text',0,'2025-09-21 12:23:29'),
(9,2,'user',NULL,'Завершити чат','text',0,'2025-09-21 12:23:57'),
(10,2,'system',NULL,'Чат закрито','system',1,'2025-09-21 12:24:06'),
(11,3,'system',NULL,'Чат створено! Очікуйте підключення оператора...','system',0,'2025-09-21 12:35:55'),
(12,3,'user',NULL,'У мене проблеми з хостингом','text',0,'2025-09-21 12:35:55'),
(13,3,'system',NULL,'Оператор Олександр підключився до чату','system',0,'2025-09-21 12:36:45'),
(14,3,'operator',5,'Як можу допомогти?','text',0,'2025-09-21 12:37:06'),
(15,3,'user',NULL,'Потрібен хостинг','text',0,'2025-09-21 12:37:24'),
(16,3,'system',NULL,'Чат закрито','system',1,'2025-09-21 12:37:52'),
(17,1,'system',NULL,'Оператор admin підключився до чату','system',0,'2025-09-23 07:22:11'),
(18,1,'operator',4,'Добрий день. Чим можу допомогти?','text',0,'2025-09-23 07:23:43'),
(19,1,'system',NULL,'Чат закрито оператором','system',0,'2025-09-23 07:24:55'),
(20,1,'system',NULL,'Чат закрито оператором','system',0,'2025-09-23 07:32:26'),
(21,1,'system',NULL,'Чат закрито оператором','system',0,'2025-09-23 07:32:36');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sthostdb`@`localhost`*/ /*!50003 TRIGGER update_session_activity 
    AFTER INSERT ON chat_messages 
    FOR EACH ROW 
BEGIN
    UPDATE chat_sessions 
    SET updated_at = NOW() 
    WHERE id = NEW.session_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_notifications`
--

DROP TABLE IF EXISTS `chat_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT NULL,
  `type` enum('new_chat','new_message','chat_transfer','urgent') NOT NULL,
  `message` text NOT NULL,
  `data` longtext DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chat_notifications_operator_read` (`operator_id`,`is_read`,`created_at`),
  CONSTRAINT `chat_notifications_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_notifications`
--

LOCK TABLES `chat_notifications` WRITE;
/*!40000 ALTER TABLE `chat_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_sessions`
--

DROP TABLE IF EXISTS `chat_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `session_key` varchar(64) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `status` enum('waiting','active','closed','transferred','inactive','expired','reset') DEFAULT 'waiting',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `subject` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_key` (`session_key`),
  KEY `user_id` (`user_id`),
  KEY `operator_id` (`operator_id`),
  KEY `idx_chat_sessions_status_priority` (`status`,`priority`,`created_at`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_sessions_ibfk_2` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_sessions`
--

LOCK TABLES `chat_sessions` WRITE;
/*!40000 ALTER TABLE `chat_sessions` DISABLE KEYS */;
INSERT INTO `chat_sessions` VALUES
(1,NULL,4,'test-session-key-2025','Тестовий Клієнт','test@example.com','closed','normal','Тестове звернення','2025-09-21 12:20:21','2025-09-23 07:32:36','2025-09-23 07:32:36'),
(2,NULL,5,'2301ce0168a8f39d017173d13d4ffe7f-1758450766','Гість','','closed','normal','Загальне питання','2025-09-21 10:32:46','2025-09-21 12:24:06','2025-09-21 12:24:06'),
(3,NULL,5,'8570e71b5e7f1a684c0aa230e2ee2d99-1758458155','Гість','','closed','normal','Загальне питання','2025-09-21 12:35:55','2025-09-21 12:37:52','2025-09-21 12:37:52');
/*!40000 ALTER TABLE `chat_sessions` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sthostdb`@`localhost`*/ /*!50003 TRIGGER log_session_actions
    AFTER UPDATE ON chat_sessions
    FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO operator_actions (operator_id, action_type, session_id, details)
        VALUES (NEW.operator_id, 
                CASE NEW.status 
                    WHEN 'active' THEN 'take_session'
                    WHEN 'closed' THEN 'close_session'
                    WHEN 'transferred' THEN 'transfer_session'
                END,
                NEW.id,
                JSON_OBJECT('old_status', OLD.status, 'new_status', NEW.status)
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `chat_settings`
--

DROP TABLE IF EXISTS `chat_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chat_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_settings`
--

LOCK TABLES `chat_settings` WRITE;
/*!40000 ALTER TABLE `chat_settings` DISABLE KEYS */;
INSERT INTO `chat_settings` VALUES
(1,'max_sessions_per_operator','5','Максимальна кількість сесій на оператора','2025-09-21 10:15:05'),
(2,'auto_assign_timeout','300','Час очікування автопризначення в секундах','2025-09-21 10:15:05'),
(3,'session_cleanup_days','30','Через скільки днів видаляти закриті сесії','2025-09-21 10:15:05'),
(4,'notification_cleanup_days','7','Через скільки днів видаляти старі уведомлення','2025-09-21 10:15:05'),
(5,'urgent_sms_enabled','1','Чи відправляти SMS для термінових повідомлень','2025-09-21 10:15:05'),
(6,'email_notifications_enabled','1','Чи відправляти email уведомлення','2025-09-21 10:15:05'),
(7,'chat_widget_enabled','1','Чи показувати віджет чату на сайті','2025-09-21 10:15:05');
/*!40000 ALTER TABLE `chat_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `chat_statistics`
--

DROP TABLE IF EXISTS `chat_statistics`;
/*!50001 DROP VIEW IF EXISTS `chat_statistics`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `chat_statistics` AS SELECT
 1 AS `date`,
  1 AS `total_sessions`,
  1 AS `closed_sessions`,
  1 AS `urgent_sessions`,
  1 AS `avg_session_duration`,
  1 AS `active_operators` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('complaint','suggestion','feedback','question') DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_requests`
--

DROP TABLE IF EXISTS `contact_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `form_type` enum('contact','reseller','support') DEFAULT 'contact',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('new','processing','resolved','closed') DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_requests`
--

LOCK TABLES `contact_requests` WRITE;
/*!40000 ALTER TABLE `contact_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `csrf_tokens`
--

DROP TABLE IF EXISTS `csrf_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `csrf_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `user_session` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_token` (`token`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `csrf_tokens`
--

LOCK TABLES `csrf_tokens` WRITE;
/*!40000 ALTER TABLE `csrf_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `csrf_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `default_dns_servers`
--

DROP TABLE IF EXISTS `default_dns_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `default_dns_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `server_address` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT 10,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `default_dns_servers`
--

LOCK TABLES `default_dns_servers` WRITE;
/*!40000 ALTER TABLE `default_dns_servers` DISABLE KEYS */;
INSERT INTO `default_dns_servers` VALUES
(1,'NS1 StormHosting','ns1.sthost.pro',1,1),
(2,'NS2 StormHosting','ns2.sthost.pro',2,1),
(3,'NS3 StormHosting','ns3.sthost.pro',3,1);
/*!40000 ALTER TABLE `default_dns_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_check_logs`
--

DROP TABLE IF EXISTS `domain_check_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_name` varchar(255) NOT NULL COMMENT 'Назва домену без зони',
  `domain_zone` varchar(50) NOT NULL COMMENT 'Доменна зона (.ua, .com, тощо)',
  `full_domain` varchar(255) NOT NULL COMMENT 'Повна назва домену',
  `is_available` tinyint(1) NOT NULL COMMENT 'Чи доступний домен',
  `check_method` enum('whois','dns','api') NOT NULL DEFAULT 'whois' COMMENT 'Метод перевірки',
  `check_time_ms` int(11) DEFAULT NULL COMMENT 'Час перевірки в мілісекундах',
  `user_ip` varchar(45) DEFAULT NULL COMMENT 'IP користувача',
  `session_id` varchar(128) DEFAULT NULL COMMENT 'ID сесії',
  `user_id` int(11) DEFAULT NULL COMMENT 'ID користувача (якщо авторизований)',
  `whois_response` text DEFAULT NULL COMMENT 'Відповідь WHOIS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_domain_check_name` (`domain_name`),
  KEY `idx_domain_check_zone` (`domain_zone`),
  KEY `idx_domain_check_full` (`full_domain`),
  KEY `idx_domain_check_date` (`created_at`),
  KEY `idx_domain_check_user` (`user_id`),
  KEY `idx_domain_check_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Логи перевірки доменів на доступність';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_check_logs`
--

LOCK TABLES `domain_check_logs` WRITE;
/*!40000 ALTER TABLE `domain_check_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_check_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_page_settings`
--

DROP TABLE IF EXISTS `domain_page_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_page_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Чи можна показувати на фронті',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_setting_key` (`setting_key`),
  KEY `idx_setting_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Налаштування сторінки реєстрації доменів';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_page_settings`
--

LOCK TABLES `domain_page_settings` WRITE;
/*!40000 ALTER TABLE `domain_page_settings` DISABLE KEYS */;
INSERT INTO `domain_page_settings` VALUES
(1,'hero_title','Знайдіть ідеальний домен для вашого проекту','string','Заголовок героїв секції','hero',1,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(2,'hero_subtitle','Підтримуємо всі популярні українські та міжнародні доменні зони','string','Підзаголовок героїв секції','hero',1,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(3,'search_placeholder','Введіть бажане доменне ім\'я','string','Плейсхолдер поля пошуку','search',1,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(4,'promo_text','Спеціальна пропозиція! Знижка 20% на реєстрацію доменів .ua','string','Промо текст','promo',1,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(5,'min_domain_length','2','number','Мінімальна довжина домену','validation',0,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(6,'max_domain_length','63','number','Максимальна довжина домену','validation',0,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(7,'enable_whois_privacy','true','boolean','Увімкнути захист WHOIS','features',0,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(8,'enable_auto_renewal','true','boolean','Увімкнути автопродовження','features',0,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(9,'popular_zones','[\"ua\",\"com.ua\",\"kiev.ua\",\"com\",\"net\",\"org\",\"pp.ua\"]','json','Популярні доменні зони','display',1,'2025-08-13 14:40:30','2025-08-13 14:40:30'),
(10,'featured_zones','[{\"zone\":\".ua\",\"discount\":10},{\"zone\":\".com.ua\",\"discount\":15}]','json','Рекомендовані зони зі знижками','promo',1,'2025-08-13 14:40:30','2025-08-13 14:40:30');
/*!40000 ALTER TABLE `domain_page_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_registrars`
--

DROP TABLE IF EXISTS `domain_registrars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_registrars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `api_endpoint` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `supported_zones` text DEFAULT NULL,
  `commission_percent` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `priority` int(11) DEFAULT 1 COMMENT 'Приоритет использования',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_registrars`
--

LOCK TABLES `domain_registrars` WRITE;
/*!40000 ALTER TABLE `domain_registrars` DISABLE KEYS */;
INSERT INTO `domain_registrars` VALUES
(1,'UA Registry',NULL,NULL,NULL,1,'[\"ua\",\"com.ua\",\"net.ua\",\"org.ua\",\"kiev.ua\",\"pp.ua\"]',5.00,'2025-08-04 15:17:42',1),
(2,'Backup Registrar',NULL,NULL,NULL,1,'[\"com\",\"net\",\"org\",\"info\",\"biz\"]',7.50,'2025-08-04 15:17:42',1);
/*!40000 ALTER TABLE `domain_registrars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `domain_search_statistics`
--

DROP TABLE IF EXISTS `domain_search_statistics`;
/*!50001 DROP VIEW IF EXISTS `domain_search_statistics`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `domain_search_statistics` AS SELECT
 1 AS `search_date`,
  1 AS `domain_zone`,
  1 AS `total_checks`,
  1 AS `available_count`,
  1 AS `taken_count`,
  1 AS `avg_check_time_ms`,
  1 AS `unique_sessions`,
  1 AS `unique_users` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `domain_search_trends`
--

DROP TABLE IF EXISTS `domain_search_trends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_search_trends` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search_term` varchar(255) NOT NULL COMMENT 'Пошуковий запит',
  `search_count` int(11) NOT NULL DEFAULT 1 COMMENT 'Кількість пошуків',
  `last_searched` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_search_term` (`search_term`),
  KEY `idx_search_trends_count` (`search_count` DESC),
  KEY `idx_search_trends_date` (`last_searched`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Тренди пошуку доменів';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_search_trends`
--

LOCK TABLES `domain_search_trends` WRITE;
/*!40000 ALTER TABLE `domain_search_trends` DISABLE KEYS */;
/*!40000 ALTER TABLE `domain_search_trends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_whois_servers`
--

DROP TABLE IF EXISTS `domain_whois_servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_whois_servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` varchar(20) NOT NULL,
  `whois_server` varchar(255) NOT NULL,
  `port` int(11) DEFAULT 43,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_zone` (`zone`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_whois_servers`
--

LOCK TABLES `domain_whois_servers` WRITE;
/*!40000 ALTER TABLE `domain_whois_servers` DISABLE KEYS */;
INSERT INTO `domain_whois_servers` VALUES
(1,'.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(2,'.com.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(3,'.net.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(4,'.org.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(5,'.kiev.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(6,'.lviv.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(7,'.pp.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(8,'.co.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(9,'.in.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(10,'.biz.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(11,'.info.ua','whois.ua',43,1,'2025-08-04 15:16:09'),
(12,'.com','whois.verisign-grs.com',43,1,'2025-08-04 15:16:09'),
(13,'.net','whois.verisign-grs.com',43,1,'2025-08-04 15:16:09'),
(14,'.org','whois.pir.org',43,1,'2025-08-04 15:16:09'),
(15,'.info','whois.afilias.net',43,1,'2025-08-04 15:16:09'),
(16,'.biz','whois.neulevel.biz',43,1,'2025-08-04 15:16:09'),
(17,'.pro','whois.registrypro.pro',43,1,'2025-08-04 15:16:09');
/*!40000 ALTER TABLE `domain_whois_servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domain_zones`
--

DROP TABLE IF EXISTS `domain_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `domain_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone` varchar(20) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Опис доменної зони',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Особливості доменної зони' CHECK (json_valid(`features`)),
  `min_registration_period` int(11) NOT NULL DEFAULT 1 COMMENT 'Мінімальний період реєстрації в роках',
  `price_registration` decimal(10,2) NOT NULL,
  `price_renewal` decimal(10,2) NOT NULL,
  `price_transfer` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `max_registration_period` int(11) DEFAULT 10 COMMENT 'Максимальний період реєстрації',
  `grace_period_days` int(11) DEFAULT 30 COMMENT 'Період відновлення після закінчення',
  `whois_privacy_available` tinyint(1) DEFAULT 1 COMMENT 'Доступність приховування WHOIS',
  `auto_renewal_available` tinyint(1) DEFAULT 1 COMMENT 'Доступність автопродовження',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `zone` (`zone`),
  KEY `idx_zone` (`zone`),
  KEY `idx_popular` (`is_popular`),
  KEY `idx_active` (`is_active`),
  KEY `idx_domain_zones_popular` (`is_popular`,`is_active`),
  KEY `idx_domain_zones_type` (`zone`(10),`is_active`),
  KEY `idx_domain_zones_price` (`price_registration`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domain_zones`
--

LOCK TABLES `domain_zones` WRITE;
/*!40000 ALTER TABLE `domain_zones` DISABLE KEYS */;
INSERT INTO `domain_zones` VALUES
(1,'.ua','Український національний домен верхнього рівня. Ідеальний вибір для українських компаній та проектів.','[\"Національний домен\", \"Висока довіра\", \"Українська локалізація\"]',1,200.00,200.00,180.00,1,1,10,30,1,1,'2025-08-13 14:40:28'),
(2,'.com.ua','Комерційний домен України. Популярний вибір для бізнесу та електронної комерції.','[\"Для бізнесу\", \"Висока довіра\", \"Доступна ціна\"]',1,150.00,150.00,130.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(3,'.kiev.ua','Домен для Києва та Київської області. Ідеально підходить для місцевого бізнесу.','[\"Регіональний домен\", \"Локальний бізнес\", \"Географічна прив\'язка\"]',1,180.00,180.00,160.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(4,'.org.ua','Домен для організацій України. Підходить для некомерційних організацій.','[\"Для організацій\", \"Некомерційний сектор\", \"Соціальні проекти\"]',1,180.00,180.00,160.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(5,'.lviv.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(6,'.dp.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(7,'.kharkov.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(8,'.odessa.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(9,'.zp.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(10,'.vinnica.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(11,'.cherkassy.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(12,'.chernigov.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(13,'.crimea.ua',NULL,NULL,1,200.00,200.00,180.00,0,0,10,30,1,1,'2025-08-13 14:40:28'),
(14,'.cv.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(15,'.dn.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(16,'.if.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(17,'.kr.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(18,'.lg.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(19,'.mk.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(20,'.pl.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(21,'.rv.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(22,'.sm.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(23,'.te.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(24,'.uz.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(25,'.vn.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(26,'.volyn.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(27,'.zak.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(28,'.zt.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(29,'.net.ua',NULL,NULL,1,180.00,180.00,160.00,1,1,10,30,1,1,'2025-08-13 14:40:28'),
(30,'.co.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(31,'.pp.ua','Персональний домен для фізичних осіб України. Безкоштовна реєстрація.','[\"Для фізичних осіб\", \"Безкоштовний\", \"Персональні проекти\"]',1,120.00,120.00,100.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(32,'.in.ua',NULL,NULL,1,150.00,150.00,130.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(33,'.biz.ua',NULL,NULL,1,200.00,200.00,180.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(34,'.info.ua',NULL,NULL,1,180.00,180.00,160.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(35,'.name.ua',NULL,NULL,1,180.00,180.00,160.00,0,1,10,30,1,1,'2025-08-13 14:40:28'),
(36,'.edu.ua',NULL,NULL,1,0.00,0.00,0.00,0,0,10,30,1,1,'2025-08-13 14:40:28'),
(37,'.gov.ua',NULL,NULL,1,0.00,0.00,0.00,0,0,10,30,1,1,'2025-08-13 14:40:28'),
(38,'.mil.ua',NULL,NULL,1,0.00,0.00,0.00,0,0,10,30,1,1,'2025-08-13 14:40:28'),
(39,'.com','Найпопулярніший комерційний домен у світі. Універсальний вибір для будь-якого проекту.','[\"Світове визнання\", \"Максимальна довіра\", \"SEO переваги\"]',1,350.00,350.00,300.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(40,'.net','Міжнародний мережевий домен для IT та технічних проектів.','[\"Для технічних проектів\", \"IT сфера\", \"Міжнародний рівень\"]',1,450.00,450.00,400.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(41,'.org','Домен для некомерційних організацій. Підходить для фондів та громадських організацій.','[\"Некомерційні організації\", \"Благодійність\", \"Громадські проекти\"]',1,400.00,400.00,350.00,1,1,10,30,1,1,'2025-08-13 14:40:29'),
(42,'.info','Інформаційний домен для довідкових та інформаційних ресурсів.','[\"Інформаційні проекти\", \"Довідкові ресурси\", \"База знань\"]',1,300.00,350.00,300.00,0,1,10,30,1,1,'2025-08-13 14:40:29'),
(43,'.biz','Бізнес-домен для комерційних проектів та стартапів.','[\"Для бізнесу\", \"Стартапи\", \"B2B проекти\"]',1,350.00,400.00,350.00,0,1,10,30,1,1,'2025-08-13 14:40:29'),
(44,'.pro','Професійний домен для фахівців та експертів.','[\"Для професіоналів\", \"Експерти\", \"Консультанти\"]',1,400.00,450.00,400.00,0,1,10,30,1,1,'2025-08-13 14:40:29');
/*!40000 ALTER TABLE `domain_zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sthostdb`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_domain_zones_updated_at`
    BEFORE UPDATE ON `domain_zones`
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `email_verifications`
--

DROP TABLE IF EXISTS `email_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verifications`
--

LOCK TABLES `email_verifications` WRITE;
/*!40000 ALTER TABLE `email_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hosting_plans`
--

DROP TABLE IF EXISTS `hosting_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hosting_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ua` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `type` enum('shared','cloud','reseller') NOT NULL,
  `disk_space` int(11) NOT NULL,
  `bandwidth` int(11) NOT NULL,
  `databases` int(11) DEFAULT 0,
  `email_accounts` int(11) DEFAULT 0,
  `domains` int(11) DEFAULT 1,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `features_ua` text DEFAULT NULL,
  `features_en` text DEFAULT NULL,
  `features_ru` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_popular` (`is_popular`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hosting_plans`
--

LOCK TABLES `hosting_plans` WRITE;
/*!40000 ALTER TABLE `hosting_plans` DISABLE KEYS */;
INSERT INTO `hosting_plans` VALUES
(1,'Базовий','Basic','Базовый','shared',1024,10,1,5,1,99.00,990.00,0,1,'SSL сертифікат, Підтримка PHP, MySQL база даних',NULL,NULL),
(2,'Стандарт','Standard','Стандарт','shared',5120,50,5,20,5,199.00,1990.00,1,1,'SSL сертифікат, Підтримка PHP, MySQL бази даних, Безлімітні домени',NULL,NULL),
(3,'Преміум','Premium','Премиум','shared',10240,100,10,50,0,399.00,3990.00,0,1,'SSL сертифікат, Підтримка PHP, MySQL бази даних, Безлімітні домени, SSD накопичувач',NULL,NULL);
/*!40000 ALTER TABLE `hosting_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blacklist_cache`
--

DROP TABLE IF EXISTS `ip_blacklist_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blacklist_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `rbl_name` varchar(100) NOT NULL,
  `is_listed` tinyint(1) NOT NULL,
  `response_code` varchar(20) DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_rbl` (`ip_address`,`rbl_name`),
  KEY `idx_ip_rbl` (`ip_address`,`rbl_name`),
  KEY `idx_checked` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blacklist_cache`
--

LOCK TABLES `ip_blacklist_cache` WRITE;
/*!40000 ALTER TABLE `ip_blacklist_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blacklist_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_check_logs`
--

DROP TABLE IF EXISTS `ip_check_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `checked_ip` varchar(45) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `results_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`created_at`),
  KEY `idx_checked_ip` (`checked_ip`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_check_logs`
--

LOCK TABLES `ip_check_logs` WRITE;
/*!40000 ALTER TABLE `ip_check_logs` DISABLE KEYS */;
INSERT INTO `ip_check_logs` VALUES
(1,'93.170.44.119','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"93.170.44.119\",\"timestamp\":\"2025-08-09T18:24:47+03:00\",\"general\":{\"ip\":\"93.170.44.119\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:24:47+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk Oblast\",\"city\":\"Dnipro\",\"postal\":\"49000\",\"latitude\":48.4604,\"longitude\":35.033000000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":null,\"weather\":{\"temperature\":15,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":42,\"wind_speed\":3,\"visibility\":11}}','2025-08-09 15:24:51'),
(2,'93.170.44.119','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"93.170.44.119\",\"timestamp\":\"2025-08-09T18:24:49+03:00\",\"general\":{\"ip\":\"93.170.44.119\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:24:49+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk Oblast\",\"city\":\"Dnipro\",\"postal\":\"49000\",\"latitude\":48.4604,\"longitude\":35.033000000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":null,\"weather\":{\"temperature\":23,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":69,\"wind_speed\":9,\"visibility\":7}}','2025-08-09 15:24:51'),
(3,'194.44.7.103','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-09T18:25:03+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:25:03+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":true,\"checked\":true,\"response\":\"127.0.0.2\"},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":16,\"miles\":10,\"flight_time\":\"1 хв\"},\"weather\":{\"temperature\":22,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":52,\"wind_speed\":4,\"visibility\":14}}','2025-08-09 15:25:06'),
(4,'194.44.7.103','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-09T18:25:16+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:25:16+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":true,\"checked\":true,\"response\":\"127.0.0.2\"},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":16,\"miles\":10,\"flight_time\":\"1 хв\"},\"weather\":{\"temperature\":15,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":50,\"wind_speed\":8,\"visibility\":15}}','2025-08-09 15:25:19'),
(7,'8.8.8.8','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"8.8.8.8\",\"timestamp\":\"2025-08-09T18:28:05+03:00\",\"general\":{\"ip\":\"8.8.8.8\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:28:05+03:00\"},\"location\":{\"country\":\"United States\",\"country_code\":\"US\",\"region\":\"California\",\"city\":\"Mountain View\",\"postal\":\"94043\",\"latitude\":37.423009999999998,\"longitude\":-122.083352,\"timezone\":\"America\\/Los_Angeles\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":9883,\"miles\":6141,\"flight_time\":\"11 год\"},\"weather\":{\"temperature\":21,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":74,\"wind_speed\":5,\"visibility\":15}}','2025-08-09 15:28:08'),
(8,'194.44.7.103','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-09T18:28:15+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:28:15+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":true,\"checked\":true,\"response\":\"127.0.0.2\"},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":377,\"miles\":234,\"flight_time\":\"25 хв\"},\"weather\":{\"temperature\":16,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":64,\"wind_speed\":10,\"visibility\":7}}','2025-08-09 15:28:18'),
(10,'93.170.44.119','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"93.170.44.119\",\"timestamp\":\"2025-08-09T18:28:49+03:00\",\"general\":{\"ip\":\"93.170.44.119\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:28:49+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk Oblast\",\"city\":\"Dnipro\",\"postal\":\"49000\",\"latitude\":48.4604,\"longitude\":35.033000000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":394,\"miles\":245,\"flight_time\":\"26 хв\"},\"weather\":{\"temperature\":15,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":64,\"wind_speed\":4,\"visibility\":7}}','2025-08-09 15:28:52'),
(13,'93.170.44.119','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"93.170.44.119\",\"timestamp\":\"2025-08-09T18:29:16+03:00\",\"general\":{\"ip\":\"93.170.44.119\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T18:29:16+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk Oblast\",\"city\":\"Dnipro\",\"postal\":\"49000\",\"latitude\":48.4604,\"longitude\":35.033000000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":0,\"miles\":0,\"flight_time\":\"0 хв\"},\"weather\":{\"temperature\":19,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":43,\"wind_speed\":6,\"visibility\":7}}','2025-08-09 15:29:19'),
(14,'8.8.8.8','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"8.8.8.8\",\"timestamp\":\"2025-08-09T20:27:16+03:00\",\"general\":{\"ip\":\"8.8.8.8\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T20:27:16+03:00\"},\"location\":{\"country\":\"United States\",\"country_code\":\"US\",\"region\":\"California\",\"city\":\"Mountain View\",\"postal\":\"94043\",\"latitude\":37.423009999999998,\"longitude\":-122.083352,\"timezone\":\"America\\/Los_Angeles\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":10201,\"miles\":6339,\"flight_time\":\"11.3 год\"},\"weather\":{\"temperature\":19,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":53,\"wind_speed\":9,\"visibility\":14}}','2025-08-09 17:27:19'),
(15,'1.1.1.1','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"1.1.1.1\",\"timestamp\":\"2025-08-09T20:27:18+03:00\",\"general\":{\"ip\":\"1.1.1.1\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T20:27:18+03:00\"},\"location\":{\"country\":\"Australia\",\"country_code\":\"AU\",\"region\":\"New South Wales\",\"city\":\"Sydney\",\"postal\":\"2000\",\"latitude\":-33.859335999999999,\"longitude\":151.20362399999999,\"timezone\":\"Australia\\/Sydney\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":14599,\"miles\":9071,\"flight_time\":\"16.2 год\"},\"weather\":{\"temperature\":25,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":58,\"wind_speed\":5,\"visibility\":14}}','2025-08-09 17:27:21'),
(16,'208.67.222.222','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"208.67.222.222\",\"timestamp\":\"2025-08-09T20:27:18+03:00\",\"general\":{\"ip\":\"208.67.222.222\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-09T20:27:18+03:00\"},\"location\":{\"country\":\"United States\",\"country_code\":\"US\",\"region\":\"California\",\"city\":\"San Francisco\",\"postal\":\"94107\",\"latitude\":37.774777999999998,\"longitude\":-122.397966,\"timezone\":\"America\\/Los_Angeles\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":10170,\"miles\":6319,\"flight_time\":\"11.3 год\"},\"weather\":{\"temperature\":19,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":45,\"wind_speed\":8,\"visibility\":9}}','2025-08-09 17:27:21'),
(17,'194.44.7.103','93.170.44.31','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-13T17:44:23+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-13T17:44:23+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":true,\"checked\":true,\"response\":\"127.0.0.2\"},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":null,\"distance\":null,\"weather\":{\"temperature\":20,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":70,\"wind_speed\":6,\"visibility\":9}}','2025-08-13 14:44:26'),
(18,'194.44.7.103','93.170.44.31','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-13T17:44:35+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-13T17:44:35+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":[{\"name\":\"Spamhaus ZEN\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SpamCop\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Barracuda\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Spam\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS HTTP\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS SOCKS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Misc\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS Zombie\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"SORBS DUL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 1\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 2\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"UCEPROTECT Level 3\",\"listed\":true,\"checked\":true,\"response\":\"127.0.0.2\"},{\"name\":\"Spamhaus PBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus SBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus CSS\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Spamhaus XBL\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Composite Blocking List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Passive Spam Block List\",\"listed\":false,\"checked\":true,\"response\":null},{\"name\":\"Lashback UBL\",\"listed\":false,\"checked\":true,\"response\":null}],\"threats\":{\"risk_level\":\"Низький\",\"confidence\":0,\"categories\":[],\"last_seen\":null},\"distance\":{\"km\":16,\"miles\":10,\"flight_time\":\"1 хв\"},\"weather\":{\"temperature\":15,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":68,\"wind_speed\":4,\"visibility\":9}}','2025-08-13 14:44:38'),
(19,'194.44.7.103','93.170.44.31','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"ip\":\"194.44.7.103\",\"timestamp\":\"2025-08-13T17:44:43+03:00\",\"general\":{\"ip\":\"194.44.7.103\",\"is_valid\":true,\"ip_type\":\"Публічна IPv4\",\"check_time\":\"2025-08-13T17:44:43+03:00\"},\"location\":{\"country\":\"Ukraine\",\"country_code\":\"UA\",\"region\":\"Dnipropetrovsk\",\"city\":\"Obukhivka\",\"postal\":\"\",\"latitude\":48.548839999999998,\"longitude\":34.853870000000001,\"timezone\":\"Europe\\/Kyiv\"},\"network\":{\"isp\":\"Невідомо\",\"org\":\"Невідомо\",\"asn\":\"\",\"connection_type\":\"Невідомо\",\"usage_type\":\"Невідомо\",\"is_proxy\":false},\"blacklists\":null,\"threats\":null,\"distance\":null,\"weather\":{\"temperature\":21,\"description\":\"Хмарно\",\"condition\":\"cloudy\",\"humidity\":45,\"wind_speed\":7,\"visibility\":11}}','2025-08-13 14:44:44');
/*!40000 ALTER TABLE `ip_check_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_check_stats`
--

DROP TABLE IF EXISTS `ip_check_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_check_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_checked` date NOT NULL,
  `total_checks` int(11) DEFAULT 0,
  `unique_ips` int(11) DEFAULT 0,
  `blacklisted_count` int(11) DEFAULT 0,
  `threats_detected` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date_checked`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_check_stats`
--

LOCK TABLES `ip_check_stats` WRITE;
/*!40000 ALTER TABLE `ip_check_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_check_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_geolocation_cache`
--

DROP TABLE IF EXISTS `ip_geolocation_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_geolocation_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_geolocation_cache`
--

LOCK TABLES `ip_geolocation_cache` WRITE;
/*!40000 ALTER TABLE `ip_geolocation_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_geolocation_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `location_stats`
--

DROP TABLE IF EXISTS `location_stats`;
/*!50001 DROP VIEW IF EXISTS `location_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `location_stats` AS SELECT
 1 AS `location`,
  1 AS `checks_count`,
  1 AS `avg_response_time`,
  1 AS `success_count` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NULL DEFAULT current_timestamp(),
  `locked_until` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_email` (`email`),
  KEY `idx_locked` (`locked_until`),
  KEY `idx_login_attempts_ip_time` (`ip_address`,`last_attempt`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES
(1,'51.159.226.126','nfsdante@gmail.com',4,'2025-08-24 09:46:50',NULL);
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title_ua` varchar(255) NOT NULL,
  `content_ua` text NOT NULL,
  `content_en` text DEFAULT NULL,
  `content_ru` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_published` (`is_published`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_stats`
--

DROP TABLE IF EXISTS `newsletter_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('sent','delivered','opened','clicked','bounced','unsubscribed') NOT NULL,
  `opened_at` datetime DEFAULT NULL,
  `clicked_at` datetime DEFAULT NULL,
  `bounce_reason` text DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `subscriber_id` (`subscriber_id`),
  KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_stats`
--

LOCK TABLES `newsletter_stats` WRITE;
/*!40000 ALTER TABLE `newsletter_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscribers`
--

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_templates`
--

DROP TABLE IF EXISTS `newsletter_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_template` varchar(255) DEFAULT NULL,
  `html_content` longtext NOT NULL,
  `text_content` longtext DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_templates`
--

LOCK TABLES `newsletter_templates` WRITE;
/*!40000 ALTER TABLE `newsletter_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_actions`
--

DROP TABLE IF EXISTS `operator_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operator_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL,
  `action_type` enum('login','logout','take_session','close_session','transfer_session','send_message') NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_operator_actions_operator` (`operator_id`,`created_at`),
  KEY `idx_operator_actions_session` (`session_id`),
  KEY `idx_operator_actions_type` (`action_type`,`created_at`),
  CONSTRAINT `operator_actions_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_actions`
--

LOCK TABLES `operator_actions` WRITE;
/*!40000 ALTER TABLE `operator_actions` DISABLE KEYS */;
INSERT INTO `operator_actions` VALUES
(1,5,'take_session',2,'{\"old_status\": \"waiting\", \"new_status\": \"active\"}',NULL,NULL,'2025-09-21 12:23:26'),
(2,5,'close_session',2,'{\"old_status\": \"active\", \"new_status\": \"closed\"}',NULL,NULL,'2025-09-21 12:24:06'),
(3,5,'take_session',3,'{\"old_status\": \"waiting\", \"new_status\": \"active\"}',NULL,NULL,'2025-09-21 12:36:45'),
(4,5,'close_session',3,'{\"old_status\": \"active\", \"new_status\": \"closed\"}',NULL,NULL,'2025-09-21 12:37:52'),
(5,4,'take_session',1,'{\"old_status\": \"waiting\", \"new_status\": \"active\"}',NULL,NULL,'2025-09-23 07:22:11'),
(6,4,'close_session',1,'{\"old_status\": \"active\", \"new_status\": \"closed\"}',NULL,NULL,'2025-09-23 07:24:55');
/*!40000 ALTER TABLE `operator_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `operator_performance`
--

DROP TABLE IF EXISTS `operator_performance`;
/*!50001 DROP VIEW IF EXISTS `operator_performance`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `operator_performance` AS SELECT
 1 AS `id`,
  1 AS `name`,
  1 AS `role`,
  1 AS `total_sessions`,
  1 AS `completed_sessions`,
  1 AS `avg_resolution_time`,
  1 AS `urgent_handled`,
  1 AS `is_online`,
  1 AS `current_sessions`,
  1 AS `last_activity` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `operator_status`
--

DROP TABLE IF EXISTS `operator_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operator_status` (
  `operator_id` int(11) NOT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `status_message` varchar(255) DEFAULT 'Доступний',
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `current_sessions` int(11) DEFAULT 0,
  `max_sessions` int(11) DEFAULT 5,
  PRIMARY KEY (`operator_id`),
  KEY `idx_operator_status_online` (`is_online`,`last_activity`),
  CONSTRAINT `operator_status_ibfk_1` FOREIGN KEY (`operator_id`) REFERENCES `support_operators` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_status`
--

LOCK TABLES `operator_status` WRITE;
/*!40000 ALTER TABLE `operator_status` DISABLE KEYS */;
INSERT INTO `operator_status` VALUES
(1,0,'Доступний','2025-09-21 12:19:53',0,5),
(2,0,'Доступний','2025-09-21 12:19:53',0,3),
(3,0,'Адміністратор','2025-09-21 12:19:53',0,10),
(4,1,'Доступний','2025-09-23 07:33:05',0,5),
(5,0,'Доступний','2025-09-21 13:44:00',0,5);
/*!40000 ALTER TABLE `operator_status` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`sthostdb`@`localhost`*/ /*!50003 TRIGGER update_operator_activity 
    AFTER UPDATE ON operator_status 
    FOR EACH ROW 
BEGIN
    IF NEW.is_online = 1 THEN
        UPDATE support_operators 
        SET last_activity = NOW() 
        WHERE id = NEW.operator_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_email` (`email`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `popular_checked_sites`
--

DROP TABLE IF EXISTS `popular_checked_sites`;
/*!50001 DROP VIEW IF EXISTS `popular_checked_sites`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `popular_checked_sites` AS SELECT
 1 AS `domain`,
  1 AS `check_count`,
  1 AS `avg_response_time`,
  1 AS `last_checked` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `popular_domains_view`
--

DROP TABLE IF EXISTS `popular_domains_view`;
/*!50001 DROP VIEW IF EXISTS `popular_domains_view`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `popular_domains_view` AS SELECT
 1 AS `zone`,
  1 AS `price_registration`,
  1 AS `price_renewal`,
  1 AS `price_transfer`,
  1 AS `description`,
  1 AS `domain_type`,
  1 AS `price_category`,
  1 AS `features`,
  1 AS `whois_privacy_available`,
  1 AS `auto_renewal_available` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remember_tokens`
--

LOCK TABLES `remember_tokens` WRITE;
/*!40000 ALTER TABLE `remember_tokens` DISABLE KEYS */;
INSERT INTO `remember_tokens` VALUES
(1,1,'7d5fabfbb7ebd4a0eb3f4293aaf760a54f3ecc0787278c80452db8d8ad1c8cb1','2025-08-17 09:04:42','2025-09-16 09:04:42'),
(2,1,'9885cc764e13ad1a59b3c7bd2a464a683558598fd503b2b430d3d98c7f334750','2025-08-17 09:04:42','2025-09-16 09:04:42'),
(3,1,'a0f779ff0f9a7bc89b15fff181b3280cdd4e15691aa55afb37b1b8e9c773633b','2025-08-17 09:09:18','2025-09-16 09:09:18'),
(5,1,'ce887f8d7e2bbf6875c03054f7b7e7c675fc20f1dd370e073cca75d7e48cf78a','2025-08-17 09:21:33','2025-09-16 09:21:33'),
(7,1,'36b47fadcf9960b9f29cebc6524d1beb63111471bc2c543d8926f7d85d24d1cd','2025-08-17 09:28:01','2025-09-16 09:28:01'),
(9,1,'93955681777ddefbeaee44176c0285c570d084a8d9f4f881010aec3f8b8f1c96','2025-08-17 09:56:01','2025-09-16 09:56:01');
/*!40000 ALTER TABLE `remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_logs`
--

DROP TABLE IF EXISTS `security_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_severity` (`severity`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2808 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_logs`
--

LOCK TABLES `security_logs` WRITE;
/*!40000 ALTER TABLE `security_logs` DISABLE KEYS */;
INSERT INTO `security_logs` VALUES
(1,'104.197.69.115',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-04 14:56:55'),
(2,'93.170.44.119',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-06 10:34:46'),
(3,'93.170.44.119',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-06 10:34:51'),
(4,'93.170.44.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:01:42'),
(5,'93.170.44.31',1,'user_registration','Успішна реєстрація користувача. FOSSBilling ID: , ISPManager: помилка','low','2025-08-17 09:01:42'),
(6,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:01:42'),
(7,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:01:44'),
(8,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:01:58'),
(9,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:04:42'),
(10,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:04:42'),
(11,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:04:42'),
(12,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:04:42'),
(13,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:04:43'),
(14,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:04:58'),
(15,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:05:47'),
(16,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:09:18'),
(17,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:09:18'),
(18,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:09:18'),
(19,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:09:18'),
(20,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:09:19'),
(21,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:09:19'),
(22,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:19:33'),
(23,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:18'),
(24,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:19'),
(25,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:22'),
(26,'93.170.44.31',1,'user_logout','Користувач вийшов з системи','low','2025-08-17 09:21:22'),
(27,'93.170.44.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:33'),
(28,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:21:33'),
(29,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:33'),
(30,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:21:33'),
(31,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:34'),
(32,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:21:47'),
(33,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:22:00'),
(34,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:23:45'),
(35,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:25:34'),
(36,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:25:41'),
(37,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:26:21'),
(38,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:26:25'),
(39,'93.170.44.31',1,'user_logout','Користувач вийшов з системи','low','2025-08-17 09:26:25'),
(40,'93.170.44.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:28:01'),
(41,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:28:01'),
(42,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:28:01'),
(43,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:28:01'),
(44,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:28:02'),
(45,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:32:21'),
(46,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:32:24'),
(47,'93.170.44.31',1,'user_logout','Користувач вийшов з системи','low','2025-08-17 09:32:24'),
(48,'93.170.44.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:01'),
(49,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:56:01'),
(50,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:01'),
(51,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 09:56:01'),
(52,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:02'),
(53,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:03'),
(54,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:11'),
(55,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:44'),
(56,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:46'),
(57,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:48'),
(58,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:50'),
(59,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 09:56:55'),
(60,'93.170.44.31',1,'user_logout','Користувач вийшов з системи','low','2025-08-17 09:56:55'),
(61,'46.250.30.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:12:07'),
(62,'46.250.30.31',2,'user_registration','Успішна реєстрація користувача. FOSSBilling ID: , ISPManager: помилка','low','2025-08-17 13:12:07'),
(63,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:12:07'),
(64,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:12:09'),
(65,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:17:50'),
(66,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:18:19'),
(67,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:18:43'),
(68,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:19:08'),
(69,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:19:40'),
(70,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:20:28'),
(71,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:20:30'),
(72,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:22'),
(73,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:26'),
(74,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:29'),
(75,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:31'),
(76,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:58'),
(77,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:21:59'),
(78,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:22:06'),
(79,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:22:10'),
(80,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:22:12'),
(81,'46.250.30.31',2,'database_connect','Успешное подключение к основной БД','low','2025-08-17 13:23:50'),
(82,'93.170.44.31',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:09:52'),
(83,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 14:09:53'),
(84,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:09:53'),
(85,'93.170.44.31',1,'user_login','Успішний вхід в систему','low','2025-08-17 14:09:53'),
(86,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:09:54'),
(87,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:09:55'),
(88,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:10:52'),
(89,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:11:33'),
(90,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:27:34'),
(91,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:27:37'),
(92,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:27:45'),
(93,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:28:58'),
(94,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:30:51'),
(95,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:31:05'),
(96,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:31:20'),
(97,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:31:29'),
(98,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:04'),
(99,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:05'),
(100,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:07'),
(101,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:13'),
(102,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:22'),
(103,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:26'),
(104,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:33'),
(105,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:35'),
(106,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:33:40'),
(107,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:35:05'),
(108,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 14:35:06'),
(109,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 16:16:18'),
(110,'93.170.44.31',1,'database_connect','Успешное подключение к основной БД','low','2025-08-17 16:16:26'),
(111,'93.170.44.31',1,'user_logout','Користувач вийшов з системи','low','2025-08-17 16:16:26'),
(112,'51.159.226.126',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-24 09:46:36'),
(113,'51.159.226.126',NULL,'login_error','Невірний email або пароль','medium','2025-08-24 09:46:36'),
(114,'51.159.226.126',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-24 09:46:36'),
(115,'51.159.226.126',NULL,'login_error','Невірний email або пароль','medium','2025-08-24 09:46:36'),
(116,'51.159.226.126',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-24 09:46:50'),
(117,'51.159.226.126',NULL,'login_error','Невірний email або пароль','medium','2025-08-24 09:46:50'),
(118,'51.159.226.126',NULL,'database_connect','Успешное подключение к основной БД','low','2025-08-24 09:46:50'),
(119,'51.159.226.126',NULL,'login_error','Невірний email або пароль','medium','2025-08-24 09:46:50'),
(120,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:41'),
(121,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:44'),
(122,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:46'),
(123,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:46'),
(124,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:46'),
(125,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:47'),
(126,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:50'),
(127,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:53'),
(128,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:53'),
(129,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:56'),
(130,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:32:59'),
(131,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:02'),
(132,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:05'),
(133,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:08'),
(134,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:11'),
(135,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:14'),
(136,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:17'),
(137,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:20'),
(138,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:23'),
(139,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:26'),
(140,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:29'),
(141,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:32'),
(142,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:35'),
(143,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:38'),
(144,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:41'),
(145,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:44'),
(146,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:47'),
(147,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:50'),
(148,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:53'),
(149,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:56'),
(150,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:33:59'),
(151,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:34:02'),
(152,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:34:17'),
(153,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:35:17'),
(154,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:36:17'),
(155,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:37:17'),
(156,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:38:17'),
(157,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:39:17'),
(158,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:40:17'),
(159,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:41:17'),
(160,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:42:17'),
(161,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:43:17'),
(162,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:44:17'),
(163,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:45:17'),
(164,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:46:17'),
(165,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:47:17'),
(166,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:48:17'),
(167,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:49:17'),
(168,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:50:17'),
(169,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:51:17'),
(170,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:52:17'),
(171,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:53:17'),
(172,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:54:17'),
(173,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:55:17'),
(174,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:56:17'),
(175,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:57:17'),
(176,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:58:17'),
(177,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 10:59:17'),
(178,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:00:17'),
(179,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:01:17'),
(180,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:02:17'),
(181,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:03:17'),
(182,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:04:17'),
(183,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:05:17'),
(184,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:06:17'),
(185,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:07:17'),
(186,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:08:17'),
(187,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:09:17'),
(188,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:10:17'),
(189,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:11:17'),
(190,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:12:17'),
(191,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:13:17'),
(192,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:14:17'),
(193,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:15:17'),
(194,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:16:17'),
(195,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:17:17'),
(196,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:18:17'),
(197,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:19:17'),
(198,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:20:17'),
(199,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:21:17'),
(200,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:22:17'),
(201,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:23:17'),
(202,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:24:17'),
(203,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:25:17'),
(204,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:26:17'),
(205,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:27:17'),
(206,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:28:17'),
(207,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:29:17'),
(208,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:30:17'),
(209,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:31:17'),
(210,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:32:17'),
(211,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:33:17'),
(212,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:34:17'),
(213,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:35:17'),
(214,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:36:17'),
(215,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:37:17'),
(216,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:38:17'),
(217,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:39:17'),
(218,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:40:17'),
(219,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:41:17'),
(220,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:42:17'),
(221,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:43:17'),
(222,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:44:17'),
(223,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:45:17'),
(224,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:46:17'),
(225,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:47:17'),
(226,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:48:17'),
(227,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:49:17'),
(228,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:50:17'),
(229,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:51:17'),
(230,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:52:17'),
(231,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:53:17'),
(232,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:54:17'),
(233,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:55:17'),
(234,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:56:17'),
(235,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:57:17'),
(236,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:58:17'),
(237,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 11:59:17'),
(238,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:00:17'),
(239,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:01:17'),
(240,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:02:17'),
(241,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:03:17'),
(242,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:04:17'),
(243,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:05:17'),
(244,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:06:17'),
(245,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:07:17'),
(246,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:08:17'),
(247,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:09:17'),
(248,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:10:17'),
(249,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:11:17'),
(250,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:12:17'),
(251,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:13:17'),
(252,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:14:17'),
(253,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:11'),
(254,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:12'),
(255,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:12'),
(256,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:13'),
(257,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:13'),
(258,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:14'),
(259,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:14'),
(260,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:14'),
(261,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:17'),
(262,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:19'),
(263,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:20'),
(264,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:23'),
(265,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:26'),
(266,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:29'),
(267,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:32'),
(268,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:35'),
(269,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:38'),
(270,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:41'),
(271,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:44'),
(272,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:47'),
(273,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:50'),
(274,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:53'),
(275,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:56'),
(276,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:15:59'),
(277,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:00'),
(278,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:01'),
(279,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:02'),
(280,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:03'),
(281,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:03'),
(282,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:04'),
(283,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:05'),
(284,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:07'),
(285,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:08'),
(286,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:10'),
(287,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:11'),
(288,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:13'),
(289,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:14'),
(290,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:16'),
(291,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:17'),
(292,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:19'),
(293,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:20'),
(294,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:22'),
(295,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:23'),
(296,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:25'),
(297,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:28'),
(298,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:31'),
(299,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:34'),
(300,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:37'),
(301,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:40'),
(302,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:43'),
(303,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:46'),
(304,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:49'),
(305,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:52'),
(306,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:55'),
(307,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:16:58'),
(308,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:01'),
(309,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:04'),
(310,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:17'),
(311,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:17'),
(312,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:19'),
(313,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:22'),
(314,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:25'),
(315,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:28'),
(316,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:31'),
(317,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:34'),
(318,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:37'),
(319,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:40'),
(320,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:43'),
(321,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:46'),
(322,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:49'),
(323,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:52'),
(324,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:55'),
(325,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:17:58'),
(326,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:01'),
(327,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:04'),
(328,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:07'),
(329,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:10'),
(330,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:13'),
(331,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:16'),
(332,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:18:17'),
(333,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:19:17'),
(334,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:19:17'),
(335,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:20:17'),
(336,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:20:17'),
(337,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:17'),
(338,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:17'),
(339,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:23'),
(340,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:24'),
(341,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:25'),
(342,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:25'),
(343,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:26'),
(344,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:26'),
(345,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:29'),
(346,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:32'),
(347,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:35'),
(348,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:38'),
(349,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:40'),
(350,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:40'),
(351,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:41'),
(352,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:42'),
(353,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:42'),
(354,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:43'),
(355,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:44'),
(356,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:46'),
(357,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:47'),
(358,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:49'),
(359,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:50'),
(360,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:52'),
(361,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:53'),
(362,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:55'),
(363,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:56'),
(364,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:58'),
(365,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:21:59'),
(366,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:01'),
(367,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:02'),
(368,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:04'),
(369,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:05'),
(370,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:07'),
(371,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:08'),
(372,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:10'),
(373,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:11'),
(374,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:13'),
(375,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:14'),
(376,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:16'),
(377,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:17'),
(378,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:19'),
(379,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:20'),
(380,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:22'),
(381,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:23'),
(382,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:25'),
(383,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:28'),
(384,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:31'),
(385,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:34'),
(386,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:37'),
(387,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:40'),
(388,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:43'),
(389,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:46'),
(390,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:49'),
(391,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:50'),
(392,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:52'),
(393,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:53'),
(394,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:53'),
(395,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:53'),
(396,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:54'),
(397,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:55'),
(398,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:56'),
(399,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:22:57'),
(400,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:00'),
(401,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:02'),
(402,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:03'),
(403,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:04'),
(404,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:07'),
(405,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:07'),
(406,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:09'),
(407,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(408,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(409,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(410,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(411,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(412,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:10'),
(413,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:11'),
(414,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:11'),
(415,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:12'),
(416,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:12'),
(417,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:13'),
(418,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:13'),
(419,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:13'),
(420,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:14'),
(421,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:16'),
(422,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:16'),
(423,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:16'),
(424,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:19'),
(425,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:19'),
(426,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:19'),
(427,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:20'),
(428,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:22'),
(429,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:24'),
(430,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:24'),
(431,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:25'),
(432,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:25'),
(433,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:25'),
(434,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:26'),
(435,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:26'),
(436,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:26'),
(437,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:28'),
(438,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:28'),
(439,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:28'),
(440,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:29'),
(441,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:31'),
(442,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:31'),
(443,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:31'),
(444,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:33'),
(445,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:34'),
(446,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:34'),
(447,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:36'),
(448,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:37'),
(449,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:37'),
(450,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:39'),
(451,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:40'),
(452,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:40'),
(453,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:42'),
(454,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:43'),
(455,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:43'),
(456,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:45'),
(457,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:46'),
(458,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:46'),
(459,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:48'),
(460,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:49'),
(461,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:49'),
(462,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:51'),
(463,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:52'),
(464,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:52'),
(465,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:54'),
(466,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:55'),
(467,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:55'),
(468,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:56'),
(469,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:57'),
(470,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:58'),
(471,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:23:58'),
(472,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:01'),
(473,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:01'),
(474,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:01'),
(475,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:04'),
(476,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:06'),
(477,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:06'),
(478,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:06'),
(479,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:06'),
(480,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:07'),
(481,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:07'),
(482,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:09'),
(483,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:10'),
(484,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:11'),
(485,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:11'),
(486,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:12'),
(487,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:13'),
(488,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:14'),
(489,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:16'),
(490,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:18'),
(491,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:19'),
(492,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:20'),
(493,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:21'),
(494,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:21'),
(495,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:22'),
(496,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:22'),
(497,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:24'),
(498,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:24'),
(499,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:25'),
(500,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:25'),
(501,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:27'),
(502,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:27'),
(503,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:28'),
(504,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:28'),
(505,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:28'),
(506,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:30'),
(507,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:31'),
(508,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:31'),
(509,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:32'),
(510,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:33'),
(511,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:34'),
(512,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:34'),
(513,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:35'),
(514,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:36'),
(515,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:36'),
(516,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:37'),
(517,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:37'),
(518,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:37'),
(519,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:39'),
(520,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:42'),
(521,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:45'),
(522,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:48'),
(523,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:51'),
(524,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:54'),
(525,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:24:57'),
(526,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:00'),
(527,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:03'),
(528,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:06'),
(529,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:09'),
(530,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:12'),
(531,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:15'),
(532,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:41'),
(533,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:41'),
(534,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:42'),
(535,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:44'),
(536,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:48'),
(537,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:51'),
(538,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:54'),
(539,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:57'),
(540,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:25:59'),
(541,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:03'),
(542,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:06'),
(543,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:09'),
(544,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:12'),
(545,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:15'),
(546,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:18'),
(547,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:21'),
(548,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:24'),
(549,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:27'),
(550,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:30'),
(551,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:33'),
(552,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:36'),
(553,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:39'),
(554,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:42'),
(555,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:45'),
(556,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:48'),
(557,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:51'),
(558,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:54'),
(559,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:26:57'),
(560,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:00'),
(561,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:08'),
(562,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:12'),
(563,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:15'),
(564,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:18'),
(565,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:21'),
(566,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:24'),
(567,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:27'),
(568,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:30'),
(569,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:33'),
(570,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:36'),
(571,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:39'),
(572,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:42'),
(573,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:45'),
(574,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:48'),
(575,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:51'),
(576,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:54'),
(577,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:27:57'),
(578,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:28:00'),
(579,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:28:03'),
(580,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:28:06'),
(581,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:28:09'),
(582,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:28:17'),
(583,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:29:17'),
(584,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:30:17'),
(585,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:30:23'),
(586,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:32:55'),
(587,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:32:58'),
(588,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:01'),
(589,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:05'),
(590,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:08'),
(591,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:11'),
(592,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:14'),
(593,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:17'),
(594,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:20'),
(595,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:23'),
(596,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:26'),
(597,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:29'),
(598,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:32'),
(599,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:35'),
(600,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:38'),
(601,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:41'),
(602,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:44'),
(603,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:47'),
(604,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:50'),
(605,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:53'),
(606,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:56'),
(607,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:33:59'),
(608,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:17'),
(609,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:25'),
(610,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:26'),
(611,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:26'),
(612,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:27'),
(613,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:29'),
(614,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:33'),
(615,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:36'),
(616,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:39'),
(617,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:42'),
(618,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:45'),
(619,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:48'),
(620,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:51'),
(621,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:54'),
(622,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:34:57'),
(623,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:00'),
(624,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:03'),
(625,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:06'),
(626,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:09'),
(627,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:12'),
(628,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:15'),
(629,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:17'),
(630,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:18'),
(631,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:21'),
(632,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:24'),
(633,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:27'),
(634,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:30'),
(635,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:33'),
(636,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:36'),
(637,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:39'),
(638,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:42'),
(639,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:45'),
(640,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:48'),
(641,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:48'),
(642,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:51'),
(643,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:54'),
(644,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:55'),
(645,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:55'),
(646,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:57'),
(647,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:35:57'),
(648,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:00'),
(649,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:00'),
(650,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:03'),
(651,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:03'),
(652,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:06'),
(653,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:06'),
(654,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:09'),
(655,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:10'),
(656,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:12'),
(657,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:13'),
(658,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:15'),
(659,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:16'),
(660,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:18'),
(661,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:19'),
(662,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:21'),
(663,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:21'),
(664,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:21'),
(665,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:22'),
(666,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:24'),
(667,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:24'),
(668,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:24'),
(669,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:24'),
(670,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:25'),
(671,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:27'),
(672,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:27'),
(673,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:27'),
(674,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:28'),
(675,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:28'),
(676,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:30'),
(677,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:30'),
(678,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:30'),
(679,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:31'),
(680,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:31'),
(681,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:31'),
(682,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:33'),
(683,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:33'),
(684,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:33'),
(685,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:33'),
(686,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:34'),
(687,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:35'),
(688,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:36'),
(689,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:36'),
(690,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:36'),
(691,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:36'),
(692,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:37'),
(693,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:39'),
(694,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:39'),
(695,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:39'),
(696,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:40'),
(697,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:42'),
(698,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:42'),
(699,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:42'),
(700,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:43'),
(701,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(702,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(703,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(704,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(705,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(706,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:45'),
(707,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:46'),
(708,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:48'),
(709,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:48'),
(710,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:48'),
(711,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:49'),
(712,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:51'),
(713,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:51'),
(714,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:51'),
(715,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:52'),
(716,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:54'),
(717,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:54'),
(718,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:54'),
(719,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:55'),
(720,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:57'),
(721,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:57'),
(722,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:57'),
(723,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:36:58'),
(724,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:00'),
(725,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:00'),
(726,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:00'),
(727,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:01'),
(728,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:03'),
(729,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:03'),
(730,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:03'),
(731,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:04'),
(732,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:06'),
(733,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:06'),
(734,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:06'),
(735,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:06'),
(736,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:07'),
(737,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:09'),
(738,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:09'),
(739,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:09'),
(740,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:12'),
(741,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:12'),
(742,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:12'),
(743,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:15'),
(744,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:15'),
(745,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:15'),
(746,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:17'),
(747,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:18'),
(748,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:18'),
(749,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:18'),
(750,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:21'),
(751,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:21'),
(752,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:21'),
(753,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:24'),
(754,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:24'),
(755,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:24'),
(756,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:24'),
(757,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:27'),
(758,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:27'),
(759,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:27'),
(760,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:30'),
(761,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:30'),
(762,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:30'),
(763,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:33'),
(764,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:33'),
(765,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:33'),
(766,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:35'),
(767,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:36'),
(768,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:36'),
(769,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:36'),
(770,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:39'),
(771,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:39'),
(772,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:39'),
(773,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:42'),
(774,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:42'),
(775,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:42'),
(776,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:45'),
(777,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:45'),
(778,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:45'),
(779,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:48'),
(780,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:48'),
(781,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:48'),
(782,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:51'),
(783,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:51'),
(784,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:51'),
(785,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:52'),
(786,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:52'),
(787,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:54'),
(788,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:54'),
(789,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:56'),
(790,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:57'),
(791,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:37:57'),
(792,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:00'),
(793,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:00'),
(794,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:00'),
(795,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:02'),
(796,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:03'),
(797,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:05'),
(798,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:06'),
(799,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:08'),
(800,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:09'),
(801,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:11'),
(802,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:12'),
(803,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:14'),
(804,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:15'),
(805,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:17'),
(806,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:17'),
(807,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:18'),
(808,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:19'),
(809,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:21'),
(810,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:21'),
(811,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:24'),
(812,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:24'),
(813,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:26'),
(814,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:27'),
(815,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:29'),
(816,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:30'),
(817,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:32'),
(818,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:35'),
(819,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:38:36'),
(820,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:39:11'),
(821,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:40:54'),
(822,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:40:56'),
(823,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:40:59'),
(824,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:02'),
(825,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:05'),
(826,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:08'),
(827,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:11'),
(828,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:14'),
(829,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:17'),
(830,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:20'),
(831,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:23'),
(832,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:26'),
(833,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:29'),
(834,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:32'),
(835,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:35'),
(836,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:38'),
(837,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:41'),
(838,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:44'),
(839,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:47'),
(840,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:50'),
(841,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:53'),
(842,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:56'),
(843,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:41:59'),
(844,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:02'),
(845,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:05'),
(846,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:08'),
(847,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:11'),
(848,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:14'),
(849,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:17'),
(850,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:20'),
(851,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:42:43'),
(852,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:44:13'),
(853,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:44:18'),
(854,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:44:20'),
(855,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:44:28'),
(856,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:44:28'),
(857,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:48:33'),
(858,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:49:23'),
(859,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 12:52:35'),
(860,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:16:52'),
(861,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:13'),
(862,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:13'),
(863,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:16'),
(864,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:19'),
(865,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:22'),
(866,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:25'),
(867,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:28'),
(868,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:31'),
(869,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:34'),
(870,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:38'),
(871,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:41'),
(872,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:44'),
(873,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:46'),
(874,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:50'),
(875,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:53'),
(876,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:56'),
(877,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:17:59'),
(878,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:02'),
(879,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:05'),
(880,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:08'),
(881,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:11'),
(882,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:14'),
(883,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:17'),
(884,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:20'),
(885,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:23'),
(886,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:26'),
(887,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:29'),
(888,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:32'),
(889,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:35'),
(890,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:38'),
(891,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:41'),
(892,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:44'),
(893,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:18:47'),
(894,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:19:17'),
(895,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:20:17'),
(896,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:21:17'),
(897,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:22:17'),
(898,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:23:17'),
(899,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:24:17'),
(900,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:25:17'),
(901,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:26:17'),
(902,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:27:17'),
(903,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:28:17'),
(904,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:29:17'),
(905,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:30:17'),
(906,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:31:17'),
(907,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:32:17'),
(908,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:33:17'),
(909,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:34:17'),
(910,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:17'),
(911,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:28'),
(912,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:28'),
(913,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:30'),
(914,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:33'),
(915,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:36'),
(916,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:39'),
(917,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:42'),
(918,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:45'),
(919,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:48'),
(920,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:51'),
(921,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:54'),
(922,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:35:57'),
(923,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:01'),
(924,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:03'),
(925,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:06'),
(926,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:09'),
(927,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:10'),
(928,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:13'),
(929,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:13'),
(930,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:19'),
(931,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:19'),
(932,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:22'),
(933,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:25'),
(934,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:29'),
(935,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:32'),
(936,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:35'),
(937,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:38'),
(938,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:41'),
(939,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:44'),
(940,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:47'),
(941,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:50'),
(942,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:53'),
(943,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:56'),
(944,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:36:59'),
(945,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:02'),
(946,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:05'),
(947,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:08'),
(948,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:11'),
(949,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:14'),
(950,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:17'),
(951,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:20'),
(952,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:23'),
(953,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:24'),
(954,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:27'),
(955,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:30'),
(956,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:34'),
(957,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:36'),
(958,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:39'),
(959,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:42'),
(960,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:46'),
(961,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:49'),
(962,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:52'),
(963,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:55'),
(964,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:37:58'),
(965,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:01'),
(966,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:04'),
(967,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:07'),
(968,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:10'),
(969,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:13'),
(970,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:16'),
(971,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:19'),
(972,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:22'),
(973,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:25'),
(974,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:28'),
(975,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:31'),
(976,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:34'),
(977,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:37'),
(978,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:40'),
(979,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:38:43'),
(980,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:39:17'),
(981,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:40:17'),
(982,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:17'),
(983,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:36'),
(984,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:37'),
(985,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:38'),
(986,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:39'),
(987,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:39'),
(988,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:41'),
(989,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:44'),
(990,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:44'),
(991,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:47'),
(992,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:50'),
(993,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:50'),
(994,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:52'),
(995,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:53'),
(996,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:53'),
(997,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:56'),
(998,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:56'),
(999,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:57'),
(1000,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:59'),
(1001,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:41:59'),
(1002,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:00'),
(1003,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:00'),
(1004,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:02'),
(1005,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:02'),
(1006,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:03'),
(1007,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:05'),
(1008,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:05'),
(1009,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:07'),
(1010,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:08'),
(1011,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:08'),
(1012,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:10'),
(1013,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:11'),
(1014,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:11'),
(1015,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:13'),
(1016,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:14'),
(1017,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:14'),
(1018,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:16'),
(1019,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:17'),
(1020,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:17'),
(1021,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:19'),
(1022,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:20'),
(1023,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:20'),
(1024,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:20'),
(1025,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:20'),
(1026,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:22'),
(1027,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:23'),
(1028,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:23'),
(1029,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:25'),
(1030,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:26'),
(1031,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:26'),
(1032,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:27'),
(1033,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:28'),
(1034,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:29'),
(1035,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:29'),
(1036,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:30'),
(1037,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:31'),
(1038,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:32'),
(1039,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:32'),
(1040,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:34'),
(1041,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:34'),
(1042,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:35'),
(1043,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:35'),
(1044,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:37'),
(1045,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:37'),
(1046,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:38'),
(1047,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:38'),
(1048,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:40'),
(1049,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:40'),
(1050,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:41'),
(1051,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:43'),
(1052,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:43'),
(1053,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:44'),
(1054,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:45'),
(1055,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:46'),
(1056,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:47'),
(1057,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:49'),
(1058,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:49'),
(1059,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:50'),
(1060,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:52'),
(1061,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:52'),
(1062,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:55'),
(1063,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:55'),
(1064,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:58'),
(1065,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:42:58'),
(1066,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:01'),
(1067,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:01'),
(1068,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:04'),
(1069,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:04'),
(1070,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:07'),
(1071,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:07'),
(1072,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:10'),
(1073,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:10'),
(1074,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:13'),
(1075,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:13'),
(1076,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:16'),
(1077,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:16'),
(1078,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:17'),
(1079,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:17'),
(1080,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:19'),
(1081,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:19'),
(1082,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:19'),
(1083,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:20'),
(1084,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:22'),
(1085,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:23'),
(1086,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:25'),
(1087,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:26'),
(1088,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:28'),
(1089,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:29'),
(1090,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:31'),
(1091,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:32'),
(1092,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:34'),
(1093,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:35'),
(1094,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:37'),
(1095,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:38'),
(1096,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:40'),
(1097,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:41'),
(1098,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:43'),
(1099,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:44'),
(1100,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:46'),
(1101,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:47'),
(1102,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:50'),
(1103,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:53'),
(1104,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:56'),
(1105,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:56'),
(1106,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:59'),
(1107,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:43:59'),
(1108,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:00'),
(1109,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:02'),
(1110,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:05'),
(1111,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:08'),
(1112,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:11'),
(1113,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:14'),
(1114,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:17'),
(1115,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:17'),
(1116,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:44:17'),
(1117,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:45:17'),
(1118,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:45:17'),
(1119,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:45:17'),
(1120,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:45:54'),
(1121,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:01'),
(1122,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:01'),
(1123,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:17'),
(1124,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:17'),
(1125,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:17'),
(1126,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:46:46'),
(1127,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:47:17'),
(1128,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:47:17'),
(1129,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:47:17'),
(1130,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:48:17'),
(1131,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:48:17'),
(1132,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:48:17'),
(1133,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:49:17'),
(1134,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:49:17'),
(1135,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:49:17'),
(1136,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:50:17'),
(1137,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:50:17'),
(1138,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:50:17'),
(1139,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:51:17'),
(1140,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:51:17'),
(1141,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:51:17'),
(1142,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:52:17'),
(1143,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:52:17'),
(1144,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:52:17'),
(1145,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:53:17'),
(1146,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:53:17'),
(1147,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:53:17'),
(1148,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:54:17'),
(1149,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:54:17'),
(1150,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:54:17'),
(1151,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:55:17'),
(1152,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:55:17'),
(1153,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:55:17'),
(1154,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:56:17'),
(1155,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:56:17'),
(1156,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:56:17'),
(1157,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:57:17'),
(1158,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:57:17'),
(1159,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:57:17'),
(1160,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:58:17'),
(1161,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:58:17'),
(1162,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:58:17'),
(1163,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:59:17'),
(1164,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:59:17'),
(1165,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 13:59:17'),
(1166,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:00:17'),
(1167,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:00:17'),
(1168,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:00:17'),
(1169,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:01:17'),
(1170,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:01:17'),
(1171,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:01:17'),
(1172,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:02:17'),
(1173,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:02:17'),
(1174,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:02:17'),
(1175,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:03:17'),
(1176,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:03:17'),
(1177,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:03:17'),
(1178,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:04:17'),
(1179,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:04:17'),
(1180,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:04:17'),
(1181,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:05:17'),
(1182,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:05:17'),
(1183,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:05:17'),
(1184,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:06:17'),
(1185,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:06:17'),
(1186,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:06:17'),
(1187,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:07:17'),
(1188,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:07:17'),
(1189,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:07:17'),
(1190,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:08:17'),
(1191,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:08:17'),
(1192,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:08:17'),
(1193,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:09:17'),
(1194,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:09:17'),
(1195,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:09:17'),
(1196,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:10:17'),
(1197,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:10:17'),
(1198,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:10:17'),
(1199,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:11:17'),
(1200,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:11:17'),
(1201,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:11:17'),
(1202,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:12:17'),
(1203,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:12:17'),
(1204,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:12:17'),
(1205,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:13:17'),
(1206,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:13:17'),
(1207,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:13:17'),
(1208,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:14:17'),
(1209,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:14:17'),
(1210,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:14:17'),
(1211,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:15:17'),
(1212,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:15:17'),
(1213,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:15:17'),
(1214,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:16:17'),
(1215,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:16:17'),
(1216,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:16:17'),
(1217,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:17:17'),
(1218,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:17:17'),
(1219,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:17:17'),
(1220,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:18:17'),
(1221,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:18:17'),
(1222,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:18:17'),
(1223,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:19:17'),
(1224,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:19:17'),
(1225,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:19:17'),
(1226,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:20:17'),
(1227,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:20:17'),
(1228,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:20:17'),
(1229,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:21:17'),
(1230,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:21:17'),
(1231,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:21:17'),
(1232,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:22:17'),
(1233,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:22:17'),
(1234,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:22:17'),
(1235,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:23:17'),
(1236,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:23:17'),
(1237,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:23:17'),
(1238,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:24:17'),
(1239,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:24:17'),
(1240,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:24:17'),
(1241,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:25:17'),
(1242,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:25:17'),
(1243,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:25:17'),
(1244,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:26:17'),
(1245,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:26:17'),
(1246,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:26:17'),
(1247,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:27:17'),
(1248,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:27:17'),
(1249,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:27:17'),
(1250,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:28:17'),
(1251,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:28:17'),
(1252,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:28:17'),
(1253,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:29:17'),
(1254,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:29:17'),
(1255,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:29:17'),
(1256,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:30:17'),
(1257,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:30:17'),
(1258,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:30:17'),
(1259,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:31:17'),
(1260,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:31:17'),
(1261,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:31:17'),
(1262,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:32:17'),
(1263,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:32:17'),
(1264,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:32:17'),
(1265,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:17'),
(1266,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:17'),
(1267,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:17'),
(1268,'151.115.98.139',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:34'),
(1269,'151.115.98.139',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:34'),
(1270,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:39'),
(1271,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:41'),
(1272,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:44'),
(1273,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:47'),
(1274,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:50'),
(1275,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:53'),
(1276,'151.115.98.139',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:53'),
(1277,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:56'),
(1278,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:33:59'),
(1279,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:02'),
(1280,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:05'),
(1281,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:08'),
(1282,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:11'),
(1283,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:14'),
(1284,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:17'),
(1285,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:17'),
(1286,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:17'),
(1287,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:20'),
(1288,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:23'),
(1289,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:26'),
(1290,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:29'),
(1291,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:32'),
(1292,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:35'),
(1293,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:34:38'),
(1294,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:35:17'),
(1295,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:35:17'),
(1296,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:35:17'),
(1297,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:36:17'),
(1298,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:36:17'),
(1299,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:36:17'),
(1300,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:37:17'),
(1301,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:37:17'),
(1302,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:37:17'),
(1303,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:38:17'),
(1304,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:38:17'),
(1305,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:38:17'),
(1306,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:39:17'),
(1307,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:39:17'),
(1308,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:39:17'),
(1309,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:40:17'),
(1310,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:40:17'),
(1311,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:40:17'),
(1312,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:41:17'),
(1313,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:41:17'),
(1314,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:41:17'),
(1315,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:42:17'),
(1316,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:42:17'),
(1317,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:42:17'),
(1318,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:43:17'),
(1319,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:43:17'),
(1320,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:43:17'),
(1321,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:44:17'),
(1322,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:44:17'),
(1323,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:44:17'),
(1324,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:45:17'),
(1325,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:45:17'),
(1326,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:45:17'),
(1327,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:46:17'),
(1328,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:46:17'),
(1329,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:46:17'),
(1330,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:47:04'),
(1331,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:47:12'),
(1332,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:47:17'),
(1333,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:47:17'),
(1334,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:47:17'),
(1335,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:48:17'),
(1336,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:48:17'),
(1337,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:48:17'),
(1338,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:49:17'),
(1339,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:49:17'),
(1340,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:49:17'),
(1341,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:50:17'),
(1342,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:50:17'),
(1343,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:50:17'),
(1344,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:51:17'),
(1345,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:51:17'),
(1346,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:51:17'),
(1347,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:52:17'),
(1348,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:52:17'),
(1349,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:52:17'),
(1350,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:53:17'),
(1351,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:53:17'),
(1352,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:53:17'),
(1353,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:54:17'),
(1354,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:54:17'),
(1355,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:54:17'),
(1356,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:55:17'),
(1357,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:55:17'),
(1358,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:55:17'),
(1359,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:56:17'),
(1360,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:56:17'),
(1361,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:56:17'),
(1362,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:57:17'),
(1363,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:57:17'),
(1364,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:57:17'),
(1365,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:58:17'),
(1366,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:58:17'),
(1367,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:58:17'),
(1368,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:59:17'),
(1369,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:59:17'),
(1370,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 14:59:17'),
(1371,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:00:17'),
(1372,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:00:17'),
(1373,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:00:17'),
(1374,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:01:17'),
(1375,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:01:17'),
(1376,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:01:17'),
(1377,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:02:17'),
(1378,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:02:17'),
(1379,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:02:17'),
(1380,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:03:17'),
(1381,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:03:17'),
(1382,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:03:17'),
(1383,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:04:17'),
(1384,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:04:17'),
(1385,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:04:17'),
(1386,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:05:17'),
(1387,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:05:17'),
(1388,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:05:17'),
(1389,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:06:17'),
(1390,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:06:17'),
(1391,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:06:17'),
(1392,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:07:17'),
(1393,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:07:17'),
(1394,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:07:17'),
(1395,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:08:17'),
(1396,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:08:17'),
(1397,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:08:17'),
(1398,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:09:17'),
(1399,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:09:17'),
(1400,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:09:17'),
(1401,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:10:17'),
(1402,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:10:17'),
(1403,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:10:17'),
(1404,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:11:17'),
(1405,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:11:17'),
(1406,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:11:17'),
(1407,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:12:17'),
(1408,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:12:17'),
(1409,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:12:17'),
(1410,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:13:17'),
(1411,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:13:17'),
(1412,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:13:17'),
(1413,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:14:17'),
(1414,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:14:17'),
(1415,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:14:17'),
(1416,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:15:17'),
(1417,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:15:17'),
(1418,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:15:17'),
(1419,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:16:17'),
(1420,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:16:17'),
(1421,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:16:17'),
(1422,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:17:17'),
(1423,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:17:17'),
(1424,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:17:17'),
(1425,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:18:17'),
(1426,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:18:17'),
(1427,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:18:17'),
(1428,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:19:17'),
(1429,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:19:17'),
(1430,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:19:17'),
(1431,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:20:17'),
(1432,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:20:17'),
(1433,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:20:17'),
(1434,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:21:17'),
(1435,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:21:17'),
(1436,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:21:17'),
(1437,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:22:17'),
(1438,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:22:17'),
(1439,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:22:17'),
(1440,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:23:17'),
(1441,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:23:17'),
(1442,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:23:17'),
(1443,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:24:17'),
(1444,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:24:17'),
(1445,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:24:17'),
(1446,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:25:17'),
(1447,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:25:17'),
(1448,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:25:17'),
(1449,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:26:17'),
(1450,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:26:17'),
(1451,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:26:17'),
(1452,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:27:17'),
(1453,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:27:17'),
(1454,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:27:17'),
(1455,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:28:17'),
(1456,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:28:17'),
(1457,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:28:17'),
(1458,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:29:17'),
(1459,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:29:17'),
(1460,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:29:17'),
(1461,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:30:17'),
(1462,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:30:17'),
(1463,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:30:17'),
(1464,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:31:17'),
(1465,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:31:17'),
(1466,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:31:17'),
(1467,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:32:17'),
(1468,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:32:17'),
(1469,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:32:17'),
(1470,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:33:17'),
(1471,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:33:17'),
(1472,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:33:17'),
(1473,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:34:17'),
(1474,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:34:17'),
(1475,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:34:17'),
(1476,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:35:17'),
(1477,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:35:17'),
(1478,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:35:17'),
(1479,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:36:17'),
(1480,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:36:17'),
(1481,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:36:17'),
(1482,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:37:17'),
(1483,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:37:17'),
(1484,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:37:17'),
(1485,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:38:17'),
(1486,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:38:17'),
(1487,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:38:17'),
(1488,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:39:17'),
(1489,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:39:17'),
(1490,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:39:17'),
(1491,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:40:17'),
(1492,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:40:17'),
(1493,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:40:17'),
(1494,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:41:17'),
(1495,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:41:17'),
(1496,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:41:17'),
(1497,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:42:17'),
(1498,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:42:17'),
(1499,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:42:17'),
(1500,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:43:17'),
(1501,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:43:17'),
(1502,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:43:17'),
(1503,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:44:17'),
(1504,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:44:17'),
(1505,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:44:17'),
(1506,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:45:17'),
(1507,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:45:17'),
(1508,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:45:17'),
(1509,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:46:17'),
(1510,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:46:17'),
(1511,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:46:17'),
(1512,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:47:17'),
(1513,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:47:17'),
(1514,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:47:17'),
(1515,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:48:17'),
(1516,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:48:17'),
(1517,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:48:17'),
(1518,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:49:17'),
(1519,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:49:17'),
(1520,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:49:17'),
(1521,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:50:17'),
(1522,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:50:17'),
(1523,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:50:17'),
(1524,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:51:17'),
(1525,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:51:17'),
(1526,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:51:17'),
(1527,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:52:17'),
(1528,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:52:17'),
(1529,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:52:17'),
(1530,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:53:17'),
(1531,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:53:17'),
(1532,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:53:17'),
(1533,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:54:17'),
(1534,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:54:17'),
(1535,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:54:17'),
(1536,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:55:17'),
(1537,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:55:17'),
(1538,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:55:17'),
(1539,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:56:17'),
(1540,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:56:17'),
(1541,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:56:17'),
(1542,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:57:17'),
(1543,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:57:17'),
(1544,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:57:17'),
(1545,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:58:17'),
(1546,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:58:17'),
(1547,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:58:17'),
(1548,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:59:17'),
(1549,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:59:17'),
(1550,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 15:59:17'),
(1551,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:00:17'),
(1552,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:00:17'),
(1553,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:00:17'),
(1554,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:01:17'),
(1555,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:01:17'),
(1556,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:01:17'),
(1557,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:02:17'),
(1558,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:02:17'),
(1559,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:02:17'),
(1560,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:03:17'),
(1561,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:03:17'),
(1562,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:03:17'),
(1563,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:04:17'),
(1564,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:04:17'),
(1565,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:04:17'),
(1566,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:05:17'),
(1567,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:05:17'),
(1568,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:05:17'),
(1569,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:06:17'),
(1570,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:06:17'),
(1571,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:06:17'),
(1572,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:07:17'),
(1573,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:07:17'),
(1574,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:07:17'),
(1575,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:08:17'),
(1576,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:08:17'),
(1577,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:08:17'),
(1578,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:09:17'),
(1579,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:09:17'),
(1580,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:09:17'),
(1581,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:10:17'),
(1582,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:10:17'),
(1583,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:10:17'),
(1584,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:11:17'),
(1585,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:11:17'),
(1586,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:11:17'),
(1587,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:12:17'),
(1588,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:12:17'),
(1589,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:12:17'),
(1590,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:13:17'),
(1591,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:13:17'),
(1592,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:13:17'),
(1593,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:14:17'),
(1594,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:14:17'),
(1595,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:14:17'),
(1596,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:15:17'),
(1597,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:15:17'),
(1598,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:15:17'),
(1599,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:16:17'),
(1600,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:16:17'),
(1601,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:16:17'),
(1602,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:17:17'),
(1603,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:17:17'),
(1604,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:17:17'),
(1605,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:18:17'),
(1606,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:18:17'),
(1607,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:18:17'),
(1608,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:19:17'),
(1609,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:19:17'),
(1610,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:19:17'),
(1611,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:20:17'),
(1612,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:20:17'),
(1613,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:20:17'),
(1614,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:21:17'),
(1615,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:21:17'),
(1616,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:21:17'),
(1617,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:22:17'),
(1618,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:22:17'),
(1619,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:22:17'),
(1620,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:23:17'),
(1621,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:23:17'),
(1622,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:23:17'),
(1623,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:24:17'),
(1624,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:24:17'),
(1625,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:24:17'),
(1626,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:25:17'),
(1627,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:25:17'),
(1628,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:25:17'),
(1629,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:26:17'),
(1630,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:26:17'),
(1631,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:26:17'),
(1632,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:27:17'),
(1633,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:27:17'),
(1634,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:27:17'),
(1635,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:28:17'),
(1636,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:28:17'),
(1637,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:28:17'),
(1638,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:29:17'),
(1639,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:29:17'),
(1640,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:29:17'),
(1641,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:30:17'),
(1642,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:30:17'),
(1643,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:30:17'),
(1644,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:31:17'),
(1645,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:31:17'),
(1646,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:31:17'),
(1647,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:32:17'),
(1648,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:32:17'),
(1649,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:32:17'),
(1650,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:33:17'),
(1651,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:33:17'),
(1652,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:33:17'),
(1653,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:34:17'),
(1654,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:34:17'),
(1655,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:34:17'),
(1656,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:35:17'),
(1657,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:35:17'),
(1658,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:35:17'),
(1659,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:36:17'),
(1660,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:36:17'),
(1661,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:36:17'),
(1662,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:37:17'),
(1663,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:37:17'),
(1664,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:37:17'),
(1665,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:38:17'),
(1666,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:38:17'),
(1667,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:38:17'),
(1668,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:39:17'),
(1669,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:39:17'),
(1670,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:39:17'),
(1671,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:40:17'),
(1672,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:40:17'),
(1673,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:40:17'),
(1674,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:41:17'),
(1675,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:41:17'),
(1676,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:41:17'),
(1677,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:42:17'),
(1678,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:42:17'),
(1679,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:42:17'),
(1680,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:43:17'),
(1681,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:43:17'),
(1682,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:43:17'),
(1683,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:44:17'),
(1684,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:44:17'),
(1685,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:44:17'),
(1686,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:45:17'),
(1687,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:45:17'),
(1688,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:45:17'),
(1689,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:46:17'),
(1690,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:46:17'),
(1691,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:46:17'),
(1692,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:47:17'),
(1693,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:47:17'),
(1694,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:47:17'),
(1695,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:48:17'),
(1696,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:48:17'),
(1697,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:48:17'),
(1698,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:49:17'),
(1699,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:49:17'),
(1700,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:49:17'),
(1701,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:50:17'),
(1702,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:50:17'),
(1703,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:50:17'),
(1704,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:51:17'),
(1705,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:51:17'),
(1706,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:51:17'),
(1707,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:52:17'),
(1708,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:52:17'),
(1709,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:52:17'),
(1710,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:53:17'),
(1711,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:53:17'),
(1712,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:53:17'),
(1713,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:54:17'),
(1714,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:54:17'),
(1715,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:54:17'),
(1716,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:55:17'),
(1717,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:55:17'),
(1718,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:55:17'),
(1719,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:56:17'),
(1720,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:56:17'),
(1721,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:56:17'),
(1722,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:57:17'),
(1723,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:57:17'),
(1724,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:57:17'),
(1725,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:58:17'),
(1726,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:58:17'),
(1727,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:58:17'),
(1728,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:59:17'),
(1729,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:59:17'),
(1730,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 16:59:17'),
(1731,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:00:17'),
(1732,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:00:17'),
(1733,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:00:17'),
(1734,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:01:17'),
(1735,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:01:17'),
(1736,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:01:17'),
(1737,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:02:17'),
(1738,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:02:17'),
(1739,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:02:17'),
(1740,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:03:17'),
(1741,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:03:17'),
(1742,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:03:17'),
(1743,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:04:17'),
(1744,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:04:17'),
(1745,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:04:17'),
(1746,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:05:17'),
(1747,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:05:17'),
(1748,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:05:17'),
(1749,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:06:17'),
(1750,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:06:17'),
(1751,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:06:17'),
(1752,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:07:17'),
(1753,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:07:17'),
(1754,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:07:17'),
(1755,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:08:17'),
(1756,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:08:17'),
(1757,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:08:17'),
(1758,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:09:17'),
(1759,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:09:17'),
(1760,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:09:17'),
(1761,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:10:17'),
(1762,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:10:17'),
(1763,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:10:17'),
(1764,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:11:17'),
(1765,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:11:17'),
(1766,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:11:17'),
(1767,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:12:17'),
(1768,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:12:17'),
(1769,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:12:17'),
(1770,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:13:17'),
(1771,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:13:17'),
(1772,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:13:17'),
(1773,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:14:17'),
(1774,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:14:17'),
(1775,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:14:17'),
(1776,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:15:17'),
(1777,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:15:17'),
(1778,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:15:17'),
(1779,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:16:17'),
(1780,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:16:17'),
(1781,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:16:17'),
(1782,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:17:17'),
(1783,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:17:17'),
(1784,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:17:17'),
(1785,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:18:17'),
(1786,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:18:17'),
(1787,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:18:17'),
(1788,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:19:17'),
(1789,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:19:17'),
(1790,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:19:17'),
(1791,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:20:17'),
(1792,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:20:17'),
(1793,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:20:17'),
(1794,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:21:17'),
(1795,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:21:17'),
(1796,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:21:17'),
(1797,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:22:17'),
(1798,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:22:17'),
(1799,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:22:17'),
(1800,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:23:17'),
(1801,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:23:17'),
(1802,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:23:17'),
(1803,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:24:17'),
(1804,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:24:17'),
(1805,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:24:17'),
(1806,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:25:17'),
(1807,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:25:17'),
(1808,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:25:17'),
(1809,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:26:17'),
(1810,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:26:17'),
(1811,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:26:17'),
(1812,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:27:17'),
(1813,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:27:17'),
(1814,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:27:17'),
(1815,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:28:17'),
(1816,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:28:17'),
(1817,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:28:17'),
(1818,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:29:17'),
(1819,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:29:17'),
(1820,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:29:17'),
(1821,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:30:17'),
(1822,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:30:17'),
(1823,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:30:17'),
(1824,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:31:17'),
(1825,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:31:17'),
(1826,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:31:17'),
(1827,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:32:17'),
(1828,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:32:17'),
(1829,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:32:17'),
(1830,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:33:17'),
(1831,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:33:17'),
(1832,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:33:17'),
(1833,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:34:17'),
(1834,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:34:17'),
(1835,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:34:17'),
(1836,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:35:17'),
(1837,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:35:17'),
(1838,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:35:17'),
(1839,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:36:17'),
(1840,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:36:17'),
(1841,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:36:17'),
(1842,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:37:17'),
(1843,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:37:17'),
(1844,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:37:17'),
(1845,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:38:17'),
(1846,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:38:17'),
(1847,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:38:17'),
(1848,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:39:17'),
(1849,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:39:17'),
(1850,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:39:17'),
(1851,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:40:17'),
(1852,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:40:17'),
(1853,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:40:17'),
(1854,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:41:17'),
(1855,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:41:17'),
(1856,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:41:17'),
(1857,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:42:17'),
(1858,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:42:17'),
(1859,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:42:17'),
(1860,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:43:17'),
(1861,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:43:17'),
(1862,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:43:17'),
(1863,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:44:17'),
(1864,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:44:17'),
(1865,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:44:17'),
(1866,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:45:17'),
(1867,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:45:17'),
(1868,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:45:17'),
(1869,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:46:17'),
(1870,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:46:17'),
(1871,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:46:17'),
(1872,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:47:17'),
(1873,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:47:17'),
(1874,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:47:17'),
(1875,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:48:17'),
(1876,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:48:17'),
(1877,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:48:17'),
(1878,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:49:17'),
(1879,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:49:17'),
(1880,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:49:17'),
(1881,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:50:17'),
(1882,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:50:17'),
(1883,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:50:17'),
(1884,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:51:17'),
(1885,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:51:17'),
(1886,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:51:17'),
(1887,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:52:17'),
(1888,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:52:17'),
(1889,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:52:17'),
(1890,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:53:17'),
(1891,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:53:17'),
(1892,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:53:17'),
(1893,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:54:17'),
(1894,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:54:17'),
(1895,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:54:17'),
(1896,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:55:17'),
(1897,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:55:17'),
(1898,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:55:17'),
(1899,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:56:17'),
(1900,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:56:17'),
(1901,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:56:17'),
(1902,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:57:17'),
(1903,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:57:17'),
(1904,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:57:17'),
(1905,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:58:17'),
(1906,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:58:17'),
(1907,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:58:17'),
(1908,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:59:17'),
(1909,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:59:17'),
(1910,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 17:59:17'),
(1911,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:00:17'),
(1912,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:00:17'),
(1913,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:00:17'),
(1914,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:01:17'),
(1915,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:01:17'),
(1916,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:01:17'),
(1917,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:02:17'),
(1918,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:02:17'),
(1919,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:02:17'),
(1920,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:03:17'),
(1921,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:03:17'),
(1922,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:03:17'),
(1923,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:04:17'),
(1924,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:04:17'),
(1925,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:04:17'),
(1926,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:05:17'),
(1927,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:05:17'),
(1928,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:05:17'),
(1929,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:06:17'),
(1930,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:06:17'),
(1931,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:06:17'),
(1932,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:07:17'),
(1933,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:07:17'),
(1934,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:07:17'),
(1935,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:08:17'),
(1936,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:08:17'),
(1937,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:08:17'),
(1938,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:09:17'),
(1939,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:09:17'),
(1940,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:09:17'),
(1941,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:10:17'),
(1942,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:10:17'),
(1943,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:10:17'),
(1944,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:11:17'),
(1945,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:11:17'),
(1946,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:11:17'),
(1947,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:12:17'),
(1948,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:12:17'),
(1949,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:12:17'),
(1950,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:13:17'),
(1951,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:13:17'),
(1952,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:13:17'),
(1953,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:14:17'),
(1954,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:14:17'),
(1955,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:14:17'),
(1956,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:15:17'),
(1957,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:15:17'),
(1958,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:15:17'),
(1959,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:16:17'),
(1960,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:16:17'),
(1961,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:16:17'),
(1962,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:17:17'),
(1963,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:17:17'),
(1964,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:17:17'),
(1965,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:18:17'),
(1966,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:18:17'),
(1967,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:18:17'),
(1968,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:19:17'),
(1969,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:19:17'),
(1970,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:19:17'),
(1971,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:20:17'),
(1972,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:20:17'),
(1973,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:20:17'),
(1974,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:21:17'),
(1975,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:21:17'),
(1976,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:21:17'),
(1977,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:22:17'),
(1978,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:22:17'),
(1979,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:22:17'),
(1980,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:23:17'),
(1981,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:23:17'),
(1982,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:23:17'),
(1983,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:24:17'),
(1984,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:24:17'),
(1985,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:24:17'),
(1986,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:25:17'),
(1987,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:25:17'),
(1988,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:25:17'),
(1989,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:26:17'),
(1990,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:26:17'),
(1991,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:26:17'),
(1992,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:27:17'),
(1993,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:27:17'),
(1994,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:27:17'),
(1995,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:28:17'),
(1996,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:28:17'),
(1997,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:28:17'),
(1998,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:29:17'),
(1999,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:29:17'),
(2000,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:29:17'),
(2001,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:30:17'),
(2002,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:30:17'),
(2003,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:30:17'),
(2004,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:31:17'),
(2005,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:31:17'),
(2006,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:31:17'),
(2007,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:32:17'),
(2008,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:32:17'),
(2009,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:32:17'),
(2010,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:33:17'),
(2011,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:33:17'),
(2012,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:33:17'),
(2013,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:34:17'),
(2014,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:34:17'),
(2015,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:34:17'),
(2016,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:35:17'),
(2017,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:35:17'),
(2018,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:35:17'),
(2019,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:36:17'),
(2020,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:36:17'),
(2021,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:36:17'),
(2022,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:37:17'),
(2023,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:37:17'),
(2024,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:37:17'),
(2025,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:38:17'),
(2026,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:38:17'),
(2027,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:38:17'),
(2028,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:39:17'),
(2029,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:39:17'),
(2030,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:39:17'),
(2031,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:40:17'),
(2032,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:40:17'),
(2033,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:40:17'),
(2034,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:41:17'),
(2035,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:41:17'),
(2036,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:41:17'),
(2037,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:42:17'),
(2038,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:42:17'),
(2039,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:42:17'),
(2040,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:43:17'),
(2041,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:43:17'),
(2042,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:43:17'),
(2043,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:44:17'),
(2044,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:44:17'),
(2045,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:44:17'),
(2046,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:45:17'),
(2047,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:45:17'),
(2048,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:45:17'),
(2049,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:46:17'),
(2050,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:46:17'),
(2051,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:46:17'),
(2052,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:47:17'),
(2053,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:47:17'),
(2054,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:47:17'),
(2055,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:48:17'),
(2056,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:48:17'),
(2057,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:48:17'),
(2058,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:49:17'),
(2059,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:49:17'),
(2060,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:49:17'),
(2061,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:50:17'),
(2062,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:50:17'),
(2063,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:50:17'),
(2064,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:51:17'),
(2065,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:51:17'),
(2066,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:51:17'),
(2067,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:52:17'),
(2068,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:52:17'),
(2069,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:52:17'),
(2070,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:53:17'),
(2071,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:53:17'),
(2072,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:53:17'),
(2073,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:54:17'),
(2074,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:54:17'),
(2075,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:54:17'),
(2076,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:55:17'),
(2077,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:55:17'),
(2078,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:55:17'),
(2079,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:56:17'),
(2080,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:56:17'),
(2081,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:56:17'),
(2082,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:57:17'),
(2083,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:57:17'),
(2084,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:57:17'),
(2085,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:58:17'),
(2086,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:58:17'),
(2087,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:58:17'),
(2088,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:59:17'),
(2089,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:59:17'),
(2090,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 18:59:17'),
(2091,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:00:17'),
(2092,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:00:17'),
(2093,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:00:17'),
(2094,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:01:17'),
(2095,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:01:17'),
(2096,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:01:17'),
(2097,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:02:17'),
(2098,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:02:17'),
(2099,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:02:17'),
(2100,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:03:17'),
(2101,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:03:17'),
(2102,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:03:17'),
(2103,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:04:17'),
(2104,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:04:17'),
(2105,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:04:17'),
(2106,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:05:17'),
(2107,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:05:17'),
(2108,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:05:17'),
(2109,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:06:17'),
(2110,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:06:17'),
(2111,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:06:17'),
(2112,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:07:17'),
(2113,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:07:17'),
(2114,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:07:17'),
(2115,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:08:17'),
(2116,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:08:17'),
(2117,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:08:17'),
(2118,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:09:17'),
(2119,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:09:17'),
(2120,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:09:17'),
(2121,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:10:17'),
(2122,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:10:17'),
(2123,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:10:17'),
(2124,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:11:17'),
(2125,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:11:17'),
(2126,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:11:17'),
(2127,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:12:17'),
(2128,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:12:17'),
(2129,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:12:17'),
(2130,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:13:17'),
(2131,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:13:17'),
(2132,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:13:17'),
(2133,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:14:17'),
(2134,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:14:17'),
(2135,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:14:17'),
(2136,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:15:17'),
(2137,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:15:17'),
(2138,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:15:17'),
(2139,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:16:17'),
(2140,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:16:17'),
(2141,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:16:17'),
(2142,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:17:17'),
(2143,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:17:17'),
(2144,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:17:17'),
(2145,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:18:17'),
(2146,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:18:17'),
(2147,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:18:17'),
(2148,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:19:17'),
(2149,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:19:17'),
(2150,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:19:17'),
(2151,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:20:17'),
(2152,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:20:17'),
(2153,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:20:17'),
(2154,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:21:17'),
(2155,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:21:17'),
(2156,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:21:17'),
(2157,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:22:17'),
(2158,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:22:17'),
(2159,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:22:17'),
(2160,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:23:17'),
(2161,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:23:17'),
(2162,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:23:17'),
(2163,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:24:17'),
(2164,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:24:17'),
(2165,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:24:17'),
(2166,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:25:17'),
(2167,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:25:17'),
(2168,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:25:17'),
(2169,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:26:17'),
(2170,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:26:17'),
(2171,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:26:17'),
(2172,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:27:17'),
(2173,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:27:17'),
(2174,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:27:17'),
(2175,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:28:17'),
(2176,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:28:17'),
(2177,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:28:17'),
(2178,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:29:17'),
(2179,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:29:17'),
(2180,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:29:17'),
(2181,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:30:17'),
(2182,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:30:17'),
(2183,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:30:17'),
(2184,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:31:17'),
(2185,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:31:17'),
(2186,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:31:17'),
(2187,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:32:17'),
(2188,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:32:17'),
(2189,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:32:17'),
(2190,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:33:17'),
(2191,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:33:17'),
(2192,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-21 19:33:17'),
(2193,'51.158.204.225',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 07:38:12'),
(2194,'51.158.204.225',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 07:41:50'),
(2195,'149.57.180.56',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 08:52:16'),
(2196,'66.249.66.45',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 15:57:10'),
(2197,'66.249.66.33',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 18:48:09'),
(2198,'66.249.66.32',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-22 20:18:07'),
(2199,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:24:19'),
(2200,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:24:20'),
(2201,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:24:20'),
(2202,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:25:19'),
(2203,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:25:19'),
(2204,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:25:19'),
(2205,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:26:19'),
(2206,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:26:19'),
(2207,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 06:26:19'),
(2208,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:21:49'),
(2209,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:21:49'),
(2210,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:21:52'),
(2211,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:21:55'),
(2212,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:21:58'),
(2213,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:01'),
(2214,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:04'),
(2215,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:05'),
(2216,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:05'),
(2217,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:07'),
(2218,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:07'),
(2219,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:07'),
(2220,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:10'),
(2221,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:10'),
(2222,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:11'),
(2223,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:11'),
(2224,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:12'),
(2225,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:13'),
(2226,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:13'),
(2227,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:16'),
(2228,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:16'),
(2229,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:19'),
(2230,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:19'),
(2231,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:22'),
(2232,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:22'),
(2233,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:25'),
(2234,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:25'),
(2235,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:28'),
(2236,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:28'),
(2237,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:31'),
(2238,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:31'),
(2239,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:34'),
(2240,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:34'),
(2241,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:37'),
(2242,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:37'),
(2243,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:40'),
(2244,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:40'),
(2245,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:43'),
(2246,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:43'),
(2247,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:46'),
(2248,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:46'),
(2249,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:49'),
(2250,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:49'),
(2251,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:52'),
(2252,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:52'),
(2253,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:55'),
(2254,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:55'),
(2255,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:58'),
(2256,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:22:58'),
(2257,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:01'),
(2258,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:01'),
(2259,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:04'),
(2260,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:04'),
(2261,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:07'),
(2262,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:07'),
(2263,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:10'),
(2264,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:10'),
(2265,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:13'),
(2266,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:13'),
(2267,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:16'),
(2268,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:16'),
(2269,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:19'),
(2270,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:19'),
(2271,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:22'),
(2272,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:22'),
(2273,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:25'),
(2274,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:25'),
(2275,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:28'),
(2276,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:28'),
(2277,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:31'),
(2278,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:31'),
(2279,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:34'),
(2280,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:34'),
(2281,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:37'),
(2282,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:37'),
(2283,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:40'),
(2284,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:40'),
(2285,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:43'),
(2286,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:43'),
(2287,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:43'),
(2288,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:46'),
(2289,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:46'),
(2290,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:47'),
(2291,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:49'),
(2292,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:49'),
(2293,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:49'),
(2294,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:52'),
(2295,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:52'),
(2296,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:52'),
(2297,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:55'),
(2298,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:55'),
(2299,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:55'),
(2300,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:55'),
(2301,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:56'),
(2302,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:58'),
(2303,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:23:58'),
(2304,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:01'),
(2305,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:01'),
(2306,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:04'),
(2307,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:04'),
(2308,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:07'),
(2309,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:07'),
(2310,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:10'),
(2311,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:10'),
(2312,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:13'),
(2313,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:13'),
(2314,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:13'),
(2315,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:15'),
(2316,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:16'),
(2317,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:16'),
(2318,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:19'),
(2319,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:19'),
(2320,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:21'),
(2321,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:22'),
(2322,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:22'),
(2323,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:25'),
(2324,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:25'),
(2325,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:28'),
(2326,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:28'),
(2327,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:31'),
(2328,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:31'),
(2329,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:34'),
(2330,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:34'),
(2331,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:37'),
(2332,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:37'),
(2333,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:37'),
(2334,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:40'),
(2335,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:40'),
(2336,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:43'),
(2337,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:43'),
(2338,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:46'),
(2339,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:46'),
(2340,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:49'),
(2341,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:49'),
(2342,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:55'),
(2343,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:55'),
(2344,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:55'),
(2345,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:55'),
(2346,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:55'),
(2347,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:57'),
(2348,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:24:58'),
(2349,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:01'),
(2350,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:04'),
(2351,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:06'),
(2352,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:07'),
(2353,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:07'),
(2354,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:07'),
(2355,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:09'),
(2356,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:10'),
(2357,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:10'),
(2358,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:13'),
(2359,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:13'),
(2360,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:16'),
(2361,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:16'),
(2362,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:19'),
(2363,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:19'),
(2364,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:22'),
(2365,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:22'),
(2366,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:25'),
(2367,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:25'),
(2368,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:28'),
(2369,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:28'),
(2370,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:31'),
(2371,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:31'),
(2372,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:34'),
(2373,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:34'),
(2374,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:37'),
(2375,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:37'),
(2376,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:39'),
(2377,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:40'),
(2378,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:40'),
(2379,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:43'),
(2380,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:43'),
(2381,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:46'),
(2382,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:46'),
(2383,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:49'),
(2384,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:49'),
(2385,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:52'),
(2386,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:52'),
(2387,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:54'),
(2388,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:55'),
(2389,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:55'),
(2390,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:58'),
(2391,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:25:58'),
(2392,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:00'),
(2393,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:01'),
(2394,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:01'),
(2395,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:04'),
(2396,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:04'),
(2397,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:07'),
(2398,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:07'),
(2399,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:10'),
(2400,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:10'),
(2401,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:13'),
(2402,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:13'),
(2403,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:16'),
(2404,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:16'),
(2405,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:19'),
(2406,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:19'),
(2407,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:22'),
(2408,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:22'),
(2409,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:25'),
(2410,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:25'),
(2411,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:28'),
(2412,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:28'),
(2413,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:31'),
(2414,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:31'),
(2415,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:34'),
(2416,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:34'),
(2417,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:37'),
(2418,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:37'),
(2419,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:40'),
(2420,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:40'),
(2421,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:43'),
(2422,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:43'),
(2423,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:46'),
(2424,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:46'),
(2425,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:46'),
(2426,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:49'),
(2427,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:49'),
(2428,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:52'),
(2429,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:52'),
(2430,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:55'),
(2431,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:55'),
(2432,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:58'),
(2433,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:26:58'),
(2434,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:01'),
(2435,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:01'),
(2436,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:04'),
(2437,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:04'),
(2438,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:07'),
(2439,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:07'),
(2440,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:10'),
(2441,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:10'),
(2442,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:13'),
(2443,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:13'),
(2444,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:16'),
(2445,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:16'),
(2446,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:19'),
(2447,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:19'),
(2448,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:22'),
(2449,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:22'),
(2450,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:25'),
(2451,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:25'),
(2452,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:28'),
(2453,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:28'),
(2454,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:31'),
(2455,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:31'),
(2456,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:34'),
(2457,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:34'),
(2458,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:37'),
(2459,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:37'),
(2460,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:40'),
(2461,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:40'),
(2462,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:43'),
(2463,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:43'),
(2464,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:46'),
(2465,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:46'),
(2466,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:49'),
(2467,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:49'),
(2468,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:52'),
(2469,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:52'),
(2470,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:55'),
(2471,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:55'),
(2472,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:58'),
(2473,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:27:58'),
(2474,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:01'),
(2475,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:01'),
(2476,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:04'),
(2477,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:04'),
(2478,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:07'),
(2479,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:07'),
(2480,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:10'),
(2481,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:10'),
(2482,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:13'),
(2483,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:13'),
(2484,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:16'),
(2485,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:16'),
(2486,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:19'),
(2487,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:19'),
(2488,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:22'),
(2489,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:22'),
(2490,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:25'),
(2491,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:25'),
(2492,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:28'),
(2493,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:28'),
(2494,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:31'),
(2495,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:31'),
(2496,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:34'),
(2497,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:34'),
(2498,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:37'),
(2499,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:37'),
(2500,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:40'),
(2501,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:40'),
(2502,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:43'),
(2503,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:43'),
(2504,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:46'),
(2505,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:46'),
(2506,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:49'),
(2507,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:49'),
(2508,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:52'),
(2509,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:52'),
(2510,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:55'),
(2511,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:55'),
(2512,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:58'),
(2513,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:28:58'),
(2514,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:01'),
(2515,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:01'),
(2516,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:04'),
(2517,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:04'),
(2518,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:07'),
(2519,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:07'),
(2520,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:10'),
(2521,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:10'),
(2522,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:13'),
(2523,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:13'),
(2524,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:16'),
(2525,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:16'),
(2526,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:19'),
(2527,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:19'),
(2528,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:22'),
(2529,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:22'),
(2530,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:25'),
(2531,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:25'),
(2532,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:28'),
(2533,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:28'),
(2534,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:31'),
(2535,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:31'),
(2536,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:34'),
(2537,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:34'),
(2538,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:37'),
(2539,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:37'),
(2540,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:40'),
(2541,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:40'),
(2542,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:43'),
(2543,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:43'),
(2544,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:46'),
(2545,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:46'),
(2546,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:49'),
(2547,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:49'),
(2548,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:52'),
(2549,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:52'),
(2550,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:55'),
(2551,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:55'),
(2552,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:58'),
(2553,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:29:58'),
(2554,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:01'),
(2555,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:01'),
(2556,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:04'),
(2557,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:04'),
(2558,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:07'),
(2559,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:07'),
(2560,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:10'),
(2561,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:10'),
(2562,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:13'),
(2563,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:13'),
(2564,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:16'),
(2565,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:16'),
(2566,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:19'),
(2567,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:19'),
(2568,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:22'),
(2569,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:22'),
(2570,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:25'),
(2571,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:26'),
(2572,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:28'),
(2573,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:28'),
(2574,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:31'),
(2575,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:31'),
(2576,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:34'),
(2577,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:34'),
(2578,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:37'),
(2579,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:37'),
(2580,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:40'),
(2581,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:40'),
(2582,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:43'),
(2583,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:43'),
(2584,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:46'),
(2585,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:46'),
(2586,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:49'),
(2587,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:49'),
(2588,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:52'),
(2589,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:52'),
(2590,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:55'),
(2591,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:55'),
(2592,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:58'),
(2593,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:30:58'),
(2594,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:01'),
(2595,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:01'),
(2596,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:04'),
(2597,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:04'),
(2598,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:07'),
(2599,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:07'),
(2600,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:10'),
(2601,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:10'),
(2602,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:13'),
(2603,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:13'),
(2604,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:16'),
(2605,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:16'),
(2606,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:19'),
(2607,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:19'),
(2608,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:22'),
(2609,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:22'),
(2610,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:25'),
(2611,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:25'),
(2612,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:28'),
(2613,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:28'),
(2614,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:31'),
(2615,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:31'),
(2616,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:34'),
(2617,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:34'),
(2618,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:37'),
(2619,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:37'),
(2620,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:40'),
(2621,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:40'),
(2622,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:43'),
(2623,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:43'),
(2624,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:46'),
(2625,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:46'),
(2626,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:49'),
(2627,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:49'),
(2628,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:52'),
(2629,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:52'),
(2630,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:55'),
(2631,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:55'),
(2632,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:58'),
(2633,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:31:58'),
(2634,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:01'),
(2635,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:01'),
(2636,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:04'),
(2637,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:04'),
(2638,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:07'),
(2639,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:07'),
(2640,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:10'),
(2641,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:10'),
(2642,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:13'),
(2643,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:13'),
(2644,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:16'),
(2645,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:16'),
(2646,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:19'),
(2647,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:19'),
(2648,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:22'),
(2649,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:22'),
(2650,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:26'),
(2651,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:26'),
(2652,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:26'),
(2653,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:26'),
(2654,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:28'),
(2655,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:31'),
(2656,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:32'),
(2657,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:34'),
(2658,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:34'),
(2659,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:36'),
(2660,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:36'),
(2661,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:37'),
(2662,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:37'),
(2663,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:37'),
(2664,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:38'),
(2665,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:38'),
(2666,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:39'),
(2667,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:39'),
(2668,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:40'),
(2669,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:43'),
(2670,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:32:44'),
(2671,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:05'),
(2672,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:05'),
(2673,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:08'),
(2674,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:09'),
(2675,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:11'),
(2676,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:14'),
(2677,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:17'),
(2678,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:17'),
(2679,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:17'),
(2680,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:20'),
(2681,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:23'),
(2682,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:26'),
(2683,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:29'),
(2684,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:32'),
(2685,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:35'),
(2686,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:38'),
(2687,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:41'),
(2688,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:44'),
(2689,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:47'),
(2690,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:47'),
(2691,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:50'),
(2692,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:53'),
(2693,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:56'),
(2694,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:33:59'),
(2695,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:01'),
(2696,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:02'),
(2697,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:04'),
(2698,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:05'),
(2699,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:07'),
(2700,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:08'),
(2701,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:10'),
(2702,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:11'),
(2703,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:13'),
(2704,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:14'),
(2705,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:16'),
(2706,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:23'),
(2707,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:24'),
(2708,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:25'),
(2709,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:28'),
(2710,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:31'),
(2711,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:34'),
(2712,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:37'),
(2713,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:40'),
(2714,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:43'),
(2715,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:46'),
(2716,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:49'),
(2717,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:52'),
(2718,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:55'),
(2719,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:34:58'),
(2720,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:01'),
(2721,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:04'),
(2722,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:05'),
(2723,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:09'),
(2724,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:11'),
(2725,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:14'),
(2726,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:17'),
(2727,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:20'),
(2728,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:23'),
(2729,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:26'),
(2730,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:29'),
(2731,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:32'),
(2732,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:35'),
(2733,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:38'),
(2734,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:41'),
(2735,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:44'),
(2736,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:47'),
(2737,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:50'),
(2738,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:53'),
(2739,'109.200.255.202',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:35:56'),
(2740,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2741,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2742,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2743,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2744,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2745,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2746,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:51'),
(2747,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:53'),
(2748,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:56'),
(2749,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:36:59'),
(2750,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:02'),
(2751,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:05'),
(2752,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:08'),
(2753,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:11'),
(2754,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:14'),
(2755,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:17'),
(2756,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:20'),
(2757,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:23'),
(2758,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:26'),
(2759,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:29'),
(2760,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:32'),
(2761,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:35'),
(2762,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:38'),
(2763,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:41'),
(2764,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:44'),
(2765,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:47'),
(2766,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:50'),
(2767,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:53'),
(2768,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:56'),
(2769,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:37:59'),
(2770,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:02'),
(2771,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:05'),
(2772,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:08'),
(2773,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:11'),
(2774,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:14'),
(2775,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:17'),
(2776,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:20'),
(2777,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:23'),
(2778,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:26'),
(2779,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:29'),
(2780,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:32'),
(2781,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:35'),
(2782,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:38'),
(2783,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:41'),
(2784,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:44'),
(2785,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:47'),
(2786,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:50'),
(2787,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:53'),
(2788,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:56'),
(2789,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:38:59'),
(2790,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:39:02'),
(2791,'46.250.18.149',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 07:39:05'),
(2792,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:38:59'),
(2793,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:39:00'),
(2794,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:39:00'),
(2795,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:40:25'),
(2796,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:40:27'),
(2797,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 12:40:27'),
(2798,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:42:31'),
(2799,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:42:31'),
(2800,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:42:35'),
(2801,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:44:26'),
(2802,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:44:26'),
(2803,'179.43.149.114',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-23 17:44:26'),
(2804,'93.170.44.39',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-24 04:40:04'),
(2805,'40.77.167.151',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-24 10:33:10'),
(2806,'34.122.147.229',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-24 10:47:52'),
(2807,'205.169.39.178',NULL,'database_connect','Успешное подключение к основной БД','low','2025-09-24 10:48:02');
/*!40000 ALTER TABLE `security_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_alerts`
--

DROP TABLE IF EXISTS `site_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` int(11) NOT NULL COMMENT 'ID записи мониторинга',
  `alert_type` enum('down','slow','ssl_expiring','ssl_expired') NOT NULL COMMENT 'Тип алерта',
  `message` text NOT NULL COMMENT 'Сообщение алерта',
  `is_resolved` tinyint(1) DEFAULT 0 COMMENT 'Решен ли алерт',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL COMMENT 'Время решения алерта',
  PRIMARY KEY (`id`),
  KEY `idx_monitor_type` (`monitor_id`,`alert_type`),
  KEY `idx_unresolved` (`is_resolved`,`created_at`),
  CONSTRAINT `site_alerts_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `site_monitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Алерты и уведомления';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_alerts`
--

LOCK TABLES `site_alerts` WRITE;
/*!40000 ALTER TABLE `site_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_check_logs`
--

DROP TABLE IF EXISTS `site_check_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_check_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(512) NOT NULL COMMENT 'URL проверяемого сайта',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP адрес пользователя',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent браузера',
  `results_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Результаты проверки в JSON формате' CHECK (json_valid(`results_json`)),
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Время создания записи',
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`,`created_at`) COMMENT 'Индекс для rate limiting',
  KEY `idx_url` (`url`(100)) COMMENT 'Индекс для поиска по URL',
  KEY `idx_created` (`created_at`) COMMENT 'Индекс для сортировки по времени'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Логи проверки доступности сайтов';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_check_logs`
--

LOCK TABLES `site_check_logs` WRITE;
/*!40000 ALTER TABLE `site_check_logs` DISABLE KEYS */;
INSERT INTO `site_check_logs` VALUES
(1,'https://worldmates.club','93.170.44.119','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','{\"url\":\"https:\\/\\/worldmates.club\",\"timestamp\":\"2025-08-09T17:57:41+03:00\",\"general\":{\"url\":\"https:\\/\\/worldmates.club\",\"host\":\"worldmates.club\",\"ip\":\"195.22.131.11\",\"check_time\":\"2025-08-09T17:57:41+03:00\",\"server\":null,\"content_length\":66867,\"content_type\":\"text\\/html; charset=UTF-8\"},\"locations\":[{\"location\":\"kyiv\",\"location_name\":\"Київ, Україна\",\"response_time\":265,\"status_code\":200,\"status_text\":\"OK\",\"dns_time\":63,\"connect_time\":64,\"error\":null,\"content_length\":66786,\"content_type\":\"text\\/html; charset=UTF-8\",\"server_ip\":\"195.22.131.11\"},{\"location\":\"frankfurt\",\"location_name\":\"Франкфурт, Німеччина\",\"response_time\":134,\"status_code\":200,\"status_text\":\"OK\",\"dns_time\":41,\"connect_time\":41,\"error\":null,\"content_length\":66832,\"content_type\":\"text\\/html; charset=UTF-8\",\"server_ip\":\"195.22.131.11\"},{\"location\":\"london\",\"location_name\":\"Лондон, Великобританія\",\"response_time\":160,\"status_code\":200,\"status_text\":\"OK\",\"dns_time\":71,\"connect_time\":72,\"error\":null,\"content_length\":66867,\"content_type\":\"text\\/html; charset=UTF-8\",\"server_ip\":\"195.22.131.11\"}],\"ssl\":{\"valid\":true,\"issuer\":\"R10\",\"subject\":\"worldmates.club\",\"valid_from\":\"2025-07-08 19:55:58\",\"valid_to\":\"2025-10-06 19:55:57\",\"days_until_expiry\":58,\"alt_names\":[\"chat.worldmates.club\",\"music.worldmates.club\",\"video.worldmates.club\",\"worldmates.club\",\"www.worldmates.club\"],\"signature_algorithm\":\"RSA-SHA256\"},\"headers\":{\"server\":\"nginx\",\"date\":\"Sat, 09 Aug 2025 14:57:41 GMT\",\"content-type\":\"text\\/html; charset=UTF-8\",\"set-cookie\":\"src=1; expires=Sun, 09 Aug 2026 20:46:27 GMT; Max-Age=31556926; path=\\/\",\"expires\":\"Thu, 19 Nov 1981 08:52:00 GMT\",\"cache-control\":\"no-store, no-cache, must-revalidate\",\"pragma\":\"no-cache\",\"location\":\"https:\\/\\/worldmates.club\\/welcome\",\"strict-transport-security\":\"max-age=31536000;\",\"Content-Type\":\"text\\/html; charset=UTF-8\",\"Content-Length\":0}}','2025-08-09 14:57:41');
/*!40000 ALTER TABLE `site_check_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_monitor_results`
--

DROP TABLE IF EXISTS `site_monitor_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_monitor_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` int(11) NOT NULL COMMENT 'ID записи мониторинга',
  `location` varchar(50) NOT NULL COMMENT 'Локация проверки',
  `status_code` int(11) DEFAULT NULL COMMENT 'HTTP статус код',
  `response_time` int(11) DEFAULT NULL COMMENT 'Время ответа в миллисекундах',
  `error_message` text DEFAULT NULL COMMENT 'Сообщение об ошибке если есть',
  `is_up` tinyint(1) NOT NULL COMMENT 'Доступен ли сайт',
  `checked_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Время проверки',
  PRIMARY KEY (`id`),
  KEY `idx_monitor_time` (`monitor_id`,`checked_at`),
  KEY `idx_location` (`location`),
  KEY `idx_status` (`is_up`,`checked_at`),
  CONSTRAINT `site_monitor_results_ibfk_1` FOREIGN KEY (`monitor_id`) REFERENCES `site_monitors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Результаты мониторинга сайтов';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_monitor_results`
--

LOCK TABLES `site_monitor_results` WRITE;
/*!40000 ALTER TABLE `site_monitor_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_monitor_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_monitors`
--

DROP TABLE IF EXISTS `site_monitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_monitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID пользователя (NULL для анонимных)',
  `url` varchar(512) NOT NULL COMMENT 'URL для мониторинга',
  `check_interval` int(11) DEFAULT 300 COMMENT 'Интервал проверки в секундах',
  `locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Массив локаций для проверки' CHECK (json_valid(`locations`)),
  `email_notifications` tinyint(1) DEFAULT 0 COMMENT 'Включены ли email уведомления',
  `webhook_url` varchar(512) DEFAULT NULL COMMENT 'URL для webhook уведомлений',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Активен ли мониторинг',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_next_check` (`created_at`,`check_interval`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Настройки мониторинга сайтов';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_monitors`
--

LOCK TABLES `site_monitors` WRITE;
/*!40000 ALTER TABLE `site_monitors` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_monitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `is_public` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`),
  KEY `idx_public` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES
(1,'site_maintenance','0','boolean',0,'2025-08-04 14:12:37'),
(2,'registration_enabled','1','boolean',0,'2025-08-04 14:12:37'),
(3,'max_upload_size','10485760','number',0,'2025-08-04 14:12:37'),
(4,'session_timeout','3600','number',0,'2025-08-04 14:12:37'),
(5,'enable_recaptcha','1','boolean',0,'2025-08-04 14:12:37');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_operators`
--

DROP TABLE IF EXISTS `support_operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `support_operators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('operator','supervisor','admin') DEFAULT 'operator',
  `department` varchar(100) DEFAULT 'general',
  `is_online` tinyint(1) DEFAULT 0,
  `last_activity` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `support_operators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_operators`
--

LOCK TABLES `support_operators` WRITE;
/*!40000 ALTER TABLE `support_operators` DISABLE KEYS */;
INSERT INTO `support_operators` VALUES
(1,NULL,'Яков Техпідтримка','jaha@sthost.pro',NULL,'operator','general',0,NULL,NULL,'2025-09-21 12:19:34'),
(2,NULL,'Максим Менеджер','maks@sthost.pro',NULL,'supervisor','sales',0,NULL,NULL,'2025-09-21 12:19:34'),
(3,NULL,'Олександр Адмін','support@sthost.pro',NULL,'admin','general',0,NULL,NULL,'2025-09-21 12:19:34'),
(4,NULL,'admin','admin@sthost.pro',NULL,'operator','general',0,'2025-09-23 07:33:05',NULL,'2025-09-21 12:16:01'),
(5,NULL,'Олександр','Олександр@sthost.pro',NULL,'operator','general',0,'2025-09-21 13:44:00',NULL,'2025-09-21 12:21:40');
/*!40000 ALTER TABLE `support_operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translation_key` varchar(255) NOT NULL,
  `language` enum('ua','en','ru') NOT NULL,
  `translation_value` text NOT NULL,
  `section` varchar(100) DEFAULT 'general',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_translation` (`translation_key`,`language`),
  KEY `idx_key` (`translation_key`),
  KEY `idx_lang` (`language`),
  KEY `idx_section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity`
--

LOCK TABLES `user_activity` WRITE;
/*!40000 ALTER TABLE `user_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `data` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `language` enum('ua','en','ru') DEFAULT 'ua',
  `registration_date` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `marketing_emails` tinyint(1) DEFAULT 0,
  `fossbilling_client_id` int(11) DEFAULT NULL,
  `ispmanager_username` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_active` (`is_active`),
  KEY `idx_users_email_active` (`email`,`is_active`),
  KEY `idx_users_registration_date` (`registration_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'dncdante@isi.gov.ua','$2y$10$JaakHvsDvWZRCXjpRpwgCeTEtqbcJptvO7gw8QJANT5DETakeVp1C','Олександр тостер','+380930253941',0,'/uploads/avatars/avatar_1_1755441051.webp','2025-08-17 14:28:58','2025-08-17 14:33:04','ua','2025-08-17 09:01:42','2025-08-17 14:09:53',1,1,NULL,NULL),
(2,'slavka.sich@gmail.com','$2y$10$0rPiOzQR6JIslpxXa50xCefEckmg2OVkBQ9mgVfmV7d40HTDtpGEm','Яков','+380974639515',0,NULL,'2025-08-17 14:28:58','2025-08-17 14:28:58','ru','2025-08-17 13:12:07',NULL,1,0,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vds_plans`
--

DROP TABLE IF EXISTS `vds_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vds_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_ua` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `name_ru` varchar(100) DEFAULT NULL,
  `type` enum('virtual','dedicated') NOT NULL,
  `cpu_cores` int(11) NOT NULL,
  `ram_mb` int(11) NOT NULL,
  `disk_gb` int(11) NOT NULL,
  `bandwidth_gb` int(11) NOT NULL,
  `price_monthly` decimal(10,2) NOT NULL,
  `price_yearly` decimal(10,2) NOT NULL,
  `is_popular` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `features_ua` text DEFAULT NULL,
  `features_en` text DEFAULT NULL,
  `features_ru` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vds_plans`
--

LOCK TABLES `vds_plans` WRITE;
/*!40000 ALTER TABLE `vds_plans` DISABLE KEYS */;
INSERT INTO `vds_plans` VALUES
(1,'VDS-1','VDS-1','VDS-1','virtual',1,1024,20,1000,299.00,2990.00,0,1,'KVM віртуалізація, SSD диск, Root доступ',NULL,NULL),
(2,'VDS-2','VDS-2','VDS-2','virtual',2,2048,40,2000,599.00,5990.00,1,1,'KVM віртуалізація, SSD диск, Root доступ, Безкоштовна міграція',NULL,NULL),
(3,'VDS-4','VDS-4','VDS-4','virtual',4,4096,80,4000,1199.00,11990.00,0,1,'KVM віртуалізація, SSD диск, Root доступ, Безкоштовна міграція, 24/7 підтримка',NULL,NULL);
/*!40000 ALTER TABLE `vds_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `chat_statistics`
--

/*!50001 DROP VIEW IF EXISTS `chat_statistics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `chat_statistics` AS select cast(`cs`.`created_at` as date) AS `date`,count(0) AS `total_sessions`,count(case when `cs`.`status` = 'closed' then 1 end) AS `closed_sessions`,count(case when `cs`.`priority` = 'urgent' then 1 end) AS `urgent_sessions`,avg(timestampdiff(MINUTE,`cs`.`created_at`,`cs`.`closed_at`)) AS `avg_session_duration`,count(distinct `cs`.`operator_id`) AS `active_operators` from `chat_sessions` `cs` where `cs`.`created_at` >= current_timestamp() - interval 30 day group by cast(`cs`.`created_at` as date) order by cast(`cs`.`created_at` as date) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `domain_search_statistics`
--

/*!50001 DROP VIEW IF EXISTS `domain_search_statistics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `domain_search_statistics` AS select cast(`dl`.`created_at` as date) AS `search_date`,`dl`.`domain_zone` AS `domain_zone`,count(0) AS `total_checks`,sum(case when `dl`.`is_available` = 1 then 1 else 0 end) AS `available_count`,sum(case when `dl`.`is_available` = 0 then 1 else 0 end) AS `taken_count`,avg(`dl`.`check_time_ms`) AS `avg_check_time_ms`,count(distinct `dl`.`session_id`) AS `unique_sessions`,count(distinct `dl`.`user_id`) AS `unique_users` from `domain_check_logs` `dl` group by cast(`dl`.`created_at` as date),`dl`.`domain_zone` order by cast(`dl`.`created_at` as date) desc,count(0) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `location_stats`
--

/*!50001 DROP VIEW IF EXISTS `location_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `location_stats` AS select json_unquote(json_extract(`location_data`.`value`,'$.location')) AS `location`,count(0) AS `checks_count`,avg(json_extract(`location_data`.`value`,'$.response_time')) AS `avg_response_time`,sum(case when json_extract(`location_data`.`value`,'$.status_code') between 200 and 299 then 1 else 0 end) AS `success_count` from (`sthostsitedb`.`site_check_logs` join JSON_TABLE(json_extract(`sthostsitedb`.`site_check_logs`.`results_json`,'$.locations'), '$[*]' COLUMNS (`row_id` FOR ORDINALITY, `value` longtext PATH '$')) `location_data`) where `sthostsitedb`.`site_check_logs`.`created_at` > current_timestamp() - interval 24 hour and json_valid(`sthostsitedb`.`site_check_logs`.`results_json`) group by json_unquote(json_extract(`location_data`.`value`,'$.location')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `operator_performance`
--

/*!50001 DROP VIEW IF EXISTS `operator_performance`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `operator_performance` AS select `so`.`id` AS `id`,`so`.`name` AS `name`,`so`.`role` AS `role`,count(`cs`.`id`) AS `total_sessions`,count(case when `cs`.`status` = 'closed' then 1 end) AS `completed_sessions`,avg(timestampdiff(MINUTE,`cs`.`created_at`,`cs`.`closed_at`)) AS `avg_resolution_time`,count(case when `cs`.`priority` = 'urgent' then 1 end) AS `urgent_handled`,`os`.`is_online` AS `is_online`,`os`.`current_sessions` AS `current_sessions`,`so`.`last_activity` AS `last_activity` from ((`support_operators` `so` left join `chat_sessions` `cs` on(`so`.`id` = `cs`.`operator_id` and `cs`.`created_at` >= current_timestamp() - interval 7 day)) left join `operator_status` `os` on(`so`.`id` = `os`.`operator_id`)) group by `so`.`id`,`so`.`name`,`so`.`role`,`os`.`is_online`,`os`.`current_sessions`,`so`.`last_activity` order by count(case when `cs`.`status` = 'closed' then 1 end) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `popular_checked_sites`
--

/*!50001 DROP VIEW IF EXISTS `popular_checked_sites`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `popular_checked_sites` AS select substring_index(substring_index(`site_check_logs`.`url`,'/',3),'//',-1) AS `domain`,count(0) AS `check_count`,avg(json_extract(`site_check_logs`.`results_json`,'$.locations[0].response_time')) AS `avg_response_time`,max(`site_check_logs`.`created_at`) AS `last_checked` from `site_check_logs` where `site_check_logs`.`created_at` > current_timestamp() - interval 7 day and json_valid(`site_check_logs`.`results_json`) group by substring_index(substring_index(`site_check_logs`.`url`,'/',3),'//',-1) order by count(0) desc limit 100 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `popular_domains_view`
--

/*!50001 DROP VIEW IF EXISTS `popular_domains_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`sthostdb`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `popular_domains_view` AS select `domain_zones`.`zone` AS `zone`,`domain_zones`.`price_registration` AS `price_registration`,`domain_zones`.`price_renewal` AS `price_renewal`,`domain_zones`.`price_transfer` AS `price_transfer`,`domain_zones`.`description` AS `description`,case when `domain_zones`.`zone` like '%.ua' then 'Український домен' when `domain_zones`.`zone` in ('.com','.net','.org','.info','.biz') then 'Міжнародний домен' else 'Спеціальний домен' end AS `domain_type`,case when `domain_zones`.`price_registration` <= 150 then 'Економ' when `domain_zones`.`price_registration` <= 300 then 'Стандарт' else 'Преміум' end AS `price_category`,`domain_zones`.`features` AS `features`,`domain_zones`.`whois_privacy_available` AS `whois_privacy_available`,`domain_zones`.`auto_renewal_available` AS `auto_renewal_available` from `domain_zones` where `domain_zones`.`is_active` = 1 and `domain_zones`.`is_popular` = 1 order by case when `domain_zones`.`zone` like '%.ua' then 1 else 2 end,`domain_zones`.`price_registration` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-24 13:58:17
