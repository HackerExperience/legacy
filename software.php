<?php

require '/var/www/classes/Player.class.php';
require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/PC.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Process.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$process = new Process();

if($session->issetLogin()){

    $software = new SoftwareVPC($_SESSION['id']);
    $hardware = new HardwareVPC($_SESSION['id']);

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST)){

        $software->handlePost();

    }
    
    $allSoftware = ' active';
    $external = '';
    $specificSoftware = '';
    $haveFolder = '';    
    $haveText = '';
    $add = '';
        
    $gotGet = '0';
    if($system->issetGet('action')){

        $getInfoAction = $system->switchGet('action', 'hide', 'seek', 'del', 'install', 'uninstall', 'nmap', 'text', 'folder', 'move', 'webserver');
        $gotGet= '1';

    }

    if($system->issetGet('id')){

        $getInfoSoftID = $system->verifyNumericGet('id');
        
        $specificSoftware = 'active';
        $allSoftware = '';

        if($getInfoSoftID['IS_NUMERIC'] == 1){
        
            if($software->issetSoftware($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC')){

                $softInfo = $software->getSoftware($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC');

                $softExists = 1;
                
            } else {
                
                if($software->issetExternalSoftware($getInfoSoftID['GET_VALUE'])){
                    
                    $softInfo = $software->getExternalSoftware($getInfoSoftID['GET_VALUE']); 

                    $softExists = 1;
                    
                } else {
                    $name = 'Unknown software';
                    $type = 'error';
                    $softExists = 0;
                }
            }
            
            if($softExists == 1){
                
                $name = $softInfo->softname.$software->getExtension($softInfo->softtype);
                $type = $softInfo->softtype;
                
            }
        
        } else {
            $session->addMsg('Invalid get.', 'error');
            $name = 'Unknown software';
            $type = 'error';
        }
        
        $link = '?';
        if($system->issetGet('action') && isset($getInfoAction)){
            
            if($getInfoAction['ISSET_GET'] == 1 && $_GET['action'] != 'buy'){
                
                $link = '?action='.$_GET['action'].'&';
                
            } elseif($getInfoAction['ISSET_GET'] == 0 && !isset($_GET['page'])){
                $system->handleError('INVALID_GET', 'software.php');
            }

        } else {
            $getInfoAction = NULL;
        }
        
        $link .= 'id='.$_GET['id'];
        
        $gotGet = 1;
        
    } elseif($system->issetGet('action') && $system->issetGet('view')){

        if($_GET['action'] == 'folder') {
            
            $haveFolder = ' active';
            $allSoftware = '';
            
            $viewInfo = $system->verifyNumericGet('view');
            
            if($viewInfo['IS_NUMERIC'] == 1){
                
                $folderName = $software->folder_name($viewInfo['GET_VALUE']);
                $link = 'software?action=folder&view='.$viewInfo['GET_VALUE'];
                
            }
            
            if(isset($_SESSION['TEXT'])){
                unset($_SESSION['TEXT']);
            }            
            $_SESSION['FOLDER'] = $viewInfo['GET_VALUE'];
            
        } elseif($_GET['action'] == 'text'){
            
            $haveText = ' active';
            $allSoftware = '';
            
            $viewInfo = $system->verifyNumericGet('view');
            
            if($viewInfo['IS_NUMERIC'] == 1){
                
                $textName = $software->text_name($viewInfo['GET_VALUE']);
                $link = 'software?action=text&view='.$viewInfo['GET_VALUE'];
                
            }
            
            if(isset($_SESSION['FOLDER'])){
                unset($_SESSION['FOLDER']);
            }
            $_SESSION['TEXT'] = $viewInfo['GET_VALUE'];
            
        }
                
    } elseif(isset($_SESSION['FOLDER'])){
        
        $folderName = $software->folder_name($_SESSION['FOLDER']);
        $link = 'software?action=folder&view='.$_SESSION['FOLDER'];
        $haveFolder = ' ';

    } elseif(isset($_SESSION['TEXT'])){
        
        $textName = $software->text_name($_SESSION['TEXT']);
        $link = 'software?action=text&view='.$_SESSION['TEXT'];
        $haveText = ' ';
        
    }
    
    if($system->issetGet('page')){
        
        $gotGet = '0'; //tratamento especial
        if($_GET['page'] == 'external'){
            $add = 'external';
            $external = ' active';
            $allSoftware = '';
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
                                    <li class="link <?php echo $allSoftware; ?>"><a href="software.php"><span class="icon-tab he16-software"></span></span><?php echo _("Softwares"); ?></a></li>
                                    <li class="link <?php echo $external; ?>"><a href="?page=external"><span class="icon-tab he16-xhd"></span><span class="hide-phone"><?php echo _("External HD"); ?></span></a></li>
<?php 
    if($specificSoftware != ''){ 
?>
                                    <li class="link <?php echo $specificSoftware; ?>"><a href="<?php echo $link; ?>"><span class="icon-tab he16-<?php echo $type; ?>"></span><?php echo _($name); ?></a></li>
<?php 
    }
    if($haveFolder != ''){ 
?>
                                    <li class="link <?php echo $haveFolder; ?>"><a href="<?php echo $link; ?>"><span class="icon-tab he16-31"></span><?php echo $folderName; ?></a></li>
<?php 
    } elseif ($haveText != ''){ 
?>
                                    <li class="link <?php echo $haveText; ?>"><a href="<?php echo $link; ?>"><span class="icon-tab he16-30"></span><?php echo $textName; ?></a></li>
<?php 

    } 
?>
                                    <a href="<?php echo $session->help('software', $add); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                                </ul>
                            </div>
                            <div class="widget-content padding noborder">
                                <div class="span9">
<?php
    
    if($gotGet == '1'){

        if($getInfoAction['ISSET_GET'] == '1' && $getInfoAction['GET_NAME'] == 'action' && isset($getInfoAction['GET_VALUE'])){

            // 2019: Action that contains file ID
            if(isset($getInfoSoftID)){ //é uma action que contém ID

                if($getInfoSoftID['IS_NUMERIC'] == '1' && isset($getInfoSoftID['GET_VALUE'])){

                    if($softExists == 1){

                        switch($getInfoAction['GET_VALUE']){

                            case 'del':

                                if($softInfo->softtype == 29 || $softInfo->softtype == 19 || $softInfo->softtype >= 90){
                                    $system->handleError('You can not delete this software.', 'software.php');
                                }
                                
                                $process->studyProcess('DELETE', 'local', 'VPC');

                                break;
                            case 'hide':

                                $process->studyProcess('HIDE', 'local', 'VPC');

                                break;
                            case 'seek':

                                $process->studyProcess('SEEK', 'local', 'VPC');

                                break;
                            case 'install':

                                $softInfo = $software->getSoftware($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC');

                                if($softInfo->softhidden == '0'){

                                    if(!$software->isInstalled($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC')){

                                        if($software->isExecutable($softInfo->softtype, $getInfoSoftID['GET_VALUE'], 1)){

                                            if (!$software->isSpecialExecutable($softInfo->softtype)) {

                                                $process->studyProcess('INSTALL', 'local', 'VPC');

                                            } else {

                                                switch ($softInfo->softtype) {

                                                    case '7': //av
                                                        $process->studyProcess('AV', 'local', 'VPC');
                                                        break;
                                                    case '15':
                                                        $process->studyProcess('NMAP', 'local', 'VPC');
                                                        break;
                                                    case '16':
                                                        header("Location:hardware.php");
                                                        break;
                                                    case 19:
                                                        $finances = new Finances();
                                                        $finances->bitcoin_runWallet($_SESSION['id']);
                                                        break;
                                                    case '29':
                                                        $process->studyProcess('INSTALL_DOOM', 'local', 'VPC');
                                                        break;
                                                    case '50': //storyline exlpoit

                                                        die("NOT FOR NOW");

                                                        break;
                                                    default:
                                                        die("aaaqewrquj");
                                                        break;
                                                }

                                            }

                                        } else {
                                            header("Location:software.php");
                                        }

                                    } else {
                                        header("Location:software.php");
                                    }
                                } else {
                                    die("hiden");
                                    header("Location:software.php");
                                }

                                break;
                            case 'uninstall':

                                $softInfo = $software->getSoftware($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC');

                                if($softInfo->softhidden == '0'){

                                    if($software->isInstalled($getInfoSoftID['GET_VALUE'], $_SESSION['id'], 'VPC') && $software->isVirus($softInfo->softtype) == FALSE){

                                        $process->studyProcess('UNINSTALL', 'local', 'VPC');

                                    } else {
                                        header("Location:software.php");
                                    }

                                } else {
                                    die("hiden");
                                }

                                break;
                            case 'move':
                                
                                if($softInfo->isfolder == 1){
                                    
                                    $software->folder_moveBack($getInfoSoftID['GET_VALUE']);
                                    
                                    $session->addMsg('Software was moved back to root.', 'notice');
                                    
                                    header("Location:software.php");
                                    
                                    
                                } else {
                                    echo 'This software is not on a folder!';
                                }
                                                                
                                break;
                            default:
                                die("errro");
                                break;

                        }

                    } else {
                        echo 'This software doesnt exists';
                    }

                } else {

                        header("Location:software.php");

                }


        // 2019: Action that does not require file id. 
        } else { //é uma action q n precisa de id do arquivo. ex: ?action=format

                switch($getInfoAction['GET_VALUE']){
                    case 'format':
                        echo 'format';
                        break;
                    case 'av':
                        echo 'av';
                        break;
                    case 'text':
                        
                        if($system->issetGet('view')){
                            
                            $viewInfo = $system->verifyNumericGet('view');
                            
                            if($viewInfo['IS_NUMERIC'] == 1){
                                
                                $software->text_show($viewInfo['GET_VALUE']);
                                
                            } else {
                                die("Invalid text id");
                            }
                            
                        } else {
                            header("Location:software.php");
                            exit();
                        }
                        
                        break;
                    case 'folder':
                        
                        if($system->issetGet('view')){
                            
                            $viewInfo = $system->verifyNumericGet('view');
                            
                            if($viewInfo['IS_NUMERIC'] == 1){
                            
                                if($system->issetGet('move')){
                                    exit("Deprecated");
                                    
                                    $moveInfo = $system->verifyNumericGet('move');
                                    
                                    if($moveInfo['IS_NUMERIC'] == 1){
                                        
                                        if($software->issetSoftware($moveInfo['GET_VALUE'], $_SESSION['id'], 'VPC')){
                                            
                                            $soft2moveInfo = $software->getSoftware($moveInfo['GET_VALUE'], $_SESSION['id'], 'VPC');
                                            
                                            if($soft2moveInfo->isfolder == 1){
                                            
                                                echo "<b>".'This software is already in a folder'."</b><br/><br/>";
                                            
                                            } elseif($soft2moveInfo->softhidden != 0) {
                                                echo "<b>".'Cant move hidden software'."</b><br/><br/>";
                                            } else {
                                                
                                                echo 'Moved'."<br/>";
                                                
                                                $software->folder_move($viewInfo['GET_VALUE'], $moveInfo['GET_VALUE']);
                                                
                                            }
                                            
                                            $software->folder_show($viewInfo['GET_VALUE'], '', 0);
                                            
                                        } else {
                                            die("This software doesnt exists");
                                        }
                                        
                                    } else {
                                        die("Invalid software ID");
                                    }
                                    
                                } else {
                                
                                    $software->folder_show($viewInfo['GET_VALUE'], '', 0);
                                
                                }
                                
                            } else {
                                die("Invalid folder id");
                            }
                            
                        } else {
                        
                            header("Location:software.php");
                            exit();
                        
                        }
                        
                        break;
                    case 'webserver':
                        
                        require '/var/www/classes/Internet.class.php';
                        $internet = new Internet();
                        
                        $internet->webserver_showPage();
                        
                        break;
                    default:
                        die("errrro");
                        break;
                }

            }

        } elseif($system->issetGet('id')){
            
            $idInfo = $system->verifyNumericGet('id');
            
            if($idInfo['IS_NUMERIC'] == 1){
            
                $software->viewSoftware($idInfo['GET_VALUE'], '', '');
                    
            } else {
                $system->handleError('INVALID_ID', 'software.php');
            }
            
        } else {

            $system->handleError('INVALID_GET', 'software.php');

        }

    } else {

        if($system->issetGet('page')){
            
            $pageInfo = $system->verifyStringGet('page');
            
            if($pageInfo['IS_NUMERIC'] == '0'){
                
                if($system->issetGet('action')){
                    
                    $actionInfo = $system->verifyStringGet('action');
                    
                    if($actionInfo['IS_NUMERIC'] == '0'){
                    
                        if($system->issetGet('id')){

                            $idInfo = $system->verifyNumericGet('id');

                            if($idInfo['IS_NUMERIC'] == '1'){

                                switch($actionInfo['GET_VALUE']){
                                    
                                    case 'upload':
                                        
                                        $hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                                        
                                        if($software->issetSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC')){
                                            
                                            $softInfo = $software->getSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC');
                                            
                                            if($hardware->xhd >= $softInfo->softsize + $hardware->getXHDUsage()){
                                                
                                                $process->studyProcess('UPLOAD_XHD', 'local', 'VPC');
                                                
                                            } else {

                                                $system->handleError('BAD_XHD', 'software?page=external');
                                                
                                            }
                                            
                                        } else {
                                            
                                            $system->handleError('NO_HAVE_SOFT', 'software?page=external');
                                            
                                        }
                                        
                                        break;
                                    case 'download':
                                        
                                        if(!$software->issetSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC')){
                                        
                                            if($software->issetExternalSoftware($idInfo['GET_VALUE'])){

                                                $softInfo = $software->getExternalSoftware($idInfo['GET_VALUE']);
                                                $hddUsage = $hardware->calculateHDDUsage($_SESSION['id'], 'VPC');

                                                if($hddUsage['AVAILABLE'] >= $softInfo->softsize){

                                                    $process->studyProcess('DOWNLOAD_XHD', 'local', 'VPC');

                                                } else {

                                                    $system->handleError('Insufficient disk space.', 'software?page=external');

                                                }

                                            } else {

                                                $system->handleError('NO_HAVE_SOFT', 'software?page=external');

                                            }

                                        } else {
                                            $system->handleError('SOFT_ALREADY_HAVE', 'software?page=external');
                                        }
                                        
                                        break;
                                    case 'del':

                                        if($software->issetExternalSoftware($idInfo['GET_VALUE'])){

                                            $softInfo = $software->getExternalSoftware($idInfo['GET_VALUE']);
  
                                            $process->studyProcess('DELETE_XHD', 'local', 'VPC');

                                        } else {

                                            $system->handleError('NO_HAVE_SOFT', 'software?page=external');

                                        }

                                        
                                        break;
                                    default:
                                        $system->handleError('INVALID_GET', 'software.php');
                                        break;
                                    
                                }
                                
                            } else {
                                $system->handleError('INVALID_GET', 'software?page=external');
                            }

                        } else {

                            if($actionInfo['GET_VALUE'] == 'format'){
                                echo 'format';
                            } else {
                                $system->handleError('INVALID_GET', 'software?page=external');
                            }

                        }
                    
                    } else {
                        $system->handleError('INVALID_GET', 'software?page=external');
                    }
                    
                } else {
                
                    if($pageInfo['GET_VALUE'] == 'external'){

                        $xhdInfo = $hardware->getXHDInfo();

                        if($xhdInfo['TOTAL'] > '0'){

                            $software->external_listSoftware();

                        } else {

                            $system->handleError('You do not have an external hard drive. Buy one at the <a href="hardware?opt=xhd">hardware store</a>.', 'software.php');

                        }

                    } else {
                        $system->handleError('INVALID_GET', 'software.php');
                    }

                }
                
            } else {
                $system->handleError('INVALID_GET', 'software.php');
            }
            
        } else {       
            
            $software->showSoftware('1', 'VPC', '');
        
        }
        
    }

    require 'template/contentEnd.php';
    
} else {        

    header("Location:logout.php");

} 

?>