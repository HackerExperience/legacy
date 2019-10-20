<?php

class HardwareVPC extends Player {

    public $ram;
    public $cpu;
    public $hdd;
    public $xhd;
    public $net;
    private $totalPCs;
    protected $id;
    protected $hddSpecs;
    
    protected $pdo;
    public $session;

    function __construct($id = '') {

        if (is_numeric($id)) {
            parent::__construct($id);
        } else {
            $this->pdo = PDO_DB::factory();
        }

        $this->session = new Session();
        
    }

    public function getHardwareInfo($id, $pcType, $xhd = '') {

        require 'hardwareItens.php';

        if ($id == '') {
            $id = $_SESSION['id'];
        }

        $npc = 0;
        if ($pcType == 'NPC') {
            $npc = 1;
        }

        $this->session->newQuery();
        $sqlSelect = "  SELECT 
                            COUNT(*) AS total, 
                            SUM(cpu) AS cpu, 
                            SUM(hdd) AS hdd,  
                            SUM(ram) AS ram, 
                            net
                        FROM hardware
                        WHERE hardware.userID = $id AND isNPC = $npc";
        $hardwareInfo = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);
        
        $totalPCs = $hardwareInfo->total;

        if($totalPCs == 0){
            die("Error: hardware is not registered");
        }

        $totalCPU = $hardwareInfo->cpu;
        $totalHDD = $hardwareInfo->hdd;
        $totalRAM = $hardwareInfo->ram;
        $totalNET = $hardwareInfo->net;

        $this->session->newQuery();
        $sqlSelect = "  SELECT SUM(size) AS xhd
                        FROM hardware_external
                        WHERE userID = $id";
        $totalXHD = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->xhd;
        
        $values = Array(

            'CPU' => $totalCPU,
            'HDD' => $totalHDD,
            'RAM' => $totalRAM,
            'NET' => $totalNET,
            'XHD' => $totalXHD,

        );

        $this->totalPCs = $totalPCs;
        $this->ram = $totalRAM;
        $this->cpu = $totalCPU;
        $this->hdd = $totalHDD;
        $this->xhd = $totalXHD;
        $this->net = $totalNET;

