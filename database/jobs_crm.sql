-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 06:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jobs_crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `complete_job_forms`
--

CREATE TABLE `complete_job_forms` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `w9_vendor_business_name` varchar(255) NOT NULL,
  `w9_address` text NOT NULL,
  `w9_ein_ssn` varchar(50) NOT NULL,
  `w9_entity_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `final_request_approvals`
--

CREATE TABLE `final_request_approvals` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `job_vendor_id` int(11) NOT NULL,
  `requested_user_id` int(11) NOT NULL,
  `estimated_amount` decimal(10,2) NOT NULL,
  `visit_date_time` datetime NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('accepted','rejected','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_to` text NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `total_amount` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices_items`
--

CREATE TABLE `invoices_items` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `item` varchar(255) NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `unit_price` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_address`
--

CREATE TABLE `invoice_address` (
  `id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `invc_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_reminders`
--

CREATE TABLE `invoice_reminders` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `payment_request_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `reminder_type` enum('pending','urgent','overdue') DEFAULT 'pending',
  `notification_sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `job_type` varchar(50) NOT NULL,
  `job_detail` text NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `job_sla` datetime DEFAULT NULL,
  `status` enum('added','in_progress','completed') DEFAULT 'added',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_comments`
--

CREATE TABLE `job_comments` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_role` enum('admin','user') NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_completion_attachments`
--

CREATE TABLE `job_completion_attachments` (
  `id` int(11) NOT NULL,
  `job_complete_id` int(11) NOT NULL,
  `attachment_type` enum('pictures','invoices') NOT NULL,
  `attachment_name` varchar(255) NOT NULL,
  `attachment_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_pictures`
--

CREATE TABLE `job_pictures` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `picture_name` varchar(255) NOT NULL,
  `picture_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_timeline`
--

CREATE TABLE `job_timeline` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `event_time` datetime NOT NULL,
  `status` enum('completed','active','pending') DEFAULT 'completed',
  `icon` varchar(50) DEFAULT 'bi-circle',
  `created_by` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores timeline events for jobs to maintain permanent history';

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment` tinyint(1) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_attachments`
--

CREATE TABLE `message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT 'general',
  `color` varchar(20) DEFAULT '#007bff',
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notify_for` enum('admin','user') NOT NULL,
  `type` enum('new_job_added','visit_request','final_visit_request','job_completed','request_vendor_payment','requested_vendor_payment','request_visit_accepted','visit_request_rejected','final_visit_request_accepted','final_visit_request_rejected','vendor_payment_accepted','vendor_payment_rejected','sla_reminder','invoice_reminder','vendor_added','partial_payment_requested','partial_payment_accepted','partial_payment_rejected') NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `action_required` tinyint(1) DEFAULT 0,
  `alert_sent` tinyint(1) DEFAULT 0,
  `alert_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partial_payments`
--

CREATE TABLE `partial_payments` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_amount` decimal(10,2) NOT NULL,
  `final_request_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `screenshot_path` varchar(255) DEFAULT NULL COMMENT 'Path to the screenshot uploaded by admin when approving partial payment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `partial_payment_screenshots`
--

CREATE TABLE `partial_payment_screenshots` (
  `id` int(11) NOT NULL,
  `partial_payment_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `approved_amount` decimal(10,2) NOT NULL,
  `screenshot_path` varchar(500) NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `status` enum('approved','rejected') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_reminders`
--

CREATE TABLE `payment_reminders` (
  `id` int(11) NOT NULL,
  `request_payment_id` int(11) NOT NULL,
  `reminder_type` enum('urgent','warning','overdue','pending') NOT NULL,
  `notification_sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_payments`
--

CREATE TABLE `request_payments` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_platform` enum('payment_link_invoice','zelle') NOT NULL,
  `payment_link_invoice_url` varchar(500) DEFAULT NULL,
  `zelle_email_phone` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sla_reminders`
--

CREATE TABLE `sla_reminders` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reminder_type` enum('urgent','warning') NOT NULL,
  `notification_sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('admin','user','manager') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `added_by` int(11) DEFAULT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `quote_type` enum('free_quote','paid_quote') NOT NULL,
  `quote_amount` decimal(10,2) DEFAULT NULL,
  `vendor_platform` varchar(100) DEFAULT NULL,
  `appointment_date_time` datetime DEFAULT NULL,
  `status` enum('added','visit_requested','visit_request_rejected','final_visit_requested','final_visit_request_rejected','job_completed','requested_vendor_payment','payment_request_rejected','request_visit_accepted','final_visit_request_accepted','vendor_payment_accepted','partial_payment_requested','partial_payment_accepted','partial_payment_rejected') DEFAULT 'added',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complete_job_forms`
--
ALTER TABLE `complete_job_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_complete_job_forms_job_id` (`job_id`),
  ADD KEY `idx_complete_job_forms_user_id` (`user_id`);

--
-- Indexes for table `final_request_approvals`
--
ALTER TABLE `final_request_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_final_request_approvals_requested_user_id` (`requested_user_id`),
  ADD KEY `idx_final_request_approvals_status` (`status`),
  ADD KEY `idx_final_request_approvals_visit_date_time` (`visit_date_time`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_invoices_job_id` (`job_id`);

--
-- Indexes for table `invoices_items`
--
ALTER TABLE `invoices_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_invoice_number` (`invoice_number`);

--
-- Indexes for table `invoice_address`
--
ALTER TABLE `invoice_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invc_id` (`invc_id`);

--
-- Indexes for table `invoice_reminders`
--
ALTER TABLE `invoice_reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_invoice_reminder` (`payment_request_id`,`reminder_type`),
  ADD KEY `idx_invoice_reminders_job_id` (`job_id`),
  ADD KEY `idx_invoice_reminders_payment_request_id` (`payment_request_id`),
  ADD KEY `idx_invoice_reminders_vendor_id` (`vendor_id`),
  ADD KEY `idx_invoice_reminders_reminder_type` (`reminder_type`),
  ADD KEY `idx_invoice_reminders_notification_sent` (`notification_sent`),
  ADD KEY `idx_invoice_reminders_created_at` (`created_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jobs_status` (`status`),
  ADD KEY `idx_jobs_job_type` (`job_type`),
  ADD KEY `idx_jobs_created_at` (`created_at`),
  ADD KEY `idx_jobs_job_sla` (`job_sla`),
  ADD KEY `idx_jobs_assigned_to` (`assigned_to`);

--
-- Indexes for table `job_comments`
--
ALTER TABLE `job_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_comments_job_id` (`job_id`),
  ADD KEY `idx_job_comments_user_id` (`user_id`),
  ADD KEY `idx_job_comments_user_role` (`user_role`),
  ADD KEY `idx_job_comments_created_at` (`created_at`);

--
-- Indexes for table `job_completion_attachments`
--
ALTER TABLE `job_completion_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_completion_attachments_job_complete_id` (`job_complete_id`),
  ADD KEY `idx_job_completion_attachments_attachment_type` (`attachment_type`);

--
-- Indexes for table `job_pictures`
--
ALTER TABLE `job_pictures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_pictures_job_id` (`job_id`);

--
-- Indexes for table `job_timeline`
--
ALTER TABLE `job_timeline`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_timeline` (`job_id`,`event_time`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_event_time` (`event_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_job_id` (`job_id`),
  ADD KEY `idx_messages_sender_id` (`sender_id`),
  ADD KEY `idx_messages_receiver_id` (`receiver_id`),
  ADD KEY `idx_messages_vendor_id` (`vendor_id`),
  ADD KEY `idx_messages_is_read` (`is_read`),
  ADD KEY `idx_messages_attachment` (`attachment`),
  ADD KEY `idx_messages_created_at` (`created_at`),
  ADD KEY `idx_messages_sender_receiver` (`sender_id`,`receiver_id`);

--
-- Indexes for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message_attachments_message_id` (`message_id`),
  ADD KEY `idx_message_attachments_file_type` (`file_type`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notes_user_id` (`user_id`),
  ADD KEY `idx_notes_category` (`category`),
  ADD KEY `idx_notes_pinned` (`is_pinned`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_id` (`user_id`),
  ADD KEY `idx_notifications_notify_for` (`notify_for`),
  ADD KEY `idx_notifications_type` (`type`),
  ADD KEY `idx_notifications_job_id` (`job_id`),
  ADD KEY `idx_notifications_is_read` (`is_read`),
  ADD KEY `idx_notifications_action_required` (`action_required`),
  ADD KEY `idx_notifications_created_at` (`created_at`);

--
-- Indexes for table `partial_payments`
--
ALTER TABLE `partial_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `final_request_id` (`final_request_id`),
  ADD KEY `idx_job_vendor` (`job_id`,`vendor_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_partial_payments_screenshot` (`screenshot_path`);

--
-- Indexes for table `partial_payment_screenshots`
--
ALTER TABLE `partial_payment_screenshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_partial_payment_screenshots` (`partial_payment_id`,`status`);

--
-- Indexes for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_payment_reminder` (`request_payment_id`,`reminder_type`),
  ADD KEY `idx_payment_reminders_request_id` (`request_payment_id`),
  ADD KEY `idx_payment_reminders_reminder_type` (`reminder_type`),
  ADD KEY `idx_payment_reminders_notification_sent` (`notification_sent`),
  ADD KEY `idx_payment_reminders_created_at` (`created_at`);

--
-- Indexes for table `request_payments`
--
ALTER TABLE `request_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_request_payments_job_id` (`job_id`),
  ADD KEY `idx_request_payments_user_id` (`user_id`),
  ADD KEY `idx_request_payments_payment_platform` (`payment_platform`);

--
-- Indexes for table `sla_reminders`
--
ALTER TABLE `sla_reminders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job_reminder` (`job_id`,`reminder_type`),
  ADD KEY `idx_sla_reminders_job_id` (`job_id`),
  ADD KEY `idx_sla_reminders_reminder_type` (`reminder_type`),
  ADD KEY `idx_sla_reminders_notification_sent` (`notification_sent`),
  ADD KEY `idx_sla_reminders_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_created_at` (`created_at`),
  ADD KEY `idx_users_username` (`username`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `fk_vendors_added_by` (`added_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complete_job_forms`
--
ALTER TABLE `complete_job_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `final_request_approvals`
--
ALTER TABLE `final_request_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices_items`
--
ALTER TABLE `invoices_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_address`
--
ALTER TABLE `invoice_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_reminders`
--
ALTER TABLE `invoice_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_comments`
--
ALTER TABLE `job_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_completion_attachments`
--
ALTER TABLE `job_completion_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_pictures`
--
ALTER TABLE `job_pictures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_timeline`
--
ALTER TABLE `job_timeline`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `message_attachments`
--
ALTER TABLE `message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partial_payments`
--
ALTER TABLE `partial_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `partial_payment_screenshots`
--
ALTER TABLE `partial_payment_screenshots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_payments`
--
ALTER TABLE `request_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sla_reminders`
--
ALTER TABLE `sla_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complete_job_forms`
--
ALTER TABLE `complete_job_forms`
  ADD CONSTRAINT `complete_job_forms_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complete_job_forms_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `final_request_approvals`
--
ALTER TABLE `final_request_approvals`
  ADD CONSTRAINT `final_request_approvals_ibfk_1` FOREIGN KEY (`requested_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices_items`
--
ALTER TABLE `invoices_items`
  ADD CONSTRAINT `fk_invoice_number` FOREIGN KEY (`invoice_number`) REFERENCES `invoices` (`invoice_number`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoice_address`
--
ALTER TABLE `invoice_address`
  ADD CONSTRAINT `invoice_address_ibfk_1` FOREIGN KEY (`invc_id`) REFERENCES `invoices` (`id`);

--
-- Constraints for table `invoice_reminders`
--
ALTER TABLE `invoice_reminders`
  ADD CONSTRAINT `fk_invoice_reminders_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_reminders_payment_request_id` FOREIGN KEY (`payment_request_id`) REFERENCES `request_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_invoice_reminders_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_comments`
--
ALTER TABLE `job_comments`
  ADD CONSTRAINT `fk_job_comments_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_job_comments_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_completion_attachments`
--
ALTER TABLE `job_completion_attachments`
  ADD CONSTRAINT `job_completion_attachments_ibfk_1` FOREIGN KEY (`job_complete_id`) REFERENCES `complete_job_forms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_pictures`
--
ALTER TABLE `job_pictures`
  ADD CONSTRAINT `job_pictures_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_timeline`
--
ALTER TABLE `job_timeline`
  ADD CONSTRAINT `job_timeline_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_timeline_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_attachments`
--
ALTER TABLE `message_attachments`
  ADD CONSTRAINT `message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partial_payments`
--
ALTER TABLE `partial_payments`
  ADD CONSTRAINT `partial_payments_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partial_payments_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partial_payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partial_payments_ibfk_4` FOREIGN KEY (`final_request_id`) REFERENCES `final_request_approvals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `partial_payment_screenshots`
--
ALTER TABLE `partial_payment_screenshots`
  ADD CONSTRAINT `partial_payment_screenshots_ibfk_1` FOREIGN KEY (`partial_payment_id`) REFERENCES `partial_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `partial_payment_screenshots_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_reminders`
--
ALTER TABLE `payment_reminders`
  ADD CONSTRAINT `fk_payment_reminders_request_id` FOREIGN KEY (`request_payment_id`) REFERENCES `request_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_payments`
--
ALTER TABLE `request_payments`
  ADD CONSTRAINT `request_payments_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sla_reminders`
--
ALTER TABLE `sla_reminders`
  ADD CONSTRAINT `fk_sla_reminders_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `fk_vendors_added_by` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
