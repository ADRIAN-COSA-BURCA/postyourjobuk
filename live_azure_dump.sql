/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for Linux (x86_64)
--
-- Host: hr-analytical-db.mysql.database.azure.com    Database: postyourjobhere
-- ------------------------------------------------------
-- Server version	8.0.44-azure

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
-- Current Database: `postyourjobhere`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `postyourjobhere` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `postyourjobhere`;

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicants` (
  `applicant_id` int unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int unsigned NOT NULL COMMENT 'Which job they applied to',
  `tenant_id` int unsigned NOT NULL COMMENT 'Which company owns this (denormalized for security)',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full name',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contact email',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contact phone (optional)',
  `cv_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Original uploaded filename',
  `cv_storage_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full server path to file',
  `cv_file_size` int unsigned DEFAULT NULL COMMENT 'File size in bytes',
  `ai_score` int unsigned DEFAULT '0' COMMENT 'AI score 0-100 (0=not processed yet)',
  `ai_summary` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'AI-generated detailed candidate evaluation matrix',
  `ai_processed_at` timestamp NULL DEFAULT NULL COMMENT 'When AI finished processing',
  `status` enum('new','reviewed','shortlisted','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When application submitted',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Applicant IP address (IPv4/IPv6)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`applicant_id`),
  UNIQUE KEY `unique_email_job` (`email`,`job_id`),
  KEY `idx_job_id` (`job_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_job_score` (`job_id`,`ai_score`),
  KEY `idx_tenant_date` (`tenant_id`,`applied_at`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_ai_score` (`ai_score`),
  KEY `idx_tenant_job_score` (`tenant_id`,`job_id`,`ai_score`),
  CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `applicants_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1019 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job applications with AI scoring';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applicants`
--

