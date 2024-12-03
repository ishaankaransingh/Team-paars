-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 01:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aanwezigheids_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `rollen`
--

CREATE TABLE `rollen` (
  `role_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rollen`
--

INSERT INTO `rollen` (`role_id`, `role`) VALUES
(1, 'admin'),
(2, 'student'),
(3, 'docent'),
(4, 'rc'),
(5, 'od'),
(6, 'directeur'),
(7, 'systeembeheer');

-- --------------------------------------------------------

--
-- Table structure for table `tgebruiker`
--

CREATE TABLE `tgebruiker` (
  `gebruiker_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tgebruiker`
--

INSERT INTO `tgebruiker` (`gebruiker_id`, `user_name`, `email`, `password`, `name`, `role_id`) VALUES
(1, 'admin', 'admin@natin.sr', 'admin', 'admin', 1),
(4, 'shawn', 'shawn@natin.student.sr', 'shawn', 'shawn', 2),
(5, 'docent', 'docent@natin.sr', 'docent', 'docent', 3),
(6, 'rc', 'rc@natin.sr', 'rc', 'rc', 4),
(7, 'od', 'od@natin.sr', 'od', 'od', 5),
(8, 'directeur', 'directeur@natin.sr', 'directeur', 'directeur', 6),
(9, 'systeembeheer', 'systeembeheer@natin.sr', 'systeembeheer', 'systeembeheer', 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rollen`
--
ALTER TABLE `rollen`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  ADD PRIMARY KEY (`gebruiker_id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD UNIQUE KEY `user_name_2` (`user_name`),
  ADD UNIQUE KEY `user_name_3` (`user_name`),
  ADD UNIQUE KEY `user_name_4` (`user_name`),
  ADD KEY `fk_role` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rollen`
--
ALTER TABLE `rollen`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  MODIFY `gebruiker_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  ADD CONSTRAINT `fk_role` FOREIGN KEY (`role_id`) REFERENCES `rollen` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
