-- Structure de la base Invader Mapper
-- Genere depuis la base de production le 2026-09-26
-- Aucune donnee utilisateur : structure seule + donnees de reference.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- ---------------------------------------------------------------
-- Tables
-- ---------------------------------------------------------------

CREATE TABLE `api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `key_hash` char(64) NOT NULL,
  `label` varchar(100) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `request_count` int(10) unsigned NOT NULL DEFAULT 0,
  `window_started_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_key_hash` (`key_hash`),
  KEY `idx_user_name` (`user_name`),
  KEY `idx_active` (`key_hash`,`revoked_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `badges` (
  `id_badge` int(11) NOT NULL AUTO_INCREMENT,
  `nom_badge` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icone` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `fonction` text DEFAULT NULL,
  `arguments` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT NULL,
  `niveau` text DEFAULT NULL,
  PRIMARY KEY (`id_badge`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `deleted_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `deletion_requested_at` datetime DEFAULT NULL,
  `purged_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `etat` (
  `idx` int(11) NOT NULL AUTO_INCREMENT,
  `inv_name` text DEFAULT NULL,
  `points` text DEFAULT NULL,
  `etat` text DEFAULT NULL,
  `last_maj` text DEFAULT NULL,
  `image1` text DEFAULT NULL,
  `image2` text DEFAULT NULL,
  `image3` text DEFAULT NULL,
  KEY `etat_idx_IDX` (`idx`) USING BTREE,
  KEY `idx_etat_etat` (`etat`(768))
) ENGINE=InnoDB AUTO_INCREMENT=4422334 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table automatique contenant les états des invaders (bon état';
CREATE TABLE `modif_state_user` (
  `user_name` text DEFAULT NULL,
  `inv_name` text DEFAULT NULL,
  `etat` text DEFAULT NULL,
  `date_modif` date DEFAULT NULL,
  KEY `idx_modif_state_user_inv_name_user_name` (`inv_name`(15),`user_name`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `positions` (
  `inv_name` varchar(1024) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `photo` varchar(1024) DEFAULT NULL,
  `alti` int(11) DEFAULT NULL,
  UNIQUE KEY `inv_name` (`inv_name`) USING HASH,
  KEY `idx_positions_lat_lon` (`lat`,`lon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `device_id` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_token_device` (`token`,`device_id`),
  KEY `idx_username` (`username`),
  KEY `idx_expires` (`expires`),
  KEY `idx_username_device` (`username`,`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=846 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `uid_table` (
  `uid` text DEFAULT NULL,
  `url_datas` text DEFAULT NULL,
  `body_datas` longblob DEFAULT NULL,
  `other_datas` longblob DEFAULT NULL,
  `cookies` longblob DEFAULT NULL,
  `image_exif` text DEFAULT NULL,
  `image_info` text DEFAULT NULL,
  `headers` text DEFAULT NULL,
  `image_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `user_badges` (
  `id_user` int(11) NOT NULL,
  `id_badge` int(11) NOT NULL,
  `date_obtention` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`,`id_badge`),
  KEY `id_badge` (`id_badge`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`),
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`id_badge`) REFERENCES `badges` (`id_badge`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `user_config` (
  `user_name` text DEFAULT NULL,
  `categories_map` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `user_connexions` (
  `user_name` text DEFAULT NULL,
  `temps` timestamp NULL DEFAULT NULL,
  `device_type` text DEFAULT NULL,
  `browser` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `user_flash` (
  `user_name` text DEFAULT NULL,
  `inv_name` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `date_flash` datetime DEFAULT NULL,
  KEY `idx_user_flash_inv_name_status_user_name` (`inv_name`(15),`status`(40),`user_name`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `user_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `inv_name` varchar(20) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `credit` tinyint(1) DEFAULT 1,
  `validated` tinyint(1) DEFAULT 0,
  `upload_date` datetime DEFAULT current_timestamp(),
  `validation_date` datetime DEFAULT NULL,
  `validated_by` varchar(50) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `idx_inv_name_user_photos` (`inv_name`),
  KEY `idx_status_user_photos` (`status`),
  CONSTRAINT `user_photos_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1315 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `users` (
  `login` text NOT NULL,
  `name` text DEFAULT NULL,
  `pwd` text DEFAULT NULL,
  `game_name` text DEFAULT NULL,
  `user_type` varchar(100) DEFAULT 'normal',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text DEFAULT NULL,
  `uid_flashinvader` text DEFAULT NULL,
  `deletion_requested_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_deletion_requested` (`deletion_requested_at`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `villes` (
  `short_name` text DEFAULT NULL,
  `long_name` text DEFAULT NULL,
  `fr_ville` text DEFAULT NULL,
  `fr_pays` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `ville_distances` (
  `ville1` text NOT NULL,
  `ville2` text NOT NULL,
  `distance_km` double DEFAULT NULL,
  PRIMARY KEY (`ville1`(100) DESC,`ville2`(100) DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------
-- Vues
-- ---------------------------------------------------------------

CREATE VIEW `ville_centroides` AS select substring_index(`positions`.`inv_name`,'_',1) AS `ville`,avg(`positions`.`lat`) AS `lat`,avg(`positions`.`lon`) AS `lon` from `positions` where `positions`.`inv_name` not like '%DELETED%' and `positions`.`inv_name` not like 'SPACE%' group by substring_index(`positions`.`inv_name`,'_',1);

CREATE VIEW `liste_etats` AS select distinct `etat`.`etat` AS `etat` from `etat`;

SET FOREIGN_KEY_CHECKS=1;
