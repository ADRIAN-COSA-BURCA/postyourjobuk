SET FOREIGN_KEY_CHECKS = 0;

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

-- ========================================================
-- 1. DROP EXISTING TABLES IN DEPENDENCY ORDER
-- ========================================================
DROP TABLE IF EXISTS `applicants`;
DROP TABLE IF EXISTS `jobs`;
DROP TABLE IF EXISTS `security_audit_logs`;
DROP TABLE IF EXISTS `tenants`;

-- ========================================================
-- 2. CREATE BASE MASTER TABLES FIRST (No External Dependencies)
-- ========================================================

-- Table structure for table `tenants`
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `tenant_id` int unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name of company',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Login username (must be unique)',
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Bcrypt hashed password',
  `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `logo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL/path to company logo (optional)',
  `status` enum('active','suspended') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'Admin can suspend accounts',
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Company accounts that post jobs';
/*!40101 SET character_set_client = @saved_cs_client */;

-- Table structure for table `security_audit_logs`
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tenant_id` int DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


-- ========================================================
-- 3. CREATE CHILD TABLES (That Depend on Tenants)
-- ========================================================

-- Table structure for table `jobs`
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `job_id` int unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int unsigned NOT NULL COMMENT 'Which company posted this job',
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Job title/position name',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full job description (rich text)',
  `requirements` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Required skills/qualifications',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Job location (NULL = Remote)',
  `salary` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Salary range (flexible format)',
  `employment_type` enum('full-time','part-time','contract','internship') COLLATE utf8mb4_unicode_ci DEFAULT 'full-time' COMMENT 'Type of employment',
  `status` enum('active','inactive','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'active' COMMENT 'active=visible on board, inactive=hidden, closed=filled',
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
) ENGINE=InnoDB AUTO_INCREMENT=1010 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job postings from companies';
/*!40101 SET character_set_client = @saved_cs_client */;


-- Table structure for table `applicants`
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `applicants` (
  `applicant_id` int unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int unsigned NOT NULL COMMENT 'Which job they applied to',
  `tenant_id` int unsigned NOT NULL COMMENT 'Which company owns this (denormalized for security)',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full name',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contact email',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contact phone (optional)',
  `cv_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Original uploaded filename',
  `cv_storage_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Full server path to file',
  `cv_file_size` int unsigned DEFAULT NULL COMMENT 'File size in bytes',
  `ai_score` int unsigned DEFAULT '0' COMMENT 'AI score 0-100 (0=not processed yet)',
  `ai_summary` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'AI-generated detailed candidate evaluation matrix',
  `ai_processed_at` timestamp NULL DEFAULT NULL COMMENT 'When AI finished processing',
  `status` enum('pending','reviewed','shortlisted','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'Application status (for future workflow)',
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When application submitted',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Applicant IP address (IPv4/IPv6)',
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
) ENGINE=InnoDB AUTO_INCREMENT=1012 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Job applications with AI scoring';
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- ========================================================
-- 4. SEED CORPORATE LIVE TEST CREDENTIALS
-- ========================================================
USE postyourjobhere;

INSERT INTO tenants (company_name, is_active, email, password_hash, is_super_admin, status, created_at)
VALUES (
    'Test Corporation Ltd', 
    1, 
    'recruiter@test.com', 
    '$2y$10$U6pEA.pW9Gg87RvjPrB8vO6PzXg8Kz9A6l7C6n6K6Fm6vO6PzXg8K', -- Secure Bcrypt representation of: password
    0, 
    'active', 
    NOW()
);

SET FOREIGN_KEY_CHECKS = 1;