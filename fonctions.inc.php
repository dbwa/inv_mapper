<?php
setlocale(LC_TIME, "fr_FR.UTF-8", "fr_FR", "fr");

$pdo = null; // Variable globale pour la connexion PDO

function connect() {
    global $pdo; // Accédez à la variable globale
    if ($pdo === null) {
        include(__DIR__ . '/config.php');
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// --- Limitation des tentatives de connexion (anti brute-force) ---
// Après LOGIN_MAX_ATTEMPTS échecs en moins de LOGIN_LOCK_SECONDS secondes,
// toute nouvelle tentative pour ce login est refusée jusqu'à l'expiration
// de la fenêtre. Compteur en base (table login_attempts), pas en session :
// il survit au changement de session et s'applique aussi à l'API.

define('LOGIN_MAX_ATTEMPTS', 3);
define('LOGIN_LOCK_SECONDS', 60);

function login_attempt_wait($login)
{
    try {
        $pdo = connect();
        $stmt = $pdo->prepare(
            "SELECT GREATEST(0, " . LOGIN_LOCK_SECONDS . " - TIMESTAMPDIFF(SECOND, last_attempt, NOW())) AS wait_seconds
            FROM login_attempts
            WHERE login = ? AND attempts >= " . LOGIN_MAX_ATTEMPTS . "
              AND last_attempt > NOW() - INTERVAL " . LOGIN_LOCK_SECONDS . " SECOND"
        );
        $stmt->execute([$login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['wait_seconds'] : 0;
    } catch (PDOException $e) {
        // Table absente (migration non jouée) : on ne bloque pas la connexion,
        // l'erreur reste tracée dans les logs.
        error_log("login_attempts indisponible: " . $e->getMessage());
        return 0;
    }
}

function login_attempt_failure($login)
{
    try {
        $pdo = connect();
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (login, attempts, last_attempt)
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE
              attempts = IF(last_attempt < NOW() - INTERVAL " . LOGIN_LOCK_SECONDS . " SECOND, 1, attempts + 1),
              last_attempt = NOW()"
        );
        $stmt->execute([mb_substr($login, 0, 100)]);
        // Purge des vieux compteurs pour ne pas grossir indéfiniment.
        $pdo->prepare("DELETE FROM login_attempts WHERE last_attempt < NOW() - INTERVAL 1 DAY")->execute();
    } catch (PDOException $e) {
        error_log("login_attempts indisponible: " . $e->getMessage());
    }
}

function login_attempt_reset($login)
{
    try {
        $pdo = connect();
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE login = ?");
        $stmt->execute([$login]);
    } catch (PDOException $e) {
        error_log("login_attempts indisponible: " . $e->getMessage());
    }
}

// Authentification d'un utilisateur
function authentificate($username, $password, $remember = false) {
    $pdo = connect();

    $query = "SELECT login, name, user_type FROM users WHERE login = ? and pwd = SHA1(?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $password]);
    $result = $stmt->fetch();
    $count = $stmt->rowCount();

    if ($result) {
        // si okay, enregistrer la connexion 
        // Récupérer les informations sur le type d'appareil (mobile ou desktop)
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $deviceType = (strpos($userAgent, 'Mobile') !== false) ? 'Mobile' : 'Desktop';
        $browser = getBrowserName($userAgent);
        
        // Enregistrer la connexion dans la base
        $query = "INSERT INTO user_connexions (user_name, device_type, browser, temps) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username, $deviceType, $browser]);

        // Si "Se souvenir de moi" est coché, créer un token
        if ($remember) {
            $token = create_remember_token($username);
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', true, true);
        }
        
        return array($count, $result);  // Garder le format de retour original
    }
    return false;
}

// Fonction pour obtenir le nom du navigateur à partir de l'agent utilisateur (user agent)
function getBrowserName($userAgent) {
    if (strpos($userAgent, 'Opera') || strpos($userAgent, 'OPR/')) {
        return 'Opera';
    } elseif (strpos($userAgent, 'Edge')) {
        return 'Microsoft Edge';
    } elseif (strpos($userAgent, 'Chrome')) {
        return 'Google Chrome';
    } elseif (strpos($userAgent, 'Safari')) {
        return 'Safari';
    } elseif (strpos($userAgent, 'Firefox')) {
        return 'Mozilla Firefox';
    } elseif (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/7')) {
        return 'Internet Explorer';
    } else {
        return 'Autre';
    }
}

function check_user($login)
{
    $pdo = connect();

    $query = "SELECT login FROM users WHERE login = ?;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$login]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = ($row) ? 1 : 0;

    return array($count, $row);
}

// Création d'un utilisateur
function register_user($login, $user_password, $invitcode)
{   
    include(__DIR__ . '/config.php');
    $pdo = connect();

    if (check_user($login)){
        list($count, $row) = check_user($login);
        if ($count >= 1){   
            return array(1, ['login' => 'user exists']);
        }
    }

    // Si aucun code d'invitation n'est configuré, l'inscription est ouverte à tous.
    // Sinon, on compare le code fourni avec le code attendu (comparaison stricte
    // et résistante aux attaques par timing).
    $code_requis = isset($code_inscription) ? (string) $code_inscription : '';
    $code_fourni = (string) $invitcode;
    $code_valide = ($code_requis === '') || hash_equals($code_requis, $code_fourni);

    if ($code_valide) {
        $query = "INSERT INTO users (login, name, pwd)
            SELECT ?, ?, SHA1(?);";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login, $login, $user_password]);

        $query = "INSERT INTO user_flash (user_name, inv_name, status)
            SELECT ?, 'PA_1', 'flash';";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login]);

        $query = "SELECT login FROM users WHERE login = ? AND pwd = SHA1(?);";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login, $user_password]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = ($row) ? 1 : 0;

        return array($count, $row);
    } else {
        $query = "INSERT INTO users (login, name, pwd)
            SELECT username, ?, SHA1(?)
            FROM invit_users
            WHERE username = ? AND invitcode = ? AND status = 'en attente';";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login, $user_password, $login, $invitcode]);

        $query = "UPDATE invit_users SET status = 'cree'
            WHERE username = ? AND invitcode = ? AND status = 'en attente';";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login, $invitcode]);

        $query = "INSERT INTO user_flash (user_name, inv_name, status)
            SELECT ?, 'PA_1', 'flash';";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login]);

        $query = "SELECT login FROM users WHERE login = ? AND pwd = SHA1(?);";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login, $user_password]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = ($row) ? 1 : 0;

        return array($count, $row);
    }
}

// Fonction pour générer un identifiant unique pour l'appareil
function generate_device_id() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $deviceType = (strpos($userAgent, 'Mobile') !== false) ? 'Mobile' : 'Desktop';
    $browser = getBrowserName($userAgent);
    
    // Créer un identifiant unique basé sur ces informations
    return hash('sha256', $userAgent . $deviceType . $browser);
}

