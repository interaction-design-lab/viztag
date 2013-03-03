# ************************************************************
# Sequel Pro SQL dump
# Version 4004
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.6.10)
# Database: viztag
# Generation Time: 2013-02-26 13:24:16 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table coders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `coders`;

CREATE TABLE `coders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `verastatuses_id` int(10) unsigned DEFAULT NULL,
  `coder_id` int(10) unsigned NOT NULL,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_vstats` (`verastatuses_id`),
  KEY `comments_coders` (`coder_id`),
  CONSTRAINT `comments_coders` FOREIGN KEY (`coder_id`) REFERENCES `coders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comments_vstats` FOREIGN KEY (`verastatuses_id`) REFERENCES `verastatuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(40) NOT NULL DEFAULT '',
  `tag` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace-tag` (`namespace`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tags_verastatuses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags_verastatuses`;

CREATE TABLE `tags_verastatuses` (
  `tag_id` int(11) unsigned NOT NULL,
  `verastatus_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`,`verastatus_id`),
  KEY `to_verastats` (`verastatus_id`),
  CONSTRAINT `to_tags` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `to_verastats` FOREIGN KEY (`verastatus_id`) REFERENCES `verastatuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table verastatuses
# ------------------------------------------------------------

DROP TABLE IF EXISTS `verastatuses`;

CREATE TABLE `verastatuses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `dataset` varchar(40) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
