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
DROP DATABASE IF EXISTS `mvc`;
CREATE DATABASE IF NOT EXISTS `mvc` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `mvc`;

-- Exportiere Struktur von Tabelle mvc.access_permissions
DROP TABLE IF EXISTS `access_permissions`;
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
DROP TABLE IF EXISTS `access_restrictions`;
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
DROP TABLE IF EXISTS `access_restriction_types`;
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
DROP TABLE IF EXISTS `actors`;
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
  PRIMARY KEY (`id`),
  KEY `actor_roles_child_of_fkey` (`child_of`),
  CONSTRAINT `actor_roles_child_of_fkey` FOREIGN KEY (`child_of`) REFERENCES `actor_roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.actor_roles: ~3 rows (ungefähr)
/*!40000 ALTER TABLE `actor_roles` DISABLE KEYS */;
INSERT INTO `actor_roles` (`id`, `child_of`, `name`, `rights_all`, `rights_group`, `rights_own`, `is_protected`, `is_default`) VALUES
	(1, NULL, 'Administrator', 15, 0, 0, 1, 0),
	(2, 1, 'Moderator', 8, 7, 0, 1, 0),
	(3, 2, 'Member', 0, 8, 7, 1, 0),
	(4, NULL, 'Guest', 0, 0, 0, 1, 1);
/*!40000 ALTER TABLE `actor_roles` ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(180) NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_actor_id_fkey` (`actor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exportiere Daten aus Tabelle mvc.sessions: ~57 rows (ungefähr)
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` (`id`, `actor_id`, `expired`) VALUES
	('02c2a0d9584de8c5de395d5b10c924fa', 2, '2022-03-21 11:36:25'),
	('06116e859cd33f33563ae0fed6845761', 1, '2022-03-04 11:08:43'),
	('0c06b1d1df8e757541db19fceaba4350', 1, '2022-04-05 16:52:19'),
	('117e62cc913304ea1b95e51bb84ec653', 1, '2022-03-04 11:20:23'),
	('12b0f119f10e3a71c475dd3c38ff86b3', 1, '2022-03-04 10:55:14'),
	('1427999a046d7b91f8e44a2bd241d99f', 1, '2022-03-04 11:15:37'),
	('156e726543a27a156b9633df8f37bfba', 1, '2022-03-04 11:32:24'),
	('17c8ff288c97da249a7b4495c339b5bd', 1, '2022-03-04 11:06:48'),
	('1a2b77801e6f6b9ce0f7f487be7b717a', 1, '2022-03-29 14:19:39'),
	('1d9004e721a2d898873130a2bbc6cb18', 1, '2022-03-04 11:30:32'),
	('1e2150934df535089f9eb1d2d27a2f03', 1, '2022-03-04 11:03:18'),
	('2344d93e498263d98002732e234fd0e1', 1, '2022-03-04 11:25:37'),
	('25b6632f3c6eacfb67e12e50e9f62b64', 1, '2022-03-28 11:45:40'),
	('25f6591e213c605826d883e8a0190efb', 1, '2022-03-04 11:25:04'),
	('26451e354dbd661015381f9f44e3d376', 1, '2022-03-04 11:54:17'),
	('287f01cd0a3c970f36af3028e82e5818', 1, '2022-03-04 11:33:52'),
	('2dd61942ca719ee924c7d366f6238162', 1, '2022-03-04 11:27:28'),
	('2dd76c49d9424032dce7aad3585af8e9', 1, '2022-02-28 23:46:26'),
	('2e9ae129b92539a5d7594e653d2c98a1', 1, '2022-03-04 11:04:30'),
	('341d7d3c9b0fd741534a947438dd6205', 1, '2022-03-04 11:24:18'),
	('3bf23a8689efe78d6e99e77e0f32c119', 1, '2022-03-04 17:45:22'),
	('3c18db264302bb0a1ba1b98096ac6c41', 1, '2022-03-04 11:36:39'),
	('413e1aeb2f698cdd3d3ad145b95fc6fd', 1, '2022-03-04 11:09:50'),
	('4440a16ef55b68f66886fe50253fa4cf', 1, '2022-03-04 11:10:22'),
	('450fad25107f0753cbbef41954a9fb5a', 2, '2022-03-21 11:34:03'),
	('45efab11b63a9638b50d7877f8ab4856', 1, '2022-03-04 16:19:17'),
	('4a7447b7465d322a9281bec4620856db', 1, '2022-03-04 11:20:44'),
	('4cbcb67419def5c3afe2b1543cc9ca16', 1, '2022-02-27 19:55:29'),
	('4d2e65f25e79c14590e5d4e7c150ab40', 1, '2022-03-18 12:21:49'),
	('576e3b04058b238dbde702e90ca43f48', 1, '2022-03-04 11:09:47'),
	('58fdf80da8024e1849f03f027bb70e9c', 1, '2022-03-15 10:15:59'),
	('591e80cd641f6c252bdde4a72f847427', 1, '2022-03-04 11:09:12'),
	('5a4f51dc138ef059c22397513e1fdd1c', 1, '2022-03-04 11:04:59'),
	('5ad663faaed6936d7ac41a6d6af18d14', 1, '2022-03-04 10:59:47'),
	('5c18aeae0f2a1313f9b45fb4afb4bf1f', 2, '2022-03-21 12:34:05'),
	('5e7141d4d41a8eca74cd4f89d55b8c72', 1, '2022-03-04 09:38:22'),
	('604d533af5180169151d6f764981992d', 2, '2022-03-21 12:34:44'),
	('65e5216066fed3642257590cd6560de7', 1, '2022-03-04 11:25:55'),
	('6b77218fabe6223eacd36d2af3b14f79', 1, '2022-03-04 11:06:16'),
	('6f636b73ae795e427fe688332f9a5de5', 2, '2022-03-21 12:33:14'),
	('6fae03412fbc39dcbf4a1a2cb36c5c24', 1, '2022-03-04 11:29:40'),
	('719d0f30d7220aecb9d24bd5d5967f4c', 1, '2022-03-10 15:06:59'),
	('786f8eaad2a3f8b25612721c66985f6c', 1, '2022-03-29 11:32:24'),
	('7a99e968d2637ebf5365cb8d2986624c', 1, '2022-03-04 11:26:54'),
	('7de729f55079a9372a0575ff66a79343', 1, '2022-03-04 11:37:21'),
	('818e038c940fc4102c48b020db446ed9', 1, '2022-03-04 11:50:54'),
	('8694a367ab8b5661c18f0d73d849681e', 1, '2022-03-04 09:48:36'),
	('877b3c4f9ca90f8b9b912750dd07b09f', 2, '2022-03-21 11:35:46'),
	('891aacb99cf0c81738362b6a009c96d7', 1, '2022-03-04 11:38:27'),
	('911cec8766c92199a35c404f6cc2fe02', 1, '2022-03-04 11:08:21'),
	('92d5a0ca86e432068bea7ff7d79e094a', 1, '2022-03-04 11:11:47'),
	('94e6b6f85a6e831035dd976d7141a32d', 1, '2022-03-04 11:10:38'),
	('96d1af65a5b52f235888f065bab1da5b', 1, '2022-03-04 11:51:49'),
	('99270907f235227ca44f657d23a37436', 1, '2022-03-04 11:50:11'),
	('9c35a496f19c2375c899c702e7f0949d', 1, '2022-03-04 11:05:59'),
	('9f0a0227f27b42e51aedce28182aa8f2', 2, '2022-03-21 11:35:15'),
	('a62e18e7b66fee4dd0d5b1ade138b5a1', 1, '2022-04-05 10:44:01');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
