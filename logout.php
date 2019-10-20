<?php

require '/var/www/classes/Session.class.php';
require '/var/www/classes/Ranking.class.php';
require '/var/www/classes/Forum.class.php';

$session  = new Session();
$ranking = new Ranking();
$forum = new Forum();

$ranking->updateTimePlayed();

$forum->logout();


$session->logout();



if($session->issetFBLogin()){
    
    require_once '/var/www/classes/Facebook.class.php';

    $facebook = new Facebook(array(
        'appId' => 'REDACTED',
        'secret' => 'REDACTED'
    ));

    $facebook->destroySession();
    
}

header("Location:index.php");
exit();