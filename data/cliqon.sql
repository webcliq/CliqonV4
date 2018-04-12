-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: markr_cukieblog
-- ------------------------------------------------------
-- Server version	5.7.21-log

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
-- Table structure for table `dbarchive`
--

DROP TABLE IF EXISTS `dbarchive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbarchive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'str(0)',
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'string',
  `c_category` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_common` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_level` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '50:50:50',
  `c_order` varchar(255) COLLATE utf8_bin DEFAULT 'zz',
  `c_parent` varchar(255) COLLATE utf8_bin DEFAULT '0',
  `c_document` longtext COLLATE utf8_bin NOT NULL,
  `c_options` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_version` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `c_status` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'active',
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbcollection`
--

DROP TABLE IF EXISTS `dbcollection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbcollection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'str(0)',
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'string',
  `c_category` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_common` longtext COLLATE utf8_bin,
  `c_level` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '50:50:50',
  `c_order` varchar(255) COLLATE utf8_bin DEFAULT 'zz',
  `c_parent` varchar(255) COLLATE utf8_bin DEFAULT '0',
  `c_document` longtext COLLATE utf8_bin NOT NULL,
  `c_options` longtext COLLATE utf8_bin,
  `c_revision` varchar(255) COLLATE utf8_bin DEFAULT '0',
  `c_status` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'active',
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `c_reference` (`c_reference`),
  KEY `c_type` (`c_type`)
) ENGINE=InnoDB AUTO_INCREMENT=767 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbindex`
--

DROP TABLE IF EXISTS `dbindex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbindex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL,
  `c_category` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_value` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbitem`
--

DROP TABLE IF EXISTS `dbitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbitem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'str(0)',
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'string',
  `c_category` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_common` varchar(255) CHARACTER SET latin1 COLLATE latin1_spanish_ci DEFAULT NULL,
  `c_level` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '50:50:50',
  `c_order` varchar(255) COLLATE utf8_bin DEFAULT 'zz',
  `c_parent` varchar(255) COLLATE utf8_bin DEFAULT '0',
  `c_document` longtext COLLATE utf8_bin NOT NULL,
  `c_options` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_version` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `c_status` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'active',
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `c_reference` (`c_reference`),
  KEY `c_type` (`c_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dblog`
--

DROP TABLE IF EXISTS `dblog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dblog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL,
  `c_category` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_text` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_value` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  `c_type` varchar(191) COLLATE utf8_bin DEFAULT NULL,
  `c_revision` int(11) unsigned DEFAULT NULL,
  `c_document` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbsession`
--

DROP TABLE IF EXISTS `dbsession`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbsession` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL,
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'cookie',
  `c_datavalue` text COLLATE utf8_bin,
  `c_access` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbtransaction`
--

DROP TABLE IF EXISTS `dbtransaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbtransaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_reference` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `c_category` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_common` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_level` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '20:20:20',
  `c_order` varchar(255) COLLATE utf8_bin DEFAULT 'zz',
  `c_parent` varchar(255) COLLATE utf8_bin DEFAULT 'z',
  `c_document` longtext COLLATE utf8_bin NOT NULL,
  `c_options` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_value` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '1',
  `c_dateentered` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whoentered` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dbuser`
--

DROP TABLE IF EXISTS `dbuser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dbuser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_username` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'str(0)',
  `c_password` varchar(255) COLLATE utf8_bin NOT NULL,
  `c_email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_group` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_level` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '50:50:50',
  `c_type` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'other',
  `c_document` longtext COLLATE utf8_bin NOT NULL,
  `c_options` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `c_status` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'active',
  `c_lastmodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '2017-01-01',
  `c_whomodified` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'admin',
  `c_notes` longtext COLLATE utf8_bin,
  PRIMARY KEY (`id`),
  KEY `c_username` (`c_username`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-04-12 13:47:02
