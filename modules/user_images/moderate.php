<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
include_once(__DIR__ . '/../../fonctions.inc.php');
include_once(__DIR__ . '/../../config.php');

// Vérification des droits
if (
    !isset($_SESSION['login_user']) ||
    !isset($_SESSION['user_type']) ||
    $_SESSION['user_type'] !== 'admin'
) {
    header('Location: index.php');
    exit();
}

// Récupération des photos en attente
$pending_photos = get_user_photos_by_invader('pending');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modération des photos</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    
    <!-- Inclure jQuery et DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <style>
        body {
            background: #181c24;
            color: #f1f1f1;
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-top: 30px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto 0 auto;
            background: #23283a;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
            padding: 30px 30px 50px 30px;
        }
        .invader-row {
            margin-bottom: 40px;
            background: #23283a;
            border-radius: 8px;
            padding: 20px 10px 10px 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .invader-row h3 {
            margin-top: 0;
            color: #ffb300;
            font-size: 1.3em;
            letter-spacing: 1px;
        }
        .ref-photo {
            display: inline-block;
            vertical-align: top;
            margin-right: 30px;
        }
        .ref-photo img {
            max-width: 120px;
            border-radius: 6px;
            border: 2px solid #444;
            background: #222;
        }
        .photo-container {
            display: inline-block;
            margin: 0 10px 10px 0;
            position: relative;
            background: #22242e;
            border-radius: 8px;
            padding: 10px 10px 15px 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.18);
            min-width: 220px;
            vertical-align: top;
        }
        .photo-container img {
            max-width: 200px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #181c24;
        }
        .photo-container.processing {
            opacity: 0.6;
        }
        .status-indicator {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 2;
        }
        .status-indicator.accepted {
            background: rgba(40, 167, 69, 0.9);
        }
        .status-indicator.rejected {
            background: rgba(220, 53, 69, 0.9);
        }
        .status-indicator.error {
            background: rgba(220, 53, 69, 0.9);
        }
        .photo-container button {
            background: #23283a;
            color: #ffb300;
            border: 1px solid #ffb300;
            border-radius: 4px;
            padding: 6px 14px;
            margin: 5px 2px 0 2px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .photo-container button:hover:not(:disabled) {
            background: #ffb300;
            color: #23283a;
        }
        .photo-container button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .photo-meta {
            font-size: 0.95em;
            color: #bbb;
            margin-top: 5px;
        }
        .section-title {
            margin-top: 40px;
            color: #ffb300;
            font-size: 1.2em;
            border-bottom: 1px solid #444;
            padding-bottom: 5px;
        }
        .show-moderated-btn {
            display: block;
            margin: 30px auto 0 auto;
            background: #23283a;
            color: #ffb300;
            border: 1px solid #ffb300;
            border-radius: 4px;
            padding: 10px 30px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .show-moderated-btn:hover {
            background: #ffb300;
            color: #23283a;
        }
        #moderated-section {
            display: none;
            margin-top: 30px;
        }
        .no-photos {
            color: #888;
            text-align: center;
            margin: 40px 0;
        }
        
        .current-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            color: white;
            z-index: 1;
        }
        
        .current-status.accepted {
            background: rgba(40, 167, 69, 0.9);
        }
        
        .current-status.rejected {
            background: rgba(220, 53, 69, 0.9);
        }
        
        .photo-container.accepted {
            border-left: 3px solid rgba(40, 167, 69, 0.9);
        }
        
        .photo-container.rejected {
            border-left: 3px solid rgba(220, 53, 69, 0.9);
        }
        
        .action-buttons {
            margin-top: 10px;
        }
        
        .action-buttons button:disabled {
            opacity: 0.3;
            border-color: #666;
            color: #666;
        }

    </style>
    <script>
function updateStatus(photoId, status) {
    const container = $(`#photo-${photoId}`);
    if (container.hasClass('processing')) return;
    container.addClass('processing');
    container.find('button').prop('disabled', true);
    container.append('<div class="status-indicator">Traitement en cours...</div>');

    $.ajax({
        url: '/modules/user_images/ajax_update_photo_status.php',
        method: 'POST',
        data: { photo_id: photoId, status: status, csrf_token: CSRF_TOKEN },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                container.find('.status-indicator')
                    .text(status === 'accepted' ? 'Acceptée' : 'Rejetée')
                    .addClass(status);
                setTimeout(() => {
                    container.fadeOut(400, function() {
                        const invaderRow = container.closest('.invader-row');
                        const remainingPhotos = invaderRow.find('.photo-container:visible').length;
                        if (remainingPhotos <= 1) {
                            invaderRow.fadeOut(400, function() { invaderRow.remove(); });
                        } else {
                            container.remove();
                        }
                    });
                }, 1000);
            } else {
                container.removeClass('processing');
                container.find('.status-indicator')
                    .text('Erreur : ' + response.message)
                    .addClass('error');
                container.find('button').prop('disabled', false);
            }
        },
        error: function() {
            container.removeClass('processing');
            container.find('.status-indicator')
                .text('Erreur de connexion')
                .addClass('error');
            container.find('button').prop('disabled', false);
        }
    });
}

