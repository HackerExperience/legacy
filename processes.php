<?php

require 'config.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Process.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';
   
$process = new Process();

$gotGet = '0';
if($system->issetGet('pid')){

    $getInfo = $system->verifyNumericGet('pid');

    if($getInfo['IS_NUMERIC'] == '1'){

        if($process->issetPID($getInfo['GET_VALUE'])){

            $gotGet = '1';

        } else {

            $system->handleError('PROC_NOT_FOUND');

        }          

    } else {
        $system->handleError('INVALID_GET', 'processes.php');
    }

}

if($system->issetGet('del')){

    if($_GET['del'] == '1' && $gotGet == '1'){

        $process->deleteProcess($getInfo['GET_VALUE'], false);
        $session->addMsg('Process deleted.', 'notice');

        $gotGet = '0';

    }

}

if($session->issetMsg()){

    $session->returnMsg();

}    

if($system->issetGet('page')){

    $pageInfo = $system->switchGet('page', 'all', 'cpu', 'network', 'running');

    if($pageInfo['ISSET_GET'] == 1){

        switch($pageInfo['GET_VALUE']){

            case 'all':
                $page2load = 'all';
                break;
            case 'cpu':
                $page2load = 'cpu';
                break;
            case 'network':
                $page2load = 'net';
                break;
            case 'running':
                $page2load = 'running';
                break;

        }

    } else {
        $system->handleError('INVALID_GET', 'processes.php');
    }

} elseif($system->issetGet('pid') && $gotGet == 1){

    $process->getProcessInfo($getInfo['GET_VALUE']);

    if($process->pAction == 1 || $process->pAction == 2){
        $page2load = 'net';
    } else {
        $page2load = 'cpu';
    }


} else {

    $procInfo = $process->runningProcessInfo();

    if($procInfo['ISSET'] == 1){

        if($procInfo['INCOMPLETE_NET_PROC'] > 0 && $procInfo['INCOMPLETE_CPU_PROC'] > 0){
            $page2load = 'all';
        } elseif($procInfo['INCOMPLETE_NET_PROC'] > 0){
            $page2load = 'net';
        } elseif($procInfo['INCOMPLETE_CPU_PROC'] > 0){
            $page2load = 'cpu';
        } elseif($procInfo['COMPLETE_CPU_PROC'] > 0 && $procInfo['COMPLETE_NET_PROC'] > 0){
            $page2load = 'all';
        } elseif($procInfo['COMPLETE_CPU_PROC'] > 0) {
            $page2load = 'cpu';
        } else {
            $page2load = 'net';
        }

    } else {
        $page2load = 'all';
    }

}

?>

            <div class="widget-box">

                <div class="widget-title">
                    <ul class="nav nav-tabs">                                  


            <li class="link<?php if($page2load == 'all') { echo ' active'; } ?>"><a href="?page=all"><span class="icon-tab he16-taskmanager"></span><?php echo _("All tasks");?></a></li>
            <li class="hide-phone link<?php if($page2load == 'cpu') { echo ' active'; } ?>"><a href="?page=cpu"><span class="icon-tab he16-tasks_cpu"></span><?php echo _("CPU tasks");?></a></li>
            <li class="hide-phone link<?php if($page2load == 'net') { echo ' active'; } ?>"><a href="?page=network"><span class="icon-tab he16-tasks_download"></span><?php echo _("Download manager");?></a></li>
            <li class="link<?php if($page2load == 'running') { echo ' active'; } ?>"><a href="?page=running"><span class="he16-running icon-tab"></span><span class="hide-phone"><?php echo _("Running softwares");?></span></a></li>
            <a href="<?php echo $session->help('task'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>

                            </ul>
                    </div>
                    <div class="widget-content padding noborder">    

<?php

if($gotGet == '1'){

    if($system->issetGet('action')){

        $actionInfo = $system->switchGet('action', 'pause', 'resume');

        if($actionInfo['ISSET_GET'] == 1){

            switch($actionInfo['GET_VALUE']){

                case 'pause':

                    if(!$process->isPaused($getInfo['GET_VALUE'])){

                        $process->pauseProcess($getInfo['GET_VALUE']);

                        $session->addMsg(_('Process paused.'), 'notice');
                        
                        header("Location:processes.php");
                        exit();
                        
                    } else {
                        $system->handleError('PROC_ALREADY_PAUSED', 'processes.php');
                    }

                    break;
                case 'resume':

                    if($process->isPaused($getInfo['GET_VALUE'])){

                        $process->resumeProcess($getInfo['GET_VALUE']);

                        $session->addMsg(_('Process resumed.'), 'notice');
                        
                        header("Location:processes.php");
                        exit();

                    } else {
                        $system->handleError('PROC_NOT_PAUSED', 'processes.php');
                    }

                    break;

            }

        } else {
            $system->handleError('INVALID_GET', 'processes.php');
        }


    } else {

        if($process->pID != $getInfo['GET_VALUE']){

            $process->getProcessInfo($getInfo['GET_VALUE']);

        }

        $process->showProcess();

        $process->endShowProcess(2);

    }

} else {

    switch($page2load){

        case 'all':

            $process->listProcesses($_SESSION['id'], 'all');

            break;
        case 'cpu':

            $process->listProcesses($_SESSION['id'], 'cpu');

            break;
        case 'net':

            $process->listProcesses($_SESSION['id'], 'net');

            break;
        case 'running':

            $software = new SoftwareVPC();
            $software->listRunningSoftwares();

            break;

    }

}

require 'template/contentEnd.php';

?>