<?php
setlocale(LC_TIME, "fr_FR");

// --> BASE DE DONNEES
function connect()
{
    include(__DIR__ . '/config.php');
    $uri = "host=$host port=$port dbname=$dbname user=$user password=$password";
    pg_connect($uri);
}

function close()
{
    pg_close();
}


// --> UTILISATEUR
function authentificate($login, $password)
{
    $query = "SELECT login, name FROM users WHERE login=$1 AND pwd= encode(digest($2, 'sha1'), 'hex')";
    $params = array($login, $password);

    $res = pg_query_params($query, $params);
    $count = pg_num_rows($res);
    $row = pg_fetch_array($res);
    return array($count, $row);
}

// --> CREER L'UTILISATEUR
function register_user($login, $password, $invitcode)
{
    $query = "insert into users (login, name, pwd) 
    select username, username, encode(digest( $1, 'sha1'), 'hex') as pwd 
    from invit_users 
    where username = $2 and invitcode = $3 and status = 'en attente'";
    $params = array($password, $login, $invitcode);
    $res = pg_query_params($query, $params);
    $count = pg_num_rows($res);
    $row = pg_fetch_array($res);

    $query = "update invit_users set status = 'cree'
    where username = $1 and invitcode = $2 and status = 'en attente'";
    $params = array($login, $invitcode);
    $res = pg_query_params($query, $params);
    $count = pg_num_rows($res);
    $row = pg_fetch_array($res);

    $query = "SELECT login, name FROM users WHERE login=$1 AND pwd=encode(digest($2, 'sha1'), 'hex')";
    $params = array($login, $password);
    $res = pg_query_params($query, $params);
    $count = pg_num_rows($res);
    $row = pg_fetch_array($res);

    return array($count, $row);
}



function get_geojson_a_flasher()
{
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    
    if (!empty($_SESSION['login_name'])) {
        $query = "
        with liste_a_flasher as (
            select row_number() OVER (ORDER BY pos.inv_name) as id, pos.inv_name, pos.lat, pos.lon,
            et.points,
            et.etat,
            et.last_maj,
            et.image1,
            et.image2,
            et.image3
            from public.positions as pos
            left join etat as et on (et.inv_name = pos.inv_name)
            left join modif_state_user as uf2  on (uf2.inv_name = pos.inv_name  and uf2.user_name=$1)
            where 
            pos.lat is not null 
            and pos.lon is not null
            and coalesce (uf2.etat, et.etat)  in ('Un peu dégradé','Inconnu','Dégradé','OK')
            and pos.inv_name not in (select uf.inv_name from user_flash as uf where uf.status = 'flash' and uf.user_name=$1)
        )
        select '{\"type\":\"Feature\",\"id\":\"'|| id ||'\",\"geometry\": {\"type\":\"Point\", \"coordinates\":['|| lon || ',' || lat|| ']}, \"properties\":{\"name\":\"'|| inv_name || '\",\"points\":\"'|| points ||'\",\"etat\":\"'|| etat ||'\",\"last_maj\":\"'|| last_maj ||'\",\"image1\":\"'|| image1 ||'\",\"image2\":\"'|| image2 ||'\",\"image3\":\"'|| image3 ||'\"  
}},' as feature from liste_a_flasher
        ";
        
        $params = array($_SESSION['login_name']);
        $res = pg_query_params($query, $params);
        $data = pg_fetch_all($res);

        /*$geojson='{\"type\" : \"FeatureCollection\", \"crs\" : {\"type\" : \"name\", \"properties\" : {\"name\" : \"EPSG:4326\"}},\"features\":[';*/
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
        return $geojson;
    }
}


function get_geojson_deja_flashe()
{
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    
    if (!empty($_SESSION['login_name'])) {
        $query = "
        with liste_a_flasher as (
            select row_number() OVER (ORDER BY pos.inv_name) as id, pos.inv_name, pos.lat, pos.lon,
            et.points,
            et.etat,
            et.last_maj,
            et.image1,
            et.image2,
            et.image3
            from public.positions as pos
            left join etat as et on (et.inv_name = pos.inv_name)
            where 
            pos.lat is not null 
            and pos.lon is not null
            and pos.inv_name in (select uf.inv_name from user_flash as uf where uf.status = 'flash' and uf.user_name=$1)
        )
        select '{\"type\":\"Feature\",\"id\":\"'|| id ||'\",\"geometry\": {\"type\":\"Point\", \"coordinates\":['|| lon || ',' || lat|| ']}, \"properties\":{\"name\":\"'|| inv_name || '\",\"points\":\"'|| points ||'\",\"etat\":\"'|| etat ||'\",\"last_maj\":\"'|| last_maj ||'\",\"image1\":\"'|| image1 ||'\",\"image2\":\"'|| image2 ||'\",\"image3\":\"'|| image3 ||'\"  
}},' as feature from liste_a_flasher
        ";
        
        $params = array($_SESSION['login_name']);
        $res = pg_query_params($query, $params);
        $data = pg_fetch_all($res);

        /*$geojson='{\"type\" : \"FeatureCollection\", \"crs\" : {\"type\" : \"name\", \"properties\" : {\"name\" : \"EPSG:4326\"}},\"features\":[';*/
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
        return $geojson;
    }
}


