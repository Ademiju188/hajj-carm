-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 24, 2025 at 02:35 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hajj_registration_crm`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--
CREATE DATABASE hajj_crm;
CREATE USER 'hajj_crm'@'localhost' IDENTIFIED BY 'Q!W@E#R$T%Y^U&I*O(P)';
GRANT ALL PRIVILEGES ON hajj_crm.* TO 'hajj_crm'@'localhost';
FLUSH PRIVILEGES;
EXIT;

CREATE TABLE `admin_users` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','manager') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@awaisitours.com', '$2y$10$SjI7SeRUn6SeXk6FMl6olOEnGfUafCH5egyFs6ghtwVjo.UrSvnQa', 'System Administrator', 'admin', 1, '2025-12-21 09:55:59', '2025-12-16 18:06:57', '2025-12-21 08:55:59');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `form_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name1` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name3` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `place_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `town` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emergency_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `emergency_country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emergency_relationship` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passport_issue_date` date NOT NULL,
  `passport_expiry_date` date NOT NULL,
  `passport_country_of_issue` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passport_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_id` int NOT NULL,
  `room_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_companions` json DEFAULT NULL,
  `roommates` json DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `max_occupancy` int NOT NULL DEFAULT '4',
  `room_types` json DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `max_occupancy`, `room_types`, `price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Standard', 'Standard package with flexible room options for 2-4 persons', 4, '[\"Double (2)\", \"Triple (3)\", \"Quad (4)\"]', 0.00, 1, '2025-12-16 18:06:57', '2025-12-16 18:06:57'),
(2, 'Premium', 'Premium package with comfortable rooms for 2-3 persons', 3, '[\"Double (2)\", \"Triple (3)\"]', 0.00, 1, '2025-12-16 18:06:57', '2025-12-16 18:06:57'),
(3, 'Luxury', 'Luxury package with exclusive double rooms for 2 persons', 2, '[\"Double (2)\"]', 0.00, 1, '2025-12-16 18:06:57', '2025-12-16 18:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `reports_cache`
--

CREATE TABLE `reports_cache` (
  `id` int NOT NULL,
  `report_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `report_data` json NOT NULL,
  `generated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `setting_type` enum('text','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Hajj Registration CRM', 'text', 'app', 'Application Name', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(2, 'app_url', 'http://localhost/mini-crm', 'text', 'app', 'Application URL', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(3, 'app_timezone', 'Europe/London', 'text', 'app', 'Application Timezone', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(4, 'app_env', 'development', 'text', 'app', 'Application Environment (development/production)', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(5, 'smtp_enabled', '1', 'boolean', 'smtp', 'Enable SMTP Email', '2025-12-16 19:18:58', '2025-12-16 19:20:48'),
(6, 'smtp_host', 'sandbox.smtp.mailtrap.io', 'text', 'smtp', 'SMTP Host', '2025-12-16 19:18:58', '2025-12-16 19:20:48'),
(7, 'smtp_port', '2525', 'number', 'smtp', 'SMTP Port', '2025-12-16 19:18:58', '2025-12-16 19:20:48'),
(8, 'smtp_secure', 'tls', 'text', 'smtp', 'SMTP Security (tls/ssl)', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(9, 'smtp_username', '3625fc91b5bd41', 'text', 'smtp', 'SMTP Username', '2025-12-16 19:18:58', '2025-12-16 19:20:48'),
(10, 'smtp_password', '3dcdadbf24fddf', 'text', 'smtp', 'SMTP Password', '2025-12-16 19:18:58', '2025-12-16 19:20:48'),
(11, 'smtp_from_email', 'awaisitours@gmail.com', 'text', 'smtp', 'SMTP From Email', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(12, 'smtp_from_name', 'Awaisi Tours', 'text', 'smtp', 'SMTP From Name', '2025-12-16 19:18:58', '2025-12-16 19:18:58'),
(13, 'notification_email', 'awaisitours@gmail.com', 'text', 'notification', 'Notification Email Address', '2025-12-16 19:18:58', '2025-12-16 19:18:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_id` (`form_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_mobile` (`mobile`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submitted_at` (`submitted_at`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `reports_cache`
--
ALTER TABLE `reports_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_report_type` (`report_type`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reports_cache`
--
ALTER TABLE `reports_cache`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
