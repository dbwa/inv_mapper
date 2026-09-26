<?php
/**
 * Jeton CSRF côté client : définit window.CSRF_TOKEN et installe un
 * préfiltre jQuery qui l'ajoute automatiquement à toutes les requêtes POST
 * de la page. À inclure une fois par page (directement ou via navbar.php),
 * après le chargement de jQuery si possible — l'installation est retardée
 * au DOMContentLoaded sinon.
 */
if (defined('CSRF_BOOTSTRAP_DONE')) {
    return;
}
define('CSRF_BOOTSTRAP_DONE', true);
require_once(__DIR__ . '/fonctions.inc.php');
$csrf_token_client = csrf_token();
?>
<script>
(function () {
    function install() {
        if (window.__csrfInstalled) return;
        window.__csrfInstalled = true;
        window.CSRF_TOKEN = <?php echo json_encode($csrf_token_client); ?>;
        if (window.jQuery) {
            window.jQuery.ajaxPrefilter(function (options) {
                var method = (options.method || options.type || 'GET').toUpperCase();
                if (method !== 'POST') return;
                if (options.data === undefined || options.data === null) {
                    options.data = {};
                }
                if (window.jQuery.isPlainObject(options.data)) {
                    options.data.csrf_token = window.CSRF_TOKEN;
                } else if (typeof options.data === 'string') {
                    options.data += (options.data ? '&' : '')
                        + 'csrf_token=' + encodeURIComponent(window.CSRF_TOKEN);
                }
            });
        }
    }
    install();
    document.addEventListener('DOMContentLoaded', install);
})();
</script>
