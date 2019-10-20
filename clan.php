<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Mission.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Clan.class.php';


$session = new Session();
$system = new System();

require 'template/contentStart.php';

$clan = new Clan();

$myClan = 'active';
$createClan = '';
$searchClan = '';
$ranking = '';
$adminClan = '';
$settingsClan = '';
$warClan = '';
$span = 'span8';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

    $clan->handlePost();
    

}    

if($system->issetGet('action')){
    
    $actionInfo = $system->switchGet('action', 'join', 'search', 'ranking', 'create', 'settings', 'admin', 'list', 'leave', 'war');
    
    if($actionInfo['ISSET_GET'] == 1){
        
        $myClan = '';
        
        switch($actionInfo['GET_VALUE']){
            
            case 'search':             
                $searchClan = 'active';
                break;
            case 'create':
                $createClan = 'active';
                break;
            case 'ranking':
                $ranking = 'active';
                break;
            case 'admin':
            case 'leave':    
                $adminClan = 'active';
                $settingsClan = 'active';
                if(!isset($_GET['id'])){
                    $span = 'span12';
                }
                break;
            case 'settings':
            case 'leave':    
                $settingsClan = 'active';
                break;
            case 'list':
                $span = 'span12';
                $myClan = 'active';
                break;
            case 'war':
                $warClan = 'active';
                break;
            
            
        }
        
    } else {
        
        $session->addMsg('Invalid get.', 'error');
        $session->returnMsg();
        
    }
    
}

