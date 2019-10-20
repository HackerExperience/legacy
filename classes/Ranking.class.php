<?php

require_once '/var/www/classes/Player.class.php';

class Ranking extends Player {
    
    protected $pdo;
    public $session;
    
    private $serverInfo;
    
    public function __construct(){
        
        
        $this->pdo = PDO_DB::factory();
        $this->session = new Session();
        
    }
    
    public function handlePost(){
        
        $system = new System();
        
        $redirect = 'university?opt=certification';
        
        if(!isset($_POST['act'])){
            $system->handleError('Invalid get.', $redirect);
        }

        switch($_POST['act']){
            
            case 'buy':
                
                $player = new Player();
                
                if(!isset($_POST['id'])){
                    $system->handleError('Invalid ID.', $redirect);
                }

                $id = $_POST['id'];

                if(!ctype_digit($id) || ($id > 6) || ($id < 1)){
                    $system->handleError('Invalid ID.', $redirect);
                }

                if(self::cert_have($id)){
                    $system->handleError('You already have this certification.', $redirect);
                }
                
                if(!self::cert_haveReq($id)){
                    $system->handleError('You do not have the needed certifications to take this one.', $redirect);
                }
                
                $playerLearning = $player->playerLearning();
                
                if($playerLearning > 0){
                    if($playerLearning == $id){
                        $system->handleError('You already bought this certification.', $redirect);
                    } else {
                        $system->handleError('You are learning a different certification.', $redirect);
                    }
                }

                require 'certs.php';
                
                $price = $certs[$id]['PRICE'];
                
                if($price > 0){
                
                    if(!isset($_POST['acc'])){
                        $system->handleError('Invalid bank account. Please wait the account drop-down load.', $redirect);
                    }                    
                    
                    $acc = $_POST['acc'];
                    
                    if(!ctype_digit($acc)){
                        $system->handleError('Invalid bank account.', $redirect);
                    }
                    
                    require '/var/www/classes/Finances.class.php';
                    $finances = new Finances();

                    if($finances->totalMoney() < $price){
                        $system->handleError('You do not have enough money to buy this certification.', $redirect);
                    }

                    $accInfo = $finances->bankAccountInfo($acc);

                    if($accInfo['0']['exists'] == 0){
                        $system->handleError('This bank account is invalid.', $redirect);
                    } elseif($accInfo['0']['bankuser'] != $_SESSION['id']){
                        $system->handleError('This bank account is invalid.', $redirect);
                    }

                    $finances->debtMoney($price, $acc);

                }
                
                $player->setPlayerLearning($id);
                
                $this->session->addMsg('Certification bought.', 'notice');
           
                break;
            
        }

        header('Location:'.$redirect);
        exit();
        
    }
    
    public function updateDDoSCount($total){
        
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET ddosCount = ddosCount + $total WHERE uid = '".$_SESSION['id']."'";
        $this->pdo->query($sql);
        
    }
    
    public function updateTimePlayed(){

        $now = new Datetime('now');
        $diff = $now->diff($_SESSION['LAST_CHECK']);

        $timePlayed = ($diff->i * 60) + $diff->s;
        $corrTime = round(($timePlayed/60), 1);
        
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET timePlaying = timePlaying + '".(double)$corrTime."' WHERE uid = '".$_SESSION['id']."'";
        $this->pdo->query($sql);
        
    }
    
    public function updateMoneyStats($type, $amount, $to = ''){
        
        if($type == 1){ // hardware
            $column = 'moneyHardware';
        } elseif($type == 2) { //research
            $column = 'moneyResearch';
        } elseif($type == 3){ //transfer
            $column = 'moneyTransfered';
        } else {
            $column = 'moneyEarned';
        }
        
        if($to != ''){
            $id = $to;
        } else {
            $id = $_SESSION['id'];
        }
        
        $newAmount = round($amount, 0);
        
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET ".$column." = ".$column." + $newAmount WHERE uid = '".$id."'";
        $this->pdo->query($sql);
        
    }
    
    public function exp_getLevel($exp){
        
        if($exp < 100){
            return 1;
        } elseif($exp < 500){
            return 2;
        } elseif($exp < 1000){
            return 3;
        } elseif($exp < 2500){
            return 4;
        } elseif($exp < 5000){
            return 5;
        } elseif($exp < 10000){
            return 6;
        } elseif($exp < 20000){
            return 7;
        } elseif($exp < 40000){
            return 8;
        } elseif($exp < 80000){
            return 9;
        } else {
            return 10;
        }
        
    }
    
