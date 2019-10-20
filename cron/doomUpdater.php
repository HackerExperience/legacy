<?php

require_once '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = 'SELECT doomID FROM virus_doom';
$data2 = $pdo->query($sql)->fetchAll();

if(sizeof($data2) > 0){
    
    function recursiveCheck(){
        
        $pdo = PDO_DB::factory();
        
        $sql = "SELECT doomID, creatorID, clanID, TIMESTAMPDIFF(SECOND, NOW(), doomDate) AS timeLeft FROM virus_doom WHERE status = 1 ORDER BY releaseDate ASC";
        $data2 = $pdo->query($sql)->fetchAll();
    
        for($i=0;$i<sizeof($data2);$i++){
            
            if($data2[$i]['timeleft'] < 0){ //FINISH ROUND
                
                $sql = "UPDATE virus_doom SET status = 3 WHERE doomID = '".$data2[$i]['doomid']."'";
                $pdo->query($sql);
                
                require 'finishRound.php';
                
                //TODO disconnect all users

                return FALSE;
                
            } elseif($data2[$i]['timeleft'] < 60){
                
                set_time_limit(0);
                sleep($data2[$i]['timeleft'] + 1);
                
                return recursiveCheck();
                
            }
          
        }
        
        
    }
    
    recursiveCheck();
    
}

?>
