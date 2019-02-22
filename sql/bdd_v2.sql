-- phpMyAdmin SQL Dump
-- version 4.1.14.8
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 18 Février 2019 à 17:55
-- Version du serveur :  5.1.73
-- Version de PHP :  7.0.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `rimet2u`
--

-- --------------------------------------------------------

--
-- Structure de la table `jukebox`
--

CREATE TABLE IF NOT EXISTS `jukebox` (
  `idJukebox` int(10) NOT NULL AUTO_INCREMENT,
  `idBibliotheque` int(10) NOT NULL,
  `tokenActivation` varchar(100) NOT NULL,
  `estActive` int(10) NOT NULL DEFAULT '0',
  `qr_code` varchar(100) NOT NULL,
  `nomClient` varchar(255) NOT NULL,
  `mailClient` varchar(255) NOT NULL,
  `adresseClient` varchar(255) NOT NULL,
  PRIMARY KEY (`idJukebox`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `jukebox`
--

INSERT INTO `jukebox` (`idJukebox`, `idBibliotheque`, `tokenActivation`, `estActive`, `qr_code`, `nomClient`, `mailClient`, `adresseClient`) VALUES
(1, 1, 'token', 1, 'qrcode', '', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
