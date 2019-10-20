<?php

class Internet {

    protected $pdo;
    protected $npc;
    protected $player;
    protected $session;
    protected $system;
    protected $software;
    protected $log;
    protected $process;
    protected $list;
    protected $virus;
    protected $finances;
    private $mission;
    
    private $ipInfo;
    private $menuShown;

    function __construct() {

        require_once '/var/www/classes/Player.class.php';
        require_once '/var/www/classes/Session.class.php';
        require_once '/var/www/classes/System.class.php';
        require_once '/var/www/classes/PC.class.php';
        require_once '/var/www/classes/Process.class.php';
        require_once '/var/www/classes/NPC.class.php';
        require_once '/var/www/classes/List.class.php';
        require_once '/var/www/classes/Finances.class.php';
        require_once '/var/www/classes/Ranking.class.php';

        $this->pdo = PDO_DB::factory();
        $this->player = new Player();
        $this->session = new Session();
        $this->system = new System();
        $this->software = new SoftwareVPC();
        $this->log = new LogVPC();
        $this->virus = new Virus();
        $this->process = new Process();
        $this->npc = new NPC();
        $this->list = new Lists();
        $this->finances = new Finances();
        $this->ranking = new Ranking();

    }

    public function navigate($ip, $header = '') {
                
        if ($header != '1') {
            self::showHeader($ip);
        }        
        
        if ($this->session->issetMsg()) {
            $this->session->returnMsg();
        }                

        $this->session->createInternetSession($ip);

        $ipInfo = $this->player->getIDByIP($ip, '');
        $playerInfo = $this->player->getPlayerInfo($_SESSION['id']);

        $this->ipInfo = $ipInfo;
        
        if ($ipInfo['0']['existe'] == '0') {
            
?>
                    <div class="widget-box">
                        <div class="widget-content padding noborder">
                            
                            <?php echo _('404 - Page not found'); ?><br/>
                            <?php echo _('This ip does not exists.'); ?><br/><br/>
                            <a href="internet?ip=1.2.3.4"><?php echo _('Back to First Whois'); ?></a>

                        </div>
                        <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                    </div>
                </div>
<?php

            self::show_sideBar();
            
            $this->session->deleteInternetSession();
            
        } else {

            $down = '0';
            if($this->ipInfo['0']['pctype'] == 'NPC'){

                if($this->npc->downNPC($this->ipInfo['0']['id'])){

                    $down = '1';

                }

            }

            if($down == '0'){
            
                // 2019: Is currently logged in
                if ($this->session->isInternetLogged() == TRUE && $_SESSION['LOGGED_IN'] == $ip && !$this->system->issetGet('bAction')) { //estÃ¡ logado
                    
                    self::refreshConnection();
                    
                    if ($this->session->issetMsg()) {
                        $endPage = '0';
                        if (strpos($_SESSION['msg'], 'disconnected')) {
                            $endPage = '1';
                        }
                        $this->session->returnMsg();
                        if ($endPage == '1') {
                            die("Has to end. Recursion is solution here.");
                        }
                    }

                    if ($this->system->issetGet('view')) {

                        $pageInfo = $this->system->switchGet('view', 'logs', 'software', 'logout', 'clan');

                        if ($pageInfo['ISSET_GET'] == '1') {

                            switch ($pageInfo['GET_VALUE']) {

                                case 'logs':
                                    self::showPage('logs');
                                    break;
                                case 'software':
                                    self::showPage('software');
                                    break;
                                case 'logout':
                                    self::showPage('logout');
                                    break;
                                case 'clan':
                                    self::showPage('clan');
                                    break;
                            }
                        } else {
                            $this->system->handleError('INVALID_GET', 'internet');
                        }
                        
                    } else {

                        self::showPage($_SESSION['CUR_PAGE']);
                        
                    }
                    
                // 2019: Not logged in
                } else { //nao estÃ¡ logado
                    
                    if ($this->system->issetGet('action') || $this->session->isHacking()) {

                        if($this->session->isHacking()){
                            $actionInfo = Array();
                            $actionInfo['ISSET_GET'] = 1;
                            $actionInfo['GET_VALUE'] = 'login';
                            $this->session->deleteHackingSession();
                        } else {
                            $actionInfo = $this->system->switchGet('action', 'hack', 'login', 'register');
                        }
                        
                        if ($actionInfo['ISSET_GET'] == '1') {

                            if ($ip == $playerInfo->gameip) {
                                $this->system->handleError('Dude! You cant login/hack yourself...', 'internet');
                            } else {

?>
                        <div class="widget-box">
                            <div class="widget-title">
                                <ul class="nav nav-tabs">          
<?php

                                $deepInfo = self::gatherInfo($ip);
                                $this->menuShown = 1;

                                $bankLogin = '0';
                                $bankHack = '0';
                                $bankRegister = '0';
                                $hack = '0';
                                $login = '0';
                                
                                if($this->system->issetGet('type')){
                                    $typeInfo = $this->system->verifyStringGet('type');
                                    if($typeInfo['GET_VALUE'] == 'bank'){
                                        if($actionInfo['GET_VALUE'] == 'hack'){
                                            $bankHack = '1';
                                        } else {
                                            $bankLogin = '1';
                                        }
                                    }
                                } elseif($this->system->issetGet('acc')){
                                    if($actionInfo['GET_VALUE'] == 'hack'){
                                        $bankHack = '1';
                                    } else {
                                        $bankLogin = '1';
                                    }
                                }
                                
                                if($bankHack == '0' && $bankLogin == '0'){
                                    if($actionInfo['GET_VALUE'] == 'hack'){
                                        $hack = '1';
                                    } else {
                                        $login = '1';
                                    }
                                }
                                
                                $indexLink = '';
                                if($this->session->issetBankSession()){
                                    $indexLink = '?view=index';
                                }

                                if($actionInfo['GET_VALUE'] == 'register'){
                                    $bankRegister = '1';
                                    $login = '0';
                                    $hack = '0';
                                }
                                
?>
                                    <li class="link"><a href="internet<?php echo $indexLink; ?>"><span class="he16-index icon-tab"></span></span><?php echo _("Index"); ?></a></li>
                                    <li class="link<?php if($login == '1') { echo ' active'; } ?>"><a href="?action=login"><span class="he16-login icon-tab"></span></span><?php echo _("Login"); ?></a></li>
                                    <li class="link<?php if($hack == '1') { echo ' active'; } ?>"><a href="?action=hack"><span class="icon-tab he16-internet_hack"></span><?php echo _("Hack"); ?></a></li>
<?php
                                if($deepInfo['NPCTYPE'] == 1 && $this->session->issetBankSession() == FALSE){
?>
                                    <li class="link<?php if($bankLogin == '1') { echo ' active'; } ?>"><a href="?action=login&type=bank"><span class="icon-tab he16-internet_bLogin"></span><?php echo _("Account login"); ?></a></li>
                                    <li class="link<?php if($bankHack == '1') { echo ' active'; } ?>"><a href="?action=hack&type=bank"><span class="icon-tab he16-internet_bHack"></span><?php echo _("Hack account"); ?></a></li>
<?php
                                    if (!$this->finances->isUserRegisteredOnBank($_SESSION['id'], $deepInfo['NPCID'])) {
?>
                                    <li class="link<?php if($bankRegister == '1') { echo ' active'; } ?>"><a href="?action=register"><span class="icon-tab he16-internet_bRegister"></span><?php echo _("Register account"); ?></a></li>
<?php
                                    }
                                } elseif($this->session->issetBankSession()){
?>
                                    <li class="link"><a href="?bAction=show"><span class="icon-tab he16-internet_bOverview"></span><?php echo _("Account overview"); ?></a></li>
<?php    
                                }
?>
                                    <a href="<?php echo $this->session->help('internet', 'hack'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>                                    
                                </ul>
                            </div>
                            <div class="widget-content padding noborder">
<?php
                                                
                                switch ($actionInfo['GET_VALUE']) {

                                    case 'hack':                                                                  
                                        
                                        $hackedInfo = $this->player->getIDByIP($ip, '');

                                        $listed = FALSE;
                                        $isRoot = FALSE;
                                        $isDownload = FALSE;
                                        
                                        if($this->list->isListed($_SESSION['id'], $ip)){
                                            if(!$this->list->isUnknown($_SESSION['id'], $ip)){
                                                $listed = TRUE;
                                                if($this->list->isExploited($_SESSION['id'], $ip)){
                                                    $exploited = TRUE;
                                                    $exploitedUser = $this->list->getListedLoginInfo($_SESSION['id'], $ip);
                                                    if($exploitedUser['0']['user'] == 'root'){
                                                        $isRoot = TRUE;
                                                    }
                                                } else {
                                                    $exploited = FALSE;
                                                }
                                                $listUser = $this->list->getListedLoginInfo($_SESSION['id'], $ip);
                                                if($listUser['0']['user'] == 'download'){
                                                    $isDownload = TRUE;
                                                }
                                            }
                                        }                                     
                                        
                                        if($this->system->issetGet('type') || $this->system->issetGet('acc')){
                                            $listed = FALSE;
                                        }

                                        if($listed && !$isDownload){
                                            if(!$exploited){
                                                $this->system->handleError('This IP is already on your hacked database.', 'internet?action=login');
                                            }
                                        }
                                        
                                        if($hackedInfo['0']['pctype'] == 'VPC'){
                                            if($this->player->isNoob($hackedInfo['0']['id'])){
                                                $this->system->handleError('This player is under newbie protection.', 'internet');
                                            }
                                        }
                                        
                                        if ($this->system->issetGet('method')) {

                                            $methodInfo = $this->system->switchGet('method', 'bf', 'xp');

                                            if ($methodInfo['ISSET_GET'] == '1') {

                                                switch ($methodInfo['GET_VALUE']) {

						case 'bf':
							if($deepInfo['NPCTYPE'] == 52){
								$this->system->handleError('Your cracker is not good enough.', 'internet');
							}
                                                        self::hack('bf', $_SESSION['id'], $ip);
                                                        break;
                                                    case 'xp':

                                                        if($deepInfo['NPCTYPE'] == 52){
                                                            $this->system->handleError('It\'s impossible to exploit the NSA. You should try to perform a bruteforce attack instead.', 'internet?action=hack');
                                                        }
                                                        
                                                        if($isRoot){
                                                            $this->system->handleError('You already are root user. Use the cracker to discover the password.', 'internet?action=login');
                                                        }
                                                        
                                                        $scanInfo = $this->software->getBestSoftware('3', '', '', '1');

                                                        if($this->ranking->cert_have('3')){

                                                            if ($scanInfo['0']['exists'] == '1') {
 
                                                                if ($this->system->issetGet('reset')) {
                                                                    unset($_SESSION['PORT_SCAN']);
                                                                    header("Location:internet?action=hack&method=xp");
                                                                    exit();
                                                                }

                                                                if (isset($_SESSION['PORT_SCAN']) && !isset($_SESSION['XP_HACK'])) {

                                                                    if ($_SESSION['PORT_SCAN'] == $ip) {

                                                                        $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                                                                        $sshExp = $this->software->getBestSoftware('14', '', '', '1');

                                                                        $hackedInfo = $this->player->getIDByIP($ip, '');
                                                                        $hackedFwl = $this->software->getBestSoftware('4', $hackedInfo['0']['id'], $hackedInfo['0']['pctype'], '1');

                                                                        if ($hackedFwl['0']['exists'] == '0') {
                                                                            $fwlVersion = '0';
                                                                        } else {
                                                                            $fwlVersion = $hackedFwl['0']['softversion'];
                                                                        }

                                                                        $ports = '0';

                                                                        $text = 'Scanning FTP on port 21 ..... ';
                                                                        
                                                                        if ($ftpExp['0']['exists'] == '0') {
                                                                            $text .= '<font color="red">[FAILED]</font> <span class="small">You have no running exploit.</span>';
                                                                        } elseif ($ftpExp['0']['softversion'] < $fwlVersion) {
                                                                            $text .= '<font color="red">[FAILED]</font>';
                                                                        } else {
                                                                            $text .= '<font color="green">[VULNERABLE]</font>';
                                                                            $ports++;
                                                                        }

                                                                        $text .= "<br/>" . 'Scanning SSH on port 23 ..... ';

                                                                        if ($sshExp['0']['exists'] == '0') {
                                                                            $text .= '<font color="red">[FAILED]</font> <span class="small">You have no running exploit.</span>';
                                                                        } elseif ($sshExp['0']['softversion'] < $fwlVersion) {
                                                                            $text .= '<font color="red">[FAILED]</font>';
                                                                        } else {
                                                                            $text .= '<font color="green">[VULNERABLE]</font>';
                                                                            $ports++;
                                                                        }
                                                                        
                                                                        $text .= '<br/><br/>';

                                                                        echo $text;

                                                                        if ($ports != '0') {
                                                                            $_SESSION['XP_HACK'] = $ip;
                                                                            $expLink = '?action=hack&method=xp';
                                                                            $expAction = 'Exploit';
                                                                       } else {
                                                                            $expLink = '?action=hack&method=xp&reset=1';
                                                                            $expAction = 'Scan Again';
                                                                        }
                                                                        
?>
                                <a href="<?php echo $expLink; ?>" class="btn btn-success"><?php echo $expAction; ?></a>
<?php                                
                                                                        
 ?>
                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                        </div>
                    </div>

<?php
                                                                        
                                                                        self::show_sideBar();
                                                                        
                                                                    } else {
                                                                        unset($_SESSION['PORT_SCAN']);
                                                                        $this->system->handleError('Wrong scanned IP. Please, try again.', 'internet');
                                                                    }
                                                                    
                                                                } elseif (isset($_SESSION['XP_HACK'])) {
                                                                    
                                                                    if ($_SESSION['XP_HACK'] == $ip) {

                                                                        unset($_SESSION['XP_HACK']);
                                                                        
                                                                        self::hack('xp', '', $ip);
                                                                        
                                                                    } else {

                                                                        unset($_SESSION['XP_HACK']);
                                                                        
                                                                        header("Location:internet?action=hack&method=xp");
                                                                        
                                                                    }
                                                                } else {
                                                                    
                                                                    self::hack_portScan();
                                                                    
                                                                }
                                                                
                                                            } else {
                                                                $this->system->handleError('NO_SOFT', 'internet');
                                                            }

                                                        } else {
                                                            $this->system->handleError('NO_CERTIFICATION', 'internet');
                                                        }

                                                        break;
                                                }
                                            } else {
                                                $this->system->handleError('INVALID_GET', 'internet');
                                            }
                                            
                                        // 2019: Player is hacking a bank account
                                        } elseif ($this->system->issetGet('acc')) { //tÃ¡ hackeando uma conta de banco!!

                                            $accInfo = $this->system->verifyNumericGet('acc');

                                            if($this->ranking->cert_have('3')){

                                                if ($accInfo['IS_NUMERIC'] == '1') {

                                                    if ($deepInfo['NPCTYPE'] == '1') {

                                                        //hacking $accInfo['GET_VALUE'] on bank $ip;
                                                        $userBank = $this->finances->getBankAccountInfo($_SESSION['id'], $deepInfo['NPCID']);

                                                        if ($userBank['VALID_ACC'] == '1' && $userBank['BANK_ACC'] == $accInfo['GET_VALUE']) {

                                                            $this->system->handleError(_('Oh, you can not hack your own accout. Its password is ').'<b>'.$userBank['BANK_PASS'].'</b>', 'internet?action=login&type=bank');
                                                            
                                                        } else { //tÃ¡ invadindo, Ã© banco e a conta nÃ£o Ã© dele.
                                                            //agora vejo se a conta existe
                                                            if ($this->finances->issetBankAccount($accInfo['GET_VALUE'], $deepInfo['NPCID'])) {

                                                                //existe. pronto pra hack
                                                                if(!$this->list->bank_isListed($_SESSION['id'], $accInfo['GET_VALUE'])){

                                                                    self::bank_hack($_SESSION['id'], $accInfo['GET_VALUE'], $deepInfo['NPCID']);

                                                                } else {

                                                                    $accPass = $this->list->bank_getListedLoginInfo($_SESSION['id'], $accInfo['GET_VALUE']);
                                                                    
                                                                    $this->system->handleError(sprintf(_('You already hacked the bank account %s! Its password is %s.'), $accInfo['GET_VALUE'], '<strong>'. $accPass['0']['bankpass'].'</strong>'), 'internet?action=login&type=bank');
                                                                    
                                                                }

                                                            } else {

                                                                $this->session->deleteBankSession();
                                                                $this->system->handleError('INEXISTENT_ACC', 'internet?action=hack&type=bank');

                                                            }

                                                        }

                                                    } else {
                                                        $this->system->handleError('BAD_BANK', 'internet');
                                                    }
                                                } else {
                                                    $this->system->handleError('BAD_ACC', 'internet?action=hack&type=bank');
                                                }

                                            } else {
                                                $this->system->handleError('NO_CERTIFICATION', 'internet');
                                            }

                                        // 2019: No defined method (on the query string, I suppose)
                                        } else { //nao tem um mÃ©todo definido
                                            
                                            if($this->system->issetGet('type')){
                                                $typeInfo = $this->system->verifyStringGet('type');
                                                if($typeInfo['GET_VALUE'] == 'bank'){
                                                    
                                                    self::bank_hackForm();
                                                    
                                                } else {
                                                    $this->system->handleError('INVALID_GET', 'internet?action=hack');
                                                }
                                            } else {
                                        

                                                $crcInfo = $this->software->getBestSoftware('1', '', '', '1');
                                                $portscanInfo = $this->software->getBestSoftware('3', '', '', '1'); //portscanner

                                                $bfDisable = FALSE;
                                                $xpDisable = FALSE;                                                
                                                
                                                $bfLink = '?action=hack&method=bf';
                                                $xpLink = '?action=hack&method=xp';
                                                
                                                if(!$this->ranking->cert_have('3')){
                                                    $xpDisable = TRUE;
                                                    $xpDisableMsg = 'No certification.';
                                                }

                                                if ($crcInfo['0']['exists'] == '0') {
                                                    $bfDisable = TRUE;
                                                    $bfDisableMsg = 'No Cracker.';
                                                }

                                                if ($portscanInfo['0']['exists'] == '0' && !$xpDisable) {
                                                    $xpDisable = TRUE;
                                                    $xpDisableMsg = 'No Port Scan.';
                                                }

                                                if(!$xpDisable){
                                                
                                                    $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                                                    $sshExp = $this->software->getBestSoftware('14', '', '', '1');

                                                    $tmp = 0;
                                                    
                                                    if($ftpExp['0']['exists'] == 0){
                                                        $ftpVersion = ' <font color="red">-</font>&nbsp;&nbsp;';
                                                        $tmp = 1;
                                                    } else {
                                                        $ftpVersion = $this->software->dotVersion($ftpExp['0']['softversion']);
                                                    }
                                                    
                                                    if($sshExp['0']['exists'] == 0){
                                                        $sshVersion = ' <font color="red">-</font>&nbsp;&nbsp;';
                                                        if($tmp == 1){
                                                            $xpDisable = 1;
                                                            $xpDisableMsg = 'No Exploits.';
                                                            $xpLink = '#';
                                                        }
                                                    } else {
                                                        $sshVersion = $this->software->dotVersion($sshExp['0']['softversion']);
                                                    }
                                                    
                                                
                                                    
                                                } else {
                                                    $xpLink = '#';
                                                }
                                                
                                                if($bfDisable){
                                                    $bfLink = '#';
                                                }
                                                
?>
                                <div class="span12 center" style="text-align: center;">
                                    <?php echo _('Choose your attack method:'); ?><br/>
                                    <ul class="quick-actions">
                                        <li>
                                            <a href="<?php echo $bfLink; ?>">
                                                <i class="icon- he32-bruteforce"></i>
<?php
if($bfDisable){
    echo '<font color="red">';
}
?>
                                                <?php echo _('Bruteforce attack'); ?>
<?php
if($bfDisable){
    echo '</font>';
}
?>
                                                <br/>
<?php
                                                
if($bfDisable){
?>
                                                    <span class="small"><?php echo _($bfDisableMsg); ?></span>
<?php
} else {
?>
                                                <span class="he16-bruteforce"></span>
                                                <?php echo $this->software->dotVersion($crcInfo['0']['softversion']); ?>
<?php                                                    
}
?>

                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo $xpLink; ?>">
                                                <i class="icon- he32-exploit"></i>
<?php
if($xpDisable){
    echo '<font color="red">';
}
?>
                                                <?php echo _('Exploit attack'); ?>
<?php
if($xpDisable){
    echo '</font>';
}
?>
                                                <br/>
<?php
if($xpDisable){
?>
                                                <span class="small"><?php echo _($xpDisableMsg); ?></span>
<?php                                                
} else {
?>
                                                <span class="he16-ftp"></span> <?php echo $ftpVersion; ?> <span class="he16-ssh"></span> <?php echo $sshVersion; ?>
<?php
}                                        
?>

                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                        </div>
                    </div>

<?php
                                                
                                                self::show_sideBar();
                                                
                                            }
                                            
                                        }

                                        break;
                                    case 'login':
   
                                        if ($this->system->issetGet('user') == TRUE && $this->system->issetGet('pass') == TRUE) {
            
                                            $getUser = $this->system->verifyStringGet('user');
                                            $getPass = $this->system->verifyStringGet('pass');
                                            
                                            switch (self::verifyLogin($getUser, $getPass, $ip)) {

                                                case '1': //login normal

                                                    if(!$this->ranking->cert_have(2)){
                                                        $this->system->handleError('NO_CERTIFICATION', 'internet');
                                                    }
                                                    
                                                    $crcInfo = $this->software->getBestSoftware('1', '', '', '1');
                                                    $pecInfo = $this->software->getBestSoftware('2', $ipInfo['0']['id'], $ipInfo['0']['pctype'], '1');

                                                    if ($crcInfo['0']['exists'] == '0') {
                                                        $this->system->handleError('NO_SOFT', 'internet');
                                                    }
                                                    
                                                    if ($crcInfo['0']['softversion'] < $pecInfo['0']['softversion']) {
                                                        $this->system->handleError('BAD_CRACKER', 'internet');
                                                    }
                                                                                                        
                                                    $this->list->specs_update($ip, $ipInfo['0']);
                                                    
                                                    self::doLogin($getUser['GET_VALUE'], $ip);

                                                    $npc = '0';
                                                    if ($ipInfo['0']['pctype'] == 'NPC') {
                                                        $npc = '1';
                                                    }

                                                    if(!$this->list->isListed($_SESSION['id'], $ip)){
                                                        $this->list->addToList($_SESSION['id'], $ip, 'root', $getPass['GET_VALUE'], '', '');
                                                    } elseif ($this->list->isUnknown($_SESSION['id'], $ip) || $this->list->isExploited($_SESSION['id'], $ip)){
                                                        $this->list->revealUnknownPassword($_SESSION['id'], $ip, 'root', $getPass['GET_VALUE']);
                                                    }

                                                    $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $getUser['GET_VALUE']);
                                                    $paramInfoHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $getUser['GET_VALUE']);
                                                    
                                                    $this->log->addLog($ipInfo['0']['id'], $this->log->logText('LOGIN', $paramInfoHacked), $npc);
                                                    $this->log->addLog($_SESSION['id'], $this->log->logText('LOGIN', $paramInfoHacker), '0');

                                                    $_SESSION['CUR_PAGE'] = 'logs';

                                                    $this->session->exp_add('REMOTE_LOGIN', Array($getUser['GET_VALUE'], $getPass['GET_VALUE'], $crcInfo['0']['softversion'], $pecInfo['0']['softversion']));
                                                    
                                                    header("Location:internet");
                                                    exit();

                                                    break;
                                                case '2': //pass doesnt match
                                                    $this->system->handleError('WRONG_PASS', 'internet?action=login');
                                                    break;
                                                case '3': //diferent login (other than root, prob exploit)

                                                    $hackedInfo = $this->player->getIDByIP($ip, '');
                                                    
                                                    if(!$this->list->isListed($_SESSION['id'], $ip)){
                                                        $this->system->handleError('You need to hack or exploit before.', 'internet');
                                                    }
                                                    
                                                    if($this->list->isUnknown($_SESSION['id'], $ip)){
                                                        $this->system->handleError('Password is unknown. You have to hack again.', 'internet?action=hack');
                                                    }
                                                    
                                                    $loginInfo = $this->list->getListedLoginInfo($_SESSION['id'], $ip);
                                                    
                                                    if ($loginInfo['0']['user'] != $getUser['GET_VALUE']) {
                                                        $this->system->handleError('Trying to login using a different user from your hacked database.', 'internet?action=login');
                                                    }
                                                    
                                                    $scanInfo = $this->software->getBestSoftware('3', '', '', '1');
                                                    $softInfoHacked = $this->software->getBestSoftware('4', $hackedInfo['0']['id'], $hackedInfo['0']['pctype'], '1');

                                                    if ($scanInfo['0']['exists'] == '0') {
                                                        $this->system->handleError('HXP_NO_SCAN', 'internet?action=login');
                                                    }

                                                    if ($softInfoHacked['0']['exists'] == '0') {
                                                        $softInfoHacked['0']['softversion'] = '0';
                                                    }

                                                    $npc = '0';
                                                    if ($ipInfo['0']['pctype'] == 'NPC') {
                                                        $npc = '1';
                                                    }

                                                    $log = 0;
                                                    if ($getUser['GET_VALUE'] == 'ftp') {

                                                        $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                                                        if ($ftpExp['0']['exists'] == '1') {
                                                            if ($ftpExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                                                                self::doLogin($getUser['GET_VALUE'], $ip, 1);
                                                                $log = 1;
                                                            }
                                                        } else {
                                                            $this->system->handleError('NO_FTP_EXP', 'internet?action=login');
                                                        }
                                                        
                                                        $exploitVersion = $ftpExp;

                                                    } else {

                                                        $sshExp = $this->software->getBestSoftware('14', '', '', '1');
                                                        if ($sshExp['0']['exists'] == '1') {
                                                            if ($sshExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                                                                self::doLogin($getUser['GET_VALUE'], $ip, '1');
                                                                $log = 1;
                                                            }
                                                        } else {
                                                            $this->system->handleError('NO_SSH_EXP', 'internet?action=login');
                                                        }

                                                        $exploitVersion = $sshExp;
                                                        
                                                    }

                                                    if($log == 0){
                                                        $this->system->handleError('BAD_EXP', 'internet?action=login');
                                                    }
                                                    
                                                    if(!$this->list->isListed($_SESSION['id'], $ip)){
                                                        $this->list->addToList($_SESSION['id'], $ip, $getUser['GET_VALUE'], $getPass['GET_VALUE'], '', '');
                                                    } elseif ($this->list->isUnknown($_SESSION['id'], $ip)){
                                                        $this->list->revealUnknownPassword($_SESSION['id'], $ip, $getUser['GET_VALUE'], $getPass['GET_VALUE']);
                                                    }
                                                    
                                                    $this->list->specs_update($ip, $ipInfo['0']);

                                                    $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $getUser['GET_VALUE']);
                                                    $paramInfoHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $getUser['GET_VALUE']);

                                                    $this->log->addLog($ipInfo['0']['id'], $this->log->logText('LOGIN', $paramInfoHacked), $npc);
                                                    $this->log->addLog($_SESSION['id'], $this->log->logText('LOGIN', $paramInfoHacker), '0');
                                                    
                                                    $_SESSION['CUR_PAGE'] = 'logs';

                                                    $this->session->exp_add('REMOTE_LOGIN', Array($getUser['GET_VALUE'], $getPass['GET_VALUE'], $exploitVersion['0']['softversion'], $softInfoHacked['0']['softversion']));
                      
                                                    header("Location:internet");
                                                    exit();

                                                    break;
                                                case '4': //user is root and password is exploited... i need to check if i have the exploits, then login

                                                    $hackedInfo = $this->player->getIDByIP($ip, '');
                                                    $loginInfo = $this->list->getListedLoginInfo($_SESSION['id'], $ip);

                                                    if ($loginInfo['0']['user'] == 'root' && $loginInfo['0']['pass'] == 'exploited') {

                                                        $scanInfo = $this->software->getBestSoftware('3', '', '', '1');
                                                        $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                                                        $sshExp = $this->software->getBestSoftware('14', '', '', '1');

                                                        if ($scanInfo['0']['exists'] == '0') {
                                                            $this->system->handleError('HXP_NO_SCAN', 'internet?action=login');
                                                        }

                                                        $softInfoHacked = $this->software->getBestSoftware('4', $hackedInfo['0']['id'], $hackedInfo['0']['pctype'], '1');

                                                        if ($softInfoHacked['0']['exists'] == '0') {
                                                            $softInfoHacked['0']['softversion'] = '0';
                                                        }

                                                        $expInfo = '0'; //0 = nenhum, 1 = ftp, 2 = ssh, 3 = os dois

                                                        if ($ftpExp['0']['exists'] == '1') {
                                                            if ($sshExp['0']['exists'] == '1') {
                                                                $expInfo = '3';
                                                            } else {
                                                                $expInfo = '1';
                                                            }
                                                        } else {
                                                            if ($sshExp['0']['exists'] == '1') {
                                                                $expInfo = '2';
                                                            }
                                                        }

                                                        $hackable = '0';
                                                        $ftpCanHack = '0';
                                                        if ($expInfo == '1' || $expInfo == '3') {
                                                            if ($ftpExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                                                                $ftpCanHack = '1';
                                                                $hackable = '1';
                                                            }
                                                        }

                                                        $sshCanHack = '0';
                                                        if ($expInfo == '2' || $expInfo == '3') {
                                                            if ($sshExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                                                                $sshCanHack = '1';
                                                                $hackable = '1';
                                                            }
                                                        }

                                                        $user = 'root';
                                                        $didLogin = '0';

                                                        // 2019: Player has both exploits so  he can login as root
                                                        if ($hackable == '1' && $expInfo == '3') { //tem os 2 exps e as versÃµes sÃ£o normais. login root ok!
                                                            self::doLogin('root', $ip, '1');
                                                            $didLogin = '1';
                                                        } elseif ($hackable == '1' && $expInfo != '3') {
                                                            if ($ftpCanHack == '1') {
                                                                $user = 'ftp';
                                                                $didLogin = '1';
                                                                self::doLogin('ftp', $ip, '1');
                                                            } elseif ($sshCanHack == '1') {
                                                                $user = 'ssh';
                                                                $didLogin = '1';
                                                                self::doLogin('ssh', $ip, '1');
                                                            }
                                                        } elseif ($hackable == '0' && $expInfo != '0') {
                                                            $this->system->handleError('Access denied: your exploits are not good enough.', 'internet?action=login');
                                                        } elseif ($expInfo == '0') {
                                                            $this->system->handleError('HXP_NO_EXP', 'internet?action=login');
                                                        }
                                                        
                                                        if ($didLogin == '1') {

                                                            $npc = '0';
                                                            if ($ipInfo['0']['pctype'] == 'NPC') {
                                                                $npc = '1';
                                                            }

                                                            $this->list->specs_update($ip, $ipInfo['0']);
                                                            
                                                            $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $user);
                                                            $paramInfoHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $user);

                                                            $this->log->addLog($ipInfo['0']['id'], $this->log->logText('LOGIN', $paramInfoHacked), $npc);
                                                            $this->log->addLog($_SESSION['id'], $this->log->logText('LOGIN', $paramInfoHacker), '0');
                                                            
                                                            $_SESSION['CUR_PAGE'] = 'logs';
                                                            
                                                            $this->session->exp_add('REMOTE_LOGIN', Array($user, $getPass['GET_VALUE'], $ftpExp['0']['softversion'] + $sshExp['0']['softversion'], $softInfoHacked['0']['softversion']));
                                                            
                                                            header("Location:internet");
                                                            exit();
                                                                                                                        
                                                        }
                                                        
                                                    } else {
                                                        $this->system->handleError('Wrong exploited user.', 'internet?action=login');
                                                    }

                                                    break;
                                                case 5: //login as user download and pass download
                                                    
                                                    self::doLogin('download', $ip);
                                                    
                                                    $user = 'download';
                                                    $npc = '1';
                                                    
                                                    $this->list->addToList($_SESSION['id'], $ip, 'download', 'download', '', '');
                                                    $this->list->specs_update($ip, $ipInfo['0']);

                                                    $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $user);
                                                    $paramInfoHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $user);

                                                    $this->log->addLog($ipInfo['0']['id'], $this->log->logText('LOGIN', $paramInfoHacked), $npc);
                                                    $this->log->addLog($_SESSION['id'], $this->log->logText('LOGIN', $paramInfoHacker), '0');
                                                            
                                                    
                                                    $_SESSION['CUR_PAGE'] = 'logs';

                                                    $this->session->exp_add('REMOTE_LOGIN', Array('download'));
                                                    
                                                    header("Location:internet");
                                                    exit();
                                                    
                                                    break;
                                                case 6: //login as user clan and pass clan
                                                    
                                                    self::doLogin('clan', $ip);
                                                    
                                                    $user = $playerInfo->login;
                                                    $npc = '1';
                                                    
                                                    $this->list->addToList($_SESSION['id'], $ip, 'clan', 'clan', '', '');
                                                    $this->list->specs_update($ip, $ipInfo['0']);

                                                    $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $user);
                                                    $paramInfoHacker = Array(2, long2ip($_SESSION['LOGGED_IN']), $user);

                                                    $this->log->addLog($ipInfo['0']['id'], $this->log->logText('LOGIN', $paramInfoHacked), $npc);
                                                    $this->log->addLog($_SESSION['id'], $this->log->logText('LOGIN', $paramInfoHacker), '0');
                                                    
                                                    $_SESSION['CUR_PAGE'] = 'logs';

                                                    $this->session->exp_add('REMOTE_LOGIN', Array('clan'));
                                                    
                                                    header("Location:internet");
                                                    exit();
                                                    
                                                    break;
                                                
                                            }
                                        } elseif ($this->system->issetGet('acc') == TRUE && $this->system->issetGet('pass') == TRUE) {

                                            if($this->session->issetBankSession()){
                                                $this->system->handleError('Logout before cracking a bank account.', 'internet?bAction=show');
                                            }
                                            
                                            $accGet = $this->system->verifyNumericGet('acc');
                                            $passInfo = $this->system->verifyStringGet('pass');

                                            $deepInfo = self::gatherInfo($ip);

                                            if ($accGet['IS_NUMERIC'] == '1') {

                                                if ($this->finances->issetBankAccount($accGet['GET_VALUE'], $deepInfo['NPCID'])) {

                                                    //get acc info
                                                    $accID = $this->finances->getIDByBankAccount($accGet['GET_VALUE'], $deepInfo['NPCID']);
                                                    $accInfo = $this->finances->getBankAccountInfo($accID, $deepInfo['NPCID'], $accGet['GET_VALUE']);

                                                    if ($passInfo['GET_VALUE'] == $accInfo['BANK_PASS']) {

                                                        if(!$this->list->bank_isListed($_SESSION['id'], $accGet['GET_VALUE']) && ($accID != $_SESSION['id'])){
                                                            $this->list->bank_addToList($deepInfo['NPCID'], $accGet['GET_VALUE'], $accInfo['BANK_PASS']);
                                                        }
                                                        
                                                        $this->list->bank_updateMoney($accGet['GET_VALUE'], $_SESSION['id'], $accInfo['CASH']);
                                                        
                                                        //logou
                                                        $this->session->createBankSession($deepInfo['NPCID'], $accGet['GET_VALUE'], $ip);

                                                        //crio o log
                                                        $logTextHacked = '[' . long2ip($playerInfo->gameip) . '] logged on account #' . $accGet['GET_VALUE'];
                                                        $logTextHacker = 'localhost logged on account #' . $accGet['GET_VALUE'] . ' on bank [' . long2ip($ip).']';

                                                        $this->log->addLog($deepInfo['NPCID'], $logTextHacked, '1');
                                                        $this->log->addLog($_SESSION['id'], $logTextHacker, '0');

                                                        $this->session->exp_add('REMOTE_LOGIN', Array('bank'));

                                                        if ($this->session->issetMissionSession()) {

                                                            require_once '/var/www/classes/Mission.class.php';
                                                            $this->mission = new Mission();

                                                            if ($this->mission->issetMission($_SESSION['MISSION_ID'])) {

                                                                if (($_SESSION['MISSION_TYPE'] == '3') && ($this->mission->missionInfo($_SESSION['MISSION_ID']) == $accGet['GET_VALUE'])) {

                                                                    $this->mission->completeMission($_SESSION['MISSION_ID']);

                                                                }
                                                            } else {
                                                                $this->session->deleteMissionSession();
                                                            }
                                                        }

                                                        header("Location:internet?bAction=show");
                                                        exit();
                                                        
                                                    } else {
                                                        $this->system->handleError('WRONG_PASS', 'internet?action=login&type=bank');
                                                    }
                                                } else {
                                                    $this->system->handleError('INEXISTENT_ACC', 'internet?action=login&type=bank');
                                                }
                                            } else {
                                                $this->system->handleError('BAD_ACC', 'internet?action=login&type=bank');
                                            }
                                        } else {

                                            self::showLoginForm($ip);
                                            
                                        }

                                        break;
                                    case 'register':
                                        if (!$this->finances->isUserRegisteredOnBank($_SESSION['id'], $deepInfo['NPCID'])) {
                                            
                                            if($this->system->issetGet('pass')){
                                                                                                                                                                                                            
                                                $this->finances->createAccount($_SESSION['id']);

                                                $acc = rand(111111,999999);

                                                $this->session->newQuery();

                                                $sql = 'INSERT INTO bankAccounts (id, bankAcc, bankPass, bankID, bankUser, cash, dateCreated) 
                                                        VALUES (\'\', :acc, :passInfo, :deepInfo, :SESSION, \'0\', NOW())';
                                                $stmt = $this->pdo->prepare($sql);
                                                $stmt->execute(array(':acc' => $acc, ':passInfo' => $passInfo['GET_VALUE'], ':deepInfo' => $deepInfo['NPCID'], ':SESSION' => $_SESSION['id']));

                                                echo 'Account created';
                                                
                                            } else {
                                            
                                            ?>
                                            
                                                    <form action="" method="POST">
                                                        
                                                        Creating an account here is free!</br><br/>
                                                        
                                                        <input type="hidden" name="int-act" value="register">
                                                        <input type="submit" value="Create account">
                                                        
                                                    </form>
                                                    
                                                </div>
                                                <div class="nav nav-tabs"></div>
                                                </div>
                    
                                            <?php     
                                            
                                            }
                                                    
                                        } else {
                                            $this->system->handleError('You already have an account on this bank.', 'internet?action=login&type=bank');
                                        }
                                        break;
                                }

                            }
                        } else {
                            $this->system->handleError('INVALID_GET', 'internet');
                        }
                        
                    // 2019: Player is hacking someone (just hacked them, now needs to login)
                    } elseif ($this->session->isHacking()) { //estÃ¡ hackeando alguÃ©m (acabou de hackear, falta logar);
                        
                        
                        
                        self::showActionMenu($ip);
                        //echo '</div>';
                        self::showLoginForm($ip);

                        //$this->session->deleteHackingSession();
                        
                    } elseif ($this->system->issetGet('view')) {

                        $viewInfo = $this->system->verifyStringGet('view');

                        if ($viewInfo['GET_VALUE'] == 'bank') {

                            if ($this->session->issetBankSession()) {

                                self::showPage('bank');
                                
                            } else {

                                self::showActionMenu($ip);
                                self::showPageInfo($ip);
                                
                            }
                            
                        } elseif ($viewInfo['GET_VALUE'] == 'index'){
                            
                            self::showActionMenu($ip);
                            self::showPageInfo($ip);
                            
                        } else {

                            $_GET['view'] = '';
                            self::navigate($ip, '1');
                            
                        }
                        
                    } elseif ($this->system->issetGet('bAction')) {

                        if ($this->session->issetBankSession()) {
                            
                            $bActionInfo = $this->system->switchGet('bAction', 'show', 'transfer', 'logout');

                            if ($bActionInfo['ISSET_GET'] == '1') {
                                self::showPage('bank');
                            } else {
                                $this->system->handleError('INVALID_GET', 'internet');
                            }
                            
                        } else {

                            header("Location:internet");
                            exit();
                            
                        }
                        
                    } elseif ($this->session->issetBankSession() == TRUE && $ip == $_SESSION['BANK_IP']) {

                        self::showPage('bank');
                        
                    } else {

                        if ($this->session->issetBankSession()) {
                            $this->session->deleteBankSession();
                        }

                        self::showActionMenu($ip);
                        self::showPageInfo($ip); //mostra web info ou cositas mas
                        
                    }
                }

            } else {
                
                self::showActionMenu($ip);
                self::showPageInfo($ip, 1); //mostra web info ou cositas mas
                
            }
        }
    }

    private function showPage($page) {

        if ($this->session->isInternetLogged()) {

            $ip = $_SESSION['LOGGED_IN'];
            $_SESSION['CUR_PAGE'] = $page;
            
            self::showActionMenu($ip);
            $hackedInfo = $this->player->getIDByIP($ip, '');

            $pNPC = '0';
            if ($hackedInfo['0']['pctype'] == 'NPC') {
                $pNPC = '1';
            }
            
        }

        switch ($page) {

            case 'logs':
                
                $this->log->listLog($hackedInfo['0']['id'], $hackedInfo['0']['pctype'], 'remote');

                if(isset($_SESSION['MISSION_ID'])){
                    if($_SESSION['MISSION_TYPE'] == 81){
                        require '/var/www/classes/Mission.class.php';
                        $mission = new Mission();
                        
                        $mission->tutorial_update(82);
                    }
                }
                
                break;
            case 'software':

                if ($this->system->issetGet('id') == TRUE && $this->system->issetGet('cmd') == TRUE) {

                    $idInfo = $this->system->verifyNumericGet('id');
                    $cmdInfo = $this->system->switchGet('cmd', 'dl', 'hide', 'seek', 'del', 'up', 'install', 'uninstall', 'move');

                    if ($idInfo['IS_NUMERIC'] == '1' && $cmdInfo['ISSET_GET'] == '1') {

                        switch ($cmdInfo['GET_VALUE']) {

                            case 'dl':

                                if (self::havePermissionTo('download')) {

                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                        if($softInfo->softtype == 19 || $softInfo->softtype == 31 || $softInfo->softtype >= 90 || $softInfo->softtype == 26){
                                            $this->system->handleError('You can not download this software.', 'internet');
                                        }
                                        
                                        $this->session->newQuery();
                                        $sql = 'SELECT id FROM software WHERE userId = :session AND softName = :softName AND softSize = :softSize AND softType = :softType AND softVersion = :softVersion AND isNPC = 0 AND softHidden = 0 LIMIT 1';
                                        $stmt = $this->pdo->prepare($sql);
                                        $stmt->execute(array(':session' => $_SESSION['id'], ':softName' => $softInfo->softname, ':softSize' => $softInfo->softsize, ':softType' => $softInfo->softtype, ':softVersion' => $softInfo->softversion));
                                        $rowsReturned = $stmt->fetchAll();

                                        if (count($rowsReturned) != '1') {

                                            if ($this->process->newProcess($_SESSION['id'], 'DOWNLOAD', $hackedInfo['0']['id'], 'remote', $idInfo['GET_VALUE'], '', '', $pNPC)) {

                                                $this->process->getProcessInfo($_SESSION['pid']);
                                                $this->process->showProcess();
                                                $this->process->endShowProcess();
                                                
                                            } else {

                                                if (!$this->session->issetMsg()) {

                                                    $pid = $this->process->getPID($_SESSION['id'], 'DOWNLOAD', $hackedInfo['0']['id'], 'remote', $idInfo['GET_VALUE'], '', '', $pNPC);

                                                    if ($pid != NULL) {

                                                        $this->session->processID('add', $pid);
                                                        $this->process->getProcessInfo($_SESSION['pid']);
                                                        $this->session->processID('del');

                                                        $this->process->showProcess();
                                                        $this->process->endShowProcess();
                                                        
                                                    } else {
                                                        $this->system->handleError('PROC_NOT_FOUND', 'internet');
                                                    }
                                                } else {
                                                    header("Location:?view=software");
                                                }
                                            }
                                        } else {
                                            $this->system->handleError('SOFT_ALREADY_HAVE', 'internet');
                                        }
                                    } else {
                                        $this->system->handleError('INEXISTENT_SOFTWARE', 'internet');
                                    }
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }

                                break;
                            case 'up':

                                if (self::havePermissionTo('upload')) {

                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC')) {

                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $_SESSION['id'], 'VPC');

                                        if($softInfo->softtype == 19 || $softInfo->softtype == 31 || $softInfo->softtype >= 90){
                                            $this->system->handleError('You can not upload this software.', 'software');
                                        }
                                        
                                        if ($softInfo->softhidden == '0') { // n estÃ¡ hide
                                            $npc = '0';
                                            if ($hackedInfo['0']['pctype'] == 'NPC') {
                                                $npc = '1';
                                            }

                                            $this->session->newQuery();
                                            $sql = 'SELECT id FROM software WHERE userId = :id AND softName = :softName AND softSize = :softSize AND softType = :softType AND softVersion = :softVersion AND isNPC = :npc LIMIT 1';
                                            $stmt = $this->pdo->prepare($sql);
                                            $stmt->execute(array(':id' => $hackedInfo['0']['id'], ':softName' => $softInfo->softname, ':softSize' => $softInfo->softsize, ':softType' => $softInfo->softtype, ':softVersion' => $softInfo->softversion, ':npc' => $npc));
                                            $rowsReturned = $stmt->fetchAll();

                                            // 2019: Check if that software already exists on "hacked"
                                            if (count($rowsReturned) != '1') { //vejo se jÃ¡ existe esse soft no hacked
                                                if ($this->process->newProcess($_SESSION['id'], 'UPLOAD', $hackedInfo['0']['id'], 'remote', $idInfo['GET_VALUE'], '', '', $pNPC)) {

                                                    $this->process->getProcessInfo($_SESSION['pid']);

                                                    $this->process->showProcess();
                                                    $this->process->endShowProcess();
                                                    
                                                } else {

                                                    if (!$this->session->issetMsg()) {

                                                        $pid = $this->process->getPID($_SESSION['id'], 'UPLOAD', $hackedInfo['0']['id'], 'remote', $idInfo['GET_VALUE'], '', '', $pNPC);

                                                        if ($pid != NULL) {

                                                            $this->session->processID('add', $pid);
                                                            $this->process->getProcessInfo($_SESSION['pid']);
                                                            $this->session->processID('del');

                                                            $this->process->showProcess();
                                                            $this->process->endShowProcess();
                                                            
                                                        } else {
                                                            $this->system->handleError('PROC_NOT_FOUND', 'internet');
                                                        }
                                                    } else {
                                                        header("Location:?view=software");
                                                    }
                                                }
                                            } else {
                                                $this->system->handleError('UPLOAD_SOFT_ALREADY_HAVE', 'internet');
                                            }
                                        } else {
                                            $this->system->handleError('SOFT_HIDDEN', 'software');
                                        }
                                    } else {
                                        $this->system->handleError('INEXISTENT_SOFTWARE', 'software');
                                    }
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }

                                break;
                            case 'hide':
                                if (self::havePermissionTo('hide')) {
                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {
                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);
                                    }
                                    if($softInfo->softtype == 26){
                                        $this->system->handleError('You can not hide this software.', 'internet');
                                    }
                                    $this->process->studyProcess('HIDE', 'remote', $hackedInfo['0']['pctype']);
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }
                                break;
                            case 'seek':
                                if (self::havePermissionTo('seek')) {
                                    $this->process->studyProcess('SEEK', 'remote', $hackedInfo['0']['pctype']);
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }
                                break;
                            case 'del':
                                
                                if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                    $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                    if($softInfo->softtype == 19 || $softInfo->softtype == 31 || $softInfo->softtype >= 90 || $softInfo->softtype == 26){
                                        $this->system->handleError('You can not delete this software.', 'internet');
                                    }
                                }
                                
                                if (self::havePermissionTo('del')) {
                                    $this->process->studyProcess('DELETE', 'remote', $hackedInfo['0']['pctype']);
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }
                                break;
                            case 'install':

                                if (self::havePermissionTo('install')) {

                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                        if ($this->software->isExecutable($softInfo->softtype, $idInfo['GET_VALUE'], 0)) {

                                            if (!$this->software->isSpecialExecutable($softInfo->softtype)) {

                                                if ($softInfo->softhidden != '1') {
                                                    $this->process->studyProcess('INSTALL', 'remote', $hackedInfo['0']['pctype']);
                                                } else {
                                                    $this->system->handleError('SOFT_HIDDEN', 'internet');
                                                }
                                                
                                            } else { //spec
                                                
                                                if ($softInfo->softhidden != '1') {

                                                    switch ($softInfo->softtype) {

                                                        case '7': //av
                                                            $this->process->studyProcess('AV', 'remote', $hackedInfo['0']['pctype']);
                                                            break;
                                                        case '15':
                                                            $this->process->studyProcess('NMAP', 'remote', $hackedInfo['0']['pctype']);
                                                            break;
                                                        case '16':
                                                            $this->process->studyProcess('ANALYZE', 'remote', $hackedInfo['0']['pctype']);
                                                            break;
                                                        case 19:
                                                            $finances = new Finances();
                                                            $finances->bitcoin_runWallet($hackedInfo['0']['id']);
                                                            break;
                                                        case '29':

                                                            $valid = 0;
                                                            if($hackedInfo['0']['pctype'] == 'NPC'){

                                                                $npcInfo = self::gatherInfo($ip);
                                                                if($npcInfo['NPCTYPE'] == 10){

                                                                    require_once '/var/www/classes/Clan.class.php';
                                                                    $clan = new Clan();

                                                                    if($clan->playerHaveClan()){
                                                                        if($clan->getClanInfo($clan->getPlayerClan())->clanip == $ip){
                                                                            $valid = 1;
                                                                        }
                                                                    }

                                                                }

                                                            }

                                                            if($valid == 1){

                                                                $this->process->studyProcess('INSTALL_DOOM', 'remote', $hackedInfo['0']['pctype']);

                                                            } else {
                                                                $this->system->handleError('DOOM_CLAN_ONLY', 'internet');
                                                            }
                                                            break;
                                                        default:
                                                            echo 'Invalid';
                                                            break;
                                                    }
                                                } else {
                                                    $this->system->handleError('SOFT_HIDDEN', 'internet');
                                                }
                                            
                                            }
                                        } else {
                                            $this->system->handleError('NOT_EXECUTABLE', 'internet');
                                        }
                                    } else {
                                        $this->system->handleError('INEXISTENT_SOFTWARE', 'internet');
                                    }
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }

                                break;
                            case 'uninstall':

                                if (self::havePermissionTo('uninstall')) {

                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                        if ($this->software->isInstalled($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                            $this->process->studyProcess('UNINSTALL', 'remote', $hackedInfo['0']['pctype']);
                                            
                                        } else {
                                            $this->system->handleError('NOT_INSTALLED', 'internet');
                                        }
                                    } else {
                                        $this->system->handleError('INEXISTENT_SOFTWARE', 'internet');
                                    }
                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }
                                
                                break;
                            case 'move':
                                
                                if(self::havePermissionTo('move')){

                                    if ($this->software->issetSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])) {

                                        $softInfo = $this->software->getSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                        if($softInfo->isfolder == 1){

                                            $this->software->folder_moveBack($idInfo['GET_VALUE']);

                                            $this->session->addMsg('Software was moved back to root.', 'notice');
                                            
                                            header("Location:internet?view=software");
                                            exit();
                                            
                                        } else {
                                            $this->system->handleError('FOLDER_INEXISTENT_SOFTWARE', 'internet');
                                        }

                                    } else {
                                        $this->system->handleError('INEXISTENT_SOFTWARE', 'internet');
                                    }

                                } else {
                                    $this->system->handleError('NO_PERMISSION', 'internet');
                                }
                                
                                break;
                            default:
                                $this->system->handleError('INVALID_GET', 'internet');
                                break;
                        }
                        
                    } else {
                        $this->system->handleError('INVALID_GET', 'internet');
                    }
                    
                } elseif($this->system->issetGet('cmd')) {

                    if($hackedInfo['0']['pctype'] == 'VPC'){
                        $npc = 0;
                    } else {
                        $npc = 1;
                    }                    
                    
                    $cmdInfo = $this->system->verifyStringGet('cmd');
                    
                    if($cmdInfo['IS_NUMERIC'] == 0){
                        
                        if($cmdInfo['GET_VALUE'] == 'txt'){
                            
                            if($this->system->issetGet('txt')){

                                $txtInfo = $this->system->verifyNumericGet('txt');

                                if($txtInfo['IS_NUMERIC'] == 1){

                                    $this->software->text_show($txtInfo['GET_VALUE'], $npc);

                                } else {
                                    $this->system->handleError('INVALID_GET', 'internet');
                                }

                            } else {

                                header("Location:internet");
                                exit();

                            }
                            
                        } elseif($cmdInfo['GET_VALUE'] == 'folder'){
                            
                            if($this->system->issetGet('folder')){
                                
                                $folderInfo = $this->system->verifyNumericGet('folder');
                                
                                if($folderInfo['IS_NUMERIC'] == 1){
                                
                                    if($this->system->issetGet('move')){
                                        
                                        if(self::havePermissionTo('move')){
                                        
                                            $moveInfo = $this->system->verifyNumericGet('move');

                                            if($moveInfo['IS_NUMERIC'] == 1){

                                                if($this->software->issetSoftware($moveInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype'])){

                                                    $soft2move = $this->software->getSoftware($moveInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);

                                                    if($soft2move->isfolder == 1){
                                                        $this->system->handleError('FOLDER_ALREADY_HAVE', 'internet?view=software&cmd=folder&folder='.$folderInfo['GET_VALUE']);
                                                    } elseif($soft2move->softhidden != 0){
                                                        $this->system->handleError('SOFT_HIDDEN', 'internet');                                                        
                                                    } else {

                                                        $this->software->folder_move($folderInfo['GET_VALUE'], $moveInfo['GET_VALUE']);

                                                        $this->session->addMsg('Software was moved to folder.', 'notice');

                                                    }

                                                    header("Location:internet?view=software&cmd=folder&folder=".$folderInfo['GET_VALUE']);
                                                    exit();
                                                    
                                                } else {
                                                    $this->system->handleError('INEXISTENT_SOFTWARE', 'internet');
                                                }

                                            } else {
                                                $this->system->handleError('INVALID_GET', 'internet?view=software&cmd=folder&folder='.$folderInfo['GET_VALUE']);
                                            }
                                        
                                        } else {
                                            $this->system->handleError('NO_PERMISSION', 'internet');
                                        }
                                        
                                    } else {
                                    
                                        $this->software->folder_show($folderInfo['GET_VALUE'], $hackedInfo['0']['id'], $npc);
                                    
                                    }
                                
                                } else {
                                    $this->system->handleError('INVALID_GET', 'internet');
                                }
                                        
                            } else {
                                
                                header("location:internet");
                                exit();
                                
                            }
                            
                        } elseif($cmdInfo['GET_VALUE'] == 'webserver'){
                            
                            if($hackedInfo['0']['pctype'] == 'VPC'){
                                
                                self::webserver_showPage($hackedInfo['0']['id']);
                                
                            } else {
                                $this->system->handleError('You can not use webserver on NPCs.', 'internet');
                            }
                            
                        } elseif($cmdInfo['GET_VALUE'] == 'riddle'){
                            
                            if($hackedInfo['0']['pctype'] != 'NPC'){
                                $this->system->handleError('No can do', 'internet');
                            }
                            
                            $moreInfo = self::gatherInfo($_SESSION['LOGGED_IN']);

                            if($moreInfo['NPCTYPE'] != 7 && $moreInfo['NPCTYPE'] != 2){
                                $this->system->handleError('No can do', 'internet');
                            }
                            
                            require '/var/www/classes/Riddle.class.php';
                            $riddle = new Riddle();

                            $riddle->show($hackedInfo['0']['id'], $_SESSION['LOGGED_IN']);
                            
                        } else {
                            $this->system->handleError('INVALID_GET', 'internet');
                        }
                        
                    } else {
                        $this->system->handleError('INVALID_GET', 'internet');
                    }
                    
                    
                } elseif ($this->system->issetGet('id')){
                    
                    $idInfo = $this->system->verifyNumericGet('id');

                    if($idInfo['IS_NUMERIC'] == 1){
                    
                        $this->software->viewSoftware($idInfo['GET_VALUE'], $hackedInfo['0']['id'], $hackedInfo['0']['pctype']);
                    
                    } else {
                        $this->system->handleError('INVALID_ID', 'internet');
                    }

                } else {
                    
                    $this->virus->activateViruses($ip);
                    
                    $this->software->showSoftware('0', $hackedInfo['0']['pctype'], $hackedInfo['0']['id']);
      
                    if(isset($_SESSION['MISSION_ID'])){
                        if($_SESSION['MISSION_TYPE'] == 80){
                            $mission = new Mission();
                            $mission->tutorial_update(81);
                        }
                    }
                    
                }

                break;
            case 'logout':
                
                self::deleteConnection();                

                $this->session->deleteInternetSession();

                $this->session->addMsg(sprintf(_('Disconnected from %s.'), '<a href="internet?ip='.long2ip($ip).'">'.long2ip($ip).'</a>'), 'notice');
                
                if($this->system->issetGet('redirect')){
                    $redirectInfo = $this->system->verifyStringGet('redirect');
                    
                    if(!$this->system->validate($redirectInfo['GET_VALUE'], 'ip')){
                        exit("Invalid ip address");
                    }
                    
                    header("Location:internet?ip=".$redirectInfo['GET_VALUE']);
                    exit();
                } else {
                
                    header("Location:internet");
                    exit();

                }

                break;
            case 'clan':
                
                $hardware = new HardwareVPC();
                                                
                if($this->system->issetGet('action')){
                    
                    $actionInfo = $this->system->switchGet('action',  'buy', 'upgrade', 'internet');
                    
                    if($actionInfo['ISSET_GET'] == 1){
                        
                        switch($actionInfo['GET_VALUE']){
                            
                            case 'internet':
                                
                                $hardware->store_showPage('', 'NET', 1);
                                
                                break;
                            
                            case 'buy':
                                
                                $hardware->store_showPage('', 'BUY_PC', 1); 
                                
                                break;
                                
                            case 'upgrade':

                                if($this->system->issetGet('server')){

                                    $serverInfo = $this->system->verifyNumericGet('server');

                                    if($serverInfo['IS_NUMERIC'] == 1){
                                        
                                        $pcInfo = $hardware->getPCSpec($serverInfo['GET_VALUE'], 'NPC', $hackedInfo['0']['id']);
                                        
                                        if($pcInfo['ISSET'] == 1){

                                            $_SESSION['CUR_PC'] = $serverInfo['GET_VALUE']; 

                                            $hardware->store_showPage($pcInfo, 'CPU', 1);

                                        } else {
                                            $this->system->handleError('INEXISTENT_SERVER', 'internet?view=clan');
                                        }                                        
                                        
                                    } else {
                                        $this->system->handleError('INVALID_ID', 'internet?view=clan&action=upgrade');
                                    }                                    

                                } else {

                                    $this->session->addMsg('Choose server to upgrade.', 'error');
                                    header("Location:internet?view=clan");
                                    
                                }
                                
                                break;
                            
                        }
                        
                    } else {
                        $this->system->handleError('INVALID_GET', 'internet?view=clan');
                    }
                    
                } else {
                    
                    $hardware->getHardwareInfo($hackedInfo['0']['id'], 'NPC');
                    $hardware->showPCTotal($hackedInfo['0']['id'], 1);

                }
                
                break;
            case 'bank':

                if ($this->session->issetBankSession()) {

                    if ($this->system->issetGet('bAction')) {

                        $bActionInfo = $this->system->switchGet('bAction', 'show', 'transfer', 'logout');

                        if ($bActionInfo['ISSET_GET'] == '1') {

                            switch ($bActionInfo['GET_VALUE']) {
                                
                                case 'logout':

                                    $this->session->addMsg(sprintf(_('Disconnected from account %s.'), '<strong>#'.$_SESSION['BANK_ACC'].'.</strong>'), 'notice');
                                    
                                    $this->session->deleteBankSession();

                                    header("Location:internet");
                                    exit();

                                    break;
                                case 'show':
                                    
                                    $_SESSION['CUR_PAGE'] = 'bank';
                                    
                                    if($this->menuShown != 1){
                                        self::showActionMenu($_SESSION['BANK_IP']);
                                    }
                                    self::bank_showContent($_SESSION['BANK_ACC'], $_SESSION['BANK_ID']);
                                    
                                    break;
                                
                            }
                        } else {
                            $this->system->handleError('INVALID_GET', 'internet');
                        }
                        
                    } else {

                        self::showActionMenu($_SESSION['BANK_IP']);
                        self::bank_showContent($_SESSION['BANK_ACC'], $_SESSION['BANK_ID']);

                    }
                } else {
                    $this->system->handleError('BANK_NOT_LOGGED', 'internet');
                }

                break;
        }
    }

    private function doLogin($user, $ip, $exploited = '') {

        $_SESSION['LOGGED_IN'] = $ip;
        $_SESSION['LOGGED_USER'] = $user;
        
        switch ($user) {

            case 'root':
                $_SESSION['CHMOD'] = ''; //w = all (r+a)
                if ($exploited != '') {
                    $_SESSION['CHMOD'] = 'w';
                }
                break;
            case 'ftp':
                $_SESSION['CHMOD'] = 'r';
                break;
            case 'ssh':
                $_SESSION['CHMOD'] = 'a';
                break;
            case 'download':
                $_SESSION['CHMOD'] = 'r';
                break;
            case 'clan':
                $_SESSION['CHMOD'] = 'w';
                break;
            default:
                unset($_SESSION['LOGGED_IN']);
                unset($_SESSION['LOGGED_USER']);
                $this->system->handleError('Invalid user.', 'internet');
                break;
        }

        self::addConection($ip);
        
    }

    private function verifyLogin($user, $pass, $ip) {

        $deepInfo = self::gatherInfo($ip);

        if ($user['GET_VALUE'] == 'root') {

            if ($deepInfo['PASS'] == $pass['GET_VALUE']) { //password ok
                return '1';
            } elseif ($pass['GET_VALUE'] == 'exploited') { //user is root and password is exploited
                return '4';
            } else { //doesnt match
                return '2';
            }
        } elseif($user['GET_VALUE'] == 'download') {
            
            if($deepInfo['NPCTYPE'] != 8){
                // 2019: Logging in as `download` but not on Download Center
                return 2; //logando com download em um NPC que nÃ£o Ã© o download center.
            }
            
            if ($pass['GET_VALUE'] == 'download') {
                return 5; //login com user download e senha download
            } else {
                return 2;
            }
                
            
        } elseif($user['GET_VALUE'] == 'clan'){

            if($deepInfo['NPCTYPE'] != 10){
                // 2019: Logging in as `download` but not on Download Center (maybe I meant clan server?)
                return 2; //logando com download em um NPC que nÃ£o Ã© o download center.
            }

            require 'classes/Clan.class.php';
            $clan = new Clan();

            if(!$clan->playerHaveClan()){
                return 2;
            }

            if($clan->getClanInfo($clan->getPlayerClan())->clanip != $ip){
                return 2;
            }

            return 6;

        } else {
            return '3';
        }
        
    }

    public function havePermissionTo($action) {

        $permission = $_SESSION['CHMOD'];

        if ($permission == 'w' || $permission == '') {
            return TRUE;
        }

        switch ($action) {
            case 'download':
            case 'upload':
                $type = 'ftp';
                break;
            default:
                $type = 'ssh';
                break;
        }

        if ($type == 'ftp') {
            
            if ($permission == 'r') {
                return TRUE;
            }
            
        } else {
            
            if ($permission == 'a') {
                return TRUE;
            }
            
        }
        
        return FALSE;
        
    }

    private function refreshConnection() {

        $user = $_SESSION['LOGGED_USER'];

        $ipInfo = $this->player->getIDByIP($_SESSION['LOGGED_IN'], '');
        $ip = $_SESSION['LOGGED_IN'];
        
        $redirect = 0;

        if(!self::issetConnection()){ //this happens if victim changed ip or pass.
            
            self::deleteConnection();
            
            $this->session->addMsg(sprintf(_('The connection to %s doesnt exists anymore. You were disconnected.'), '<a href="internet?ip='.long2ip($ip).'">'.long2ip($ip).'</a>'), 'error');
            
            $this->session->deleteInternetSession();
            
            header("Location:internet");
            exit();
            
        }
        
        
        switch ($user) {
            case 'root':

                if ($_SESSION['CHMOD'] == '') { //usou cracker
                    $crcInfo = $this->software->getBestSoftware('1', '', '', '1');
                    $pecInfo = $this->software->getBestSoftware('2', $ipInfo['0']['id'], $ipInfo['0']['pctype'], '1');

                    if ($pecInfo['0']['exists'] == '0') {
                        $pecInfo['0']['softversion'] = '0';
                    }

                    if ($crcInfo['0']['exists'] == '0') {
                        $this->session->deleteInternetSession();
                        $this->session->addMsg("You do not have the cracker needed to keep logged in - disconnected.", 'error');
                        $redirect = 1;
                    } elseif ($crcInfo['0']['softversion'] < $pecInfo['0']['softversion']) {
                        $this->session->deleteInternetSession();
                        $this->session->addMsg("Your cracker isnt good enough - disconnected.", 'error');
                        $redirect = 1;
                    }
                    
                } else { //tÃ¡ com 2 exploits

                    $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                    $sshExp = $this->software->getBestSoftware('14', '', '', '1');

                    $fwlInfo = $this->software->getBestSoftware('4', $ipInfo['0']['id'], $ipInfo['0']['pctype'], '1');
                    if ($fwlInfo['0']['exists'] == '0') {
                        $fwlInfo['0']['softversion'] = '0';
                    }

                    $ftpGood = '0';
                    if ($ftpExp['0']['exists'] == '1' && $ftpExp['0']['softversion'] >= $fwlInfo['0']['softversion']) {
                        $ftpGood = '1';
                    }

                    $sshGood = '0';
                    if ($sshExp['0']['exists'] == '1' && $sshExp['0']['softversion'] >= $fwlInfo['0']['softversion']) {
                        $sshGood = '1';
                    }

                    if ($ftpExp['0']['exists'] == '0' && $sshExp['0']['exists'] == '0') {
                        $this->session->deleteInternetSession();
                        $this->session->addMsg("You do not have the exploits needed to keep logged in - disconnected.", 'error');
                        $redirect = 1;
                    } elseif (($ftpExp['0']['softversion'] < $fwlInfo['0']['softversion']) && ($sshExp['0']['softversion'] < $fwlInfo['0']['softversion'])) {
                        $this->session->deleteInternetSession();
                        $this->session->addMsg("Your exploits arent good enough - disconnected.", 'error');
                        $redirect = 1;
                    } else {

                        if ($sshGood != '1' || $ftpGood != '1') {
                            
                            if ($sshGood == '1' && $ftpGood == '0') { //must downgrade ftp permissions
                                $_SESSION['CHMOD'] = 'a';
                                $_SESSION['LOGGED_USER'] = 'ssh';
                                $this->session->addMsg("Your FTP exploit isnt good enough anymore, now you have SSH permission only.", 'error');
                                $redirect = 1;
                            } else { //must downgrade ssh permissions
                                $_SESSION['CHMOD'] = 'r';
                                $_SESSION['LOGGED_USER'] = 'ftp';
                                $this->session->addMsg("Your SSH exploit isnt good enough anymore, now you have FTP permission only.", 'error');
                                $redirect = 1;
                            }
                            
                        }
                        
                    }
                }

                break;
            case 'ftp':

                $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
                $fwlInfo = $this->software->getBestSoftware('4', $ipInfo['0']['id'], $ipInfo['0']['pctype'], '1');

                if ($ftpExp['0']['exists'] == '0') {
                    $this->session->deleteInternetSession();
                    $this->session->addMsg("You do not have the FTP exploit anymore - disconnected.", 'error');
                    $redirect = 1;
                } elseif ($ftpExp['0']['softversion'] < $fwlInfo['0']['softversion']) {
                    $this->session->deleteInternetSession();
                    $this->session->addMsg("Your FTP exploit isnt good enough - disconnected.", 'error');
                    $redirect = 1;
                }

                break;
            case 'ssh':

                $sshExp = $this->software->getBestSoftware('14', '', '', '1');
                $fwlInfo = $this->software->getBestSoftware('4', $ipInfo['0']['id'], $ipInfo['0']['pctype'], '1');

                if ($sshExp['0']['exists'] == '0') {
                    $this->session->deleteInternetSession();
                    $this->session->addMsg("You do not have the SSH exploit anymore - disconnected.", 'error');
                    $redirect = 1;
                } elseif ($sshExp['0']['softversion'] < $fwlInfo['0']['softversion']) {
                    $this->session->deleteInternetSession();
                    $this->session->addMsg("Your SSH exploit isnt good enough - disconnected.", 'error');
                    $redirect = 1;
                }

                break;
            case 'download':
                //noop
                break;
        }
        
        if($redirect == 1){
            
            header("Location:internet?ip=".long2ip($ip));
            exit();
            
        }
        
        if($this->session->isInternetLogged()){

            $virus = new Virus();
            
            if($ipInfo['0']['pctype'] == 'NPC'){
                $npc = 1;
            } else {
                $npc = 0;
            }
            
            $doomRemote = $virus->doom_haveInstalled($ipInfo['0']['id'], $npc);
            $doomLocal = $virus->doom_haveInstalled($_SESSION['id'], 0);
                        
            if(($doomRemote != FALSE) && ($doomLocal == FALSE)){ //player remote tem, e eu nao tenho

                $virus->doom_install($doomRemote, $ipInfo['0']['id'], $npc, '', '');
                
            } elseif(($doomLocal != FALSE) && ($doomRemote == FALSE)){ //eu tenho e player remote n tem

                $virus->doom_install($doomLocal, $_SESSION['id'], 0, $ipInfo['0']['id'], $npc);
                
            } elseif(($doomLocal != FALSE) && ($doomRemote != FALSE)){
                
                $doomLocalOrigin = $this->software->getOriginalFrom($doomLocal)->originalfrom;
                $doomRemoteOrigin = $this->software->getOriginalFrom($doomRemote)->originalfrom;

                if($doomLocalOrigin != $doomRemoteOrigin){
                    
                    if(!$virus->doom_specificIsset($doomLocalOrigin, $ipInfo['0']['id'], $npc)){

                        $virus->doom_install($doomLocal, $_SESSION['id'], 0, $ipInfo['0']['id'], $npc);
                        
                    }
                    
                    if(!$virus->doom_specificIsset($doomRemoteOrigin, $_SESSION['id'], 0)){

                        $virus->doom_install($doomRemote, $ipInfo['0']['id'], $npc, '', '');
                        
                    }
                    
                }

            }
            
        }
        
    }

    private function hack($hackType, $id, $ip) {

        $hackedInfo = $this->player->getIDByIP($ip, '');
        $error = '';

        if ($hackType == 'bf' || $hackType == 'bank') {

            $softInfoHacker = $this->software->getBestSoftware('1', '', '', '1');
            $softInfoHacked = $this->software->getBestSoftware('2', $hackedInfo['0']['id'], $hackedInfo['0']['pctype'], '1');
            
            if(!$this->ranking->cert_have('2')){
                
                $error = 'NO_CERTIFICATION';
                
            }
            
        } else {

            $softInfoHacker = $this->software->getBestSoftware('3', '', '', '1'); //portscanner
            $softInfoHacked = $this->software->getBestSoftware('4', $hackedInfo['0']['id'], $hackedInfo['0']['pctype'], '1');
            
            if(!$this->ranking->cert_have('3')){
                
                $error = 'NO_CERTIFICATION';
                
            }
            
        }

        if ($softInfoHacker['0']['exists'] == '0') {
            $error = 'NO_SOFT';
        }

        if ($softInfoHacked['0']['exists'] == '0') {
            $softInfoHacked['0']['softversion'] = '0';
        }

        if ($hackType == 'xp') {

            $ftpExp = $this->software->getBestSoftware('13', '', '', '1');
            $sshExp = $this->software->getBestSoftware('14', '', '', '1');

            $expInfo = '0'; //0 = nenhum, 1 = ftp, 2 = ssh, 3 = os dois

            if ($ftpExp['0']['exists'] == '1') {
                if ($sshExp['0']['exists'] == '1') {
                    $expInfo = '3';
                } else {
                    $expInfo = '1';
                }
            } else {
                if ($sshExp['0']['exists'] == '1') {
                    $expInfo = '2';
                }
            }

            $ftpCanHack = '0';
            if ($expInfo == '1' || $expInfo == '3') {
                if ($ftpExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                    $ftpCanHack = '1';
                    $softInfoHacker['0']['softversion'] = $softInfoHacked['0']['softversion'] + 1;
                }
            }

            $sshCanHack = '0';
            if ($expInfo == '2' || $expInfo == '3') {
                if ($sshExp['0']['softversion'] >= $softInfoHacked['0']['softversion']) {
                    $sshCanHack = '1';
                    $softInfoHacker['0']['softversion'] = $softInfoHacked['0']['softversion'] + 1;
                }
            }
        }

        $pNPC = '0';
        if ($hackedInfo['0']['pctype'] == 'NPC') {
            $pNPC = '1';
        }
        
        if (strlen($error) == '0') {//sem erros
            if ($softInfoHacker['0']['softversion'] >= $softInfoHacked['0']['softversion']) { //crc>enc ou xp>fwl
                $string = 'HACK';
                if ($hackType == 'xp') {
                    $string = 'HACK_XP';
                }

                if ($this->process->newProcess($_SESSION['id'], $string, $hackedInfo['0']['id'], 'remote', '', $hackType, '', $pNPC)) {                                        

                    $this->process->getProcessInfo($_SESSION['pid']);
                    $this->process->showProcess();
                    $this->process->endShowProcess();
                    
                } else {
                    if (!$this->session->issetMsg()) {

                        $pid = $this->process->getPID($_SESSION['id'], $string, $hackedInfo['0']['id'], 'remote', '', $hackType, '', $pNPC);

                        if ($pid != NULL) {

                            $this->session->processID('add', $pid);
                            $this->process->getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            $this->process->showProcess();
                            $this->process->endShowProcess();
                            
                        } else {
                            $this->system->handleError('PROC_NOT_FOUND', 'internet');
                        }
                        
                    } else {
                        header("Location:?action=hack");
                    }
                    
                }
                
            } else {
                $this->system->handleError('BAD_CRACKER', 'internet');
            }
            
        } else {
            $this->system->handleError($error, 'internet');
        }
    }

    private function hack_portScan() {


        $hackedInfo = $this->player->getIDByIP($_SESSION['CUR_IP'], '');

        $pNPC = '0';
        if ($hackedInfo['0']['pctype'] == 'NPC') {
            $pNPC = '1';
        }

        if ($this->process->newProcess($_SESSION['id'], 'PORT_SCAN', $hackedInfo['0']['id'], 'remote', '', '', '', $pNPC)) {

            $this->process->getProcessInfo($_SESSION['pid']);
            $this->process->showProcess();
            $this->process->endShowProcess();
            
        } else { //deu erro ao criar o processo
            
            if (!$this->session->issetMsg()) {

                $pid = $this->process->getPID($_SESSION['id'], 'PORT_SCAN', $hackedInfo['0']['id'], 'remote', '', '', '', $pNPC);

                if ($pid != NULL) {

                    $this->session->processID('add', $pid);
                    $this->process->getProcessInfo($_SESSION['pid']);
                    $this->session->processID('del');

                    $this->process->showProcess();
                    $this->process->endShowProcess();
                    
                } else {
                    $this->system->handleError('PROC_NOT_FOUND', 'internet');
                }
            } else {
                header("Location:?action=hack");
            }
            
        }
        
    }

    private function showLoginForm($ip) {

        $bank = '0';

        if($this->system->issetGet('type')){
            $typeInfo = $this->system->verifyStringGet('type');
            if($typeInfo['IS_NUMERIC'] == '0'){
                if($typeInfo['GET_VALUE'] == 'bank'){
                    $bank = '1';
                }
            }
        }
        
        if($bank == '0'){
        
            $existe = '';
            if ($this->list->isListed($_SESSION['id'], $ip)) { //jÃ¡ invadi, tÃ¡ salvo na minha ip list
                
                if(!$this->list->isUnknown($_SESSION['id'], $ip)){

                    $loginInfo = $this->list->getListedLoginInfo($_SESSION['id'], $ip);

                    $pass = $loginInfo['0']['pass'];
                    if ($loginInfo['0']['pass'] == 'exploited') {
                        $pass = 'exploited';
                    }

                    $user = $loginInfo['0']['user'];
                    $existe = '1';
                
                }
            } else {

                $ipInfo = self::gatherInfo($ip);

                if($ipInfo['PCTYPE'] == 'NPC'){                
                    if($ipInfo['NPCTYPE'] == 10){
                        require 'classes/Clan.class.php';
                        $clan = new Clan();
                        if($clan->playerHaveClan()){
                            if($clan->getClanInfo($clan->getPlayerClan())->clanip == $ip){
                                $existe = 1;
                                $user = 'clan';
                                $pass = 'clan';
                            }
                        }
                    }
                }

            }
            
            $style = '';
            
?>
                            </div>
                                <div id="loginbox">            
                                    <form id="loginform" class="form-vertical" action="" method="GET"/>
                                    <input type="hidden" name="action" value="login">
                                                        <p><?php echo _("Enter username and password to continue."); ?></p>
                                        <div class="control-group">
                                            <div class="controls">
                                                <div class="input-prepend">
                                                    <span class="add-on"><i class="fa fa-user"></i></span><input type="text" name="user" placeholder="<?php echo _("Username"); ?>" value="<?php if($existe == 1){ echo $user; } ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <div class="controls">
                                                <div class="input-prepend">
                                                    <span class="add-on"><i class="fa fa-lock"></i></span><input type="password" name="pass" placeholder="<?php echo _("Password"); ?>" value="<?php if($existe == 1){ echo $pass; } ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-actions">
<?php 
            if($existe == 1){
                $style = 'style="margin-top: -20px;"';
?>
                                            <span class="small pull-left" style="margin-top: -5px;"><strong><?php echo _("Username"); ?></strong>: <?php echo $user; ?></span><br/>
                                            <span class="small pull-left" style="margin-top: -5px;"><strong><?php echo _("Password"); ?></strong>: <?php echo $pass; ?></span>
<?php
            }
?>
                                            <span class="pull-right" <?php echo $style; ?>><input type="submit" class="btn btn-inverse" value="<?php echo _("Login"); ?>" /></span>
                                        </div>
                                    </form>

                                </div>
                                <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                                
                            </div>
                            
<?php self::show_ad(); ?>


                        </div>
<?php
        
            self::show_sideBar();
            
        } else {
            
            self::bank_showDesc($ip);
            
        }
        
    }

    private function showHeader($ip) {
                
?>
                    <div class="span9">
                        <div class="widget-box">
                            <div class="widget-content padding">
                                <div class="span12">
                                    <form action="" method="get" class="form-horizontal">
                                        <div class="browser-input">
                                            <?php echo _("IP address"); ?>: <input class="browser-bar" name="ip" type="text" value="<?php echo long2ip($ip); ?>"/>
                                            <input type="submit" class="btn btn-inverse" value="<?php echo _("Go"); ?>">
                                            <div style="float: right; padding-top: 5px;" class="hide-phone">
                                                <a href="internet?ip=<?php echo long2ip(self::home_getIP()); ?>"><span class="he16-home" title="<?php echo _("Home"); ?>" style="margin-top: 4px;"></span></img></a>
                                                <a href="internet"><span class="he16-refresh" title="<?php echo _("Refresh"); ?>" style="margin-top: 4px;"></span></a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div style="clear: both;"></div>
                            </div>
                        </div>
<?php
                
    }

    private function showActionMenu($ip) {

        if($this->menuShown != 1){

            $this->menuShown = 1;

            $deepInfo = self::gatherInfo($ip);
 
            if ($this->session->isInternetLogged() == TRUE && $ip == $_SESSION['LOGGED_IN']){

                $active = ' active';
                $span = 'span9';

?>
                    </div>
                        <div style="text-align: center;">
<?php if($_SESSION['premium'] != 1) { ?>
<style type="text/css">
@media (min-width : 320px) { .adslot_internet_un { width: 234px; height: 60px;} }
@media (min-width : 360px) and (max-width : 480px) { .adslot_internet_un { width: 320px; height: 50px;} }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_internet_un { width: 320px; height: 50px; } }
@media (min-width:1024px) { .adslot_internet_un { width: 125px; height: 125px;} }
@media (min-width:1280px) { .adslot_internet_un { width: 234px; height: 60px; margin-top: 20px} }
@media (min-width:1366px) { .adslot_internet_un { width: 234px; height: 60px; margin-top: 20px} }
@media (min-width:1824px) { .adslot_internet_un { width: 320px; height: 100px; margin-top: 0px} }
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- internet responsive -->
<ins class="adsbygoogle adslot_internet_un"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="5776909757"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<?php } ?>
                        </div>
<div class="widget-box">
                        <div class="widget-title">
                            <ul class="nav nav-tabs">   
                                <li class="link<?php if($_SESSION['CUR_PAGE'] == 'logs') { echo $active; $span = 'span12'; }?>"><a href="?view=logs"><span class="icon-tab he16-internet_log"></span><?php echo _("Logs"); ?></a></li>
                                <li class="link<?php if($_SESSION['CUR_PAGE'] == 'software') { echo $active; } ?>"><a href="?view=software"><span class="he16-software icon-tab"></span><?php echo _("Softwares"); ?></a></li>
<?php
                if($this->session->issetBankSession() && $_SESSION['BANK_IP'] == $ip){
?>    
                                <li class="link<?php if($_SESSION['CUR_PAGE'] == 'bank') { echo $active; } ?>"><a href="?bAction=show"><span class="icon-tab he16-internet_bOverview"></span><?php echo _("Account overview"); ?></a></li>    
<?php
                } elseif($deepInfo['NPCTYPE'] == '10'){
?>
                                <li class="link<?php if($_SESSION['CUR_PAGE'] == 'clan') { echo $active; $span = 'span12 center'; } ?>"><a href="?view=clan"><span class="icon-tab he16-servers"></span><?php echo _("Clan server"); ?></a></li>    
<?php 
                }
?>
                                <li class="link<?php if($_SESSION['CUR_PAGE'] == 'logout') { echo $active; } ?>"><a href="?view=logout"><span class="icon-tab he16-internet_logout"></span><span class="hide-phone"><?php echo _("Logout"); ?></span></a></li>
                                <a href="<?php echo $this->session->help('internet'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                            </ul>
                        </div>
                        <div class="widget-content padding noborder">
                            <div class="span12">
                                <div class="<?php echo $span; ?>">
<?php

            } else {

                $login = '';
                
                if(!$this->session->issetBankSession()){
                    $index = ' active';
                    $link = '';
                } else {
                    $index = '';
                    $link = '?view=index';
                }
                
                if($this->session->isHacking()){
                    $index = '';
                    $link = '';
                    $login = ' active';                
                }
                    
                if($this->system->issetGet('view')){
                    $viewInfo = $this->system->verifyStringGet('view');
                    if($viewInfo['GET_VALUE'] == 'index'){
                        $index = ' active';

                    }
                }

                if($index != ' active'){
?>
                    </div>           
<?php
                }
                
                if($this->session->isInternetLogged()){
                    $redirIP = long2ip($_SESSION['LOGGED_IN']);
                    $loggedIP = '<a href="internet?ip='.$redirIP.'">'.$redirIP.'</a>';
                            ?>
                                <div class="alert">
                                    <?php echo _('<strong>Warning! </strong>You are currently logged to '); ?><strong><?php echo $loggedIP; ?></strong>.
                                    <?php echo sprintf(_('Would you like to %slog out%s?'), '<a href="internet?view=logout&redirect='.long2ip($ip).'">', '</a>'); ?>
                                </div>
                            <?php
                }
                
?>
                        <div class="widget-box">
                            <div class="widget-title">
                                <ul class="nav nav-tabs">   
                                    <li class="link<?php echo $index; ?>"><a href="internet<?php echo $link; ?>"><span class="he16-index icon-tab"></span></span><?php echo _("Index"); ?></a></li>
                                    <li class="link<?php echo $login; ?>"><a href="?action=login"><span class="he16-login icon-tab"></span></span><?php echo _("Login"); ?></a></li>
                                    <li class="link"><a href="?action=hack"><span class="icon-tab he16-internet_hack"></span><?php echo _("Hack"); ?></a></li>
<?php
                if($deepInfo['NPCTYPE'] == 1 && $this->session->issetBankSession() == FALSE){
?>
                                    <li class="link"><a href="?action=login&type=bank"><span class="icon-tab he16-internet_bLogin"></span><?php echo _("Account login"); ?></a></li>
                                    <li class="link"><a href="?action=hack&type=bank"><span class="icon-tab he16-internet_bHack"></span><?php echo _("Hack account"); ?></a></li>
<?php
                    if (!$this->finances->isUserRegisteredOnBank($_SESSION['id'], $deepInfo['NPCID'])) {
?>
                                    <li class="link"><a href="?action=register"><span class="icon-tab he16-internet_bRegister"></span><?php echo _("Register account"); ?></a></li>
<?php
                    }

                } elseif($this->session->issetBankSession()){
?>
                                    <li class="link<?php if($index == ''){ echo ' active'; } ?>"><a href="?bAction=show"><span class="icon-tab he16-internet_bOverview"></span><?php echo _("Account overview"); ?></a></li>    
<?php    
                }

?>
                                </ul>
                            </div>
                            <div class="widget-content padding noborder">
                                <div class="span12">
<?php
                $down = '0';
                if($deepInfo['PCTYPE'] == 'NPC'){

                    if(self::isImportant($deepInfo['NPCTYPE'])){
                        if(!self::important_isset($ip)){
                            self::important_add($ip);
                        }
                    }
                    
                    if($this->npc->downNPC($this->ipInfo['0']['id'])){
                        $down = '1';
                    }
                    
                    $tag = self::getTag($deepInfo['NPCTYPE']);

                } else {
                    $tag = self::getTag('VPC');
                }
                
                if(!$this->session->isHacking() && (!$this->session->issetBankSession() || $this->system->issetGet('view'))){
                              
                    self::history_add($ip);
   
                    if($down == '0'){
                        $status = '<font color="green"><b>online</b></font>';
                    } else {
                        $status = '<font color="red"><b>OFFLINE</b></font>';
                    }
?>
                                    <p><?php echo sprintf( _('Server %1$s is %2$s'), long2ip($ip), $status); ?><?php echo $tag; ?></p>
<?php                                        

                }
                
            }

        }
                                       
    }
    
    public function getDCIP(){
        
        $this->session->newQuery();
        $sqlSelect = "  SELECT npcIP
                        FROM npc
                        WHERE npcType = 8
                        LIMIT 1";
        return long2ip($this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->npcip);
        
    }

    private function showPageInfo($ip, $down='0') {

        if($down == '0'){
        
            $deepInfo = self::gatherInfo($ip);
            
            if ($this->session->isInternetLogged() == TRUE && $ip == $_SESSION['LOGGED_IN']) {

                header("Location:internet");
                
            } else {

                if ($deepInfo['PCTYPE'] == 'NPC') { //Ã© um npc
                    
                    if($deepInfo['NPCTYPE'] == 2){
                        
                        $sendToDC = FALSE;
                        $sendToPuzzle = FALSE;
                        $scriptStr = '';

                        $bestCracker = $this->software->getBestSoftware(1, $_SESSION['id'], 'VPC'); 
                        if($bestCracker['0']['exists'] == 0){
                            //sem cracker
                            $sendToDC = TRUE;
                        }
                        
                        

                        if(!$sendToDC){
                             if($this->software->totalSoftwares($_SESSION['id'], 'VPC') <= 3){
                                 //poucos softwares
                                 $sendToDC = TRUE;
                             }
                        }

                        if($sendToDC){
                            $dcIP = self::getDCIP();
                            $scriptStr .= 'var telldc = "'.$dcIP.'";';
                            $scriptStr .= 'var dctitle = "'._('Download more softwares.').'";var dcdesc="'._('Need more softwares? You might want to take a look at the ').'<a class=\"notify-link\" href=\"internet?ip='.$dcIP.'\">'._('Download Center').'</a>.'.'";';
                        }

                        require '/var/www/classes/Riddle.class.php';
                        $riddle = new Riddle();

                        if($riddle->getLatestSolved() == 0){
                            $sendToPuzzle = TRUE;
                            $firstRiddleIP = $riddle->getFirstRiddle();
                            $scriptStr .= 'var tellpz = "'.$firstRiddleIP.'";';
                            $scriptStr .= 'var pztitle = "'._('Puzzle Campaign!').'"; var pzdesc = "'._('So you are new here, uh? A good place to start is the First Puzzle located at ').'<a class=\"notify-link\" href=\"internet?ip='.$firstRiddleIP.'\">'.$firstRiddleIP.'</a>.";';
                        }
                        
                        
                        
                        if($sendToDC || $sendToPuzzle){
                            echo '<script type="text/javascript">'.$scriptStr.'</script>';
                        }
                       
                    }
                    
                    switch($deepInfo['NPCTYPE']){
                        
                        case 5:
                            
                            self::ISP_index();
                            
                            break;
                        case 40:
                            
                            self::bitcoin_index($deepInfo['NPCID']);
                            
                            break;
                        case 45:
                            
                            self::torrent_index($deepInfo['NPCID']);
                            
                            break;
                        case 50:
                        
                            require '/var/www/classes/Storyline.class.php';
                            $storyline = new Storyline();

                            $storyline->safenet_list();

                            break;
                        case 51:
                            
                            require '/var/www/classes/Storyline.class.php';
                            $storyline = new Storyline();

                            $storyline->fbi_list();
                            
                            break;
                        case 99:
                            echo "<p>" . _('No webserver running at ') . long2ip($ip) . "</p>";
                            break;
                        default:
                            
?>
                                    <p><?php echo $deepInfo['WEBDESC']; ?></p>
<?php
                                                        
                            break;
                            
                    }
                    
                } else { //Ã© vpc

                    $webInfo = self::webserver_getInfo($this->ipInfo['0']['id']);
                    
                    if($webInfo['active'] == 0){
                    
                        echo "<p>" . 'No webserver running at ' . long2ip($ip) . "</p>";
                    
                    } else {
                        
                        echo "<p>" . strip_tags($webInfo['webdesc']) . "</p>";
                        
                    }
                }

            }
        
        } else {
            echo 'Couldnt retrive server information - server is down';
        }
        
?>
                            </div>
                        </div>
                        <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                    </div>

                    <?php self::show_ad(); ?>
                                
                </div>
<?php
        
        self::show_sideBar();

    }

    public function show_sideBar(){
        
?>
                <div class="span3">
<?php
                                        
        self::history_show();

        self::important_show();
 
        
    }
    
    private function gatherInfo($ip) {

        $ipInfo = $this->player->getIDByIP($ip, '');

        if ($ipInfo['0']['pctype'] == 'NPC') { //Ã© um npc
            $this->session->newQuery();

            $language_table = 'npc_info_en';

            if($this->session->l == 'pt_BR'){
                $language_table = 'npc_info_pt';
            }
            
            $sql = 'SELECT id, npcType, npcPass, '.$language_table.'.name, '.$language_table.'.web AS npcWeb
                    FROM npc
                    LEFT JOIN '.$language_table.'
                    ON '.$language_table.'.npcID = npc.id
                    WHERE npcIP = :ip';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':ip' => $ip));
            $searchInfo = $stmt->fetchAll();

            $return = Array(
                'PCTYPE' => 'NPC',
                'NPCID' => $searchInfo['0']['id'],
                'WEBDESC' => $searchInfo['0']['npcweb'],
                'NPCTYPE' => $searchInfo['0']['npctype'],
                'PASS' => $searchInfo['0']['npcpass'],
                'NAME' => $searchInfo['0']['name'],
            );
        } else { //Ã© vpc
            $this->session->newQuery();
            $sql = 'SELECT gamePass FROM users WHERE gameIP = :ip';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':ip' => $ip));
            $searchInfo = $stmt->fetchAll(); 
            
            $return = Array(
                'NPCTYPE' => '',
                'PCTYPE' => 'VPC',
                'PASS' => $searchInfo['0']['gamepass'],
            );
        }

        return $return;
    }

    private function bank_showDesc($ip) {

        $deepInfo = self::gatherInfo($ip);
        $npcID = $deepInfo['NPCID'];

        if ($this->finances->isUserRegisteredOnBank($_SESSION['id'], $deepInfo['NPCID'])) {

            $accInfo = $this->finances->getBankAccountInfo($_SESSION['id'], $deepInfo['NPCID']);

            $bankAcc = $accInfo['BANK_ACC'];
            $bankPass = $accInfo['BANK_PASS'];
            
        } else {

            $bankAcc = $bankPass = '';
                        
        }

        self::bank_loginForm($npcID, $bankAcc, $bankPass);
        
    }

    private function bank_loginForm($npcID, $bankAcc, $bankPass) {

        $shown = '0';

        $acc = $bankAcc;
        $pass = $bankPass;
        
        if (isset($_SESSION['HACKED_ACC_BANK'])) {

            if ($_SESSION['HACKED_ACC_BANK'] == $npcID) {

                $acc = $_SESSION['HACKED_ACC_NUMBER'];
                $pass = $_SESSION['HACKED_ACC_PASS'];
                
                unset($_SESSION['HACKED_ACC_NUMBER']);
                unset($_SESSION['HACKED_ACC_PASS']);
                unset($_SESSION['HACKED_ACC_BANK']);
            }
            
        }
        

        ?>        

                    </div>
                        <div id="loginbox">            
                            <form id="loginform" class="form-vertical" action="" method="GET"/>
                            <input type="hidden" name="action" value="login">
                                                <p><?php echo _("Enter account number and password to continue."); ?></p>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on"><i class="fa fa-user"></i></span><input type="text" name="acc" placeholder="<?php echo _("Account #"); ?>" value="<?php echo $acc; ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on"><i class="fa fa-lock"></i></span><input type="password" name="pass" placeholder="<?php echo _("Password"); ?>" value="<?php echo $pass; ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
<?php
if($bankAcc == ''){
?>
                                    <span class="small pull-left" style="margin-top: -5px;"><?php echo _("You do not have an account on this bank."); ?></span><br/>
                                    <span class="small pull-left" style="margin-top: -5px;"><?php echo sprintf(_('%s Create one %s now'), '<a href="?action=register" class="black">', '</a>'); ?></span>
<?php
} else {
?>
                                    <span class="small pull-left" style="margin-top: -5px;"><strong><?php echo _("Account #"); ?></strong>: <?php echo $bankAcc; ?></span><br/>
                                    <span class="small pull-left" style="margin-top: -5px;"><strong><?php echo _("Password"); ?></strong>: <?php echo $bankPass; ?></span>                            
<?php
}
?>

                                    <span class="pull-right" style="margin-top: -20px;"><input type="submit" class="btn btn-inverse" value="<?php echo _("Login"); ?>" /></span>
                                </div>
                            </form>

                        </div>
                        <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                    </div>
    
                    <?php self::show_ad(); ?>
    
                </div>

                 
        <?php
        
        self::show_sideBar();
        
    }

    private function bank_hack($id, $bankAcc, $bankID) {

        $softInfoHacker = $this->software->getBestSoftware('1', '', '', '1');
        $softInfoHacked = $this->software->getBestSoftware('2', $bankID, 'NPC', '1');

        $error = '';

        if ($softInfoHacker['0']['exists'] == '0') {
            $error = 'NO_SOFT';
        }

        if ($softInfoHacked['0']['exists'] == '0') {
            $softInfoHacked['0']['softversion'] = '0';
        }

        if ($softInfoHacker['0']['softversion'] < $softInfoHacked['0']['softversion']) {         
            $error = 'BAD_CRACKER';
        }
        
        if (strlen($error) == '0') {
            
                if ($this->process->newProcess($_SESSION['id'], 'BANK_HACK', $bankID, 'remote', '', $bankAcc, '', '1')) {

                    $this->process->getProcessInfo($_SESSION['pid']);
                    $this->process->showProcess();
                    $this->process->endShowProcess();
                    
                } else { //deu erro ao criar o processo
                    
                    if (!$this->session->issetMsg()) {
                        $pid = $this->process->getPID($_SESSION['id'], 'BANK_HACK', $bankID, 'remote', '', $bankAcc, '', '1');

                        if ($pid != NULL) {

                            $this->session->processID('add', $pid);
                            $this->process->getProcessInfo($_SESSION['pid']);
                            $this->session->processID('del');

                            $this->process->showProcess();
                            $this->process->endShowProcess();
                        } else {
                            $this->system->handleError('PROC_NOT_FOUND', 'internet');
                        }
                        
                    } else {
                        header("Location:?action=hack&acc=$bankAcc");
                    }
                    
                }
            
        } else {
            $this->system->handleError($error, 'internet?action=hack&type=bank');
        }
        
    }

    private function bank_showContent($bankAcc, $bankID) {
        
        if ($this->finances->issetBankAccount($bankAcc, $bankID)) {
            
            $accID = $this->finances->getIDByBankAccount($bankAcc, $bankID);

            $bankInfo = $this->finances->getBankAccountInfo($accID, $bankID, $bankAcc);
            
            if($bankInfo['USER'] != $_SESSION['id']){
                //TODO: get my bank acc (and ip) to autofill
            }
            
            if($this->session->isInternetLogged()){
                echo '</div>';
            }
            
?>
                                    <div class="span12 center">
                                        <ul class="finance-box">
                                            <li>
                                                <div class="left"><span class="heicon he32-safe"></span></div>
                                                <div class="right">
                                                    <strong>$<?php echo number_format($bankInfo['CASH']); ?></strong>
                                                </div>
                                            </li>                                                        
                                        </ul>               
                                    </div>
                                    <div class="row-fluid">
                                        <div class ="span9">
                                            <div class="widget-box padding">
                                                <div class="widget-title">
                                                    <span class="icon"><span class="he16-transfer"></span></span><h5><?php echo _('Transfer money'); ?></h5>
                                                </div>
                                                <div class="widget-content nopadding">
                                                    <form action="" method="POST" class="form-horizontal">
                                                        <input type="hidden" name="int-act" value="transfer">
                                                        <div class="control-group">
                                                            <label class="control-label"><?php echo _('From'); ?></label>
                                                            <div class="controls">
                                                                <input type="text" value="<?php echo $bankAcc; ?>" disabled/>
                                                            </div>
                                                            <label class="control-label"><?php echo _('To'); ?></label>
                                                            <div class="controls">
                                                                <input type="text" placeholder="<?php echo _('Transfer to...'); ?>" name="acc" autofocus="1"/>
                                                            </div>
                                                            <label class="control-label"><?php echo _('Amount'); ?></label>
                                                            <div class="controls">
                                                                <input id="money" type="text" placeholder="<?php echo _('Amount of money'); ?>" name="money" value="<?php echo $bankInfo['CASH']; ?>"/>
                                                            </div>
                                                            <label class="control-label"><?php echo _('Bank IP'); ?></label>
                                                            <div class="controls">
                                                                <input type="text" placeholder="<?php echo _('IP of the receiver account'); ?>" name="ip"/>
                                                            </div>                                                            
                                                        </div>
                                                        <div class="form-actions">
                                                            <button type="submit" class="btn btn-success"><?php echo _('Transfer Money'); ?></button>
                                                        </div>                    
                                                    </form>
                                                </div>
                                            </div>                                                        
                                        </div>
                                        <div class="span3" style="padding-right: 15px;">
                                            <div class="widget-box padding">
                                                <div class="widget-title">
                                                    <span class="icon"><i class="he16-actions"></i></span><h5><?php echo _('Other actions'); ?></h5>
                                                </div>
                                                <div class="widget-content padding center">
                                                    <span id="bchgpwd" class="btn btn-inverse"><?php echo _('Change password'); ?></span>
                                                    <span id="bendacc" class="btn btn-danger"><?php echo _('Close account'); ?></span>
                                                    <br/><br/><br/><a class="btn btn-info" href="?bAction=logout"><?php echo _('Logout'); ?></a>
                                                </div>
                                                <span id="modal"></span>
                                            </div>
                                        </div>   
                                    </div>                        
                                </div>
                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
<?php
        } else {
            
            $this->session->deleteBankSession();
            $this->system->handleError('INEXISTENT_ACC', 'internet');
            
        }
    }
    
    private function bank_hackForm(){
        
        ?>        

                <div class="widget-box reply-area">
                    <div class="widget-title">                                                            
                        <span class="icon"><i class="fa fa-arrow-right"></i></span>
                        <h5>Hack bank account</h5>
                    </div>                            
                    <div class="widget-content nopadding"> 
                        <form action="" method="GET" class="form-horizontal">
                            <input type="hidden" name="action" value="hack">   
                            <div class="control-group">
                                <label class="control-label">Account #</label>
                                <div class="controls">
                                    <input type="text" placeholder="Account to hack" name="acc" autofocus="1"/>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-inverse">Hack</button>
                            </div>                    
                        </form>
                    </div>
                </div>
            </div>
        <div style="clear: both;" class="nav nav-tabs"></div>
        </div>
        
        <?php self::show_ad(); ?>
        
    </div>

<?php
        
        self::show_sideBar();

    }
    
    public function show_ad(){
        if($_SESSION['premium'] == 1) return;
?>
    
                            <div class="center" style="margin-bottom: 20px;">
<style type="text/css">
@media (min-width : 320px) and (max-width : 480px) { .adslot_internet_un { width: 250px; height: 250px; } }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_internet_un { width: 336px; height: 280px; } }
@media (min-width:1024px) { .adslot_internet_un { width: 728px; height: 90px; } }
@media (min-width:1280px) { .adslot_internet_un { width: 728px; height: 90px; } }
@media (min-width:1366px) { .adslot_internet_un { width: 728px; height: 90px; } }
@media (min-width:1824px) { .adslot_internet_un { width: 970px; height: 90px; } }
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- internet responsive -->
<ins class="adsbygoogle adslot_internet_un"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="5776909757"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
                            </div>
    
