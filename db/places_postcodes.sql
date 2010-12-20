-- MySQL dump 10.9
--
-- Host: localhost    Database: ecolocal_development
-- ------------------------------------------------------
-- Server version	4.1.13-standard

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

--
-- Table structure for table `places_postcodes`
--

DROP TABLE IF EXISTS `places_postcodes`;
CREATE TABLE `places_postcodes` (
  `place_id` bigint(10) unsigned NOT NULL default '0',
  `postcode_id` bigint(10) unsigned NOT NULL default '0',
  KEY `place` (`place_id`),
  KEY `postcode` (`postcode_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `places_postcodes`
--


/*!40000 ALTER TABLE `places_postcodes` DISABLE KEYS */;
LOCK TABLES `places_postcodes` WRITE;
UNLOCK TABLES;
/*!40000 ALTER TABLE `places_postcodes` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

