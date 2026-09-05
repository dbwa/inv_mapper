-- ============================================================================
-- API Invaders Mapper - Creation de la table des cles API
-- ============================================================================
--
-- A executer UNE SEULE FOIS dans phpMyAdmin (ou en ligne de commande) sur la
-- base nom_de_la_base.
--
-- Aucune table existante n'est modifiee : ce script ne fait que CREER une
-- nouvelle table. Il est sans danger pour les donnees deja presentes.
--
-- ============================================================================

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Login de l'utilisateur proprietaire de la cle (correspond a users.login)
  `user_name`         VARCHAR(100) NOT NULL,

  -- Empreinte SHA-256 de la cle. La cle en clair n'est JAMAIS stockee.
  `key_hash`          CHAR(64) NOT NULL,

  -- Libelle libre choisi par l'utilisateur ("Mon script python", "Home Assistant"...)
  `label`             VARCHAR(100) NOT NULL DEFAULT '',

  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at`      DATETIME NULL DEFAULT NULL,

  -- Revocation : une cle revoquee n'est plus acceptee mais reste tracee
  `revoked_at`        DATETIME NULL DEFAULT NULL,

  -- Compteur pour la limitation du nombre de requetes (fenetre d'une heure)
  `request_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `window_started_at` DATETIME NULL DEFAULT NULL,

  PRIMARY KEY (`id`),

  -- Une empreinte de cle est unique
  UNIQUE KEY `uniq_key_hash` (`key_hash`),

  -- Recherche rapide des cles d'un utilisateur
  KEY `idx_user_name` (`user_name`),

  -- Recherche rapide lors de l'authentification (cle active)
  KEY `idx_active` (`key_hash`, `revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- OPTIONNEL : nettoyage des cles revoquees depuis plus d'un an
-- ============================================================================
-- A lancer de temps en temps, ou a planifier dans un evenement MySQL.
--
-- DELETE FROM api_keys
-- WHERE revoked_at IS NOT NULL
--   AND revoked_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);


-- ============================================================================
-- VERIFICATION
-- ============================================================================
-- Doit renvoyer la structure de la table :
--
-- DESCRIBE api_keys;
--
-- Doit renvoyer 0 au depart :
--
-- SELECT COUNT(*) FROM api_keys;