<?php
        
    }
    
    private function addConection($ip){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(userID) AS total FROM internet_connections WHERE userID = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        if($data->total == 1){
            
            $this->session->newQuery();
            $sql = "DELETE FROM internet_connections WHERE userID = '".$_SESSION['id']."' LIMIT 1";
            $this->pdo->query($sql);
            
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO internet_connections (userID, ip) VALUES ('".$_SESSION['id']."', '".$ip."')";
        $this->pdo->query($sql);
        
    }
    
    private function issetConnection(){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(userID) AS total FROM internet_connections WHERE userID = '".$_SESSION['id']."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        if($data->total == 1){
            return TRUE;
        }
        
        return FALSE;
        
    }
    
    public function deleteConnection(){
        
        $this->session->newQuery();
        $sql = "DELETE FROM internet_connections WHERE userID = '".$_SESSION['id']."' LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function torrent_index($npcID){
        
?>
    
    F.L.I.E.N.D.S (1.0) - <span class="green">$0.1</span><span class="small nomargin"> / GB</span><br/>
    Winblows 8.1 (2.0) - <span class="green">$0.2</span><span class="small nomargin"> / GB</span><br/>
    Fotoshop CS6 (3.0) - <span class="green">$0.3</span><span class="small nomargin"> / GB</span>
    
<?php
        
    }
    
    public function bitcoin_index($npcID){
        
        $_SESSION['START_NPC'] = 'bitcoin();';
        
        if(!$this->finances->userHaveWallet($_SESSION['id'], $npcID)){
            $haveWallet = FALSE;
        } else {
            $haveWallet = TRUE;
        }
        
        if($this->session->issetWalletSession()){
            $isLogged = TRUE;
        } else {
            $isLogged = FALSE;
        }
        
        if($isLogged){
            $actClass = ' class="link"';
            $overviewTitle = 'Wallet Overview';
        } else {
            $actClass = ' class="unlogged unlogged-tip" title="Please login first"';
            $overviewTitle = 'Wallet Login';
        }
        
        if($haveWallet){
            if($isLogged){
                $walletInfo = $this->finances->getWalletInfoByAddress($_SESSION['WALLET_ADDR']);
            } else {
                $walletInfo = $this->finances->getWalletInfo($this->finances->bitcoin_getID());
            }
            $walletAddress = $walletInfo->address;
            $walletKey = $walletInfo->key;
        } else {
            $walletAddress = $walletKey = '';
            if($isLogged){
                $walletInfo = $this->finances->getWalletInfoByAddress($_SESSION['WALLET_ADDR']);
            }
        }
        
        $btcValue = $this->finances->bitcoin_getValue();    
        
?>
                <div class="row-fluid">
                    <div class="span8">
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-btc"></span></span>
                                <h5><?php echo _('Bitcoin Actions'); ?></h5>
                            </div>
                            <div class="alert alert-success nomargin">
                                <center><?php echo _('Only public information is logged!'); ?></center>
                            </div>
                            <div class="widget-content padding center">

                                    <ul class="quick-actions" >
                                        <li id="btc-buy" <?php echo $actClass; ?> >
                                            <a>
                                                <i class="icon- he32-btc-buy"></i>
                                                <?php echo _('Buy Bitcoins'); ?>
                                                <br/>
                                                <span class="small">for <span class="green">$<?php echo $btcValue; ?></span></span>
                                            </a>
                                        </li>
                                        <li id="btc-sell" <?php echo $actClass; ?>>
                                            <a>
                                                <i class="icon- he32-btc-sell"></i>
                                                <?php echo _('Sell Bitcoins'); ?>
                                                <br/>
                                                <span class="small">for <span class="green">$<?php echo $btcValue; ?></span></span>
                                            </a>
                                        </li>
                                        <li id="btc-transfer" <?php echo $actClass; ?>>
                                            <a>
                                                <i class="icon- he32-btc-transfer"></i>
                                                <?php echo _('Transfer Bitcoins'); ?>
                                                <br/>
                                                <span class="small"><?php echo _('No fees!'); ?></span>
                                            </a>
                                        </li>
                                    </ul>

                            </div>
                        </div>
                    </div>
                    <div class="span4">
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-bank_bag"></span></span>
                                <h5><?php echo _($overviewTitle); ?></h5>
                            </div>
<?php
    if($isLogged){
?>
                            <div class="widget-content padding center">
                                <div style="overflow: hidden;">
                                    <strong><?php echo _('Address'); ?>:</strong> <br/><?php echo $_SESSION['WALLET_ADDR']; ?><br/><br/>
                                    <strong>Bitcoins:</strong> <br/><?php echo $walletInfo->amount; ?> BTC<br/><br/>
                                    <btn id="btc-logout" class="btn btn-info"><?php echo _('Log out'); ?></btn>
                                </div>
                            </div>
<?php
    } else {
?>
                            <div class="alert alert-success nomargin">
                                <center><?php echo _('Anonymous login'); ?>!</center>
                            </div>
                            <div class="widget-content padding center">
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on"><i class="fa fa-user"></i></span><input type="text" id="btc-address" name="address" placeholder="<?php echo _('Address'); ?>" style="width: 80%" value="<?php echo $walletAddress; ?>"><br/>
                                        </div>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <div class="controls">
                                        <div class="input-prepend">
                                            <span class="add-on"><i class="fa fa-lock"></i></span><input type="password" id="btc-key" name="key" placeholder="<?php echo _('Private Key'); ?>" style="width: 80%" value="<?php echo $walletKey; ?>"><br/>
                                        </div>
                                    </div>
                                </div>
                                <btn id="btc-login" class="btn btn-inverse"/><?php echo _('Login'); ?></btn>
<?php
    if(!$haveWallet){
?>
                                or <btn id="btc-register" class="btn btn-success"/>Register for free</btn>
<?php
    }
?>
                            </div>
<?php
    }
?>
                        </div>
                    </div>
                </div>
        <span id="modal" class="center"></span>
    
    <script type="text/javascript">
        
        <?php if($isLogged){ ?>
            var logged = true;
        <?php } else { ?>
            var logged = false;
        <?php } ?>

    </script>
    
<?php
        
    }
    
    public function ISP_index(){
        
        echo 'HEX Internet Service Provider - ISP'."<br/><br/>";

        if(!$this->process->issetProcess($_SESSION['id'], 'RESET_IP', '', 'local', '', '', '')){

            $ipInfo = $this->player->ip_info();

            if($ipInfo['PRICE'] == 0){

                $str = _('You can reset your IP <b>for free</b>!');
                $priceStr = 'free ';

            } else {

                $formatedPrice = number_format($ipInfo['PRICE'], 0, '.', ',');

                $str = 'You can wait <b><font color="red">'.$ipInfo['NEXT_RESET'].'</font></b> for your next free reset or pay $<b>'.$formatedPrice.'</b> for our charged IP reset service.<br/>';
                $str = sprintf(_('You can wait %s for your next free reset or pay %s for our charged IP reset service.<br/>'), '<b><font color="red">'.$ipInfo['NEXT_RESET'].'</font></b>', '$<b>'.$formatedPrice.'</b>');
                $priceStr = _('for $').$formatedPrice;

            }

            echo $str;

            if($this->finances->totalMoney() >= $ipInfo['PRICE']){

            ?>
                <br/><br/>
                <form action="resetIP" method="POST">

                    <?php

                    if($ipInfo['PRICE'] > 0){

                        $this->finances->htmlSelectBankAcc();

                    }

                    ?>

                    <input class="btn btn-success" type="submit" value="<?php echo _('Reset '); echo $priceStr; ?>">

                </form>

            <?php

            } else {
                echo "<br/>"._('Oops! You do not have enough money to buy an IP reset.');
            }

        } else {
            
            if($this->session->issetProcessSession()){
                $pid = $this->session->processID('show');
                $this->session->processID('del');
            } else {
                $pid = $this->process->getPID($_SESSION['id'], 'RESET_IP', '', 'local', '', '', '', 0);                
            }
                        
            $this->process->getProcessInfo($pid);
            $this->process->showProcess();
            
        }
        
    }
    
    public function webserver_getInfo($userID){
        
        $arr = Array();
        
        $this->session->newQuery();
        $sql = "SELECT webDesc, active FROM internet_webserver WHERE id = '".$userID."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();        
        
        if(sizeof($data) == 1){
            
            $arr['existe'] = 1;
            $arr['active'] = $data['0']['active'];
            $arr['webdesc'] = $data['0']['webdesc'];
            
        } else {
            $arr['existe'] = 0;
            $arr['active'] = 0;
            $arr['webdesc'] = '';
        }
        
        return $arr;
        
    }
    
    public function webserver_showPage($id = ''){
        
        if($id == ''){
            $id = $_SESSION['id'];
            $local = 1;
            $back = 'software';
        } else {
            $local = 0;
            $back = 'internet?view=software';
        }
        
        if(!$this->player->isPremium($id)){
            
            $errorMsg = _('Only <a href="premium">premium</a> users can have active webservers');
            $redirect = 'software';
            
            if($local == 0){
                $errorMsg .= _(', and the user you are logged at is not premium.');
                $redirect = 'internet?view=software';
            }
            
            $errorMsg .= '.';
            
            $this->system->handleError($errorMsg, $redirect);
            
        }

        $webInfo = self::webserver_getInfo($id);

        if($webInfo['active'] == 0){
            $btnText = 'Activate web server';
        } else {
            $btnText = 'Set new web server text';
        }
                
?>
     
                            <div class="widget-box">
                                <div class="widget-title">
                                    <span class="icon">
                                        <i class="icon-arrow"></i>									
                                    </span>
                                    <h5>Web Server text</h5>
                                </div>
                                <div class="widget-content nopadding">
                                    <form action="webserver" method="POST" class="form-horizontal" />
                                        <input type="hidden" name="uid" value="<?php echo $id; ?>">
                                        <div class="control-group">
                                            <label class="control-label">Web Server Content</label>
                                            <div class="controls">
                                                    <textarea name="content"><?php echo $webInfo['webdesc']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-success"><?php echo $btnText; ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <br/>
                        <div class="center">
                            <ul class="soft-but">
                                <li>
                                    <a href="<?php echo $back; ?>">
                                        <i class="icon-" style="background-image: none;"><span class="he32-root"></span></i>
                                        Back to root
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                    <?php if($local == 0) { ?></div><?php } ?>

                </div>
                <div class="nav nav-tabs"></div>
        <?php    

    }
    
    public function webserver_setup($uid, $desc, $softID){

        $softInfo = $this->software->getSoftware($softID, $uid, 'VPC');

        if($softInfo->softhidden != 0){
            $this->system->handleError('SOFT_HIDDEN', 'internet');
        }
        
        if(!$this->software->isInstalled($softID, $uid, 'VPC')){

            $hardware = new HardwareVPC();
            $ramInfo = $hardware->calculateRamUsage($uid, 'VPC');
            
            if($ramInfo['AVAILABLE'] < $softInfo->softram){
                $this->system->handleError('INSUFFICIENT_RAM', 'internet');
            }
            
            $this->session->newQuery();
            $sqlQuery = "INSERT INTO software_running (id, softID, userID, ramUsage, isNPC) VALUES ('', ?, ?, ?, 0)";
            $sqlReg = $this->pdo->prepare($sqlQuery);
            $sqlReg->execute(array($softID, $uid, $softInfo->softram));            
            
        }

        $webInfo = self::webserver_getInfo($uid);
        
        if($webInfo['existe'] == 1){
            $sql = 'UPDATE internet_webserver SET webDesc = :desc, active = 1 WHERE id = :id';
        } else {
            $sql = 'INSERT INTO internet_webserver (id, webDesc, active) VALUES (:id, :desc, 1)';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':desc' => $desc, ':id' => $uid));        

    }
    
    public function webserver_shutdown($uid){
        
        $this->session->newQuery();
        $sql = "UPDATE internet_webserver SET active = 0 WHERE id = '".$uid."'";
        $this->pdo->query($sql);
        
    }
    
    public function home_getIP(){

        //TODO: home funciona, mas nÃ£o tem como definir a home. adicionar botÃ£o "set as home" e, entÃ£o, uncomment essa funÃ§Ã£o
        
//        $this->session->newQuery();
//        $sql = 'SELECT COUNT(*) AS total, homeIP FROM internet_home WHERE userID = '.$_SESSION['id'].' LIMIT 1';
//        $homeInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
//
//        if($homeInfo->total == 1){
//            return $homeInfo->homeip;
//        }
        
        return 16909060; //1.2.3.4
        
    }
    
    public function isImportant($type){
        
        switch($type){
            case 1: //bank
            case 2: //whois
            case 5: //isp
            case 50: //safnt
            case 51: //fbi
            case 52: //nsa
                return TRUE;
        }
        
        return FALSE;

    }
    
    public function getTag($type){

        switch($type){

            case 1:
                return '<span class="label label-inverse pull-right">'._('Bank').'</span>';
            case 2:
                return '<span class="label label-warning pull-right">'._('Whois').'</span>';
            case 4:
                return '<span class="label pull-right">'._('Company').'</span>';
            case 5:
                return '<span class="label label-important pull-right">'._('ISP').'</span>';
            case 10:
                return '<span class="label label-info pull-right">'._('Clan Server').'</span>';
            case 30:
                return '<span class="label label-inverse pull-right">'._('Numataka Corporation').'</span>';
            case 40:
                return '<span class="label label-inverse pull-right">'._('Bitcoin Market').'</span>';
            case 50:
            case 51:
            case 52:
                return '<span class="label label-important pull-right">'._('Important').'</span>';
            case 99:
            case 'VPC':
                return '<span class="label label-success pull-right">VPC</span>';
            default:
                return '<span class="label label-info pull-right">NPC</span>';
                
          }
                
        
    }
    
    public function important_add($ip){
        
        $this->session->newQuery();
        $sql = 'INSERT INTO internet_important (id, userID, ip) VALUES (\'\', \''.$_SESSION['id'].'\', \''.$ip.'\')';
        $this->pdo->query($sql);
        
    }
    
    public function important_isset($ip){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM internet_important WHERE userID = \''.$_SESSION['id'].'\' AND ip = \''.$ip.'\'';
        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

        
        if($total > 0){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function important_show(){
        
        $language_table = 'npc_info_en';
        
        if($this->session->l == 'pt_BR'){
            $language_table = 'npc_info_pt';
        }
        
        $this->session->newQuery();
        $sql = 'SELECT internet_important.ip, '.$language_table.'.name
                FROM internet_important
                INNER JOIN npc
                ON npc.npcIP = internet_important.ip
                INNER JOIN '.$language_table.'
                ON '.$language_table.'.npcID = npc.id
                WHERE userID = \''.$_SESSION['id'].'\'';
        $important = $this->pdo->query($sql)->fetchAll();

        $total = sizeof($important);        
        
        if($total == 15){
            require '/var/www/classes/Social.class.php';
            $social = new Social();
            $social->badge_add(53, $_SESSION['id']);
        }
        
?>
                    <div class="widget-box collapsible">
                        <div class="widget-title">
                            <a href="#important" data-toggle="collapse">
                                <span class="icon"><span class="he16-important"></span></span>
                                <h5><?php echo _("Important IPs"); ?></h5>
                                <span class="label label-important hide1024"><?php echo $total; ?></span>
                            </a>
                        </div>
                        <div class="collapse in" id="important">
                            <div class="widget-content">
<?php
                    
        for($i = 0; $i < $total; $i++){

            $ip = long2ip($important[$i]['ip']);
            $name = $important[$i]['name'];
                        
?>
                                <a href="internet?ip=<?php echo $ip; ?>"><?php echo $ip; ?><span class="hide1024 small"> <?php echo $name; ?></span></a><br/>
<?php
        }
?>
                            </div>
                        </div>
                    </div>
<?php

    }
    
    public function history_show(){
        
?>
                    <div class="widget-box collapsible">
                        <div class="widget-title">
                            <a href="#history" data-toggle="collapse">
                                <span class="icon"><span class="he16-page_copy"></span></span>
                                <h5><?php echo _("Recently visited"); ?></h5>
                            </a>
                        </div>                                            
                        <div class="collapse in" id="history">
                            <div class="widget-content">  
                                <div id="visited-ips"></div>                                              
                            </div>
                        </div>  
                    </div>            
<?php
                
    }

    public function history_get(){
        
        if(isset($_COOKIE['ip-data'])){
            return unserialize(stripslashes($_COOKIE['ip-data']));
        } else {
            return Array();
        }
        
    }
    
    public function history_add($ip){
        
        $visitedArray = self::history_get();

        if(!array_key_exists(long2ip($ip), $visitedArray)){
        
            if(sizeof($visitedArray) > 4){
                $visitedArray = array_slice($visitedArray, -4, 4, TRUE);
            }
        
        } else {
            
            unset($visitedArray[long2ip($ip)]);
            
        }
            
        date_default_timezone_set('UTC');
        $time = date('Y-m-d\TH:i:s'); //TODO: change it to be by javascript date (timezone differs..)

        $curArray = Array(long2ip($ip) => $time);
          
        setcookie('ip-data',serialize(array_merge($visitedArray, $curArray)));
                
    }
    
    public function history_getJSON(){ //usado no ajax
        
        $visitedArray = self::history_get();

        $JSON = '[';
        
        foreach($visitedArray as $ip => $data){

            $JSON .= '{"ip":"'.$ip.'","time":"'.$data.'"},';

        }        
        
        $JSON = substr($JSON, 0, -1);
        $JSON .= ']';
        
        return $JSON;
        
    }

    public function handlePost(){
                
        if(isset($_POST['clan'])){
            $hardware = new HardwareVPC();
            $hardware->handlePost();
        } elseif(isset($_POST['act'])){
            $software = new SoftwareVPC();
            $software->handlePost('internet');
        }
        
        $system = new System();
        
        $redirect = 'internet';

        if(!isset($_POST['int-act'])){
            $system->handleError('Invalid requisition.', 'internet');
        }
        
        switch($_POST['int-act']){

            case 'transfer':
                
                $action = 'transfer';
                $redirect = 'internet?bAction=show';             
                
                if(!isset($_POST['acc'])){
                    $system->handleError('Missing bank account.', $redirect);
                }
                
                $acc = $_POST['acc'];
                
                if($acc == ''){
                    $system->handleError('Missing bank account.', $redirect);
                }
                
                if(!ctype_digit($acc)){
                    $system->handleError('Invalid bank account.', $redirect);
                }
                
                if(!isset($_POST['money'])){
                    $system->handleError('Missing transfer amount.', $redirect);
                }
                
                $amount = $_POST['money'];
                
                if($amount == ''){
                    $system->handleError('Missing transfer amount.', $redirect);
                }

                $amount = substr($amount, 1);
                $amount = str_replace(',', '', $amount);
                
                if(!ctype_digit($amount) || $amount <= 0){
                    $system->handleError('Invalid transfer amount.', $redirect);
                }
                
                if(!isset($_POST['ip'])){
                    $system->handleError('Missing bank IP.', $redirect);
                }
                
                if(!$system->validate($_POST['ip'], 'ip')){
                    $system->handleError('Invalid IP address.', $redirect);
                }
                
                $bankIP = ip2long($_POST['ip']);
                
                break;
            case 'changepass':
                $redirect = 'internet';
                $action = 'changepass';
                break;
            case 'closeacc':
                $redirect = 'internet';
                $action = 'closeacc';
                break;
            case 'register':
                $redirect = 'internet';
                $action = 'register';
                break;
            case 'qa':
                $redirect = FALSE;
                $action = '';
                break;
            
        }
        
        switch($action){
            case 'transfer':
                
                if(!$this->session->issetBankSession()){
                    $system->handleError('You are not logged in to a bank account, therefore you can not transfer.', 'internet');
                }
                
                $accFrom = $_SESSION['BANK_ACC'];
                
                if($accFrom == $acc){
                    $system->handleError('You can not transfer money to the same bank account. Duh.', $redirect);
                }
                
                $accInfo = $this->finances->getBankAccountInfo('', '', $acc);
                $fromInfo = $this->finances->getBankAccountInfo('', '', $accFrom);
                
                if($accInfo['VALID_ACC'] == 0){
                    $system->handleError('This account does not exists.', $redirect);
                }
                
                $bankInfo = $this->player->getIDByIP($bankIP, 'NPC');
                
                if($bankInfo['0']['existe'] == 0){
                    $system->handleError('Ops! The Bank IP you entered is not valid.', $redirect);
                }
                
                $npcInfo = $this->npc->getNPCInfo($bankInfo['0']['id']);
                
                if($npcInfo->npctype != 1){
                    $system->handleError('Ops! The Bank IP you entered is not valid.', $redirect);
                }
                
                if(!$this->finances->issetBankAccount($acc, $bankInfo['0']['id'])){
                    $system->handleError('This account does not exists.', $redirect);
                }

                if($fromInfo['CASH'] < $amount){
                    $system->handleError('Calm down, boy. Invalid transfer amount.', $redirect);
                }
                
                //SEEMS TO BE ALL OK \/
                
                $newAmount = $fromInfo['CASH'] - $amount;
                
                $playerInfo = $this->player->getPlayerInfo($_SESSION['id']);
                $this->finances->transferMoney($accFrom, $acc, $amount, $_SESSION['BANK_ID'], $bankInfo['0']['id'], $this->finances->getIDByBankAccount($acc, $bankInfo['0']['id']), $playerInfo->gameip);

                $this->session->addMsg(sprintf(_('%s transferred to account %s.'), '<strong>$'.number_format($amount).'</strong>', '<strong>#'.$acc.'</strong>'), 'notice');

                $this->list->bank_updateMoney($accFrom, $_SESSION['id'], $newAmount);  

                if ($this->session->issetMissionSession()) {

                    require '/var/www/classes/Mission.class.php';
                    $this->mission = new Mission();

                    if ($this->mission->issetMission($_SESSION['MISSION_ID'])) {

                        if (($_SESSION['MISSION_TYPE'] == '4') && ($this->mission->missionInfo($_SESSION['MISSION_ID']) == $_SESSION['BANK_ACC']) && ($this->mission->missionInfo2($_SESSION['MISSION_ID']) == $acc)) {

                            $this->mission->completeMission($_SESSION['MISSION_ID']);
                            $this->session->addMsg(sprintf(_('%s transferred to account %s. Mission completed.'), '<strong>$'.number_format($amount).'</strong>', '<strong>#'.$acc.'</strong>'), 'notice');

                        }
                    }

                }

                $logTextHacker = 'localhost transfered $' . $amount . ' from #'.$accFrom.' ('.long2ip($_SESSION['BANK_IP']).') to account #' . $acc . ' on bank [' . long2ip($npcInfo->npcip).']';

                
                $this->log->addLog($_SESSION['id'], $logTextHacker, '0');

                if($_SESSION['BANK_ID'] != $bankInfo['0']['id']){
                    $logTextHackedTo = '[' . long2ip($playerInfo->gameip) . '] transfered $' . $amount . ' from #'.$accFrom.' ('.long2ip($_SESSION['BANK_IP']).') to #' . $acc . ' at localhost';
                    $logTextHacked = '[' . long2ip($playerInfo->gameip) . '] transfered $' . $amount . ' from #'.$accFrom.' (localhost) to #' . $acc . ' at ['.long2ip($npcInfo->npcip).']';

                    $this->log->addLog($bankInfo['0']['id'], $logTextHackedTo, '1');
                    
                } else {
                    $logTextHacked = '[' . long2ip($playerInfo->gameip) . '] transfered $' . $amount . ' from #'.$accFrom.' to #' . $acc . ' at localhost';
                }                
                
                $this->log->addLog($_SESSION['BANK_ID'], $logTextHacked, '1');
                
                $this->session->exp_add('TRANSFER', Array($amount));                               
                
                require_once '/var/www/classes/Storyline.class.php';
                $storyline = new Storyline();

                if($amount > 10000){
                    if($amount < 25000){
                        $safenetOdds = 0.25;
                        $fbiOdds = 0.1;
                    } elseif($amount < 50000){
                        $safenetOdds = 0.50;
                        $fbiOdds = 0.25;
                    } else {
                        $safenetOdds = 1.0; //100%
                        $fbiOdds = 0.5; //50% chance going fbi
                    }
                } else {
                    $safenetOdds = 0.1;
                    $fbiOdds = 0.1;
                }

                $die = (rand(1, 10)/10);

                if(!$storyline->safenet_isset($playerInfo->gameip, 3)){//nao estÃ¡ na safenet
                    if($safenetOdds >= $die){ //se combinar a porcentagem, eu pego =D
                        $storyline->safenet_add($playerInfo->gameip, 3, $amount);
                    }
                } else { //estÃ¡ na safernet! reincidente = vamos ver no FBI, seu puto
                    $storyline->safenet_update($playerInfo->gameip, 3, $amount);
                    if(!$storyline->fbi_isset($playerInfo->gameip, 3)){
                        if($fbiOdds >= $die){
                            $storyline->fbi_add($playerInfo->gameip, 3, $amount);
                        }                
                    } else {
                        $storyline->fbi_update($playerInfo->gameip, 3, $amount);
                    }
                }
                
                break;
            case 'register':

                $ipInfo = $this->player->getIDByIP($_SESSION['CUR_IP'], 'NPC');
                                
                if($ipInfo['0']['existe'] == 0){
                    $this->system->handleError('This IP does not exists', $redirect);
                }
                
                $bankInfo = $this->npc->getNPCInfo($ipInfo['0']['id']);
                
                if($bankInfo->npctype != 1){
                    $this->system->handleError('Oh well, this is not a bank.', $redirect);
                }
   
                if($this->finances->isUserRegisteredOnBank($_SESSION['id'], $ipInfo['0']['id'])){
                    $this->system->handleError('You already have an account on this bank o.O.', $redirect);
                }
                
                $this->finances->createAccount($_SESSION['id'], $ipInfo['0']['id']);
                
                $this->session->addMsg('Bank account created.', 'notice');
                
                break;
            case 'changepass':
                
                if(!$this->session->issetBankSession()){
                    $this->system->handleError('You are not connected to a bank account.', $redirect);
                }

                $accInfo = $this->finances->getBankAccountInfo($_SESSION['id'], $_SESSION['BANK_ID']);
                
                if($accInfo['VALID_ACC'] == '0'){
                    $this->system->handleError('Invalid bank account.', $redirect);
                } elseif($accInfo['USER'] != $_SESSION['id']){
                    $this->system->handleError('Invalid bank account.', $redirect);
                }
                
                $newPwd = $this->finances->changeAccountPassword($accInfo['BANK_ACC']);
                
                $this->session->addMsg(sprintf(_("Account password changed. The new password is %s."), '<strong>'.$newPwd.'</strong>'), 'notice');
                
                break;
            case 'closeacc':
                
                if(!$this->session->issetBankSession()){
                    $this->system->handleError('You are not connected to a bank account.', $redirect);
                }

                $accInfo = $this->finances->getBankAccountInfo($_SESSION['id'], $_SESSION['BANK_ID']);
                
                if($accInfo['VALID_ACC'] == '0'){
                    $this->system->handleError('Invalid bank account.', $redirect);
                } elseif($accInfo['USER'] != $_SESSION['id']){
                    $this->system->handleError('Invalid bank account.', $redirect);
                }
                
                if($accInfo['CASH'] > 0){
                    $this->system->handleError('You can\'t close an account with money.', $redirect);
                }
                
                $this->finances->closeAccount($accInfo['BANK_ACC']);
                
                $this->session->deleteBankSession();
                
                $this->session->addMsg('Account closed.', 'notice');
                
                break;
                
        }
                
        if($redirect){
            header("Location:$redirect");
            exit();       
        }

    }

}

?>