// Création d'un token de connexion persistante
function create_remember_token($username) {
    $pdo = connect();
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    $device_id = generate_device_id();
    
    // Supprimer l'ancien token pour cet appareil s'il existe
    $query = "DELETE FROM remember_tokens WHERE username = ? AND device_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $device_id]);
    
    // Insérer le nouveau token
    $query = "INSERT INTO remember_tokens (username, token, device_id, expires) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $token, $device_id, $expires]);
    
    return $token;
}

// Vérification d'un token de connexion
function verify_remember_token($token) {
    $pdo = connect();
    $device_id = generate_device_id();
    
    $query = "SELECT username FROM remember_tokens WHERE token = ? AND device_id = ? AND expires > NOW()";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$token, $device_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['username'];
    }
    return false;
}

// Restaure la session à partir du cookie "remember_token" si elle a expiré
// (navigateur fermé, session GC'd, etc.). Retourne true si l'utilisateur est connecté.
function restore_session_from_cookie() {
    if (!empty($_SESSION['login_user'])) {
        return true;
    }

    if (empty($_COOKIE['remember_token'])) {
        return false;
    }

    $username = verify_remember_token($_COOKIE['remember_token']);
    if (!$username) {
        // token invalide ou expiré : on nettoie le cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        return false;
    }

    $pdo = connect();
    $query = "SELECT login, name, user_type FROM users WHERE login = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $result = $stmt->fetch();

    if (!$result) {
        return false;
    }

    $_SESSION['login_user'] = $result['login'];
    $_SESSION['login_name'] = $result['name'];
    $_SESSION['user_type'] = $result['user_type'];

    return true;
}

// Mise à jour du mot de passe d'un utilisateur
function update_password($username, $currentpass, $newpassword)
{   
    include(__DIR__ . '/config.php');
    $pdo = connect();

    // Vérifiez d'abord si le mot de passe actuel est correct
    $query = "SELECT login, name, user_type FROM users WHERE login = ? and pwd = SHA1(?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $currentpass]);
    $result = $stmt->fetch();
    $count = $stmt->rowCount();

    if ($result) {
        // Si le mot de passe actuel est correct, mettez à jour avec le nouveau mot de passe
        $query = "UPDATE users SET pwd = SHA1(?) WHERE login = ?;";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$newpassword, $username]);
        return 'password changé';
    } else {
        // Le mot de passe actuel n'est pas correct
        return 'password non changé';
    }
}

