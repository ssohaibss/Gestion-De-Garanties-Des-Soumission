-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 20 déc. 2025 à 14:51
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `agence`
--

INSERT INTO `agence` (`id`, `nom`, `code`, `adresse`, `banqueID`) VALUES
(1, 'cheraga', 'A12', 'cheraga', 18),
(2, 'xlcdc', 'yy', 'ccc', 18),
(9, 'cheraga', 'A13', 'cheraga', 18);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `appel_offre`
--

INSERT INTO `appel_offre` (`id`, `num_app_offre`) VALUES
(1, '3999'),
(2, '6666');

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
  `code` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nom_banque` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `banque`
--

INSERT INTO `banque` (`id`, `code`, `nom_banque`) VALUES
(18, 'B12', 'Crédit Populaire d\'Algérie'),
(20, 'B11', 'Crédit');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `devise`
--

INSERT INTO `devise` (`id`, `code`, `libelle`) VALUES
(1, 'EUR', 'Euro'),
(2, 'USD', 'Dollar Américain'),
(3, 'DZD', 'Dinar Algérien'),
(9, 'S', 'ss');

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `garantie_soumission`
--

INSERT INTO `garantie_soumission` (`id`, `num_garantie`, `montant_garantie`, `date_emission`, `date_expiration`, `soumissionnaireID`, `agenceID`, `deviseID`, `structureID`, `appel_offreID`, `statutID`, `utilisateurID`) VALUES
(13, 5757, 200000.00, '2025-12-14', '2026-12-14', 1, 1, 1, 1, 1, 1, 3),
(16, 48859, 443330.00, '4444-04-12', '5555-03-31', 3, 1, 2, 1, 2, 1, 3);

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
  UNIQUE KEY `code_pays` (`code_pays`),
  UNIQUE KEY `Nom` (`Nom`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `pays`
--

INSERT INTO `pays` (`id`, `Nom`, `code_pays`) VALUES
(1, 'algeria', 'DZ'),
(2, 'france', 'FR');

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
  `telephone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `paysID` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pays_soumissionnaire` (`paysID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `soumissionnaire`
--

INSERT INTO `soumissionnaire` (`id`, `nom_entreprise`, `adresse`, `telephone`, `email`, `paysID`) VALUES
(1, '7ds', 'sioddsoifdsio', '0507993831', '7ds@tristan.hell', 1),
(2, 'lol', 'jjjjjjjjjjj', '0556783902', 'ahri@mid.ff', 1),
(3, 'chazyl', 'sdsaklds', '0789912934', 'chznidsk@bourak.com', 1),
(4, 'Sohaib2', 'sss', '+213 657576105', 'sohaib@gmail.com', 1),
(5, 'Sohaib2', '123', '+213 657576105', 'sohaib@gmail.com', 1),
(6, '12345668899', '12', '+213 657576105', 'sohaib@gmail.com', 1),
(7, 'xdxd', 'xd', '+213 657576105', 'bchazyl@gmail.com', 1);

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
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `structure`
--

INSERT INTO `structure` (`id`, `code`, `libelle`) VALUES
(1, 'IT', 'Informatique'),
(2, 'tt', 'tttt'),
(4, 'zzdad', 'ss'),
(5, 'ASASA', 'sssssss');

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
  `nom` varchar(50) NOT NULL,
  `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `roleID` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nom` (`nom`),
  KEY `fk_role_utilisateur` (`roleID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `nom`, `mot_de_passe`, `roleID`) VALUES
(3, 'admin@gmail.com', 'admin', '$2y$10$BYsSkm1h8Txeg8QWV0ZgwuB1VOwOGWpKS9V387691u3Ch.MyOnyGi', 1),
(4, 'sohaibtata13@gmail.com', 'sohaib', '$2y$10$NoM6J0T6rIFlwN1C4swemu/QCIm0.SDg1hQb76G5.RFwoEcH2rwhW', 1),
(5, 'bchazyl@gmail.com', 'belabed', '$2y$10$ojI67TuBzmdbptK8KUyqFOV/owqZqh.5noI8z3XoNMn6.UhzTYalO', 2),
(12, 'admin1@gmail.com', 't', '$2y$10$iMyMqm3Rpv0T29Asvledm.x6t2YfovFjZhgp5VhCkKVEPL9iwDKc2', 1);

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
