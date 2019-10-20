<?php

// 2019: This script ends the round. It is also called from within the game.
// 2019: Before removing the exit below, make sure to not allow anyone to externally (remotely) execute this script.

exit();
exec('/bin/sh ../cron2/updateStatsAndRanking.sh');

function getExtension($softType) {

    static $extensions = Array(

        '1' => '.crc',
        '2' => '.hash',
        '3' => '.scan',
        '4' => '.fwl',
        '5' => '.hdr',
        '6' => '.skr',
        '7' => '.av',
        '8' => '.vspam',
        '9' => '.vwarez',
        '10' => '.vddos',
        '11' => '.vcol',
        '12' => '.vbrk',
        '13' => '.exp',
        '14' => '.exp',
        '15' => '.nmap',
        '16' => '.ana',
        '17' => '.torrent',
        '18' => '.exe',
        '19' => '.exe',
        '20' => '.vminer',
        '29' => '.doom',
        '30' => '.txt',
        '31' => '',
        '50' => '.nsa',        
        '51' => '.emp',
        '90' => '.vdoom',
        '96' => '.vminer',
        '97' => '.vddos',
        '98' => '.vwarez',
        '99' => '.vspam',

    );

    return $extensions[$softType];
        
}

 function dotVersion($softVersion) {

    switch (strlen($softVersion)) {

        case '1':
            $strEdit = '0' . $softVersion;
            break;
        case '2': //1.9
            $strEdit = str_split($softVersion, 1);
            break;
        case '3': //12.0
            $strEdit = str_split($softVersion, 2);
            break;
        case '4': //132.8
            $strEdit = str_split($softVersion, 3);
            break;
        default:
            die("erreeeeeor");
            break;
    }

    $strReturn = $strEdit['0'] . '.' . $strEdit['1'];
    return $strReturn;

 }

require_once '/var/www/classes/PDO.class.php';
$pdo = PDO_DB::factory();

$start = microtime(true);

$sql = 'SELECT id FROM round ORDER BY id DESC LIMIT 1';
$curRound = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id; //current round is $curRound, next round is $curRound + 1

$pdo->query('UPDATE round SET status = 2, endDate = NOW() ORDER BY id DESC LIMIT 1');
$pdo->query('INSERT INTO round_stats (id) VALUES (\''.($curRound + 1).'\')');

$best = Array(
    'user' => Array(),
    'soft' => Array(),
    'clan' => Array(),
    'ddos' => Array()
);

//INICIO RANK USUARIOS

$sql = "SELECT 
            uid, exp, timePlaying, hackCount, ddosCount, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch,
            warezSent, spamSent, bitcoinSent, profileViews,
            TIMESTAMPDIFF(DAY, dateJoined, NOW()) AS age, clan.name, users.login,
            (SELECT COUNT(DISTINCT userID) AS total FROM software_research WHERE software_research.userID = users_stats.uid) AS researchCount
        FROM users_stats 
        INNER JOIN users
        ON users.id = users_stats.uid
        LEFT JOIN clan_users
        ON clan_users.userID = users_stats.uid
        LEFT JOIN clan
        ON clan.clanID = clan_users.clanID
        ORDER BY exp DESC";
$data = $pdo->query($sql);

$rank = 0;

while($pInfo = $data->fetch(PDO::FETCH_OBJ)){
    
    $rank++;
    
    $sql = "SELECT softName, softVersion, softType FROM software WHERE userID = '".$pInfo->uid."' AND isNPC = 0 AND softtype < 30 ORDER BY softVersion DESC LIMIT 1";
    $softInfo = $pdo->query($sql)->fetchAll();
    
    if(sizeof($softInfo) == 1){
        
        $softName = $softInfo['0']['softname'].getExtension($softInfo['0']['softtype']);
        $softVersion = dotVersion($softInfo['0']['softversion']);
        
    } else {
        $softName = $softVersion = '';
    }
    
    $sql = "INSERT INTO hist_users 
                (round, rank, id, userID, user, reputation, age, clanName, timePlaying, hackCount, ddosCount, bitcoinSent, ipResets, moneyEarned, 
                 moneyTransfered, moneyHardware, moneyResearch, bestSoft, bestSoftVersion, warezSent, spamSent, profileViews, researchCount) 
            VALUES ('".$curRound."', '".$rank."', '', '".$pInfo->uid."', '".$pInfo->login."', '".$pInfo->exp."', '".$pInfo->age."','".$pInfo->name."', '".$pInfo->timeplaying."',
                    '".$pInfo->hackcount."', '".$pInfo->ddoscount."', '".$pInfo->bitcoinsent."', '".$pInfo->ipresets."', '".$pInfo->moneyearned."', '".$pInfo->moneytransfered."', '".$pInfo->moneyhardware."', '".$pInfo->moneyresearch."',
                    '".$softName."', '".$softVersion."', '".$pInfo->warezsent."', '".$pInfo->spamsent."', '".$pInfo->profileviews."', '".$pInfo->researchcount."')";
    $pdo->query($sql);
    
    if($rank <= 10){
        $best['user'][$rank] = $pInfo->uid;
    }
    
}

