-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server Version:               10.4.22-MariaDB - mariadb.org binary distribution
-- Server Betriebssystem:        Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;


-- Exportiere Datenbank Struktur für mvc
CREATE DATABASE IF NOT EXISTS `mvc` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `mvc`;

-- Exportiere Struktur von Tabelle mvc.actors
CREATE TABLE IF NOT EXISTS `actors`
(
    `id`             int(10) unsigned NOT NULL AUTO_INCREMENT,
    `email`          varchar(120)     NOT NULL,
    `password`       varchar(256)     NOT NULL,
    `first_name`     varchar(40)      NOT NULL,
    `last_name`      varchar(40)      NOT NULL,
    `login_fails`    smallint(6)      NOT NULL DEFAULT 0,
    `login_disabled` tinyint(1)       NOT NULL DEFAULT 0,
    `created`        datetime         NOT NULL DEFAULT current_timestamp(),
    `updated`        datetime                  DEFAULT NULL,
    `deleted`        datetime                  DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `actors_email_ukey` (`email`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4;

-- Exportiere Daten aus Tabelle mvc.actors: ~1 rows (ungefähr)
/*!40000 ALTER TABLE `actors`
    DISABLE KEYS */;
INSERT INTO `actors` (`id`, `email`, `password`, `first_name`, `last_name`, `login_fails`, `login_disabled`, `created`,
                      `updated`, `deleted`)
VALUES (1, 'admin@localhost.de', '$2y$16$Di4cP5/7IF4Axo/pGOWb0.JPzsprrUH6AFQPjJJYrsypXUMB6QLei', 'admin', 'super', 0, 0,
        '2022-02-27 01:45:19', NULL, NULL);
/*!40000 ALTER TABLE `actors`
    ENABLE KEYS */;

-- Exportiere Struktur von Tabelle mvc.sessions
CREATE TABLE IF NOT EXISTS `sessions`
(
    `id`       varchar(180)     NOT NULL,
    `actor_id` int(10) unsigned NOT NULL,
    `expired`  datetime         NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_actor_id_fkey` (`actor_id`),
    CONSTRAINT `sessions_actor_id_fkey` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- Exportiere Daten aus Tabelle mvc.sessions: ~1 rows (ungefähr)
/*!40000 ALTER TABLE `sessions`
    DISABLE KEYS */;
INSERT INTO `sessions` (`id`, `actor_id`, `expired`)
VALUES ('2dd76c49d9424032dce7aad3585af8e9', 1, '2022-02-28 23:46:26'),
       ('4cbcb67419def5c3afe2b1543cc9ca16', 1, '2022-02-27 19:55:29');
/*!40000 ALTER TABLE `sessions`
    ENABLE KEYS */;

/*!40101 SET SQL_MODE = IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS = IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES = IFNULL(@OLD_SQL_NOTES, 1) */;
