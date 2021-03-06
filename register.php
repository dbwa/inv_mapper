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
<div class="site-wrap">

<div class="main-wrap " id="section-home">
<div class="cover_1 overlay bg-light">
<div class="img_bg" style="background-image: url(https://images.pexels.com/photos/2603464/pexels-photo-2603464.jpeg?auto=compress&cs=tinysrgb&dpr=3&h=750&w=1260); background-position: 50% -25px;" data-stellar-background-ratio="0.5">
<div id="vertical-center">
<div class="row align-items-center justify-content-center text-center vertical-center">


        <div id="box">
        <h3>Creer un compte</h3>
            <form action="" method="post">
                <label>Login</label>
                <input type="text" name="username" class="input" autocomplete="off" id="username" value="<?php echo $_GET['user']; ?>"/>
                <label>Mot de passe </label>
                <input type="password" name="password" class="input" autocomplete="off" id="password"/><br/>
                <label>Repeter le mot de passe </label>
                <input type="password" name="password2" class="input" autocomplete="off" id="password2"/><br/>
                <input type="submit" class="button button-primary button-orange" value="Se connecter" id="login"/>
                <span class='msg'></span>
                <div id="error">

                </div>

        </div>
        </form>
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
<p>Invaders mapper est un moyen simple de localiser et gerer les invaders pour l'application flashInvaders </p>
<p><a href="https://play.google.com/store/apps/details?id=com.ltu.flashInvader&hl=fr" class="btn btn-outline-orange">Télécharger l'application</a></p>

</div>
</div>


<div class="col-md-6">
<div class="footer-widget">
<h3 class="mb-4">Suivre le projet </h3>

<p><a href="https://github.com/dbwa/inv_mapper"><span class="fa fa-github"></span><small> inv_mapper</small></a></p>

</div>

</div>
</div>
<div class="row fin">
<div class="col-md-12 text-center">
<p>

v0.3.1 <script>document.write(new Date().getFullYear());</script> Invaders Mapper

</p>
</div>
</div>
</div>
</footer>
</div>

</div>

</body>

</html>
