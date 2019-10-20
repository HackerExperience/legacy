<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Fame.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$fame = new Fame();

$user = ' active';
$clan = '';
$soft = '';
$ddos = '';

if($system->issetGet('show')){

    $showInfo = $system->switchGet('show', 'clan', 'software', 'ddos');

    if($showInfo['ISSET_GET'] == 1){

        $user = '';

        if($showInfo['GET_VALUE'] == 'clan'){
            $clan = ' active';
        } elseif($showInfo['GET_VALUE'] == 'software') {
            $soft = ' active';
        } else {
            $ddos = ' active';
        }

    } else {
        $session->addMsg('Invalid get.', 'error');
    }

}



?>
                <div class="span12">
<?php
if($session->issetMsg()){
    $session->returnMsg();
}
?>
                    <div class="widget-box">
                        <div class="widget-title">
                            <ul class="nav nav-tabs">
                                <li class="link<?php echo $user; ?>"><a href="fame"><span class="icon-tab he16-fame_user"></span>Famous Users</a></li>
                                <li class="link<?php echo $clan; ?>"><a href="?show=clan"><span class="icon-tab he16-fame_clan"></span>Famous Clans</a></li>
                                <li class="link<?php echo $soft; ?>"><a href="?show=software"><span class="icon-tab he16-fame_software"></span>Famous Softwares</a></li>
                                <li class="link<?php echo $ddos; ?>"><a href="?show=ddos"><span class="icon-tab he16-fame_ddos"></span>Famous DDoS</a></li>
                            </ul>
                        </div>
                        <div class="widget-content padding noborder center">
<?php

$display  = 'user';
$round = '';
$top = FALSE;

if($system->issetGet('show')){

    $showInfo = $system->switchGet('show', 'clan', 'software', 'ddos');

    if($showInfo['ISSET_GET'] == 1){
        $display = $showInfo['GET_VALUE'];
    }
    
}    

if($system->issetGet('round')){
    
    $roundInfo = $system->verifyNumericGet('round');
    
    if($roundInfo['IS_NUMERIC'] == 1 && $roundInfo['GET_VALUE'] > 0){
        $round = $roundInfo['GET_VALUE'];
    } elseif($_GET['round'] == 'all'){
        $top = TRUE;
    }
    
}

$fame->HallOfFame($display, $round, $top);

?>
            </div>
            <div class="nav nav-tabs" style="clear: both;"></div>           
<?php

require 'template/contentEnd.php';

?>