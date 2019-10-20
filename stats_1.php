<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Internet.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Pagination.class.php';

$session = new Session();

if($session->issetLogin()){

    $player = new Player($_SESSION['id']);
    $system = new System();
    $pagination = new Pagination();
    
    require 'template/gameHeader.php';
    ?>

    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php echo $gameTitle; ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" /> 
    <link href="css/stats.css" rel="stylesheet" type="text/css" />
</head>
    
        
    <body style="background-color: #F5F5F5;">
        <br/>
    <?php 
        
    if($session->issetMsg()){
        $session->returnMsg();
    }

    if($system->issetGet('round')){
        
        $page = 'round='.$_GET['round'].'&page';
        
    } else {
        $page = 'page';
    }
    
    $pagination->paginate('', 'round', 100, $page, 1);
    $pagination->showPages(100, $page);    
    

    ?>

    </body>
</html>
        
    <?php
    
} else {
    header("Location:index.php");
}

?>