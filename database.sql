-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               MySQL 9.1
-- Server OS:                    Win64
-- Converted for MySQL 9.1 compatibility
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for jujuconnect
CREATE DATABASE IF NOT EXISTS `jujuconnect` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `jujuconnect`;

-- Dumping structure for table jujuconnect.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `AchievementID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `AchievementType` varchar(50) NOT NULL COMMENT 'e.g., FirstRide, EcoWarrior, TopDriver',
  `AchievementName` varchar(100) NOT NULL,
  `AchievementDescription` text DEFAULT NULL,
  `BadgeIcon` varchar(100) DEFAULT NULL COMMENT 'Icon/image filename',
  `PointsAwarded` int(11) DEFAULT 0,
  `EarnedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`AchievementID`),
  KEY `idx_user` (`UserID`),
  KEY `idx_type` (`AchievementType`),
  KEY `idx_earned` (`EarnedAt`),
  CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.achievements: ~3 rows (approximately)
INSERT INTO `achievements` (`AchievementID`, `UserID`, `AchievementType`, `AchievementName`, `AchievementDescription`, `BadgeIcon`, `PointsAwarded`, `EarnedAt`) VALUES
	(1, 2, 'FirstRide', 'First Ride', 'Completed your first ride as a driver', 'badge-first-ride.png', 10, '2026-01-04 07:23:58'),
	(2, 3, 'EcoWarrior', 'Eco Warrior', 'Saved 10kg of CO2 emissions', 'badge-eco-warrior.png', 50, '2026-01-04 07:23:58'),
	(3, 5, 'FirstRide', 'First Ride', 'Completed your first ride as a passenger', 'badge-first-ride.png', 10, '2026-01-04 07:23:58');

-- Dumping structure for table jujuconnect.activitylogs
CREATE TABLE IF NOT EXISTS `activitylogs` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `Action` varchar(100) NOT NULL,
  `EntityType` varchar(50) DEFAULT NULL COMMENT 'e.g., User, Ride, Report',
  `EntityID` int(11) DEFAULT NULL,
  `Details` text DEFAULT NULL,
  `IPAddress` varchar(45) DEFAULT NULL,
  `UserAgent` text DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`),
  KEY `idx_user` (`UserID`),
  KEY `idx_action` (`Action`),
  KEY `idx_created` (`CreatedAt`),
  CONSTRAINT `activitylogs_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.activitylogs: ~193 rows (approximately)
