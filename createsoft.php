<?php
session_start();
if($_SESSION['id'] > 2)
exit();
// 2019: Translation: "Unsafe and temporary code". Gawd.
//código inseguro e temporário

if(!isset($_SESSION['id'])){
    header("Location:index.php");
}

if(isset($_POST['name'])){
    
    require '/var/www/classes/PDO.class.php';
    $pdo = PDO_DB::factory();
        
    $sql = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, isNPC)
            VALUES ('', '".$_SESSION['id']."', '".$_POST['name']."', '".$_POST['versao']."', '".$_POST['size']."', '".$_POST['ram']."', '".$_POST['type']."', 0)";
    $pdo->query($sql);
    
    
    
    if(isset($_POST['running'])){
    
        $softID = $pdo->lastInsertId();
        
        $sql = "INSERT INTO software_running (id, softID, userID, ramUsage, isNPC)
                VALUES ('', '".$softID."', '".$_SESSION['id']."', '".$_POST['ram']."', 0)";
        $pdo->query($sql);
        
    }
    
    header("Location: software.php");
    exit();
}

?>

<strong>Nao aperte f5 (resubmitar post)</strong><br/><br/>

<form action="" method="POST">
    
    Name: <input type="text" name="name" autofocus="1"><br/><br/>
    Size: <input type="text" name="size" value="100"> MB <br/><br/>
    Vers: <input type="text" name="versao" value="10"> (Formato: 10 = 1.0, 34 = 3.4, 123 = 12.3)<br/><br/>
    Tipo: 
    <select name="type">
        <option value="1">1 - Cracker</option>
        <option value="2">2 - Password encryptor</option>
        <option value="3">3 - Port Scanner</option>
        <option value="4">4 - Firewall</option>
        <option value="5">5 - Hidder</option>
        <option value="6">6 - Seeker</option>
        <option value="7">7 - Antivirus</option>
        <option value="8">8 - Virus Spam .vspam</option>
        <option value="9">9 - Virus Warez .vwarez</option>
        <option value="10">10 - Virus DDoS .vddos</option>
        <option value="11">11 - Virus Collector</option>
        <option value="12">12 - DDoS Breaker</option>
        <option value="13">13 - FTP Exploit</option>
        <option value="14">14 - SSH Exploit</option>
        <option value="15">15 - Nmap scanner</option>
        <option value="16">16 - Hardware Analyzer</option>
        <option value="17">17 - .torrent</option>
        <option value="18">18 - webserver.exe</option>
        <option value="19">19 - wallet.exe</option>
        <option value="20">20 - BTC Miner.vminer</option>
        <option value="26">26 - riddle.exe (NPC only)</option>
        <option value="29">29 - Doom (*)</option>
    </select><br/><br/>
    RAM: <input type="text" name="ram" value="50"> MB<br/><br/>
    Running: <input type="checkbox" name="running" value="1"><br/><br/>
    <br/><br/>
    <input type="submit" value="CRIAR">
    
</form>

<br/><br/>

<a href="software.php">Back to my softwares</a>