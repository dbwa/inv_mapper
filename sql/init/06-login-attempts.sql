-- Limitation des tentatives de connexion (anti brute-force, voir point 11
-- de besoin_securite.md). Utilisee par login_attempt_wait/failure/reset
-- dans fonctions.inc.php.
-- Sur une base EXISTANTE (volume deja initialise), jouer ce fichier
-- manuellement : le dossier sql/init n'est execute qu'a la creation
-- de la base.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `login` varchar(100) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
