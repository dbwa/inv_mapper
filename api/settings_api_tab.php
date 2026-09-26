<?php
/**
 * Onglet "API" de la page settings.php.
 *
 * Affiche :
 *   - la cle API active de l'utilisateur (masquee) et ses statistiques d'usage
 *   - un bouton pour generer une nouvelle cle
 *   - un bouton pour revoquer la cle
 *   - la documentation des endpoints avec des exemples prets a copier
 *
 * Ce fichier est inclus depuis settings.php : il dispose donc deja de
 * $username, de la session et de la connexion PDO.
 */

if (!isset($username) || empty($username)) {
    echo '<p>Vous devez etre connecte pour gerer votre cle API.</p>';
    return;
}

$pdo = connect();

// ---------------------------------------------------------------------------
// Gestion de la generation / revocation (POST classique, pas d'AJAX)
// ---------------------------------------------------------------------------

$api_message = null;
$api_message_type = 'ok';
$api_new_key = null;

// Action modifiant des donnees : jeton CSRF obligatoire.
if (isset($_POST['api_action']) && !csrf_validate()) {
    $api_message = 'Session expiree ou requete invalide. Rechargez la page et reessayez.';
    $api_message_type = 'error';
} elseif (isset($_POST['api_action'])) {

    if ($_POST['api_action'] === 'generate') {

        $label = isset($_POST['api_label']) ? trim($_POST['api_label']) : '';
        if ($label === '') {
            $label = 'Cle du ' . date('d/m/Y');
        }
        $label = mb_substr($label, 0, 100);

        // Limite du nombre de cles actives par utilisateur
        $query = "SELECT COUNT(*) FROM api_keys WHERE user_name = ? AND revoked_at IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $active_keys = (int) $stmt->fetchColumn();

        if ($active_keys >= 10) {
            $api_message = "Vous avez deja 10 cles actives. Revoquez-en une avant d'en creer une nouvelle.";
            $api_message_type = 'error';
        } else {
            $new_key = 'inv_' . bin2hex(random_bytes(32));

            $query = "INSERT INTO api_keys (user_name, key_hash, label, created_at)
                      VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username, hash('sha256', $new_key), $label]);

            $api_new_key = $new_key;
            $api_message = "Nouvelle cle API creee. Copiez-la maintenant : elle ne sera plus jamais affichee.";
        }
    }

    if ($_POST['api_action'] === 'revoke') {

        $key_id = isset($_POST['api_key_id']) ? (int) $_POST['api_key_id'] : 0;

        $query = "UPDATE api_keys SET revoked_at = CURRENT_TIMESTAMP
                  WHERE id = ? AND user_name = ? AND revoked_at IS NULL";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$key_id, $username]);

        if ($stmt->rowCount() > 0) {
            $api_message = "Cle API revoquee. Les outils qui l'utilisaient ne fonctionnent plus.";
        } else {
            $api_message = "Cle introuvable ou deja revoquee.";
            $api_message_type = 'error';
        }
    }
}

// ---------------------------------------------------------------------------
// Lecture des cles actives
// ---------------------------------------------------------------------------

$query = "SELECT id, label, created_at, last_used_at, request_count, window_started_at
          FROM api_keys
          WHERE user_name = ? AND revoked_at IS NULL
          ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$api_keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// URL de base de l'API, deduite de l'URL courante
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'votre-site';
$api_base = $scheme . '://' . $host . '/api/v1';
?>