        return $values;
    }
    
    public function showPCTotal($uid, $remote = 0){

        if($remote == 1){
            $linkBuy = '?view=clan&action=buy';
            $page = 'upgradenet';
        } else {
            $linkBuy = '?opt=buy';
            $page = 'home';
        }
                
        $cpu = $this->cpu;
        $net = $this->net;
        $hdd = $this->hdd;
        $ram = (float)$this->ram;

        if($cpu < 1000){
            $cpuStr = 'MHz';
        } else {
            $cpuStr = 'GHz';
            $cpu /= 1000;
        }
        
        
        if($net == 1000){
            $net = 1;
            $netStr = 'Gbit';
        } else {
            $netStr = ' Mbit';
        }
        
        if(is_float($cpu)){
            $cpu = round($cpu, 1);
        }
        
        $hdd /= 1000;
        if(is_float($hdd)){
            $hdd = round($hdd, 1);
        }
        if($hdd < 1){
            $hddStr = 'MB';
            $hdd *= 1000;
        } else {
            $hddStr = 'GB';
        }
        
        if(is_float($ram)){
            $ram = round($ram, 1);
        }
        
        if(is_float($net)){
            $net = round($net, 1);
        }
        
        ?>
        
        <ul class="hard-box">
            <li>
                    <div class="left"><span class="he32-cpu32"></span></div>
                    <div class="right">
                            <strong><?php echo $cpu; ?></strong>
                            <?php echo $cpuStr; ?>
                    </div>
            </li>
            <li>
                    <div class="left"><span class="he32-hdd32"></span></div>
                    <div class="right">
                            <strong><?php echo $hdd; ?></strong>
                            <?php echo $hddStr; ?>
                    </div>
            </li>
            <li>
                <div class="left"><span class="he32-ddr-memory"></span></div>
                    <div class="right">
                            <strong><?php echo $ram; ?></strong>
                            MB
                    </div>
            </li>
            <li>
                    <div class="left"><span class="he32-net32"></span></div>
                    <div class="right">
                            <strong><?php echo $net; ?></strong>
                            <?php echo $netStr; ?>
                    </div>
            </li>
            <?php if($remote == 0 && $this->xhd != 0){ ?>
                <li>
                        <div class="left"><span class="he32-xhd32"></span></div>
                        <div class="right">
                                <strong><?php echo $this->xhd / 1000; ?></strong>
                                GB
                        </div>
                </li>            
            <?php } ?>
        </ul> 

        <?php
        
        self::listPCs($uid, $page);

        if($remote == 1){
            
            //todo: player can only buy new server (or upgrade) his own clan server
            
        ?>

                        <br/>
                        <button class="btn" onClick="parent.location='?view=clan&action=buy'">Buy new server</button>&nbsp;
                        <button class="btn" onClick="parent.location='?view=clan&action=internet'">Upgrade internet</button>

                    </div>
                </div>
            </div>
            <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>

        <?php
        
        }

        
    }

    public function listPCs($id, $page, $type = ''){
        
        if($page == 'upgrade' || $page =='home') {
            $link = '?opt=upgrade&id=';
            $isNPC = 0;
            $pcType = 'VPC';
        } elseif($page == 'upgradenet'){
            $link = '?view=clan&action=upgrade&server=';
            $isNPC = 1;
            $pcType = 'NPC';
        }

        $this->session->newQuery();
        $sqlSelect = "SELECT COUNT(*) AS total FROM hardware WHERE userID = $id AND isNPC = $isNPC LIMIT 1";
        $totalPCs = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;
        
        if($totalPCs > 0){
                        
            if($page == 'home' || $page == 'upgradenet'){
                ?>
                <div class="row-fluid">
                <?php
            }      

            $firstEmptyDiv = 0;
            $secondEmptyDiv = 0;

            switch($totalPCs){
                case 1:
                    $firstEmptyDiv = 4;
                    break;
                case 2:
                    $firstEmptyDiv = 2;
                    break;
                case 4:
                    $firstEmptyDiv = 2;
                    $secondEmptyDiv = 2;
                    break;
                case 5:
                    $secondEmptyDiv = 2;
                    break;
            }

            if($firstEmptyDiv != 0){
                ?>

                <div class="span<?php echo $firstEmptyDiv; ?>"></div>

                <?php
            }

            $toClose = 0;
            for($i=0; $i<$totalPCs; $i++){

                $offset = (string)$i;
                
                $pcInfo = self::getPCSpec('', $pcType, $id, $offset);
                                
                $cpu = $pcInfo['CPU'];
                $hdd = round($pcInfo['HDD']) / 1000;
                if($hdd < 1){
                    $hdd = $pcInfo['HDD'];
                    $hddStr = ' MB';
                } else {
                    $hddStr = ' GB';
                }
                $ram = $pcInfo['RAM'];
                $name = $pcInfo['NAME'];               

                if($cpu < 1000){
                    $cpuStr = ' MHz';
                } else {
                    $cpuStr = ' GHz';
                    $cpu /= 1000;
                }


                ?>
                <div class="span4">

                    <div class="widget-box" style="text-align: left;">

                        <div class="widget-title">
                            <span class="icon"><span class="he16-server"></span></span>
                            <h5><?php echo $name; ?></h5>
                            <a href="<?php echo $link.$pcInfo['ID']; ?>"><span class="label">Upgrade</span></a>
                        </div>

                        <div class="widget-content padding">        

                            <ul class="list">
                                <a href="<?php echo $link.$pcInfo['ID']; ?>">
                                    <li  class="li-click">
                                        <div class="span2 hard-ico">
                                            <span class="he32-pc"></span>
                                        </div>
                                        <div class="span10">
                                            <div class="list-ip">
                                                <?php echo $name; ?>
                                            </div>
                                            <div class="list-user">
                                                <span class="he16-cpu" style="margin-top: 4px;"></span>
                                                <small><?php echo $cpu.$cpuStr; ?></small>
                                                <span class="he16-hdd" style="margin-top: 4px;"></span>
                                                <small><?php echo $hdd.$hddStr; ?></small>
                                                <span class="he16-ram" style="margin-top: 4px;"></span>
                                                <small><?php echo $ram; ?> MB</small>
                                            </div>
                                        </div>
                                        <div style="clear: both;"></div>
                                    </li>
                                </a>
                            </ul>                            

                        </div>

                    </div>

                </div>

                <?php


                if(($i == 2 && $totalPCs > 4) || ($i == 1 && $totalPCs == 4)){

                    $toClose = 1;

                    ?>



                    <div class="row-fluid" style="text-align: center;">

                        <div class="span12 center" style="text-align: left;">                    

                            <?php if($secondEmptyDiv > 0){ ?>

                                <div class="span<?php echo $secondEmptyDiv; ?>"></div>

                            <?php } ?>

                    <?php
                }


            }

            if($toClose == 1){

                ?>

                    </div>
                </div>

                <?php
            }

            
            
            if($page == 'home' || $page == 'upgradenet'){
                ?>
                </div>
                <?php
            }  
            
        } else {
            echo 'You have no servers';
        }
        
    }
    
    public function getNetSpec($id, $pcType){
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
        
        $npc = '0';
        if($pcType == 'NPC'){
            $npc = '1';
        }
        
        $this->session->newQuery();
        $sql = "SELECT net FROM hardware WHERE userID = '".$id."' AND isNPC = $npc LIMIT 1";
        
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function getInternetRates($speed){
        
        $speed *= 1000;
        
        $return = Array();
        
        $return['download'] = $speed / 8;
        $return['upload'] = $speed / 16;
        
        if($return['download'] >= 1000){
            $return['downloadstr'] = round(($return['download']/1000), 2) .'MB/s';
        } else {
            $return['downloadstr'] = round($return['download'], 2) .'kB/s';
        }
        
        if($return['upload'] >= 1000){
            $return['uploadstr'] = round(($return['upload']/1000), 2) .'MB/s';
        } else {
            $return['uploadstr'] = round($return['upload'], 2) .'kB/s';
        }
        
        return $return;
        
    }
    
    public function getTotalPCs($id = '', $pcType = '') {

        if ($id == '') {
            $id = $_SESSION['id'];
        }

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        if($npc == 0 && isset($this->totalPCs)){
            if($this->totalPCs > 0){
                return $this->totalPCs;
            }
        }
        
        $this->session->newQuery();
        $sqlSelect = "SELECT COUNT(*) AS total FROM hardware WHERE userID = $id AND isNPC = $npc";
        $this->totalPCs = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;
        
        return $this->totalPCs;

    }

    public function getPCSpec($serverID, $pcType, $id, $unknownID = '') {
        
        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        if ($id == '') {
            $id = $_SESSION['id'];
        }

        $offset = '';
        if($unknownID !== ''){

            $where = 'userID = '.$id.' AND isNPC = '.$npc;
            
            if($unknownID > 0){
                $offset = 'OFFSET '.$unknownID;
            }

        } else {

            $where = 'serverID = '.$serverID;
        }
        
        if($unknownID != 0){
            $totalPCs = 1;
        } else {
        
            $this->session->newQuery();
            $sqlSelect = "SELECT COUNT(*) AS total FROM hardware WHERE ".$where." LIMIT 1 ".$offset;
            $totalPCs = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;

        }

        $existe = 0;
        if($totalPCs > 0){
        
            $this->session->newQuery();
            $sqlSelect = "SELECT serverID, userID, name, cpu, ram, hdd, net, isNPC FROM hardware WHERE ".$where." LIMIT 1 ".$offset;
            $pcInfo = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);   
            
            if($pcInfo->userid == $id && $pcInfo->isnpc == $npc){
                
                $existe = 1;

                $pcSpecs = Array(
                    'ID' => $pcInfo->serverid,
                    'CPU' => $pcInfo->cpu,
                    'HDD' => $pcInfo->hdd,
                    'RAM' => $pcInfo->ram,
                    'NET' => $pcInfo->net,
                    'NAME' => $pcInfo->name
                );

            }
                
        }

        $pcSpecs['ISSET'] = $existe;

        return $pcSpecs;

    }

    public function showServerInfo($pcInfo, $pcID){

        
        require 'hardwareItens.php';
        
        ?>
        
        Details of server <b><?php echo $pcInfo['NAME']; ?></b>: <br/><br/>  
                
        <table>
            <tr><td><b>CPU:</b> </td><td><?php echo $cpuItens[$pcInfo['CPU']]['POW']; ?> MHz</td></tr>
            <tr><td><b>RAM:</b> </td><td><?php echo $ramItens[$pcInfo['RAM']]['POW']; ?> MB</td></tr>
            <tr><td><b>HDD:</b> </td><td><?php echo $hddItens[$pcInfo['HDD']]['POW']; ?> MB</td></tr>
        </table>
                
        <br/><br/>
        
        <input type="submit" onClick="parent.location='hardware?opt=list'" value="Go back">
        <input type="submit" onClick="parent.location='hardware?opt=upgrade&id=<?php echo $pcID + 1; ?>'" value="Upgrade">
        <input type="submit" onClick="parent.location='hardware?opt=upgrade&id=<?php echo $pcID + 1; ?>'" value="Change name">
                
        <?php        
                
    }
    
    public function calculateRamUsage($id, $pcType) {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        $this->session->newQuery();
        $sqlSelect = "SELECT ramUsage FROM software_running WHERE userID = $id AND isNPC = $npc";
        $data = $this->pdo->query($sqlSelect);

        $totalRam = '0';

        while ($runningInfo = $data->fetch(PDO::FETCH_OBJ)) {

            $totalRam += $runningInfo->ramusage;
        }

        $hardwareInfo = self::getHardwareInfo($id, $pcType);

        $return = Array(
            'TOTAL' => $hardwareInfo['RAM'],
            'USED' => $totalRam,
            'AVAILABLE' => $hardwareInfo['RAM'] - $totalRam,
        );

        return $return;
    }

    public function getSoftUsage($id, $pcType, $softID) {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        $this->session->newQuery();
        $sqlSelect = "SELECT ramUsage FROM software_running WHERE userID = $id AND softID = $softID AND isNPC = $npc";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        $ramInfo = self::calculateRamUsage($id, $pcType);

        return $data['0']['ramusage'] / $ramInfo['TOTAL'];
        
    }

    public function calculateHDDUsage($id, $pcType) {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        $this->session->newQuery();
        $sqlSelect = "SELECT softSize FROM software WHERE userID = $id AND isNPC = $npc";
        $data = $this->pdo->query($sqlSelect);

        $totalSize = '0';

        while ($softInfo = $data->fetch(PDO::FETCH_OBJ)) {

            $totalSize += $softInfo->softsize;
            
        }

        $hardwareInfo = self::getHardwareInfo($id, $pcType);

        $return = Array(
            'TOTAL' => $hardwareInfo['HDD'],
            'USED' => $totalSize,
            'AVAILABLE' => $hardwareInfo['HDD'] - $totalSize,
        );

        return $return;
        
    }

    public function handlePost(){
                
        $system = new System();

        if(isset($_POST['act']) && isset($_POST['price'])){
        
            $type = 'buy';
            
            if(isset($_POST['clan'])){
                $isClan = 1;
            } else {
                $isClan = 0;
            }
            
            if($isClan == 1){
                $postRedirect = 'internet?view=clan';
            } else {
                $postRedirect = 'hardware';
            }

            $act = $_POST['act'];
            $postPrice = $_POST['price'];

            if(!ctype_digit($postPrice)){
                $system->handleError('Invalid information.', 'hardware');
            }
            
            $badAcc = 0;
            if(isset($_POST['acc'])){
                $acc = $_POST['acc'];
                if(!ctype_digit($acc)){
                    $badAcc = 1;
                }
            } else {
                $badAcc = 1;
            }
            
            if($badAcc == 1){
                $system->handleError('BAD_ACC', 'hardware');
            }
            
        } elseif((isset($_POST['sName']) && isset($_POST['sID'])) || (isset($_POST['xName']) && isset($_POST['xID']))){
        
            $type = 'rename';

            if(isset($_POST['sName'])){
                
                $isXHD = 0;
                $link = 'hardware?opt=upgrade&id=';
                
                $sName = $_POST['sName'];
                $sID = $_POST['sID'];
                
            } else {

                $link = 'hardware?opt=xhd&id=';
                $isXHD = 1;
                
                $sName = $_POST['xName'];
                $sID = $_POST['xID'];
                
            }

            $postRedirect = $link.$sID;
            
            if(!ctype_digit($sID)){
                $system->handleError('Invalid information.', 'hardware');
            }
            
            if(strlen($sName) == 0){
                $system->handleError('Invalid information.', $link.$sID);
            }
            
            if(strlen($sName) > 12){
                $system->handleError('This name is too damn big.', $link.$sID);
            }
            
        } else {
            header("Location:hardware");
            exit();
        }
        
        if($type == 'rename'){

            if($isXHD == 0){

                $str = 'server';
                $function = 'renameServer';
                $opt = 'upgrade';
                
                $serverInfo = self::getPCSpec($sID, 'VPC', $_SESSION['id']);

                $existe = $serverInfo['ISSET'];
                $curName = $serverInfo['NAME'];

            } else {

                $str = 'external disk';
                $function = 'renameXHD';
                $opt = 'xhd';
                
                $xhdInfo = self::getXHD($sID);

                $existe = $xhdInfo['ISSET'];
                $curName = $xhdInfo['NAME'];       

            }

            if($existe == 0){
                $system->handleError(_('Oh ho! Invalid ').$str.'.', 'hardware');
            }

            if($curName == $sName){
                $system->handleError(sprintf(_('This %s already got this name.'), _($str.'\'s')), 'hardware?opt='.$opt.'&id='.$sID);
            }

            self::$function($sID, $sName);

            $this->session->addMsg(sprintf(_("Look! Your %s has a new, pretty name. :)"), $str), 'success');

            
        } elseif($type == 'buy'){
        
            switch($act){

                case 'BUY-PC':

                    if($isClan == 1){
                        
                        require '/var/www/classes/Clan.class.php';
                        $clan = new Clan();

                        $clanIP = $clan->getClanInfo($clan->getPlayerClan())->clanip;
                        
                        $clanInfo = parent::getIDByIP($clanIP, 'NPC');
                        $cid = $clanInfo['0']['id'];
                        $logID = $cid;
                        $cnpc = 1;
                        
                        $sucMsg = 'New clan server bought.';
                        $redirect = 'internet?view=clan?action=buy';
                        
                    } else {
                        
                        require '/var/www/classes/Process.class.php';
                        
                        $logID = $_SESSION['id'];
                        $cid = '';
                        $cnpc = 0;
                        $sucMsg = 'Your new server was bought.';
                        $redirect = 'hardware?opt=buy';
                        
                    }
                    
                    $price = self::getPCPrice($cid);

                    $accInfo = self::handlePostCommon($acc, $price, $redirect);

                    self::buyPC($acc, $cid);

                    $process = new Process();
                    $process->updateProcessTime('', '');   
                    
                    if($isClan == 0){
                        if(self::getTotalPCs() == 1){
                            
                            require '/var/www/classes/Social.class.php';
                            $social = new Social();
                            
                            //add badge 'buy second pc'
                            $social->badge_add(15, $_SESSION['id']);
                            
                        }
                    }
                    
                    $this->session->addMsg($sucMsg, 'success');
                    
                    
                    $finances = new Finances();

                    if($cnpc == 1){
                        $logType = 1;
                        $myIP = long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip);
                    } else {
                        $logType = 0;
                        $myIP = '';
                    }
                    
                    $log = new LogVPC();
                    $log->addLog($logID, $log->logText('BUY_PC', Array($logType, $price, $acc, long2ip($finances->getBankIP($accInfo['0']['bankid'])), $myIP)), $cnpc);

                    if($cnpc == 1){
                        $log->addLog($_SESSION['id'], $log->logText('BUY_PC', Array(2, $price, $acc, long2ip($finances->getBankIP($accInfo['0']['bankid'])), long2ip($clanIP))), 0);
                    }
                    
                    break;

                case 'BUY-XHD':

                    $xhdInfo = self::getXHDInfo();
                    
                    $price = self::getXHDPrice($xhdInfo['TOTAL']);

                    $accInfo = self::handlePostCommon($acc, $price, 'hardware?opt=xhd');

                    self::buyXHD($acc, $price, $xhdInfo['TOTAL']);

                    if($xhdInfo['TOTAL'] == 0){

                        require '/var/www/classes/Social.class.php';
                        $social = new Social();

                        //add badge 'buy second xhd'
                        $social->badge_add(70, $_SESSION['id']);

                    }
                    
                    $this->session->addMsg('Your new External Hard Drive was bought.', 'success');
                    
                    $finances = new Finances();

                    $log = new LogVPC();
                    $log->addLog($_SESSION['id'], $log->logText('BUY_XHD', Array($price, $acc, long2ip($finances->getBankIP($accInfo['0']['bankid'])))), 0);
                    
                    break;

                case 'net':
                case 'xhd': //upgrade XHD, not new xhd.
                case 'cpu':
                case 'ram':
                case 'hdd':    

                    require 'hardwareItens.php';

                    if($isClan == 1){
                        
                        require '/var/www/classes/Clan.class.php';
                        $clan = new Clan();

                        $clanIP = $clan->getClanInfo($clan->getPlayerClan())->clanip;
                        
                        $clanInfo = parent::getIDByIP($clanIP, 'NPC');
                        
                        $cid = $clanInfo['0']['id'];
                        $logID = $cid;
                        $cnpc = 1;
                        
                        $pcType = 'NPC';
                        $sucMsg = 'Clan server hardware upgraded.';
                        $baseRedirect = 'internet?view=clan';
                        
                        if($act == 'net'){
                            $redirect = 'internet?view=clan&action=internet';
                        } else {
                            $redirect = 'internet?view=clan';
                        }
                        
                    } else {
                        
                        $pcType = 'VPC';
                        $cid = 0;
                        $cnpc = 0;
                        $logID = $_SESSION['id'];
                        $sucMsg = 'Your hardware was upgraded.';
                        $baseRedirect = 'hardware?opt=upgrade';
                        
                        if($act == 'net' || $act == 'xhd'){
                            $redirect = 'hardware?opt='.$act;
                        } else {
                            $redirect = 'hardware?opt=upgrade';
                        }     
                        
                    }                   
                    
                    $link = $act.'Itens';
                    $partArray = $$link;                

                    if(isset($_POST['part-id'])){
                        $partID = $_POST['part-id'];
                        if(!ctype_digit($partID)){
                            $system->handleError('Invalid information.', $baseRedirect);
                        }
                    } else {
                        $system->handleError('Invalid information.', $baseRedirect);
                    }

                    if(array_key_exists($partID, $partArray)){
                        $price = $partArray[$partID]['PRICE'];
                    } else {
                        $system->handleError('Invalid information.', $baseRedirect);
                    }

                    $accInfo = self::handlePostCommon($acc, $price, $redirect);

                    self::commitUpgrade($partID, $partArray, $act, $acc, $cid);
                    
                    if($isClan == 0){
                        if($act == 'net'){
                            if(!array_key_exists($partID + 1, $partArray)){

                                require '/var/www/classes/Social.class.php';
                                $social = new Social();

                                //add badge 'max internet upgrade'
                                $social->badge_add(21, $_SESSION['id']);

                            }
                        } else {
                            if(($act == 'ram' && $partID > 3) || $partID > 5){

                                $hardwareInfo = FALSE;
                                
                                
                                                                    
                                if(!array_key_exists($partID + 1, $partArray)){
                                    
                                    $hardwareInfo = self::getHardwareInfo($_SESSION['id'], 'VPC');
                                    
                                    if($hardwareInfo[strtoupper($act)] == $partArray[$partID]['POW'] * 4){
                                        
                                        require '/var/www/classes/Social.class.php';
                                        $social = new Social();
                                        
                                        if($act == 'cpu'){
                                            $badgeID = 19;
                                        } elseif($act == 'hdd'){
                                            $badgeID = 18;
                                        } else {
                                            $badgeID = 20;
                                        }
                                        
                                        //add badge 'max out spec uçgrade of 4 pcs'
                                        $social->badge_add($badgeID, $_SESSION['id']);
                                    }
                                    
                                }
                                
                                $pcSpec = self::getPCSpec($_SESSION['CUR_PC'], 'VPC', $_SESSION['id']);
                                
                                if($pcSpec['CPU'] == 4000 && $pcSpec['HDD'] == 100000 && $pcSpec['RAM'] == 2048){

                                    require_once '/var/www/classes/Social.class.php';
                                    $social = new Social();

                                    //add badge 'max upgrades de 1 pc'
                                    $social->badge_add(16, $_SESSION['id']);

                                    if(!$hardwareInfo){
                                        $hardwareInfo = self::getHardwareInfo($_SESSION['id'], 'VPC');
                                    }

                                    if ($hardwareInfo['CPU'] == 4000 * 4 && $hardwareInfo['HDD'] == 100000 * 4 && $hardwareInfo['RAM'] == 2048 * 4) {

                                        //add badge 'max out pc upgrades'
                                        $social->badge_add(17, $_SESSION['id']);

                                    }
                                    
                                }
                                
                            }
                        }
                        
                    }
                    
                    unset($_SESSION['CUR_PC']);
                    unset($_SESSION['XHD_ID']);

                    if($cid == 0){
                        $cid = '';
                        require '/var/www/classes/Process.class.php';
                    }
                    
                    $process = new Process();
                    $process->updateProcessTime($cid, $pcType);                

                    $this->session->addMsg('Your hardware was upgraded.', 'success');
                    
                    if($act == 'hdd' || $act == 'xhd'){
                        $power = ($partArray[$partID]['POW'] / 1000) . ' GB';
                    } elseif($act == 'cpu'){
                        if ($partArray[$partID]['POW'] >= 1000) {
                            $power = ($partArray[$partID]['POW'] / 1000) . ' GHz';
                        } else {
                            $power = $partArray[$partID]['POW'] . ' MHz';
                        }
                    } elseif ($act == 'ram'){
                        $power = $partArray[$partID]['POW'] . ' MB';
                    } else {
                        $power = $partArray[$partID]['POW'] . ' Mbit';
                    }
                    
                    $finances = new Finances();

                    if($cnpc == 1){
                        $logType = 1;
                        $myIP = long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip);
                    } else {
                        $logType = 0;
                        $myIP = '';
                    }
                    
                    $log = new LogVPC();
                    $log->addLog($logID, $log->logText('BUY_HARDWARE', Array($logType, $act, $power, $price, $acc, long2ip($finances->getBankIP($accInfo['0']['bankid'])), $myIP)), $cnpc);

                    if($cnpc == 1){
                        $log->addLog($_SESSION['id'], $log->logText('BUY_HARDWARE', Array(2, $act, $power, $price, $acc, long2ip($finances->getBankIP($accInfo['0']['bankid'])), long2ip($clanIP))), 0);
                    }
                                        
                    break;

                default:
                    die("Invalid action");
                    break;
            }

        }
        
        header("Location:$postRedirect");        
        exit();
        
    }
        
    private function handlePostCommon($acc, $price, $redirect){

        $system = new System();
        
        if($price != $_POST['price']){
            $system->handleError('Current price does not match buy price. Please, try again.', $redirect);
        }
        
        $finances = new Finances();

        if($finances->totalMoney() < $price){
            $system->handleError('BAD_MONEY', $redirect);
        }

        $accInfo = $finances->bankAccountInfo($acc);

        
        if($accInfo['0']['exists'] == '0' || $accInfo['0']['bankuser'] != $_SESSION['id']){
            $system->handleError('BAD_ACC', $redirect);            
        }
        
        return $accInfo;
        
    }
    
    private function renameXHD($serverID, $serverName){

        $this->session->newQuery();

        $sql = 'UPDATE hardware_external SET name = :serverName WHERE serverID = :serverID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':serverName' => $serverName, ':serverID' => $serverID));            
        
    }
    
    private function renameServer($serverID, $serverName){
        
        $this->session->newQuery();
        $sql = 'UPDATE hardware SET name = :serverName WHERE serverID = :serverID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':serverName' => $serverName, ':serverID' => $serverID));

        
    }
    
    public function store_showPage($specs, $page, $internet = '') {

        require 'hardwareItens.php';

        if($internet != ''){
            $link = '?view=clan&action=upgrade&part='.strtolower($page).'&id=';
            $linkBack = '?view=clan&action=upgrade';
        } else {
            $link = '?opt=upgrade&action=buy&part='.strtolower($page).'&id=';
            $linkBack = '?opt=upgrade';
        }
        
        if($page != 'HDD' && $page != 'RAM'){
        
            ?>

            <div class="span8">
                <div class="widget-box text-left">
            <?php

        }
        
        switch ($page) {

            case 'CPU':
            case 'RAM':
            case 'HDD':    

                switch($page){
                    
                    case 'CPU':
                        $unit = ' MHz';
                        
                        ?>

                            <div class="widget-title">
                                <span class="icon"><span class="he16-upgrade_cpu"></span></span>
                                <h5><?php echo _("Upgrade processor"); ?></h5>
                            </div>
                            <div class="widget-content nopadding">        
                                <table class="table table-bordered table-striped table-hardware table-hover">
                                    <thead>
                                            <tr>
                                                    <th></th>
                                                    <th><?php echo _("Name"); ?></th>
                                                    <th><?php echo _("Power"); ?></th>
                                                    <th><?php echo _("Price"); ?></th>
                                                    <th><?php echo _("Action"); ?></th>
                                            </tr>
                                    </thead>
                                    <tbody>                    

                        <?php                        
                        
                        break;
                    case 'RAM':
                        $unit = ' MB';
                        
                        ?>


                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="widget-box text-left">
                    
                            <div class="widget-title">
                                <span class="icon"><span class="he16-upgrade_ram"></span></span>
                                <h5><?php echo _("Upgrade memory"); ?></h5>
                            </div>
                            <div class="widget-content nopadding">        
                                <table class="table table-bordered table-striped table-hardware table-hover">
                                    <thead>
                                            <tr>
                                                    <th></th>
                                                    <th><?php echo _("Name"); ?></th>
                                                    <th><?php echo _("Power"); ?></th>
                                                    <th><?php echo _("Price"); ?></th>
                                                    <th><?php echo _("Action"); ?></th>
                                            </tr>
                                    </thead>
                                    <tbody>                    

                        <?php                        
                        
                        break;
                    case 'HDD':
                        $unit = ' GB';
                        ?>

                                </tbody>
                            </table>
                        </div> 
                        </div>
                        <div class="widget-box text-left">
                
                            <div class="widget-title">
                                <span class="icon"><span class="he16-upgrade_hdd"></span></span>
                                <h5><?php echo _("Upgrade Hard Drive"); ?></h5>
                            </div>
                            <div class="widget-content nopadding">        
                                <table class="table table-bordered table-striped table-hardware table-hover">
                                    <thead>
                                            <tr>
                                                    <th></th>
                                                    <th><?php echo _("Name"); ?></th>
                                                    <th><?php echo _("Power"); ?></th>
                                                    <th><?php echo _("Price"); ?></th>
                                                    <th><?php echo _("Action"); ?></th>
                                            </tr>
                                    </thead>
                                    <tbody>                    

                        <?php                        
                        
                        break;
                    
                }
                
                $var = strtolower($page).'Itens';
                $itens = $$var;
                
                $i = '1';
                                
                $pow = $specs[$page]; //o preço do meu hard
                
                while (array_key_exists($i, $itens)) {

                    if ($itens[$i]['POW'] >= $pow) {

                        $ico =  '<span class="he16-'.strtolower($page).'" style="margin-top: 4px;"></span>';
                        $name = $itens[$i]['NAME'];
                        $value = '<font color="green">$<span id="price">'.number_format($itens[$i]['PRICE']).'</span></font>';
                        $power = $itens[$i]['POW'];

                        if ($itens[$i]['POW'] > $pow) {
                            $action = '<span class="he16-buy_hardware heicon icon-tab tip-top upgrade-part link" title="Buy" id="'.$itens[$i]['POW'].'" value="'.strtolower($page).'"></span>';                            
                        } else {
                            $action = '&nbsp;';
                            $value = '&nbsp;';
                        }                        
                        
                        $div = 1;
                        if($page == 'HDD'){
                            if($power < 1000){
                                $unit = ' MB';
                            } else {
                                $div = 1000;
                                $unit = ' GB';
                            }
                        }
                        
                        ?>
                            
                        <tr id="<?php echo strtolower($page).str_replace(',', '.', $power); ?>" class="<?php echo $i; ?>">
                            <td><?php echo $ico; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $power/$div.$unit; ?></td>
                            <td><?php echo $value; ?></td>
                            <td><?php echo $action; ?></td>
                        </tr>
                        
                        <?php

                    }

                    $i++;
                }

                if($page == 'CPU'){
                    self::store_showPage($specs, 'HDD', $internet);
                } elseif($page == 'HDD'){
                    self::store_showPage($specs, 'RAM', $internet);
                } else {
                    
                    ?>

                                </tbody>
                            </table>
                            </div>
                        </div>                        
                    
                    <?php
                                            
                }
                
                break;
                
            case 'BUY_PC':    

                ?>
                
                <div class="widget-title">
                    <span class="icon"><span class="he16-buy_server"></span></span>
                    <h5><?php echo _("Buy Server"); ?></h5>
                </div>
                <div class="widget-content padding">                   
                
                <?php
                
                require_once '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                if($internet != ''){
                    
                    require '/var/www/classes/Clan.class.php';
                    $clan = new Clan();
                    
                    $clanInfo = parent::getIDByIP($clan->getClanInfo($clan->getPlayerClan())->clanip, 'NPC');
                    $userID = $clanInfo['0']['id'];
                    
                    $input = '<input type="hidden" name="view" value="clan">';
                    $input .= '<input type="hidden" name="action" value="buy">';
                    
                } else {
                    $userID = '';
                    
                    $input = '<input type="hidden" name="opt" value="buy">';
                }
                
                $price = self::getPCPrice($userID);

                echo _('Price').': <b>$'.number_format($price, '0', '.', ',')."</b><br/><br/>";
                
                if($finances->totalMoney() >= $price){
                    
                    ?>

                    <form action="#buy" method="GET">

                        <?php echo $input; ?>
                        
                        <?php echo _("Account #"); ?>: <?php echo $finances->htmlSelectBankAcc(); ?><br/><br/>

                        <input type="submit" class="btn btn-success" value="<?php echo _("Buy"); ?>...">

                    </form>

                    <?php
                    
                    self::showBuyModal('server', $price, $internet);
                    
                } else {
                    echo _('You dont have enough money');
                }
                
                ?>
                    
                    </div>
                </div>
                    
                <?php
                
                break;
                
            case 'XHD-BUY':

                ?>

                <div class="widget-title">
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5><?php echo _("Buy new external hard drive"); ?></h5>
                </div>
                <div class="widget-content padding">        
        
                <?php
        
                require_once '/var/www/classes/Finances.class.php';
                $finances = new Finances();                
                
                $price = self::getXHDPrice(); 
                
                echo _('Price').': <b>$'.number_format($price, '0', '.', ',')."</b><br/><br/>";
                
                if($finances->totalMoney() >= $price){
                    
                    ?>

                    <form action="#buy" method="GET">

                        <input type="hidden" name="opt" value="xhd">
                        
                        <?php echo _("Account #"); ?>: <?php echo $finances->htmlSelectBankAcc(); ?><br/><br/>

                        <input type="submit" class="btn btn-success" value="<?php echo _("Buy"); ?>...">

                    </form>

                    <?php
                    
                    self::showBuyModal('external hard drive', $price);
                    
                } else {
                    echo 'You dont have enough money';
                }                

                ?>
        
                </div></div>
        
                <?php
                
                break;
                
            case 'XHD-UPG':
                
                require 'hardwareItens.php';
                $system = new System();
                                
                $idInfo = $system->verifyNumericGet('id');
                
                $xhdInfo = self::getXHD($idInfo['GET_VALUE']);
                
                if($idInfo['GET_VALUE'] == 0 || !$xhdInfo['ISSET']){
                    $system->handleError('This external disk does not exists.', 'hardware?opt=xhd');
                }

                                
                $_SESSION['XHD_ID'] = $idInfo['GET_VALUE'];
                
                ?>
                
                
                <div class="widget-title">
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5><?php echo _("Upgrade External Disk"); ?></h5>
                </div>
                <div class="widget-content nopadding">
                    
                    <table class="table table-bordered table-striped table-hardware table-hover">
                        <thead>
                                <tr>
                                        <th></th>
                                        <th><?php echo _("Name"); ?></th>
                                        <th><?php echo _("Power"); ?></th>
                                        <th><?php echo _("Price"); ?></th>
                                        <th><?php echo _("Action"); ?></th>
                                </tr>
                        </thead>
                        <tbody>                  
                
                <?php
                
                $i = '1';
                $pow = $xhdInfo['SIZE'];
                
                while (array_key_exists($i, $xhdItens)) {
                    
                    if($xhdItens[$i]['POW'] >= $pow){

                        $name = $xhdItens[$i]['NAME'];
                        $price = '$<span id="price">'.number_format($xhdItens[$i]['PRICE']).'</span>';
                        $power = $xhdItens[$i]['POW']/1000 .' GB';
                        $link = '';
                        
                        if ($xhdItens[$i]['POW'] > $pow) {
                            $link = '<span class="he16-buy_hardware icon-tab tip-top upgrade-part link" title="'._('Buy').'" id="'.$xhdItens[$i]['POW'].'" value="xhd"></span>';                            
                        } else {
                            $price = '';
                        }

                        ?>
                            
                            <tr id="xhd<?php echo $xhdItens[$i]['POW']; ?>" class="<?php echo $i; ?>">
                                <td><span class="he16-xhd heicon"></span></td>
                                <td><?php echo $name; ?></td>
                                <td><?php echo $power; ?></td>
                                <td><font color="green"><?php echo $price; ?></font></td>
                                <td><?php echo $link; ?></td>
                            </tr>
                            
                        <?php    
                        
                    }

                    $i++;

                }                
                
                ?>
                     
                            </tbody>
                        </table>
                    </div>
                <span id="xid" value="<?php echo $idInfo['GET_VALUE'] - 1; ?>"></span>
                
                <?php
                                
                self::show_changeName($idInfo['GET_VALUE'], 'xhd');
                  
                //$system->changeHTML('link2', $xhdInfo['NAME']);
                
                ?>
                
                </div>
                            
                <?php            
                
                break;
                
            case 'NET':
                
                ?>
                
                <div class="widget-title">
                    <span class="icon"><span class="he16-upgrade_net"></span></span>
                    <h5><?php echo _("Upgrade internet connection"); ?></h5>
                </div>
                <div class="widget-content nopadding">                           
        
                    <table class="table table-bordered table-striped table-hardware table-hover">
                        <thead>
                            <tr>
                                <th></th>
                                <th><?php echo _("Name"); ?></th>
                                <th><?php echo _("Power"); ?></th>
                                <th><?php echo _("Price"); ?></th>
                                <th><?php echo _("Action"); ?></th>
                            </tr>
                        </thead>
                        <tbody>                             
                    
                    
                <?php        
                        
                if($internet != ''){
                    
                    require '/var/www/classes/Clan.class.php';
                    $clan = new Clan();
                    
                    $clanInfo = parent::getIDByIP($clan->getClanInfo($clan->getPlayerClan())->clanip, 'NPC');

                    $userID = $clanInfo['0']['id'];
                    $pcType = 'NPC';
                } else {
                    $userID = $_SESSION['id'];
                    $pcType = 'VPC';
                }
                
                $netInfo = self::getNetSpec($userID, $pcType);

                $i = '1';
                $pow = $netInfo->net;
                
                while (array_key_exists($i, $netItens)) {

                    $value = " ($" . $netItens[$i]['PRICE'] . ") - ";

                    if($netItens[$i]['POW'] >= $pow){

                        $name = $netItens[$i]['NAME'];
                        $power = $netItens[$i]['POW'].' Mbit';
                        $price = '$<span id="price">'.number_format($netItens[$i]['PRICE']).'</span>';
                        
                        if ($netItens[$i]['POW'] > $pow) {
                            $buy = '<span class="he16-buy_hardware icon-tab tip-top upgrade-part link" title="Buy" id="'.$netItens[$i]['POW'].'" value="net"></span>';                            
                        } else {
                            $buy = '';
                            $price = '';
                        }              
                        
                        ?>
                        
                            <tr id="net<?php echo $netItens[$i]['POW']; ?>" class="<?php echo $i; ?>">
                                <td><span class="he16-internet"></span></td>
                                <td><?php echo $name; ?></td>
                                <td><?php echo $power; ?></td>
                                <td><font color="green"><?php echo $price; ?></font></td>
                                <td><?php echo $buy; ?></td>
                            </tr>
                            
                        <?php    

                    }

                    $i++;

                }

                $_SESSION['CUR_PC'] = '0';
                
                ?>
                
                        </tbody>
                    </table>
                    
                    </div>
                </div>
                        
                <?php
                
                break;
                
        }
        
        if($page != 'CPU' && $page != 'HDD'){
            
            ?>
                
                <span id="modal"></span>
            </div>

            <?php

            self::sideBar($page, $internet);
            
            if($internet != ''){
                
                ?>

                        </div>
                    </div>
                </div>
                <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>

                <?php                
                
            }
            
        }
                        
    }

    private function showBuyModal($spec, $price, $internet = 0){

        if(isset($_GET['acc'])){

            $input = '<input type="hidden" name="acc" value="'.$_GET['acc'].'">
                      <input type="hidden" name="price" value="'.$price.'">'."\n";

            switch($spec){
                
                case 'server':
                    
                    $input .= '<input type="hidden" name="act" value="BUY-PC">'."\n";
                    
                    if($internet == 1){
                        $input .= '<input type="hidden" name="clan" value="1">'."\n";
                        
                        $cancelRedirect = 'internet?view=clan&action=buy';
                    } else {
                        $cancelRedirect = 'hardware?opt=buy';
                    }
                    
                    break;
                    
                default:
                    
                    $input .= '<input type="hidden" name="act" value="BUY-XHD">'."\n";
                    $cancelRedirect = 'hardware?opt=xhd';
                    
                    break;
                
                
            }
            
            ?>

            <script>

            window.onload = function () {
                $('#buy').modal('show');
            }           

            </script>

            <div id="buy" class="modal hide" data-backdrop="static">
                    <div class="modal-header">
                            <h3><?php echo _('Confirm buy'); ?></h3>
                    </div>
                    <div class="modal-body">
                            <p><?php echo sprintf(_('Are you sure you want to buy this new %s for %s<strong>$%s</strong></span>?'), _($spec), '<span class="red">', number_format($price)); ?></p>
                    </div>
                    <div class="modal-footer">
                        <form action="#" method="POST">
                            <?php echo $input; ?>
                            <input type="submit" value="<?php echo _('Yes, I want to buy it.'); ?>" class="btn btn-primary">
                            <a class="btn" href="<?php echo $cancelRedirect; ?>"><?php echo _('No, cancel'); ?></a>
                        </form>
                            
                            
                    </div>                
            </div>

            <?php

        }      
        
    }
    
    private function sideBar($page, $internet){

        ?>
        <div class="span4">
            
            <div class="widget-box" style="text-align: left;">
                
                <?php
                if($page == 'XHD-BUY' || $page == 'XHD-UPG'){

                    $xhdStr = self::getXHDInfo();
                                        
                    ?>

                    <div class="widget-title">
                        <span class="label label-info"><?php echo $xhdStr['TOTAL']; ?></span>
                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                        <h5><?php echo _("My external disks"); ?></h5>
                    </div>

                    <div class="widget-content padding"> 

                        <?php 
                        
                        if($xhdStr['TOTAL'] == 0){
                            echo _('You do not have any external disks yet.');
                        } else {
                        
                        ?>
                        
                        <ul class="list">
                            
                            <?php
                            
                            require 'hardwareItens.php';
                            
                            for($i = 0; $i < $xhdStr['TOTAL']; $i++){
                                
                                $power = $xhdStr[$i]['SIZE'] / 1000;
                                
                                $str = ' class="li-click"';
                                if(isset($_GET['id'])){
                                    if($xhdStr[$i]['SID'] == $_GET['id']){
                                        $str = ' class="li-click hard-checked"';
                                    }
                                }
                                
                                ?>
                                <a href="hardware?opt=xhd&id=<?php echo $xhdStr[$i]['SID']; ?>">
                                    <li <?php echo $str; ?>>
                                            <div class="span3 hard-ico">
                                                <span class="he32-xhd32"></span>
                                            </div>
                                            <div class="span9">
                                                <div class="list-ip">
                                                    <?php echo $xhdStr[$i]['NAME']; ?>
                                                </div>
                                                <div class="list-user">
                                                    <span class="he16-xhd" style="margin-top: 4px;"></span>
                                                    <small><?php echo $power; ?> GB</small>
                                                </div>
                                            </div>
                                            <div style="clear: both;"></div>
                                    </li>  
                                </a>
                                <?php
                                                                
                            }
                            
                            ?>
                                                                       
                        </ul>                                                            

                        <?php
                        
                        }
                        
                        ?>
                        
                    </div>
                    
                    <?php

                } else {

                    if($internet == ''){

                        $uid = $_SESSION['id'];
                        $isNPC = 0;
                        $pcType = 'VPC';
                        
                        if(isset($_GET['id'])){
                            $id = (int)$_GET['id'];
                        } else {
                            $id = -1;
                        }
                        
                        $linkBase = 'hardware?opt=upgrade&id=';
                        $title = 'My servers';
                        $span = 'link2';
                                                
                    } else {
                        
                        require_once '/var/www/classes/Clan.class.php';
                        $clan = new Clan();

                        $clanInfo = parent::getIDByIP($clan->getClanInfo($clan->getPlayerClan())->clanip, 'NPC');
                        
                        $uid = $clanInfo['0']['id'];
                        $isNPC = 1;
                        $pcType = 'NPC';
                  
                        if(isset($_GET['server'])){
                            $id = (int)$_GET['server'];
                        } else {
                            $id = -1;
                        }
                        
                        $linkBase = 'internet?view=clan&action=upgrade&server=';
                        $title = 'Clan servers';
                        $span = 'link3';
                    }
                    
                    ?>
                
                    <div class="widget-title">
                        <span class="label label-info" id="total"><?php echo self::getTotalPCs($uid, $pcType); ?></span>
                        <span class="icon"><span class="he16-servers"></span></span>
                        <h5><?php echo _($title); ?></h5>
                    </div>

                    <div class="widget-content padding">
                
                    <?php
                    
                    require 'hardwareItens.php';
                    
                    $this->session->newQuery();
                    $sqlSelect = "SELECT COUNT(*) AS total FROM hardware WHERE userID = $uid AND isNPC = $isNPC";
                    $totalPCs = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;                    

                    $curName = '';
                    for($i=0;$i<$totalPCs;$i++){

                        $offset = (string)$i;
                        
                        $pcInfo = self::getPCSpec('', $pcType, $uid, $offset);
                        
                        $cpu = $pcInfo['CPU'];
                        $hdd = $pcInfo['HDD'] / 1000;
                        if($hdd < 1){
                            $hdd *= 1000;
                            $hddStr = ' MB';
                        } else {
                            $hddStr = ' GB';
                        }
                        $ram = $pcInfo['RAM'];
                        $name = $pcInfo['NAME'];          

                        if($cpu < 1000){
                            $cpuStr = ' MHz';
                        } else {
                            $cpuStr = ' GHz';
                            $cpu /= 1000;
                        }    

                        if($id == $pcInfo['ID']){
                            $style = ' class="li-click hard-checked"';
                            $curName = $name;
                        } else {
                            $style = ' class="li-click"';
                        }

                        ?>

                        <ul class="list">
                            <a href="<?php echo $linkBase . $pcInfo['ID']; ?>">
                                <li <?php echo $style; ?>>
                                    <div class="span2 hard-ico">
                                        <span class="he32-pc"></span>
                                    </div>
                                    <div class="span10">
                                        <div class="list-ip">
                                            <?php echo $name; ?>
                                        </div>
                                        <div class="list-user">
                                            <span class="he16-cpu" style="margin-top: 4px;"></span>
                                            <small><?php echo $cpu.$cpuStr; ?></small>
                                            <span class="he16-hdd" style="margin-top: 4px;"></span>
                                            <small><?php echo $hdd.$hddStr; ?></small>
                                            <span class="he16-ram" style="margin-top: 4px;"></span>
                                            <small><?php echo $ram; ?> MB</small>
                                        </div>
                                    </div>
                                    <div style="clear: both;"></div>
                                </li>
                            </a>
                        </ul>                                                                            

                        <?php

                    }

                    $system = new System();

                    if($page != 'BUY_PC' && $page != 'NET'){
                        //$system->changeHTML($span, $curName);
                    }

                    //$system->changeHTML('total', $totalPCs);

                    ?>

                    </div>

                <?php
                
                }
                
                ?>
                
            </div>
            
            <?php 
            
            if(isset($_GET['opt'])){ 
                
                if($_GET['opt'] == 'upgrade'){
            
                    if(!ctype_digit($_GET['id'])){
                        exit("Invalid id");
                    }
                    
                    self::show_changeName((int)$_GET['id']);
            
                } 
            
            } 
            
            if($internet != ''){
                
                ?>
                <div class="text-left">
                    <button class="btn" onClick="parent.location='internet?view=clan'">Back</button>&nbsp;
                </div>
                <?php
            }            
            
            ?>
            
        </div>
        
        
        <?php
        
    }
    
    public function show_changeName($serverID, $page = ''){
        
        
        
        if($page == 'xhd'){
            
            $widgetTitle = 'Change external disk name';
            
            $xhdInfo = self::getXHD($serverID);
            
            $serverName = $xhdInfo['NAME'];
            $linkDesc = 'Change disk name';
            $postID = 'xID';
            $postName = 'xName';
            
            ?>
      
            </div>
            <div class="row-fluid">

            <?php
            
        } else {
            
            $pcInfo = self::getPCSpec($serverID, 'VPC', $_SESSION['id']);
           
            $serverName = $pcInfo['NAME'];
            $widgetTitle = 'Change server name';
            $linkDesc = 'Change server name';
            $postID = 'sID';
            $postName = 'sName';
            
        }
                
        ?>

        <a href="#changeName" data-toggle="modal"><button class="btn"><i class="icon-refresh"></i> <?php echo _($linkDesc); ?></button></a>

        <div id="changeName" class="modal hide" tabindex="-1">
            <div class="modal-header">
                    <button data-dismiss="modal" class="close" type="button">×</button>
                    <h3><?php echo _($widgetTitle); ?></h3>
            </div>
            <form action="hardware" method="POST">
                <input type="hidden" name ="<?php echo $postID; ?>" value="<?php echo $serverID; ?>">
                <div class="modal-body">
                        <p><?php echo sprintf(_('What would you like to rename \'%s\' for?'), $serverName); ?></p>
                        <input type="text" name="<?php echo $postName; ?>">
                </div>
                <div class="modal-footer">
                        <input type="submit" value="Rename" class="btn btn-primary">
                        <a data-dismiss="modal" class="btn" href="#">Cancel</a>
                </div>  
            </form>
        </div>            
            
        <?php

    }
    
    public function getPCPrice($clan = ''){
        
        if($clan == ''){
            $id = $_SESSION['id'];
            $pcType = 'VPC';
        } else {
            $id = $clan;
            $pcType = 'NPC';
        }
        
        $totalPCs = self::getTotalPCs($id, $pcType);

        if($totalPCs == 5){
            return 500000;
        } elseif($totalPCs == 4){
            return 100000;
        } elseif($totalPCs == 3){
            return 50000;
        }
        
        return pow(10, $totalPCs + 2);
        
    }
    
    
    public function getXHDPrice($total = ''){
  
        if($total == ''){
            $xhdInfo = self::getXHDInfo();
            $total = $xhdInfo['TOTAL'];
        }
  
        if($total == 0){
            return 100;
        } elseif($total == 1){
            return 1000;
        } elseif($total == 2){
            return 10000;
        } elseif($total == 3){
            return 50000;
        } elseif($total == 4){
            return 150000;
        } elseif($total == 5) {
            return 300000;
        } else {
            return 500000;
        }
        
        $n = $total+1;

        return pow(10, $n + 2);

    }
    
    public function getXHDUsage(){
        
        $this->session->newQuery();
        $sql = "SELECT SUM(softSize) AS total FROM software_external WHERE userID = ".$_SESSION['id'];
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }
    
    public function getXHDInfo(){

        $this->session->newQuery();
        $sql = "SELECT serverID, name, size FROM hardware_external WHERE userID = '".$_SESSION['id']."'";
        $data = $this->pdo->query($sql)->fetchAll();

        $return = Array();
        
        $totalXHD = sizeof($data);
        
        $return['TOTAL'] = $totalXHD;
        
        if($totalXHD > 0){
            
            for($i=0;$i < $totalXHD;$i++){
                
                $return[$i]['SID'] = $data[$i]['serverid'];
                $return[$i]['SIZE'] = $data[$i]['size'];
                $return[$i]['NAME'] = $data[$i]['name'];

            }
            
        }
        
        return $return;
        
    }
    
    public function getXHD($serverID){
        
        $this->session->newQuery();
        $sql = "SELECT name, size FROM hardware_external WHERE serverID = '".$serverID."'";
        $data = $this->pdo->query($sql)->fetchAll();

        $return = Array();
        
        $totalXHD = sizeof($data);
                
        if($totalXHD > 0){
      
            $return['ISSET'] = TRUE;
            $return['SIZE'] = $data['0']['size'];
            $return['NAME'] = $data['0']['name'];
            
        } else {
            $return['ISSET'] = FALSE;
        }
        
        return $return;
        
    }

    public function addServer($specs, $internet = 0){
        
        if($internet != 0){
            
            $clan = new Clan();
            
            $userID = parent::getIDByIP($clan->getClanInfo($clan->getPlayerClan())->clanip, 'NPC');
            $userID = $userID['0']['id'];
            
            $npc = 1;
            
        } else {
            
            $userID = $_SESSION['id'];
            $npc = 0;
            
        }
        
        $this->session->newQuery();
        $sql = "    INSERT INTO hardware
                        (userID, name, cpu, net, hdd, ram, isNPC)
                    VALUES
                        ($userID, '".$specs['NAME']."', '".$specs['CPU']."', '', '".$specs['HDD']."', '".$specs['RAM']."', $npc)";
        $this->pdo->query($sql);
        
    }
    
    public function updateHardwareSpecs($serverID, $id, $pcType, $string, $part, $type='') {

        $isNPC = '0';
        if ($pcType == 'NPC') {
            $isNPC = '1';
        }

        if($type == ''){
        
            switch($part){
                case 'ram':
                case 'RAM':
                    $field = 'ram';
                    break;
                case 'cpu':
                case 'CPU':
                    $field = 'cpu';
                    break;
                case 'hdd':
                case 'HDD':
                    $field = 'hdd';
                    break;
            }            
            
            $this->session->newQuery();
            $sql = "UPDATE hardware SET ".$field." = '" . $string . "' WHERE serverID = $serverID";
            $this->pdo->query($sql);
        
        } else {
            
            switch($type){
                
                case 'XHD':
                    
                    $this->session->newQuery();
                    $sql = "UPDATE hardware_external SET size = '" . $string . "' WHERE serverID = $serverID";
                    $this->pdo->query($sql);
                    
                    break;
                
                case 'NET':
                    
                    $this->session->newQuery();
                    $sql = "UPDATE hardware SET net = '" . $string . "' WHERE userID = $id AND isNPC = $isNPC";
                    $this->pdo->query($sql);

                    break;
                
            }
            
        }
    }

    public function updateHardwareNet($id, $pcType, $netID) {

        $isNPC = '0';
        if ($pcType == 'NPC') {
            $isNPC = '1';
        }

        $this->session->newQuery();
        $sql = "UPDATE hardware SET net = '" . $netID . "' WHERE userID = $id AND isNPC = $isNPC";
        $this->pdo->query($sql);
    }

    public function checkHDDForOverclock($id, $pcType, $oldHardwareInfo) {

        $isNPC = '0';
        if ($pcType == 'NPC') {
            $isNPC = '1';
        }

        $curHardwareInfo = self::getHardwareInfo($id, $pcType);

        $softDiff = $oldHardwareInfo['HDD'] - $curHardwareInfo['HDD'];

        if ($softDiff != '0') {

            $hddUsage = self::calculateHDDUsage($id, $pcType);

            $software = new SoftwareVPC();

            while ($hddUsage['AVAILABLE'] < '0') {

                $this->session->newQuery();
                $sql = "SELECT id, softSize, softType, originalFrom, softName, softVersion FROM software WHERE userID = $id AND isNPC = $isNPC ORDER BY softSize DESC LIMIT 1";
                $selectedSoft = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                $this->session->newQuery();
                $sql = "DELETE FROM software WHERE id = '" . $selectedSoft->id . "' LIMIT 1";
                $this->pdo->query($sql);

                if ($software->isInstalled($selectedSoft->id, $id, $pcType)) {

                    $this->session->newQuery();
                    $sql = "DELETE FROM software_running WHERE softID = '" . $selectedSoft->id . "' LIMIT 1";
                    $this->pdo->query($sql);
                    
                }
                                
                // 2019: It's a virus
                if($selectedSoft->softtype > 95){ //é virus
                                        
                    $this->session->newQuery();
                    $sql = "SELECT installedIp FROM virus WHERE virusID = '".$selectedSoft->id."'";
                    $ip = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->installedip;
                    
                    $this->session->newQuery();
                    $sql = "DELETE FROM virus WHERE virusID = '".$selectedSoft->id."'";
                    $this->pdo->query($sql);
                    
                    $this->session->newQuery();
                    $sql = "UPDATE lists SET virusID = 0 WHERE virusID = '".$selectedSoft->id."'";
                    $this->pdo->query($sql);
                    
                    $this->session->newQuery();
                    $sql = "INSERT INTO lists_notifications (userID, ip, notificationType, virusName)
                            VALUES ('".$selectedSoft->originalfrom."', '".$ip."', '3', '".$selectedSoft->softname.' ('.$software->dotVersion($selectedSoft->softversion).')'."')";
                    $this->pdo->query($sql);
                    
                    if($selectedSoft->softtype == 97){ //vddos
                        
                        $this->session->newQuery();
                        $sql = "DELETE FROM virus_ddos WHERE ddosID = '".$selectedSoft->id."'";
                        $this->pdo->query($sql);
                        
                    }
                    
                // 2019: The doom 29 was deleted and now we need to disable the doom attack
                } elseif($selectedSoft->softtype == 29){//foi deletado o doom29 do software, agora precisa desabilitar o ataque doom
   
                    $virus = new Virus();
                    $virus->doom_disable($selectedSoft->id, $id);

                // 2019: Webserver deleted
                } elseif($selectedSoft->softtype == 18){ //deletou o webserver
                    
                    require_once '/var/www/classes/Internet.class.php';
                    $internet = new Internet();
                    
                    $internet->webserver_shutdown($id);
                    
                }
                
                $hddUsage['AVAILABLE'] += $selectedSoft->softsize;
                
            }
        }
    }

    public function checkRamForOverclock($id, $pcType, $numPCs, $oldHardwareInfo) {

        $isNPC = '0';
        if ($pcType == 'NPC') {
            $isNPC = '1';
        }

        $curHardwareInfo = self::getHardwareInfo($id, $pcType);

        $ramDiff = $oldHardwareInfo['RAM'] - $curHardwareInfo['RAM'];

        if ($ramDiff != '0') {

            $ramUsage = self::calculateRamUsage($id, $pcType);

            while ($ramUsage['AVAILABLE'] < '0') {

                $this->session->newQuery();
                $sql = "SELECT id, ramUsage FROM software_running WHERE userID = $id AND isNPC = $isNPC ORDER BY ramUsage DESC LIMIT 1";
                $selectedSoft = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                $this->session->newQuery();
                $sql = "DELETE FROM software_running WHERE id = '" . $selectedSoft->id . "' LIMIT 1";
                $this->pdo->query($sql);

                $ramUsage['AVAILABLE'] += $selectedSoft->ramusage;
            }
        }
    }
    
    public function buyPC($bankAcc, $internet = ''){

        $stdPrice = self::getPCPrice();
        $sqlString = '';
        
        $name = 'Server #';
        
        require_once '/var/www/classes/Ranking.class.php';
        $ranking = new Ranking();

        $ranking->updateMoneyStats('1', $stdPrice);              
        
        require_once '/var/www/classes/Finances.class.php';
        $finances = new Finances();
        
        if($internet != ''){
            $pcType = 'NPC';
            $id = $internet;
        } else {
            $pcType = 'VPC';
            $id = $_SESSION['id'];
        }

        $numPCs = self::getTotalPCs($id, $pcType);

        $specs = Array(
            'NAME' => 'Server #'.($numPCs + 1),
            'CPU' => 500,
            'HDD' => 100,
            'RAM' => 256
        );
        
        self::addServer($specs, $internet);
        $finances->debtMoney($stdPrice, $bankAcc);
        
        $this->session->exp_add('BUY', Array('pc', $numPCs + 1));
        
    }
    
    public function buyXHD($bankAcc, $price, $total){
                
        $name = 'External #'. ($total + 1);
        
        $this->session->newQuery();
        $sql = "INSERT INTO hardware_external (userID, serverID, name)
                VALUES ('".$_SESSION['id']."', '', '".$name."')";
        $this->pdo->query($sql);
        
        require_once '/var/www/classes/Finances.class.php';
        $finances = new Finances();
        
        $finances->debtMoney($price, $bankAcc);
        
        require_once '/var/www/classes/Ranking.class.php';
        $ranking = new Ranking();

        $ranking->updateMoneyStats(1, $price);        
        
        $this->session->exp_add('BUY', Array('xhd', $total + 1));
        
    }
    
    public function upgradeHardware($id, $itemInfo, $part, $internet = ''){
     
        echo 'This function will stay obsolete soon. 14-10-2013';
        
        require_once '/var/www/classes/Finances.class.php';
        $finances = new Finances();
        
        echo "Buy new $part for $".$itemInfo[$id]['PRICE']."<br/><br/>";
        
        if($part == 'net' || $part == 'xhd'){
            $link = 'devices';
        } else {
            $link = 'upgrade';
        }

        if($finances->totalMoney() >= $itemInfo[$id]['PRICE']){

            ?>

            <form action="" method="GET">

                <?php
                if($internet == 1){ ?>
                    
                <input type="hidden" name="view" value="clan">
                <input type="hidden" name="action" value="upgrade">
                <input type="hidden" name="part" value="<?php echo $part; ?>">
                
                <?php } else { ?>
                <input type="hidden" name="opt" value="<?php echo $link; ?>">
                <input type="hidden" name="action" value="buy">
                <input type="hidden" name="part" value="<?php echo $part; ?>">

                <?php }
                ?>

                <input type="hidden" name="id" value="<?php echo $id; ?>">

                Bank acc: <?php echo $finances->htmlSelectBankAcc(); ?><br/>

                <input type="submit" value="Upgrade">

            </form>

            <?php

        } else {
            echo 'You dont have money for this new hardware';
        }
        
    }
    
    public function commitUpgrade($id, $itemInfo, $part, $bankAccount, $internet = 0){
        
        require_once '/var/www/classes/Finances.class.php';
        $finances = new Finances();

        require_once '/var/www/classes/Ranking.class.php';
        $ranking = new Ranking();

            
        
        if(isset($_SESSION['CUR_PC'])){
            $pc = $_SESSION['CUR_PC'];
        } else {
            $pc = 0;
        }

        if($internet != 0){
            $userID = $internet;
            $pcType = 'NPC';
            $redirect = 'internet?view=clan';
            $unknownID = '';
        } else {
            $userID = $_SESSION['id'];
            $pcType = 'VPC';
            $redirect = 'hardware';
            $unknownID = '';
        }
            
        $oldSpecs = self::getPCSpec($pc, $pcType, $userID, $unknownID);
        $newPower = $itemInfo[$id]['POW'];

        if($internet != 0 && $oldSpecs['ISSET'] == 0){
            //$this->session->addMsg('You can only upgrade YOUR clan server. Sorry.', 'error');
            //header("Location:internet");
            //exit();
        }
        
        $type = '';
        
        switch($part){
            
            case 'cpu':
                
                if($oldSpecs['CPU'] < $newPower){
                    $oldSpecs['CPU'] = $newPower;
                } else {
                    $system = new System();
                    $system->handleError('DOWNGRADE', $redirect);
                }
                break;
                
            case 'hdd':
                
                if($oldSpecs['HDD'] < $newPower){
                    $oldSpecs['HDD'] = $newPower;
                } else {
                    $system = new System();
                    $system->handleError('DOWNGRADE', $redirect);
                }
                break;
                
            case 'ram':
                
                if($oldSpecs['RAM'] < $newPower){
                    $oldSpecs['RAM'] = $newPower;
                } else {
                    $system = new System();
                    $system->handleError('DOWNGRADE', $redirect);
                }
                
                break;
                
            case 'xhd':

                if(isset($_SESSION['XHD_ID'])){
                
                    $xhdID = $_SESSION['XHD_ID'];

                    $xhdInfo = self::getXHD($xhdID);

                    if($xhdInfo['ISSET'] == 0){
                        header("Location:hardware");
                        exit();
                    }
                    
                    if($xhdInfo['SIZE'] >= $newPower){
                        $system = new System();
                        $system->handleError('DOWNGRADE', $redirect);
                    }                    
                    
                    $pc = $xhdID;
                    
                } else {
                    header("Location:hardware");
                    exit();
                }

                $type = 'XHD';
                
                break;
                
            case 'net':
                
                $netInfo = self::getNetSpec($userID, $pcType);
                
                if($netInfo->net >= $newPower){
                    $system = new System();
                    $system->handleError('DOWNGRADE', $redirect);
                }
                
                $type = 'NET';
                
                break;
            
        }

        self::updateHardwareSpecs($pc, $userID, $pcType, $newPower, $part, $type);
        $finances->debtMoney($itemInfo[$id]['PRICE'], $bankAccount);
        
        $ranking->updateMoneyStats('1', $itemInfo[$id]['PRICE']);  
        
        $this->session->exp_add('BUY', Array('hardware', $itemInfo[$id]['PRICE']));

    }
    
}

