# PSA Journal System Restructure - Implementation Complete

## Overview
This document describes the comprehensive restructure of the PSA journal system, including database changes, new features, and file organization.

---

## 1. Database Changes (SQL Migration)

### File: `migrate_journals.sql`

Run this SQL file on your database to apply the changes:

```sql
-- Add views tracking to journals
ALTER TABLE `journals` ADD COLUMN `views` int DEFAULT '0' AFTER `downloads`;

-- Add views and article metadata to journal_topics
ALTER TABLE `journal_topics`
  ADD COLUMN `views` int DEFAULT '0' AFTER `downloads`,
  ADD COLUMN `article_author` varchar(255) DEFAULT NULL AFTER `topic`,
  ADD COLUMN `article_summary` text DEFAULT NULL AFTER `article_author`;

-- Create analytics tracking table
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

-- Create settings table
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
```

---

## 2. New File Structure

### Admin Pages (moved to `admin/` folder)
All admin pages have been organized into the `admin/` folder for better organization:

```
admin/
├── add_article.php          # Add new article to a journal
├── add_event.php            # Add new event
├── add_journal.php          # Add new journal with articles
├── add_members.php          # Add new PSA member
├── add_news.php             # Add new news article
├── add_team_member.php      # Add new team member
├── add_user.php             # Add new admin user
├── ckeditor_upload.php      # CKEditor image upload handler
├── dashboard.php            # Admin dashboard
├── edit_article.php         # Edit existing article
├── edit_event.php           # Edit existing event
├── edit_journal.php         # Edit existing journal
├── edit_members.php         # Edit existing member
├── edit_news.php            # Edit existing news
├── edit_team_member.php     # Edit existing team member
├── edit_user.php            # Edit existing user
├── journal_analytics.php    # Detailed journal analytics
├── manage_articles.php      # Manage articles for a journal
├── manage_comments.php      # Manage comments
├── manage_events.php        # Manage events
├── manage_journals.php      # Manage journals (main page)
├── manage_members.php       # Manage PSA members
├── manage_news.php          # Manage news articles
├── manage_subscribers.php   # Manage subscribers
├── manage_team.php          # Manage team members
├── manage_users.php         # Manage admin users
└── [action files]
    ├── approve_comment.php
    ├── delete_comment.php
    ├── delete_event.php
    ├── delete_journal.php
    ├── delete_members.php
    ├── delete_news.php
    ├── delete_team_member.php
    ├── delete_user.php
    ├── download_topic.php
    ├── export_members.php
    ├── generate_slugs.php
    ├── get_news.php
    ├── subscriber_approve.php
    └── subscriber_delete.php
```

### New Include Files
```
includes/
├── admin_sidebar.php        # Unified admin sidebar component
├── db.php                   # Database connection
├── footer.php               # Frontend footer
├── header.php               # Frontend header
├── meta.php                 # Meta tags
└── pdf_helper.php           # PDF upload/compression utilities
```

---

## 3. Key Features Implemented

### 3.1 Journal + Articles Structure
- **Main Journal Title**: Each journal has a title, author, abstract, keywords, and optional full PDF
- **Individual Articles**: Each journal can have multiple articles, each with:
  - Title
  - Author
  - Summary
  - Individual PDF file
  - View and download tracking

### 3.2 Analytics Tracking
- **Views Tracking**: Tracks when users view journals and articles
- **Downloads Tracking**: Tracks when users download journals and articles
- **Detailed Analytics Page**: `admin/journal_analytics.php` shows:
  - Total views and downloads per journal
  - Per-article performance
  - Recent activity log
  - Visual charts

### 3.3 PDF Upload Validation & Compression
- **Size Validation**: Checks PDF file size against configurable limit (default 10MB)
- **Automatic Compression**: Compresses large PDFs using:
  - Imagick (if available)
  - Ghostscript (fallback)
  - Direct copy (last resort)
- **User Feedback**: Shows compression results and savings

### 3.4 Unified Admin Dashboard
- **Consistent Sidebar**: All admin pages now use the same sidebar component
- **Color Scheme**: Black sidebar with green accents (`#117733`)
- **Active State**: Current page is highlighted in the sidebar
- **Responsive**: Works on all screen sizes

### 3.5 Folder Organization
- Admin pages moved to `admin/` folder
- Action files moved to `admin/` folder
- Includes remain in `includes/` folder
- Frontend pages remain in root

