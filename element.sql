-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 16, 2024 at 06:15 PM
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
-- Table structure for table `element`
--

DROP TABLE IF EXISTS `element`;
CREATE TABLE IF NOT EXISTS `element` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_conditional` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `element`
--

INSERT INTO `element` (`id`, `name`, `icon`, `slug`, `is_conditional`, `created_at`, `updated_at`) VALUES
(1, 'View Profile', '<i class=\"fa-solid fa-eye\"></i>', 'view_profile', 0, '2024-10-16 18:05:45', '2024-10-16 18:05:45'),
(2, 'Invite to Connect', '<i class=\"fa-solid fa-share-nodes\"></i>', 'invite_to_connect', 0, '2024-10-16 18:05:45', '2024-10-16 18:05:45'),
(3, 'Message', '<i class=\"fa-solid fa-message\"></i>', 'message', 0, '2024-10-16 18:07:37', '2024-10-16 18:07:37'),
(4, 'InMail Message', '<i class=\"fa-solid fa-envelopes-bulk\"></i>', 'inmail_message', 0, '2024-10-16 18:08:11', '2024-10-16 18:08:11'),
(5, 'Follow', '<i class=\"fa-solid fa-user-plus\"></i>', 'follow', 0, '2024-10-16 18:08:11', '2024-10-16 18:08:11'),
(6, 'Email Message', '<i class=\"fa-solid fa-envelope\"></i>', 'email_message', 0, '2024-10-16 18:09:03', '2024-10-16 18:09:03'),
(7, 'Find & verify business email via your source', '<i class=\"fa-solid fa-square-envelope\"></i>', 'find_verify_business_email_via_your_source', 0, '2024-10-16 18:09:03', '2024-10-16 18:09:03'),
(8, 'Find & verify business email via Linkedin', '<i class=\"fa-brands fa-linkedin\"></i>', 'find_verify_business_email_via_linkedin', 0, '2024-10-16 18:10:02', '2024-10-16 18:10:02'),
(12, 'If connected', '<i class=\"fas fa-user-plus\"></i>', 'if_connected', 1, '2024-10-16 18:10:31', '2024-10-16 18:10:31'),
(10, 'If email is opened', '<i class=\"fa-solid fa-comment\"></i>', 'if_email_is_opened', 1, '2024-10-16 18:10:50', '2024-10-09 18:10:50'),
(11, 'If has imported email', '<i class=\"fa-solid fa-at\"></i>', 'if_has_imported_email', 1, '2024-10-16 18:10:50', '2024-10-16 18:10:50'),
(9, 'Custom condition', '<i class=\"fa-brands fa-connectdevelop\"></i>', 'custom_condition', 1, '2024-10-16 18:12:42', '2024-10-16 18:12:42'),
(13, 'If has verified email', '<i class=\"fa-regular fa-envelope-open\"></i>', 'if_has_verified_email', 1, '2024-10-16 18:13:42', '2024-10-16 18:13:42'),
(14, 'If Free InMail', '<i class=\"fa-solid fa-envelope-circle-check\"></i>', 'if_free_inmail', 1, '2024-10-16 18:13:42', '2024-10-16 18:13:42');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
