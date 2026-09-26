<?php include_once(__DIR__ . '/csrf.php'); ?>
<!-- Navigation -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

<script src="js/fuzzy-logo.js"></script>

<style>
    /* Pour le logo du site sur les petits écrans */
    body{
    font-family: 'Nunito', Arial, sans-serif;
    }
  
    #fuzzy-logo-large {
      display: block;
      max-width: 300px;
      height: auto;
    }

    #fuzzy-logo-small {
      display: block;
      max-width: 200px;
      height: auto;
    }

    .nav-wrapper{
        padding: 0px 20px;
    }

    /* Ajustements pour le sidenav */
    .sidenav {
        background-color:#192d3bee;
        font-size: 1.5rem
    }
    .sidenav li > a {
        color: #fff; /* Couleur du texte */
        padding: 0 32px; /* Ajustement du padding pour l'alignement */
    }
    .sidenav li > a > i {
        margin: 0px; /* Ajustement du padding pour l'alignement */
    }

    h2{
     font-family: 'Tiny5', Arial, sans-serif;
   }
    h3{
     font-family: 'Tiny5', Arial, sans-serif;
   }
   h5{
     font-family: 'Tiny5', Arial, sans-serif;
   }
 
   li{
      font-family: 'Tiny5', Arial, sans-serif;
      padding:10px;
   }
   
       
</style>

<!-- Navigation -->
<nav class="blue-grey darken-4">
<!--     <div class="nav-wrapper container">
        <a href="/index.php" class="brand-logo hide-on-med-and-down">
 
            
        </a>

        <a href="#" data-target="mobile-nav" class="sidenav-trigger" style="margin:5px;"><i class="fa fa-bars white-text fa-lg"></i></a>
        <a href="#" data-target="mobile-nav" class="sidenav-trigger brand-logo show-on-med-and-down hide-on-med-and-up" style="margin-top:24px; padding-left: 20px;">

            <span id="logo-fallback2" style="font-family:'Tiny5',;fontSize:'1.8rem';monospace;color:#fff;margin:0px;">Invader Mapper</span>
            <canvas id="fuzzy-logo-small" style="display:none;"></canvas>
        </a>
 -->

<div class="nav-wrapper" style="display: flex; align-items: center; justify-content: space-between;">



    <div class="hide-on-med-and-down">
        <a href="/index.php" style="max-width: 260px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
        <canvas id="fuzzy-logo-large" style="padding-left: 40px;"></canvas>
        </a>
    </div>

    <div class="hide-on-large" style="display: flex; justify-content: center; align-items: center;">
      <a href="#" data-target="mobile-nav" class="sidenav-trigger" style="margin:5px;"><i class="fa fa-bars white-text fa-lg"></i></a>

      <a href="#" data-target="mobile-nav" class="sidenav-trigger" style="max-width: 260px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
        <canvas id="fuzzy-logo-small"></canvas>
      </a>
    </div>


    <ul class="right hide-on-med-and-down" style="display: flex; gap: 10px; padding-right: 40px;">
      <li><a href="/index.php">Carte</a></li>
      <li><a href="/stats2.php">Statistiques</a></li>
      <li><a href="/modules/badges/badges.php">Mes badges</a></li>
      <li><a href="/liste_position.php">Travaux</a></li>
      <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
      <li><a href="/modules/user_images/moderate.php">Modération</a></li>
      <?php endif; ?>
      <li><a href="/settings.php">Paramètres</a></li>
      <li><a href="/logout.php">Se déconnecter</a></li>
    </ul>
  </div>

   <!--  </div> -->
</nav>

<script src="js/fuzzy-text.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Logo pour grand écran
    createFuzzyText('fuzzy-logo-large', 'Invader Mapper', {
        fontSize: '3rem',
        fontWeight: 900,
        fontFamily: "'Tiny5', monospace",
        color: '#fff',
        baseIntensity: 0.1,
        hoverIntensity: 0.2,
        enableHover: true
    });

    // Logo pour petit écran
    createFuzzyText('fuzzy-logo-small', 'Invader Mapper', {
        fontSize: '1.8rem',
        fontWeight: 900,
        numFrames: 25,
        fontFamily: "'Tiny5', monospace",
        color: '#fff',
        baseIntensity: 0.05,
        hoverIntensity: 0.1,
        enableHover: true
    });
});
</script>

<!-- Sidenav pour mobile -->
<ul class="sidenav" id="mobile-nav">
    <br>
    <li><a href="/index.php" class="brand-logo show-on-med-and-down hide-on-large"><h5>Invader Mapper</h5></a></li>
            <li></li>
    <li><a href="/index.php"><i class="fa fa-compass white-text"></i>Carte</a></li>
    <li><a href="/stats2.php"><i class="fa fa-calculator white-text"></i>Statistiques</a></li>
    <li><a href="/modules/badges/badges.php"><i class="fa fa-trophy white-text"></i>Mes badges</a></li>
    <li><a href="/liste_position.php"><i class="fa fa-map white-text"></i>Travaux</a></li>
    <br>
    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
    <li><a href="/modules/user_images/moderate.php"><i class="fa fa-shield white-text"></i>Modération</a></li>
    <?php endif; ?>
    <li><a href="/settings.php"><i class="fa fa-sliders white-text"></i>Paramètres</a></li>
    <li><a href="/logout.php"><i class="fa fa-user white-text"></i>Se déconnecter</a></li>
</ul>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('.sidenav');
        var instances = M.Sidenav.init(elems);
    });
</script>