<?php
// cette page prend en entrée un code d'invitation 'invit' et le username invité 'user' associé
include_once(__DIR__ . '/config.php');
session_start();
if (!empty($_SESSION['login_user'])) { //la session est bonne on redirige vers page membre
    header('Location: index.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php include_once("fonctions.inc.php"); 
?>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>S'enregistrer</title>

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
        /*pour hasher les passwords*/
        function hashAsync(algo, str) {
          return crypto.subtle.digest(algo, new TextEncoder("utf-8").encode(str)).then(buf => {
            return Array.prototype.map.call(new Uint8Array(buf), x=>(('00'+x.toString(16)).slice(-2))).join('');
          });
        };

        $(document).ready(function () {   /*http://192.168.0.10/invaders_dev/register.php?user=test&invit=AAAAA*/

            $('#login').click(function () {
                var username = $("#username").val();
                var password = $("#password").val();
                var password2 = $("#password2").val();

                if (password2 != password) {
                    $('#box').shake();
                    $("#error").html("<span style='color:#cc0000'>Erreur:</span> Mots de passes differents. ");
                } else
                 {   
                    var hashpassword = CryptoJS.SHA1(password).toString();
                    var dataString = 'username='+ username +'&pass=<?php echo $_GET['invi']; ?>&password='+ hashpassword;
                    console.log(dataString);

                    if ($.trim(username).length > 0 && $.trim(password).length > 0) {


                        $.ajax({
                            type: "POST",
                            url: "AjaxRegister.php",
                            data: dataString,
                            cache: false,
                            beforeSend: function () {
                                $("#login").val('Connection...');
                            },
                            success: function (data) {
                                if (data) {
                                    window.location.href = "adherent.php?register=success";
                                }
                                else {
                                    $('#box').shake();
                                    $("#login").val('Se connecter');
                                    $("#error").html("<span style='color:#cc0000'>Erreur:</span> Ce login n'a pas été invité. ");
                                }
                            }
                        });

                    }
                 }
                return false;
            });


        });
    </script>


</head>

        <br>
        <div id="box">
        <h4>Creer un compte</h4>
            <form action="" method="post">
                <label>Login</label>
                <input type="text" name="username" class="input" autocomplete="off" id="username" value="<?php echo $_GET['user']; ?>"/>
                <label>Mot de passe </label>
                <input type="password" name="password" class="input" autocomplete="off" id="password"/><br/>
                <label>Repeter le mot de passe </label>
                <input type="password" name="password2" class="input" autocomplete="off" id="password2"/><br/>
                <input type="submit" class="button button-primary" value="Se connecter" id="login"/>
                <span class='msg'></span>
                <div id="error">

                </div>

        </div>
        </form>
    </div>


<!-- Footer -->
<footer>
    <div class="container text-center">
        <p><small> v0.1 2021 </small></p>
    </div>
</footer>


</body>

</html>
