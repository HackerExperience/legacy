<?php

Class Pagination {

    private $pdo;
    private $system;
    private $virus;
    private $player;
    private $software;
    private $session;
    private $total;
    private $logVPC;
 
   function __construct() {

        require_once '/var/www/classes/System.class.php';
        require_once '/var/www/classes/Player.class.php';
        require_once '/var/www/classes/PC.class.php';

        $this->pdo = PDO_DB::factory();
        $this->system = new System();
        $this->software = new SoftwareVPC();
        $this->logVPC = new LogVPC();
        $this->virus = new Virus();
        $this->player = new Player();
        $this->session = new Session();
    }

    private function getCurrentPage($get) {
        
        $originalGet = $get;
        
        if (strpos($get, '&') == TRUE) {

            if (strpos($get, 'tab') == TRUE) {
                $length = '-3';
            } else {
                
                if(strpos($get, 'page')){

                    $length = -4;
                    
                } else {
                    die("study this");
                }
                
            }
            
            $get = substr($get, $length);
            
        }

        if ($this->system->issetGet($get)) {

            $getInfo = $this->system->verifyNumericGet($get);

            if ($getInfo['IS_NUMERIC'] == '1' && isset($getInfo['GET_VALUE'])) {
                
                $page = $getInfo['GET_VALUE'];
                
            } else {
                die("invalid page");
            }
            
        } else {
            $page = '1';
        }

        if($page < 1){
            
            header('Location:?'.$originalGet.'=1');
            exit();            
            
        }
  
        return $page;
        
    }

    private function getQueryLimit($limit, $page) {

        $start = $page - 1;
        $start = $limit * $start;

        $limitStr = $start . ',' . $limit;

        return $limitStr;
        
    }

    private function varInfo($page, $total, $limit) {

        $prev = $page - 1;
        $next = $page + 1;
        $totalPages = ceil($total / $limit);
        
        $return = Array(
            'PREV' => $prev,
            'NEXT' => $next,
            'TOTAL_PAGES' => $totalPages,
        );

        return $return;
        
    }
    
    public function verifyLimit($queryLimit, $total, $get){

        list($i, $limit) = explode(",", $queryLimit);
        
        if($i > $total){

            $lastPage = ceil($total / $limit);
            
            header('Location:?'.$get.'='.$lastPage);
            exit();
            
        }
        
    }

    public function paginate($uid, $gamePage, $limit, $get, $local, $npc = '') {

        $page = self::getCurrentPage($get);
        $queryLimit = self::getQueryLimit($limit, $page);

        switch ($gamePage) {

            case 'list':

                $this->session->newQuery();
                $sqlCount = "SELECT id FROM lists WHERE userID = $uid";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);

                if($total > 0){

                    ?>

                    <ul class="list ip" id="list">
                        
                    <?php
                    
                    $this->session->newQuery();
                    $sqlQuery = "SELECT 
                                    id, userID, user, ip, pass, virusID, 
                                    lists_specs.spec_net, lists_specs.spec_hdd,
                                    lists_specs_analyzed.minCPU, lists_specs_analyzed.maxCPU,
                                    lists_specs_analyzed.minRAM, lists_specs_analyzed.maxRAM
                                 FROM lists
                                 LEFT JOIN lists_specs
                                 ON lists_specs.listID = lists.id
                                 LEFT JOIN lists_specs_analyzed
                                 ON lists_specs_analyzed.listID = lists.id
                                 WHERE userID = $uid 
                                 ORDER BY hackedTime DESC 
                                 LIMIT $queryLimit";
                    $sqlInfo = $this->pdo->query($sqlQuery);

                    self::verifyLimit($queryLimit, $total, $get);
                    
                    $gotInstalled = '0';
                    $timeArray = Array();
                    $k = -1;
                    $i = 0;
                    while ($showInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)) {

                        $multipleVirus = 0;

                        $virText = '<span id="vname"></span>'._('No running virus');
                        $virTime = $virStr = '';
                        $cpu = $ram = $net = $hdd = '?';                        
                        
                        if ($showInfo->virusid != '0') {

                            $virText = '<span class="he16-bug heicon icon-tab"></span><span id="vname">';
                            
                            $virusInfo = $this->virus->getVirusInfo($showInfo->virusid);
                            
                            $virText .= $virusInfo['VIRUS_NAME'];

                            switch($virusInfo['VIRUS_TYPE']){

                                case 'DDoS':
                                    $virExtension = '.vddos';
                                    break;
                                case 'Spam':
                                    $virExtension = '.vspam';
                                    break;
                                case 'Warez':
                                    $virExtension = '.vwarez';
                                    break;
                                case 'Miner':
                                    $virExtension = '.vminer';
                                    break;

                            }
                            
                            $virText .= $virExtension.'</span>';

                            if ($virusInfo['VIRUS_TYPE'] != 'DDoS') {
                                $virTime = $virusInfo['TIME_WORKED_SECONDS'];
                                $virStr = $virusInfo['TIME_WORKED'];
                                $gotInstalled = '1';
                                $timeArray[$showInfo->id] = $virTime;
                                $k++;
                            }

                            if($this->virus->totalVirus($showInfo->ip) > 1){
                                $multipleVirus = 1;
                            }

                        }

                        $correctIP = long2ip($showInfo->ip);

                        if($showInfo->mincpu != FALSE){
                            
                            $cpuMin = round($showInfo->mincpu/1000, 1);
                            $cpuMax = round($showInfo->maxram/1000, 1);
                            
                            if($cpuMin == $cpuMax){
                                $cpu = $cpuMax.' G<span class="hide1024">Hz</span>';
                            } else {
                                $cpu = $cpuMin.'~'.$cpuMax.' G<span class="hide1024">Hz</span>';
                            }
                            
                            $ramMin = round($showInfo->minram/1000, 1);
                            $ramMax = round($showInfo->maxram/1000, 1);
                         
                            if($ramMin == $ramMax){
                                $ram = $ramMax.' GB';
                            } else {
                                $ram = $ramMin.'~'.$ramMax.' GB';
                            }
                            
                        }
                        
                        if($showInfo->spec_hdd != FALSE){
                            $hdd = ($showInfo->spec_hdd/1000).' GB';
                            if($hdd < 1){
                                $hdd = round($showInfo->spec_hdd).' MB';
                            }
                        }
                        
                        if($showInfo->spec_net != FALSE){
                            $net = $showInfo->spec_net;
                            if($net == 1000){
                                $net /= 1000;
                                $net .= ' Gbit/s';
                            } else {
                                $net .= ' Mbit/s';
                            }
                        }

                        if($showInfo->pass == 'unknown' || $showInfo->pass == 'exploited'){
                            $openTag = '<i>';
                            $closeTag = '</i>';
                        } else {
                            $openTag = $closeTag = '';
                        }
                        
                        ?>

                        <li id="l<?php echo $showInfo->id; ?>">
                            <div class="span4">
                                <div class="list-ip">
                                    <a  href="internet?ip=<?php echo $correctIP; ?>"><span id="ip"><?php echo $correctIP; ?></span></a>
                                </div>
                                <div class="list-user">
                                    <span class="he16-user heicon" title="User"></span><span class="small"><?php echo $showInfo->user; ?></span> 
                                    <span class="he16-password heicon" title="Password"></span><span class="small"><?php echo $openTag._($showInfo->pass).$closeTag; ?></span>
                                </div>
                            </div>
                            <div class="span4">
                                <div class="list-virus">
                                    <?php echo $virText; ?>
                                </div>
                                <div class="list-time">
                                    <span class="small" id="v<?php echo $k; ?>" title="<?php echo $virStr; ?>"><?php echo $virStr; ?></span>
                                </div>
                            </div>
                            <div class="span3">
                                <div class="span6">
                                    <span class="small hide-phone"><span class="he16-net heicon icon-tab nomargin"></span><?php echo $net; ?></span><br/>
                                    <span class="small hide-phone"><span class="he16-hdd heicon icon-tab nomargin"></span><?php echo $hdd; ?></span>     
                                </div>
                                <div class="span6">
                                    <span class="small hide-phone"><span class="he16-cpu heicon icon-tab nomargin"></span><?php echo $cpu; ?></span><br/>
                                    <span class="small hide-phone"><span class="he16-ram heicon icon-tab nomargin"></span><?php echo $ram; ?></span>                                                                                                          
                                </div>
                            </div>
                            <div class="span1" style="text-align: right;">
                                <div class="list-actions">
                                    <?php if($multipleVirus == 1) { ?>
                                    <span class="he16-change_virus heicon icon-tab tip-top manage-ip link" title="<?php echo _('Change Virus'); ?>" id="<?php echo $showInfo->id; ?>"></span>
                                    <?php } ?>
                                    <span class="tip-top delete-ip he16-delete icon-tab link" title="<?php echo _('Remove'); ?>" id="<?php echo $showInfo->id; ?>"></span>                            
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                        </li>    

                        <?php

                        $i++;
                        
                    }
                    
                    ?>
    
                    </ul>
    
                    <script>

                    var virTime = new Array();

                    <?php

                    $j = 0;
                    foreach($timeArray as $time){

                        echo 'virTime[\''.$j.'\'] = '.$time.';';

                        $j++;
                    }

                    ?>

                    </script>
                    <span id="modal"></span>

                    <?php
     
                } else {
                    echo _('You do not have any IPs on your Hacked Database.');
                }

                break;
            case 'mailAll':

                $mail = new Mail();
                
                $total = $mail->countMails();
                $id = $_SESSION['id'];

                self::verifyLimit($queryLimit, $total, $get);
                
                $this->session->newQuery();
                $sql = "SELECT id, mails.from, subject, dateSent, isRead FROM mails WHERE mails.to = $id AND isDeleted = 0 ORDER BY dateSent DESC LIMIT $queryLimit";
                $data = $this->pdo->query($sql);

                ?>
                
                <div class="widget-box">       
                    
                <?php if($total > 0){ ?>
                    
                    <div class="widget-title">                                                            
                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                        <h5><?php echo _('Inbox'); ?></h5>
                        <span class="label label-info"><?php echo $total; ?></span>
                    </div>

                    <div class="widget-content nopadding">                     

                        <table class="table table-cozy table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo _('Date'); ?></th>
                                    <th><?php echo _('Subject'); ?></th>
                                    <th><?php echo _('From'); ?></th>
                                    <th><?php echo _('Actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                <?php                         

                while ($mailInfo = $data->fetch(PDO::FETCH_OBJ)) {

                    $date = substr($mailInfo->datesent, 0, -3);
                    $subject = '<a href="mail?id='.$mailInfo->id.'">'._($mailInfo->subject).'</a>';
                    $name = $mail->mail_getSender($mailInfo->from);
                    
                    if ($mailInfo->isread == '0') {

                        $date = '<strong>'.$date.'</strong>';
                        $subject = '<strong>'.$subject.'</strong>';
                        $name = '<strong>'.$name.'</strong>';
                        
                    }

                    if($mailInfo->from > 0){
                        $name = '<a href="profile?id='.$mailInfo->from.'">'.$name.'</a>';
                    }
                    
                    ?>
                                
                        <tr>
                            <td>
                                <?php echo $date; ?>
                            </td>
                            <td>
                                <?php echo $subject; ?>
                            </td>
                            <td>
                                <?php echo $name; ?>
                            </td>
                            <td>
                                <span class="he16-bin heicon icon-tab tip-top link mail-delete" title="<?php echo _('Delete e-mail'); ?>" value="<?php echo $mailInfo->id; ?>"></span>
                            </td>
                            
                        </tr>
                                
                    <?php

                }

                ?>
                                
                            </tbody>
                        </table>
                        <span id="modal"></span>
                    </div>
                <?php } else { ?>
                    
                    <?php echo _('Ops! You have not received any email yet. :('); ?>
                    
                <?php } ?>
                </div>
                                
                                
                <?php

                break;
            case 'mailSent':

                $mail = new Mail();
                
                $total = $mail->countSentMails();

                $id = $_SESSION['id'];

                self::verifyLimit($queryLimit, $total, $get);
                
                $this->session->newQuery();
                $sql = "SELECT id, mails.to, subject, dateSent, isRead FROM mails WHERE mails.from = $id ORDER BY dateSent DESC LIMIT $queryLimit";
                $data = $this->pdo->query($sql);

                ?>
                
                <div class="widget-box">  
                    
                <?php if($total > 0){ ?>
                    
                    <div class="widget-title">                                                            
                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                        <h5><?php echo _('Outbox'); ?></h5>
                        <span class="label label-info"><?php echo $total; ?></span>
                    </div>

                    <div class="widget-content nopadding">                     
                        <table class="table table-cozy table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo _('Date'); ?></th>
                                    <th><?php echo _('Subject'); ?></th>
                                    <th><?php echo _('To'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                
                <?php                  

                while ($mailInfo = $data->fetch(PDO::FETCH_OBJ)) {

                    $date = substr($mailInfo->datesent, 0, -3);
                    $subject = '<a href="mail?id='.$mailInfo->id.'">'.$mailInfo->subject.'</a>';                    
                    $name = '<a href="profile?id='.$mailInfo->to.'">'.$this->player->getPlayerInfo($mailInfo->to)->login.'</a>';

                    ?>
                                
                                <tr>
                                    <td>
                                        <?php echo $date; ?>
                                    </td>
                                    <td>
                                        <?php echo $subject; ?>
                                    </td>
                                    <td>
                                        <?php  echo $name; ?>
                                    </td>
                                </tr>
                                
                    <?php

                }

                ?>
                                
                            </tbody>
                        </table>
                        <span id="modal"></span>
                    </div>
                    
                <?php } else { ?>
                    <?php echo _('You have not sent any emails yet.'); ?>
                <?php } ?>
                    
                </div>
                                
                <?php

                break;
            case 'missionsHistory':

                $mission = new Mission();
                
                $missionStats = $mission->getMissionsStats();
                                
                $total = $mission->countCompletedMissions();
                $id = $_SESSION['id'];

?>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><i class="he16-missions_completed"></i></span>
                                            <h5><?php echo _('Completed missions'); ?></h5>
                                            <span class="label label-info"><?php echo $missionStats['TOTAL']; ?></span>
                                        </div>
                                        <div class="widget-content nopadding">
<?php                        
                
                $studyLimit = explode(",", $queryLimit);
                $i = $studyLimit['0'];

                self::verifyLimit($queryLimit, $total, $get);
                
                $this->session->newQuery();
                $sql = "SELECT missions_history.id, type, hirer, missionEnd, prize, hirerInfo.name AS hirerName 
                        FROM missions_history
                        INNER JOIN npc ON npc.npcIP = hirer
                        INNER JOIN npc_info_en AS hirerInfo ON hirerInfo.npcID = npc.id
                        WHERE userID = $id AND completed = 1
                        ORDER BY missionEnd DESC 
                        LIMIT $queryLimit";
                $data = $this->pdo->query($sql);

?>
                                            <table class="table table-cozy table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"><?php echo _('Hirer'); ?></th>
                                                        <th scope="col"><?php echo _('Mission'); ?></th>
                                                        <th scope="col"><?php echo _('Reward'); ?></th>
                                                        <th scope="col"><?php echo _('Date'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
<?php                  

                while ($missionInfo = $data->fetch(PDO::FETCH_OBJ)) {

                    $hirerText = '<a href="internet?ip='.long2ip($missionInfo->hirer).'">'.$missionInfo->hirername.'</a>';
                    $missionText = $mission->missionText($missionInfo->type);
                    
                    $reward = number_format($missionInfo->prize);
                    
?>
                                                    <tr>
                                                        <td><span class="item"><?php echo $hirerText; ?></span></td>
                                                        <td><?php echo $missionText; ?></td>
                                                        <td><font color="green">$<?php echo $reward; ?></font></td>
                                                        <td><?php echo $missionInfo->missionend; ?></td>
                                                    </tr>
<?php
                    
                    $i++;
                    
                }
?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
<?php

                break;
            case 'changelog':
                
                //DEPRECATED
                
                $this->session->newQuery();
                $sqlCount = "SELECT id FROM changelog";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);

                $this->session->newQuery();
                $sqlQuery = "SELECT id, author, dateCreated, description FROM changelog ORDER BY dateCreated DESC LIMIT $queryLimit";
                $sqlInfo = $this->pdo->query($sqlQuery);

                echo "<table border=\"1\">";
                echo "<tr><td>ID</td><td>Date</td><td>Title</td><td>Author</td></tr>";

                while ($showInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)) {

                    echo "<tr>
                    <td>$showInfo->id</td>
                    <td>$showInfo->datecreated</td>
                    <td><a href=\"about?page=changelog&id=$showInfo->id\">$showInfo->description</a></td>
                    <td>$showInfo->author</td>
                    </tr>";
                }

                echo "</table>";

                break;
            case 'bugs':

                //DEPRECATED
                
                $this->session->newQuery();
                $sqlCount = "SELECT id FROM bugreports";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);

                $this->session->newQuery();
                $sqlQuery = "SELECT id, bugLink, bugReporter, bugText,  dateCreated, comment, reviewed FROM bugreports ORDER BY dateCreated ASC LIMIT $queryLimit";
                $sqlInfo = $this->pdo->query($sqlQuery);

                echo "<table border=\"1\">";
                echo "<tr><td>ID</td><td>Date</td><td>Text (click for details)</td><td>Reported by</td><td>Reviewed</td></tr>";

                while ($showInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)) {

                    $reviewed = 'No';

                    if ($showInfo->reviewed == '1') {
                        $reviewed = 'Yes';
                    }

                    $playerInfo = $this->player->getPlayerInfo($showInfo->bugreporter);

                    echo "<tr>
                    <td>$showInfo->id</td>
                    <td>$showInfo->datecreated</td>
                    <td><a href=\"bugs?id=$showInfo->id\">$showInfo->bugtext</a></td>
                    <td>$playerInfo->login</td>
                    <td>$reviewed</td>
                    </tr>";
                }

                echo "</table>";

                break;
                
            case 'news':
                
                $this->session->newQuery();
                $sqlCount = "SELECT id FROM news";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);

                self::verifyLimit($queryLimit, $total, $get);                
                
                $this->session->newQuery();
                $sqlQuery = "SELECT id, author, title, date FROM news ORDER BY date DESC LIMIT $queryLimit";
                $sqlInfo = $this->pdo->query($sqlQuery);

