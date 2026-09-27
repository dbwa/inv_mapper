<?php
/**
 * Onglet "Compte" de la page settings.php.
 *
 * Contient :
 *   - la liste des dernières connexions (date, appareil, navigateur)
 *   - la suppression du compte en deux étapes
 *
 * Ce fichier est inclus depuis settings.php : il dispose donc déjà de
 * $username, de la session et de la connexion PDO.
 */

if (!isset($username) || empty($username)) {
    echo '<p>Vous devez être connecté pour gérer votre compte.</p>';
    return;
} 

$pdo = connect();

// ---------------------------------------------------------------------------
// Traitement des actions
// ---------------------------------------------------------------------------

$compte_message = null;
$compte_message_type = 'ok';

// Action modifiant des donnees : jeton CSRF obligatoire.
if (isset($_POST['compte_action']) && !csrf_validate()) {
    $compte_message = "Session expirée ou requête invalide. Rechargez la page et réessayez.";
    $compte_message_type = 'error';
} elseif (isset($_POST['compte_action'])) {

    // --- Demande de suppression de compte ----------------------------------
    if ($_POST['compte_action'] === 'demander_suppression') {

        $confirmation = isset($_POST['confirmation']) ? trim($_POST['confirmation']) : '';

        if ($confirmation !== $username) {
            $compte_message = "La confirmation ne correspond pas à votre identifiant. "
                            . "Saisissez exactement « " . htmlspecialchars($username) . " » pour confirmer.";
            $compte_message_type = 'error';
        } else {
            // Étape 1 : le compte devient inaccessible.
            // Le mot de passe est remplacé par une valeur aléatoire que personne
            // ne connaît, et la date de demande est enregistrée.
            $mot_de_passe_aleatoire = bin2hex(random_bytes(32));

            $query = "UPDATE users
                      SET pwd = ?,
                          deletion_requested_at = CURRENT_TIMESTAMP
                      WHERE login = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([password_hash(sha1($mot_de_passe_aleatoire), PASSWORD_DEFAULT), $username]);

            // On invalide toutes les connexions persistantes de cet utilisateur.
            $query = "DELETE FROM remember_tokens WHERE username = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username]);

            // On révoque les clés API.
            $query = "UPDATE api_keys SET revoked_at = CURRENT_TIMESTAMP
                      WHERE user_name = ? AND revoked_at IS NULL";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username]);

            // On détruit la session en cours.
            $_SESSION = array();
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']);
            }
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            session_destroy();

            // Redirection vers la page de connexion : l'utilisateur n'est plus
            // connecté. On ne peut pas utiliser header() ici, car settings.php a
            // déjà envoyé du HTML avant d'inclure ce fichier. On passe donc par
            // du JavaScript, avec un lien de secours si le JS est désactivé.
            echo '<script>window.location.replace("adherent.php?compte_supprime=1");</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=adherent.php?compte_supprime=1"></noscript>';
            echo '<p>Votre compte a été supprimé. '
               . '<a href="adherent.php?compte_supprime=1">Cliquez ici si vous n\'êtes pas redirigé.</a></p>';
            exit;
        }
    }
}

// ---------------------------------------------------------------------------
// État du compte
// ---------------------------------------------------------------------------

$query = "SELECT login, name, user_type, deletion_requested_at
          FROM users WHERE login = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$compte = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compte) {
    echo '<p>Compte introuvable.</p>';
    return;
}

// ---------------------------------------------------------------------------
// Dernières connexions
// ---------------------------------------------------------------------------

// On récupère les deux connexions les plus récentes :
//   - la première correspond à la session en cours
//   - la seconde est la connexion précédente
$query = "SELECT device_type, browser, temps
          FROM user_connexions
          WHERE user_name = ?
          ORDER BY temps DESC
          LIMIT 2";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$connexions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$connexion_actuelle = null;
$connexion_precedente = null;

if (count($connexions) >= 1) {
    $connexion_actuelle = $connexions[0];
}
if (count($connexions) >= 2) {
    $connexion_precedente = $connexions[1];
}

// Nombre de jours avant la purge définitive des données.
$delai_purge_jours = 30;
?>

