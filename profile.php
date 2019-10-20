<?php
require '/var/www/classes/Session.class.php';
require 'config.php';
require '/var/www/classes/Social.class.php';
require '/var/www/classes/System.class.php';
require_once '/var/www/classes/Player.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$player = new Player();
$social = new Social();

if(!isset($_SESSION['VALID_ID'])){
    $_SESSION['VALID_ID'] = '';
}

$profileInfo = Array();
$profileInfo['ACTIVE'] = 'profile';
$profileInfo['VALID_ID'] = 1;
$profileInfo['GET'] = '';
$error = 0;
$str = '';

$menuProfile = 'active';
$menuEmail = $menuSearch = $menuFriends = $menuEdit = '';

if ($system->issetGet('id')) {

    $idInfo = $system->verifyNumericGet('id');

    if ($idInfo['IS_NUMERIC'] == '1') {
        
        if ($player->verifyID($idInfo['GET_VALUE'])) {
        
            $_SESSION['PROFILE_ID'] = $idInfo['GET_VALUE'];
            $profileInfo['VALID_ID'] = 1;
            $profileInfo['GET'] = 'id';
            
            if($system->issetGet('view')){

                $profileInfo['GET'] = 'id.view';

                $viewInfo = $system->switchGet('view', 'email', 'search', 'friends');

                if($viewInfo['ISSET_GET'] == '1'){

                    $menuProfile = '';
                    
                    switch($viewInfo['GET_VALUE']){

                        case 'email':
                            $profileInfo['ACTIVE'] = 'email';
                            $menuEmail = 'active';
                            break;
                        case 'search':
                            $profileInfo['ACTIVE'] = 'search';
                            $menuSearch = 'active';
                            break;
                        case 'friends':
                            $profileInfo['ACTIVE'] = 'friends';
                            $menuFriends = 'active';
                            break;
                    
                    }

                } else {
                    $system->handleError('Invalid Get', 'profile?id='.$idInfo['GET_VALUE']);
                }

            }

            $str = '?id='.$idInfo['GET_VALUE'];

        } else {

            $error = 1;
            $profileInfo['VALID_ID'] = 0;
            
        }
        
    } else {
        $error = 1;
        $system->handleError('INVALID_ID');
        $session->returnMsg();
    }
    
} else {
    
    $_SESSION['PROFILE_ID'] = $_SESSION['id'];
    $_SESSION['VALID_ID'] = '1';
    
    if($system->issetGet('view')){

        $profileInfo['GET'] = 'view';

        $viewInfo = $system->switchGet('view', 'edit', 'search', 'friends');
        
        if($viewInfo['ISSET_GET'] == 1){
            
            $menuProfile = '';
            
            switch($viewInfo['GET_VALUE']){
                
                case 'edit':
                    $profileInfo['ACTIVE'] = 'edit';
                    $menuEdit = 'active';
                    break;
                case 'search':
                    $profileInfo['ACTIVE'] = 'search';
                    $menuSearch = 'active';
                    break;
                case 'friends':
                    $profileInfo['ACTIVE'] = 'friends';
                    $menuFriends = 'active';
                    break;
            }
            
        } else {
            $system->handleError('Invalid Get', 'profile.php');
        }
        
    } 
    
}

if($error == 1){
    
    $profileInfo['GET'] = '';
    $_SESSION['PROFILE_ID'] = $_SESSION['id'];
    $str = '';
    
}

if($str != ''){
    $conectivo = '&';
} else {
    $conectivo = '?';
}

$friendLink = $str.$conectivo.'view=friends';
$emailLink = $str.$conectivo.'view=email';
$searchLink = $str.$conectivo.'view=search';
$profileLink = 'profile.php';

if($_SESSION['PROFILE_ID'] != $_SESSION['id']){
    
    $profileLink .= '?id='.$_SESSION['PROFILE_ID'];
    
}

