-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               10.4.27-MariaDB - mariadb.org binary distribution
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             12.3.0.6589
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Exportiere Datenbank Struktur für mvc
DROP DATABASE IF EXISTS `mvc`;
CREATE DATABASE IF NOT EXISTS `mvc` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `mvc`;

-- Exportiere Struktur von Tabelle mvc.access_permissions
DROP TABLE IF EXISTS `access_permissions`;
CREATE TABLE IF NOT EXISTS `access_permissions` (
  `actor_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `domain` varchar(50) NOT NULL,
  `controller` varchar(50) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  UNIQUE KEY `actor_permissions_ukey` (`actor_id`,`role_id`,`domain`,`controller`,`method`) USING BTREE,
  KEY `actor_permissions_actor_id_fkey` (`actor_id`) USING BTREE,
  KEY `actor_permissions_role_id_fkey` (`role_id`),
  KEY `idx_access_permissions` (`domain`,`controller`,`method`),
  CONSTRAINT `actor_permissions_actor_id_fkey` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `actor_permissions_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `actor_roles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.access_permissions: ~6 rows (ungefähr)
INSERT INTO `access_permissions` (`actor_id`, `role_id`, `domain`, `controller`, `method`, `created`, `updated`, `deleted`) VALUES
	(1, 1, 'admin', NULL, NULL, '2022-04-06 14:03:35', NULL, NULL),
	(1, 1, 'www', NULL, NULL, '2022-04-06 14:03:35', NULL, NULL),
	(2, 2, 'admin', NULL, NULL, '2022-05-03 14:16:18', NULL, NULL),
	(2, 2, 'www', NULL, NULL, '2022-05-03 14:16:18', NULL, NULL),
	(3, 3, 'admin', NULL, NULL, '2022-05-03 14:17:28', NULL, NULL),
	(3, 3, 'www', NULL, NULL, '2022-05-03 14:17:28', NULL, NULL);

-- Exportiere Struktur von Tabelle mvc.access_restrictions
DROP TABLE IF EXISTS `access_restrictions`;
CREATE TABLE IF NOT EXISTS `access_restrictions` (
  `domain` varchar(50) NOT NULL,
  `controller` varchar(50) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `restriction_type` int(11) unsigned NOT NULL,
  `role_id` int(11) unsigned NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  UNIQUE KEY `access_restrictions_ukey` (`domain`,`controller`,`method`,`restriction_type`,`role_id`),
  KEY `access_restrictions_role_id_fkey` (`role_id`),
  KEY `fk_access_restrictions` (`restriction_type`),
  KEY `idx_access_restrictions` (`domain`,`controller`,`method`),
  CONSTRAINT `access_restrictions_role_id_fkey` FOREIGN KEY (`role_id`) REFERENCES `actor_roles` (`id`),
  CONSTRAINT `fk_access_restrictions` FOREIGN KEY (`restriction_type`) REFERENCES `access_restriction_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.access_restrictions: ~9 rows (ungefähr)
INSERT INTO `access_restrictions` (`domain`, `controller`, `method`, `restriction_type`, `role_id`, `created`, `updated`, `deleted`) VALUES
	('admin', NULL, NULL, 1, 1, '2023-01-03 10:03:54', NULL, NULL),
	('admin', 'controller\\admin\\ActorController', NULL, 1, 2, '2023-01-03 10:03:54', NULL, NULL),
	('admin', 'controller\\admin\\CacheFileController', NULL, 1, 1, '2023-01-03 10:03:54', NULL, NULL),
	('admin', 'controller\\admin\\ErrorController', NULL, 1, 4, '2023-01-03 10:03:54', NULL, NULL),
	('admin', 'controller\\admin\\HomeController', 'index', 2, 4, '2023-01-03 10:03:54', NULL, NULL),
	('admin', 'controller\\admin\\RoutesController', NULL, 1, 1, '2023-01-03 10:03:54', NULL, NULL),
	('www', NULL, NULL, 3, 3, '2023-01-03 10:03:54', NULL, NULL),
	('www', 'controller\\www\\ErrorController', NULL, 1, 4, '2023-01-03 10:03:54', NULL, NULL),
	('www', 'controller\\www\\HomeController', NULL, 1, 4, '2023-01-03 10:03:54', NULL, NULL);

-- Exportiere Struktur von Tabelle mvc.access_restriction_types
DROP TABLE IF EXISTS `access_restriction_types`;
CREATE TABLE IF NOT EXISTS `access_restriction_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `include_siblings` tinyint(1) NOT NULL DEFAULT 0,
  `include_children` tinyint(1) NOT NULL DEFAULT 0,
  `include_descendants` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.access_restriction_types: ~4 rows (ungefähr)
INSERT INTO `access_restriction_types` (`id`, `name`, `include_siblings`, `include_children`, `include_descendants`, `created`, `updated`, `deleted`) VALUES
	(1, 'Nur Diese', 0, 0, 0, '2022-04-06 11:03:52', '2022-04-06 16:17:46', NULL),
	(2, 'Selbe Obergruppe', 1, 0, 0, '2022-04-06 11:03:52', '2022-04-06 16:18:04', NULL),
	(3, 'Inkl. direkter Untergruppen', 0, 1, 0, '2022-04-06 11:03:52', '2022-04-06 16:18:38', NULL),
	(4, 'Inkl. Aller Untergruppen', 0, 0, 1, '2022-04-06 11:03:52', '2022-04-06 16:19:05', NULL);

-- Exportiere Struktur von Tabelle mvc.actors
DROP TABLE IF EXISTS `actors`;
CREATE TABLE IF NOT EXISTS `actors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(10) unsigned NOT NULL DEFAULT 0,
  `email` varchar(120) NOT NULL,
  `password` varchar(256) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `login_fails` smallint(6) NOT NULL DEFAULT 0,
  `login_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actors_email_ukey` (`email`),
  KEY `FK_actors_actor_types` (`type_id`),
  KEY `idx_actors` (`email`,`password`),
  KEY `idx_actors_0` (`first_name`,`last_name`),
  CONSTRAINT `FK_actors_actor_types` FOREIGN KEY (`type_id`) REFERENCES `actor_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.actors: ~3 rows (ungefähr)
INSERT INTO `actors` (`id`, `type_id`, `email`, `password`, `first_name`, `last_name`, `login_fails`, `login_disabled`, `created`, `updated`, `deleted`) VALUES
	(1, 1, 'admin@localhost.de', '$2y$10$D3JrwZhUeKJ7b2XZnnhXMu5byRZFpIBg07rNSMqIRtf0tNFvEMFZ.', 'Markus', 'Schröder', 0, 0, '2022-02-27 01:45:19', '2022-11-10 09:10:32', NULL),
	(2, 2, 'moderator@localhost.de', '$2y$10$WQ0SO8aanfKbEtZMTG4ed.xrHid4fKAh78qhzf2yuYZddaqXgMj/2', 'Site', 'Moderator', 2, 0, '2022-05-03 14:16:18', '2022-11-10 09:10:21', NULL),
	(3, 2, 'member@localhost.de', '$2y$10$GDXxC0lHHXlvmlFF9nj3E.umDqT4v03AHeMnuVh1U8gKnemrnfaaG', 'Site', 'Member', 1, 0, '2022-05-03 14:17:28', '2023-01-26 16:15:40', NULL);

-- Exportiere Struktur von Tabelle mvc.actor_data
DROP TABLE IF EXISTS `actor_data`;
CREATE TABLE IF NOT EXISTS `actor_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` int(10) unsigned NOT NULL,
  `connection_id` int(10) unsigned DEFAULT NULL,
  `data_key` varchar(80) NOT NULL,
  `data_value` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_actor_data_actors` (`actor_id`),
  KEY `fk_actor_data_data_connections` (`connection_id`),
  CONSTRAINT `fk_actor_data_actors` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_actor_data_data_connections` FOREIGN KEY (`connection_id`) REFERENCES `data_connections` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.actor_data: ~0 rows (ungefähr)

-- Exportiere Struktur von Tabelle mvc.actor_roles
DROP TABLE IF EXISTS `actor_roles`;
CREATE TABLE IF NOT EXISTS `actor_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `child_of` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `rights_all` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rights_group` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `rights_own` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_protected` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actor_roles_child_of_fkey` (`child_of`),
  CONSTRAINT `actor_roles_child_of_fkey` FOREIGN KEY (`child_of`) REFERENCES `actor_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.actor_roles: ~6 rows (ungefähr)
INSERT INTO `actor_roles` (`id`, `child_of`, `name`, `rights_all`, `rights_group`, `rights_own`, `is_protected`, `is_default`, `created`, `updated`, `deleted`) VALUES
	(1, NULL, 'Administrator', 15, 0, 0, 1, 0, '2022-04-06 11:00:11', NULL, NULL),
	(2, 1, 'Moderator', 8, 7, 0, 1, 0, '2022-04-06 11:00:11', NULL, NULL),
	(3, 2, 'Member', 0, 8, 7, 1, 0, '2022-04-06 11:00:11', NULL, NULL),
	(4, NULL, 'Guest', 0, 0, 0, 1, 1, '2022-04-06 11:00:11', NULL, NULL),
	(5, 3, 'Owner', 0, 0, 8, 0, 0, '2022-11-08 10:55:35', '2023-01-26 14:53:58', NULL);

-- Exportiere Struktur von Tabelle mvc.actor_types
DROP TABLE IF EXISTS `actor_types`;
CREATE TABLE IF NOT EXISTS `actor_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `is_protected` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.actor_types: ~2 rows (ungefähr)
INSERT INTO `actor_types` (`id`, `name`, `is_protected`, `created`, `updated`, `deleted`) VALUES
	(1, 'Developer', 1, '2022-11-10 15:04:52', NULL, NULL),
	(2, 'User', 1, '2022-11-10 15:04:52', NULL, NULL);

-- Exportiere Struktur von Tabelle mvc.data_connections
DROP TABLE IF EXISTS `data_connections`;
CREATE TABLE IF NOT EXISTS `data_connections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `db_name` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `table_col` varchar(40) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.data_connections: ~0 rows (ungefähr)
INSERT INTO `data_connections` (`id`, `name`, `db_name`, `table_name`, `table_col`, `created`, `updated`, `deleted`) VALUES
	(1, 'phs_mitarbeiter', 'phs', 'phs_mitarbeiter', 'mid', '2023-01-04 14:29:52', '2023-01-04 16:23:39', NULL);

-- Exportiere Struktur von Tabelle mvc.data_connection_columns
DROP TABLE IF EXISTS `data_connection_columns`;
CREATE TABLE IF NOT EXISTS `data_connection_columns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection_id` int(10) unsigned NOT NULL,
  `column_name` varchar(100) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_actor_links_columns` (`connection_id`),
  CONSTRAINT `fk_actor_links_columns` FOREIGN KEY (`connection_id`) REFERENCES `data_connections` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.data_connection_columns: ~4 rows (ungefähr)
INSERT INTO `data_connection_columns` (`id`, `connection_id`, `column_name`, `created`, `updated`, `deleted`) VALUES
	(1, 1, 'title', '2023-01-04 15:53:02', NULL, NULL),
	(2, 1, 'street', '2023-01-04 15:53:02', NULL, NULL),
	(3, 1, 'post_code', '2023-01-04 15:53:02', NULL, NULL),
	(4, 1, 'company_tel', '2023-01-04 15:53:02', NULL, NULL);

-- Exportiere Struktur von Tabelle mvc.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(180) NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `as_actor` int(10) unsigned DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `expired` datetime NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sessions_actor_id_fkey` (`actor_id`),
  CONSTRAINT `FK_sessions_actors` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Exportiere Daten aus Tabelle mvc.sessions: ~4 rows (ungefähr)
INSERT INTO `sessions` (`id`, `actor_id`, `as_actor`, `ip`, `expired`, `created`, `updated`) VALUES
	('15fa11e0-07b7-46ca-bcb6-0278b225cae6', 1, 0, '127.0.0.1', '2024-01-17 13:51:03', '2023-01-17 13:51:03', NULL),
	('1c45d63e-c281-4a77-b08f-5b1c9f5ac5fc', 1, 0, '127.0.0.1', '2023-01-27 12:36:28', '2023-01-27 09:28:48', '2023-01-27 10:36:28'),
	('554ac671-a74f-4131-9d80-eca1468a1edb', 1, 0, '127.0.0.1', '2024-01-04 08:45:39', '2023-01-04 08:45:39', NULL),
	('864dabaf-d979-43b3-a8a8-da709efe9c46', 1, 0, '127.0.0.1', '2024-01-17 13:59:22', '2023-01-17 13:59:22', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
