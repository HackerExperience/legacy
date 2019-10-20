<?php

// 2019 Bad translation:
// Objective: detect if there exists a (clan) war. A clan war exists if:
// - Member of clan1 attacked at least 2 members of clan2 AND at least 1 member of clan2 countered the attack onto any member of clan1
// - Member of clan1 attacked clan2's server and member of clan2 countered the attacked onto clan1's server or any of its members.
// Attacks have duration. That is, after some time the attack is "forgiven" and the war is voided.


/*
 *  Objetivo: detectar se há guerra. Há guerra se)
 *  Membro do clan1 atacou pelo menos 2 membros do clan2 E pelo menos 1 membro do clan2 revidou o ataque em algum membro do clan1
 *  Membro do clan1 atacou o clan server do clan2 e membro do clan2 retornou ataque no clan server ou em algum membro do clan1
 *  Os ataques têm duração. Isto é, depois de algum tempo o ataque é "esquecido" e a situaçãop de guerra se esvazia.
 */

$start = microtime(true);

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = "SELECT id FROM clan_defcon";
$info = $pdo->query($sql)->fetchAll();

if($info['0']['id'] > 0){
    
    $sql = "SELECT id, attackerID, attackerClanID, victimID, victimClanID, attackDate, clanServer FROM clan_defcon";
    $data = $pdo->query($sql);
    
    $defconArr = Array();
    $i = 0;
    while($defconInfo = $data->fetch(PDO::FETCH_OBJ)){
        $i++;
        
        $defconArr[$i]['id'] = $defconInfo->id;
        $defconArr[$i]['att'] = $defconInfo->attackerid;
        $defconArr[$i]['attclan'] = $defconInfo->attackerclanid;
        $defconArr[$i]['vic'] = $defconInfo->victimid;
        $defconArr[$i]['vicclan'] = $defconInfo->victimclanid;
        $defconArr[$i]['attdate'] = $defconInfo->attackdate ;
        $defconArr[$i]['clanserver'] = $defconInfo->clanserver;
        
    }
    
    $total = $i;

    $modifiedArr = Array();
    $usedArr = Array();
    $possibleArr = Array();
    $possibleArr['TOTAL'] = 0;
    
    for($k=1;$k<$total+1;$k++){
        
        for($z=1;$z<$total+1;$z++){
            
            if(array_key_exists($z, $usedArr) && $z != $k){

                if(($defconArr[$k]['attclan'] == $usedArr[$z]['CLAN_ID']) && ($defconArr[$k]['vicclan'] == $usedArr[$z]['CLAN_VIC'])){

                    if(!array_key_exists($usedArr[$z]['CLAN_ID'], $modifiedArr)){
                    
                        $possibleArr[$possibleArr['TOTAL']+1]['CLAN_ID'] = $usedArr[$z]['CLAN_ID'];
                        $possibleArr[$possibleArr['TOTAL']+1]['CLAN_VIC'] = $usedArr[$z]['CLAN_VIC'];
                        $possibleArr['TOTAL']++;

                        $modifiedArr[$usedArr[$z]['CLAN_ID']] = 1;
                    
                    }
                    
                }
 
            } else {
                
                $usedArr[$k]['CLAN_ID'] = $defconArr[$k]['attclan'];
                $usedArr[$k]['ATTACKER_ID'] = $defconArr[$k]['att']; 
                $usedArr[$k]['CLAN_VIC'] = $defconArr[$k]['vicclan'];
                $usedArr[$k]['ATTACKER_VIC'] = $defconArr[$k]['vic'];
                $usedArr[$k]['ATTACK_DATE'] = $defconArr[$k]['attdate'];
                $usedArr[$k]['CLAN_SERVER'] = $defconArr[$k]['clanserver'];
                
            }

        }

    }
    
    $war = Array();
    $war['TOTAL'] = 0;

    for($i=1;$i<sizeof($possibleArr);$i++){
        
        $invalid = 0;
        for($z=1;$z<sizeof($usedArr)+1;$z++){
            
            if(($possibleArr[$i]['CLAN_VIC'] == $usedArr[$z]['CLAN_ID']) && ($possibleArr[$i]['CLAN_ID'] == $usedArr[$z]['CLAN_VIC'])){
                
                for($k=1;$k<sizeof($war);$k++){
                    
                    if(($war[$k]['CLANS'] == $possibleArr[$i]['CLAN_ID'].'x'.$possibleArr[$i]['CLAN_VIC']) || 
                       ($war[$k]['CLANS'] == $possibleArr[$i]['CLAN_VIC'].'x'.$possibleArr[$i]['CLAN_ID'])){
                        
                        $invalid = 1;
                        
                        break;
                    }
                    
                }
                
                if($invalid == 0){
                    
                    $war[$war['TOTAL']+1]['CLANS'] = $possibleArr[$i]['CLAN_ID'].'x'.$possibleArr[$i]['CLAN_VIC'];
                    $war['TOTAL']++;
                    
                }
                
            }
            
        }
        
    }

    $total = sizeof($usedArr);
    
    for($a=1;$a<sizeof($war);$a++){
        
        list($id1, $id2) = explode('x', $war[$a]['CLANS']);

        for($i=1;$i<$total+1;$i++){
            
            if(array_key_exists($i, $usedArr)){

                if(($usedArr[$i]['CLAN_ID'] == $id1 && $usedArr[$i]['CLAN_VIC'] == $id2) || 
                   ($usedArr[$i]['CLAN_ID'] == $id2 && $usedArr[$i]['CLAN_VIC'] == $id1)){
                    
                    unset($usedArr[$i]);
                    
                }

            }
            
        }
        
    }

    $pdo->query('DELETE FROM clan_defcon');
    $pdo->query('ALTER TABLE `clan_defcon` AUTO_INCREMENT = 1');    
    
    if(sizeof($usedArr) > 0){
    
        shuffle($usedArr);

        foreach($usedArr as $key => $value){
            $sqlArr[$key+1] = $value;
        }

        for($i=1;$i<sizeof($sqlArr)+1;$i++){

            $sql = "INSERT INTO clan_defcon (id, attackerID, attackerClanID, victimID, victimClanID, attackDate, groupServer)
                    VALUES ('', '".$sqlArr[$i]['ATTACKER_ID']."', '".$sqlArr[$i]['CLAN_ID']."', '".$sqlArr[$i]['ATTACKER_VIC']."', '".$sqlArr[$i]['CLAN_VIC']."', '".$sqlArr[$i]['ATTACK_DATE']."', '".$sqlArr[$i]['CLAN_SERVER']."')";
            $pdo->query($sql);

        }
    
    }
    
}

