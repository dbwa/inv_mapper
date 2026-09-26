-- Fonctions stockees utilisees par le site (badges, distances, flashs)
-- Extraites du dump de production, sans clause de definisseur (utilisateur de prod inexistant en local).
-- Aucune donnee utilisateur.

SET NAMES utf8mb4;


DROP FUNCTION IF EXISTS `ajout_position`;
DELIMITER ;;
CREATE FUNCTION `ajout_position`(inv_name_user TEXT(25),
    lat_user DECIMAL(10, 6),
    lon_user DECIMAL(10, 6),
    user_login TEXT(100)
    ) RETURNS int(11)
BEGIN
	
    DECLARE user_type_ VARCHAR(255);
    DECLARE invader_count INT;
    DECLARE position_count INT;

    -- Récupérer le game_name de l'utilisateur
    SELECT user_type INTO user_type_ FROM users WHERE login = user_login;

    -- Vérifier si le game_name de l'utilisateur est non null
    IF user_type_ <> 'admin' THEN 
        RETURN 2;
    END IF;

    -- Vérifier si l'invader existe dans la table 'etat'
    SELECT COUNT(*) INTO invader_count FROM etat WHERE inv_name = UPPER(REPLACE(inv_name_user, '_0', '_'));
    IF invader_count <> 1 THEN
       RETURN 3;
    END IF;
   
    -- Vérifier si la position de l'invader existe déjà dans la table 'positions'
     SELECT COUNT(*) INTO position_count FROM positions WHERE inv_name = UPPER(REPLACE(inv_name_user, '_0', '_'));
     IF position_count >= 1 THEN
        RETURN 4;
     END IF;

    -- Insérer la position de l'invader dans la table 'positions'
    INSERT INTO positions (inv_name, lat, lon, points, photo)
    SELECT inv_name_user, lat_user, lon_user, NULL, login
    FROM users
    WHERE login = user_login AND user_type = 'admin';

    RETURN 1;	
		
	
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_distance`;
DELIMITER ;;
CREATE FUNCTION `check_badge_distance`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
          
    select distance_max_optimized(p_username) INTO v_count;
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_flashnuit`;
DELIMITER ;;
CREATE FUNCTION `check_badge_flashnuit`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
       
    select  
