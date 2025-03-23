-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 23, 2025 at 07:40 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

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
-- Table structure for table `dagen`
--

CREATE TABLE `dagen` (
  `dag_id` int NOT NULL,
  `dag` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `dagen`
--

INSERT INTO `dagen` (`dag_id`, `dag`) VALUES
(1, 'maandag'),
(2, 'dinsdag'),
(3, 'woensdag'),
(4, 'donderdag'),
(5, 'vrijdag'),
(6, 'zaterdag'),
(7, 'zondag');

-- --------------------------------------------------------

--
-- Table structure for table `klassen`
--

CREATE TABLE `klassen` (
  `klas_id` int NOT NULL,
  `klas_naam` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `klassen`
--

INSERT INTO `klassen` (`klas_id`, `klas_naam`) VALUES
(1, 'PT 4.06.21'),
(2, 'PT 3.06.21'),
(3, 'PT 2.06.01'),
(4, 'PT 3.06.11');

-- --------------------------------------------------------

--
-- Table structure for table `lesblok`
--

CREATE TABLE `lesblok` (
  `lesblok_id` int NOT NULL,
  `lesblok` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lesblok`
--

INSERT INTO `lesblok` (`lesblok_id`, `lesblok`) VALUES
(1, 'Blok 1 '),
(2, 'Blok 2'),
(3, 'Blok 3'),
(4, 'Blok 4');

-- --------------------------------------------------------

--
-- Table structure for table `lokaal`
--

CREATE TABLE `lokaal` (
  `lokaal_id` int NOT NULL,
  `lokaal_naam` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `capaciteit` int DEFAULT NULL,
  `opmerkingen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lokaal`
--

INSERT INTO `lokaal` (`lokaal_id`, `lokaal_naam`, `capaciteit`, `opmerkingen`) VALUES
(1, 'Unasat bg 5', 28, 'geen'),
(2, 'Unasat bg 4', 11, NULL),
(3, 'online', 0, 'geen');

-- --------------------------------------------------------

--
-- Table structure for table `periode`
--

CREATE TABLE `periode` (
  `periode_id` int NOT NULL,
  `periode_naam` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `start_datum` date DEFAULT NULL,
  `eind_datum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `periode`
--

INSERT INTO `periode` (`periode_id`, `periode_naam`, `start_datum`, `eind_datum`) VALUES
(1, 'periode 1', '2024-10-09', '2025-01-15'),
(2, 'periode 4', '2024-10-09', '2025-01-15');

-- --------------------------------------------------------

--
-- Table structure for table `personen`
--

CREATE TABLE `personen` (
  `persoon_id` int NOT NULL,
  `naam` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol_id` int DEFAULT NULL,
  `voornaam` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `geboorte_datum` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `klas_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personen`
--

INSERT INTO `personen` (`persoon_id`, `naam`, `rol_id`, `voornaam`, `geboorte_datum`, `active`, `klas_id`) VALUES
(1, 'ishaan', 1, 'karansingh', '2005-04-11 12:08:46', 1, NULL),
(3, 'boi', 3, 'sigma', '2005-11-11 00:00:00', 1, NULL),
(6, 'sanmoeradji', 2, 'rashawn', '2005-11-12 00:00:00', 1, 1),
(9, 'master', 3, 'chief', '1977-11-12 00:00:00', 1, NULL),
(10, 'rishika', 2, 'jainath', '2005-11-22 00:00:00', 1, 1),
(11, 'King', 1, 'admin', '2005-04-11 12:08:46', 1, NULL),
(15, 'wilson', 4, 'jenna', '1980-11-11 00:00:00', 1, NULL),
(17, 'Vikash', 2, 'Gangandien', '2005-04-11 12:08:46', 1, 4),
(18, 'Shivam', 3, 'karansingh', '2055-11-11 00:00:00', 1, NULL),
(19, 'Shiven', 2, 'Joeglal', '2005-11-22 00:00:00', 1, 2),
(20, 'chivar', 2, 'wijngaard', '2006-11-02 00:00:00', 1, 4),
(21, 'Isha', 2, 'Jagai', '2005-07-06 00:00:00', 1, 2),
(27, 'Ganga', 6, 'Pandey', '1975-11-11 00:00:00', 1, NULL),
(28, 'Jardena', 5, 'Nyra', '1980-02-22 00:00:00', 1, NULL),
(29, 'Neel', 7, 'Rampersad', '1990-11-11 00:00:00', 1, NULL),
(30, 'tester', 7, '12', '2009-11-11 00:00:00', 1, NULL),
(33, 'Vijay', 2, 'Sewradj', '1997-11-22 00:00:00', 1, NULL),
(34, 'Nandenie', 2, 'Pawiro', '2007-11-22 00:00:00', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `presentie`
--

CREATE TABLE `presentie` (
  `presentie_id` int NOT NULL,
  `persoon_id` int NOT NULL,
  `vak_id` int NOT NULL,
  `klas_id` int NOT NULL,
  `status_id` int NOT NULL,
  `periode_id` int NOT NULL,
  `Jaar_id` int NOT NULL,
  `datum` date DEFAULT NULL,
  `dag_id` int NOT NULL,
  `Richting_ID` int NOT NULL,
  `lokaal_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presentie`
--

INSERT INTO `presentie` (`presentie_id`, `persoon_id`, `vak_id`, `klas_id`, `status_id`, `periode_id`, `Jaar_id`, `datum`, `dag_id`, `Richting_ID`, `lokaal_id`) VALUES
(66, 6, 2, 1, 5, 2, 1, '2025-03-05', 3, 3, 2),
(67, 10, 2, 1, 1, 2, 1, '2025-03-05', 3, 3, 1),
(71, 19, 2, 2, 3, 1, 1, '2025-02-28', 5, 3, 1),
(72, 21, 2, 2, 2, 1, 1, '2025-02-28', 5, 3, 1),
(77, 6, 2, 1, 2, 2, 1, '2025-03-15', 6, 3, 2),
(78, 10, 2, 1, 2, 2, 1, '2025-03-15', 6, 3, 2),
(79, 20, 2, 1, 5, 2, 1, '2025-03-15', 6, 3, 1),
(103, 17, 5, 4, 1, 2, 1, '2025-03-20', 4, 3, 1),
(104, 20, 5, 4, 4, 2, 1, '2025-03-20', 4, 3, 1),
(105, 33, 5, 3, 1, 2, 1, '2025-03-20', 4, 3, 3),
(106, 17, 5, 4, 1, 2, 1, '2025-03-23', 7, 3, 1),
(107, 20, 5, 4, 1, 2, 1, '2025-03-23', 7, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `richting`
--

CREATE TABLE `richting` (
  `Richting_ID` int NOT NULL,
  `Richting` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Complex` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `richting`
--

INSERT INTO `richting` (`Richting_ID`, `Richting`, `Complex`, `persoon_id`) VALUES
(3, 'ICT', 'Jagernath Lachmon', NULL),
(4, 'AA', 'Leysweg', NULL),
(5, 'AV', 'Jagernath', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rollen`
--

CREATE TABLE `rollen` (
  `role_id` int NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
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
(7, 'systeembeheerder');

-- --------------------------------------------------------

--
-- Table structure for table `rooster`
--

CREATE TABLE `rooster` (
  `Rooster_id` int NOT NULL,
  `vak_id` int NOT NULL,
  `periode_id` int NOT NULL,
  `klas_id` int NOT NULL,
  `persoon_id` int NOT NULL,
  `start_tijd` time NOT NULL,
  `eind_tijd` time NOT NULL,
  `lokaal_id` int NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'gepland',
  `Jaar_id` int NOT NULL,
  `Richting_ID` int NOT NULL,
  `dag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooster`
--

INSERT INTO `rooster` (`Rooster_id`, `vak_id`, `periode_id`, `klas_id`, `persoon_id`, `start_tijd`, `eind_tijd`, `lokaal_id`, `status`, `Jaar_id`, `Richting_ID`, `dag_id`) VALUES
(16, 2, 1, 1, 9, '08:30:00', '10:15:00', 1, 'goedgekeurd', 1, 3, 1),
(18, 2, 2, 2, 18, '07:00:00', '08:30:00', 2, 'goedgekeurd', 1, 3, 1),
(19, 2, 2, 1, 9, '08:30:00', '10:15:00', 2, 'goedgekeurd', 1, 3, 2),
(23, 2, 1, 2, 9, '11:30:00', '13:15:00', 2, 'goedgekeurd', 1, 3, 5),
(28, 5, 2, 4, 9, '09:20:00', '10:20:00', 1, 'goedgekeurd', 1, 3, 4),
(32, 2, 1, 3, 3, '08:30:00', '10:30:00', 1, 'goedgekeurd', 1, 3, 5),
(34, 4, 2, 3, 3, '11:11:00', '00:12:00', 3, 'gepland', 1, 5, 1),
(35, 5, 2, 3, 9, '00:30:00', '13:00:00', 3, 'gepland', 1, 3, 5);

-- --------------------------------------------------------

--
-- Table structure for table `roosterstud`
--

CREATE TABLE `roosterstud` (
  `rooster_id` int NOT NULL,
  `dag_id` int NOT NULL,
  `klas_id` int NOT NULL,
  `vak_id` int DEFAULT NULL,
  `lesblok_id` int NOT NULL,
  `lokaal_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roosterstud`
--

INSERT INTO `roosterstud` (`rooster_id`, `dag_id`, `klas_id`, `vak_id`, `lesblok_id`, `lokaal_id`) VALUES
(4, 2, 1, 2, 1, 1),
(6, 1, 1, 2, 2, 2),
(7, 1, 1, 4, 3, 3),
(21, 1, 4, 2, 1, 2),
(27, 5, 1, 2, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `schooljaar`
--

CREATE TABLE `schooljaar` (
  `Jaar_id` int NOT NULL,
  `Schooljaar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schooljaar`
--

INSERT INTO `schooljaar` (`Jaar_id`, `Schooljaar`) VALUES
(1, '2024/2025');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_id` int NOT NULL,
  `status_naam` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status_naam`) VALUES
(1, 'Afwezig'),
(2, 'Aanwezig'),
(3, 'Laat'),
(4, 'Ziek'),
(5, 'Vrijstelling'),
(6, 'Laatbrief');

-- --------------------------------------------------------

--
-- Table structure for table `tgebruiker`
--

CREATE TABLE `tgebruiker` (
  `gebruiker_id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tgebruiker`
--

INSERT INTO `tgebruiker` (`gebruiker_id`, `email`, `password`, `persoon_id`) VALUES
(3, 'ishaan.karansingh@natin.admin.sr', '$2y$10$0WEGEMFvG807rRjvA7y4n.jEbBoPRVyGbjx7hubFC8yWqaZC6m95q', 1),
(5, 'sigma.boi@docent.sr', '$2y$10$aXA53aEOouXOyiLp3KmD9.GJN8ZE4sboHh0h2szYnHLBIjYhK/ZSi', 3),
(8, 'shawn@natin.student.sr', '$2y$10$Q/pDdJ/CLSozdnXz6/agZevin9aHC3I8.iYWIvzXiUyhHSLyd4Fiu', 6),
(11, 'master@docent.com', '$2y$10$PRdjJ.SB11HEHAPaYcpOF.uEtxdfa65lbtYruXyUJg3ipI2LnWzsK', 9),
(12, 'rishika.jainath@natin.student.sr', '$2y$10$eCXv4sYOyETojPAIpF.YcOS.yPH9ieNAby9UnCrTLeIzWY1AEQJfy', 10),
(13, 'admin@natin.sr', '$2y$10$xcd1zg3YFdwlSnTwyi18TujEwkvWNfNdkM.XoZYIhGLCeLV5U3b3e', 11),
(17, 'jenna@rc.sr', '$2y$10$Ty2IWIEHXu90wVLRlrDTue2oP/kAhXtyb6b2yJ6oe32SwJtyU6/ha', 15),
(19, 'shivam@natin.sr', '$2y$10$Z.u6Sc3/1ZzfNmt8xOrbL.dJ47JMVHljIdqFQGRrr6/3H9FHSk6ke', 18),
(20, 'shiven@student.natin.sr', '$2y$10$Luj1J/ifRGrTnevBjlpwMutDowFGBNUCHjvhjIq7CHTdtPu8NEEOO', 19),
(21, 'chivar@natin.stud.sr', '$2y$10$cBERnWSZSg.wu9dbHUZjY.tKGMizt8tTm/ZEhrjF4ospcHVFP8cGe', 20),
(22, 'isha@natin.stud.sr', '$2y$10$zysUH/wWIxKB7r.bCqkH0u9sPA2IJNAtP15JvxQFz3b0r/kCGSeo2', 21),
(28, 'directeur@natin.sr', '$2y$10$/z6UjGGayJJqdRP71JxMp.XN/apBxHw/Fcky60nGp3RtMiwuCPcMC', 27),
(29, 'od@natin.sr', '$2y$10$...GhaXhs75Ou2LRBPblXeIzn54pQ8OGNiXX8m3SgyA.lcHg37VHe', 28),
(30, 'systeembeheer@natin.sr', '$2y$10$yc.HbVxpglcxKBo65tCP1ubKnJiott9zTiC/gUbrcj9Jq1ASx05wO', 29),
(31, 'tester@gmail.com', '$2y$10$iAQjIAkGwX9Zz7hKj1ubuupJYeoBOy7STW48KkpN6XcxMEJjP6GW.', 30),
(34, 'vijay.sewradj@natin.sr', '$2y$10$pB.vzIPo/FKbhgR4TxFglO.E.J1Fg/lem9n05OHniHcj8GadhWpMG', 33),
(35, 'pawiro@natin.sr', '$2y$10$Hb/u944UNzV.d3X8RwX9gOkii0qHYlRjtM2zNsmhGp9f04.UmT6tq', 34);

-- --------------------------------------------------------

--
-- Table structure for table `vakken`
--

CREATE TABLE `vakken` (
  `vak_id` int NOT NULL,
  `vak_naam` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `periode_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vakken`
--

INSERT INTO `vakken` (`vak_id`, `vak_naam`, `periode_id`) VALUES
(2, 'Nederlands', 1),
(4, 'Ondernemerschap', 2),
(5, 'Studie loopbaan', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dagen`
--
ALTER TABLE `dagen`
  ADD PRIMARY KEY (`dag_id`);

--
-- Indexes for table `klassen`
--
ALTER TABLE `klassen`
  ADD PRIMARY KEY (`klas_id`);

--
-- Indexes for table `lesblok`
--
ALTER TABLE `lesblok`
  ADD PRIMARY KEY (`lesblok_id`);

--
-- Indexes for table `lokaal`
--
ALTER TABLE `lokaal`
  ADD PRIMARY KEY (`lokaal_id`);

--
-- Indexes for table `periode`
--
ALTER TABLE `periode`
  ADD PRIMARY KEY (`periode_id`);

--
-- Indexes for table `personen`
--
ALTER TABLE `personen`
  ADD PRIMARY KEY (`persoon_id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `idx_klas_id` (`klas_id`);

--
-- Indexes for table `presentie`
--
ALTER TABLE `presentie`
  ADD PRIMARY KEY (`presentie_id`),
  ADD KEY `persoon_id` (`persoon_id`),
  ADD KEY `klas_id` (`klas_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `Jaar_id` (`Jaar_id`),
  ADD KEY `vak_id` (`vak_id`),
  ADD KEY `presentie_ibfk_dag` (`dag_id`),
  ADD KEY `fk_lokaal_id` (`lokaal_id`);

--
-- Indexes for table `richting`
--
ALTER TABLE `richting`
  ADD PRIMARY KEY (`Richting_ID`),
  ADD KEY `fk_richting_persoon` (`persoon_id`);

--
-- Indexes for table `rollen`
--
ALTER TABLE `rollen`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `rooster`
--
ALTER TABLE `rooster`
  ADD PRIMARY KEY (`Rooster_id`),
  ADD KEY `persoon_id` (`persoon_id`),
  ADD KEY `lokaal_id` (`lokaal_id`),
  ADD KEY `klas_id` (`klas_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `Jaar_id` (`Jaar_id`),
  ADD KEY `vak_id` (`vak_id`),
  ADD KEY `idx_Richting_id` (`Richting_ID`),
  ADD KEY `fk_dag_id` (`dag_id`);

--
-- Indexes for table `roosterstud`
--
ALTER TABLE `roosterstud`
  ADD PRIMARY KEY (`rooster_id`),
  ADD KEY `dag_id` (`dag_id`,`klas_id`,`vak_id`),
  ADD KEY `vak_id` (`vak_id`),
  ADD KEY `klas_id` (`klas_id`),
  ADD KEY `fk_lesblok` (`lesblok_id`),
  ADD KEY `fk_lokaal` (`lokaal_id`);

--
-- Indexes for table `schooljaar`
--
ALTER TABLE `schooljaar`
  ADD PRIMARY KEY (`Jaar_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  ADD PRIMARY KEY (`gebruiker_id`),
  ADD KEY `persoon_id` (`persoon_id`);

--
-- Indexes for table `vakken`
--
ALTER TABLE `vakken`
  ADD PRIMARY KEY (`vak_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dagen`
--
ALTER TABLE `dagen`
  MODIFY `dag_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `klassen`
--
ALTER TABLE `klassen`
  MODIFY `klas_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lesblok`
--
ALTER TABLE `lesblok`
  MODIFY `lesblok_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lokaal`
--
ALTER TABLE `lokaal`
  MODIFY `lokaal_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `periode`
--
ALTER TABLE `periode`
  MODIFY `periode_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `personen`
--
ALTER TABLE `personen`
  MODIFY `persoon_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `presentie`
--
ALTER TABLE `presentie`
  MODIFY `presentie_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `richting`
--
ALTER TABLE `richting`
  MODIFY `Richting_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rooster`
--
ALTER TABLE `rooster`
  MODIFY `Rooster_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `roosterstud`
--
ALTER TABLE `roosterstud`
  MODIFY `rooster_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `schooljaar`
--
ALTER TABLE `schooljaar`
  MODIFY `Jaar_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  MODIFY `gebruiker_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `vakken`
--
ALTER TABLE `vakken`
  MODIFY `vak_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `personen`
--
ALTER TABLE `personen`
  ADD CONSTRAINT `personen_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `rollen` (`role_id`),
  ADD CONSTRAINT `personen_ibfk_2` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `presentie`
--
ALTER TABLE `presentie`
  ADD CONSTRAINT `fk_lokaal_id` FOREIGN KEY (`lokaal_id`) REFERENCES `lokaal` (`lokaal_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_1` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_2` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_3` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_5` FOREIGN KEY (`Jaar_id`) REFERENCES `schooljaar` (`Jaar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_6` FOREIGN KEY (`vak_id`) REFERENCES `vakken` (`vak_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_dag` FOREIGN KEY (`dag_id`) REFERENCES `dagen` (`dag_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `richting`
--
ALTER TABLE `richting`
  ADD CONSTRAINT `fk_richting_persoon` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooster`
--
ALTER TABLE `rooster`
  ADD CONSTRAINT `fk_dag_id` FOREIGN KEY (`dag_id`) REFERENCES `dagen` (`dag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_10` FOREIGN KEY (`Richting_ID`) REFERENCES `richting` (`Richting_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_4` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_5` FOREIGN KEY (`lokaal_id`) REFERENCES `lokaal` (`lokaal_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_6` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_7` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_8` FOREIGN KEY (`Jaar_id`) REFERENCES `schooljaar` (`Jaar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_9` FOREIGN KEY (`vak_id`) REFERENCES `vakken` (`vak_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `roosterstud`
--
ALTER TABLE `roosterstud`
  ADD CONSTRAINT `fk_lesblok` FOREIGN KEY (`lesblok_id`) REFERENCES `lesblok` (`lesblok_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_lokaal` FOREIGN KEY (`lokaal_id`) REFERENCES `lokaal` (`lokaal_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `roosterstud_ibfk_1` FOREIGN KEY (`vak_id`) REFERENCES `vakken` (`vak_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roosterstud_ibfk_2` FOREIGN KEY (`dag_id`) REFERENCES `dagen` (`dag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `roosterstud_ibfk_3` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  ADD CONSTRAINT `tgebruiker_ibfk_1` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`);

--
-- Constraints for table `vakken`
--
ALTER TABLE `vakken`
  ADD CONSTRAINT `vakken_ibfk_1` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
