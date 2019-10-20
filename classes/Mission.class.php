<?php
require_once '/var/www/classes/List.class.php';

class Mission {

    private $pdo;
    private $list;
    private $finances;
    private $software;
    private $session;
    private $ranking;

    private $level;
    private $sidebar;
    
    
    function __construct() {

        
        $this->pdo = PDO_DB::factory();

        $this->list = new Lists();
        $this->session = new Session();
        $this->ranking = new Ranking();
        
    }

    public function handlePost(){
        
        $system = new System();
        
        $redirect = 'missions';
        
        if(!isset($_POST['act'])){
            $system->handleError('Invalid POST.', $redirect);
        }
        
        switch($_POST['act']){
            
            case 'abort':

                if(!$this->session->issetMissionSession()){
                    $system->handleError('You are not in a mission.', $redirect);
                }
                
                if(!self::issetMission($_SESSION['MISSION_ID'])){
                    $system->handleError('This mission does not exists.', $redirect);
                }
                
                if($_SESSION['MISSION_TYPE'] > 49){
                    $system->handleError('You are not allowed to abort this kind of mission.', $redirect);
                }
                
                self::abort();
                
                $this->session->addMsg('Mission aborted.', 'notice');
                
                break;
                
            case 'accept':
                
                if(!isset($_POST['mid'])){
                    $system->handleError('Invalid POST.', $redirect);
                }
                
                $mid = $_POST['mid'];
                
                if(!ctype_digit($mid)){
                    $system->handleError('Invalid ID.', $redirect);
                }
                
                if(!self::passedTutorial($_SESSION['id'])){
                    $system->handleError('You must first complete the tutorial from Certification 2.', $redirect);
                }
                
                if($this->session->issetMissionSession()){
                    $system->handleError('You already are in a mission.', $redirect);
                }
                
                if(!self::issetMission($mid)){
                    $system->handleError('This mission does not exists.', $redirect);
                }
                
                if(self::missionStatus($mid) != 1){
                    $system->handleError('This mission has already been taken.', $redirect);
                }
                
                self::acceptMission($_SESSION['id'], $mid);
                
                $this->session->addMsg('The mission is yours! <a href="internet">Go to your browser</a> and hack the victim.', 'notice');
         
                break;
                
            case 'complete':
                
//                if(!isset($_POST['acc'])){
//                    $system->handleError('Missing bank account information.', $redirect);
//                }
//                
//                $acc = $_POST['acc'];
                
                $finances = new Finances();
                
                $acc = $finances->getWealthiestBankAcc();
                
                if(!ctype_digit($acc)){
                    //$system->handleError('Invalid bank account.', $redirect);
                    $acc = $finances->getWealthiestBankAcc();
                }
                
                $accInfo = $finances->bankAccountInfo($acc);

                if($accInfo['0']['exists'] == '0'){
                    $system->handleError('BAD_ACC', $redirect);
                } elseif($accInfo['0']['bankuser'] != $_SESSION['id']){
                    //$system->handleError('BAD_ACC', $redirect);
                }

                if(!$this->session->issetMissionSession()){
                    $system->handleError('You already are in a mission.', $redirect);
                }
                
                if(!self::issetMission($_SESSION['MISSION_ID'])){
                    $system->handleError('This mission does not exists.', $redirect);
                }
                
                if(self::missionStatus($_SESSION['MISSION_ID']) != 3){
                    $system->handleError('This mission is not yet completed.', $redirect);
                }
                
                if($_SESSION['MISSION_TYPE'] == 3){

                    if(!isset($_POST['amount'])){
                        $system->handleError('Missing bank account balance.', $redirect);
                    }
                    
                    $amount = $_POST['amount'];

                    if($_POST['amount'] == ''){
                        $system->handleError('Missing bank account balance.', $redirect);
                    }

                    $amount = substr($amount, 1);
                    $amount = str_replace(',', '', $amount);

                    if(!ctype_digit($amount) || $amount <= 0){
                        $system->handleError('Invalid balance amount.', $redirect);
                    }

                    if($amount != self::missionNewInfo($_SESSION['MISSION_ID'])){
                        $system->handleError(sprintf(_('We have reasons to believe that the amount %s is invalid.'), '<strong>$'.number_format($amount).'</strong>'), $redirect);
                    }
                    
                }
                
                self::finalizeMission($_SESSION['MISSION_ID'], $acc);
                                
                break;
                
        }
        
        header('Location:'.$redirect);
        exit();
        
    }
    
    public function updateInfo($newSoftID, $mid) {

        $this->session->newQuery();
        $sql = "UPDATE missions SET newInfo = $newSoftID WHERE id = $mid LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function updateInfo2($newSoftID, $mid) {

        $this->session->newQuery();
        $sql = "UPDATE missions SET info2 = $newSoftID WHERE id = $mid LIMIT 1";
        $this->pdo->query($sql);
        
    }

    public function playerOnMission($uid = ''){

        if($uid == ''){
            if($this->session->issetMissionSession()){
                return TRUE;
            }
        } else {
            $this->session->newQuery();
            $sqlQuery = "SELECT COUNT(*) AS total FROM missions WHERE userID = $uid AND (status = 2 OR status = 3) LIMIT 1";
            $total = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ)->total;
            if($total > 0){
                return TRUE;
            }
        }
        