LOCK TABLES `applicants` WRITE;
/*!40000 ALTER TABLE `applicants` DISABLE KEYS */;
INSERT INTO `applicants` VALUES
(1012,1016,8,'nnjhmn bnhb','bvnbn@yahoo.com','967857878989','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1016/1781239291_Adrian_Cosa_-_Night_Concierge.docx',NULL,NULL,NULL,NULL,'new','2026-06-12 04:41:31',NULL,'2026-06-12 04:41:31'),
(1013,1016,8,'test name','test@yahoo.com','0909090090909','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1016/1781323124_Adrian_Cosa_-_Night_Concierge.docx',NULL,NULL,NULL,NULL,'new','2026-06-13 03:58:44',NULL,'2026-06-13 03:58:44'),
(1015,1015,8,'xtest xxtt','xoxo@yahoo.com','9292929229','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1015/1781323527_Adrian_Cosa_-_Night_Concierge.docx',NULL,NULL,NULL,NULL,'new','2026-06-13 04:05:27',NULL,'2026-06-13 04:05:27'),
(1016,1014,8,'testtest aa','fofo@yahoo.com','89887766554','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1014/1781323777_Adrian_Cosa_-_Night_Concierge.docx',NULL,NULL,NULL,NULL,'new','2026-06-13 04:09:37',NULL,'2026-06-13 04:09:37'),
(1017,1016,8,'jojn jojn1','jojo1@yahoo.com','9494394949','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1016/1781323997_Adrian_Cosa_-_Night_Concierge.docx',NULL,NULL,NULL,NULL,'new','2026-06-13 04:13:18',NULL,'2026-06-13 04:13:18'),
(1018,1015,8,'hhoohh','kkoo@yahoo.com','84848448483','Adrian Cosa - Night Concierge.docx','blob://cv-storage/tenant-8/job-1015/1781324415_Adrian_Cosa_-_Night_Concierge.docx',NULL,85,'Adrian Cosa Burca is a highly experienced candidate with over 15 years in luxury hospitality, including significant night operations management in 5-star hotels. He demonstrates excellent guest service skills, extensive hotel experience, and multilingual abilities, aligning well with most requirements. His past role as a Night Supervisor shows direct operational leadership, though his recent roles have been more solo. The primary gap is the lack of explicit experience or knowledge in human resources practices related to staffing and staff development.','2026-06-13 04:20:31','reviewed','2026-06-13 04:20:16',NULL,'2026-06-13 04:20:16');
/*!40000 ALTER TABLE `applicants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `job_id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL COMMENT 'Which company posted this job',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Job title/position name',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full job description (rich text)',
  `requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Required skills/qualifications',
  `location` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Job location (NULL = Remote)',
  `salary` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Salary range (flexible format)',
  `employment_type` enum('full-time','part-time','contract','internship') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'full-time' COMMENT 'Type of employment',
  `status` enum('active','inactive','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'active=visible on board, inactive=hidden, closed=filled',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When job was posted',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last modification time',
  `closed_at` timestamp NULL DEFAULT NULL COMMENT 'When job was closed/filled',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`job_id`),
  KEY `idx_tenant_id` (`tenant_id`),
  KEY `idx_tenant_status` (`tenant_id`,`status`),
  KEY `idx_status_created` (`status`,`created_at`),
  KEY `idx_employment_type` (`employment_type`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1017 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job postings from companies';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES
(1011,10,'Senior Developer','We are looking for a Senior dev','5+ years experience',NULL,NULL,'full-time','active','2026-06-10 03:03:15','2026-06-10 03:03:15',NULL,1),
(1013,8,'Bid Coordinator','We are seeking a highly organised, detail-oriented Bid Assistant / Bid Coordinator to join our London office. This is an exciting opportunity for a motivated individual to be part of a dynamic and fast-paced environment with international exposure, ou will support the end-to-end bid process, international procurement projects, and supply chain coordination. This includes preparing competitive offers, liaising with global partners, and ensuring','Minimum 2 years’ experience in project administration, bid support, or procurement\r\n\r\n· Graduate-level education (preferred in Business, Supply Chain, or related fields)\r\n\r\n· Strong attention to detail and excellent organisation skills\r\n\r\n· Professional telephone manner and fluent spoken and written English\r\n\r\n· Experience working to tight deadlines and delivering results under pressure\r\n\r\n· Strong knowledge of Excel, Word, and Outlook\r\n\r\n· Confident communicator with good negotiation and supplier relationship management skills','','','full-time','active','2026-06-11 00:51:51','2026-06-11 00:51:51',NULL,1),
(1014,8,'Night Manager','We are seeking a professional and dynamic Night Manager/Duty Manager to oversee hotel operations during the overnight shift. The ideal candidate will possess strong leadership skills, excellent guest service abilities, and relevant experience within the hospitality industry. This role offers an opportunity to ensure smooth night-time operations, maintain high standards of service, and lead a dedicated team to deliver exceptional guest experiences. Multilingual or bilingual skills are highly desirable to effectively communicate with diverse guests and staff.','Proven supervising experience within the hospitality or hotel industry.\r\nStrong leadership skills with the ability to manage a diverse team effectively.\r\nExcellent guest service skills with a focus on delivering memorable experiences.\r\nPrevious hotel experience is highly advantageous.\r\nKnowledge of human resources practices related to staffing and staff development.','London','40000','full-time','active','2026-06-11 22:29:36','2026-06-11 22:29:36',NULL,1),
(1015,8,'Duty Manager','Professional and dynamic Night Manager/Duty Manager to oversee hotel operations during the overnight shift. The ideal candidate will possess strong leadership skills, excellent guest service abilities, and relevant experience within the hospitality industry. This role offers an opportunity to ensure smooth night-time operations, maintain high standards of service, and lead a dedicated team to deliver exceptional guest experiences.','Strong leadership skills with the ability to manage a diverse team effectively.\r\nExcellent guest service skills with a focus on delivering memorable experiences.\r\nPrevious hotel experience is highly advantageous.\r\nKnowledge of human resources practices related to staffing and staff development.\r\nMultilingual or bilingual abilities are preferred to facilitate communication with international guests and staff.','London','50000','contract','active','2026-06-11 22:48:48','2026-06-11 22:48:48',NULL,1),
(1016,8,'GENERAL MANAGER','professional and dynamic Night Manager/Duty Manager to oversee hotel operations during the overnight shift. The ideal candidate will possess strong leadership skills, excellent guest service abilities, and relevant experience within the hospitality industry. This role offers an opportunity to ensure smooth night-time operations, maintain high standards of service,xxxxxxxxxxxxxxxxxxxxxxx','roven supervising experience within the hospitality or hotel industry.\r\nStrong leadership skills with the ability to manage a diverse team effectively.\r\nExcellent guest service skills with a focus on delivering memorable experiences.\r\nPrevious hotel experience is highly advantageous.\r\nKnowledge of human resources practices related to staffing and staff development.xxxxxxxxxxxxxxxxx','lONDON','70000','part-time','active','2026-06-11 22:55:19','2026-06-12 18:21:18',NULL,1);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_audit_logs`
--

DROP TABLE IF EXISTS `security_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tenant_id` int DEFAULT '0',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=440 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_audit_logs`
--

LOCK TABLES `security_audit_logs` WRITE;
/*!40000 ALTER TABLE `security_audit_logs` DISABLE KEYS */;
INSERT INTO `security_audit_logs` VALUES
(361,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 02:47:37'),
(362,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 02:47:42'),
(363,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 02:47:44'),
(364,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 02:47:48'),
(365,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 02:47:55'),
(366,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:04:46'),
(367,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:12:06'),
(368,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:12:36'),
(369,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:16:28'),
(370,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:17:00'),
(371,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:17:04'),
(372,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:31:39'),
(373,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 03:43:42'),
(374,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 04:10:31'),
(375,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 04:10:41'),
(376,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 04:10:43'),
(377,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 05:08:56'),
(378,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 05:09:03'),
(379,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 05:09:07'),
(380,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 05:09:09'),
(381,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 05:11:08'),
(382,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:30:22'),
(383,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:30:39'),
(384,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:32:27'),
(385,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:34:05'),
(386,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:34:07'),
(387,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:34:08'),
(388,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 20:34:13'),
(389,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 21:54:43'),
(390,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 21:54:54'),
(391,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 21:54:58'),
(392,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 23:41:09'),
(393,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 23:41:29'),
(394,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-10 23:41:43'),
(395,'JOB_RECORD_CREATED',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','New job created (ID: 1012).','2026-06-11 00:29:28'),
(396,'JOB_RECORD_CREATED',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','New job created (ID: 1013).','2026-06-11 00:51:51'),
(397,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 01:51:44'),
(398,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 01:54:09'),
(399,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:05:43'),
(400,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:07:15'),
(401,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:16:54'),
(402,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:17:09'),
(403,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:17:41'),
(404,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:17:45'),
(405,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:23:18'),
(406,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:24:02'),
(407,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:24:21'),
(408,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:24:25'),
(409,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:26:06'),
(410,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:32:27'),
(411,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:33:59'),
(412,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:40:11'),
(413,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:40:22'),
(414,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:40:33'),
(415,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 02:40:42'),
(416,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 03:32:22'),
(417,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-11 03:32:34'),
(418,'JOB_RECORD_CREATED',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','New job created (ID: 1016).','2026-06-11 22:55:20'),
(419,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 04:44:51'),
(420,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 04:45:02'),
(421,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 04:51:51'),
(422,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 18:20:12'),
(423,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 18:20:18'),
(424,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 18:20:29'),
(425,'JOB_RECORD_UPDATED',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Job record modified (ID: 1016).','2026-06-12 18:21:18'),
(426,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 18:24:27'),
(427,'UNAUTHORIZED_ACCESS',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Unauthorized attempt to view Job ID: 0','2026-06-12 18:27:34'),
(428,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-12 21:37:08'),
(429,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-12 21:37:41'),
(430,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-12 22:51:13'),
(431,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 02:32:35'),
(432,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 02:56:58'),
(433,'JOB_RECORD_DELETED',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Job deleted (ID: 1012).','2026-06-13 02:58:19'),
(434,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 02:59:29'),
(435,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 03:01:41'),
(436,'ADMIN_USER_QUARANTINE',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Super Admin changed corporate account posture to: suspended','2026-06-13 03:03:21'),
(437,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 03:03:29'),
(438,'ADMIN_USER_REHABILITATION',8,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Super Admin changed corporate account posture to: active','2026-06-13 03:03:49'),
(439,'ACCESS_DASHBOARD',0,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','Admin accessed the System Dashboard.','2026-06-13 03:03:56');
/*!40000 ALTER TABLE `security_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `tenant_id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name of company',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Login username (must be unique)',
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bcrypt hashed password',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `logo_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL/path to company logo (optional)',
  `status` enum('active','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'Admin can suspend accounts',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When tenant registered',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Last successful login time',
  `failed_login_attempts` int NOT NULL DEFAULT '0',
  `lockout_until` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`tenant_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_active` (`is_active`),
  KEY `idx_super_admin` (`is_super_admin`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Company accounts that post jobs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES
(8,'Test Corporation Ltd',1,'recruiter@test.com','$2y$10$a5LE5t1lSWGYSQckUYrl6en2T1Zn90x1ADaTnDXRpExzUKx.tE9.K',0,NULL,'active','2026-06-10 02:13:37','2026-06-13 04:21:54',0,NULL,NULL),
(9,'System Root',1,'admin@postyourjobhere.com','$2y$10$vH9jw634.03QIdNAL70mOuHAAKehs/UD9Zj8N.oyk5/VTPDqkGi1a',1,NULL,'active','2026-06-10 02:36:00','2026-06-10 20:28:16',0,NULL,NULL),
(10,'Test Company',1,'test@company.com','dummy',0,NULL,'active','2026-06-10 02:52:51',NULL,0,NULL,NULL);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-13 20:16:47
