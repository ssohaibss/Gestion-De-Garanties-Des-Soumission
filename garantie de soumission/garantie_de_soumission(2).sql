-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 14 déc. 2025 à 13:29
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `garantie_de_soumission`
--

-- --------------------------------------------------------

--
-- Structure de la table `agence`
--

DROP TABLE IF EXISTS `agence`;
CREATE TABLE IF NOT EXISTS `agence` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `code` varchar(11) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `banqueID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_banque_agence` (`banqueID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `amendement`
--

DROP TABLE IF EXISTS `amendement`;
CREATE TABLE IF NOT EXISTS `amendement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_amendement` int NOT NULL,
  `date_amendement` date NOT NULL,
  `nouveau_montant` decimal(15,2) DEFAULT NULL,
  `nouvelle_date_expiration` date DEFAULT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_amendementID` int NOT NULL,
  `utilisateurID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_amendement` (`num_amendement`),
  KEY `fk_garantie_amendement` (`garantie_soumissionID`),
  KEY `fk_TYPAm_amendement` (`type_amendementID`),
  KEY `fk_utilisateur_amendement` (`utilisateurID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `appel_offre`
--

DROP TABLE IF EXISTS `appel_offre`;
CREATE TABLE IF NOT EXISTS `appel_offre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_app_offre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_app_offre` (`num_app_offre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `authentification`
--

DROP TABLE IF EXISTS `authentification`;
CREATE TABLE IF NOT EXISTS `authentification` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_authentification` int NOT NULL,
  `date_authentification` date NOT NULL,
  `garantie_soumissionID` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_garantie_authentification` (`garantie_soumissionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `banque`
--

DROP TABLE IF EXISTS `banque`;
CREATE TABLE IF NOT EXISTS `banque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `nom_banque` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `devise`
--

DROP TABLE IF EXISTS `devise`;
CREATE TABLE IF NOT EXISTS `devise` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

DROP TABLE IF EXISTS `document`;
CREATE TABLE IF NOT EXISTS `document` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_document` varchar(255) NOT NULL,
  `chemin_access` varchar(255) NOT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_documentID` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_garantie_document` (`garantie_soumissionID`),
  KEY `fk_TYPDoc_document` (`type_documentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `garantie_soumission`
--

DROP TABLE IF EXISTS `garantie_soumission`;
CREATE TABLE IF NOT EXISTS `garantie_soumission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_garantie` int NOT NULL,
  `montant_garantie` decimal(15,2) NOT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date NOT NULL,
  `soumissionnaireID` int NOT NULL,
  `agenceID` int NOT NULL,
  `deviseID` int NOT NULL,
  `structureID` int NOT NULL,
  `appel_offreID` int NOT NULL,
  `statutID` int NOT NULL,
  `utilisateurID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_garantie` (`num_garantie`),
  KEY `fk_soumissionnaire_garantie` (`soumissionnaireID`),
  KEY `fk_agence_garantie` (`agenceID`),
  KEY `fk_devise_garantie` (`deviseID`),
  KEY `fk_structure_garantie` (`structureID`),
  KEY `fk_appel_garantie` (`appel_offreID`),
  KEY `fk_statut_garantie` (`statutID`),
  KEY `fk_utilisateur_garantie` (`utilisateurID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `liberation`
--

DROP TABLE IF EXISTS `liberation`;
CREATE TABLE IF NOT EXISTS `liberation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num_liberation` int NOT NULL,
  `montant_libere` decimal(15,2) NOT NULL,
  `date_liberation` date NOT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_liberationID` int NOT NULL,
  `utilisateurID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_liberation` (`num_liberation`),
  KEY `fk_garantie_liberation` (`garantie_soumissionID`),
  KEY `fk_TYPLib_liberation` (`type_liberationID`),
  KEY `fk_utilisateur_liberation` (`utilisateurID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pays`
--

DROP TABLE IF EXISTS `pays`;
CREATE TABLE IF NOT EXISTS `pays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nom` varchar(255) NOT NULL,
  `code_pays` varchar(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code_pays` (`code_pays`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `soumissionnaire`
--

DROP TABLE IF EXISTS `soumissionnaire`;
CREATE TABLE IF NOT EXISTS `soumissionnaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_entreprise` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `telephone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `paysID` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pays_soumissionnaire` (`paysID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `statut`
--

DROP TABLE IF EXISTS `statut`;
CREATE TABLE IF NOT EXISTS `statut` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `structure`
--

DROP TABLE IF EXISTS `structure`;
CREATE TABLE IF NOT EXISTS `structure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_amendement`
--

DROP TABLE IF EXISTS `type_amendement`;
CREATE TABLE IF NOT EXISTS `type_amendement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_document`
--

DROP TABLE IF EXISTS `type_document`;
CREATE TABLE IF NOT EXISTS `type_document` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `type_liberation`
--

DROP TABLE IF EXISTS `type_liberation`;
CREATE TABLE IF NOT EXISTS `type_liberation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(11) NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `mot_de_pass` varchar(255) NOT NULL,
  `roleID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_role_utilisateur` (`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `agence`
--
ALTER TABLE `agence`
  ADD CONSTRAINT `fk_banque_agence` FOREIGN KEY (`banqueID`) REFERENCES `banque` (`id`);

--
-- Contraintes pour la table `amendement`
--
ALTER TABLE `amendement`
  ADD CONSTRAINT `fk_garantie_amendement` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPAm_amendement` FOREIGN KEY (`type_amendementID`) REFERENCES `type_amendement` (`id`),
  ADD CONSTRAINT `fk_utilisateur_amendement` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `authentification`
--
ALTER TABLE `authentification`
  ADD CONSTRAINT `fk_garantie_authentification` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`);

--
-- Contraintes pour la table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_garantie_document` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPDoc_document` FOREIGN KEY (`type_documentID`) REFERENCES `type_document` (`id`);

--
-- Contraintes pour la table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  ADD CONSTRAINT `fk_agence_garantie` FOREIGN KEY (`agenceID`) REFERENCES `agence` (`id`),
  ADD CONSTRAINT `fk_appel_garantie` FOREIGN KEY (`appel_offreID`) REFERENCES `appel_offre` (`id`),
  ADD CONSTRAINT `fk_devise_garantie` FOREIGN KEY (`deviseID`) REFERENCES `devise` (`id`),
  ADD CONSTRAINT `fk_soumissionnaire_garantie` FOREIGN KEY (`soumissionnaireID`) REFERENCES `soumissionnaire` (`id`),
  ADD CONSTRAINT `fk_statut_garantie` FOREIGN KEY (`statutID`) REFERENCES `statut` (`id`),
  ADD CONSTRAINT `fk_structure_garantie` FOREIGN KEY (`structureID`) REFERENCES `structure` (`id`),
  ADD CONSTRAINT `fk_utilisateur_garantie` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `liberation`
--
ALTER TABLE `liberation`
  ADD CONSTRAINT `fk_garantie_liberation` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPLib_liberation` FOREIGN KEY (`type_liberationID`) REFERENCES `type_liberation` (`id`),
  ADD CONSTRAINT `fk_utilisateur_liberation` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `soumissionnaire`
--
ALTER TABLE `soumissionnaire`
  ADD CONSTRAINT `fk_pays_soumissionnaire` FOREIGN KEY (`paysID`) REFERENCES `pays` (`id`);

--
-- Contraintes pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_role_utilisateur` FOREIGN KEY (`roleID`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
