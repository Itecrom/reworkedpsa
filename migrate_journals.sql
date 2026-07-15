-- ============================================
-- PSA Journal System Restructure Migration
-- ============================================
-- This migration adds:
-- 1. Views tracking for journals and articles
-- 2. Analytics table for detailed tracking
-- 3. PDF size validation support
-- ============================================

-- Add views column to journals table
ALTER TABLE `journals`
  ADD COLUMN `views` int DEFAULT '0' AFTER `downloads`;

-- Add views column to journal_topics (articles) table
ALTER TABLE `journal_topics`
  ADD COLUMN `views` int DEFAULT '0' AFTER `downloads`,
  ADD COLUMN `article_author` varchar(255) DEFAULT NULL AFTER `topic`,
  ADD COLUMN `article_summary` text DEFAULT NULL AFTER `article_author`;

-- Create journal analytics table for detailed tracking
CREATE TABLE `journal_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `journal_id` int DEFAULT NULL,
  `article_id` int DEFAULT NULL,
  `action` enum('view','download') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_journal_id` (`journal_id`),
  KEY `idx_article_id` (`article_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Create journal_settings table for configuration
CREATE TABLE `journal_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert default settings
INSERT INTO `journal_settings` (`setting_key`, `setting_value`) VALUES
  ('max_pdf_size_mb', '10'),
  ('pdf_quality', 'medium'),
  ('enable_analytics', '1'),
  ('articles_per_page', '10');

-- ============================================
-- END OF MIGRATION
-- ============================================
