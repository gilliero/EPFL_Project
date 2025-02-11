-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for epfl_timbreuse
CREATE DATABASE IF NOT EXISTS `epfl_timbreuse` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `epfl_timbreuse`;

-- Dumping structure for table epfl_timbreuse.branches
CREATE TABLE IF NOT EXISTS `branches` (
  `id` int NOT NULL,
  `formation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table epfl_timbreuse.branches: ~90 rows (approximately)
INSERT INTO `branches` (`id`, `formation`, `year`, `category`, `subject`) VALUES
	(29, 'informaticien', 1, 'CFC', 'Module 100'),
	(30, 'informaticien', 1, 'CFC', 'Module 104'),
	(31, 'informaticien', 1, 'CFC', 'Module 114'),
	(32, 'informaticien', 1, 'CFC', 'Module 117'),
	(33, 'informaticien', 1, 'CFC', 'Module 123'),
	(34, 'informaticien', 1, 'CFC', 'Module 301'),
	(35, 'informaticien', 1, 'CFC', 'Module 403'),
	(36, 'informaticien', 1, 'CFC', 'Module 404'),
	(37, 'informaticien', 1, 'CFC', 'Module 431'),
	(38, 'informaticien', 1, 'CIE', 'Module 101'),
	(39, 'informaticien', 1, 'CIE', 'Module 302'),
	(40, 'informaticien', 1, 'CIE', 'Module 304'),
	(41, 'informaticien', 1, 'CIE', 'Module 305'),
	(42, 'informaticien', 1, 'Culture générale', 'Langue et communication'),
	(43, 'informaticien', 1, 'Culture générale', 'Société'),
	(44, 'informaticien', 1, 'Compétences de base élargies', 'Anglais technique'),
	(45, 'informaticien', 2, 'CFC', 'Module 115'),
	(46, 'informaticien', 2, 'CFC', 'Module 120'),
	(47, 'informaticien', 2, 'CFC', 'Module 121'),
	(48, 'informaticien', 2, 'CFC', 'Module 122'),
	(49, 'informaticien', 2, 'CFC', 'Module 124'),
	(50, 'informaticien', 2, 'CFC', 'Module 126'),
	(51, 'informaticien', 2, 'CFC', 'Module 129'),
	(52, 'informaticien', 2, 'CFC', 'Module 213'),
	(53, 'informaticien', 2, 'CFC', 'Module 214'),
	(54, 'informaticien', 2, 'CFC', 'Module 226A'),
	(55, 'informaticien', 2, 'CFC', 'Module 226B'),
	(56, 'informaticien', 2, 'CFC', 'Module 411'),
	(57, 'informaticien', 2, 'CFC', 'Module 426'),
	(58, 'informaticien', 2, 'CFC', 'Module 437'),
	(59, 'informaticien', 2, 'CIE', 'Module 127'),
	(60, 'informaticien', 2, 'CIE', 'Module 130'),
	(61, 'informaticien', 2, 'CIE', 'Module 256'),
	(62, 'informaticien', 2, 'CIE', 'Module 307'),
	(63, 'informaticien', 2, 'CIE', 'Module 318'),
	(64, 'informaticien', 2, 'Culture générale', 'Langue et communication'),
	(65, 'informaticien', 2, 'Culture générale', 'Société'),
	(66, 'informaticien', 2, 'Compétences de base élargies', 'Anglais technique'),
	(67, 'informaticien', 3, 'CFC', 'Module 133'),
	(68, 'informaticien', 3, 'CFC', 'Module 128'),
	(69, 'informaticien', 3, 'CFC', 'Module 140'),
	(70, 'informaticien', 3, 'CFC', 'Module 141'),
	(71, 'informaticien', 3, 'CFC', 'Module 143'),
	(72, 'informaticien', 3, 'CFC', 'Module 145'),
	(73, 'informaticien', 3, 'CFC', 'Module 146'),
	(74, 'informaticien', 3, 'CFC', 'Module 151'),
	(75, 'informaticien', 3, 'CFC', 'Module 239'),
	(76, 'informaticien', 3, 'CFC', 'Module 300'),
	(77, 'informaticien', 3, 'CFC', 'Module 306'),
	(78, 'informaticien', 3, 'CFC', 'Module 326'),
	(79, 'informaticien', 3, 'CIE', 'Module 105'),
	(80, 'informaticien', 3, 'Culture générale', 'Langue et communication'),
	(81, 'informaticien', 3, 'Culture générale', 'Société'),
	(82, 'informaticien', 3, 'Compétences de base élargies', 'Anglais technique'),
	(83, 'informaticien', 4, 'CFC', 'Module 153'),
	(84, 'informaticien', 4, 'CFC', 'Module 157'),
	(85, 'informaticien', 4, 'CFC', 'Module 158'),
	(86, 'informaticien', 4, 'CFC', 'Module 159'),
	(87, 'informaticien', 4, 'CFC', 'Module 182'),
	(88, 'informaticien', 4, 'CFC', 'Module 183'),
	(89, 'informaticien', 4, 'CIE', 'Module 184'),
	(90, 'informaticien', 4, 'CIE', 'Module 223'),
	(91, 'informaticien', 4, 'CIE', 'Module 330'),
	(92, 'informaticien', 4, 'CIE', 'Module 340'),
	(93, 'informaticien', 4, 'Culture générale', 'Langue et communication'),
	(94, 'informaticien', 4, 'Culture générale', 'Société'),
	(95, 'informaticien', 4, 'Compétences de base élargies', 'Anglais technique'),
	(96, 'operateur', 1, 'CFC', 'Module 117'),
	(97, 'operateur', 1, 'CFC', 'Module 123'),
	(98, 'operateur', 1, 'CFC', 'Module 126'),
	(99, 'operateur', 1, 'CFC', 'Module 214'),
	(100, 'operateur', 1, 'CFC', 'Module 431'),
	(101, 'operateur', 1, 'CFC', 'Module 437'),
	(102, 'operateur', 1, 'CIE', 'Module 260'),
	(103, 'operateur', 1, 'CIE', 'Module 304'),
	(104, 'operateur', 1, 'CIE', 'Module 305'),
	(105, 'operateur', 1, 'Culture générale', 'Langue et communication'),
	(106, 'operateur', 1, 'Culture générale', 'Société'),
	(107, 'operateur', 1, 'Compétences de base élargies', 'Anglais technique'),
	(108, 'operateur', 2, 'CFC', 'Module 129'),
	(109, 'operateur', 2, 'CFC', 'Module 263'),
	(110, 'operateur', 2, 'CIE', 'Module 261'),
	(111, 'operateur', 2, 'Culture générale', 'Langue et communication'),
	(112, 'operateur', 2, 'Culture générale', 'Société'),
	(113, 'operateur', 2, 'Compétences de base élargies', 'Anglais technique'),
	(114, 'operateur', 3, 'CFC', 'Module 122'),
	(115, 'operateur', 3, 'CFC', 'Module 262'),
	(116, 'operateur', 3, 'Culture générale', 'Langue et communication'),
	(117, 'operateur', 3, 'Culture générale', 'Société'),
	(118, 'operateur', 3, 'Compétences de base élargies', 'Anglais technique');

-- Dumping structure for table epfl_timbreuse.notes
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `note` decimal(3,1) DEFAULT NULL,
  `user_id` int NOT NULL,
  `branche` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table epfl_timbreuse.notes: ~0 rows (approximately)

-- Dumping structure for table epfl_timbreuse.t_personne
CREATE TABLE IF NOT EXISTS `t_personne` (
  `ID_personne` int NOT NULL AUTO_INCREMENT,
  `prenom_personne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nom_personne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gaspar_personne` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mail_personne` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_personne` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Apprentis',
  `password_personne` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `formation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  PRIMARY KEY (`ID_personne`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table epfl_timbreuse.t_personne: 0 rows
/*!40000 ALTER TABLE `t_personne` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_personne` ENABLE KEYS */;

-- Dumping structure for table epfl_timbreuse.t_timbrage
CREATE TABLE IF NOT EXISTS `t_timbrage` (
  `ID_timbrage` int NOT NULL AUTO_INCREMENT,
  `ID_personne` int DEFAULT NULL,
  `date_timbrage` date DEFAULT NULL,
  `heure_timbrage` time DEFAULT NULL,
  `type_timbrage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'bureau',
  `position_timbrage` int DEFAULT NULL,
  `manière_timbrage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_timbrage`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1915 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table epfl_timbreuse.t_timbrage: 0 rows
/*!40000 ALTER TABLE `t_timbrage` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_timbrage` ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