//FIM RANK USUARIOS

//INICIO RANK CLAN

$sql = "SELECT clan.clanID, clan.name, clan.nick, clan.power, clan_stats.won, clan_stats.lost, clan_stats.pageClicks,
        (
            SELECT COUNT(*)
            FROM clan_users
            WHERE clan_users.clanID = clan.clanID
            GROUP BY clan_users.clanID
        ) AS members
        FROM clan
        INNER JOIN clan_stats ON clan_stats.cid = clan.clanID
        ORDER BY clan.power DESC";
$data = $pdo->query($sql);

$rank = 0;

while($cInfo = $data->fetch(PDO::FETCH_OBJ)){
    
    $rank++;

    if($cInfo->won == 0 && $cInfo->lost == 0){
        $rate = -1;
    } elseif($cInfo->won == 0 && $cInfo->lost > 0){
        $rate = 0;
    } else {
        $rate = ($cInfo->won / ($cInfo->won + $cInfo->lost))*100;
    }   
    
    $sql = "INSERT INTO hist_clans (id, rank, cid, name, nick, reputation, round, won, lost, clicks, members) 
            VALUES ('', '".$rank."', '".$cInfo->clanid."', '".$cInfo->name."', '".$cInfo->nick."', 
                    '".$cInfo->power."', '".$curRound."', '".$cInfo->won."', '".$cInfo->lost."', 
                    '".$cInfo->pageclicks."', '".$cInfo->members."')";
    $pdo->query($sql);
    
    if($rank <= 10){
        $best['clan'][$rank] = $cInfo->clanid;
    }
    
}

//FIM RANK CLAN

//INICIO RANK SOFTWARE

$sql = "SELECT softName, softType, softVersion, userID, users.login
        FROM software 
        JOIN users ON users.id = software.userID 
        WHERE isNPC = 0 AND softtype < 30 AND softType <> 19 
        ORDER BY softVersion DESC, softSize ASC";
$data = $pdo->query($sql);

$rank = 0;

while($sInfo = $data->fetch(PDO::FETCH_OBJ)){

    $rank++;
    
    $sql = "INSERT INTO hist_software (id, rank, softName, softType, softVersion, owner, ownerID, round) 
            VALUES ('', '".$rank."', '".$sInfo->softname."', '".$sInfo->softtype."', '".dotVersion($sInfo->softversion)."', '".$sInfo->login."', '".$sInfo->userid."', '".$curRound."')";
    $pdo->query($sql);
    
    if($rank <= 10){
        $best['soft'][$rank] = $sInfo->userid;
    }

}

//FIM RANK SOFTWARE

//INICIO RANK DDOS

$sql = "SELECT ranking_ddos.rank, attID, vicID, power, servers, att.login AS attUser, vic.login AS vicUser
        FROM round_ddos
        LEFT JOIN users att ON att.id = round_ddos.attID 
        LEFT JOIN users vic ON vic.id = round_ddos.vicID 
        INNER JOIN ranking_ddos ON round_ddos.id = ranking_ddos.ddosID
        WHERE vicNPC = 0
        ORDER BY power DESC, servers DESC";

$data = $pdo->query($sql);

$rank = 0;

while($dInfo = $data->fetch(PDO::FETCH_OBJ)){

    $rank++;
    
    $sql = "INSERT INTO hist_ddos (id, rank, round, attID, attUser, vicID, vicUser, power, servers) 
            VALUES ('', '".$rank."', '".$curRound."', '".$dInfo->attid."', '".$dInfo->attuser."', '".$dInfo->vicid."', '".$dInfo->vicuser."', '".$dInfo->power."', '".$dInfo->servers."')";
    $pdo->query($sql);

    if($rank <= 10){
        $best['ddos'][$rank] = $dInfo->attid;
    }
    
}

