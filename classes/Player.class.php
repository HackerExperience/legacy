<?php

class Player {
    protected $id;
    private $name;
    protected $gameIP;
    private $curRound;
    
    public $session;
    protected $pdo;
    protected $npc;
    private $forum;
    private $storyline;
    
    public function __construct($id = ''){

        require_once '/var/www/classes/Session.class.php';
        require_once '/var/www/classes/NPC.class.php';
        $this->session = new Session();
        $this->pdo = PDO_DB::factory();
        $this->npc = new NPC();

    }

    public function handlePost(){
        
        $system = new System();
        
        $postRedirect = 'index';
        
        if(!isset($_POST['act'])){
            $system->handleError('Invalid POST data.', $postRedirect);
        }

        $act = $_POST['act'];
        
        switch($act){
            case 'changepwd':
                
                $pwdInfo = self::pwd_info();
                
                if($pwdInfo['PRICE'] == NULL){
                    
                    if($pwdInfo['NEXT_RESET'] != 0){
                        $system->handleError('You can not reset your password now.', $postRedirect);
                    }

                    $acc = 0;
                    
                } else {
                    
                    if(!isset($_POST['acc'])){
                        $system->handleError('Missing account information.', $postRedirect);
                    }
                    
                    $acc = $_POST['acc'];
                    
                    if(!ctype_digit($acc)){
                        $system->handleError('Invalid bank account.', $postRedirect);
                    }
                    
                    require '/var/www/classes/Finances.class.php';
                    $finances = new Finances();

                    if($finances->totalMoney() < $pwdInfo['PRICE']){
                        $system->handleError('Not enough money.', $postRedirect);
                    }        
        
                    $accInfo = $finances->bankAccountInfo($acc);
                    
                    if($accInfo['0']['exists'] == '0'){
                        $system->handleError('Invalid bank account.', $postRedirect);
                    }

                    $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                    if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                        $system->handleError('Invalid bank account.', $postRedirect);
                    }
                                        
                }

                require '/var/www/classes/Process.class.php';
                $process = new Process();
                
                if($process->newProcess($_SESSION['id'], 'RESET_PWD', '', 'local', '', $acc, '', 0)){

                    $this->session->addMsg('Process to reset password started.', 'notice');
                    
                    header("Location:processes");
                    exit();

                } else {

                    $this->session->addMsg('There already is a password reset process.', 'error');

                    $pid = $process->getPID($_SESSION['id'], 'RESET_PWD', '', 'local', '', $acc, '', 0);
                    header("Location:processes?id=".$pid);
                    exit();

                }

                break;
            default:
                $system->handleError('Invalid POST data.', $postRedirect);
                break;
        }
        
