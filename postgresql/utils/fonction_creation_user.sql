-- creation d'un utilisateur
CREATE FUNCTION create_user_invit(login TEXT, addresse_page text, param1 TEXT)
RETURNS TEXT AS $$
BEGIN
        -- Effectuer le travail sécurisé de la fonction.
        insert into invit_users (username, invitcode, user_type, status)
        values($1, encode(digest($1 || left($1 ,2), 'sha256'), 'hex'), $3, 'en attente');

        RETURN $2||'?user='|| $1 ||'&invi='|| encode(digest($1 || left($1 ,2), 'sha256'), 'hex') ;
END;
$$  LANGUAGE plpgsql