INSERT INTO `activitylogs` (`LogID`, `UserID`, `Action`, `EntityType`, `EntityID`, `Details`, `IPAddress`, `UserAgent`, `CreatedAt`) VALUES
	(1, 9, 'register', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:39:17'),
	(2, 9, 'login', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:39:23'),
	(3, 9, 'request_ride', 'RideRequest', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:40:17'),
	(4, 9, 'update_profile', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:46:21'),
	(5, 9, 'update_profile', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:55:57'),
	(6, 9, 'logout', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:56:13'),
	(7, 10, 'register', 'User', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:57:47'),
	(8, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:58:08'),
	(9, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:58:24'),
	(10, 2, 'login', 'User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:58:32'),
	(11, 2, 'create_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 07:59:28'),
	(12, 2, 'approve_request', 'RideRequest', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:04:32'),
	(13, 2, 'logout', 'User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:07:07'),
	(14, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:07:32'),
	(15, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:10:17'),
	(16, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:15:58'),
	(17, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:21'),
	(18, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:26'),
	(19, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:40'),
	(20, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:47'),
	(21, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:50'),
	(22, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:18:53'),
	(23, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:20:06'),
	(24, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:20:14'),
	(25, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:20:36'),
	(26, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:20:44'),
	(27, 1, 'admin_ban_user', 'User', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:22:20'),
	(28, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:22:25'),
	(29, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:22:40'),
	(30, 1, 'admin_activate_user', 'User', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:22:47'),
	(31, 1, 'admin_suspend_user', 'User', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:22:51'),
	(32, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:23:04'),
	(33, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:25:51'),
	(34, 1, 'admin_activate_user', 'User', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:25:57'),
	(35, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:31:19'),
	(36, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:31:25'),
	(37, 8, 'change_password', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 08:38:16'),
	(38, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:21:51'),
	(39, 2, 'login', 'User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:22:13'),
	(40, 2, 'reject_request', 'RideRequest', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:27:19'),
	(41, 2, 'logout', 'User', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:27:34'),
	(42, 5, 'login', 'User', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:27:44'),
	(43, 5, 'logout', 'User', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:30:48'),
	(44, 3, 'login', 'User', 3, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-04 09:31:04'),
	(45, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 17:38:33'),
	(46, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 17:39:42'),
	(47, 10, 'login', 'User', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 17:39:58'),
	(48, 10, 'logout', 'User', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 18:55:36'),
	(49, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 18:55:56'),
	(50, 8, 'moderator_cancel_ride', 'Ride', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-07 19:03:07'),
	(51, 1, 'login', 'User', 1, NULL, '51.79.161.225', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 01:48:39'),
	(52, 8, 'login', 'User', 8, NULL, '210.19.13.180', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 01:54:09'),
	(53, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:26:50'),
	(54, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:28:43'),
	(55, 11, 'register', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:31:15'),
	(56, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:31:35'),
	(57, 11, 'request_ride', 'RideRequest', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:32:08'),
	(58, 11, 'update_profile', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:32:54'),
	(59, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:34:35'),
	(60, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:34:51'),
	(61, 1, 'admin_ban_user', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:35:32'),
	(62, 1, 'admin_suspend_user', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:35:40'),
	(63, 1, 'admin_activate_user', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:35:45'),
	(64, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:37:52'),
	(65, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:38:05'),
	(66, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:13'),
	(67, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:13'),
	(68, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:38'),
	(69, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:38'),
	(70, 8, 'moderator_flag_ride', 'Ride', 3, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:49'),
	(71, 8, 'moderator_flag_ride', 'Ride', 3, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:40:49'),
	(72, 8, 'moderator_cancel_ride', 'Ride', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:41:08'),
	(73, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:43:17'),
	(74, 8, 'moderator_flag_ride', 'Ride', 5, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:43:17'),
	(75, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:43:21'),
	(76, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:43:40'),
	(77, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:46:48'),
	(78, 1, 'update_settings', 'SystemSettings', 0, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:47:12'),
	(79, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:48:22'),
	(80, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:49:19'),
	(81, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:49:43'),
	(82, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:51:16'),
	(83, 4, 'create_ride', 'Ride', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:52:21'),
	(84, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:05'),
	(85, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:18'),
	(86, 11, 'request_ride', 'RideRequest', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:24'),
	(87, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:27'),
	(88, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:41'),
	(89, 4, 'approve_request', 'RideRequest', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:44'),
	(90, 4, 'cancel_ride', 'Ride', 6, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:53:59'),
	(91, 4, 'create_ride', 'Ride', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:55:13'),
	(92, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:55:28'),
	(93, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:57:17'),
	(94, 11, 'request_ride', 'RideRequest', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:57:26'),
	(95, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:57:39'),
	(96, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:57:55'),
	(97, 4, 'reject_request', 'RideRequest', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:57:57'),
	(98, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:58:01'),
	(99, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:58:18'),
	(100, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:58:47'),
	(101, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:59:08'),
	(102, 4, 'cancel_ride', 'Ride', 7, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:59:14'),
	(103, 4, 'create_ride', 'Ride', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:59:39'),
	(104, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 07:59:53'),
	(105, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:05'),
	(106, 11, 'request_ride', 'RideRequest', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:21'),
	(107, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:32'),
	(108, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:48'),
	(109, 4, 'approve_request', 'RideRequest', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:51'),
	(110, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:00:58'),
	(111, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:01:11'),
	(112, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:01:29'),
	(113, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:02:01'),
	(114, 1, 'admin_ban_user', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:02:31'),
	(115, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:02:49'),
	(116, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:03:26'),
	(117, 8, 'moderator_flag_ride', 'Ride', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:04:20'),
	(118, 8, 'moderator_flag_ride', 'Ride', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:04:20'),
	(119, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:04:27'),
	(120, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:04:38'),
	(121, 4, 'update_profile', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:06:44'),
	(122, 4, 'update_profile', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:06:57'),
	(123, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:07:22'),
	(124, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:07:39'),
	(125, 8, 'request_ride', 'RideRequest', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:09:50'),
	(126, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:09:54'),
	(127, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:10:35'),
	(128, 4, 'cancel_ride', 'Ride', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:11:04'),
	(129, 4, 'reject_request', 'RideRequest', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:11:14'),
	(130, 4, 'create_ride', 'Ride', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:11:46'),
	(131, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:11:51'),
	(132, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:20'),
	(133, 1, 'admin_activate_user', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:27'),
	(134, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:29'),
	(135, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:41'),
	(136, 11, 'request_ride', 'RideRequest', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:47'),
	(137, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:12:50'),
	(138, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:13:01'),
	(139, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:13:59'),
	(140, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:14:14'),
	(141, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:14:31'),
	(142, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:14:42'),
	(143, 4, 'reject_request', 'RideRequest', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:14:44'),
	(144, 4, 'cancel_ride', 'Ride', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:14:49'),
	(145, 4, 'create_ride', 'Ride', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:15:19'),
	(146, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:15:28'),
	(147, 11, 'login', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:15:40'),
	(148, 11, 'request_ride', 'RideRequest', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:15:49'),
	(149, 11, 'logout', 'User', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:15:52'),
	(150, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:16:02'),
	(151, 4, 'reject_request', 'RideRequest', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 08:17:28'),
	(152, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 16:56:51'),
	(153, 9, 'login', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 16:59:37'),
	(154, 9, 'logout', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 16:59:52'),
	(155, 9, 'login', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:00:06'),
	(156, 9, 'change_password', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:01:48'),
	(157, 9, 'logout', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:02:02'),
	(158, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:07:12'),
	(159, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:14:31'),
	(160, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:14:37'),
	(161, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:25:46'),
	(162, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:25:56'),
	(163, 8, 'moderator_flag_ride', 'Ride', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:26:30'),
	(164, 8, 'moderator_flag_ride', 'Ride', 10, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:26:30'),
	(165, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:26:35'),
	(166, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:26:48'),
	(167, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:32:07'),
	(168, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:32:34'),
	(169, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-08 17:45:36'),
	(170, 8, 'login', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-08 17:45:48'),
	(171, 8, 'moderator_resolve_report', 'Report', 3, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-08 17:46:44'),
	(172, 8, 'logout', 'User', 8, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-08 17:46:50'),
	(173, 1, 'login', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2026-01-08 17:47:00'),
	(174, 1, 'admin_resolve_report', 'Report', 2, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:48:17'),
	(175, 1, 'logout', 'User', 1, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:49:34'),
	(176, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 17:55:57'),
	(177, 4, 'create_ride', 'Ride', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:02:03'),
	(178, 4, 'earn_eco_points', 'User', 4, 'Completed ride as driver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:08'),
	(179, 4, 'complete_ride', 'Ride', 11, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:08'),
	(180, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:31'),
	(181, 9, 'login', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:45'),
	(182, 9, 'request_ride', 'RideRequest', 12, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:55'),
	(183, 9, 'logout', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:04:58'),
	(184, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:05:07'),
	(185, 4, 'approve_request', 'RideRequest', 12, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:05:13'),
	(186, 4, 'earn_eco_points', 'User', 4, 'Completed ride as driver', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:05:33'),
	(187, 9, 'earn_eco_points', 'User', 9, 'Completed ride as passenger', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:05:33'),
	(188, 4, 'submit_rating', 'Rating', 26, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:06:59'),
	(189, 4, 'logout', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:07:03'),
	(190, 9, 'login', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:07:11'),
	(191, 9, 'submit_rating', 'Rating', 27, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:12:25'),
	(192, 9, 'logout', 'User', 9, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:21:50'),
	(193, 4, 'login', 'User', 4, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-08 18:22:05');

-- Dumping structure for table jujuconnect.blockedusers
CREATE TABLE IF NOT EXISTS `blockedusers` (
  `BlockID` int(11) NOT NULL AUTO_INCREMENT,
  `BlockerID` int(11) NOT NULL COMMENT 'User who blocked',
  `BlockedID` int(11) NOT NULL COMMENT 'User who was blocked',
  `Reason` text DEFAULT NULL,
  `BlockedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`BlockID`),
  UNIQUE KEY `unique_block` (`BlockerID`,`BlockedID`),
  KEY `idx_blocker` (`BlockerID`),
  KEY `idx_blocked` (`BlockedID`),
  CONSTRAINT `blockedusers_ibfk_1` FOREIGN KEY (`BlockerID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `blockedusers_ibfk_2` FOREIGN KEY (`BlockedID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.blockedusers: ~0 rows (approximately)

-- Dumping structure for table jujuconnect.emergencycontacts
CREATE TABLE IF NOT EXISTS `emergencycontacts` (
  `ContactID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `ContactName` varchar(180) NOT NULL,
  `ContactPhone` varchar(20) NOT NULL,
  `ContactEmail` varchar(180) DEFAULT NULL,
  `Relationship` varchar(50) DEFAULT NULL,
  `IsPrimary` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ContactID`),
  KEY `idx_user` (`UserID`),
  CONSTRAINT `emergencycontacts_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.emergencycontacts: ~0 rows (approximately)

-- Dumping structure for table jujuconnect.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `NotificationID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Type` enum('Ride Request','Request Approved','Request Rejected','Ride Cancelled','New Rating','Achievement','System') NOT NULL,
  `Title` varchar(255) NOT NULL,
  `Message` text NOT NULL,
  `RelatedID` int(11) DEFAULT NULL COMMENT 'ID of related entity (RideID, RequestID, etc.)',
  `IsRead` tinyint(1) DEFAULT 0,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationID`),
  KEY `idx_user` (`UserID`),
  KEY `idx_read` (`IsRead`),
  KEY `idx_created` (`CreatedAt`),
  KEY `idx_notifications_user_read` (`UserID`,`IsRead`,`CreatedAt`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.notifications: ~26 rows (approximately)
INSERT INTO `notifications` (`NotificationID`, `UserID`, `Type`, `Title`, `Message`, `RelatedID`, `IsRead`, `CreatedAt`) VALUES
	(1, 2, 'Ride Request', 'New Ride Request', 'Alice Passenger has requested to join your ride.', 1, 1, '2026-01-04 07:23:58'),
	(2, 2, 'Ride Request', 'New Ride Request', 'Bob Travel has requested to join your ride.', 2, 1, '2026-01-04 07:23:58'),
	(3, 3, 'Ride Request', 'New Ride Request', 'Carol Ride has requested to join your ride.', 3, 0, '2026-01-04 07:23:58'),
	(4, 3, 'Ride Request', 'New Ride Request', 'JuJu has requested to join your ride.', 4, 0, '2026-01-04 07:40:17'),
	(6, 6, 'Request Rejected', 'Ride Request Not Approved', 'Unfortunately, your request for the ride from KL Sentral, Kuala Lumpur to Asia Pacific University (APU), Technology Park Malaysia was not approved.', 1, 0, '2026-01-04 09:27:19'),
	(7, 2, 'Ride Cancelled', 'Your ride has been cancelled by a moderator', 'Reason: Test', 1, 0, '2026-01-07 19:03:07'),
	(8, 2, 'Ride Request', 'New Ride Request', 'julianbmw has requested to join your ride.', 5, 0, '2026-01-08 07:32:08'),
	(9, 3, 'Ride Cancelled', 'Your ride has been cancelled by a moderator', 'Reason: 123', 2, 0, '2026-01-08 07:41:08'),
	(10, 4, 'Ride Request', 'New Ride Request', 'julianbmw123 has requested to join your ride.', 6, 0, '2026-01-08 07:53:24'),
	(11, 11, 'Request Approved', 'Ride Request Approved!', 'Your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia has been approved!', 6, 1, '2026-01-08 07:53:44'),
	(12, 11, 'Ride Cancelled', 'Ride Cancelled by Driver', 'The ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia has been cancelled by the driver.', 6, 1, '2026-01-08 07:53:59'),
	(13, 4, 'Ride Request', 'New Ride Request', 'julianbmw123 has requested to join your ride.', 7, 0, '2026-01-08 07:57:26'),
	(14, 11, 'Request Rejected', 'Ride Request Not Approved', 'Unfortunately, your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia was not approved.', 7, 1, '2026-01-08 07:57:57'),
	(15, 4, 'Ride Request', 'New Ride Request', 'julianbmw123 has requested to join your ride.', 8, 0, '2026-01-08 08:00:21'),
	(16, 11, 'Request Approved', 'Ride Request Approved!', 'Your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia has been approved!', 8, 0, '2026-01-08 08:00:51'),
	(17, 4, 'Ride Request', 'New Ride Request', 'Tom Moderator has requested to join your ride.', 9, 0, '2026-01-08 08:09:50'),
	(18, 11, 'Ride Cancelled', 'Ride Cancelled by Driver', 'The ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia has been cancelled by the driver.', 8, 0, '2026-01-08 08:11:04'),
	(19, 8, 'Request Rejected', 'Ride Request Not Approved', 'Unfortunately, your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia was not approved.', 8, 1, '2026-01-08 08:11:14'),
	(20, 4, 'Ride Request', 'New Ride Request', 'julianbmw123 has requested to join your ride.', 10, 0, '2026-01-08 08:12:47'),
	(21, 11, 'Request Rejected', 'Ride Request Not Approved', 'Unfortunately, your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia was not approved.', 9, 0, '2026-01-08 08:14:44'),
	(22, 4, 'Ride Request', 'New Ride Request', 'julianbmw123 has requested to join your ride.', 11, 0, '2026-01-08 08:15:49'),
	(23, 11, 'Request Rejected', 'Ride Request Not Approved', 'Unfortunately, your request for the ride from Cheras, Kuala Lumpur, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia was not approved.', 10, 0, '2026-01-08 08:17:28'),
	(24, 4, 'Ride Request', 'New Ride Request', 'JuJu has requested to join your ride.', 12, 0, '2026-01-08 18:04:55'),
	(25, 9, 'Request Approved', 'Ride Request Approved!', 'Your request for the ride from Jalan Mas 10, Taman Lagenda Mas, Cheras, Kajang, Hulu Langat, Selangor, 56000, Malaysia to Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia has been approved!', 11, 1, '2026-01-08 18:05:13'),
	(26, 9, 'New Rating', 'You received a new rating!', 'You have been rated 5 stars for the ride.', 11, 1, '2026-01-08 18:06:59'),
	(27, 4, 'New Rating', 'You received a new rating!', 'You have been rated 5 stars for the ride.', 11, 0, '2026-01-08 18:12:25');

-- Dumping structure for table jujuconnect.ratings
CREATE TABLE IF NOT EXISTS `ratings` (
  `RatingID` int(11) NOT NULL AUTO_INCREMENT,
  `RideID` int(11) NOT NULL,
  `RaterID` int(11) NOT NULL COMMENT 'User who gave the rating',
  `RatedUserID` int(11) NOT NULL COMMENT 'User who received the rating',
  `Rating` int(11) NOT NULL CHECK (`Rating` between 1 and 5),
  `Review` text DEFAULT NULL,
  `RatingType` enum('Driver','Passenger') NOT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`RatingID`),
  UNIQUE KEY `unique_rating` (`RideID`,`RaterID`,`RatedUserID`),
  KEY `RaterID` (`RaterID`),
  KEY `idx_rated_user` (`RatedUserID`),
  KEY `idx_rating_type` (`RatingType`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`RideID`) REFERENCES `rides` (`RideID`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`RaterID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_3` FOREIGN KEY (`RatedUserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.ratings: ~2 rows (approximately)
INSERT INTO `ratings` (`RatingID`, `RideID`, `RaterID`, `RatedUserID`, `Rating`, `Review`, `RatingType`, `CreatedAt`) VALUES
	(1, 11, 4, 9, 5, 'shit', 'Driver', '2026-01-08 18:06:59'),
	(2, 11, 9, 4, 5, 'test', 'Driver', '2026-01-08 18:12:25');

-- Dumping structure for table jujuconnect.reports
CREATE TABLE IF NOT EXISTS `reports` (
  `ReportID` int(11) NOT NULL AUTO_INCREMENT,
  `ReporterID` int(11) NOT NULL,
  `ReportedUserID` int(11) NOT NULL,
  `RideID` int(11) DEFAULT NULL COMMENT 'Optional: if report is about a specific ride',
  `ReportType` enum('Inappropriate Behavior','Safety Concern','Fraud','Spam','Other') NOT NULL,
  `Reason` text NOT NULL,
  `Evidence` text DEFAULT NULL COMMENT 'Additional evidence or details',
  `Status` enum('Pending','Under Review','Resolved','Dismissed') DEFAULT 'Pending',
  `AdminNotes` text DEFAULT NULL,
  `ReviewedBy` int(11) DEFAULT NULL COMMENT 'Admin or Moderator who reviewed',
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ResolvedAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ReportID`),
  KEY `RideID` (`RideID`),
  KEY `ReviewedBy` (`ReviewedBy`),
  KEY `idx_reporter` (`ReporterID`),
  KEY `idx_reported` (`ReportedUserID`),
  KEY `idx_status` (`Status`),
  KEY `idx_created` (`CreatedAt`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`ReporterID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`ReportedUserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`RideID`) REFERENCES `rides` (`RideID`) ON DELETE SET NULL,
  CONSTRAINT `reports_ibfk_4` FOREIGN KEY (`ReviewedBy`) REFERENCES `users` (`UserID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.reports: ~3 rows (approximately)
INSERT INTO `reports` (`ReportID`, `ReporterID`, `ReportedUserID`, `RideID`, `ReportType`, `Reason`, `Evidence`, `Status`, `AdminNotes`, `ReviewedBy`, `CreatedAt`, `ResolvedAt`) VALUES
	(1, 8, 4, 10, 'Other', 'Moderator flagged ride for review: test123', NULL, 'Under Review', NULL, NULL, '2026-01-08 17:32:41', NULL),
	(2, 8, 2, 5, 'Other', 'Moderator flagged ride for review: test123', NULL, 'Resolved', NULL, NULL, '2026-01-08 17:43:49', '2026-01-08 17:48:17'),
	(3, 8, 4, 10, 'Other', 'Moderator flagged ride for review: test123', NULL, 'Resolved', NULL, NULL, '2026-01-08 17:44:03', '2026-01-08 17:46:44');

-- Dumping structure for table jujuconnect.ridehistory
CREATE TABLE IF NOT EXISTS `ridehistory` (
  `HistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `RideID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `Role` enum('Driver','Passenger') NOT NULL,
  `SeatsUsed` int(11) DEFAULT 1,
  `EcoPointsEarned` int(11) DEFAULT 0,
  `CO2Saved` decimal(10,2) DEFAULT 0.00 COMMENT 'CO2 saved in kg',
  `CompletedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`HistoryID`),
  KEY `idx_ride` (`RideID`),
  KEY `idx_user` (`UserID`),
  KEY `idx_role` (`Role`),
  KEY `idx_completed` (`CompletedAt`),
  CONSTRAINT `ridehistory_ibfk_1` FOREIGN KEY (`RideID`) REFERENCES `rides` (`RideID`) ON DELETE CASCADE,
  CONSTRAINT `ridehistory_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.ridehistory: ~0 rows (approximately)

-- Dumping structure for table jujuconnect.riderequests
CREATE TABLE IF NOT EXISTS `riderequests` (
  `RequestID` int(11) NOT NULL AUTO_INCREMENT,
  `RideID` int(11) NOT NULL,
  `PassengerID` int(11) NOT NULL,
  `SeatsRequested` int(11) NOT NULL DEFAULT 1 CHECK (`SeatsRequested` > 0),
  `PickupPoint` varchar(255) DEFAULT NULL COMMENT 'Specific pickup location if different from origin',
  `DropoffPoint` varchar(255) DEFAULT NULL COMMENT 'Specific dropoff location if different from destination',
  `Message` text DEFAULT NULL COMMENT 'Message to driver',
  `Status` enum('Pending','Approved','Rejected','Cancelled','Completed') DEFAULT 'Pending',
  `RequestedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `RespondedAt` timestamp NULL DEFAULT NULL,
  `ResponseMessage` text DEFAULT NULL COMMENT 'Driver response message',
  PRIMARY KEY (`RequestID`),
  UNIQUE KEY `unique_request` (`RideID`,`PassengerID`),
  KEY `idx_ride` (`RideID`),
  KEY `idx_passenger` (`PassengerID`),
  KEY `idx_status` (`Status`),
  KEY `idx_ride_requests_user_status` (`PassengerID`,`Status`),
  CONSTRAINT `riderequests_ibfk_1` FOREIGN KEY (`RideID`) REFERENCES `rides` (`RideID`) ON DELETE CASCADE,
  CONSTRAINT `riderequests_ibfk_2` FOREIGN KEY (`PassengerID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.riderequests: ~12 rows (approximately)
INSERT INTO `riderequests` (`RequestID`, `RideID`, `PassengerID`, `SeatsRequested`, `PickupPoint`, `DropoffPoint`, `Message`, `Status`, `RequestedAt`, `RespondedAt`, `ResponseMessage`) VALUES
	(1, 1, 5, 1, NULL, NULL, 'Hi! I would like to join your ride. Thanks!', 'Approved', '2026-01-04 07:23:58', '2026-01-04 08:04:32', NULL),
	(2, 1, 6, 1, NULL, NULL, 'Can I join? I am at KL Sentral too.', 'Rejected', '2026-01-04 07:23:58', '2026-01-04 09:27:19', NULL),
	(3, 2, 7, 1, NULL, NULL, 'I am near Subang Jaya. Would love to carpool!', 'Approved', '2026-01-04 07:23:58', NULL, NULL),
	(4, 2, 9, 1, NULL, NULL, 'test', 'Pending', '2026-01-04 07:40:17', NULL, NULL),
	(5, 5, 11, 1, NULL, NULL, '', 'Pending', '2026-01-08 07:32:08', NULL, NULL),
	(6, 6, 11, 1, NULL, NULL, '', 'Approved', '2026-01-08 07:53:24', '2026-01-08 07:53:44', NULL),
	(7, 7, 11, 1, NULL, NULL, '', 'Rejected', '2026-01-08 07:57:26', '2026-01-08 07:57:57', NULL),
	(8, 8, 11, 1, NULL, NULL, '', 'Approved', '2026-01-08 08:00:21', '2026-01-08 08:00:51', NULL),
	(9, 8, 8, 1, NULL, NULL, '', 'Rejected', '2026-01-08 08:09:50', '2026-01-08 08:11:14', NULL),
	(10, 9, 11, 1, NULL, NULL, '', 'Rejected', '2026-01-08 08:12:47', '2026-01-08 08:14:44', NULL),
	(11, 10, 11, 2, NULL, NULL, '', 'Rejected', '2026-01-08 08:15:49', '2026-01-08 08:17:28', NULL),
	(12, 11, 9, 1, NULL, NULL, '', 'Approved', '2026-01-08 18:04:55', '2026-01-08 18:05:13', NULL);

-- Dumping structure for table jujuconnect.rides
CREATE TABLE IF NOT EXISTS `rides` (
  `RideID` int(11) NOT NULL AUTO_INCREMENT,
  `DriverID` int(11) NOT NULL,
  `OriginLocation` varchar(255) NOT NULL,
  `DestinationLocation` varchar(255) NOT NULL,
  `DepartureDate` date NOT NULL,
  `DepartureTime` time NOT NULL,
  `AvailableSeats` int(11) NOT NULL CHECK (`AvailableSeats` > 0),
  `TotalSeats` int(11) NOT NULL,
  `PricePerSeat` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Distance` decimal(10,2) DEFAULT NULL COMMENT 'Distance in kilometers',
  `VehicleType` varchar(50) DEFAULT NULL COMMENT 'Car model/type',
  `VehiclePlateNumber` varchar(20) DEFAULT NULL,
  `Notes` text DEFAULT NULL COMMENT 'Additional ride information',
  `Status` enum('Available','Full','Completed','Cancelled','In Progress') DEFAULT 'Available',
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `CompletedAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`RideID`),
  KEY `idx_driver` (`DriverID`),
  KEY `idx_date` (`DepartureDate`),
  KEY `idx_status` (`Status`),
  KEY `idx_locations` (`OriginLocation`(100),`DestinationLocation`(100)),
  KEY `idx_search` (`DepartureDate`,`Status`,`AvailableSeats`),
  KEY `idx_rides_search` (`DepartureDate`,`Status`,`AvailableSeats`,`OriginLocation`(50)),
  CONSTRAINT `rides_ibfk_1` FOREIGN KEY (`DriverID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.rides: ~11 rows (approximately)
INSERT INTO `rides` (`RideID`, `DriverID`, `OriginLocation`, `DestinationLocation`, `DepartureDate`, `DepartureTime`, `AvailableSeats`, `TotalSeats`, `PricePerSeat`, `Distance`, `VehicleType`, `VehiclePlateNumber`, `Notes`, `Status`, `CreatedAt`, `UpdatedAt`, `CompletedAt`) VALUES
	(1, 2, 'KL Sentral, Kuala Lumpur', 'Asia Pacific University (APU), Technology Park Malaysia', '2026-01-06', '08:00:00', 1, 3, 5.00, 15.50, 'Honda Civic', 'ABC 1234', 'Air-conditioned car, punctual driver', 'Cancelled', '2026-01-04 07:23:58', '2026-01-07 19:03:07', NULL),
	(2, 3, 'Subang Jaya, Selangor', 'Asia Pacific University (APU), Technology Park Malaysia', '2026-01-06', '07:30:00', 2, 2, 3.00, 8.00, 'Toyota Vios', 'DEF 5678', 'Morning commute, prefer non-smokers', 'Cancelled', '2026-01-04 07:23:58', '2026-01-08 07:41:08', NULL),
	(3, 4, 'Petaling Jaya, Selangor', 'Asia Pacific University (APU), Technology Park Malaysia', '2026-01-06', '08:15:00', 1, 3, 4.00, 12.00, 'Perodua Myvi', 'GHI 9012', 'Student-friendly rates', 'Available', '2026-01-04 07:23:58', '2026-01-04 07:23:58', NULL),
	(4, 2, 'Bangsar, Kuala Lumpur', 'Asia Pacific University (APU), Technology Park Malaysia', '2026-01-07', '08:00:00', 3, 3, 6.00, 18.00, 'Honda Civic', 'ABC 1234', 'Regular morning ride', 'Available', '2026-01-04 07:23:58', '2026-01-04 07:23:58', NULL),
	(5, 2, 'MRT Taman Maluri', 'APU', '2026-01-16', '18:03:00', 3, 3, 5.00, 15.50, 'Nissan Serena', 'APU 1234', '', 'Available', '2026-01-04 07:59:28', '2026-01-04 07:59:28', NULL),
	(6, 4, 'Cheras, Kuala Lumpur, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-08', '16:52:00', 1, 3, 5.00, 7.20, 'BMW', 'ABC 1', '123', 'Cancelled', '2026-01-08 07:52:21', '2026-01-08 07:53:59', NULL),
	(7, 4, 'Cheras, Kuala Lumpur, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-08', '16:55:00', 3, 3, 5.00, 7.20, 'City', 'ABC1', '', 'Cancelled', '2026-01-08 07:55:13', '2026-01-08 07:59:14', NULL),
	(8, 4, 'Cheras, Kuala Lumpur, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-08', '16:59:00', 1, 3, 5.00, 7.20, 'Civic', 'A 1', '', 'Cancelled', '2026-01-08 07:59:39', '2026-01-08 08:11:04', NULL),
	(9, 4, 'Cheras, Kuala Lumpur, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-08', '17:11:00', 1, 1, 5.00, 7.20, 'Honda City', 'ABCX1', '', 'Cancelled', '2026-01-08 08:11:46', '2026-01-08 08:14:49', NULL),
	(10, 4, 'Cheras, Kuala Lumpur, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-08', '17:15:00', 2, 2, 5.00, 7.20, 'Honda City', 'ABC  1', '', 'Available', '2026-01-08 08:15:19', '2026-01-08 08:15:19', NULL),
	(11, 4, 'Jalan Mas 10, Taman Lagenda Mas, Cheras, Kajang, Hulu Langat, Selangor, 56000, Malaysia', 'Asia Pacific University of Technology and Innovation (APU) - Faculty of Business &amp; Management (FBM), Sungai Besi Expressway, Seri Kembangan, Subang Jaya, Petaling, Selangor, 43300, Malaysia', '2026-01-09', '10:00:00', 1, 3, 5.00, 6.40, 'Perodua Myvi', 'TTT 1234', '', 'Completed', '2026-01-08 18:02:03', '2026-01-08 18:05:33', NULL);

-- Dumping structure for procedure jujuconnect.sp_CompleteRide
DELIMITER //
CREATE PROCEDURE `sp_CompleteRide`(IN p_RideID INT)
BEGIN
    UPDATE Rides
    SET Status = 'Completed',
        CompletedAt = NOW()
    WHERE RideID = p_RideID;
    
    UPDATE RideRequests
    SET Status = 'Completed'
    WHERE RideID = p_RideID AND Status = 'Approved';
END//
DELIMITER ;

-- Dumping structure for procedure jujuconnect.sp_GetSustainabilityStats
DELIMITER //
CREATE PROCEDURE `sp_GetSustainabilityStats`()
BEGIN
    SELECT 
        COUNT(DISTINCT UserID) AS TotalUsers,
        COUNT(DISTINCT CASE WHEN Role = 'Driver' THEN UserID END) AS TotalDrivers,
        COUNT(DISTINCT CASE WHEN Role = 'Passenger' THEN UserID END) AS TotalPassengers,
        SUM(EcoPoints) AS TotalEcoPoints
    FROM Users
    WHERE Status = 'Active';
    
    SELECT 
        COUNT(*) AS TotalRides,
        COUNT(CASE WHEN Status = 'Completed' THEN 1 END) AS CompletedRides,
        SUM(CASE WHEN Status = 'Completed' THEN (TotalSeats - AvailableSeats) END) AS TotalPassengerTrips
    FROM Rides;
    
    SELECT 
        SUM(CO2Saved) AS TotalCO2Saved,
        SUM(EcoPointsEarned) AS TotalEcoPointsEarned
    FROM RideHistory;
END//
DELIMITER ;

-- Dumping structure for table jujuconnect.systemsettings
CREATE TABLE IF NOT EXISTS `systemsettings` (
  `SettingID` int(11) NOT NULL AUTO_INCREMENT,
  `SettingKey` varchar(100) NOT NULL,
  `SettingValue` text DEFAULT NULL,
  `SettingType` varchar(50) DEFAULT 'string',
  `Description` text DEFAULT NULL,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SettingID`),
  UNIQUE KEY `SettingKey` (`SettingKey`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.systemsettings: ~19 rows (approximately)
INSERT INTO `systemsettings` (`SettingID`, `SettingKey`, `SettingValue`, `SettingType`, `Description`, `UpdatedAt`) VALUES
	(1, 'site_name', 'JuJuConnect', 'string', 'Application name', '2026-01-04 07:23:58'),
	(2, 'eco_points_per_km', '0.5', 'decimal', 'Eco points multiplier per kilometer', '2026-01-04 07:23:58'),
	(3, 'co2_per_km', '0.12', 'decimal', 'CO2 saved in kg per km per passenger', '2026-01-04 07:23:58'),
	(4, 'base_eco_points', '10', 'integer', 'Base eco points for completing a ride', '2026-01-04 07:23:58'),
	(5, 'passenger_bonus_points', '5', 'integer', 'Bonus points per passenger for driver', '2026-01-04 07:23:58'),
	(6, 'max_seats_per_ride', '6', 'integer', 'Maximum seats allowed per ride', '2026-01-08 07:47:12'),
	(7, 'min_rating_to_drive', '3.0', 'decimal', 'Minimum rating required to offer rides', '2026-01-04 07:23:58'),
	(8, 'report_threshold', '3', 'integer', 'Number of reports before auto-suspension', '2026-01-04 07:23:58'),
	(9, 'maintenance_mode', '1', 'boolean', 'Site maintenance mode', '2026-01-04 08:18:21'),
	(10, 'registration_enabled', '1', 'boolean', 'Allow new user registrations', '2026-01-04 07:23:58'),
	(12, 'site_description', '', 'string', NULL, '2026-01-08 07:47:12'),
	(13, 'contact_email', '', 'string', NULL, '2026-01-08 07:47:12'),
	(14, 'base_points_per_ride', '10', 'string', NULL, '2026-01-08 07:47:12'),
	(15, 'points_per_km', '1', 'string', NULL, '2026-01-08 07:47:12'),
	(16, 'driver_bonus_per_passenger', '6', 'string', NULL, '2026-01-08 07:46:48'),
	(18, 'min_price_per_seat', '1', 'string', NULL, '2026-01-08 07:47:12'),
	(19, 'max_price_per_seat', '100', 'string', NULL, '2026-01-08 07:47:12'),
	(20, 'enable_registration', '1', 'string', NULL, '2026-01-04 08:18:21'),
	(21, 'enable_notifications', '1', 'string', NULL, '2026-01-04 08:18:21');

-- Dumping structure for table jujuconnect.users
CREATE TABLE IF NOT EXISTS `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(180) NOT NULL,
  `Email` varchar(180) NOT NULL,
  `Password` varchar(255) NOT NULL COMMENT 'Hashed password using bcrypt/argon2',
  `Phone` varchar(20) DEFAULT NULL,
  `Role` enum('Driver','Passenger','Admin','Moderator') DEFAULT 'Passenger',
  `ProfilePicture` varchar(255) DEFAULT 'default-avatar.png',
  `Bio` text DEFAULT NULL,
  `EcoPoints` int(11) DEFAULT 0,
  `Rating` decimal(3,2) DEFAULT 0.00 COMMENT 'Average rating out of 5.00',
  `TotalRatings` int(11) DEFAULT 0,
  `Status` enum('Active','Suspended','Banned','Pending') DEFAULT 'Active',
  `EmailVerified` tinyint(1) DEFAULT 0,
  `VerificationToken` varchar(100) DEFAULT NULL,
  `ResetToken` varchar(100) DEFAULT NULL,
  `ResetTokenExpiry` datetime DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastLogin` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_email` (`Email`),
  KEY `idx_role` (`Role`),
  KEY `idx_status` (`Status`),
  KEY `idx_eco_points` (`EcoPoints`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jujuconnect.users: ~11 rows (approximately)
INSERT INTO `users` (`UserID`, `FullName`, `Email`, `Password`, `Phone`, `Role`, `ProfilePicture`, `Bio`, `EcoPoints`, `Rating`, `TotalRatings`, `Status`, `EmailVerified`, `VerificationToken`, `ResetToken`, `ResetTokenExpiry`, `CreatedAt`, `UpdatedAt`, `LastLogin`) VALUES
	(1, 'System Administrator', 'admin@jujuconnect.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456789', 'Admin', 'default-avatar.png', NULL, 0, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-08 17:47:00', '2026-01-08 17:47:00'),
	(2, 'John Driver', 'john.driver@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456001', 'Driver', 'default-avatar.png', 'Experienced driver, love carpooling!', 150, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-04 09:22:13', '2026-01-04 09:22:13'),
	(3, 'Sarah Green', 'sarah.green@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456002', 'Driver', 'default-avatar.png', 'Eco-friendly commuter', 220, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-04 09:31:04', '2026-01-04 09:31:04'),
	(4, 'Mike Road', 'mike.road@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456003', 'Driver', 'default-avatar.png', 'Daily commute to APU', 247, 5.00, 1, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-08 18:22:05', '2026-01-08 18:22:05'),
	(5, 'Alice Passenger', 'alice.passenger@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456004', 'Passenger', 'default-avatar.png', NULL, 80, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-04 09:27:44', '2026-01-04 09:27:44'),
	(6, 'Bob Travel', 'bob.travel@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456005', 'Passenger', 'default-avatar.png', NULL, 95, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-04 07:58:00', NULL),
	(7, 'Carol Ride', 'carol.ride@email.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456006', 'Passenger', 'default-avatar.png', NULL, 65, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-04 08:25:57', NULL),
	(8, 'Tom Moderator', 'moderator@jujuconnect.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456007', 'Moderator', 'default-avatar.png', NULL, 0, 0.00, 0, 'Active', 1, NULL, NULL, NULL, '2026-01-04 07:23:58', '2026-01-08 17:45:48', '2026-01-08 17:45:48'),
	(9, 'JuJu', 'juju@gmail.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0102154114', 'Passenger', 'profile_695a1acda8892_1767512781.png', 'Test123', 39, 5.00, 1, 'Active', 0, '9e9681649620749648f623ef67c065ed034288319ec4da75e18a4eeadb02ec3d', NULL, NULL, '2026-01-04 07:39:17', '2026-01-08 18:07:11', '2026-01-08 18:07:11'),
	(10, 'admin', 'Admin123@gmail.com', '$2y$10$QySUME3iFdi3T60WN6KOWu2BNgV1PAtsWZKBjJgnOc0F6hV4Vub8q', '0123456789', 'Driver', 'default-avatar.png', NULL, 0, 0.00, 0, 'Active', 0, 'b58010e242b188143713248f2fed1ea91e6e57f5654b3c70dd5564a1dcf98a16', NULL, NULL, '2026-01-04 07:57:47', '2026-01-07 17:39:58', '2026-01-07 17:39:58'),
	(11, 'julianbmw123', '123@gmail.com', '$2y$10$gNZZQv7ShjHHWQ1a6pW1a.gVYe5q7wTNr6c.mBUCsoCnJQiwbHBwW', '01131081233', 'Passenger', 'default-avatar.png', '', 0, 0.00, 0, 'Active', 0, '7dcc133473512d0d923548e4fbe5a0035cd91329b9c736656f9d3ecf21053d76', NULL, NULL, '2026-01-08 07:31:15', '2026-01-08 08:15:40', '2026-01-08 08:15:40');

-- Dumping structure for view jujuconnect.vw_activerides
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_activerides` (
	`RideID` INT(11) NOT NULL,
	`OriginLocation` VARCHAR(255) NOT NULL,
	`DestinationLocation` VARCHAR(255) NOT NULL,
	`DepartureDate` DATE NOT NULL,
	`DepartureTime` TIME NOT NULL,
	`AvailableSeats` INT(11) NOT NULL,
	`TotalSeats` INT(11) NOT NULL,
	`PricePerSeat` DECIMAL(10,2) NOT NULL,
	`Distance` DECIMAL(10,2) NULL COMMENT 'Distance in kilometers',
	`VehicleType` VARCHAR(50) NULL COMMENT 'Car model/type',
	`Notes` TEXT NULL COMMENT 'Additional ride information',
	`DriverID` INT(11) NOT NULL,
	`DriverName` VARCHAR(180) NOT NULL,
	`DriverPicture` VARCHAR(255) NULL,
	`DriverRating` DECIMAL(3,2) NULL COMMENT 'Average rating out of 5.00',
	`DriverTotalRatings` INT(11) NULL
) ENGINE=MyISAM;

-- Dumping structure for view jujuconnect.vw_leaderboard
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_leaderboard` (
	`UserID` INT(11) NOT NULL,
	`FullName` VARCHAR(180) NOT NULL,
	`ProfilePicture` VARCHAR(255) NULL,
	`EcoPoints` INT(11) NULL,
	`Rating` DECIMAL(3,2) NULL COMMENT 'Average rating out of 5.00',
	`TotalRatings` INT(11) NULL,
	`Role` ENUM('Driver','Passenger','Admin','Moderator') NULL,
	`Rank` BIGINT(21) NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for view jujuconnect.vw_pendingreports
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_pendingreports` (
	`ReportID` INT(11) NOT NULL,
	`ReportType` ENUM('Inappropriate Behavior','Safety Concern','Fraud','Spam','Other') NOT NULL,
	`Reason` TEXT NOT NULL,
	`Status` ENUM('Pending','Under Review','Resolved','Dismissed') NULL,
	`CreatedAt` TIMESTAMP NULL,
	`ReporterName` VARCHAR(180) NOT NULL,
	`ReporterEmail` VARCHAR(180) NOT NULL,
	`ReportedUserName` VARCHAR(180) NOT NULL,
	`ReportedUserEmail` VARCHAR(180) NOT NULL,
	`OriginLocation` VARCHAR(255) NULL,
	`DestinationLocation` VARCHAR(255) NULL) ENGINE=MyISAM;

-- Dumping structure for view jujuconnect.vw_userridestats
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_userridestats` (
	`UserID` INT(11) NOT NULL,
	`FullName` VARCHAR(180) NOT NULL,
	`EcoPoints` INT(11) NULL,
	`TotalRidesAsDriver` BIGINT(21) NOT NULL,
	`TotalRidesAsPassenger` BIGINT(21) NOT NULL,
	`TotalCO2Saved` DECIMAL(32,2) NULL,
	`TotalPointsEarned` DECIMAL(32,0) NULL
) ENGINE=MyISAM;

-- Dumping structure for trigger jujuconnect.after_rating_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER after_rating_insert
AFTER INSERT ON Ratings
FOR EACH ROW
BEGIN
    UPDATE Users
    SET Rating = (
        SELECT AVG(Rating)
        FROM Ratings
        WHERE RatedUserID = NEW.RatedUserID
    ),
    TotalRatings = (
        SELECT COUNT(*)
        FROM Ratings
        WHERE RatedUserID = NEW.RatedUserID
    )
    WHERE UserID = NEW.RatedUserID;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger jujuconnect.after_request_approved
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER after_request_approved
AFTER UPDATE ON RideRequests
FOR EACH ROW
BEGIN
    IF NEW.Status = 'Approved' AND OLD.Status != 'Approved' THEN
        UPDATE Rides
        SET AvailableSeats = AvailableSeats - NEW.SeatsRequested
        WHERE RideID = NEW.RideID;
        
        -- Check if ride is now full
        UPDATE Rides
        SET Status = 'Full'
        WHERE RideID = NEW.RideID AND AvailableSeats <= 0 AND Status = 'Available';
    END IF;
    
    -- If request is cancelled or rejected, add seats back
    IF (NEW.Status IN ('Cancelled', 'Rejected')) AND (OLD.Status = 'Approved') THEN
        UPDATE Rides
        SET AvailableSeats = AvailableSeats + NEW.SeatsRequested,
            Status = CASE 
                WHEN Status = 'Full' THEN 'Available'
                ELSE Status
            END
        WHERE RideID = NEW.RideID;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger jujuconnect.after_ride_completed
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER after_ride_completed
AFTER UPDATE ON Rides
FOR EACH ROW
BEGIN
    DECLARE eco_points INT;
    DECLARE co2_saved DECIMAL(10,2);
    DECLARE passengers_count INT;
    
    IF NEW.Status = 'Completed' AND OLD.Status != 'Completed' THEN
        -- Calculate number of approved passengers
        SET passengers_count = (
            SELECT COUNT(*) 
            FROM RideRequests 
            WHERE RideID = NEW.RideID AND Status = 'Approved'
        );
        
        -- Calculate eco points and CO2 saved
        -- Formula: Base 10 points + (distance * passengers) + passenger bonus
        SET eco_points = 10 + CEIL(COALESCE(NEW.Distance, 10) * (passengers_count + 1) * 0.5);
        SET co2_saved = COALESCE(NEW.Distance, 10) * passengers_count * 0.12; -- 0.12 kg CO2 per km per passenger
        
        -- Award points to driver
        INSERT INTO RideHistory (RideID, UserID, Role, SeatsUsed, EcoPointsEarned, CO2Saved, CompletedAt)
        VALUES (NEW.RideID, NEW.DriverID, 'Driver', passengers_count, eco_points + (passengers_count * 5), co2_saved, NOW());
        
        UPDATE Users
        SET EcoPoints = EcoPoints + eco_points + (passengers_count * 5)
        WHERE UserID = NEW.DriverID;
        
        -- Award points to passengers
        INSERT INTO RideHistory (RideID, UserID, Role, SeatsUsed, EcoPointsEarned, CO2Saved, CompletedAt)
        SELECT NEW.RideID, PassengerID, 'Passenger', SeatsRequested, eco_points, co2_saved / passengers_count, NOW()
        FROM RideRequests
        WHERE RideID = NEW.RideID AND Status = 'Approved';
        
        UPDATE Users u
        INNER JOIN RideRequests rr ON u.UserID = rr.PassengerID
        SET u.EcoPoints = u.EcoPoints + eco_points
        WHERE rr.RideID = NEW.RideID AND rr.Status = 'Approved';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger jujuconnect.after_ride_request_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER after_ride_request_insert
AFTER INSERT ON RideRequests
FOR EACH ROW
BEGIN
    DECLARE driver_id INT;
    DECLARE passenger_name VARCHAR(180);
    
    SELECT DriverID INTO driver_id FROM Rides WHERE RideID = NEW.RideID;
    SELECT FullName INTO passenger_name FROM Users WHERE UserID = NEW.PassengerID;
    
    INSERT INTO Notifications (UserID, Type, Title, Message, RelatedID)
    VALUES (
        driver_id,
        'Ride Request',
        'New Ride Request',
        CONCAT(passenger_name, ' has requested to join your ride.'),
        NEW.RequestID
    );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_activerides`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_activerides` AS SELECT 
    r.RideID,
    r.OriginLocation,
    r.DestinationLocation,
    r.DepartureDate,
    r.DepartureTime,
    r.AvailableSeats,
    r.TotalSeats,
    r.PricePerSeat,
    r.Distance,
    r.VehicleType,
    r.Notes,
    u.UserID AS DriverID,
    u.FullName AS DriverName,
    u.ProfilePicture AS DriverPicture,
    u.Rating AS DriverRating,
    u.TotalRatings AS DriverTotalRatings
FROM Rides r
INNER JOIN Users u ON r.DriverID = u.UserID
WHERE r.Status = 'Available' 
  AND r.DepartureDate >= CURDATE()
  AND u.Status = 'Active' ;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_leaderboard`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_leaderboard` AS SELECT 
    UserID,
    FullName,
    ProfilePicture,
    EcoPoints,
    Rating,
    TotalRatings,
    Role,
    RANK() OVER (ORDER BY EcoPoints DESC) AS `Rank`
FROM Users
WHERE Status = 'Active'
ORDER BY EcoPoints DESC
LIMIT 100 ;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_pendingreports`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_pendingreports` AS SELECT 
    r.ReportID,
    r.ReportType,
    r.Reason,
    r.Status,
    r.CreatedAt,
    reporter.FullName AS ReporterName,
    reporter.Email AS ReporterEmail,
    reported.FullName AS ReportedUserName,
    reported.Email AS ReportedUserEmail,
    rides.OriginLocation,
    rides.DestinationLocation
FROM Reports r
INNER JOIN Users reporter ON r.ReporterID = reporter.UserID
INNER JOIN Users reported ON r.ReportedUserID = reported.UserID
LEFT JOIN Rides rides ON r.RideID = rides.RideID
WHERE r.Status IN ('Pending', 'Under Review')
ORDER BY r.CreatedAt DESC ;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_userridestats`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_userridestats` AS SELECT 
    u.UserID,
    u.FullName,
    u.EcoPoints,
    COUNT(DISTINCT CASE WHEN rh.Role = 'Driver' THEN rh.RideID END) AS TotalRidesAsDriver,
    COUNT(DISTINCT CASE WHEN rh.Role = 'Passenger' THEN rh.RideID END) AS TotalRidesAsPassenger,
    SUM(rh.CO2Saved) AS TotalCO2Saved,
    SUM(rh.EcoPointsEarned) AS TotalPointsEarned
FROM Users u
LEFT JOIN RideHistory rh ON u.UserID = rh.UserID
GROUP BY u.UserID, u.FullName, u.EcoPoints ;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