<style>
    .api-key-box {
        background-color: #19191e;
        border: 1px solid #26a69a;
        border-radius: 4px;
        padding: 12px;
        font-family: monospace;
        font-size: 0.95em;
        word-break: break-all;
        color: #7ee8dc;
        margin: 10px 0;
    }
    .api-code {
        background-color: #19191e;
        border-left: 3px solid #26a69a;
        border-radius: 3px;
        padding: 12px;
        font-family: monospace;
        font-size: 0.85em;
        color: #d7d7d7;
        overflow-x: auto;
        white-space: pre;
        margin: 10px 0;
    }
    .api-endpoint {
        font-family: monospace;
        color: #7ee8dc;
    }
    .api-method {
        display: inline-block;
        min-width: 62px;
        text-align: center;
        border-radius: 3px;
        padding: 1px 6px;
        margin-right: 8px;
        font-size: 0.8em;
        font-weight: bold;
        color: #181c1f;
    }
    .api-method.get    { background-color: #4fc3f7; }
    .api-method.post   { background-color: #81c784; }
    .api-method.put    { background-color: #ffb74d; }
    .api-method.delete { background-color: #e57373; }
    .api-table td, .api-table th { padding: 6px 10px; }
    .api-alert {
        border-radius: 4px;
        padding: 12px;
        margin: 12px 0;
    }
    .api-alert.ok    { background-color: #1b5e20; color: #fff; }
    .api-alert.error { background-color: #b71c1c; color: #fff; }
</style>

<h5>API</h5>

<p>
    L'API vous permet de piloter vos flashs et votre carte depuis vos propres outils
    (script Python, application mobile, tableur...), sans passer par la carte.
</p>

<?php if ($api_message): ?>
    <div class="api-alert <?php echo $api_message_type; ?>">
        <?php echo htmlspecialchars($api_message); ?>
    </div>
<?php endif; ?>

<?php if ($api_new_key): ?>
    <p><strong>Votre nouvelle cle API :</strong></p>
    <div class="api-key-box" id="new-api-key"><?php echo htmlspecialchars($api_new_key); ?></div>
    <button class="btn-small waves-effect waves-light teal" type="button" onclick="copyApiKey()">
        Copier la cle
    </button>
    <p style="font-size:0.85em; margin-top:10px;">
        Cette cle ne sera plus jamais affichee. Si vous la perdez, revoquez-la et creez-en une nouvelle.
    </p>
<?php endif; ?>

<!-- ===================================================================== -->
<!-- Gestion des cles                                                       -->
<!-- ===================================================================== -->

<h6 style="margin-top:30px;">Mes cles API</h6>

<?php if (empty($api_keys)): ?>
    <p>Aucune cle API active pour le moment.</p>
<?php else: ?>
    <table class="striped api-table">
        <thead>
            <tr>
                <th>Libelle</th>
                <th>Creee le</th>
                <th>Derniere utilisation</th>
                <th>Requetes (heure en cours)</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($api_keys as $key): ?>
            <tr>
                <td><?php echo htmlspecialchars($key['label']); ?></td>
                <td><?php echo htmlspecialchars($key['created_at']); ?></td>
                <td><?php echo $key['last_used_at'] ? htmlspecialchars($key['last_used_at']) : 'jamais'; ?></td>
                <td><?php echo (int) $key['request_count']; ?></td>
                <td>
                    <form method="post" action="#settings-api" style="margin:0;"
                          onsubmit="return confirm('Revoquer cette cle ? Les outils qui l\'utilisent cesseront de fonctionner.');">
                        <input type="hidden" name="api_action" value="revoke">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="api_key_id" value="<?php echo (int) $key['id']; ?>">
                        <button class="btn-small waves-effect waves-light red darken-2" type="submit">
                            Revoquer
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<form method="post" action="#settings-api" style="margin-top:20px;">
    <input type="hidden" name="api_action" value="generate">
    <?php echo csrf_field(); ?>
    <div class="row" style="margin-bottom:0;">
        <div class="input-field col s12 m6">
            <input id="api_label" name="api_label" type="text" maxlength="100">
            <label for="api_label">Libelle (ex : mon script python)</label>
        </div>
        <div class="col s12 m6" style="padding-top:12px;">
            <button class="btn waves-effect waves-light teal" type="submit">
                Generer une nouvelle cle
            </button>
        </div>
    </div>
</form>

<!-- ===================================================================== -->
<!-- Documentation                                                          -->
<!-- ===================================================================== -->

<h6 style="margin-top:40px;">Documentation</h6>

<p>
    Toutes les requetes doivent presenter votre cle API dans l'en-tete HTTP
    <code>Authorization</code>. Les reponses sont en JSON.
</p>

<div class="api-code">Authorization: Bearer VOTRE_CLE_API</div>

<p style="font-size:0.9em;">
    Par commodite, la cle peut aussi etre passee en parametre d'URL
    (<code>?api_key=VOTRE_CLE_API</code>), mais l'en-tete est plus sur : il n'apparait
    pas dans les journaux des serveurs.
</p>

<h6 style="margin-top:25px;">Points d'entree</h6>

<table class="striped api-table">
    <thead>
        <tr>
            <th>Methode</th>
            <th>URL</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/me</td>
            <td>Informations sur votre compte invader-mapper et votre clé</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/stats</td>
            <td>Nombre d'invaders à flasher / flashés / detruits</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/invaders?status=a_flasher</td>
            <td>Liste des invaders à flasher (GeoJSON)</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/invaders?status=deja_flashe</td>
            <td>Liste des invaders déjà flashés (GeoJSON)</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/invaders?status=detruits</td>
            <td>Liste des invaders déclarés détruits ou inaccessibles (GeoJSON)</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/invaders?status=all</td>
            <td>Les trois listes d'un coup</td>
        </tr>
        <tr>
            <td><span class="api-method get">GET</span></td>
            <td class="api-endpoint">/api/v1/invaders/PA_1238</td>
            <td>Detail d'un invader (position, état (détruit ou pas), état de flash (flashé ou pas))</td>
        </tr>
        <tr>
            <td><span class="api-method post">POST</span></td>
            <td class="api-endpoint">/api/v1/invaders/PA_1238/flash</td>
            <td>Marquer comme flashé ("c'est bon, je l'ai pris en photo")</td>
        </tr>
        <tr>
            <td><span class="api-method delete">DELETE</span></td>
            <td class="api-endpoint">/api/v1/invaders/PA_1238/flash</td>
            <td>Annuler le flash ("non, je ne l'ai pas")</td>
        </tr>
        <tr>
            <td><span class="api-method put">PUT</span></td>
            <td class="api-endpoint">/api/v1/invaders/PA_1238/status</td>
            <td>Changer l'état déclaré (corps JSON : <code>{"etat": "Detruit !"}</code> ou <code>{"etat": "OK"}</code>)</td>
        </tr>
        <tr>
            <td><span class="api-method delete">DELETE</span></td>
            <td class="api-endpoint">/api/v1/invaders/PA_1238/status</td>
            <td>Revenir a l'etat global de l'invader</td>
        </tr>
        <tr>
            <td><span class="api-method post">POST</span></td>
            <td class="api-endpoint">/api/v1/login</td>
            <td>Echanger login + mot de passe contre une cle API</td>
        </tr>
    </tbody></table>

<h6 style="margin-top:25px;">Exemples</h6>

<p>Recuperer la liste des invaders à flasher :</p>
<div class="api-code">curl -H "Authorization: Bearer VOTRE_CLE_API" \
     "<?php echo htmlspecialchars($api_base); ?>/invaders?status=a_flasher"</div>

<p>Marquer un invader comme flashé :</p>
<div class="api-code">curl -X POST -H "Authorization: Bearer VOTRE_CLE_API" \
     "<?php echo htmlspecialchars($api_base); ?>/invaders/PA_1238/flash"</div>

<p>Annuler un flash :</p>
<div class="api-code">curl -X DELETE -H "Authorization: Bearer VOTRE_CLE_API" \
     "<?php echo htmlspecialchars($api_base); ?>/invaders/PA_1238/flash"</div>

<p>Déclarer un invader détruit :</p>
<div class="api-code">curl -X PUT -H "Authorization: Bearer VOTRE_CLE_API" \
     -H "Content-Type: application/json" \
     -d '{"etat":"Detruit !"}' \
     "<?php echo htmlspecialchars($api_base); ?>/invaders/PA_1238/status"</div>

<p>Exemple en Python :</p>
<div class="api-code">import requests

API = "<?php echo htmlspecialchars($api_base); ?>"
HEADERS = {"Authorization": "Bearer VOTRE_CLE_API"}

# Les invaders qu'il me reste à flasher
reponse = requests.get(f"{API}/invaders", params={"status": "a_flasher"}, headers=HEADERS)
invaders = reponse.json()["data"]["features"]

for invader in invaders:
    print(invader["properties"]["name"], invader["geometry"]["coordinates"])

# J'ai flashé le premier de la liste
nom = invaders[0]["properties"]["name"]
requests.post(f"{API}/invaders/{nom}/flash", headers=HEADERS)</div>

<p style="font-size:0.9em; margin-top:20px;">
    Les codes HTTP renvoyes sont standards : <code>200</code> succes,
    <code>400</code> parametre invalide, <code>401</code> cle manquante ou invalide,
    <code>404</code> invader inconnu, <code>429</code> trop de requetes.
    En cas d'erreur, le corps contient un objet <code>error</code> avec un
    <code>code</code> et un <code>message</code>.
</p>

<script>
    // Le formulaire de generation recharge la page : on revient sur l'onglet API
    // pour que l'utilisateur voie immediatement sa nouvelle cle.
    document.addEventListener('DOMContentLoaded', function () {
        if (window.location.hash === '#settings-api') {
            var onglet = document.querySelector('.tabs a[href="#settings-api"]');
            if (onglet && window.M && M.Tabs) {
                var instance = M.Tabs.getInstance(onglet.closest('.tabs'));
                if (instance) {
                    instance.select('settings-api');
                }
            }
        }
    });

    function copyApiKey() {
        var el = document.getElementById('new-api-key');
        if (!el) { return; }
        var texte = el.textContent.trim();

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(texte).then(function () {
                M.toast({ html: 'Clé copiée dans le presse-papiers' });
            });
        } else {
            var zone = document.createElement('textarea');
            zone.value = texte;
            document.body.appendChild(zone);
            zone.select();
            document.execCommand('copy');
            document.body.removeChild(zone);
            M.toast({ html: 'Clé copiée dans le presse-papiers' });
        }
    }
</script>
