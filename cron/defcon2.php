<?php

// 2019: Why there are 2 defcon files? No idea. But the crontab uses `defcon2` and so should you.

// 2019 Bad translation:
// Objective: detect if there exists a (clan) war. A clan war exists if:
// - 2 members of clan 1 attacked member of clan 2 and at least 1 member of clan 2 countered the attack onto any member of clan 1
// - Member of clan1 attacked at least 2 members of clan2 AND at least 1 member of clan2 countered the attack onto any member of clan1
// - Member of clan1 attacked clan2's server and member of clan2 countered the attacked onto clan1's server or any of its members.
// Attacks have duration. That is, after some time the attack is "forgiven" and the war is voided.


/*
 *  Objetivo: detectar se há guerra. Há guerra se)
 *  2 membros do clan 1 atacaram 1 membro do clan2 E pelo menos 1 membro do clan2 revidou o ataque em algum membro do clan1
 *  Membro do clan1 atacou pelo menos 2 membros do clan2 E pelo menos 1 membro do clan2 revidou o ataque em algum membro do clan1
 *  Membro do clan1 atacou o clan server do clan2 e membro do clan2 retornou ataque no clan server ou em algum membro do clan1
 *  Os ataques têm duração. Isto é, depois de algum tempo o ataque é "esquecido" e a situaçãop de guerra se esvazia.
 */

$start = microtime(true);

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = 'DELETE FROM clan_defcon WHERE TIMESTAMPDIFF(DAY, attackDate, NOW()) >= 3';
$pdo->query($sql);

$k = 0;
$offset = 0;

$sql = 'SELECT COUNT(*) AS total FROM clan_defcon';
$total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

while($total > 0){

    $victimArr = $starterArr = Array();
    
    $sql = 'SELECT id, attackerID, attackerClanID, victimID, victimClanID, attackDate, clanServer FROM clan_defcon LIMIT 1 OFFSET '.$offset;
    $curAttackInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);
    $attackerClanID = $curAttackInfo->attackerclanid;

    $sql = 'SELECT victimClanID FROM clan_defcon WHERE attackerClanID = '.$attackerClanID.' GROUP BY victimClanID';
    $data = $pdo->query($sql);
    
    while($defconInfo = $data->fetch(PDO::FETCH_OBJ)){
        
        $attackerGrant = FALSE;
        
        $victimClanID = $defconInfo->victimclanid; 
        
        $sql = 'SELECT attackerID, victimID, clanServer FROM clan_defcon WHERE attackerClanID = '.$attackerClanID.' AND victimClanID = '.$victimClanID;
        $data2 = $pdo->query($sql);
        
        $k = 0;
        while($attackInfo = $data2->fetch(PDO::FETCH_OBJ)){            
            
            if(!array_key_exists($attackInfo->victimid, $victimArr)){
                $victimArr[$attackInfo->victimid] = 1;
                if($attackInfo->clanserver == 0){
                    $starterArr[sizeof($starterArr)] = $attackInfo->victimid;
                }
            }
            
            if(!array_key_exists($attackInfo->attackerid, $starterArr)){
                $starterArr[sizeof($starterArr)] = $attackInfo->attackerid;
                $k++;
            }
            
            if($attackInfo->clanserver == 1){
                $attackerGrant = TRUE;
                break;
            } elseif(sizeof($victimArr) >= 2){
                $attackerGrant = TRUE;
                break;
            } elseif($k >= 2){
                $attackerGrant = TRUE;
                break;
            }
            
        }

        
        
        if($attackerGrant){
                        
            $victimGrant = FALSE;
            
            $sql = 'SELECT COUNT(*) AS total FROM clan_defcon WHERE attackerClanID = '.$victimClanID.' AND victimClanID = '.$attackerClanID.' LIMIT 1';
            
            if($pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 1){
                $victimGrant = TRUE;
            }
            
            if($victimGrant){
         
                for($i = 0; $i < sizeof($starterArr); $i++){
                    exec('/usr/bin/env python /var/www/python/badge_add.py user '.$starterArr[$i].' 62');
                }

                $duration = 2;
                $score1 = $score2 = 0;
                
                $sql = "INSERT INTO clan_war (clanID1, clanID2, startDate, endDate, score1, score2)
                        VALUES ('".$attackerClanID."', '".$victimClanID."', NOW(), DATE_ADD(NOW(), INTERVAL '".$duration."' DAY), '".$score1."', '".$score2."')";
                $pdo->query($sql);                
                
                $sql = 'DELETE FROM clan_defcon 
                        WHERE 
                            (attackerClanID = '.$victimClanID.' AND victimClanID = '.$attackerClanID.') OR 
                            (attackerClanID = '.$attackerClanID.' AND victimClanID = '.$victimClanID.')';
                $pdo->query($sql);
                
                $sql = 'SELECT COUNT(*) AS total FROM clan_defcon';
                $total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;                
                
            }
            
        }
        
    }

    $offset++;
    $total--;
        
}

echo round(microtime(true)-$start,3)*1000 .'ms';

?>