if($_SESSION['VALID_ID'] == '1' && $error == 0){

    $playerInfo = $player->getPlayerInfo($_SESSION['PROFILE_ID']);
    $profileOf = _(' of ')."$playerInfo->login";

} else {
    $profileOf = '';
    $profileLink = 'profile.php';
}


if($menuProfile != ''){
    $span = '<div class="span8">';
} else {
    $span = '';
}

?>
                    <div class="span12">

<?php

if ($session->issetMsg()) {
    $session->returnMsg();
}

?>
                        
                        <div class="widget-box">

                            <div class="widget-title">
                                <ul class="nav nav-tabs">
                                    <li class="link <?php echo $menuProfile; ?>"><a href="<?php echo $profileLink; ?>"><span class="icon-tab he16-profile"></span><span class="hide-phone"><?php echo _('Profile'); ?> <?php echo $profileOf; ?></span></img></a></li>
                                    <li class="link <?php echo $menuFriends; ?>"><a href="<?php echo $friendLink; ?>"><span class="icon-tab he16-profile_friends"></span><span class="hide-phone"><?php echo _('Friends'); ?></span></img></a></li>
                                    <?php if($_SESSION['PROFILE_ID'] != $_SESSION['id']){ ?>
                                    <li class="hide-phone link <?php echo $menuEmail; ?>"><a href="<?php echo $emailLink; ?>"><span class="icon-tab he16-email_send"></span><?php echo _('Email'); ?></img></a></li>
                                    <?php } else { ?>
                                    <li class="link <?php echo $menuEdit; ?>"><a href="?view=edit"><span class="icon-tab he16-profile_edit"></span><span class="hide-phone"><?php echo _('Edit'); ?></span></img></a></li>
                                    <?php } ?>
                                    <li class="link <?php echo $menuSearch; ?>"><a href="<?php echo $searchLink; ?>"><span class="icon-tab he16-search"></span><span class="hide-phone"><?php echo _('Search'); ?></span></img></a></li>
                                    <?php if($_SESSION['PROFILE_ID'] != $_SESSION['id']){ ?>
                                    <li class="hide-phone link"><a href="profile.php"><span class="icon-tab he16-my_profile"></span><?php echo _('My Profile'); ?></img></a></li>
                                    <?php } ?>
                                    <li class="link" style="float: right;"><span class="icol32-help" ></span></li>
                                </ul>
                            </div>

                            <div class="widget-content padding noborder">

                                <?php echo $span; ?>

<?php

