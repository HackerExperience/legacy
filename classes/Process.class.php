<?php

// 2019: This is the most complex part of Legacy and HE2.

require_once '/var/www/classes/Session.class.php';
require_once '/var/www/classes/Player.class.php';
require_once '/var/www/classes/PC.class.php';
require_once '/var/www/classes/System.class.php';
require_once '/var/www/classes/NPC.class.php';
require_once '/var/www/classes/List.class.php';
require_once '/var/www/classes/Finances.class.php';
require_once '/var/www/classes/Ranking.class.php';

class Process {

    public $pID;
    public $pCreatorID;
    public $pVictimID; 
    public $pAction;
    public $pSoftID;
    public $pLocal;
    public $pTimeLeft;
    public $pInfo;
    public $pInfoStr;
    public $bestSoft;
    public $pNPC;
    public $cpuUsage;
    public $netUsage;
    
    private $session;
    private $player;
    private $software;
    private $hardware;
    private $system;
    private $npc;
    private $pdo;
    private $list;
    private $virus;
    private $finances;
    private $ranking;

    function __construct(){

        
        $this->pdo = PDO_DB::factory();

        $this->session = new Session();
        $this->player = new Player();
        $this->software = new SoftwareVPC();
        $this->hardware = new HardwareVPC();
        $this->virus = new Virus();
        $this->system = new System();
        $this->npc = new NPC();
        $this->list = new Lists();
        $this->finances = new Finances();
        $this->ranking = new Ranking();

    }

    public function getDownloadSpeed($vicID, $vicNPC, $pAction, $netUsage){
        
        if($vicNPC == 1){
            $pcType = 'NPC';
        } else {
            $pcType = 'VPC';
        }
                
        $netInfoHacker = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
        $netInfoHacked = $this->hardware->getHardwareInfo($vicID, $pcType);
        
        if($pAction == 1){ //download
            
            $downloadRateHacker = $netInfoHacker['NET']/8;
            $uploadRateHacked = $netInfoHacked['NET']/16;            
            
            $transferRate = $downloadRateHacker;
            if($uploadRateHacked < $downloadRateHacker){
                $transferRate = $uploadRateHacked;
            }            
            
        } else { //upload

            $uploadRateHacker = $netInfoHacker['NET']/16;
            $downloadRateHacked = $netInfoHacked['NET']/8;            

            $transferRate = $uploadRateHacker;
            if($downloadRateHacked < $uploadRateHacker){
                $transfer = $downloadRateHacked;
            }            
            
        }
        
        return ($transferRate * 1000) * ($netUsage / 100);
        
    }
     
   public function listProcesses($uid, $type){

        switch($type){
            
            case 'all':
                $str = '';
                $str2 = ', cpuUsage, netUsage';
                break;
            case 'cpu':
                $str = ' AND (pAction <> 1 AND pAction <> 2) ';
                $str2 = ', cpuUsage';
                break;
            case 'net':
                $str = ' AND (pAction = 1 OR pAction = 2) ';
                $str2 = ', netUsage';
                break;
            
        }
        
        if($this->player->verifyID($uid)){

            $this->session->newQuery();
            $sqlSelect = "SELECT pid, pvictimid, paction, psoftid, pinfo, plocal, pnpc, isPaused, TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft $str2 FROM processes WHERE pcreatorid = $uid $str ORDER BY ptimeend DESC";
            $data = $this->pdo->query($sqlSelect);
            
            if($data->rowCount() != '0'){

                ?>

                <ul class="list">

                <?php
                
                $scriptArr = Array();
                
                $i=0;
                while($pInfo = $data->fetch(PDO::FETCH_OBJ)){

                    ?>  
                    
                    <li>
                        <div class="span4">
                            <div class="proc-desc">

                    <?php                        
                    
                    $action = self::getProcAction($pInfo->paction);

                    if($pInfo->ptimeleft > 0){ //ainda n terminou

                        $timeLeft = $pInfo->ptimeleft;
                        $done = '0';

                    } else { //processo terminou

                        $timeLeft = 0;
                        $done = '1';

                    }

                    if($pInfo->pvictimid != '0'){

                        if($pInfo->pnpc == '0'){ //é vpc

                            $pVictimInfo = $this->software->getPlayerInfo($pInfo->pvictimid);
                            $pcType = 'VPC';

                        } else {

                            $pVictimInfo = $this->npc->getNPCInfo($pInfo->pvictimid);
                            $pcType = 'NPC';

                        }

                    } else {
                        $pcType = 'VPC';
                    }

                    if($pInfo->plocal == 1){
                        $host = " <i>".'localhost'."</i>";
                    } else {
                        if($pInfo->pnpc == 0){
                            $ip = long2ip($pVictimInfo->gameip);
                        } else {
                            $ip = long2ip($pVictimInfo->npcip);
                        }

                        $host = "<a href=\"internet?ip=$ip\">$ip</a>";
                    }                    
                    
                    if($pInfo->psoftid != '0'){ //é software

                        if(self::mustChangeID($pInfo->paction, $pInfo->plocal)){ //é download, então a id é outra
                            $uid = $pInfo->pvictimid;
                        } else {
                            $uid = $_SESSION['id'];
                        }

                        $xid = $uid;
                        if($pInfo->paction == '2'){
                            $pcType = 'VPC';
                            $xid = $_SESSION['id'];
                        }

                        $ignore = 0;
                        $valid = 0;
                        if($this->software->issetSoftware($pInfo->psoftid, $uid, $pcType)){
                            $valid = 1;
                        } elseif($this->software->issetSoftware($pInfo->psoftid, $_SESSION['id'], $pcType) == TRUE && $pInfo->paction == '2'){
                            $valid = 1;
                        } elseif($this->pAction > '17' || $this->pAction < '21'){
                            
                            if($this->software->issetExternalSoftware($pInfo->psoftid)){
                                $softInfo = $this->software->getExternalSoftware($pInfo->psoftid);
                                $ignore = 1;
                                $valid = 1;
                            }
                            
                        }
                        
                        if($valid == 1){

                            if($ignore == 0){
                                $softInfo = $this->software->getSoftware($pInfo->psoftid, $xid, $pcType);
                            }
                                                        
                            $extension = $this->software->getExtension($softInfo->softtype);
 
                            if($pInfo->paction == 22 || $pInfo->paction == 23 || $pInfo->paction == 24){
                            
                                if($pInfo->paction == 24){
                                    
                                    echo _('Doom the Internet using ')."<b>".$softInfo->softname.$extension."</b>".' ('.$this->software->dotVersion($softInfo->softversion).')'._(' at ').$host;
                                    
                                } else {
                                
                                    echo $action. _('at ').$host._(' using ')."<b>".$softInfo->softname.$extension."</b>".' ('.$this->software->dotVersion($softInfo->softversion).')'; 

                                }
                                
                            } else {
                                
                                echo $action;
                                ?>

                                 file <b><?php echo $softInfo->softname.$extension; ?></b> 
                                 <?php if($softInfo->softtype != 30){ ?>
                                 (<?php echo $this->software->dotVersion($softInfo->softversion); ?>)
                                 <?php }
                                 
                                 echo _(' at ').$host;                                
                                
                            }
                            

                            if(strlen($softInfo->softname.$extension) < 15){ //tornar mais preciso; (se for mto longo, já tem uma quebra de linha
                                echo "<br/><br/>";
                            }                                      
                            
                        } else {

                            if($pInfo->paction == 13){
                                
                                echo _('Install unknown file at ').$host;
                                
                            } else {
                            
                                echo $action._(' at ').$host;
                            
                            }

                        }              

                    } else { //nao usa software, apenas pinfo

                        if($action == 'DDoS'){
                            
                            echo _('DDoS attack against ').$host;
                            
                        } else {
                        
                            echo $action. _(' at ').$host; 
                        
                        }
                        echo "<br/><br/>";
                        
                    }
                    
                    ?>
                         
                        </div>
                    </div>
                        
                    <div class="span5">    
                        
                    <?php             
                    
                    
                    if($pInfo->ispaused == 0){
                    
                    ?>
                        

                    
                        <div id="process<?php echo $pInfo->pid; ?>">
                            <div class="percent"></div>
                            <div class="pbar"></div>
                            <div class="elapsed"></div>
                        </div>

                    <?php
                    } else {
                        echo _('Paused');
                    }
                    ?>
                        
                    </div>
                    <div class="span3 proc-action">
                        <div class="span6">                        
                                <?php
                                
                                if($pInfo->paction < 3){
                                    if($timeLeft == 0 || $pInfo->ispaused == 1){
                                        $usage = 0;
                                        $link = '';
                                    } else {
                                        $usage = round($pInfo->netusage, 1);
                                        $link = 'processes#';
                                    }
                                    
                                    $speed = self::getDownloadSpeed($pInfo->pvictimid, $pInfo->pnpc, $pInfo->paction, $pInfo->netusage);

                                    ?>
                            
                                    <span class="he16-net heicon"></span> <span class="small nomargin"><?php echo $usage; ?>%</span><br/>
                                    <span class="he16-speed heicon"></span> <span class="small nomargin">
                                      
                                    <?php
                                    if($timeLeft > 0 && $pInfo->ispaused == 0){
                                        echo "$speed KB/s";
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>     
                                        
                                    </span>
                                    
                                    <?php
                                    
                                } else {
                                    if($timeLeft == 0 || $pInfo->ispaused == 1){
                                        $usage = 0;
                                        $link = '';
                                    } else {
                                        $usage = round($pInfo->cpuusage, 1);
                                        $link = 'processes#';
                                    }        
                                    ?>
                                    <span class="he16-cpu"></span> <span class="small nomargin"><?php echo $usage; ?>%</span><br/>
                                    <?php
                                }
                                ?>

                        </div>
                        <div class="span6"  style="text-align: right;">  
                            
                                <?php if($link != ''){ ?>
                                <span class="he16-cog heicon" title="Priority Manager (coming soon)"></span> 
                                <?php } ?>
                                
                                <?php if($pInfo->ispaused == 1){ ?>
                                    <a href="processes?pid=<?php echo $pInfo->pid; ?>&action=resume"><span class="he16-play heicon"></span></a>
                                <?php } else { ?>
                                    <a href="processes?pid=<?php echo $pInfo->pid; ?>&action=pause"><span class="he16-pause heicon"></span></a>
                                <?php } ?> 
                                <a href="processes?pid=<?php echo $pInfo->pid; ?>&del=1"><span class="he16-cancel heicon"></span></a><br/>
                                <span id="complete<?php echo $pInfo->pid; ?>"></span>
                                <?php                                
                                if($timeLeft == 0 && $pInfo->ispaused != 1){
                                    ?>
                                    
                                    <form action="" method="GET">

                                        <input type="hidden" name="pid" value="<?php echo $pInfo->pid; ?>">
                                        <input type="submit" class="btn btn-mini" value="<?php echo _('Complete'); ?>">

                                    </form>

                                    <?php                                    
                                }
                                
                                ?>                            
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                    <?php
                    
                    if($pInfo->ispaused == 1){
                        $scriptArr[$i]['TIME_LEFT'] = -1;
                    } else {
                        $scriptArr[$i]['TIME_LEFT'] = $timeLeft;
                    }
                    
                    $i++;
                    
                }
                                
                ?>

                </ul>            

                <?php    
                    
            } else {

                echo _('No process found');

            }

            ?>

            </div>
            <div style="clear: both;" class="nav nav-tabs"></div> 

            <?php
            
        }

    }

    public function endShowProcess($type = 0){
        
        $shown = 0;

        switch($this->pAction){
            case 11:
            case 12:
            case 15:
            case 16:
                $type = 3;
        }
        
        switch($type){
            case 0: //internet
                ?>
                        </div>
                <?php
                if($this->pLocal == 0){
                    self::showSideBar();
                    $shown = 1;
                }
            case 1: //software
                ?>
                        </div>
                <?php
                if($shown == 0){
                    self::showSideBar();
                    $shown = 1;
                }
            case 2: //processes (span12, sem sidebar)
                ?>
                    </div>
                <div style="clear:both;" class="nav nav-tabs">&nbsp;</div>            
                <?php
                break;
            case 3: //internet & action = hack/xp/portscan/bankhack
                
                ?>
                
                    </div>
                
                <?php
                
                self::showSideBar();
                
                break;
        }
    }
    
    public function showSideBar(){
        
        $btnArr = Array();
        $display = 1;
        
        switch($this->pAction){
            
            case 1:
            case 2:   
            
                $btnArr[0]['icon'] = 'root';
                $btnArr[0]['name'] = _('My files');
                $btnArr[0]['link'] = 'software';

                if(isset($_SESSION['LOGGED_IN'])){
                    $ip = long2ip($_SESSION['LOGGED_IN']);
                } else {
                    $ip = 'Victim';
                }

                $btnArr[1]['icon'] = 'remote_files';
                $btnArr[1]['name'] = sprintf(_('%s\'s files'), $ip);
                $btnArr[1]['link'] = 'internet?view=software';
                
                break;
                
            case 3:
            case 4:
            case 5:
            case 7:
            case 10:
            case 13:
            case 14:
            case 17:    
            case 22:
            case 23:    
            case 24:
            case 28:
                
                $btnArr[0]['icon'] = 'root';
                $btnArr[0]['name'] = _('My files');
                $btnArr[0]['link'] = 'software';
                
                $btnArr[1]['icon'] = 'tasks';
                $btnArr[1]['name'] = _('My tasks');
                $btnArr[1]['link'] = 'processes';                
                
                if($this->pLocal == 0){
                    
                    if(isset($_SESSION['LOGGED_IN'])){
                        $ip = long2ip($_SESSION['LOGGED_IN']);
                    } else {
                        $ip = 'Victim';
                    }

                    $btnArr[2]['icon'] = 'remote_files';
                    $btnArr[2]['name'] = sprintf(_('%s\'s files'), $ip);
                    $btnArr[2]['link'] = 'internet?view=software';
                                    
                }
                
                break;
  
            case 11:
            case 12:
            case 15:
            case 16:
                
                ?>

                    <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                    
                    </div>
                </div>     

                <?php
                
                $internet = new Internet();
                $internet->show_sideBar();
                
                $display = 0;
                
                break;

        }
        
        if($display == 1){
        
            ?>

            <div class="span3 center" style="padding-top: 8px;">

                <ul class="soft-but">

                <?php

                for($i=0;$i<sizeof($btnArr);$i++){

                    ?>


                    <li>
                        <a href="<?php echo $btnArr[$i]['link']; ?>">
                            <i class="icon-" style="background-image: none;"><span class="he32-<?php echo $btnArr[$i]['icon']; ?>"></span></i>
                            <?php echo $btnArr[$i]['name']; ?>
                        </a>
                    </li>            

                    <?php

                }

                ?>

                </ul>

            </div>

            <?php           
        
        }
        
    }
    
    public function getProcAction($action){

        switch($action){

            case '1':
                $return = 'Download';
                break;
            case '2':
                $return = 'Upload';
                break;
            case '3':
                $return = 'Delete';
                break;
            case '4':
                $return = 'Hide';
                break;
            case '5':
                $return = 'Seek';
                break;
            case '6':
                $return = 'Install';
                break;
            case '7':
                $return = 'Run Antivirus';
                break;
            case '8':
                $return = 'Edit log';
                break;
            case '10':
                $return = 'Format';
                break;
            case '11':
                $return = 'Crack server';
                break;
            case '12':
                $return = 'Crack bank acc';
                break;
            case '13':
                $return = 'Install ';
                break;
            case '14':
                $return = 'Uninstall ';
                break;
            case '15':
                $return = 'Port scan ';
                break;
            case '16':
                $return = 'Exploit ';
                break;
            case '17':
                $return = 'Research ';
                break;
            case '18':
                $return = 'Upload to external HD ';
                break;
            case '19':
                $return = 'Download from external HD ';
                break;
            case '20':
                $return = 'Delete from external HD ';
                break;
            case '21':
                $return = 'Exploit NSA with';
                break;
            case '22':
                $return = 'Nmap scan ';
                break;
            case '23':
                $return = 'Analyze ';
                break;
            case '24':
                $return = 'Doom ';
                break;
            case '25':
                $return = 'Reset IP';
                break;
            case '26':
                return 'Reset password';
            case '27':
                return 'DDoS';
            case '28':
                return 'Edit web server';
            default:
                die("errorrr");
                break;

        }

        return _($return);

    }

    public function newProcess($userID, $pAction, $victimID, $host, $pSoftID, $pInfo, $pInfoStr, $pNPC){

        if($this->player->verifyID($userID)){

            if(!$this->issetProcess($userID, $pAction, $victimID, $host, $pSoftID, $pInfo, $pInfoStr)){
                
                $numericHost = $this->getHostID($host);
                $pNumericAction = $this->pNumericAction($pAction);                
                $pInformation = $this->processDuration($pAction, $userID, $victimID, $numericHost, $pNPC, $pSoftID, $pInfo);
                
                if(strlen($pInformation['pError']) > '0'){ //se existir algum erro nos requerimentos do processDuration();

                    self::processErrorMsg($pInformation['pError']);

                    return FALSE;

                }
                
                $resInfo = self::resourceableInfo($pNumericAction);
                if($resInfo['IS_RES']){
                    $usageInfo = self::updateProcessUsage(1, '1', $resInfo['COLUMN_ACTIVE'], $pInformation['pTime']);
                    $pDuration = $pInformation['pTime'] + $usageInfo['ADDITIONAL_TIME'];
                } else {
                    $usageInfo = '0';
                    $pDuration = $pInformation['pTime'];
                }

                $this->session->newQuery();
                $sqlQuery = "INSERT INTO processes (pid, pCreatorID, pVictimID, pAction, pSoftID, pInfo, pInfoStr, pTimeStart, pTimeEnd, pTimeIdeal, pLocal, pNPC, ".$resInfo['COLUMN_ACTIVE'].", ".$resInfo['COLUMN_INACTIVE'].") 
                            VALUES ('', ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL '".$pDuration."' SECOND), ?, ?, ?, ?, '0')";
                $sqlReg = $this->pdo->prepare($sqlQuery);
                $sqlReg->execute(array($userID, $victimID, $pNumericAction, $pSoftID, $pInfo, $pInfoStr, $pInformation['pTime'], $numericHost, $pNPC, $usageInfo['COLUMN_USAGE']));

                if($sqlReg->rowCount() == '1'){

                    $pid = $this->getPID($userID, $pAction, $victimID, $host, $pSoftID, $pInfo, $pInfoStr, $pNPC);

                    $this->session->processID('add', $pid);

                    return TRUE;

                } else {

                    return FALSE;

                }

            } else { //já existe um processo idêntico

                return FALSE;

            } 

        } else {

            header("Location:logout");

        }

    }

