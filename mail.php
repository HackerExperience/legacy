<?php
require 'config.php';
require '/var/www/classes/Mail.class.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/System.class.php';
require_once '/var/www/classes/Player.class.php';


$session = new Session();
$system = new System();

require 'template/contentStart.php';

$mail = new Mail();
$player = new Player();



if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){
    $mail->handlePost();
}    

$current = '';
$inbox = ' active';
$sent = '';
$new = '';
$invalid = 0;
$issetCurrent = 0;
$currentLink = '';

if(isset($_SESSION['CUR_MAIL']) && !$system->issetGet('show') && $_SERVER['PHP_SELF'] != '/mail.php'){
    $current = ' active';
    $inbox = '';
    $issetCurrent = 1;
}

if($system->issetGet('action') && !$system->issetGet('id')){
    
    $inbox = '';
    
    $actionInfo = $system->verifyStringGet('action');
    
    switch($actionInfo['GET_VALUE']){
        
        case 'new':
            $new = ' active';
            $current = '';
            break;
        case 'outbox':
            $sent = ' active';
            break;
        
    }
    
} elseif($system->issetGet('id')){
    
    $idInfo = $system->verifyNumericGet('id');
    
    if($idInfo['IS_NUMERIC'] == '1'){
        
        $currentLink = '?id='.$idInfo['GET_VALUE'];
        $mid = $idInfo['GET_VALUE'];
        
    } else {
        $currentLink = 'mail.php';
        $invalid = 1;
    }
    
    $current = ' active';
    $inbox = '';
    
    $issetCurrent = 1;
    
}

if(isset($_SESSION['CUR_MAIL']) && !$system->issetGet('id')){
    
    $currentLink = '?id='.$_SESSION['CUR_MAIL'];
    $issetCurrent = '1';
    $mid = $_SESSION['CUR_MAIL'];
    
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
                                    <li class="link<?php echo $inbox; ?>"><a href="mail.php"><?php echo _('Inbox'); ?></a></li>
                                    <li class="link<?php echo $sent; ?>"><a href="?action=outbox"><?php echo _('Outbox'); ?></a></li>
                                    <li class="link<?php echo $new; ?>"><a href="?action=new"><?php echo _('New message'); ?></a></li>
                                    <?php if($issetCurrent == 1) { ?>
                                        <li class="link<?php echo $current; ?>"><a href="<?php echo $currentLink; ?>" id="mail-title"><?php echo _('Current Message'); ?></a></li>
                                    <?php } ?>
                                    <li class="link" style="float: right;"><span class="icol32-help" ></span></li>
                                </ul>
                            </div>

                            <div class="widget-content padding noborder">

<?php

$error = '';

if ($system->issetGet('action')) {

    $actionInfo = $system->verifyStringGet('action');

    if ($actionInfo['IS_NUMERIC'] == '0') {

        switch ($actionInfo['GET_VALUE']) {

            case 'new':
                
                $mail->show_sendMail();

                break;
            case 'outbox':

                $mail->listSentMails();

                break;
                
            default:
                $error = 'Invalid action';
                break;
                
        }
        
    } else {
        $error = 'Invalid action';
    }
    
} elseif ($system->issetGet('id')) {

    $idInfo = $system->verifyNumericGet('id');

    if ($idInfo['IS_NUMERIC'] == '1') {

        if ($mail->issetMail($idInfo['GET_VALUE'])) {

            $valid = '0';
            if(!$mail->isDeleted($idInfo['GET_VALUE'])){
            
                $mailInfo = $mail->returnMailInfo($idInfo['GET_VALUE']);
            
                if($mailInfo->to == $_SESSION['id']){
                    if($mailInfo->isdeleted == '0'){
                        $valid = '1';
                    }
                } elseif($mailInfo->from == $_SESSION['id']){
                    $valid = '1';
                }
                
            } else {
                
                if($mail->returnMailFrom($idInfo['GET_VALUE']) == $_SESSION['id']){
                    $valid = '1';
                }
                
            }
            
            if($valid == '1'){
            
                $mail->showMail($idInfo['GET_VALUE']);
            
            } else {
                $error = 'This email does not exists.';
            }
            
        } else {
            $error = 'This email does not exists.';
        }
        
    } else {
        $error = 'Invalid mail ID.';
    }
    
} else {
    $mail->listMails();
}

if($error != ''){
    $system->handleError($error, 'mail.php');
}

$nbsp = '';
if($new != '' || $current != ''){
    ?>
                            </div>                    
    <?php
    if($current != ''){
        $nbsp = '&nbsp;';
    }
}

?>

                        </div>
                        <div style="clear: both;" class="nav nav-tabs"><?php echo $nbsp; ?></div>    
                        
<?php

require 'template/contentEnd.php';

?>