function get_geojson_a_flasher()
{   
    $pdo = connect();
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    $_SESSION['login_name'];
    if (!empty($_SESSION['login_name'])) {
        $query = "
            
  with liste_a_flasher as (

            SELECT @rownum := @rownum + 1 AS id, pos.inv_name, pos.lat, pos.lon,
            et.points,
            et.etat,
            et.last_maj,
            -- replace(et.image1, 'https://www.invader-spotter.art/', './img_invader/')) as image1, 
            -- replace(et.image2, 'https://www.invader-spotter.art/', './img_invader/')) as image2,  
            -- replace(et.image3, 'https://www.invader-spotter.art/', './img_invader/')) as image3
            CONCAT('./img_invader/', et.image1) as image1, 
            CONCAT('./img_invader/', et.image2) as image2, 
            CONCAT('./img_invader/', et.image3) as image3
            FROM positions AS pos
            LEFT JOIN etat AS et ON et.inv_name = pos.inv_name
            LEFT JOIN modif_state_user AS uf2 ON uf2.inv_name = pos.inv_name AND uf2.user_name = ?,
            (SELECT @rownum := 0) r
            WHERE
            pos.lat IS NOT NULL
            AND pos.lon IS NOT NULL
            AND COALESCE(uf2.etat, et.etat) IN ('Un peu dégradé', 'Inconnu', 'Dégradé', 'OK')
            AND pos.inv_name NOT IN (
            SELECT uf.inv_name
            FROM user_flash AS uf
            WHERE uf.status = 'flash'
            AND uf.user_name = ?)

)
select 
  CONCAT(
    '{\"type\":\"Feature\",\"id\":\"', f.id, '\",\"geometry\": {\"type\":\"Point\", \"coordinates\":[', f.lon, ',',  f.lat, ']}, \"properties\":{',
    '\"name\":\"', f.inv_name, '\",',
    '\"points\":\"', f.points, '\",',
    '\"etat\":\"', f.etat, '\",',
    '\"last_maj\":\"', f.last_maj, '\",',
    -- Images utilisateur en priorité, sinon image par défaut
    '\"image1\":\"', f.image1, '\",',
    '\"image1_d\":\"',  '', '\",',
    '\"image1_c\":\"', 'invader spotter', '\",',
    '\"image2\":\"', COALESCE(up.photo1_path, f.image2), '\",',
    '\"image2_d\":\"', COALESCE(up.photo1_date, ''), '\",',
    '\"image2_c\":\"', COALESCE(up.photo1_credit, 'invader spotter'), '\",',
    '\"image3\":\"', COALESCE(up.photo2_path, f.image3), '\",',
    '\"image3_d\":\"', COALESCE(up.photo2_date, ''), '\",',
    '\"image3_c\":\"', COALESCE(up.photo2_credit, 'invader spotter'), '\",',
    '\"image4\":\"', COALESCE(up.photo3_path, f.image3), '\",',
    '\"image4_d\":\"', COALESCE(up.photo3_date, ''), '\",',
    '\"image4_c\":\"', COALESCE(up.photo3_credit, ''), '\",',
    
    '\"more_p\":', IF(IFNULL(up.nb_valid_photos, 0) < 3 OR IFNULL(up.oldest_photo, '1900-01-01') < DATE_SUB(NOW(), INTERVAL 2 YEAR), 'true','false'),
    '}},'
  ) AS feature
from liste_a_flasher f
left join (
    select 
      inv_name,
      MAX(CASE WHEN rn = 1 THEN photo_path END) as photo1_path,
      MAX(CASE WHEN rn = 1 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo1_date,
      MAX(CASE WHEN rn = 1 AND credit = 1 THEN login ELSE '' END) as photo1_credit,
      MAX(CASE WHEN rn = 2 THEN photo_path END) as photo2_path,
      MAX(CASE WHEN rn = 2 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo2_date,
      MAX(CASE WHEN rn = 2 AND credit = 1 THEN login ELSE '' END) as photo2_credit,
      MAX(CASE WHEN rn = 3 THEN photo_path END) as photo3_path,
      MAX(CASE WHEN rn = 3 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo3_date,
      MAX(CASE WHEN rn = 3 AND credit = 1 THEN login ELSE '' END) as photo3_credit,
      COUNT(*) as nb_valid_photos,
      MIN(upload_date) as oldest_photo
    from (
      select 
        inv_name, 
        photo_path, 
        upload_date,
        credit,
        login,
        ROW_NUMBER() OVER (PARTITION BY inv_name ORDER BY upload_date DESC) as rn
      from user_photos
      where validated = 1
    ) t
    where rn <= 3
    group by inv_name
) up on up.inv_name = f.inv_name
            "
            ;
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'], $_SESSION['login_name']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $d) {
            $geojson .= $d['feature'];
        }
        $geojson=substr($geojson,0,-1);  //on enleve la derniere ','
        $geojson .= ']}';

        $geojson = str_replace("\\", "", $geojson);
        return $geojson;
    }
    else {
        $geojson .= ']}';
        $geojson = str_replace("\\", "", $geojson);
        return $query;
    }
}

