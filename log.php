<?php


require 'config.php';
require_once '/var/www/classes/System.class.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/PC.class.php';
require '/var/www/classes/Process.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

?>
                    <div class="span12">
<?php
    if($session->issetMsg()){
        $session->returnMsg();
    }
?>

                        <div class="widget-box" style="width:100%">
                            <div class="widget-title">
                                <ul class="nav nav-tabs">
                                    <li class="link active"><a href="log.php"><span class="icon-tab he16-internet_log"></span><?php echo _('Log file'); ?></a></li>
                                    <a href="<?php echo $session->help('log'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                                </ul>
                            </div>
                            <div class="widget-content padding noborder center">
                                
<?php                                


$player = new Player($_SESSION['id']);
$log = new LogVPC();
$process = new Process();

$gotGet = '0';
if($system->issetGet('action')){
    $getAction = $system->switchGet('action', 'view', 'edit', 'del');
    $gotGet += '1';
}

if($system->issetGet('id')){
    $getIDInfo = $system->verifyNumericGet('id');
    $gotGet += '1';
}

if($gotGet == '2'){ //existe get

    if($getAction['ISSET_GET'] == '1' && $getAction['GET_NAME'] == 'action' && isset($getAction['GET_VALUE']) && $getIDInfo['IS_NUMERIC'] == '1' && isset($getIDInfo['GET_VALUE'])){ //verifico se existe get action E get id, e se são valores válidos

        switch($getAction['GET_VALUE']){

            case 'view':

                $log->listLog($_SESSION['id'], '', '1');

                $log->showLog($getIDInfo['GET_VALUE'], $_SESSION['id']);

                break;
            case 'edit':
                die("edit");
                break;
            case 'del':

                if($log->issetLog($getIDInfo['GET_VALUE'])){

                    if($process->newProcess($_SESSION['id'], 'D_LOG', '', 'local', '', $getIDInfo['GET_VALUE'], '', '0')){

                        $pid = $session->processID('show');
                        header("Location:processes?pid=$pid");

                    } else { //provavelmente já existe a ação pra del

                        $process->getProcessInfo($process->getPID($_SESSION['id'], 'D_LOG', '', 'local', '', $getIDInfo['GET_VALUE'], '', '0'));

                        require 'template/templateTop.php';
                        $process->showProcess();
                        require 'template/templateBot.php';

                    }

                }

                break;
            default:
                die("Invalid get");
                break;

        }

    } else {

        die("EEEErrrorrr");

    }

} else {


    if($session->issetMsg()){

        $session->returnMsg();

    }

    $log->listLog($_SESSION['id'], '', '1');

}


require 'template/contentEnd.php';

?>