?>
                                <table class="table table-cozy table-bordered table-striped table-hover table-news with-check">
                                    <thead> 
                                        <tr>
                                            <th></th>
                                            <th class="hide-phone"><?php echo _('Posted Date'); ?></th>
                                            <th><?php echo _('Title'); ?></th>
                                            <th><?php echo _('Author'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php

                while($newsInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)){

                    switch($newsInfo->author){
                        
                        case 0:
                            $author = _('Game news');
                            break;
                        case -1:
                            $author = 'FBI';
                            break;
                        default:
                            $author = _('Unknown');
                            break;

                    }
                    
?>
                                        <tr>
                                            <td><a href="#"><span class="he16-news_list heicon"></a></td>
                                            <td class="hide-phone"><?php echo substr($newsInfo->date, 0, -3); ?></td>
                                            <td><a href="news?id=<?php echo $newsInfo->id; ?>"><?php echo $newsInfo->title; ?></a></td>
                                            <td><?php echo $author; ?></td>
                                        </tr>
<?php

                }
                
?>
                                    </tbody>
                                </table>
<?php
                                
                break;
            case 'listBank':
                                
                $this->session->newQuery();
                $sqlCount = "SELECT id FROM lists_bankAccounts WHERE userID = $uid";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);
                
                self::verifyLimit($queryLimit, $total, $get);

                if($total > 0){
                
                    ?>
                        
                    <ul class="list acc" id="list">
                        
                    <?php    
                    
                    $table = 'npc_info_en';
                    if($this->session->l == 'pt_BR'){
                        $table = 'npc_info_pt';
                    }
                    
                    $this->session->newQuery();
                    $sqlQuery = "SELECT lists_bankAccounts.id, bankID, bankAcc, bankPass, bankIP, lastMoney, lastMoneyDate, npcInfo.name
                                 FROM lists_bankAccounts 
                                 INNER JOIN $table AS npcInfo ON npcInfo.npcID = bankID
                                 WHERE userID = $uid 
                                 ORDER BY hackedDate DESC 
                                 LIMIT $queryLimit";
                    $sqlInfo = $this->pdo->query($sqlQuery);

                    $i = 0;
                    while($bankInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)){

                        if($bankInfo->lastmoney == -1){
                            $money = '?';
                        } else {
                            $money = $bankInfo->lastmoney;
                        }

                        $npc = new NPC();

                        $bankIP = long2ip($npc->getNPCInfo($bankInfo->bankid, $this->session->l)->npcip);

                        if($money != '?'){
                            $class = 'list-amount';
                            $money = '$'.number_format($money);
                        } else {
                            $class = 'list-noamount';
                        }
                        
                        $modalArr[$i]['ID'] = $bankInfo->id;
                        $modalArr[$i]['ACC'] = $bankInfo->bankacc;
                        
                        ?>
                        <li id="b<?php echo $bankInfo->id; ?>">

                            <div class="span3">
                                <div class="list-ip">
                                    #<span id="acc"><?php echo $bankInfo->bankacc; ?></span>
                                </div>
                                <div class="list-user">
                                    <span class="he16-password heicon icon-tab"></span><small><?php echo $bankInfo->bankpass; ?></small>
                                </div>
                            </div>
                            <div class="span4">
                                <div class="<?php echo $class; ?>">
                                    <?php echo $money; ?>
                                </div>
                                <div class="list-time">
                                    <small><?php echo substr($bankInfo->lastmoneydate, 5, -3); ?></small>
                                </div>
                            </div>
                            <div class="span3">
                                <div class="list-bank">
                                <span class="he16-bank heicon icon-tab"></span><?php echo $bankInfo->name; ?>
                                </div>
                                <div class="list-bankip">
                                    <a href="internet?ip=<?php echo $bankIP; ?>"><span class="he16-doom_location heicon icon-tab"></span><?php echo $bankIP; ?></a>
                                </div>
                            </div>
                            <div class="span2" style="text-align: right;">
                                <span class="tip-top delete-acc he16-delete heicon icon-tab link" title="Remove" id="<?php echo $bankInfo->id; ?>"></span>                                                           
                            </div>
                            <div style="clear: both;"></div>

                        </li>                  

                        <?php    
                        
                        $i++;

                    }

                    ?>
                        
                    </ul>
                        
                    <span id="modal"></span>
                    
                    <?php

                } else {
                    echo _('You havent hacked any bank account so far.');
                }

                break;
            case 'rankUser':
                
                $this->session->newQuery();
                $sqlCount = "SELECT rank FROM ranking_user";
                $count = $this->pdo->query($sqlCount)->fetchAll();
                
                $total = sizeof($count);
                
                $maxPossible = ceil($total / $limit);
         
                $pageToLoad = $page - 1;
                
                if($page > $maxPossible){
                    $this->system->handleError('Invalid page.', 'ranking');
                }
                
