<?php

//TODO: e se empatar?


$start = microtime(true);

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = 'SELECT clan_war.clanID1, clan_war.clanID2, clan_war.score1, clan_war.score2, id1.name AS name1, id2.name AS name2, clan_war.endDate, clan_war.startDate, clan_war.bounty
        FROM clan_war 
        INNER JOIN clan id1 ON clan_war.clanID1 = id1.clanID
        INNER JOIN clan id2 ON clan_war.clanID2 = id2.clanID
        WHERE TIMESTAMPDIFF(SECOND, NOW(), endDate) < 0';
$data = $pdo->query($sql)->fetchAll();

if(sizeof($data) > 0){
    
    for($i=0;$i<sizeof($data);$i++){
        
        $startDate = $data[$i]['startdate'];
        
        if($data[$i]['score1'] > $data[$i]['score2']){
            
            $winnerID = $data[$i]['clanid1'];
            $loserID = $data[$i]['clanid2'];
            $winnerScore = $data[$i]['score1'];
            $loserScore = $data[$i]['score2'];
            $winnerName = $data[$i]['name1'];
            $loserName = $data[$i]['name2'];
            
        } else {
            
            $winnerID = $data[$i]['clanid2'];
            $loserID = $data[$i]['clanid1'];
            $winnerScore = $data[$i]['score2'];
            $loserScore = $data[$i]['score1'];
            $winnerName = $data[$i]['name2'];
            $loserName = $data[$i]['name1'];
            
        }
        
        $sql = "SELECT round_ddos.attID, round_ddos.power
                FROM round_ddos
                INNER JOIN clan_ddos
                ON clan_ddos.ddosID = round_ddos.id
                WHERE clan_ddos.attackerClan = '".$winnerID."' AND victimClan = '".$loserID."'";
        $ddosList = $pdo->query($sql)->fetchAll();
        
        $ddoserArr = Array();
        $totalPower = 0;
        
        for($k=0;$k<sizeof($ddosList);$k++){
            
            $total = ceil(sizeof($ddoserArr)/2);

            $invalid = 0;
            for($a=0;$a<$total;$a++){
                
                if($ddoserArr[$a]['userID'] == $ddosList[$k]['attid']){
                    $id = $a;
                    $invalid = 1;
                    break;
                }
                                
            }

            if($invalid == 0){ //nao foi add ainda
                
                $ddoserArr[$total]['userID'] = $ddosList[$k]['attid'];
                $ddoserArr[$total]['power'] = $ddosList[$k]['power'];
                                
            } else {
                
                $ddoserArr[$id]['power'] += $ddosList[$k]['power'];
                
            }
            
            $totalPower += $ddosList[$k]['power'];
            
        }
           
        $bounty = $data[$i]['bounty'];
                
        $mostInfluent = 0;
        $split = 0;
        
        for($k=0;$k<sizeof($ddoserArr);$k++){
            
            $playerInfluence = $ddoserArr[$k]['power']/$totalPower;
            $earned = ceil($bounty * $playerInfluence);
            
            if($playerInfluence > $mostInfluent){
                $mostInfluent = $playerInfluence;
                $mostInfluentID = $ddoserArr[$k]['userID'];
            }
                        
            $sql = "SELECT bankAcc FROM bankAccounts WHERE bankUser = '".$ddoserArr[$k]['userID']."' ORDER BY cash ASC LIMIT 1";
            $bankInfo = $pdo->query($sql)->fetchAll();
            
            $sql = "UPDATE bankAccounts SET cash = cash + '".$earned."' WHERE bankAcc = '".$bankInfo['0']['bankacc']."'";
            $pdo->query($sql);
            
            $sql = "UPDATE users_stats SET moneyEarned = moneyEarned + '".$earned."' WHERE uid = '".$ddoserArr[$k]['userID']."'";
            $pdo->query($sql);
            
            $split++;
            
        }
        
        

        $sql = "SELECT login FROM users WHERE id = '".$mostInfluentID."'";
        $playerName = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->login;
        
        $title = $winnerName.' won clan battle against '.$loserName;
        
        $brief = 'The war against <a href="clan?id='.$winnerID.'">'.$winnerName.'</a> and <a href="clan?id='.$loserID.'">'.$loserName.'</a> reached its end at '.substr($data[$i]['enddate'], 0, -3).'<br/>
                The total score was <font color="green"><b>'.number_format($winnerScore,'0', '.', ',').'</b></font> for <a href="clan?id='.$winnerID.'">'.$winnerName.'</a>,
                and <font color="red"><b>'.number_format($loserScore,'0', '.', ',').'</b></font> for <a href="clan?id='.$loserID.'">'.$loserName.'</a>. <br/> The most influent player was 
                <a href="profile?id='.$mostInfluentID.'">'.$playerName.'</a>, who scored alone <b>'.number_format(($winnerScore * $mostInfluent),'0', '.', ',').'</b>
                points. The total bounty for this clan war was <font color="green">$'.number_format($bounty,'0', '.', ',').'</font>, split between '.$split.' players.';
        
        $sql = 'INSERT INTO news (id, author, title, content, date)
                VALUES (\'\', \'-5\', :title, :content, NOW())';
        $data2 = $pdo->prepare($sql);
        $data2->execute(array(':title' => $title, ':content' => $brief));
        
        $sql = "UPDATE clan
                INNER JOIN clan_stats
                ON clan.clanID = clan_stats.cid
                SET clan_stats.won = clan_stats.won + 1, clan.power = clan.power + '". ($totalPower + $loserScore)/8 ."'
                WHERE clan.clanID = '".$winnerID."'
                ";
        $pdo->query($sql);
        
        $sql = "UPDATE clan_stats SET lost = lost + 1 WHERE cid = '".$loserID."'";
        $pdo->query($sql);

        
        $sql = "DELETE FROM clan_war WHERE (clanID1 = '".$winnerID."' and clanID2 = '".$loserID."') OR (clanID2 = '".$winnerID."' and clanID1 = '".$loserID."')";
        //$pdo->query($sql);
        
        //Add to clan war history
        $sql = "INSERT INTO clan_war_history (id, idWinner, idLoser, scoreWinner, scoreLoser, startDate, endDate, bounty)
                VALUES ('', '".$winnerID."', '".$loserID."', '".$winnerScore."', '".$loserScore."', '".$startDate."', NOW(), '".$bounty."')";
        $pdo->query($sql);
        $warID = $pdo->lastInsertId();
        
        $sql = "SELECT attackerClan, victimClan, ddosID FROM clan_ddos WHERE (attackerClan = '".$winnerID."' AND victimClan = '".$loserID."') OR (attackerClan = '".$loserID."' AND victimClan = '".$winnerID."')";
        $data2 = $pdo->query($sql)->fetchAll();
        
        if(sizeof($data2) > 0){
            
            for($j=0; $j<sizeof($data2); $j++){
                
                $sql = "INSERT INTO clan_ddos_history (attackerClan, victimClan, ddosID, warID) 
                        VALUES ('".$data2[$j]['attackerclan']."', '".$data2[$j]['victimclan']."', '".$data2[$j]['ddosid']."', '".$warID."')";
                $pdo->query($sql);
                
            }
            
        }
        
        $sql = "DELETE FROM clan_ddos WHERE (attackerClan = '".$winnerID."' AND victimClan = '".$loserID."') OR (attackerClan = '".$loserID."' AND victimClan = '".$winnerID."')";
        //$pdo->query($sql);        

    }
        
}

echo round(microtime(true)-$start,3)*1000 .'ms';


?>
