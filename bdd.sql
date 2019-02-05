-- phpMyAdmin SQL Dump
-- version 4.1.14.8
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Lun 04 Février 2019 à 14:32
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
-- Structure de la table `album`
--

CREATE TABLE IF NOT EXISTS `album` (
  `idAlbum` int(10) NOT NULL AUTO_INCREMENT,
  `nomAlbum` varchar(255) CHARACTER SET latin1 NOT NULL,
  `imageAlbum` varchar(255) CHARACTER SET latin1 NOT NULL,
  `annéeAlbum` int(10) NOT NULL,
  PRIMARY KEY (`idAlbum`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Contenu de la table `album`
--

INSERT INTO `album` (`idAlbum`, `nomAlbum`, `imageAlbum`, `annéeAlbum`) VALUES
(1, 'Let there be fire', 'https://f4.bcbits.com/img/a3614231628_10.jpg', 2018),
(2, 'Godhunter', 'http://www.gap-tallard-durance.fr/fileadmin/_processed_/5/a/csm_AdobeStock_cle_sol_03179e2243.jpg', 2019),
(3, 'Balavoine sur scène', 'https://img.cdandlp.com/2016/11/imgL/3058765433.jpg', 1981),
(4, 'Daniel Balavoine: Les 50 Plus Belles Chansons', 'https://static.fnac-static.com/multimedia/images_produits/ZoomPE/0/7/7/0600753074770/tsp20130831051645/Les-50-plus-belles-chansons.jpg', 2008),
(5, 'D''eux', 'https://is1-ssl.mzstatic.com/image/thumb/Music/3a/4e/ff/mzi.eciahqkd.jpg/268x0w.jpg', 1995);

-- --------------------------------------------------------

--
-- Structure de la table `artiste`
--

CREATE TABLE IF NOT EXISTS `artiste` (
  `idArtiste` int(10) NOT NULL AUTO_INCREMENT,
  `nomArtiste` varchar(255) NOT NULL,
  `prénomArtiste` varchar(255) NOT NULL,
  PRIMARY KEY (`idArtiste`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `artiste`
--

INSERT INTO `artiste` (`idArtiste`, `nomArtiste`, `prénomArtiste`) VALUES
(1, 'Aviators', ''),
(2, 'Balavoine', 'Daniel'),
(3, 'Workman', 'Nanette'),
(4, 'Dion', 'Céline');

-- --------------------------------------------------------

--
-- Structure de la table `a_joué_album`
--

CREATE TABLE IF NOT EXISTS `a_joué_album` (
  `idAJoueAlbum` int(10) NOT NULL AUTO_INCREMENT,
  `idAlbum` int(10) NOT NULL,
  `idArtiste` int(10) NOT NULL,
  PRIMARY KEY (`idAJoueAlbum`),
  KEY `idAlbum` (`idAlbum`),
  KEY `idArtiste` (`idArtiste`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `a_joué_album`
--

INSERT INTO `a_joué_album` (`idAJoueAlbum`, `idAlbum`, `idArtiste`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2),
(4, 4, 2),
(5, 5, 4);

-- --------------------------------------------------------

--
-- Structure de la table `a_joué_piste`
--

CREATE TABLE IF NOT EXISTS `a_joué_piste` (
  `idAJouePiste` int(10) NOT NULL AUTO_INCREMENT,
  `idPiste` int(10) NOT NULL,
  `idArtiste` int(10) NOT NULL,
  PRIMARY KEY (`idAJouePiste`),
  KEY `idPiste` (`idPiste`),
  KEY `idArtiste` (`idArtiste`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `a_joué_piste`
--

INSERT INTO `a_joué_piste` (`idAJouePiste`, `idPiste`, `idArtiste`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2),
(4, 3, 3),
(5, 4, 4);

-- --------------------------------------------------------

--
-- Structure de la table `est_du_genre_album`
--

CREATE TABLE IF NOT EXISTS `est_du_genre_album` (
  `idEstDuGenreAlbum` int(10) NOT NULL AUTO_INCREMENT,
  `idAlbum` int(10) NOT NULL,
  `idGenre` int(10) NOT NULL,
  PRIMARY KEY (`idEstDuGenreAlbum`),
  KEY `idAlbum` (`idAlbum`),
  KEY `idGenre` (`idGenre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Contenu de la table `est_du_genre_album`
--

INSERT INTO `est_du_genre_album` (`idEstDuGenreAlbum`, `idAlbum`, `idGenre`) VALUES
(1, 1, 6),
(2, 2, 6),
(3, 1, 1),
(4, 2, 1),
(5, 3, 9),
(6, 4, 9),
(7, 5, 9);

-- --------------------------------------------------------

--
-- Structure de la table `est_du_genre_piste`
--

CREATE TABLE IF NOT EXISTS `est_du_genre_piste` (
  `idEstDuGenrePiste` int(10) NOT NULL AUTO_INCREMENT,
  `idPiste` int(10) NOT NULL,
  `idGenre` int(10) NOT NULL,
  PRIMARY KEY (`idEstDuGenrePiste`),
  KEY `idPiste` (`idPiste`),
  KEY `idGenre` (`idGenre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Contenu de la table `est_du_genre_piste`
--

INSERT INTO `est_du_genre_piste` (`idEstDuGenrePiste`, `idPiste`, `idGenre`) VALUES
(1, 1, 1),
(2, 1, 6),
(3, 2, 1),
(4, 2, 6),
(5, 3, 9),
(6, 4, 9);

-- --------------------------------------------------------

--
-- Structure de la table `fait_partie`
--

CREATE TABLE IF NOT EXISTS `fait_partie` (
  `idFaitPartie` int(10) NOT NULL AUTO_INCREMENT,
  `idPiste` int(10) NOT NULL,
  `idAlbum` int(10) NOT NULL,
  PRIMARY KEY (`idFaitPartie`),
  KEY `idPiste` (`idPiste`),
  KEY `idAlbum` (`idAlbum`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Contenu de la table `fait_partie`
--

INSERT INTO `fait_partie` (`idFaitPartie`, `idPiste`, `idAlbum`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 3, 4),
(5, 4, 5);

-- --------------------------------------------------------

--
-- Structure de la table `file`
--

CREATE TABLE IF NOT EXISTS `file` (
  `idFile` int(10) NOT NULL AUTO_INCREMENT,
  `idPiste` int(10) NOT NULL,
  PRIMARY KEY (`idFile`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=135 ;

--
-- Contenu de la table `file`
--

INSERT INTO `file` (`idFile`, `idPiste`) VALUES
(123, 1),
(124, 2),
(125, 3),
(126, 2),
(127, 3),
(128, 4),
(129, 2),
(130, 4),
(131, 2),
(132, 3),
(133, 1),
(134, 4);

-- --------------------------------------------------------

--
-- Structure de la table `genre`
--

CREATE TABLE IF NOT EXISTS `genre` (
  `idGenre` int(10) NOT NULL AUTO_INCREMENT,
  `nomGenre` varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`idGenre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Contenu de la table `genre`
--

INSERT INTO `genre` (`idGenre`, `nomGenre`) VALUES
(1, 'Rock'),
(2, 'Métal'),
(3, 'Disco'),
(4, 'Blues'),
(5, 'Country'),
(6, 'Synth'),
(7, 'Pop'),
(8, 'Rap'),
(9, 'Chanson française');

-- --------------------------------------------------------

--
-- Structure de la table `piste`
--

CREATE TABLE IF NOT EXISTS `piste` (
  `idPiste` int(10) NOT NULL AUTO_INCREMENT,
  `nomPiste` varchar(255) CHARACTER SET latin1 NOT NULL,
  `imagePiste` varchar(255) CHARACTER SET latin1 NOT NULL,
  `annéePiste` int(10) NOT NULL,
  PRIMARY KEY (`idPiste`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Contenu de la table `piste`
--

INSERT INTO `piste` (`idPiste`, `nomPiste`, `imagePiste`, `annéePiste`) VALUES
(1, 'Paralyzed', 'https://i.ytimg.com/vi/-6fcs8uE5Q4/maxresdefault.jpg', 2018),
(2, 'Endgame', 'https://f4.bcbits.com/img/a3456776437_10.jpg', 2018),
(3, 'Quand on arrive en ville', 'https://images-na.ssl-images-amazon.com/images/I/51ojtUtrxHL._SX355_.jpg', 1978),
(4, 'Pour que tu m''aimes encore', 'https://upload.wikimedia.org/wikipedia/en/e/e8/Pour_que_tu_m%27aimes_encore_single.jpg', 1995);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `a_joué_album`
--
ALTER TABLE `a_joué_album`
  ADD CONSTRAINT `a_joué_album_ibfk_1` FOREIGN KEY (`idAlbum`) REFERENCES `album` (`idAlbum`),
  ADD CONSTRAINT `a_joué_album_ibfk_2` FOREIGN KEY (`idArtiste`) REFERENCES `artiste` (`idArtiste`);

--
-- Contraintes pour la table `a_joué_piste`
--
ALTER TABLE `a_joué_piste`
  ADD CONSTRAINT `a_joué_piste_ibfk_1` FOREIGN KEY (`idPiste`) REFERENCES `piste` (`idPiste`),
  ADD CONSTRAINT `a_joué_piste_ibfk_2` FOREIGN KEY (`idArtiste`) REFERENCES `artiste` (`idArtiste`);

--
-- Contraintes pour la table `est_du_genre_album`
--
ALTER TABLE `est_du_genre_album`
  ADD CONSTRAINT `est_du_genre_album_ibfk_1` FOREIGN KEY (`idAlbum`) REFERENCES `album` (`idAlbum`),
  ADD CONSTRAINT `est_du_genre_album_ibfk_2` FOREIGN KEY (`idGenre`) REFERENCES `genre` (`idGenre`);

--
-- Contraintes pour la table `est_du_genre_piste`
--
ALTER TABLE `est_du_genre_piste`
  ADD CONSTRAINT `est_du_genre_piste_ibfk_1` FOREIGN KEY (`idPiste`) REFERENCES `piste` (`idPiste`),
  ADD CONSTRAINT `est_du_genre_piste_ibfk_2` FOREIGN KEY (`idGenre`) REFERENCES `genre` (`idGenre`);

--
-- Contraintes pour la table `fait_partie`
--
ALTER TABLE `fait_partie`
  ADD CONSTRAINT `fait_partie_ibfk_1` FOREIGN KEY (`idPiste`) REFERENCES `piste` (`idPiste`),
  ADD CONSTRAINT `fait_partie_ibfk_2` FOREIGN KEY (`idAlbum`) REFERENCES `album` (`idAlbum`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;