?>
                                <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo _('User'); ?></th>
                                            <th><?php echo _('Reputation'); ?></th>
                                            <th><?php echo _('Hacked Database IPs'); ?></th>
                                            <th><?php echo _('Clan'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>            
<?php                    
                
require 'html/ranking/user_'.$pageToLoad.'.html';

?>
                                    </tbody>
                                </table>
                            <a class="btn btn-success " href="stats"><?php echo _('View detailed Rank'); ?></a>
<?php
                
                self::verifyLimit($queryLimit, $total, $get);

                break;
            case 'rankClan':
               
                $this->session->newQuery();
                $sqlCount = "SELECT rank FROM ranking_clan";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = sizeof($count);
                
                $maxPossible = ceil($total / $limit);
         
                $pageToLoad = $page - 1;
                
                if($page > $maxPossible){
                    $this->system->handleError('Invalid page.', 'ranking?show=clan');
                }
               
?>
                                <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo _('Clan Name'); ?></th>
                                            <th><?php echo _('Power'); ?></th>
                                            <th><?php echo _('Win / Loses'); ?></th>
                                            <th><?php echo _('Members'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>                            
<?php                    
                
require 'html/ranking/clan_'.$pageToLoad.'.html';

?>
                                    </tbody>
                                </table>
<?php    
                break;
            case 'rankSoftware':
                
                $software = new SoftwareVPC();

                $static = TRUE;
                $orderStr = '';
                $joinStr = ' INNER JOIN software_research ON ranking_software.softID = software_research.softID';
                
                if($this->system->issetGet('orderby')){
                    
                    $extArray = Array('crc', 'hash', 'psc', 'fwl', 'hdr', 'skr', 'av', 'vspam', 'vwarez', 'vdoos', 'vcol', 'vbrk', 'exp', 'nmap', 'ana', 'doom');
                    
                    $orderBy = $this->system->verifyStringGet('orderby');
                    
                    if(in_array($orderBy['GET_VALUE'], $extArray)){
                        
                        $extID = $software->ext2int($orderBy['GET_VALUE']);
                        
                        $orderStr = ' WHERE software_research.softwareType = '.$extID.' ';
                        $joinStr = ' INNER JOIN software_research ON ranking_software.softID = software_research.softID WHERE software_research.softwareType = '.$extID.' ';
                        
                    } else {
                        
                        $this->session->addMsg('Invalid get.', 'error');
                        header("Location:ranking?show=software");
                        exit();
                        
                    }
                    
                    $static = FALSE;
                    
                }

                $this->session->newQuery();
                $sqlCount = "SELECT ranking_software.rank FROM ranking_software $joinStr";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = sizeof($count);
                                
?>
                                <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo _('Software Name'); ?></th>
                                            <th><?php echo _('Version'); ?></th>
                                            <th><?php echo _('Type'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>                            
<?php                   
                
                if($static){
                    
                    $maxPossible = ceil($total / $limit);

                    $pageToLoad = $page - 1;

                    if($page > $maxPossible){
                        $this->system->handleError('Invalid page.', 'ranking?show=software');
                    }
                    
                    require 'html/ranking/soft_'.$pageToLoad.'.html';
                    
                } else {
         
                    $this->session->newQuery();
                    $sqlQuery = "SELECT ranking_software.softID, software.softname, software.userID, software.softType, software.softversion
                                FROM ranking_software
                                INNER JOIN software
                                ON ranking_software.softID = software.id
                                $orderStr
                                ORDER BY ranking_software.id ASC 
                                LIMIT $queryLimit";
                    $sqlInfo = $this->pdo->query($sqlQuery);

                    $studyLimit = explode(",", $queryLimit);
                    $i = $studyLimit['0'];                     

?>
                                <a href="ranking?show=software" class="btn btn-primary">Show all softwares</a><br/><br/>
<?php

                    while($rankInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)){

                        $i++;

                        $extension = $software->getExtension($rankInfo->softtype);

                        $pos = '<center>'. $i .'</center>';
                        $name = $rankInfo->softname.$extension;
                        $version = $software->dotVersion($rankInfo->softversion);
                        $type = '<a href="?show=software&orderby='.substr($extension, 1).'">'.$software->int2stringSoftwareType($rankInfo->softtype).'</a>';
                    
?>
                                        <tr>
                                            <td><?php echo $pos; ?></td>
                                            <td><?php echo $name; ?></td>
                                            <td><?php echo $version; ?></td>
                                            <td><?php echo $type; ?></td>
                                        </tr>
<?php                    

                    }            
                
                }
                
?>
                                    </tbody>
                                </table>
<?php                

                break;
            case 'rankDDoS':
               
                $this->session->newQuery();
                $sqlCount = "SELECT rank FROM ranking_ddos";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = sizeof($count);
                
                $maxPossible = ceil($total / $limit);

                $pageToLoad = $page - 1;

                if($page > $maxPossible){
                    $this->system->handleError('Invalid page.', 'ranking?show=ddos');
                }
                
?>
                                <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo _('Attacker'); ?></th>
                                            <th><?php echo _('Victim'); ?></th>
                                            <th><?php echo _('Power'); ?></th>
                                            <th><?php echo _('Servers used'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>                            
<?php                

                require 'html/ranking/ddos_'.$pageToLoad.'.html';

?>
                                    </tbody>
                                </table>
<?php
                
                break;
            case 'fame':

                $top = $uid;
                
                
                if(!$top){
                    $roundInfo = $this->system->verifyNumericGet('round');
                    $round = $roundInfo['GET_VALUE'];
                } else {
                    $round = 'top';
                }
                
                $display = 'user';
                if($this->system->issetGet('show')){
                    $showInfo = $this->system->switchGet('show', 'clan', 'software', 'ddos');
                    if($showInfo['ISSET_GET'] == 1){
                        $display = $showInfo['GET_VALUE'];
                    }
                }
                
                switch($display){
                    case 'user':
                        
                        $title = 'Best Players of ';
                        $table = 'hist_users';
                        $redirect = 'fame';
                        $pathName = 'user';
                        $th = Array(
                            '0' => '#',
                            '1' => 'User',
                            '2' => 'Reputation',
                            '3' => 'Best Software',
                            '4' => 'Clan'
                        );
                        
                        break;
                    case 'clan':
                        
                        $title = 'Best Clans of ';
                        $table = 'hist_clans';
                        $redirect = 'fame?show=clan';
                        $pathName = 'clan';
                        $th = Array(
                            '0' => '#',
                            '1' => 'Clan',
                            '2' => 'Power',
                            '3' => 'Win / Losses',
                            '4' => 'Members',
                        );
                        
                        break;
                    case 'software':
                        
                        $title = 'Best Softwares of ';
                        $table = 'hist_software';
                        $redirect = 'fame?show=software';
                        $pathName = 'soft';
                        $th = Array(
                            '0' => '#',
                            '1' => 'Software Name',
                            '2' => 'Version',
                            '3' => 'Owner',
                            '4' => 'Type',
                        );
                        
                        break;
                    case 'ddos':
                        
                        $title = 'Best DDoS attacks of ';
                        $table = 'hist_ddos';
                        $redirect = 'fame?show=ddos';
                        $pathName = 'ddos';
                        $th = Array(
                            '0' => '#',
                            '1' => 'Attacker',
                            '2' => 'Victim',
                            '3' => 'Power',
                            '4' => 'Servers',
                        );
                        
                        break;
                }

                if($top){
                    $title .= 'All-Time';
                } else {
                    $title .= 'Round #'.$round;
                }
                                
                if($top){
                    
                    $i = 0;
                    $maxPossible = 0;
                    while(file_exists('html/fame/top_'.$pathName.'_'. $i .'.html')){
                        $i++;
                        $maxPossible++;
                    }
                    
                    $total = $maxPossible * $limit;

                } else {
                    
                    $this->session->newQuery();
                    $sqlCount = "SELECT COUNT(*) AS total FROM ".$table." WHERE round = '".$round."'";
                    $total = $this->pdo->query($sqlCount)->fetch(PDO::FETCH_OBJ)->total;
                    
                    $maxPossible = ceil($total / $limit);
                }

                if($page > $maxPossible){
                    $this->system->handleError('Invalid page.', $redirect);
                }
                
                if(!file_exists('html/fame/'.$round.'_'.$pathName.'_'. ($page - 1) .'.html') && !$top){
                    $this->system->handleError('Ops! We could not find this file :/ Please, try again in a few minutes.', $redirect);
                }

?>
                                <div style="padding-top:5px;">
                                    <center>
                                        <span style="font-size: 1.5em;"><?php echo $title; ?></span>
                                    </center>
                                </div><br/>
                                <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                    <thead>
                                        <tr>
<?php
foreach($th as $thName){
?>
                                    <th><?php echo _($thName); ?></th>
<?php
}
?>
                                        </tr>
                                    </thead>
                                    <tbody>                            
<?php                

                require 'html/fame/'.$round.'_'.$pathName.'_'. ($page - 1) .'.html';

?>
                                    </tbody>
                                </table>
                                <a href="<?php echo $redirect; ?>" class="btn btn-default btn-success">Back to Hall of Fame</a><br/>
<?php
                
      
                break;
            case 'round':

                require '/var/www/classes/Storyline.class.php';
                $storyline = new Storyline();

                $curRound = $storyline->round_current();

                $error = '';
                if($this->system->issetGet('round')){
                    
                    if($_GET['round'] == 'all'){
                        
                        $table = ' hist_users_all ';
                        $extraSelect = '';
                        $where = '';
                        
                        header("Location:stats");
                        
                    } else {
                        
                        $roundInfo = $this->system->verifyNumericGet('round');
                        
                        if($roundInfo['IS_NUMERIC'] == 1){
                            
                            if($curRound == 1 || $roundInfo['GET_VALUE'] == $curRound){

                                header("Location:stats");
                                exit();
                                
                            } else {
                                
                                if($roundInfo['GET_VALUE'] > 0 && $roundInfo['GET_VALUE'] < $curRound){
                                    
                                    $extraSelect = ', bestSoft, bestSoftVersion ';
                                    $table = 'hist_users';
                                    $where = ' WHERE hist_users.round = '.$roundInfo['GET_VALUE'].' ';
                                    
                                } else {
                                    $error = 'Invalid round.';
                                }
                                
                            }
                                                        
                        } else {
                            $error = 'Invaalid get.';
                        }
                        
                    }
                    
                } else {
                    
                    $extraSelect = ', clanID ';
                    $table = 'hist_users_current';
                    $where = '';
                    
                }
                
                if($error != ''){
                    
                    $this->session->addMsg($error, 'error');
                    header("Location:stats");
                    
                    exit();
                    
                }
                

                ?>

                <br/>
                <form action="" method="GET">
                    <center><span style="font-size: 1.3em; font-family: cursive;">Displaying ranking of round 1 (current) - Change to: </span>
                    <select name="round">

                        <option value="all"> All-time stats</option>
                        <option value="<?php echo $curRound; ?>">Round <?php echo $curRound; ?> (Current) &nbsp;</option>
                        
                        <?php

                        for($i=1;$i<$curRound;$i++){
                            
                            ?>
                        
                            <option value="<?php echo $i; ?>">Round <?php echo $i; ?>&nbsp;</option>
                        
                            <?php
                            
                        }
                        
                        ?>
                        
                    </select>
                    <input type="submit" value="Change"></center>
                </form><br/><br/>

                <?php
                
                $this->session->newQuery();
                $sqlCount = "SELECT rank FROM ranking_user";
                $count = $this->pdo->query($sqlCount)->fetchAll();

                $total = count($count);

                self::verifyLimit($queryLimit, $total, $get);
                
                $this->session->newQuery();
                $sqlQuery = "SELECT userID, user, reputation, age, clanName, timePlaying, missionCount, hackCount, ddosCount, ipResets, moneyEarned, moneyTransfered, moneyHardware, moneyResearch ".$extraSelect."
                            FROM ".$table."
                            ".$where."    
                            ORDER BY id ASC
                            LIMIT $queryLimit";
                $sqlInfo = $this->pdo->query($sqlQuery);

                $studyLimit = explode(",", $queryLimit);
                $i = $studyLimit['0'];                
                
                ?>
                
                <table id="hor-zebra" summary="List" border="1">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Reputation</th>
                            <th>Clan</th>
                            <th>Servers</th>
                            <th>Age (days)</th>
                            <th>Time Playing</th>
                            <th>IP resets</th>
                            <th>Missions completed</th>
                            <th>Hack count</th>
                            <th>DDoS count</th>
                            <th>Servers used to DDoS</th>
                            <th>Total DDoS power</th>
                            <th>Avg DDoS power</th>
                            <th>Money research</th>
                            <th>Money hardware</th>
                            <th>Money transfered</th>
                            <th>Money earned</th>
                        </tr>
                    </thead>
                    <tbody>
                                                   
                    
                <?php
                                                    
                $g = 0;
                while($rankInfo = $sqlInfo->fetch(PDO::FETCH_OBJ)){
        
                    $g++;
                    
                    if($g%2==1){
                        $str = ' class="odd"';
                    } else {
                        $str = '';
                    }
                                        
                    $i++;
                    
                    if($str == ' class="odd"'){
                        $outBg = '#e2e2e2';
                    } else {
                        $outBg = '#F5F5F5';
                    }
                    
                    if($rankInfo->userid == $_SESSION['id']){
                        $str = ' style="background: #BCFFCC"';
                        $outBg = '#BCFFCC';
                    }
                           
                    $timePlayingHour = floor($rankInfo->timeplaying / 60);
                    $timePlayingMin = ceil($rankInfo->timeplaying % 60);
                    
                    $timePlaying = $timePlayingHour.'h'.$timePlayingMin.'m';

                    $link = '';
                    $linkEnd = '';
                    if(isset($rankInfo->clanid)){
                        if($rankInfo->clanid != 0){
                            
                            $link = '<a href="clan?id='.$rankInfo->clanid.'">';
                            $linkEnd = '</a>';
                            
                        }
                    }

                    ?>
                        
                        <tr<?php echo $str; ?> onmouseover="style.backgroundColor='#CCFFCC';" onmouseout="style.backgroundColor='<?php echo $outBg; ?>';">

                            <td><?php echo $i; ?></td>
                            <td><a href="profile?id=<?php echo $rankInfo->userid; ?>"><?php echo $rankInfo->user; ?></a></td>
                            <td><?php echo $rankInfo->reputation; ?></td>
                            <td><?php echo $link.$rankInfo->clanname.$linkEnd; ?></td>  
                            <td>to do</td>
                            <td><?php echo $rankInfo->age; ?></td>
                            <td><?php echo $timePlaying; ?></td>
                            <td><?php echo $rankInfo->ipresets; ?></td>
                            <td><?php echo $rankInfo->missioncount; ?></td>
                            <td><?php echo $rankInfo->hackcount; ?></td>
                            <td>to do</td>
                            <td><?php echo $rankInfo->ddoscount; ?></td>
                            <td>to do</td>
                            <td>to do</td>
                            <td><?php echo $rankInfo->moneyresearch; ?></td>
                            <td><?php echo $rankInfo->moneyhardware; ?></td>
                            <td><?php echo $rankInfo->moneytransfered; ?></td>
                            <td><?php echo $rankInfo->moneyearned; ?></td>
                            
                        </tr>
                    
                    <?php    
                    
                }            
                
                echo "</tbody></table>";                
                
                break;
            case 'roundPast':
                die("ToDoPagination");
                break;
            default:
                die("configurar esquema personalizado de paginação");
                break;
        }

        $this->total = $total;
        
    }

    public function showPages($limit, $get) {

        $page = self::getCurrentPage($get);
        $varInfo = self::varInfo($page, $this->total, $limit);

    
?>
                                <br/>
<?php



        $htmlMaxBefore = 14;
        $htmlMaxAfter = 2;
        
        $curPage = '1';

        if ($varInfo['TOTAL_PAGES'] > '1') {

            ?>

            <div class="pagination alternate">
                <ul>                

            <?php            
            
            $prevDisabled = '';
            $prevLink = '?'.$get.'='.$varInfo['PREV'];
            
            if ($varInfo['PREV'] == '0') {
                $prevDisabled = ' class="disabled"';
                $prevLink = '#';
            }
            
            ?>
               
            <li <?php echo $prevDisabled; ?>><a href="<?php echo $prevLink; ?>" class="prevnext"><?php echo _('Previous'); ?></a></li>
                
            <?php
            
            for ($i = 1; $i <= $varInfo['TOTAL_PAGES']; $i++) {
                
                if($i == $page){

                    $curPage = $i;
                    echo "<li class=\"active\"><a href=\"#\">$i</a></li>";

                } else {
                
                    if($varInfo['TOTAL_PAGES'] > $htmlMaxBefore + $htmlMaxAfter){

                            //antes
                            if($i < $htmlMaxBefore){

                                echo "<li><a href=\"?$get=" . ($i) . "\">$i</a></li>";

                            } elseif($i == $htmlMaxBefore){

                                echo "<li><a href=\"?$get=" . ($i) . "\">$i</a>...</li>";

                            } elseif($varInfo['TOTAL_PAGES'] - $i < $htmlMaxAfter){

                                echo "<li><a href=\"?$get=" . ($i) . "\">$i</a></li>";

                            }

                    } else {

                        echo "<li><a href=\"?$get=" . ($i) . "\">$i</a></li>";
                        
                    }
                
                }

            }

            $nextLink = '?'.$get.'='.$varInfo['NEXT'];
            $nextDisabled = '';
            
            if ($varInfo['NEXT'] > $varInfo['TOTAL_PAGES']) {
                $nextDisabled = ' class="disabled"';
                $nextLink = '#';
            }
            
            ?>

            <li <?php echo $nextDisabled; ?>><a href="<?php echo $nextLink; ?>"><?php echo _('Next'); ?></a></li>

            <?php

            ?>
                
                </ul>
            </div>                
                
            <?php
            
        }

    }
    
}

?>