class SoftwareVPC extends Player {

    public $softID;
    public $softName;
    public $softVersion;
    public $softType;
    public $session;
    private $hardware;
    private $mission;

    function __construct($id = '') {

        if (is_numeric($id)) {

            parent::__construct($id);
        } else {

            
            $this->pdo = PDO_DB::factory();
        }

        $this->session = new Session();
        $this->hardware = new HardwareVPC();
    }
    
    public function handlePost($page = ''){
        
        $system = new System();
        
        if($page == ''){
            $redirect = 'software';
        } elseif($page == 'university') {
            $redirect = 'university';
        } elseif($page == 'internet'){
            $redirect = 'internet';
        }

        if(!isset($_POST['act'])){
            $system->handleError('');
        }
        
        if($_POST['act'] == 'buy'){ //nothing to do here
            $ranking = new Ranking();
            $ranking->handlePost();
        }
        
        $act = $_POST['act'];
        
        switch($act){

            case 'delete-ddos':
                
                $virus = new Virus();
                $virus->delete_ddos_reports();
                
                $this->session->addMsg('All DDoS reports deleted.', 'notice');
                
                break;            
            case 'buy-license':
                
                if(!isset($_POST['id'])){
                    $system->handleError('Missing information.', $redirect);
                }
                
                $softID = $_POST['id'];
                
                if(!ctype_digit($softID)){
                    $system->handleError('Invalid software ID.', $redirect);
                }
                
                $external = FALSE;
                if(!self::issetSoftware($softID, $_SESSION['id'], 'VPC')){
                    if(!self::issetExternalSoftware($softID)){
                        $system->handleError('This software does not exists.', $redirect);
                    }
                    $external = TRUE;
                }

                $redirect .= '?id='.$softID;
                
                if(!isset($_POST['acc'])){
                    $system->handleError('Missing bank account.', $redirect);                    
                }
                
                $acc = $_POST['acc'];
                
                if(!ctype_digit($acc)){
                    $system->handleError('Invaaalid bank account.', $redirect);
                }
                
                $finances = new Finances();
                
                $accInfo = $finances->bankAccountInfo($acc);

                if (($accInfo['0']['exists'] == '0') || ($accInfo['0']['bankuser'] != $_SESSION['id'])) {
                    $system->handleError('This bank account does not exists.', $redirect);
                }
                                
                if($external){
                    $softInfo = self::getExternalSoftware($softID);
                } else {
                    $softInfo = self::getSoftware($softID, $_SESSION['id'], 'VPC');
                }
                
                if(!self::isResearchable($softInfo->softtype)){
                    $system->handleError('You are not allowed to buy a license of this software.', $redirect);
                }
                
                $price = self::license_studyPrice($softInfo->softtype, $softInfo->softversion, $softInfo->licensedto);

                if($price > $finances->totalMoney()){
                    $system->handleError('You dont have enough money.', $redirect);
                }

                self::license_buy($softInfo, $external, $price, Array($acc, long2ip($finances->getBankIP($accInfo['0']['bankid']))));
                $finances->debtMoney($price, $acc);
                
                $this->session->addMsg('License bought.', 'notice');
                
                $this->session->exp_add('BUY', Array('license', $softInfo->softversion));
                
                break;
            case 'research':

                if(!isset($_POST['id'])){
                    $system->handleError('Missing information.', $redirect);
                }
                
                $softID = $_POST['id'];
                
                if(!ctype_digit($softID)){
                    $system->handleError('Invalid software ID.', $redirect);
                }
                
                if(!self::issetSoftware($softID, $_SESSION['id'], 'VPC')){
                    $system->handleError('This software does not exists.', $redirect);                    
                }
                
                $redirect .= '?id='.$softID;
                
                if(!isset($_POST['name'])){
                    $system->handleError('Please insert a name for your software.', $redirect);
                }
                
                $name = $_POST['name'];
                
                if(empty($name)){
                    $system->handleError('Missing software name.', $redirect);
                }
                
                if(!$system->validate($name, 'software')){
                    $system->handleError('Invalid software name. Allowed characters are <strong>azAZ09 _-</strong> startin with <strong>azAZ09</strong>.', $redirect);
                }
                
                $softInfo = self::getSoftware($softID, $_SESSION['id'], 'VPC');
                
                if(!self::isResearchable($softInfo->softtype)){
                    $system->handleError('You can not research this type of software.', $redirect);
                }
                
                $softExtension = self::getExtension($softInfo->softtype);

                $checkExtension = explode($softExtension, $name);
                
                if(array_key_exists('1', $checkExtension)){
                    $system->handleError('Do not insert the extension on the software name.', $redirect);
                }

                $name = $checkExtension[0];
                                
                if(!isset($_POST['delete'])){
                    $deleteOldVersion = 0;
                } else {
                    $deleteOldVersion = 1;
                }

                $price = self::research_calculatePrice($softInfo->softversion, $softInfo->softtype);
                
                if(!isset($_POST['acc'])){
                    $system->handleError('Missing bank account.', $redirect);
                }
                
                $acc = $_POST['acc'];
                
                if(!ctype_digit($acc)){
                    $system->handleError('Invaaaalid bank account.', $redirect);
                }
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();

                if($finances->totalMoney() < $price){
                    $system->handleError('You do not have enough money.', $redirect);
                }        

                $accInfo = $finances->bankAccountInfo($acc);

                if($accInfo['0']['exists'] == '0'){
                    $system->handleError('This account does not exists.', $redirect);
                }        

                $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                    $system->handleError('This account does not exists.', $redirect);
                }                                                
                
                require '/var/www/classes/Process.class.php';
                $process = new Process();
                
                if ($process->newProcess($_SESSION['id'], 'RESEARCH', '', 'local', $softID, $name, $acc.'/'.$deleteOldVersion, '0')) {
                    $this->session->addMsg('Software is being researched.', 'notice');
                } else {
                    $this->session->addMsg('This software is already being researched.', 'error');
                }       
                
                $pid = $this->session->processID('show');                    
                
                $redirect = 'processes?pid='.$pid;
                
                break;
            
            case 'create-text':
                
                if(!isset($_POST['name'])){
                    $system->handleError('Please insert a name for your text file.', $redirect);
                }
                
                if(!isset($_POST['text'])){
                    $system->handleError('Please insert a text for your text file', $redirect);
                }
                
                $name = $_POST['name'];
                $textUnfiltered = $_POST['text'];
                
                if(empty($name) || empty($textUnfiltered)){
                    $system->handleError('Invalid information', $redirect);
                }
                
                if(!$system->validate($name, 'software')){
                    $system->handleError('Invalid text-file name. Allowed are <strong>azAZ09- _</strong>, starting with <strong>azAZ09</strong>.', $redirect);
                }
                
                require '/var/www/classes/Purifier.class.php';
                $purifier = new Purifier();
                $purifier->set_config('software-text');
                
                $text = $purifier->purify($textUnfiltered);
                
                if($page == 'internet'){
                    
                    $player = new Player();
                    $playerInfo = $player->getIDByIP($_SESSION['LOGGED_IN'], '');
                    
                    $id = $playerInfo['0']['id'];
                    $pcType = $playerInfo['0']['pctype'];
                    
                } else {
                    
                    $id = $_SESSION['id'];
                    $pcType = 'VPC';
                    
                }
                
                self::text_add($name, $text, $id, $pcType);

                $this->session->addMsg('Text file created.', 'notice');
                
                break;
            case 'edit-text':
                
                if(!isset($_POST['name'])){
                    $system->handleError('Please insert a name for your text file.', $redirect);
                }
                
                if(!isset($_POST['text'])){
                    $system->handleError('Please insert a text for your text file.', $redirect);
                }
                
                $name = $_POST['name'];
                $textUnfiltered = $_POST['text'];
                
                if(empty($name) || empty($textUnfiltered)){
                    $system->handleError('Invalid information.', $redirect);
                }
                
                if(!$system->validate($name, 'software')){
                    $system->handleError('Invalid text-file name. Allowed are <strong>azAZ09- _</strong>, starting with <strong>azAZ09</strong>.', $redirect);
                }
                
                require '/var/www/classes/Purifier.class.php';
                $purifier = new Purifier();
                $purifier->set_config('software-text');
                
                $text = $purifier->purify($textUnfiltered);
                
                if(!isset($_POST['id'])){
                    $system->handleError('Invalid information.', $redirect);
                }
                
                $id = $_POST['id'];
                
                if(!ctype_digit($id)){
                    $system->handleError('Invalid file ID.', $redirect);
                }
                
                $this->session->newQuery();
                $sql = "SELECT software_texts.text, software.softName, software.userID, software.isNPC
                        FROM software_texts 
                        INNER JOIN software ON software.id = software_texts.id
                        WHERE software_texts.id = '".$id."' 
                        LIMIT 1";
                $txtInfo = $this->pdo->query($sql)->fetchAll();
                
                if(sizeof($txtInfo) == 0){
                    $system->handleError('This text file does not exists.', $redirect);
                }
                
                if($name == $txtInfo['0']['softname'] && $text == $txtInfo['0']['text']){
                    $system->handleError('There were no changes to the edited file.', $redirect);
                }

                self::text_edit($name, $text, $id);
                                
                $this->session->addMsg('Text file edited.', 'notice');
                
                if($page == 'internet'){
                    $redirect = 'internet?view=software&cmd=txt&txt='.$id;
                } else {
                    $redirect = 'software?action=text&view='.$id;
                }

                break;
            case 'create-folder':
                
                if(!isset($_POST['name'])){
                    $system->handleError('The folder name can\'t be empty.', $redirect);
                }
                
                $name = $_POST['name'];
                
                if(empty($name)){
                    $system->handleError('Invalid information', $redirect);
                }
                
                if(!$system->validate($name, 'software')){
                    $system->handleError('Invalid folder name. Allowed characters are <strong>azAZ09- _</strong>, starting with <strong>azAZ09</strong>.', $redirect);
                }
                
                if($page == 'internet'){
                    
                    $player = new Player();
                    $playerInfo = $player->getIDByIP($_SESSION['LOGGED_IN'], '');
                    
                    $id = $playerInfo['0']['id'];
                    $pcType = $playerInfo['0']['pctype'];
                    
                } else {
                    
                    $id = $_SESSION['id'];
                    $pcType = 'VPC';
                    
                }
                
                self::folder_create($name, $id, $pcType);
                
                $this->session->addMsg('Folder created.', 'notice');                

                break;
            case 'edit-folder':
                
                if(!isset($_POST['name'])){
                    $system->handleError('The folder name can\'t be empty.', $redirect);
                }                
                
                $name = $_POST['name'];
                
                if(empty($name)){
                    $system->handleError('Invalid information', $redirect);
                }
                
                if(!$system->validate($name, 'software')){
                    $system->handleError('Invalid folder name. Allowed are <strong>azAZ09- _</strong>, starting with <strong>azAZ09</strong>.', $redirect);
                }
                
                if(!isset($_POST['id'])){
                    $system->handleError('Missing folder ID.', $redirect);
                }

                $id = $_POST['id'];
                
                if(!ctype_digit($id)){
                    $system->handleError('Invalid Folder ID.', $redirect);
                }
                
                $this->session->newQuery();
                $sql = "SELECT userID, softname FROM software WHERE id = '".$id."' LIMIT 1";
                $data = $this->pdo->query($sql)->fetchAll();

                if(sizeof($data) == 0){
                    $system->handleError('This folder does not exists.', $redirect);
                }
                
                if($name == $data['0']['softname']){
                    $system->handleError('The folder already have this name.', $redirect);
                }
                
                if($data['0']['userid'] == $_SESSION['id']){
                    $local = TRUE;
                } else {
                    $local = FALSE;
                }
                
                self::folder_edit($id, $name, $local);
                
                $this->session->addMsg('Folder name edited.', 'notice');
                
                if($page == 'internet'){
                    $redirect = 'internet?view=software&cmd=folder&folder='.$id;
                } else {
                    $redirect = 'software?action=folder&view='.$id;
                }
                
                break;
            case 'delete-folder':
                
                $folderID = $_POST['id'];

                if(!ctype_digit($folderID)){
                    $system->handleError('Invalid ID.', $redirect);
                }

                if($page == 'internet'){
                    
                    if(!$this->session->isInternetLogged()){
                        $system->handleError('You are not connected to anyone.', $redirect);
                    }
                    
                    $player = new Player();
                    $playerInfo = $player->getIDByIP($_SESSION['LOGGED_IN'], '');
                    
                    $id = $playerInfo['0']['id'];
                    if($playerInfo['0']['pctype'] == 'NPC'){
                        $npc = 1;
                    } else {
                        $npc = 0;
                    }
                    
                    $redirect = 'internet?view=software';
                    
                } else {
                    $id = $_SESSION['id'];
                    $npc = 0;
                }
                
                if(!self::folder_isset($folderID, $id, $npc)){
                    $system->handleError('This folder does not exists.', $redirect);
                }
                
                if($page == 'internet'){
                    
                    $internet = new Internet();
                    
                    if(!$internet->havePermissionTo('delete_folder')){
                        $system->handleError('You do not have permission to delete this folder.', $redirect);
                    }
                    
                }
                
                //TODO: check \/
                if(self::folder_used($folderID)){
                    $system->handleError('This folder have softwares that need to be deleted before.', $redirect);
                }
                
                self::folder_delete($folderID, $id, $npc);

                $this->session->addMsg('Folder deleted.', 'notice');
                
                break;
            case 'move-folder':
                
                if(!isset($_POST['view']) || !isset($_POST['id'])){
                    $system->handleError('Missing fields.', $redirect);
                }
                                
                if(isset($_POST['cmd'])){
                    
                    $remote = TRUE;
                    
                    if(!isset($_POST['folder'])){
                        $system->handleError('Missing fields.', $redirect);
                    }
                    
                    $folderID = $_POST['folder'];
                    
                    if(!$this->session->isInternetLogged()){
                        $system->handleError('You are not logged in.', $redirect);
                    }
                    
                    $victimIP = $_SESSION['LOGGED_IN'];
                    
                    $victimInfo = parent::getIDByIP($victimIP, '');
                    
                    if($victimInfo['0']['existe'] == 0){
                        $system->handleError('Invalid IP location.', $redirect);
                    }
                    
                    $id = $victimInfo['0']['id'];
                    $npcStr = $victimInfo['0']['pctype'];
                    
                    if($npcStr == 'NPC'){
                        $npc = 1;
                    } else {
                        $npc = 0;
                    }
                    
                } else {
                    $remote = FALSE;
                    $id = $_SESSION['id'];
                    $npc = 0;
                    $npcStr = 'VPC';
                    
                    $folderID = $_POST['view'];
                }
                
                if(!ctype_digit($folderID)){
                    $system->handleError('Invalid Folder ID.', $redirect);
                }
                
                if(!self::folder_isset($folderID, $id, $npc)){
                    $system->handleError('This folder does not exists.', $redirect);
                }
                
                if($remote){
                    $redirect .= '?view=software&cmd=folder&folder='.$folderID;
                } else {
                    $redirect .= '?action=folder&view='.$folderID;
                }
                
                $moveID = $_POST['id'];
 
                if(!ctype_digit($moveID)){
                    $system->handleError('Please select a software.', $redirect);
                }
                
                if(!self::issetSoftware($moveID, $id, $npcStr)){
                    $system->handleError('The software you are trying to move does not exists.', $redirect);
                }
                
                $softInfo = self::getSoftware($moveID, $id, $npcStr);
                
                if($softInfo->softtype > 30 || $softInfo->softtype == 26 || $softInfo->softtype == 19){
                    $system->handleError('You cant move this kind of software.', $redirect);
                } elseif($softInfo->softhiddenwith > 0){
                    $system->handleError('You cant move hidden software.', $redirect);
                } elseif($softInfo->isfolder != '0'){
                    $system->handleError('This software is already in a folder.', $redirect);
                }
                
                self::folder_move($folderID, $moveID);
                
                $this->session->addMsg(sprintf(_('Software %s moved to folder.'), $softInfo->softname.self::getExtension($softInfo->softtype)), 'notice');
                
                break;
            
        }
        
