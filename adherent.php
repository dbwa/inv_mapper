<?php
include_once(__DIR__ . '/config.php');
include_once("fonctions.inc.php");
session_start();

// Vérifier d'abord le cookie de connexion
restore_session_from_cookie();

if (!empty($_SESSION['login_user'])) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    $auth_result = authentificate($username, $password, $remember);
    if ($auth_result && is_array($auth_result)) {
        list($count, $user) = $auth_result;
        $_SESSION['login_user'] = $user['login'];
        $_SESSION['login_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        header("location: index.php");
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Carte d'invasion : Connexion</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/grayscale.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
          type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet"
          type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">


    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- pour hash le password -->
    <script src="js/CryptoJS.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="js/jquery.easing.min.js"></script>
    <script src="js/jquery.ui.shake.js"></script>

   <style type="text/css">
        
        /*disposition*/
        .vertical-center{margin: 0; position: absolute; top: 50%; left: 50%; -ms-transform: translate(-50%, -50%); transform: translate(-50%, -50%)}
        p{font-size: medium}

        .fin{margin-top: 62px}

        #box{border:2px solid #FFA518; background:#000; color:#fff; text-decoration:none; opacity:90%; border-radius:4px;}
        #box h3{color:#FFA518;}
        #box label{color:#FFA518;}
        .input{width: 100%;  background:#555; border-radius:4px;}

        .button-orange{border:2px solid #FFA518;background:#FFA518; color:#000; text-decoration:none; opacity:100%; border-radius:4px;}

        /*grande image*/
        .cover_1 .img_bg{background-repeat:no-repeat;background-size:cover!important;background-position:center center}
        .cover_1 .img_bg,.cover_1 {min-height:600px;height:100vh} 
        .cover_1 .heading{color:#fff;font-weight:300;font-size:30px;line-height:1.5} 

        /*boutons*/
        .btn.btn-primary.btn-outline-primary{border-width:2px;cursor:pointer}
        .btn.btn-outline-white{border:2px solid #fff;background:none;color:#fff;text-decoration:none}
        .btn.btn-outline-white:hover{background:#FFA518;color:#000;border:2px solid transparent}

        .btn-outline-orange{border:2px solid #FFA518;background:none;color:#fff;text-decoration:none}
        .btn-outline-orange:hover{background:#FFA518;color:#000;border:2px solid transparent}

        /*footer*/
        .ftco-footer{background:#121212;padding:7em 0;font-size:15px;font-weight:400}
        .ftco-footer .footer-widget h3{font-size:20px;color:#FFA518}
        .ftco-footer .btn {font-size:20px;color:#ffe2e6;font-size: small}
        .footer-widget{padding: 0px 25px 25px;}
    </style>

    <!-- login -->
    <script>
        $(document).ready(function () {
            $('form.form-signin').on('submit', function (e) {
                e.preventDefault();
                var username = $("#username").val();
                var password = CryptoJS.SHA1($("#password").val()).toString();
                var remember = $("#remember").prop('checked');
                
                console.log("Tentative de connexion pour:", username);
                console.log("Remember me:", remember);
                
                if ($.trim(username).length > 0 && $.trim(password).length > 0) {
                    var data = {
                        username: username,
                        password: password,
                        remember: remember
                    };
                    
                    console.log("Envoi des données:", data);
                    
                    $.ajax({
                        type: "POST",
                        url: "ajaxLogin.php",
                        contentType: "application/json",
                        data: JSON.stringify(data),
                        beforeSend: function () {
                            $("#login").prop('disabled', true).text('Connection...');
                            $("#error").html("");
                        },
                        success: function (response) {
                            console.log("Réponse reçue:", response);
                            try {
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                if (response.success) {
                                    window.location.href = "index.php";
                                } else {
                                    $('#box').shake();
                                    $("#error").html("<span style='color:#ff0000'><b>Erreur:</b></span> " + (response.message || "Erreur inconnue"));
                                }
                            } catch (e) {
                                console.error("Erreur parsing JSON:", e);
                                $('#box').shake();
                                $("#error").html("<span style='color:#ff0000'><b>Erreur:</b></span> Réponse invalide du serveur");
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Erreur AJAX:", status, error);
                            console.log("Réponse:", xhr.responseText);
                            $('#box').shake();
                            $("#error").html("<span style='color:#ff0000'><b>Erreur:</b></span> Erreur de connexion au serveur");
                        },
                        complete: function() {
                            $("#login").prop('disabled', false).text('Se connecter');
                        }
                    });
                } else {
                    $("#error").html("<span style='color:#ff0000'><b>Erreur:</b></span> Veuillez remplir tous les champs");
                }
            });
        });
    </script>



</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">


<!-- cartos Section -->










<div class="site-wrap">

<div class="main-wrap " id="section-home">
<div class="cover_1 overlay bg-light">
<div class="img_bg" style="background-image: url(https://images.pexels.com/photos/2603464/pexels-photo-2603464.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260); background-position: 50% -25px;" data-stellar-background-ratio="0.5">
<div id="vertical-center">
<div class="row align-items-center justify-content-center text-center vertical-center">


        <div id="box">
            <h3>Invader mapper</h3>

        <?php
                if ($_GET['register'] == "success"){ //pour tout le monde                    
                   echo '
            <div class="alert alert-success" role="alert">Compte créé. Veuillez vous connecter.
            </div>
        '; } ?>

                <?php if (isset($_GET['compte_supprime'])) { ?>
            <div class="alert alert-success" role="alert">
                <strong>Votre compte a été supprimé.</strong><br>
                Il est désormais inaccessible et vous avez été déconnecté.
                Vos données seront définitivement effacées sous 30 jours.
                D'ici là, ce nom d'utilisateur reste réservé et ne peut pas être réutilisé
                pour créer un nouveau compte.
            </div>
                <?php } ?>

            <br>
            <form class="form-signin" method="post">
                <h2 class="form-signin-heading">Connexion</h2>
                <?php if(isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
                <input style="margin-bottom: 10px;" type="text" id="username" class="form-control" name="username" placeholder="Nom d'utilisateur" required autofocus>
                <input style="margin-bottom: 10px;" type="password" id="password" class="form-control" name="password" placeholder="Mot de passe" required>
                <div class="checkbox mb-3">
                    <label>
                        <input type="checkbox" id="remember" name="remember" value="1"> Se souvenir de moi
                    </label>
                </div>
                <button class="btn btn-lg btn-primary btn-block" id="login" type="submit">Se connecter</button>
                <div id="error"></div>
            </form>

            <p class="text-center" style="margin-top: 20px;">
                <a href="register.php" class="btn btn-outline-white" style="opacity:0.7;">
                    <small>Créer un compte</small>
                </a>
            </p>
        </div>
    </div>




</div>
</div>
</div>
</div> 




<footer class="ftco-footer">
<div class="container">
<div class="row">
<div class="col-md-6 mb-6">

<div class="footer-widget">

<h3 class="mb-4">A propos</h3>
<p>Invader mapper est un moyen simple de localiser et gerer les invaders pour l'application flashInvaders </p>
<br>
<p><a href="https://play.google.com/store/apps/details?id=com.ltu.flashInvader&hl=fr" class="btn btn-outline-orange">Télécharger l'application</a></p>

</div>
</div>


<div class="col-md-6">
<div class="footer-widget">
<h3 class="mb-4">Suivre le projet </h3>

<p><a href="https://github.com/dbwa/inv_mapper"><span class="fa fa-github"></span> inv_mapper</a></p>
<br>
<script type='text/javascript' src='https://storage.ko-fi.com/cdn/widget/Widget_2.js'></script><script type='text/javascript'>kofiwidget2.init('Support Me on Ko-fi', '#eb750e', 'I3I6KLTIW');kofiwidget2.draw();</script> 

</div>

</div>
</div>
<div class="row fin">
<div class="col-md-12 text-center">
<p>

v0.3.1 <script>document.write(new Date().getFullYear());</script> Invader Mapper

</p>
</div>
</div>
</div>
</footer>
</div>

</div>



</body>

</html>
