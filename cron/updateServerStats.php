<?php

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

//UPDATE SERVER STATS DO ROUND ATUAL

$sql = "SELECT COUNT(*) AS totalUsers FROM cache_profile";
$totalUsers = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalusers;

$sql = 'SELECT COUNT(*) AS total FROM users WHERE TIMESTAMPDIFF(DAY, lastLogin, NOW()) <= 14';
$activeUsers = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM users_online';
$onlineUsers = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT 
            SUM(warezSent) AS tWarez, SUM(spamSent) AS tSpam, SUM(ddosCount) AS tDdos, SUM(hackCount) AS tHack, 
            SUM(timePlaying) AS tTime, SUM(moneyTransfered) AS mTransfered, SUM(moneyHardware) AS mHardware, 
            SUM(moneyEarned) AS mEarned, SUM(moneyResearch) AS mResearch, SUM(profileViews) AS tClicks,
            SUM(bitcoinSent) AS tBitcoin
        FROM users_stats';
$totalInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

$sql = 'SELECT COUNT(*) AS totalClan FROM clan';
$totalClan = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalclan;

$sql = 'SELECT COUNT(*) AS total FROM clan_users';
$totalClanMembers = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT SUM(pageClicks) AS total FROM clan_stats';
$totalClanClicks = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM clan_war_history';
$totalClanWar = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS tLists FROM lists';
$totalListed = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->tlists;

$sql = 'SELECT COUNT(*) AS tVirus FROM virus';
$totalVirus = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->tvirus;

$sql = 'SELECT SUM(cash) AS tCash FROM bankAccounts';
$totalMoney = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->tcash;

$sql = 'SELECT COUNT(*) AS total FROM missions_history WHERE completed = 1';
$totalMissions = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM mails WHERE mails.from > 0';
$totalMails = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM internet_connections';
$totalConnections = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM software_research';
$totalResearched = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM processes';
$totalTasks = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM software';
$totalSoftware = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM software_running';
$totalSoftwareRunning = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$sql = 'SELECT COUNT(*) AS total FROM hardware';
$totalServers = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;



$sql = "UPDATE round_stats 
        SET
            totalUsers = '".$totalUsers."', 
            activeUsers = '".$activeUsers."',
            onlineUsers = '".$onlineUsers."',
            usersClicks = '".$totalInfo->tclicks."',
            warezSent = '".$totalInfo->twarez."', 
            spamSent = '".$totalInfo->tspam."',
            bitcoinSent = '".$totalInfo->tbitcoin."',
            mailSent = '".$totalMails."', 
            ddosCount = '".$totalInfo->tddos."', 
            hackCount = '".$totalInfo->thack."', 
            clans = '".$totalClan."',
            clansWar = '".$totalClanWar."',
            clansMembers = '".$totalClanMembers."',
            clansClicks = '".$totalClanClicks."',
            timePlaying = '".$totalInfo->ttime."', 
            totalListed = '".$totalListed."', 
            totalVirus = '".$totalVirus."', 
            totalMoney = '".$totalMoney."',
            moneyHardware = '".$totalInfo->mhardware."',
            moneyEarned = '".$totalInfo->mearned."',
            moneyTransfered = '".$totalInfo->mtransfered."',
            moneyResearch = '".$totalInfo->mresearch."',
            missionCount = '".$totalMissions."',
            totalConnections = '".$totalConnections."',
            researchCount = '".$totalResearched."',
            totalTasks = '".$totalTasks."',
            totalSoftware = '".$totalSoftware."',
            totalRunning = '".$totalSoftwareRunning."',
            totalServers = '".$totalServers."'
        ORDER BY id DESC LIMIT 1";
$pdo->query($sql);

?>