<style>
    .compte-alert {
        border-radius: 4px;
        padding: 12px;
        margin: 12px 0;
    }
    .compte-alert.ok    { background-color: #1b5e20; color: #fff; }
    .compte-alert.error { background-color: #b71c1c; color: #fff; }
    .compte-alert.warn  { background-color: #e65100; color: #fff; }
    .compte-table td, .compte-table th { padding: 6px 10px; }
    .compte-actuelle {
        font-size: 0.9em;
        color: #9e9e9e;
        margin-top: 8px;
    }
    .danger-zone {
        border: 1px solid #3a3a44;
        border-radius: 4px;
        padding: 0;
        margin-top: 20px;
        overflow: hidden;
    }
    .danger-collapsible {
        border: none;
        margin: 0;
        box-shadow: none;
    }
    .danger-collapsible .collapsible-header {
        background-color: #2b2b36;
        border-bottom: 1px solid #3a3a44;
        color: #e0e0e0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .danger-collapsible .collapsible-header i {
        color: #9e9e9e;
        margin-right: 0;
    }
    .danger-collapsible .collapsible-header .badge {
        margin-left: auto;
        border-radius: 10px;
        font-size: 0.75em;
        padding: 0 8px;
    }
    .danger-collapsible .collapsible-body {
        background-color: #24242e;
        border-bottom: none;
        padding: 20px;
        font-size: 1rem;
        font-weight: normal;
        line-height: 1.6;
    }
    .danger-collapsible .collapsible-body p,
    .danger-collapsible .collapsible-body li {
        color: #e0e0e0;
        font-size: 1rem;
        font-weight: normal;
    }
    .danger-collapsible .collapsible-body strong {
        font-weight: bold;
    }
</style>

<h5>Mon compte</h5>

<p>
    Connecté en tant que <strong><?php echo htmlspecialchars($compte['login']); ?></strong>
    (<?php echo htmlspecialchars($compte['name']); ?>).
</p>

<?php if ($compte_message): ?>
    <div class="compte-alert <?php echo $compte_message_type; ?>">
        <?php echo $compte_message; ?>
    </div>
<?php endif; ?>

<!-- ===================================================================== -->
<!-- Dernières connexions                                                   -->
<!-- ===================================================================== -->

<h6 style="margin-top:30px;">Dernières connexions</h6>

<?php if (!$connexion_precedente): ?>
    <p>Aucune connexion précédente enregistrée.</p>
<?php else: ?>
    <table class="striped compte-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Appareil</th>
                <th>Navigateur</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($connexion_precedente['temps']); ?> UTC</td>
                <td><?php echo htmlspecialchars($connexion_precedente['device_type']); ?></td>
                <td><?php echo htmlspecialchars($connexion_precedente['browser']); ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<?php if ($connexion_actuelle): ?>
    <p class="compte-actuelle">
        Connexion en cours : <?php echo htmlspecialchars($connexion_actuelle['device_type']); ?>,
        <?php echo htmlspecialchars($connexion_actuelle['browser']); ?>
        (<?php echo htmlspecialchars($connexion_actuelle['temps']); ?> UTC).
    </p>
<?php endif; ?>

<p style="font-size:0.9em; margin-top:15px;">
    Si vous voyez une connexion que vous ne reconnaissez pas, changez votre mot de passe
    dans l'onglet « Mot de passe ».
</p>

<!-- ===================================================================== -->
<!-- Suppression du compte                                                  -->
<!-- ===================================================================== -->

<div class="danger-zone">
    <ul class="collapsible danger-collapsible">
        <li>
            <div class="collapsible-header">
                Supprimer mon compte
                <?php if (!empty($compte['deletion_requested_at'])): ?>
                    <span class="badge grey darken-3 white-text">demande enregistrée</span>
                <?php endif; ?>
            </div>
            <div class="collapsible-body">
                <?php if (!empty($compte['deletion_requested_at'])): ?>
                    <div class="compte-alert warn">
                        Une demande de suppression a déjà été enregistrée le
                        <?php echo htmlspecialchars($compte['deletion_requested_at']); ?>.
                    </div>
                <?php endif; ?>

                <p>
                    La suppression se fait en deux étapes :
                </p>
                <ol>
                    <li>
                        <strong>Immédiatement :</strong> votre compte devient inaccessible. Vos connexions persistantes et vos clés API
                        sont révoquées.
                    </li>
                    <li>
                        <strong>Après <?php echo $delai_purge_jours; ?> jours :</strong> vos données sont
                        définitivement effacées (flashs, badges, préférences, historique de connexions).
                    </li>                
                </ol>

                <p>
                    <strong>Attention :</strong> vos photos déjà publiées restent visibles. Elles ont été
                    partagées sous licence Creative Commons BY-SA 4.0, qui est irrévocable.
                </p>

                <p>
                    Pour confirmer, saisissez votre identifiant
                    (<code><?php echo htmlspecialchars($compte['login']); ?></code>) ci-dessous.
                </p>

                <form method="post" action="#settings-compte"
                      onsubmit="return confirm('Confirmer la suppression de votre compte ? Cette action vous déconnectera immédiatement.');">
                    <input type="hidden" name="compte_action" value="demander_suppression">
                    <?php echo csrf_field(); ?>
                    <div class="row" style="margin-bottom:0;">
                        <div class="input-field col s12 m6">
                            <input id="confirmation" name="confirmation" type="text" autocomplete="off" required>
                            <label for="confirmation">Votre identifiant</label>
                        </div>
                        <div class="col s12 m6" style="padding-top:12px;">
                            <button class="btn waves-effect waves-light red darken-2" type="submit">
                                Supprimer mon compte
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var elems = document.querySelectorAll('.danger-collapsible');
        M.Collapsible.init(elems, {});
    });
</script>