        header("Location:$redirect");
        exit();
        
    }
    
    public function licensedTo($softID){
        
        $this->session->newQuery();
        $sql = "SELECT licensedTo FROM software WHERE id = $softID LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            
            return $data['0']['licensedto'];
            
        // 2019: Either it's in the external HD or this shit does not exist
        } else { //ou tá no hd externo, ou não existe çaporra
            
            $this->session->newQuery();
            $sql = "SELECT licensedTo FROM software_external WHERE id = $softID LIMIT 1";
            $data = $this->pdo->query($sql)->fetchAll();
            
            if(sizeof($data) == 1){
                
                return $data['0']['licensedto'];
                
            } else {
                die("Software dooesnt exists");
            }
            
        }
        
        
    }
    
    public function isVirus($softType) {

        switch ($softType) {

            case '8':
            case '9':
            case '10':
            case '20': //vminer
            case '29': //vdoom
                return TRUE;
                break;
            default:
                return FALSE;
                break;
        }
    }

    public function isVirusInstalled($softType) {

        switch ($softType) {

            case '99':
            case '98':
            case '97':
            case '96':
            case '90':
                return TRUE;
                break;
            default:
                return FALSE;
                break;
        }
    }

    public function isSpecialExecutable($softType) {

        switch ($softType) {

            case '7': //av
            case '11': //coll
            case '12': //ddosbreaker
            case '15':
            case '16':
            case '19': //btc wallet
            case '29':    
                return TRUE;
                break;
            default:
                return FALSE;
                break;
        }
    }

    public function isExecutable($softType, $softID, $local) {

        if (self::isVirusInstalled($softType) || $softType == 30 || $softType == 31 || $softType == 17){ //nao instala os virus
            return FALSE;
        }
        
        if($softType == 29){ //se for doom

            if(self::licensedTo($softID) != $_SESSION['id']){
                return FALSE;
            }

            // 2019: Installing doom on my own server; I'll check if the virus is licensed to myself
            // 2019: Note: I think this comment is wrong. Local = 0 means remote.
            if($local == 0){ //instalando um doom no meu próprio pc, verifico se a id do virus ($softID) esta licenciado pra mim ($_SESS)
                
                $hackedIP = $_SESSION['LOGGED_IN'];

                $ipInfo = parent::getIDByIP($hackedIP, '');
                
                if($ipInfo['0']['existe'] == 1){

                    if($ipInfo['0']['pctype'] == 'VPC'){

                        return FALSE;

                    } else {

                        require_once '/var/www/classes/Clan.class.php';
                        $clan = new Clan();
                        
                        if($clan->playerHaveClan()){
                            
                            if($clan->getClanInfo($clan->getPlayerClan())->clanip != $hackedIP){
                                return FALSE;
                            }
                            
                        } else {
                            return FALSE;
                        }

                    }

                }

            }
            
        } elseif($softType == 11 && $local == 0){ //virus collector no ip remoto
            return FALSE;
        }
        
        return TRUE;
        
    }

    public function getSpecificSoftwareID($uid, $softInfo){
                
        $this->session->newQuery();
        $sql = "SELECT id 
                FROM software 
                WHERE userID = '".$uid."' AND softType = $softInfo->softtype AND softVersion = $softInfo->softversion AND isNPC = '1' AND softName = '".$softInfo->softname."' 
                LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(count($data) == 1 && $uid == $softInfo->npcid){
            return $data['0']['id'];
        } else {
            return 0;
        }
        
    }
    
    public function getSoftwareOriginalInfo($softID) {

        $this->session->newQuery();
        $sqlSelect = "SELECT npcID, softName, softVersion, softType FROM software_original WHERE id = $softID LIMIT 1";

        return $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function int2stringSoftwareType($softType){
        
        switch($softType){
            
            case 1:
                return _('Cracker');
            case 2:
                return _('Hasher');
            case 3:
                return _('Port Scan');
            case 4:
                return _('Firewall');
            case 5:
                return _('Hidder');
            case 6:
                return _('Seeker');
            case 7:
                return _('Antivirus');
            case 8:
                return _('Spam virus');
            case 9:
                return _('Warez virus');
            case 10:
                return _('DDoS virus');
            case 11:
                return _('Virus collector');
            case 12:
                return _('DDoS breaker');
            case 13:
                return _('FTP Exploit');
            case 14:
                return _('SSH Exploit');
            case 15:
                return _('Nmap');
            case 16:
                return _('Analyzer');
            case 17:
                return _('Torrent');
            case 18:
                return _('Web server');
            case 19:
                return _('Bitcoin Wallet');
            case 20:
                return _('Bitcoin Miner');
            case 26:
                return _('Riddle');
            case 29:
                return _('Doom');
            case 30:
                return _('Text file');
            case 31:
                return _('Folder');
            case 96:
                return _('Doom virus');
            case 97:
            case 98:
            case 99:
                return _('Virus');
            default:
                return _('Unknown');
            
        }
        
    }
    
    public function ext2int($softExt){
                
        switch($softExt){

            case 'crc':
                return 1;
            case 'pec':
                return 2;
            case 'psc':
                return 3;
            case 'fwl':
                return 4;
            case 'hdr':
                return 5;
            case 'skr':
                return 6;
            case 'av':
                return 7;
            case 'vspam':
                return 8;
            case 'vwarez':
                return 9;
            case 'vddos':
                return 10;
            case 'vcol':
                return 11;
            case 'vbrk':
                return 12;
            case 'exp':
                return 13;
            case 'nmap':
                return 15;
            case 'ana':
                return 16;
            case 'torrent':
                return 17;
            case 'exe':
                return 18;
            case 'vminer':
                return 20;
            default:
                echo 'Invalid extension';
                exit();
        
        }
        
    }

    public function viewSoftware($softID, $userID, $pcType){
        
        $system = new System();
        
        $valid = 0;
        $external = FALSE;
        
        
        if($userID == ''){
            $userID = $_SESSION['id'];
        }

        if($pcType == ''){
            $pcType = 'VPC';
            $isNPC = 0;
        }

        if($userID == $_SESSION['id']){
            $local = 1;
            $redirect = 'software';
            $replace = 'link1';
        } else {
            $local = 0;
            $redirect = 'internet';
            $replace = 'link3';
        }
        
        if(self::issetSoftware($softID, $userID, $pcType)){
            
            $softInfo = self::getSoftware($softID, $userID, $pcType);
            $lastUsed = $softInfo->softlastedit;
            $valid = 1;
            
        } elseif(self::issetExternalSoftware($softID)){
                
            $softInfo = self::getExternalSoftware($softID);
            $lastUsed = $softInfo->uploaddate;
            $valid = 1;
            $external = TRUE;

        }

        if($valid == 0){
            $system->handleError('INEXISTENT_SOFTWARE', $redirect);
        }        
        
        if($softInfo->softtype == 30 || $softInfo->softtype == 31){
            
            if($softInfo->softtype == 30){
                if($local == 1){
                    header("Location:software?action=text&view=".$softID);
                } else {
                    header("Location:internet?view=software&cmd=txt&txt=".$softID);
                }
            } else {
                if($local == 1){
                    header("Location:software?action=folder&view=".$softID);
                } else {
                    header("Location:internet?view=software&cmd=folder&folder=".$softID);
                }
            }
            
            exit();
        }
        
        if(!$external){
            if($softInfo->softhidden > 0){
                if (self::canSeek($softInfo->softhidden, $softInfo->softhiddenwith)) {
                    $system->handleError('INEXISTENT_SOFTWARE', $redirect);
                }
            }
        }

        $name = $softInfo->softname.self::getExtension($softInfo->softtype);
        $version = self::dotVersion($softInfo->softversion);            

        //$system->changeHTML($replace, $name);

        ?>


        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="he16-software_info"></i></span>
                <h5><?php echo $name.' ('.$version.')'; ?></h5>
            </div>

            <div class="widget-content nopadding">        

                <table class="table table-cozy table-bordered table-striped table-fixed">
                        <tbody>
                                <tr>
                                        <td><span class="item"><?php echo _("Name"); ?></span></td>
                                        <td><?php echo $name; ?></span></td>
                                </tr>
                                <tr>
                                        <td><span class="item"><?php echo _("Version"); ?></span></td>
                                        <td><?php echo $version; ?></td>
                                </tr>
                                <tr>
                                        <td><span class="item"><?php echo _("Licensed to"); ?></span></td>
                                        <td><?php echo self::studyLicense($softInfo->licensedto); ?></td>
                                </tr>
                        </tbody>
                </table>

            </div>
        </div>

        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="he16-detailed_info"></i></span>
                <h5><?php echo _("Detailed Information"); ?></h5>
            </div>

            <div class="widget-content nopadding">        

                <table class="table table-cozy table-bordered table-striped table-fixed">
                        <tbody>
                                <tr>
                                        <td><span class="item"><?php echo _("Type"); ?></span></td>
                                        <td><?php echo self::int2stringSoftwareType($softInfo->softtype); ?></td>
                                </tr>
                                <tr>
                                        <td><span class="item"><?php echo _("Size"); ?></span></td>
                                        <td><?php echo $softInfo->softsize; ?> MB</td>
                                </tr>
                                <tr>
                                        <td><span class="item"><?php echo _("RAM usage"); ?></span></td>
                                        <td><?php echo $softInfo->softram; ?> MHz</td>
                                </tr>
                                <tr>
                                        <td><span class="item"><?php echo _("Last Edit"); ?></span></td>
                                        <td><?php echo $lastUsed; ?></td>
                                </tr>                                            
                        </tbody>
                </table>

            </div>
        </div>                        

        </div>

        <div class="span3 center">
            <br/>
        <?php
        if($local == 1){
            ?>
            <ul class="soft-but">
            <?php
            if($softInfo->licensedto == $_SESSION['id']){ ?>
                <li>
                    <a href="university?id=<?php echo $softID; ?>">
                        <i class="icon-" style="background-image: none;"><span class="heicon he32-research"></span></i>
                        <?php echo _("Research"); ?>
                    </a>
                </li>
            <?php } elseif(self::isResearchable($softInfo->softtype)) { ?>
                <li>
                    <a id="buy-license" class="link" value="<?php echo $softID; ?>">
                        <i class="icon-" style="background-image: none;"><span class="heicon he32-buycert"></span></i>
                        <?php echo _("Buy license"); ?>
                    </a>
                </li>
            <?php } ?>
                <li>
                    <a href="software">
                        <i class="icon-" style="background-image: none;"><span class="heicon he32-root"></span></i>
                        <?php echo _("Back to root"); ?>
                    </a>                                  
                </li>
            </ul>
            <div id="modal" class="pull-left" style="text-align: left;"></div>
            <?php
        } else { ?>

            <ul class="soft-but">
                <li>
                    <a href="internet?view=software">
                        <i class="icon-" style="background-image: none;"><span class="heicon he32-root"></span></i>
                        <?php echo _("Back to root"); ?>
                    </a>                                  
                </li>
            </ul>

        <?php } ?>
        </div>

        </div>

        <?php

        if($local == 0){
            echo "</div>";
        }

        ?>

        <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>

        <?php
        
    }

    public function studyLicense($licensedTo){
        
        if($licensedTo < 0){
            
            switch($licensedTo){
                
                default:
                    $name = '(doom [todo])';
                    break;
                
            }
            
        } elseif($licensedTo == $_SESSION['id']){
            
            $name = '<font color="green">'.parent::getPlayerInfo($_SESSION['id'])->login.'</font>';
            
        } elseif($licensedTo == 0) {
            $name = '<font color="red">'._('No one').'</font>';
        } else {
            $name = '<font color="red">'._('Unknown player').'</font>';
        }
        
        return $name;
        
    }
    
    public function colorSoftVersion($softVersion){
        
        $colorOpen = '<font color="green">';
        $colorClose = '</font>';

        if($softVersion > 29){

            if($softVersion < 50){
                
                $colorOpen = '<font color="#003300">';
                $colorClose = '</font>';
                
            } elseif($softVersion < 100){

                $colorOpen = '<font color="orangered">';
                $colorClose = '</font>';

            } elseif($softVersion < 149){

                $colorOpen = '<font color="red">';
                $colorClose = '</font>';

            } else {

                $colorOpen = '<font color="red"><b>';
                $colorClose = '</b></font>';

            }

        }

        return $colorOpen.self::dotVersion($softVersion).$colorClose;         
        
    }
    
    public function colorSoftSize($softSize){
        
        $colorOpen = '<font color="green">';
        $colorClose = '</font>';
        $str = ' MB';
        
        if($softSize > 99){

            if($softSize < 499){
                
                $colorOpen = '<font color="#003300">';
                $colorClose = '</font>';
                
            } elseif($softSize < 999){

                $colorOpen = '<font color="orangered">';
                $colorClose = '</font>';

            } else {

                $colorOpen = '<font color="red">';
                $colorClose = '</font>';
                $softSize = round($softSize / 1000, 1);
                $str = ' GB';

            }

        }

        return $colorOpen.$softSize.$str.$colorClose;  
        
    }
    
    public function showSoftware($local, $pcType, $id, $folderID = '') {

        require_once '/var/www/classes/Mission.class.php';

        $this->mission = new Mission();

        if ($id == '') {
            $id = $_SESSION['id'];
        }

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }
        
        $pertinentID = 0;
        $pertinentIP = 1; //used only to validate doom-based missions.
        if ($this->session->issetMissionSession()) {

            if ($local == '0') {

                if ($_SESSION['MISSION_TYPE'] < 3) {
                    
                    $pertinentID = $this->mission->getMissionPertinentSoftID($id, $this->mission->missionInfo($_SESSION['MISSION_ID']));

                } elseif($_SESSION['MISSION_TYPE'] == 83){
                    
                    $pertinentID = $this->mission->missionNewInfo($_SESSION['MISSION_ID']);
                    
                } elseif($_SESSION['MISSION_TYPE'] > 49){

                    require '/var/www/classes/Storyline.class.php';
                    $storyline = new Storyline();
                    
                    if($_SESSION['LOGGED_IN'] != $storyline->nsa_getIP()){
                        
                        require '/var/www/classes/Clan.class.php';
                        $clan = new Clan();

                        if($clan->playerHaveClan()){
                            if($clan->getClanInfo($clan->getPlayerClan())->clanip != $_SESSION['LOGGED_IN']){
                                $pertinentIP = 0;
                            }
                        }                        

                    }

                }
                
            } else {
                if($_SESSION['MISSION_TYPE'] == 83){
                    $pertinentID = $this->mission->missionInfo($_SESSION['MISSION_ID']);
                }
            }
            
        }        

        $sshPerm = 0;
        $ftpPerm = 0;
        
        if($id == $_SESSION['id']){
            $sshPerm = 1;
        } else {
            $permission = $_SESSION['CHMOD'];
            if($permission == '' || $permission == 'w' || $permission == 'a'){
                $sshPerm = 1;
            }                
        }

        if(isset($_SESSION['CHMOD'])){
            $permission = $_SESSION['CHMOD'];
            if($permission == '' || $permission == 'w' || $permission == 'r'){
                $ftpPerm = 1;
            }                
        }        
        
        if($local == 0){
            $hackedInfo = parent::getIDByIP($_SESSION['LOGGED_IN'], '');
        } else {
            $hackedInfo = 0;
        }
        
        $bestSoft = self::getBestSoftware('6', '', '', 1);
        
        if($folderID == ''){
        
            $isFile = 0;
            
            $this->session->newQuery();
            $sqlSelect = "SELECT id, softname, softversion, softsize, softtype, softhidden, softhiddenwith, isFolder, originalFrom
                         FROM software 
                         WHERE userid = $id AND isNPC = $npc AND isFolder = 0 
                         ORDER BY softType, softVersion DESC, softLastEdit ASC";
            $data = $this->pdo->query($sqlSelect);

        } else {
            
            $isFile = 1;
            
            $this->session->newQuery();
            $sql = "SELECT software_folders.softID AS id, software.softname, software.softversion, software.softsize, software.softtype, software.softhidden, software.softhiddenwith, software.isFolder, software.originalFrom
                    FROM software_folders
                    INNER JOIN software
                    ON software.id = software_folders.softID
                    WHERE software_folders.folderID = '".$folderID."' AND software.userID = $id AND software.isNPC = $npc
                    ORDER BY software.softType, software.softVersion DESC, software.softLastEdit ASC";
            $data = $this->pdo->query($sql);
                        
        }
        
        if ($data) {
            
?>                                  <table class="table table-cozy table-bordered table-striped table-software table-hover with-check">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th><?php echo _("Software name"); ?></th>
                                                <th><?php echo _("Version"); ?></th>
                                                <th class="hide-phone"><?php echo _("Size"); ?></th>
                                                <th><?php echo _("Actions"); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php    
            
            $hdUsage = 0;
            $haveText = FALSE;
            while ($softInfo = $data->fetch(PDO::FETCH_OBJ)) {
                
                $softInstalled = '0';
                $class = '';
                if (self::isInstalled($softInfo->id, $id, $pcType)) {
                    $softInstalled = 1;
                }
                
                if($softInstalled == 1 || $softInfo->softtype >= 96){
                    $class = 'installed';
                }
                
                $softExtension = self::getExtension($softInfo->softtype);

                if (self::canSeek($softInfo->softhidden, $softInfo->softhiddenwith, $bestSoft)) {
           
                    $hdUsage += $softInfo->softsize;
                    
                    $missionPertinent = 0;
                    if ($this->session->issetMissionSession() && $pertinentIP == 1) {

                        if ($local == '1') {

                            if ($_SESSION['MISSION_TYPE'] == '2') {

                                if($this->mission->missionNewInfo($_SESSION['MISSION_ID']) == $softInfo->id){
                                    $missionPertinent = '1';
                                }
                                
                            } elseif($_SESSION['MISSION_TYPE'] == 83){
                                
                                if($this->mission->missionInfo($_SESSION['MISSION_ID']) == $softInfo->id){
                                    $missionPertinent = 1;
                                }
                                
                            } elseif (($_SESSION['MISSION_TYPE'] > 49) && ($softInfo->softtype == '29')){
  
                                if($this->mission->missionNewInfo($_SESSION['MISSION_ID']) == $softInfo->id){
                                    $missionPertinent = '1';
                                }
                                
                            }
                            
                        } else {

                            if ($_SESSION['MISSION_TYPE'] < '3' || $_SESSION['MISSION_TYPE'] == 83) {
                                
                                if($pertinentID == $softInfo->id){
                                    $missionPertinent = '1';
                                }
                                
                            } elseif($_SESSION['MISSION_TYPE'] > 49){

                                if(($this->mission->missionInfo($_SESSION['MISSION_ID']) == $softInfo->id) || ($this->mission->missionInfo2($_SESSION['MISSION_ID']) == $softInfo->id)){
                                    $missionPertinent = 1;
                                }
                                
                            }
                        }
                    }

                    if($softInfo->softtype == 30){

                        if($local == 1){
                            $link = '?action=text&view=';
                        } else {
                            $link = '?view=software&cmd=txt&txt=';
                        }
                        
                        $link .= $softInfo->id;
                        $haveText = TRUE;
                        
                    } elseif($softInfo->softtype == 31){
                        
                        if($local == 1){
                            $link = '?action=folder&view=';
                        } else {
                            $link = '?view=software&cmd=folder&folder=';
                        }
                        
                        $link .= $softInfo->id;
                        
                    } elseif($softInfo->softtype == 26){
                        
                        $link = '?view=software&cmd=riddle';
                        
                    } elseif($softInfo->softtype == 18){
                        
                        if($local == 1){
                            $link = '?action=webserver';
                        } else {
                            $link = '?cmd=webserver';
                        }
                        
                    }

                    ?>
                                            <tr id="<?php echo $softInfo->id; ?>" class="<?php echo $class; ?>">
                                                <td>
                                                    <span class="he16-<?php echo $softInfo->softtype; ?> tip-top" title="<?php echo self::int2stringSoftwareType($softInfo->softtype); ?>"></span>
                                                </td>
                                                <td>
                                                    <?php
                        
                            if($local == 0 && $softInfo->softtype > 95 && $softInfo->originalfrom == $_SESSION['id']){
                                echo "<b>";
                            }
                            if ($softInstalled == '1' || ($missionPertinent == 1)) {
                                echo "<b>";
                            }
                            if($missionPertinent == 1){
                                echo "<font color=\"red\">";
                            }
                            if($softInfo->softtype == 30 || $softInfo->softtype == 31 || $softInfo->softtype == 26 || $softInfo->softtype == 18){
                                echo "<a href=\"$link\">";
                            }                         
                            if($softInfo->softname == 'puzzle'){
                                $softInfo->softname = _($softInfo->softname);
                            }
                            echo $softInfo->softname . $softExtension;                      
                            if($softInfo->softtype == 30 || $softInfo->softtype == 31 || $softInfo->softtype == 26 || $softInfo->softtype == 18){
                                echo "</a>";
                            }
                            if($missionPertinent == 1){
                                echo "</font>";
                            }                            
                            if ($softInstalled == '1' || ($missionPertinent == 1)) {
                                echo "</b>";
                            }
                            if($local == 0 && $softInfo->softtype > 95 && $softInfo->originalfrom == $_SESSION['id']){
                                echo "</b>";
                            }                            
?>

                                                </td>
                                                <td>
                                                    <?php 
                            if($softInfo->softtype != 30 && $softInfo->softtype != 31 && $softInfo->softtype != 26) { 
                                echo self::colorSoftVersion($softInfo->softversion);
                            } 
?>

                                                </td>
                                                <td  class="hide-phone">
                                                    <?php
                            if($softInfo->softtype != 31 && $softInfo->softtype != 26) { 
                                echo self::colorSoftSize($softInfo->softsize); 
                            } 
                            ?>

                                                </td>
                                                <td style="text-align: center">
<?php
                            if($softInfo->softtype != 31){
                                self::showActions($softInfo->softhidden, $softInfo->softhiddenwith, $softInfo->id, $local, $softInfo->softtype, $softInstalled, $softInfo->isfolder, $hackedInfo);
                            } else {
                                
                                if($softInfo->softversion == 0){
                                    $str = 'No files';
                                    $style = 11;
                                } elseif($softInfo->softversion == 1){
                                    $str = '1 file';
                                    $style = 26;
                                } else {
                                    $str = $softInfo->softversion.' files';
                                    if($softInfo->softversion > 9){
                                        $style = 12;
                                    } else {
                                        $style = 20;
                                    }
                                }
                                
                                if($ftpPerm == 0){

                                    switch($style){
                                        case 20:
                                            $style = 12;
                                            break;
                                        case 26:
                                            $style = 18;
                                            break;
                                        case 11:
                                            $style = 2;
                                            break;
                                        case 12:
                                            $style = 4;
                                            break;
                                    }
                                    
                                }
           
                                if(!isset($_SESSION['LOGGED_IN'])){
                                    $style -= 12;
                                }
                                
                                $styleStr = ' margin-right: '.$style.'px;';
                                
                                $posLink = '';
                                $dataToggle = '';
                                if($local == 1){
                                    $link = '?action=folder&delete=';
                                    $posLink = '#folder'.$softInfo->id;
                                    $dataToggle = 'data-toggle="modal"';                                    
                                } else {
                                    $link = '?view=software&cmd=folder&delete=';
                                    $posLink = '#folder'.$softInfo->id;
                                    $dataToggle = 'data-toggle="modal"';
                                }
?>
                                                    <span class="he16-transparent" style="<?php echo $styleStr; ?>"></span>
                                                    <?php 
echo $str;
?>
                           
                                                    <span class="he16-folder_delte heicon tip-top link delete-folder" title="<?php echo _("Delete folder"); ?>" value="<?php echo $softInfo->id; ?>"></span>
<?php
                            }
?>
                                                </td>
                                            </tr>
<?php
                    
                }
            }

?>
                                        </tbody> 
                                    </table>  
<?php
            
            if($hdUsage == 0){
                
?>
        <script>
            window.onload = function () {
                $('.table-software').replaceWith('<br/><center><?php echo _("There are no softwares to display."); ?></center>');
            }
        </script>
<?php
                
            }

            if($local == 0){
?>
                                    <div style="clear: both;">&nbsp;</div>
                                </div>
<?php
            } else {
?>
                                </div>
                            </div>
<?php
            }

            $hddInfo = $this->hardware->getHardwareInfo($id, $pcType);
            
            if($hdUsage < 1000){
                $usedStr = $hdUsage.' MB';
            } else {
                $usedStr = round($hdUsage / 1000, 2).' GB';
            }
            
            $totalStr = round($hddInfo['HDD'] / 1000, 2).' GB';
            if($hddInfo['HDD'] < 1000){
                $totalStr = round($hddInfo['HDD'], 1).' MB';
            }
            $porct = round($hdUsage / $hddInfo['HDD'], 3) * 100 ."%";
            
            if($local == 1 && $hdUsage >= 50000 && rand(1,10) == 1){
                require '/var/www/classes/Social.class.php';
                $social = new Social();
                $social->badge_add(52, $_SESSION['id']);
            }
            
            if($local == 1){
                $txtLink = 'software?action=text';
                $folderLink = 'software?action=folder';
            } else {
                $txtLink = 'internet?view=software&cmd=txt';
                $folderLink = 'internet?view=software&cmd=folder';
            }

            if($isFile == 0){
                
                $showDeleteDdos = FALSE;
                
                if($haveText && $local == 1){
                    $this->session->newQuery();
                    $sql = "SELECT COUNT(*) AS total FROM software_texts WHERE userID = ".$_SESSION['id']." AND isNPC = 0 AND ddos = 1";
                    $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
                                        
                    if($total > 0){
                        $showDeleteDdos = TRUE;
                    }
                }

?>
                            <div class="span3" style="text-align: center;">
                                <div id="softwarebar">
<?php
                if($local == 0){

                    self::uploadHTML();
                    
                    $internet = $hddInfo['NET'];
                    $rates = $this->hardware->getInternetRates($internet);
?>
                                    <div style="margin-top: 5px;">
                                        <span class="small"><strong><?php echo $internet; ?> Mbit</strong> ( <?php echo $rates['downloadstr']; ?> - <?php echo $rates['uploadstr']; ?>)</span>
                                    </div>
<?php

                }
?>
                                    <div class="hd-usage">
                                        <!--<div id="chart-me" class="percentage easyPieChart" data-percent="<?php //echo ceil($porct); ?>">-->
                                        <div class="chart easyPieChart chartpie" data-percent="<?php echo ceil($porct); ?>">
                                            <div id="downmeplz"><span id="percentpie"></span></div>
                                        </div>
                                        <div class="hd-usage-text"><?php echo _("HDD Usage"); ?></div>
                                        <span class="small"><?php echo '<font color="green">'.$usedStr.'</font> / <font color="red">'.$totalStr.'</font>'; ?></span>
                                    </div>
<?php 
                if($sshPerm == 1){ 
?>
                                    <ul class="soft-but">
                                        <li>
                                            <a class="link create-txt">
                                                <i class="icon-" style="background-image: none;"><span class="heicon he32-text_create"></span></i>
                                                <?php echo _("New text file"); ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="link create-folder">
                                                <i class="icon-" style="background-image: none;"><span class="heicon he32-folder_create"></span></i>
                                                <?php echo _("Create folder"); ?>
                                            </a>
                                        </li>
<?php
                if($showDeleteDdos){
?>
                                        <br/>
                                        <li style="width: 90%;">
                                            <a class="link delete-ddos">
                                                <i class="icon-" style="background-image: none;"><span class="heicon he32-delete_ddos"></span></i>
                                                <?php echo _("Delete DDoS reports"); ?>
                                            </a>
                                        </li>          
<?php
                }
?>
                                    </ul>
                                    <span id="modal"></span>
                                    
                                    
                                    <br/>
<?php  

if($npc == 1 && $local == 0){

    $npcc = new NPC();
    $npcInfo = $npcc->getNPCInfo($id);
    $t = $npcInfo->npctype;

    $date = new DateTime();
    $nextSoftReset = (60 - $date->format('i'))%15;

    $date = new DateTime();

    $today = date('Y-m-d');
    $reset = new DateTime($today.' 00:00:00');

    while($date->diff($reset)->invert == 1){

        $reset->add(new DateInterval('PT4H'));

    }
    
    $interval = $date->diff($reset);
    $hour = $interval->format('%h');

    if($hour == 0){
        if($interval->format('%i') == 0){
            $nextHardReset = '<strong>'.$interval->format('%s').'</strong> second';
        } else {
            $nextHardReset = '<strong>'.$interval->format('%i').'</strong> minute';
        }
    } else {
        $nextHardReset = '<strong>'.$hour.'</strong> hour';
        if($hour >= 2){
            $nextHardReset .= 's';
        }
    }

    // 2019: WTF
    if($t <= 8 || $t == 40 || $t == 45 || $t == 50 || $t == 51 || $t == 71 || $t == 72 || $t == 73){ 

?>
                                    <?php echo _('Next software reset'); ?>: <strong><?php echo $nextSoftReset; ?></strong> minutes<br/>
                                    <?php echo _('Next hardware reset'); ?>: <?php echo $nextHardReset; ?>
<?php 

    }

}

?>

                                    
                                    
<?php
                }
?>
                                </div>
                            </div>
<?php 
                
                if($local == 0){
?>
                        </div>
                    </div>
<?php
                }

?>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
<?php

            }

        } else {
            echo 'Something went wrong. Contact admin';
        }
    }
    
    public function uploadHTML(){
                
?>
                                <form action="" method="GET">
                                    <input type="hidden" name="view" value="software">
                                    <input type="hidden" name="cmd" value="up">
                                    <input id="upload-id" type="hidden" name="id" value="">
                                    <div class="controls">
                                        <div id="toBeHidden">
                                            <span id="link" class="btn btn-primary">Upload...</span>
                                        </div>
                                        <input type="hidden" id="uploadSelect" style="width: 100%" value="">
                                    </div>
                                    <div id="uploadForm"></div>
                                </form>                
<?php       
        
    }

    public function license_buy($softInfo, $external, $price, $bankInfo){
        
        $str = '';
        if($external){
            $str = '_external';
        }
        
        $nameStr = '';
        if($softInfo->softtype == 29){
            
            $user = parent::getPlayerInfo($_SESSION['id'])->login;
            $nameStr = ', softName = \''.$user.'\' ';
            
        }
        
        $this->session->newQuery();
        $sql = "UPDATE software$str SET licensedTo = '".$_SESSION['id']."'$nameStr WHERE id = $softInfo->id";
        $this->pdo->query($sql);

        $log = new LogVPC();
        $log->addLog($_SESSION['id'], $log->logText('BUY_LICENSE', Array($softInfo->softname, self::dotVersion($softInfo->softversion), self::getExtension($softInfo->softtype), $price, $bankInfo[0], $bankInfo[1])), 0);

    }
    
    public function license_studyPrice($softType, $softVersion, $licensedTo){
        
        if($licensedTo < 0){
            $multiplier = 1;
        } else {
            $multiplier = 3;
        }

        if($softVersion < 20){
            $cte = 10;
        } elseif($softVersion < 50){
            $cte = 40;
        } elseif($softVersion < 100){
            $cte = 60;
        } elseif($softVersion < 200){
            $cte = 80;
        } elseif($softVersion < 300){
            $cte = 110;
        } else {
            $cte = 150;
        }
        
        $price = $softVersion * $cte * $multiplier * 2;
        
        if($softType == 29){
            return 100000;
        }
        
        return round($price / 10);
        
    }

    public function text_name($id){
        
        $this->session->newQuery();
        $sql = "SELECT softName FROM software WHERE id = '".$id."' LIMIT 1";

        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return $data['0']['softname'];
        } else {
            return 'Unknown file';
        }
                
    }
    
    public function text_edit($name, $text, $txtID){

        $phpSelf = $_SERVER['PHP_SELF'];

        $crudePage = substr(substr($phpSelf, 1), 0, -4);
        
        if($crudePage == 'internet'){
            if(!$this->session->issetInternetSession()){
                exit();
            }
            $hackedInfo = parent::getIDByIP($_SESSION['LOGGED_IN'], '');
            $local = FALSE;
        } else {
            $local = TRUE;
        }
        
        $this->session->newQuery();
        $sql = 'UPDATE software SET softname = :name WHERE id = :txtID';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':name' => $name, ':txtID' => $txtID));
        
        $this->session->newQuery();
        $sql = 'UPDATE software_texts SET text = :text, lastEdit = NOW() WHERE id = :txtID';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':text' => $text, ':txtID' => $txtID));
        
        $log = new LogVPC();

        $name .= '.txt';
        
        if($local){

            $log->addLog($_SESSION['id'], $log->logText('TXT_EDIT', Array(0, $name)), '0');
            
        } else {
            
            $paramHacked = Array(1, $name, long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip));
            $paramHacker = Array(2, $name, long2ip($_SESSION['LOGGED_IN']));
            
            $log->addLog($hackedInfo['0']['id'], $log->logText('TXT_EDIT', $paramHacked), $hackedInfo['0']['pctype']);
            $log->addLog($_SESSION['id'], $log->logText('TXT_EDIT', $paramHacker), '0');
            
        }
        
    }
    
    public function text_show($id, $npc = 0){

        $phpSelf = $_SERVER['PHP_SELF'];

        $crudePage = substr(substr($phpSelf, 1), 0, -4);
        
        if($crudePage == 'internet'){
            $link = 'internet?view=software';
            $replace = 'link3';
            $linkDelete = 'internet?view=software&cmd=del&id='.$id;
            $redirect = 'internet';
        } else {
            $link = 'software';
            $replace = 'link1';
            $linkDelete = 'software?action=del&id='.$id;
            $redirect = 'software';
        }
        
        $this->session->newQuery();
        $sql = "SELECT software_texts.text, software_texts.ddos, software.softName
                FROM software_texts 
                INNER JOIN software ON software.id = software_texts.id
                WHERE software_texts.id = '".$id."'
                LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 0){
            $system = new System();
            $system->handleError('This text file doesnt exists.', $redirect);
        }

        //$system->changeHTML($replace, $data['0']['softname'].'.txt');

        $linkClass = 'edit-txt';
        $spanClass = '';

        if($data['0']['ddos'] == 1){
            $linkClass = '';
            $spanClass = 'red';
        }
        
        $formatedText = nl2br($data['0']['text']);
        
        ?>

                                                <div class="widget-box">                   
                                                    <div class="widget-title">                                                            
                                                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                                                        <h5>File <b><?php echo $data['0']['softname']; ?>.txt</b></h5>
                                                        <span class="label label-important link">Report</span>
                                                    </div>

                                                    <div class="widget-content padding">
        <?php echo $formatedText; ?>
                                                    </div>
                                                    <span id="txt-id" value="<?php echo $id; ?>"></span>
                                                </div>

    </div>

    <div class="span3 center">
        <br/>
        <ul class="soft-but">
            <li>
                <a class="link <?php echo $linkClass; ?>">
                    <i class="icon-" style="background-image: none;"><span class="he32-text_edit"></span></i>

                    <span class="<?php echo $spanClass; ?>">

                        <?php echo _("Edit file"); ?>

                    </span>

                </a>
            </li>
            <li>
                <a href="<?php echo $linkDelete; ?>">
                    <i class="icon-" style="background-image: none;"><span class="he32-text_delete"></span></i>
                    Delete file
                </a>
            </li>
            <li>
                <a href="<?php echo $link; ?>">
                    <i class="icon-" style="background-image: none;"><span class="he32-root"></span></i>
                    <?php echo _("Back to root"); ?>
                </a>
            </li>                
        </ul>
        <span id="modal">
    </div>

    </div>

    <?php

    if($crudePage == 'internet'){
        echo "</div>";
    }

    ?>
    <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>             

        <?php
        
    }
    
    public function text_add($name, $text, $id, $npc, $record = true){
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
                
        if($npc === 'NPC'){
            $npc = 1;
        }
        
        if($npc === 'VPC'){
            $npc = 0;
        }
                
        $this->session->newQuery();
        $sql = 'INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, licensedTo) VALUES (\'\', :id, :name, \'0\', \'1\', \'0\', \'30\', NOW(), \'0\', \'0\', :npc, \'\')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id, ':name' => $name, ':npc' => $npc));

        $txtID = $this->pdo->lastInsertID();
        
        $this->session->newQuery();
        $sql = 'INSERT INTO software_texts (id, userID, isNPC, text, lastEdit) VALUES (:txtID, :id, :npc, :text, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':txtID' => $txtID, ':id' => $id, ':npc' => $npc, ':text' => $text));
        
        $log = new LogVPC();

        $name .= '.txt';

        if($record){
        
            if($id == $_SESSION['id']){

                $log->addLog($_SESSION['id'], $log->logText('TXT_NEW', Array(0, $name)), '0');

            } else {

                $paramHacked = Array(1, $name, long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip));
                $paramHacker = Array(2, $name, long2ip($_SESSION['LOGGED_IN']));

                $log->addLog($id, $log->logText('TXT_NEW', $paramHacked), $npc);
                $log->addLog($_SESSION['id'], $log->logText('TXT_NEW', $paramHacker), '0');

            }
        
        }
        
    }

    public function folder_edit($folderID, $name, $local){
        
        $this->session->newQuery();
        $sql = 'UPDATE software SET softname = :name WHERE id = :folderID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':name' => $name, ':folderID' => $folderID));
        
        $log = new LogVPC();
        
        if($local){

            $log->addLog($_SESSION['id'], $log->logText('FOLDER_EDIT', Array(0, $name)), '0');
            
        } else {
            
            $paramHacked = Array(1, $name, long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip));
            $paramHacker = Array(2, $name, long2ip($_SESSION['LOGGED_IN']));
            
            $hackedInfo = parent::getIDByIP($_SESSION['LOGGED_IN'], '');
            
            $log->addLog($hackedInfo['0']['id'], $log->logText('FOLDER_EDIT', $paramHacked), $hackedInfo['0']['pctype']);
            $log->addLog($_SESSION['id'], $log->logText('FOLDER_EDIT', $paramHacker), '0');
            
        }
        
    }
    
    public function folder_used($folderID){
        
        $this->session->newQuery();
        $sql = "SELECT softVersion FROM software WHERE id = $folderID LIMIT 1";
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->softversion > 0){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function folder_delete($folderID, $uid, $npc){
        
        $this->session->newQuery();
        $sql = "SELECT softName FROM software WHERE id = $folderID LIMIT 1";
        $folderName = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->softname;        
        
        $this->session->newQuery();
        $sql = "DELETE FROM software WHERE id = $folderID LIMIT 1";
        $this->pdo->query($sql);
        
        $log = new LogVPC();
        
        if($uid == $_SESSION['id']){

            $log->addLog($_SESSION['id'], $log->logText('FOLDER_DEL', Array(0, $folderName)), '0');
            
        } else {
            
            $paramHacked = Array(1, $folderName, long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip));
            $paramHacker = Array(2, $folderName, long2ip($_SESSION['LOGGED_IN']));
            
            $log->addLog($uid, $log->logText('FOLDER_DEL', $paramHacked), $npc);
            $log->addLog($_SESSION['id'], $log->logText('FOLDER_DEL', $paramHacker), '0');
            
        }

    }
    
    public function folder_name($folderID){
        
        $this->session->newQuery();
        $sql = "SELECT softname FROM software WHERE id = '".$folderID."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return $data['0']['softname'];
        } else {

            return 'Unknown folder';
        }
        
    }
    
    public function folder_show($folderID, $id, $npc){
        
        if($id == ''){
            $id = $_SESSION['id'];
            $replace = 'link2';
        } else {
            $replace = 'link3';
        }        
        
        $folderName = self::folder_name($folderID);

        $system = new System();
        //$system->changeHTML($replace, $folderName);        
 
        $this->session->newQuery();
        $sql = "SELECT software_folders.softID 
                FROM software_folders
                INNER JOIN software
                ON software.id = software_folders.folderID
                WHERE software_folders.folderID = '".$folderID."' AND software.userID = $id AND software.isNPC = $npc";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            $total = 0;
            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['softid'] != 0){
                    
                    $total++;
                    break;
                    
                }
                
            }
            
            if($npc == 1){
                $pcType = 'NPC';
            } else {
                $pcType = 'VPC';
            }            

            if($id == $_SESSION['id']){
                $local = 1;
                $softLink = 'software';
                $editLink = 'software?action=folder&edit='.$folderID;
            } else {
                $local = 0;
                $softLink = 'internet?view=software';
                $editLink = 'internet?view=software&cmd=folder&edit='.$folderID;
            }
                            
            ?>
                                                    <div class="widget-box">                   
                                                        <div class="widget-title">                                                            
                                                            <span class="icon"><i class="fa fa-arrow-right"></i></span>
                                                            <h5><?php echo _('Folder'); ?> <b><?php echo $folderName; ?></b></h5>
                                                        </div>

                                                        <div class="widget-content padding">
 
        
            <?php
            
            if($total == 0){
                
                echo _('This folder is empty. Use the sidebar to move files.');
                           
?>
                                                    </div>
<?php

                if($local == 1){
?>
                                                </div>
<?php
                }
                
            } else {
                                
                self::showSoftware($local, $pcType, $id, $folderID);
 
            }
            
            ?>
            
                                            </div>
                                        </div>
        
            <div id="softwarebar" class="span3" style="text-align: center;"><!-- todo: não testado scroll down em pastas -->
            
            <?php
            
            $valid = 0;
            if($local == 0){

                $permission = $_SESSION['CHMOD'];

                if ($permission == 'w' || $permission == '' || $permission == 'a') {
                    $valid = 1;
                }
            
            } else {
                $valid = 1;
            }

            if($valid == 1){

                ?>
                <form action="" method="POST" style="margin-top: 10px;">
                    <?php if($id == $_SESSION['id']) { ?>
                        <input type="hidden" name="action" value="folder">
                        <input type="hidden" name="view" value="<?php echo $folderID; ?>">
                    <?php } else { ?>
                        <input type="hidden" name="view" value="software">
                        <input type="hidden" name="cmd" value="folder">
                        <input type="hidden" name="folder" value="<?php echo $folderID; ?>">
                    <?php } ?>
                    <div class="controls">
                        <span id="link" class="btn btn-primary"><?php echo _('Move...'); ?></span>
                        <input id="upload-id" type="hidden" name="id" value="">
                        <input type="hidden" id="uploadSelect" style="width: 100%" value="">
                    </div>
                    <div id="uploadForm"></div>
                </form><br/><br/>
                <?php
            
            } else {
                echo _('You do not have permission to move softwares to this folder.')."<br/><br/>";
            }
            ?>
                
            <ul class="soft-but">
                <?php if($valid == 1){ ?>
                <li>
                    <span id="folder-id" value="<?php echo $folderID; ?>"></span>
                    <a class="link edit-folder">
                        <i class="icon-" style="background-image: none;"><span class="he32-folder_edit"></span></i>
                        <?php echo _("Edit folder"); ?>
                    </a>
                </li>
                <?php } ?>                
                <?php if($total == 0){ ?>
                <li>
                    <a class="link delete-folder" value="<?php echo $folderID; ?>">
                        <i class="icon-" style="background-image: none;"><span class="he32-folder_delete"></span></i>
                        <?php echo _("Delete folder"); ?>
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="<?php echo $softLink; ?>">
                        <i class="icon-" style="background-image: none;"><span class="he32-root"></span></i>
                        <?php echo _("Back to root"); ?>
                    </a>
                </li>                
            </ul>                    
            <span id="modal"></span>
            </div>
            
            <?php
            
            if($local == 0){
                ?>
                    </div>
                </div>
                <?php
            }
            
            ?>
            
            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
            
            <?php    
                        
        } else {
            
            if(isset($_SESSION['FOLDER'])){
                unset($_SESSION['FOLDER']);
            }
            
            $this->session->addMsg('This folder doesnt exists.', 'error');
            header("Location:software");
            
        }
        
    }
    
    public function folder_isset($folderID, $userID, $npc){

        $this->session->newQuery();
        $sql = "SELECT id FROM software WHERE id = '".$folderID."' AND softType = 31 AND userID = '".$userID."' AND isNPC = $npc LIMIT 1";
        
        if(sizeof($this->pdo->query($sql)->fetchAll()) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function folder_move($folderID, $softID){
        
        $this->session->newQuery();
        $sql = "INSERT INTO software_folders (folderID, softID) VALUES ('".$folderID."', '".$softID."')";
        $this->pdo->query($sql);        
        
        $this->session->newQuery();
        $sql = "UPDATE software SET isFolder = 1 WHERE id = $softID";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "UPDATE software SET softVersion = softVersion + 1 WHERE id = $folderID";
        $this->pdo->query($sql);        
        
    }
    
    public function folder_moveBack($softID){
        
        $this->session->newQuery();
        $sql = "SELECT folderID FROM software_folders WHERE softID = '".$softID."' LIMIT 1";
        $folderID = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->folderid;

        $this->session->newQuery();
        $sql = "UPDATE software_folders SET softID = 0 WHERE folderID = '".$folderID."' AND softID = '".$softID."' LIMIT 1";
        $this->pdo->query($sql);        
                
        $this->session->newQuery();
        $sql = "UPDATE software SET isFolder = 0 WHERE id = $softID";
        $this->pdo->query($sql);
         
        $this->session->newQuery();
        $sql = "UPDATE software SET softVersion = softVersion - 1 WHERE id = $folderID";
        $this->pdo->query($sql);      
               
    }
    
    private function folder_list($id, $npc){
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
        
        if($npc == '' || $npc == 'VPC'){
            $npc = 0;
        } else {
            $npc = 1;
        }
        
        $this->session->newQuery();
        $sqlSelect = "SELECT id, softname, softversion, softType
                     FROM software 
                     WHERE userid = $id AND isNPC = $npc AND isFolder = 0 AND softHidden = 0 AND softType <> 31
                     GROUP BY softType, softVersion DESC, softLastEdit ASC";
        $data = $this->pdo->query($sqlSelect)->fetchAll();
        if(sizeof($data) > 0){

            ?>
            


                        <div class="controls">
                            <select name="move" style="width: 100%">
                                <option>Move to folder</option>
            
            <?php

            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['softtype'] == 30){
                    $version = '';
                } else {
                    $version = ' ('.self::dotVersion($data[$i]['softversion']).')';
                }
                                
                ?>
            
                    <option value="<?php echo $data[$i]['id']; ?>"><?php echo $data[$i]['softname'].self::getExtension($data[$i]['softtype']).$version; ?></option>
            
                <?php

            }
            
            ?>
                    </select>
                </div><br/>
                <input type="submit" value="Move">                
                
            <?php
            
        }
        
        echo "</form><br/>";
        
    }
    
    public function folder_create($name, $id, $npc){
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
        
        if($id == $_SESSION['id']){
            $local = 1;
        } else {
            $local = 0;
        }
        
        if($npc == 'NPC'){
            $npc = 1;
        }
        
        if($npc == 'VPC'){
            $npc = 0;
        }
        
        $this->session->newQuery();

        $sql = 'INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, licensedTo) 
                VALUES (\'\', :id, :name, \'0\', \'1\', \'0\', \'31\', NOW(), \'0\', \'0\', :npc, \'\')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id, ':name' => $name, ':npc' => $npc));
        
        $folderID = $this->pdo->lastInsertID();
        
        $this->session->newQuery();
        $sql = "INSERT INTO software_folders (folderID) VALUES ('".$folderID."')";
        $this->pdo->query($sql);
        
        $log = new LogVPC();
        
        if($local == 1){

            $log->addLog($_SESSION['id'], $log->logText('FOLDER_NEW', Array(0, $name)), '0');
            
        } else {
            
            $paramHacked = Array(1, $name, long2ip(parent::getPlayerInfo($_SESSION['id'])->gameip));
            $paramHacker = Array(2, $name, long2ip($_SESSION['LOGGED_IN']));
            
            $log->addLog($id, $log->logText('FOLDER_NEW', $paramHacked), $npc);
            $log->addLog($_SESSION['id'], $log->logText('FOLDER_NEW', $paramHacker), '0');
            
        }
        
    }

    public function isInstalled($softID, $uid, $pcType) {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        $this->session->newQuery();
        $sqlSelect = "SELECT id FROM software_running WHERE userID = $uid AND softID = $softID AND isNPC = $npc LIMIT 1";
        $info = $this->pdo->query($sqlSelect)->fetchAll();

        if (count($info) != '0')
            return TRUE;
        else
            return FALSE;
        
    }

    public function softRamUsage($softID, $uid, $pcType) {

        if (self::isInstalled($softID, $uid, $pcType)) {

            $npc = '0';
            if ($pcType == 'NPC') {
                $npc = '1';
            }

            $this->session->newQuery();
            $sqlSelect = "SELECT ramUsage FROM software_running WHERE userID = $uid AND softID = $softID AND isNPC = $npc LIMIT 1";
            $info = $this->pdo->query($sqlSelect)->fetchAll();

            return $info['0']['ramUsage'];
        }
    }

    public function showActions($softHidden, $softHiddenWith, $softID, $local, $softType, $softInstalled, $isFolded, $hackedInfo) {
                
        $isVirusInstalled = self::isVirusInstalled($softType);        
        $isHide = self::isHide($softHidden);
        
        if($isHide){
            $seekTip = _('Seek (hidden with ').self::dotVersion($softHiddenWith).')';
        } else {
            $hideTip = 'Hide';
        }
        
        $infoTip = 'Information';
        $killTip = 'Kill';
        $installTip = 'Run';
        $deleteTip = 'Delete';
        
        if ($local == '1') {
                        
            if($softType != 30){
                if($softType == 18){
                    $link = '?action=webserver';
                } else {
                    $link = '?id='.$softID;
                }
?>
                                                    <a href="<?php echo $link; ?>" class="tip-top" title="<?php echo _($infoTip); ?>"><span class="he16-software_info"></span></a>
<?php                 
                
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }                    
            
            if($isFolded == 1){
                $folderTip = 'Move software back to root';
                
                
?>
                                                    <a href="?action=move&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($folderTip); ?>"><span class="he16-folder_delete"></span></a>
<?php                     

            }    
            
            if ($this->session->isInternetLogged()) {
                
                if(!$isVirusInstalled && $softType != 19){

                    $ftpPerm = 0;
                    $permission = $_SESSION['CHMOD'];
                    
                    if ($permission == 'w' || $permission == '' || $permission == 'r') {
                        $ftpPerm = 1;
                    }
                    
                    if($ftpPerm == 1){
                    
                        $uploadTip = 'Upload to '.long2ip($_SESSION['LOGGED_IN']);

?>
                                                    <a href="internet?view=software&cmd=up&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($uploadTip); ?>"><span class="he16-upload"></span></a>
<?php   

                    } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                    }
                    
                    
                } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                }
            }            
            
            if (self::isExecutable($softType, $softID, 1)) {

                if ($softInstalled == '1') {
                    
                    if (!self::isVirus($softType)) {
                    
?>
                                                    <a href="?action=uninstall&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($killTip); ?>"><span class="he16-stop"></span></a>
<?php
                        
                    } else {
                        if($softType == 29){?>
                                                    <span class="he16-transparent"></span>      
<?php
                        }
                    }
                } else {                    
                    if(!$isHide){

                        if ($softType == '11') {
                            $link = 'list?action=collect';
                        } elseif ($softType == '12') {
                            $link = 'list?action=ddos';
                        } elseif ($softType == '16'){
                            $link = 'hardware';
                        } elseif ($softType == '18'){
                            $link = '?action=webserver';
                        } else {
                            $link = "?action=install&id=$softID";
                        }

?>
                                                    <a href="<?php echo $link; ?>" class="tip-top" title="<?php echo _($installTip); ?>"><span class="he16-cog"></span></a>
<?php                                        


                    } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                        if($softType == 29){
?>
                                                    <span class="he16-transparent"></span>      
<?php
                        }
                    }
                    
                }
                
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }                
            
            if (!$isHide) {

                if ($softInstalled == 0) {
                    
?>
                                                    <a href="?action=hide&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($hideTip); ?>"><span class="he16-hide"></span></a>
<?php
                } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                }

            } else {
                
?>
                                                    <a href="?action=seek&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($seekTip); ?>"><span class="he16-search"></span></a>
<?php
                
            }                      
            
            if (!$isVirusInstalled && $softType != 29 && $softType != 19) {
?>
                                                    <a href="?action=del&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($deleteTip); ?>"><span class="he16-bin"></span></a>
<?php
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }            
            
        } else {
            
            $permission = $_SESSION['CHMOD'];
            
            $ftpPerm = 0;
            $sshPerm = 0;
            
            if ($permission == 'w' || $permission == '' || $permission == 'a') {
                $sshPerm = 1;
            }
            
            if ($permission == 'w' || $permission == '' || $permission == 'r') {
                $ftpPerm = 1;
            }

            $downloadTip = 'Download';

            if($softType != 30 && $softType != 26){
                if($softType == 18){
                    $link = '?cmd=webserver';
                } else {
                    $link = '?view=software&id='.$softID;
                } 
                
?>
                                                    <a href="<?php echo $link; ?>" class="tip-top" title="<?php echo _($infoTip); ?>"><span class="he16-software_info"></span></a>
<?php                         
                
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }              
            
            if($isFolded == 1 && $sshPerm == 1){
                $foldedTip = 'Move software back to root';
                
?>
                                                    <a href="?view=software&cmd=move&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($foldedTip); ?>"><span class="he16-folder_delete"></span></a>
<?php                         
                
            } else {
                if($isFolded == 1){
?>
                                                    <span class="he16-transparent"></span>      
<?php
                }
            }                                               
            
            if($ftpPerm == 1){
                if (!$isVirusInstalled && $softType != 19  && $softType != 26) {
                    if($softType == 29 && !$isHide && $softInstalled = 0){
                        echo "<span class=\"he16-transparent\"></span>";
                    }
                    
?>
                                                    <a href="internet?view=software&cmd=dl&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($downloadTip); ?>"><span class="he16-download"></span></a>
<?php 
                    
                } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                }
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }

            if ($sshPerm == 1) {
            
                if (self::isExecutable($softType, $softID, 0)) {

                    if ($softInstalled == '1' ) {

                        if (!self::isVirus($softType)) {
 
?>
                                                    <a href="?view=software&cmd=uninstall&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($killTip); ?>"><span class="he16-stop"></span></a>
<?php                                  
                            
                        } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                        }

                    } else {

                        if(!$isHide){

                            if($softType == 18){
                                $link = '?view=software&cmd=webserver';
                            } elseif($softType == 26){
                                $link = '?view=software&cmd=riddle';
                            } else {
                                $link = '?view=software&cmd=install&id='.$softID;
                            }
                            
?>
                                                    <a href="<?php echo $link; ?>" class="tip-top" title="<?php echo _($installTip); ?>"><span class="he16-cog"></span></a>
<?php                                    
                            
                        } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                            if($softType == 29){
?>
                                                    <span class="he16-transparent"></span>      
<?php
                            }                            
                        }

                    }

                } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
                }                   
                
                if (!$isHide) {

                    if (($softInstalled == 0 || $softType == 29) && $softType != 26) {
                        
?>
                                                    <a href="?view=software&cmd=hide&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($hideTip); ?>"><span class="he16-hide"></span></a>
<?php
                        
                    } else {
                        if($isFolded == 0){
?>
                                                    <span class="he16-transparent"></span>      
<?php
                        }
                    }

                } else {

?>
                                                    <a href="?view=software&cmd=seek&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($seekTip); ?>"><span class="he16-search"></span></a>
<?php                    
                    
                } 
                
            } else {
?>
                                                    <span class="he16-transparent"></span>
                                                    <span class="he16-transparent"></span>      
<?php
            }     
            
            if (!$isVirusInstalled && $sshPerm == 1 && $softType != 29 && $softType != 19 && $softType != 26) {
?>
                                                    <a href="?view=software&cmd=del&id=<?php echo $softID; ?>" class="tip-top" title="<?php echo _($deleteTip); ?>"><span class="he16-bin"></span></a>
<?php                
            } else {
?>
                                                    <span class="he16-transparent"></span>      
<?php
            }     

        }
                
    }

    public function getExecutableType($softType) {

        if ($softType == '8') {
            $type = 'virus';
        } elseif ($softType == '5') {

        }

        return $type;
    }

    public function dotVersion($softVersion) {

        if(strlen($softVersion) > 1){
            $strEdit = str_split($softVersion, strlen($softVersion) - 1);
        } else {
            $strEdit = '0' . $softVersion;
        }
                
        return $strEdit['0'] . '.' . $strEdit['1'];
        
    }

    public function getExtension($softType) {

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
            '19' => '.dat',
            '20' => '.vminer',
            '26' => '.exe',
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
    
    public function totalSoftwares($id, $pcType){
        
        if ($pcType == 'NPC') {
            $npc = '1';
        } else {
            $npc = 0;
        }
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM software WHERE userid = $id AND isNPC = $npc";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }

    public function getBestSoftware($softType, $id, $pcType, $installed = '') {

        $npc = '0';
        if (strlen($id) > '0' && strlen($pcType) > '0') {
            if ($pcType == 'NPC' || $pcType == 1) {
                $npc = '1';
            }
        } else {
            $id = $_SESSION['id'];
        }

        if ($installed == '1') {

            $sql = "SELECT software.id, software.softVersion, software.softHidden, software.softRam
                    FROM software
                    INNER JOIN software_running
                    ON software.userID = software_running.userID
                    WHERE software.userID = $id AND software.softType = $softType AND software.softHidden = 0 AND software.isNPC = $npc AND software_running.softID = software.id
                    ORDER BY software.softVersion DESC
                    LIMIT 1
                    ";
        } else {
            $sql = "SELECT id, softversion, softHidden, softName, softRam FROM software WHERE userid = $id AND softtype = $softType AND softhidden = 0 AND isNPC = $npc ORDER BY softversion DESC";            
        }

        $this->session->newQuery();
        $data = $this->pdo->query($sql)->fetchAll();

        if (count($data) != '0') {
            
            $data['0']['exists'] = '1';
            
        } else {

            $data['0']['exists'] = '0';
            $data['0']['softversion'] = '0';

        }

        return $data;
    }

    public function numberSoftware($softType, $uid, $info) {

        $addSql = '';
        if ($info != 'all') { //busca em todos os softs
            $addSql = ' AND softhidden = 0';
        }

        $this->session->newQuery();
        $sql = "SELECT id FROM software WHERE userID = $uid AND softType = $softType AND isNPC = 0 $addSql";
        $data = $this->pdo->query($sql)->fetchAll();

        return count($data);
    }

    private function isHide($softHidden) {

        if ($softHidden == '0') { //não está escondido, então posso esconder
            return FALSE;
        } else { // o soft tá escondido
            return TRUE;
        }
    }

    private function canSeek($softHidden, $softHiddenWith, $bestSoft = '') {

        if ($softHidden == '1') {

            $bestSoft = self::getBestSoftware('6', '', '', 1);

            if ($bestSoft['0']['exists'] == '1') {

                if ($bestSoft['0']['softhidden'] == '0') {

                    if ($softHiddenWith <= $bestSoft['0']['softversion']) {
                        
                        return TRUE;
                        
                    } else {
                        return FALSE;
                    }
                    
                } else {
                    return FALSE; //TODO: aqui nao dá bug? repito o processo no ajax
                }
                
            } else {
                return FALSE;
            }
            
        } else {
            
            return TRUE;
            
        }
    }

    public function getSoftware($softID, $uid = '', $pcType = '') {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }
        
        if($uid != ''){
            $where = "WHERE userID = $uid AND id = $softID AND isNPC = $npc";
        } else {
            $where = "WHERE id = $softID";
        }
        
        $this->session->newQuery();
        $sqlSelect = "SELECT id, softName, softVersion, softSize, softRam, softType, softHidden, softHiddenWith, originalFrom, softLastEdit, licensedTo, isFolder, originalFrom FROM software $where LIMIT 1";
        
        return $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function getOriginalFrom($softID){
        
        $this->session->newQuery();
        $sql = "SELECT originalFrom FROM software WHERE id = '".$softID."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function getExternalSoftware($softID){
        
        $this->session->newQuery();
        $sql = 'SELECT softName, softVersion, softSize, softRam, softType, uploadDate, licensedTo FROM software_external WHERE id = :softID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':softID' => $softID));
        return $stmt->fetch(PDO::FETCH_OBJ);
        
    }

    public function issetSoftware($softID, $uid, $pcType, $hide = false) {

        $npc = '0';
        if ($pcType == 'NPC') {
            $npc = '1';
        }

        if ($softID == '') {
            $softID = '0';
        }

        $this->session->newQuery();

        $sql = 'SELECT COUNT(*) AS total FROM software WHERE userID = :uid AND id = :softID AND isNPC = :npc LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid' => $uid, ':softID' => $softID, ':npc' => $npc));
                
        $total = $stmt->fetch(PDO::FETCH_OBJ)->total;
        
        if($total == 1){
            return TRUE;
        }
        
        return FALSE;
        
    }
    
    public function issetExternalSoftware($softID){
        
        if ($softID == '') {
            $softID = '0';
        }

        $this->session->newQuery();

        $sql = 'SELECT userID FROM software_external WHERE id = :softID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':softID' => $softID));
        
        $rowsReturned = $stmt->fetchAll();

        if (count($rowsReturned) == '1'){
            if($rowsReturned['0']['userid'] == $_SESSION['id']){
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
        
    }

    public function listRunningSoftwares() {

        $id = $_SESSION['id'];

        $sqlSelect = "
            SELECT software_running.id, software_running.softID, software_running.ramUsage, software.softType, software.softVersion, software.softName, software.softRam
            FROM software_running
            INNER JOIN software 
            ON software_running.userID = software.userID
            WHERE software_running.userID = $id AND software_running.isNPC = '0' AND software_running.softID = software.id
            ORDER BY software_running.ramUsage DESC, software.softType, software.softVersion DESC
        ";

        $this->session->newQuery();
        $data = $this->pdo->query($sqlSelect);

        if (count($data) != '0') {

            ?>
                
                <div class="span9">

                <table class="table table-cozy table-bordered table-striped table-running table-hover with-check">
                    <thead>
                        <tr>
                            <th></th>
                            <th scope="col"><?php echo _("Software name"); ?></th>
                            <th scope="col"><?php echo _("Version"); ?></th>
                            <th scope="col"><?php echo _("RAM usage"); ?></th>
                            <th scope="col"><?php echo _("Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                
                
            <?php
            
            $i = $usedRam = $usedRamPerc = 0;
            while ($softInfo = $data->fetch(PDO::FETCH_OBJ)) {

                if (!self::isVirus($softInfo->softtype)) {

                    $i++;

                    $ramUsage = $this->hardware->getSoftUsage($id, 'VPC', $softInfo->softid);

                    $softVersion = self::colorSoftVersion($softInfo->softversion);
                    $softExtension = self::getExtension($softInfo->softtype);
                    
                    $percent = round($ramUsage, 3) * 100;

                    ?>
                    
                        <tr>
                            <td>
                                <span class="he16-<?php echo "$softInfo->softtype"; ?>"></span>
                            </td>
                            <td>
                                <?php echo $softInfo->softname.$softExtension; ?>
                            </td>
                            <td>
                                <?php echo $softVersion; ?>
                            </td>
                            <td>
                                <div class="center">
                                    <?php echo $softInfo->softram.' MB <span class="small">'. $percent . '%</span>'; ?>
                                </div>
                            </td>
                            <td>
                                <div class="center">
                                    <a href="software?id=<?php echo $softInfo->softid; ?>"><span class="he16-software_info"></span></a>      
                                    <a href="software?action=uninstall&id=<?php echo $softInfo->softid; ?>"><span class="he16-stop"></span></a>
                                </div>
                            </td>
                        </tr>

                    <?php

                    $usedRam += $softInfo->softram;
                    $usedRamPerc += $percent;

                }

            }

            ?>
                 
                    </tbody>
                </table>
                    
                </div>
  
            <?php
            
        } else {
            ?>
                <div class="span9">
                    <?php echo _('Ops! There are no software running in your system. <br/>Go to the <a href="software">software</a> page to run them.'); ?>
                </div>
            <?php
        }
        
        $ramInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                
        ?>
                             
                <div class="span3 center">

                    <div class="hd-usage">
                        <div class="percentage easyPieChart" data-percent="<?php echo ceil($usedRamPerc); ?>">
                            <strong><span></span>%</strong>
                        </div>
                        <div class="hd-usage-text"><?php echo _("RAM usage"); ?></div>
                        <span class="small"><font color="green"><?php echo round($usedRam,2); ?> MB</font> / <font color="red"><?php echo round($ramInfo['RAM'], 2); ?> MB</font></span>
                    </div>                       
                    
                    
                </div>
                
                </div>
                
                <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>                
                
        <?php
        
    }

    public function research_list(){

        $ranking = new Ranking();
        
        $roundStats = $ranking->serverStats_get();
        $researchRanking = $ranking->getResearchRank($_SESSION['id']);
        
        $researchRank = '#<span id="research-side-rank">'.number_format($researchRanking['rank']).'</span> <span class="small">of '.number_format($researchRanking['total']).'</span>';
        
        if($researchRanking['rank'] == 0){
            $researchRank = '<span class="small nomargin">'._('Not ranked').'</span>';
        }
        
        if($researchRanking == '')
        
?>
     
            <div class="span7">
                <div class="widget-box text-left">
                            
                    <div class="widget-title">
                        <span class="icon"><span class="he16-research_info"></span></span>
                        <h5><?php echo _('Research softwares'); ?></h5>
                    </div>
                    <div class="widget-content padding">                   

<?php                
        $sql = "SELECT id, softName, softType, softVersion 
                FROM software 
                WHERE userID = '".$_SESSION['id']."' AND softHidden = 0 AND softType < 99 AND licensedTo = '".$_SESSION['id']."' 
                ORDER BY softType, softVersion DESC";
        $researchInfo = $this->pdo->query($sql)->fetchAll();        
        
        if(sizeof($researchInfo) > 0){
            
?>
                        <?php echo _('What software would you like to research?'); ?><br/><br/>
                        <select id="research-list" placeholder="<?php echo _('Please select a software...'); ?>">
                            <option></option>
<?php                
            
            for($i=0;$i<sizeof($researchInfo);$i++){
            
                if(!self::isResearchable($researchInfo[$i]['softtype']) || $researchInfo[$i]['softtype'] == 29) continue;

?>
                            <option value="<?php echo $researchInfo[$i]['id']; ?>"><?php echo $researchInfo[$i]['softname'] . self::getExtension($researchInfo[$i]['softtype']); ?> (<?php echo self::dotVersion($researchInfo[$i]['softversion']); ?>)</option>
<?php                
                            
            }
            
?>
                        </select>
<?php                
            
        } else {
?>

                        <?php echo _('Ops! You do not have any software licensed to you.<br/><br/>Access the software information and buy it\'s license.'); ?>

<?php                                    
        }
?>
                    </div>
                </div>
                    
                                
            </div>

            <div class="span5">
                <div class="widget-box" style="text-align: left;">
                    <div class="widget-title">
                        <span id="research-switch" title="Switch to All-time stats" class="hide-phone link label label-info pull-right">Current round</span>
                        <span class="icon"><span class="he16-charts"></span></span>
                        <h5><?php echo _('Research stats'); ?></h5>
                    </div>

                    <div class="widget-content nopadding">

                        <table class="table table-cozy table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td><span class="item"><?php echo _('Total money spent on research'); ?></span></td>
                                    <td><font color="green">$<span id="research-side-money"><?php echo number_format($roundStats->moneyresearch); ?></span> </font><br/></td>
                                </tr>
                                <tr>
                                    <td><span class="item"><?php echo _('Total softwares researched'); ?></span></td>
                                    <td><span id="research-side-count"><?php echo number_format($roundStats->researchcount); ?></span></td>
                                </tr>
                                <tr>
                                    <td><span class="item"><?php echo _('Your research rank'); ?></span></td>
                                    <td><?php echo $researchRank; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                
<?php
        

    }
 
    public function research_getMultiplier($softType){
  
        switch($softType){
            case 1: //cracker
                return 25;
            case 2: //enc
                return 23;
            case 13: //ftp exp
            case 14: //ssh exp
                return 19;
            case 4: //firewall
                return 21;
            case 5: //hide
            case 6: //seek
                return 15;
            case 7: //av
            case 15: //nmap
                return 16;
            case 8: //vspam
            case 9: //vwarez
                return 12;
            case 10: //vddos
                return 13;
            case 11: //vcol
            case 12: //vbreak
                return 14;
            default:
                echo 'UNDEFINED MULTIPLIER TO SOFT TYPE '.$softType;
                return 15;
        }
 
    }
    
    public function research_calculatePrice($softVersion, $softType){

        $multiplier = self::research_getMultiplier($softType);
        $i = $softVersion;

        if($i >= 100){
            return ceil($i*($multiplier*(($i-90)/100)-10)+3000-(25-$multiplier)*80);
        } else {
            return ceil((pow($i, 2)-9*$i)/100*$multiplier);
        }
        
    }
    
    public function research_calculateSize($softVersion, $softType){
        
        $multiplier = self::research_getMultiplier($softType);
        $i = $softVersion;
        
        if($i >= 100){
            return ceil($i*($multiplier*(($i-100)/100)-9.5)+3000-(25-$multiplier)*80);
        } else {
            return ceil(((pow($i, 2)-9*$i)/110+1)*$multiplier);
        }
        
    }
    
    public function research_calculateRAM($softVersion, $softType){
        
        $multiplier = self::research_getMultiplier($softType);
        $i = $softVersion;
        
        if($i >= 100){
            if($i >= 150){
                return ceil($i*($multiplier*(($i-110)/100)-15)+3000-(25-$multiplier)*80);
            } else {
                return ceil($i*($multiplier*(($i-110)/100)-15)+3000-(25-$multiplier)*80);
            }
        } else {
            if($i >= 50){
                $price = (pow($i*0.9, 2)-9*$i)/145+0.3;
            } else {
                $price = (pow($i*0.9, 2)-9*$i)/140+0.4;
            }

            return ceil($price*$multiplier);
        }
        
    }
    
    public function research_calculateDuration($softVersion){

        if($softVersion < 30){
            return 280 + 20 * ($softVersion - 10);            
        }
        
        if($softVersion < 50){
            return 670 + 10 * ($softVersion - 50);
        }
        
        if($softVersion < 75){
            return 900;
        }
        
        if($softVersion < 100){
            return 1200;
        }
        
        if($softVersion < 150){
            return 1500;
        }

        return 1800;
        
    }
    
    public function research_durationToString($duration){
                
        return (int)($duration / 60)._(' minutes');

    }

    
    public function research_show($sid){
        
        $softInfo = self::getSoftware($sid, $_SESSION['id'], 'VPC');
        $softName = $softInfo->softname.self::getExtension($softInfo->softtype);
        
        if(!self::isResearchable($softInfo->softtype) || $softInfo->softtype == 29){
            $system = new System();
            $system->handleError('You are not allowed to research this type of software.', 'university');
        }
        
        $price = self::research_calculatePrice($softInfo->softversion, $softInfo->softtype);
        $duration = self::research_calculateDuration($softInfo->softversion);
        
        $ranking = new Ranking();
        $softwareRanking = $ranking->getSoftwareRanking($sid);
        $softwareRankingCategory = $ranking->getSoftwareRanking($sid, TRUE);
        
        if($softwareRanking == '-1'){
            $softwareRanking = 'Unranked';
        } else {
            $softwareRanking = '#'.number_format($softwareRanking);
        }
        
        if($softwareRankingCategory == '-1'){
            $softwareRankingCategory = 'Unranked';
        } else {
            $softwareRankingCategory = '#'.number_format($softwareRankingCategory);
        }
                
        require '/var/www/classes/Finances.class.php';
        $finances = new Finances();

        if($finances->totalMoney() >= $price){
            $canBuy = TRUE;
        } else {
            $canBuy = FALSE;
        }

?>
                                <div class="span7">
                                    <div class="widget-box text-left">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-research_info_selected"></span></span>
                                            <h5><?php echo _('Research software '); ?><?php echo $softName; ?></h5>
                                        </div>
                                        <div class="widget-content padding">                   
                                            <?php echo _('Research '); ?><b><?php echo $softName; ?></b><?php echo _(' from version '); ?><?php echo self::dotVersion($softInfo->softversion); ?><?php echo _(' to '); ?><b><?php echo self::dotVersion($softInfo->softversion + 1); ?></b><br/><br/>
                                            <span class="black"><?php echo _('Price'); ?>:</span> <span class="green">$<?php echo number_format($price); ?></span><br/>
                                            <span class="black"><?php echo _('Duration'); ?>:</span> <?php echo self::research_durationToString($duration); ?><br/>
                                            <br/>
<?php
if($canBuy){
?>
                                            <btn id="research" class="btn btn-info"><?php echo _('Research...'); ?></btn>
<?php                        
} else {
?>
                                            <btn class="btn btn-info disabled"><?php echo _('Insufficient money'); ?></btn>
<?php
}
?>
                                        </div>
                                    </div>
                                </div>
                                <div class="span5">
                                    <div class="widget-box" style="text-align: left;">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-charts"></span></span>
                                            <h5><?php echo _('Research stats'); ?></h5>
                                        </div>
                                        <div class="widget-content nopadding">
                                            <table class="table table-cozy table-bordered table-striped">
                                                <tbody>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Money you spent on research'); ?></span></td>
                                                        <td><span class="green">$<?php echo number_format($ranking->getMoneySpentOnResearch()); ?> </span><br/></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Cost to upgrade to '); ?><?php echo self::dotVersion($softInfo->softversion + 2); ?></span></td>
                                                        <td><span class="green">$<?php echo number_format(self::research_calculatePrice($softInfo->softversion + 1, $softInfo->softtype)); ?></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Software Rank'); ?><span class="small">(<?php echo _('General'); ?>)</span></span></td>
                                                        <td><?php echo $softwareRanking; ?> </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Software Rank'); ?><span class="small">(<?php echo _('Category'); ?>)</span></span></td>
                                                        <td><?php echo $softwareRankingCategory; ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div id="research-area" class="row-fluid" style="display:none;">
                                    <div class="span8">
                                        <div class="widget-box">
                                            <div class="widget-title">
                                                <span class="icon"><span class="he16-research"></span></span>
                                                <h5><?php echo _('Research software '); ?><?php echo $softName; ?></h5>
                                            </div>                            
                                            <div class="widget-content nopadding"> 
                                                <form action="" method="POST" class="form-horizontal">
                                                    <input type="hidden" name="act" value="research">   
                                                    <input type="hidden" name="id" value="<?php echo $sid; ?>">   
                                                    <div class="control-group input-prepend">
                                                        <label class="control-label"><?php echo _('Software name'); ?></label>
                                                        <div class="controls input-box">
                                                            <input name="name" type="text" style="width: 70%;" value="<?php echo $softInfo->softname; ?>" autofocus="1"/>
                                                            <span class="add-on" style="width: 20%;"><?php echo self::getExtension($softInfo->softtype); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label"><?php echo _('Delete old version'); ?></label>
                                                        <div class="controls">
                                                            <input type="checkbox" name="delete" value="0"/>
                                                        </div>
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label"><?php echo _('Bank Account'); ?></label>
                                                        <div class="controls">
                                                            <?php

$finances->htmlSelectBankAcc(); 

?>

                                                        </div>
                                                    </div>
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-success"><?php echo _('Research for $'); ?><?php echo $price; ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>                    
                                    </div>
                                    <div class="span4">
                                        <div class="widget-box" style="text-align: left;">
                                            <div class="widget-title">
                                                <span class="icon"><span class="he16-research_specs"></span></span>
                                                <h5><?php echo _('Research specs'); ?></h5>
                                            </div>
                                            <div class="widget-content nopadding">
                                                <table class="table table-cozy table-bordered table-striped">
<?php
    
    $newSize = self::research_calculateSize($softInfo->softversion, $softInfo->softtype);
    $newRam = self::research_calculateRAM($softInfo->softversion, $softInfo->softtype);
    
?>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="item"><?php echo _("RAM usage"); ?></span></td>
                                                        <td><span class="black"><?php echo number_format($newRam); ?> MB</span> <span class="small"><?php echo number_format($softInfo->softram); ?> + <?php echo $newRam - $softInfo->softram; ?> MB</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Size'); ?></span></td>
                                                        <td><span class="black"><?php echo number_format($newSize); ?> MB</span> <span class="small"><?php echo number_format($softInfo->softsize); ?> + <?php echo $newSize - $softInfo->softsize; ?> MB</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Duration'); ?></span></td>
                                                        <td><?php echo self::research_durationToString($duration); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
<?php                

    }

    public function isResearchable($softType){
        
        switch($softType){
            case 3:
            case 16:
            case 17:
            case 18:
            case 19:
                return FALSE;
        }
        
        if($softType >= 30){
            return FALSE;
        }
        
        return TRUE;
        
    }
    
    public function research($sid, $name, $remove){

        $softInfo = self::getSoftware($sid, $_SESSION['id'], 'VPC');
       
        if($softInfo->softtype == 29 || !self::isResearchable($softInfo->softtype)){
            exit("Modafuca");
        }
        
        $hddUsage = $this->hardware->calculateHDDUsage($_SESSION['id'], 'VPC');

        if($remove == 1){
            $hddUsage['AVAILABLE'] += $softInfo->softsize;            
        }
        
        $newHDUsage = self::research_calculateSize($softInfo->softversion, $softInfo->softtype);
        $newRAMUsage = self::research_calculateRAM($softInfo->softversion, $softInfo->softtype);

        if($newHDUsage <= 0 || $newRAMUsage <= 0){
            exit("Please contact the admin");
        }
        
        if($hddUsage['AVAILABLE'] < $newHDUsage){
            
            $system = new System();
            $system->handleError('You do not have enough disk space to research the new software. Research was cancelled.', 'software');
            
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, originalFrom, licensedTo) 
                VALUES ('', ?, ?, ?, ?, ?, ?, NOW(), '0', '0', '0', ?, ?)";
        $sqlReg = $this->pdo->prepare($sql);
        $sqlReg->execute(array($_SESSION['id'], $name, $softInfo->softversion + 1, $newHDUsage, $newRAMUsage, $softInfo->softtype, $_SESSION['id'], $softInfo->licensedto));        

        $newSoftID = $this->pdo->lastInsertId();
        
        $this->session->newQuery();
        $sql = "INSERT INTO ranking_software (softID) VALUES ('".$newSoftID."')";
        $this->pdo->query($sql);
        
        if($remove == 1){

            $this->session->newQuery();
            $sql = "DELETE FROM software WHERE id = '".$sid."' LIMIT 1";
            $this->pdo->query($sql);
            
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO software_research 
                    (userID, softwareType, newVersion, softwareName, softID) 
                VALUES 
                    (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(Array($_SESSION['id'], $softInfo->softtype, ($softInfo->softversion + 1), $softInfo->softname, $newSoftID));        
    }

    public function external_listSoftware(){

        $this->session->newQuery();
        $sqlSelect = "SELECT id, softName, softVersion, softSize, softRam, softType, uploadDate FROM software_external WHERE userID = '".$_SESSION['id']."' ORDER BY softtype, softversion DESC";
        $softInfo = $this->pdo->query($sqlSelect)->fetchAll();

        if(sizeof($softInfo) > 0){

?>
                                    <table class="table table-cozy table-bordered table-striped table-software table-hover with-check">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Name</th>
                                                <th>Version</th>
                                                <th>Size</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php
    
            $hdUsage = 0;
            for($i = 0; $i < sizeof($softInfo); $i++) {

                $img = '<span class="he16-'.$softInfo[$i]['softtype'].' tip-top" title="Uploaded on '.substr($softInfo[$i]['uploaddate'], 0, -3).'"></span>';
                
                $softName = $softInfo[$i]['softname'].self::getExtension($softInfo[$i]['softtype']);
                
                if($softInfo[$i]['softtype'] != 30) { 
                    $softVersion = self::colorSoftVersion($softInfo[$i]['softversion']);
                } else {
                    $softVersion = self::dotVersion($softInfo[$i]['softversion']);
                }
                
                $softSize = self::colorSoftSize($softInfo[$i]['softsize']);
                
                $hdUsage += $softInfo[$i]['softsize'];
                $softID = $softInfo[$i]['id'];

?>
                                            <tr>
                                                <td><?php echo $img; ?></td>
                                                <td><?php echo $softName; ?></td>
                                                <td><?php echo $softVersion; ?></td>
                                                <td><?php echo $softSize; ?></td>
                                                <td style="text-align: center">
                                                    <a href="?id=<?php echo $softID; ?>" class="tip-top" title="Show software information"><span class="he16-software_info heicon"></span></a>
                                                    <a href="?page=external&action=download&id=<?php echo $softID; ?>" class="tip-top" title="Download"><span class="he16-download heicon"></span></a>
                                                    <a href="?page=external&action=del&id=<?php echo $softID; ?>" class="tip-top" title="Delete"><span class="he16-bin heicon"></span></a>
                                                </td>
                                            </tr>
<?php

            }

?>
                                        </tbody>
                                    </table>
<?php

        } else {

            $hdUsage = 0;
?>
                                    <p><?php echo _('You do not have any software on your external HD. Use the sidebar to upload.'); ?></p>
<?php

        }
        
?>
                                </div>
<?php            
        
        self::external_uploadHTML($hdUsage);

?>
                                </div>
                            </div>
                            <div style="clear: both;" class="nav nav-tabs"></div>                
<?php
        
    }
    
    public function external_have(){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM software_external WHERE userID = '".$_SESSION['id']."' LIMIT 1";
        $tmp = $this->pdo->query($sql)->fetchAll();
        
        if(count($tmp) > '0'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function external_uploadHTML($hdUsage){
        
        $xhdInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
        
        if($hdUsage < 1000){
            $usedStr = $hdUsage.' MB';
        } else {
            $usedStr = round($hdUsage / 1000, 2).' GB';
        }

        $totalStr = round($xhdInfo['XHD'] / 1000, 2).' GB';

        $porct = round($hdUsage / $xhdInfo['XHD'], 3) * 100 ."%";        
        
?>
                                <div class="span3" style="text-align: center;">
                                    <form action="" method="GET">
                                        <input type="hidden" name="page" value="external">
                                        <input type="hidden" name="action" value="upload">
                                        <input id="upload-id" type="hidden" name="id" value="">
                                        <div class="controls">
                                            <span id="link" class="btn btn-primary"><?php echo _('Upload'); ?>...</span>
                                            <input type="hidden" id="uploadSelect" style="width: 100%" value="">
                                        </div>
                                        <div id="uploadForm"></div>
                                    </form>
                                    <div class="hd-usage">
                                        <div class="chart easyPieChart chartpie" data-percent="<?php echo ceil($porct); ?>">
                                            <div id="downmeplz"><span id="percentpie"></span></div>
                                        </div>
                                        <div class="hd-usage-text"><?php echo _('XHD usage'); ?></div>
                                        <span class="small"><?php echo '<font color="green">'.$usedStr.'</font> / <font color="red">'.$totalStr.'</font>'; ?></span>
                                    </div>
<?php

    }

}

class LogVPC extends Player {

    protected $pdo;
    protected $npc;
    public $session;
    private $logText;
    private $logID;
    private $logTime;

    function __construct($id = '') {

        if (is_numeric($id)) {

            parent::__construct($id);
        } else {

            
            $this->pdo = PDO_DB::factory();
        }

        require_once '/var/www/classes/Session.class.php';
        require_once '/var/www/classes/NPC.class.php';

        $this->session = new Session();
        $this->npc = new NPC();
    }

    public function logText($action, $infoArr){
        
        switch($action){
            
            case 'LOGIN':
                if($infoArr[0] == 0){
                    $log = 'localhost logged in';
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[1].'] logged in as '.$infoArr[2];
                } else {
                    $log = 'localhost logged in to ['.$infoArr[1].'] as '.$infoArr[2];
                }
                break;
            case 'DOWNLOAD':
                if($infoArr[0] == 1){
                    if($infoArr[4] != '0.0'){
                        $log = '['.$infoArr[1].'] downloaded file '.$infoArr[2].$infoArr[3].' ('.$infoArr[4].') at localhost';
                    } else {
                        $log = '['.$infoArr[1].'] downloaded file '.$infoArr[2].$infoArr[3].' at localhost';
                    }
                } else {
                    if($infoArr[4] != '0.0'){
                        $log = 'localhost downloaded file '.$infoArr[2].$infoArr[3].' ('.$infoArr[4].') at ['.$infoArr[1].']';
                    } else {
                        $log = 'localhost downloaded file '.$infoArr[2].$infoArr[3].' at ['.$infoArr[1].']';
                    }
                }
                break;
            case 'UPLOAD':
                if($infoArr[0] == 1){
                    if($infoArr[4] != '0.0'){
                        $log = '['.$infoArr[1].'] uploaded file '.$infoArr[2].$infoArr[3].' ('.$infoArr[4].') at localhost';
                    } else {
                        $log = '['.$infoArr[1].'] uploaded file '.$infoArr[2].$infoArr[3].' at localhost';
                    }
                } else {
                    if($infoArr[4] != '0.0'){
                        $log = 'localhost uploaded file '.$infoArr[2].$infoArr[3].' ('.$infoArr[4].') to ['.$infoArr[1].']';
                    } else {
                        $log = 'localhost uploaded file '.$infoArr[2].$infoArr[3].' to ['.$infoArr[1].']';
                    }
                }
                break;
            case 'DELETE':
                if($infoArr[0] == 0){
                    $log = 'localhost deleted file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] deleted file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost deleted file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'HIDE':
                if($infoArr[0] == 0){
                    $log = 'localhost hid file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] hid file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost hid file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'SEEK':
                if($infoArr[0] == 0){
                    $log = 'localhost seeked file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] seeked file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost seeked file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'AV':
                if($infoArr[0] == 0){
                    $log = 'localhost runned antivirus '.$infoArr[1].', deleting '.$infoArr[2].' virus.';
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[3].'] runned antivirus '.$infoArr[1].' at localhost, deleting '.$infoArr[2].' virus.';
                } else {
                    $log = 'localhost runned antivirus '.$infoArr[1].' at ['.$infoArr[3].'], deleting '.$infoArr[2].' virus.';
                }
                break;
            case 'INSTALL':
                if($infoArr[0] == 0){
                    $log = 'localhost installed file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] installed file '.$infoArr[1].' at localhost';
                } elseif($infoArr[0] == 2) {
                    $log = 'localhost installed file '.$infoArr[1].' at ['.$infoArr[2].']';
                } elseif($infoArr[0] == 3){
                    $log = '['.$infoArr[2].'] installed virus '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost installed virus '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'UNINSTALL':
                if($infoArr[0] == 0){
                    $log = 'localhost uninstalled file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] uninstalled file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost uninstalled file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'RESEARCH':
                $log = 'localhost researched software '.$infoArr[4].' for $'.$infoArr[1].'. Funds transfered from account #'.$infoArr[2].' at ['.$infoArr[3].']';
                break;
            case 'ANALYZE':
                if($infoArr[0] == 1){
                    $log = '['.$infoArr[1].'] analyzed hardware information from localhost using '.$infoArr[2];
                } else {
                    $log = 'localhost analyzed hardware information at ['.$infoArr[1].'] using '.$infoArr[2];
                }
                break;
            case 'DOOM':
                if($infoArr[0] == 0){
                    $log = 'localhost installed doom virus '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] installed doom virus '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost installed doom virus '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'IP_RESET':
                $log = 'localhost reseted ip ';
                if($infoArr[1] > 0){
                    $log .= 'for $'.$infoArr[1].'. Funds were transfered from #'.$infoArr[2].' at ['.$infoArr[3].']';
                } else {
                    $log .= 'for free';
                }
                break;
            case 'PWD_RESET':
                $log = 'localhost reseted password ';
                if($infoArr[1] > 0){
                    $log .= 'for $'.$infoArr[1].'. Funds were transfered from #'.$infoArr[2].' at ['.$infoArr[3].']';
                } else {
                    $log .= 'for free';
                }
                break;
            case 'TXT_NEW':
                if($infoArr[0] == 0){
                    $log = 'localhost created text file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] created text file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost created text file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'TXT_EDIT':
                if($infoArr[0] == 0){
                    $log = 'localhost edited text file '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] edited text file '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost edited text file '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'FOLDER_NEW':
                if($infoArr[0] == 0){
                    $log = 'localhost created folder '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] created folder '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost created folder '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'FOLDER_EDIT':
                if($infoArr[0] == 0){
                    $log = 'localhost edited folder '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] edited folder '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost edited folder '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'FOLDER_DEL':
                if($infoArr[0] == 0){
                    $log = 'localhost deleted folder '.$infoArr[1];
                } elseif($infoArr[0] == 1){
                    $log = '['.$infoArr[2].'] deleted folder '.$infoArr[1].' at localhost';
                } else {
                    $log = 'localhost deleted folder '.$infoArr[1].' at ['.$infoArr[2].']';
                }
                break;
            case 'BTCLOGIN':
                if($infoArr[0] == 1){
                    $log = 'anonymous login to account '.$infoArr[2];
                } else {
                    $log = 'localhost logged in to ['.$infoArr[1].'] on account '.$infoArr[2].' using key '.$infoArr[3];
                }
                break;
            case 'BUY_LICENSE':
                $log = 'localhost bought license of software '.$infoArr[0].$infoArr[2].' ('.$infoArr[1].') for $'.$infoArr[3].'. Funds transfered from account #'.$infoArr[4].' at ['.$infoArr[5].']';
                break;
            case 'BUY_HARDWARE':
                if($infoArr[0] == 0){
                    $log = 'localhost upgraded '.$infoArr[1].' to '.$infoArr[2].' for $'.$infoArr[3].'. Funds were transfered from acount #'.$infoArr[4].' at ['.$infoArr[5].']';
                } elseif($infoArr[0] == 1) {
                    $log = '['.$infoArr[6].'] upgraded localhost '.$infoArr[1].' to '.$infoArr[2].' for $'.$infoArr[3].'. Funds were transfered from acount #'.$infoArr[4].' at ['.$infoArr[5].']';
                } else {
                    $log = 'localhost upgraded ['.$infoArr[6].'] '.$infoArr[1].' to '.$infoArr[2].' for $'.$infoArr[3].'. Funds were transfered from acount #'.$infoArr[4].' at ['.$infoArr[5].']';
                }
                break;
            case 'BUY_PC':
                if($infoArr[0] == 0){
                    $log = 'localhost bought new server for $'.$infoArr[1].'. Funds were transfered from acount #'.$infoArr[2].' at ['.$infoArr[3].']';
                } elseif($infoArr[0] == 1) {
                    $log = '['.$infoArr[4].'] bought new server at localhost for $'.$infoArr[1].'. Funds were transfered from acount #'.$infoArr[2].' at ['.$infoArr[3].']';
                } else {
                    $log = 'localhost bought new server at ['.$infoArr[4].'] for $'.$infoArr[1].'. Funds were transfered from acount #'.$infoArr[2].' at ['.$infoArr[3].']';
                }
                break;
            case 'BUY_XHD':
                $log = 'localhost bought new external HD for $'.$infoArr[0].'. Funds were transfered from acount #'.$infoArr[1].' at ['.$infoArr[2].']';
                break;
            case 'DDOS':
                //it's done on the ddos own function.
                break;
        }
        
        return $log;
        
    }
    
    public function listLog($id, $pcType, $local) {

        $valid = '1';
        $npc = '0';

        if ($pcType == 'NPC') {
            $npc = '1';
        }
        
        if ($valid == '1') {

            $this->session->newQuery();
            $sqlSelect = "SELECT text FROM log WHERE userID = $id AND isNPC = $npc";
            $data = $this->pdo->query($sqlSelect)->fetchAll();

            if (count($data) == '0') {

                echo 'No logs';
                
            } else {

                if($local == 1){
                    $str = 1;
                } else {
                    $str = 0;
                }
                
                $formatedLog = htmlspecialchars($data['0']['text']);
                

                if($npc == 1 && $id == 59){
                    $formatedLog = _('Download Center doesnt record logs.');
                }
                
                ?>

                <div class="span2 center">
<?php if($local == 1 && $_SESSION['premium'] == 0){ ?>
<style type="text/css">
@media (min-width : 320px) { .adslot_log { width: 234px; height: 60px; display: none; } .logleft {display: none;} }
@media (min-width : 360px) and (max-width : 480px) { .adslot_log { width: 300px; height: 250px; display: none; } .logleft {display: none;} }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_log { width: 336px; height: 280px; display: none;} .logleft { display:block !important; } }
@media (min-width:1024px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1280px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1366px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1824px) { .adslot_log { width: 160px; height: 600px; margin-top: 8px;} .logarea{height:500px} .logleft { display:block !important; }} 
</style>
<div class="logleft">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- log both responsive -->
<ins class="adsbygoogle adslot_log"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="3338147350"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<?php } elseif($_SESSION['premium'] == 0) { ?>
<style type="text/css">
@media (min-width : 320px) { .adslot_log { width: 234px; height: 60px; display: none; } .logleft {display: none;} }
@media (min-width : 360px) and (max-width : 480px) { .adslot_log { width: 300px; height: 250px; display: none; } .logleft {display: none;} }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_log { width: 336px; height: 280px; display: none;} .logleft { display:block !important; } }
@media (min-width:1024px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1280px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1366px) { .adslot_log { width: 120px; height: 240px; margin-top: 50px; } .logleft { display:block !important; } }
@media (min-width:1824px) { .adslot_log { width: 200px; height: 200px; margin-top: 50px;} .logarea{height:300px}  .logleft { display:block !important; }}
</style>
<div class="logleft">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- log both responsive -->
<ins class="adsbygoogle adslot_log"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="3338147350"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<?php } ?>
                </div>
                <div class="span8 center">
                                    
                    <form action="logEdit" method="POST" class="log">
                        <input type="hidden" name="id" value="<?php echo $str; ?>">
                        <textarea class="logarea" rows="15" name="log" spellcheck=FALSE><?php echo $formatedLog; ?></textarea><br/><br/>
                        <input class="btn btn-inverse" type="submit" value="<?php echo _('Edit log file'); ?>">
                    </form>
                    <br/>

                </div>
                <div class="span2">
<?php if($_SESSION['premium'] == 0){ ?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- log both responsive -->
<ins class="adsbygoogle adslot_log"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="3338147350"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<?php } ?>
                </div>
                                    
                                    
                <?php
                
            }
            
            if($local == 0){
?>
                                </div>
                            </div>
<?php
            }
            
?>
     
                </div>
            <div class="nav nav-tabs" style="clear: both;"></div>
                                    
<?php
        }
    }

    public function addLog($uid, $logText, $npc) {

        $logFinal = date('Y-m-d H:i').' - ';
        
        $logFinal .= $logText;
                
        $valid = '0';

        if ($npc == 1 || $npc === 'NPC') {

            if ($this->npc->issetNPC($uid)) {
                $valid = '1';
                $npc = '1';
            }
            
        } elseif (parent::verifyID($uid)) {

            $valid = '1';
            $npc = '0';
        }

        $logFinal .= "\n";
        
        if ($valid == '1') {

            $this->session->newQuery();
            
            $sql = 'UPDATE log SET text = CONCAT(:logFinal, text) WHERE userID = :uid AND isNPC = :npc';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':logFinal' => $logFinal, ':uid' => $uid, ':npc' => $npc));
            
        } else {
            die("bErrror while adding log. contact admin");
        }

    }

    public function getLogValue($uid, $npc) {

        if($npc == 'NPC'){
            $npc = 1;
        } elseif($npc == 'VPC'){
            $npc = 0;
        }
        
        $this->session->newQuery();
        $sqlSelect = "SELECT text FROM log WHERE userID = $uid AND isNPC = $npc";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        return $data['0']['text'];
        
    }
    
    public function tmpLog($uid, $npc, $text){
        
        $this->session->newQuery();

        $sql = 'INSERT INTO log_edit (vicID, isNPC, editorID, logText) VALUES (:uid, :npc, :id, :text)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid' => $uid, ':npc' => $npc, ':id' => $_SESSION['id'], ':text' => $text));
                
        return $this->pdo->lastInsertId();
        
    }
    
    public function deleteTmpLog($logID){
        
        $this->session->newQuery();
        $sql = "DELETE FROM log_edit WHERE id = '".$logID."' LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function getTmpLog($logID){
        
        $this->session->newQuery();
        $sql = "SELECT logText
                FROM log_edit
                WHERE id = '".$logID."'
                LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->logtext;
        
    }

}

class Virus extends Player {

    protected $pdo;
    protected $software;
    protected $numPCs;
    protected $pcSpecs;
    protected $DDoS_newSpecs;
    
    public $session;
    public $hardware;
    public $log;
    
    function __construct($id = '') {

        if (is_numeric($id)) {

            parent::__construct($id);
            
        } else {

            $this->pdo = PDO_DB::factory();
            $this->software = new SoftwareVPC();
            
        }

        $this->session = new Session();
        
    }

    public function alreadyInstalled($victimIP, $virusType = '') {

        $hackerID = $_SESSION['id'];
        $where = "installedBy = $hackerID AND installedIP = $victimIP";
        
        if($virusType != ''){
            $where .= " AND virusType = '".$virusType."'";
        }
        
        $this->session->newQuery();
        $sqlSelect = "SELECT virusID FROM virus WHERE ".$where." LIMIT 1";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if (count($data) == '1') {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function getVirusInfo($virusID) {

        $this->session->newQuery();
        $sqlSelect = "SELECT installedBy, installedIP, virusType, TIMESTAMPDIFF(SECOND, lastCollect, NOW()) AS duration, active 
                      FROM virus 
                      WHERE virusID = :virusID 
                      LIMIT 1";
        $stmt = $this->pdo->prepare($sqlSelect);
        $stmt->execute(array(':virusID' => $virusID));
        $data = $stmt->fetch(PDO::FETCH_OBJ);         
        
        switch ($data->virustype) {

            case 1:
                $virusType = 'Spam';
                break;
            case 2:
                $virusType = 'Warez';
                break;
            case 3:
                $virusType = 'DDoS';
                break;
            case 4:
                $virusType = 'Miner';
                break;
            default:
                $virusType = 'Error';
                break;
        }

        $workedHours = (int)($data->duration / 3600);
        $workedMinutes = ($data->duration / 60) % 60;

        $stringWorked = $workedHours.'h and '.$workedMinutes.'m';           

        $victimID = parent::getIDByIP($data->installedip, '');
        $virusInfo = $this->software->getSoftware($virusID, $victimID['0']['id'], $victimID['0']['pctype']);

        return Array(
            'TIME_WORKED' => $stringWorked, //DEPRECATED, ACHO.
            'TIME_WORKED_SECONDS' => $data->duration,
            'INSTALLED_BY' => $data->installedby,
            'INSTALLED_IP' => $data->installedip,
            'VIRUS_TYPE' => $virusType,
            'VIRUS_NAME' => $virusInfo->softname,
            'VIRUS_VERSION' => $virusInfo->softversion,
        );
        
    }

    public function studySecondsTime($seconds) {

        if ($seconds < '60') {

            $minTime = '0';
            $hourTime = '0';
            $dayTime = '0';
        } elseif ($seconds > '59' && $seconds < '3600') {

            $newSeconds = $seconds / (60);

            $minTime = floor($newSeconds);
            $hourTime = '0';
            $dayTime = '0';
        } elseif ($seconds > '3599' && $seconds < '86400') {

            $newSeconds = $seconds / (60 * 60);

            if (strstr($newSeconds, ".")) {
                $seconds = explode(".", $newSeconds);
                $sec = "0." . $seconds['1'];

                $minTime = round($sec * 60);
            } else {
                $minTime = '0';
            }

            $hourTime = floor($newSeconds);
            $dayTime = '0';
        } else {

            $dayTime = 'Many';
            $hourTime = 'many';
            $minTime = 'many';
        }

        return $dayTime . ' days, ' . $hourTime . ' hours and ' . $minTime . ' minutes';
    }

    public function DDoS_count() {

        $this->session->newQuery();
        $sql = "SELECT virusID FROM virus WHERE installedBy = '" . $_SESSION['id'] . "' AND virusType = 3 AND active = 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        return count($this->pdo->query($sql)->fetchAll());
    }

    private function DDoS_mitigate($victimInfo){
        
        $bestSoft = $this->software->getBestSoftware(4, $victimInfo['0']['id'], $victimInfo['0']['pctype'], 1);

        if($bestSoft['0']['exists'] == 1){
            
            $v = $bestSoft['0']['softversion'];
            
            if($v < 30){
                
                $rand = rand(10, 30);
                
                $min = 0;
                $max = 1;

            } elseif($v < 50){
                
                $rand = rand(30, 50);
                
                $min = 1;
                $max = 2;
                
            } elseif($v < 100){
                
                $rand = rand(50, 100);
                
                $min = 2;
                $max = 3;                
                
            } elseif($v < 150){
                
                $rand = rand(100, 150);
                
                $min = 3;
                $max = 4;                
                
            } elseif($v < 200){
                
                $rand = rand(150, 200);
                
                $min = 4;
                $max = 5;                
                
            } else {
                return 5;
            }

            if($v >= $rand){
                return $max;
            } else {
                return $min;
            }             
            
        } else {
            return 0;
        }
        
    }
    
    public function DDoS_studyDamage($ddosPower, $mitigate, $hardwareBefore) {
        
        $bonus = 0;

        if($hardwareBefore['CPU'] == 500){
            $bonus++;
        }
        if($hardwareBefore['RAM'] == 256){
            $bonus++;
        }
        if($hardwareBefore['HDD'] == 1000){
            $bonus++;
        }
        if($hardwareBefore['NET'] == 1){
            $bonus++;
        }

        $ddosConstant = '2000';
        $i = '0';
        $damage = '0';
        $returnDamage = Array();

        for ($i = 0; $i < 4; $i++) {

            $rand = rand(1, 100);

            if ($ddosPower < 10000) {

                if ($rand < 15) { //15% de fazer dano 1
                    $damage = '1';
                }
                
            } elseif ($ddosPower < 15000) {

                if ($rand < 30) {
                    $damage = rand(1, 2); //30% de fazer dano 1 ou 2 (50%)
                }
                
            } elseif ($ddosPower < 20000) {

                if ($rand < 50) {
                    $damage = rand(1, 3); //50% de fazer dano 1 a 3 (33%)
                }
                
            } else {

                $randDamage = ceil(($ddosPower - 20000) / $ddosConstant); //o restante div. por dois mil, aproximado para cima;
                
                if ($randDamage < 4) { //até 26 mil
                    $damage = rand(2, 4);
                } else {
                    $damage = rand($randDamage - 2, $randDamage + 2);
                }
                
            }
            
            $damage += $bonus;

            $mitigatedDamage = $damage - $mitigate;
            if($mitigatedDamage < 0){
                $mitigatedDamage = 0;
            }
            
            $returnDamage[$i] = $mitigatedDamage;
        }

        return $returnDamage;
        
    }

    public function DDoS_record($power, $servers, $attUser, $victimID, $victimPCType){
        
        if($victimPCType == 'NPC'){
            $npc = 1;
        } else {
            $npc = 0;
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO round_ddos (id, attID, attUser, vicID, power, servers, date, vicNPC)
                VALUES ('', '".$_SESSION['id']."', '".$attUser."', '".$victimID."', '".$power."', '".$servers."', NOW(), '".$npc."')";
        $this->pdo->query($sql);
        
        if($npc == 0){
        
            $this->session->newQuery();
            $sql = "INSERT INTO ranking_ddos (ddosID)
                    VALUES ('".$this->pdo->lastInsertId()."')";
            $this->pdo->query($sql);

        }
        
    }
    
    public function DDoS_studyClanExp($victimInfo, $ddosPower, $seized = false){
        
        if($seized){
            $ddosPower = round($ddosPower / 5);
        }
        
        require_once '/var/www/classes/Clan.class.php';
        $clan = new Clan();

        $myClanID = NULL;

        if($clan->playerHaveClan()){

            $myClanID = $clan->getPlayerClan();

            if($victimInfo['0']['pctype'] == 'VPC'){

                if($clan->playerHaveClan($victimInfo['0']['id'])){

                    $vicClanID = $clan->getPlayerClan($victimInfo['0']['id']);

                    if($vicClanID != $myClanID){

                        if(!$clan->issetWar($vicClanID, $myClanID)){

                            $clanDDoS = 3;
                            if(!$clan->defcon_exist($_SESSION['id'], $myClanID, $victimInfo['0']['id'], $vicClanID)){

                                $clan->defcon($_SESSION['id'], $myClanID, $victimInfo['0']['id'], $vicClanID);

                            }

                        } else {

                            $clanDDoS = 4;

                            $clan->war_update($vicClanID, $ddosPower);

                        }

                        $clan->war_recordDDoS($victimInfo['0']['id'], $vicClanID);

                    } else {
                        $clanDDoS = 2;
                    }

                } else {
                    $clanDDoS = 2;
                }

            } else {

                require_once '/var/www/classes/NPC.class.php';
                $npc = new NPC();

                $npcInfo = $npc->getNPCInfo($victimInfo['0']['id']);

                if($npcInfo->npctype == 10){

                    $this->session->newQuery();
                    $sql = "SELECT clanID FROM clan WHERE clanIP = '".$npcInfo->npcip."' LIMIT 1";
                    $vicClanID = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->clanid;

                    if($vicClanID != $myClanID){

                        if(!$clan->issetWar($vicClanID, $myClanID)){

                            if(!$clan->defcon_exist($_SESSION['id'], $myClanID, $victimInfo['0']['id'], $vicClanID, 1)){

                                $clan->defcon($_SESSION['id'], $myClanID, $victimInfo['0']['id'], $vicClanID, 1);

                            }

                        } else {

                            $clan->war_update($vicClanID, $ddosPower);

                        }

                        $clan->war_recordDDoS($victimInfo['0']['id'], $vicClanID);

                        $clanDDoS = 4;

                    } else {
                        $clanDDoS = 1;
                    }                                        

                } else {
                    $clanDDoS = 1;
                }

            }

        }

        if($myClanID != NULL){

            $newPoints = $clan->DDoS_addPoints($clanDDoS, $ddosPower, $myClanID);

            return '<br/><b>'.number_format($newPoints, '0', '.', ',').'</b> points were added to your clan<br/>';

        } else {
            return '';
        }
        
    }
    
    public function DDoS($ip){
        
        $error = '';
        $issetDoom = FALSE;
        
        $ranking = new Ranking();
        $this->hardware = new HardwareVPC();
        $this->log = new LogVPC();
        
        $hackerInfo = parent::getPlayerInfo($_SESSION['id']);

        if ($hackerInfo->gameip != $ip) {

            $victimInfo = parent::getIDByIP($ip, '');

            if ($victimInfo['0']['existe'] == '1') {

                if (self::DDoS_count() > 2) {

                    $report = '';
                    
                    $victimHardwareBefore = $this->hardware->getHardwareInfo($victimInfo['0']['id'], $victimInfo['0']['pctype']);

                    require 'hardwareItens.php';

                    if($victimHardwareBefore['CPU'] > $cpuItens['1']['POW']){

                        $numPCs = $this->hardware->getTotalPCs($victimInfo['0']['id'], $victimInfo['0']['pctype']);

                    } else {
                        $numPCs = 1;
                    }

                    if( ($victimHardwareBefore['CPU'] == ($cpuItens['1']['POW'] * $numPCs)) &&
                        ($victimHardwareBefore['HDD'] == ($hddItens['1']['POW'] * $numPCs)) &&
                        ($victimHardwareBefore['RAM'] == ($ramItens['1']['POW'] * $numPCs)) &&     
                        ($victimHardwareBefore['NET'] == 1) ){

                        $seizedBefore = 1;

                    } else {
                        $seizedBefore = 0;
                    }

                    $ddoserHardware = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                    
                    $this->session->newQuery();
                    $sql = "SELECT ip, ddosName, ddosVersion, cpu FROM virus_ddos WHERE userID = '".$_SESSION['id']."' AND active = 1";
                    $tmp = $this->pdo->query($sql);

                    $total = '0';
                    $ddosPower = $ddoserHardware['CPU'] + ($ddoserHardware['NET'] * 10);

                    if($ddosPower == 500){
                        $hardwarePenalty = TRUE;
                    } else {
                        $hardwarePenalty = FALSE;
                    }

                    $crackerSoft = $this->software->getBestSoftware(1, $_SESSION['id'], 'VPC');
                    $victimCracker = $this->software->getBestSoftware(1, $victimInfo['0']['id'], $victimInfo['0']['pctype']);
                    
                    $attackerVersion = 0;
                    $victimVersion = 0;
                    
                    if($crackerSoft['0']['exists'] == 1){
                        $attackerVersion = $crackerSoft['0']['softversion'];
                    }
                    
                    if($victimCracker['0']['exists'] == 1){
                        $victimVersion = $victimCracker['0']['softversion'];
                    }

                    $noobPenalty = 0;
                    if($victimVersion == 0){
                        $noobPenalty++;
                    }
                    
                    if($victimVersion <= 20){
                        if(($victimVersion + 10) < $attackerVersion){
                            $noobPenalty++;
                        }
                    }

                    if(($victimVersion * 2) < $attackerVersion){
                        $noobPenalty++;
                    }
                    
                    if ($this->session->issetMissionSession()) {
                        if($_SESSION['MISSION_TYPE'] == '5'){
                            if($victimInfo['0']['pctype'] == 'NPC'){
                                $noobPenalty = 0;
                            }
                        }
                    }
                    
                    $slaveInfoArray = Array();
                    $ddosInfoArray = Array();

                    $serverDesc = '';
                    
                    while ($ddosInfo = $tmp->fetch(PDO::FETCH_OBJ)) {

                        if ($ddosInfo->ip != $ip) {

                            $slaveInfoArray[$ddosInfo->ip] = parent::getIDByIP($ddosInfo->ip, '');

                            $ddosInfoArray[$total]['DDOS_NAME'] = $ddosInfo->ddosname;
                            $ddosInfoArray[$total]['DDOS_VERSION'] = $ddosInfo->ddosversion;
                            $ddosInfoArray[$total]['INSTALLED_IP'] = $ddosInfo->ip;

                            $bonus = $ddosInfo->ddosversion * 5;
                            $curPower = $ddosInfo->cpu + $bonus;

                            //TODO: curpower pega a cpu pela tabela virus_ddos. mas esse dado é atualizado? verificar.
                            //TODO: study dynamics of ddos power;
                            $ddosPower += $curPower;

                            $total++;
                            
                            $serverDesc .= 'Server <u>'.long2ip($ddosInfo->ip).'</u> generated a DDoS power of <font color="red">'.self::power2gbps($curPower).' Gbps </font> using <b>'.$ddosInfo->ddosname.'.vddos ('.$this->software->dotVersion($ddosInfo->ddosversion).')</b> <br/>';

                        }
                        
                    }
                    
                    if($hardwarePenalty){
                        $ddosPower /= 2;
                    }

                    if($noobPenalty){
                        $ddosPower /= (2 + $noobPenalty);
                        if($ddosPower >= 100000){
                            $ddosPower *= 0.1;
                        }
                    }
                    
                    $serverDesc .= '<br/>';

                    $isClan = $isBot = FALSE;
                    if($victimInfo['0']['pctype'] == 'NPC'){
                    
                        $npc = new NPC();
                        $npcInfo = $npc->getNPCInfo($victimInfo['0']['id']);                       

                        if($npcInfo->npctype == 10){
                            $isClan = TRUE;
                        } elseif($npcInfo->npctype == 99){
                            $isBot = TRUE;
                        }

                    }
                                        
                    if ($total > 2) {

                        if($seizedBefore == 0){

                            if($victimInfo['0']['pctype'] == 'VPC'){
                                if(self::doom_isset($victimInfo['0']['id'])){
                                    $issetDoom = TRUE;
                                }
                            } elseif($isClan) {
                                if(self::doom_clanUsed($victimInfo['0']['id'], TRUE)){
                                    $issetDoom = TRUE;
                                }
                            }

                            $ddosMitigation = self::DDoS_mitigate($victimInfo);
                            
                            $damageInfo = self::DDoS_studyDamage($ddosPower, $ddosMitigation, $victimHardwareBefore);
                            
                            self::DDoS_doDamage($damageInfo['0'], 'CPU', $victimInfo);
                            self::DDoS_doDamage($damageInfo['1'], 'HDD', $victimInfo);
                            self::DDoS_doDamage($damageInfo['2'], 'RAM', $victimInfo);
                            self::DDoS_doDamage($damageInfo['3'], 'NET', $victimInfo);
                            
                        } else {
                            $damageInfo = 0;
                        }

                        $slaveLogTxt = '';
                        $hackerLogTxt = 'localhost launched a DDoS attack against ['.long2ip($ip).']';
                        $hackedLogTxt = '['.long2ip($hackerInfo->gameip).'] launched a DDoS attack against localhost';

                        $aux = '0';

                        while ($aux < $total) {

                            $hackerLogTxt .= "\n";
                            $hackedLogTxt .= "\n";
                            
                            $slaveLogTxt = '[' . long2ip($hackerInfo->gameip) . '] DDoSed [' . long2ip($ip) . '] via localhost using ' . $ddosInfoArray[$aux]['DDOS_NAME'] . '.vddos (' . $this->software->dotVersion($ddosInfoArray[$aux]['DDOS_VERSION']) . ')';
                            $this->log->addLog($slaveInfoArray[$ddosInfoArray[$aux]['INSTALLED_IP']]['0']['id'], $slaveLogTxt, $slaveInfoArray[$ddosInfoArray[$aux]['INSTALLED_IP']]['0']['pctype']);

                            $hackerLogTxt .= 'localhost DDoSed [' . long2ip($ip) . '] via [' . long2ip($ddosInfoArray[$aux]['INSTALLED_IP']) . '] using ' . $ddosInfoArray[$aux]['DDOS_NAME'] . '.vddos (' . $this->software->dotVersion($ddosInfoArray[$aux]['DDOS_VERSION']) . ')';
                            $hackedLogTxt .= '[' . long2ip($hackerInfo->gameip) . '] DDoSed localhost via [' . long2ip($ddosInfoArray[$aux]['INSTALLED_IP']) . '] using ' . $ddosInfoArray[$aux]['DDOS_NAME'] . '.vddos (' . $this->software->dotVersion($ddosInfoArray[$aux]['DDOS_VERSION']) . ')';

                            $aux++;

                        }

                        $this->log->addLog($_SESSION['id'], $hackerLogTxt, 'VPC');
                        $this->log->addLog($victimInfo['0']['id'], $hackedLogTxt, $victimInfo['0']['pctype']);

                        $totalDamage = '0';
                        for ($i = 0; $i < 4; $i++) {
                            $totalDamage += $damageInfo[$i];
                        }

                        $this->session->exp_add('DDOS', Array($ddosPower, $totalDamage, $seizedBefore));
                        
                        $report = 'DDoS attack against <b>'.long2ip($ip).'</b><br/><br/>';

                        $down = 0;
                        $seizedFBI = False;
                        if ($totalDamage == '0') {

                            if($seizedBefore == 0){

                                $report .= 'DDoS was sucessfull, however <i>NO</i> damage was done. Increase your DDoS power.<br/>';

                                if($ddosMitigation > 0){
                                    $report .= 'Victim firewall might have mitigated your DDoS attack.<br/>';
                                }
                                
                            } else {

                                $report .= 'DDoS was sucessfull, however this server is already seized. No damage was inflicted.';

                                if($victimInfo['0']['pctype'] == 'NPC' && $seizedBefore == 1 && !$isBot){

                                    if($ddosPower >= 15000){

                                        $down = 1;

                                        $this->session->newQuery();
                                        $sqlSelect = "SELECT COUNT(*) AS total FROM npc_down WHERE npcID = '".$victimInfo['0']['id']."' LIMIT 1";
                                        $total = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;

                                        if($total == 1){
                                            $report .= '<br/>This server is already down! You probably added some downtime.<br/>';
                                            
                                            $this->session->newQuery();
                                            $sql = 'UPDATE npc_down SET downUntil = DATE_ADD(downUntil, INTERVAL 1 HOUR) WHERE npcID = \''.$victimInfo['0']['id'].'\' LIMIT 1';
                                            //$this->pdo->query($sql);
                                            
                                        } else {
                                            $report .= '<br/>Due to recurrence, this server is down!<br/>';
                                            
                                            $this->session->newQuery();
                                            $sql = 'INSERT INTO npc_down (npcID, downUntil) VALUES (\''.$victimInfo['0']['id'].'\', DATE_ADD(NOW(), INTERVAL 2 HOUR))';
                                            //$this->pdo->query($sql);
                                            
                                        }
                                        
                                        $this->session->newQuery();
                                        $sql = 'DELETE FROM internet_connections WHERE ip = \''.$ip.'\' LIMIT 1';
                                        $this->pdo->query($sql);
                                        
                                        if($this->session->issetInternetSession()){
                                            if($_SESSION['LOGGED_IN'] == $ip){
                                                $this->session->deleteInternetSession();
                                            }
                                        }

                                    } else {

                                        $report .= 'You need to increase your DDoS power in order to take down this server. <br/>';

                                    }

                                }

                                require_once '/var/www/classes/Storyline.class.php';
                                $storyline = new Storyline();

                                if($storyline->fbi_isset($ip)){

                                    $seizedFBI = TRUE;
                                    
                                    $report .= self::ddos_seizeFBI($ip, $victimInfo, $hackerInfo, $storyline);

                                }
                                
                                //record ddos na db
                                self::DDoS_record(round($ddosPower / 5), $total, $hackerInfo->login, $victimInfo['0']['id'], $victimInfo['0']['pctype']);

                                $report .= self::DDoS_studyClanExp($victimInfo, $ddosPower, true);
                                
                            }

                        } else {

                            require_once '/var/www/classes/Process.class.php';
                            $process = new Process();
          
                            // 2019: TODO: Verify whether that line \/ is working. You really should!
                            //TODO: verificar se essa linha \/ tá funcionando
                            $process->updateProcessTime($victimInfo['0']['id'], $victimInfo['0']['pctype']);
                   
                            $victimHardwareAfter = $this->hardware->getHardwareInfo($victimInfo['0']['id'], $victimInfo['0']['pctype']);

                            if( ($victimHardwareAfter['CPU'] == ($cpuItens['1']['POW'] * $numPCs)) &&
                                ($victimHardwareAfter['RAM'] == ($ramItens['1']['POW'] * $numPCs)) &&
                                ($victimHardwareAfter['HDD'] == ($hddItens['1']['POW'] * $numPCs)) &&
                                ($victimHardwareAfter['NET'] == '1') ){

                                $report .= 'Massive damage! Victim was <b>seized</b>.<br/><br/>';
                                $seizedAfter = 1;

                            } else {
                                $seizedAfter = 0;
                            }
                                                
                            $ranking->updateDDoSCount($total);

                            $report .= $serverDesc;
                            $report .= 'Hardware damage: <br/><br/>';

                            $cpuDamage = abs(100 - (($victimHardwareAfter['CPU']*100)/$victimHardwareBefore['CPU']));
                            $ramDamage = abs(100 - (($victimHardwareAfter['RAM']*100)/$victimHardwareBefore['RAM']));
                            $hddDamage = abs(100 - (($victimHardwareAfter['HDD']*100)/$victimHardwareBefore['HDD']));
                            $netDamage = abs(100 - (($victimHardwareAfter['NET']*100)/$victimHardwareBefore['NET']));

                            if($cpuDamage == 0){
                                $report .= 'CPU was not damaged<br/>';
                            } else {
                                $report .= 'CPU overheat lead to <font color="red">'.round($cpuDamage, 1).'%</font> loss<br/>';
                            }
                            
                            if($ramDamage == 0){
                                $report .= 'RAM circuits were not affected<br/>';
                            } else {
                                $report .= 'Ram shorted and was damaged at <font color="red">'.round($ramDamage, 1).'%</font><br/>';
                            }

                            if($hddDamage == 0){
                                $report .= 'Hard drive suffered no damage<br/>';
                            } else {
                                $report .= 'Hard drive damage led to <font color="red">'.round($hddDamage, 1).'%</font> loss and some files might have been wiped out<br/>';
                            }
                            
                            if($netDamage == 0){
                                $report .= 'Network kept stable<br/>';
                            } else {
                                $report .= 'Network suffered a total loss of <font color="red">'.round($netDamage, 1).'%</font><br/>';
                            }
                            
                            if($ddosMitigation > 0){
                                $report .= '<br/>Victim firewall might have mitigated your DDoS attack.<br/>';
                            }                            
                                                        
                            if($issetDoom){
                                
                                $stopped = TRUE;
                                if($victimInfo['0']['pctype'] == 'VPC'){
                                    if(self::doom_isset($victimInfo['0']['id'])){
                                        $stopped = FALSE;
                                    }
                                } else {
                                    if(self::doom_clanUsed($victimInfo['0']['id'], TRUE)){
                                        $stopped = FALSE;
                                    }
                                }
                                
                                if($stopped){
                                    $report .= '<br/><strong><span class="green">SUCCESS! You stopped the Doom Virus!</span></strong><br/>';
                                } else {
                                    $report .= '<br/><strong><span class="red">The doom virus is still working.</span></strong></br>';
                                }

                            }
                            
                            $report .= '<br/><u>DDoS power:</u> <b>'.number_format(self::power2gbps($ddosPower), '0', '.', ',').' Gbps</b><br/>';

                            if($seizedAfter == 1){

                                require_once '/var/www/classes/Storyline.class.php';
                                $storyline = new Storyline();

                                if($storyline->fbi_isset($ip)){

                                    $seizedFBI = TRUE;
                                    
                                    $report .= self::ddos_seizeFBI($ip, $victimInfo, $hackerInfo, $storyline);

                                }

                            }
                            
                            //record ddos na db
                            self::DDoS_record($ddosPower, $total, $hackerInfo->login, $victimInfo['0']['id'], $victimInfo['0']['pctype']);

                            $report .= self::DDoS_studyClanExp($victimInfo, $ddosPower);
                            
                            //add badge
                            require_once '/var/www/classes/Social.class.php';
                            $social = new Social();
                            
                            $social->badge_add(13, $_SESSION['id']);

                        }

                        //study FBI and/or SAFENET consequences
                        $ddosConsequence = self::DDoS_consequences($ddosPower, $hackerInfo->gameip, $ip, $seizedFBI);

                        switch($ddosConsequence){

                            case 0:
                                $consequenceStr = 'Safenet and FBI won\'t track you because you are targeting a wanted IP.';
                                break;
                            case 1:
                                $consequenceStr = 'You got lucky: There was no SafeNet activity.<br/>';
                                break;
                            case 2:
                                $consequenceStr = '<b>Busted! Safenet tracked you. Be careful!</b>';
                                break;
                            case 3:
                                $consequenceStr = 'You got lucky: FBI did not get you.';
                                break;
                            case 4:
                                $consequenceStr = '<b>Busted! FBI is right behind you. Expect attacks!</b>';
                                break;
                            case 5:
                                $consequenceStr = '<b>FBI is now paying more for your head. Be careful!</b>';
                                break;

                        }

                        $report .= '<br/>'.$consequenceStr.'<br/>';

                        if ($this->session->issetMissionSession()) {

                            if($_SESSION['MISSION_TYPE'] == '5'){

                                require_once '/var/www/classes/Mission.class.php';
                                $mission = new Mission();

                                if($mission->missionVictim($_SESSION['MISSION_ID']) == $ip){

                                    if($down == 1){

                                        $mission->completeMission($_SESSION['MISSION_ID']);

                                        $report .= '<br/><b><font color="green">Mission complete!</font></b> The server is down<br/>';

                                    } else {

                                        $report .= '<br/>Mission is not yet complete, since the server is still online<br/>';

                                    }

                                }

                            }

                        }
                        
                        $name = 'DDoS_'.long2ip($ip);
                        
                        $this->session->newQuery();
                        $sql = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, licensedTo)
                                VALUES ('', '".$_SESSION['id']."', '".$name."', '0', '1', '0', '30', NOW(), '0', '0', '0', '0')";
                        $this->pdo->query($sql);
                        
                        $txtID = $this->pdo->lastInsertID();

                        $this->session->newQuery();
                        $sqlQuery = "INSERT INTO software_texts (id, userID, isNPC, text, lastEdit, ddos) VALUES ('".$txtID."', '".$_SESSION['id']."', '0', ?, NOW(), 1)";
                        $sqlReg = $this->pdo->prepare($sqlQuery);
                        $sqlReg->execute(array($report));
                        
                    } else {
                        $error = 'The virus installed on the victim doesnt count, and so you dont have 3 viruses running.';
                    }
                } else {
                    $error = 'You must have at least 3 DDoS running virus.';
                }

            } else {
                $error = 'This user doesnt exists.';
            }
        } else {
            $error = 'Yeah, you cant ddos yourself.';
        }                                    

        if($error != ''){
            
            $this->session->addMsg($error, 'error');
            header("Location:list?action=ddos&ignore=1");
            exit();
            
        }
        
    }
    
    public function ddos_seizeFBI($ip, $victimInfo, $hackerInfo, $storyline){
        
        $bounty = $storyline->fbi_getBounty($ip);

        $formatedBounty = number_format($bounty);
        $victimName = parent::getPlayerInfo($victimInfo['0']['id'])->login;

        $str = '<br/>Congratulations! You seized a FBI wanted IP. You received the bounty of <font color="green">$'.$formatedBounty.'</font>. You are on the news!<br/>';

        $storyline->fbi_payBounty($ip);

        $title = $hackerInfo->login.' seized FBI suspect known as '.$victimName;

        $brief = 'Hacker <a href="profile?id='.$_SESSION['id'].'">'.$hackerInfo->login.'</a> managed to DDoS and seize the FBI suspect known as <a href="profile?id='.$victimInfo['0']['id'].'">'.$victimName.'</a>.<br/>
                '.$hackerInfo->login.' received a total bounty of <font color="green">$<b>'.$formatedBounty.'</b></font> for doing this brave act.';

        require '/var/www/classes/News.class.php';
        $news = new News();

        $news->news_add(-2, $title, $brief, Array($_SESSION['id'], $bounty, ''));

        require_once '/var/www/classes/Mail.class.php';
        $mail = new Mail();

        $title = _('Congratulations.');
        $brief = _('Hello, ').$hackerInfo->login.'. '._('We from FBI appreciate you taking down hacker ').$victimName.'. '._('As a thank you, please receive this bounty of').' <font color="green"><b>$'.$formatedBounty.'</b></font>';

        $mail->newMail($_SESSION['id'], $title, $brief, 3, -2);

        return $str;
        
    }
    
    public function power2gbps($power){
        
        return round(($power / 100), 1);
        
    }
    
    public function DDoS_doDamage($damage, $item, $victimInfo) {

        if ($damage > '0') {

            $hardware = new HardwareVPC();

            if (strlen($this->numPCs) == '0') {
                $this->numPCs = $hardware->getTotalPCs($victimInfo['0']['id'], $victimInfo['0']['pctype']);
            }

            for ($i = 0; $i < $this->numPCs; $i++) {
                $this->pcSpecs[$i] = $hardware->getPCSpec('', $victimInfo['0']['pctype'], $victimInfo['0']['id'], $i);
            }
            
            $i = '0';

            self::DDoS_commitDamage($victimInfo, $item, $damage);
            
        }
    }
    

    private function DDoS_damageAlg($item, $damage) {

        require 'hardwareItens.php';
        
        $i = '0';
        $newSpecs = Array();
        $DDoS_newSpecs = Array();
            
        $hardItem = strtolower($item).'Itens';

        $itensInfo = $$hardItem;

        while ($i < $this->numPCs) {

            $pcSpecs = $this->pcSpecs[$i];

            if ($pcSpecs['ISSET'] == '1') {

                $minSpec = $comp = $itensInfo[1]['POW'];
                $partSpecs = $pcSpecs[$item];
                $curdamage = $damage / 10; //transforma em porcentagem
                
                if($item == 'NET'){
                    $partSpecs *= 1000;
                    $comp = $minSpec * 1000;
                } elseif($item == 'RAM'){
                    if($partSpecs <= 1500){
                        $curdamage *= 3;
                    }
                } elseif($item == 'CPU'){
                    if($partSpecs <= 1500){
                        $curdamage *= 2;
                    }
                } elseif($item == 'HDD'){
                    if($partSpecs <= 1000){
                        $curDamage *= 2;
                    }
                }
                
                $newSpecs = ceil($partSpecs - $partSpecs * $damage);
                
                if($item == 'NET' && $newSpecs < 10000){
                    $newSpecs = ceil($newSpecs - $partSpecs * ($damage));
                }
                
                if($newSpecs < $comp){
                    $newSpecs = $minSpec;
                }

                $DDoS_newSpecs[$item][$i] = $newSpecs;
            }

            $i++;
        }
       
        $this->DDoS_newSpecs = $DDoS_newSpecs;

    }

    private function DDoS_commitDamage($victimInfo, $item, $damage) {

        $hardware = new HardwareVPC();

        if ($item == 'HDD' || $item == 'RAM') {

            $hardwareInfo = $hardware->getHardwareInfo($victimInfo['0']['id'], $victimInfo['0']['pctype']);
            
        }
        
        self::DDoS_damageAlg($item, $damage);

        $i = '0';

        while ((array_key_exists($i, $this->DDoS_newSpecs[$item])) || ($this->numPCs > $i)) {

            $curSpecs = $this->pcSpecs[$i];

            if ($curSpecs['ISSET'] == '1') {

                if (array_key_exists($i, $this->DDoS_newSpecs[$item])) {
                    $newSpecs = $this->DDoS_newSpecs[$item][$i];
                } else {
                    $newSpecs = $curSpecs[$item];
                }

                if($item != 'NET'){

                    $hardware->updateHardwareSpecs($this->pcSpecs[$i]['ID'], $victimInfo['0']['id'], $victimInfo['0']['pctype'], $newSpecs, $item);

                } else {
                                        
                    if($newSpecs > 1){
                        $newSpecs /= 1000;
                    }
                    
                    if($i == 0){                    
                        $hardware->updateHardwareNet($victimInfo['0']['id'], $victimInfo['0']['pctype'], $newSpecs);
                    }
                    
                }
            }

            $i++;
        }
        
        if ($item == 'HDD') {

            $hardware->checkHDDForOverclock($victimInfo['0']['id'], $victimInfo['0']['pctype'], $hardwareInfo);
            
        } elseif ($item == 'RAM') {

            $hardware->checkRamForOverclock($victimInfo['0']['id'], $victimInfo['0']['pctype'], '', $hardwareInfo);
            
        }
        
    }
    
    public function doom_haveInstalled($id, $npc){
        
        $this->session->newQuery();
        $sql = "SELECT software.softtype, software.id
                FROM software
                WHERE software.userID = '".$id."' AND software.isNPC = '".$npc."' AND softType = 96";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            $doomArr = Array();
            
            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['softtype'] == 29){

                    $this->session->newQuery();
                    $sql = "SELECT softID FROM software_running WHERE softID = '".$data[$i]['id']."' LIMIT 1";
                    $result = $this->pdo->query($sql)->fetchAll();

                    if(sizeof($result) == 1){
                        return $result['0']['softid'];
                    } 
                    
                } else {
                    $doomArr[$i] = $data[$i]['id']; 
                }
                
            }

            shuffle($doomArr);

            if(array_key_exists(0, $doomArr)){
            
                return $doomArr[0];
            
            }

        }
        
        return FALSE;

    }
    
    public function doom_install($softID, $id, $npc, $vid, $vnpc){

        if($vid == ''){
            $vid = $_SESSION['id'];
        }
        
        if($vnpc == ''){
            $vnpc = 0;
        }
        
        if($npc == 1){
            $pcType = 'NPC';
        } else {
            $pcType = 'VPC';
        }
        
        $software = new SoftwareVPC();
                
        $doomDB = $software->getSoftware($softID, $id, $pcType);
        
        if(self::doom_isset('', $doomDB->originalfrom)){

            // 2019: Translation: Bugs:
            // 2019: When I'm connected to a clan server I can't upload the virus (check if the samme happens with other NPCs)

            /* 
             * bugs: 
             * além disso, qndo estou conctado a um clan server eu nao consigo upar o vírus (ver com outros npcs)
             */

            $this->session->newQuery();
            $sql = "INSERT INTO software (userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, isNPC, originalFrom, licensedTo, isFolder)
                    VALUES ('".$vid."', '".$doomDB->softname."', '".$doomDB->softversion."', '100', '".$doomDB->softram."', '90',
                    NOW(), 0, $vnpc, $doomDB->originalfrom, '', 0)";
            $this->pdo->query($sql);
        
        }
        
    }
        
    public function doom_sendMission($type, $info = ''){
        
        $mission = new Mission();
        
        if($type == 4 || $type == 5){
            $vicInfo = parent::getIDByIP($_SESSION['LOGGED_IN'], 'VPC');
            $id = $vicInfo['0']['id'];
        } else {
            $id = $_SESSION['id'];
        }
        
        if(!$mission->doom_haveMission($id)){
            
            require '/var/www/classes/Clan.class.php';
            require '/var/www/classes/Mail.class.php';
            
            $mail = new Mail();
            $finances = new Finances();
            
            require '/var/www/classes/Storyline.class.php';
            $storyline = new Storyline();
            
            $user = parent::getPlayerInfo($id)->login;
            
            if(!$storyline->nsa_haveDoom()){
                $storyline->nsa_installDoom();
            }

            switch($type){
                
                // 2019: User developed crc X
                case 1: //Usuario desenvolveu crc X
                    
                    //TRADUÇÃO TODO
                    $missionType = 50;
                    
                    $subject = _('Doom the world');
                    $text = _('Hello ').$user.'. '._('Remember me?').'
                            '._('We\'ve been tracking you since you started working for us, and now we have reasons to believe your newly researched cracker is good enough to hack into the NSA.
                            I sent you some seed money in order to support your incoming actions. I\'ll send much more as soon as you complete the Doom attack.
                            You can check your progress and further informations on your <a href="missions">missions page</a>.');
                    $addMoney = 25000;
                    
                    break;
                case 2: //User downloaded Doom
                    
                    $missionType = 51;
                    
                    $subject = _('Doom the world');
                    $text = 'Hey '.$user.'. I see you downloaded the Doom virus. Check your <a href="missions">missions page</a> for more information.
                            I sent you some seed money. Expect attacks.';
                    
                    $addMoney = 100000;
                    
                    break;
                case 3: //User downloaded someone's CRC X
                    
                    $missionType = 52;
                    
                    $subject = _('Doom the world');
                    $text = $user.', looks like you downloaded a very powerful cracker.
                            It\'s powerful enough to hack the NSA! Check your <a href="missions">missions page</a>.
                            I sent you some seed money :)';
                    $addMoney = 25000;
                    
                    break;
                case 4: //Doom was uploaded on user
                    
                    $missionType = 53;
                    
                    $subject = _('Doom the world');
                    $text = 'Hello '.$user.'. A friend of yours uploaded a fresh version of the doom virus. Research it and doom the world. More details on the mission sent to you.
                             In addition, we also sent you $100,000 to help cover the costs of researching.';
                    
                    $addMoney = 100000;
                    
                    break;
                case 5: //Someone uploaded CRC X to user
                    
                    $missionType = 54;
                    
                    $subject = _('Doom the world');
                    $text = $user.', looks like you were uploaded a very powerful cracker. 
                            It\'s powerful enough to hack the NSA! Check your <a href="missions">missions page</a>.
                            I sent you some seed money :)';
                    $addMoney = 25000;                             
                    
                    break;
                default:
                    die("Erro doom_sendMission");
                    break;
                
            }

            $mission->doom_createMission($missionType, $info, $id);
            
            $mail->newMail($id, $subject, $text, '', -1);
            
            $finances->addMoney($addMoney, $finances->getWealthiestBankAcc());
            
        }
        
    }
    
    public function doom_haveInstaller($uid, $npc = ''){
        
        if($npc == ''){
            $npc = 0;
        }

        $this->session->newQuery();
        $sql = "SELECT software.id, software.licensedTo
                FROM software
                WHERE software.userID = '".$uid."' AND software.isNPC = $npc AND softType = 29";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['licensedto'] == $_SESSION['id']){
                    return TRUE;
                }
            }
            
        }
        
        return FALSE;
        
    }
    
    public function doom_setup($id, $isClan){

        $system = new System();
        
        $valid = 1;
        $msg = '';
        if(self::doom_isset($_SESSION['id'])){
            
            $valid = 0;
            $msg = 'You already have a Doom service running.';
            $redirect = 'software';
            
        } elseif($isClan == 1) {
            
            if(self::doom_clanUsed($id)){

                $valid = 0;
                $msg = 'There already is a Doom running on your clan. Install it on your VPC or wait for its ending.';
                $redirect = 'internet?view=software';

            }

        }
        
        if($valid == 1){

            require_once '/var/www/classes/Clan.class.php';
            require '/var/www/classes/Storyline.class.php';
            require '/var/www/classes/Social.class.php';
            
            $storyline = new Storyline();
            $clan = new Clan();
            $social = new Social();
            
            $durTime = 21600; //6 horas
            $newSize = 500; //Novo tamanho: 501MB (Para ser (necessariamente) deletado pelo HD em caso de ddos overclock)
            
            $playerInfo = parent::getPlayerInfo($_SESSION['id']);
            
            if($isClan == 1){
                $clanID = $clan->getPlayerClan();
                $pcType = 'NPC';
                $clanInfo = $clan->getClanInfo($clanID);
                $doomIP = $clanInfo->clanip;
                $redirect = 'internet?view=software';
            } else {
                $clanID = '';
                $pcType = 'VPC';
                $doomIP = $playerInfo->gameip;
                $redirect = 'software';
            }
            
            $doomID = self::doom_getID($id, $isClan)->id;
            $doomInfo = $this->software->getSoftware($doomID, $id, $pcType);
            
            $hardware = new HardwareVPC();
            $hddInfo = $hardware->calculateHDDUsage($id, $pcType);            

            if($hddInfo['AVAILABLE'] >= $newSize){

                $this->session->newQuery();
                $sql = "UPDATE software SET originalFrom = '".$doomID."', softSize = '".$newSize."' WHERE id = '".$doomID."'";
                $this->pdo->query($sql);

                $this->session->newQuery();
                $sql = "INSERT INTO virus_doom (doomID, doomIP, creatorID, clanID, releaseDate, doomDate, status)
                        VALUES ($doomID, $doomIP, '".$_SESSION['id']."', '".$clanID."', NOW(), DATE_ADD(NOW(), INTERVAL '".$durTime."' SECOND), '1')";
                $this->pdo->query($sql);

                $this->session->newQuery();
                $sql = "INSERT INTO software_running (softID, userID, ramUsage, isNPC)
                        VALUES ('".$doomID."', $id, 0, $isClan)";
                $this->pdo->query($sql);

                $this->session->newQuery();
                $sql = "INSERT INTO software (userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, isNPC, originalFrom, licensedTo, isFolder)
                        VALUES ('".$_SESSION['id']."', '".$doomInfo->softname."', '".$doomInfo->softversion."', '100', '".$doomInfo->softram."', '90',
                        NOW(), 0, 0, $doomID, '', 0)";
                $this->pdo->query($sql);
                
                if($isClan == 1){
                    
                    $this->session->newQuery();
                    $sql = "INSERT INTO software (userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, isNPC, originalFrom, licensedTo, isFolder)
                            VALUES ('".$id."', '".$doomInfo->softname."', '".$doomInfo->softversion."', '100', '".$doomInfo->softram."', '90',
                            NOW(), 0, 1, $doomID, '', 0)";
                    $this->pdo->query($sql);                    
                    
                    $userIP = $playerInfo->gameip;
                } else {
                    $userIP = $doomIP;
                }

                $software = new SoftwareVPC();
                $softName = $doomInfo->softname.'.doom ('.$software->dotVersion($doomInfo->softversion).')';
                
                $log = new LogVPC();
                
                if($isClan == 1){
                    
                    $paramClan = Array(1, $softName, $playerInfo->gameip);
                    $paramMy = Array(2, $softName, long2ip($doomIP));
                    
                    $log->addLog($clanID, $log->logText('DOOM', $paramClan), 1);
                    $log->addLog($_SESSION['id'], $log->logText('DOOM', $paramMy), 0);
                    
                } else {
                    
                    $param = Array(0, $softName);
                    
                    $log->addLog($_SESSION['id'], $log->logText('DOOM', $param), 0);
                    
                }
                
                $storyline->safenet_add($userIP, 2);
                $storyline->fbi_add($userIP, 2);
                
                $doomIP = '<a href="internet?ip='.long2ip($doomIP).'">'.long2ip($doomIP).'</a>';
                
                if($isClan == 1){
                    $location = 'clan server <a href="clan?id='.$clanID.'">'.$clanInfo->name.'</a> located at '.$doomIP;
                    $support = 'was installed by <a href="profile?id='.$_SESSION['id'].'">'.$playerInfo->login.'</a>';
                } else {
                    $location = '<a href="profile?id='.$_SESSION['id'].'">'.$playerInfo->login.'</a>\'s server located at '.$doomIP;
                    if($clan->playerHaveClan()){
                        $clanID = $clan->getPlayerClan();
                        $clanInfo = $clan->getClanInfo($clanID);
                        $support = ' is receiving support of the clan <a href="clan?id='.$clanID.'">'.$clanInfo->name.'</a>';
                    } else {
                        $support = ' he is on a lonewolf attack withouth clan support';
                    }
                }
                
                $evilCorpIP = $storyline->evilcorp_getIP();
                $evilCorp = '<a href="internet?ip='.$evilCorpIP.'">'.$storyline->evilcorp_getName().'</a>';
                
                $doomNewsTitle = 'Doom revealed';
                $doomNewsText =  'Doom virus was first seen at '.$location.' and '.$support.'. FBI is tracking his physical location and he should soon be at the FBI Wanted List. ';
                $doomNewsText .= 'There are reasons to believe this attack is being supported by '.$evilCorp.'. ';
                $doomNewsText .= 'NSA director is afraid that this is not the only doom virus since forensics team found more attack logs at their mainframe.';

                require '/var/www/classes/News.class.php';
                $news = New News();
                $news->news_add(-1, $doomNewsTitle, $doomNewsText, Array('ip', 'lol', '+6'));
                                
                if($isClan == 1){
                    
                    //add doom badge pro usuário que lanchou o ataque, mesmo sendo clan
                    $social->badge_add(11, $_SESSION['id']);
                    
                } else {
                    
                    //add doom badge
                    $social->badge_add(11, $_SESSION['id']);
                    
                    //add 'have name quoted on news' badge
                    $social->badge_add(71, $_SESSION['id']);
                    
                }
                
            } else {
                $system->handleError('Installed doom requires 500 MB of disk space.', $redirect);
            }

        } else {
            $system->handleError($msg, $redirect);
        }
        
    }
    
    private function doom_getID($vid, $isClan){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM software WHERE userID = $vid AND isNPC = $isClan AND licensedTo = '".$_SESSION['id']."' AND softType = 29";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
    }
    
    private function doom_isset($crID, $vID = ''){
        
        $this->session->newQuery();
        
        if($vID == ''){
            $sql = "SELECT doomID FROM virus_doom WHERE creatorID = '".$crID."' AND status = 1 LIMIT 1";
        } else {
            $sql = "SELECT doomID FROM virus_doom WHERE doomID = '".$vID."' AND status = 1 LIMIT 1";
        }
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    private function doom_clanUsed($clanID, $npc2clan = FALSE){
        
        if($npc2clan){
            $this->session->newQuery();
            $sql = "SELECT clan.clanID
                    FROM npc
                    INNER JOIN clan
                    ON clan.clanIP = npc.npcIP
                    WHERE npc.id = '".$clanID."' LIMIT 1";
            $clanID = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->clanid;
        }
        
        $this->session->newQuery();
        $sql = "SELECT doomID FROM virus_doom WHERE clanID = '".$clanID."' AND status = 1 LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function doom_specificIsset($doomID, $userID, $npc){

        $this->session->newQuery();
        $sql = "SELECT id FROM software WHERE userID = $userID AND isNPC = $npc AND softType = 90 AND originalFrom = '".$doomID."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function doom_disable($doomID, $uid){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM virus_doom WHERE doomID = '".$doomID."' LIMIT 1";
        $running = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($running == 1){ //esse doom que será deletado está rodando, vou desabilitá-lo

            $this->session->newQuery();
            $sql = "SELECT id, softSize FROM software WHERE softType = 90 AND originalFrom = '".$doomID."'";
            $data = $this->pdo->query($sql)->fetchAll();

            for($i=0;$i<sizeof($data);$i++){

                $this->session->newQuery();
                $sql = "DELETE FROM software WHERE id = '".$data[$i]['id']."' LIMIT 1";
                $this->pdo->query($sql);

            }

            $this->session->newQuery();
            $sql = "UPDATE virus_doom SET status = 2 WHERE doomID = '".$doomID."'";
            $this->pdo->query($sql);
            
            $this->session->newQuery();
            $sql = "DELETE FROM software_running WHERE softID = '".$doomID."' LIMIT 1";
            $this->pdo->query($sql);

            $this->session->newQuery();
            $sql = "INSERT INTO doom_abort (doomID, abortedBy, abortDate) 
                    VALUES ('".$doomID."', '".$_SESSION['id']."', NOW())";
            $this->pdo->query($sql);            
            
            require '/var/www/classes/Social.class.php';
            require '/var/www/classes/News.class.php';
            require '/var/www/classes/Clan.class.php';
            
            $social = new Social();
            $news = New News();
            $clan = new Clan();
            $finances = new Finances();

            //add badge 'anti doom'
            $social->badge_add('12', $_SESSION['id']);

            //add badge 'quoted on news'
            $social->badge_add('71', $_SESSION['id']);
            
            $reward = 100000;
            $rewardStr = '<span class="green">$'.number_format($reward).'</span>';
            
            $finances->addMoney($reward, $finances->getWealthiestBankAcc());
            
            $this->session->newQuery();
            $sql = "SELECT creatorID, clanID, doomIP, releaseDate, TIMESTAMPDIFF(SECOND, NOW(), doomDate) AS timeLeft FROM virus_doom WHERE doomID = '".$doomID."' LIMIT 1";
            $doomInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

            $doomIP = long2ip($doomInfo->doomip);
            $locationIP = '<a href="internet?ip='.$doomIP.'">'.$doomIP.'</a>';
            $idToDisable = $doomInfo->creatorid;
            $doomClan = $doomInfo->clanid;
            
            if($doomClan == 0){
                $releasedBy = '<a href="profile?id='.$doomInfo->creatorid.'">'.parent::getPlayerInfo($doomInfo->creatorid)->login.'</a>';
                if($clan->playerHaveClan($doomInfo->creatorid)){
                    $clanID = $clan->getPlayerClan($doomInfo->creatorid);
                    $supportedBy = '<a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                    $supportedStr = ' supported by clan '.$supportedBy;
                } else {
                    $supportedBy = 'Lonewolf';
                    $supportedStr = ' a lonewolf attack, since there was no support from any clan.';
                }
                $locationStr = $releasedBy.'\'s personal computer';
                
            } else {
                $clanID = $doomClan;
                $releasedBy = '<a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                $supportedBy = '<a href="profile?id='.$doomInfo->creatorid.'">'.parent::getPlayerInfo($doomInfo->creatorid)->login.'</a>';
                $locationStr = $releasedBy.' clan server';
                $supportedStr = ' launched by ';
            }
            
            $abortTime = $doomInfo->timeleft;
            if($abortTime < 60){
                $timeStr = $abortTime.' seconds';
            } else if($abortTime < 3600){
                $timeStr = $abortTime / 60 .' minutes';
            } else {
                $timeStr = round($abortTime / 3600).' hour';
                if($abortTime > 7200){
                    $timeStr .= 's';
                }
            }
            
            $disabledBy = '<a href="profile?id='.$_SESSION['id'].'">'.parent::getPlayerInfo($_SESSION['id'])->login.'</a>';
            
            $disableNewsTitle = 'Doom disabled';
            $disableNewsContent =  'Doom at '.$locationIP.' was disabled by '.$disabledBy.' only '.$timeStr.' before the strike. The attack was located at '.$locationStr.' and was'.$supportedStr.$supportedBy.'. ';
            $disableNewsContent .= 'For this honourable action, '.$disabledBy.' was rewarded '.$rewardStr.'. ';
            $disableNewsContent .= 'Despite the good news, NSA claims new doom attacks might pop, since there was more than one breach on it\'s servers.';
            
            $news->news_add(-1, $disableNewsTitle, $disableNewsContent, Array($_SESSION['id'], $reward, ''));
            
            require_once '/var/www/classes/Mission.class.php';
            $mission = new Mission();

            if($mission->playerOnMission($idToDisable)){
                $mission->doom_abort($mission->getPlayerMissionID($idToDisable));
                //now I'll have to logout the user $idToDisable, otherwise his mission will still be recored on the session
                $this->session->newQuery();
                $sql = 'DELETE FROM users_online WHERE id = :doomUser LIMIT 1';
                $data = $this->pdo->prepare($sql);
                $data->execute(array(':doomUser' => $idToDisable));
            }

        }

    }
    
    public function totalVirus($ip){
        
        $this->session->newQuery();
        $sql = "SELECT virusID FROM virus WHERE installedIp = '".$ip."' AND installedBy = '".$_SESSION['id']."' AND active = 1";
        
        return sizeof($this->pdo->query($sql)->fetchAll());
        
    }

    // 2019: Random note from my ex
    
    //matheus passou por aqui//
    //duas vezes//
    
    public function activateViruses($ip){
        
        $this->session->newQuery();
        $sql = "SELECT virusID FROM virus WHERE installedBy = '".$_SESSION['id']."' AND installedIp = '".$ip."' AND active = 0";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            $this->session->newQuery();
            $sql = "UPDATE lists SET virusID = '".$data['0']['virusid']."' WHERE userID = '".$_SESSION['id']."' AND ip = '".$ip."'";
            $this->pdo->query($sql);

            $this->session->newQuery();
            $sql = "UPDATE virus SET active = 1, lastCollect = NOW() WHERE installedBy = '".$_SESSION['id']."' AND installedIp = '".$ip."'";
            $this->pdo->query($sql);
            
            $this->session->newQuery();
            $sql = "UPDATE virus_ddos SET active = 1 WHERE userID = '".$_SESSION['id']."' AND ip = '".$ip."'";
            $this->pdo->query($sql);
            
        }
        
    }
    
    public function DDoS_consequences($ddosPower, $ddoserIP, $victimIP, $seizedFBI){
        
        require_once '/var/www/classes/Storyline.class.php';
        $storyline = new Storyline();

        if($storyline->fbi_isset($victimIP) || $seizedFBI){
            return 0;
        }
        
        if($ddosPower > 20000){

            if($ddosPower < 25000){ //good power (20 - 25)
                
                $safenetOdds = 0.25;
                $fbiOdds = 0.1;
                
            } elseif($ddosPower < 35000){ //very good power (25-35)
                
                $safenetOdds = 0.50;
                $fbiOdds = 0.25;
                
            } else { //huge power (over 35k)
                
                $safenetOdds = 1.0; //100%
                $fbiOdds = 0.5; //50% chance going fbi
                
            }
            
        } else {
            
            $safenetOdds = 0.1;
            $fbiOdds = 0.1;
            
        }

        $die = (rand(1, 10)/10);

        if(!$storyline->safenet_isset($ddoserIP, 1)){//nao está na safenet
        
            $consequence = 1; 

            if($safenetOdds >= $die){ //se combinar a porcentagem, eu pego =D
                
                $storyline->safenet_add($ddoserIP, 1, $ddosPower);
                $consequence = 2;
                
            }
        
        } else { //está na safernet! reincidente = vamos ver no FBI, seu puto
            
            $storyline->safenet_update($ddoserIP, 1, $ddosPower);

            if(!$storyline->fbi_isset($ddoserIP, 1)){
                
                $consequence = 3;
                if($fbiOdds >= $die){

                    $consequence = 4;
                    $storyline->fbi_add($ddoserIP, 1, $ddosPower);
                    
                }                
                
            } else {

                $consequence = 5;
                $storyline->fbi_update($ddoserIP, 1, $ddosPower);
                
            }
            
        }
        
        return $consequence;

    }
    
    public function delete_ddos_reports(){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM software_texts WHERE userID = ".$_SESSION['id']." AND isNPC = 0 AND ddos = 1";
        $data = $this->pdo->query($sql);
        
        while($textInfo = $data->fetch(PDO::FETCH_OBJ)){
            
            $this->session->newQuery();
            $sql = "DELETE FROM software_texts WHERE id = ".$textInfo->id;
            $this->pdo->query($sql);
            
            $this->session->newQuery();
            $sql = "DELETE FROM software WHERE id = ".$textInfo->id;
            $this->pdo->query($sql);
            
        }
        
    }

}

?>
