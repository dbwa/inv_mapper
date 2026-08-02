<?php
session_start();
include_once(__DIR__ . '/../../config.php');
include_once("./../../fonctions.inc.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
connect();


$achievements = get_achievements();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes badges</title>

    <!-- Inclure jQuery et DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <style>
        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            perspective: 1000px; /* Perspective pour les petites cartes si on ajoute des effets 3D au survol */
        }

        h3 {
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Tiny5', Arial, sans-serif;
        }

        .card-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }

        .achievement-card {
            width: 150px;
            height: 150px;
            background-color: #333;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            transform-style: preserve-3d;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .achievement-card:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 16px rgba(0,0,0,0.5);
        }

        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            border-radius: 10px;
            overflow: hidden;
            background-color: inherit; 
        }
        
        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-sizing: border-box;
            text-align: center;
            border-radius: 10px;
        }

        .card-logo {
            width: 60px;
            height: 60px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }

        .card-title {
            font-size: 15px;
            font-weight: bold;
            font-family: 'Tiny5', Arial, sans-serif;
        }
        
        .gold-border .card-inner {
            border: 4px solid #ffd700;
            box-shadow: 0 0 15px #ffd700, inset 0 0 10px rgba(255, 215, 0, 0.5);
        }

        .silver-border .card-inner {
            border: 4px solid #c0c0c0;
            box-shadow: 0 0 15px #c0c0c0, inset 0 0 10px rgba(192, 192, 192, 0.5);
        }

        .bronze-border .card-inner {
            border: 4px solid #cd7f32;
        }
        
        
        .standard-border .card-inner {
             border: 4px solid #adcae6;
        }

        .glass-shine .card-inner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to right bottom,
                rgba(255,255,255,0.3) 0%,
                rgba(255,255,255,0.1) 30%,
                transparent 50%
            );
            transform: rotate(45deg);
            transition: opacity 0.3s;
            opacity: 0.7;
            pointer-events: none;
            border-radius: 10px;
        }
         .achievement-card:hover .glass-shine .card-inner::before,
         .enlarged-card.glass-shine .card-inner::before { /* Appliquer aussi sur la carte agrandie */
            opacity: 1;
        }

        .holographic-logo .card-logo {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #4f4f4f 25%, #303030 25%, #303030 50%, #4f4f4f 50%, #4f4f4f 75%, #303030 75%, #303030 100%);
            background-size: 40px 40px;
        }

        .holographic-logo .card-logo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -100%;
            width: 50%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.4) 50%,
                rgba(255,255,255,0) 100%
            );
            transform: skewX(-25deg);
            animation: holographic-shine 4s infinite linear;
            opacity: 0.8;
        }

        @keyframes holographic-shine {
            0% { left: -100%; }
            50% { left: 150%; }
            100% { left: 150%; }
        }

        .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;  /* Utilisation de vw au lieu de % */
                height: 100vh; /* Utilisation de vh au lieu de % */
                background-color: rgba(0,0,0,0.85);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                perspective: 1500px;
                box-sizing: border-box;
                /* Suppression du padding ici */
            }

        .modal-content {
                position: fixed; /* Changed from relative */
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                padding: 20px;
                max-width: 90vw;
                max-height: 90vh;
            }

        .enlarged-card {
            /* Ajuster les dimensions pour être sûr que ça rentre dans la vue */
            width: clamp(300px, 80vw, 400px);
            height: clamp(400px, 70vh, 500px);
            margin: 0 auto; /* Centre horizontalement */
        }
        
        .enlarged-card .card-inner {
            background-color: #333; /* CHANGEMENT: Fond déplacé ici */
            box-shadow: 0 10px 30px rgba(0,0,0,0.7);
            flex-grow: 1; /* Pour que card-inner remplisse enlarged-card */
            /* Les autres propriétés de card-inner (position, width, height 100%, transform-style, border-radius, overflow) 
               sont héritées ou déjà définies par la classe .card-inner de base */
        }
        
        .enlarged-card .card-face {
            justify-content: flex-start; /* Aligner le contenu en haut */
            align-items: stretch; /* Étirer les éléments enfants en largeur */
            padding: 20px; /* Padding interne réduit */
        }

        .enlarged-card .card-logo {
            width: 80px; /* Taille du logo réduite */
            height: 80px;
            font-size: 32px; /* Taille de la police du logo réduite */
            margin-top: 10px;
            margin-bottom: 15px; 
            align-self: center; 
        }
        .enlarged-card .card-title {
            font-size: 20px; /* Taille du titre réduite */
            margin-bottom: 15px; 
            text-align: center; 
        }
        
        .enlarged-card .card-description {
            font-size: 14px; /* Taille de la description réduite */
            color: #e0e0e0;
            line-height: 1.5;
            text-align: justify;
            width: 100%; 
            box-sizing: border-box; 
            max-height: 150px; /* Hauteur max pour la description, ajustée */
            overflow-y: auto;
            padding: 10px; 
            /* border: 1px dashed red;  Suppression de la bordure de débogage */
            min-height: 30px; 
        }

        .enlarged-card .card-deblo {
            font-size: 12px;
            color: #888;
            font-style: italic;
            text-align: center;
            margin-top: 15px;
            width: 100%;
            padding: 5px;
        }

        .enlarged-card .card-date_obt {
            font-size: 12px;
            color: #888;
            font-style: italic;
            text-align: center;
            margin-top: 15px;
            width: 100%;
            padding: 5px;
        }

        .enlarged-card .card-description::-webkit-scrollbar {
            width: 8px;
        }
        .enlarged-card .card-description::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.2);
            border-radius: 4px;
        }
        .enlarged-card .card-description::-webkit-scrollbar-thumb {
            background: #666;
            border-radius: 4px;
        }
        .enlarged-card .card-description::-webkit-scrollbar-thumb:hover {
            background: #888;
        }

        body.modal-open {
            overflow: hidden; /* Empêche le scroll du body quand le modal est ouvert */
        }

    </style>


