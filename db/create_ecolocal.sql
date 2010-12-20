-- MySQL dump 10.9
--
-- Host: localhost    Database: suttree_ecolocal_production
-- ------------------------------------------------------
-- Server version	4.1.16-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `article_ratings`
--

DROP TABLE IF EXISTS `article_ratings`;
CREATE TABLE `article_ratings` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `article_id` bigint(20) unsigned NOT NULL default '0',
  `rating` int(1) NOT NULL default '0',
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user_id`),
  KEY `article` (`article_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `normalised_title` varchar(255) NOT NULL default '',
  `section` varchar(255) NOT NULL default '',
  `body` text,
  `url` varchar(255) default NULL,
  `image` varchar(255) default NULL,
  `audio` varchar(255) default NULL,
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `articles_counties`
--

DROP TABLE IF EXISTS `articles_counties`;
CREATE TABLE `articles_counties` (
  `article_id` bigint(10) unsigned NOT NULL default '0',
  `county_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `articles_countries`
--

DROP TABLE IF EXISTS `articles_countries`;
CREATE TABLE `articles_countries` (
  `article_id` bigint(10) unsigned NOT NULL default '0',
  `country_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `articles_places`
--

DROP TABLE IF EXISTS `articles_places`;
CREATE TABLE `articles_places` (
  `article_id` bigint(10) unsigned NOT NULL default '0',
  `place_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `comment_ratings`
--

DROP TABLE IF EXISTS `comment_ratings`;
CREATE TABLE `comment_ratings` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `comment_id` bigint(20) unsigned NOT NULL default '0',
  `rating` int(1) unsigned NOT NULL default '0',
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `user` (`user_id`),
  KEY `comment` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `article_id` bigint(20) unsigned NOT NULL default '0',
  `parent_id` bigint(20) unsigned default NULL,
  `title` varchar(255) NOT NULL default '',
  `body` text,
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `author` (`user_id`),
  KEY `article` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
-- Table structure for table `counties_groups`
--

DROP TABLE IF EXISTS `counties_groups`;
CREATE TABLE `counties_groups` (
  `county_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `url_name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `owner_id` bigint(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `url_name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `user` (`owner_id`),
  KEY `group_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `groups_users`
--

DROP TABLE IF EXISTS `groups_users`;
CREATE TABLE `groups_users` (
  `user_id` bigint(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `local_events`
--

DROP TABLE IF EXISTS `local_events`;
CREATE TABLE `local_events` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `article_id` int(10) unsigned NOT NULL default '0',
  `start_date` datetime default NULL,
  `end_date` datetime default NULL,
  `event_type` varchar(255) default NULL,
  `recurrence` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `mint__config`
--

DROP TABLE IF EXISTS `mint__config`;
CREATE TABLE `mint__config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cfg` text NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `mint_geo`
--

DROP TABLE IF EXISTS `mint_geo`;
CREATE TABLE `mint_geo` (
  `id` int(11) NOT NULL auto_increment,
  `ip` int(10) NOT NULL default '0',
  `country_abrv` char(3) NOT NULL default '',
  `country` varchar(25) NOT NULL default '',
  `city` varchar(40) NOT NULL default '',
  `latitude` float NOT NULL default '0',
  `longitude` float NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `mint_visit`
--

DROP TABLE IF EXISTS `mint_visit`;
CREATE TABLE `mint_visit` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `dt` int(10) unsigned NOT NULL default '0',
  `ip_long` int(10) NOT NULL default '0',
  `referer` varchar(255) NOT NULL default '',
  `referer_checksum` int(10) NOT NULL default '0',
  `domain_checksum` int(10) NOT NULL default '0',
  `referer_is_local` tinyint(1) NOT NULL default '-1',
  `resource` varchar(255) NOT NULL default '',
  `resource_checksum` int(10) NOT NULL default '0',
  `resource_title` varchar(255) NOT NULL default '',
  `search_terms` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `dt` (`dt`),
  KEY `ip_long` (`ip_long`),
  KEY `referer_checksum` (`referer_checksum`),
  KEY `domain_checksum` (`domain_checksum`),
  KEY `referer_is_local` (`referer_is_local`),
  KEY `resource_checksum` (`resource_checksum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `places`
--

DROP TABLE IF EXISTS `places`;
CREATE TABLE `places` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `county_id` int(10) unsigned NOT NULL default '0',
  `postcode_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `url_name` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `county` (`county_id`),
  KEY `postcode` (`postcode_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `postcodes`
--

DROP TABLE IF EXISTS `postcodes`;
CREATE TABLE `postcodes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `postcode` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` bigint(20) unsigned NOT NULL default '0',
  `remember_me` varchar(32) NOT NULL default '',
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `remember_me` (`remember_me`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `tags_articles`
--

DROP TABLE IF EXISTS `tags_articles`;
CREATE TABLE `tags_articles` (
  `tag_id` bigint(20) unsigned NOT NULL default '0',
  `article_id` bigint(20) unsigned NOT NULL default '0',
  `user_id` bigint(20) unsigned NOT NULL default '0',
  KEY `article` (`article_id`),
  KEY `tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `nickname` varchar(100) NOT NULL default '',
  `forename` varchar(255) NOT NULL default '',
  `surname` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `date_of_birth` date default NULL,
  `gender` char(1) default NULL,
  `country` varchar(255) default NULL,
  `description` text,
  `subscribe` int(1) NOT NULL default '0',
  `activation_code` varchar(255) default NULL,
  `activation_date` datetime default NULL,
  `updated_on` datetime default NULL,
  `created_on` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `login_via_email` (`email`,`password`),
  KEY `login_via_nickname` (`nickname`,`password`),
  KEY `activation` (`activation_code`),
  KEY `nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