        header("Location:".$postRedirect);
        exit();
        
    }
    
    public function playerIDByUser($user){
    die("WTF?");   //DEPRECATED
        $return = Array();
        
        $sql = "SELECT id FROM users WHERE login = ? LIMIT 1";
        $query = $this->pdo->query($sql)->fetchAll();
        
        if(count($query) == 1){
            $return['PLAYER_ID'] = $query['0']['id'];
        } else {
            $return['PLAYER_ID'] = 0;
        }
        
        return $return;
        
    }
    
    public function verifyID($uid){

        if(is_int($uid) || ctype_digit($uid)){

            if(isset($_SESSION['id'])){            
                if($uid == $_SESSION['id']) return true;
            }
            
            $this->session->newQuery();
            $sqlSelect = "SELECT COUNT(*) AS total FROM users WHERE id = $uid LIMIT 1";
            $total = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;

            if ($total == '1') {
                return TRUE;
            } else {
                return FALSE;
            }

        }

    }

    public function getPlayerInfo($uid){

        $this->session->newQuery();
        $sqlSelect = "SELECT COUNT(*) AS total, login, gameIP, homeIP, gamePass, email FROM users WHERE id = $uid LIMIT 1";
        $data = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);

        if($data->total == 0){
            exit();
        }
        
        return $data;

    }

    public function unsetPlayerLearning(){

        $uid = $_SESSION['id'];
        
        $sql = "DELETE FROM users_learning WHERE userID = $uid";
        $this->pdo->query($sql);
        
    }
    
    public function setPlayerLearning($cid){
        
        $uid = $_SESSION['id'];
        
        $sql = "INSERT INTO users_learning (userID, learning) VALUES ('".$uid."', '".$cid."')";
        $this->pdo->query($sql);
        
    }
    
    public function playerLearning(){
        
        $uid = $_SESSION['id'];
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total, learning FROM users_learning WHERE userID = $uid LIMIT 1";
        $learningInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        if($learningInfo->total == 1){
            return $learningInfo->learning;
        }
        
        return 0;
        
    }
    
    public function getIDByIP($ip, $pcType){
        
        $existe = '1';
        if($pcType != 'VPC' && $pcType != 'NPC'){

            $this->session->newQuery();
            $sqlSelect = "SELECT id FROM npc WHERE npcIP = '".$ip."' LIMIT 1";
            $data = $this->pdo->query($sqlSelect)->fetchAll();

            $pcType = 'NPC';

            if(count($data) == '0'){

                $this->session->newQuery();
                $sqlSelect = "SELECT id FROM users WHERE gameIP = '".$ip."' LIMIT 1";
                $data = $this->pdo->query($sqlSelect)->fetchAll();

                if(count($data) == '0'){
                    //unset($_SESSION['CUR_IP']);
                    $existe = '0';
                }

                $pcType = 'VPC';

            }

        } else {

            if($pcType == 'VPC'){
                $sql = 'SELECT id FROM users WHERE gameIP = '.$ip.' LIMIT 1';
            } else {
                $sql = 'SELECT id FROM npc WHERE npcIP = '.$ip.' LIMIT 1';
            }
            
            $this->session->newQuery();
            $data = $this->pdo->query($sql)->fetchAll();
            if(count($data) == 0){
                $existe = 0;
            }
        
        }

        if($existe == '1'){

            $data['0']['existe'] = '1';

        } else {

            $data = Array();
            $data['0']['existe'] = '0';

        }

        if($pcType == 'VPC'){

            $data['0']['pctype'] = 'VPC';

        } else {

            $data['0']['pctype'] = 'NPC';

        }

        return $data;

    }

    public  function issetBankAccount($uid, $bankID){

	$this->session->newQuery();
        $sqlSelect = "SELECT id FROM bankAccounts WHERE bankID = $bankID AND bankUser = $uid LIMIT 1";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '1'){

            return TRUE;

        } else {

            return FALSE;

        }

    }

    public function getBankInfo($uid, $bankID){

	$this->session->newQuery();
        $sqlSelect = "SELECT bankAcc, bankPass, cash FROM bankAccounts WHERE bankID = $bankID AND bankUser = $uid LIMIT 1";
        $data = $this->pdo->query($sqlSelect)->fetchAll();

        if(count($data) == '1'){

            return $data;

        } else {

            die("contact admin");

        }

    }
    
    public function createBankAccount($bankID, $bankAcc, $bankPass){

	$this->session->newQuery();
        $sqlQuery = "INSERT INTO bankAccounts (id, bankAcc, bankID, bankPass, bankUser, cash, dateCreated) VALUES ('', ?, ?, ?, ?, '0', NOW())";
        $sqlReg = $this->pdo->prepare($sqlQuery);
        $sqlReg->execute(array($bankAcc, $bankID, $bankPass, $_SESSION['id']));

        if($sqlReg->rowCount() == '1'){

            return TRUE;

        } else {

            return FALSE;

        }

    }

    public function issetUser($user){

        $sql = 'SELECT COUNT(*) AS total FROM users WHERE login = :user LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':user' => $user));
        $total = $stmt->fetch(PDO::FETCH_OBJ)->total;

        if($total == '1'){
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public function getIDByUser($user){

        $this->session->newQuery();

        $sql = 'SELECT id FROM users WHERE login = :user LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':user' => $user));
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        return $data->id;

    }

    public function countLoggedInUsers(){

        $sql = "SELECT COUNT(*) AS tUsers FROM users_online";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->tusers;

    }
    
    private function over_show($type){
        
        switch($type){
            
            case 'myinfo':
                
                $sql = "SELECT COUNT(*) AS total, rank, user, reputation, clanName, bestSoft, bestSoftVersion, hackCount, ddosCount
                        FROM hist_users
                        WHERE userID = '".$_SESSION['id']."' AND round = '". ($this->curRound - 1) ."'";
                $userInfo = $this->pdo->query($sql)->fetchAll();
                
                if(sizeof($userInfo) > 0){
                
                    require '/var/www/classes/PC.class.php';
                    require '/var/www/classes/Clan.class.php';
                    
                    $software = new SoftwareVPC();
                    $clan = new Clan();
                  
                    $reputation = number_format($userInfo[0]['reputation']).' <span class="small">Ranked #'.number_format($userInfo[0]['rank']).'</span>';
                    $hackCount = number_format($userInfo[0]['hackcount']);
                    $ddosCount = number_format($userInfo[0]['ddoscount']);
                    
                    if($userInfo[0]['bestsoft'] != NULL){
                        $bestSoftware = $userInfo[0]['bestsoft'].' ('.$software->dotVersion($userInfo[0]['bestsoftversion']*10).')';
                    } else {
                        $bestSoftware = '';
                    }
                    
                    $userClan = 'None';
                    if($clan->playerHaveClan()){
                        $myClan = $clan->getClanInfo($clan->getPlayerClan());
                        if($myClan->name == $userInfo[0]['clanname']){
                            $userClan = '<a href="clan?id='.$myClan->cid.'">'.$myClan->name.'</a>';
                        }
                    }
                    
?>
                    <div class="widget-box">
                        <div class="widget-title">
                            <a href="hardware"><span class="icon"><i class="he16-user"></i></span></a>
                            <h5>My Information</h5>
                        </div>
                        <div class="widget-content nopadding border">
                            <table class="table table-cozy table-bordered table-striped with-check">
                                <tbody>
                                    <tr>
                                        <td><span class="he16-reputation"></td>
                                        <td><span class="item">Reputation</span></td>
                                        <td><?php echo $reputation; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-clan"></td>
                                        <td><span class="item">Clan</span></td>
                                        <td><?php echo $userClan; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-software"></td>
                                        <td><span class="item">Best Software</span></td>
                                        <td><?php echo $bestSoftware; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-internet_hack"></td>
                                        <td><span class="item">Hack Count</span></td>
                                        <td><?php echo $hackCount; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-ddos"></td>
                                        <td><span class="item">DDoS Count</span></td>
                                        <td><?php echo $ddosCount; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
<?php

                } else {
                    echo 'You were not a member of the last round :(';
                }
                
                break;
            case 'roundinfo':
                
                $storyline = new Storyline();
                $roundStats = $storyline->round_stats($this->curRound - 1);
                $doomStats = $storyline->doom_stats($this->curRound - 1);
                
                $totalDooms = sizeof($doomStats);
                $totalResearched = number_format($roundStats->researchcount);
                $totalHacked = number_format($roundStats->hackcount);
                $totalDdos = number_format($roundStats->ddoscount);
                                
                if($doomStats['DOOM']['clanID'] == 0){
                    $doomedBy = '<a href="profile?id='.$doomStats['DOOM']['creatorID'].'">'.self::getPlayerInfo($doomStats['DOOM']['creatorID'])->login.'</a>';
                } else {
                    
                    $clan = new Clan();
                    $clanInfo = $clan->getClanInfo($doomStats['DOOM']['clanID']);
                    $doomedBy = '<a href="clan?id="'.$doomStats['DOOM']['clanID'].'">'.$clanInfo->name.'</a>';
                    $doomedBy .= ' <span class="small nomargin">(Released by <a href="profile?id='.$doomStats['DOOM']['creatorID'].'">'.self::getPlayerInfo($doomStats['DOOM']['creatorID'])->login.'</a>)</span>';
                }
                
?>
                    <div class="widget-box">
                        <div class="widget-title">
                            <a href="hardware"><span class="icon"><i class="he16-charts"></i></span></a>
                            <h5>Last Round Details</h5>
                        </div>
                        <div class="widget-content nopadding border">
                            <table class="table table-cozy table-bordered table-striped with-check">
                                <tbody>
                                    <tr>
                                        <td><span class="he16-doom_failed"></td>
                                        <td><span class="item">Total Doom Attempts</span></td>
                                        <td><?php echo $totalDooms; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-doom"></td>
                                        <td><span class="item">Internet was doomed by</span></td>
                                        <td><?php echo $doomedBy; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-research"></td>
                                        <td><span class="item">Total Softwares Researched</span></td>
                                        <td><?php echo $totalResearched; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-internet_hack"></td>
                                        <td><span class="item">Total Hacked IPs</span></td>
                                        <td><?php echo $totalHacked; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-ddos"></td>
                                        <td><span class="item">Total DDoS Attacks</span></td>
                                        <td><?php echo $totalDdos; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
<?php
                
                break;
            case 'users':
            case 'clans':
                
                if($type == 'users'){
                
                    $title = 'Best Players';
                    $pathName = 'user';
                    $th = Array(
                        '0' => '#',
                        '1' => 'User',
                        '2' => 'Reputation',
                        '3' => 'Best Software',
                        '4' => 'Clan'
                    );
                
                } else {

                    $title = 'Best Clans';
                    $pathName = 'clan';
                    $th = Array(
                        '0' => '#',
                        '1' => 'Clan',
                        '2' => 'Power',
                        '3' => 'Win / Losses',
                        '4' => 'Members',
                    );

                }
                
?>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><i class="he16-rank_user"></i></span>
                                            <h5><?php echo $title; ?></h5>
                                        </div>
                                        <div class="widget-content nopadding border">
                                            <table class="table table-cozy table-bordered table-striped table-hover with-check">
                                                <thead>
                                                    <tr>
<?php
foreach($th as $thName){
?>
                                                        <th><?php echo $thName; ?></th>
<?php
}
?>
                                                    </tr>
                                                </thead>
                                                <tbody>
<?php
require 'html/fame/'. ($this->curRound - 1) .'_'.$pathName.'_preview.html';
?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

<?php

        }
 
    }
    
    public function showGameOver(){
        
        require '/var/www/classes/Storyline.class.php';
        $storyline = new Storyline();
        
        $this->curRound = $storyline->round_current();        

?>

                <div class="span12">
                    <div class="alert alert-error">
                        <center>
                            <strong>The round is over!</strong> 
                            The next one starts in <?php echo $storyline->round_timeToStart(); ?>
                        </center>
                    </div>
                    
                    <div class="widget-box">
                        <div class="row-fluid">
                            <div class="widget-content padding noborder">

                                <div class="span5">

                                    <?php
                                    self::over_show('myinfo');
                                    ?>

                                </div>

                                <div class="span7">

                                    <?php
                                    self::over_show('roundinfo');
                                    ?>                                    

                                </div>
                            </div>
                        </div>
                        <div class="row-fluid">
                            <div class="widget-content padding noborder">

                                    <div class="span6">

                                        <?php
                                        self::over_show('users');
                                        ?>

                                    </div>

                                    <div class="span6">

                                        <?php
                                        self::over_show('clans');

                                        ?>                                    

                                    </div>
                            </div>
                        </div>
                        <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>
                    </div>
                    
                <div class="row-fluid">

                    <div class="widget-box">

                        <div class="widget-title">
                            <span class="icon"><i class="fa fa-arrow-right"></i></span>
                            <h5>Forum Updates</h5>
                        </div>

                        <div class="widget-content padding noborder">

                            <div class="span7">

                                <?php
                                self::forum_show('recent_comments');
                                ?>

                            </div>

                            <div class="span5">

                                <?php
                                self::forum_show('recent_posts');

                                self::forum_show('announcements');

                                ?>                                    

                            </div>

                        </div>

                    <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>

                </div>

        <?php
        
        $this->session->newQuery();
        $sql = "SELECT users.gameIP, users.gamePass
                FROM users
                WHERE users.id = '".$_SESSION['id']."'
                LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        ?>
<script>

    var indexdata = {
        ip: '<?php echo long2ip($data->gameip); ?>',
        pass: '<?php echo $data->gamepass; ?>',
        up: '<?php echo self::ip_uptime(); ?>',
        chg: '<?php echo _('change'); ?>'
    };

</script>
        <?php

        
    }
    
    public function showIndex(){

        //require '/var/www/classes/Forum.class.php';
        //$this->forum = new Forum();

        require_once '/var/www/classes/Storyline.class.php';
        $this->storyline = new Storyline();     
        
        ?>

                    <div class="widget-box">
                        <div class="widget-title">
                            <span class="icon"><span class="he16-monitor"></span></span>
                            <h5><?php echo _("Control Panel"); ?></h5>
                        </div>
                        <div class="widget-content padding noborder">
                            <div class="span5">
        
        <?php
        self::controlpanel_show('hardware');
        ?>
                                
                            </div>

                            <div class="span7">
                                
        <?php
        self::controlpanel_show('userinfo');
        ?>
                                
                            </div>
                            
                            <div class="row-fluid">

                                <div class="span7">
                                    
        <?php
        self::controlpanel_show('news');
        ?>
                                        
                                    <div class="row-fluid">

                                        <div class="span6">
                                    
        <?php
        self::controlpanel_show('fbi');
        ?>

                                        </div>
                                   
                                    
                                        <div class="span6">
                                            
        <?php
        self::controlpanel_show('round');
        ?>     
                                            
                                        </div>
                                        
                                    </div>
                                                                        
                                </div>
                        
                                <div class="span5">
                                
        <?php
        self::controlpanel_show('top10');
        ?>     
                                
                                </div>
                                
                            </div>
                            
                        </div>
                        
                        <div style="clear: both;" class="nav nav-tabs"></div>
                        
                    </div>
                    
                    <div class="row-fluid">

                        <div class="widget-box">

                            <div class="widget-title">
                                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                                <h5><?php echo _("Forum Updates"); ?></h5>
                            </div>
          
                            <div class="widget-content padding noborder">

                                <div class="span7">        
                                    
                                    <?php
                                    self::forum_show('recent_comments');
                                    ?>
                                    
                                </div>
                                    
                                <div class="span5">
                                
                                    <?php
                                    self::forum_show('recent_posts');
                                    
                                    self::forum_show('announcements');
                                    
                                    ?>                                    
                                    
                                </div>
                                
                            </div>
                            
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
     
        <?php

        $this->session->newQuery();
        $sql = "SELECT users.gameIP, users.gamePass
                FROM users
                WHERE users.id = '".$_SESSION['id']."'
                LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        ?>

                        <script>

                            var indexdata = {
                                ip: '<?php echo long2ip($data->gameip); ?>',
                                pass: '<?php echo $data->gamepass; ?>',
                                up: '<?php echo self::ip_uptime(); ?>',
                                chg: '<?php echo _('change'); ?>'
                            };

                        </script>
                        <span id="modal"></span>


        <?php

    }
    
    public function forum_show($page){
        
        if(!$this->forum){
            require_once '/var/www/classes/Forum.class.php';
            $this->forum = new Forum();
        }
        
        switch($page){
            
            case 'recent_comments':
                
                ?>
                        

                                    <div class="widget-box">

                                        <div class="widget-title">
                                            <span class="icon"><i class="he16-comments"></i></span>
                                            <h5><?php echo _("Recent Comments"); ?></h5>
                                        </div>

                                        <div class="widget-content padding border">

<?php
     $this->forum->showPosts('recent_comments');                                       
?>

                                        </div>  

                                    </div>
                        
                <?php
                
                break;
            case 'recent_posts':
                
                ?>
                

                                    <div class="widget-box">

                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-directions"></span></span>
                                            <h5><?php echo _("Recent Posts"); ?></h5>
                                        </div>

                                        <div class="widget-content padding border">

<?php
     $this->forum->showPosts('recent_posts');                                       
?>

                                        </div>  

                                    </div>

                    <?php
                
                break;
            case 'announcements':
                
                ?>
                        
                                    <div class="widget-box">

                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-announcements"></span></span>
                                            <h5>Announcements</h5>
                                            <span class="label label-important">New</span>
                                        </div>

                                        <div class="widget-content padding border">

<?php
     $this->forum->showPosts('announcements');                                       
?>

                                        </div>  

                                    </div>                           
                        
                <?php
                
                break;
            
        }
        
    }
    
    public function isAdmin($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM users_admin WHERE userID = '".$uid."' LIMIT 1";
        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total > 0){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function controlpanel_show($page){

        switch($page){
            
            case 'hardware':
                
                    require_once '/var/www/classes/PC.class.php';
                    $hardware = new HardwareVPC();
                    
                    $hardwareInfo = $hardware->getHardwareInfo($_SESSION['id'], '');
                    
                    $cpu = round($hardwareInfo['CPU']/1000, 1) .' GHz';
                    $hdd = round($hardwareInfo['HDD']/1000, 1) . ' GB';
                    if($hdd < 1){
                        $hdd = round($hardwareInfo['HDD']).' MB';
                    }
                    $ram = round($hardwareInfo['RAM']/1000, 1).' GB';
                    if($ram < 1){
                        $ram = round($hardwareInfo['RAM']).' MB';
                    }
                    $net = round($hardwareInfo['NET'], 1);
                    if($net == 1000){
                        $net = '1 Gbit/s';
                    } else {
                        $net .= ' Mbit/s';
                    }
                    $xhd = $hardwareInfo['XHD']/1000;
                    if($xhd == 0){
                        $xhd = 'None';
                    } else {
                        $xhd .= ' GB';
                    }
                    
                    ?>
                    
                    <div class="widget-box">

                        <div class="widget-title">
                            <a href="hardware"><span class="icon"><i class="he16-server"></i></span></a>
                            <h5><?php echo _("Hardware Information"); ?></h5>
                        </div>

                        <div class="widget-content nopadding border">

                            <table class="table table-cozy table-bordered table-striped table-fixed with-check">
                                <tbody>
                                    <tr>
                                        <td><span class="he16-cpu heicon"></span></td>
                                        <td><span class="item"><?php echo _("Processor"); ?></span></td>
                                        <td><?php echo $cpu; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-hdd heicon"></span></td>
                                        <td><span class="item"><?php echo _("Hard Drive"); ?></span></td>
                                        <td><?php echo $hdd; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-ram heicon"></span></td>
                                        <td><span class="item"><?php echo _("Memory"); ?></span></td>
                                        <td><?php echo $ram; ?></td>
                                    </tr>
                                    <tr>
                                        <td><i class="he16-net heicon"></i></td>
                                        <td><span class="item"><?php echo _("Internet"); ?></span></td>
                                        <td><?php echo $net; ?></td>
                                    </tr>
                                    <tr>
                                        <td><span class="he16-xhd heicon"></span></td>
                                        <td><span class="item"><?php echo _("External HD"); ?></span></td>
                                        <td><?php echo _($xhd); ?></td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                    </div>

                <?php    
                
                break;
            case 'userinfo':

                        require_once '/var/www/classes/Clan.class.php';
                        require_once '/var/www/classes/Process.class.php';
                
                        $clan = new Clan();
                        $process = new Process();

                        if($this->session->issetMissionSession()){
                            
                            $mission = '<a class="black" href="missions?id='.$_SESSION['MISSION_ID'].'">';
                            
                            switch($_SESSION['MISSION_TYPE']){

                                case 1:
                                    $mission .= _('Delete software');
                                    break;
                                case 2:
                                    $mission .= _('Steal software');
                                    break;
                                case 3:
                                    $mission .= _('Check bank status');
                                    break;
                                case 4:
                                    $mission .= _('Transfer money');
                                    break;
                                case 5:
                                    $mission .= _('Shutdown Rival');
                                    break;
                                case 50:
                                case 52:
                                case 54:
                                    $mission .= _('Hack the NSA');
                                    break;
                                case 51:
                                case 53:
                                    $mission .= _('Doom the Internet');
                                    break;
                                case 80:
                                case 81:
                                case 82:
                                case 83:
                                    $mission .= _('Tutorial Mission');
                                    break;
                                default:
                                    $mission .= _('Unknown mission');
                                    break;
                                
                            }
                            
                            $mission .= '</a>';
                            
                        } else {
                            $mission = 'None, f';
                        }

                        if($this->session->isInternetLogged()){
                            $ip = long2ip($_SESSION['LOGGED_IN']);
                            $connected = '<a class="black" href="internet?ip='.$ip.'">'.$ip.'</a>';
                        } else {
                            $connected = 'No one';
                        }

                        $war = $master = '';
                        if($clan->playerHaveClan()){
                            $clanInfo = $clan->getClanInfo($clan->getPlayerClan());
                            $clanName = '<a class="black" href="clan?id='.$clanInfo->cid.'">['.$clanInfo->nick.'] '.$clanInfo->name.'</a>';
                            
                            if($clan->getClanOwnerID($clanInfo->cid)->userid == $_SESSION['id']){
                                $master = '<span class="label label-info pull-right">'._('Master').'</span>';
                            }
                            
                            if($clan->clan_inWar($clanInfo->cid)){
                                $war = '<span class="label label-important pull-right" style="margin-right: 3px;">'._('War').'</span>';
                            }
                            
                        } else {
                            $clanName = _('Not a member');
                        }
                        
                        if(self::isPremium()){
                            $labelcolor = 'label-warning';
                            $membership = 'Premium';
                        } elseif(self::isAdmin()){
                            $labelcolor = 'label-important';
                            $membership = _('Staff');
                        } else {
                            $labelcolor = 'label-success';
                            $membership = _('Basic');
                        }
                        
                        
                        $ranking = new Ranking();
               
                        $tasks = '<a class="black" href="processes">'.$process->totalProcesses().'</a>';
                        
?>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><i class="he16-pda"></i></span>
                                            <h5><?php echo _("General Info"); ?></h5>
                                            <span class="hide-phone label <?php echo $labelcolor; ?>"><?php echo _($membership); ?></span>                                                    
                                        </div>
                                        <div class="widget-content nopadding border">
                                            <table class="table table-cozy table-bordered table-striped with-check">
                                                <tbody>
                                                    <tr>
                                                        <td><span class="he16-reputation heicon"></span></td>
                                                        <td><span class="item"><?php echo _("Reputation"); ?></span></td>
                                                        <td>
                                                            <?php echo number_format($ranking->exp_getTotal($_SESSION['id'], 1)); ?>
                                                            <span class="small"><?php echo _("Ranked"); ?> #<?php echo $ranking->getPlayerRanking($_SESSION['id'], 1); ?></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="he16-taskmanager heicon"></span></td>
                                                        <td><span class="item"><?php echo _("Running tasks"); ?></span></td>
                                                        <td><?php echo $tasks; ?></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><i class="he16-world heicon"></i></td>
                                                        <td><span class="item"><?php echo _("Connected to"); ?></span></td>
                                                        <td><?php echo _($connected); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><i class="he16-missions heicon"></i></td>
                                                        <td><span class="item"><?php echo _("Mission"); ?></span></td>
                                                        <td><?php echo _($mission); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><i class="he16-clan heicon"></i></td>
                                                        <td><span class="item">Clan</span></td>
                                                        <td><?php echo $clanName; ?>
                                                            <?php echo $master.$war; ?>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
<?php    
                
                break;
            case 'top10':

                ?>
    
                            <div class="widget-box">

                                <div class="widget-title">
                                    <span class="icon"><span class="he16-ranking"></span></span>
                                    <h5><?php echo _("Top 7 users"); ?></h5>
                                    <a href="ranking"><span class="hide-phone label"><?php echo _("View ranking"); ?></span></a>
                                </div>

                                <div class="widget-content nopadding border">
    
                                    <table class="table table-bordered table-striped with-check">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th><?php echo _("User"); ?></th>
                                                <th><?php echo _("Reputation"); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                    
                <?php
                
                
                $this->session->newQuery();
                $sql = "SELECT ranking_user.userID, users.login, cache.reputation
                        FROM ranking_user 
                        INNER JOIN users
                        ON ranking_user.userID = users.id
                        LEFT JOIN cache
                        ON ranking_user.userID = cache.userID
                        WHERE rank > -1
                        ORDER BY ranking_user.rank ASC
                        LIMIT 7";
                $data = $this->pdo->query($sql)->fetchAll();

                for($i=0;$i<sizeof($data);$i++){
                    
                    ?>

                                            <tr>
                                                <td><?php echo $i+1; ?></td>
                                                <td><a href="profile?id=<?php echo $data[$i]['userid']; ?>"><?php echo $data[$i]['login']; ?></a></td>
                                                <td><?php echo number_format($data[$i]['reputation']); ?></td>
                                            </tr>
                                    
                    <?php

                }
                
                if($i == 0){
                    
                    //todos os usuários tem rank -1
                    //(situação única no início do round)
                    
                    $this->session->newQuery();
                    $sql = "SELECT ranking_user.userID, users.login, cache.reputation
                            FROM ranking_user 
                            INNER JOIN users
                            ON ranking_user.userID = users.id
                            LEFT JOIN cache
                            ON ranking_user.userID = cache.userID
                            ORDER BY ranking_user.rank ASC
                            LIMIT 7";
                    $data = $this->pdo->query($sql)->fetchAll();

                    for($i=0;$i<sizeof($data);$i++){

                        ?>

                                                <tr>
                                                    <td><?php echo $i+1; ?></td>
                                                    <td><a href="profile?id=<?php echo $data[$i]['userid']; ?>"><?php echo $data[$i]['login']; ?></a></td>
                                                    <td><?php echo number_format($data[$i]['reputation']); ?></td>
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
                    
                break;
            case 'fbi':
 
                $this->storyline->fbi_display();
            
                break;
            case 'news':
                
                require '/var/www/classes/News.class.php';
                $news = new News();
                
                
                
                ?>
                    
                        <div class="widget-box">

                            <div class="widget-title">
                                <span class="icon"><i class="he16-news"></i></span>
                                <h5><?php echo _("News"); ?></h5>
                                <a href="news"><span class="label"><?php echo _("View all, f"); ?></span></a>                                                    
                            </div>                                    

                            <div class="widget-content nopadding border">
<?php

$news->listIndex(3);

?>
                            </div>

                        </div>
                    
                <?php
                
                break;
            case 'announcements':
                
                ?>
                    <span class="item" style="letter-spacing: 0.6pt;"><b><center><a href="forum" style="color:#000;"><?php echo _("Announcements"); ?></a></center></b></span><br/>
                <?php               

                $this->forum->showPosts('announcements');

                break;
            case 'latest':        

                ?>
                    <span class="item" style="letter-spacing: 0.6pt;"><b><center><a href="forum" style="color:#000;"><?php echo _("Latest Forum Posts"); ?></a></center></b></span><br/>
                <?php

                $this->forum->showPosts('latest');         
                
                ?>
                
                    <center><span style="font-size: x-small; margin-left: 20px;"><a>(<?php echo _("View forum"); ?>)</a></span></center>
                    
                <?php
                
                break;
            case 'round':

                $this->storyline->round_display();
                    
                break;
            
        }
        
    }
    
    public function ip_uptime(){
        
        $this->session->newQuery();
        $sql = "SELECT TIMESTAMPDIFF(SECOND, lastIpReset, NOW()) AS uptime FROM users_stats WHERE uid = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
  
        $reset = new DateTime('now');
        $reset->modify('-'.$data->uptime.' seconds');
        $now = new DateTime('now');
        $diff = $now->diff($reset);
        
        $plural = 0;
        
        $waitTime = '';
        $str = '';
        
        if($diff->d > 0){
            
            $waitTime = $diff->d;
            
            $str = sprintf(ngettext("%d day", "%d days", $diff->d), $diff->d);

            if($diff->h == 0 && $diff->i == 0){
                return $str;
            } else {
                $str .= ' '._('and').' ';
            }

        }
        
        if($diff->h > 0){
            
            $hourStr = sprintf(ngettext("%d hour", "%d hours", $diff->h), $diff->h);
            
            return $str.$hourStr;
            
        } else {
            
            $waitTime .= $diff->i;
            $minStr = sprintf(ngettext("%d minute", "%d minutes", $diff->i), $diff->i);
            
            return $str.$minStr;
            
        }
        
    }
    
    public function ip_info(){
        
        $return = Array();
        
        $this->session->newQuery();
        $sql = "SELECT ipResets, TIMESTAMPDIFF(SECOND, lastIpReset, NOW()) AS uptime FROM users_stats WHERE uid = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
    
        $ipInfo = self::ip_studyPrice($data->ipresets, $data->uptime);
       
        $price = $ipInfo['PRICE'];
       
        if($price > 0){
            
            $now = new DateTime('now');
            
            $reset = new DateTime('now');
            $reset->modify($ipInfo['TIME'] - $data->uptime.' seconds');
            
            $diff = $now->diff($reset);
            
            $plural = 0;
            if($diff->h > 0){
                
                $waitTime = $diff->h;
                
                if($diff->d > 0){
                    $waitTime += $diff->d * 24;
                }
                
                $string = ' hour';
                if($diff->h > 1){
                    $plural = 1;
                }

            } else {

                $waitTime = $diff->i;
                $string = ' minute';
                if($diff->i > 1){
                    $plural = 1;
                }

            }

            $waitTime .= $string;

            if($plural == 1){
                $waitTime .= 's';
            }
            
        } else {
            $waitTime = 0;
        }

        $return['PRICE'] = $price;
        $return['NEXT_RESET'] = $waitTime;
        
        return $return;
        
    }
    
    public function ip_studyPrice($resets, $uptime){
        
        $return = Array();

        if($resets == 0){
            return 0;
        }

        switch($resets){

            case 1:
                $time = 3*3600; //3 hours
                break;
            case 2:
                $time = 6*3600; //6 hours
                break;
            case 3:
                $time = 12*3600; //12 hours
                break;
            case 4:
                $time = 24*3600; //24 hours
                break;
            case 5:
                $time = 48*3600; //48 hours
                break;
            case 6:
                $time = 96*3600; //96 hours
                break;
            default:
                $time = 168*3600; //168 hours (1 semana)
                break;

        }

        if($time <= $uptime){
            return 0;
        }

        $price = 100 + 500*$resets + (pow($resets, 3.8)/10);
        
        if($price > 100000){
            $price = 100000;
        }

        $return['PRICE'] = (int)$price;
        $return['TIME'] = $time;

        return $return;
        
    }
    
    public function pwd_info(){
        
        $return = Array();
        
        $this->session->newQuery();
        $sql = "SELECT pwdResets, TIMESTAMPDIFF(SECOND, lastPwdReset, NOW()) AS lastReset FROM users_stats WHERE uid = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                
        $pwdInfo = self::pwd_studyPrice($data->pwdresets, $data->lastreset);
        
        if($pwdInfo['PRICE'] > 0){
                        
            $now = new DateTime('now');
            
            $reset = new DateTime('now');
            $reset->modify($pwdInfo['WAIT_TIME'] - $data->lastreset.' seconds');
            
            $diff = $now->diff($reset);
            
            $plural = 0;
            if($diff->h > 0){
                
                $waitTime = $diff->h;
                
                if($diff->d > 0){
                    $waitTime += $diff->d * 24;
                }
                
                $str = sprintf(ngettext("%d hour", "%d hours", $waitTime), $waitTime);

            } else {

                $waitTime = $diff->i;

                $str = sprintf(ngettext("%d minute", "%d minutes", $waitTime), $waitTime);
                
            }
            
        } else {
            $waitTime = 0;
            $str = 0;
        }
        
        $return['PRICE'] = $pwdInfo['PRICE'];
        $return['NEXT_RESET'] = $str;
        
        return $return;
        
    }
    
    public function pwd_studyPrice($pwdResets, $lastReset){
        
        $return = Array();
        
        if($pwdResets == 0){
            return 0;
        }
        
        switch($pwdResets){

            case 1:
                $time = 3*3600; //3 hours
                break;
            case 2:
                $time = 6*3600; //6 hours
                break;
            case 3:
                $time = 12*3600; //12 hours
                break;
            case 4:
                $time = 24*3600; //24 hours
                break;
            case 5:
                $time = 48*3600; //48 hours
                break;
            case 6:
                $time = 96*3600; //96 hours
                break;
            default:
                $time = 168*3600; //168 hours (1 semana)
                break;

        }
        
        $price = 50 + 10*$pwdResets + (pow($pwdResets, 3.2))/10;
        
        if($time <= $lastReset){
            return 0;
        }
        
        $return['PRICE'] = (int)$price;
        $return['WAIT_TIME'] = $time;
        
        return $return;
        
    }
    
    public function addReport($errorID, $sql, $varArray){
        
        require 'admin/classes/Reports.class.php';
        $report = new Reports();
        
        if($report->criticalError($errorID)){
            $critical = 1;
        } else {
            $critical = 0;
        }
        
        $report = '<b>Error ID:</b> '.$errorID.'<br/>';
        
        $report .= '<b>User ID:</b> '.$_SESSION['id'].'<br/>';
        
        $report .= '<b>SQL:</b> '.$sql.'<br/>';
        
        $report .= '<b>User variables:</b> <br/>';
        
        if($varArray != ''){
            
            foreach($varArray as $key => $value){
                
                $report .= '<u>'.$key.'</u> : '.$value.'<br/>';
                
            }
            
        }
        
        $report .= '<br/><b>$_SERVER:</b> <br/>';
        foreach($_SERVER as $key => $value){
            
            $report .= '<u>'.$key.'</u> : '.$value.'<br/>';
            
        }
        
        if(isset($_POST)){
            
            $report .= '<br/><b>$_POST:</b> <br/>';
            foreach($_POST as $key => $value){

                $report .= '<u>'.$key.'</u> : '.$value.'<br/>';
                
            }
            
        }
        
        if(isset($_GET)){
            
            $report .= '<br/><b>$_GET:</b> <br/>';
            foreach($_GET as $key => $value){
                
                $report .= '<u>'.$key.'</u> : '.$value.'<br/>';
                
            }
            
        }
        
        echo $report;
        exit();
    }    
    
    public function isPremium($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }

        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM users_premium WHERE id = '".$uid."' LIMIT 1";
        $premium = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($premium == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function isNoob($uid){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM hist_users WHERE userID = '".$uid."' LIMIT 1";
        $previousRound = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($previousRound > 0){
            return FALSE;
        }
        
        $this->session->newQuery();
        $sql = "SELECT exp FROM users_stats WHERE uid = '".$uid."' LIMIT 1";
        $exp = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->exp;
        
        if($exp < 100){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function getProfilePic($uid, $username, $thumbnail = FALSE){
        
        if($uid > 0){
            
            $size = '';
            if($thumbnail){
                $size = 'thumbnail/';
            }
            
            $commonPath = 'images/profile/'.$size.md5($username.$uid).'.jpg';
            
            if(file_exists($commonPath)){
                return $commonPath;
            }

            return 'images/profile/'.$size.'unsub.jpg';
            
        }
        
        switch($uid){
            
            case 0:
                return 'images/profile/tux.png';
            case -1:
                return 'images/profile/tux-ec.png';
            case -2:
                return 'images/profile/tux-fbi.png';
            case -3:
                return 'images/profile/tux-safenet.png';
            case -4:
                return 'images/profile/tux-clan2.png';
            case -5:
                return 'images/profile/tux-clan.png';
            case -6:
                return 'images/profile/tux-social.png';
            case -7:
                return 'images/profile/tux-badges.png';
            case -8:
                return 'images/profile/tux.png';
        }
        
        return 'TODO';
        
    }
    
    public function settings_show(){
        
?>
                    
<div class="span6">

                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-chg_lang"></span></span>
                                <h5><?php echo _('Change language'); ?></h5>
                            </div>
                            <div class="widget-content nopadding">
         
                                <form action="" method="POST" class="form-horizontal">
                                    <div class="control-group">
                                        <div class="controls">
                                            <select id="select-lang" name="lang" placeholder="<?php echo _('Choose new language'); ?>" style="width: 300px;">
                                                <option></option>
                                                <option name="en">English</option>
                                                <option name="pt">Português</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary"><?php echo _('Change'); ?></button>
                                    </div>
                                </form>
                                
                                
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-change_pwd"></span></span>
                                <h5><?php echo _('Change password'); ?></h5>
                            </div>
                            <div class="widget-content nopadding">

                                <form action="" method="POST" class="form-horizontal">
                                    <div class="control-group">
                                            <label class="control-label"><?php echo _('Current password'); ?></label>
                                            <div class="controls">
                                                    <input name="old" type="password" />
                                            </div>
                                    </div>
                                    <div class="control-group">
                                            <label class="control-label"><?php echo _('New password'); ?></label>
                                            <div class="controls">
                                                    <input name="new1" type="password" />
                                            </div>
                                    </div>
                                    <div class="control-group">
                                            <label class="control-label"><?php echo _('Confirm new password'); ?></label>
                                            <div class="controls">
                                                    <input name="new2" type="password" />
                                            </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary"><?php echo _('Change'); ?></button>
                                    </div>
                                </form>
                                
                            </div>
                        </div>
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-del_acc"></span></span>
                                <h5><?php echo _('Delete account'); ?></h5>
                            </div>
                            <div class="widget-content">
                                <?php echo _('We are sad to hear you want to leave. You can delete your account at anytime, however there is no automatic way to do this for now.'); ?>
                                <br/>
                                    <?php echo _('Please drop us an email at ')._('contact@hackerexperience.com')._(' asking us to remove your account. If possible, let us know why you are leaving.'); ?>
                                <br/>
                                <?php echo _('A verification e-mail will be sent to you.'); ?>
                            </div>
                        </div>
                        
                    </div>
                    <div class="span6">
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-premium"></span></span>
                                <h5><?php echo _('Buy premium account'); ?></h5>
                            </div>
                            <div class="widget-content">
                                <?php echo sprintf(_('Hey oh! Let\'s make this real fun. Refeer to the %spremium page%s in order to get detailed information about premium accounts.'), '<a href="premium">', '</a>'); ?>
                                <br/>
                                <?php echo _('*Licks from Phoebe*'); ?><br/><br/>
                                <div class="center">
                                    <img src="images/phoebe2.jpg" width="500">
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    
<?php
        
    }
}

?>