    public function getResearchRank($uid, $alltime = FALSE){
        
        if($alltime){
            die("Todo");
        }
                   
        $this->session->newQuery();
        $sql = "SELECT COUNT(DISTINCT userID) AS totalResearchs FROM software_research GROUP BY userID";
        $totalResearchs = sizeof($this->pdo->query($sql)->fetchAll());
        
        $this->session->newQuery();
        $sql = 'SELECT userID, COUNT(*) AS total, SUM(newVersion) AS soma 
                FROM software_research 
                GROUP BY userID 
                ORDER BY soma DESC';
        $researchInfo = $this->pdo->query($sql)->fetchAll();
        
        for($i = 0; $i < sizeof($researchInfo); $i++){
            
            if($researchInfo[$i]['userid'] != $uid) continue;
            
            return Array('rank' => ($i + 1), 'total' => $totalResearchs); 
            
        }
        
    }
    
    public function getSoftwareRanking($softwareID, $category = FALSE){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total, rank FROM ranking_software WHERE softID = '.$softwareID.' LIMIT 1';
        $issetInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                
        if($issetInfo->total == 0){
            return -1;
        }
        
        if($issetInfo->rank == -1){
            return -1;
        }
        
        if(!$category){
            if($issetInfo->rank == 0){
                return 1;
            }
            return $issetInfo->rank;
        }
        
        if($issetInfo->rank < 2){
            return 1;
        }
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(id) AS total
                FROM ranking_software
                INNER JOIN software
                ON software.id = ranking_software.softID
                WHERE 
                    rank <= '.$issetInfo->rank.' AND 
                    rank > -1 AND 
                    softID <> '.$softwareID.'
                GROUP BY software.softType 
                HAVING software.softType = 
                (
                    SELECT softType 
                    FROM software 
                    WHERE id = '.$softwareID.'
                )';
        $categoryRank = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        if(!$categoryRank){
            return 1;
        }
        if($categoryRank->total == 0){
            return 1;
        }
        
        return $categoryRank->total + 1;
        
        
    }
    
    public function getPlayerRanking($uid, $cached = 0){
        
        if($cached == 1){
            
            $this->session->newQuery();
            $sql = "SELECT rank FROM ranking_user WHERE userID = $uid LIMIT 1";
            $cached = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

            if($cached->rank == -1){
                
                $sql = "SELECT COUNT(*) AS total FROM ranking_user";
                return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
                
            }
            
            return $cached->rank;
            
        }        
        
        $this->session->newQuery();
        $sql = "SELECT id FROM hist_users_current WHERE userID = $uid";
        $query = $this->pdo->query($sql)->fetchAll();
        
        if(count($query) > '0'){
            
            return $query['0']['id'];
            
        } else {
            
            return 0;
            
        }
        
    }
    
    public function exp_getTotal($uid, $cached = 0){
        
        if($cached == 1){
            
            $this->session->newQuery();
            $sql = "SELECT COUNT(*) AS total, reputation FROM cache WHERE userID = $uid LIMIT 1";
            $cached = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

            if($cached->total == 0){
                return 0;
            }
            
            return $cached->reputation;
            
        }
        
        $this->session->newQuery();
        $sql = "SELECT exp FROM users_stats WHERE uid = $uid";
        $query = $this->pdo->query($sql)->fetchAll();
        
        if(count($query) > '0'){
            
            return $query['0']['exp'];
            
        } else {
            
            return 0;
            
        }

    }
    
    public function exp_studyAmount($action, $info){
        
        switch($action){
            
            case 'HACK':
                
                return round($info); //hacked(vCRC + vPEC)
            case 'COLLECT':

                return round($info * 0.01); //1% do total coletado
                
                break;
            case 'COLLECT_BITCOIN':
                
                return $info * 1000;
                
            case 'DDOS':
                
                return round($info * 0.1); //1% do total do DDoS
            case 'MISSION':
                
                return round($info * 0.05);
            case 'RESEARCH':
                
                return round($info * 10);
                            
        }

    }
    
    public function exp_add($exp, $uid){
        echo 'deprecated';
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET exp = exp + $exp WHERE uid = $uid";
        $this->pdo->query($sql);
        
    }
 
