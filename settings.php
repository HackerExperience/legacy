<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';

$session = new Session();
$system = new System();
$player = new Player();

if(isset($_POST)){
    
    if(isset($_POST['old'])){
        $session->addMsg('Sorry, change password is not available for now.', 'error');
    }
    
    if(isset($_POST['lang'])){
        
        $lang = $_POST['lang'];
        if(empty($lang)){
            $session->addMsg('Please choose a language.', 'error');
            header("Location:settings.php");
            exit();
        }
        
        switch($lang){
            case 'English':
            case 'Português':
                break;
            default:
                $_POST = Array();
                $session->addMsg('Please choose a valid language.', 'error');
                header("Location:settings.php");
                exit();
        }

        $change = TRUE;
        if($lang == 'Português'){
            if($session->l == 'pt_BR'){
                $change = FALSE;
            }
        } elseif($lang == 'English'){
            if($session->l == 'en_US'){
                $change = FALSE;
            }
        }
        
        if(!$change){
            $session->addMsg('This already is your language.', 'error');
            header("Location:settings.php");
            exit();
        }
        
        $pdo = PDO_DB::factory();
        
        if($lang == 'Português'){
            
            $session->newQuery();
            $sql = 'UPDATE users_language SET lang = :lang WHERE userID = :userID';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':userID' => $_SESSION['id'], ':lang' => 'br'));

            $_SESSION['language'] = 'pt_BR';
            $session->addMsg('Language changed to portuguese.', 'notice');
            header("Location:https://br.hackerexperience.com/");
            exit();
        } elseif($lang == 'English'){
            
            $session->newQuery();
            $sql = 'UPDATE users_language SET lang = :lang WHERE userID = :userID';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(':userID' => $_SESSION['id'], ':lang' => 'en'));
            
            $_SESSION['language'] = 'en_US';
            $session->addMsg('Language changed to english.', 'notice');
            header("Location:https://en.hackerexperience.com/");
            exit();
        }
        
    }
    
    
    
}


require 'template/contentStart.php';

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
                    <li class="link active"><a href="settings.php"><span class="icon-tab he16-settings"></span><?php echo _('My settings'); ?></a></li>
                    <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                </ul>
            </div>
            <div class="widget-content padding noborder">
                
<?php

$player->settings_show();


?>
                                


                
            </div>
            <div class="nav nav-tabs" style="clear: both;"></div>
<?php

require 'template/contentEnd.php';

?>