if($clan->playerHaveClan()){
    $clanMember = 1;
    $auth = $clan->playerAuth();
    $clan->myClan = $clan->getPlayerClan();
} else {
    $clanMember = 0;
    $auth = 0;
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
                                    <li class="link <?php echo $myClan; ?>"><a href="clan"><span class="icon-tab he16-clan"></span><span class="hide-phone"><?php echo _('My clan'); ?></span></a></li>
                                    <?php if($clanMember == 0){  ?>
                                    <li class="link <?php echo $createClan; ?>"><a href="?action=create"><span class="icon-tab he16-clan_add"></span><span class="hide-phone"><?php echo _('Create clan'); ?></span></a></li>
                                    <?php } else { ?>
                                    <li class="link <?php echo $warClan; ?>"><a href="?action=war"><span class="icon-tab he16-war_info"></span><span class="hide-phone"><?php echo _('Clan war'); ?></span></a></li>
                                        <?php if($auth == 1){ ?>
                                            <li class="link <?php echo $settingsClan; ?>"><a href="?action=settings"><span class="icon-tab he16-clan_set"></span><span class="hide-phone"><?php echo _('Clan settings'); ?></span></a></li>
                                        <?php } else { ?>
                                            <li class="link <?php echo $adminClan; ?>"><a href="?action=admin"><span class="icon-tab he16-clan_adm"></span><span class="hide-phone"><?php echo _('Admin'); ?></span></a></li>
                                        <?php } ?>
                                    <?php } ?>
                                    <li class="link <?php echo $searchClan; ?>"><a href="?action=search"><span class="icon-tab he16-search"></span><span class="hide-phone"><?php echo _('Search clan'); ?></span></a></li>
                                    <li class="link <?php echo $ranking; ?>"><a href="ranking?show=clan"><span class="icon-tab he16-ranking"></span><span class="hide-phone"><?php echo _('Clan ranking'); ?></span></a></li>
                                    <a href="<?php echo $session->help('clan'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                                </ul>
                            </div>

                            <div class="widget-content padding noborder">

                                <div class="<?php echo $span; ?>">                           

<?php

if($system->issetGet('action')){
    
    $actionInfo = $system->switchGet('action', 'join', 'search', 'ranking', 'create', 'settings', 'admin', 'list', 'leave', 'war');
    
    if($actionInfo['ISSET_GET'] == 1){
        
        switch($actionInfo['GET_VALUE']){
            
            case 'war':
                
                if($clanMember == 1){

                    if($system->issetGet('clan') && !$system->issetGet('show')){

                        $error = '';
                        $clanInfo = $system->verifyNumericGet('clan');

                        if($clanInfo['IS_NUMERIC'] == 1){

                            if($clan->issetClan($clanInfo['GET_VALUE'])){

                                if($clan->issetWar($clanInfo['GET_VALUE'], $clan->myClan)){

                                    $clan->show_warHistory($clanInfo['GET_VALUE']);

                                } else {
                                    $error = 'This clan isnt in war with yours.';
                                }

                            } else {
                                $error  = 'Clan doesnt exists.';
                            }

                        } else {
                            $error = 'Invalid get.';
                        }

                        if($error != ''){
                            $session->addMsg($error, 'error');
                            header("Location:clan?action=war");
                        }

                    } elseif($system->issetGet('show')){
           
                        $error = '';
                        $showInfo = $system->verifyStringGet('show');
                        
                        if($showInfo['IS_NUMERIC'] == 0){
                            
                            if($showInfo['GET_VALUE'] == 'history'){
                                
                                if($system->issetGet('round')){
                                    
                                    $roundInfo = $system->verifyNumericGet('round');
                                    
                                    if($roundInfo['IS_NUMERIC'] == 1){

                                        $clan->show_warHistoryAll($roundInfo['GET_VALUE']); 

                                    } else {
                                        $error = 'Invalid get.';
                                    }
                                    
                                } else {

                                    $clan->show_warHistoryAll(); 
                                
                                }
                                
                            } else {
                                $clan->show_warHistoryAll('current'); //show current
                            }
                            
                        } else {
                            $error = 'Invalid get.';
                        }
                        
                        if($error != ''){
                            $session->addMsg($error, 'error');
                            header("Location:clan?action=war");
                        }
                        
                    } else {

                        $clan->show_warSelect();

                    }
                
                } else {
                    $session->addMsg('Only members can see the war details.', 'error');
                    header("Location:clan");
                }
                
                break;
            case 'leave':
                
                if($clan->playerHaveClan()){

                    $clan->show_leavePage($clan->getUserAuthAndHierarchy()->authlevel);

                } else {
                    header("Location:clan");
                }
                
                break;
            
            case 'list':
                
                if($system->issetGet('id')){
                    
                    $idInfo = $system->verifyNumericGet('id');
                    
                    if($idInfo['IS_NUMERIC'] == 1){
                        
                        if($clan->issetClan($idInfo['GET_VALUE'])){
                            
                            $clan->show_listMembers($idInfo['GET_VALUE']);
                                                        
                        } else {
                            echo 'This clan does not exists';
                        }
                        
                    } else {
                        echo 'Invalid clan ID';
                    }
                    
                } else {
                 
                    if($clanMember == 1){

                        $clan->show_listMembers($clan->myClan);

                    } else {
                        echo _('You are not a member of any clan yet');
                    }
                    
                }
                
                break;
            case 'search':             
                
                $clan->view_search();

                break;
            case 'create':
                
                if($clanMember == 0){
              
                    $clan->show_createClan();

                } else {
                    echo 'Already part of a clan';
                }
                
                break;
            case 'ranking':
                $cid = 1;
                $clan->show_listMembers($cid);
                break;
            case 'admin':
                
                if($clanMember == 1 && $auth > 1){

                    if($system->issetGet('reject')){
                        
                        $rejectInfo = $system->verifyNumericGet('reject');
                        
                        if($rejectInfo['IS_NUMERIC'] == 1){
                            
                            $reqData = Array();
                            $reqData['0']['INFO'] = $clan->getRequestInfo($rejectInfo['GET_VALUE']);
                            
                            if($reqData['0']['INFO']['ISSET'] == 1){
                                                                
                                $clan->showModal($reqData, 'request_deny');
                                
                            } else {
                                $system->handleError('This request does not exists.', 'clan?action=admin');
                            }
                            
                        }
                        
                    }
                    
                    if($system->issetGet('opt')){

                        $optInfo = $system->switchGet('opt', 'img', 'manage');

                        if($optInfo['ISSET_GET'] == 1){

                            switch($optInfo['GET_VALUE']){

                                case 'manage':

                                    $error = 0;
                                    if($system->issetGet('id')){
                                        
                                        $idInfo = $system->verifyNumericGet('id');
                                        
                                        if($idInfo['IS_NUMERIC'] == 1){
                                            
                                            if($idInfo['GET_VALUE'] != $_SESSION['id']){
                                            
                                                $player = new Player();
                                                if($player->verifyID($idInfo['GET_VALUE'])){

                                                    if($clan->playerHaveClan($idInfo['GET_VALUE'])){

                                                        if($clan->getPlayerClan($idInfo['GET_VALUE']) == $clan->myClan){

                                                            if($system->issetGet('do')){

                                                                $doInfo = $system->switchGet('do', 'hierarchy', 'kick');

                                                                if($doInfo['GET_VALUE'] == 'kick'){
                                                                
                                                                    $clan->show_manage($idInfo['GET_VALUE'], 'kick');
                                                                    
                                                                } else {
                                                                    $clan->show_manage($idInfo['GET_VALUE'], 'index');
                                                                }

                                                            } else {
                                                            
                                                                $clan->show_manage($idInfo['GET_VALUE'], 'index');

                                                            }

                                                        } else {
                                                            echo 'This player is not in your clan';
                                                            $error = 1;
                                                            $page = 'member';
                                                        }

                                                    } else {
                                                        echo 'This player is not in your clan';
                                                        $error = 1;
                                                        $page = 'member';
                                                    }

                                                } else {
                                                    echo 'No such player';
                                                    $error = 1;
                                                    $page = 'member';
                                                }
                                            
                                            } else {
                                                $clan->show_manageSelf();
                                            }
                                            
                                        } else {
                                            echo 'Invalid get';
                                        }
                                        
                                    } else {
                                    
                                        $clan->show_listMembers($clan->getPlayerClan(), 2);

                                    }
                                    
                                    if($error == 1){
                                        
                                        echo "<br/><br/>";
                                        
                                        switch($page){
                                            
                                            case 'admin':
                                            case 'hierarchy':
                                                
                                                $clan->show_manage($idInfo['GET_VALUE'], $page);
                                                
                                                break;
                                            case 'member':
                                                
                                                $clan->show_listMembers($clan->getPlayerClan(), 1);
                                                
                                                break;
                                            
                                            
                                        }
                                                                                
                                    }
                                    
                                    break;
                                
                            }

                        } else {
                            echo 'Invalid get';
                        }

                    } else {

                        $clan->show_adminPanel();

                    }
                
                } else {
                    echo 'You dont have auth to access this page';
                }
                                
                
                break;
            case 'settings':
                
                if($clanMember == 1){
                    if($auth == 1){
                        
                        $clan->show_clanSettings();
                        
                    } elseif($auth == 4){
                        header("Location:clan?action=admin");
                        exit();
                    }
                } else {
                    echo 'You dont have auth to access this page';
                }
                
                break;
            case 'join':
                
                if($clanMember == 0){
                
                    if($system->issetGet('id')){

                        $idInfo = $system->verifyNumericGet('id');

                        if($idInfo['IS_NUMERIC'] == 1){

                            if($clan->issetClan($idInfo['GET_VALUE'])){

                                ?>

                                <form action="#" method="POST">

                                    <input type="hidden" name="act" value="request">
                                    <input type="hidden" name="clanID" value="<?php echo $idInfo['GET_VALUE']; ?>">
                                    <p>Message to admin (optional)<br/><br/><textarea name="text" id="styled" style="width:57%; height: 70px;" onfocus=" setbg('#F5F5F5');" onblur="setbg('white');"></textarea></p>
                                    <br/><input type="submit" value="Ask to join">

                                </form>

                                <?php

                            } else {
                                echo 'This clan doesnt exists';
                            }

                        } else {
                            echo 'Invalid get';
                        }

                    } else {
                        echo 'No clan specified';
                    }                
                
                } else {
                    echo 'You are already a member of a clan';
                }
                
                break;

        }
        
    } else {

        if($clanMember == 1){

            $clan->show_clanInfo();

        } else {
            echo 'You are not a member of any clan yet';
        }
        
    }
    
} else {
    if($system->issetGet('id')){

        $idInfo = $system->verifyNumericGet('id');
        
        if($idInfo['IS_NUMERIC'] == 1){
            
            if($clan->issetClan($idInfo['GET_VALUE'])){
                
                $clan->show_clanInfo($idInfo['GET_VALUE']);
                
            } else {
                echo 'This clan doesnt exists';
            }
            
        } else {
            echo 'Invalid get';
        }
        
    } elseif($system->issetGet('opt') && $system->issetGet('request')){
        
        $optInfo = $system->verifyStringGet('opt');
        $requestInfo = $system->verifyNumericGet('request');
        
        if($requestInfo['IS_NUMERIC'] == 1 && $optInfo['IS_NUMERIC'] == 0){
            
            switch($optInfo['GET_VALUE']){
                
                case 'del':
                    
                    $error = 0;
                    $requestData = $clan->getRequestInfo($requestInfo['GET_VALUE']);
                    
                    if($requestData['ISSET'] == 1){
                        
                        if($requestData['TYPE'] == 1 && $requestData['USER_ID'] == $_SESSION['id']){
                            
                            $clan->deleteRequest($requestInfo['GET_VALUE']);
                            
                            $clan->show_clanInfo();
                            
                        } else {
                            $session->addMsg('This request does not exists.', 'error');
                            $error = 1;
                        }
                        
                    } else {
                        $session->addMsg('This request does not exists.', 'error');
                        $error = 1;
                    }
                    
                    if($error == 1){
                        
                        header("Location:clan");
                        
                    }
                    
                    break;
                default:
                    echo 'Invalid option';
                    break;
                
            }
            
        } else {
            echo 'Invalid get';
        }
        
    } else {
        if($clanMember == 1){

            $clan->show_clanInfo();

        } else {
            echo 'You are not a member of any clan yet';
            $clan->show_pendingRequests();
        }
    }
}

?>
     
                                </div>
                            </div>
                            <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>
                                        
<?php                                        

require 'template/contentEnd.php';

?>
