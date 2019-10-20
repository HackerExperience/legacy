<?php

require '../connect.php';

$sql = "SELECT region, id FROM storyline_launches WHERE effect = 0 AND status = 3 ORDER BY endTime DESC";
$data = $pdo->query($sql)->fetchAll();

if(count($data) > 0){
    
    $sql = "UPDATE storyline_launches SET effect = '1' WHERE id = '".$data['0']['id']."'";
    $pdo->query($sql);
    
    $region = $data['0']['region'];
            
    //disable all npcs
    $sql = "UPDATE npc SET downUntil = '2020-01-01 12:00:00' WHERE region = '".$region."'";
    $pdo->query($sql);
    
    //disable all bank accounts
    $sql = "SELECT id FROM npc WHERE npcType = 1 AND region = '".$region."'";
    $data = $pdo->query($sql);
    
    $bankArr = Array();
    
    $i = 0;
    while($bankInfo = $data->fetch(PDO::FETCH_OBJ)){
        
        $bankArr[$i]['id'] = $bankInfo->id;
        
        $i++;
    }
    
    for($a=0;$a<$i;$a++){
        
        $sql = "DELETE FROM bankAccounts WHERE bankID = '".$bankArr[$a]['id']."'";
        echo $sql;
        $pdo->query($sql);
        
    }
    
}

?>
