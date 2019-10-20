<?php
require '/var/www/classes/System.class.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/PC.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$software = new SoftwareVPC();

$research = 'active';
$cert = '';
$center = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

    $software->handlePost('university');

}

if($system->issetGet('opt')){

    $optInfo = $system->verifyStringGet('opt');

    if($optInfo['IS_NUMERIC'] == 1){
        $system->handleError('Invalid get.', 'university.php');
    }
    
    if($optInfo['GET_VALUE'] != 'certification'){
        $system->handleError('Invalid get.', 'university.php');
    }

    $research = '';
    $cert = 'active';
    $center = ' center';

}

if($system->issetGet('learn')){
    //página da certificação
    $center = '';
    
}

?>
                    <div class="span12<?php echo $center; ?>">
<?php

if($session->issetMsg()){
    $session->returnMsg();
}

?>
                        <div class="widget-box">
                            <div class="widget-title">
                                <ul class="nav nav-tabs">
                                    <li class="link <?php echo $research; ?>"><a href="university.php"><span class="icon-tab he16-research"></span><span class="hide-phone"><?php echo _("Research softwares"); ?></span></a></li>
                                    <li class="link <?php echo $cert; ?>"><a href="university?opt=certification"><span class="icon-tab he16-certs"></span><span class="hide-phone"><?php echo _("Certifications"); ?></span></a></li>
                                    <a href="<?php echo $session->help('university', 'research'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                                </ul>
                            </div>
                            <div class="cert-complete"></div>
                            <div class="widget-content padding noborder">
<?php

if($system->issetGet('opt')){

    if($system->issetGet('learn')){

        $player = new Player();
        $ranking = new Ranking();

        $learnInfo = $system->verifyNumericGet('learn');

        if($learnInfo['IS_NUMERIC'] == 0 || $learnInfo['GET_VALUE'] < 1 || $learnInfo['GET_VALUE'] > 5){
            $system->handleError('Invalid certification.', 'university?opt=certification');
        }

        $playerLearning = $player->playerLearning();

        if($playerLearning == 0){
            $system->handleError('You are not learning any certification. Did you bought it?', 'university?opt=certification');
        }        
        
        if($playerLearning != $learnInfo['GET_VALUE']){
            $system->handleError('You are busy learning this certification.', 'university?opt=certification&learn='.$playerLearning);
        }

        if(!$ranking->cert_validate2learn($learnInfo['GET_VALUE'])){
            $system->handleError('Some error happened.', 'university?opt=certification');
        }

        if($system->issetGet('page')){

            $pageInfo = $system->verifyNumericGet('page');

            if($pageInfo['IS_NUMERIC'] == 0){
                $system->handleError('Invalid page number.', 'university?opt=certification&learn='.$learnInfo['GET_VALUE']);
            }

            if($pageInfo['GET_VALUE'] > $ranking->cert_totalPages($learnInfo['GET_VALUE']) || $pageInfo['GET_VALUE'] < 1){
                $system->handleError('Invalid page number.', 'university?opt=certification&learn='.$learnInfo['GET_VALUE']);
            }

            $page = $pageInfo['GET_VALUE'];

        } else {
            $page = 0;
        }

        $ranking->cert_showPage($learnInfo['GET_VALUE'], $page);

    } elseif($system->issetGet('complete')){

        $player = new Player();
        
        $completeInfo = $system->verifyStringGet('complete');

        $playerLearning = $player->playerLearning();

        if($playerLearning == 0){
            $system->handleError('You are not learning any certification. Did you bought it?', 'university?opt=certification');
        }  
        
        if(strlen($completeInfo['GET_VALUE']) != '32'){
            $system->handleError('Invalid page.', 'university?opt=certification');
        } 

        if(md5('cert'.$playerLearning.$_SESSION['id']) != $completeInfo['GET_VALUE']){
            $system->handleError('You are busy learning this certification.', 'university?opt=certification&learn='.$playerLearning);
        }

        if(!$ranking->cert_validate2learn($playerLearning)){
            $system->handleError('Some error happened.', 'university?opt=certification');
        }

        $ranking->cert_add($playerLearning);

        $ranking->cert_end($playerLearning);

        $player->unsetPlayerLearning();
        
        if($playerLearning == 1){
            $player->setPlayerLearning(2);
        }
        
        header("Location:university?opt=certification");
        exit();

    } else {

        $ranking->cert_list();

    }

} elseif($system->issetGet('id')) {
    
    $idInfo = $system->verifyNumericGet('id');
    
    if($idInfo['IS_NUMERIC'] == 0 || $idInfo['GET_VALUE'] < 0){
        $system->handleError('Invalid get', 'university.php');
    }
 
    if(!$software->issetSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC')) {
        $system->handleError('This software does not exists.', 'university.php');
    }
    
    $software->research_show($idInfo['GET_VALUE']);

} else {

    $software->research_list();

}

?>
     
                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                                
<?php                                

require 'template/contentEnd.php';

?>