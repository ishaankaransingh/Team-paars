-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 20, 2025 at 07:25 PM
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
-- Table structure for table `klassen`
--

CREATE TABLE `klassen` (
  `klas_id` int NOT NULL,
  `klas_naam` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
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
-- Table structure for table `lokaal`
--

CREATE TABLE `lokaal` (
  `lokaal_id` int NOT NULL,
  `lokaal_naam` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `capaciteit` int DEFAULT NULL,
  `opmerkingen` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lokaal`
--

INSERT INTO `lokaal` (`lokaal_id`, `lokaal_naam`, `capaciteit`, `opmerkingen`) VALUES
(1, 'Unasat bg 5', 28, 'geen'),
(2, 'Unasat bg 5', 11, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `periode`
--

CREATE TABLE `periode` (
  `periode_id` int NOT NULL,
  `periode_naam` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `naam` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rol_id` int DEFAULT NULL,
  `voornaam` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
(4, 'joel', 3, 'joel', '2003-11-02 00:00:00', 1, NULL),
(6, 'sanmoeradji', 2, 'rashawn', '2005-11-12 00:00:00', 1, 1),
(9, 'master', 3, 'chief', '1977-11-12 00:00:00', 1, NULL),
(10, 'rishika', 2, 'jainath', '2005-11-22 00:00:00', 1, 1),
(11, 'admin', 1, '1', '2005-04-11 12:08:46', 1, NULL),
(12, 'Jhon', 7, 'wilson', '1980-11-02 00:00:00', 1, NULL),
(13, 'Sookhlal', 6, 'ganga', '1970-11-02 00:00:00', 1, NULL),
(15, 'wilson', 4, 'jenna', '1980-11-11 00:00:00', 1, NULL),
(16, 'Mira', 5, 'Hansberg', '1978-11-02 00:00:00', 1, NULL),
(17, 'Vikash', 2, 'Gangandien', '2005-04-11 12:08:46', 1, 4),
(18, 'Shivam', 3, 'Kransingh', '2055-11-11 00:00:00', 1, NULL),
(19, 'Shiven', 2, 'Joeglal', '2005-11-22 00:00:00', 1, 2);

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
  `dag` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Richting_ID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `richting`
--

CREATE TABLE `richting` (
  `Richting_ID` int NOT NULL,
  `Richting` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Complex` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `persoon_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `richting`
--

INSERT INTO `richting` (`Richting_ID`, `Richting`, `Complex`, `persoon_id`) VALUES
(3, 'ICT', 'Jagernath Lachmon', 10),
(4, 'AA', 'Leysweg', 10),
(5, 'AV', 'Jagernath', 9);

-- --------------------------------------------------------

--
-- Table structure for table `rollen`
--

CREATE TABLE `rollen` (
  `role_id` int NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
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
-- Table structure for table `rooster`
--

CREATE TABLE `rooster` (
  `Rooster_id` int NOT NULL,
  `vak_id` int NOT NULL,
  `periode_id` int NOT NULL,
  `klas_id` int NOT NULL,
  `persoon_id` int NOT NULL,
  `dag` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `start_tijd` time NOT NULL,
  `eind_tijd` time NOT NULL,
  `lokaal_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'gepland',
  `Jaar_id` int NOT NULL,
  `Richting_ID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooster`
--

INSERT INTO `rooster` (`Rooster_id`, `vak_id`, `periode_id`, `klas_id`, `persoon_id`, `dag`, `start_tijd`, `eind_tijd`, `lokaal_id`, `status`, `Jaar_id`, `Richting_ID`) VALUES
(4, 2, 1, 1, 9, 'maandag', '08:30:00', '10:15:00', 1, 'gepland', 1, 3),
(6, 2, 2, 4, 9, 'maandag', '12:00:00', '13:15:00', 1, 'gepland', 1, 3),
(8, 2, 2, 2, 18, 'maandag', '07:00:00', '08:30:00', 2, 'gepland', 1, 3),
(9, 2, 1, 1, 9, 'dinsdag', '08:30:00', '10:15:00', 2, 'gepland', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `schooljaar`
--

CREATE TABLE `schooljaar` (
  `Jaar_id` int NOT NULL,
  `Schooljaar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
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
  `status_naam` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_id`, `status_naam`) VALUES
(1, 'Afwezig'),
(2, 'Aanwezig'),
(3, 'Laat'),
(4, 'Ziek');

-- --------------------------------------------------------

--
-- Table structure for table `tgebruiker`
--

CREATE TABLE `tgebruiker` (
  `gebruiker_id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `persoon_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tgebruiker`
--

INSERT INTO `tgebruiker` (`gebruiker_id`, `email`, `password`, `persoon_id`) VALUES
(3, 'ishaan.karansingh@natin.admin.sr', '$2y$10$0WEGEMFvG807rRjvA7y4n.jEbBoPRVyGbjx7hubFC8yWqaZC6m95q', 1),
(5, 'sigma.boi@docent.sr', '$2y$10$aXA53aEOouXOyiLp3KmD9.GJN8ZE4sboHh0h2szYnHLBIjYhK/ZSi', 3),
(6, 'joel.zending@docent.natin.sr', '$2y$10$De/8vdM079e/GH9cFjZO6eatMdpbFL6vqj/9.wT6nPEGHTMv5h/i6', 4),
(8, 'shawn@natin.student.sr', '$2y$10$gFThx1NHaer07zQEoby5SeI3ZXrcyBXPjNTtapZo52xD9bFURxHau', 6),
(11, 'master@docent.com', '$2y$10$AsHfhA56/i6ex9ZkcByYK.PrMxjz98WQ1DekINvP0FBeDFeh03p4O', 9),
(12, 'rishika.jainath@natin.student.sr', '$2y$10$XqMz6h23WKnTETDnGrseROwnNbdn0xHPPIrxYczhPWwsEuBk7GOwS', 10),
(13, 'admin@natin.sr', '$2y$10$xcd1zg3YFdwlSnTwyi18TujEwkvWNfNdkM.XoZYIhGLCeLV5U3b3e', 11),
(14, 'systeembeheer@natin.sr', '$2y$10$r4CO.Xhc66rpDGzMCRk6h.JupjiWr9tsfKmR6QcOgUnKTquv4g3.q', 12),
(15, 'soekhlal@directeur.sr', '$2y$10$VPYyFe1q/W.pNRNsuba6QO.5DJKAGIjrxjEfV21nT5tNM2aDRhXw.', 13),
(17, 'jenna@rc.sr', '$2y$10$RxKFjTYNfVihxnRiRsuep.7Uwgmn9diSU72jwmvBpnRqw9fzH.daO', 15),
(18, 'hansburg@od.sr', '$2y$10$22U.RN8C/KJ.883rQJR8Q.RAcEIilD/9SsVDwhFcTgz6MdjUWRnm6', 16),
(19, 'shivam@natin.sr', '$2y$10$4BWFvu6nD3iHR94Jw1lkkOBEbPnW7/uhQdVGymb1ZH7nh2OVpbHGq', 18),
(20, 'shiven@student.natin.sr', '$2y$10$Gy5nTbtqsQFANRKnf.y0ce3JVxbhltUa0YSfa4PBknyFcykndNvVO', 19);

-- --------------------------------------------------------

--
-- Table structure for table `vakken`
--

CREATE TABLE `vakken` (
  `vak_id` int NOT NULL,
  `vak_naam` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `periode_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vakken`
--

INSERT INTO `vakken` (`vak_id`, `vak_naam`, `periode_id`) VALUES
(1, 'Wiskunde', 1),
(2, 'Nederlands', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `klassen`
--
ALTER TABLE `klassen`
  ADD PRIMARY KEY (`klas_id`);

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
  ADD KEY `vak_id` (`vak_id`);

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
  ADD KEY `idx_Richting_id` (`Richting_ID`);

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
-- AUTO_INCREMENT for table `klassen`
--
ALTER TABLE `klassen`
  MODIFY `klas_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lokaal`
--
ALTER TABLE `lokaal`
  MODIFY `lokaal_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `periode`
--
ALTER TABLE `periode`
  MODIFY `periode_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `personen`
--
ALTER TABLE `personen`
  MODIFY `persoon_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `presentie`
--
ALTER TABLE `presentie`
  MODIFY `presentie_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `richting`
--
ALTER TABLE `richting`
  MODIFY `Richting_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rooster`
--
ALTER TABLE `rooster`
  MODIFY `Rooster_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `schooljaar`
--
ALTER TABLE `schooljaar`
  MODIFY `Jaar_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tgebruiker`
--
ALTER TABLE `tgebruiker`
  MODIFY `gebruiker_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vakken`
--
ALTER TABLE `vakken`
  MODIFY `vak_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `presentie_ibfk_1` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_2` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_3` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `status` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_5` FOREIGN KEY (`Jaar_id`) REFERENCES `schooljaar` (`Jaar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `presentie_ibfk_6` FOREIGN KEY (`vak_id`) REFERENCES `vakken` (`vak_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `richting`
--
ALTER TABLE `richting`
  ADD CONSTRAINT `fk_richting_persoon` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooster`
--
ALTER TABLE `rooster`
  ADD CONSTRAINT `rooster_ibfk_10` FOREIGN KEY (`Richting_ID`) REFERENCES `richting` (`Richting_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_4` FOREIGN KEY (`persoon_id`) REFERENCES `personen` (`persoon_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_5` FOREIGN KEY (`lokaal_id`) REFERENCES `lokaal` (`lokaal_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_6` FOREIGN KEY (`klas_id`) REFERENCES `klassen` (`klas_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_7` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`periode_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_8` FOREIGN KEY (`Jaar_id`) REFERENCES `schooljaar` (`Jaar_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `rooster_ibfk_9` FOREIGN KEY (`vak_id`) REFERENCES `vakken` (`vak_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