function get_geojson_detruits()
{
    $geojson='{\"type\" : \"FeatureCollection\", \"features\":[';
    
    if (!empty($_SESSION['login_name'])) {
        $query = "
        with liste_a_flasher as (
            select row_number() OVER (ORDER BY pos.inv_name) as id, pos.inv_name, pos.lat, pos.lon,
            et.points,
            et.etat,
            et.last_maj,
            et.image1,
            et.image2,
            et.image3
            from public.positions as pos
            left join etat as et on (et.inv_name = pos.inv_name)
            left join modif_state_user as uf2  on (uf2.inv_name = pos.inv_name  and uf2.user_name=$1)
            where 
            pos.lat is not null 
            and pos.lon is not null
            and coalesce (uf2.etat, et.etat) not in ('Un peu dégradé','Inconnu','Dégradé','OK')
            and pos.inv_name not in (select uf.inv_name from user_flash as uf where uf.status = 'flash' and uf.user_name=$1)
        )
        select '{\"type\":\"Feature\",\"id\":\"'|| id ||'\",\"geometry\": {\"type\":\"Point\", \"coordinates\":['|| lon || ',' || lat|| ']}, \"properties\":{\"name\":\"'|| inv_name || '\",\"points\":\"'|| points ||'\",\"etat\":\"'|| etat ||'\",\"last_maj\":\"'|| last_maj ||'\",\"image1\":\"'|| image1 ||'\",\"image2\":\"'|| image2 ||'\",\"image3\":\"'|| image3 ||'\"  
}},' as feature from liste_a_flasher
        ";
        
        $params = array($_SESSION['login_name']);
        $res = pg_query_params($query, $params);
        $data = pg_fetch_all($res);

        /*$geojson='{\"type\" : \"FeatureCollection\", \"crs\" : {\"type\" : \"name\", \"properties\" : {\"name\" : \"EPSG:4326\"}},\"features\":[';*/
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
        return $geojson;
    }
}

function update_status($inv_name, $out_status){
    //pour changer le l'etat de l'invaders de ok a detruit par exemple, mais user par user
    $query = "delete from modif_state_user where inv_name=$1 and user_name=$2";
    $params = array($inv_name, $_SESSION['login_name']);
    $res = pg_query_params($query, $params);
    $data = pg_fetch_all($res);

    $query = "Insert into modif_state_user(user_name, inv_name, etat, date_modif) values ($1, $2, $3, current_timestamp)";
    $params = array($_SESSION['login_name'], $inv_name, $out_status);
    $res = pg_query_params($query, $params);
    $data = pg_fetch_all($res);
    return 1;
}


function suppri_flash($inv_name){
    //supr du flash dans la base user pour un invader donnée
    $query = "delete from user_flash where user_name=$1 and inv_name=$2;";
    $params = array($_SESSION['login_name'], $inv_name);
    $res = pg_query_params($query, $params);
    $data = pg_fetch_all($res);
}

function ajout_flash($inv_name){
    //ajout d'un nouveau flash par un user dans la base user_flash
    //deja on supprime, au cas ou il existe deja
    suppri_flash($inv_name);

    //puis on l'ajoute
    $query = " INSERT INTO user_flash (user_name, inv_name, status, date_flash)
     SELECT $1, $2, 'flash', current_timestamp as date_flash
     WHERE NOT EXISTS (SELECT inv_name FROM user_flash WHERE user_name = $1 and inv_name = $2)
     and EXISTS (select inv_name from etat where inv_name = $2)
    ";
    $params = array($_SESSION['login_name'], $inv_name);
    $res = pg_query_params($query, $params);
    $data = pg_fetch_all($res);   
    return 1;
}

?>