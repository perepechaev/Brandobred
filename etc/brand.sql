-- MySQL dump 10.11
--
-- Host: localhost    Database: brand
-- ------------------------------------------------------
-- Server version	5.0.89-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `brand`
--

DROP TABLE IF EXISTS `brand`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` enum('dirty','approve','disapprove','trash') NOT NULL default 'dirty',
  `company` varchar(255) default NULL,
  `priority` int(11) NOT NULL default '50',
  `industry_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand`
--

LOCK TABLES `brand` WRITE;
/*!40000 ALTER TABLE `brand` DISABLE KEYS */;
INSERT INTO `brand` VALUES (1,'2293667581.jpg','noname','disapprove',NULL,0,NULL),(2,'2293781887.jpg','noname','disapprove',NULL,0,NULL),(3,'2293782257.jpg','noname','disapprove',NULL,0,NULL),(4,'2293782549.jpg','noname','disapprove',NULL,0,NULL),(5,'2293943065.jpg','noname','disapprove',NULL,0,NULL),(6,'2293943099.jpg','noname','disapprove',NULL,0,NULL),(7,'2293943137.jpg','noname','disapprove',NULL,0,NULL),(8,'2293943163.jpg','noname','disapprove',NULL,0,NULL),(9,'2293943193.jpg','noname','disapprove',NULL,0,NULL),(10,'2293949297.jpg','noname','disapprove',NULL,0,NULL),(11,'2294028719.jpg','noname','disapprove',NULL,0,NULL),(12,'2294142980.jpg','noname','disapprove',NULL,0,NULL),(13,'2294435130.jpg','noname','disapprove',NULL,0,NULL),(14,'2294571850.jpg','noname','disapprove',NULL,0,NULL),(15,'2294733076.jpg','noname','disapprove',NULL,0,NULL),(16,'2477957243.jpg','noname','disapprove',NULL,0,NULL),(17,'2478052271.jpg','noname','disapprove',NULL,0,NULL),(18,'2478053601.jpg','noname','disapprove',NULL,0,NULL),(19,'2478069959.jpg','noname','disapprove',NULL,0,NULL),(20,'2478069979.jpg','noname','disapprove',NULL,0,NULL),(21,'2478070051.jpg','noname','disapprove',NULL,0,NULL),(22,'2478070069.jpg','noname','disapprove',NULL,0,NULL),(23,'2478070101.jpg','noname','disapprove',NULL,0,NULL),(24,'2478087493.jpg','noname','disapprove',NULL,0,NULL),(25,'2478087527.jpg','noname','disapprove',NULL,0,NULL),(26,'2478087571.jpg','noname','disapprove',NULL,0,NULL),(27,'2478167697.jpg','noname','disapprove',NULL,0,NULL),(28,'2478167737.jpg','noname','disapprove',NULL,0,NULL),(29,'2478460003.jpg','noname','disapprove',NULL,0,NULL),(30,'2478460029.jpg','noname','disapprove',NULL,0,NULL),(31,'2478462907.jpg','noname','disapprove',NULL,0,NULL),(32,'2478463007.jpg','noname','disapprove',NULL,0,NULL),(33,'2478535057.jpg','noname','disapprove',NULL,0,NULL),(34,'2478768884.jpg','noname','disapprove',NULL,0,NULL),(35,'2478863632.jpg','noname','disapprove',NULL,0,NULL),(36,'2478864098.jpg','noname','disapprove',NULL,0,NULL),(37,'2478864238.jpg','noname','disapprove',NULL,0,NULL),(38,'2478881186.jpg','noname','disapprove',NULL,0,NULL),(39,'2478881222.jpg','noname','disapprove',NULL,0,NULL),(40,'2478898856.jpg','noname','disapprove',NULL,0,NULL),(41,'2478898942.jpg','noname','disapprove',NULL,0,NULL),(42,'2478937146.jpg','noname','disapprove',NULL,0,NULL),(43,'2478978506.jpg','noname','disapprove',NULL,0,NULL),(44,'2479275096.jpg','noname','disapprove',NULL,0,NULL),(45,'2479341726.jpg','noname','disapprove',NULL,0,NULL),(46,'2479752005.jpg','noname','disapprove',NULL,0,NULL),(47,'2479752045.jpg','noname','disapprove',NULL,0,NULL),(48,'2479752071.jpg','noname','disapprove',NULL,0,NULL),(49,'2479752177.jpg','noname','disapprove',NULL,0,NULL),(50,'2479761151.jpg','noname','disapprove',NULL,0,NULL),(51,'2480566728.jpg','noname','disapprove',NULL,0,NULL),(52,'2480566776.jpg','noname','disapprove',NULL,0,NULL),(53,'2480566792.jpg','noname','disapprove',NULL,0,NULL),(54,'2480566802.jpg','noname','disapprove',NULL,0,NULL),(55,'2480575902.jpg','noname','disapprove',NULL,0,NULL),(56,'2480575930.jpg','noname','disapprove',NULL,0,NULL),(57,'2480655355.jpg','noname','disapprove',NULL,0,NULL),(58,'2480655437.jpg','noname','disapprove',NULL,0,NULL),(59,'2480655493.jpg','noname','disapprove',NULL,0,NULL),(60,'2480655775.jpg','noname','disapprove',NULL,0,NULL),(61,'2480655795.jpg','noname','disapprove',NULL,0,NULL),(62,'2480655819.jpg','noname','disapprove',NULL,0,NULL),(63,'2480655871.jpg','noname','disapprove',NULL,0,NULL),(64,'2480655889.jpg','noname','disapprove',NULL,0,NULL),(65,'2480656361.jpg','noname','disapprove',NULL,0,NULL),(66,'2480656469.jpg','noname','disapprove',NULL,0,NULL),(67,'2480656493.jpg','noname','disapprove',NULL,0,NULL),(68,'2480656537.jpg','noname','disapprove',NULL,0,NULL),(69,'2480739623.jpg','noname','disapprove',NULL,0,NULL),(70,'2480743117.jpg','noname','disapprove',NULL,0,NULL),(71,'2481467358.jpg','noname','disapprove',NULL,0,NULL),(72,'2481467416.jpg','noname','disapprove',NULL,0,NULL),(73,'2481467662.jpg','noname','disapprove',NULL,0,NULL),(74,'2481467686.jpg','noname','disapprove',NULL,0,NULL),(75,'2481467796.jpg','noname','disapprove',NULL,0,NULL),(76,'2481467814.jpg','noname','disapprove',NULL,0,NULL),(77,'2481467824.jpg','noname','disapprove',NULL,0,NULL),(78,'2481467988.jpg','noname','disapprove',NULL,0,NULL),(79,'2481468126.jpg','noname','disapprove',NULL,0,NULL),(80,'2481468378.jpg','noname','disapprove',NULL,0,NULL),(81,'2481468408.jpg','noname','disapprove',NULL,0,NULL),(82,'2481468478.jpg','noname','disapprove',NULL,0,NULL),(83,'2481472368.jpg','noname','disapprove',NULL,0,NULL),(84,'2481472408.jpg','noname','disapprove',NULL,0,NULL),(85,'2481553168.jpg','noname','disapprove',NULL,0,NULL),(86,'2481553282.jpg','noname','disapprove',NULL,0,NULL),(87,'2482381089.jpg','noname','disapprove',NULL,0,NULL),(88,'2482381143.jpg','noname','disapprove',NULL,0,NULL),(89,'2482381181.jpg','noname','disapprove',NULL,0,NULL),(90,'2482381195.jpg','noname','disapprove',NULL,0,NULL),(91,'2482381229.jpg','noname','disapprove',NULL,0,NULL),(92,'2482381253.jpg','noname','disapprove',NULL,0,NULL),(93,'2482381371.jpg','noname','disapprove',NULL,0,NULL),(94,'2482381575.jpg','noname','disapprove',NULL,0,NULL),(95,'2482381671.jpg','Sony Playstation','disapprove','Sony',10,5),(96,'2482381687.jpg','BBC','disapprove','BBC',20,5),(97,'2483195788.jpg','noname','disapprove',NULL,0,NULL),(98,'2483195836.jpg','noname','disapprove',NULL,0,NULL),(99,'2483195906.jpg','noname','disapprove',NULL,0,NULL),(100,'2483195990.jpg','noname','disapprove',NULL,0,NULL),(101,'4b6f406de7eeb.png','Дождь по столу','disapprove',NULL,0,NULL),(102,'4b6f40954a7d2.png','Дождь по столу','disapprove',NULL,0,NULL),(103,'4b7d1b39e7f49.png','точка','disapprove','OWLS',0,5),(104,'4b73d9fc23443.png','Другая Компания','disapprove',NULL,0,NULL),(105,'4b73dab9c2184.png','Другая Компания','disapprove',NULL,0,NULL),(106,'4b743733cdf13.png','моник','disapprove',NULL,0,NULL),(107,'4b743774e3f94.png','моник','disapprove',NULL,0,NULL),(108,'4b743874a0b3f.png','моник','disapprove',NULL,0,NULL),(109,'4b74389b2f397.png','моник','disapprove',NULL,0,NULL),(110,'4b7439240d307.png','волны','disapprove',NULL,0,NULL),(111,'4b7439b21e592.png','Зимнее дерево','disapprove','Природа',100,5),(112,'4b7439b5567e5.png','Зимнее дерево','approve',NULL,0,NULL),(113,'4b744e9060ac2.png','хундайка','disapprove',NULL,0,NULL),(114,'4b751bf31f469.png','Plazma','disapprove','KDE',0,1),(115,'4b7ad2625ddbf.png','sample','disapprove','sample',50,1),(116,'4b7ad327ac217.png','<script>alert(\'xss\')</script>','disapprove','http://topas.firstvds.ru:8003/brand/115/waiting/',50,1),(117,'4b7ad34f4e2b4.png','<script>alert(\'xss\')</script>','disapprove','http://topas.firstvds.ru:8003/brand/115/waiting/',50,1),(118,'4b7dc29d39e3c.png','Балтика','approve','Carlsberg',50,5),(119,'4b7dc2cbab784.png','Пять озер','approve','Алкогольная сибирская группа',50,5),(120,'4b7dc35eb2e3d.png','Арсенальное','approve','Carlsberg',50,5),(121,'4b7dc3d81966a.png','Черкизовский','approve','Группа Черкизово',50,5),(122,'4b7dc41911eff.png','Чудо','approve','Вимм-Билль-Данн',50,5),(123,'4b7dc440e8cd6.png','Добрый','approve','Coca-Cola',50,5),(124,'4b7dc4634c9f9.png','Домик в деревне','approve','Вимм-Билль-Данн',50,5),(125,'4b7dc48fa2090.png','Фруктовый сад','approve','PepsiCo',50,5),(126,'4b7dc4d73977f.png','Клинское','approve','SUN InBev',50,5),(127,'4b7dc56b32a28.png','Макфа','approve','Макфа',50,5),(128,'4b7dc59b6cc40.png','Микоян','approve','Эксима',50,5),(129,'4b7dc5d7dac04.png','Охота','approve','Heineken',50,5),(130,'4b7dc62c900b2.png','ОстаNкино','approve','Останкинский мясоперерабатывающий комбинат',50,4),(131,'4b7dc6687191c.png','Простоквашино','approve','Юнимилк',50,5),(132,'4b7dc692b5f22.png','Путинка','approve','Винэксим',50,5),(133,'4b7dc6c0b9a51.png','Rolsen','approve','Rolsen Electronics',50,2),(134,'4b7dc70c6c2af.png','Роллтон','approve','Mareven Food Central',50,5),(135,'4b7dc7454be21.png','Россия - Щедрая душа','approve','Nestle',50,5),(136,'4b7dc75ebfe43.png','Сибирская корона','approve','SUN InBev',50,5),(137,'4b7dc77fd4a42.png','Слобода','approve','Эфко',50,5),(138,'4b7dc7eae0f11.png','Старый мельник','approve','Efes',50,5),(139,'4b7dc81584319.png','Веселый молочник','approve','Вимм-Билль-Данн',50,5),(140,'4b7dc8918310e.png','Ява','approve','British American Tobacco',50,5),(141,'4b7dc8b72db43.png','Зеленая марка','approve','Central European Distribution Corporation',50,5),(142,'4b7dc9105e8e4.png','Золотая бочка','approve','SABMiller',50,5),(143,'4b7dc938cdae4.png','Amtel','approve','Амтел-Фредештайн',50,1),(144,'4b7dc969467be.png','Bag Bier','approve','SUN InBev',50,5),(145,'4b7dc9a75ea81.png','Беленькая','approve','Синергия',50,5),(146,'4b7dca6dd4032.png','Белый медведь','approve','Efes',50,5),(147,'4b7dca866148e.png','Большая кружка','approve','Carlsberg',50,5),(148,'4b7dcae7be200.png','Bonanza','approve','JFC',50,5),(149,'4b7dcb44616d3.png','Царицыно','approve','Группа компаний «Царицыно»',50,5),(150,'4b7dcb62a35ac.png','Дымов','approve','Дымов',50,5),(151,'4b7dcbd79875c.png','J7','approve','Вимм-Билль-Данн',50,5),(152,'4b7dcd11e6db1.png','Мясокомбинат Клинский','approve','Продо',50,5),(153,'4b7dcd36a764a.png','А. Коркунов','approve','Wrigley',50,5),(154,'4b7dcd54baf18.png','LD','approve','JTI',50,5),(155,'4b7dcd71cfc79.png','Любимый сад','approve','Вимм-Билль-Данн',50,5),(156,'4b7dcd977a490.png','Лукойл','approve','Лукойл',50,1),(157,'4b7dce292f682.png','М','approve','Вимм-Билль-Данн',50,5),(158,'4b7dce4607ae7.png','Моя семья','approve','Нидан Соки',50,5),(159,'4b7dced07a8c7.png','Невское','approve','Carlsberg',50,5),(160,'4b7dcef017b55.png','Очаково','approve','Очаково',50,5),(161,'4b7dcf4b4b04e.png','Петр I','approve','JTI',50,5),(162,'4b7dcf6c698e4.png','Толстяк','approve','SUN InBev',50,5),(163,'4b7dcf863513e.png','Vitek','approve','Golder Electronics',50,3),(164,'4b7dcfa8ab881.png','Ярпиво','approve','Carlsberg',50,5),(165,'4b7dcfbf57559.png','Журавли','approve','Central European Distribution Corporation',50,5),(166,'4b7dcfdb5892a.png','Золотая семечка','approve','Юг Руси',50,5),(167,'4b7dd06d0cfc2.png','Славянская','approve','Алкогольные заводы Гросс',50,5),(168,'4b7e4788440b1.png','ttest','disapprove','ttest',50,1);
/*!40000 ALTER TABLE `brand` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand_industry`
--

