<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/PC.class.php';
require '/var/www/classes/Finances.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$hardware = new HardwareVPC($_SESSION['id']);
$hardware->getHardwareInfo('', 'VPC');


$myHardware = 'active';
$myPC = '';
$upgradePC = '';
$buyPC = '';
$internet = '';
$xhd = '';


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

    $hardware->handlePost();

}    

unset($_SESSION['XHD_ID']);
unset($_SESSION['CUR_PC']);

if($system->issetGet('opt')){

    $optInfo = $system->switchGet('opt', 'upgrade', 'buy', 'internet', 'xhd');

    if($optInfo['ISSET_GET'] == 1){

        $myHardware = '';

        switch($optInfo['GET_VALUE']){

            case 'upgrade':
                $upgradePC = 'active';
                break;
            case 'buy':
                $buyPC = 'active';
                break;
            case 'internet':
                $internet = 'active';
                break;
            case 'xhd':
                $xhd = 'active';
                break;

        }

    }

}

if($myHardware == 'active'){
    $style = 'style="text-align: center;"';
} else {
    $style = '';
}

?>

<div class="span12" <?php echo $style; ?>>

    <?php

    if($session->issetMsg()){
        $session->returnMsg();
    }    

    ?>        

    <div class="widget-box">

        <div class="widget-title">
            <ul class="nav nav-tabs">
                <li class="link <?php echo $myHardware; ?>"><a href="hardware"><span class="icon-tab he16-servers"></span><span class="hide-phone"><?php echo _("My hardware"); ?></span></a></li>
                <li class="hide-phone link <?php echo $upgradePC; ?>"><a href="?opt=upgrade"><span class="icon-tab he16-upgradeserver"></span><?php echo _("Upgrade server"); ?></a></li>
                <li class="link <?php echo $buyPC; ?>"><a href="?opt=buy"><span class="icon-tab he16-buyserver"></span><span class="hide-phone"><?php echo _("Buy new server"); ?></span></a></li>
                <li class="link <?php echo $internet; ?>"><a href="?opt=internet"><span class="he16-net icon-tab"></span><span class="hide-phone"><?php echo _("Internet"); ?></a></a></li>
                <li class="link <?php echo $xhd; ?>"><a href="?opt=xhd"><span class="icon-tab he16-xhd"></span><span class="hide-phone"><?php echo _("External HD"); ?></span></a></li>
                <a href="<?php echo $session->help('hardware'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
            </ul>
        </div>

        <div class="widget-content padding noborder">

<?php

if($system->issetGet('opt')){

    $optInfo = $system->switchGet('opt', 'upgrade', 'buy', 'internet', 'xhd');

    if($optInfo['ISSET_GET'] == '1'){

        switch($optInfo['GET_VALUE']){

            case 'upgrade':

                if($system->issetGet('id')){

                    $idInfo = $system->verifyNumericGet('id');

                    if($idInfo['IS_NUMERIC'] == '1'){

                        $pcInfo = $hardware->getPCSpec($idInfo['GET_VALUE'], 'VPC', '');

                        if($pcInfo['ISSET'] == '1'){

                            $_SESSION['CUR_PC'] = $idInfo['GET_VALUE']; 

                            $hardware->store_showPage($pcInfo, 'CPU');

                        } else {
                            $system->handleError('This server does not exists.', 'hardware?opt=upgrade');
                        }

                    } else {
                        $system->handleError('Invalid server ID.', 'hardware?opt=upgrade');
                    }

                } else {

                    $hardware->listPCs($_SESSION['id'], 'upgrade');

                }

                break;
            case 'buy':

                $hardware->store_showPage('', 'BUY_PC');                        

                break;

            case 'internet':    

                $hardware->store_showPage('', 'NET');

                break;
            case 'xhd':

                if($system->issetGet('id')){
                    $idInfo = $system->verifyNumericGet('id');

                    if($idInfo['IS_NUMERIC'] == 1){
                        $_SESSION['XHD_ID'] = $idInfo['GET_VALUE'];
                        $hardware->store_showPage('', 'XHD-UPG');
                    } else {
                        $hardware->store_showPage('', 'XHD-BUY');
                    }

                } else {

                    $hardware->store_showPage('', 'XHD-BUY');

                }

                break;

        }


    } else {
        header("Location:hardware");
        exit();
    }

} else {

        if(isset($_SESSION['RECENT_BUY'])){
           unset($_SESSION['RECENT_BUY']); 
        }

        $hardware->showPCTotal($_SESSION['id']);

    }

    ?>

        </div>
    <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>

    <?php

    require 'template/contentEnd.php';

?>