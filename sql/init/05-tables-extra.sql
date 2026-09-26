-- Tables supplementaires presentes en production mais absentes du schema initial.
-- Structure seule, extraite du dump de production (sql/donnees-locales.sql).
-- Aucune donnee utilisateur.

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `api_players`;
CREATE TABLE `api_players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rank` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `city_count` int(11) NOT NULL,
  `invaders_count` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_key_player` tinyint(1) DEFAULT 0,
  `pattern` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11512 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `invit_users`;
CREATE TABLE `invit_users` (
  `username` text DEFAULT NULL,
  `invitcode` text DEFAULT NULL,
  `user_type` text DEFAULT 'normal',
  `status` text DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `last_maj_invaders`;
CREATE TABLE `last_maj_invaders` (
  `idx` smallint(6) DEFAULT NULL,
  `inv_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_name` varchar(255) NOT NULL,
  `rank` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `city_count` int(11) NOT NULL,
  `invaders_count` int(11) NOT NULL,
  `fetched_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