DROP TABLE IF EXISTS `brand_industry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand_industry` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_industry`
--

LOCK TABLES `brand_industry` WRITE;
/*!40000 ALTER TABLE `brand_industry` DISABLE KEYS */;
INSERT INTO `brand_industry` VALUES (1,'Пиво'),(2,'Водка'),(3,'Молочные продукты'),(4,'Мясопродукты'),(5,'Сигареты'),(6,'Сок'),(7,'Кондитерские изделия'),(8,'Бытовая техника'),(9,'Продукты быстрого приготовления'),(10,'Макароны, мука, крупы'),(11,'Растительное и сливочное масло, майонез'),(12,'Бананы'),(13,'Моторное масло'),(14,'Шины');
/*!40000 ALTER TABLE `brand_industry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brand_tag`
--

DROP TABLE IF EXISTS `brand_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brand_tag` (
  `brand_id` int(11) NOT NULL,
  `tag_id` varchar(255) NOT NULL,
  `count` int(11) NOT NULL default '0',
  UNIQUE KEY `brand_id_tag_id` (`brand_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brand_tag`
--

LOCK TABLES `brand_tag` WRITE;
/*!40000 ALTER TABLE `brand_tag` DISABLE KEYS */;
INSERT INTO `brand_tag` VALUES (120,'15',1),(126,'1',1),(138,'4',1),(140,'3',1),(141,'3',1),(143,'5',1),(151,'8',1),(153,'2',1),(165,'18',1);
/*!40000 ALTER TABLE `brand_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_tag`
--

DROP TABLE IF EXISTS `post_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_tag` (
  `id` int(11) NOT NULL auto_increment,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_tag`