for($a=1;$a<sizeof($war);$a++){

    list($id1, $id2) = explode('x', $war[$a]['CLANS']);

    $sql = "SELECT endDate FROM clan_war WHERE (clanID1 = '".$id1."' AND clanID2 = '".$id2."') OR (clanID1 = '".$id2."' AND clanID2 = '".$id1."') LIMIT 1";
    $data = $pdo->query($sql)->fetchAll();
    
    if(sizeof($data) == 1){

        $interval = 1; //add 1 day

        $sql = "UPDATE clan_war SET endDate = DATE_ADD(endDate, INTERVAL '".$interval."' DAY) WHERE (clanID1 = '".$id1."' AND clanID2 = '".$id2."') OR (clanID1 = '".$id2."' AND clanID2 = '".$id1."') LIMIT 1";
        $pdo->query($sql);
        
    } else {

        $score1 = 0;
        $score2 = 0;        
        
        $sql = "SELECT clan_ddos.attackerClan, round_ddos.power
                FROM clan_ddos
                INNER JOIN round_ddos
                ON round_ddos.id = clan_ddos.ddosID
                WHERE (attackerClan = '".$id1."' AND victimClan = '".$id2."') OR (attackerClan = '".$id2."' AND victimClan = '".$id1."')";
        $data = $pdo->query($sql)->fetchAll();

        if(sizeof($data) > 0){

            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['attackerclan'] == $id1){
                    
                    $score1 += $data[$i]['power'];
                    
                } else {
                    
                    $score2 += $data[$i]['power'];
                    
                }
                
            }
            
        }

        $duration = 2; //duration

        $sql = "INSERT INTO clan_war (clanID1, clanID2, startDate, endDate, score1, score2)
                VALUES ('".$id1."', '".$id2."', NOW(), DATE_ADD(NOW(), INTERVAL '".$duration."' DAY), '".$score1."', '".$score2."')";
        $pdo->query($sql);
        
    }
    
}

echo round(microtime(true)-$start,3)*1000 .'ms';

?>
