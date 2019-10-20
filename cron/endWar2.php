<?php

// 2019: TODO: What if there's a tie?
//TODO: e se empatar?


$start = microtime(true);

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = 'SELECT COUNT(*) AS total
        FROM clan_war 
        WHERE TIMESTAMPDIFF(SECOND, NOW(), endDate) < 0';
$total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

if($total > 0){
    
    $sql = 'SELECT 
                w.clanID1, w.clanID2, w.score1, w.score2, w.endDate, w.startDate, w.bounty,
                c1.name AS name1, c2.name AS name2
            FROM clan_war w 
            INNER JOIN clan c1 ON w.clanID1 = c1.clanID
            INNER JOIN clan c2 ON w.clanID2 = c2.clanID
            WHERE TIMESTAMPDIFF(SECOND, NOW(), endDate) < 0';
    $warInfo = $pdo->query($sql)->fetchAll();
    
    for($i=0;$i<sizeof($warInfo);$i++){
        
        $startDate = $warInfo[$i]['startdate'];
        $endDate = $warInfo[$i]['enddate'];
        
        if($warInfo[$i]['score1'] > $warInfo[$i]['score2']){
            
            $winnerID = $warInfo[$i]['clanid1'];
            $loserID = $warInfo[$i]['clanid2'];
            $winnerScore = $warInfo[$i]['score1'];
            $loserScore = $warInfo[$i]['score2'];
            $winnerName = $warInfo[$i]['name1'];
            $loserName = $warInfo[$i]['name2'];
            
        } elseif($warInfo[$i]['score1'] < $warInfo[$i]['score2']) {
            
            $winnerID = $warInfo[$i]['clanid2'];
            $loserID = $warInfo[$i]['clanid1'];
            $winnerScore = $warInfo[$i]['score2'];
            $loserScore = $warInfo[$i]['score1'];
            $winnerName = $warInfo[$i]['name2'];
            $loserName = $warInfo[$i]['name1'];
            
        } else {
            continue;
        }
        
        $sql = "SELECT r.attID, r.power
                FROM round_ddos r
                INNER JOIN clan_ddos d
                ON d.ddosID = r.id
                WHERE 
                    d.attackerClan = '".$winnerID."' AND 
                    d.victimClan = '".$loserID."' AND 
                    TIMESTAMPDIFF(SECOND, r.date, '".$startDate."') < 0";
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
           
        $bounty = $warInfo[$i]['bounty'];
        $mostInfluent = 0;
        $split = 0;
        
        $earnedArr = Array();
        
        for($k=0;$k<sizeof($ddoserArr);$k++){
            
            $playerInfluence = $ddoserArr[$k]['power']/$totalPower;
            $earned = ceil($bounty * $playerInfluence);
            $earnedArr[$ddoserArr[$k]['userID']] = $earned;
            
            if($playerInfluence > $mostInfluent){
                $mostInfluent = $playerInfluence;
                $mostInfluentID = $ddoserArr[$k]['userID'];
            }
                      
            $sql = "SELECT bankAcc FROM bankAccounts WHERE bankUser = '".$ddoserArr[$k]['userID']."' ORDER BY cash ASC LIMIT 1";
            $bankacc = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->bankacc;
            
            $sql = "UPDATE bankAccounts SET cash = cash + '".$earned."' WHERE bankAcc = '".$bankacc."'";
            $pdo->query($sql);
            
            $sql = "UPDATE users_stats SET moneyEarned = moneyEarned + '".$earned."' WHERE uid = '".$ddoserArr[$k]['userID']."'";
            $pdo->query($sql);
            
            $split++;

        }
        

        
        // 2019: Updates related to the end of the clan war
        //ATUALIZAÇÕES RELATIVAS AO FIM DA CLAN WAR \/
        
        $sql = "UPDATE clan
                INNER JOIN clan_stats
                ON clan.clanID = clan_stats.cid
                SET clan_stats.won = clan_stats.won + 1, clan.power = clan.power + '". $totalPower ."'
                WHERE clan.clanID = '".$winnerID."'
                ";
        $pdo->query($sql);
        
        $sql = "UPDATE clan_stats SET lost = lost + 1 WHERE cid = '".$loserID."'";
        $pdo->query($sql);
        
        $sql = "DELETE FROM clan_war WHERE (clanID1 = '".$winnerID."' and clanID2 = '".$loserID."') OR (clanID2 = '".$winnerID."' and clanID1 = '".$loserID."')";
        $pdo->query($sql);
        
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
        
        // 2019: "Social" updates notifying the end of the war
        //ATUALIZAÇÕES "SOCIAIS" AVISANDO O FIM DA GUERRA \/
        
        $sql = "SELECT login FROM users WHERE id = '".$mostInfluentID."' LIMIT 1";
        $playerName = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->login;
        
        $title = $winnerName.' won clan battle against '.$loserName;
        
        $brief = 'The war against <a href="clan?id='.$winnerID.'">'.$winnerName.'</a> and <a href="clan?id='.$loserID.'">'.$loserName.'</a> reached its end at '.substr($endDate, 0, -3).'<br/>
                The total score was <font color="green"><b>'.number_format($winnerScore,'0', '.', ',').'</b></font> for <a href="clan?id='.$winnerID.'">'.$winnerName.'</a>,
                and <font color="red"><b>'.number_format($loserScore,'0', '.', ',').'</b></font> for <a href="clan?id='.$loserID.'">'.$loserName.'</a>. <br/> The most influent player was 
                <a href="profile?id='.$mostInfluentID.'">'.$playerName.'</a>, who scored alone <b>'.number_format(($winnerScore * $mostInfluent),'0', '.', ',').'</b>
                points. The total bounty for this clan war was <font color="green">$'.number_format($bounty,'0', '.', ',').'</font>, split between '.$split.' players.';
        
        $sql = 'INSERT INTO news (id, author, title, content, date)
                VALUES (\'\', \'-5\', :title, :content, NOW())';
        $data2 = $pdo->prepare($sql);
        $data2->execute(array(':title' => $title, ':content' => $brief));
        
        $newsID = $pdo->lastInsertId();
        
        $sql = "INSERT INTO news_history (newsID, info1, info2) 
                VALUES ('".$newsID."', '".$winnerID."', '".$bounty."')";
        $pdo->query($sql);
        
        $sql = "SELECT r.attID, clan_users.clanID, users.login
                FROM round_ddos r
                INNER JOIN clan_ddos d
                ON d.ddosID = r.id
                INNER JOIN clan_users
                ON r.attID = clan_users.userID
                INNER JOIN users
                ON users.id = r.attID
                WHERE 
                    (d.attackerClan = '".$winnerID."' AND d.victimClan = '".$loserID."') OR 
                    (d.attackerClan = '".$loserID."' AND d.victimClan = '".$winnerID."')";
        $usersInvolved = $pdo->query($sql)->fetchAll();
        
        $from = -5;
        $type = 1;
        
        $sentArr = Array();
        
        for($k = 0; $k < sizeof($usersInvolved); $k++){

            if(!array_key_exists($usersInvolved[$k]['attid'], $sentArr)){
            
                $to = $usersInvolved[$k]['attid'];

                if($usersInvolved[$k]['clanid'] == $winnerID){
                    
                    $subject = 'We won the clan battle against '.$loserName;
                    $text = 'Yay, '.$usersInvolved[$k]['login'].'! We won the battle against <a href="clan?id='.$loserID.'">'.$loserName.'</a>. You earned <span class="green">$'.number_format($earnedArr[$usersInvolved[$k]['attid']]).'</span>!! 
                            Check our name at <a href="news?id='.$newsID.'">the news</a>.';
                    
                } else {
                    
                    $subject = 'Clan battle lost';
                    $text = 'Hey, '.$usersInvolved[$k]['login'].'. I\'m sad to say that we lost the clan battle against <a href="clan?id='.$winnerID.'">'.$winnerName.'</a>.
                             You can see more information of the battle on <a href="news?id='.$newsID.'">the news</a>.';
                    
                }

                $sentArr[$usersInvolved[$k]['attid']] = 1;
                
                $sql = "INSERT INTO mails (id, mails.from, mails.to, mails.type, subject, text, dateSent) VALUES ('', ?, ?, ?, ?, ?, NOW())";
                $sqlMail = $pdo->prepare($sql);
                $sqlMail->execute(array($from, $to, $type, $subject, $text));
                
                exec('/usr/bin/env python /var/www/python/badge_add.py user '.$to.' 60');
                
            }
            
        }
        
        $sql = "DELETE FROM clan_ddos WHERE (attackerClan = '".$winnerID."' AND victimClan = '".$loserID."') OR (attackerClan = '".$loserID."' AND victimClan = '".$winnerID."')";
        $pdo->query($sql);
        
        exec('/usr/bin/env python /var/www/python/badge_add.py user '.$mostInfluentID.' 61');
        exec('/usr/bin/env python /var/www/python/badge_add.py user '.$mostInfluentID.' 71');
        
    }
    
}

echo round(microtime(true)-$start,3)*1000 .'ms';
