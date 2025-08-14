-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 14, 2025 at 02:14 PM
-- Server version: 5.7.19
-- PHP Version: 8.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `assets_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE IF NOT EXISTS `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assets_value` decimal(12,2) DEFAULT NULL,
  `assets_lifespan` int(11) NOT NULL COMMENT 'In months. Use category as default lifespan.',
  `attachments_id` int(11) NOT NULL DEFAULT '0',
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_tracking` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = enable asset tracking, 0 = disable asset tracking.',
  `status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available' COMMENT 'available, write_off, loan_out, out_of_stock, maintenance, unavailable. Out of stock is when quantity = 0. This is a cached status. For each write operation, update status.',
  `salvage_value` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Enter salvage value after write off. Default to 0.',
  `supplier_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `maintenance_interval` int(11) DEFAULT NULL COMMENT 'In months. Apply to all items even multiple quantity.',
  `warranty_expiry` date DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `status` (`status`),
  KEY `assets_name` (`assets_name`(191)),
  KEY `enable_tracking` (`enable_tracking`),
  KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets_categories`
--

DROP TABLE IF EXISTS `assets_categories`;
CREATE TABLE IF NOT EXISTS `assets_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `categories_id` (`categories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets_departments`
--

DROP TABLE IF EXISTS `assets_departments`;
CREATE TABLE IF NOT EXISTS `assets_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_id` int(11) NOT NULL,
  `departments_id` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `departments_id` (`departments_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='One asset can have multiple quantity at different department';

-- --------------------------------------------------------

--
-- Table structure for table `assets_departments_loan`
--

DROP TABLE IF EXISTS `assets_departments_loan`;
CREATE TABLE IF NOT EXISTS `assets_departments_loan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_departments_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL COMMENT 'Record created by user.',
  `quantity` int(11) NOT NULL,
  `borrower_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `borrower_entity` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Company/hotel/warehouse/dept',
  `loan_period` int(11) NOT NULL COMMENT 'Number of hours. Expected to return within these hours.',
  `approver_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_departments_id` (`assets_departments_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='indicate loan quantity. Track current loan status only.';

-- --------------------------------------------------------

--
-- Table structure for table `assets_maintenance`
--

DROP TABLE IF EXISTS `assets_maintenance`;
CREATE TABLE IF NOT EXISTS `assets_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `notification_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = pending due date, 1 = sent once for one month before, 2 = sent second time 2 weeks before. Send 2 times per maintenance record. Send only for upcoming maintenance. Ignore past maintenance date. Cron job to create maintenance record at the end of maintenance date.',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `maintenance_date` (`maintenance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets_tracking`
--

DROP TABLE IF EXISTS `assets_tracking`;
CREATE TABLE IF NOT EXISTS `assets_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assets_id` int(11) NOT NULL,
  `departments_id` int(11) NOT NULL,
  `datetime_scanned` datetime NOT NULL,
  `quantity` int(11) NOT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT 'User enter condition information here.',
  `terminal_record_id` int(11) NOT NULL COMMENT 'Record ID of the terminal database.',
  `users_id` int(11) NOT NULL COMMENT 'The user who do scanning. Allow user to upload as selected user.',
  `terminal_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `datetime_scanned` (`datetime_scanned`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Include quantity if more than one. List of tracked asset.';

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL COMMENT 'Owner of attachment.',
  `full_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Including file. max_width, max_height and crop/showall parameters. Two folders, original and resized. Resized stores all custom x and y pixels. local = excluding base_url',
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Representative name to be used for app to show. Just name. When retrieve use ID.',
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 = disable, 1 enable for public viewing, 2 = deleted. Only super admin in access matrix manage attachment can see all. If disable, show default photo. If user delete it, remove all attachments_id to ID = 0 from hashtags, stores and products.',
  `attachment_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'image, video, document, youtube',
  `link_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'local = 1. external = 0. As of now only local file. If external link, would include youtube link only.',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lifespan_default` int(11) NOT NULL DEFAULT '0' COMMENT 'In months. Default lifespan for new asset. Default = 0, no lifespan.',
  `tracking_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = tracking enabled, 0 = tracking disabled.',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'config group name.',
  `config_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human readable key name.',
  `config_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Space using underscore.',
  `config_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SMTP settings and etc.';

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departments_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `functions`
--

DROP TABLE IF EXISTS `functions`;
CREATE TABLE IF NOT EXISTS `functions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `functions_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Function title. Display in access matrix page. Eg. Manage User 禄 Add User ',
  `functions_description` text COLLATE utf8mb4_unicode_ci,
  `icon_class` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'representation string in menu bar. All lower case and exact same name as menu bar.',
  `function_group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Active label indicator. Technical usage.',
  `dependencies_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*' COMMENT '* represents all access only, type = *, department',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `functions_controller`
--

DROP TABLE IF EXISTS `functions_controller`;
CREATE TABLE IF NOT EXISTS `functions_controller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `functions_id` int(11) NOT NULL,
  `uri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_dependencies` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usage for audit trail and type function. add,edit,delete,view,list,download,print. If value is list, in access matrix, no need parameters, default to *',
  `access_control_level` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uri' COMMENT 'Access control level. uri or function.',
  `canonical_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Technical description for audit trail purpose.',
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lang_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_menu` tinyint(1) NOT NULL,
  `menu_order` int(11) NOT NULL DEFAULT '0' COMMENT 'Larger number will be displayed first in menu. Priority based.',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `functions_id` (`functions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = successfully sent, 0 = not sent, -1 = failed to send',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Keep track sent notification and sending status.';

-- --------------------------------------------------------

--
-- Table structure for table `notifications_recipients`
--

DROP TABLE IF EXISTS `notifications_recipients`;
CREATE TABLE IF NOT EXISTS `notifications_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notifications_id` int(11) NOT NULL,
  `recipient_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `notifications_id` (`notifications_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Keep track of actual email. No referencing to recipients table.';

-- --------------------------------------------------------

--
-- Table structure for table `recipients`
--

DROP TABLE IF EXISTS `recipients`;
CREATE TABLE IF NOT EXISTS `recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departments_id` int(11) NOT NULL DEFAULT '0',
  `email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `departments_id` (`departments_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notification recipients list';

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roles_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles_functions`
--

DROP TABLE IF EXISTS `roles_functions`;
CREATE TABLE IF NOT EXISTS `roles_functions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roles_id` int(11) NOT NULL,
  `functions_id` int(11) NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `roles_id` (`roles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles_functions_parameter`
--

DROP TABLE IF EXISTS `roles_functions_parameter`;
CREATE TABLE IF NOT EXISTS `roles_functions_parameter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roles_functions_id` int(11) NOT NULL,
  `parameter` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'if (function/parameter in url) == functions/parameter, then permission granted',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `roles_functions_id` (`roles_functions_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
CREATE TABLE IF NOT EXISTS `transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Copy from loan table.',
  `assets_id` int(11) NOT NULL,
  `transaction_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'loan, return, transfer_department, transfer_location',
  `quantity` int(11) NOT NULL,
  `origin_departments_id` int(11) NOT NULL,
  `origin_departments_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_departments_id` int(11) DEFAULT NULL,
  `destination_departments_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loan_period` int(11) DEFAULT NULL COMMENT 'Number of hours. Expected to return within these hours.',
  `borrower_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `borrower_entity` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loan_datetime` datetime DEFAULT NULL,
  `return_datetime` datetime DEFAULT NULL,
  `assets_departments_loan_id` int(11) DEFAULT NULL COMMENT 'For updating loan when return loaned asset. No need to create separate record for return loan asset. Use existing and update return datetime.',
  `users_id` int(11) NOT NULL COMMENT 'Record created by user.',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `approver_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `assets_departments_loan_id` (`assets_departments_loan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Track transfer depart/location and loan.Log purpose only.';

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `users_password` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MD5 of password',
  `person_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Include in cache file. <user_id>::<api_key>',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = enabled, 0 = disabled. Update cache user file when enable or disable. Regenerate CU file when update user.',
  `web_login_datetime` datetime DEFAULT NULL COMMENT 'Last login datetime',
  `web_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Last login IP',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_2` (`username`),
  KEY `api_key` (`api_key`),
  KEY `username` (`username`,`users_password`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_roles`
--

DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE IF NOT EXISTS `users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `roles_id` int(11) NOT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `roles_id` (`roles_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification`
--

DROP TABLE IF EXISTS `verification`;
CREATE TABLE IF NOT EXISTS `verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `verification_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'reset_password',
  `users_id` int(11) NOT NULL,
  `users_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datetime_expiry` datetime NOT NULL COMMENT 'if expired, ask user reset again. verify no expiry.',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = active, 0 = used or disabled. Change to 0 after being used.',
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `verification_type` (`verification_type`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset purpose';

-- --------------------------------------------------------

--
-- Table structure for table `writeoff`
--

DROP TABLE IF EXISTS `writeoff`;
CREATE TABLE IF NOT EXISTS `writeoff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requester_users_id` int(11) NOT NULL,
  `approver_users_id` int(11) DEFAULT NULL,
  `origin_departments_id` int(11) NOT NULL,
  `origin_departments_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin_location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assets_id` int(11) NOT NULL COMMENT 'Send notification email.',
  `writeoff_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'reduce_quantity, complete_writeoff',
  `quantity` int(11) NOT NULL COMMENT '2 types. Write off specified quantity or completely write off whole asset. If only 1 quantity, ask whether write off whole asset. If write off whole asset, change status of asset to written off. Ask user to enter salvage value when writing off.',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = pending, 1 = approved, -1 = rejected',
  `datetime_approved` datetime DEFAULT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assets_id` (`assets_id`),
  KEY `status` (`status`),
  KEY `origin_departments_id` (`origin_departments_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Writeoff request and log of writing off.';

-- --------------------------------------------------------

--
-- Table structure for table `writeoff_approvers`
--

DROP TABLE IF EXISTS `writeoff_approvers`;
CREATE TABLE IF NOT EXISTS `writeoff_approvers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `writeoff_id` int(11) NOT NULL,
  `users_id` int(11) NOT NULL COMMENT 'Approver',
  `priority` int(11) NOT NULL DEFAULT '100' COMMENT 'Larger number to approve first',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 = pending, 1 = approved, -1 = rejected',
  `datetime_approved` datetime DEFAULT NULL,
  `datetime_created` datetime NOT NULL,
  `timestamp_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `writeoff_id` (`writeoff_id`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;
