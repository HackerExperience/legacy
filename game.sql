-- MySQL dump 10.13  Distrib 5.7.4-m14, for Linux (x86_64)
--
-- Host: db.hackerexperience.com    Database: game
-- ------------------------------------------------------
-- Server version	5.7.4-m14

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
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(36) NOT NULL,
  `lastLogin` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_reports`
--

DROP TABLE IF EXISTS `admin_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `errorID` int(5) NOT NULL,
  `userID` int(5) NOT NULL,
  `report` text NOT NULL,
  `critical` tinyint(1) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_reports`
--

LOCK TABLES `admin_reports` WRITE;
/*!40000 ALTER TABLE `admin_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges_clans`
--

DROP TABLE IF EXISTS `badges_clans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges_clans` (
  `badgeID` tinyint(3) NOT NULL,
  `priority` int(5) NOT NULL,
  PRIMARY KEY (`badgeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges_clans`
--

LOCK TABLES `badges_clans` WRITE;
/*!40000 ALTER TABLE `badges_clans` DISABLE KEYS */;
/*!40000 ALTER TABLE `badges_clans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges_users`
--

DROP TABLE IF EXISTS `badges_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges_users` (
  `badgeID` tinyint(3) NOT NULL,
  `priority` smallint(3) NOT NULL,
  PRIMARY KEY (`badgeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges_users`
--

LOCK TABLES `badges_users` WRITE;
/*!40000 ALTER TABLE `badges_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `badges_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bankAccounts`
--

DROP TABLE IF EXISTS `bankAccounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankAccounts` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `bankAcc` bigint(12) NOT NULL,
  `bankPass` varchar(6) NOT NULL,
  `bankID` int(5) NOT NULL,
  `bankUser` int(5) NOT NULL,
  `cash` bigint(15) NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id` (`bankUser`),
  KEY `bankUser` (`bankUser`,`bankAcc`,`bankID`),
  KEY `bankAcc` (`bankAcc`)
) ENGINE=InnoDB AUTO_INCREMENT=12716004 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bankAccounts`
--

