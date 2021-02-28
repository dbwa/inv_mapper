<?php
include_once(__DIR__ . '/config.php');
session_start();
if (!empty($_SESSION['login_user'])) { //la session est bonne on redirige vers page membre
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once("fonctions.inc.php"); ?>
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

    <!-- login -->
    <script>
        $(document).ready(function () {

            $('#login').click(function () {
                var username = $("#username").val();
                var password = CryptoJS.SHA1($("#password").val()).toString();  /*on hash le password une premiere fois ici*/
                var dataString = 'username=' + username + '&password=' + password;
                if ($.trim(username).length > 0 && $.trim(password).length > 0) {


                    $.ajax({
                        type: "POST",
                        url: "ajaxLogin.php",
                        data: dataString,
                        cache: false,
                        beforeSend: function () {
                            $("#login").val('Connection...');
                        },
                        success: function (data) {
                            if (data) {
                                window.location.href = "index.php";
                            }
                            else {
                                $('#box').shake();
                                $("#login").val('Se connecter');
                                $("#error").html("<span style='color:#cc0000'>Erreur:</span> Login/Mot de passe incorrect. ");
                            }
                        }
                    });

                }
                return false;
            });


        });
    </script>


</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">


<!-- cartos Section -->
<section id="page_login" class="container cartos-section">
    <br>
        <div id="box">
            <h3>Invader mapper</h3>

        <?php
                if ($_GET['register'] == "success"){ //pour tout le monde                    
                   echo '
            <div class="alert alert-success" role="alert">Compte créé. Veuillez vous connecter.
            </div>
        '; } ?>

            <br>
            <form action="" method="post">
                <label>Login</label>
                <input type="text" name="username" class="input" autocomplete="off" id="username"/>
                <label>Password </label>
                <input type="password" name="password" class="input" autocomplete="off" id="password"/><br/>
                <input type="submit" class="button button-primary" value="Se connecter" id="login"/>
                <span class='msg'></span>

                <div id="error">

                </div>

        </div>
        </form>
    </div>


</section>



<!-- Footer -->
<footer>
    <div class="container text-center">
        <p><small> v0.1 2021 </small></p>
    </div>
</footer>


</body>

</html>
