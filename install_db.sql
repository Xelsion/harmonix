-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               10.4.22-MariaDB - mariadb.org binary distribution
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             11.1.0.6116
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Exportiere Datenbank Struktur für mvc
CREATE DATABASE IF NOT EXISTS `mvc` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `mvc`;

-- Exportiere Struktur von Tabelle mvc.access_permissions
CREATE TABLE IF NOT EXISTS `access_permissions` (
  `actor_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `domain` varchar(50) NOT NULL,
  `controller` varchar(50) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  UNIQUE KEY `actor_permissions_ukey` (`actor_id`,`role_id`,`domain`,`controller`,`method`) USING BTREE,
  KEY `actor_permissions_actor_id_fkey` (`actor_id`) USING BTREE,
  KEY `actor_permissions_role_id_fkey` (`role_id`),
  CONSTRAINT `actor_permissions_actor_id_fkey` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`),
  CONSTRAINT `actor_permissions_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `actor_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.access_permissions: ~2 rows (ungefähr)
/*!40000 ALTER TABLE `access_permissions` DISABLE KEYS */;
INSERT INTO `access_permissions` (`actor_id`, `role_id`, `domain`, `controller`, `method`) VALUES
	(1, 1, 'admin', NULL, NULL),
	(1, 1, 'www', NULL, NULL);
/*!40000 ALTER TABLE `access_permissions` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.access_restrictions
CREATE TABLE IF NOT EXISTS `access_restrictions` (
  `domain` varchar(50) NOT NULL,
  `controller` varchar(50) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `restriction_type` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  UNIQUE KEY `access_restrictions_ukey` (`domain`,`controller`,`method`,`restriction_type`,`role_id`),
  KEY `access_restrictions_role_id_fkey` (`role_id`),
  CONSTRAINT `access_restrictions_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `actor_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.access_restrictions: ~6 rows (ungefähr)
/*!40000 ALTER TABLE `access_restrictions` DISABLE KEYS */;
INSERT INTO `access_restrictions` (`domain`, `controller`, `method`, `restriction_type`, `role_id`) VALUES
	('admin', NULL, NULL, 1, 1),
	('admin', 'controller\\admin\\ErrorController', NULL, 1, 4),
	('admin', 'controller\\admin\\HomeController', NULL, 1, 4),
	('www', NULL, NULL, 3, 3),
	('www', 'controller\\www\\ErrorController', NULL, 1, 4),
	('www', 'controller\\www\\HomeController', NULL, 1, 4);
/*!40000 ALTER TABLE `access_restrictions` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.access_restriction_types
CREATE TABLE IF NOT EXISTS `access_restriction_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `include_siblings` tinyint(1) NOT NULL DEFAULT 0,
  `include_children` tinyint(1) NOT NULL DEFAULT 0,
  `include_descendants` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.access_restriction_types: ~4 rows (ungefähr)
/*!40000 ALTER TABLE `access_restriction_types` DISABLE KEYS */;
INSERT INTO `access_restriction_types` (`id`, `name`, `include_siblings`, `include_children`, `include_descendants`) VALUES
	(1, 'Only This', 0, 0, 0),
	(2, 'Same Parent', 1, 0, 0),
	(3, 'Include only Children', 0, 1, 0),
	(4, 'Include All Descendants', 0, 0, 1);
/*!40000 ALTER TABLE `access_restriction_types` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.actors
CREATE TABLE IF NOT EXISTS `actors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(120) NOT NULL,
  `password` varchar(256) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `login_fails` smallint(6) NOT NULL DEFAULT 0,
  `login_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actors_email_ukey` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.actors: ~0 rows (ungefähr)
/*!40000 ALTER TABLE `actors` DISABLE KEYS */;
INSERT INTO `actors` (`id`, `email`, `password`, `first_name`, `last_name`, `login_fails`, `login_disabled`, `created`, `updated`, `deleted`) VALUES
	(1, 'admin@localhost.de', '$2y$10$D3JrwZhUeKJ7b2XZnnhXMu5byRZFpIBg07rNSMqIRtf0tNFvEMFZ.', 'admin', 'super', 0, 0, '2022-02-27 01:45:19', NULL, NULL);
/*!40000 ALTER TABLE `actors` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.actor_roles
CREATE TABLE IF NOT EXISTS `actor_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `child_of` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `rights_all` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rights_group` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rights_own` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_protected` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `actor_roles_child_of_fkey` (`child_of`),
  CONSTRAINT `actor_roles_child_of_fkey` FOREIGN KEY (`child_of`) REFERENCES `actor_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.actor_roles: ~4 rows (ungefähr)
/*!40000 ALTER TABLE `actor_roles` DISABLE KEYS */;
INSERT INTO `actor_roles` (`id`, `child_of`, `name`, `rights_all`, `rights_group`, `rights_own`, `is_protected`, `is_default`) VALUES
	(1, NULL, 'Administrator', 15, 0, 0, 1, 0),
	(2, 1, 'Moderator', 8, 7, 0, 1, 0),
	(3, 2, 'Member', 0, 8, 7, 1, 0),
	(4, NULL, 'Guest', 0, 0, 0, 1, 1);
/*!40000 ALTER TABLE `actor_roles` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(180) NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `ip` varchar(15) NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_actor_id_fkey` (`actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.sessions: ~1 rows (ungefähr)
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` (`id`, `actor_id`, `ip`, `expired`) VALUES
	('0c06b1d1df8e757541db19fceaba4350', 1, '127.0.0.1', '2022-04-05 18:40:52');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
