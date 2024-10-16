-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 06, 2024 at 11:24 AM
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
-- Database: `dallas-weeks`
--

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
CREATE TABLE IF NOT EXISTS `properties` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `element_id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `property_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `optional` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `element_id`, `property_name`, `data_type`, `created_at`, `updated_at`, `optional`) VALUES
(1, '1', 'Days', 'number', '2024-03-01 15:17:17', '2024-03-01 15:17:17', '1'),
(2, '1', 'Hours', 'number', '2024-03-01 15:52:06', '2024-03-01 15:52:06', '1'),
(3, '2', 'Connect Message', 'text', '2024-03-01 18:53:18', '2024-03-01 18:53:18', '1'),
(4, '2', 'Days', 'number', '2024-03-01 18:53:54', '2024-03-01 18:53:54', '1'),
(5, '2', 'Hours', 'number', '2024-03-01 18:54:45', '2024-03-01 18:54:45', '1'),
(6, '3', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(7, '3', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(8, '4', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(9, '4', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(10, '5', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(11, '5', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(12, '6', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(13, '6', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(14, '7', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(15, '7', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(16, '8', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(17, '8', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(23, '11', 'Hours', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(22, '11', 'Days', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(20, '10', 'Days', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(21, '10', 'Hours', 'number', '2024-04-19 14:42:15', '2024-04-19 14:42:15', '1'),
(24, '12', 'Days', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(25, '12', 'Hours', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(26, '13', 'Days', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(27, '13', 'Hours', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(28, '14', 'Days', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(29, '14', 'Hours', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(18, '9', 'Days', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(19, '9', 'Hours', 'number', '2024-04-19 14:54:40', '2024-04-19 14:54:40', '1'),
(30, '3', 'Message', 'text', '2024-06-11 07:03:01', '2024-06-11 07:03:01', '1'),
(31, '6', 'Body', 'text', '2024-06-20 15:26:46', '2024-06-20 15:26:46', '1'),
(32, '6', 'Subject', 'text', '2024-06-20 16:08:37', '2024-06-20 16:08:37', '1');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