//FIM RANK DDOS

//INICIO HISTORY CLAN WAR

$sql = "SELECT idWinner, idLoser, scoreWinner, scoreLoser, startDate, endDate, bounty
        FROM clan_war_history
        ORDER BY endDate ASC";
$data = $pdo->query($sql);

while($wInfo = $data->fetch(PDO::FETCH_OBJ)){

    $sql = "INSERT INTO hist_clans_war (id, idWinner, idLoser, scoreWinner, scoreLoser, startDate, endDate, bounty, round) 
            VALUES ('', '".$wInfo->idwinner."', '".$wInfo->idloser."', '".$wInfo->scorewinner."', '".$wInfo->scoreloser."', '".$wInfo->startdate."', '".$wInfo->enddate."', '".$wInfo->bounty."', '".$curRound."')";
    $pdo->query($sql);

}

//FIM HISTORY CLAN WAR

//INICIO HISTORY MAILS

//$sql = "SELECT subject, mails.text, mails.from, mails.to, dateSent
//        FROM mails
//        WHERE mails.from > 0 AND isDeleted = 0
//        ORDER BY dateSent ASC";
//$data = $pdo->query($sql);
//
//while($mInfo = $data->fetch(PDO::FETCH_OBJ)){
//    
//    $sql = "INSERT INTO hist_mails (id, hist_mails.from, hist_mails.to, subject, hist_mails.text, dateSent, round) 
//            VALUES ('', ?, ?, ?, ?, ?, ?)";
//    $stmt = $pdo->prepare($sql);
//    $stmt->execute(array($mInfo->from, $mInfo->to, $mInfo->subject, $mInfo->text, $mInfo->datesent, $curRound));
//
//}

//FIM HISTORY MAILS

//INICIO HISTORY MISSIONS

$sql = "SELECT type, missionEnd, prize, userID, completed, npc.id AS hirerID
        FROM missions_history
        INNER JOIN npc ON npc.npcIP = hirer
        ORDER BY missionEnd ASC";
$data = $pdo->query($sql);

while($mInfo = $data->fetch(PDO::FETCH_OBJ)){
    
    $sql = "INSERT INTO hist_missions (id, userID, type, hirerID, prize, missionEnd, completed, round) 
            VALUES ('', ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($mInfo->userid, $mInfo->type, $mInfo->hirerid, $mInfo->prize, $mInfo->missionend, $mInfo->completed, $curRound));

}

//FIM HISTORY MISSIONS

//INICIO HISTORY DOOM

$sql = "SELECT creatorID, clanID, status
        FROM virus_doom";
$data = $pdo->query($sql);

while($dInfo = $data->fetch(PDO::FETCH_OBJ)){
        
    if($dInfo->status == 3){
        $dommerID = $dInfo->creatorid;
    }
    
    $sql = "INSERT INTO hist_doom (round, doomCreatorID, doomClanID, status) 
            VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($curRound, $dInfo->creatorid, $dInfo->clanid, $dInfo->status));

}

//FIM HISTORY DOOM

//TODO:
//MYSQL_DUMP


//DELETE's