function get_geojson_deja_flashe() {
    $pdo = connect();
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    $_SESSION['login_name'];
    if (!empty($_SESSION['login_name'])) {
        $query = "

  
  with liste_a_flasher as (
      SELECT  
      @rownum := @rownum + 1 as id, 
      pos.lon, pos.lat, 
      pos.inv_name, 
      et.points,
      et.etat,  
      et.last_maj,
      CONCAT('./img_invader/', et.image1) as image1, 
      CONCAT('./img_invader/', et.image2) as image2, 
      CONCAT('./img_invader/', et.image3) as image3
      FROM positions as pos
      LEFT JOIN etat as et ON (et.inv_name = pos.inv_name)
      CROSS JOIN (SELECT @rownum:=0) r
      WHERE 
      pos.lat IS NOT NULL 
      AND pos.lon IS NOT NULL
      AND pos.inv_name IN (SELECT uf.inv_name FROM user_flash as uf WHERE uf.status = 'flash' AND uf.user_name=? AND uf.user_name=?)
      ORDER BY pos.inv_name
)
select 
  CONCAT(
    '{\"type\":\"Feature\",\"id\":\"', f.id, '\",\"geometry\": {\"type\":\"Point\", \"coordinates\":[', f.lon, ',',  f.lat, ']}, \"properties\":{',
    '\"name\":\"', f.inv_name, '\",',
    '\"points\":\"', f.points, '\",',
    '\"etat\":\"', f.etat, '\",',
    '\"last_maj\":\"', f.last_maj, '\",',
    -- Images utilisateur en priorité, sinon image par défaut
    '\"image1\":\"', f.image1, '\",',
    '\"image1_d\":\"',  '', '\",',
    '\"image1_c\":\"', 'invader spotter', '\",',
    '\"image2\":\"', COALESCE(up.photo1_path, f.image2), '\",',
    '\"image2_d\":\"', COALESCE(up.photo1_date, ''), '\",',
    '\"image2_c\":\"', COALESCE(up.photo1_credit, 'invader spotter'), '\",',
    '\"image3\":\"', COALESCE(up.photo2_path, f.image3), '\",',
    '\"image3_d\":\"', COALESCE(up.photo2_date, ''), '\",',
    '\"image3_c\":\"', COALESCE(up.photo2_credit, 'invader spotter'), '\",',
    '\"image4\":\"', COALESCE(up.photo3_path, f.image3), '\",',
    '\"image4_d\":\"', COALESCE(up.photo3_date, ''), '\",',
    '\"image4_c\":\"', COALESCE(up.photo3_credit, ''), '\",',
    
    '\"more_p\":', IF(IFNULL(up.nb_valid_photos, 0) < 3 OR IFNULL(up.oldest_photo, '1900-01-01') < DATE_SUB(NOW(), INTERVAL 2 YEAR), 'true','false'),
    '}},'
  ) AS feature
from liste_a_flasher f
left join (
    select 
      inv_name,
      MAX(CASE WHEN rn = 1 THEN photo_path END) as photo1_path,
      MAX(CASE WHEN rn = 1 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo1_date,
      MAX(CASE WHEN rn = 1 AND credit = 1 THEN login ELSE '' END) as photo1_credit,
      MAX(CASE WHEN rn = 2 THEN photo_path END) as photo2_path,
      MAX(CASE WHEN rn = 2 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo2_date,
      MAX(CASE WHEN rn = 2 AND credit = 1 THEN login ELSE '' END) as photo2_credit,
      MAX(CASE WHEN rn = 3 THEN photo_path END) as photo3_path,
      MAX(CASE WHEN rn = 3 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo3_date,
      MAX(CASE WHEN rn = 3 AND credit = 1 THEN login ELSE '' END) as photo3_credit,
      COUNT(*) as nb_valid_photos,
      MIN(upload_date) as oldest_photo
    from (
      select 
        inv_name, 
        photo_path, 
        upload_date,
        credit,
        login,
        ROW_NUMBER() OVER (PARTITION BY inv_name ORDER BY upload_date DESC) as rn
      from user_photos
      where validated = 1
    ) t
    where rn <= 3
    group by inv_name
) up on up.inv_name = f.inv_name
            "
            ;
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'], $_SESSION['login_name']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $d) {
            $geojson .= $d['feature'];
        }
        $geojson=substr($geojson,0,-1);  //on enleve la derniere ','
        $geojson .= ']}';

        $geojson = str_replace("\\", "", $geojson);
        return $geojson;
    }
    else {
        $geojson .= ']}';
        $geojson = str_replace("\\", "", $geojson);
        return $query;
    }
}

function get_geojson_detruits()
{
    $pdo = connect();
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';

    if (!empty($_SESSION['login_name'])) {
        $query = "
with liste_a_flasher as (
    select @row_number := @row_number + 1 as id, pos.inv_name, pos.lat, pos.lon,
    et.points,
    et.etat,
    et.last_maj,
    CONCAT('./img_invader/', et.image1) as image1, 
    CONCAT('./img_invader/', et.image2) as image2, 
    CONCAT('./img_invader/', et.image3) as image3
    from positions as pos
    left join etat as et on (et.inv_name = pos.inv_name)
    left join modif_state_user as uf2  on (uf2.inv_name = pos.inv_name  and uf2.user_name=?),
    (select @row_number := 0) as r
    where 
    pos.lat is not null 
    and pos.lon is not null
    and coalesce (uf2.etat, et.etat) not in ('Un peu dégradé','Inconnu','Dégradé','OK')
    and pos.inv_name not in (select uf.inv_name from user_flash as uf where uf.status = 'flash' and uf.user_name=?)
    order by pos.inv_name
)
select 
  CONCAT(
    '{\"type\":\"Feature\",\"id\":\"', f.id, '\",\"geometry\": {\"type\":\"Point\", \"coordinates\":[', f.lon, ',',  f.lat, ']}, \"properties\":{',
    '\"name\":\"', f.inv_name, '\",',
    '\"points\":\"', f.points, '\",',
    '\"etat\":\"', f.etat, '\",',
    '\"last_maj\":\"', f.last_maj, '\",',
    -- Images utilisateur en priorité, sinon image par défaut
    '\"image1\":\"', f.image1, '\",',
    '\"image1_d\":\"',  '', '\",',
    '\"image1_c\":\"', 'invader spotter', '\",',
    '\"image2\":\"', COALESCE(up.photo1_path, f.image2), '\",',
    '\"image2_d\":\"', COALESCE(up.photo1_date, ''), '\",',
    '\"image2_c\":\"', COALESCE(up.photo1_credit, 'invader spotter'), '\",',
    '\"image3\":\"', COALESCE(up.photo2_path, f.image3), '\",',
    '\"image3_d\":\"', COALESCE(up.photo2_date, ''), '\",',
    '\"image3_c\":\"', COALESCE(up.photo2_credit, 'invader spotter'), '\",',
    '\"image4\":\"', COALESCE(up.photo3_path, f.image3), '\",',
    '\"image4_d\":\"', COALESCE(up.photo3_date, ''), '\",',
    '\"image4_c\":\"', COALESCE(up.photo3_credit, ''), '\",',
    
    '\"more_p\":', IF(IFNULL(up.nb_valid_photos, 0) < 3 OR IFNULL(up.oldest_photo, '1900-01-01') < DATE_SUB(NOW(), INTERVAL 2 YEAR), 'true','false'),
    '}},'
  ) AS feature
from liste_a_flasher f
left join (
    select 
      inv_name,
      MAX(CASE WHEN rn = 1 THEN photo_path END) as photo1_path,
      MAX(CASE WHEN rn = 1 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo1_date,
      MAX(CASE WHEN rn = 1 AND credit = 1 THEN login ELSE '' END) as photo1_credit,
      MAX(CASE WHEN rn = 2 THEN photo_path END) as photo2_path,
      MAX(CASE WHEN rn = 2 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo2_date,
      MAX(CASE WHEN rn = 2 AND credit = 1 THEN login ELSE '' END) as photo2_credit,
      MAX(CASE WHEN rn = 3 THEN photo_path END) as photo3_path,
      MAX(CASE WHEN rn = 3 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo3_date,
      MAX(CASE WHEN rn = 3 AND credit = 1 THEN login ELSE '' END) as photo3_credit,
      COUNT(*) as nb_valid_photos,
      MIN(upload_date) as oldest_photo
    from (
      select 
        inv_name, 
        photo_path, 
        upload_date,
        credit,
        login,
        ROW_NUMBER() OVER (PARTITION BY inv_name ORDER BY upload_date DESC) as rn
      from user_photos
      where validated = 1
    ) t
    where rn <= 3
    group by inv_name
) up on up.inv_name = f.inv_name
        
        
        
        
        
            "
            ;
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'], $_SESSION['login_name']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $d) {
            $geojson .= $d['feature'];
        }
        $geojson=substr($geojson,0,-1);  //on enleve la derniere ','
        $geojson .= ']}';

        $geojson = str_replace("\\", "", $geojson);
        return $geojson;
    }
    else {
        $geojson .= ']}';
        $geojson = str_replace("\\", "", $geojson);
        return $query;
    }
}

function update_status($inv_name, $out_status){
    $pdo = connect();
    //pour changer le l'etat de l'invaders de ok a detruit par exemple, mais user par user
    $query = "DELETE FROM modif_state_user WHERE inv_name=? AND user_name=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$inv_name, $_SESSION['login_name'] ?? '']);

    $out_status = str_replace(':s:', ' ', $out_status);
    $query = "INSERT INTO modif_state_user(user_name, inv_name, etat, date_modif) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['login_name'] ?? '', $inv_name, $out_status]);

    return 1;
}

function suppri_flash($inv_name) {
    $pdo = connect();
    // supr du flash dans la base user pour un invader donnée
    $query = "DELETE FROM user_flash WHERE user_name=? AND inv_name=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['login_name'] ?? '', $inv_name]);
}

function ajout_flash($inv_name) {
    $pdo = connect();
    // ajout d'un nouveau flash par un user dans la base user_flash
    // déjà on supprime, au cas où il existe déjà
    suppri_flash($inv_name);

    // puis on l'ajoute
    $query = "INSERT INTO user_flash (user_name, inv_name, status, date_flash)
        SELECT ?, ?, 'flash', CURRENT_TIMESTAMP AS date_flash
        FROM dual
        WHERE NOT EXISTS (SELECT inv_name FROM user_flash WHERE user_name = ? AND inv_name = ?)
        AND EXISTS (SELECT inv_name FROM etat WHERE inv_name = ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['login_name'] ?? '', $inv_name, $_SESSION['login_name'] ?? '', $inv_name, $inv_name]);
    return 1;
}

/*
function ajout_flash_multi($list_inv_name) {
    $pdo = connect();
    // ajout d'un paquet de nouveaux flash par un user dans la base user_flash (on ajoute que ceux ui n'y sont pas déjà, et qui existent vraiment)
    $inv_names = explode(",", $list_inv_name);
    $inv_names = array_map('trim', $inv_names);
    $inv_names_placeholder = implode(',', array_fill(0, count($inv_names), '?'));
    $query = "INSERT INTO user_flash (user_name, inv_name, status, date_flash)
        SELECT ?, inv_names, 'flash', CURRENT_TIMESTAMP AS date_flash
        FROM (SELECT * FROM (SELECT unnest(?) AS inv_names) AS lst) AS lst
        WHERE inv_names NOT IN (SELECT inv_name FROM user_flash WHERE user_name = ?)
        AND inv_names IN (SELECT inv_name FROM etat)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$inv_names, $_SESSION['login_name']]);
    return 1;
}
*/


function ajout_flash_multi($list_inv_name) {
    $pdo = connect();
    
    // Divisez la liste en noms d'invaders individuels
    $inv_names = explode(",", $list_inv_name);
    $inv_names = array_map('trim', $inv_names);

    // Préparez la requête SQL
    $query = "INSERT INTO user_flash (user_name, inv_name, status, date_flash) VALUES (?, ?, 'flash', CURRENT_TIMESTAMP)";

    // Préparez la déclaration SQL une seule fois en dehors de la boucle
    $stmt = $pdo->prepare($query);

    // Nom d'utilisateur à insérer
    $user_name = $_SESSION['login_name'] ?? '';

    // Compteur pour le nombre d'invaders insérés
    $insertedCount = 0;

    // Boucle pour insérer chaque invader individuellement
    foreach ($inv_names as $inv_name) {
        // Vérifiez si l'invader n'existe pas déjà pour cet utilisateur
        $checkQuery = "SELECT COUNT(*) FROM user_flash WHERE user_name = ? AND inv_name = ?";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute([$user_name, $inv_name]);
        $rowCount = $checkStmt->fetchColumn();

        if ($rowCount == 0) {
            // L'invader n'existe pas encore, insérez-le
            $stmt->execute([$user_name, $inv_name]);
            $insertedCount++;
        }
    }
    return $insertedCount;
} 


function update_categorisations_utilisateur($txt_categories){

    //pour changer le fonctionnement des categories affichées sur la carte de l'utilisateurs

    //si "default", alors remise à zéro avec la la formule créée par défaut dans la page config.php
    $pdo = connect();
    $query = "delete from user_config where user_name=?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['login_name'] ?? '']);
    $data = $stmt->fetch();

    if($txt_categories <=> 'default')
    {   
        $query = "insert into user_config select ? as user_name, ? as categories_map";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'] ?? '', $txt_categories]);
        $data = $stmt->fetch();
    }
    return 1;
}

function get_categories($categories_default){
    //recuperation des categories définies (ou pas) par l'utilisateur
    $pdo = connect();
    $query = "select categories_map from user_config where user_name=?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username]);
    $data = $stmt->fetch();
    if (empty($data)) {
        return $categories_default;
    }
    else{
        return $data;
    }
}



function get_geojson_invaders_from_config($categories, $cat)
{
    $pdo = connect();
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    
    if (!empty($_SESSION['login_name'])) {

        $etat_param = $categories[$cat]['include']['etats'];
        if ($etat_param == 'all')
        {
            $etat_query = ' etat like \'%\' ';
            }
        else{
            $etat_query = ' etat in (';
            foreach ($etat_param as $item) { $etat_query .= '\''.$item.'\',';};
            $etat_query = substr($etat_query, 0, -1);
            $etat_query .= ') ';
        }

        $flash_param = $categories[$cat]['include']['flash'];
        if ($flash_param == 'all')
        {
            $flash_query = ' and flash in (true, false) ';
        }
        else{
            if ($flash_param == 'flashé')
            {
                $flash_query = ' and flash in (true)';
            }
            else
            {   
                $flash_query = ' and flash in (false)';
            }
        }


        $query = "
        with liste_invaders as (
            select @row_number := @row_number + 1 as id, pos.inv_name, pos.lat, pos.lon,
            et.points,
            coalesce (uf2.etat, et.etat) as etat,
            case when uf3.inv_name is null then false else true end as flash,
            et.last_maj,
            CONCAT('./img_invader/', et.image1) as image1, 
            CONCAT('./img_invader/', et.image2) as image2, 
            CONCAT('./img_invader/', et.image3) as image3
            from positions as pos
            left join etat as et on (et.inv_name = pos.inv_name)
            left join modif_state_user as uf2  on (uf2.inv_name = pos.inv_name  and uf2.user_name=?)
            left join user_flash as uf3 on (uf3.inv_name = pos.inv_name  and uf3.user_name=?),
            (select @row_number := 0) as r
            where 
            pos.lat is not null 
            and pos.lon is not null
        )
select 
  CONCAT(
    '{\"type\":\"Feature\",\"id\":\"', f.id, '\",\"geometry\": {\"type\":\"Point\", \"coordinates\":[', f.lon, ',',  f.lat, ']}, \"properties\":{',
    '\"name\":\"', f.inv_name, '\",',
    '\"points\":\"', f.points, '\",',
    '\"etat\":\"', f.etat, '\",',
    '\"last_maj\":\"', f.last_maj, '\",',
    -- Images utilisateur en priorité, sinon image par défaut
    '\"image1\":\"', f.image1, '\",',
    '\"image1_date\":\"',  '', '\",',
    '\"image1_credit\":\"', 'invader spotter', '\",',
    '\"image2\":\"', COALESCE(up.photo1_path, f.image2), '\",',
    '\"image2_date\":\"', COALESCE(up.photo1_date, ''), '\",',
    '\"image2_credit\":\"', COALESCE(up.photo1_credit, 'invader spotter'), '\",',
    '\"image3\":\"', COALESCE(up.photo2_path, f.image3), '\",',
    '\"image3_date\":\"', COALESCE(up.photo2_date, ''), '\",',
    '\"image3_credit\":\"', COALESCE(up.photo2_credit, 'invader spotter'), '\",',
    '\"image4\":\"', COALESCE(up.photo3_path, f.image3), '\",',
    '\"image4_date\":\"', COALESCE(up.photo3_date, ''), '\",',
    '\"image4_credit\":\"', COALESCE(up.photo3_credit, ''), '\",',
    
    '\"need_more_photos\":', IF(IFNULL(up.nb_valid_photos, 0) < 3 OR IFNULL(up.oldest_photo, '1900-01-01') < DATE_SUB(NOW(), INTERVAL 2 YEAR), 'true','false'),
    '}},'
  ) AS feature
from liste_invaders f
left join (
    select 
      inv_name,
      MAX(CASE WHEN rn = 1 THEN photo_path END) as photo1_path,
      MAX(CASE WHEN rn = 1 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo1_date,
      MAX(CASE WHEN rn = 1 AND credit = 1 THEN login ELSE '' END) as photo1_credit,
      MAX(CASE WHEN rn = 2 THEN photo_path END) as photo2_path,
      MAX(CASE WHEN rn = 2 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo2_date,
      MAX(CASE WHEN rn = 2 AND credit = 1 THEN login ELSE '' END) as photo2_credit,
      MAX(CASE WHEN rn = 3 THEN photo_path END) as photo3_path,
      MAX(CASE WHEN rn = 3 THEN DATE_FORMAT(upload_date, '%Y-%m-%d') END) as photo3_date,
      MAX(CASE WHEN rn = 3 AND credit = 1 THEN login ELSE '' END) as photo3_credit,
      COUNT(*) as nb_valid_photos,
      MIN(upload_date) as oldest_photo
    from (
      select 
        inv_name, 
        photo_path, 
        upload_date,
        credit,
        login,
        ROW_NUMBER() OVER (PARTITION BY inv_name ORDER BY upload_date DESC) as rn
      from user_photos
      where validated = 1
    ) t
    where rn <= 3
    group by inv_name
) up on up.inv_name = f.inv_name
            where 
                " . $etat_query . $flash_query 
                ; 
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'], $_SESSION['login_name']]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


        /*$geojson='{\"type\" : \"FeatureCollection\", \"crs\" : {\"type\" : \"name\", \"properties\" : {\"name\" : \"EPSG:4326\"}},\"features\":[';*/
        if (empty($data)) {
            $data=array(array( "feature" => "{\"type\":\"Feature\",\"id\":\"'|| id ||'\",\"geometry\": {\"type\":\"Point\", \"coordinates\":[-999,-999]}, \"properties\":{\"name\":\"emptyinvader\",\"points\":\"0\",\"etat\":\"virtuel\",\"last_maj\":\"null\",\"image1\":\"null\",\"image2\":\"null\",\"image3\":\"null\"}},") );
        }
        foreach ($data as $d) {
          //$geojson .= addslashes($d['feature']);
          $geojson .= $d['feature'];
        }
        $geojson=substr($geojson,0,-1);  //on enleve la derniere ','
        $geojson .= ']}';

        $geojson = str_replace("\\", "", $geojson);
        return $geojson;
    }
    else {
        $geojson .= ']}';
        $geojson = str_replace("\\", "", $geojson);
        return $query;
    }
}


function get_altitude($lat, $lon) {
    // Appel à l'API OpenTopoData
    $url = "https://api.opentopodata.org/v1/srtm30m?locations=$lat,$lon";
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    if (isset($data['results'][0]['elevation'])) {
        return intval(round($data['results'][0]['elevation']));
    }
    return null; // ou 0 si tu préfères
}


function ajout_position($inv_name, $lat, $lon){
    $pdo = connect();
    $alti = get_altitude($lat, $lon);
    $query = "insert into positions 
        select replace(?, '_0', '_') as inv_name, CAST(? as float) as lat , CAST(? as float) as lon, null as points, login as photo, ? as alti 
        from users where login = ? and game_name is not null;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$inv_name, $lat, $lon, $alti, $_SESSION['login_name'] ?? '']);
    return 1;
}


function delete_position($inv_name){
    //Ajoute un invader en connaissant son lon lat
    //si utilisateur avancé uniquement (ici, si game_name is not null)
    $pdo = connect();
    $query = "update positions set inv_name = concat('DELETED - ', inv_name, ' - ', NOW()),
    photo = concat('DELETED_', photo) where inv_name = ? and photo = ?;";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$inv_name, $_SESSION['login_name'] ?? '']);
    return 1;
}


function get_achievements(){
    //Récupère les achivements de l'utilisateur
    try {
        $pdo = connect();
        $query = "
        SELECT 
            u.login, ub.date_obtention, b.nom_badge,b.description,b.icone,lower(b.niveau) as niveau
        FROM users as u
        JOIN user_badges as ub ON (u.id = ub.id_user)
        JOIN badges as b ON (ub.id_badge = b.id_badge)
        WHERE u.login = ?
        ORDER BY ub.date_obtention DESC, b.id_badge DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_SESSION['login_name'] ?? '']);
        
        // Récupère tous les résultats dans un tableau associatif
        $achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => $achievements,
            'count' => count($achievements)
        ];
        
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'data' => [],
            'count' => 0
        ];
    }
}