        return FALSE;
        
    }
    
    public function getPlayerMissionID($uid){
        
        $this->session->newQuery();
        $sqlQuery = "SELECT id FROM missions WHERE userID = $uid AND (status = 2 OR status = 3) LIMIT 1";
        $total = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $total->id;
        
    }
    
    public function generateMissionText($mission){
                
        $player = new Player();
 
        $seedArr = self::seed_get($mission->id);
        
        $pname = $player->getPlayerInfo($_SESSION['id'])->login;
        
        $ceoname = 'William Bell';
        
        $hirerName = $mission->hirername;
        $hirerIP = long2ip($mission->hirer);
        
        $victimName = $mission->victimname;
        $victimIP = long2ip($mission->victim);
        
        $reward = number_format($mission->prize);
        
        $this->sidebar = new stdClass();

        $TOTAL = 6;
        $textArray = Array();        
        for($i=0; $i < $TOTAL; $i++){
            
            $seed = $seedArr[$i];
            
            switch($i){
                case 0:

                    if($seed == 1){
                        $t = _('Hello there').', <strong>'.$pname.'</strong>.';
                    } elseif($seed == 2){
                        $t = '<strong>'.$pname.'</strong>,';
                    } else {
                        $t = _('Dear').' <strong>'.$pname.'</strong>,';
                    }
                    
                    $textArray['GREETING'] = $t;
                    
                    break;
                case 1:                    

                    if($seed == 1){
                        $t = _('I am here representing').' <a href="internet?ip='.$hirerIP.'">'.$hirerName.'</a>.';
                    } elseif($seed == 2){
                        $t = _('as a representative spokesperson of').' <a href="internet?ip='.$hirerIP.'">'.$hirerName.'</a>.';
                    } else {
                        $t = sprintf(_('we from %s have a proposal for you.'), '<a href="internet?ip='.$hirerIP.'">'.$hirerName.'</a>');
                    }
                    
                    if(strpos($textArray['GREETING'], 'there') !== FALSE || strpos($textArray['GREETING'], 'Olá') !== FALSE){
                        $t[0] = strtoupper($t[0]);
                    }
                    
                    $textArray['INTRO'] = $t;
                    
                    break;
                case 2:
                    
                    if($seed == 1){
                        $t = sprintf(_('I want you to access %s\'s%s servers and'), '<a href="internet?ip='.$victimIP.'">'.$victimName, '</a>');
                    } elseif($seed == 2){
                        $t = sprintf(_('I need you to hack into %s%s and'), '<a href="internet?ip='.$victimIP.'">'.$victimName, '</a>');
                    } elseif($seed == 3){
                        $t = sprintf(_('Your mission, choose you to accept it, is to access %s\'s%s servers and'), '<a href="internet?ip='.$victimIP.'">'.$victimName, '</a>');
                    } else {
                        $t = sprintf(_('You have to discover for us what %s%s is up to. Hack them and'), '<a href="internet?ip='.$victimIP.'">'.$victimName, '</a>');
                        
                    }

                    if((strpos($textArray['INTRO'], 'representative') !== FALSE && $seed >= 3) || strpos($textArray['INTRO'], 'porta-voz') !== FALSE){
                        $t[0] = strtolower($t[0]);
                    }
                    
                    $textArray['VICTIM_CALL'] = $t;
                    
                    break;
                case 3:
                    
                    if($seed == 1){
                        $t = sprintf(_('I am willing to offer you %s for this service.'), '<span class="green">$'.$reward.'</span>');
                    } elseif($seed == 2){
                        $t = sprintf(_('You\'ll have %s when the job is done.'), '<span class="green">$'.$reward.'</span>');
                    } else {
                        $t = sprintf(_('Complete the job and you will receive %s from us as a \'thank you\'.'), '<span class="green">$'.$reward.'</span>');
                    }
                    
                    $textArray['PAYMENT'] = $t;
                    
                    break;
                case 4:
                    
                    if($seed == 1){
                        $t = _('You can find them on').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                    } elseif($seed == 2){
                        $t = _('Their mainframe is located at').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                    } else {
                        $t = _('Ping them at').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                    }
                    
                    $textArray['VICTIM_LOCATION'] = $t;
                    
                    break;
                case 5:
                    
                    if($seed == 1){
                        $t = 'Do it quietly, and remember: if you get caught, I dont know who you are.';
                    } elseif($seed == 2){
                        $t = 'We dont want hear our company name on the news, so do a good job.';
                    } else {
                        $t = 'We are watching your steps, dont try to screw on us.';
                    }
                    
                    $textArray['WARNING'] = _($t);
                    
                    break;
            }
            
        }
        
        if ($mission->status == 1) {
            
            return $textArray['GREETING']._(' I have a job offer for you. I will hand you the details when you accept it. ').$textArray['PAYMENT'].'' ;
            
        }
                
        switch($mission->type){
            case 1:
            case 2:
                
                $software = new SoftwareVPC();
                $fileInfo = $software->getSoftwareOriginalInfo($mission->info);
                
                $softwareName = $fileInfo->softname.$software->getExtension($fileInfo->softtype);
                
                $this->sidebar->filename = $softwareName;
                $this->sidebar->fileversion = $software->dotVersion($fileInfo->softversion);
                
                break;
            case 3:
            case 4:
                
                $acc1 = $mission->info;
                $ip1 = $mission->victim;
                
                $acc2 = $mission->info2;
                $ip2 = $mission->newinfo2;
    
                $this->sidebar->ip1 = $ip1;
                $this->sidebar->ip2 = $ip2;
                
                break;
            case 51: //doom
            case 53: //doom
                $software = new SoftwareVPC();
                if($mission->newinfo != ''){
                    $doomInfo = $software->getSoftware($mission->newinfo, $_SESSION['id'], 'VPC');
                } else {
                    $doomInfo = $software->getSoftware($mission->info);
                }
                
                if($doomInfo !== FALSE){
                    $this->sidebar->filename = $doomInfo->softname.$software->getExtension($doomInfo->softtype);
                    $this->sidebar->fileversion = $software->dotVersion($doomInfo->softversion);
                } else {
                    $this->sidebar->filename = 'Doom.doom';
                    $this->sidebar->fileversion = '1.0';
                }
                
                break;
        }
        
        $staticText = '';
        $action = '';
        switch($mission->type){
            case 1:
                
                $seed = $seedArr[6];
                
                if($seed == 1){
                    $action = _('delete the file').' <span class="red">';
                } elseif($seed == 2) {
                    $action = _('remove the file').' <span class="red">';
                } else {
                    $action = _('wipe out the file').' <span class="red">';
                }
                
                $action .= $softwareName.'</span>.';
                                
                break;
            case 2:
                
                $seed = $seedArr[6];
                
                if($seed == 1){
                    $action = _('copy the file').' <span class="red">';
                } elseif($seed == 2) {
                    $action = _('transfer the file').' <span class="red">';
                } else {
                    $action = _('upload the file').' <span class="red">';
                }
                
                $action .= $softwareName.'</span>. '._('to our servers.');                
                                
                
                break;
            case 3:
                
                $seed = $seedArr[2];

                if($seed == 1){
                    $t = _('I need you to hack into bank account').' <font color="red">#'.$acc1.'</font> '._('from').' <a href="internet?ip='.$victimIP.'">'.$victimName.'</a>';
                    $t .= _(' and check it\'s total balance. This guy say they have no money, but I want to know for sure.');
                } elseif($seed == 2){
                    $t = _('Your mission, choose you to accept it, is to check the balance of account').' <font color="red">#'.$acc1.'</font> (<a href="internet?ip='.$victimIP.'">'.$victimName.'</a>)';
                    $t .= _(' and report back to us. They claim they have no money, but we want to know for sure.');
                }
                
                $textArray['VICTIM_CALL'] = $t;
                
                $seed = $seedArr[5];
                
                $t = _(' The account is located at bank').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                
                $textArray['VICTIM_LOCATION'] = $t;
                
                break;
            case 4:
                
                $seed = $seedArr[2];

                if($seed == 1){
                    $t = _('I need you to hack into bank account').' <font color="red">#'.$acc1.'</font> '._('from').' <a href="internet?ip='.$victimIP.'">'.$victimName.'</a>';
                    $t .= _(' and transfer the money to our account').', <font color="red">#'.$acc2.'</font>.';
                } elseif($seed == 2){
                    $t = _('Your mission, choose you to accept it, is to transfer all the money from bank account').' <font color="red">#'.$acc1.'</font> (<a href="internet?ip='.$victimIP.'">'.$victimName.'</a>)';
                    $t .= _(' to').' <font color="red">#'.$acc2.'</font>';
                    if($ip1 != $ip2){
                        $t .= ' (<a href="internet?ip='.$victimIP.'">'.$victimName.'</a>)';
                    }
                    $t .= '.';
                }
                
                $textArray['VICTIM_CALL'] = $t;
                
                $seed = $seedArr[5];
                
                if($ip1 == $ip2){
                    if($seed == 1){
                        $t = _(' And you got lucky: both accounts are at the same IP').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                    } elseif($seed == 2){
                        $t = _(' You can find the bank for both accounts here:').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>.';
                    }
                } else {
                    $t = _(' The first account is located at bank').' <a href="internet?ip='.$victimIP.'" class="link-default">'.$victimIP.'</a>';
                    $t .= _(' and the second at').' <a href="internet?ip='.long2ip($ip2).'" class="link-default">'.long2ip($ip2).'</a>.';
                }
                
                $textArray['VICTIM_LOCATION'] = $t;
                
                break;
            case 5:
                
                $seed = $seedArr[2];
                
                if($seed == 1){
                    $t = sprintf(_(' We are worried with the quick growing of %s. We believe a little down time on their servers will be enough to slow them down.'), '<a href="internet?ip='.$victimIP.'">'.$victimName.'</a>');
                    $t .= _(' Your mission is to DDoS\'em and seize their hardware big time.');
                } elseif($seed == 2){
                    $t = '';
                    $t = sprintf(_(' The guys at %s are annoying us, so we need you to throw several DDoS attacks against them.'), '<a href="internet?ip='.$victimIP.'">'.$victimName.'</a>');
                    $t .= _(' We are paying you big money, so your mission is to seize their hardware.');
                }
                
                $textArray['VICTIM_CALL'] = $t;
                
                break;
            case 50: //researched crc x
                $staticText =  $pname.', I\'ve been checking your performance on the last weeks and you seems to be a very experienced hacker. I would love to work with you.';
                $staticText .= ' You might have heard of me or my company. Don\'t listen to what news jerks says. We are not evil.';
                $staticText .= '<br/>&emsp;';
                $staticText .= 'You recently researched a very powerful software. Kudos! Did you know that this software is powerful enough to hack into NSA? ';
                $staticText .= 'Well, you certainly heard that NSA is in possession of the most powerful software of the world. The <strong>doom virus</strong> is not a myth!';
                $staticText .= '<br/>&emsp;';
                $staticText .= 'You got too involved, kid. Now there is no turning back. Your mission is to hack into the NSA and retrieve the doom virus. ';
                $staticText .= 'Dont worry, I\'ll give you further instructions once you download the virus.';

                break;
            case 51: //downloaded doom

                require '/var/www/classes/Clan.class.php';
                $clan = new Clan();
                if($clan->playerHaveClan()){
                    $haveClan = TRUE;
                } else {
                    $haveClan = FALSE;
                }

                $staticText =  'Good. Now that you are in possession of the doom virus, I need you to finally execute it. In order to be able to run it, you need to ';
                $staticText .= 'buy it\'s license. It will be costy, but we sent you a seed money. After buying the license, you can either research it or decide to launch.';
                $staticText .= '<br/>&emsp;';
                if($haveClan){
                    $staticText .= ' You can launch the doom attack on your own computer or on your clan server. It makes no difference! ';
                    $staticText .= 'Releasing on your clan server might hand you the help of your mates :). ';
                } else {
                    $staticText .= ' You can launch the doom attack on your own computer. If you were a part of a clan, you could release the attack at the clan server. ';
                    $staticText .= 'There is no difference, but you could count with the help of your clan mates. ';
                }
                $staticText .= '<br/>&emsp;';
                $staticText .= 'Create your strategy and whenever you feel ready, launch it! But remember: you\'ll be haunted by those FBI suckers. And everybody ';
                $staticText .= 'will attempt to hack you. Good luck.';
                break;
            case 52: //downloaded crc x

                $staticText  = 'Hello '.$pname.'. You might have heard of me or my company. We were checking here and looks like you downloaded a quite powerful cracker. ';
                $staticText .= 'Did you know this software is powerful enough to hack into the NSA? Well, yeah. And you certainly heard about them, right?';
                $staticText .= '<br/>&emsp;';
                $staticText .= 'The rumors are true: NSA does have that so called doom virus. And it is now your mission to hack into the NSA and retrieve the doom. ';
                $staticText .= 'Dont worry, I\'ll give you further instructions once you download the virus.';

                break;
            case 53: //someone uploaded doom

                require '/var/www/classes/Clan.class.php';
                $clan = new Clan();
                if($clan->playerHaveClan()){
                    $haveClan = TRUE;
                } else {
                    $haveClan = FALSE;
                }

                $staticText =  'You are lucky, someone uploaded you the most valuable software in the world! Now that you are in possession of the doom virus, I need you to execute it. In order to be able to run it, you need to ';
                $staticText .= 'buy it\'s license. It will be costy, but we sent you a seed money. After buying the license, you can either research it or decide to launch.';
                $staticText .= '<br/>&emsp;';
                if($haveClan){
                    $staticText .= ' You can launch the doom attack on your own computer or on your clan server. It makes no difference! ';
                    $staticText .= 'Releasing on your clan server might hand you the help of your mates :). ';
                } else {
                    $staticText .= ' You can launch the doom attack on your own computer. If you were a part of a clan, you could release the attack at the clan server. ';
                    $staticText .= 'There is no difference, but you could count with the help of your clan mates. ';
                }
                $staticText .= '<br/>&emsp;';
                $staticText .= 'Create your strategy and whenever you feel ready, launch it! But remember: you\'ll be haunted by those FBI suckers. And everybody ';
                $staticText .= 'will attempt to hack you. Good luck.';
                break;
            case 54: //someone uploaded crc x

                $staticText  = 'Hello '.$pname.'. You might have heard of me or my company. You are lucky, someone uploaded you a quite powerful cracker.';
                $staticText .= 'Did you know this software is powerful enough to hack into the NSA? Well, yeah. And you certainly heard about them, right?';
                $staticText .= '<br/>&emsp;';
                $staticText .= 'The rumors are true: NSA does have that so called doom virus. And it is now your mission to hack into the NSA and retrieve the doom. ';
                $staticText .= 'Dont worry, I\'ll give you further instructions once you download the virus.';

                break;

            case 80:
                $action = _(' retrieve the file <span class="red">Users.dat</span> for us.');
                break;
            case 81:
                $staticText = _('Thats weird! Why is the file Users.dat not there? Please, check the log page.');
                break;
            case 82:
                self::tutorial_update(83);
                $staticText = _('Looks like someone knew we were going there and deleted the file before we could take a look at it. Too bad for him that you caught his IP address.');
                $staticText .= sprintf(_(' I sent you a virus. Go to %s and install <font color="red">heartbleed.vspam</font> on his computer.'), '<a href="internet?ip='.$victimIP.'">'.$victimIP.'</a>');
            case 83:
                $staticText = _('Looks like someone knew we were going there and deleted the file before we could take a look at it. Too bad for him that you caught his IP address.');
                $staticText .= sprintf(_(' I sent you a virus. Go to %s and install <font color="red">heartbleed.vspam</font> on his computer.'), '<a href="internet?ip='.$victimIP.'">'.$victimIP.'</a>');
                break;
        }
        
        if($staticText == ''){
        
            return $textArray['GREETING'].' '.$textArray['INTRO'].' '.$textArray['VICTIM_CALL'].' '.$action.' '.$textArray['PAYMENT'].' '.$textArray['VICTIM_LOCATION'].' '.$textArray['WARNING'];
        
        } else {
            return $staticText;
        }
        
    }
    
    public function seed_isset($mid){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM missions_seed WHERE missionID = \''.$mid.'\' LIMIT 1';
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function seed_generate($missionID, $missionType){
        
        $limitArr = Array(3, 3, 4, 3, 3, 3, 0);
        $seedArr = Array();

        switch($missionType){
            case 1:
            case 2:
                $limitArr[6] = 3;
                break;
            case 3:
                $limitArr[2] = 2;
                $limitArr[5] = 2;
                break;
            case 4:
                $limitArr[2] = 2;
                $limitArr[5] = 2;
                break;
            case 5:
                $limitArr[2] = 2;
                break;
            
        }
        
        
        
        for($i = 0; $i < sizeof($limitArr); $i++){
            $seedArr[$i] = rand(1, $limitArr[$i]);
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO missions_seed (missionID, greeting, intro, victim_call, payment, victim_location, warning, action) 
                VALUES ('".$missionID."', '".$seedArr[0]."', '".$seedArr[1]."', '".$seedArr[2]."', '".$seedArr[3]."', '".$seedArr[4]."', '".$seedArr[5]."', '".$seedArr[6]."')";
        $this->pdo->query($sql);
        
    }
    
    public function seed_get($missionID){
        
        $this->session->newQuery();
        $sql = 'SELECT greeting AS s0, intro AS s1, victim_call AS s2, payment AS s3, victim_location AS s4, warning AS s5, action AS s6 
                FROM missions_seed WHERE missionID = \''.$missionID.'\' LIMIT 1';
        $seed = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        return Array(
            0 => $seed->s0,
            1 => $seed->s1,
            2 => $seed->s2,
            3 => $seed->s3,
            4 => $seed->s4,
            5 => $seed->s5,
            6 => $seed->s6
        );
        
    }
    
    public function showMission($mid) {
            
        $table = 'npc_info_en';
        if($this->session->l == 'pt_BR'){
            $table = 'npc_info_pt';
        }
        
        $this->session->newQuery();
        $sqlQuery = "SELECT missions.id, status, info, info2, newInfo, newInfo2, type, hirer, prize, victim, userID, level, dateGenerated, hirerInfo.name AS hirerName, victimInfo.name AS victimName
                     FROM missions
                     INNER JOIN npc npcHirer ON npcHirer.npcIP = hirer
                     INNER JOIN $table AS hirerInfo ON hirerInfo.npcID = npcHirer.id
                     INNER JOIN npc npcVictim ON npcVictim.npcIP = victim
                     INNER JOIN $table AS victimInfo ON victimInfo.npcID = npcVictim.id
                     WHERE missions.id = $mid
                     LIMIT 1";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        $valid = FALSE;
        if($this->session->issetMissionSession()){
            if($_SESSION['MISSION_ID'] == $mid){
                $valid = TRUE;
            }
        }
        
        if($mission->type < 49 && !$valid){
            if (!$this->list->isListed($_SESSION['id'], $mission->hirer)) {
                $system = new System();
                $system->handleError('This mission does not exists.', 'missions');
            }

            if($mission->level != self::getPlayerMissionLevel()){
                $system = new System();
                $system->handleError('This mission does not exists.', 'missions');
            }
        }

        $display = 1;
        
        $block  = FALSE;
        if ($mission->status == 2) {

            $block = TRUE;

            if(self::playerOnMission() && $this->session->missionID() == $mid){
                if ($mission->userid == $_SESSION['id']) {
                    $block = FALSE;
                }
            }
        }

        if($block){
            $system = new System();
            $system->handleError('This mission is already taken.', 'missions');
        }
        
        if(!self::seed_isset($mid)){
             self::seed_generate($mission->id, $mission->type);
        }

?>
                                <div class="span8">
                                    <ul class="recent-posts">
                                        <li>
                                            <div class="user-thumb pull-left">
                                                <img width="60" height="60" alt="User" src="images/profile/tux-hirer.png" />
                                            </div>
                                            <div class="article-post">
                                                <span class="user-info"><?php echo _('By'); ?>: <?php echo $mission->hirername; ?> <?php echo _('on'); ?> <?php echo $mission->dategenerated; ?>, IP: <?php echo long2ip($mission->hirer); ?> </span>
                                                <p>
                                                    <?php echo self::generateMissionText($mission); ?>

                                                </p>
                                            </div>
                                        </li>
                                    </ul>
<?php

        $system = new System();
        //$system->changeHTML('link1', $text);

        if ($mission->status == 1) {

            $display = 0;

            if(!self::playerOnMission()){

?>
                                    <div class="mission-new-margin"></div>
                                    <span class="btn btn-success mission-accept" value="<?php echo $mid; ?>"><?php echo _('Accept mission'); ?></span>
                                    <span id="modal"></span>
<?php

            } else {
                echo _('You can not take this mission because you already are in one');
            }

        } else {

            if(self::playerOnMission() && $this->session->missionID() == $mid){

                if ($mission->userid == $_SESSION['id']) {

?>
                                    <div class="mission-margin"></div>
<?php

                    if ($mission->status == '3') {
?>
                        <input type="hidden" name="action" value="complete">
<?php
                        if ($mission->type == '3') {

?>

            <form id="m3-amount" class="form-horizontal" style="text-align: left"/>
                <div class="control-group">
                    <label class="control-label"><?php echo _('Balance'); ?>: </label>
                    <div class="controls">
                        <input id="amount-input" type="text" placeholder="<?php echo _('Balance of account'); ?> #<?php echo $mission->info; ?>" name="to" autofocus="1"/>
                    </div>
                </div>
            </form><br/>

<?php

                        }
?>
                                    <span class="btn btn-success mission-complete" value="<?php echo number_format($mission->prize); ?>"><?php echo _('Complete Mission'); ?></span>
                                    <span id="modal"></span>
<?php
                    }

                    if ($mission->type < 49) {

?>
                                    <span class="btn btn-danger mission-abort" value="<?php echo $_SESSION['MISSION_ID']; ?>"><?php echo _('Abort'); ?></span>
                                    <span id="modal"></span>
<?php
                    }

                } else {
                    $display = 0;
                    echo _("<b>Mission already taken</b>");

                }

            } else {
                $display = 0;
                echo _('This mission is already taken');
            }

        }

        self::showMissionSideBar($display, $mission);

    }

    public function showMissionSideBar($display, $mission){
        
?>
                                    </div>
                                    <div class="span4">
                                        <div class="widget-box">
                                            <div class="widget-title">
                                                <span class="icon"><span class="he16-asterisk"></span></span>
                                                <h5><?php echo _('Mission information'); ?></h5>
                                            </div>
                                            <div class="widget-content nopadding"> 
                                                <table class="table table-cozy table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <td><span class="item"><?php echo _('Victim'); ?></span></td>
<?php 
                                            if($display == 1){ 
?>
                                                            <td><a class="black" href="internet?ip=<?php echo long2ip($mission->victim); ?>"><?php echo long2ip($mission->victim); ?> </a><span class="small nomargin"><?php echo $mission->victimname; ?></span></td>
<?php 
                                            } else { 
?>
                                                            <td><?php echo _('Unknown, f'); ?></td>
<?php 
                                            } 
?>
                                                        </tr>
                                                        <tr>
<?php 
                                            if($display == 1){
                                                
                                                    switch($mission->type){

                                                        case 1:
                                                        case 2:    

?>
                                                            <td><span class="item"><?php echo _('File'); ?></span></td>
                                                            <td><?php echo $this->sidebar->filename; ?><span class="small"><?php echo $this->sidebar->fileversion; ?></span></td>   
<?php

                                                            break;
                                                        case 3:
?>
                                                            <td><span class="item"><?php echo _('Bank Account'); ?></span></td>
                                                            <td>#<?php echo $mission->info; ?><a class="small" href="internet?ip=<?php echo long2ip($this->sidebar->ip1); ?>"><?php echo long2ip($this->sidebar->ip1); ?></a></td>   
<?php                                                            
                                                            break;
                                                        case 4:
?>
                                                            <td><span class="item"><?php echo _('Account From'); ?></span></td>
                                                            <td>#<?php echo $mission->info; ?><a class="small" href="internet?ip=<?php echo long2ip($this->sidebar->ip1); ?>"><?php echo long2ip($this->sidebar->ip1); ?></a></td>   
                                                        </tr>
                                                        <tr>
                                                            <td><span class="item"><?php echo _('Account To'); ?></span></td>
                                                            <td>#<?php echo $mission->info2; ?><a class="small" href="internet?ip=<?php echo long2ip($this->sidebar->ip2); ?>"><?php echo long2ip($this->sidebar->ip2); ?></a></td>   
<?php                                                            
                                                            break;
                                                        case 5:
?>
                                                            <td><span class="item"><?php echo _('Action'); ?></span></td>
                                                            <td>DDoS</td>   
<?php                                                            
                                                            break;
                                                        case 50: //hack nsa
                                                        case 52: //hack nsa
                                                        case 54: //hack nsa
?>
                                                            <td><span class="item"><?php echo _('NSA Defense'); ?></span></td>
                                                            <td>Unknown name<span class="small">20.0</span></td>   
<?php
                                                            break;
                                                        case 51: //launch doom
                                                        case 53: //launch doom
?>
                                                            <td><span class="item"><?php echo _('File'); ?></span></td>
                                                            <td><?php echo $this->sidebar->filename; ?><span class="small"><?php echo $this->sidebar->fileversion; ?></span></td>   
<?php
                                                            break;
                                                        case 80:
                                                        case 81:    
?>
                                                            <td><span class="item"><?php echo _('File'); ?></span></td>
                                                            <td>Users.dat<span class="small">28 MB</span></td>   
<?php
                                                            break;
                                                        case 82:
                                                        case 83:
?>
                                                            <td><span class="item"><?php echo _('File'); ?> </span></td>
                                                            <td>heartbleed.vspam<span class="small">1.0</span></td>   
<?php
                                                            break;
                                                        default:
                                                            ?>
                                                            <td><span class="item">File</span></td>
                                                            <td>Gmail.crc<span class="small">2.0</span></td>   
                                                            <?php
                                                            break;

                                                    }
                                                
                                                } else { 
                                                    ?>
                                                        <td><span class="item"><?php echo _('Action'); ?></span></td>
                                                        <td><?php echo _('Unknown, f'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="item"><?php echo _('Difficulty'); ?></span></td>
                                                        <td><?php echo self::getMissionDifficulty($mission->type); ?></td>
                                                    <?php
                                                } 
                                                
?>                                            
                                                        </tr>   
                                                        <tr>
                                                            <td><span class="item"><?php echo _('Reward'); ?></span></td>
                                                            <td><font color="green">$<?php echo number_format($mission->prize); ?></font></td>
                                                        </tr>                                                                               
                                                        <tr>
                                                            <td><span class="item"><?php echo _('Hirer'); ?></span></td>
                                                            <td><a class="black" href="internet?ip=<?php echo long2ip($mission->hirer); ?>"><?php echo long2ip($mission->hirer); ?> </a><span class="small nomargin"><?php echo $mission->hirername; ?></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
<?php
                
        
    }
    
    private function getMissionDifficulty($type, $label = false){
        
        if($label){
            $class = 'label pull-right ';
        } else {
            $class = '';
        }
        
        switch($type){
            case 1:
                if($label){
                    $class .= 'label-success';
                } else {
                    $class .= 'green bold';
                }
                return '<span class="'.$class.'">'._('Very Easy').'</span>';
            case 2:
                if($label){
                    $class .= 'label-success';
                } else {
                    $class .= 'green';
                }
                return '<span class="'.$class.'">'._('Easy').'</span>';
            case 3:
                if($label){
                    $class .= 'label-warning';
                } else {
                    $class .= 'orange';
                }
                return '<span class="'.$class.'">'._('Medium').'</span>';
            case 4:
                if($label){
                    $class .= 'label-important';
                } else {
                    $class .= 'red';
                }
                return '<span class="'.$class.'">'._('Hard').'</span>';
            case 5:
                if($label){
                    $class .= 'label-important';
                } else {
                    $class .= 'red bold';
                }
                return '<span class="'.$class.'">'._('Very hard').'</span>';
            default:
                return 'Unknown';
                
        }
        
    }
    
    public function showCompletedMissionSideBar(){
        
        $missionStats = self::getMissionsStats();

?>
                            </div>
                            <div class="span3">
                                <div class="widget-box">
                                    <div class="widget-title">
                                        <span class="icon"><i class="he16-stats"></i></span>
                                        <h5><?php echo _('Stats'); ?><span class="small">(<?php echo _('total'); ?>)</span></h5>
                                        <span class="label label-info pull-right hide1024">Round</span>
                                    </div>
                                    <div class="widget-content nopadding"> 
                                        <table class="table table-cozy table-bordered table-striped table-fixed">
                                            <tbody>
                                                <tr>
                                                    <td><span class="item"><?php echo _('Completed'); ?></span></td>
                                                    <td><?php echo $missionStats['COMPLETED']; ?><span class="small"><?php echo $missionStats['RATIO']; ?></span></td>
                                                </tr>
                                                <tr>
                                                    <td><span class="item"><?php echo _('Aborted'); ?></span></td>
                                                    <td><font color="red"><?php echo $missionStats['ABORTED']; ?></font></td>
                                                </tr>                                                                            
                                                <tr>
                                                    <td><span class="item"><?php echo _('Reward'); ?></span></td>
                                                    <td><font color="green">$<?php echo number_format($missionStats['REWARD']); ?></font></td>
                                                </tr>                                                                            
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
<?php
        
    }
    
    public function showListMissionSideBar($hidden){
                
    $date = new DateTime();
    $nextMissions = (60 - $date->format('i'))%15;
        
?>
                                </div>
                                <div class="span3">
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-missions_add"></span></span>
                                            <h5><?php echo _('New missions'); ?></h5>
                                        </div>
                                        <div class="widget-content padding">
                                            <?php echo sprintf(_('New missions will be generated in %s minutes.'), '<b>'.$nextMissions.'</b>'); ?>
                                        </div>
                                    </div>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-missions_level"></span></span>
                                            <h5><?php echo _('Mission Level'); ?></h5>
                                            <a href="<?php echo $this->session->help('missions', 'level'); ?>"><span class="label label-info">?</span></a>
                                        </div>
                                        <div class="widget-content padding">
                                            <?php echo _('Your current mission level is'); ?> <strong><?php echo $this->level; ?></strong>
                                        </div>
                                    </div>
<?php 
            if($hidden > 0){ 
?>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-exclamation"></span></span>
                                            <h5><?php echo _('Missions hidden'); ?></h5>
                                        </div>
                                        <div class="widget-content padding">
                                            <b><?php echo $hidden; ?></b> <?php echo _('missions were hidden because you do not have access to their hirers.'); ?>
                                        </div>
                                    </div>
<?php 
            } 
?>
                                    

<?php if($_SESSION['premium'] == 0){ ?>
<style type="text/css">
@media (min-width : 320px) and (max-width : 480px) { .adslot_mission { width: 250px; height: 250px; } }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_mission { width: 336px; height: 280px; } }
@media (min-width:1024px) { .adslot_mission { width: 120px; height: 240px; } }
@media (min-width:1280px) { .adslot_mission { width: 200px; height: 200px; } }
@media (min-width:1366px) { .adslot_mission { width: 250px; height: 250px; } }
@media (min-width:1824px) { .adslot_mission { width: 336px; height: 280px; } }
</style>
<div class="center">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- missions responsive -->
<ins class="adsbygoogle adslot_mission"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="7907947758"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<?php } ?>

                                </div>
<?php
        
    }

    public function getMissionsStats(){
        
        $id = $_SESSION['id'];
        
        $this->session->newQuery();
        $sql = "SELECT completed, prize FROM missions_history WHERE userID = '".$id."'";
        $data = $this->pdo->query($sql);
        
        $total = 0;
        $completed = 0;
        $reward = 0;
        
        while($missionInfo = $data->fetch(PDO::FETCH_OBJ)){
            $total++;
            if($missionInfo->completed == 1){
                $completed++;
                $reward += $missionInfo->prize;
            }
        }
        
        $aborted = $total - $completed;

        if($total > 0){
            $ratio = round(($completed / $total), 2) * 100 .'%';
        } else {
            $ratio = '';
        }
        
        $return = Array(
            
            'TOTAL' => $total,
            'COMPLETED' => $completed,
            'ABORTED' => $aborted,
            'RATIO' => $ratio,
            'REWARD' => $reward
            
        );
        
        return $return;
        
        
    }
    
    public function missionText($type) {

        switch ($type) {

            case 1:
                $text = 'Delete software';
                break;
            case 2:
                $text = 'Steal software';
                break;
            case 3:
                $text = 'Check bank status';
                break;
            case 4:
                $text = 'Transfer money';
                break;
            case 5:
                $text = 'Destroy server';
                break;
            case 50:
            case 51:    
                $text = 'Exploit';
                break;
            case 80:
            case 81:
            case 82:
            case 83:
            case 84:
                $text = 'Tutorial Mission';
                break;
            default:
                $text = 'INVALID';
                break;
        }

        return _($text);
    }

    public function finalizeMission($mid, $bankAcc) {

        $system = new System();
        
        $this->finances = new Finances();
        $this->session = new Session();

        $accInfo = $this->finances->bankAccountInfo($bankAcc);
        if ($accInfo['0']['exists'] == '0') {
            $system->handleError('This bank account is invalid.', 'missions?id='.$mid);
        } elseif ($accInfo['0']['bankuser'] != $_SESSION['id']) {
            $system->handleError('This bank account is invalid.', 'missions?id='.$mid);
        }

        switch($_SESSION['MISSION_TYPE']){
            
            case '3':
            case '4':
                
                $deleteIn = 2; //delete in 2 days
                
                $infoAcc = self::missionInfo($_SESSION['MISSION_ID']);
                $this->finances->setExpireDate($infoAcc, $deleteIn);
                
                if($_SESSION['MISSION_TYPE'] == '4'){
                    
                    $extraInfoAcc = self::missionInfo2($_SESSION['MISSION_ID']);
                    $this->finances->setExpireDate($extraInfoAcc, $deleteIn);
                    
                }
                
                break;
            case 84:
                
                require '/var/www/classes/Storyline.class.php';
                $storyline = new Storyline();
                
                $storyline->tutorial_setExpireDate(self::missionVictim($_SESSION['MISSION_ID']), self::missionInfo2($_SESSION['MISSION_ID']));
                
                require '/var/www/classes/Mail.class.php';
                $mail = new Mail();
                $mail->sendGreetingMail();
                
                $finances = new Finances();
                $finances->bitcoin_createAcc($finances->bitcoin_getID());
                
                break;
            
        }

        $prize = self::missionPrize($mid);
        
        self::recordMission($mid);

        $this->session->newQuery();
        $sql = "DELETE FROM missions WHERE id = $mid LIMIT 1";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "DELETE FROM missions_seed WHERE missionID = " .$mid.' LIMIT 1';
        $this->pdo->query($sql);
        
        unset($_SESSION['MISSION_ID']);
        unset($_SESSION['MISSION_TYPE']);
        
        $newAmount = number_format($prize);
        
        $this->finances->addMoney($prize, $bankAcc);
        $this->session->addMsg(sprintf(_("Mission completed! %s added to %s."), '<strong>$'.$newAmount.'</strong>', '#'.$bankAcc), 'mission');
        
        $this->ranking->updateMoneyStats('4', $prize);
       
        $this->session->exp_add('MISSION', Array($prize));
        
    }
    
    public function recordMission($mid){
        
        $this->session->newQuery();
        $sqlQuery = "SELECT type, hirer, prize FROM missions WHERE id = '".$mid."' LIMIT 1";
        $data = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);
        
        $this->session->newQuery();
        $sql = "INSERT INTO missions_history (id, type, hirer, missionEnd, prize, userID, completed) VALUES ('".$mid."', :type, :hirer, NOW(), :prize, '".$_SESSION['id']."', '1')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':type' => $data->type, ':hirer' => $data->hirer, ':prize' => $data->prize));
        
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET missionCount = missionCount + 1 WHERE uid = '".$_SESSION['id']."' LIMIT 1";
        $this->pdo->query($sql);
        
    }
    
    public function restoreMissionSession($uid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT id, type FROM missions WHERE userID = $uid AND (status = 2 OR status = 3)";
        $total = $this->pdo->query($sqlQuery)->fetchAll();

        if (count($total) == '1') {
            $_SESSION['MISSION_ID'] = $total['0']['id'];
            $_SESSION['MISSION_TYPE'] = $total['0']['type'];
        }
        
    }

    public function issetMission($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT id FROM missions WHERE id = $mid";
        $total = $this->pdo->query($sqlQuery)->fetchAll();

        if (count($total) != 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getPlayerMissionLevel() {
    
        $software = new SoftwareVPC();
        $bestCracker = $software->getBestSoftware(1, $_SESSION['id'], 'VPC');
        
        if($bestCracker['0']['exists'] == 0){
            return 1;
        }
                
        if($bestCracker['0']['softversion'] >= 60){
            return 3;
        } elseif($bestCracker['0']['softversion'] >= 30){
            return 2;
        } else {
            return 1;
        }
    
    }
    
    public function missionLevel($mid){
        
        $this->session->newQuery();
        $sqlQuery = "SELECT level FROM missions WHERE id = $mid LIMIT 1";
        return $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ)->level;
        
    }
    
    public function acceptMission($uid, $mid) {

        if(!self::playerOnMission()){
        
            if(self::getPlayerMissionLevel() != self::missionLevel($mid)){
                $system = new System();
                $system->handleError('You can not accept this mission because you are in a different level.', 'missions');
            }
            
            $this->session->newQuery();
            $sql = "UPDATE missions SET status = 2, userID = $uid WHERE id = $mid";
            $this->pdo->query($sql);

            $_SESSION['MISSION_ID'] = $mid;
            $_SESSION['MISSION_TYPE'] = self::missionType($mid);
        
        } else {
            $system = new System();
            $system->handleError('You can not accept this mission because you already are in one.', 'missions');
        }
        
    }

    public function missionVictim($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT victim FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->victim;
    }
    
    public function missionHirer($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT hirer FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->hirer;
    }
    
    public function missionStatus($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT status FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->status;
    }

    public function missionType($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT type FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->type;
    }

    public function missionInfo($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT info FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->info;
    }

    public function missionNewInfo($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT newInfo FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->newinfo;
    }

    public function missionInfo2($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT info2 FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->info2;
    }

    public function missionPrize($mid) {

        $this->session->newQuery();
        $sqlQuery = "SELECT prize FROM missions WHERE id = $mid";
        $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);

        return $mission->prize;
    }

    public function availableMissions() {

        $this->session->newQuery();
        $sqlQuery = "SELECT id 
                     FROM missions 
                     WHERE status = 1 AND level = ".$this->level;
        $total = $this->pdo->query($sqlQuery)->fetchAll();

        return count($total);
    }

    public function completeMission($mid) {

        $this->session->newQuery();
        $sql = "UPDATE missions SET status = 3 WHERE id = $mid";
        $this->pdo->query($sql);
        
    }

    public function listMissions() {
        
        $this->level = self::getPlayerMissionLevel();
        
        if($this->level == 3){
            // 2019: The IP below (from sexsi) is hardcoded and must be manually updated after every round reset.
            ?>
                <div class="alert center">
                    <?php echo _('<strong>Hey! I love <a href="internet.php?ip=105.10.220.227">sexsi</a>. Especially when it *works*.</strong>'); ?>
                </div>
            <?php
        }

?>
                                    <div class="widget-box">
                                        <div class="widget-title">
                                            <span class="icon"><span class="he16-missions"></span></span>
                                            <h5><?php echo _('Available missions'); ?></h5>
                                        </div>
                                        
<?php
        
        $hidden = 0;
        $display = 0;
        if (self::availableMissions() == 0) {

?>
              
                                        <div class="widget-content">
                                            <?php echo _('No missions available at the moment.'); ?>
                                        </div></div>
                                            
<?php

        } else {

            $this->session->newQuery();
            $sqlQuery = "SELECT missions.id, type, hirer, prize, victim, hirerInfo.name AS hirerName 
                         FROM missions
                         INNER JOIN npc ON npc.npcIP = hirer
                         INNER JOIN npc_info_en AS hirerInfo ON hirerInfo.npcID = npc.id
                         WHERE status = 1 AND level = '".$this->level."'
                         ORDER BY prize DESC";
            $query = $this->pdo->query($sqlQuery);

            $i = 0;
            while ($mission = $query->fetch(PDO::FETCH_OBJ)) {

                if ($mission->victim != NULL) {

                    if($display == 0){

?>
                                    <div class="widget-content nopadding">
                                        <table class="table table-cozy table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col"><?php echo _('Hirer'); ?></th>
                                                    <th scope="col"><?php echo _('Description'); ?></th>
                                                    <th scope="col"><?php echo _('Reward'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php

                        $display++;
                    }
                    
                    if ($this->list->isListed($_SESSION['id'], $mission->hirer)) {
                        $i++;

                        $hirerText = $mission->hirername;
                        $text = self::missionText($mission->type);

?>
                                                    <tr>
                                                        <td><?php echo $hirerText; ?></td>
                                                        <td>
                                                            <a href="?id=<?php echo $mission->id; ?>"><?php echo _($text); ?></a>
                                                            <?php echo self::getMissionDifficulty($mission->type, TRUE); ?>
                                                        </td>
                                                        <td><font color="green">$<?php echo number_format($mission->prize); ?></font></td>
                                                    </tr>
<?php

                    } else {
                        $hidden++;
?>
                                                    <tr>
                                                        <td><?php echo _('Unknown'); ?></td>
                                                        <td><?php echo _('This mission is hidden.'); ?></td>
                                                        <td><?php echo _('Unknown'); ?></td>
                                                    </tr>
<?php
                    }
                }
            }

            if($display > 0){
?>
                                                </tbody>
                                            </table>
<?php                
            }
?>
                                        </div>
                                    </div>
<?php

        }

        self::showListMissionSideBar($hidden);
        
    }
    
    public function countCompletedMissions($id = ''){
        
        if($id == ''){
            $id = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM missions_history WHERE userID = '".$id."' AND completed = 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }
    
    public function listCompletedMissions(){
        
        if(self::countCompletedMissions() > 0){

            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate($_SESSION['id'], 'missionsHistory', '10', 'view=completed&page', 1);
            $pagination->showPages('10', 'view=completed&page');
            
            self::showCompletedMissionSideBar();
            
        } else {
            echo _('You havent completed any missions yet').'</div>';
        }
        
        
        
    }
    
    public function storyline_hasMission($uid = ''){

        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT id, type FROM missions WHERE (type = '50' OR type = '51' OR type = '52' OR type = '53' OR type = '54') AND userID = '".$uid."' AND status <> 4 ORDER BY status ASC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(count($data) > '0'){
            
            $_SESSION['MISSION_ID'] = $data['0']['id'];
            $_SESSION['MISSION_TYPE'] = $data['0']['type'];
            
            return TRUE;
            
        } else {
            return FALSE;
        }
        
    }
    
    public function abort_confirmation(){

        if($_SESSION['MISSION_TYPE'] < 49){ //cant abort doom-based missions.

            $this->session->newQuery();
            $sqlQuery = "SELECT status, type, prize 
                         FROM missions
                         WHERE missions.id = ".$_SESSION['MISSION_ID']."
                         LIMIT 1";
            $mission = $this->pdo->query($sqlQuery)->fetch(PDO::FETCH_OBJ);        

            ?>

            <span class="item"><?php echo _('Mission'); ?>: </span><?php echo self::missionText($mission->type); ?><br/>
            <span class="item"><?php echo _('Reward'); ?>: </span><font color="green">$<?php echo number_format($mission->prize, '0', '.', ','); ?></font>

            <br/><br/><font color="red"><?php echo _('Abort?'); ?></font> (<?php echo _('This action can not be undone'); ?>)
            <form action="mission_abort" method="POST">
                <br/>
                <input type="submit" value="<?php echo _('Yes'); ?>"> or <b><a href="missions"><?php echo _('No, go back!'); ?></a></b>
            </form>

            <?php
        
        } else {
            echo 'One does not simply "abort" a doom mission. :)';
        }
        
    }
    
    public function abort($mid = ''){
        
        if($mid == ''){
            $mid = $_SESSION['MISSION_ID'];
            $mtype = $_SESSION['MISSION_TYPE'];
            $deleteSession = 1;
        } else {
            $mtype = self::missionType($mid);
            $deleteSession = 0;
        }

        if($mtype < 49){

            self::abort_penalty($mid);
            
            $this->session->newQuery();
            $sql = "INSERT INTO missions_history (id, type, hirer, missionEnd, prize, userID, completed) 
                    VALUES ('".$mid."', :type, :hirer, NOW(), :prize, '".$_SESSION['id']."', '0')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':type' => $mtype, ':hirer' => self::missionHirer($mid), ':prize' => self::missionPrize($mid)));

            self::deleteMission($mid);

            if($deleteSession == 1){
                $this->session->deleteMissionSession();
            }
            
            $this->session->newQuery();
            $sql = "SELECT COUNT(*) AS total FROM missions_history WHERE completed = 0 AND userID = '".$_SESSION['id']."'";
            if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 5){
                require '/var/www/classes/Social.class.php';
                $social = new Social();
                $social->badge_add(58, $_SESSION['id']);
            }

        } else {
            $system = new System();
            $system->handleError('You can not abort this type of mission.', 'missions');
        }
        
    }
    
    public function deleteMission($mid = ''){
        
        if($mid == ''){
            $mid = $_SESSION['MISSION_ID'];
        }
        
        $this->session->newQuery();
        $sql = "DELETE FROM missions WHERE id = " .$mid.' LIMIT 1';
        $this->pdo->query($sql);

        $this->session->newQuery();
        $sql = "DELETE FROM missions_seed WHERE missionID = " .$mid.' LIMIT 1';
        $this->pdo->query($sql);

    }
    
    public function abort_penalty($mid){
                
        $finances = new Finances();
        //$finances->debtMoney(500 + self::missionPrize($mid), $finances->getWealthiestBankAcc());
        
        $this->session->newQuery();
        $sql = 'UPDATE users_stats
                SET exp = exp*0.9
                WHERE uid = \''.$_SESSION['id'].'\'
                LIMIT 1';
        $this->pdo->query($sql);
        
    }
    
    public function doom_abort($mid = ''){
        
        if($mid == ''){
            $mid = $_SESSION['MISSION_ID'];
            $deleteSession = 1;
        } else {
            $deleteSession = 0;
        }

        $this->session->newQuery();
        $sql = "DELETE FROM missions WHERE id = " .$mid.' LIMIT 1';
        $this->pdo->query($sql);

        if($deleteSession == 1){
            $this->session->deleteMissionSession();
        }
        
    }    
    
    public function doom_createMission($type, $info, $id){
        
        $storyline = new Storyline();
        
        $hirer = $storyline->evilcorp_getIP();
        $victim = $storyline->nsa_getIP();

        $prize = 1000000000;

        $newInfo = '';
        
        switch($type){
            
            case 50:
                
                //$info = ''; //ID do doom na NSA
                
                break;
            case 51:
                
                $newInfo = $info;
                
                break;
            case 52:
                
                //$info = ''; //ID do doom na NSA
                
                break;
            case 53:
                
                $newInfo = $info;
                
                break;
            case 54:
                
                //$info = ''; //ID do doom na NSA
                
                break;
            
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO missions (id, type, status, hirer, victim, info, newInfo, prize, userID) VALUES ('', '".$type."', 2, '".$hirer."', '".$victim."', '".$storyline->nsa_getDoomID()."', '".$newInfo."', '".$prize."', '".$id."')";
        $this->pdo->query($sql);
        
    }
    
    public function doom_haveMission($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT id, type 
                FROM missions 
                WHERE (type = '50' OR type = '51' OR type = '52' OR type = '53' OR type = '54') AND 
                userID = '".$uid."'";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(count($data) > '0'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function getMissionPertinentSoftID($nid, $oid){
        
        $software = new SoftwareVPC();

        return $software->getSpecificSoftwareID($nid, $software->getSoftwareOriginalInfo($oid));
        
    }
    
    public function tutorial_createMission($missionType, $hirer, $victim){
        
        if($missionType == 1){
            $type = 80;
        } else {
            die("Do Type");
        }
                        
        $this->session->newQuery();
        $sql = "INSERT INTO missions (id, type, status, hirer, victim, info, newInfo, prize, userID) VALUES ('', '".$type."', 2, '".$hirer."', '".$victim[0]."', '".$victim[1]."', '', '500', '".$_SESSION['id']."')";
        $this->pdo->query($sql);
        
        self::restoreMissionSession($_SESSION['id']);
        
    }
    
    public function tutorial_update($to){
        
        if(self::issetMission($_SESSION['MISSION_ID'])){

            switch($to){
                case 81:
                    
                    require_once '/var/www/classes/PC.class.php';
                    
                    $player = new Player();
                    $log = new LogVPC();
                    
                    $vic1 = self::missionVictim($_SESSION['MISSION_ID']);
                    $vic2 = self::missionInfo($_SESSION['MISSION_ID']);
                    
                    $vic1ID = $player->getIDByIP($vic1, 'NPC');
                    $vic2ID = $player->getIDByIP($vic2, 'NPC');

                    $log->addLog($vic1ID['0']['id'], '['.long2ip($vic2).'] deleted file Users.dat at localhost', 1);
                    $log->addLog($vic2ID['0']['id'], 'localhost deleted file Users.dat at ['.long2ip($vic1).']', 1);
                    
                    $sql = 'UPDATE missions SET type = 81 WHERE id = \''.$_SESSION['MISSION_ID'].'\'';

                    break;
                case 82:
                    
                    $this->session->newQuery();
                    $sqlQuery = "INSERT INTO software (id, userID, softName, softVersion, softSize, softRam, softType, softLastEdit, softHidden, softHiddenWith, isNPC, originalFrom)
                        VALUES ('', ?, 'heartbleed', '10', '4', '5', '8', NOW(), '0', '', '0', '')";
                    $sqlReg = $this->pdo->prepare($sqlQuery);
                    $sqlReg->execute(array($_SESSION['id']));                    
                    
                    $softID = $this->pdo->lastInsertId();
                    
                    $sql = 'UPDATE missions SET type = 82, victim = \''.self::missionInfo($_SESSION['MISSION_ID']).'\', info = \''.$softID.'\', info2 = \''.self::missionVictim($_SESSION['MISSION_ID']).'\' WHERE id = \''.$_SESSION['MISSION_ID'].'\'';

                    break;
                case 83:
                    $sql = 'UPDATE missions SET type = 83 WHERE id = \''.$_SESSION['MISSION_ID'].'\'';
                    break;
                case 84:
                    $sql = 'UPDATE missions SET type = 84 WHERE id = \''.$_SESSION['MISSION_ID'].'\'';
                    break;
            }
            
            $this->session->newQuery();
            $this->pdo->query($sql);
            
            self::restoreMissionSession($_SESSION['id']);
            
        }
        
    }

    public function passedTutorial($uid){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM missions_history WHERE userID = \''.$uid.'\' AND type = \''. 84 .'\' LIMIT 1';
        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total > 0){
            return TRUE;
        } else {

            $this->session->newQuery();
            $sql = 'SELECT COUNT(*) AS total FROM hist_missions WHERE userID = \''.$uid.'\' AND type = \''. 84 .'\' LIMIT 1';
            $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
            
            if($total > 0){
                return TRUE;
            }
            
        }
        
        return FALSE;
        
    }
    
}

?>