count(inv_name) INTO v_count
    FROM user_flash 
    WHERE user_name = p_username
    AND status = 'flash'
    AND (
        (TIME(date_flash) >= '22:00:00' AND TIME(date_flash) <= '23:59:59')
        OR 
        (TIME(date_flash) >= '00:00:01' AND TIME(date_flash) < '06:00:00')
    )
    ;
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_nbr_flash`;
DELIMITER ;;
CREATE FUNCTION `check_badge_nbr_flash`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) INTO v_count
    FROM user_flash 
    WHERE user_name = p_username 
    AND status = 'flash';
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_nbr_jours_consecutifs`;
DELIMITER ;;
CREATE FUNCTION `check_badge_nbr_jours_consecutifs`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
       
    SELECT 
        MAX(length_sequence) INTO v_count
    FROM (
        SELECT 
            COUNT(*) AS length_sequence
        FROM (
            SELECT 
                t1.jour,
                (SELECT COUNT(*) 
                 FROM (SELECT DISTINCT DATE(date_flash) AS dt FROM user_flash WHERE user_name = p_username AND status = 'flash') AS t2 
                 WHERE t2.dt < t1.jour) AS grp
            FROM (SELECT DISTINCT DATE(date_flash) AS jour FROM user_flash WHERE user_name = p_username AND status = 'flash') AS t1
        ) AS subq
        GROUP BY jour - INTERVAL grp DAY
    ) AS result
    ;
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_nbr_pays`;
DELIMITER ;;
CREATE FUNCTION `check_badge_nbr_pays`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
       
    select  
count(distinct fr_pays) INTO v_count
    FROM user_flash 
    join villes on short_name = SUBSTRING_INDEX(inv_name, '_', 1)
    WHERE user_name = p_username
    AND status = 'flash'
    and inv_name not like 'ISS%' 
    ;
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_nbr_ville`;
DELIMITER ;;
CREATE FUNCTION `check_badge_nbr_ville`(p_username VARCHAR(100), p_seuil INT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
       
    select  
count(distinct SUBSTRING_INDEX(inv_name, '_', 1)) INTO v_count
    FROM user_flash 
    WHERE user_name = p_username
    AND status = 'flash'
    AND inv_name not like 'ISS%';
    
    RETURN v_count >= p_seuil;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `check_badge_specific_flash`;
DELIMITER ;;
CREATE FUNCTION `check_badge_specific_flash`(p_username VARCHAR(100), inv_name_check TEXT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE v_count INT;
       
    select  
count(inv_name) INTO v_count
    FROM user_flash 
    WHERE user_name = p_username
    AND status = 'flash'
    AND inv_name = inv_name_check;
    
    RETURN v_count >= 1;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `create_user_invit`;
DELIMITER ;;
CREATE FUNCTION `create_user_invit`(login TEXT, adresse_page TEXT, param1 TEXT) RETURNS text CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    READS SQL DATA
    DETERMINISTIC
BEGIN
    -- Effectuer le travail sécurisé de la fonction.
    INSERT INTO invit_users (username, invitcode, user_type, status)
    VALUES (login, HEX(SHA2(CONCAT(login, LEFT(login, 2)), 256)), 'normal', 'en attente');

    RETURN CONCAT(adresse_page, '?user=', login, '&invi=', HEX(SHA2(CONCAT(login, LEFT(login, 2)), 256)));
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `distance_max`;
DELIMITER ;;
CREATE FUNCTION `distance_max`(p_user_name TEXT) RETURNS double
    DETERMINISTIC
BEGIN
    DECLARE max_distance DOUBLE DEFAULT 0;

    SELECT MAX(
        ST_Distance_Sphere(
            POINT(p1.lon, p1.lat),
            POINT(p2.lon, p2.lat)
        )
    ) / 1000 INTO max_distance
    FROM (
        SELECT DISTINCT p.lat, p.lon
        FROM user_flash uf
        JOIN positions p ON uf.inv_name = p.inv_name
        WHERE uf.user_name = p_user_name
        AND uf.status = 'flash'
    ) p1
    CROSS JOIN (
        SELECT DISTINCT p.lat, p.lon
        FROM user_flash uf
        JOIN positions p ON uf.inv_name = p.inv_name
        WHERE uf.user_name = p_user_name
        AND uf.status = 'flash'
    ) p2;

    RETURN COALESCE(max_distance, 0);
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `distance_max_optimized`;
DELIMITER ;;
CREATE FUNCTION `distance_max_optimized`(p_user_name TEXT) RETURNS double
    DETERMINISTIC
BEGIN
    DECLARE max_distance DOUBLE DEFAULT 0;
    DECLARE v1, v2 TEXT;
    DECLARE ville_count INT;
    
    -- Compte le nombre de villes où l'utilisateur a flashé
    SELECT COUNT(DISTINCT SUBSTRING_INDEX(inv_name, '_', 1)) INTO ville_count
    FROM user_flash
    WHERE user_name = p_user_name 
    AND status = 'flash';
    
    -- Si une seule ville ou pas de flash, pas besoin d'aller plus loin
    IF ville_count <= 1 THEN
        -- Calcul classique pour une seule ville
        SELECT MAX(
            ST_Distance_Sphere(
                POINT(p1.lon, p1.lat),
                POINT(p2.lon, p2.lat)
            ) / 1000
        ) INTO max_distance
        FROM (
            SELECT DISTINCT p.lat, p.lon
            FROM user_flash uf
            JOIN positions p ON uf.inv_name = p.inv_name
            WHERE uf.user_name = p_user_name 
            AND uf.status = 'flash'
        ) p1
        CROSS JOIN (
            SELECT DISTINCT p.lat, p.lon
            FROM user_flash uf
            JOIN positions p ON uf.inv_name = p.inv_name
            WHERE uf.user_name = p_user_name 
            AND uf.status = 'flash'
        ) p2;
        
        RETURN COALESCE(max_distance, 0);
    END IF;
    
    -- Trouve la paire de villes la plus éloignée
    SELECT ville1, ville2, distance_km INTO v1, v2, max_distance
    FROM ville_distances vd
    JOIN (
        SELECT DISTINCT SUBSTRING_INDEX(inv_name, '_', 1) AS ville
        FROM user_flash
        WHERE user_name = p_user_name 
        AND status = 'flash'
    ) v1 ON vd.ville1 = v1.ville
    JOIN (
        SELECT DISTINCT SUBSTRING_INDEX(inv_name, '_', 1) AS ville
        FROM user_flash
        WHERE user_name = p_user_name 
        AND status = 'flash'
    ) v2 ON vd.ville2 = v2.ville
    ORDER BY vd.distance_km DESC
    LIMIT 1;
    
    -- Calcul précis entre les points des deux villes les plus éloignées
    SELECT MAX(
        ST_Distance_Sphere(
            POINT(p1.lon, p1.lat),
            POINT(p2.lon, p2.lat)
        ) / 1000
    ) INTO max_distance
    FROM (
        SELECT DISTINCT p.lat, p.lon
        FROM user_flash uf
        JOIN positions p ON uf.inv_name = p.inv_name
        WHERE uf.user_name = p_user_name 
        AND uf.status = 'flash'
        AND SUBSTRING_INDEX(uf.inv_name, '_', 1) = v1
    ) p1
    CROSS JOIN (
        SELECT DISTINCT p.lat, p.lon
        FROM user_flash uf
        JOIN positions p ON uf.inv_name = p.inv_name
        WHERE uf.user_name = p_user_name 
        AND uf.status = 'flash'
        AND SUBSTRING_INDEX(uf.inv_name, '_', 1) = v2
    ) p2;
    
    RETURN COALESCE(max_distance, 0);
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `get_badges_to_check`;
DELIMITER ;;
CREATE FUNCTION `get_badges_to_check`(p_username VARCHAR(100)) RETURNS text CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    DETERMINISTIC
BEGIN
    -- On retourne en JSON pour avoir une structure propre
    RETURN (
         SELECT JSON_ARRAYAGG(
            JSON_OBJECT(
                'id_badge', id_badge,
                'nom_badge', nom_badge,
                'description', description,
                'fonction', fonction,
                'arguments', arguments,
                'icone', icone
            )
        ) as sortie
        FROM badges b
        WHERE id_badge NOT IN (
            SELECT ub.id_badge 
            FROM user_badges ub 
            join users on (users.id = ub.id_user)
            WHERE users.login = p_username
        )
        AND b.actif = 1
    );
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `nb_flash_pays`;
DELIMITER ;;
CREATE FUNCTION `nb_flash_pays`(p_pays TEXT, p_user_name TEXT) RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE nb_flash INT DEFAULT 0;

    SELECT COUNT(*) INTO nb_flash
    FROM user_flash uf
    JOIN villes v ON SUBSTRING_INDEX(uf.inv_name, '_', 1) = v.short_name
    WHERE uf.user_name = p_user_name
    AND uf.status = 'flash'
    AND v.fr_pays = p_pays;

    RETURN nb_flash;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `nb_flash_total`;
DELIMITER ;;
CREATE FUNCTION `nb_flash_total`(p_user_name TEXT) RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE nb_flash INT DEFAULT 0;

    SELECT COUNT(*) INTO nb_flash
    FROM user_flash uf
    WHERE uf.user_name = p_user_name
    AND uf.status = 'flash';

    RETURN nb_flash;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `nb_flash_ville`;
DELIMITER ;;
CREATE FUNCTION `nb_flash_ville`(p_ville TEXT, p_user_name TEXT) RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE nb_flash INT DEFAULT 0;

    SELECT COUNT(*) INTO nb_flash
    FROM user_flash uf
    WHERE uf.user_name = p_user_name
    AND uf.status = 'flash'
    AND SUBSTRING_INDEX(uf.inv_name, '_', 1) = p_ville;

    RETURN nb_flash;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `update_ville_distances`;
DELIMITER ;;
CREATE FUNCTION `update_ville_distances`() RETURNS int(11)
    DETERMINISTIC
BEGIN
    DECLARE nb_distances INT;
    
    -- Insertion ou mise à jour des distances
    REPLACE INTO ville_distances (ville1, ville2, distance_km)
    SELECT 
        v1.ville AS ville1,
        v2.ville AS ville2,
        ST_Distance_Sphere(
            POINT(v1.lon, v1.lat),
            POINT(v2.lon, v2.lat)
        ) / 1000 AS distance_km
    FROM ville_centroides v1
    JOIN ville_centroides v2 ON v1.ville < v2.ville;
    
    -- Retourne le nombre de distances calculées
    SELECT COUNT(*) INTO nb_distances FROM ville_distances;
    RETURN nb_distances;
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `verifie_motdepasse`;
DELIMITER ;;
CREATE FUNCTION `verifie_motdepasse`(login TEXT, addresse_page TEXT, param1 TEXT) RETURNS text CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    DETERMINISTIC
BEGIN
    DECLARE ok BOOLEAN;
    -- Effectuer le travail sécurisé de la fonction.
    INSERT INTO invit_users (username, invitcode, user_type, status)
    VALUES(login, SHA2(CONCAT(login, LEFT(login, 2)), 256), param1, 'en attente');

    RETURN CONCAT(addresse_page, '?user=', login, '&invi=', SHA2(CONCAT(login, LEFT(login, 2), CAST(RAND()*1000 AS INTEGER)), 256));
END
;;
DELIMITER ;

DROP FUNCTION IF EXISTS `verif_pays_flash_simple`;
DELIMITER ;;
CREATE FUNCTION `verif_pays_flash_simple`(p_pays TEXT, p_user_name TEXT) RETURNS tinyint(1)
    DETERMINISTIC
BEGIN
    DECLARE nb_flash INT DEFAULT 0;

    -- Vérifie si l'utilisateur a flashé dans ce pays
    SELECT COUNT(*) INTO nb_flash
    FROM user_flash uf
    JOIN positions p ON uf.inv_name = p.inv_name
    JOIN villes v ON SUBSTRING_INDEX(uf.inv_name, '_', 1) = v.short_name
    WHERE uf.user_name = p_user_name
    AND uf.status = 'flash'
    AND v.fr_pays = p_pays;

    -- Retourne TRUE si au moins un flash, FALSE sinon
    RETURN nb_flash > 0;
END
;;
DELIMITER ;