// Mise à jour des informations de synchronisation d'un utilisateur
function update_user_synchro($login, $uid_flashinvader, $game_name)
{   
    $pdo = connect();
    
    try {
        // Vérification que l'utilisateur existe
        $query = "SELECT login FROM users WHERE login = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login]);
        
        if (!$stmt->fetch()) {
            return array(false, "Utilisateur non trouvé");
        }
        
        // Mise à jour des champs uid_flashinvader et game_name
        $query = "UPDATE users 
                 SET uid_flashinvader = ?, game_name = ?
                 WHERE login = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$uid_flashinvader, $game_name, $login]);
        
        if ($stmt->rowCount() > 0) {
            return array(true, "Informations de synchronisation mises à jour");
        } else {
            return array(true, "Aucune modification effectuée");
        }
        
    } catch (PDOException $e) {
        return array(false, "Erreur lors de la mise à jour: " . $e->getMessage());
    }
}

// Récupère l'UID FlashInvader d'un utilisateur
function get_user_uid($login)
{
    $pdo = connect();
    $query = "SELECT uid_flashinvader FROM users WHERE login = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$login]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['uid_flashinvader'] : null;
}

//pour mettre à jour la base grace à la connexion avec l'API
function update_user_flashes($login, $invaders)
{
    $pdo = connect();

    try {
        $pdo->beginTransaction();

        // On supprime tous les anciens flashes de l'utilisateur
        $query = "DELETE FROM user_flash WHERE user_name = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$login]);

        // On insère les nouveaux flashes
        $query = "INSERT INTO user_flash (user_name, inv_name, status, date_flash) VALUES (?, replace(?, '_0', '_'), 'flash', ?)";
        $stmt = $pdo->prepare($query);

        $count = 0;
        foreach ($invaders as $inv_name => $invader) {
            $date_flash = $invader['date_flash'];
            if ($date_flash) { // On ne prend que les invaders flashés
                $stmt->execute([$login, $inv_name, $date_flash]);
                $count++;
            }
        }

        $pdo->commit();

        return [
            'success' => true,
            'stats' => [
                'added' => $count,
                'total' => count($invaders)
            ]
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}


//pour récup les photos utilisateurs
function get_user_photos_by_invader($status = null) {
    $pdo = connect();
    
    $query = "SELECT 
        CONCAT('/img_invader/',etat.image1) as image_ref, 
        up.id, up.id_user, up.login, up.inv_name, 
        up.photo_path, up.credit, up.validated, 
        up.upload_date, up.validation_date, 
        up.validated_by, up.status
    FROM user_photos up 
    LEFT JOIN etat USING (inv_name)";
    
    if ($status) {
        $query .= " WHERE up.status = :status";
    }
    else {
      $query .= " WHERE up.status <> 'pending' ";
    }
    $query .= " ORDER BY up.inv_name, up.upload_date DESC";
    
    $stmt = $pdo->prepare($query);
    if ($status) {
        $stmt->execute(['status' => $status]);
    } else {
        $stmt->execute();
    }
    
    // On regroupe par invader
    $results = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($results[$row['inv_name']])) {
            $results[$row['inv_name']] = [
                'image_ref' => $row['image_ref'],
                'photos' => []
            ];
        }
        $results[$row['inv_name']]['photos'][] = $row;
    }
    
    return $results;
}

//pour valider ou invalider les photos des utilisateurs
function update_photo_status($photo_id, $status, $moderator_login) {
    $pdo = connect();
    
    $validated = ($status === 'accepted') ? 1 : 0;
    
    $query = "UPDATE user_photos 
        SET status = :status,
            validated = :validated,
            validation_date = CURRENT_TIMESTAMP,
            validated_by = :moderator
        WHERE id = :photo_id";
        
    $stmt = $pdo->prepare($query);
    return $stmt->execute([
        'status' => $status,
        'validated' => $validated,
        'moderator' => $moderator_login,
        'photo_id' => $photo_id
    ]);
}

?>