--

LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
INSERT INTO `post_tag` VALUES (1,1,0,143),(2,2,0,130),(3,3,0,143),(4,4,0,143);
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `count` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (1,'Кто идет за клинским?',1),(2,'вкусно',1),(3,'СССР',2),(4,'Нашествие',1),(5,'Скорость',1),(8,'Js',1),(15,'бяка',1),(18,'спички',1);
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_censure`
--

DROP TABLE IF EXISTS `tag_censure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_censure` (
  `id` int(11) NOT NULL auto_increment,
  `tag_id` int(11) NOT NULL,
  `status` enum('dirty','approve','disapprove','trash') NOT NULL default 'dirty',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `tag_id` (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_censure`
--

LOCK TABLES `tag_censure` WRITE;
/*!40000 ALTER TABLE `tag_censure` DISABLE KEYS */;
INSERT INTO `tag_censure` VALUES (1,9,'dirty'),(2,11,'dirty'),(3,12,'dirty'),(4,13,'dirty'),(5,17,'dirty'),(6,19,'dirty'),(7,23,'dirty'),(8,24,'dirty'),(9,25,'dirty');
/*!40000 ALTER TABLE `tag_censure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag_merge`
--

DROP TABLE IF EXISTS `tag_merge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_merge` (
  `master_id` int(11) NOT NULL,
  `slave_id` int(11) NOT NULL,
  UNIQUE KEY `master_id_slave_id` (`master_id`,`slave_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag_merge`
--

LOCK TABLES `tag_merge` WRITE;
/*!40000 ALTER TABLE `tag_merge` DISABLE KEYS */;
INSERT INTO `tag_merge` VALUES (6,6),(6,7);
/*!40000 ALTER TABLE `tag_merge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `alias` varchar(255) NOT NULL,
  `role` enum('user','admin') default 'user',
  `public_on_twitter` int(11) NOT NULL default '0',
  `email` text NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'perepechaev','admin',0,'',''),(2,'gin0nly','admin',0,'',''),(3,'vadimpeskov','admin',0,'',''),(4,'sunofvault','user',1,'vadim@drucom.ru','Вадим Песков'),(5,'AlexShow','admin',0,'',''),(6,'brandomet','admin',0,'',''),(7,'Хрен','user',0,'Хрен вам 2','Хрен'),(8,'Хрен','user',0,'Хрен 2','Хрен'),(9,'хрен 8','user',0,'хрен 9','хрен 8'),(10,'полный хрен','user',0,'хрена вам','полный хрен'),(11,'Евгений','user',0,'evgeny@drucom.ru','Евгений'),(12,'Тайный пользователь','user',0,'admin@sex.com','Тайный пользователь');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_brand_tag`
--

DROP TABLE IF EXISTS `user_brand_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_brand_tag` (
  `user_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_brand_tag`
--

LOCK TABLES `user_brand_tag` WRITE;
/*!40000 ALTER TABLE `user_brand_tag` DISABLE KEYS */;
INSERT INTO `user_brand_tag` VALUES (3,134,108),(3,126,1),(3,153,2),(3,141,3),(3,138,4),(5,140,3),(5,143,5),(1,151,8),(1,120,15);
/*!40000 ALTER TABLE `user_brand_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_oauth`
--

DROP TABLE IF EXISTS `user_oauth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_oauth` (
  `user_id` int(11) NOT NULL,
  `type` enum('twitter.com') NOT NULL,
  `remote_id` int(11) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `type_remote_id` (`type`,`remote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_oauth`
--

LOCK TABLES `user_oauth` WRITE;
/*!40000 ALTER TABLE `user_oauth` DISABLE KEYS */;
INSERT INTO `user_oauth` VALUES (1,'twitter.com',110943324),(2,'twitter.com',16854072),(3,'twitter.com',15923328),(4,'twitter.com',113109275),(5,'twitter.com',31398915),(6,'twitter.com',114758777);
/*!40000 ALTER TABLE `user_oauth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_post`
--

DROP TABLE IF EXISTS `user_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_post` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '-1',
  `comment` text NOT NULL,
  `create` datetime NOT NULL,
  `status` enum('dirty','approve','disapprove','trash') NOT NULL default 'dirty',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_post`
--

LOCK TABLES `user_post` WRITE;
/*!40000 ALTER TABLE `user_post` DISABLE KEYS */;
INSERT INTO `user_post` VALUES (1,10,'вот так','2010-02-21 22:27:51','disapprove'),(2,11,'Отличный бренд','2010-02-21 23:08:06','dirty'),(3,4,'Хорошая компания','2010-02-23 01:29:00','dirty'),(4,12,'бла бла','2010-02-23 01:31:21','disapprove');
/*!40000 ALTER TABLE `user_post` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-02-23 12:33:48
