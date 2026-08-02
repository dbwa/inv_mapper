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
    <title>Achievements - Flash Invaders</title>

    <!-- Inclure Materialize CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    
    <!-- Inclure les icônes Material -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        body {
            background-color: #181c1f;
            color: #e0e0e0;
        }


.achievements-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 20px;
  padding: 30px 0;
}

.achievement-card {
  position: relative;
  aspect-ratio: 1/1;
  background: rgba(34, 37, 41, 0.85);
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0,0,0,0.18);
  transition: transform 0.3s, box-shadow 0.3s;
  z-index: 1;
}

.achievement-card::before {
  content: '';
  position: absolute;
  inset: -3px;
  border-radius: 18px;
  background: conic-gradient(
    from var(--angle, 0deg),
    #ffd700 0deg, #ffb300 60deg, #00ffe7 120deg, #ffd700 180deg, #ffb300 240deg, #00ffe7 300deg, #ffd700 360deg
  );
  filter: blur(2px) brightness(1.2);
  opacity: 0.7;
  z-index: 2;
  pointer-events: none;
  transition: filter 0.3s, opacity 0.3s;
  animation: holo-rotate 4s linear infinite;
}

.achievement-card:hover, .achievement-card.active {
  transform: scale(1.15);
  box-shadow: 0 0 32px 0 #ffd70088, 0 2px 8px rgba(0,0,0,0.22);
}

.achievement-card:hover::before, .achievement-card.active::before {
  filter: blur(4px) brightness(1.5);
  opacity: 1;
  animation-play-state: paused;
}

@keyframes holo-rotate {
  to { --angle: 360deg; }
}

.achievement-content {
  position: relative;
  z-index: 3;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
}

.achievement-icon {
  font-size: 48px;
  color: #ffd700;
  margin-top: 22px;
  margin-bottom: 10px;
  filter: drop-shadow(0 2px 4px #0008);
}

.achievement-title {
  font-size: 0.9em;
  color: #fff;
  margin-bottom: 18px;
  text-align: center;
  font-weight: 600;
  letter-spacing: 0.02em;
  text-shadow: 0 1px 2px #0006;
  width: 90%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.achievement-details {
  display: none;
  position: absolute;
  inset: 0;
  background: rgba(24, 28, 31, 0.98);
  border-radius: 14px;
  padding: 12px;
  color: #fff;
  animation: fadeIn 0.2s ease;
  overflow: auto;
  z-index: 10;
}

.achievement-details .achievement-message {
  color: #ffd700;
  font-size: 0.9em;
  margin: 8px 0;
  font-style: italic;
  text-shadow: 0 1px 2px #0008;
}

.achievement-details .achievement-unlock {
  color: #b0b0b0;
  font-size: 0.8em;
  margin: 8px 0;
}

.achievement-details .achievement-date {
  color: #888;
  font-size: 0.7em;
  text-align: right;
  margin-top: 8px;
  position: absolute;
  bottom: 8px;
  right: 8px;
}


    </style>
</head>

<body>
    <?php include_once("./navbar.php"); ?>

   <div class="container">
        <h3>Achievements</h3>

        <?php if ($achievements['success'] && $achievements['count'] > 0): ?>



<div class="achievements-grid">
  <?php foreach ($achievements['data'] as $achievement): ?>
    <?php
    preg_match("/<div class='message'>(.*?)<\/div>/", $achievement['description'], $message);
    preg_match("/<div='deblo'>(.*?)<\/div>/", $achievement['description'], $deblo);
    $message = $message[1] ?? '';
    $deblo = $deblo[1] ?? '';
    ?>
    <div class="achievement-card">
      <div class="achievement-content">
        <i class="material-icons achievement-icon">emoji_events</i>
        <div class="achievement-title"><?= htmlspecialchars($achievement['nom_badge']) ?></div>
        <div class="achievement-details">
          <div class="achievement-message"><?= $message ?></div>
          <div class="achievement-unlock"><?= $deblo ?></div>
          <div class="achievement-date">
            Obtenu le <?= date('d/m/Y', strtotime($achievement['date_obtention'])) ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>





        </div>

        <?php else: ?>
            <div class="row">
                <div class="col s12">
                    <div class="card-panel red lighten-4 red-text text-darken-4">
                        <?php 
                        if (!$achievements['success']) {
                            echo "Erreur lors de la récupération des achievements : " . htmlspecialchars($achievements['error']);
                        } else {
                            echo "Vous n'avez pas encore débloqué d'achievements. Continuez à jouer pour en gagner !";
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            M.AutoInit();
        });
        




document.querySelectorAll('.achievement-card').forEach(card => {
  card.addEventListener('mousemove', function(e) {
    const rect = this.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width/2;
    const y = e.clientY - rect.top - rect.height/2;
    const angle = Math.atan2(y, x) * 180 / Math.PI + 180;
    this.style.setProperty('--angle', angle + 'deg');
  });
  card.addEventListener('mouseleave', function() {
    this.style.removeProperty('--angle');
  });

  // Affichage des détails (comme avant)
  const details = card.querySelector('.achievement-details');
  const isMobile = window.matchMedia("(max-width: 600px)").matches;
  function closeAllDetails() {
    document.querySelectorAll('.achievement-card').forEach(c => {
      c.classList.remove('active');
      c.querySelector('.achievement-details').style.display = 'none';
    });
  }
  if (isMobile) {
    card.addEventListener('click', function(e) {
      e.stopPropagation();
      if (details.style.display === 'block') {
        details.style.display = 'none';
        card.classList.remove('active');
      } else {
        closeAllDetails();
        details.style.display = 'block';
        card.classList.add('active');
      }
    });
    document.body.addEventListener('click', closeAllDetails);
  } else {
    card.addEventListener('mouseenter', function() {
      details.style.display = 'block';
      card.classList.add('active');
    });
    card.addEventListener('mouseleave', function() {
      details.style.display = 'none';
      card.classList.remove('active');
    });
  }
});




    </script>

</body>
<?php include_once(__DIR__ . '/../../footer.php'); ?>
</html>