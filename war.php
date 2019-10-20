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
die("TODO");

$hardware = new HardwareVPC();
$process = new Process();

require 'template/templateTop.php';
require_once 'gameInfo.php';

if($session->issetLogin()){

    require_once '/var/www/classes/Storyline.class.php';
    $storyline = new Storyline();
    
    $storylineProgress = $storyline->returnStorylineProgress();
    
    $progress = explode(";", $storylineProgress);
    
    
    
    if($progress['2'] == 1){
        
        if($system->issetGet('action')){
            
            $actionInfo = $system->switchGet('action', 'list', 'launch');
            
            if($actionInfo['ISSET_GET'] == '1'){
                
                switch($actionInfo['GET_VALUE']){
                    
                    case 'list':
                        
                        break;
                    case 'launch':
                        
                        if($storyline->isUserLaunching($_SESSION['id']) == FALSE){
                            
                            $launcherInfo = $storyline->launcherInfo($_SESSION['id']);
                            
                            if($launcherInfo['ISSET'] == '1'){
                                
                                $ramInfo = $hardware->calculateRamUsage($_SESSION['id'], 'VPC');
                                
                                if($ramInfo['AVAILABLE'] >= $launcherInfo['RAM']){

                                    if($system->issetGet('region')){
                                        
                                        $regionInfo = $system->verifyNumericGet('region');
                                        
                                        if($regionInfo['IS_NUMERIC'] == 1){
                                                          
                                            if($regionInfo['GET_VALUE'] > 0 || $regionInfo['GET_VALUE'] < 9){
                                            
                                                $regions = explode("/", $_SESSION['CORP']['REGIONS']);
                                                
                                                if($regionInfo['GET_VALUE'] != $regions['0'] && $regionInfo['GET_VALUE'] != $regions['1']){

                                                    $session->newQuery();
                                                    $sqlQuery = "INSERT INTO software_running (id, softID, userID, ramUsage, isNPC) VALUES ('', ?, ?, ?, '0')";
                                                    $sqlReg = $pdo->prepare($sqlQuery);
                                                    $sqlReg->execute(array($launcherInfo['ID'], $_SESSION['id'], $launcherInfo['RAM']));

                                                    $timeNow = $process->currentTimeToSec('', '');
                                                    $endTime = $timeNow + $launchDuration;

                                                    $session->newQuery();
                                                    $sql = "INSERT INTO storyline_launches (id, userID, status, startTime, endTime, region) VALUES 
                                                            ('', '".$_SESSION['id']."', '1', '".$timeNow."', '".$endTime."', '".$regionInfo['GET_VALUE']."')";
                                                    $pdo->query($sql);

                                                    $storyline->updateLauncherProgress('new');

                                                    echo 'EMP LAUNCHED against '.$storyline->returnRegionByID($regionInfo['GET_VALUE']).'!';

                                                } else {
                                                    echo 'You cant attack your own region!';
                                                }
                                            
                                            } else {
                                                die("Invalid region");
                                            }
                                        
                                        } else {
                                            die("Invalid region!");
                                        }

                                    } else {
                                    
                                        ?>
                                        
                                        <form action="" method="GET">

                                            <input type="hidden" name="action" value="launch">
                                             
                                            Choose a region to attack: <?php $storyline->regionForm(); ?><br/>
                                            <input type="submit" value="Launch">

                                        </form>
                                        
                                        <?php

                                    }
                                    
                                } else {
                                    die("not enough ram to run launcher");
                                }
                                                     
                            } else {
                                die("You dont have a launcher");
                            }
                            
                        } else {
                            echo 'You already launched the EMPs.';
                        }
                        
                        break;
                    
                }
                
            } else {
                die("Invalid get");
            }
            
        } elseif($system->issetGet('id')){
            
            $idInfo = $system->verifyStringGet('id');
            
            if($idInfo['IS_NUMERIC'] == '1'){
                
                echo 'id';
                
            } else {
                die("Invalid ID");
            }
            
        } else {
            
            $storyline->listLaunches();
            
        }
        
        
        
    } else {
        
        if($progress['1'] == 1){
            $step = 2;
        } elseif($progress['0'] == 1){
            $step = 1;
        } else {
            $step = 0;
        }

        switch($step){
            
            case 0:
                echo 'No big war news';
                break;
            case 1:
                echo 'NASA vulnerable?';
                break;
            case 2:
                echo 'NASA vulnerable!';
                break;
            
        }
        
    }
    
    require 'template/templateBot.php';

} else {        

    header("Location:logout.php");

} 

?>