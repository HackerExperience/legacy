<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Premium.class.php';

$session = new Session();
$system = new System();
$premium = new Premium();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){
    $premium->handlePost();
}

require 'template/contentStart.php';

if(isset($_GET['my'])){
    $my = 'active';
    $pricing = '';
} else {
    $my = '';
    $pricing = 'active';
}

?>

    <div class="span12">
        
<?php
if($session->issetMsg()){
    $session->returnMsg();
}

if($system->issetGet('plan')){
    $player = new Player();
    if($player->isPremium()){
        ?>
        <div class="alert center">
            <?php echo sprintf(_('%sAttention:%s You already are a premium member. Buying again will extend your premium membership duration.'), '<strong>', '</strong>'); ?>
        </div>
        <?php
    }
}

?>
        
        <div class="widget-box">
            <div class="widget-title">
                <ul class="nav nav-tabs">                                  
                    <li class="link <?php echo $pricing; ?>"><a href="premium.php"><span class="icon-tab he16-premium_buy"></span><?php echo _('Buy premium'); ?></a></li>
                    <li class="link <?php echo $my; ?>"><a href="?my"><span class="icon-tab he16-mypremium"></span><?php echo _('My premium'); ?></a></li>
                    <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                </ul>
            </div>
            <div class="widget-content padding noborder">
                
<?php

if(isset($_GET['my'])){

    $premium->show_myPremium();
    
} elseif($system->issetGet('plan')){
    
    $plan = $_GET['plan'];
    
    if(!$system->validate($plan, 'pricing_plan')){
        $system->handleError('Invalid plan.', 'premium.php');
    }
        
    if(!$premium->valid_plan($plan)){
        $system->handleError('Invalid plan.', 'premium.php');
    }
    
    $premium->getPlanInfo($plan);
    
    $premium->show_payment();
        
} else {
    $premium->show_main();
}

?>
                                


                
            </div>
            <div class="nav nav-tabs" style="clear: both;"></div>
<?php

require 'template/contentEnd.php';

?>