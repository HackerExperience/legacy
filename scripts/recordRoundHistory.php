<?php
die("ACHO QUE NAO USO ISSO!");
function getExtension($softType) {

    static $extensions = Array(

        '1' => '.crc',
        '2' => '.pec',
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
        '29' => '.doom',
        '30' => '.txt',
        '31' => '',
        '50' => '.nsa',        
        '51' => '.emp',
        '9' => '.vdoom',
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

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$start = microtime(true);

//TESTES SOMENTE
$pdo->query('DELETE FROM hist_users; DELETE FROM hist_software; DELETE FROM hist_clans');
//TESTES SOMENTE

//cur round
$sql = 'SELECT id FROM round ORDER BY id DESC';
$curRound = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;

//INICIO USERS

$sql = 'SELECT users.id, users.login, users_stats.exp, clan_users.clanID, timePlaying, hackCount, ddosCount, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch,
        TIMESTAMPDIFF(DAY, dateJoined, NOW()) AS age
        FROM users
        INNER JOIN users_stats
        ON users.id = users_stats.uid
        LEFT JOIN clan_users
        ON clan_users.userID = users.id
        ORDER BY users_stats.exp DESC';
$data = $pdo->query($sql);

$pos = 1;

while($userInfo = $data->fetch(PDO::FETCH_OBJ)){

    if($userInfo->clanid != NULL){
        $sql = "SELECT name FROM clan WHERE clanID = '".$userInfo->clanid."'";
        $clanName = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->name;
    } else {
        $clanName = '';
    }
    
    //melhorar dps, precisa ser certificado por mim!
    $sql = "SELECT softVersion, softName, softType FROM software WHERE userID = '".$userInfo->id."' AND isNPC = 0 AND softType < 30 ORDER BY softVersion DESC, softType DESC LIMIT 1";
    $softInfo = $pdo->query($sql)->fetchAll();
    
    if(sizeof($softInfo) > 0){
        $bestSoft = $softInfo['0']['softname'].getExtension($softInfo['0']['softtype']);
        $bestSoftVersion = dotVersion($softInfo['0']['softversion']);
    } else {
        $bestSoft = '';
        $bestSoftVersion = '';
    }

    $sql = "INSERT INTO hist_users (id, rank, userID, user, reputation, bestSoft, bestSoftVersion, clanName, round, timePlaying, hackCount, ddosCount, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch, age)
            VALUES ('', '".$pos."', '".$userInfo->id."', '".$userInfo->login."', '".$userInfo->exp."', '".$bestSoft."', '".$bestSoftVersion."', '".$clanName."', '".$curRound."', '".$userInfo->timeplaying."',
                    '".$userInfo->hackcount."', '".$userInfo->ddoscount."', '".$userInfo->ipresets."', '".$userInfo->moneyearned."', '".$userInfo->moneytransfered."', '".$userInfo->moneyhardware."', '".$userInfo->moneyresearch."', '".$userInfo->age."')";
    $pdo->query($sql);

    $pos++;
    
}

//FIM USERS

//INICIO CLAN

$sql = 'SELECT clan.name, clan.nick, clan.slotsUsed, clan.power, clan_users.userID, users.login
        FROM clan
        INNER JOIN clan_users
        ON clan_users.clanID = clan.clanID
        INNER JOIN users
        ON clan_users.userID = users.id
        WHERE clan_users.authLevel = 4
        ORDER BY clan.power DESC';
$data = $pdo->query($sql);

$pos = 1;

while($clanInfo = $data->fetch(PDO::FETCH_OBJ)){
    
    $sql = "INSERT INTO hist_clans (id, rank, name, nick, reputation, owner, ownerID, members, round)
            VALUES ('', '".$pos."', '".$clanInfo->name."', '".$clanInfo->nick."', '".$clanInfo->power."', '".$clanInfo->login."', '".$clanInfo->userid."', '".$clanInfo->slotsused."', '".$curRound."')";
    $pdo->query($sql);
    
    $pos++;
    
}

//FIM CLAN
  
//INICIO SOFTWARE

$sql = 'SELECT software.softName, software.softVersion, software.softType, software.userID, users.login
        FROM software
        INNER JOIN users
        ON users.id = software.userID
        WHERE software.isNPC = 0 AND softType < 29
        ORDER BY softVersion DESC, softType DESC
        LIMIT 100';
$data = $pdo->query($sql);

$pos = 1;

while($softInfo = $data->fetch(PDO::FETCH_OBJ)){
    
    $sql = "INSERT INTO hist_software (id, rank, softName, softType, softVersion, owner, ownerID, round)
            VALUES ('', '".$pos."', '".$softInfo->softname."', '".$softInfo->softtype."', '".$softInfo->softversion."', '".$softInfo->login."', '".$softInfo->userid."', '".$curRound."')";
    $pdo->query($sql);
    
    $pos++;
    
}

//FIM SOFTWARE

//DELETE ALL SOFTWARES
//DELETE ALL CLANS

echo "<br/><br/>".round(microtime(true) - $start, 4)*1000 .'ms';

?>
