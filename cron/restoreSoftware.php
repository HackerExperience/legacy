<?php

//function: restore npc software according to originalsoftware table on mysql

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

//talvez dá pra otimizar com um left / right join aqui..

$sql = "SELECT id, npcID, softName, softVersion, softRam, softSize, softType FROM software_original";
$query = $pdo->query($sql);

while($row = $query->fetch(PDO::FETCH_OBJ)){
 
    $newSql = "SELECT id FROM software WHERE userID = $row->npcid AND isNPC = 1 AND softName = '".$row->softname."' AND softVersion = $row->softversion";
    $newQuery = $pdo->query($newSql)->fetchAll();

    if(count($newQuery) == 0){

        $sqlQuery = "INSERT INTO software (id, softHidden, softHiddenWith, softLastEdit, softName, softSize,
            softType, softVersion, userID, isNPC, softRam) VALUES ('', '0', '0', NOW(), ?, ?, ?, ?, ?, '1', ?)";
        $sqlDown = $pdo->prepare($sqlQuery);
        $sqlDown->execute(array($row->softname, $row->softsize, $row->softtype, $row->softversion, $row->npcid, $row->softram));

    }

}

?>
