-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Mar 06, 2024 at 01:43 PM
-- Server version: 5.7.39
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epfl_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `note` decimal(3,1) DEFAULT NULL,
  `id_apprenti` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `branche` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `note`, `id_apprenti`, `id_utilisateur`, `user_id`, `branche`) VALUES
(7, '4.5', NULL, NULL, 42, 'math'),
(8, '5.2', NULL, NULL, 42, 'math'),
(9, '4.4', NULL, NULL, 42, 'anglais'),
(10, '6.0', NULL, NULL, 42, 'math'),
(11, '5.6', NULL, NULL, 42, 'anglais'),
(12, '5.6', NULL, NULL, 42, 'anglais'),
(13, '4.3', NULL, NULL, 42, 'Module 2'),
(14, '5.3', NULL, NULL, 42, 'Module 4'),
(15, '4.2', NULL, NULL, 44, 'math'),
(16, '4.3', NULL, NULL, 44, 'Module 2'),
(17, '4.4', NULL, NULL, 44, 'math'),
(18, '6.0', NULL, NULL, 44, 'math'),
(19, '4.5', NULL, NULL, 44, 'anglais'),
(20, '6.0', NULL, NULL, 44, 'anglais');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `prenom` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int(11) NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'apprenti'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `prenom`, `nom`, `username`, `year`, `password`, `role`) VALUES
(37, 'Arnaud', 'Sartoni', 'sartoni', 0, '1234', 'formateur'),
(42, 'Jérémy', 'Noth', 'jnoth', 1, '1234', 'apprenti'),
(43, 'Ivan', 'Forte', 'ivan', 3, '1234', 'apprenti'),
(44, 'test', 'test', 'test', 1, '1234', 'apprenti');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
