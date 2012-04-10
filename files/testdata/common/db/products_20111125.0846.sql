-- phpMyAdmin SQL Dump
-- version 3.4.7.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 25, 2011 at 08:46 AM
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
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `importDate` datetime DEFAULT NULL,
  `postProcessed` tinyint(1) NOT NULL,
  `abbrevation` varchar(50) NOT NULL,
  `gameTitle` varchar(150) NOT NULL,
  `soundNum` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `rbvNumber` varchar(50) NOT NULL,
  `oidStart` int(11) NOT NULL,
  `oidEnd` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `importDate`, `postProcessed`, `abbrevation`, `gameTitle`, `soundNum`, `visible`, `rbvNumber`, `oidStart`, `oidEnd`) VALUES
(1, 'Testprodukt', NULL, 1, 'test', '', 1, 1, '', 9999000, 9999999),
(2, 'Die Tiere Afrikas', '2011-07-12 10:36:58', 1, 'taf', 'africabook', 901, 1, '32909', 11601, 12100),
(3, 'Wald', NULL, 1, 'WAL', 'book-forrest', 1, 0, '', 0, 0),
(4, 'Bilderlexikon Tiere', NULL, 1, 'BLT', 'encyclopedia animals', 1, 0, '', 0, 0),
(5, 'Stadt', '2011-08-18 12:17:02', 1, 'sta', 'citybook', 823, 1, '32908', 11001, 11600),
(6, 'Piraten', NULL, 0, 'PIR', 'book-pirates', 1, 0, '', 0, 0),
(7, 'Der neue Fußball', '2011-09-12 12:31:23', 0, 'lfu', 'Der neue Fußball', 1286, 1, '38591', 13001, 14000),
(8, 'Ritter', NULL, 0, 'rit', 'knights', 1, 0, '32910', 0, 0),
(9, 'Der kleine Drache', '2011-11-24 18:53:12', 1, 'dra', '', 1, 1, '38592', 0, 0),
(10, 'Wörterbuch', NULL, 0, 'wrt', '', 1, 0, '', 0, 0);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
