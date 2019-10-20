<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Ranking.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$system = new System();

$ranking = new Ranking();



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
                                <li class="link<?php echo $user; ?>"><a href="ranking.php"><span class="icon-tab he16-rank_user"></span><span class="hide-phone"><?php echo _('User ranking'); ?></span></a></li>
                                <li class="link<?php echo $clan; ?>"><a href="?show=clan"><span class="icon-tab he16-rank_clan"></span><span class="hide-phone"><?php echo _('Clan ranking'); ?></span></a></li>
                                <li class="link<?php echo $soft; ?>"><a href="?show=software"><span class="icon-tab he16-rank_software"></span><span class="hide-phone"><?php echo _('Software ranking'); ?></span></a></li>
                                <li class="link<?php echo $ddos; ?>"><a href="?show=ddos"><span class="icon-tab he16-rank_ddos"></span><span class="hide-phone"><?php echo _('DDoS Ranking'); ?></span></a></li>
                            </ul>
                        </div>
                        <div class="widget-content padding noborder center">
<?php

if($system->issetGet('show')){

    $showInfo = $system->switchGet('show', 'clan', 'software', 'ddos');

    if($showInfo['ISSET_GET'] == 1){

        switch($showInfo['GET_VALUE']){

            case 'clan':

                $display = 'clan';

                break;
            case 'software':

                $display = 'software';

                break;
            case 'ddos':

                $display = 'ddos';

                break;

        }

    } else {
        $display = 'user';
    }

} else {

    $display = 'user';

}        

$ranking->ranking_display($display);

?>
    </div>
<div style="clear: both;" class="nav nav-tabs"></div>

<?php

require 'template/contentEnd.php';

?>