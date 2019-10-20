<?php

$l = 'en_US';

if(isset($_SERVER['HTTP_HOST'])){
    if($_SERVER['HTTP_HOST'] == 'br.hackerexperience.com' || $_SERVER['HTTP_HOST'] == 'www.br.hackerexperience.com'){
        $l = 'pt_BR';
    }
}

putenv("LANG=" . $l);
setlocale(LC_ALL, $l);

$domain = "messages";
bindtextdomain($domain, "locale");
bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);

if(!isset($_SESSION['SPECIAL_ID'])){
    session_destroy();
    header("Location:../index");
    exit();
} elseif($_SESSION['SPECIAL_ID'] != 'tt'){
    session_destroy();
    header("Location:../index");
    exit();
}

if(!isset($_SESSION['twitter_data'])){
    session_destroy();
    header("Location:../index");
    exit();
}



$userInfo = $_SESSION['twitter_data'];

$fullname = $userInfo->name;
$username = $userInfo->screen_name;

$names = explode(" ", $fullname);

$firstname = $names[0];
$lastname = $names[sizeof($names) - 1];

$userID = $userInfo->id;

$error = FALSE;

if(isset($_POST['ttuser']) || isset($_POST['predefined'])){

    if(isset($_POST['predefined'])){
                
        switch($_POST['predefined']){
            
            case 'fname':
                $name = $firstname;
                break;
            case 'lname':
                $name = $lastname;
                break;
            case 'uname':
                $name = $username;
                break;
            default:
                $error = TRUE;
                $errorMsg = 'Invalid get. Please, try again.';
                break;
            
        }
        
    } else {
        $name = $_POST['ttuser'];
    }
    
    $system = new System();
    
    if(!$system->validate($name, 'username')){
        $errorMsg = sprintf(_('The name %s contains invalid characters. Allowed are %s.'), $name, '<strong>azAZ09._-</strong>');
    }
    
    if(strlen($name) < 3 || strlen($name) > 15){
        $error = TRUE;
        if(strlen($name) < 4){
            $errorMsg = sprintf(_('The name %s is too small.<br/>Please insert 3 characters at least.'), $name);
        } else {
            $errorMsg = sprintf(_('The name %s is too big.<br/>Please insert 15 characters at most.'), $name);
        }
    }
    
    if(!$error){
    
        $pdo = PDO_DB::factory();
        
        $sql = 'SELECT COUNT(*) AS total FROM users WHERE login = \''.$name.'\' LIMIT 1';
        $total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total == 1){
            $error = TRUE;
            $errorMsg = sprintf(_('<strong>Oh no!</strong> The user %s is already in use.'), $name);
        } else {
            $error = FALSE;
        }
        
        if(!$error){

            require '/var/www/classes/Python.class.php';
            $python = new Python();

            $gameIP1 = rand(0, 255);
            $gameIP2 = rand(0, 255);
            $gameIP3 = rand(0, 255);
            $gameIP4 = rand(0, 255);

            $gameIP = $gameIP1 . '.' . $gameIP2 . '.' . $gameIP3 . '.' . $gameIP4;    

            $python->createUser($name, 0, 0, $gameIP, $userID, 'twitter');
            
            require '/var/www/classes/Forum.class.php';
            $forum = new Forum();
            
            $sql = 'SELECT COUNT(*) AS total, id FROM users WHERE login = \''.$name.'\' LIMIT 1';
            $regInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

            if($regInfo->total == 1){

                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();

                $finances->createAccount($regInfo->id);
                
                $forum->externalRegister($name, 'special_tt', 'twitter_login', $regInfo->id);

                unset($_SESSION['SPECIAL_ID']);
                $_SESSION['TTLOGIN'] = TRUE;

                header("Location:index");
                exit();

            } else {

                $error = TRUE;
                $errorMsg = 'Looks like there was some weird error :(. We are looking into it. Please, try again.';

            }
        
        }
    
    }
    
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
        <style>

        .intro-header {
            padding: 0;
        }

        .errormsg {
            color: red;
            font-size: 18px;
        }

        .btn-front {
            width: auto;
            min-width: 150px;
        }

        .network-name {
            text-transform: none;
        }

        .userinput {
            width: 50%;
            text-align: center;
            display: inline-block;
        }

        #sendbtn {
            display: none;
        }

        .backmsg {
            margin-top: 80px;
            font-size: 15px;
            cursor: pointer;
        }

        </style>
    </head>
    <body>
        <div class="intro-header">

            <div class="container">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="intro-message">
<?php
if($error){
?>
                            <span class="errormsg"><?php echo $errorMsg; ?></span>
<?php
}
?>
                            <h1><?php echo $fullname; ?>,</h1>
                            <h3 class="digital"><?php echo _('How would you like to be called in-game?'); ?><span class="a_bebida_que_pisca">_</span></h3>
                            <hr class="intro-divider">
                            <form id="predefined" action="" method="POST">
                                <ul class="list-inline intro-social-buttons">
                                    <li name="d"><a class="btn btn-default btn-lg btn-front btn-firstname" name="abc"><span class="network-name" name="asdf"><?php echo $firstname; ?></span></a></li>
                                    <li><a class="btn btn-default btn-lg btn-front btn-lastname"><span class="network-name"><?php echo $lastname; ?></span></a></li>
                                    <li><a class="btn btn-default btn-lg btn-front btn-username"><span class="network-name"><?php echo $username; ?></span></a></li>
                                </ul>
                            </form>
                            <h3><?php echo _('or'); ?></h3>
                            <form action="" method="POST">
                                <input type="text" name="ttuser" class="form-control input-md userinput" placeholder="<?php echo _('Enter your username'); ?>"><br/><br/>
                                <input type="submit" value="<?php echo _('Send'); ?>" id="sendbtn" class="btn btn-default btn-success"></input>
                            </form>
                            <div class="backmsg">
                                <span id="backindex"><?php echo _('Back to Index'); ?></span>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
        <script src="js/jquery.min.js"></script>
        <script src="js/he_social.js"></script>
    </body>
</html>