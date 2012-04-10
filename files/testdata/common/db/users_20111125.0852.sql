-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2011 at 08:52 AM
-- Server version: 5.5.9
-- PHP Version: 5.3.8

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tiptoi_drache`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `password`) VALUES
('Anna.Janke@Ravensburger.de', 'a4a2c1de8035711b0fa03fe89d8ffed1'),
('anna.kirchberg@ravensburger.de', '29295110367073297c0a776849b683ca'),
('Anuschka.Albertz@Ravensburger.de', 'e8cabf6c5091494f8f42c2340e3198c6'),
('christina.ehlgen@ravensburger.de', 'ceeb60eda8b9d2b2f51d2aea00c20b61'),
('christine.meier@ravensburger.de', '021e73a3fd261fe22f4fdc674df35339'),
('Edita.Meikis@Ravensburger.de', 'd8084f2351243423c9683098bee40ed7'),
('eike.ostermann@ravensburger.de', '942c8c0fb3f2fb0305389cbfa7f6da02'),
('imme.karbach@googlemail.com', 'a99c5dcca916988d423952d45d5e7e31'),
('info@ps-webforge.com', 'df54f1fb4664bbd32c23cf11070dab3d'),
('Julia.Scheit@Ravensburger.de', '06a10119c0f31ca3d1e2edc08c6b4b98'),
('mira.kramer@ravensburger.de', 'bc5c7cdf106d6bd19729a9140d593333'),
('p.scheit@ps-webforge.com', '583cdd008f2ea237bfe4d39a2d827f42'),
('Sybille.siegmund@ravensburger.de', '393ca512d516ef037bc7ad7916158de4');
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
