-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 30 déc. 2025 à 12:22
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
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `banqueID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `uniq_agence` (`nom`,`adresse`,`banqueID`),
  KEY `fk_banque_agence` (`banqueID`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `agence`
--

INSERT INTO `agence` (`id`, `nom`, `code`, `adresse`, `banqueID`) VALUES
(15, 'Banque Extérieure d\'Algérie', 'BEA-ALG03', '11 Lot. Ben Haddadi Said, Chéraga', 39),
(16, 'BEA Constantine Gare', 'BEA-CON04', '8 Rue Ibn Khaldoun, Constantine', 39),
(17, 'Agence Les Vergers', 'CPA-ALG05', 'P3F6+859, St Charles, Kouba', 40),
(18, 'Banque CPA', 'CPA-ORAN06', 'P923+W8Q, Rue Med Khemisti, Oran', 40),
(19, 'BNA Agence Val d\'Hydra', 'BNA-ALG01', 'BLIA Office, Hydra', 38),
(20, 'BNA Agence Soumam', 'BNA-ORAN02', '04 Bd de la Soummam, Oran', 38);

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
  `date_emission` date NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `deviseID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `num_app_offre` (`num_app_offre`),
  KEY `fk_devise_appelOffre` (`deviseID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `appel_offre`
--

INSERT INTO `appel_offre` (`id`, `num_app_offre`, `date_emission`, `montant`, `deviseID`) VALUES
(18, 'AO N 12/2024/EP/LOG', '2024-03-15', 85000000.00, 16),
(19, 'AO N 07/2023/RA/TRX', '2024-06-10', 150000000.00, 16),
(21, 'AO N 22/2024/EP/DRL', '2024-10-18', 1350000.00, 17);

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
  `code` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nom_banque` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `nom_banque` (`nom_banque`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `banque`
--

INSERT INTO `banque` (`id`, `code`, `nom_banque`) VALUES
(38, 'BNA', 'Banque Nationale d\'Algérie'),
(39, 'BEA', 'Banque Extérieure d\'Algérie'),
(40, 'CPA', 'Crédit Populaire d\'Algérie');

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
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `devise`
--

INSERT INTO `devise` (`id`, `code`, `libelle`) VALUES
(16, 'DZD', 'Dinar Algérien'),
(17, 'USD', 'Dollar American'),
(18, 'EUR', 'Euro');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `garantie_soumission`
--

INSERT INTO `garantie_soumission` (`id`, `num_garantie`, `montant_garantie`, `date_emission`, `date_expiration`, `soumissionnaireID`, `agenceID`, `deviseID`, `structureID`, `appel_offreID`, `statutID`, `utilisateurID`) VALUES
(18, 2, 1.00, '2025-02-14', '2026-02-10', 12, 17, 16, 11, 21, 1, 19);

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
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `code_pays` varchar(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code_pays` (`code_pays`),
  UNIQUE KEY `Nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `pays`
--

INSERT INTO `pays` (`id`, `nom`, `code_pays`) VALUES
(1, 'Algérie', 'DZ'),
(2, 'France', 'FRA'),
(49, 'Chine', 'CN'),
(51, 'État-Unis', 'USA');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id`, `code`, `libelle`) VALUES
(1, 'ADMIN', 'Adminstrateur'),
(2, 'USER', 'Utilisateur');

-- --------------------------------------------------------

--
-- Structure de la table `soumissionnaire`
--

DROP TABLE IF EXISTS `soumissionnaire`;
CREATE TABLE IF NOT EXISTS `soumissionnaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_entreprise` varchar(255) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `telephone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(255) NOT NULL,
  `paysID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nom_entreprise` (`nom_entreprise`),
  UNIQUE KEY `telephone` (`telephone`),
  KEY `fk_pays_soumissionnaire` (`paysID`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `soumissionnaire`
--

INSERT INTO `soumissionnaire` (`id`, `nom_entreprise`, `adresse`, `telephone`, `email`, `paysID`) VALUES
(12, 'ETB TCE', 'Boumerdas', '+213657576105', 'salim@gmail.com', 1),
(14, 'ETB TCE 1', 'Boumerdas', '+213657576106', 'salim@gmail.co', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `statut`
--

INSERT INTO `statut` (`id`, `code`, `libelle`) VALUES
(1, 'ACTIF', 'Active'),
(2, 'EXPIRE', 'Expirée'),
(3, 'LIBERE', 'Libérée');

-- --------------------------------------------------------

--
-- Structure de la table `structure`
--

DROP TABLE IF EXISTS `structure`;
CREATE TABLE IF NOT EXISTS `structure` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `libelle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `structure`
--

INSERT INTO `structure` (`id`, `code`, `libelle`) VALUES
(1, 'IT', 'Technologies de l’Information'),
(11, 'ADP', 'Administration du Personnel'),
(12, 'DRH', 'Direction Ressources Humaines');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `type_amendement`
--

INSERT INTO `type_amendement` (`id`, `code`, `libelle`) VALUES
(1, 'MONTANT', 'Modification du montant'),
(2, 'DATE', 'Prolongation de date'),
(3, 'MIXTE', 'Modification montant et date');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `type_document`
--

INSERT INTO `type_document` (`id`, `code`, `libelle`) VALUES
(1, 'GARANTIE', 'Document de garantie'),
(2, 'AMENDEMENT', 'Document d\'amendement'),
(3, 'LIBERATION', 'Document de libération'),
(4, 'AUTHENTIF', 'Document d\'authentification');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `type_liberation`
--

INSERT INTO `type_liberation` (`id`, `code`, `libelle`) VALUES
(1, 'TOTALE', 'Libération totale'),
(2, 'PARTIELLE', 'Libération partielle');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `mot_de_pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `roleID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_role_utilisateur` (`roleID`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `username`, `nom`, `prenom`, `mot_de_pass`, `roleID`) VALUES
(13, 'admin@sonatrach.com', 'admin', 'admin', 'admin', '$2y$10$BYsSkm1h8Txeg8QWV0ZgwuB1VOwOGWpKS9V387691u3Ch.MyOnyGi', 1),
(18, 'KSC@sonatrach.com', 'kasdarli', 'Kasdarli', 'Sidahmed Cherif', '$2y$10$/davH.t/TkExllantTymCuWVdLYEc7wah9CxTKBq3v7H4V2Xgehgq', 2),
(19, 'admin1@sonatrach.com', 'admin1', 'admin', 'admin', '$2y$10$Pji6WE.qnseTQjTCvgWik.nOTxv3lJ2TM/HCVJLtdgJ96iaBW8Fpu', 1);

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
-- Contraintes pour la table `appel_offre`
--
ALTER TABLE `appel_offre`
  ADD CONSTRAINT `fk_devise_appelOffre` FOREIGN KEY (`deviseID`) REFERENCES `devise` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

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
