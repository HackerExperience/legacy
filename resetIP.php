<?php

require '/var/www/classes/Session.class.php';

$session = new Session();

if($session->issetLogin()){
    
    $redirectToISP = 'internet';
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        
        require '/var/www/classes/Player.class.php';
        require '/var/www/classes/Finances.class.php';
        require '/var/www/classes/Process.class.php';
        
        $player = new Player();
        $finances = new Finances();
        $process = new Process();
        $npc = new NPC();
        
        $ispIP = $npc->getNPCByKey('ISP')->npcip;
        $redirectToISP .= '?ip='.long2ip($ispIP);
        
        $ipInfo = $player->ip_info();

        if($ipInfo['NEXT_RESET'] != 0 && !isset($_POST['acc'])){
            
            $session->addMsg('Calm down, boy, you cant reset your IP yet.', 'error');
            header("Location:$redirectToISP");
            exit();
            
        }
        
        if($ipInfo['PRICE'] > 0){
        
            if(!isset($_POST['acc'])){
                
                $session->addMsg('You sir, we dont work for free, I need da accountz', 'error');
                header("Location:$redirectToISP");
                exit();
                
            }
            
            $acc = $_POST['acc'];

            if(!ctype_digit($acc)){
                $session->addMsg('Sir, this account is not valid.', 'error');
                header("Location:$redirectToISP");
                exit();
            } 
            
            if(!$finances->isPlayerAccount($acc)){
                $session->addMsg('Sir, this account is not valid.', 'error');
                header("Location:$redirectToISP");
                exit();
            }
            
            if($finances->totalMoney() < $ipInfo['PRICE']){

                $session->addMsg('You dont have enough money for this, sir', 'error');
                header("Location:$redirectToISP");
                exit();     
                
            }

        } else {
            $acc = '0';
        }
            //exit("aaa");
        if($process->newProcess($_SESSION['id'], 'RESET_IP', '', 'local', '', $acc, '', 0)){
            
            header("Location:$redirectToISP");
            
        } else {
            
            if (!$session->issetMsg()) {

                $pid = $process->getPID($_SESSION['id'], 'RESET_IP', '', 'local', '', $acc, '', 0);
                header("Location:processes?id=".$pid);

            } else {

                header("Location:$redirectToISP");

            }            
            
        }
        
    } else {
        
        $session->addMsg('Invalid get', 'error');
        header("Location:$redirectToISP");
        
    }
    
} else {
    header("Location:index.php");
}

?>