    public function cert_list(){
                
        require 'certs.php';
        
        $player = new Player();
        $learning = $player->playerLearning();
        
        for($i = 1; $i < 6; $i++){
            
            $addClass = '';
            $value = ' value="'.$certs[$i]['PRICE'].'"';
            
            if(self::cert_have($i)){
                $addClass = 'complete';
            } else {
                
                $addClass = 'incomplete';
                
                if(self::cert_haveReq($i)){
                    
                    if($learning == $i){
                        $addClass .= ' learning';
                    } else {
                        $addClass .= ' buy';
                    }
                    
                } else {
                    $addClass .= ' locked';
                }
                
            }
            
?>
                                <span class="<?php echo $addClass; ?>" id="cert<?php echo $i; ?>"<?php echo $value; ?>></span>
<?php
        }
        
?>
                                <span id="certs"></span>
                                <span id="modal"></span>
<?php
        
    }
    
    public function cert_getAll(){
        
        $uid = $_SESSION['id'];
        
        $this->session->newQuery();
        $sql = "SELECT certLevel FROM certifications WHERE userID = $uid";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->certlevel;
        
    }

    public function cert_show($cid){
        
        require 'certs.php';
        
        $certArray = $this->session->getCert();
        
        if(array_key_exists($cid, $certArray)){
            
            echo "<b>".$certs[$cid]['NAME']."</b><br/><br/>".$certs[$cid]['DESC']."<br/><br/>";
            
            if($certArray[$cid]['value'] == '0'){
                
                $certPrice = $certs[$cid]['PRICE'];
                
                if(self::cert_haveReq($cid)){
                
                    if($certPrice == '0'){

                        echo "<a href=\"?opt=certification&id=$cid&action=learn\">".'Its free, learn now.'."</a>";

                    } else {

                        echo "<a href=\"?opt=certification&id=$cid&action=buy\">".'Buy ($'.$certPrice.")</a>";

                    }
                
                } else {
                    
                    echo 'You need to learn '.$certs[$certArray[$cid]['req']]['NAME'].' before';
                    
                }
                
            } else {
                echo 'complete';
            }
            
        } else {
            echo 'Certification not found';
        }
        
    }

    public function cert_showPage($cid, $page){
        
        if($page == 0){
            $page = 1;
        }
        
        $link = 'certs/'.$cid.'-'.$page.'.php';

        require $link;
        
    }
   
    public function cert_totalPages($cid){
        
        switch($cid){
            
            case 1: //getting started
            case 2: //hacking 101
            case 4: //meet ddos
                return 3;
            case 3: //intermediate hacking
                return 2;
            case 5:
            case 6:
                return 1;
            default:
                die("invalid");
                break;
            
        }
        
    }
 
    public function cert_validate2learn($cid){
        
        if(!self::cert_have($cid)){
            if(self::cert_haveReq($cid)){
                return TRUE;
            }
        }
        
        return FALSE;
        
    }
    
    public function cert_showLearn($cid){

        if(self::cert_validate2learn($cid)){
            
            self::cert_showPage($cid, 0);

        }
        
    }
    
    public function cert_add(){
        
        $uid = $_SESSION['id'];        

        $this->session->newQuery();
        $sql = "UPDATE certifications SET certLevel = certLevel + 1 WHERE userID = $uid";
        $this->pdo->query($sql);
        
        $this->session->certSession($this->session->getCert() + 1);
        
        if($this->session->getCert() == 5){
            require '/var/www/classes/Social.class.php';
            $social = new Social();
            $social->badge_add(54, $_SESSION['id']);
        }
        
    }
    
    public function cert_end($cid){
        
        $msg = 'Congratulations! You just completed certification '.$cid.'! You are now able to ';
        
        switch($cid){
            case 1:
                $msg = 'Congratulations on completing the first certification. Take the next one as soon as possible.';
                break;
            case 2:
                $msg .= 'hack servers and install viruses.';
                break;
            case 3:
                $msg .= 'hack bank accounts and exploit services.';
                break;
            case 4:
                $msg .= 'DDoS servers.';
                break;
            case 5:
                $msg .= 'mitigate DDoS attacks.';
                break;
        }
        
        $this->session->addMsg($msg, 'notice');

        if($cid == 1 && $this->session->l == 'pt_BR'){
            
            $this->session->newQuery();
            $sql = 'UPDATE users_language SET lang = :lang WHERE userID = :userID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':userID' => $_SESSION['id'], ':lang' => 'br'));
            
        }
        
        if($cid == 2){
            
            require '/var/www/classes/Storyline.class.php';
            $storyline = new Storyline();
            
            $storyline->tutorial_start();
            
        }
        
        $this->session->exp_add('CERT');
        
    }
    
