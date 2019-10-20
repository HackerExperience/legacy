<?php

require '/var/www/classes/Session.class.php';

$session = new Session();

if($session->issetLogin()){

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        require '/var/www/classes/Player.class.php';
        require '/var/www/classes/PC.class.php';
        require '/var/www/classes/Internet.class.php';
        require '/var/www/classes/Process.class.php';
        
        $player = new Player();
        $internet = new Internet();
        $software = new SoftwareVPC();
        $hardware = new HardwareVPC();
        $process = new Process();

        if(isset($_POST['uid']) && isset($_POST['content'])){
            
            $uid = $_POST['uid'];
            $content = $_POST['content'];
            
            if(!ctype_digit($uid)){
                die("Invalid user ID");
            }
            
            if(empty($content)){
                die("Insert web server text");
            }
            
            $system = new System();
            if(!$system->validate($content, 'text')){
                echo "<br/>".$content."<br/>";
                die("<br/><strong>Invalid web server text</strong>");
            }
            
            require '/var/www/classes/Purifier.class.php';
            $purifier = new Purifier();
            $purifier->set_config('text');

            $content = $purifier->purify($content);
            
            if(strlen($content) == 0){
                exit("Please insert some valid text");
            }
                        
            if($uid != $_SESSION['id']){

                $redirect = 'internet?view=software';
                $victimID = $uid;
                $host = 'remote';
                
                $valid = 0;
                if($session->issetInternetSession()){
                   
                    $victimInfo = $player->getIDByIP($_SESSION['LOGGED_IN'], 'VPC');

                    if($victimInfo['0']['existe'] == 1){
                        
                        if($victimInfo['0']['id'] == $uid){
                            $valid = 1;
                        }
                        
                    }
                    
                }
                
                if($valid == 0){
                    die("For some reason, this web server edition is not valid. Sorry");
                }
                
            } else {
                $redirect = 'software.php';
                $victimID = 0;
                $host = 'local';
            }
            
            if(!$player->isPremium($uid)){
                die("This user is not premium");
            }
            
            $webServerSoftware = $software->getBestSoftware(18, $uid, 'VPC');
            
            if($webServerSoftware['0']['exists'] == 0){
                die("The user doesnt have webserver software");
            }
            
            $softID = $webServerSoftware['0']['id'];
            
            $softInfo = $software->getSoftware($softID, $uid, 'VPC');

            if($softInfo->softhidden != 0){
                die("Unhid before running the webserver");
            }

            if(!$software->isInstalled($softID, $uid, 'VPC')){

                $hardware = new HardwareVPC();
                $ramInfo = $hardware->calculateRamUsage($uid, 'VPC');

                if($ramInfo['AVAILABLE'] < $softInfo->softram){
                    die("Not enough ram to run the server");
                }

            }

            if($process->newProcess($_SESSION['id'], 'INSTALL_WEBSERVER', $victimID, $host, $softID, '', $content, 0)){
                
                header("Location:processes.php");
                exit();
                
                $process->getProcessInfo($_SESSION['pid']);
                echo "<tr><td>";
                $process->showProcess();
                echo "</td></tr>";
                
            } else {
                
                if (!$session->issetMsg()) {

                    header("Location:processes.php");
                    exit();
                    
                    if ($pid != NULL) {

                        $session->processID('add', $pid);
                        $process->getProcessInfo($_SESSION['pid']);
                        $session->processID('del');

                        echo "<tr><td>";
                        $process->showProcess();
                        echo "</td></tr>";
                    } else {
                        die("Process not found! :S");
                    }
                } else {
                    header("Location:".$redirect);
                }
                
            }
            
        } else {
            echo 'bad';
        }
        
    } else {
        
        $session->addMsg('Invalid get.', 'error');
        header("Location:software.php");
        
    }
    
} else {
    header("Location:index.php");
}

?>
