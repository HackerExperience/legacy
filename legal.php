<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Ranking.class.php';

$session = new Session();


$system = new System();

$ranking = new Ranking();

require 'template/contentStart.php';

$all = ' class="active"';
$cur = '';



$show = 'tos';
$tos = ' active';
$pp = '';
if($system->issetGet('show')){
    $showInfo = $system->switchGet('show', 'tos', 'privacy', 'forum');

    if($showInfo['ISSET_GET'] == 1){
        switch($showInfo['GET_VALUE']){
            case 'tos':
                $show = 'tos';
                break;
            case 'privacy':
                $show = 'pp';
                $tos = '';
                $pp = ' active';
                break;
        }
    }

}

?>

    <div class="span12">
        <div class="alert alert-error">
            <button class="close" data-dismiss="alert">×</button>
            <?php echo _('<strong>Attention!</strong> By playing Hacker Experience you consent with the following terms, so read them carefully!'); ?>
        </div>
        <div class="widget-box ">
            <div class="widget-title">
                <ul class="nav nav-tabs">                                  
                    <li class="link <?php echo $tos; ?>"><a href="legal.php"><span class="icon-tab he16-tos"></span><?php echo _('Terms of service'); ?></a></li>
                    <li class="link<?php echo $pp; ?>"><a href="?show=privacy"><span class="icon-tab he16-privacy"></span><?php echo _('Privacy Policy'); ?></a></li>
                    <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                </ul>
            </div>
            <div class="widget-content padding noborder">

<?php


if($show == 'tos'){
    $source = 'TOS.php';
} else {
    $source = 'privacy.php';
}

    ?>
                <iframe id="legalframe" src="<?php echo $source; ?>" width="100%" seamless="1"></iframe> 
    <?php

    
    
?>
                
                <center><a class="btn btn-danger" target="__blank" href="<?php echo $source; ?>"><?php echo _('Open in a new tab'); ?></a></center>
                
        </div>
        <div class="nav nav-tabs" style="clear: both;"></div>
<?php

require 'template/contentEnd.php';

?>