LOCK TABLES `bankAccounts` WRITE;
/*!40000 ALTER TABLE `bankAccounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `bankAccounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bankaccounts_expire`
--

DROP TABLE IF EXISTS `bankaccounts_expire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankaccounts_expire` (
  `accID` int(30) unsigned NOT NULL,
  `expireDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `expireDate` (`expireDate`),
  KEY `accID` (`accID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bankaccounts_expire`
--

LOCK TABLES `bankaccounts_expire` WRITE;
/*!40000 ALTER TABLE `bankaccounts_expire` DISABLE KEYS */;
/*!40000 ALTER TABLE `bankaccounts_expire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitcoin_wallets`
--

DROP TABLE IF EXISTS `bitcoin_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitcoin_wallets` (
  `address` varchar(34) NOT NULL,
  `userID` int(5) unsigned NOT NULL,
  `npcID` int(5) unsigned NOT NULL,
  `key` varchar(64) NOT NULL,
  `amount` decimal(12,7) unsigned NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`address`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitcoin_wallets`
--

LOCK TABLES `bitcoin_wallets` WRITE;
/*!40000 ALTER TABLE `bitcoin_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitcoin_wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bugreports`
--

DROP TABLE IF EXISTS `bugreports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bugreports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bugText` text NOT NULL,
  `reportedBy` int(5) unsigned NOT NULL,
  `sessionContent` text NOT NULL,
  `serverContent` text NOT NULL,
  `follow` tinyint(1) NOT NULL DEFAULT '0',
  `solved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4289 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bugreports`
--

LOCK TABLES `bugreports` WRITE;
/*!40000 ALTER TABLE `bugreports` DISABLE KEYS */;
/*!40000 ALTER TABLE `bugreports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `userID` int(5) unsigned NOT NULL,
  `reputation` int(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_profile`
--

DROP TABLE IF EXISTS `cache_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_profile` (
  `userID` int(5) unsigned NOT NULL,
  `expireDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_profile`
--

LOCK TABLES `cache_profile` WRITE;
/*!40000 ALTER TABLE `cache_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certifications`
--

DROP TABLE IF EXISTS `certifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certifications` (
  `userID` int(5) NOT NULL,
  `certLevel` tinyint(1) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certifications`
--

LOCK TABLES `certifications` WRITE;
/*!40000 ALTER TABLE `certifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `certifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `changelog`
--

DROP TABLE IF EXISTS `changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `dateCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `author` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changelog`
--

LOCK TABLES `changelog` WRITE;
/*!40000 ALTER TABLE `changelog` DISABLE KEYS */;
/*!40000 ALTER TABLE `changelog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan`
--

DROP TABLE IF EXISTS `clan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan` (
  `clanID` int(5) NOT NULL AUTO_INCREMENT,
  `clanIP` int(11) unsigned NOT NULL,
  `name` varchar(25) NOT NULL,
  `nick` varchar(3) NOT NULL,
  `desc` text NOT NULL,
  `slotsUsed` smallint(3) NOT NULL,
  `slotsTotal` smallint(3) NOT NULL,
  `createdOn` datetime NOT NULL,
  `createdBy` int(5) NOT NULL,
  `powerTax` tinyint(2) NOT NULL,
  `moneyTax` tinyint(2) NOT NULL,
  `money` int(10) NOT NULL,
  `power` int(10) NOT NULL,
  `corp` tinyint(1) NOT NULL,
  PRIMARY KEY (`clanID`)
) ENGINE=InnoDB AUTO_INCREMENT=23259 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan`
--

LOCK TABLES `clan` WRITE;
/*!40000 ALTER TABLE `clan` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_badge`
--

DROP TABLE IF EXISTS `clan_badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_badge` (
  `clanID` int(5) NOT NULL,
  `badgeID` smallint(3) NOT NULL,
  `round` tinyint(3) NOT NULL DEFAULT '0',
  `dateAdd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `userID` (`clanID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_badge`
--

LOCK TABLES `clan_badge` WRITE;
/*!40000 ALTER TABLE `clan_badge` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_badge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_ddos`
--

DROP TABLE IF EXISTS `clan_ddos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_ddos` (
  `attackerClan` int(5) NOT NULL,
  `victimClan` int(5) NOT NULL,
  `ddosID` int(5) NOT NULL,
  `displayAttacker` tinyint(1) NOT NULL DEFAULT '1',
  `displayVictim` tinyint(1) NOT NULL DEFAULT '1',
  KEY `attackerClan` (`attackerClan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_ddos`
--

LOCK TABLES `clan_ddos` WRITE;
/*!40000 ALTER TABLE `clan_ddos` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_ddos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_ddos_history`
--

DROP TABLE IF EXISTS `clan_ddos_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_ddos_history` (
  `attackerClan` int(5) NOT NULL,
  `victimClan` int(5) NOT NULL,
  `ddosID` int(5) NOT NULL,
  `warID` int(5) NOT NULL,
  KEY `attackerClan` (`attackerClan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_ddos_history`
--

LOCK TABLES `clan_ddos_history` WRITE;
/*!40000 ALTER TABLE `clan_ddos_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_ddos_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_defcon`
--

DROP TABLE IF EXISTS `clan_defcon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_defcon` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `attackerID` int(5) NOT NULL,
  `attackerClanID` int(4) NOT NULL,
  `victimID` int(5) NOT NULL,
  `victimClanID` int(4) NOT NULL,
  `attackDate` datetime NOT NULL,
  `clanServer` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29249 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_defcon`
--

LOCK TABLES `clan_defcon` WRITE;
/*!40000 ALTER TABLE `clan_defcon` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_defcon` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_requests`
--

DROP TABLE IF EXISTS `clan_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_requests` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `clanID` int(5) unsigned NOT NULL,
  `userID` int(5) NOT NULL,
  `adminID` int(5) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `askedDate` datetime NOT NULL,
  `msg` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `clanID` (`clanID`)
) ENGINE=InnoDB AUTO_INCREMENT=63154 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_requests`
--

LOCK TABLES `clan_requests` WRITE;
/*!40000 ALTER TABLE `clan_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_stats`
--

DROP TABLE IF EXISTS `clan_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_stats` (
  `cid` int(5) NOT NULL,
  `totalMemberPower` int(11) NOT NULL,
  `averageMemberPower` int(10) NOT NULL,
  `averageMemberRanking` int(5) NOT NULL,
  `totalMoneyEarned` int(11) NOT NULL,
  `averageMoneyEarned` int(11) NOT NULL,
  `servers` smallint(3) NOT NULL,
  `members` int(5) NOT NULL,
  `pageClicks` int(5) NOT NULL,
  `won` int(4) NOT NULL,
  `lost` int(4) NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_stats`
--

LOCK TABLES `clan_stats` WRITE;
/*!40000 ALTER TABLE `clan_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_users`
--

DROP TABLE IF EXISTS `clan_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `clanID` int(5) NOT NULL,
  `userID` int(5) NOT NULL,
  `memberSince` date NOT NULL,
  `authLevel` tinyint(1) NOT NULL,
  `hierarchy` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `clanID` (`clanID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=48282 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_users`
--

LOCK TABLES `clan_users` WRITE;
/*!40000 ALTER TABLE `clan_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_war`
--

DROP TABLE IF EXISTS `clan_war`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_war` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `clanID1` int(5) NOT NULL,
  `clanID2` int(5) NOT NULL,
  `score1` int(10) NOT NULL,
  `score2` int(10) NOT NULL,
  `startDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bounty` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `clanID1` (`clanID1`,`clanID2`)
) ENGINE=InnoDB AUTO_INCREMENT=4764 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_war`
--

LOCK TABLES `clan_war` WRITE;
/*!40000 ALTER TABLE `clan_war` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_war` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clan_war_history`
--

DROP TABLE IF EXISTS `clan_war_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clan_war_history` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `idWinner` int(5) unsigned NOT NULL,
  `idLoser` int(5) unsigned NOT NULL,
  `scoreWinner` int(10) unsigned NOT NULL,
  `scoreLoser` int(10) unsigned NOT NULL,
  `startDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bounty` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4494 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clan_war_history`
--

LOCK TABLES `clan_war_history` WRITE;
/*!40000 ALTER TABLE `clan_war_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `clan_war_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `debug_pagarme`
--

DROP TABLE IF EXISTS `debug_pagarme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debug_pagarme` (
  `debug_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(10) NOT NULL,
  `post` text NOT NULL,
  PRIMARY KEY (`debug_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=481 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `debug_pagarme`
--

LOCK TABLES `debug_pagarme` WRITE;
/*!40000 ALTER TABLE `debug_pagarme` DISABLE KEYS */;
/*!40000 ALTER TABLE `debug_pagarme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doom_abort`
--

DROP TABLE IF EXISTS `doom_abort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doom_abort` (
  `doomID` int(5) unsigned NOT NULL,
  `abortedBy` int(5) unsigned NOT NULL,
  `abortDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`doomID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doom_abort`
--

LOCK TABLES `doom_abort` WRITE;
/*!40000 ALTER TABLE `doom_abort` DISABLE KEYS */;
/*!40000 ALTER TABLE `doom_abort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_delete`
--

DROP TABLE IF EXISTS `email_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_delete` (
  `userID` int(5) NOT NULL,
  `code` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_delete`
--

LOCK TABLES `email_delete` WRITE;
/*!40000 ALTER TABLE `email_delete` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_delete` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_reset`
--

DROP TABLE IF EXISTS `email_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_reset` (
  `userID` int(5) NOT NULL,
  `code` varchar(32) NOT NULL,
  `requestDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_reset`
--

LOCK TABLES `email_reset` WRITE;
/*!40000 ALTER TABLE `email_reset` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_reset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_verification`
--

DROP TABLE IF EXISTS `email_verification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_verification` (
  `userID` int(5) unsigned NOT NULL,
  `email` varchar(60) NOT NULL,
  `code` varchar(25) NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_verification`
--

LOCK TABLES `email_verification` WRITE;
/*!40000 ALTER TABLE `email_verification` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_verification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fbi`
--

DROP TABLE IF EXISTS `fbi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fbi` (
  `ip` bigint(11) NOT NULL,
  `reason` tinyint(1) NOT NULL,
  `bounty` bigint(15) NOT NULL,
  `dateAdd` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dateEnd` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fbi`
--

LOCK TABLES `fbi` WRITE;
/*!40000 ALTER TABLE `fbi` DISABLE KEYS */;
/*!40000 ALTER TABLE `fbi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friend_requests`
--

DROP TABLE IF EXISTS `friend_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friend_requests` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `requestedBy` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95891 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friend_requests`
--

LOCK TABLES `friend_requests` WRITE;
/*!40000 ALTER TABLE `friend_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `friend_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hardware`
--

DROP TABLE IF EXISTS `hardware`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hardware` (
  `serverID` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `name` varchar(15) NOT NULL,
  `cpu` float NOT NULL DEFAULT '500',
  `hdd` float NOT NULL DEFAULT '100',
  `ram` float NOT NULL DEFAULT '256',
  `net` float NOT NULL DEFAULT '1',
  `isNPC` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`serverID`),
  KEY `IndiceComNPC` (`userID`,`isNPC`)
) ENGINE=InnoDB AUTO_INCREMENT=6593993 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hardware`
--

LOCK TABLES `hardware` WRITE;
/*!40000 ALTER TABLE `hardware` DISABLE KEYS */;
/*!40000 ALTER TABLE `hardware` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hardware_external`
--

DROP TABLE IF EXISTS `hardware_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hardware_external` (
  `serverID` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `name` varchar(15) NOT NULL,
  `size` int(4) NOT NULL DEFAULT '100',
  UNIQUE KEY `serverID` (`serverID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=329611 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hardware_external`
--

LOCK TABLES `hardware_external` WRITE;
/*!40000 ALTER TABLE `hardware_external` DISABLE KEYS */;
/*!40000 ALTER TABLE `hardware_external` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_clans`
--

DROP TABLE IF EXISTS `hist_clans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_clans` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `cid` int(5) NOT NULL,
  `rank` int(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `nick` varchar(4) NOT NULL,
  `reputation` bigint(10) NOT NULL,
  `members` tinyint(3) NOT NULL,
  `round` tinyint(3) NOT NULL,
  `won` int(4) NOT NULL,
  `lost` int(4) NOT NULL,
  `rate` float NOT NULL,
  `clicks` smallint(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `reputation` (`reputation`)
) ENGINE=InnoDB AUTO_INCREMENT=67957 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_clans`
--

LOCK TABLES `hist_clans` WRITE;
/*!40000 ALTER TABLE `hist_clans` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_clans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_clans_current`
--

DROP TABLE IF EXISTS `hist_clans_current`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_clans_current` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `cid` int(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `nick` varchar(4) NOT NULL,
  `clanIP` bigint(12) NOT NULL,
  `reputation` bigint(10) NOT NULL,
  `members` tinyint(3) NOT NULL,
  `won` smallint(4) NOT NULL,
  `lost` smallint(4) NOT NULL,
  `rate` double NOT NULL,
  `clicks` smallint(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `reputation` (`reputation`)
) ENGINE=InnoDB AUTO_INCREMENT=23259 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_clans_current`
--

LOCK TABLES `hist_clans_current` WRITE;
/*!40000 ALTER TABLE `hist_clans_current` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_clans_current` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_clans_war`
--

DROP TABLE IF EXISTS `hist_clans_war`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_clans_war` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `idWinner` int(5) unsigned NOT NULL,
  `idLoser` int(5) unsigned NOT NULL,
  `scoreWinner` int(10) unsigned NOT NULL,
  `scoreLoser` int(10) unsigned NOT NULL,
  `startDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `bounty` int(10) unsigned NOT NULL,
  `round` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1958 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_clans_war`
--

LOCK TABLES `hist_clans_war` WRITE;
/*!40000 ALTER TABLE `hist_clans_war` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_clans_war` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_ddos`
--

DROP TABLE IF EXISTS `hist_ddos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_ddos` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `rank` int(4) NOT NULL,
  `round` tinyint(2) NOT NULL,
  `attID` int(5) NOT NULL,
  `attUser` varchar(35) NOT NULL,
  `vicID` int(5) NOT NULL,
  `vicUser` varchar(35) NOT NULL,
  `power` int(10) NOT NULL,
  `servers` int(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=159502 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_ddos`
--

LOCK TABLES `hist_ddos` WRITE;
/*!40000 ALTER TABLE `hist_ddos` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_ddos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_doom`
--

DROP TABLE IF EXISTS `hist_doom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_doom` (
  `round` tinyint(3) NOT NULL,
  `doomCreatorID` int(5) unsigned NOT NULL,
  `doomClanID` int(5) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL,
  KEY `round` (`round`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_doom`
--

LOCK TABLES `hist_doom` WRITE;
/*!40000 ALTER TABLE `hist_doom` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_doom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_mails`
--

DROP TABLE IF EXISTS `hist_mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_mails` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(5) unsigned NOT NULL,
  `to` int(5) unsigned NOT NULL,
  `subject` varchar(50) NOT NULL,
  `text` varchar(1000) NOT NULL,
  `dateSent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `round` smallint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_mails`
--

LOCK TABLES `hist_mails` WRITE;
/*!40000 ALTER TABLE `hist_mails` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_mails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_missions`
--

DROP TABLE IF EXISTS `hist_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_missions` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(5) unsigned NOT NULL,
  `type` tinyint(2) NOT NULL,
  `hirerID` int(5) unsigned NOT NULL,
  `prize` int(6) NOT NULL,
  `missionEnd` datetime NOT NULL,
  `completed` tinyint(1) NOT NULL,
  `round` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=486196 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_missions`
--

LOCK TABLES `hist_missions` WRITE;
/*!40000 ALTER TABLE `hist_missions` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_missions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_software`
--

DROP TABLE IF EXISTS `hist_software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_software` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `rank` int(5) NOT NULL,
  `softName` varchar(50) NOT NULL,
  `softType` tinyint(2) NOT NULL,
  `softVersion` double NOT NULL,
  `owner` varchar(50) NOT NULL,
  `ownerID` int(5) NOT NULL,
  `round` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=543259 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_software`
--

LOCK TABLES `hist_software` WRITE;
/*!40000 ALTER TABLE `hist_software` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_users`
--

DROP TABLE IF EXISTS `hist_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `rank` int(5) NOT NULL,
  `userID` int(5) NOT NULL,
  `user` varchar(30) NOT NULL,
  `reputation` bigint(10) NOT NULL,
  `bestSoft` varchar(50) NOT NULL,
  `bestSoftVersion` float NOT NULL,
  `clanName` varchar(30) NOT NULL,
  `age` int(5) NOT NULL,
  `timePlaying` double NOT NULL,
  `bitcoinSent` double unsigned NOT NULL,
  `spamSent` int(15) unsigned NOT NULL,
  `warezSent` float unsigned NOT NULL,
  `profileViews` int(5) unsigned NOT NULL,
  `researchCount` int(5) unsigned NOT NULL,
  `missionCount` int(5) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `ddosCount` int(4) NOT NULL,
  `ipResets` int(4) NOT NULL,
  `moneyEarned` bigint(15) NOT NULL,
  `moneyTransfered` bigint(15) NOT NULL,
  `moneyHardware` bigint(15) NOT NULL,
  `moneyResearch` bigint(15) NOT NULL,
  `round` tinyint(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID_2` (`userID`,`round`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=446252 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_users`
--

LOCK TABLES `hist_users` WRITE;
/*!40000 ALTER TABLE `hist_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hist_users_current`
--

DROP TABLE IF EXISTS `hist_users_current`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hist_users_current` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `user` varchar(50) NOT NULL,
  `reputation` bigint(15) NOT NULL,
  `age` int(4) NOT NULL,
  `clanID` int(5) NOT NULL,
  `clanName` varchar(50) NOT NULL,
  `timePlaying` double NOT NULL,
  `missionCount` int(5) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `ddosCount` int(5) NOT NULL,
  `ipResets` int(4) NOT NULL,
  `moneyEarned` bigint(15) NOT NULL,
  `moneyTransfered` bigint(15) NOT NULL,
  `moneyHardware` bigint(15) NOT NULL,
  `moneyResearch` bigint(15) NOT NULL,
  `warezSent` int(20) unsigned NOT NULL,
  `spamSent` int(20) unsigned NOT NULL,
  `bitcoinSent` double unsigned NOT NULL,
  `profileViews` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `reputation` (`reputation`)
) ENGINE=InnoDB AUTO_INCREMENT=750474 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hist_users_current`
--

LOCK TABLES `hist_users_current` WRITE;
/*!40000 ALTER TABLE `hist_users_current` DISABLE KEYS */;
/*!40000 ALTER TABLE `hist_users_current` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internet_connections`
--

DROP TABLE IF EXISTS `internet_connections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internet_connections` (
  `userID` int(5) NOT NULL,
  `ip` bigint(14) NOT NULL,
  `expires` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_connections`
--

LOCK TABLES `internet_connections` WRITE;
/*!40000 ALTER TABLE `internet_connections` DISABLE KEYS */;
/*!40000 ALTER TABLE `internet_connections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internet_home`
--

DROP TABLE IF EXISTS `internet_home`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internet_home` (
  `userID` int(5) unsigned NOT NULL,
  `homeIP` bigint(11) unsigned NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_home`
--

LOCK TABLES `internet_home` WRITE;
/*!40000 ALTER TABLE `internet_home` DISABLE KEYS */;
/*!40000 ALTER TABLE `internet_home` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internet_important`
--

DROP TABLE IF EXISTS `internet_important`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internet_important` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(5) unsigned NOT NULL,
  `ip` bigint(11) unsigned NOT NULL,
  `name` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=970151 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_important`
--

LOCK TABLES `internet_important` WRITE;
/*!40000 ALTER TABLE `internet_important` DISABLE KEYS */;
/*!40000 ALTER TABLE `internet_important` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internet_webserver`
--

DROP TABLE IF EXISTS `internet_webserver`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internet_webserver` (
  `id` int(5) NOT NULL,
  `webDesc` text NOT NULL,
  `active` tinyint(1) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_webserver`
--

LOCK TABLES `internet_webserver` WRITE;
/*!40000 ALTER TABLE `internet_webserver` DISABLE KEYS */;
/*!40000 ALTER TABLE `internet_webserver` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists`
--

DROP TABLE IF EXISTS `lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `ip` bigint(11) NOT NULL,
  `user` varchar(8) NOT NULL,
  `pass` varchar(9) NOT NULL,
  `hackedTime` datetime NOT NULL,
  `virusID` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `ip` (`ip`),
  KEY `virusID` (`virusID`)
) ENGINE=InnoDB AUTO_INCREMENT=4620175 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists`
--

LOCK TABLES `lists` WRITE;
/*!40000 ALTER TABLE `lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists_bankAccounts`
--

DROP TABLE IF EXISTS `lists_bankAccounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_bankAccounts` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `bankID` int(5) NOT NULL,
  `bankAcc` int(6) NOT NULL,
  `bankPass` varchar(6) NOT NULL,
  `bankIP` int(11) NOT NULL,
  `hackedDate` datetime NOT NULL,
  `lastMoney` int(10) NOT NULL,
  `lastMoneyDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1511488 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists_bankAccounts`
--

LOCK TABLES `lists_bankAccounts` WRITE;
/*!40000 ALTER TABLE `lists_bankAccounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists_bankAccounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists_collect`
--

DROP TABLE IF EXISTS `lists_collect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_collect` (
  `userID` int(5) unsigned NOT NULL,
  `collectText` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists_collect`
--

LOCK TABLES `lists_collect` WRITE;
/*!40000 ALTER TABLE `lists_collect` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists_collect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists_notifications`
--

DROP TABLE IF EXISTS `lists_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_notifications` (
  `userID` int(5) NOT NULL,
  `ip` bigint(11) NOT NULL,
  `notificationType` tinyint(1) NOT NULL,
  `virusName` varchar(30) NOT NULL,
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists_notifications`
--

LOCK TABLES `lists_notifications` WRITE;
/*!40000 ALTER TABLE `lists_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists_specs`
--

DROP TABLE IF EXISTS `lists_specs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_specs` (
  `listID` int(5) unsigned NOT NULL,
  `spec_net` int(5) NOT NULL DEFAULT '1',
  `spec_hdd` int(5) NOT NULL DEFAULT '1000',
  PRIMARY KEY (`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists_specs`
--

LOCK TABLES `lists_specs` WRITE;
/*!40000 ALTER TABLE `lists_specs` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists_specs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lists_specs_analyzed`
--

DROP TABLE IF EXISTS `lists_specs_analyzed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lists_specs_analyzed` (
  `listID` int(5) unsigned NOT NULL,
  `minCPU` int(5) unsigned NOT NULL,
  `maxCPU` int(5) unsigned NOT NULL,
  `minRAM` int(5) unsigned NOT NULL,
  `maxRAM` int(5) unsigned NOT NULL,
  PRIMARY KEY (`listID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lists_specs_analyzed`
--

LOCK TABLES `lists_specs_analyzed` WRITE;
/*!40000 ALTER TABLE `lists_specs_analyzed` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists_specs_analyzed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `userID` int(5) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `isNPC` tinyint(1) NOT NULL,
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=897198 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_edit`
--

DROP TABLE IF EXISTS `log_edit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_edit` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `vicID` int(5) NOT NULL,
  `isNPC` tinyint(1) NOT NULL,
  `editorID` int(5) NOT NULL,
  `logText` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vicID` (`vicID`,`isNPC`),
  KEY `editorID` (`editorID`)
) ENGINE=InnoDB AUTO_INCREMENT=13490282 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_edit`
--

LOCK TABLES `log_edit` WRITE;
/*!40000 ALTER TABLE `log_edit` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_edit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mails`
--

DROP TABLE IF EXISTS `mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mails` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `from` int(5) NOT NULL,
  `to` int(5) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `subject` varchar(40) NOT NULL,
  `text` text NOT NULL,
  `dateSent` datetime NOT NULL,
  `isRead` tinyint(1) NOT NULL,
  `isDeleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=4016487 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mails`
--

LOCK TABLES `mails` WRITE;
/*!40000 ALTER TABLE `mails` DISABLE KEYS */;
/*!40000 ALTER TABLE `mails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mails_history`
--

DROP TABLE IF EXISTS `mails_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mails_history` (
  `mid` int(15) unsigned NOT NULL,
  `infoDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `info1` varchar(15) NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mails_history`
--

LOCK TABLES `mails_history` WRITE;
/*!40000 ALTER TABLE `mails_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `mails_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missions`
--

DROP TABLE IF EXISTS `missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `hirer` bigint(11) unsigned NOT NULL,
  `victim` bigint(11) unsigned NOT NULL,
  `info` bigint(11) unsigned NOT NULL,
  `newInfo` int(6) NOT NULL,
  `info2` bigint(11) NOT NULL,
  `newInfo2` int(11) unsigned NOT NULL,
  `missionStart` date NOT NULL,
  `missionEnd` date NOT NULL,
  `prize` int(6) NOT NULL,
  `userID` int(5) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `dateGenerated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13887278 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missions`
--

LOCK TABLES `missions` WRITE;
/*!40000 ALTER TABLE `missions` DISABLE KEYS */;
/*!40000 ALTER TABLE `missions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missions_history`
--

DROP TABLE IF EXISTS `missions_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions_history` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL,
  `hirer` bigint(11) NOT NULL,
  `missionEnd` date NOT NULL,
  `prize` int(6) NOT NULL,
  `userID` int(5) NOT NULL,
  `completed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `completed` (`completed`),
  KEY `missionEnd` (`missionEnd`),
  KEY `hirer` (`hirer`)
) ENGINE=InnoDB AUTO_INCREMENT=13887173 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missions_history`
--

LOCK TABLES `missions_history` WRITE;
/*!40000 ALTER TABLE `missions_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `missions_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missions_seed`
--

DROP TABLE IF EXISTS `missions_seed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missions_seed` (
  `missionID` int(5) unsigned NOT NULL,
  `greeting` tinyint(1) NOT NULL,
  `intro` tinyint(1) NOT NULL,
  `victim_call` tinyint(1) NOT NULL,
  `payment` tinyint(1) NOT NULL,
  `victim_location` tinyint(1) NOT NULL,
  `warning` tinyint(1) NOT NULL,
  `action` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`missionID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missions_seed`
--

LOCK TABLES `missions_seed` WRITE;
/*!40000 ALTER TABLE `missions_seed` DISABLE KEYS */;
/*!40000 ALTER TABLE `missions_seed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `author` varchar(30) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `date` datetime NOT NULL,
  `type` tinyint(2) NOT NULL,
  `info1` varchar(15) NOT NULL,
  `info2` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35359 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news_history`
--

DROP TABLE IF EXISTS `news_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_history` (
  `newsID` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `info1` varchar(15) NOT NULL,
  `info2` varchar(15) NOT NULL,
  `infoDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`newsID`)
) ENGINE=InnoDB AUTO_INCREMENT=35359 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news_history`
--

LOCK TABLES `news_history` WRITE;
/*!40000 ALTER TABLE `news_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `news_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc`
--

DROP TABLE IF EXISTS `npc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `npcType` tinyint(2) NOT NULL,
  `npcIP` bigint(11) NOT NULL,
  `npcPass` varchar(8) NOT NULL,
  `downUntil` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `npcIP` (`npcIP`)
) ENGINE=InnoDB AUTO_INCREMENT=897198 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc`
--

LOCK TABLES `npc` WRITE;
/*!40000 ALTER TABLE `npc` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_down`
--

DROP TABLE IF EXISTS `npc_down`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_down` (
  `npcID` int(5) unsigned NOT NULL,
  `downDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `downUntil` datetime NOT NULL,
  PRIMARY KEY (`npcID`),
  KEY `downUntil` (`downUntil`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_down`
--

LOCK TABLES `npc_down` WRITE;
/*!40000 ALTER TABLE `npc_down` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_down` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_expire`
--

DROP TABLE IF EXISTS `npc_expire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_expire` (
  `npcID` int(5) unsigned NOT NULL,
  `expireDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`npcID`),
  KEY `expireDate` (`expireDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_expire`
--

LOCK TABLES `npc_expire` WRITE;
/*!40000 ALTER TABLE `npc_expire` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_expire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_info_en`
--

DROP TABLE IF EXISTS `npc_info_en`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_info_en` (
  `npcID` int(5) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `web` text NOT NULL,
  PRIMARY KEY (`npcID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_info_en`
--

LOCK TABLES `npc_info_en` WRITE;
/*!40000 ALTER TABLE `npc_info_en` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_info_en` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_info_pt`
--

DROP TABLE IF EXISTS `npc_info_pt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_info_pt` (
  `npcID` int(5) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `web` text NOT NULL,
  PRIMARY KEY (`npcID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_info_pt`
--

LOCK TABLES `npc_info_pt` WRITE;
/*!40000 ALTER TABLE `npc_info_pt` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_info_pt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_key`
--

DROP TABLE IF EXISTS `npc_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_key` (
  `npcID` int(5) unsigned NOT NULL,
  `key` varchar(15) NOT NULL,
  PRIMARY KEY (`npcID`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_key`
--

LOCK TABLES `npc_key` WRITE;
/*!40000 ALTER TABLE `npc_key` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `npc_reset`
--

DROP TABLE IF EXISTS `npc_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `npc_reset` (
  `npcID` int(5) NOT NULL,
  `nextScan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`npcID`),
  KEY `nextScan` (`nextScan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `npc_reset`
--

LOCK TABLES `npc_reset` WRITE;
/*!40000 ALTER TABLE `npc_reset` DISABLE KEYS */;
/*!40000 ALTER TABLE `npc_reset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `info` text NOT NULL,
  `paid` double NOT NULL,
  `original_amount` double NOT NULL,
  `plan` varchar(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM AUTO_INCREMENT=137918 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments_history`
--

DROP TABLE IF EXISTS `payments_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_history` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '1',
  `info` text NOT NULL,
  `paid` double NOT NULL,
  `plan` varchar(15) NOT NULL,
  `confirmation` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=359 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments_history`
--

LOCK TABLES `payments_history` WRITE;
/*!40000 ALTER TABLE `payments_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `premium_history`
--

DROP TABLE IF EXISTS `premium_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `premium_history` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `boughtDate` datetime NOT NULL,
  `premiumUntil` datetime NOT NULL,
  `paid` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=891 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `premium_history`
--

LOCK TABLES `premium_history` WRITE;
/*!40000 ALTER TABLE `premium_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `premium_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processes`
--

DROP TABLE IF EXISTS `processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `processes` (
  `pid` int(32) NOT NULL AUTO_INCREMENT,
  `pCreatorID` int(5) NOT NULL,
  `pVictimID` int(5) NOT NULL,
  `pAction` smallint(3) NOT NULL,
  `pSoftID` int(32) NOT NULL,
  `pInfo` varchar(30) NOT NULL,
  `pInfoStr` text NOT NULL,
  `pTimeStart` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pTimePause` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pTimeEnd` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pTimeIdeal` int(5) NOT NULL,
  `pTimeWorked` int(11) NOT NULL,
  `cpuUsage` double NOT NULL,
  `netUsage` double NOT NULL,
  `pLocal` tinyint(1) NOT NULL,
  `pNPC` tinyint(1) NOT NULL,
  `isPaused` tinyint(1) NOT NULL,
  PRIMARY KEY (`pid`),
  KEY `pCreatorID` (`pCreatorID`),
  KEY `pNPC` (`pNPC`),
  KEY `pVictimID` (`pVictimID`),
  KEY `pTimeEnd` (`pTimeEnd`)
) ENGINE=InnoDB AUTO_INCREMENT=48831552 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processes`
--

LOCK TABLES `processes` WRITE;
/*!40000 ALTER TABLE `processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `processes_paused`
--

DROP TABLE IF EXISTS `processes_paused`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `processes_paused` (
  `pid` int(30) NOT NULL,
  `timeLeft` int(6) NOT NULL,
  `timePaused` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `userID` int(5) NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `processes_paused`
--

LOCK TABLES `processes_paused` WRITE;
/*!40000 ALTER TABLE `processes_paused` DISABLE KEYS */;
/*!40000 ALTER TABLE `processes_paused` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile` (
  `id` int(5) NOT NULL,
  `premium` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `reputation` bigint(15) NOT NULL,
  `rank` int(5) NOT NULL,
  `age` date NOT NULL,
  `timePlaying` float NOT NULL,
  `missionCount` int(5) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `ipResets` smallint(4) NOT NULL,
  `ddosCount` smallint(5) NOT NULL,
  `warezSent` double NOT NULL,
  `spamSent` bigint(15) NOT NULL,
  `mailSent` int(5) NOT NULL,
  `moneyEarned` bigint(11) NOT NULL,
  `moneyTransfered` bigint(11) NOT NULL,
  `moneyHardware` bigint(11) NOT NULL,
  `moneyResearch` bigint(11) NOT NULL,
  `profileViews` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile`
--

LOCK TABLES `profile` WRITE;
/*!40000 ALTER TABLE `profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `puzzle_solved`
--

DROP TABLE IF EXISTS `puzzle_solved`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `puzzle_solved` (
  `puzzleID` int(5) unsigned NOT NULL,
  `userID` int(5) unsigned NOT NULL,
  `solvedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `userID` (`userID`),
  KEY `puzzleID` (`puzzleID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `puzzle_solved`
--

LOCK TABLES `puzzle_solved` WRITE;
/*!40000 ALTER TABLE `puzzle_solved` DISABLE KEYS */;
/*!40000 ALTER TABLE `puzzle_solved` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_clan`
--

DROP TABLE IF EXISTS `ranking_clan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_clan` (
  `clanID` int(5) NOT NULL,
  `rank` int(5) NOT NULL,
  UNIQUE KEY `clanID` (`clanID`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_clan`
--

LOCK TABLES `ranking_clan` WRITE;
/*!40000 ALTER TABLE `ranking_clan` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_clan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_ddos`
--

DROP TABLE IF EXISTS `ranking_ddos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_ddos` (
  `ddosID` int(5) NOT NULL,
  `rank` int(5) NOT NULL DEFAULT '-1',
  KEY `rank` (`rank`),
  KEY `ddosID` (`ddosID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_ddos`
--

LOCK TABLES `ranking_ddos` WRITE;
/*!40000 ALTER TABLE `ranking_ddos` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_ddos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_software`
--

DROP TABLE IF EXISTS `ranking_software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_software` (
  `softID` int(5) NOT NULL,
  `rank` int(5) NOT NULL DEFAULT '-1',
  KEY `rank` (`rank`),
  KEY `softID` (`softID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_software`
--

LOCK TABLES `ranking_software` WRITE;
/*!40000 ALTER TABLE `ranking_software` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ranking_user`
--

DROP TABLE IF EXISTS `ranking_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ranking_user` (
  `userID` int(5) NOT NULL,
  `rank` int(5) NOT NULL,
  KEY `rank` (`rank`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ranking_user`
--

LOCK TABLES `ranking_user` WRITE;
/*!40000 ALTER TABLE `ranking_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `ranking_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round`
--

DROP TABLE IF EXISTS `round`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round` (
  `id` smallint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `startDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round`
--

LOCK TABLES `round` WRITE;
/*!40000 ALTER TABLE `round` DISABLE KEYS */;
/*!40000 ALTER TABLE `round` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round_ddos`
--

DROP TABLE IF EXISTS `round_ddos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round_ddos` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `attID` int(5) NOT NULL,
  `attUser` varchar(15) NOT NULL,
  `vicID` int(5) NOT NULL,
  `power` int(10) NOT NULL,
  `servers` int(3) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `vicNPC` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attID` (`attID`),
  KEY `attUser` (`attUser`),
  KEY `vicID` (`vicID`),
  KEY `power` (`power`)
) ENGINE=InnoDB AUTO_INCREMENT=290530 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round_ddos`
--

LOCK TABLES `round_ddos` WRITE;
/*!40000 ALTER TABLE `round_ddos` DISABLE KEYS */;
/*!40000 ALTER TABLE `round_ddos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `round_stats`
--

DROP TABLE IF EXISTS `round_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round_stats` (
  `id` tinyint(1) NOT NULL,
  `totalUsers` int(6) NOT NULL,
  `activeUsers` int(5) NOT NULL,
  `warezSent` double NOT NULL,
  `spamSent` bigint(15) NOT NULL,
  `bitcoinSent` double unsigned NOT NULL,
  `mailSent` int(6) NOT NULL,
  `ddosCount` int(6) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `clans` int(4) NOT NULL,
  `timePlaying` bigint(15) NOT NULL,
  `totalListed` int(6) NOT NULL,
  `totalVirus` int(5) NOT NULL,
  `totalMoney` bigint(20) NOT NULL,
  `researchCount` int(5) NOT NULL,
  `moneyResearch` int(20) unsigned NOT NULL,
  `moneyHardware` int(20) unsigned NOT NULL,
  `moneyEarned` int(20) unsigned NOT NULL,
  `moneyTransfered` int(20) unsigned NOT NULL,
  `usersClicks` int(10) unsigned NOT NULL,
  `missionCount` int(10) unsigned NOT NULL,
  `totalConnections` int(10) unsigned NOT NULL,
  `totalTasks` int(10) unsigned NOT NULL,
  `totalSoftware` int(10) unsigned NOT NULL,
  `totalRunning` int(10) unsigned NOT NULL,
  `totalServers` int(10) unsigned NOT NULL,
  `clansWar` int(10) unsigned NOT NULL,
  `clansMembers` int(5) unsigned NOT NULL,
  `clansClicks` int(10) unsigned NOT NULL,
  `onlineUsers` int(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `round_stats`
--

LOCK TABLES `round_stats` WRITE;
/*!40000 ALTER TABLE `round_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `round_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `safeNet`
--

DROP TABLE IF EXISTS `safeNet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `safeNet` (
  `IP` bigint(11) NOT NULL,
  `reason` tinyint(1) NOT NULL,
  `startTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `endTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `count` tinyint(3) NOT NULL DEFAULT '1',
  `onFBI` tinyint(1) NOT NULL,
  KEY `IP` (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `safeNet`
--

LOCK TABLES `safeNet` WRITE;
/*!40000 ALTER TABLE `safeNet` DISABLE KEYS */;
/*!40000 ALTER TABLE `safeNet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `server_stats`
--

DROP TABLE IF EXISTS `server_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_stats` (
  `id` tinyint(1) NOT NULL,
  `totalUsers` int(6) NOT NULL,
  `activeUsers` int(5) NOT NULL,
  `warezSent` double NOT NULL,
  `spamSent` bigint(15) NOT NULL,
  `mailSent` int(6) NOT NULL,
  `ddosCount` int(6) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `clans` int(4) NOT NULL,
  `timePlaying` bigint(15) NOT NULL,
  `totalListed` int(5) NOT NULL,
  `totalVirus` int(6) NOT NULL,
  `totalMoney` bigint(20) NOT NULL,
  `researchCount` int(5) NOT NULL,
  `researchMoney` bigint(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `server_stats`
--

LOCK TABLES `server_stats` WRITE;
/*!40000 ALTER TABLE `server_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `server_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software`
--

DROP TABLE IF EXISTS `software`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `userID` int(5) NOT NULL,
  `softName` varchar(25) NOT NULL,
  `softVersion` int(5) NOT NULL,
  `softSize` int(8) NOT NULL,
  `softRam` int(9) NOT NULL,
  `softType` smallint(2) NOT NULL,
  `softLastEdit` datetime NOT NULL,
  `softHidden` tinyint(1) NOT NULL,
  `softHiddenWith` bigint(20) NOT NULL,
  `isNPC` tinyint(1) NOT NULL,
  `originalFrom` bigint(20) NOT NULL,
  `licensedTo` int(5) NOT NULL,
  `isFolder` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`,`isNPC`)
) ENGINE=InnoDB AUTO_INCREMENT=13662031 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software`
--

LOCK TABLES `software` WRITE;
/*!40000 ALTER TABLE `software` DISABLE KEYS */;
/*!40000 ALTER TABLE `software` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_external`
--

DROP TABLE IF EXISTS `software_external`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_external` (
  `id` bigint(20) NOT NULL,
  `userID` int(5) NOT NULL,
  `softName` varchar(50) NOT NULL,
  `softVersion` int(5) NOT NULL,
  `softSize` int(5) NOT NULL,
  `softRam` int(5) NOT NULL,
  `softType` tinyint(3) NOT NULL,
  `uploadDate` datetime NOT NULL,
  `licensedTo` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_external`
--

LOCK TABLES `software_external` WRITE;
/*!40000 ALTER TABLE `software_external` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_external` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_folders`
--

DROP TABLE IF EXISTS `software_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_folders` (
  `folderID` int(30) NOT NULL,
  `softID` bigint(20) NOT NULL,
  KEY `folderID` (`folderID`),
  KEY `softID` (`softID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_folders`
--

LOCK TABLES `software_folders` WRITE;
/*!40000 ALTER TABLE `software_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_original`
--

DROP TABLE IF EXISTS `software_original`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_original` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `npcID` int(5) NOT NULL,
  `softName` varchar(20) NOT NULL,
  `softVersion` int(5) NOT NULL,
  `softSize` int(6) NOT NULL,
  `softRam` int(9) NOT NULL,
  `softType` smallint(2) NOT NULL,
  `running` tinyint(1) NOT NULL,
  `licensedTo` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9886 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_original`
--

LOCK TABLES `software_original` WRITE;
/*!40000 ALTER TABLE `software_original` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_original` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_research`
--

DROP TABLE IF EXISTS `software_research`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_research` (
  `researched_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(30) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(5) unsigned NOT NULL,
  `softID` int(10) unsigned NOT NULL,
  `softwareType` tinyint(2) unsigned NOT NULL,
  `newVersion` int(5) unsigned NOT NULL,
  `softwareName` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`),
  KEY `softID` (`softID`)
) ENGINE=InnoDB AUTO_INCREMENT=767171 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_research`
--

LOCK TABLES `software_research` WRITE;
/*!40000 ALTER TABLE `software_research` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_research` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_running`
--

DROP TABLE IF EXISTS `software_running`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_running` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `softID` int(30) NOT NULL,
  `userID` int(5) NOT NULL,
  `ramUsage` int(5) NOT NULL,
  `isNPC` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID_isNPC` (`userID`,`isNPC`),
  KEY `softID` (`softID`)
) ENGINE=InnoDB AUTO_INCREMENT=4803691 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_running`
--

LOCK TABLES `software_running` WRITE;
/*!40000 ALTER TABLE `software_running` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_running` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `software_texts`
--

DROP TABLE IF EXISTS `software_texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `software_texts` (
  `creator` bigint(20) DEFAULT NULL,
  `id` bigint(20) NOT NULL,
  `userID` int(5) NOT NULL,
  `isNPC` tinyint(1) NOT NULL,
  `text` text NOT NULL,
  `lastEdit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ddos` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userID_isNPC` (`userID`,`isNPC`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `software_texts`
--

LOCK TABLES `software_texts` WRITE;
/*!40000 ALTER TABLE `software_texts` DISABLE KEYS */;
/*!40000 ALTER TABLE `software_texts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_login`
--

DROP TABLE IF EXISTS `stats_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(5) unsigned NOT NULL,
  `loginTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=4585265 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_login`
--

LOCK TABLES `stats_login` WRITE;
/*!40000 ALTER TABLE `stats_login` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_register`
--

DROP TABLE IF EXISTS `stats_register`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_register` (
  `userID` int(5) unsigned NOT NULL,
  `registrationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(15) NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_register`
--

LOCK TABLES `stats_register` WRITE;
/*!40000 ALTER TABLE `stats_register` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_register` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `login` varchar(15) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(50) NOT NULL,
  `gamePass` varchar(8) NOT NULL,
  `gameIP` bigint(11) NOT NULL,
  `realIP` bigint(11) NOT NULL,
  `homeIP` bigint(11) NOT NULL,
  `learning` tinyint(1) NOT NULL DEFAULT '0',
  `premium` tinyint(1) NOT NULL,
  `lastLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gameIP` (`gameIP`),
  KEY `lastLogin` (`lastLogin`)
) ENGINE=InnoDB AUTO_INCREMENT=750703 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_admin`
--

DROP TABLE IF EXISTS `users_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_admin` (
  `userID` int(5) unsigned NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_admin`
--

LOCK TABLES `users_admin` WRITE;
/*!40000 ALTER TABLE `users_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_badge`
--

DROP TABLE IF EXISTS `users_badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_badge` (
  `userID` int(5) NOT NULL,
  `badgeID` smallint(3) NOT NULL,
  `round` tinyint(3) NOT NULL DEFAULT '0',
  `dateAdd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `priority` tinyint(5) NOT NULL,
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_badge`
--

LOCK TABLES `users_badge` WRITE;
/*!40000 ALTER TABLE `users_badge` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_badge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_banned`
--

DROP TABLE IF EXISTS `users_banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_banned` (
  `user_id` bigint(11) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_banned`
--

LOCK TABLES `users_banned` WRITE;
/*!40000 ALTER TABLE `users_banned` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_banned` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_expire`
--

DROP TABLE IF EXISTS `users_expire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_expire` (
  `userID` int(5) unsigned NOT NULL,
  `expireDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_expire`
--

LOCK TABLES `users_expire` WRITE;
/*!40000 ALTER TABLE `users_expire` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_expire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_facebook`
--

DROP TABLE IF EXISTS `users_facebook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_facebook` (
  `gameID` int(5) NOT NULL,
  `userID` bigint(20) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_facebook`
--

LOCK TABLES `users_facebook` WRITE;
/*!40000 ALTER TABLE `users_facebook` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_facebook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_friends`
--

DROP TABLE IF EXISTS `users_friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_friends` (
  `userID` int(5) NOT NULL,
  `friendID` int(5) NOT NULL,
  `dateAdd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`,`friendID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_friends`
--

LOCK TABLES `users_friends` WRITE;
/*!40000 ALTER TABLE `users_friends` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_language`
--

DROP TABLE IF EXISTS `users_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_language` (
  `userID` int(5) unsigned NOT NULL,
  `lang` varchar(2) NOT NULL DEFAULT 'en',
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_language`
--

LOCK TABLES `users_language` WRITE;
/*!40000 ALTER TABLE `users_language` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_learning`
--

DROP TABLE IF EXISTS `users_learning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_learning` (
  `userID` int(5) unsigned NOT NULL,
  `learning` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_learning`
--

LOCK TABLES `users_learning` WRITE;
/*!40000 ALTER TABLE `users_learning` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_learning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_online`
--

DROP TABLE IF EXISTS `users_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_online` (
  `id` int(5) NOT NULL,
  `loginTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `token` varchar(200) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_online`
--

LOCK TABLES `users_online` WRITE;
/*!40000 ALTER TABLE `users_online` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_online` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_premium`
--

DROP TABLE IF EXISTS `users_premium`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_premium` (
  `id` int(5) unsigned NOT NULL,
  `boughtDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `premiumUntil` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `totalPaid` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_premium`
--

LOCK TABLES `users_premium` WRITE;
/*!40000 ALTER TABLE `users_premium` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_premium` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_puzzle`
--

DROP TABLE IF EXISTS `users_puzzle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_puzzle` (
  `userID` int(5) unsigned NOT NULL,
  `puzzleID` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`),
  KEY `puzzleID` (`puzzleID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_puzzle`
--

LOCK TABLES `users_puzzle` WRITE;
/*!40000 ALTER TABLE `users_puzzle` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_puzzle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_stats`
--

DROP TABLE IF EXISTS `users_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_stats` (
  `uid` int(5) NOT NULL,
  `dateJoined` datetime NOT NULL,
  `exp` int(10) NOT NULL,
  `certifications` varchar(30) NOT NULL,
  `awards` varchar(50) NOT NULL,
  `timePlaying` float NOT NULL,
  `missionCount` int(5) NOT NULL,
  `hackCount` int(5) NOT NULL,
  `ddosCount` int(5) NOT NULL,
  `warezSent` double NOT NULL,
  `spamSent` bigint(15) NOT NULL,
  `bitcoinSent` double unsigned NOT NULL,
  `ipResets` int(5) NOT NULL,
  `lastIpReset` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pwdResets` mediumint(4) NOT NULL,
  `lastPwdReset` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `moneyEarned` bigint(15) NOT NULL,
  `moneyTransfered` bigint(15) NOT NULL,
  `moneyHardware` bigint(15) NOT NULL,
  `moneyResearch` bigint(15) NOT NULL,
  `profileViews` int(10) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_stats`
--

LOCK TABLES `users_stats` WRITE;
/*!40000 ALTER TABLE `users_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_twitter`
--

DROP TABLE IF EXISTS `users_twitter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_twitter` (
  `gameID` int(5) NOT NULL,
  `userID` int(20) unsigned NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_twitter`
--

LOCK TABLES `users_twitter` WRITE;
/*!40000 ALTER TABLE `users_twitter` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_twitter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virus`
--

DROP TABLE IF EXISTS `virus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virus` (
  `installedIp` bigint(11) NOT NULL,
  `installedBy` int(5) NOT NULL,
  `virusID` bigint(20) NOT NULL,
  `virusVersion` smallint(5) NOT NULL,
  `originalID` bigint(20) NOT NULL,
  `virusType` tinyint(2) NOT NULL,
  `lastCollect` varchar(19) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`virusID`),
  KEY `por_instalacao` (`installedIp`,`installedBy`),
  KEY `installedIp` (`installedIp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virus`
--

LOCK TABLES `virus` WRITE;
/*!40000 ALTER TABLE `virus` DISABLE KEYS */;
/*!40000 ALTER TABLE `virus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virus_ddos`
--

DROP TABLE IF EXISTS `virus_ddos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virus_ddos` (
  `userID` int(5) NOT NULL,
  `ip` bigint(11) NOT NULL,
  `ddosID` bigint(5) NOT NULL,
  `ddosName` varchar(30) NOT NULL,
  `ddosVersion` smallint(4) NOT NULL,
  `cpu` int(5) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`userID`,`ip`),
  KEY `ip` (`ip`),
  KEY `ddosID` (`ddosID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virus_ddos`
--

LOCK TABLES `virus_ddos` WRITE;
/*!40000 ALTER TABLE `virus_ddos` DISABLE KEYS */;
/*!40000 ALTER TABLE `virus_ddos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `virus_doom`
--

DROP TABLE IF EXISTS `virus_doom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virus_doom` (
  `doomID` bigint(20) NOT NULL,
  `doomIP` bigint(12) NOT NULL,
  `creatorID` int(5) NOT NULL,
  `clanID` int(5) NOT NULL,
  `releaseDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `doomDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL,
  KEY `doomID` (`doomID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virus_doom`
--

LOCK TABLES `virus_doom` WRITE;
/*!40000 ALTER TABLE `virus_doom` DISABLE KEYS */;
/*!40000 ALTER TABLE `virus_doom` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-05-15  8:30:50
