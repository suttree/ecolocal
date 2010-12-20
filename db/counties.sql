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
-- Table structure for table `counties`
--

DROP TABLE IF EXISTS `counties`;
CREATE TABLE `counties` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `country_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `url_name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `counties`
--


/*!40000 ALTER TABLE `counties` DISABLE KEYS */;
LOCK TABLES `counties` WRITE;
INSERT INTO `counties` VALUES (103,1,'Greater London','greater_london','TBC'),(104,1,'Kent','kent','TBC'),(105,1,'East Sussex','east_sussex','TBC'),(106,1,'Gloucestershire','gloucestershire','TBC'),(107,1,'Hampshire','hampshire','TBC'),(108,1,'Devon','devon','TBC'),(109,1,'Derbyshire','derbyshire','TBC'),(110,1,'Shropshire','shropshire','TBC'),(111,1,'Surrey','surrey','TBC'),(112,1,'Tyne and Wear','tyne_and_wear','TBC'),(113,1,'Norfolk','norfolk','TBC'),(114,1,'Lincolnshire','lincolnshire','TBC'),(115,1,'Hertfordshire','hertfordshire','TBC'),(116,1,'Isle of Man','isle_of_man','TBC'),(117,1,'London','london','The Capital'),(118,1,'Inverclyde','inverclyde','TBC'),(119,1,'Glasgow City','glasgow_city','TBC'),(120,1,'Essex','essex','TBC'),(121,1,'Staffordshire','staffordshire','TBC'),(122,1,'Northumberland','northumberland','TBC'),(123,1,'City of Edinburgh','city_of_edinburgh','TBC'),(124,1,'County Durham','county_durham','TBC'),(125,1,'Greater Manchester','greater_manchester','TBC'),(126,1,'Cornwall','cornwall','TBC'),(127,1,'Bristol','bristol','TBC'),(128,1,'Somerset','somerset','TBC'),(129,1,'Cambridgeshire','cambridgeshire','TBC'),(130,1,'Bridgend','bridgend','TBC'),(131,1,'Cardiff','cardiff','TBC'),(132,1,'Falkirk','falkirk','TBC'),(133,1,'Wiltshire','wiltshire','TBC'),(134,1,'Leicester','leicester','TBC'),(135,1,'Bedfordshire','bedfordshire','TBC'),(136,1,'Berkshire','berkshire','TBC'),(137,1,'West Midlands','west_midlands','TBC'),(138,1,'Southampton','southampton','TBC'),(139,1,'Suffolk','suffolk','TBC'),(140,1,'Hereford and Worcester','hereford_and_worcester','TBC'),(141,1,'Brighton and Hove','brighton_and_hove','TBC'),(142,1,'Merseyside','merseyside','TBC'),(143,1,'Buckinghamshire','buckinghamshire','TBC'),(144,1,'Northamptonshire','northamptonshire','TBC'),(145,1,'Milton Keynes','milton_keynes','TBC'),(146,1,'North Yorkshire','north_yorkshire','TBC'),(147,1,'Vale of Glamorgan','vale_of_glamorgan','TBC'),(148,1,'Angus','angus','TBC'),(149,1,'Dorset','dorset','TBC'),(150,1,'Cheshire','cheshire','TBC'),(151,1,'Darlington','darlington','TBC'),(152,1,'Scottish Borders','scottish_borders','TBC'),(153,1,'Nottinghamshire','nottinghamshire','TBC'),(154,1,'Shetland Islands','shetland_islands','TBC'),(155,1,'Orkney Islands','orkney_islands','TBC'),(156,1,'Highland','highland','TBC'),(157,1,'Cumbria','cumbria','TBC'),(158,1,'E Riding of Yorkshire','e_riding_of_yorkshire','TBC'),(159,1,'Warwickshire','warwickshire','TBC'),(160,1,'Leicestershire','leicestershire','TBC'),(161,1,'Newport','newport','TBC'),(162,1,'West Yorkshire','west_yorkshire','TBC'),(163,1,'West Sussex','west_sussex','TBC'),(164,1,'South Yorkshire','south_yorkshire','TBC'),(165,1,'Portsmouth','portsmouth','TBC'),(166,1,'Wolverhampton','wolverhampton','TBC'),(167,0,'Caerphilly','caerphilly','TBC'),(168,1,'Perth and Kinross','perth_and_kinross','TBC'),(169,1,'Powys','powys','TBC'),(170,1,'South Gloucestershire','south_gloucestershire','TBC'),(171,1,'Pembrokeshire','pembrokeshire','TBC'),(172,1,'Isle of Wight','isle_of_wight','TBC'),(173,1,'Lancashire','lancashire','TBC'),(174,1,'North Lincolnshire','north_lincolnshire','TBC'),(175,1,'North Somerset','north_somerset','TBC'),(176,1,'Oxfordshire','oxfordshire','TBC'),(177,1,'Dundee City','dundee_city','TBC'),(178,1,'York','york','TBC'),(179,1,'Bristol Avon','bristol_avon','TBC'),(180,1,'Blaenau Gwent','blaenau_gwent','TBC'),(181,1,'North Eart Lincolnshire','north_eart_lincolnshire','TBC'),(182,1,'East Lothian','east_lothian','TBC'),(183,1,'Argyll and Bute','argyll_and_bute','TBC'),(184,1,'Renfrewshire','renfrewshire','TBC'),(185,1,'Stirling','stirling','TBC'),(186,1,'Aberdeenshire','aberdeenshire','TBC'),(187,1,'Bath Avon','bath_avon','TBC'),(188,1,'Midlothian','midlothian','TBC'),(189,1,'Stoke-on-Trent','stokeontrent','TBC'),(190,1,'Swindon','swindon','TBC'),(191,1,'Carmarthenshire','carmarthenshire','TBC'),(198,1,'Isle of Anglesey','isle_of_anglesey','TBC'),(199,1,'West Lothian','west_lothian',''),(200,1,'Denbighshire','denbighshire',''),(201,1,'Poole','poole',''),(202,1,'County Down','county_down',''),(203,1,'Middlesbrough','middlesbrough',''),(204,1,'Dumfries and Galloway','dumfries_and_galloway',''),(205,1,'South Ayrshire','south_ayrshire',''),(206,1,'Belfast','belfast',''),(207,1,'Stockton-on-Tees','stocktonontees',''),(208,1,'Bournemouth','bournemouth',''),(209,1,'West Dunbart','west_dunbart',''),(210,1,'Kingston upon Hull','kingston_upon_hull',''),(211,1,'Jersey Channel Islands','jersey_channel_islands',''),(212,1,'Swansea','swansea',''),(213,1,'Ceredigion','ceredigion',''),(214,1,'Rhondda Cynon Taff','rhondda_cynon_taff',''),(215,1,'Monmouthshire','monmouthshire',''),(216,1,'Fife','fife','');
UNLOCK TABLES;
/*!40000 ALTER TABLE `counties` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

