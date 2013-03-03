# ************************************************************
# Sequel Pro SQL dump
# Version 4004
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: localhost (MySQL 5.6.10)
# Database: viztag
# Generation Time: 2013-02-26 13:24:45 +0000
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

LOCK TABLES `coders` WRITE;
/*!40000 ALTER TABLE `coders` DISABLE KEYS */;

INSERT INTO `coders` (`id`, `name`, `password`)
VALUES
	(1,'admin','e5e9fa1ba31ecd1ae84f75caaa474f3a663f05f4'),
	(2,'test','5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8');

/*!40000 ALTER TABLE `coders` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table comments
# ------------------------------------------------------------



# Dump of table tags
# ------------------------------------------------------------

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;

INSERT INTO `tags` (`id`, `namespace`, `tag`)
VALUES
	(5,'composition','mugshot'),
	(6,'composition','still-life'),
	(1,'content','exercise'),
	(2,'content','nutrition');

/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tags_verastatuses
# ------------------------------------------------------------



# Dump of table verastatuses
# ------------------------------------------------------------

LOCK TABLES `verastatuses` WRITE;
/*!40000 ALTER TABLE `verastatuses` DISABLE KEYS */;

INSERT INTO `verastatuses` (`id`, `status_id`, `user_id`, `created`, `dataset`, `image_path`, `note`)
VALUES
	(1,16285,107,'2013-02-21 22:29:18','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/85ec0b959149b2e1e266f92e0618d6cdd08d4dda.jpg','Great salad'),
	(2,15707,192,'2013-02-19 20:17:56','VERAPLUS_BETATESTERS','/img/user-uploads/default/192/70207882ba5c30b263bcb754277fb0976a5b3c77.jpg','Testing a post from old Vera .'),
	(3,12207,107,'2013-01-15 16:57:45','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/6d8d2718df99a0cc9299febf5aea0cb2b5d3aacf.jpg',''),
	(4,12203,107,'2012-12-03 18:42:07','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/a630bd6ee471cd25ad211acae0e9dd183c488b59.jpg',''),
	(5,9893,192,'2012-11-13 18:54:41','VERAPLUS_BETATESTERS','/img/user-uploads/default/192/979bad16bf3066d9654114349bc2d8f89e009417.jpg','This was a long time ago. I\'m not sure what that was, but it was probably yummy and unhealthy. Chinese food is that way. Long post test. Mhm'),
	(6,8981,107,'2012-11-08 19:33:31','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/b7ca9dcd3a982480a537eca94b192d38cb216fc4.jpg',''),
	(7,8980,107,'2012-11-08 19:32:15','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/3b73463bc3c01c8d32830bbfd44b68fdfcd4c746.jpg','deadmau5 test'),
	(8,8979,201,'2012-11-08 19:28:15','VERAPLUS_BETATESTERS','/img/user-uploads/default/201/0bd597631316592718485aaf3d92a9470c065554.jpg','ice'),
	(9,8975,107,'2012-11-08 19:11:40','VERAPLUS_BETATESTERS','/img/user-uploads/default/107/27f415fb2746e205a293f915192b5b7e516787b4.jpg','pumpkinning - jp style'),
	(10,8853,201,'2012-11-07 21:09:21','VERAPLUS_BETATESTERS','/img/user-uploads/default/201/0417a08f2b980157f78858b7db69f14c3c788692.jpg','books'),
	(11,8852,201,'2012-11-07 21:08:47','VERAPLUS_BETATESTERS','/img/user-uploads/default/201/42d55d54ccc451135a78b6b27e55d3aabb38658d.jpg','Cornell vs Harvard'),
	(12,8851,201,'2012-11-07 21:05:47','VERAPLUS_BETATESTERS','/img/user-uploads/default/201/79e1fded6ed5b4bc0b3e33eb54fb077a07f5911e.jpg','go Google');

/*!40000 ALTER TABLE `verastatuses` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
