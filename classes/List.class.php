<?php

class Lists {

    private $pdo;
    private $player;
    private $pagination;
    private $software;
    private $virus;
    private $log;
    private $finances;
    private $session;

    function __construct(){

        require_once '/var/www/classes/Player.class.php';
        require_once '/var/www/classes/Pagination.class.php';
        require_once '/var/www/classes/PC.class.php';
        require_once '/var/www/classes/Process.class.php';
        require_once '/var/www/classes/Finances.class.php';

        $this->pdo = PDO_DB::factory();
        $this->player = new Player();
        $this->pagination = new Pagination();
        $this->software = new SoftwareVPC();
        $this->virus = new Virus();
        $this->log = new LogVPC();
        $this->finances = new Finances();
        $this->session = new Session();

    }
    
    public function handlePost(){
             
        $system = new System();
        
        $postRedirect = 'list';

        if(isset($_POST['act'])){
            
            switch($_POST['act']){
                
                case 'assign':

                    $listID = $_POST['lid'];                    
                    $virusID = $_POST['vid'];
                    
                    if(!ctype_digit($virusID) || !ctype_digit($listID)){
                        $system->handleError('Invalid ID.', $postRedirect);
                    }
                    
                    if(!self::issetID($listID, 1)){
                        $system->handleError('This IP is not on your hacked database.', $postRedirect);
                    }     
                    
                    $listIP = self::getListIPByID($listID);
                    
                    $sql = "SELECT COUNT(*) AS totalList FROM lists WHERE virusID = '".$virusID."' AND id = '".$listID."' LIMIT 1";
                    $totalList = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totallist;
                    
                    if($totalList == 1){
                        $system->handleError('This virus is already assigned to this IP.', $postRedirect);
                    }
                    
                    $sql = "SELECT COUNT(*) AS totalVirus FROM virus WHERE virusID = '".$virusID."' AND installedIp = '".$listIP."' AND installedBy = '".$_SESSION['id']."' AND active = 1 LIMIT 1";
                    $totalVirus = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalvirus;                    
                    
                    if($totalVirus == 0){
                        $system->handleError('This virus does not exists.', $postRedirect);
                    }
                    
                    self::assign($virusID, $listIP, $listID);
                    
                    $this->session->addMsg(sprintf(_('New virus assigned to %s.'), '<strong>'.long2ip($listIP).'</strong>'), 'notice');
  
                    break;
                case 'deleteacc':
                case 'deleteip':
                    
                    $act = $_POST['act'];
                                                      
                    if($act == 'deleteacc'){
                        $postRedirect = 'list?show=bankaccounts';
                        $type = 2;
                        $errMsg = 'This bank account is not in your hacked database.';
                    } else {
                        $postRedirect = 'list';
                        $type = 1;
                        $errMsg = 'This IP is not in your hacked database.';
                    }
                    
                    if(!isset($_POST['id'])){
                        $system->handleError('Missing information.', $postRedirect);
                    }
                    
                    $id = $_POST['id'];
                    
                    if(!ctype_digit($id)){
                        $system->handleError('Invalid information.', $postRedirect);
                    }
             
                    $list = new Lists();
                                                            
                    if(!$list->issetID($id, $type)){
                        $system->handleError($errMsg, $postRedirect);
                    }
                    
                    $sucMsg = 'Bank account removed from list.';
                    if($act == 'deleteip'){
                    
                        $listIP = self::getListIPByID($id);

                        if($this->session->isInternetLogged()){

                            $connectedIP = $_SESSION['LOGGED_IN'];                        
                            if($listIP == $connectedIP){
                                $system->handleError(sprintf(_('Disconnect from %s before.'), long2ip($listIP)), $postRedirect);
                            }

                        }

                        $sucMsg = sprintf(_('%s removed from list.'), '<strong>'.long2ip($listIP).'</strong>');
                        
                    }
                    
                    self::deleteList($id, $type);
                    
                    $this->session->addMsg($sucMsg, 'notice');
                    
                    break;
                case 'collect':
                    
                    $postRedirect .= '?action=collect';
                    
                    if(!isset($_POST['acc'])){
                        $system->handleError('Missing information.', $postRedirect);
                    }
                    
                    $acc = $_POST['acc'];

                    if(!ctype_digit($acc)){
                        $system->handleError('Missing information.', $postRedirect);
                    }
                    
                    $finances = new Finances();

                    $accInfo = $finances->bankAccountInfo($acc);

                    if($accInfo['0']['exists'] == '0'){
                        $system->handleError('BAD_ACC', $postRedirect);
                    }

                    $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                    if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                        $system->handleError('BAD_ACC', $postRedirect);
                    }
                    
                    $software = new SoftwareVPC();
                    $bestCollector = $software->getBestSoftware('11', $_SESSION['id'], 'VPC', '');
                    
                    if($bestCollector['0']['exists'] == 0){
                        $system->handleError('NO_COLLECTOR', 'list?action=collect');
                    }

                    self::collect($acc);
                    
                    header("Location:list?action=collect&show=last");
                    exit();
                    
                    break;
            }
            
        }

