<?php
require '/var/www/classes/System.class.php';
require '/var/www/classes/Session.class.php';
//require '/var/www/classes/Player.class.php';

$session = new Session();
$system = new System();

require '/var/www/classes/EmailVerification.class.php';
$emailVerification = new EmailVerification();

if($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_GET['code'])){

    if(!isset($_SESSION['id'])){
        header("Location:index.php");
        exit();
    }
    
    $verified = $emailVerification->isVerified($_SESSION['id']);
    $verified = TRUE;
    
    if($_SESSION['CERT'] >= 1){
        header("Location:index.php");
        exit();
    }

    $btnSize = 200;
    if($session->l == 'pt_BR'){
        $btnSize = 250;
    }
    
    if(!$verified){
        $btnVerified = '<li><a id="btn-verify" class="btn btn-default btn-lg btn-front" style="width: '.$btnSize.'px;"><i class="fa fa-barcode fa-fw"></i> <span class="network-name">'._('Verify email').'</span></a></li>';
        $btnTutorial = '<li><a id="btn-start" class="btn btn-default btn-lg btn-front" style="width: '.$btnSize.'px; display:none;"><i class="fa fa-power-off fa-fw"></i> <span class="network-name">'._('Start tutorial').'</span></a></li>';
    } else {
        $btnVerified = '';
        $btnTutorial = '<li><a id="btn-start" class="btn btn-default btn-lg btn-front" style="width: '.$btnSize.'px;"><i class="fa fa-power-off fa-fw"></i> <span class="network-name">'._('Start tutorial').'</span></a></li>';
    }

    ?>

    <!DOCTYPE html>
    <!--
        Hello, is it me you're looking for?
        www.renatomassaro.com
    -->
    <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="">
            <meta name="author" content="">
            <title>Hacker Experience</title>
            <link href="css/bootstrap.css" rel="stylesheet">
            <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
            <link href="css/he_index.css" rel="stylesheet">
        </head>
        <body>
            <div id="terminal"></div>
            <div class="intro-header">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                        <span id="error-msg" class="alert alert-danger" style="display:none;"></span>
                            <div class="intro-message">
                                <h1>Hacker Experience</h1>
                                <h3 class="digital"><?php echo _('The Internet under attack'); ?><span class="a_bebida_que_pisca">_</span></h3>
                                <hr class="intro-divider">
                                <ul class="list-inline intro-social-buttons">
                                    <?php if(!$verified) { ?><input id="code-input" type="text" style="width: 300px; margin-bottom: 30px; padding-left: 7px;" placeholder="<?php echo _('Verification Code'); ?>"><br/><?php } ?>
                                    <?php echo $btnVerified; ?>
                                    <?php echo $btnTutorial; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
            <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
            <script src="js/jquery.min.js"></script>
            <script src="js/welcome.js"></script>
        </body>
    <!--
        Hello! I've just got to let you know.
        www.neoartgames.com
    -->
    </html>

<?php

} else {
    
    if(isset($_GET['code'])){
        
        if(isset($_SESSION['id'])){
            header("Location:index.php");
            exit();
        }
        
        $code = $_GET['code'];
        
        if(strlen($code) == 0 || strlen($code) != 25){
            die("Please insert a 25-character code.");
        }
        
        $userID = $emailVerification->codeOnlyVerification($code);

        if($userID == 0){
            die("Ops. This code is not valid. Please verify the link on your email or <a href=\"index.php\">login</a> and enter it manually.");
        } else {
            
            require '/var/www/classes/Database.class.php';
            $database = new LRSys();
            $player = new Player();
            
            $userInfo = $player->getPlayerInfo($userID);
            
            if($database->login($userInfo->login, '', 'remember')){
                header("Location:welcome.php");
            } else {
                header("Location:index.php");
            }
            
            exit();
            
        }
        
    } else {
        
        $result = Array();
        $result['msg'] = '';
        
        $fail = FALSE;
        
        if(!isset($_POST['code'])){
            $fail = TRUE;
        }
        
        $code = $_POST['code'];
        
        if(strlen($code) == 0 || strlen($code) != 25){
            $fail = TRUE;
        }
        
        if($emailVerification->isVerified($_SESSION['id'])){
            $fail = TRUE;
        }
        
        if(!$fail){
            
            $result['status'] = 'OK';
            
            if($emailVerification->verify($_SESSION['id'], $code)){
                $result['msg'] = '';
            } else {
                $result['msg'] = _('Verification code does not match');
            }
                        
        } else {
            $result['msg'] = _('Invalid verification code');
        }
        
        header('Content-type: application/json');
        die(json_encode($result));
        
    }
    
    //$function = $_POST['verify']
    
}

?>