$pdo->query('DELETE FROM bankAccounts');
$pdo->query('DELETE FROM bankaccounts_expire');
$pdo->query('DELETE FROM bitcoin_wallets');
$pdo->query("UPDATE cache SET reputation = 0");
$pdo->query("UPDATE clan SET power = 0");
$pdo->query('DELETE FROM clan_ddos');
$pdo->query('DELETE FROM clan_ddos_history');
$pdo->query('DELETE FROM clan_defcon');
$pdo->query('DELETE FROM clan_requests');
$pdo->query("UPDATE clan_stats SET totalMemberPower = '0', averageMemberPower = '0', averageMemberRanking = '0', totalMoneyEarned = '0', averageMoneyEarned = '0', servers = '0', won = '0', lost = '0'");
$pdo->query('DELETE FROM clan_war');
$pdo->query('DELETE FROM clan_war_history');
$pdo->query('DELETE FROM doom_abort');
$pdo->query('DELETE FROM fbi');
//$pdo->query('DELETE FROM news');
$pdo->query('DELETE FROM hardware');
$pdo->query('DELETE FROM hardware_external');
$pdo->query('DELETE FROM internet_connections');
$pdo->query('DELETE FROM internet_important');
$pdo->query("UPDATE internet_webserver SET active = '0'");
$pdo->query('DELETE FROM lists');
$pdo->query('DELETE FROM lists_bankAccounts');
$pdo->query('DELETE FROM lists_collect');
$pdo->query('DELETE FROM lists_notifications');
$pdo->query('DELETE FROM lists_specs');
$pdo->query('DELETE FROM lists_specs_analyzed');
$pdo->query('UPDATE log SET text = \'\'');
$pdo->query('DELETE FROM log_bot');
$pdo->query('DELETE FROM log_edit');
$pdo->query('DELETE FROM missions');
$pdo->query('DELETE FROM missions_history');
$pdo->query('DELETE FROM missions_seed');
$pdo->query('DELETE FROM processes');
$pdo->query('DELETE FROM processes_paused');
$pdo->query('DELETE FROM puzzle_solved');
$pdo->query('UPDATE ranking_clan SET rank = -1');
$pdo->query('DELETE FROM ranking_software');
$pdo->query('UPDATE ranking_user SET rank = -1');
$pdo->query('DELETE FROM ranking_ddos');
$pdo->query('DELETE FROM round_ddos');
$pdo->query('DELETE FROM safeNet');
$pdo->query('DELETE FROM software');
$pdo->query('DELETE FROM software_external');
$pdo->query('DELETE FROM software_folders');
$pdo->query('DELETE FROM software_research');
$pdo->query('DELETE FROM software_running');
$pdo->query('DELETE FROM software_texts');
$pdo->query('DELETE FROM users_expire');
$pdo->query('DELETE FROM users_online');
$pdo->query('UPDATE users_puzzle SET puzzleID = 0');
$pdo->query('UPDATE users_stats SET exp = 0, timePlaying = 0, missionCount = 0, hackCount = 0, ddosCount = 0, warezSent = 0, spamSent = 0, ipResets = 0, pwdResets = 0, moneyEarned = 0, moneyTransfered = 0, moneyHardware = 0, moneyResearch = 0, profileViews = 0');
$pdo->query('DELETE FROM virus');
$pdo->query('DELETE FROM virus_ddos');
$pdo->query('DELETE FROM virus_doom');

exec('/usr/bin/env python /var/www/python/fame_generator.py '.$curRound.' preview');
exec('/usr/bin/env python /var/www/python/fame_generator.py '.$curRound);
exec('/usr/bin/env python /var/www/python/fame_generator_alltime.py');
exec('/usr/bin/env python /var/www/python/fame_generator_alltime.py preview');

//badges

//doomer badges
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$dommerID.' 14');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$dommerID.' 71');

//bests
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['user'][1].' 7');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['user'][2].' 8');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['user'][3].' 9');

exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['soft'][1].' 72');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['soft'][2].' 73');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['soft'][3].' 74');

exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['ddos'][1].' 76');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['ddos'][2].' 77');
exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['ddos'][3].' 78');

exec('/usr/bin/env python /var/www/python/badge_add.py clan '.$best['clan'][1].' 81');
exec('/usr/bin/env python /var/www/python/badge_add.py clan '.$best['clan'][2].' 82');
exec('/usr/bin/env python /var/www/python/badge_add.py clan '.$best['clan'][3].' 83');

//almost there
for($i = 4; $i <= 10; $i++){
    if(array_key_exists($i, $best['user'])){
        exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['user'][$i].' 10');
    }
    if(array_key_exists($i, $best['soft'])){
        exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['soft'][$i].' 75');
    }
    if(array_key_exists($i, $best['ddos'])){
        exec('/usr/bin/env python /var/www/python/badge_add.py user '.$best['ddos'][$i].' 79');
    }
    if(array_key_exists($i, $best['clan'])){
        exec('/usr/bin/env python /var/www/python/badge_add.py clan '.$best['clan'][$i].' 84');
    }
}

$pdo->query('INSERT INTO round (id, startDate) VALUES (\''.($curRound + 1).'\', DATE_ADD(NOW(), INTERVAL 1 DAY))');

echo round(microtime(true)-$start,3)*1000 .' ms';

?>