if($profileInfo['VALID_ID'] == 1){

    if($profileInfo['GET'] != ''){

        if($profileInfo['GET'] == 'id'){
            $social->showProfile($idInfo['GET_VALUE']);
        } elseif($profileInfo['GET'] == 'view' || $profileInfo['GET'] == 'id.view'){

            switch($profileInfo['ACTIVE']){
                
                case 'edit':

                    if($profileInfo['GET'] == 'view'){
                        
                        $social->profile_edit();
                        
                    } else {
                        echo 'Invalid page!';
                    }
                    
                    break;
                case 'search':

                    if($system->issetGet('user')){
                        
                        $userInfo = $system->verifyStringGet('user');
                        
                        if($userInfo['IS_NUMERIC'] == '0'){
                            
                            $searchID = $player->getIDByUser($userInfo['GET_VALUE']);
                            
                            if($searchID['PLAYER_ID'] != 0){
                                
                                header("Location:profile?id=$searchID[PLAYER_ID]");
                                
                            } else {
                                echo 'User "'.$userInfo['GET_VALUE'].'" not found';
                            }
                            
                        } else {
                            echo 'Invalid username';
                        }
                        
                    } else {
                    
                        $social->profile_search($_SESSION['PROFILE_ID']);
                    
                    }

                    break;
                case 'friends':
                    
                    if($system->issetGet('request')){
                        
                        $requestInfo = $system->verifyNumericGet('request');
                                                
                        if($requestInfo['IS_NUMERIC'] == 1){
                            
                            if($system->issetGet('friend')){
                                
                                $friendInfo = $system->verifyNumericGet('friend');
                                
                                if($friendInfo['IS_NUMERIC'] == 1){
                                    
                                    if($social->friend_validRequest($requestInfo['GET_VALUE'], $friendInfo['GET_VALUE'])){
                                        
                                        if(!$social->friend_isset($friendInfo['GET_VALUE'])){
                                            
                                            $social->friend_add($requestInfo['GET_VALUE'], $friendInfo['GET_VALUE']);
                                                                                        
                                            require '/var/www/classes/Python.class.php';
                                            $python = new Python();
                                            
                                            $python->generateProfile($friendInfo['GET_VALUE']);
                                            $python->generateProfile($friendInfo['GET_VALUE'], 'br');
                                            $python->generateProfile($_SESSION['id']);
                                            $python->generateProfile($_SESSION['id'], 'br');
                                            
                                            $session->addMsg('Yey! You two are now friends :)', 'notice');
                                            header("Location:profile");
                                            exit();
                                            
                                        } else {
                                            $error = 'You are already friends with this guy.';
                                        }
                                        
                                    } else {
                                        $error = 'Invalid friend request.';
                                    }
                                    
                                } else {
                                    $error = 'Invalid get.';
                                }
                                
                            } else {
                                $error = 'Invalid get.';
                            }
                            
                        } else {
                            $error = 'Invalid get.';
                        }
                        
                        if($error != ''){
                            
                            $session->addMsg($error, 'error');
                            header("Location:profile.php");
                            
                        } else {
                            
                            $social->profile_friends($_SESSION['id']);
                            
                        }
                        
                    } else {
                    
                        if($system->issetGet('add')){

                            $addInfo = $system->verifyNumericGet('add');

                            if($addInfo['IS_NUMERIC'] == 1){

                                if($player->verifyID($addInfo['GET_VALUE'])){

                                    if($addInfo['GET_VALUE'] != $_SESSION['id']){

                                        if(!$social->friend_isset($addInfo['GET_VALUE'])){

                                            if(!$social->friend_issetRequest($addInfo['GET_VALUE'])){

                                                $social->friend_request($addInfo['GET_VALUE']);

                                                $session->addMsg('Friend request sent. Wait until he/she answers your request.', 'notice');
                                                header("Location:profile.php");
                                                exit();

                                            } else {
                                                $session->addMsg('There already is a pending request of friendship.', 'error');
                                                header("Location:profile.php");
                                                exit();
                                            }

                                        } else {
                                            $session->addMsg('You already are friends with this guy.', 'error');
                                            header("Location:profile.php");
                                            exit();
                                        }

                                    } else {
                                        $session->addMsg('Sorry, but you can not add yourself! Thats way too forever alone.', 'error');
                                        header("Location:profile.php");
                                        exit();
                                    }

                                } else {
                                    $session->addMsg('This user does not exists.', 'error');
                                    header("Location:profile.php");
                                    exit();
                                }

                            } else {
                                $session->addMsg('Invalid get.', 'error');
                                header("Location:profile.php");
                                exit();
                            }

                            $social->profile_friends($_SESSION['PROFILE_ID']);

                        } elseif($system->issetGet('del')){
                            echo 'del';
                        } else {

                            $social->profile_friends($_SESSION['PROFILE_ID']);

                        }
                    
                    }
                    
                    break;
            }
            
        }

    } else {

        $social->showProfile($_SESSION['id']);

    }

} else {
    if($error == 1){
        echo $error;
        $social->showProfile($_SESSION['id']);
    } else {
        echo 'User #'.$idInfo['GET_VALUE'].' doesnt exists';
    }
}

if($menuProfile != ''){
    
?>
                                    </div>
                                </div>
                                    
<?php    
    
}

?>
                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
<?php                                    

require 'template/contentEnd.php';

?>