    public function cert_have($cid){
        
        if($cid > $this->session->getCert()){
            return FALSE;
        }
        
        return TRUE;        
        
    }
    
    public function cert_haveReq($cid){
        
        if($this->session->getCert() == $cid - 1){
            return TRUE;
        }

        return FALSE;
        
    }

    public function ranking_display($display){
        
        $system = new System();
        $table = 'ranking_';
        
        switch($display){
            
            case 'user':
                
                $table .= 'user';
                $pagStr = 'rankUser';
                $page = 'page';
                
                break;
            case 'clan':
                
                $table .= 'clan';
                $pagStr = 'rankClan';
                $page = 'show=clan&page';
                
                break;
            case 'software':
                
                $table .= 'software';
                $pagStr = 'rankSoftware';
                if($system->issetGet('orderby')){
                    $page = 'show=software&orderby='.$_GET['orderby'].'&page';
                } else {
                    $page = 'show=software&page';
                }
                
                break;
            case 'ddos':
                
                $table .= 'ddos';
                $pagStr = 'rankDDoS';
                $page = 'show=ddos&page';

                break;
            default:
                exit();
                break;
            
        }
        
        $this->session->newQuery();
        $sql = "SELECT rank FROM ".$table." LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            
            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate('', $pagStr, 50, $page, 1);
            $pagination->showPages(50, $page);
            
        } else {
            echo 'Ranking is being updated. Please, refresh in a few seconds.';
        }
 
    }
    
    public function serverStats_get($round = ''){

        if($_SESSION['ROUND_STATUS'] == 0 || $_SESSION['ROUND_STATUS'] == 2){
            $sql = 'SELECT id FROM round ORDER BY id DESC LIMIT 1';
            $round = ($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id - 1);
        }
        
        $ending = '';
        $select = 'totalusers, activeUsers, warezSent, spamSent, mailSent, ddosCount, hackCount, clans, timePlaying, totalListed, totalVirus,
                   totalMoney, researchCount, moneyResearch, moneyHardware, moneyTransfered, moneyEarned, usersClicks, missionCount,
                   totalConnections, totalTasks, totalSoftware, totalRunning, totalServers, clansWar, clansMembers, clansClicks, onlineUsers,
                   bitcoinSent';
        
        if($round == 'all'){
            $select = 'SUM(totalUsers) AS totalusers, SUM(activeUsers) AS activeusers, SUM(warezSent) AS warezsent, SUM(spamSent) AS spamsent,
                       SUM(mailSent) AS mailsent, SUM(ddosCount) AS ddoscount, SUM(hackCount) AS hackcount, clans,
                       SUM(timePlaying) AS timeplaying, SUM(totalListed) AS totallisted, SUM(totalVirus) AS totalvirus, 
                       SUM(totalMoney) AS totalmoney, SUM(researchCount) AS researchcount, SUM(moneyResearch) AS moneyResearch,
                       SUM(moneyHardware) AS moneyHardware, SUM(moneyEarned) AS moneyearned, SUM(moneyTransfered) AS moneyTransfered,
                       SUM(usersClicks) AS usersclicks, SUM(missionCount) AS missioncount, SUM(totalConnections) AS totalconnections,
                       SUM(totalTasks) AS totalTasks, SUM(totalSoftware) AS totalSoftware, SUM(totalRunning) AS totalRunning,
                       SUM(totalServers) AS totalservers, SUM(clansWar) AS clanswar, SUM(clansMembers) AS clansMembers, 
                       SUM(clansClicks) AS clansclicks, SUM(onlineUsers) AS onlineusers, SUM(bitcoinSent) AS bitcoinSent';
        } elseif($round == '') {
            $ending = 'ORDER BY id DESC LIMIT 1';
        } else {
            $ending = 'WHERE id = '.$round.' LIMIT 1';
        }

        $this->session->newQuery();
        $sql = 'SELECT 
                    '.$select.' 
                FROM round_stats 
                '.$ending;
        $this->serverInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        $this->session->newQuery();
        $sql = 'SELECT 
                    COUNT(id) AS total
                FROM round_stats';
        $this->serverInfo->totalrounds = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        
        $this->serverInfo->timeplaying /= 60;
       
        if($round == 'all'){
            $this->session->newQuery();
            $sql = 'SELECT TIMESTAMPDIFF(DAY, startDate, NOW()) AS roundDuration FROM round WHERE id = 1 LIMIT 1';
            $this->serverInfo->roundstarted = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->roundduration;

        } else {
            $this->serverInfo->totallisted = number_format($this->serverInfo->totallisted);
            $this->serverInfo->totalvirus = number_format($this->serverInfo->totalvirus);
                        
            $this->session->newQuery();
            $sql = 'SELECT TIMESTAMPDIFF(DAY, startDate, NOW()) AS roundDuration FROM round ORDER BY id DESC LIMIT 1';
            $this->serverInfo->roundstarted = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->roundduration;
            
            $this->session->newQuery();
            $sql = 'SELECT COUNT(*) AS total FROM users';
            $this->serverInfo->totalusers = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

        }
        
        return $this->serverInfo;
                
    }
    
    public function stats_setupTable($contentArray){
        
?>
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                        <h5><?php echo $contentArray['name']; ?></h5>
                    </div>

                    <div class="widget-content nopadding">
                        <table class="table table-cozy table-bordered table-striped">
                            <tbody>

<?php

foreach($contentArray AS $content){
    if(is_array($content)){
?>
                                <tr>
                                    <td><span class="item"><?php echo $content['item']; ?></span></td>
                                    <td><?php echo $content['val']; ?></td>
                                </tr>
<?php
    }
}
?>
                            </tbody>
                        </table>
                    </div>
                </div>
<?php
        
    }
    
    public function stats_create($section){
        
        $content = Array();
        
        switch($section){
            
            case 'users':
                
                $content['name'] = 'Users stats';
                
                $content[0]['item'] = 'Total players';
                $content[0]['val'] = number_format($this->serverInfo->totalusers);
                
                $content[1]['item'] = 'Active users';
                $content[1]['val'] = number_format($this->serverInfo->activeusers);
                
                $content[2]['item'] = 'Online users';
                $content[2]['val'] = number_format($this->serverInfo->onlineusers);
                
                $content[3]['item'] = 'Hours played';
                $content[3]['val'] = number_format($this->serverInfo->timeplaying);
                
                break;
            case 'hacking':
                
                $content['name'] = 'Hacking stats';
                
                $content[0]['item'] = 'Servers on Hacked Database';
                $content[0]['val'] = $this->serverInfo->totallisted;
                
                $content[1]['item'] = 'Servers used to DDoS';
                $content[1]['val'] = number_format($this->serverInfo->ddoscount);
                
                $content[2]['item'] = 'Hack Count';
                $content[2]['val'] = number_format($this->serverInfo->hackcount);

                break;
            case 'virus':
                
                $content['name'] = 'Virus stats';
                
                $content[0]['item'] = 'Running viruses';
                $content[0]['val'] = $this->serverInfo->totalvirus;
                
                $content[1]['item'] = 'Spam mails sent';
                $content[1]['val'] = number_format($this->serverInfo->spamsent);
                
                $content[2]['item'] = 'Warez uploaded';
                $content[2]['val'] = number_format($this->serverInfo->warezsent).' GB';
                
                $content[3]['item'] = 'Bitcoin mined';
                $content[3]['val'] = round($this->serverInfo->bitcoinsent, 1).' BTC';

                break;
            case 'clan':
                
                $content['name'] = 'Clan stats';
                
                $content[0]['item'] = 'Clan Count';
                $content[0]['val'] = number_format($this->serverInfo->clans);
                
                $content[1]['item'] = 'Members';
                $content[1]['val'] = number_format($this->serverInfo->clansmembers);
                
                $content[2]['item'] = 'Wars';
                $content[2]['val'] = number_format($this->serverInfo->clanswar);
                
                $content[3]['item'] = 'Clan Clicks';
                $content[3]['val'] = number_format($this->serverInfo->clansclicks);

                break;
            case 'computer':
                
                $content['name'] = 'Computer stats';
                
                $content[0]['item'] = 'Servers';
                $content[0]['val'] = number_format($this->serverInfo->totalservers);
                
                $content[1]['item'] = 'Softwares';
                $content[1]['val'] = number_format($this->serverInfo->totalsoftware);
                
                $content[2]['item'] = 'Running Softwares';
                $content[2]['val'] = number_format($this->serverInfo->totalrunning);
                
                $content[3]['item'] = 'Running tasks';
                $content[3]['val'] = number_format($this->serverInfo->totaltasks);
                
                $content[4]['item'] = 'Softwares researched';
                $content[4]['val'] = number_format($this->serverInfo->researchcount);
                
                $content[5]['item'] = 'Connected users';
                $content[5]['val'] = number_format($this->serverInfo->totalconnections);
                
                break;
            case 'round':
                
                $content['name'] = 'Others stats';
                
                if(isset($_GET['show'])){
                    $content[0]['item'] = 'Game age';
                } else {
                    $content[0]['item'] = 'Round age';
                }
                $content[0]['val'] = $this->serverInfo->roundstarted.' days';
                
                $content[1]['item'] = 'Missions completed';
                $content[1]['val'] = number_format($this->serverInfo->missioncount);
                
                $content[2]['item'] = 'Mails sent';
                $content[2]['val'] = number_format($this->serverInfo->mailsent);
                
                $content[3]['item'] = 'Profile Clicks';
                $content[3]['val'] = number_format($this->serverInfo->usersclicks);

                break;
            case 'money':
                
                $content['name'] = 'Money stats';
                
                $content[0]['item'] = 'Total Money';
                $content[0]['val'] = '<span class="green">$'.number_format($this->serverInfo->totalmoney).'</span>';
                
                $content[1]['item'] = 'Money spent on hardware';
                $content[1]['val'] = '<span class="green">$'.number_format($this->serverInfo->moneyhardware).'</span>';
                
                $content[2]['item'] = 'Money spent on research';
                $content[2]['val'] = '<span class="green">$'.number_format($this->serverInfo->moneyresearch).'</span>';

                $content[3]['item'] = 'Money transfered';
                $content[3]['val'] = '<span class="green">$'.number_format($this->serverInfo->moneytransfered).'</span>';
                
                $content[4]['item'] = 'Money earned';
                $content[4]['val'] = '<span class="green">$'.number_format($this->serverInfo->moneyearned).'</span>';
                
                break;
        }
        
        return $content;
        
    }
    
    public function serverStats_list($round){

?>

<?php
        
        self::serverStats_get($round);
        
        if($round == 'all'){
            $str = 'Showing stats since first round';
            $btn = 'View stats from current round';
            $btnLink = 'stats';
        } else {
            $str = 'Showing stats for current round';
            $btn = 'View stats since first round';
            $btnLink  = 'stats?round=all';
        }
        
?>
     
        <div class="center">
            <strong><?php echo $str; ?></strong>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <?php
                
                self::stats_setupTable(self::stats_create('users'));
                self::stats_setupTable(self::stats_create('hacking'));
                self::stats_setupTable(self::stats_create('virus'));
                self::stats_setupTable(self::stats_create('clan'));
                
                
                ?>
            </div>           
            <div class="span6">
                <?php
                
                self::stats_setupTable(self::stats_create('computer'));
                self::stats_setupTable(self::stats_create('round'));
                self::stats_setupTable(self::stats_create('money'));

                ?>

            </div>
        </div>
            <div class="row-fluid">
                <div class="span12 center">
                    <a href="<?php echo $btnLink; ?>" class="btn btn-info"><?php echo $btn; ?></a>
                </div>
            </div>
                                
<?php
        

    }
    
    public function stats_updateCollect($warezSent, $mailSent, $moneyEarned, $bitcoinSent){
        
        $this->session->newQuery();
        $sql = "UPDATE users_stats 
                SET 
                    moneyEarned = moneyEarned + '".$moneyEarned."', 
                    warezSent = warezSent + '".$warezSent."', 
                    spamSent = spamSent + '".$mailSent."',
                    bitcoinSent = bitcoinSent + '".$bitcoinSent."'
                WHERE uid = '".$_SESSION['id']."' LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function getMoneySpentOnResearch($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT moneyResearch FROM users_stats WHERE uid = '.$uid.' LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->moneyresearch;
        
    }
    
}

?>
