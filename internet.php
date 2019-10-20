<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Internet.class.php';
require '/var/www/classes/System.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$player = new Player($_SESSION['id']);
$internet = new Internet();
$ranking = new Ranking();

if(!$ranking->cert_have(2)){
    $session->addMsg(sprintf(_("You need the certification %s to enable this page."), '<strong>'._('Hacking 101').'</strong>'), 'error');
    header("Location:university?opt=certification");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

    $internet->handlePost();

}

if($system->issetGet('ip')){

    $getIP = trim($_GET['ip']);
    
    if(!$system->validate($getIP, 'ip')){
        exit("Invalid IP");
    }

    $internet->navigate(ip2long($getIP));

} else {

    if($session->isInternetLogged()){

        $internet->navigate($_SESSION['LOGGED_IN']);

    } elseif($session->issetInternetSession()){ 

        $internet->navigate($_SESSION['CUR_IP']);

    } else {


        $internet->navigate($internet->home_getIP());

    }

}

require 'template/contentEnd.php';

?>