// Chargement dynamique des photos déjà modérées
let moderatedLoaded = false;

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

function showModeratedPhotos() {
    if (moderatedLoaded) {
        $('#moderated-section').slideToggle();
        return;
    }
    $('#moderated-section').html('<div class="no-photos">Chargement...</div>').slideDown();
    $.ajax({
        url: '/modules/user_images/ajax_get_moderated_photos.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (!data || Object.keys(data).length === 0) {
                $('#moderated-section').html('<div class="no-photos">Aucune photo modérée pour le moment.</div>');
                return;
            }
            let html = '<div class="section-title">Photos déjà traitées</div>';
            for (const inv_name in data) {
                const inv = data[inv_name];
                html += `<div class="invader-row">
                    <h3>${escapeHtml(inv_name)}</h3>
                    <div class="ref-photo">
                        <img src="${escapeHtml(inv.image_ref)}" alt="Reference">
                    </div>`;
                inv.photos.forEach(photo => {
                    let statusLabel = photo.status === 'accepted' ? 'Acceptée' : 'Rejetée';
                    let statusClass = photo.status === 'accepted' ? 'accepted' : 'rejected';
                    html += `
                    <div class="photo-container ${statusClass}" id="photo-${escapeHtml(photo.id)}">
                        <div class="current-status ${statusClass}">${statusLabel}</div>
                        <img src="${escapeHtml(photo.photo_path)}" alt="User photo">
                        <div class="action-buttons">
                            <button onclick="updateStatus(${escapeHtml(photo.id)}, 'accepted')"
                                    ${photo.status === 'accepted' ? 'disabled' : ''}>
                                Accepter
                            </button>
                            <button onclick="updateStatus(${escapeHtml(photo.id)}, 'rejected')"
                                    ${photo.status === 'rejected' ? 'disabled' : ''}>
                                Rejeter
                            </button>
                        </div>
                        <div class="photo-meta">Par: ${escapeHtml(photo.login)}</div>
                        <div class="photo-meta">Le: ${escapeHtml(photo.upload_date)}</div>
                        <div class="photo-meta">Modéré par: ${escapeHtml(photo.validated_by || '-')}</div>
                        <div class="photo-meta">Le: ${escapeHtml(photo.validation_date || '-')}</div>
                    </div>`;
                });
                html += `</div>`;
            }
            $('#moderated-section').html(html);
            moderatedLoaded = true;
        },
        error: function() {
            $('#moderated-section').html('<div class="no-photos">Erreur lors du chargement.</div>');
        }
    });
}


    </script>
</head>

<body>

<?php include_once("./../../navbar.php"); ?>
    <h1>Modération des photos</h1>
    <div class="container">
        <div class="section-title">Photos à modérer</div>
        <?php if (empty($pending_photos)): ?>
            <div class="no-photos">Aucune photo à modérer pour le moment.</div>
        <?php else: ?>
            <?php foreach ($pending_photos as $inv_name => $data): ?>
            <div class="invader-row">
                <h3><?php echo htmlspecialchars($inv_name); ?></h3>
                <div class="ref-photo">
                    <img src="<?php echo htmlspecialchars($data['image_ref']); ?>" alt="Reference">
                </div>
                <?php foreach ($data['photos'] as $photo): ?>
                <div class="photo-container" id="photo-<?php echo $photo['id']; ?>">
                    <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="User photo">
                    <div>
                        <button onclick="updateStatus(<?php echo $photo['id']; ?>, 'accepted')">Accepter</button>
                        <button onclick="updateStatus(<?php echo $photo['id']; ?>, 'rejected')">Rejeter</button>
                    </div>
                    <div class="photo-meta">Par: <?php echo htmlspecialchars($photo['login']); ?></div>
                    <div class="photo-meta">Le: <?php echo htmlspecialchars($photo['upload_date']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <button class="show-moderated-btn" onclick="showModeratedPhotos()">Afficher les photos déjà traitées</button>
        <div id="moderated-section"></div>
    </div>
</body>
<?php include_once('./../../footer.php'); ?>
</html>