---

## 4. How to Use

### 4.1 Running the SQL Migration
1. Open phpMyAdmin or your MySQL client
2. Select the `malawips_2026mpsa` database
3. Import the `migrate_journals.sql` file
4. Verify the new tables and columns exist

### 4.2 Adding a New Journal
1. Go to `admin/manage_journals.php`
2. Click "Add Journal"
3. Fill in the journal information (title, author, abstract, etc.)
4. Optionally upload a full journal PDF
5. Add individual articles with their titles, authors, summaries, and PDFs
6. Click "Save Journal"

### 4.3 Managing Articles
1. Go to `admin/manage_journals.php`
2. Click the "Articles" button next to any journal
3. Add, edit, or delete articles
4. Each article can have its own PDF

### 4.4 Viewing Analytics
1. Go to `admin/manage_journals.php`
2. Click the chart icon next to any journal
3. View detailed analytics including:
   - Views and downloads
   - Article performance
   - Recent activity

### 4.5 PDF Settings
PDF settings can be configured in the `journal_settings` table:
- `max_pdf_size_mb`: Maximum PDF file size (default: 10)
- `pdf_quality`: Compression quality - 'low', 'medium', 'high' (default: 'medium')
- `enable_analytics`: Enable/disable analytics tracking (default: 1)
- `articles_per_page`: Articles per page in listings (default: 10)

---

## 5. Frontend Changes

### 5.1 Journal Listing (`journal.php`)
- Shows main journal title with summary
- Lists individual articles under each journal
- Each article has "View" and "Download" buttons
- Shows view and download counts

### 5.2 Journal Detail (`journal-view.php`)
- Shows full journal information
- Lists all articles with their details
- View and download options for each article
- Shows analytics counts

### 5.3 Analytics Tracking
- Views are tracked when users click "View" on an article
- Downloads are tracked when users click "Download"
- Data is stored in `journal_analytics` table

---

## 6. Admin Dashboard Updates

The dashboard now includes:
- Journal performance overview
- Total articles count
- Total views and downloads
- Quick link to detailed journal analytics

---

## 7. Technical Details

### 7.1 PDF Compression
The system uses multiple methods for PDF compression:
1. **Imagick**: Best quality, requires PHP Imagick extension
2. **Ghostscript**: Good quality, requires Ghostscript installed
3. **Direct Copy**: Fallback when compression tools unavailable

### 7.2 Analytics Schema
```sql
journal_analytics:
- id: Primary key
- journal_id: Reference to journal (nullable)
- article_id: Reference to article (nullable)
- action: 'view' or 'download'
- ip_address: User's IP address
- user_agent: Browser user agent
- created_at: Timestamp of action
```

### 7.3 Helper Functions
Located in `includes/pdf_helper.php`:
- `validatePdfSize()`: Validates PDF file size
- `compressPdf()`: Compresses PDF using Imagick
- `compressPdfWithGhostscript()`: Compresses PDF using Ghostscript
- `handlePdfUpload()`: Complete upload handler with validation and compression
- `trackAnalytics()`: Records view/download action
- `incrementViews()`: Increments view counter
- `incrementDownloads()`: Increments download counter
- `getJournalAnalytics()`: Gets analytics for a specific journal
- `getOverallAnalytics()`: Gets overall site analytics
- `getJournalSetting()`: Gets setting from database

---

## 8. Notes

- All admin pages now use the unified sidebar for consistent styling
- The sidebar uses Tailwind CSS with custom PSA colors
- PDF compression is automatic but may not work on all servers
- Analytics data is stored indefinitely for historical tracking
- The `journal_topics` table now stores article-specific data (author, summary)

---

## 9. Troubleshooting

### PDF Compression Not Working
- Check if Imagick extension is installed: `php -m | grep imagick`
- Check if Ghostscript is installed: `gs --version`
- The system will fall back to direct file copy if compression tools unavailable

### Analytics Not Tracking
- Ensure `journal_analytics` table exists
- Check `journal_settings` table has `enable_analytics` set to '1'
- Verify database connection is working

### Links Not Working After Folder Reorganization
- Ensure all admin pages are in the `admin/` folder
- Check that `includes/admin_sidebar.php` exists
- Verify include paths use `../includes/` prefix

---

## 10. Credits
Developed by Leonard Mhone for PSA Malawi
Version: 2.0