</head>
<body>
    <?php include_once(__DIR__ . '/../../navbar.php'); ?>

    <h3>Mes badges</h3>

    <div class="card-gallery">

     <?php foreach ($achievements['data'] as $achievement): ?>
        <?php
        preg_match("/<div class='message'>(.*?)<\/div>/", $achievement['description'], $message);
        preg_match("/<div='deblo'>(.*?)<\/div>/", $achievement['description'], $deblo);
        $message = $message[1] ?? '';
        $deblo = $deblo[1] ?? '';
        ?>

        <div class="achievement-card <?= htmlspecialchars($achievement['niveau']) ?>-border glass-shine" 
             data-title="<?= htmlspecialchars($achievement['nom_badge']) ?>" data-logo="<?= htmlspecialchars($achievement['icone']) ?>" data-tier="<?= htmlspecialchars($achievement['niveau']) ?>"
             data-description="<?= $message ?>"
             data-deblo="<?= $deblo ?>"
             data-date_obt="Obtenu le <?= date('d/m/Y', strtotime($achievement['date_obtention'])) ?>">
            <div class="card-inner"><div class="card-face"><div class="card-logo"><?= htmlspecialchars($achievement['icone']) ?></div><div class="card-title"><?= htmlspecialchars($achievement['nom_badge']) ?></div></div></div>
        </div>

      <?php endforeach; ?>

    </div>
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content" id="modalContent">
            <!-- Le contenu de .enlarged-card sera injecté ici par JS -->
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.achievement-card');
        const modalOverlay = document.getElementById('modalOverlay');
        const modalContent = document.getElementById('modalContent');
        let activeEnlargedCardInner = null; // Référence à l'élément .card-inner de la carte agrandie

        cards.forEach(card => {
            card.addEventListener('click', () => {
                showModal(card);
            });
        });

        function showModal(originalCard) {
            // Créer le conteneur externe pour la carte agrandie
            const enlargedCardContainer = document.createElement('div');
            // Copier les classes de la carte originale (pour les bordures, effets, etc.)
            enlargedCardContainer.className = originalCard.className; 
            enlargedCardContainer.classList.add('enlarged-card'); // Ajouter la classe spécifique à la carte agrandie
            enlargedCardContainer.classList.remove('achievement-card'); // Retirer la classe de la petite carte

            // Cloner la structure interne de la carte (.card-inner et son contenu)
            const cardInnerClone = originalCard.querySelector('.card-inner').cloneNode(true);
            const cardFaceClone = cardInnerClone.querySelector('.card-face');
            cardFaceClone.innerHTML = ''; // Vider la face pour la reconstruire avec plus de détails

            // Récupérer les données de la carte originale
            const titleText = originalCard.dataset.title || "Titre de l'accomplissement";
            const logoChar = originalCard.dataset.logo || "❓";
            const descriptionText = originalCard.dataset.description || "Aucune description disponible.";
            
            // console.log("Description récupérée:", descriptionText); // Débogage

            // Créer et ajouter le logo
            const logoEl = document.createElement('div');
            logoEl.className = 'card-logo'; // Utilise les styles de .enlarged-card .card-logo
            logoEl.textContent = logoChar;
            cardFaceClone.appendChild(logoEl);

            // Créer et ajouter le titre
            const titleEl = document.createElement('div');
            titleEl.className = 'card-title'; // Utilise les styles de .enlarged-card .card-title
            titleEl.textContent = titleText;
            cardFaceClone.appendChild(titleEl);

            // Créer et ajouter la description
            const descriptionEl = document.createElement('div');
            descriptionEl.className = 'card-description'; // Utilise les styles de .enlarged-card .card-description
            descriptionEl.textContent = descriptionText;
            cardFaceClone.appendChild(descriptionEl);

            const debloText = originalCard.dataset.deblo;
            if (debloText) {
                const debloEl = document.createElement('div');
                debloEl.className = 'card-deblo';
                debloEl.textContent = debloText;
                cardFaceClone.appendChild(debloEl);
            }

            const date_obtText = originalCard.dataset.date_obt;
            if (date_obtText) {
                const date_obtEl = document.createElement('div');
                date_obtEl.className = 'card-date_obt';
                date_obtEl.textContent = date_obtText;
                cardFaceClone.appendChild(date_obtEl);
            }
            
            // Ajouter le .card-inner cloné et modifié au conteneur .enlarged-card
            enlargedCardContainer.appendChild(cardInnerClone);
            
            // Vider le contenu précédent du modal et ajouter la nouvelle carte agrandie
            modalContent.innerHTML = ''; 
            modalContent.appendChild(enlargedCardContainer);
            
            // Garder une référence à .card-inner pour la manipulation 3D
            activeEnlargedCardInner = cardInnerClone; 

            // Afficher le modal
            modalOverlay.style.display = 'flex';
            document.body.classList.add('modal-open');

            modalContent.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Configurer la manipulation 3D pour le nouvel .card-inner
            if (activeEnlargedCardInner) {
                setup3DManipulation(activeEnlargedCardInner);
            }
        }

        modalOverlay.addEventListener('click', (event) => {
            // Fermer le modal si on clique sur l'overlay (en dehors de modalContent)
            if (event.target === modalOverlay) {
                closeModal();
            }
        });

        function closeModal() {
            modalOverlay.style.display = 'none';
            document.body.classList.remove('modal-open');
            if (activeEnlargedCardInner) {
                // Réinitialiser la transformation pour éviter les sauts lors de la prochaine ouverture
                activeEnlargedCardInner.style.transform = 'rotateX(0deg) rotateY(0deg)'; 
                activeEnlargedCardInner = null;
            }
            modalContent.innerHTML = ''; // Vider le contenu du modal
        }

        function setup3DManipulation(cardInnerElement) {
            let isDragging = false;
            let startX, startY;
            let currentRotateX = 0, currentRotateY = 0; 

            // Cible pour les événements de souris/toucher: modalContent ou cardInnerElement lui-même
            // Utiliser modalContent peut être plus simple pour la capture, mais s'assurer que la logique de drag ne s'applique qu'à la carte
            const dragTarget = cardInnerElement; // Ou modalContent si on gère bien les cibles

            function handleDragStart(clientX, clientY) {
                isDragging = true;
                startX = clientX;
                startY = clientY;
                // Lire les rotations actuelles depuis le style pour continuer à partir de là
                const existingTransform = cardInnerElement.style.transform;
                const matchX = existingTransform.match(/rotateX\(([^d]*)deg\)/);
                const matchY = existingTransform.match(/rotateY\(([^d]*)deg\)/);
                currentRotateX = matchX ? parseFloat(matchX[1]) : 0;
                currentRotateY = matchY ? parseFloat(matchY[1]) : 0;
                cardInnerElement.style.transition = 'none'; // Désactiver la transition pendant le drag
            }

            function handleDragMove(clientX, clientY) {
                if (!isDragging) return;
                const deltaX = clientX - startX;
                const deltaY = clientY - startY;
                const sensitivity = 10; // Ajuster pour la sensibilité de la rotation
                
                let newRotateY = currentRotateY + (deltaX / sensitivity);
                let newRotateX = currentRotateX - (deltaY / sensitivity); // Inverser pour un mouvement naturel
                
                // Limiter l'angle de rotation pour éviter que la carte ne se retourne complètement ou ne devienne illisible
                newRotateX = Math.max(-60, Math.min(60, newRotateX)); // Limite pour X
                newRotateY = Math.max(-60, Math.min(60, newRotateY)); // Limite pour Y

                cardInnerElement.style.transform = `rotateX(${newRotateX}deg) rotateY(${newRotateY}deg)`;
            }

            function handleDragEnd() {
                if (!isDragging) return;
                isDragging = false;
                cardInnerElement.style.transition = 'transform 0.2s ease-out'; // Rétablir la transition pour un effet lisse
                // Les valeurs currentRotateX/Y sont déjà à jour grâce à la lecture au début du drag
            }
            
            // Nettoyer les anciens écouteurs pour éviter les duplications si setup3DManipulation est appelé plusieurs fois
            // sur des éléments qui pourraient persister d'une manière ou d'une autre (même si ici on recrée la carte)
            dragTarget.onmousedown = null;
            document.onmousemove = null; 
            document.onmouseup = null;
            dragTarget.ontouchstart = null;
            dragTarget.ontouchmove = null;
            dragTarget.ontouchend = null;

            // Attacher les nouveaux écouteurs
            dragTarget.onmousedown = (event) => {
                event.preventDefault(); // Empêcher la sélection de texte pendant le drag
                handleDragStart(event.clientX, event.clientY);
            };
            document.onmousemove = (event) => { // Écouter sur document pour un drag plus fluide même si la souris sort de l'élément
                if (isDragging) { // Appliquer le mouvement seulement si le drag a commencé sur la cible
                    handleDragMove(event.clientX, event.clientY);
                }
            };
            document.onmouseup = () => { // Écouter sur document pour terminer le drag même si la souris est relâchée ailleurs
                if (isDragging) {
                    handleDragEnd();
                }
            };
            
            dragTarget.ontouchstart = (event) => {
                // event.preventDefault(); // Peut empêcher le scroll sur la description si elle est scrollable. À tester.
                if (event.touches.length === 1) { // Gérer un seul doigt pour la rotation
                    const touch = event.touches[0];
                    handleDragStart(touch.clientX, touch.clientY);
                }
            };
            dragTarget.ontouchmove = (event) => {
                if (!isDragging || event.touches.length !== 1) return;
                event.preventDefault(); // Important pour empêcher le scroll de la page pendant la rotation sur mobile
                const touch = event.touches[0];
                handleDragMove(touch.clientX, touch.clientY);
            };
            dragTarget.ontouchend = () => {
                if (isDragging) {
                    handleDragEnd();
                }
            };
        }
    });
    </script>

<br>
</body>

<?php include_once(__DIR__ . '/../../footer.php'); ?>

</html>