    // 2019: Function to update my processes time when my hardware is changed
    public function updateProcessTime($id, $pcType){ //comprei hardware, entao atualizo tempo dos meus processos (e relativos)
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
        
        if($pcType == ''){
            $pcType = 'VPC';
            $isNPC = 0;
        }
                
        $this->session->newQuery();
        $sql = "SELECT pid, pAction, pSoftID, cpuUsage, netUsage, pVictimID, pLocal, pNPC, pTimeIdeal,
                TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft, TIMESTAMPDIFF(SECOND, pTimeStart, NOW()) AS pDuration
                FROM processes 
                WHERE (pCreatorID = '".$id."' OR (pVictimID = '".$id."' AND pNPC <> 1))
                HAVING pTimeLeft > 0";
        $data = $this->pdo->query($sql)->fetchAll();

        if(sizeof($data) > 0){ //com processos para alterar

            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['pnpc'] == 1){
                    $pcType = 'NPC';
                } else {
                    $pcType = 'VPC';
                }
                
                $id = $_SESSION['id'];
                if(self::mustChangeID($data[$i]['paction'], $data[$i]['plocal'])){
                    $id = $data[$i]['pvictimid'];
                }

                if($data[$i]['paction'] == 2){
                    $id = $_SESSION['id'];
                    $pcType = 'VPC';
                }
                
                if($data['0']['psoftid'] != NULL){
                    $softInfo = $this->software->getSoftware($data[$i]['psoftid'], $id, $pcType);
                } else {
                    $softInfo = '';
                }

                $pTime = floor(self::calculateDuration($data[$i]['paction'], $id, $pcType, $softInfo));

                $resInfo = self::resourceableInfo($data[$i]['paction']);

                if($resInfo['IS_RES'] == 1){
                    
                    $curUsage = $data[$i][strtolower($resInfo['COLUMN_ACTIVE'])];

                    $newTime = ($pTime*100)/$curUsage - $data[$i]['pduration'];

                    //echo "New ideal time: $pTime <br/> Cur Usage: $curUsage <br/> Possible new process time: $newTime";

                    if($data[$i]['ptimeleft'] != $newTime){
                                                
                        if($data[$i]['ptimeleft'] > $newTime){ //o processo ficou mais rápido, termina antes, date sub
                            
                            $finalTime = $data[$i]['ptimeleft'] - $newTime;
                            
                            $query = 'pTimeEnd = DATE_SUB(pTimeEnd, INTERVAL '.$finalTime.' SECOND)';
                            
                        } else { //o processo ficou mais demorado, termina depois, dateadd
                            
                            $finalTime = $newTime - $data[$i]['pduration'];
                            
                            $query = 'pTimeEnd = DATE_ADD(pTimeEnd, INTERVAL '.$finalTime.' SECOND)';
                            
                        }

                        $this->session->newQuery();
                        $sql = "UPDATE processes SET $query, pTimeIdeal = $pTime WHERE pid = ".$data[$i]['pid'];
                        $this->pdo->query($sql);
                        
                    }

                }
                
            }
        
        }

    }
    
    public function updateProcessUsage($pType, $pid, $column, $pDuration){

        if($pType == 1){ //add proc
            
            $return = Array();
            
            $this->session->newQuery();
            
            $sql =  "
                    SELECT pid, ".$column.", pAction, pSoftID, pTimeIdeal, TIMESTAMPDIFF(SECOND, pTimeStart, NOW()) AS pDuration
                    FROM processes
                    WHERE pCreatorID = :id AND ".$column." <> 0 AND TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) > 0 AND isPaused = 0
                    ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':id' => $_SESSION['id']));
            $pInfo = $stmt->fetchAll();
            
            $totalProcesses = count($pInfo);
            
            if($totalProcesses > 0){
                
                $oldUsage = 100 / $totalProcesses;
                $newUsage = 100 / ($totalProcesses + 1);
                
                for($i=0;$i<$totalProcesses;$i++){

                    $additionalTime = ($pInfo[$i]['ptimeideal']*100)/$newUsage - $pInfo[$i]['pduration'];
                    
                    $this->session->newQuery();
                    $sql = "UPDATE processes SET ".$column." = '".$newUsage."', pTimeEnd = DATE_ADD(NOW(), INTERVAL $additionalTime SECOND) 
                            WHERE pid = '".$pInfo[$i]['pid']."' LIMIT 1";

                    $this->pdo->query($sql);
                    
                }

                $return['ADDITIONAL_TIME'] = ($pDuration / ($newUsage/100)) - $pDuration;

            } else {
                
                $newUsage = 100; //100%, já que n existe outro processo do mesmo tipo
                $return['ADDITIONAL_TIME'] = 0;
                
            }
            
            $return['COLUMN_USAGE'] = $newUsage;

            return $return;
            
        } else { //delete proc
            
            //estudar qual a coluna
            if($column == ''){
                $sql = "SELECT pAction FROM processes WHERE pid = $pid";
                $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                $resourceInfo = self::resourceableInfo($data->paction);
            } else {
                $resourceInfo['COLUMN_ACTIVE'] = $column;
                $resourceInfo['IS_RES'] = 1;
            }
            
            if($resourceInfo['IS_RES'] == 1){
            
                $sql =  "
                        SELECT 
                            pid, ".$resourceInfo['COLUMN_ACTIVE'].", 
                            TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft, 
                            TIMESTAMPDIFF(SECOND, pTimeStart, NOW()) AS pDuration
                        FROM processes
                        WHERE pCreatorID = :id 
                            AND ".$resourceInfo['COLUMN_ACTIVE']." <> 0 
                            AND TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) > 0 
                            AND pid <> :pid
                            AND isPaused = 0
                        ";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':id' => $_SESSION['id'], ':pid' => $pid));
                $pInfo = $stmt->fetchAll();

                $totalProcesses = count($pInfo);

                if($totalProcesses > 0){

                    $oldUsage = 100 / ($totalProcesses + 1);
                    $newUsage = 100 / $totalProcesses;
                    
                    if($oldUsage == 100){
                        return TRUE;
                    }

                    $recID = '';
                    
                    for($i=0;$i<$totalProcesses;$i++){


			    self::getProcessInfo($pInfo[$i]['pid']);

			    if($this->pNPC == 1){
				        $pcType = 'NPC';
			    } else {
				        $pcType = 'VPC';
			    }

			    $id = $this->pCreatorID;
			    if(self::mustChangeID($this->pAction, $this->pLocal)){
				        $id = $this->pVictimID;
			    }

			    if($this->pSoftID == 0){
				        $softInfo = '';
			    } else {
				        $softInfo = $this->software->getSoftware($this->pSoftID, $id, $pcType); //pego os dados do soft
			    }

			    $idealTime = self::calculateDuration($this->pAction, $id, $pcType, $softInfo);
			    $curTime = $pInfo[$i]['pduration'] + $pInfo[$i]['ptimeleft'];

			    $subtractTime = $pInfo[$i]['ptimeleft'] - (( ceil(($pInfo[$i]['ptimeleft'] + $pInfo[$i]['pduration'])*($oldUsage/100)) * 100)/($newUsage) - ($pInfo[$i]['pduration']));

			    if($curTime - $subtractTime <= $idealTime){
				        $replace = TRUE;
					    $newTime = $idealTime - $pInfo[$i]['pduration'];
			    } else {    
				        $replace = FALSE;
					    $newTime = $subtractTime;
			    }

if($this->pAction == 27){ $replace = TRUE; $newTime = 300 - $pInfo[$i]['pduration']; }
//if($this->pAction == 27){ continue; }

			    if($subtractTime < 0){
				        
				        $recID = $pInfo[$i]['pid'];
					    $subtractTime = 0;
					    
			    }

			    if($replace){

				        $this->session->newQuery();
					    $sql = "UPDATE processes SET ".$resourceInfo['COLUMN_ACTIVE']." = '".$newUsage."', pTimeEnd = DATE_ADD(NOW(), INTERVAL $newTime SECOND)
						                WHERE pid = '".$pInfo[$i]['pid']."' LIMIT 1";
					    $this->pdo->query($sql); 

			    } else {

				        $this->session->newQuery();
					    $sql = "UPDATE processes SET ".$resourceInfo['COLUMN_ACTIVE']." = '".$newUsage."', pTimeEnd = DATE_SUB(pTimeEnd, INTERVAL $newTime SECOND) 
						                WHERE pid = '".$pInfo[$i]['pid']."' LIMIT 1";
					    $this->pdo->query($sql); 

			    }

                                                
                    }

                    if($recID != ''){

                        self::updateProcessUsage($pType, $pid, $resourceInfo['COLUMN_ACTIVE'], '');

                    }                    

                }
            
            }
            
        }
        
    }
    
    
    public function resourceableInfo($action){
        
        $return = Array();
        $return['COLUMN_ACTIVE'] = 'cpuUsage';
        $return['COLUMN_INACTIVE'] = 'netUsage';
        $return['IS_RES'] = '0';
        
        switch($action){
            
            case 1:
            case 2:
                $return['COLUMN_ACTIVE'] = 'netUsage';
                $return['COLUMN_INACTIVE'] = 'cpuUsage';
                $return['IS_RES'] = '1';
                break;
            default:
                
                if($action != 6){
                    $return['IS_RES'] = '1';
                }
                
                break;
        }
        
        return $return;
        
    }
    
    private function processErrorMsg($arrayStr){

        $pError = Array(

            'HIDDEN' => 'The software is hidden, you must seek it before.',
            'HIDDER_HIDDEN' => 'The hidder you are using is hidden!',
            'NO_HIDDER' => 'You don\'t have a hidder software.',
            'HIDING_HIDDER' => 'You are hiding your own hidder!',
            'NOT_HIDDEN' => 'The software is not hidden.',
            'BAD_SEEKER' => 'Your seeker is not good enough.',
            'SEEKER_HIDDEN' => 'Your seeker is hidden.',
            'NO_SEEKER' => 'You don\'t have any seeker.',
            'IS_INSTALLED' => 'You can\'t hide an installed software.',
            'INSUFICIENT_HD' => 'You do not have enough disk space to download this software.',
            'INSUFFICIENT_RAM' => 'You do not have free RAM enough to run this software.',

        );

        if(isset($pError[$arrayStr])){

            $this->session->addMsg("$pError[$arrayStr]", 'error');

        } else {

            $this->session->addMsg($arrayStr, 'error');

        }


    }

    private function processDuration($pAction, $uid, $victimID, $pLocal, $pNPC, $pSoftID = '', $pInfo = ''){

        $error = '';
        
        $pcType = 'VPC';
        if($pNPC == '1'){
            $pcType = 'NPC';
        }

        $id = $uid;
        if(self::mustChangeID($pAction, $pLocal)){
            $id = $victimID;
        }
        
        $this->hardware->getHardwareInfo($id, $pcType);

        $pTime = 4;
        if(is_numeric($pSoftID)){

            if($this->software->issetSoftware($pSoftID, $id, $pcType) || $pAction == 'UPLOAD' || $pAction == 'DOWNLOAD_XHD'){ //verifico se o software existe
                
                $softInfo = $this->software->getSoftware($pSoftID, $id, $pcType); //pego os dados do soft

                switch($pAction){

                    case 'DOWNLOAD': //ok

                        $hddInfo = $this->hardware->calculateHDDUsage($_SESSION['id'], 'VPC');
                        
                        if($hddInfo['TOTAL'] >= $hddInfo['USED'] + $softInfo->softsize){
                            
                            $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                            
                        } else {
                            $error = 'INSUFICIENT_HD';  
                        }
                        break;
                    case 'UPLOAD': //ok
                        
                        $hddInfo = $this->hardware->calculateHDDUsage($id, $pcType);
                        $softInfo = $this->software->getSoftware($pSoftID, $_SESSION['id'], 'VPC');
                        
                        if($hddInfo['TOTAL'] >= $hddInfo['USED'] + $softInfo->softsize){
                            
                            $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                            
                        } else {
                            $error = 'INSUFICIENT_HD';  
                        }
                        
                        break;
                    // 2019: Restrictions: must have hider; soft must not be seek; can't hide my best hider; hider can't be hidden
                    case 'HIDE': //restrições: preciso ter um hider, soft n pode estar seek, n posso hidear o melhor hider, hider n pode estar hide

                        $softBest = $this->software->getBestSoftware('5', '', '', '1');

                        if($this->software->isInstalled($pSoftID, $id, $pcType) && $softInfo->softtype != 29){
                            $error = 'IS_INSTALLED';
                        }
                        
                        if($softBest['0']['exists'] == '0'){ //n tem hidder

                            $error = 'NO_HIDDER';

                        } else {

                            if($softInfo->softhidden == '1'){ //o q ele está tentando esconder já está hide

                                $error = 'HIDDEN';

                            }

                            if($softBest['0']['softhidden'] == '1'){ //o melhor hidder tá hidden

                                $error = 'HIDDER_HIDDEN';

                            }

                            if($softBest['0']['id'] == $pSoftID && $softBest['0']['userid'] == $uid){ //ele está dando hide no hider

                                $totalValidHidder = $this->software->numberSoftware('5', $_SESSION['id'], 'valid');

                                if($totalValidHidder != '1'){

                                    $this->pSoftID = $softBest['1']['id']; //é o segundo melhor software ativo :D

                                } else {

                                    $error = 'HIDING_HIDDER';

                                }

                            }
                            
                        }
                        
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);

                        break;
                    // 2019: Restrictions: Must have skr; skr must not be hidden; soft I'm seeking MUST be hidden; skr version > hiddenwith version (Note: It's actually >=)
                    case 'SEEK': //restrições: preciso ter skr, skr não pode estar hide, soft q vou seekear TEM QUE estar hide, versão do skr > versão hiddenwith

                        $softBest = $this->software->getBestSoftware('6', '', '', '1');

                        if($softInfo->softhidden == '0'){

                            $error = 'NOT_HIDDEN';

                        }

                        if($softBest['0']['softversion'] < $softInfo->softhiddenwith){

                            $error = 'BAD_SEEKER';

                        }

                        if($softBest['0']['softhidden'] == '1'){ //o skr está hide

                            $error = 'SEEKER_HIDDEN';

                        }

                        if($softBest['0']['exists'] == '0'){ //n tem skr

                            $error = 'NO_SEEKER';

                        }

                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);

                        break;
                    case 'AV':
                        $ramInfo = $this->hardware->calculateRamUsage($id, $pcType);

                        if($ramInfo['AVAILABLE'] > $ramInfo['USED'] + $softInfo->softram){  
                            
                            $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                            
                        } else {
                            $error = 'INSUFFICIENT_RAM';
                        }
                        
                        break;
                    case 'DELETE':
                        // 2019: Must not delete software that is currently hidden;
                        //restrições:não pode deletar software que está escondido
                        if($softInfo->softhidden == '1'){
                            $error = 'HIDDEN';
                        }

                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);

                        break;
                    case 'INSTALL':
                        
                        $ramInfo = $this->hardware->calculateRamUsage($id, $pcType);
                        
                        if($ramInfo['AVAILABLE'] - $softInfo->softram >= 0){
                            
                            if(!$this->software->isVirus($softInfo->softtype)){
                                $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                            } else {
                                
                                if($victimID != ''){
                                    $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                                } else {
                                    $error = 'CANT_INSTALL_VIRUS_YOURSELF';
                                }
                                
                            }
                            
                        } else {
                            $error = 'INSUFFICIENT_RAM';
                        }

                        break;
                    case 'UNINSTALL':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'RESEARCH':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'UPLOAD_XHD':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'DOWNLOAD_XHD':
                        if($this->software->issetExternalSoftware($pSoftID)){
                            $softInfo = $this->software->getExternalSoftware($pSoftID);
                            $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        } else {
                            $error = 'INEXISTENT_SOFTWARE';
                        }
                        break;
                    case 'DELETE_XHD':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'NMAP':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'ANALYZE':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    case 'INSTALL_DOOM':
                        
                        $hddInfo = $this->hardware->calculateHDDUsage($_SESSION['id'], 'VPC');
                        
                        if($hddInfo['AVAILABLE'] >= 500){
                            
                            $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                            
                        } else {
                            $error = 'The installed doom virus requires at least 500 MB of free disk space.';  
                        }
                        break;
                    case 'INSTALL_WEBSERVER':
                        $pTime = self::calculateDuration($pAction, $id, $pcType, $softInfo);
                        break;
                    
                }

            }

        } else {

            //action sem softid.. ex:format, av..
            switch($pAction){

                case 'FORMAT':

                    $pTime = self::calculateDuration($pAction, $id, $pcType, '');

                    break;
                case 'E_LOG':
                    
                    $pTime = self::calculateDuration($pAction, $id, $pcType, $pInfo);

                    break;
                case 'BANK_HACK':
                case 'HACK': //restrições: aqui nada, só no completeProcess
                    // 2019: No restrictions here; but we do have some at completeProcess
                    $pTime = self::calculateDuration($pAction, $id, $pcType, '');

                    break;
                case 'PORT_SCAN':

                    $pTime = '4';

                    break;
                case 'HACK_XP':

                    $pTime = '22';

                    break;
                case 'RESET_IP':
                    
                    $pTime = 600;
                    
                    break;
                case 'RESET_PWD':
                    
                    $pTime = 60;
                    
                    break;
                case 'DDOS':
                    
                    $pTime = 300;
                    
                    break;
                default:
                    die("erererrrrae");
                    break;
            }

        }
        
        //$pTime = 4;
        //$pTime = 0;
        
        $returnArray = Array(

            'pTime' => $pTime,
            'pError' => $error,

        );

        return $returnArray;

    }

    private function pNumericAction($pAction){

        require 'config.php';

        return $processActions[$pAction];

    }

    public function getProcessInfo($pid){
        
	$this->session->newQuery();
        $sqlSelect = "SELECT COUNT(*) AS total, pcreatorid, pvictimid, paction, psoftid, pinfo, pinfostr, cpuUsage, netUsage, plocal, pnpc, TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft FROM processes WHERE pid = $pid LIMIT 1";
        $pInfo =  $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);
        
        if($pInfo->total == 0){
            $this->session->addMsg('Some error happened. Please try again.', 'error');
            header("Location:index.php");
            exit();
        }
        
        $this->pID = $pid;
        $this->pCreatorID = $pInfo->pcreatorid;
        $this->pVictimID = $pInfo->pvictimid;
        $this->pAction = $pInfo->paction;
        $this->pSoftID = $pInfo->psoftid;
        $this->pLocal = $pInfo->plocal;
        $this->pInfo = $pInfo->pinfo;
        $this->pInfoStr = $pInfo->pinfostr;
        $this->pNPC = $pInfo->pnpc;
        $this->cpuUsage = $pInfo->cpuusage;
        $this->netUsage = $pInfo->netusage;

        if($pInfo->ptimeleft < 0){

            $this->pTimeLeft = '0';

        } else {
            
            $this->pTimeLeft = $pInfo->ptimeleft;
            
        }

    }

    private function mb2kb($softSize){

        return round($softSize*1024);

    }

    private function getHostID($host){

        $return = '0';
        if($host == 'local'){ 
            $return = '1';     
        }

        return $return;

    }

    // 2019: Kids, don't do this
    public function currentTimeToSec($date, $hour){
//DEPRECATED
        if(strlen($date) == '0' && strlen($hour) == '0'){
            
            date_default_timezone_set('America/Chicago');
            $date = date("Y-m-d");
            $hour = date("H:i:s");

        }

        $anoSec = (substr($date, 0, 4)) * 31536000;
        $mesSec = (substr($date, 5, 2)) * 2592200; //precisa verificar se o mes tem 30 ou 31 dias;
        $diaSec = (substr($date, 8, 2)) * 86400;
        $horSec = (substr($hour, 0, 2)) * 3600;
        $minSec = (substr($hour, 3, 2)) * 60;
        $secSec = (substr($hour, 6, 2));

        return $secondsNow = $anoSec + $mesSec + $diaSec + $horSec + $minSec + $secSec;

    }

    public function issetPID($pid){

        if(is_numeric($pid)){

            $this->session->newQuery();
            $sqlSelect = "SELECT pcreatorid FROM processes WHERE pid = $pid LIMIT 1";
            $pInfo = $this->pdo->query($sqlSelect)->fetchAll();

            if(count($pInfo) == '1'){

                return TRUE;

            }

        }
        
        return FALSE;

    }
    
    public function issetProcess($userID, $pAction, $victimID, $host, $pSoftID, $pInfo){

        if($this->player->verifyID($userID)){

            $host = $this->getHostID($host);
            $pNumericAction = $this->pNumericAction($pAction);

            $opArray = Array(':pCreatorID' => $userID, ':pVictimID' => $victimID, ':pAction' => $pNumericAction, ':pSoftID' => $pSoftID, ':pLocal' => $host);
            
            $sql = 'SELECT pid 
                    FROM processes 
                    WHERE 
                        pCreatorID = :pCreatorID AND
                        pVictimID = :pVictimID AND
                        pAction = :pAction AND
                        pSoftID = :pSoftID AND
                        pLocal = :pLocal';
            
            if($pAction != 'RESET_IP' && $pAction != 'RESET_PWD' && $pAction != 'E_LOG'){
                $sql .= ' AND pInfo = :pInfo';
                $opArray[':pInfo'] = $pInfo;
            }
            
            $sql .= ' LIMIT 1';
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($opArray);
            $data = $stmt->fetchAll();
            
            if(sizeof($data) == '1'){

                return TRUE;

            } else {

                return FALSE;

            }

        }

    }

    public function getPID($userID, $pAction, $victimID, $host, $pSoftID, $pInfo, $pInfoStr, $pNPC){

        if($this->player->verifyID($userID) == TRUE || $this->npc->issetNPC($userID)){

            $host = $this->getHostID($host);
            $pNumericAction = $this->pNumericAction($pAction);

            $opArray = Array(':pCreatorID' => $userID, ':pVictimID' => $victimID, ':pAction' => $pNumericAction, ':pSoftID' => $pSoftID, ':pInfoStr' => $pInfoStr, ':pLocal' => $host, ':pNPC' => $pNPC);
            
            $sql = 'SELECT pid 
                    FROM processes 
                    WHERE 
                        pCreatorID = :pCreatorID AND
                        pVictimID = :pVictimID AND
                        pAction = :pAction AND
                        pSoftID = :pSoftID AND
                        pInfoStr = :pInfoStr AND
                        pLocal = :pLocal AND
                        pNPC = :pNPC';
            
            if($pAction != 'RESET_IP' && $pAction != 'RESET_PWD'){
                $sql .= ' AND pInfo = :pInfo';
                $opArray[':pInfo'] = $pInfo;
            }
            
            $sql .= ' LIMIT 1';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($opArray);
            $data = $stmt->fetchAll();
   
            if(sizeof($data) == '1'){

                $this->pID = $data['0']['pid'];
                return $data['0']['pid'];

            }

        }

    }

    public function showProcess(){

        $redirect = self::studyRedirect();

        if($this->pLocal == '1'){
            $ip = 'localhost';
        } else {
            if($this->session->isInternetLogged()){
                $ip = long2ip($_SESSION['LOGGED_IN']);
            } else {
                $ip = 'remote host';
            }
        }

        if($this->pNPC == '0'){ //é vpc
            $pcType = 'VPC';
        } else {
            $pcType = 'NPC';
        }

        $done = '0';
        if($this->pTimeLeft == '0'){
            $done = '1';
        }

        ?>
            
        <ul class="list">
            <li>
                <div class="span4">
                    <div class="proc-desc">
            
        <?php    
        
        $softInfo = $this->software->getSoftware($this->pSoftID, $_SESSION['id'], $pcType);

        if($softInfo == NULL || $this->pAction == 22){ //não existe psoftid, só pinfo

            if($this->pVictimID != '0'){ //é um processo via internet

                if($this->pNPC == '0'){ //é vpc

                    $pVictimInfo = $this->software->getPlayerInfo($this->pVictimID);

                } else {

                    $pVictimInfo = $this->npc->getNPCInfo($this->pVictimID);

                }

            }

            echo $this->getProcAction($this->pAction) . _(' at '); if($this->pLocal == '1'){ echo ' localhost'; } else { if($this->pNPC == '0') echo long2ip($pVictimInfo->gameip); else echo long2ip($pVictimInfo->npcip); }

        } else { //existe psoftid

            echo $this->getProcAction($this->pAction) . _(' file ').'<b>' . $softInfo->softname . '</b> ('. $this->software->dotVersion($softInfo->softversion) .') '._('on').' '.$ip;

        }

        ?>
             
                    </div>
                </div>

                <div class="span5">
                        
        <?php                
        
        $isPaused = 0;
        if(self::isPaused($this->pID)){
            $isPaused = 1;
        }
        
        if($isPaused == 0){
        
            if($done == 1){

                self::completeProcess($this->pID);
                header("Location:$redirect");
                exit();

            } else {

                $_SESSION['pLoad'] = $this->pTimeLeft;
                $_SESSION['pLoadID'] = $this->pID;
                
                ?>
                
                <div id="process0">
                    <div class="percent"></div>
                    <div class="pbar"></div>
                    <div class="elapsed"></div>
                </div>                
                
                <?php

            }
        
        } else {
            echo 'Process paused';
            $_SESSION['pLoad'] = 'p';
        }
        
        ?>
                
                </div>
                <div class="span3 proc-action">
                    <div class="span6">    
                
        <?php

        if($this->pAction < 3){
            if($isPaused == 1){
                $usage = 0;
                $link = '';
            } else {
                $usage = round($this->netUsage, 1);
                $link = 'processes#';
            }            

            ?>

            <span class="he16-net heicon"></span> <span class="small nomargin"><?php echo $usage; ?>%</span><br/>
            <span class="he16-speed heicon"></span> <span class="small nomargin">

            <?php
            if($isPaused == 0){
                $speed = self::getDownloadSpeed($this->pVictimID, $this->pNPC, $this->pAction, $this->netUsage);
                echo "$speed KB/s";
            } else {
                echo 'N/A';
            }
            ?>     

            </span>

            <?php

        } else {
            if($isPaused == 1){
                $usage = 0;
                $link = '';
            } else {
                $usage = round($this->cpuUsage, 1);
                $link = 'processes#';
            }        
            ?>
            <span class="he16-cpu"></span> <span class="small nomargin"><?php echo $usage; ?>%</span><br/>
            <?php
        }
        ?>
            
                    </div>
                    <div class="span6"  style="text-align: right;">  

                <?php if($link != ''){ ?>
                <span class="he16-cog heicon"></span> <!-- Usar a variável $link para o priority, se for '' não precisa mudar, se for diferente mostra o modal -->   
                <?php } ?>

                <?php if($isPaused == 1){ ?>
                    <a href="processes?pid=<?php echo $this->pID; ?>&action=resume"><span class="he16-play heicon"></span></a>
                <?php } else { ?>
                    <a href="processes?pid=<?php echo $this->pID; ?>&action=pause"><span class="he16-pause heicon"></span></a>
                <?php } ?> 
                <a href="processes?pid=<?php echo $this->pID; ?>&del=1"><span class="he16-cancel heicon"></span></a><br/>
                          
                    </div>
                </div>
                <div style="clear:both;"></div>

            </li>
        </ul>

        <?php
        
        
    }

    public function mustChangeID($pAction, $pLocal){

        if(!is_numeric($pAction)){
            $action = self::pNumericAction($pAction);
        } else {
            $action = $pAction;
        }

        if(!is_numeric($pLocal)){
            if($pLocal == 'remote'){
                $pLocal = '0';
            } else {
                $pLocal = '1';
            }
        }
                
        switch($action){

            case '1':
                return TRUE;
                break;
            case '2':
                return TRUE;
                break;
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '11':
            case '12':    
            case '13':
            case '14':
            case '15':    
            case '16':
            case '21':  
            case '22':
            case '23':
            case '24':    
            case '27':
            case '28':    
                if($pLocal == '0'){
                    return TRUE;
                }
                break;
            default:
                return FALSE;
            break;

        }

    }

    public function completeProcess($pid){

        // 2019: This is the function that is executed when a process finishes. It also includes all the side effects.
        // 2019: So there's a good chance it's the most important function of the game, as well as the one with the most bugs

        $log = new LogVPC();

        //verifica se já acabou
        self::getProcessInfo($pid);

        $id = $this->pCreatorID;
        if(self::mustChangeID($this->pAction, $this->pLocal)){
            $id = $this->pVictimID;
        }

        $pcType = 'VPC';
        if($this->pNPC == '1'){
            $pcType = 'NPC';
        }

        if($this->pSoftID != '0' && $this->software->issetSoftware($this->pSoftID, $id, $pcType) == TRUE){

            $issetSoft = 1;
            
            $softInfo = $this->software->getSoftware($this->pSoftID, $id, $pcType);
            $softVersion = $this->software->dotVersion($softInfo->softversion);

        } else {
            $issetSoft = 0;
        }

        if($this->pLocal == '0'){

            $redirect = self::studyRedirect();
            if($pcType == 'VPC'){
                $victimInfo = $this->player->getPlayerInfo($this->pVictimID);
                $victimIP = $victimInfo->gameip;
            } else {
                $victimInfo = $this->npc->getNPCInfo($this->pVictimID);
                $victimIP = $victimInfo->npcip;
            }

            $invalid = '0';
            if($this->session->isInternetLogged()){

                if(($victimIP != $_SESSION['LOGGED_IN'] || $_SESSION['LOGGED_IN'] != $_SESSION['CUR_IP']) && ($this->pAction != '27')){
                    $invalid = '1';
                }

            } elseif($this->pAction != '11' && $this->pAction != '16' && $this->pAction != '27') { //nao sendo hack ou hack_xp ou ddos...
                $invalid = '1';
            }
            
            if($this->pAction == '12' || $this->pAction == '15'){
                $invalid = '0';
            }
            
            if($invalid == '1'){

                $this->session->addMsg(sprintf(_('You must be logged in at %s to complete this process!'), long2ip($victimIP)), 'error');
                header("Location:$redirect");
                exit();

            }

        }
        
        if($this->pSoftID != 0 && $issetSoft == 0 && $this->pAction != 2 && $this->pAction != 19 && $this->pAction != 20){
            self::deleteProcess($pid);
            $this->system->handleError('This software does not exists anymore. The task was deleted.', self::studyRedirect());
        }
        
        $playerInfo = $this->player->getPlayerInfo($this->pCreatorID);

        require_once '/var/www/classes/Mission.class.php'; 

        $this->mission = new Mission();
        
        if($this->pTimeLeft == '0'){

            $error = '';
            
            switch($this->pAction){

                case '1': //download

                    //antes de tudo, verifico se o software existeeeeeeee
                    if($issetSoft == 1){

                        if($softInfo->softtype == 19 || $softInfo->softtype == 31 || $softInfo->softtype >= 90){
                            $this->system->handleError('You can not download this software.', 'internet');
                        }
                        
                        $this->session->newQuery();
                        $sqlSelect = "SELECT id FROM software WHERE userId = '".$_SESSION['id']."' AND softType = $softInfo->softtype AND softVersion = $softInfo->softversion AND isNPC = 0 AND softName = '".$softInfo->softname."' AND softHidden = 0 LIMIT 1";
                        $rowsReturned = $this->pdo->query($sqlSelect)->fetchAll();

                        if(count($rowsReturned) != '1'){
						
                            $hddInfo = $this->hardware->calculateHDDUsage($_SESSION['id'], 'VPC');
                            if($hddInfo['TOTAL'] >= $hddInfo['USED'] + $softInfo->softsize){
            
                                $this->session->newQuery();
                                $sqlQuery = "INSERT INTO software (id, softHidden, softHiddenWith, softLastEdit, softName, softSize, softType, softVersion, userID, isNPC, originalFrom, licensedTo) VALUES ('', '0', '0', NOW(), ?, ?, ?, ?, ?, '0', ?, ?)";
                                $sqlDown = $this->pdo->prepare($sqlQuery);
                                $sqlDown->execute(array($softInfo->softname, $softInfo->softsize, $softInfo->softtype, $softInfo->softversion, $_SESSION['id'], $this->pSoftID, $softInfo->licensedto));
                                $softInsertID = $this->pdo->lastInsertId();
                                
                                if($softInfo->softtype == 1 && $softInfo->softversion >= 9200){
                                    
                                    $valid = TRUE;
                                    
                                    if($this->session->issetMissionSession()){
                                        if($_SESSION['MISSION_TYPE'] != 52 && $_SESSION['MISSION_TYPE'] != 51){
                                            $valid = FALSE;
                                        }
                                    }
                                    
                                    if($valid){
                                    
                                        require_once '/var/www/classes/Mission.class.php';
                                        $mission = new Mission();                                    

                                        if($this->session->issetMissionSession()){
                                            $mission->deleteMission();
                                        }

                                        $this->virus->doom_sendMission(3, $softInsertID);

                                        $mission->restoreMissionSession($_SESSION['id']);

                                    }
                                    
                                }
                                
                                if($softInfo->softtype == 29){

                                    require_once '/var/www/classes/Mission.class.php';
                                    $mission = new Mission();                                    
                                    
                                    if($this->session->issetMissionSession()){
                                        $mission->deleteMission();
                                    }
                                    
                                    $this->virus->doom_sendMission(2, $softInsertID);
                                    
                                    $mission->restoreMissionSession($_SESSION['id']);
                                    
                                }
                                
                                if($softInfo->softtype == 30){

                                    $this->session->newQuery();                                    
                                    $sql = "SELECT text FROM software_texts WHERE id = '".$this->pSoftID."'";
                                    $oldTextInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                                    
                                    $this->session->newQuery();
                                    $sql = "INSERT INTO software_texts (id, userID, isNPC, text, lastEdit)
                                            VALUES ('".$softInsertID."', '".$_SESSION['id']."', '0', '".$oldTextInfo->text."', NOW())";
                                    $this->pdo->query($sql);
                                    
                                }
                                
                                if($this->session->issetMissionSession()){
                                    if($this->mission->issetMission($_SESSION['MISSION_ID'])){
                                        
                                        if(($_SESSION['MISSION_TYPE'] == '2')){
                         
                                            $pertinentID = $this->mission->getMissionPertinentSoftID($this->pVictimID, $this->mission->missionInfo($_SESSION['MISSION_ID']));                                            
                                            
                                            if($pertinentID == $this->pSoftID){
                                                $this->mission->updateInfo($softInsertID, $_SESSION['MISSION_ID']);
                                            }
                                            
                                        } elseif(($_SESSION['MISSION_TYPE'] == 50) || ($_SESSION['MISSION_TYPE'] == 51) || ($_SESSION['MISSION_TYPE'] == 52) || ($_SESSION['MISSION_TYPE'] == 53) || ($_SESSION['MISSION_TYPE'] == 54)){

                                            if($this->mission->missionInfo($_SESSION['MISSION_ID']) == $this->pSoftID){
                                                $this->mission->updateInfo($softInsertID, $_SESSION['MISSION_ID']);
                                            }
                  
                                        }

                                    } else {
                                        $this->session->deleteMissionSession();
                                    }
                                }

                                $paramHacked = Array(1, long2ip($playerInfo->gameip), $softInfo->softname, $this->software->getExtension($softInfo->softtype), $softVersion);
                                $paramHacker = Array(2, long2ip($playerInfo->gameip), $softInfo->softname, $this->software->getExtension($softInfo->softtype), $softVersion);

                                $log->addLog($this->pVictimID, $log->logText('DOWNLOAD', $paramHacked), $this->pNPC);
                                $log->addLog($this->pCreatorID, $log->logText('DOWNLOAD', $paramHacker), '0');

                                $this->session->addMsg('Software successfully downloaded.', 'notice');
                                
                                $this->session->exp_add('ACTIONS', Array('net', $softInfo->softsize));

                            } else {
                                $this->system->handleError('DOWNLOAD_INSUFFICIENT_HD', 'internet');
                            }

                        } else {
                            $this->system->handleError('SOFT_ALREADY_HAVE', 'internet');
                        }

                    } else {
                        $this->system->handleError('INEXISTENT_SOFTWARE');
                    }

                    break;
                case '2': //upload

                    if($this->software->issetSoftware($this->pSoftID, $_SESSION['id'], 'VPC')){
                    
                        $softInfo = $this->software->getSoftware($this->pSoftID, $_SESSION['id'], 'VPC');
                        $softVersion = $this->software->dotVersion($softInfo->softversion);

                        if($softInfo->softtype == 19 || $softInfo->softtype == 31 || $softInfo->softtype >= 90){
                            $this->system->handleError('This software can not be uploaded.', 'software');
                        }
                        
                        if($softInfo->softhidden == '0'){
	
                            $this->session->newQuery();
                            $sqlSelect = "SELECT id FROM software WHERE userId = '".$this->pVictimID."' AND softType = $softInfo->softtype AND softVersion = $softInfo->softversion AND isNPC = $this->pNPC AND softName = '".$softInfo->softname."' LIMIT 1";
                            $sqlQuery = "INSERT INTO software (id, softHidden, softHiddenWith, softLastEdit, softName, softSize, softType, softVersion, userID, isNPC, originalFrom, licensedTo) VALUES ('', '0', '0', NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $rowsReturned = $this->pdo->query($sqlSelect)->fetchAll();

                            if(count($rowsReturned) != '1'){

                                $hddInfo = $this->hardware->calculateHDDUsage($id, $pcType);
                                if($hddInfo['TOTAL'] >= $hddInfo['USED'] + $softInfo->softsize){

                                    $this->session->newQuery();
                                    $sqlDown = $this->pdo->prepare($sqlQuery);
                                    $sqlDown->execute(array($softInfo->softname, $softInfo->softsize, $softInfo->softtype, $softInfo->softversion, $this->pVictimID, $this->pNPC, $this->pSoftID, $softInfo->licensedto));
                                    $softInsertID = $this->pdo->lastInsertId();
                                    
                                    if($softInfo->softtype == 1 && $softInfo->softversion >= 9200 && $this->pNPC == 0){
                                    
                                        require_once '/var/www/classes/Mission.class.php';
                                        $mission = new Mission();                                    

                                        
                                        if($mission->playerOnMission($this->pVictimID)){
                                            $missionID = $mission->getPlayerMissionID($this->pVictimID);
                                            $missionType = $mission->missionType($missionID);

                                            if($missionType < 49){
                                                $mission->deleteMission($missionID);
                                            }
                                        }

                                        $this->virus->doom_sendMission(5, $softInsertID);

                                    }

                                    if($softInfo->softtype == 29){
                                        
                                        if($this->pNPC == 0){ //upando em um VPC
                                        
                                            require_once '/var/www/classes/Mission.class.php';
                                            $mission = new Mission();                                             
                                            
                                            if($mission->playerOnMission($this->pVictimID)){
                                                $missionID = $mission->getPlayerMissionID($this->pVictimID);
                                                $missionType = $mission->missionType($missionID);
                                                
                                                if($missionType < 49 || ($missionType == 54 || $missionType == 52 || $missionType == 50)){
                                                    $mission->deleteMission($missionID);
                                                }
                                            }
                                            
                                            $this->virus->doom_sendMission(4, $softInsertID);
                                        
                                        } else { //upando em NPC
                                            
                                            //para atualizar a MINHA missao.
                                            if($this->session->issetMissionSession()){
                                                
                                                if($_SESSION['MISSION_TYPE'] > 49){
                                                    
                                                    if($this->mission->issetMission($_SESSION['MISSION_ID'])){

                                                        require '/var/www/classes/Clan.class.php';
                                                        $clan = new Clan();

                                                        if($clan->playerHaveClan()){
                                                            if($clan->getClanInfo($clan->getPlayerClan())->clanip == $_SESSION['LOGGED_IN']){
                                                                $this->mission->updateInfo2($softInsertID, $_SESSION['MISSION_ID']);
                                                            }
                                                        }
                                                        
                                                    } else {
                                                        $this->session->deleteMissionSession();
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }
                                        
                                    }
                                    
                                    if($softInfo->softtype == 30){

                                        $this->session->newQuery();                                    
                                        $sql = "SELECT text FROM software_texts WHERE id = '".$this->pSoftID."'";
                                        $oldTextInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                                        $this->session->newQuery();
                                        $sql = "INSERT INTO software_texts (id, userID, isNPC, text, lastEdit)
                                                VALUES ('".$softInsertID."', '".$this->pVictimID."', '".$this->pNPC."', '".$oldTextInfo->text."', NOW())";
                                        $this->pdo->query($sql);

                                    }
                                    
                                    $paramHacked = Array(1, long2ip($playerInfo->gameip), $softInfo->softname, $this->software->getExtension($softInfo->softtype), $softVersion);
                                    $paramHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $softInfo->softname, $this->software->getExtension($softInfo->softtype), $softVersion);

                                    $log->addLog($this->pVictimID, $log->logText('UPLOAD', $paramHacked), $this->pNPC);
                                    $log->addLog($this->pCreatorID, $log->logText('UPLOAD', $paramHacker), '0');

                                    $this->session->addMsg('Software successfully uploaded.', 'notice');
                                    $this->session->exp_add('ACTIONS', Array('net', $softInfo->softsize));
     
                                    if($this->session->issetMissionSession()){
                                        if($this->mission->issetMission($_SESSION['MISSION_ID'])){

                                            if($_SESSION['MISSION_TYPE'] == '2'){

                                                if($this->mission->missionNewInfo($_SESSION['MISSION_ID']) == $this->pSoftID){
                                                    $this->mission->completeMission($_SESSION['MISSION_ID']);
                                                    $this->session->addMsg('Software uploaded. Mission completed!', 'mission');
                                                }

                                            } elseif($_SESSION['MISSION_TYPE'] == 83){
                                                
                                                if($this->mission->missionInfo($_SESSION['MISSION_ID']) == $this->pSoftID){
                                                    $this->mission->updateInfo($softInsertID, $_SESSION['MISSION_ID']);
                                                }
                                                
                                            }

                                        } else {
                                            $this->session->deleteMissionSession();
                                        }

                                    }

                                } else {
                                    $this->system->handleError('UPLOAD_INSUFFICIENT_HD', 'internet');
                                }

                            } else {
                                $this->system->handleError('UPLOAD_SOFT_ALREADY_HAVE', 'software');
                            }

                        } else {
                            $this->system->handleError('SOFT_HIDDEN');
                        }
                        
                    } else {
                        $this->system->handleError('INEXISTENT_SOFTWARE');
                    }

                    break;
                case '3':  //delete

                    if($issetSoft == 1){

                        $nameStr = $softInfo->softname.$this->software->getExtension($softInfo->softtype);

                        if($softInfo->softtype != 30){
                            $nameStr .= ' ('.$softVersion.')';
                        } else {
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM software_texts WHERE id = '".$this->pSoftID."' LIMIT 1";
                            $this->pdo->query($sql);
                            
                            if(isset($_SESSION['TEXT'])){
                                unset($_SESSION['TEXT']);
                            }
                            
                        }

                        if(!$this->software->isVirusInstalled($softInfo->softtype) && $softInfo->softtype != 29 && $softInfo->softtype != 19){
                        
                            if($this->pLocal == '1'){ //deleta em localhost

                                if($this->session->issetMissionSession()){
                                    if($_SESSION['MISSION_TYPE'] == 83){
                                        if($this->mission->missionInfo($_SESSION['MISSION_ID']) == $this->pSoftID || $softInfo->softtype == 1){
                                            $this->system->handleError('You can not delete this software during the tutorial mission.');
                                            return;
                                        }
                                    } elseif($_SESSION['MISSION_TYPE'] >= 80 && $_SESSION['MISSION_TYPE'] < 83){
                                        if($softInfo->softtype == 1){
                                            $this->system->handleError('You can not delete this software during the tutorial mission.');
                                            return;
                                        }
                                    }
                                }
                                
                                $uid = $_SESSION['id'];
                                $npc = 0;

                                $param = Array(0, $nameStr);
                                
                                $log->addLog($this->pCreatorID, $log->logText('DELETE', $param), '0');
                                
                                $this->session->addMsg('Software successfully deleted.', 'notice');
                                $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                            } else {//delete remoto

                                $uid = $this->pVictimID;
                                $npc = $this->pNPC;
                                
                                require_once '/var/www/classes/Storyline.class.php';
                                $storyline = new Storyline();
  
                                $odds = (5 + (($softInfo->softversion)/10))*10;
                                $rand = rand(1,100)*10;
                        
                                if($npc == 0 && ($softInfo->softtype <= 2 || $softInfo->softtype == 4) && $softInfo->softversion == 10){
                                    $this->system->handleError('Cant delete basic softwares on real users.', 'internet');
                                    return; 
                                }
                                
                                if($uid == 59 && $npc == 1){
                                    $this->system->handleError('Cant delete softwares at the download center', 'internet');
                                    return; 
                                }

                                $crackerSoft = $this->software->getBestSoftware(1, $_SESSION['id'], 'VPC');
                                $victimSoft = $this->software->getBestSoftware(1, $this->pVictimID, $this->pNPC);
                                
                                $attackerVersion = 0;
                                $victimVersion = 0;
                                
                                if($crackerSoft['0']['exists'] == 1){
                                    $attackerVersion = $crackerSoft['0']['softversion'];
                                }
                                
                                if($victimSoft['0']['exists'] == 1){
                                    $victimVersion = $victimSoft['0']['softversion'];
                                }

                                $invalid = false;
                                if($victimVersion == 0){
                                    $invalid = true;
                                }
                                
                                if($victimVersion <= 20){
                                    if(($victimVersion + 10) < $attackerVersion){
                                        $invalid = true;
                                    }
                                } else {
                                    if(($victimVersion * 2) < $attackerVersion){
                                        $invalid = TRUE;
                                    }
                                }
                                
                                if($npc == 1){
                                    $invalid = false;
                                }

                                if($invalid){
                                    $this->system->handleError('Cant delete softwares on victim because you are much stronger than her.', 'internet');
                                    return;
                                }

                                if($rand <= $odds){
                                    
                                    $playerInfo = $this->player->getPlayerInfo($_SESSION['id']);
                                    
                                    if(!$storyline->safenet_isset($playerInfo->gameip, 4)){
                                        $storyline->safenet_add($playerInfo->gameip, 4, '');
                                    } else {
                                        $storyline->fbi_add($playerInfo->gameip, 4, '');
                                        $storyline->safenet_update($playerInfo->gameip, 4, '');
                                    }
                                    
                                }
                                
                                $paramHacked = Array(1, $nameStr, long2ip($playerInfo->gameip));
                                $paramHacker = Array(2, $nameStr, long2ip($_SESSION['LOGGED_IN']));
                                
                                $log->addLog($this->pVictimID, $log->logText('DELETE', $paramHacked), $this->pNPC);
                                $log->addLog($this->pCreatorID, $log->logText('DELETE', $paramHacker), '0');

                                $this->session->addMsg('Software successfully deleted.', 'notice');
                                $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize));

                                if($this->session->issetMissionSession()){

                                    if($this->mission->issetMission($_SESSION['MISSION_ID'])){

                                        if($_SESSION['MISSION_TYPE'] == '1'){

                                            $pertinentID = $this->mission->getMissionPertinentSoftID($this->pVictimID, $this->mission->missionInfo($_SESSION['MISSION_ID']));                                            

                                            if($pertinentID == $this->pSoftID){

                                                $this->mission->completeMission($_SESSION['MISSION_ID']);
                                                $this->session->addMsg('Software deleted. Mission completed!', 'mission');
  
                                            }

                                        }

                                    } else {
                                        $this->session->deleteMissionSession();
                                    }

                                }

                            }

                            if($this->pNPC == 1){
                                $pcType = 'NPC';
                            } else {
                                $pcType = 'VPC';
                            }
                            
                            if($this->software->isInstalled($this->pSoftID, $uid, $pcType)){
                                
                                if($softInfo->softtype == 18){

                                    require_once '/var/www/classes/Internet.class.php';
                                    $internet = new Internet();
                                    
                                    $internet->webserver_shutdown($uid);
                                    
                                }
                                
                                $this->session->newQuery();
                                $sql = "DELETE FROM software_running WHERE softID = '".$this->pSoftID."' LIMIT 1"; 
                                $this->pdo->query($sql);                                

                            }
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM software WHERE userID = '".$uid."'  AND id = '".$this->pSoftID."' AND isNPC = '".$npc."' LIMIT 1";
                            $this->pdo->query($sql);

                        } else {
                            $this->system->handleError('CANT_DELETE');
                        }
                        
                    } else {
                        $this->system->handleError('SOFTWARE_INEXISTENT');
                    }
                    
                    break;
                case '4': //hide

                    if(!$this->software->isInstalled($this->pSoftID, $id, $pcType) || $softInfo->softtype == 29){
                        
                        if($this->pLocal == 1){
                            $redirect = 'software';
                        } else {
                            $redirect = 'internet';
                        }
                        
                        if($softInfo->softtype == 31){
                            $this->system->handleError('You cant hide a folder.', $redirect);
                        }
                        
                        $softName = $softInfo->softname.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';
                        
                        if($this->pLocal == '1'){ //hide em localhost

                            $bestSoft = $this->software->getBestSoftware('5', '', '', '1');

                            if(self::isReflexiveProcess('5')){
                                $hdrVersion = $bestSoft['1']['softversion'];
                            } else {
                                $hdrVersion = $bestSoft['0']['softversion'];
                            }

                            $this->session->newQuery();
                            $sql = "UPDATE software SET softHidden = 1, softHiddenWith = $hdrVersion, softLastEdit = NOW() WHERE id = $this->pSoftID AND userID = $this->pCreatorID AND isNPC = '0' LIMIT 1";
                            $this->pdo->query($sql);

                            $param = Array(0, $softName);
                            //$logText = "localhost hid file $softInfo->softname (" . $softVersion . ") at localhost";
                            $log->addLog($this->pCreatorID, $log->logText('HIDE', $param), '0');

                            $this->session->addMsg('Software successfully hidden.', 'notice');
                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                        } else {//hide remoto

                            $bestSoft = $this->software->getBestSoftware('5', '', '', '1');
                            $hdrVersion = $bestSoft['0']['softversion'];

                            $this->session->newQuery();
                            $sql = "UPDATE software SET softHidden = 1, softHiddenWith = $hdrVersion, softLastEdit = NOW() WHERE id = $this->pSoftID AND userID = $this->pVictimID AND isNPC = $this->pNPC LIMIT 1";
                            $this->pdo->query($sql);

                            $paramHacked = Array(1, $softName, long2ip($playerInfo->gameip));
                            $paramHacker = Array(2, $softName, long2ip($_SESSION['LOGGED_IN']));
                            
                            $log->addLog($this->pVictimID, $log->logText('HIDE', $paramHacked), $this->pNPC);
                            $log->addLog($this->pCreatorID, $log->logText('HIDE', $paramHacker), '0');

                            $this->session->addMsg('Software successfully hidden.', 'notice');
                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize));
                            
                        }
                    
                    } else {
                        $this->system->handleError('HIDE_INSTALLED_SOFTWARE');
                    }
                    
                    break;
                case '5': //seek

                    $bestSoft = $this->software->getBestSoftware('6', '', '', '1');

                    if($bestSoft['0']['exists'] == '1'){ //verifica se eu tenho seeker (as vezes deletaram no meiotempo)

                        $softName = $softInfo->softname.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';
                        
                        if($this->pLocal == '1'){ //seek em localhost

                            $this->session->newQuery();
                            $sql = "UPDATE software SET softHidden = 0, softHiddenWith = 0 WHERE id = $this->pSoftID AND userID = $this->pCreatorID AND isNPC = 0 LIMIT 1";
                            $this->pdo->query($sql);

                            $param = Array(0, $softName);
                            
                            $log->addLog($this->pCreatorID, $log->logText('SEEK', $param), '0');
                            $this->session->addMsg('Software successfully seeked.', 'notice');
                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                        } else {//seek remoto
                            
                            $this->session->newQuery();
                            $sql = "UPDATE software SET softHidden = 0, softHiddenWith = 0 WHERE id = $this->pSoftID AND userID = $this->pVictimID AND isNPC = $this->pNPC LIMIT 1";
                            $this->pdo->query($sql);

                            $paramHacked = Array(1, $softName, long2ip($playerInfo->gameip));
                            $paramHacker = Array(2, $softName, long2ip($_SESSION['LOGGED_IN']));
                            
                            $log->addLog($this->pVictimID, $log->logText('SEEK', $paramHacked), $this->pNPC);
                            $log->addLog($this->pCreatorID, $log->logText('SEEK', $paramHacker), '0');

                            $this->session->addMsg('Software successfully seeked.', 'notice');
                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize));

                        }

                    } else {

                        $this->system->handleError('NO_SEEKER');

                        if($this->pLocal == '1'){
                            header("Location:software");
                        } else {
                            header("Location:internet?view=software");
                        }
                        exit();


                    }
                    break;
                case '6': //collect
                    die("To do");
                    break;
                case '7': //antivirus
                    
                    if($this->pLocal == 1){
                        
                        $victimID = $_SESSION['id'];
                        $pcType = 'VPC';
                        $isNPC = 0;
                        $ip = $playerInfo->gameip;
                        
                    } else {
                        
                        $victimID = $this->pVictimID;
                        $isNPC = $this->pNPC;
                        $ip = $_SESSION['LOGGED_IN'];
                                                
                    }
                    
                    $avInfo = $this->software->getSoftware($this->pSoftID, $victimID, $pcType);
                    
                    $this->session->newQuery();
                    $sql = "SELECT id, softVersion, softname, originalFrom FROM software WHERE userID = '".$victimID."' AND softType > 95 AND isNPC = '".$isNPC."'";
                    $data = $this->pdo->query($sql);

                    $total = '0';
                    while($virusInfo = $data->fetch(PDO::FETCH_OBJ)){

                        if($virusInfo->softversion <= $avInfo->softversion){

                            $this->session->newQuery();
                            $sql = "SELECT active FROM virus WHERE virusID = '".$virusInfo->id."' LIMIT 1";
                            $activeVirus = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->active;                            
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM virus WHERE virusID = $virusInfo->id AND installedIp = $ip LIMIT 1";
                            $this->pdo->query($sql);
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM software WHERE id = $virusInfo->id AND isNPC = '".$isNPC."'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "UPDATE lists SET virusID = 0 WHERE virusID = '".$virusInfo->id."'";
                            $this->pdo->query($sql);
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM virus_ddos WHERE ddosID = '".$virusInfo->id."'";
                            $this->pdo->query($sql);
                            
                            $total++;

                            if($activeVirus == 1){
                            
                                $this->list->addNotification($virusInfo->originalfrom, $ip, 3, $virusInfo->softname.' ('.$this->software->dotVersion($virusInfo->softversion).')');

                            }
                                                        
                        }

                    }
                    
                    $softName = $softInfo->softname.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';
                    
                    if($this->pLocal == '1'){

                        $param = Array(0, $softName, $total);
                        
                        $log->addLog($_SESSION['id'], $log->logText('AV', $param), '0');
                        
                        $this->session->exp_add('AV', Array('local', $total));
                        
                    } else {
                        
                        $paramHacked = Array(1, $softName, $total, long2ip($playerInfo->gameip));
                        $paramHacker = Array(2, $softName, $total, long2ip($ip));

                        $log->addLog($this->pVictimID, $log->logText('AV', $paramHacked), $this->pNPC);
                        $log->addLog($_SESSION['id'], $log->logText('AV', $paramHacker), '0');                        
                        
                        $this->session->exp_add('AV', Array('remote', $total));
                        
                    }

                    $this->session->addMsg(sprintf(ngettext('%d virus removed', '%d viruses removed', $total), $total), 'notice');
                    
                    break;
                case '8': //edit log

                    if($this->pLocal == '1'){ //edit em localhost
                        $vid = $this->pCreatorID;
                    } else { //edit remoto
                        $vid = $this->pVictimID;
                    }
                    
                    if($this->pInfo != ''){

                        $log = new LogVPC();
                        
                        $newLog = $log->getTmpLog($this->pInfo);
                        
                        $log->deleteTmpLog($this->pInfo);
                                                
                    } else {
                        $newLog = '';
                    }
                    
                    $this->session->newQuery();
                    $sql = "UPDATE log
                            SET text = :newLog
                            WHERE userID = :uid AND isNPC = :npc
                            LIMIT 1";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute(array(':newLog' => $newLog, ':uid' => $vid, ':npc' => $this->pNPC));

                    $this->session->addMsg('Log successfully edited.', 'notice');
                    $this->session->exp_add('EDIT_LOG', Array($this->pLocal));
                    
                    break;
                case '10': //format
                    break;
                case '11': //hack bf
                    
                    $error = '';

                    if($this->pNPC == '1'){
                        
                        $pcType = 'NPC';
                        $npcInfo = $this->npc->getNPCInfo($this->pVictimID);
                        
                        $ip = $npcInfo->npcip;
                        $pass = $npcInfo->npcpass;
                        
                    } else {
                        
                        $pcType = 'VPC';
                        $playerInfo = $this->player->getPlayerInfo($this->pVictimID);
                        $ip = $playerInfo->gameip;
                        $pass = $playerInfo->gamepass;
                        
                    }

                    $unknownPass = 0;
                    $exploitedPass = 0;
                    $downloadPass = 0;
                    if($this->list->isListed($_SESSION['id'], $ip)){
                        if($this->list->isUnknown($_SESSION['id'], $ip)){
                            $unknownPass = 1;
                        } elseif($this->list->isExploited($_SESSION['id'], $ip)) {
                            $exploitedPass = 1;
                        } else {
                            $error = 'ALREADY_LISTED';
                        }
                    }
                    
                    if($pcType == 'NPC'){
                        if($npcInfo->npctype == 8){
                            if($error == 'ALREADY_LISTED'){
                                if($this->list->isDownload($_SESSION['id'], $ip)){
                                    $error = '';
                                    $downloadPass = 1;
                                }
                            }
                        }
                    }
                    
                    $softInfoHacker = $this->software->getBestSoftware('1', '', '', '1');
                    $softInfoHacked = $this->software->getBestSoftware('2', $this->pVictimID, $pcType, '1');

                    if($softInfoHacker['0']['exists'] == '0'){
                        $error = 'NO_SOFT';
                    }

                    if($softInfoHacked['0']['exists'] == '0'){
                        $softInfoHacked['0']['softversion'] = '0';
                    }

                    if(strlen($error) == '0'){ //n tem erro, continuamos

                        if($softInfoHacker['0']['softversion'] >= $softInfoHacked['0']['softversion']){

                            $_SESSION['HACKED'] = $ip;
                            $_SESSION['METHOD'] = 'bf';
                            $_SESSION['USER'] = 'ROOT';
                            $_SESSION['CHMOD'] = '';

                            if($downloadPass == 1){
                                $this->list->deleteList($this->list->getListIDByIP($ip), 1);
                            }
                            
                            if($unknownPass == 1 || $exploitedPass == 1){
                                
                                $this->list->revealUnknownPassword($_SESSION['id'], $ip, 'root', $pass);
                                
                            } else {
                                
                                $this->list->addToList($_SESSION['id'], $ip, 'root', $pass, '0', '0');
                                
                            }
                            
                            $victimCracker = $this->software->getBestSoftware('1', $this->pVictimID, $pcType, '0');
                            
                            if($victimCracker['0']['exists'] == 0){
                                $victimCracker['0']['softversion'] = 0;
                            }
                            
                            $this->session->exp_add('HACK', Array('bf', $softInfoHacked['0']['softversion'] + $victimCracker['0']['softversion']));

                            $this->session->addMsg(sprintf(_('Successfully cracked %s. Password is <strong>%s</strong>.'), long2ip($ip), $pass), 'notice');
                            
                        } else {

                            $this->session->addMsg('Your cracker isnt good enough.', 'error');

                        }

                    } else {

                        $this->system->handleError($error);
                            
                    }

                    break;
                case '12': //bank acc hack
                    
                    $error = '';
                    
                    if($this->finances->issetBankAccount($this->pInfo, $this->pVictimID)){

                        $hackedID = $this->finances->getIDByBankAccount($this->pInfo, $this->pVictimID);

                        $softInfoHacker = $this->software->getBestSoftware('1', '', '', '1');
                        $softInfoHacked = $this->software->getBestSoftware('2', $this->pVictimID, 'NPC', '1');

                        if($softInfoHacker['0']['exists'] == '0'){
                            $error = 'NO_SOFT';
                        }

                        if($softInfoHacked['0']['exists'] == '0'){
                            $softInfoHacked['0']['softversion'] = '0';
                        }

                        if($softInfoHacker['0']['softversion'] < $softInfoHacked['0']['softversion']){
                            $error = 'BAH_BAD_CRACKER';
                        }
                        
                        if($this->list->bank_isListed($_SESSION['id'], $this->pInfo)){
                            $error = 'BAH_ALREADY_CRACKED';
                        }
                        
                    } else {
                        $error = 'BAD_ACC';
                    }
                    
                    if(strlen($error)=='0'){

                        $accInfo = $this->finances->getBankAccountInfo($hackedID, $this->pVictimID, $this->pInfo);

                        $this->session->addMsg(sprintf(_('Bank account hacked! Password is <strong>%s</strong>. %sLogin!%s'), $accInfo['BANK_PASS'], '<a href="internet?action=login&type=bank">', '</a>'), 'notice');
                        
                        $_SESSION['HACKED_ACC_NUMBER'] = $accInfo['BANK_ACC'];
                        $_SESSION['HACKED_ACC_PASS'] = $accInfo['BANK_PASS'];
                        $_SESSION['HACKED_ACC_BANK'] = $this->pVictimID;
                        
                        $this->list->bank_addToList($this->pVictimID, $accInfo['BANK_ACC'], $accInfo['BANK_PASS']);
                        
                        $this->session->exp_add('HACK', Array('bank', $softInfoHacked['0']['softversion']));
                        
                    } else {
                        $this->system->handleError($error);
                    }
                    
                    break;
                case '13': //install software

                    $id = $this->pVictimID;
                    if($id == '0'){
                        $id = $_SESSION['id'];
                    }
                    
                    $pcType = 'VPC';
                    if($this->pNPC == '1'){
                        $pcType = 'NPC';
                    }
                    
                    $error = '';
                    
                    if(!$this->software->isInstalled($this->pSoftID, $id, $pcType)){
                        
                        if($this->software->isExecutable($softInfo->softtype, $this->pSoftID, $this->pLocal)){

                            if($softInfo->softhidden != '1'){

                                $ramInfo = $this->hardware->calculateRamUsage($id, $pcType);
                                
                                if($ramInfo['AVAILABLE'] - $softInfo->softram >= 0){

                                    $softName = $softInfo->softname.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';
                                    
                                    if(!$this->software->isVirus($softInfo->softtype)){
                                        
                                        if($this->pLocal == '1'){

                                            $param = Array(0, $softName);
                                            
                                            //$logText = "localhost installed file $softInfo->softname (" . $softVersion . ")";
                                            $log->addLog($id, $log->logText('INSTALL', $param), '0');
                                            
                                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                                        } else {

                                            $paramHacked = Array(1, $softName, long2ip($playerInfo->gameip));
                                            $paramHacker = Array(2, $softName, long2ip($_SESSION['LOGGED_IN']));
                                            
                                            $log->addLog($id, $log->logText('INSTALL', $paramHacked), $this->pNPC);
                                            $log->addLog($_SESSION['id'], $log->logText('INSTALL', $paramHacker), '0');
                                            
                                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize));

                                        }

                                        $this->session->newQuery();
                                        $sqlQuery = "INSERT INTO software_running (id, softID, userID, ramUsage, isNPC) VALUES ('', ?, ?, ?, ?)";
                                        $sqlReg = $this->pdo->prepare($sqlQuery);
                                        $sqlReg->execute(array($this->pSoftID, $id, $softInfo->softram, $this->pNPC));

                                    } else { //ta instalando virus

                                        switch($softInfo->softtype){
                                            case '8':
                                                $virusType = '1';
                                                $softType = '99';
                                                break;
                                            case '9':
                                                $virusType = '2';
                                                $softType = '98';
                                                break;
                                            case '10':
                                                $virusType = '3';
                                                $softType = '97';
                                                break;
                                            case '20':
                                                $virusType = '4';
                                                $softType = '96';
                                                break;
                                            default:
                                                die("INVALID VIRUS");
                                                break;
                                        }

                                        if(!$this->virus->alreadyInstalled($_SESSION['LOGGED_IN'], $virusType)){
                                        
                                            if($this->session->issetMissionSession()){
                                                if($_SESSION['MISSION_TYPE'] == 83){
                                                    if($this->mission->missionNewInfo($_SESSION['MISSION_ID']) == $this->pSoftID){
                                                        
                                                        $this->mission->completeMission($_SESSION['MISSION_ID']);
                                                        $this->session->addMsg('Virus installed. Mission completed!', 'mission');
                                                        $this->mission->tutorial_update(84);
                                                        
                                                    }
                                                }
                                            }

                                            if($this->virus->alreadyInstalled($_SESSION['LOGGED_IN'], '')){
                                                
                                                $this->session->newQuery();
                                                $sql = "UPDATE virus 
                                                        SET active = 0 
                                                        WHERE installedBy = '".$_SESSION['id']."' AND installedIp = '".$_SESSION['LOGGED_IN']."' LIMIT 1";
                                                $this->pdo->query($sql);
                                                
                                            }
                                            
                                            $this->session->newQuery();
                                            $sqlQuery = "INSERT INTO virus (installedIp, installedBy, virusID, virusVersion, originalID, virusType, lastCollect, active) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";
                                            $sqlReg = $this->pdo->prepare($sqlQuery);
                                            $sqlReg->execute(array($_SESSION['LOGGED_IN'], $_SESSION['id'], $this->pSoftID, $softInfo->softversion, $softInfo->originalfrom, $virusType));

                                            $this->session->newQuery();
                                            $sql = "UPDATE software SET softType = $softType, originalFrom = '".$_SESSION['id']."' WHERE userID = $id AND id = $this->pSoftID AND isNPC = $this->pNPC LIMIT 1";
                                            $this->pdo->query($sql);

                                            $this->session->newQuery();
                                            $sql = "UPDATE lists SET virusID = $this->pSoftID WHERE userID = ".$_SESSION['id']." AND ip = ".$_SESSION['LOGGED_IN']." LIMIT 1";
                                            $this->pdo->query($sql);

                                            if($virusType == 3){ //é ddos, adiciono no virus_ddos

                                                $hardware = $this->hardware->getHardwareInfo($id, $pcType);
                                                
                                                $this->session->newQuery();
                                                $sql = "INSERT INTO virus_ddos (userID, ip, ddosID, ddosName, ddosVersion, cpu, active)
                                                        VALUES ('".$_SESSION['id']."', '".$_SESSION['LOGGED_IN']."', '".$this->pSoftID."', '".$softInfo->softname."', '".$softInfo->softversion."', '".$hardware['CPU']."', 1)";
                                                $this->pdo->query($sql);
                                                
                                            }
                                            
                                            $paramHacked = Array(3, $softName, long2ip($playerInfo->gameip));
                                            $paramHacker = Array(4, $softName, long2ip($_SESSION['LOGGED_IN']));
                                            
                                            $log->addLog($id, $log->logText('INSTALL', $paramHacked), $this->pNPC);
                                            $log->addLog($_SESSION['id'], $log->logText('INSTALL', $paramHacker), '0');
                                            
                                            $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize * 2));

                                        } else {
                                            $error = 'VIRUS_ALREADY_INSTALLED';
                                        }

                                    }
                                    
                                    $this->session->addMsg('Software installed.', 'notice');

                                } else {
                                    $error = 'INSUFFICIENT_RAM';
                                }
                            
                            } else {
                                $error = 'SOFT_HIDDEN';
                            }
                        
                        } else {
                            $error = 'NOT_EXECUTABLE';
                        }
                        
                    } else {
                        $error = 'ALREADY_INSTALLED';
                    }

                    if(strlen($error) > '0'){
                        $this->system->handleError($error);
                    }                    
                    
                    break;
                case '14': //uninstall software
                    
                    $id = $this->pVictimID;
                    if($id == '0'){
                        $id = $_SESSION['id'];
                    }
                    
                    $pcType = 'VPC';
                    if($this->pNPC == '1'){
                        $pcType = 'NPC';
                    }
                    
                    $error = '';
                    
                    $softInfo = $this->software->getSoftware($this->pSoftID, $id, $pcType);
                    if(!$this->software->isVirus($softInfo->softtype)){
                        
                        if($this->software->isInstalled($this->pSoftID, $id, $pcType)){
                            
                            if($this->software->isExecutable($softInfo->softtype, $this->pSoftID, $this->pLocal)){
                                
                                if($softInfo->softhidden!='1'){

                                    $softName = $softInfo->softname.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';
                                    
                                    if($this->pLocal == '1'){

                                        $param = Array(0, $softName);
                                        
                                        $log->addLog($id, $log->logText('UNINSTALL', $param), '0');
                                        
                                        $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                                    } else {

                                        $paramHacked = Array(1, $softName, long2ip($playerInfo->gameip));
                                        $paramHacker = Array(2, $softName, long2ip($_SESSION['LOGGED_IN']));
                                        
                                        $log->addLog($id, $log->logText('UNINSTALL', $paramHacked), $this->pNPC);
                                        $log->addLog($_SESSION['id'], $log->logText('UNINSTALL', $paramHacker), '0');   
                                        
                                        $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize));

                                    }

                                    $this->session->newQuery();
                                    $sqlQuery = "DELETE FROM software_running WHERE softID = ? AND userID = ? AND ramUsage = ? AND isNPC = ?";
                                    $sqlReg = $this->pdo->prepare($sqlQuery);
                                    $sqlReg->execute(array($this->pSoftID, $id, $softInfo->softram, $this->pNPC));
                                    
                                    if($softInfo->softtype == 18){
                                        
                                        require_once '/var/www/classes/Internet.class.php';
                                        $internet = new Internet();
                                        
                                        $internet->webserver_shutdown($id);
                                        
                                    }
                                    
                                    $this->session->addMsg('Software uninstalled.', 'notice');
                                    
                                    
                                } else {
                                    $error = 'SOFT_HIDDEN';
                                }
                                
                            } else {
                                $error = 'NOT_EXECUTABLE';
                            }
                            
                        } else {
                            $error = 'NOT_INSTALLED';
                        }
                        
                    } else {
                        $error = 'CANT_UINSTALL';
                    }
                    
                    if(strlen($error) > '0'){
                        $this->system->handleError($error);
                    }

                    break;
                case '15': //portscan
                    
                    $scanInfo = $this->software->getBestSoftware('3', '', 1);
                    
                    if($scanInfo['0']['exists'] == '1'){
                        
                        if($this->pNPC == '0'){
                            $victimIP = $this->player->getPlayerInfo($this->pVictimID)->gameip;
                        } else {
                            $victimIP = $this->npc->getNPCInfo($this->pVictimID)->npcip;
                        }
                        
                        $_SESSION['PORT_SCAN'] = $victimIP;
                        
                    } else {
                        $error = 'NO_SOFT';
                    }
                    
                    if(strlen($error) > '0'){
                        $this->system->handleError($error);
                    }
                    
                    break;
                case '16': //hack xp

                    $error = '';
                    
                    if($this->pNPC == '0'){
                        $victimIP = $this->player->getPlayerInfo($this->pVictimID)->gameip;
                    } else {
                        $victimIP = $this->npc->getNPCInfo($this->pVictimID)->npcip;
                    }
                        
                    $previouslyExploited = 0;
                    $unknownPass = 0;
                    if($this->list->isListed($_SESSION['id'], $victimIP)){
                        if(!$this->list->isUnknown($_SESSION['id'], $victimIP)){
                            if(!$this->list->isExploited($_SESSION['id'], $victimIP)){
                                $error = 'ALREADY_LISTED';
                            } else {
                                $previouslyExploited = 1;
                                $infoUser = $this->list->getListedLoginInfo($_SESSION['id'], $victimIP);
                                $previousUser = $infoUser['0']['user'];
                                if($previousUser == 'root'){
                                    $error = 'ALREADY_LISTED';
                                }
                            }
                        } else {
                            $unknownPass = 1;
                        }
                    }                 
                    
                    $softInfoHacker = $this->software->getBestSoftware('3', '', '', '1'); //portscanner
                    $softInfoHacked = $this->software->getBestSoftware('4', $id, $pcType, '1');

                    if($softInfoHacked['0']['exists'] == '0'){
                        $softInfoHacked['0']['softversion'] = '0';
                    }
                    
                    if($softInfoHacker['0']['exists'] == '0'){
                        $error = 'HXP_NO_SCAN';
                    }

                    $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                    $sshExp = $this->software->getBestSoftware('14', '', '', '1');

                    $expInfo = '0'; //0 = nenhum, 1 = ftp, 2 = ssh, 3 = os dois

                    if($ftpExp['0']['exists'] == '1'){
                        if($sshExp['0']['exists'] == '1'){
                            $expInfo = '3';
                        } else {
                            $expInfo = '1';
                        }
                    } else {
                        if($sshExp['0']['exists'] == '1'){
                            $expInfo = '2';
                        }
                    }

                    
                    
                    if($expInfo == '0'){
                        $error = 'HXP_NO_EXP';
                    }
                    
                    $hackable = '0';
                    $ftpCanHack = '0';
                    if($expInfo == '1' || $expInfo == '3'){
                        if($ftpExp['0']['softversion'] >= $softInfoHacked['0']['softversion']){
                            $ftpCanHack = '1';
                            $hackable = '1';
                        }
                    }

                    $sshCanHack = '0';
                    if($expInfo == '2' || $expInfo == '3'){
                        if($sshExp['0']['softversion'] >= $softInfoHacked['0']['softversion']){
                            $sshCanHack = '1';
                            $hackable = '1';
                        }
                    }
                                        
                    if($sshCanHack == '0' && $ftpCanHack == '0'){
                        $error = 'HXP_BAD_XP';
                    }
                    
                    $permission = '';
                    if($ftpCanHack == '1'){
                        $permission .= 'ldu'; //l = log, d = download, u = upload
                        $user = 'ftp';
                    }
                    if($sshCanHack == '1'){
                        $permission .= 'ls'; //s = soft = hide, seek, run, delete...
                        $user = 'ssh';
                    }
                    
                    if($ftpCanHack == '1' && $sshCanHack == '1'){
                        $user = 'root';
                    }                    
                                        
                    if($previouslyExploited == 1){
                        if($user != $previousUser){
                            $this->list->deleteList($this->list->getListIDByIP($victimIP), 1);
                        } else {
                            $error = 'ALREADY_LISTED';
                        }
                    }

                    if($hackable == '1' && $error == ''){
                        
                        $_SESSION['HACKED'] = $victimIP;
                        $_SESSION['METHOD'] = 'xp';
                        $_SESSION['PERMISSION'] = $permission;
                        $_SESSION['USER'] = $user;

                        $victimCracker = $this->software->getBestSoftware('1', $id, $pcType, '0');

                        if($victimCracker['0']['exists'] == 0){
                            $victimCracker['0']['softversion'] = 0;
                        }
  
                        $this->session->exp_add('HACK', Array('xp', $softInfoHacked['0']['softversion'] + $victimCracker['0']['softversion']));

                        if($unknownPass == 0){
                        
                            $this->list->addToList($_SESSION['id'], $victimIP, $user, 'exploited', '0', '0');
                            
                        } else {
                            
                            $this->list->revealUnknownPassword($_SESSION['id'], $victimIP, $user, 'exploited');
                            
                        }
                        
                        $this->session->addMsg(sprintf(_('Successfully exploited %s. You have <strong>%s</strong> permissions.'), long2ip($victimIP), $user), 'notice');
                        
                    }
                    
                    if(strlen($error) > '0'){
                        $this->system->handleError($error);
                    } 

                    $_SESSION['CUR_IP'] = $victimIP;
                    
                    unset($_SESSION['PORT_SCAN']);
                    
                    
                    break;
                    
                case '17': //research
                    
                    $error = '';
                    
                    $name = $this->pInfo;

                    list($acc, $remove) = explode("/", $this->pInfoStr);
                    
                    $price = $this->software->research_calculatePrice($softInfo->softversion, $softInfo->softtype);
                    
                    if($this->finances->totalMoney() >= $price){
                    
                        if($softInfo->softhidden == 0){

                            $invalid = 2;
                            if($softInfo->licensedto == $_SESSION['id']){
                                $invalid -= 1;
                            } else {
                                $error = 'NO_LICENSE';
                            }
                            
                            $accInfo = $this->finances->bankAccountInfo($acc);

                            if($accInfo['0']['exists'] == 0){
                                $error = 'Invalid bank account';
                            } elseif($accInfo['0']['bankuser'] != $_SESSION['id']){
                                $error = 'Invalid bank account';
                            } else {
                                $invalid -= 1;
                            }
                            
                            if($invalid == 0){
                            
                                $this->software->research($this->pSoftID, $name, $remove);
                                $this->finances->debtMoney($price, $acc);

                                $this->session->exp_add('RESEARCH', Array($softInfo->softtype, $softInfo->softversion+1));
                                
                                $this->session->addMsg('Software researched.', 'notice');

                                $this->ranking->updateMoneyStats('2', $price);

                                $softInfo->softversion++;
                                
                                $doomStartVersion = 9200;
                                if($softInfo->softversion >= $doomStartVersion && $softInfo->softtype == '1'){

                                    $mission = new Mission();
                                    
                                    if($this->session->issetMissionSession()){
                                        if($_SESSION['MISSION_TYPE'] != 50 && $_SESSION['MISSION_TYPE'] != 51 && $_SESSION['MISSION_TYPE'] != 53){
                                            $mission->deleteMission();
                                        }
                                    }
                                    
                                    $this->virus->doom_sendMission(1);
                                    
                                    $mission->restoreMissionSession($_SESSION['id']);
                                    
                                }
                                
                                $newSoftName = $name.$this->software->getExtension($softInfo->softtype).' ('.$this->software->dotVersion($softInfo->softversion).')';

                                $param = Array(0, $price, $acc, long2ip($this->finances->getBankIP($accInfo['0']['bankid'])), $newSoftName);
                                
                                $log->addLog($_SESSION['id'], $log->logText('RESEARCH', $param), 0);
                                
                            }

                        } else {
                            $error = 'SOFT_HIDDEN';
                        }
                    
                    } else {
                        $error = 'BAD_MONEY';
                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }

                    break;
                    
                case '18': //upload xhd
                    
                    $error = '';
                    
                    $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');

                    if($this->software->issetSoftware($this->pSoftID, $_SESSION['id'], 'VPC')){

                        $this->session->newQuery();
                        $sqlSelect = "SELECT id FROM software_external WHERE userId = '".$_SESSION['id']."' AND softType = $softInfo->softtype AND softVersion = $softInfo->softversion AND softName = '".$softInfo->softname."' LIMIT 1";
                        $rowsReturned = $this->pdo->query($sqlSelect)->fetchAll();

                        if(count($rowsReturned) != '1'){
                                                      
                            $softInfo = $this->software->getSoftware($this->pSoftID, $_SESSION['id'], 'VPC');

                            if($this->hardware->xhd >= $this->hardware->getXHDUsage() + $softInfo->softsize){

                                $this->session->newQuery();
                                $sql = "INSERT INTO software_external (id, userID, softName, softVersion, softSize, softRam, softType, uploadDate, licensedTo)
                                        VALUES ('".$this->pSoftID."', '".$_SESSION['id']."', '".$softInfo->softname."', '".$softInfo->softversion."', '".$softInfo->softsize."', '".$softInfo->softram."', '".$softInfo->softtype."', NOW(), '".$softInfo->licensedto."')";
                                $this->pdo->query($sql);

                                $this->session->addMsg('Software uploaded to external HD.', 'notice');

                                $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                            } else {
                                $error = 'BAD_XHD';
                            }

                        } else {
                            $error = 'This software is already on your external hard drive.';  
                        }
                        
                    } else {
                        $error = 'INEXISTENT_SOFTWARE';                        
                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }
                    
                    break;
                    
                case '19': //download xhd
                    
                    $error = '';
                    
                    if(!$this->software->issetSoftware($this->pSoftID, $_SESSION['id'], 'VPC')){

                        if($this->software->issetExternalSoftware($this->pSoftID)){

                            $softInfo = $this->software->getExternalSoftware($this->pSoftID);
                            $hddUsage = $this->hardware->calculateHDDUsage($_SESSION['id'], 'VPC');

                            if($hddUsage['AVAILABLE'] >= $softInfo->softsize){

                                $this->session->newQuery();
                                $sqlSelect = "SELECT id FROM software WHERE userId = '".$_SESSION['id']."' AND isNPC = 0 AND softType = $softInfo->softtype AND softVersion = $softInfo->softversion AND softName = '".$softInfo->softname."' AND softHidden = 0 LIMIT 1";
                                $rowsReturned = $this->pdo->query($sqlSelect)->fetchAll();

                                if(count($rowsReturned) != '1'){
                                    
                                    $this->session->newQuery();

                                    $sql = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, isNPC, licensedTo)
                                            VALUES ('', '".$_SESSION['id']."', '".$softInfo->softname."', '".$softInfo->softversion."', '".$softInfo->softsize."',
                                            '".$softInfo->softram."', '".$softInfo->softtype."', NOW(), '0', '".$softInfo->licensedto."')";
                                    $this->pdo->query($sql);

                                    $this->session->addMsg('Software downloaded from external HD.', 'notice');
                                    $this->session->exp_add('ACTIONS', Array('cpu', $softInfo->softsize / 2));

                                } else {
                                    $error = 'This software already exists on your root folder.';  
                                }

                                    
                            } else {
                                $error = 'BAD_HDD';
                            }

                        } else {
                            $error = 'INEXISTENT_SOFTWARE';
                        }

                    } else {
                        $error = 'SOFT_ALREADY_HAVE';
                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }
                    
                    break;
                    
                case '20': //delete xhd

                    $error = '';
                    
                    if($this->software->issetExternalSoftware($this->pSoftID)){

                        $this->session->newQuery();
                        $sql = "DELETE FROM software_external WHERE id = '".$this->pSoftID."' LIMIT 1";
                        $this->pdo->query($sql);

                        $this->session->addMsg('Software deleted.', 'notice');
                        
                        $this->session->exp_add('ACTIONS', Array('cpu', 10));

                    } else {
                        $error = 'NO_HAVE_SOFT';
                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }

                    
                    break;
                case '21': //exploit storyline

                    //deprecated, eu acho
                    die("Not working for now");
                    
                    break;
                case '22': //NMAP
                    
                    $error = '';
                    
                    if($this->pLocal == 1){

                        $bestNmap = $this->software->getBestSoftware(15, $_SESSION['id'], 'VPC');
                                                
                        if($bestNmap['0']['exists'] == 1){
                                                        
                            $this->session->newQuery();
                            $sql = "SELECT userID, TIMESTAMPDIFF(SECOND, NOW(), expires) AS connectionTime FROM internet_connections WHERE ip = ".$playerInfo->gameip." ORDER BY connectionTime DESC";
                            $data = $this->pdo->query($sql)->fetchAll();

                        } else {
                            $error = 'NO_SOFT';
                        }
                        
                    } else {

                        $bestNmap = $this->software->getBestSoftware(15, $this->pVictimID, $pcType);
                        
                        if($bestNmap['0']['exists'] == 1){
                            
                            $this->session->newQuery();
                            $sql = "SELECT userID, TIMESTAMPDIFF(SECOND, NOW(), expires) AS connectionTime FROM internet_connections WHERE ip = ".$victimIP." ORDER BY connectionTime DESC";
                            $data = $this->pdo->query($sql)->fetchAll();
                            
                        } else {
                            $error = 'NO_NMAP_VICTIM';
                        }
 
                    }
                    
                    if($error == ''){

                        $txt = '';
                        $total = 0;
                        
                        if(sizeof($data) > 0){

                            $total = 0;
                            for($i=0;$i<sizeof($data);$i++){

                                $fwlInfo = $this->software->getBestSoftware('4', $data[$i]['userid'], 'VPC', 1);

                                if($bestNmap['0']['softversion'] >= $fwlInfo['0']['softversion']){

                                    $userInfo = $this->player->getPlayerInfo($data[$i]['userid']);

                                    $connectionTime = abs($data[$i]['connectiontime']);

                                    if($connectionTime < 60){
                                        $time = $connectionTime;
                                        $str = ' seconds';
                                    } else {
                                        $time = round($connectionTime / 60, 0);
                                        $str = ' minutes';
                                    }

                                    $txt .= 'IP ['.long2ip($userInfo->gameip).'] connected on the last '.$time.$str."\n";
                                    $total++;

                                }

                            }

                            if($total == 0){
                                $txt .= 'No users connected';
                            }

                        } else {
                            $txt .= 'No users connected';
                        }                    

                        if($this->pLocal == 1){

                            $uid = $_SESSION['id'];
                            $isNPC = 0;
                            
                            $host = 'localhost';
                            $logTxt = 'localhost scanned network and found '.$total.' connected IPs';

                        } else {

                            $host = long2ip($victimIP);
                            $logTxt = '['.long2ip($playerInfo->gameip).'] scanned localhost and found '.$total.' connected IPs';
                            $uid = $this->pVictimID;
                            $isNPC = $this->pNPC;

                            $log->addLog($uid, $logTxt, $isNPC);

                            $logTxt = 'localhost scanned ['.long2ip($playerInfo->gameip).'] and found '.$total.' connected IPs';

                        }

                        $name = 'NMAP_'.$host;

                        $this->software->text_add($name, $txt, $uid, $isNPC, false);

                        $log->addLog($_SESSION['id'], $logTxt, 0);
                        
                        $this->session->exp_add('NMAP', Array($total));
                        $this->session->addMsg(sprintf(ngettext('Network scanned; %d connected IP found.', 'Network scanned; %d connected IPs found.', $total), $total), 'notice');
                        
                    } else {
                        $this->system->handleError($error);
                    }
                    
                    break;
                case '23': //ANALYZE

                    switch($softInfo->softversion){
                    
                        case 10:
                            $accuracy = 0.5; //50%
                            break;
                        case 20:
                            $accuracy = 0.8; //80%
                            break;
                        case 30:
                            $accuracy = 1.0; //100%
                            break;
                        default:
                            die("Invalid ID!");
                            break;
                    
                    }
                    
                    $victimHardware = $this->hardware->getHardwareInfo($id, $pcType);
                                                           
                    $rangeCPU = $victimHardware['CPU'] * (1 - $accuracy);
                    $rangeRAM = $victimHardware['RAM'] * (1 - $accuracy);
                    
                    $cpuMin = round($victimHardware['CPU'] - $rangeCPU + ($rangeCPU / rand(5,50)));
                    $ramMin = round($victimHardware['RAM'] - $rangeRAM + ($rangeRAM / rand(5,50)));
                    
                    if($accuracy != 1.0){
                        $cpuMax = round($victimHardware['CPU'] + $rangeCPU - ($rangeCPU / rand(5,50)));
                        $ramMax = round($victimHardware['RAM'] + $rangeRAM - ($rangeRAM / rand(5,50)));
                    } else {
                        $cpuMin = $cpuMax = $victimHardware['CPU'];
                        $ramMin = $ramMax = $victimHardware['RAM'];
                    }

                    if($this->list->isListed($_SESSION['id'], $victimIP)){

                        $this->list->updateListedHardware($this->list->getListIDByIP($victimIP), Array($cpuMin, $cpuMax, $ramMin, $ramMax));
                        
                        $this->session->addMsg('Hardware listed on your <a href="list">hacked database</a>.', 'notice');
                        
                        $softName = $softInfo->softname.'.ana ('.$this->software->dotVersion($softInfo->softversion).')';
                        
                        $paramHacked = Array(1, long2ip($playerInfo->gameip), $softName);
                        $paramHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $softName);
                        
                        $log->addLog($id, $log->logText('ANALYZE', $paramHacked), $pcType);
                        $log->addLog($_SESSION['id'], $log->logText('ANALYZE', $paramHacker), 0);
                        
                        $this->session->exp_add('ANALYZE');
                        
                    } else {
                        $this->system->handleError('NOT_LISTED');
                    }

                    break;
                case '24': //INSTALL DOOM

                    if($this->virus->doom_haveInstaller($id, $this->pNPC)){

                        $this->virus->doom_setup($id, $this->pNPC);
                        
                        $this->session->exp_add('DOOM');
       
                    } else {
                        $this->system->handleError('NO_INSTALLER');
                    }

                    break;
                case '25': //IP RESET

                    $ipInfo = $this->player->ip_info();

                    $error = '';
                    $debt = 0;
                    
                    if($ipInfo['PRICE'] > 0){

                        if($this->finances->isPlayerAccount($this->pInfo)){
                            
                            if($this->finances->totalMoney() >= $ipInfo['PRICE']){
                                
                                $debt = 1;
                                $this->finances->debtMoney($ipInfo['PRICE'], $this->pInfo);
                                
                            } else {
                                $error = 'BAD_MONEY';
                            }
                            
                        } else {
                            $error = 'BAD_ACC';
                        }

                    }

                    if($error == ''){
                    
                        if($ipInfo['NEXT_RESET'] == 0 || $debt == 1){
                        
                            while(TRUE){

                                $ip = ip2long(rand(1,255).'.'.rand(1,255).'.'.rand(1,255).'.'.rand(1,255));

                                $this->session->newQuery();
                                $searchUsers = "SELECT users.id FROM users WHERE gameIP = '".$ip."' LIMIT 1";

                                $this->session->newQuery();
                                $searchNPC = "SELECT npc.id FROM npc WHERE npcIP = '".$ip."' LIMIT 1";

                                if(sizeof($this->pdo->query($searchUsers)->fetchAll()) == 0 & sizeof($this->pdo->query($searchNPC)->fetchAll()) == 0){
                                    break;
                                }

                            }

                            //mudo ip da tabela users
                            $this->session->newQuery();
                            $sql = "UPDATE users SET gameIP = '".$ip."' WHERE id = '".$_SESSION['id']."' LIMIT 1";
                            $this->pdo->query($sql);

                            // 2019: Remove those who had my old ip in the hackedDb, and also notify them
                            //deleto quem me tinha na hacked database, e notifico
                            $this->session->newQuery();
                            $sql = "SELECT userID FROM lists WHERE ip = '".$playerInfo->gameip."'";
                            $data = $this->pdo->query($sql)->fetchAll();

                            for($i=0;$i<sizeof($data);$i++){

                                $this->list->addNotification($data[$i]['userid'], $playerInfo->gameip, 1);

                            }

                            $this->session->newQuery();
                            $sql = "DELETE lists, lists_specs 
                                    FROM lists
                                    LEFT JOIN lists_specs
                                    ON lists_specs.listID = lists.id
                                    WHERE ip = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "UPDATE virus SET active = 0, installedIp = '".$ip."' WHERE installedIp = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "DELETE FROM internet_connections WHERE ip = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "UPDATE users_stats SET ipResets = ipResets + 1, lastIpReset = NOW() WHERE uid = '".$_SESSION['id']."'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "UPDATE virus_ddos SET active = 0 WHERE ip = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);
                            
                            // 2019: Remove IP from clan war history (in case there's one), otherwise my new IP would leak there
                            //removo o ip do histórico de clan war (caso esteja acontecendo), senão o novo ip vai aparecer lá
                            $this->session->newQuery();
                            $sql = "SELECT COUNT(*) AS total
                                    FROM clan_users 
                                    INNER JOIN clan_war
                                    ON (
                                        clan_war.clanID1 = clan_users.clanID OR
                                        clan_war.clanID2 = clan_users.clanID
                                    )
                                    WHERE userID = '".$_SESSION['id']."'";
                            if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total > 0){
                                
                                $this->session->newQuery();
                                $sql = "UPDATE clan_ddos
                                        INNER JOIN round_ddos
                                        ON clan_ddos.ddosID = round_ddos.id
                                        SET displayAttacker = 0
                                        WHERE round_ddos.attID = '".$_SESSION['id']."'";
                                $this->pdo->query($sql);
                                
                                $this->session->newQuery();
                                $sql = "UPDATE clan_ddos
                                        INNER JOIN round_ddos
                                        ON clan_ddos.ddosID = round_ddos.id
                                        SET displayVictim = 0
                                        WHERE
                                            round_ddos.vicID = '".$_SESSION['id']."' AND
                                            round_ddos.vicNPC = 0";
                                $this->pdo->query($sql);
                                
                            }
                            
                            // 2019: Remove processes currently active on this IP, otherwise people would figure out my new one
                            //removo processos que estão ativos nesse ip, caso contrário pessoas descobrirão o novo ip
                            $this->session->newQuery();
                            $sql = "DELETE FROM processes WHERE pVictimID = '".$_SESSION['id']."'";
                            $this->pdo->query($sql);
                           
                            $bankIP = '';
                            if($ipInfo['PRICE'] > 0){
                                $accInfo = $this->finances->bankAccountInfo($this->pInfo);
                                $bankIP = long2ip($this->finances->getBankIP($accInfo['0']['bankid']));
                            }
                            $log->addLog($_SESSION['id'], $log->logText('IP_RESET', Array(0, $ipInfo['PRICE'], $this->pInfo, $bankIP)), 0);
                            
                            $this->session->exp_add('RESET', Array('ip'));
                            
                        } else {
                            $error = 'IPRESET_NO_TIME';
                        }

                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }
                    
                    break;
                case '26': //change password
                    
                    function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'){
                    
                        $str = '';
                        $count = strlen($charset);
                        while ($length--) {
                            $str .= $charset[mt_rand(0, $count-1)];
                        }
                        return $str;
                        
                    }
                    
                    $pwdInfo = $this->player->pwd_info();

                    $error = '';
                    $debt = 0;
                    
                    if($pwdInfo['PRICE'] > 0){

                        if($this->finances->isPlayerAccount($this->pInfo)){
                            
                            if($this->finances->totalMoney() >= $pwdInfo['PRICE']){
                                
                                $debt = 1;
                                $this->finances->debtMoney($pwdInfo['PRICE'], $this->pInfo);
                                
                            } else {
                                $error = 'BAD_MONEY';
                            }
                            
                        } else {
                            $error = 'BAD_ACC';
                        }

                    }
                    
                    if($error == ''){
                        
                        if($pwdInfo['NEXT_RESET'] == 0 || $debt == 1){
                            
                            $newPassword = randString(8);
                            
                            $this->session->newQuery();
                            $sql = "UPDATE users SET gamePass = '".$newPassword."' WHERE id = '".$_SESSION['id']."'";
                            $this->pdo->query($sql);
                            
                            $this->session->newQuery();
                            $sql = "SELECT userID FROM lists WHERE ip = '".$playerInfo->gameip."' AND pass <> 'unknown' AND pass <> 'exploited'";
                            $data = $this->pdo->query($sql)->fetchAll();

                            for($i=0;$i<sizeof($data);$i++){

                                $this->list->addNotification($data[$i]['userid'], $playerInfo->gameip, 2);

                            }
                            
                            $this->session->newQuery();
                            $sql = "UPDATE lists SET pass = 'unknown', virusID = '0' WHERE ip = '".$playerInfo->gameip."' AND pass <> 'exploited'";
                            $this->pdo->query($sql);

                            $this->session->newQuery();
                            $sql = "UPDATE virus, lists
                                    SET virus.active = 0 
                                    WHERE virus.installedIp = '".$playerInfo->gameip."' AND lists.pass <> 'exploited'";
                            $this->pdo->query($sql);                            
                            
                            $this->session->newQuery();
                            $sql = "DELETE FROM internet_connections WHERE ip = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);
                            
                            $this->session->newQuery();
                            $sql = "UPDATE users_stats SET pwdResets = pwdResets + 1, lastPwdReset = NOW() WHERE uid = '".$_SESSION['id']."'";
                            $this->pdo->query($sql);    
                            
                            $this->session->newQuery();
                            $sql = "UPDATE virus_ddos SET active = 0 WHERE ip = '".$playerInfo->gameip."'";
                            $this->pdo->query($sql);
                            
                            $this->session->addMsg('Password changed.', 'notice');
                            
                            $bankIP = '';
                            if($pwdInfo['PRICE'] > 0){
                                $accInfo = $this->finances->bankAccountInfo($this->pInfo);
                                $bankIP = long2ip($this->finances->getBankIP($accInfo['0']['bankid']));
                            }
                            $log->addLog($_SESSION['id'], $log->logText('PWD_RESET', Array(0, $pwdInfo['PRICE'], $this->pInfo, $bankIP)), 0);
                            
                        } else {
                            $error = 'PWDRESET_NO_TIME';
                        }
                        
                    }
                    
                    if(strlen($error) > 0){
                        $this->system->handleError($error);
                    }

                    break;
                case '27': //DDoS
                    
                    $virus = new Virus();

                    $virus->DDoS($victimIP);

                    $this->session->addMsg('DDoS successful. Report file generated.', 'notice');

                    break;
                case '28': //edit webserver
                    
                    require '/var/www/classes/Internet.class.php';
                    $internet = new Internet();
                    
                    if($this->pVictimID == 0){
                        $vicID = $_SESSION['id'];
                    } else {
                        $vicID = $this->pVictimID;
                    }
                    
                    $internet->webserver_setup($vicID, $this->pInfoStr, $this->pSoftID);
                    
                    $this->session->addMsg('Web server updated.', 'notice');
                    
                    break;
                default:
                    die("errerro");
                    break;

            }

            self::deleteProcess($this->pID);

        } else {
                die("Process not yet done");
        }               

    }

    public function deleteProcess($pid, $update = true){
        
        if(self::issetPID(($pid))){

            self::getProcessInfo($pid);
            
            if($this->pAction == 8){ //if he is editing log then I must delete  the tmp log
                $log = new LogVPC();
                $log->deleteTmpLog($this->pInfo);
            }
            
            if($update || $this->pTimeLeft > 0){
                self::updateProcessUsage(2, $pid, '', '');
            }

            $this->session->newQuery();
            $sql = "DELETE FROM processes WHERE pid = '".$pid."' LIMIT 1";
            $this->pdo->exec($sql);

        }

    }
    
    public function pauseProcess($pid){
        
       if($_SESSION['id'] != 1)
            $this->system->handleError('Sorry, the option to pause processes is temporarily disabled. They wont be automatically completed if you are at the Task Manager tab.', 'processes');

        $this->session->newQuery();
        $sql = "SELECT TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft
                FROM processes 
                WHERE pid = '".$pid."' 
                LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        if($data->ptimeleft > 0){

            self::updateProcessUsage(2, $pid, '', '');            
            
            $this->session->newQuery();
            $sql = "UPDATE processes SET isPaused = 1 WHERE pid = '".$pid."' LIMIT 1";
            $this->pdo->query($sql);
            
            $this->session->newQuery();
            $sql = "INSERT INTO processes_paused (pid, timeLeft, userID) VALUES ('".$pid."', '".$data->ptimeleft."', '".$_SESSION['id']."')";
            $this->pdo->query($sql);
            
        } else {
            $this->system->handleError('PROC_ALREADY_COMPLETED', 'processes');
        }
        
    }
    
    public function resumeProcess($pid){
        
        $this->session->newQuery();
        $sql = "
                SELECT processes_paused.timeLeft, processes.pAction, processes.pVictimID, processes.plocal, processes.pNPC, processes.pSoftID,
                TIMESTAMPDIFF(SECOND, processes.pTimeStart, processes_paused.timePaused) AS pDuration, 
                TIMESTAMPDIFF(SECOND, processes_paused.timePaused, NOW()) AS pTimePaused
                FROM processes_paused
                INNER JOIN processes ON processes_paused.pid = processes.pid
                WHERE processes_paused.pid = '".$pid."'
                LIMIT 1 
                ";

        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                
        $resInfo = self::resourceableInfo($data->paction);
        
        $usageInfo = self::updateProcessUsage(1, '1', $resInfo['COLUMN_ACTIVE'], $data->timeleft);
        
        if($data->pnpc == 1){
            $pcType = 'NPC';
        } else {
            $pcType = 'VPC';
        }
        
        $id = $_SESSION['id'];
        if(self::mustChangeID($data->paction, $data->plocal)){
            $id = $data->pvictimid;
        }        
        
        if($data->paction == 2){            
            $id = $_SESSION['id'];
            $pcType = 'VPC';
        }

        if(isset($data->psoftid)){
            $softInfo = $this->software->getSoftware($data->psoftid, $id, $pcType);
        } else {
            $softInfo = '';
        }
        
        $idealTime = self::calculateDuration($data->paction, $id, $pcType, $softInfo);
        
        $realTime = ($idealTime * 100) / $usageInfo['COLUMN_USAGE'];
                
        $totalDur = $realTime - $data->pduration; //timeleft      
                
        $this->session->newQuery();
        $sql = "DELETE FROM processes_paused WHERE pid = '".$pid."' LIMIT 1";
        $this->pdo->query($sql);
        
        $sum = $data->ptimepaused + ($totalDur - $data->timeleft);
        
        $sql = "UPDATE processes SET isPaused = 0, pTimeStart = DATE_ADD(pTimeStart, INTERVAL '".$data->ptimepaused."' SECOND), pTimeEnd = DATE_ADD(pTimeEnd, INTERVAL '".$sum."' SECOND), ".$resInfo['COLUMN_ACTIVE']." = '".$usageInfo['COLUMN_USAGE']."' WHERE pid = '".$pid."'";
        $this->pdo->query($sql);
                
    }
    
    public function isPaused($pid){
        
        $this->session->newQuery();
        $sql = "SELECT isPaused FROM processes WHERE pid = '".$pid."'";
        
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->ispaused == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function calculateDuration($pAction, $id, $pcType, $info = ''){

        if(!is_numeric($pAction)){
            $pAction = self::pNumericAction($pAction);
        }
        
        switch($pAction){
            
            case '1': //download
                
                $netInfoHacker = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $netInfoHacked = $this->hardware->getHardwareInfo($id, $pcType);

                $downloadRateHacker = $netInfoHacker['NET']/8;
                $uploadRateHacked = $netInfoHacked['NET']/16;

                $downloadRate = $downloadRateHacker;
                if($uploadRateHacked < $downloadRateHacker){
                    $downloadRate = $uploadRateHacked;
                }
                
                $softSize = $info->softsize;
                
                return $softSize/$downloadRate;
            case '2': //upload
                            
                $netInfoHacker = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $netInfoHacked = $this->hardware->getHardwareInfo($id, $pcType);
                
                $uploadRateHacker = $netInfoHacker['NET']/16;
                $downloadRateHacked = $netInfoHacked['NET']/8;

                $uploadRate = $uploadRateHacker;
                if($downloadRateHacked < $uploadRateHacker){
                    $uploadRate = $downloadRateHacked;
                }

                $softSize = $info->softsize;
                
                return $softSize/$uploadRate;
            case 3: //delete software                
                
                $softSize = $info->softsize;
                
                $hardwareInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $cpu = $hardwareInfo['CPU'];
                
                $time = $softSize - ($cpu / 50);                
                
                if($id == $_SESSION['id'] && $pcType == 'VPC'){
                    $time *= 0.5;
                }
                
                if($time < 10){
                    $time = 10;
                }
                                
                return $time;
            case 4: //hide
            case 5: //seek
                $softSize = $info->softsize;
                $hardwareInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $cpu = $hardwareInfo['CPU'];
         
                $time = round($softSize * 1.5 - ($cpu / 9));

                if($id == $_SESSION['id'] && $pcType == 'VPC'){
                    $time *= 0.5;
                }

                //seeking accounts for version too
                if($pAction == 5){
                    
                    $hiddenWith = $info->softhiddenwith;

                    $hiddenPenalty = ($hiddenWith - 10)*2;
                    
                    if($hiddenPenalty < 0){
                        $hiddenPenalty = 0;
                    }
                    
                    $time += $hiddenPenalty;
                    
                }
                
                if($time < 4){
                    $time = 4;
                }
                
                return $time;
            case 7: //av
                return 200;
                break;
            case 8: //edit log
                
                $log = new LogVPC();
                
                $curLog = $log->getLogValue($id, $pcType);
                
                if($info != ''){
                    $tmpLog = $log->getTmpLog($info);
                } else {
                    $tmpLog = '';
                }
                
                $lenCur = strlen($curLog);
                $lenTmp = strlen($tmpLog);                
                
                if($lenCur > $lenTmp){
                    $len = $lenCur;
                } else {
                    $len = $lenTmp;
                }
                
                $hardwareInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $cpu = $hardwareInfo['CPU'];
                
                $time = round(($len / 20) - $cpu / 6);
                
                if($id == $_SESSION['id'] && $pcType == 'VPC'){
                    $time *= 0.5;
                }
                
                if($time < 4){
                    $time = 4;
                } elseif($time > 60){
                    $time = 60;
                }
                
                return $time;
            case 11:
            case 12:
                
                $hardwareInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $cpu = $hardwareInfo['CPU'];
         
                $crackerInfo = $this->software->getBestSoftware(1, $_SESSION['id'], 'VPC', 1);
                $crc = $crackerInfo['0']['softversion'];
                
                $hashInfo = $this->software->getBestSoftware(2, $id, $pcType, 1);
                $hash = $hashInfo['0']['softversion'];
                
                if($hash == 0){
                    return 4;
                }
                
                $diff = $crc - $hash;
                
                if($diff == 0){
                    if($hash < 30){
                        return 30;
                    } elseif($hash < 50){
                        return 40;
                    } elseif($hash < 75){
                        return 50;
                    } else {
                        return 60;
                    }
                }
                
                if($diff == 1){
                    if($hash < 30){
                        return 20;
                    } elseif($hash < 50){
                        return 30;
                    } elseif($hash < 75){
                        return 40;
                    } else {
                        return 50;
                    }
                }

                if($diff >= 20){
                    if($hash < 30){
                        return 4;
                    } elseif($hash < 50){
                        return 10;
                    } elseif($hash < 75){
                        return 15;
                    } else {
                        return 30;
                    }
                }
                
                $time = round($diff + $hash/$diff);
                
                if($time < 4){
                    $time = 4;
                }
                
                return $time;
                
            case 13: //install
            case 14: //un-install
                
                $softSize = $info->softsize;
                $hardwareInfo = $this->hardware->getHardwareInfo($_SESSION['id'], 'VPC');
                $cpu = $hardwareInfo['CPU'];
         
                $time = round($softSize * 2 - ($cpu / 9));

                if($id == $_SESSION['id'] && $pcType == 'VPC'){
                    $time *= 0.5;
                }
                
                //un-installing is slightly faster
                if($pAction == 14){
                    $time *= 0.85;
                }
                
                if($time < 4){
                    $time = 4;
                }
                
                return $time;
            case 15: //port scan
                return 30;
            case 16: //exploit
                return 60;
            case 17: //research
                
                $time = 0;
                
                if($info->softversion < 30){
                    
                    $base = 280;
                    $increment = 20;
                    $totalIncrements = $info->softversion - 10;
                                        
                } elseif($info->softversion < 50){
                    
                    $base = 660;
                    $increment = 10;
                    $totalIncrements = $info->softversion - 29;                    
                    
                } elseif($info->softversion < 75){
                    $time = 900;
                } elseif($info->softversion < 100){
                    $time = 1200;
                } elseif($info->softversion < 150){
                    $time = 1500;
                } else {
                    $time = 1800;
                }
                
                if($time == 0){
                    $time = $base + $increment*$totalIncrements;
                }
                
                return $time;
            case 18:
                return 100;
            case 19:
                return 100;
            case 20:
                return 100;
            case 21:
                return 200;
            case 22:
                return 30;
            case 23:
                return 60;
            case 24:
                return 60;
            case 25:
                return 600;
            case 28:
                return 30;
            case 10:
                return 40;
	    default:
		return 60;    
                //die("Missing duratiopn");
            break;
            
        }
        
    }

    public function studyProcess($action, $host, $pcType){

            $getInfoSoftID = $this->system->verifyNumericGet('id');

            $id = $_SESSION['id'];
            if($host == 'remote'){
                $victimID = $this->player->getIDByIP($_SESSION['LOGGED_IN'], $pcType);
                $id = $victimID['0']['id'];
            }

            if(($this->software->issetSoftware($getInfoSoftID['GET_VALUE'], $id, $pcType)) || (strstr($action, 'XHD') !== FALSE)){

                if($pcType == 'VPC'){
                    $pNPC = '0';
                } else {
                    $pNPC = '1';
                }

                if($host == 'local'){

                    if($this->newProcess($_SESSION['id'], $action, '', $host, $getInfoSoftID['GET_VALUE'], '', '', '0')) {

                        if($this->session->issetProcessSession()) {

                            self::getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            self::showProcess();
               
                            self::endShowProcess(1);
                            

                        } else {

                            $softID = $getInfoSoftID['GET_VALUE'];
                            header("Location:software?action=del&id=$softID");

                        }

                    } else {

                        if (!$this->session->issetMsg()) {

                            $pid = $this->getPID($_SESSION['id'], $action, '', $host, $getInfoSoftID['GET_VALUE'], '', '', '0');

                            $this->session->processID('add', $pid);
                            self::getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            self::showProcess();
          
                            self::endShowProcess(1);
                            
                        } else {

                            header("Location:software");

                        }

                    }

                } else {

                    if($this->newProcess($_SESSION['id'], $action, $id, $host, $getInfoSoftID['GET_VALUE'], '', '', $pNPC)) {

                        if($this->session->issetProcessSession()) {

                            self::getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            self::showProcess();
                         
                            self::endShowProcess();

                        } else {

                            $softID = $getInfoSoftID['GET_VALUE'];
                            header("Location:internet?view=software&id=$softID");

                        }

                    } else {

                        if (!$this->session->issetMsg()) {

                            $pid = $this->getPID($_SESSION['id'], $action, $id, $host, $getInfoSoftID['GET_VALUE'], '', '', $pNPC);

                            $this->session->processID('add', $pid);
                            self::getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            self::showProcess();
               
                            self::endShowProcess();

                        } else {

                            header("Location:internet?view=software");

                        }

                    }

                }
                
            } else {

                $this->session->addMsg('This software doesn\'t exists.', 'error');

                if($host == 'local'){
                    header("Location:software");
                } else {
                    header("Location:internet?view=software");
                }
                
            }
                
        }

    public function studyRedirect(){
        
        switch($this->pAction){
            case 1: //download
            case 2: //upload
                $redirect = 'internet?view=software';
                break;
            case 3: //deletar software
            case 4: //hide soft
            case 5: //seek soft
            case 6:
            case 7:
            case 13:
            case 14:    
            case 17:
            case 19:
            case 23:
            case 24: //install doom    
            case 28:    
                if($this->pLocal == 1){
                    $redirect = 'software';
                } else {
                    $redirect = 'internet?view=software';
                }
                break;
            case 8://é edição de log
                if($this->pLocal == '1'){
                    $redirect = 'log';
                } else {
                    $redirect = 'internet?view=logs';
                }
                break;
            case 11:
            case 16:
                $redirect = 'internet?action=login';
                break;
            case 12:
                $redirect = 'internet?action=login&type=bank';
                break;
            case 15:
                $redirect = 'internet?action=hack&method=xp';
                break;
            case 18:
            case 20:
                $redirect = 'software?page=external';
                break;
            case 21:
                $redirect = 'internet?view=logs';
                break;
            case 22:
                if($this->pLocal == '1'){
                    $redirect = 'software';
                } else {
                    $redirect = 'internet?view=software';
                }
                break;
            case 25:
            case 26:    
                $redirect = 'index';
                break;
            case 27:
                $redirect = 'software';
                break;
            default:
                $redirect = 'clan';
                break;

        }

        return $redirect;

    }

    private function isReflexiveProcess($softType){

        $softBest = $this->software->getBestSoftware($softType, '', '', '1');

        if($softBest['0']['id'] == $this->pSoftID && $softBest['0']['userid'] == $this->pCreatorID){ //ele está dando hide no hider

            $totalValidSoft = $this->software->numberSoftware($softType, $_SESSION['id'], 'valid');

            if($totalValidSoft != 1){

                return TRUE;

            }

        }
        
        return FALSE;

    }
    
    public function runningProcessInfo(){
        
        $id = $_SESSION['id'];
        
        $return = Array();
        $return['ISSET'] = 0;
        
        $this->session->newQuery();
        $sql = "SELECT pAction, TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft FROM processes WHERE pCreatorID = $id";
        $data = $this->pdo->query($sql)->fetchAll();

        if(sizeof($data) > 0){
            
            $return['ISSET'] = 1;
            $return['COMPLETE_NET_PROC'] = 0;
            $return['INCOMPLETE_NET_PROC'] = 0;
            $return['COMPLETE_CPU_PROC'] = 0;
            $return['INCOMPLETE_CPU_PROC'] = 0;
            
            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['paction'] == 1 || $data[$i]['paction'] == 2){
                    
                    if($data[$i]['ptimeleft'] > 0){
                        $return['INCOMPLETE_NET_PROC']++;
                    } else {
                        $return['COMPLETE_NET_PROC']++;
                    }

                } else {
                    
                    if($data[$i]['ptimeleft'] > 0){
                        $return['INCOMPLETE_CPU_PROC']++;
                    } else {
                        $return['COMPLETE_CPU_PROC']++;
                    }
                    
                }
                
            }
            
        }
        
        return $return;
        
    }
    
    public function totalProcesses(){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM processes WHERE pCreatorID = '".$_SESSION['id']."' AND isPaused = 0";
        
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }
    
    public function issetDDoSProcess(){
        
        $this->session->newQuery();
        $sql = "SELECT pid FROM processes WHERE pAction = 27 AND pCreatorID = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return $data['0']['pid'];
        } else {
            return 0;
        }
        
    }

}

?>
