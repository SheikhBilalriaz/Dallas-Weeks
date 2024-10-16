-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 16, 2024 at 09:45 AM
-- Server version: 8.0.31
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

DROP TABLE IF EXISTS `permission`;
CREATE TABLE IF NOT EXISTS `permission` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allow_view_only` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`id`, `name`, `slug`, `allow_view_only`, `created_at`, `updated_at`) VALUES
(1, 'Manage webhooks', 'manage_webhooks', 0, '2024-10-02 13:00:04', '2024-10-02 13:00:04'),
(2, 'Manage other messages', 'manage_other_messages', 1, '2024-10-02 13:00:04', '2024-10-02 13:00:04'),
(3, 'Manage global limits', 'manage_global_limits', 1, '2024-10-02 13:01:20', '2024-10-02 13:01:20'),
(4, 'Manage blacklist', 'manage_blacklist', 1, '2024-10-02 13:01:20', '2024-10-02 13:01:20'),
(5, 'Manage chat', 'manage_chat', 1, '2024-10-02 13:02:17', '2024-10-02 13:02:17'),
(6, 'Delete seat', 'delete_seat', 0, '2024-10-02 13:02:17', '2024-10-02 13:02:17'),
(7, 'Manage campaigns', 'manage_campaigns', 1, '2024-10-02 13:05:58', '2024-10-02 13:05:58'),
(8, 'Cancel subscription', 'cancel_subscription', 0, '2024-10-02 13:05:58', '2024-10-02 13:05:58'),
(9, 'Manage campaign details and reports', 'manage_campaign_details_and_reports', 1, '2024-10-02 13:21:59', '2024-10-02 13:21:59'),
(10, 'Manage seat settings', 'manage_seat_settings', 0, '2024-10-02 13:21:59', '2024-10-02 13:21:59'),
(11, 'Open LinkedIn profile', 'open_linkedin_profile', 0, '2024-10-02 13:23:13', '2024-10-02 13:23:13'),
(12, 'Manage LinkedIn integrations', 'manage_linkedin_integrations', 1, '2024-10-02 13:23:13', '2024-10-02 13:23:13'),
(13, 'Manage account health', 'manage_account_health', 1, '2024-10-02 13:24:29', '2024-10-02 13:24:29'),
(14, 'Manage email settings', 'manage_email_settings', 1, '2024-10-02 13:24:29', '2024-10-02 13:24:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
