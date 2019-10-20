<?php

require_once '/var/www/classes/System.class.php';
require_once '/var/www/classes/Session.class.php';

class Storyline {
    
    private $session;
    private $pdo;
    
    private $fbiIP;
    private $safenetIP;
    
    function __construct(){
        
        $this->pdo = PDO_DB::factory();
        $this->session = new Session();
        
    }
    
    public function safenet_list(){
        
        $this->session->newQuery();
        $sql = "SELECT IP, reason FROM safeNet ORDER BY reason, startTime ASC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            for($i=0;$i<sizeof($data);$i++){
                
                switch($data[$i]['reason']){
                    
                    case 1:
                        $reason = 'DDoS';
                        break;
                    case 2:
                        $reason = 'Doom';
                        break;
                    case 3:
                        $reason = 'Illegal Transfer';
                        break;
                    case 4:
                        $reason = 'Delete file';
                        break;
                    default:
                        $reason = 'Unknown';
                        break;
                    
                }
                
                $realIP = long2ip($data[$i]['ip']);
                $counter = 0;
                for($k = 0; $k < strlen($realIP); $k++){
                    if($realIP[$k] == '.'){
                        $counter++;
                    }                    
                    if($counter < 2){
                        echo $realIP[$k];
                    } elseif($realIP[$k] == '.'){
                        echo $realIP[$k];
                    } else {
                        echo 'X';
                    }
                }
                
                echo ' - reason: '.$reason."<br/>";
                
            }
            
        } else {
            echo 'No IPs harming internet';
        }
        
    }
    
    public function safenet_onFBI($ip, $reason){
        
        $this->session->newQuery();
        $sql = "SELECT onFBI FROM safeNet WHERE IP = '".$ip."' AND reason = '".$reason."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        if($data->onfbi == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function safenet_add($ip, $reason, $info = ''){

        if(!self::safenet_isset($ip, $reason)){

            $text = '';
            
            switch($reason){

                case 1: //DDoS

                    $virus = new Virus();
                    
                    $addTime = round($info * 0.8);
                    
                    $text = _('We are tracking you for your recent ').number_format($virus->power2gbps($info))._(' gpbs DDoS attack.');
                    
                    break;
                case 2: //doom
                    $addTime = 604800;
                    $text = _('We are tracking you for your recent doom activity');
                    break;
                case 3: //transfer
                    
                    $addTime = round($info * 0.01);
                    
                    if($addTime < 3600){
                        $addTime = 3600;
                    }
                    
                    $text = _('We are tracking you for this recent transfer of $').number_format($info);
                    
                    break;
                case 4:
                    
                    $addTime = 1000;
                    
                    $text = _('We are tracking you for deleting this software.');
                    
                    break;
                default:
                    die("Invalid reason");
                    break;

            }        
            
            $this->session->newQuery();
            $sql = "INSERT INTO safeNet (IP, reason, startTime, endTime, count, onFBI)
                    VALUES ('".$ip."', '".$reason."', NOW(), DATE_ADD(NOW(), INTERVAL '".$addTime."' SECOND), 1, 0)";
            $this->pdo->query($sql);
            
            require_once '/var/www/classes/Mail.class.php';

            $mail = new Mail();
            $mail->newMail($_SESSION['id'], _('Safenet is tracking you.'), $text, '1', -3);            
                    
        } else {
            
            $this->session->newQuery();
            $sql = "UPDATE safeNet SET count = count + 1 WHERE IP = '".$ip."' AND reason = '".$reason."' LIMIT 1";
            $this->pdo->query($sql);
            
        }
        
    }
    
    public function safenet_getIP(){
        
        if($this->safenetIP != NULL){
            return $this->safenetIP;
        }
        
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE npcType = 50 LIMIT 1";
        $this->safenetIP = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;
        
        return $this->safenetIP;
        
    }
    
    public function safenet_until($ip){

        $this->session->newQuery();
        $sql = "SELECT endTime FROM safeNet WHERE ip = '".$ip."' ORDER BY endTime DESC";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->endtime;

    }
    
    public function safenet_update($ip, $reason, $info = ''){

        switch($reason){

            case 1:
                $timeSec = round($info * 0.1);
                break;
            case 2:
                die("to do");
                break;
            case 3:
                $timeSec = round($info * 0.01);
                if($timeSec < 3600){
                    $timeSec = 3600;
                }                
                break;
            case 4:
                $timeSec = 1000;
                break;
            default:
                die("Invalid reason");
                break;

        }
        
        $this->session->newQuery();
        $sql = "UPDATE safeNet SET count = count + 1, endTime = DATE_ADD(endTime, INTERVAL '".$timeSec."' SECOND) WHERE IP = '".$ip."' AND reason = '".$reason."' LIMIT 1";
        $this->pdo->query($sql);        
        
    }
    
    public function safenet_isset($ip, $reason){
        
        $this->session->newQuery();
        $sql = "SELECT IP FROM safeNet WHERE IP = '".$ip."' AND reason = '".$reason."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function safenet_monitorTransfers($amount, $userIP){
        
        if($amount >= 100000){
            
            $div = (int)($amount / 10000);
            
            if($div > 50){
                $div = 50;
            }
            
            $fbiOdds = $safenetOdds = $div/100;

        } else {
            $safenetOdds = 0.01;
            $fbiOdds = 0.01;
        }
        
        $die = rand(1,10)/10;

        if(!self::safenet_isset($userIP, 3)){
            
            if($safenetOdds >= $die){
                
                self::safenet_add($userIP, 3, $amount);

            }
            
        } else {
            
            self::safenet_update($userIP, 3, $amount);
            
            if(!self::fbi_isset($userIP)){

                if($fbiOdds >= $die){

                    self::fbi_add($userIP, 3, $amount);

                }
            
            } else {
     
                self::fbi_update($userIP, 3, $amount);
                
            }
            
        }
        
    }
    
    public function fbi_getBounty($ip){
        
        $this->session->newQuery();
        $sql = "SELECT SUM(bounty) AS totalBounty FROM fbi WHERE ip = '".$ip."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalbounty;
               
    }
    
    public function fbi_until($ip){
        
        $this->session->newQuery();
        $sql = "SELECT dateEnd FROM fbi WHERE ip = '".$ip."' ORDER BY dateEnd DESC";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->dateend;
        
    }
    
    public function fbi_update($ip, $reason, $info){
        
        $addTime = self::fbi_calculateTime($reason, $info, 1);
        $bounty = self::fbi_calculateBounty($reason, $info);
        
        $this->session->newQuery();
        $sql = "UPDATE fbi SET dateEnd = DATE_ADD(dateEnd, INTERVAL '".$addTime."' SECOND), bounty = bounty + '".$bounty."' WHERE ip = '".$ip."' AND reason = '".$reason."'";
        $this->pdo->query($sql);
        
        require_once '/var/www/classes/Mail.class.php';

        $mail = new Mail();
        $mail->newMail($_SESSION['id'], _('Bounty increased.'), _('Your bounty has increased. We want you so bad.'), '2', -2);
        
    }
    
    private function fbi_calculateBounty($reason, $info){
        
        switch($reason){
            
            case 1: //ddos
                return $info / 100;
            case 2: //doom
                return 100000;
            case 3:
                if($info * 0.10 > 100000){
                    return 100000;
                }
                return $info * 0.10;
            case 4:
                return 1000;
        }
        
    }
    
    public function fbi_payBounty($ip){
    
        $finances = new Finances();
        
        $this->session->newQuery();
        $sql = "SELECT SUM(bounty) AS totalBounty FROM fbi WHERE ip = '".$ip."'";
        $bounty = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalbounty;
        
        $finances->addMoney($bounty, $finances->getWealthiestBankAcc());
        
        $this->session->newQuery();
        $sql = "DELETE FROM fbi WHERE ip = '".$ip."'";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "DELETE FROM safeNet WHERE IP = '".$ip."'";
        $this->pdo->query($sql);
        
        $ranking = new Ranking();
        
        $ranking->updateMoneyStats(4, $bounty);
        
    }
    
    private function fbi_calculateTime($reason, $info, $reincident = 0){
        
        switch($reason){
            
            case 1:
                
                if($reincident == 1){
                    return round($info * 0.1);
                } else {
                    return round($info * 0.8);
                }
                
            case 2:
                return 604800; //1 semana
            case 3:
                return round($info * 0.01);
            case 4:
                return 600;
                
        }
        
    }
    
    public function fbi_isset($ip, $reason = ''){
        
        if($reason != ''){
            $where = ' AND reason = '.$reason.' ';
        } else {
            $where = '';
        }
        
        $this->session->newQuery();
        $sql = "SELECT ip FROM fbi WHERE ip = '".$ip."' $where LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function fbi_add($ip, $reason, $info = '0'){
        
        if(!self::fbi_isset($ip, $reason)){

            $this->session->newQuery();
            $sql = "UPDATE safeNet SET onFBI = '1' WHERE IP = '".$ip."' AND reason = '".$reason."'";
            $this->pdo->query($sql);

            $duration = self::fbi_calculateTime($reason, $info);
            $bounty = self::fbi_calculateBounty($reason, $info);

            $this->session->newQuery();
            $sql = "INSERT INTO fbi (ip, reason, bounty, dateAdd, dateEnd)
                    VALUES ('".$ip."', '".$reason."', '".$bounty."', NOW(), DATE_ADD(NOW(), INTERVAL '".$duration."' SECOND))";
            $this->pdo->query($sql);
        
            require_once '/var/www/classes/Mail.class.php';

            $mail = new Mail();
            $mail->newMail($_SESSION['id'], _('FBI suspect'), _('Hey bitch, FBI is now looking for you. Take care and expect attacks.'), '1', -2);            
            
        } else {
            
            self::fbi_update($ip, $reason, $info);
            
        }
        
    }
    
    public function fbi_list(){
        
        $this->session->newQuery();
        $sql = "SELECT ip, reason, bounty FROM fbi ORDER BY reason, dateAdd ASC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
            
            $correctList = self::fbi_mergeBounty($data);

            foreach($correctList as $ip => $info){
                
                echo '<a href="internet?ip='.long2ip($ip).'">'.long2ip($ip).'</a> - <b>Bounty:</b> <font color="green">$'.number_format($info['BOUNTY']).'</font> - <b>Reason:</b> '.$info['REASON'].'<br/>';
                
            }
            
        } else {
            echo 'No wanted IPs atm';
        }
        
    }
    
    public function fbi_getIP(){
        
        if($this->fbiIP != NULL){
            return $this->fbiIP;
        }
                
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE npcType = 51 LIMIT 1";
        $this->fbiIP = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;
        
        return $this->fbiIP;
                
    }
    
    public function fbi_mergeBounty($data){
        
        $nameArr = Array();
        for($i=0;$i<sizeof($data);$i++){
        
            switch($data[$i]['reason']){

                case 1:
                    $reason = 'DDoS';
                    break;
                case 2:
                    $reason = 'Nuke';
                    break;
                case 3:
                    $reason = 'Ilegal Transfer';
                    break;
                case 4:
                    $reason = 'Delete file';
                    break;

            }            
            
            if(!array_key_exists($data[$i]['ip'], $nameArr)){
                $nameArr[$data[$i]['ip']]['BOUNTY'] = $data[$i]['bounty'];
                $nameArr[$data[$i]['ip']]['REASON'] = $reason;
            } else {
                $nameArr[$data[$i]['ip']]['BOUNTY'] += $data[$i]['bounty'];
                $nameArr[$data[$i]['ip']]['REASON'] .= ', '.$reason;
            }
            
        }
        
        return $nameArr;
        
    }
    
    public function fbi_display(){
        
        $this->session->newQuery();
        $sql = "SELECT ip, bounty, reason FROM fbi ORDER BY bounty DESC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        $fbiIP = self::fbi_getIP();
        
        ?>

            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-thief"></span></span>
                    <h5><?php echo _("FBI Wanted List"); ?></h5>
                    <a href="internet?ip=<?php echo long2ip($fbiIP); ?>"><span class="label hide1024"><?php echo _("FBI"); ?></span></a>
                </div>

        <?php
        
        if(sizeof($data) > 0){
            
            ?>
                
                <div class="widget-content nopadding border">
                    <table class="table table-cozy table-bordered table-striped">
                        <tbody>
                
            <?php
            
            $correctList = self::fbi_mergeBounty($data);

            $i = 0;
            foreach($correctList as $ip => $info){
                $i++;
                if($i == 4){
                    break;
                }
                $strIP = long2ip($ip);
                
                $strIP = '<a class="black" href="internet?ip='.$strIP.'">'.$strIP.'</a>';
                
                $bounty = '<font color="green">$'.number_format($info['BOUNTY']).'</font>';
                
                ?>
                            <tr>
                                <td><?php echo $strIP; ?></td>
                                <td><?php echo $bounty; ?></td>
                            </tr>
                <?php
                
            }
            
            ?>
                        
                        </tbody>
                    </table>  
                </div>
                        
            <?php

            if($i > 3){//TODO: melhorar
                ?>
                <span style="font-size: x-small; text-align: center; float: right;"><a href="internet?ip=<?php echo long2ip($fbiIP); ?>">(<?php echo _("View full wanted list"); ?>)</a></span>    
                <?php
            }
            
        } else {
            
            ?>
                
                <div class="widget-content padding border">
                    <?php echo _("No wanted IPs ATM"); ?>
                </div>

            <?php
                    
        }
        
        ?>
             
            </div>
                
        <?php

    }
    
    public function round_display(){
        
        $this->session->newQuery();
        $sql = 'SELECT id, name, startDate, TIMESTAMPDIFF(DAY, startDate, NOW()) AS roundDuration FROM round ORDER BY id DESC LIMIT 1';
        $data = $this->pdo->query($sql)->fetchAll();

        ?>

                <div class="widget-box">

                    <div class="widget-title">
                        <span class="icon"><span class="icon-tab he16-tag_blue"></span></span>
                        <h5><?php echo _("Round Info"); ?></h5>
                        <span class="label label-info hide1024">Round <?php echo $data['0']['id']; ?></span>                                                 
                    </div>                                    

                    <div class="widget-content nopadding border">

                        <table class="table table-cozy table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td><span class="item"><?php echo _("Name"); ?></span></td>
                                    <td><?php echo $data['0']['name']; ?></td>
                                </tr>
                                <tr>
                                    <td><span class="item"><?php echo _("Started"); ?></span></td>
                                    <td><?php echo substr($data['0']['startdate'], 0, -9); ?></td>
                                </tr>
                                <tr>
                                    <td><span class="item"><?php echo _("Age"); ?></span></td>
                                    <td><?php printf(ngettext("%d day", "%d days", $data['0']['roundduration']), $data['0']['roundduration']); ?></td>
                                </tr>                                                       
                            </tbody>
                        </table>                                                    

                    </div>        
                    
                </div>
        
        <?php
        
    }
    
    public function round_getAll(){
        
        $this->session->newQuery();
        $sql = 'SELECT id, name, startDate, TIMESTAMPDIFF(DAY, startDate, NOW()) AS roundDuration FROM round ORDER BY id DESC LIMIT 1';
        $data = $this->pdo->query($sql)->fetchAll();

        return $data;
        
    }
    
    public function round_status(){
        
        $this->session->newQuery();
        $sql = 'SELECT status FROM round ORDER BY id DESC LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->status;
        
    }
    
    public function round_stats($roundID){
                
        $this->session->newQuery();
        $sql = 'SELECT activeUsers, ddosCount, hackCount, researchCount FROM round_stats WHERE id = \''.$roundID.'\' LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
    }
        
    public function round_current(){
        
        $this->session->newQuery();
        $sql = 'SELECT id FROM round ORDER BY id DESC LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;
        
    }
    
    public function round_timeToStart(){
        
        $this->session->newQuery();
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, NOW(), startDate) AS diff FROM round ORDER BY id DESC LIMIT 1';
        $timeToStart = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->diff;
        
        if($timeToStart < 0){
            $this->session->logout(0);
        }
        
        if($timeToStart < 60){
            $start = 'ONE MINUTE!!!';
        } elseif($timeToStart < 3600){
            $start = 'less than one hour!';
        } else {
            $timeToStart /= 3600;
            if($timeToStart < 24){
                $start = (int)$timeToStart.' hours.';
            } else {
                $timeToStart /= 24;
                $start = (int)$timeToStart.' day';
                if((int)$timeToStart > 1){
                    $start .= 's';
                }
            }
        }
        
        return $start;
        
    }

    public function doom_stats($roundID){
        
        $return = Array();
        $tries = 0;
        
        $this->session->newQuery();
        $sql = 'SELECT doomCreatorID, doomClanID, status FROM hist_doom WHERE round = \''.$roundID.'\'';
        $data = $this->pdo->query($sql);
                
        while($doomInfo = $data->fetch(PDO::FETCH_OBJ)){
            
            if($doomInfo->status == 2){
                $index = $tries;
            } else {
                $index = 'DOOM';
            }
            
            $return[$index] = Array(
                'creatorID' => $doomInfo->doomcreatorid,
                'clanID' => $doomInfo->doomclanid,
                'status' => $doomInfo->status
            );
            
            $tries++;
            
        }
        
        return $return;
        
    }

    public function doom_totalServices(){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM virus_doom WHERE status = 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }
    
    public function doom_displayProgress($display){

        
        if($display == 'current'){

            $totalServices = self::doom_totalServices();

            if($totalServices > 0){

                self::doom_listServices();

            } else {
                echo 'There are no doom virus running at the moment.';
            }

        } else {
            
            self::doom_showFailed();
            
        }
        
    }
    
    public function doom_showFailed(){
        
        require '/var/www/classes/Clan.class.php';
        
        $player = new Player();
        $clan = new Clan();
        
        $this->session->newQuery();
        $sql = "SELECT 
                    virus_doom.doomID, doomIP, creatorID, clanID, doom_abort.abortDate, TIMESTAMPDIFF(SECOND, doom_abort.abortDate, doomDate) AS timeLeft,
                    users.login AS aborter, doom_abort.abortedBy
                FROM virus_doom 
                INNER JOIN doom_abort
                ON doom_abort.doomID = virus_doom.doomID
                INNER JOIN users
                ON users.id = doom_abort.abortedBy
                WHERE status = 2
                ORDER BY doom_abort.abortDate DESC";
        $data = $this->pdo->query($sql);
        
        $i = 0;
        while ($doomInfo = $data->fetch(PDO::FETCH_OBJ)) {

            $i++;
                        
            $doomClan = $doomInfo->clanid;
            $doomIP = long2ip($doomInfo->doomip);
            $userInfo = $player->getPlayerInfo($doomInfo->creatorid);
            $locationIP = '<a href="internet?ip='.$doomIP.'">'.$doomIP.'</a>';
            $doomLocation = 'Located at '.$locationIP;
            $aborter = '<a href="profile?id='.$doomInfo->abortedby.'">'.$doomInfo->aborter.'</a>';
            
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
            
            if($doomClan == 0){
            
                $releasedBy = '<a href="profile?id='.$doomInfo->creatorid.'">'.$userInfo->login.'</a>';
                
                if( $clan->playerHaveClan($doomInfo->creatorid)){
                    $clanID = $clan->getPlayerClan($doomInfo->creatorid);
                    $supportedBy = 'Supported by <a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                } else {
                    $supportedBy = 'Lonewolf';
                }
            
            } else {
                
                $clanID = $doomInfo->clanid;
                $releasedBy = '<a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                $supportedBy = 'Launched by <a href="profile?id='.$doomInfo->creatorid.'">'.$userInfo->login.'</a>';
            
            }
            
            $abortedBy = '[ ABORTED by '.$aborter.' '.$timeStr.' before strike ]<br/><span class="small nomargin">Aborted on '.substr($doomInfo->abortdate, 0, -3).'</span>';

            ?>

            <div class="row-fluid">
                <ul class="list">
                    <li>
                        <div class="span4">
                            <div class="proc-desc">
                                Doom attempt by <?php echo $releasedBy; ?>
                            </div>
                        </div>
                        <div class="span5">    
                            <?php echo $abortedBy; ?>
                        </div>
                        <div class="span3 proc-action">
                                <span class="he16-doom_located"></span> <span class="small nomargin"><?php echo $doomLocation; ?></span><br/>
                                <span class="he16-doom_support"></span> <span class="small nomargin"><?php echo $supportedBy; ?></span>
                        </div>
                        <div style="clear: both;"></div>
                    </li>
                </ul>
            </div>
                        

            <?php    

        }
        
    }
    
    public function doom_listServices(){
        
?>
            <div class="alert alert-error">
                <center><strong>You must stop those attacks before they ruin the Internet!!! DDoS them!</strong></center>
            </div>
<?php

        require '/var/www/classes/Clan.class.php';
        
        $player = new Player();
        $clan = new Clan();
        
        $this->session->newQuery();
        $sql = "SELECT doomID, doomIP, creatorID, clanID, releaseDate, TIMESTAMPDIFF(SECOND, NOW(), doomDate) AS timeLeft 
                FROM virus_doom 
                WHERE status = 1";
        $data = $this->pdo->query($sql);
        
        $i = 0;
        while ($doomInfo = $data->fetch(PDO::FETCH_OBJ)) {
            
            $i++;
            
            $doomClan = $doomInfo->clanid;
            $doomID = $doomInfo->doomid;
            $doomIP = long2ip($doomInfo->doomip);
            $userInfo = $player->getPlayerInfo($doomInfo->creatorid);
            
            $locationIP = '<a href="internet?ip='.$doomIP.'">'.$doomIP.'</a>';
            $doomLocation = 'Located at '.$locationIP;
            
            if($doomClan == 0){
            
                $releasedBy = '<a href="profile?id='.$doomInfo->creatorid.'">'.$userInfo->login.'</a>';
                
                if($clan->playerHaveClan($doomInfo->creatorid)){
                    $clanID = $clan->getPlayerClan($doomInfo->creatorid);
                    $supportedBy = 'Supported by <a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                } else {
                    $supportedBy = 'Lonewolf';
                }
            
            } else {
                
                $clanID = $doomClan;
                $releasedBy = '<a href="clan?id='.$clanID.'">'.$clan->getClanInfo($clanID)->name.'</a>';
                $supportedBy = 'Launched by <a href="profile?id='.$doomInfo->creatorid.'">'.$userInfo->login.'</a>';
            
            }

            if($doomInfo->timeleft < 0){
                require 'cron/doomUpdater.php';
                $this->session->logout(0);
                header("Location:index");
                exit();
            }
            
            ?>

            <div class="row-fluid">
                <ul class="list">
                    <li>
                        <div class="span4">
                            <div class="proc-desc">
                                <?php echo $releasedBy; ?> is dooming the Internet at <?php echo $locationIP; ?>
                            </div>
                        </div>
                        <div class="span5">    
                            <div id="process<?php echo $doomID; ?>">
                                <div class="percent"></div>
                                <div class="pbar"></div>
                                <div class="elapsed"></div>
                            </div>
                        </div>
                        <div class="span3 proc-action">
                                <span class="he16-doom_located heicon"></span> <span class="small nomargin"><?php echo $doomLocation; ?></span><br/>
                                <span class="he16-doom_support heicon"></span> <span class="small nomargin"><?php echo $supportedBy; ?></span>
                        </div>
                        <div style="clear: both;"></div>
                    </li>
                </ul>
            </div>
                        

            <?php    

        }
        
        $_SESSION['pDoom'] = TRUE;
        
    }
        
    public function nsa_getID(){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM npc WHERE npcType = 52 LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;        
        
    }
    
    public function nsa_getIP(){
        
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE npcType = 52 LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;          
        
    }
    
    public function nsa_haveDoom(){
 
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM software WHERE softType = 29 AND userID = '".self::nsa_getID()."' AND isNPC = 1 LIMIT 1";
        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total == 1){
            return TRUE;
        } else {
            return FALSE;
        }

    }
    
    public function nsa_installDoom(){
        
        $doomName = 'DooM';
        
        $this->session->newQuery();
        $sql = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, isNPC, originalFrom, licensedTo, isFolder)
                VALUES ('', '".self::nsa_getID()."', '".$doomName."', '10', '100', '512', '29', NOW(), '0', '1', '0', '0', '0')";
        $this->pdo->query($sql);        
        
    }
    
    public function nsa_getDoomID(){

        //TODO: e se for deletado? ddosado?
        
        $this->session->newQuery();
        $sql = "SELECT id FROM software WHERE softType = 29 AND userID = '".self::nsa_getID()."' AND isNPC = 1 LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;        
        
    }

    public function md_getID(){
        
        $this->session->newQuery();
        $sql = "SELECT npcID FROM npc_key WHERE npc_key.key = 'MD' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcid;
        
    }
    
    public function md_getIP(){
        
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE id = '".self::md_getID()."' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;
        
    }
    
    public function evilcorp_getID(){
        
        $this->session->newQuery();
        $sql = "SELECT npcID FROM npc_key WHERE npc_key.key = 'EVILCORP' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcid;
        
    }
    
    public function evilcorp_getIP(){
        
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE id = '".self::evilcorp_getID()."' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;        
        
    }
    
    public function evilcorp_getName(){
        
        $this->session->newQuery();
        $sql = "SELECT name 
                FROM npc_info_en
                WHERE npcID = '".self::evilcorp_getID()."' 
                LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->name; 
        
    }
    
    public function tutorial_createVictims(){
        
        $npc = new NPC();
        
        $victim = $npc->generateNPC(80);
        $sndVictim = $npc->generateNPC(80);
        
        $victimIP = $npc->getNPCInfo($victim)->npcip;
        $sndVictimIP = $npc->getNPCInfo($sndVictim)->npcip;

        return Array($victimIP, $sndVictimIP);
        
    }
    
    public function tutorial_start(){
        
        require '/var/www/classes/Mission.class.php';
        require '/var/www/classes/Mail.class.php';
        
        $player = new Player();
        $mission = new Mission();
        $mail = new Mail();
                
        $mission->tutorial_createMission(1, self::md_getIP(), self::tutorial_createVictims());
        
        $playername = $player->getPlayerInfo($_SESSION['id'])->login;
        $subject = _('Doom the world!');
        $text = _('Hello ').'<strong>'.$playername._('</strong>,<br/><br/>I\'m Ensei Tankado, CEO of Numataka Corporation, and I\'m the guy who sponsored your equipment.<br/><br/>You might have heard of the recent leak of former NSA employee Edward Snowden, right? Well, the guy said the agency holds an extremely powerful virus that easily spreads through the network.<br/>The Doom Virus, as it was named, was the result of a banned experiment from Geek Pride Labs.<br/>This virus, if correctly setup, can hold the entire Internet as hostage of one single server.<br/><br/>He also said that they use a fork of this virus to spy on every user of the world.<br/><br/>Funny story, everyone is blaming the NSA for spying, but no one sees the potential of the original Doom Virus.<br/><br/>Well, I think you know why we hired you now. No one was able to hack the NSA - <em>until now</em>.<br/><br/>Keep researching your softwares, and good luck on your duty. You are not the only one after the virus.<br/><br/>By the way, I have an unattended job here, hope you don\'t mind but I assigned it to you.<br/><br/><br/>Sincerely,<br/>Ensei Tankado.');

        
        $mail->newMail($_SESSION['id'], $subject, $text, '0', -1);
        
        $this->session->newQuery();
        $sqlQuery = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, originalFrom)
            VALUES ('', ?, 'cracker', '10', '28', '9', '1', NOW(), '0', '', '0', '')";
        $sqlReg = $this->pdo->prepare($sqlQuery);
        $sqlReg->execute(array($_SESSION['id']));
        
    }
    
    public function tutorial_setExpireDate($ip1, $ip2){
        
        $this->session->newQuery();
        $sql = "INSERT INTO npc_expire
                    (npcID, expireDate)
                VALUES 
                    (
                        (SELECT npc.id
                        FROM npc
                        WHERE npcIP = '".$ip1."'),
                        DATE_ADD(NOW(), INTERVAL 7 DAY)
                    ),
                    (
                        (SELECT npc.id
                        FROM npc
                        WHERE npcIP = '".$ip2."'),
                        DATE_ADD(NOW(), INTERVAL 7 DAY)  
                    )";
        $this->pdo->query($sql);
        
    }
    
}


?>
