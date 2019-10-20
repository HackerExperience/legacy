<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Mission.class.php';
require '/var/www/classes/System.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$mission = new Mission();
$finances = new Finances();



if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

    $mission->handlePost();

}

$available = 'active';
$availableLink = 'missions.php';
$current = '';
$currentLink = '';
$completed = '';

if($session->issetMissionSession()){
    if($system->issetGet('id')){
        $idInfo = $system->verifyNumericGet('id');
        if($idInfo['GET_VALUE'] == $_SESSION['MISSION_ID']){
            $available = '';
            $availableLink = 'missions?view=all';
            $current = 'active';
        }
    } else {
        $available = '';
        $availableLink = 'missions?view=all';
        $current = 'active';
    }
    $currentLink = 'missions.php';
}

if($system->issetGet('view')){
    
    $viewInfo = $system->verifyStringGet('view');
    
    if($viewInfo['GET_VALUE'] == 'all'){
        $available = 'active';
        $current = '';
        $completed = '';
    } elseif($viewInfo['GET_VALUE'] == 'completed'){
        $available = '';
        $current = '';
        $completed = 'active';
    }
    
}

if($current == 'active' || $system->issetGet('id')){
    $span = 'span12';
} else {
    $span = 'span9';
}

//DONT TOUCH
if($session->issetMissionSession()){
    if($_SESSION['MISSION_TYPE'] > 49){
        if(!$system->issetGet('view')){
            $span = 'span12';
        }
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
                                    <li class="link <?php echo $available; ?>"><a href="<?php echo $availableLink; ?>"><span class="he16-missions icon-tab"></span><span class="hide-phone"><?php echo _('Available missions'); ?></span></a></li>
<?php 
if($session->issetMissionSession()){ 
?>
                                    <li class="link <?php echo $current; ?>"><a href="<?php echo $currentLink; ?>"><span class="he16-missions_current icon-tab"></span><span class="hide-phone"><?php echo _('Current mission'); ?></span></a></li>
<?php
} 
?>
                                    <li class="link <?php echo $completed; ?>"><a href="?view=completed"><span class="he16-missions_completed icon-tab"></span><span class="hide-phone"><?php echo _('Completed missions'); ?></span></a></li>
                                    <a href="<?php echo $session->help('missions'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                                </ul>
                            </div>
                            <div class="widget-content padding noborder">
                                <div class="<?php echo $span; ?>">
<?php

// 2019: User has mission
if($session->issetMissionSession() && !$system->issetGet('view') && !$system->issetGet('id')){ //tem missao

    if($mission->issetMission($_SESSION['MISSION_ID'])){
    
            $mission->showMission($_SESSION['MISSION_ID']);

    } else {
        
        $session->deleteMissionSession();

    }

// 2019: No mission, but there may be storyline mission (checking DB)
} elseif($mission->storyline_hasMission() && !$system->issetGet('view') && !$system->issetGet('id')){ //nao tem sessão de missão, mas pode ter missão de storyline (olhando no banco)

    $mission->showMission($_SESSION['MISSION_ID']);

// 2019: Definitely no mission; let me list all of them
} else { //nao ta em nenhuma misssao, vou listar todas

    if($system->issetGet('id')){

        $idInfo = $system->verifyNumericGet('id');

        if($idInfo['IS_NUMERIC'] == '1'){

            if($mission->issetMission($idInfo['GET_VALUE'])){

                if($system->issetGet('action')){

                    $actionInfo = $system->verifyStringGet('action');

                } else {

                    $mission->showMission($idInfo['GET_VALUE']);

                }

            } else {
                echo 'This mission doesnt exists anymore';
            }

        } else {
            echo 'Invalid ID';
        }

    } else {

        if($system->issetGet('view')){

            $viewInfo = $system->switchGet('view', 'all', 'completed');

            if($viewInfo['ISSET_GET'] == '1'){

                switch($viewInfo['GET_VALUE']){

                    case 'all':

                        if($session->issetMissionSession()){
                            ?>
                                <div class="alert center">
                                    <?php echo _('You are currently in a mission.'); ?>
                                </div>
                            <?php
                        }
                        
                        $mission->listMissions();

                        break;
                    case 'completed':
                        
                        $mission->listCompletedMissions();
                        
                        break;

                }

            } else {
                die("Invalid view option");
            }

        } else {
            $mission->listMissions();
        }

    }

}

            if($span == 'span12'){

?>
                                    </div>
                                </div>
<?php 
            
            } 
?>
                            </div> 
                            <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>
<?php

require 'template/contentEnd.php';

?>