        header("Location:".$postRedirect);
        exit();
        
    }
    
    public function assign($virusID, $listIP, $listID){
        
        $this->session->newQuery();
        $sql = "UPDATE virus SET lastCollect = NOW() WHERE virusID = '".$virusID."' AND installedIp = '".$listIP."' AND installedBy = '".$_SESSION['id']."' LIMIT 1";
        $this->pdo->query($sql);

        $this->session->newQuery();
        $sql = "UPDATE lists SET virusID = '".$virusID."' WHERE id = '".$listID."' LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function getListIPByID($id){
        
        $this->session->newQuery();
        $sql = "SELECT ip FROM lists WHERE id = '".$id."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->ip;
        
    }
    
    public function getListIDByIP($ip){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM lists WHERE ip = '".$ip."' AND userID = '". $_SESSION['id']."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;
        
    }
    
    public function listNotification(){
        
        $ipNote = '';
        $pwdNote = '';
        $virNote = '';
                    
        $this->session->newQuery();
        $sql = "SELECT ip, notificationType, virusName FROM lists_notifications WHERE userID = '".$_SESSION['id']."'";
        $data = $this->pdo->query($sql)->fetchAll();
                                
        if(sizeof($data) > 0){
            
            for($i=0;$i<sizeof($data);$i++){
                
                switch($data[$i]['notificationtype']){
                    
                    case 1:
                        
                        $ipNote .= sprintf(_('IP %s isn\'t responding anymore.'), '<strong>['.long2ip($data[$i]['ip']).']</strong>').'<br/>';
                        
                        break;
                    case 2:
                        
                        $pwdNote .= sprintf(_('IP %s changed password.'), '<strong>['.long2ip($data[$i]['ip']).']</strong>').'<br/>';
                        
                        break;
                    case 3:
                        
                        $virNote .= sprintf(_('Virus %s isn\'t responding anymore from IP %s.'), $data[$i]['virusname'], '<strong>['.long2ip($data[$i]['ip']).']</strong>').'<br/>';
                        
                        break;
                    
                }
                                
            }
        
            $this->session->newQuery();
            $sql = "DELETE FROM lists_notifications WHERE userID = '".$_SESSION['id']."' LIMIT ".sizeof($data);
            $this->pdo->query($sql);        
            
        }
        
        if(($ipNote != '') || ($pwdNote != '') || ($virNote != '')){
            echo '<b>'._('Notification').':</b><br/><br/>';
        }
        
        if($ipNote != ''){
            echo '<font color="red">'.$ipNote.'</font>';
        }
        
        if($pwdNote != ''){
            echo '<font color="red">'.$pwdNote.'</font>';
        }
        
        if($virNote != ''){
            echo '<font color="red">'.$virNote.'</font>';
        }

    }
    
    public function addNotification($user, $ip, $type, $virus = ''){
        
        $this->session->newQuery();
        $sql = "INSERT INTO lists_notifications (userID, ip, notificationType, virusName)
                VALUES ('".$user."', '".$ip."', '".$type."', '".$virus."')";
        $this->pdo->query($sql);
                
    }
 
    public function showList(){

        $this->pagination->paginate($_SESSION['id'], 'list', '15', 'page', '1');
        $this->pagination->showPages('15', 'page');
        
        ?>

    </div>
    </div>
    <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>

        <?php

    }
    
    public function showBankList(){
        
        $this->pagination->paginate($_SESSION['id'], 'listBank', '30', 'show=bankaccounts&page', '1');
        $this->pagination->showPages('30', 'show=bankaccounts&page');

        ?>

    </div>
    </div>
    <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>

        <?php        
        
    }
    
    public function show_ddos(){

        $virus = new Virus();
        $software = new SoftwareVPC();
        
        $total = $virus->DDoS_count();

        ?>

        <div class="span6">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><span class="he16-ddos"></span></span>
                    <h5><?php echo _('Launch DDoS attack'); ?></h5>                                                            
                </div>             
                <div class="alert alert-error ">
                    <?php echo _('<b>Atention:</b> Your IP will be logged on <b>all</b> servers used to DDoS.'); ?>
                </div>                           

                <div class="widget-content padding">
<?php
                    
                    if ($total >= 3) {

?>

                        <form class="ddos_form" action="DDoS" method="POST">

                            <div class="control-group">
                                <div class="controls center">
                                    <input type="text" name="ip" placeholder="<?php echo _('IP Address to DDoS'); ?>" style="width: 80%;" autofocus="1"/>
                                </div>
                            </div>
                            
                        <?php
                        $bestBreaker = $software->getBestSoftware('12', $_SESSION['id'], 'VPC', '');

                        if ($bestBreaker['0']['exists'] == '1') {
?>
                            <div class="control-group">
                                <div class="controls center">
                                    <input type="submit" class="btn btn-danger" value="<?php echo _('Launch DDoS!'); ?>">
                                </div>
                            </div>
<?php
                        } else {
?>
                            <center><?php echo _('You need a DDoS Breaker in order to launch attacks.'); ?></center>
                            <script>window.onload = function () {$('.ddos_form').submit(false);}</script>
<?php
                        }

?>
                        </form>
<?php


                    } else {
                        echo _('You must have at least 3 DDoS virus running to perform an attack.');
                    }

                    ?>
                </div>
            </div>
        </div>        
            <div class="span6">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><span class="he16-ddos_list"></span></span>
                        <h5><?php echo _('Running DDoS viruses'); ?></h5>
                        <span class="label label-info"><?php echo $total; ?></span>
                    </div>                
                    <div class="widget-content padding">  
                    <?php

                    if ($total > '0') {

                        $this->session->newQuery();
                        $sql = "SELECT virus_ddos.ddosID, virus_ddos.ip, virus_ddos.ddosName, virus_ddos.ddosVersion 
                                FROM virus_ddos 
                                INNER JOIN lists
                                ON lists.virusID = virus_ddos.ddosID
                                WHERE virus_ddos.userID = '" . $_SESSION['id'] . "' AND virus_ddos.active = 1";
                        $tmp = $this->pdo->query($sql);

                        while ($ddosInfo = $tmp->fetch(PDO::FETCH_OBJ)) {

                            echo $ddosInfo->ddosname . '.vddos <span class="small nomargin">(' . $software->dotVersion($ddosInfo->ddosversion) . ')</span> '._('on').' <a class="black" href="internet?ip='.long2ip($ddosInfo->ip).'">' . long2ip($ddosInfo->ip) . "</a><br />";

                        }

                    } else {
                        echo _('You havent installed any DDoS Virus yet.');
                    }     

                    ?>

                    </div>
                </div>
            </div>    
        </div>
        </div>
        <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>

                    
        <?php
        
    }
    
    public function addToList($id, $ip, $user, $pass, $virusID, $virusType){
        
        if($this->player->verifyID($id)){

            if(!self::isListed($id, $ip)){
	
		$this->session->newQuery();
                $sqlQuery = "INSERT INTO lists (id, userID, ip, user, pass, hackedTime, virusID) VALUES ('', ?, ?, ?, ?, NOW(), ?)";
                $sqlReg = $this->pdo->prepare($sqlQuery);
                $sqlReg->execute(array($id, $ip, $user, $pass, $virusID));

                $this->session->newQuery();
                $sql = "UPDATE users_stats SET hackCount = hackCount + 1 WHERE uid = '".$_SESSION['id']."'";
                $this->pdo->query($sql);
                
            }

        }

    }

    public function bank_addToList($bankID, $acc, $pass){
        
        $this->session->newQuery();
        $sql = "INSERT INTO lists_bankAccounts (id, userID, bankAcc, bankPass, bankID, hackedDate, lastMoney, lastMoneyDate)
                VALUES ('', '".$_SESSION['id']."', '".$acc."', '".$pass."', '".$bankID."', NOW(), '-1', NOW())";
        $this->pdo->query($sql);
        
    }
    
    public function isListed($id, $ip){

        $this->session->newQuery();
        $sqlCount = "SELECT id FROM lists WHERE userID = $id AND ip = $ip LIMIT 1";
        $total = $this->pdo->query($sqlCount)->fetchAll();

        if(count($total) == '1'){
            return TRUE;
        } else {
            return FALSE;
        }

    }
    
    public function isExploited($id, $ip){
        
        $this->session->newQuery();
        $sqlCount = "SELECT pass FROM lists WHERE userID = $id AND ip = $ip LIMIT 1";
        $pass = $this->pdo->query($sqlCount)->fetch(PDO::FETCH_OBJ)->pass;
        
        if($pass == 'exploited'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }    
    
    public function isDownload($id, $ip){
        
        $this->session->newQuery();
        $sqlCount = "SELECT pass FROM lists WHERE userID = $id AND ip = $ip LIMIT 1";
        $pass = $this->pdo->query($sqlCount)->fetch(PDO::FETCH_OBJ)->pass;
        
        if($pass == 'download'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }    
    
    public function isUnknown($id, $ip){
        
        $this->session->newQuery();
        $sqlCount = "SELECT pass FROM lists WHERE userID = $id AND ip = $ip LIMIT 1";
        $pass = $this->pdo->query($sqlCount)->fetch(PDO::FETCH_OBJ)->pass;
        
        if($pass == 'unknown'){
            return TRUE;
        } else {
            return FALSE;
        }
        
        
    }
    
    public function revealUnknownPassword($id, $ip, $user, $pass){
        
        $this->session->newQuery();
        $sql = "UPDATE lists SET user = '".$user."', pass = '".$pass."' WHERE userID = $id AND ip = $ip LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function updateListedHardware($id, $info){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM lists_specs_analyzed WHERE listID = '.$id.' LIMIT 1';
        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total == 0){
            $sql = "INSERT INTO lists_specs_analyzed
                        (listID, minCPU, maxCPU, minRAM, maxRAM)
                    VALUES
                        ('".$id."', '".$info[0]."', '".$info[1]."', '".$info[2]."', '".$info[3]."')";
        } else {
            $sql = "UPDATE lists_specs_analyzed 
                    SET 
                        minCPU = '".$info[0]."',
                        maxCPU = '".$info[1]."',
                        minRAM = '".$info[2]."',
                        maxRAM = '".$info[3]."'
                    WHERE listID = $id 
                    LIMIT 1";
        }
        
        $this->session->newQuery();
        $this->pdo->query($sql);
        
    }
    
    public function bank_isListed($user, $acc){
        
        $this->session->newQuery();
        $sqlCount = "SELECT id FROM lists_bankAccounts WHERE userID = $user AND bankAcc = $acc LIMIT 1";
        $total = $this->pdo->query($sqlCount)->fetchAll();

        if(count($total) == '1'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function getListedLoginInfo($id, $ip){
        
	$this->session->newQuery();
        $sqlCount = "SELECT user, pass FROM lists WHERE userID = $id AND ip = $ip LIMIT 1";
        $total = $this->pdo->query($sqlCount)->fetchAll();

        return $total;
        
    }
    
    public function bank_getListedLoginInfo($id, $acc){
        
	$this->session->newQuery();
        $sqlCount = "SELECT bankPass FROM lists_bankAccounts WHERE userID = $id AND bankAcc = $acc LIMIT 1";
        $total = $this->pdo->query($sqlCount)->fetchAll();

        return $total;
        
    }
    
    public function bank_updateMoney($acc, $id, $money){
        
        $this->session->newQuery();
        $sql = "UPDATE lists_bankAccounts SET lastMoney = $money, lastMoneyDate = NOW() WHERE bankAcc = $acc AND userID = $id";
        $this->pdo->query($sql);
        
    }

    public function collect($bankAcc){

        $hardware = new HardwareVPC();
        $system = new System();
        $ranking = new Ranking();

        $error = '';
        
        $accInfo = $this->finances->bankAccountInfo($bankAcc);
        if($accInfo['0']['exists'] == '0'){
            $error = 'BAD_ACC';
        } elseif($accInfo['0']['bankuser'] != $_SESSION['id']){
            $error = 'BAD_ACC';
        }

        $collectorInfo = $this->software->getBestSoftware('11', '', '', 0);
        if($collectorInfo['0']['exists'] == '0'){
            $error = 'NO_COLLECTOR';
        }
        
        $torrentInfo = $this->software->getBestSoftware('17', '', '', 0);
        if($torrentInfo['0']['exists'] == '1'){
            $torrentVersion = $torrentInfo['0']['softversion'];
        } else {
            $torrentVersion = 0;
        }        
        
        $haveWallet = $this->finances->userHaveWallet($_SESSION['id'], $this->finances->bitcoin_getID());
        
        $id = $_SESSION['id'];
        
	$this->session->newQuery();
        $sql = 'SELECT 
                    virus.installedIp, virus.virusID, virusVersion, virusType, lastCollect, 
                    TIMESTAMPDIFF(SECOND, lastCollect, NOW()) AS duration 
                FROM virus
                INNER JOIN lists
                ON lists.virusID = virus.virusID
                WHERE 
                    virus.installedBy = :id AND 
                    virus.virusType <> 3 AND 
                    virus.active = 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        
        if($error == ''){
            
            $collected = '0';
            $logText = 'localhost collected money using '.$collectorInfo['0']['softname'].'.vcol ('.$this->software->dotVersion($collectorInfo['0']['softversion']).')'."\n";
            $returnText = '';
            $moneyToAdd = '0';
            $totalBTC = 0;
            $mailSent = 0;
            $warezSent = 0;
            $minerSent = 0;
            $skipDuration = 0;
            $skipTorrent = 0;
            $skipMiner = 0;

            while($virusInfo = $stmt->fetch(PDO::FETCH_OBJ)){

                $hackedInfo = $this->player->getIDByIP($virusInfo->installedip, '');

                if($hackedInfo['0']['existe'] == 1){

                    $hardInfo = $hardware->getHardwareInfo($hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                    $this->session->newQuery();
                    $sql = "SELECT COUNT(*) AS virusCount FROM virus WHERE installedIp = '".$virusInfo->installedip."' AND virusType = '".$virusInfo->virustype."' AND active = 1";
                    $virusCount = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->viruscount; //total de virus desse tipo trabalhando no ip em questão
                    
                    if($virusInfo->virustype == '1'){ //vspam

                        $hardUsage = $hardInfo['CPU'];
                        $sentMultiplier = 1 + $virusInfo->virusversion/5;
                        $text1 = _(' mailed ');
                        $text2 = _('</span> emails, generating ');
                        $logText2 = ' emails, generating ';
                        
                        $rate = 0.00000694; //$0.025 per mhz per hour

                        $invalid = 0;

                    } elseif($virusInfo->virustype == '2') { //vwarez

                        $hardUsage = $hardInfo['NET'];
                        $sentMultiplier = 0.02 + $virusInfo->virusversion/50;
                        $text1 = _(' sold ');
                        $text2 = _(' GB</span> of warez, generating ');
                        $logText2 = ' GB of warez, generating ';
                        
                        if($torrentVersion == 0){
                            $invalid = 1;
                            $rate = 0;
                        } else {
                            
                            $invalid = 0;
                            
                            switch($torrentVersion){
                                
                                case 10:
                                    $rate = 0.000555556; //$2 per mbit per hour
                                    break;
                                case 20:
                                    $rate = 0.000694444; //$2.5 per mbit per hour
                                    break;
                                case 30:
                                    $rate = 0.000833333; //$3 per mbit per hour
                                    break;
                                case 40:
                                    $rate = 0.000972222; //$3.5 per mbit per hour
                                    break;
                                case 50:
                                    $rate = 0.001111111; //$4 per mbit per hour
                                    break;
                                default:
                                    $rate = 0.000555556;
                                    break;
                                
                            }
                            
                        }

                    } else { //vminer
                        
                        $hardUsage = $hardInfo['CPU'];
                        $sentMultiplier = $virusInfo->virusversion*$hardUsage*100;
                        $text1 = _(' mined at a rate of ');
                        $text2 = _('</span> GH/s, generating ');
                        $logText2 = ' GH/s, generating ';
                        $rate = 0.00010417/3600; //0.00010417BTC per mhz per hour
                        
                        if($haveWallet){
                            $invalid = 0;
                        } else {
                            $invalid = 1;
                        }
                                                
                    }

                    //$rate /= 10; //slow mode
                    $rate /= 2;
                    if($virusInfo->virustype == '3'){
                        $rate /= 2;
                    }
                    
                    $workTime = $virusInfo->duration;

                    if($workTime >= '600'){

                        if($invalid == 0){

                            $stringWorked = '';
                            $workedHours = (int)($virusInfo->duration / 3600);
                            $workedMinutes = ($virusInfo->duration / 60) % 60;
                            
                            if($workedHours > 0){
                                $stringWorked = sprintf(ngettext('%d hour', '%d hours', $workedHours), $workedHours);
                                if($workedMinutes > 0){
                                    $stringWorked .= _(' and ');
                                }
                            }
                            if($workedMinutes > 0){
                                $stringWorked .= sprintf(ngettext('%d minute', '%d minutes', $workedMinutes), $workedMinutes);
                            }
                                                        
                            $text3 = _(' worked for <b>').$stringWorked.'</b> '._('and');
                            
                            $bonusChance = rand(1,20); //chance de 5% de dobrar o $

                            if($bonusChance == 1){
                                $bonusMultiplier = 2;
                                $bonusText = ' - <span class="green"><b>BONUS</b></span>!';
                            } else {
                                $bonusMultiplier = 1;
                                $bonusText = '';
                            }

                            $virusPercent = (($virusInfo->virusversion/1000)*2)+1; 

                            $moneyGenerated = ($workTime * $rate * $hardUsage * $virusPercent * $bonusMultiplier) / $virusCount;
                                                        
                            if($virusInfo->virustype != 4){
                                $moneyGenerated = ceil($moneyGenerated);
                            }
                            $sent = $moneyGenerated * $sentMultiplier * rand(10,25);
                            
                            if($moneyGenerated > 1 || $virusInfo->virustype == 4){

                                $text4 = '';

                                if($virusInfo->virustype == 1){

                                    $sent = ceil($sent);
                                    $mailSent += $sent;

                                } elseif($virusInfo->virustype == 2) {

                                    $sent = round($sent, 2);
                                    $warezSent += $sent;

                                } else {
                                    
                                    if($sent > 1000){
                                        $round = 1;
                                    } else {
                                        $round = 2;
                                    }
                                    
                                    $sent = $hardUsage / 100;
                                    
                                }

                                if($virusInfo->virustype != 4){
                                    $moneyToAdd += $moneyGenerated;
                                    $formatedValue = '$'.number_format($moneyGenerated, '0', '.', ',');
                                } else {
                                    $formatedValue = round($moneyGenerated, 7).' BTC';
                                    $totalBTC += round($moneyGenerated, 7);
                                }

                                $collected++;
                                self::setLastCollect($virusInfo->virusid);

                            } else {
                                $text4 = ' <a href="https://wiki.hackerexperience.com/'._('en').':hacked_database'._('#collecting_money').'">(?)</a>';
                                
                                $sent = 0;
                                $moneyGenerated = $formatedValue = 0;
                                $bonusText = '';
                            }

                            $logText .= 'Server ['.long2ip($virusInfo->installedip).']'.$text1.$sent.$logText2.$formatedValue.'.'."\n";
                            $returnText .= 'Server <u>'.long2ip($virusInfo->installedip).'</u>'.$text3.$text1.'<span class="red">'.$sent.$text2.'<span class="green"><b>'.$formatedValue.'</b></span>'.$bonusText.$text4.'<br/>';

                        } else {
                            if($virusInfo->virustype == 2){
                                $skipTorrent++;
                            } else {
                                $skipMiner++;
                            }
                        }

                    } else {
                        $skipDuration++;
                    }

                }

            }

            $totalMoney = ceil($moneyToAdd * (1 + $collectorInfo['0']['softversion']/1000));
            $totalBTC = round($totalBTC * (1 + $collectorInfo['0']['softversion']/1000), 7);
            
            if($totalMoney > 0){
                $logText .= '$'.number_format($totalMoney, '0', '.', ',').' were transfered to #'.$bankAcc.' on bank ['.long2ip($this->finances->getBankIP($accInfo['0']['bankid'])).']';
                $this->session->exp_add('COLLECT', Array('cash', $totalMoney));
            }
            if($totalBTC > 0){
                $btcInfo = $this->finances->getWalletInfo($this->finances->bitcoin_getID());
                $logText .= "\n".round($totalBTC, 7).' BTC were transfered to address '.$btcInfo->address.' using key '.$btcInfo->key;
                $this->session->exp_add('COLLECT', Array('btc', $totalBTC));
            }

            
            $ranking->stats_updateCollect($warezSent, $mailSent, $totalMoney, $totalBTC);
            
            $returnText .= "<br/>";

            $string = '';
            if($skipDuration > 0){
                
                if($skipDuration == 1){
                    $string = _(' server');
                } else {
                    $string = _(' servers');
                }
                
                $returnText .= $skipDuration.$string._(' were ignored because didnt match minimum collect time.').'<br/>';
            }

            if($skipTorrent > 0){
                
                if($skipTorrent == 1){
                    $string = ' server';
                } else {
                    $string = ' servers';
                }                
                
                $returnText .= $skipTorrent._($string)._(' were ignored because you do not have a .torrent.').'<br/>';
            }
            
            if($skipMiner > 0){
                
                if($skipMiner == 1){
                    $string = ' server';
                } else {
                    $string = ' servers';
                }                
                
                $returnText .= $skipMiner._($string)._(' were ignored because you do not have a bitcoin wallet.').'<br/>';
            }

            if($string != ''){
                $returnText .= '<br/>';
            }
            
            if($collected == 0){
                $system = new System();
                if($skipDuration > 0){
                    //todos os servers foram pulados (por erro tempo mínimo)
                    $system->handleError('All servers were ignored because did not match minimum collect time.', 'list?action=collect');
                } elseif($skipTorrent > 0){ 
                    //todos os servers foram pulados (por erro torrent)
                    $system->handleError('All servers were ignored because you do not have a .torrent.', 'list?action=collect');
                } elseif($skipMiner > 0) {
                    //todos os servers foram pulados (por erro de wallet)
                    $system->handleError('All servers were ignored because you do not have a bitcoin wallet.', 'list?action=collect');
                } else {
                    //não tem nenhum virus rodando
                    $system->handleError('You do not have any running virus.', 'list?action=collect');
                }
            }
            

            $returnText .= _('Collector bonus').': <b>'.$collectorInfo['0']['softversion']/10 .'%</b><br/>';

            $this->log->addLog($id, $logText, '0');
            $this->finances->addMoney($totalMoney, $bankAcc);                    
            
            $returnText .= "<br/>".'<font color="green"><b>$'.number_format($totalMoney, '0', '.', ',').'</b></font> '._('were transfered to').' <u>#'.$bankAcc.'</u>';

            if($totalBTC > 0){
                $returnText .= "<br/>".'<font color="green"><b>'.$totalBTC.' BTC</b></font> '._('were transfered to').' <u>'.$btcInfo->address.'</u>';
                
                $this->finances->bitcoin_transfer('', round($totalBTC, 7), $btcInfo->address);
                
            }
            
            $this->session->newQuery();
            $sql = 'SELECT COUNT(*) AS total FROM lists_collect WHERE userID = '.$_SESSION['id'].' LIMIT 1';
            $isCollected = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
            
            if($isCollected == 1){
                $this->session->newQuery();
                $sql = 'UPDATE lists_collect
                        SET collectText = :text
                        WHERE userID = :id';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':text' => $returnText, ':id' => $_SESSION['id']));
            } else {
                $this->session->newQuery();
                $sql = 'INSERT INTO lists_collect (userID, collectText) 
                        VALUES (:id, :text)';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':id' => $_SESSION['id'], ':text' => $returnText));
            }

            return $returnText;
            
        } else {
            $system->handleError($error, 'list?action=collect');
        }         
        
    }

    public function show_lastCollect(){
        

        
?>
        <div class="widget-box text-left">
            <div class="widget-title">
                <span class="icon"><span class="he16-collect_info"></span></span>
                <h5><?php echo _('Collect Information'); ?></h5>
                <a href="list" class="label label-inverse"><?php echo _('Go Back'); ?></a>
            </div>
            <div class="widget-content">     
                <?php
                echo self::lastCollect();
?>

            </div>
        </div>
        
        <br/><center><a class="btn btn-danger" href="log"><?php echo _('Clear your logs'); ?></a></center>
<?php
        
    }
    
    public function lastCollect(){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total, collectText FROM lists_collect WHERE userID = '.$_SESSION['id'].' LIMIT 1';
        $text = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        if($text->total == 0){
            return _('Ops. You still havent collected :(');
        }
        
        return $text->collecttext;
        
    }
    
    public function show_collect(){
        
        $finances = new Finances();

        $accInfo = $finances->listBankAccounts($_SESSION['id']);

        if($accInfo['total'] == 0){
            $system = new System();
            $system->handleError('You need to create a bank account before collecting.', 'list');
        }
        
?>
        <div class="span8">
            <div class="widget-box text-left">
                <div class="widget-title">
                    <span class="icon"><span class="he16-collect_info"></span></span>
                    <h5><?php echo _('Last Collect Information'); ?></h5>
                </div>
                <div class="widget-content">     
                    <?php echo self::lastCollect(); ?>
                </div>
            </div>
        </div>
        <div class="span4">
            <div class="widget-box text-left">
                <div class="widget-title">
                    <span class="icon"><span class="he16-collect"></span></span>
                    <h5><?php echo _('Collect virus money'); ?></h5>
                </div>
                <div class="widget-content">     
                    <form action="" method="POST">
                        <input type="hidden" name="act" value="collect">
                        <div id="loading"><img src="images/ajax-money.gif"> <?php echo _('Loading...'); ?></div><input type="hidden" id="accSelect" value=""><span id="desc-money"></span>
                        <br/><br/><br/>
                        <input class="btn btn-large btn-success" type="submit" value="<?php echo _('Collect my money'); ?>!">
                    </form>
                </div>
            </div>
        </div>       
<?php
        
    }
    
    public function studyMoneyGenerated($virCount){

        $money = $virCount / 200;

        return $money;

    }

    public function setLastCollect($virusID){

        $id = $_SESSION['id'];

	$this->session->newQuery();
        $sql = "UPDATE virus SET lastCollect = NOW() WHERE installedBy = $id AND virusID = '".$virusID."'";
        $this->pdo->query($sql);

    }
    
    public function issetID($listID, $type){
        
        if($type == 1){ //verificando ID de ip list
            $table = 'lists';
        } else { //verificando ID de conta bancária
            $table = 'lists_bankAccounts';
        }
        
        $this->session->newQuery();
        $sql = "SELECT id FROM ".$table." WHERE userID = '".$_SESSION['id']."' AND id = '".$listID."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function deleteList($listID, $type, $ip = ''){
        
        if($type == 1){ //verificando ID de ip list
            $table = 'lists';
        } else { //verificando ID de conta bancária
            $table = 'lists_bankAccounts';
        }
        
        if($type == 1){ //check if have active viruses
            
            $this->session->newQuery();
            $sql = "UPDATE virus
                    INNER JOIN lists
                    ON virus.installedIp = lists.ip
                    LEFT JOIN virus_ddos 
                    ON virus_ddos.ip = virus.installedIp
                    SET virus.active = '0', virus_ddos.active = '0'
                    WHERE lists.id = '".$listID."' AND virus.installedBy = '".$_SESSION['id']."'
                    ";
            $this->pdo->query($sql);
            
            
        }
        
        $this->session->newQuery();
        $sql = "DELETE FROM ".$table." WHERE userID = '".$_SESSION['id']."' AND id = '".$listID."' LIMIT 1";
        $this->pdo->query($sql);
        
        if($type == 1){
            $this->session->newQuery();
            $sql = "DELETE FROM lists_specs WHERE listID = '".$listID."' LIMIT 1";
            $this->pdo->query($sql);
        }

    }
    
    public function specs_update($victimIP, $victimInfo){
        
        $hardware = new HardwareVPC();
        $victimHardware = $hardware->getHardwareInfo($victimInfo['id'], $victimInfo['pctype']);

        if(self::specs_isset($victimIP)){
            $sql = "UPDATE lists_specs
                    SET
                        spec_hdd = '".$victimHardware['HDD']."',
                        spec_net = '".$victimHardware['NET']."'
                    WHERE  
                        listID = '".self::getListIDByIP($victimIP)."'
                    LIMIT 1";            
        } else {
            $sql = "INSERT INTO lists_specs 
                        (listID, spec_hdd, spec_net) 
                    VALUES
                        ('".self::getListIDByIP($victimIP)."', '".$victimHardware['HDD']."', '".$victimHardware['NET']."')";
        }
        
        $this->session->newQuery();
        $this->pdo->query($sql);
        
    }
    
    private function specs_isset($victimIP){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total 
                FROM lists_specs
                WHERE listID = (
                    SELECT id
                    FROM lists
                    WHERE 
                        ip = \''.$victimIP.'\' AND
                        userID = \''.$_SESSION['id'].'\'
                )';

        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

}

?>
