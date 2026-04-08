-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 08, 2026 at 12:27 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `garantie_de_soumission`
--

-- --------------------------------------------------------

--
-- Table structure for table `garantie_soumission`
--

CREATE TABLE `garantie_soumission` (
  `id` int NOT NULL,
  `num_garantie` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `montant_garantie` decimal(15,2) NOT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date NOT NULL,
  `soumissionnaireID` int NOT NULL,
  `agenceID` int NOT NULL,
  `deviseID` int NOT NULL,
  `structureID` int NOT NULL,
  `appel_offreID` int NOT NULL,
  `statutID` int NOT NULL,
  `utilisateurID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `garantie_soumission`
--

INSERT INTO `garantie_soumission` (`id`, `num_garantie`, `montant_garantie`, `date_emission`, `date_expiration`, `soumissionnaireID`, `agenceID`, `deviseID`, `structureID`, `appel_offreID`, `statutID`, `utilisateurID`) VALUES
(3, 'GS/2026/0001', 20000.00, '2026-01-25', '2026-04-25', 4, 8, 3, 3, 3, 1, 1),
(4, 'GS/2026/0002', 12000.00, '2026-02-20', '2026-05-20', 4, 2, 2, 1, 4, 1, 1),
(5, 'GS/2026/0003', 35000.00, '2026-03-15', '2026-06-15', 2, 5, 2, 2, 5, 1, 1),
(7, 'GS/2026/0004', 25000.00, '2026-03-20', '2026-09-20', 5, 9, 2, 2, 5, 1, 1),
(8, 'GS/2026/0005', 1500000.00, '2026-03-25', '2026-06-25', 1, 4, 1, 1, 1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_garantie` (`num_garantie`),
  ADD KEY `fk_soumissionnaire_garantie` (`soumissionnaireID`),
  ADD KEY `fk_agence_garantie` (`agenceID`),
  ADD KEY `fk_devise_garantie` (`deviseID`),
  ADD KEY `fk_structure_garantie` (`structureID`),
  ADD KEY `fk_appel_garantie` (`appel_offreID`),
  ADD KEY `fk_statut_garantie` (`statutID`),
  ADD KEY `fk_utilisateur_garantie` (`utilisateurID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  ADD CONSTRAINT `fk_agence_garantie` FOREIGN KEY (`agenceID`) REFERENCES `agence` (`id`),
  ADD CONSTRAINT `fk_appel_garantie` FOREIGN KEY (`appel_offreID`) REFERENCES `appel_offre` (`id`),
  ADD CONSTRAINT `fk_devise_garantie` FOREIGN KEY (`deviseID`) REFERENCES `devise` (`id`),
  ADD CONSTRAINT `fk_soumissionnaire_garantie` FOREIGN KEY (`soumissionnaireID`) REFERENCES `soumissionnaire` (`id`),
  ADD CONSTRAINT `fk_statut_garantie` FOREIGN KEY (`statutID`) REFERENCES `statut` (`id`),
  ADD CONSTRAINT `fk_structure_garantie` FOREIGN KEY (`structureID`) REFERENCES `structure` (`id`),
  ADD CONSTRAINT `fk_utilisateur_garantie` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
