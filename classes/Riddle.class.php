<?php

class Riddle {
    
    private $session;
    private $pdo;
    
    private $npcID;
    private $puzzleID;
    private $puzzleInfo;
    private $solved = FALSE;
    private $whois = FALSE;
    private $nextIP;
    
    public function __construct(){
        
        $this->session = new Session();
        $this->pdo = PDO_DB::factory();        
        
    }
    
    public function show($npcID, $npcIP){
        
        $this->npcID = $npcID;
        
        self::npcPuzzleID();
        self::alreadySolvedRiddle($npcIP);
        
        if($this->whois){
            $this->puzzleInfo = json_decode(file_get_contents('json/riddle.json'))->{'WHOIS'.($this->puzzleID - 500)};
        } else {
            $this->puzzleInfo = json_decode(file_get_contents('json/riddle.json'))->{$this->puzzleID};
        }
        

                        self::showInfo();
?>

                    <div class="span3">

<?php
                        self::showSideBar();
?>

                    </div>
                </div>
            </div>
            <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>
<?php
        
    }
    
    private function npcPuzzleID($npcID = ''){
        
        if($npcID != ''){
            $this->npcID = $npcID;
        }
        
        $this->session->newQuery();
        $sql = "SELECT npc_key.key FROM npc_key WHERE npcID = '".$this->npcID."' LIMIT 1";
        $key = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->key;
                
        list($type, $puzzleID) = explode('/', $key);
        
        if($type == 'WHOIS'){
            $this->whois = TRUE;
            $puzzleID += 500;
        }
        
        $this->puzzleID = $puzzleID;

        return $puzzleID;
        
    }
    
    private function getCurrentRiddle($userID = '', $lastSolved = ''){
        
        if($userID == ''){
            $userID = $_SESSION['id'];
        }
        
        if($lastSolved == ''){
            $lastSolved = self::getLatestSolved($userID);
        }
        
        if($lastSolved > 500){
            $lastKey = 'WHOIS/'.($lastSolved - 500);
            $lastKeyID = 'WHOIS'.($lastSolved - 500);
        } else {
            $lastKey = 'RIDDLE/'.$lastSolved;
            $lastKeyID = $lastSolved;
        }
        
        $lastInfo = json_decode(file_get_contents('json/riddle.json'))->{$lastKeyID};
        
        $currentKey = $lastInfo->next;
        
        $npc = new NPC();
                
        return long2ip($npc->getNPCByKey($currentKey)->npcip);
        
    }
    
    private function cmpPuzzleID($puzzleID){
        
        if($puzzleID > 500){
            $puzzleID -= 500;
            if($puzzleID == 1){
                $puzzleID = 10;
            } elseif($puzzleID == 2){
                $puzzleID = 27;
            } elseif($puzzleID == 3){
                $puzzleID = 34;
            } else {
                $puzzleID = 38;
            }
        }
        
        return $puzzleID;
        
    }
    
    private function showInfo(){
 
        if($this->puzzleInfo->next == 'TMPEND'){
            echo '<div class="alert alert-warning center bold"><strong>This is the end of the riddle (for now). A new one is coming that will take you to the WHOIS 3.</strong> Have suggestions for new riddle? Mail us!</div>';
        }
        
        $lastSolved = self::getLatestSolved();

        if(self::cmpPuzzleID($this->puzzleID) < self::cmpPuzzleID($lastSolved)){
            $currentIP = self::getCurrentRiddle('', $lastSolved);
            echo '<div class="alert alert-warning center bold">'._('You already solved more advanced puzzles. Your current puzzle is located at ').'<strong><a href="internet?ip='.$currentIP.'">'.$currentIP.'</a></strong></div>';
        }
        
        $header = '';
        if($this->solved){
            $this->nextIP = self::getNextIP();
            $header = _('<strong>You already solved this riddle. The next IP is ').'<a href="internet?ip='.long2ip($this->nextIP).'">'.long2ip($this->nextIP).'</a></strong><br/><br/>';
        }


?>
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-puzzle"></span></span>
                                <h5><?php echo _('Puzzle'); ?></h5>
                            </div>
                            <div class="widget-content padding center">
                                <span id="puzzle-header"><?php echo $header; ?></span>
                                <span id="puzzle-isSolved" value="false"></span>
                                
                                
            
<?php
    
        if(property_exists($this->puzzleInfo, 'path')){
            require 'puzzle/'.$this->puzzleInfo->path.'/'.$this->puzzleInfo->path.'.php';
        } else {
?>
                                <h3><?php echo _('Question & Answer'); ?></h3>
                                <div style="text-align: left;">
                                    <strong><?php echo _('Puzzle'); ?>: </strong>
<?php

            if($this->session->l == 'pt_BR'){
                $loc = 'question_pt';
            } else {
                $loc = 'question';
            }
            
            echo $this->puzzleInfo->{$loc};
    
            if(property_exists($this->puzzleInfo, 'note')){
                
                if($this->session->l == 'pt_BR'){
                    $loc = 'note_pt';
                } else {
                    $loc = 'note';
                }
                
                echo "<br/><br/><strong>"._('Note:')." </strong>".$this->puzzleInfo->{$loc};
            }
?>
                                </div>
             
<?php
            $shown = FALSE;
            if(isset($_POST['qa-answer']) && !$this->solved){
                $shown = TRUE;
                echo self::verifyQA($_POST['qa-answer']);
            } else {
                echo '<br/>';
            }
            
            if(!$this->solved){
?>
                                
                                <form action="internet?view=software&cmd=riddle" method="POST">
                                    <input type="hidden" name="int-act" value="qa">
                                    <input name="qa-answer" type="text" placeholder="<?php echo _('Answer'); ?>"><br/>
                                    <input class="btn btn-success" type="submit" value="<?php echo _('Submit answer'); ?>">
                                </form>
<?php

            } elseif(!isset($_POST['qa-answer']) || !$shown) {
                
                if($this->session->l == 'pt_BR'){
                    $loc = 'answer_pt';
                } else {
                    $loc = 'answer';
                }
                
                echo '<div class="alert alert-success">'._('Congratulations, you already solved this puzzle with the answer ').'<strong>'.$this->puzzleInfo->{$loc}.'</strong>.<br/><br/>'._('The next puzzle is located at ').'<strong><a href="internet?ip='.long2ip($this->nextIP).'">'.long2ip($this->nextIP).'</a></strong></div>';
            }

        }

?>
                            </div>
                        </div>
                    </div>
                    
<?php
        
    }     
    
    private function verifyQA($answer){
        
        $system = new System();

        $returnText = '<br/>';
        
        if(trim($answer) == ''){
            $returnText .= '<div class="alert alert-error">'._('Please insert an answer.').'</div>';
            return $returnText;
        }
        
        if(!$system->validate($answer, 'qa-answer')){
            $returnText .= '<div class="alert alert-error">'._('Invalid characteres on your answer ').'\'<strong>'.$answer.'</strong>\'. '._('Please use only azAZ09 ,.').'</div>';
            return $returnText;
        }
        
        //untaint($answer);
        
        if($this->session->l == 'pt_BR'){
            $loc = 'answer_pt';
        } else {
            $loc = 'answer';
        }
        
        if(strtolower($this->puzzleInfo->{$loc}) == strtolower($answer)){
            
            self::setAsSolved();
            
            $this->nextIP = self::getNextIP();
            
            $returnText .= '<div class="alert alert-success">Yay! \'<strong>'.$answer.'</strong>\' '._('is the correct answer!<br/><br/>The next puzzle is located at ').'<strong><a href="internet?ip='.long2ip($this->nextIP).'">'.long2ip($this->nextIP).'</a></strong></div>';
            return $returnText;
        }
        
        $similarHint = '';
        
        if($this->puzzleInfo->percent){
            
            $solveBySimilarity = FALSE;
            
            if(!is_bool($this->puzzleInfo->percent)){
                $solveBySimilarity = TRUE;
                $similarRate = $this->puzzleInfo->percent;
            }
            
            similar_text(strtolower($answer), strtolower($this->puzzleInfo->{$loc}), $percent);
                        
            if($percent >= 85){
                $similarHint .= '<br/><br/>'._('But hey, your answer is *close* ').'<span class="small nomargin">('.round($percent, 1).'%)</span> '._('to the actual answer =]');
            }
            
            if($solveBySimilarity && $percent >= $similarRate){
                
                self::setAsSolved();
                
                $this->nextIP = self::getNextIP();
                
                $returnText .= '<div class="alert alert-success">Yay! \'<strong>'.$answer.'</strong>\' '._('is close enough to the correct answer!<br/><br/>The next puzzle is located at ').'<strong><a href="internet?ip='.long2ip($this->nextIP).'">'.long2ip($this->nextIP).'</a></strong></div>';
                return $returnText;
            }
            
            $lev = levenshtein(strtolower($answer), strtolower($this->puzzleInfo->{$loc}));
            
            if($lev <= 2){
                $similarHint .= sprintf(_('<br/>You only need to change (add or remove or edit) %s characters...'), $lev);
            }
            
        }
        
        $returnText .= '<div class="alert alert-error">'._('Damn!').' \'<strong>'.$answer.'</strong>\''._(' is wrong :(').$similarHint.'</div>';
        return $returnText;
        
    }
    
    private function showSideBar(){
        
?>
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-puzzle_info"></span></span>
                                <h5><?php echo _('Puzzle Status'); ?></h5>
                            </div>
                            <div class="widget-content padding center">
<?php
    if($this->solved){
?>
                                <div class="puzzle-next">
                                    <span id="puzzle-next"><a href="internet?ip=<?php echo long2ip($this->nextIP); ?>"><?php echo long2ip($this->nextIP); ?></a></span><br/>
                                </div>
                                <div class="puzzle-result puzzle-win">
                                    <span id="puzzle-status"><?php echo _('SOLVED!'); ?></span>
                                </div>
<?php
    } else {
                
        $query = TRUE;
        if(isset($_COOKIE['PUZZLE_HINT'])){
            $query = FALSE;
            if($_COOKIE['PUZZLE_HINT']['ID'] != $this->puzzleID){
                self::hint_delCookie();
                $query = TRUE;
            }
            if(strlen($_COOKIE['PUZZLE_HINT']['IP']) > 15){
                self::hint_delCookie();
                $query = TRUE;
            }
        }
        
        if($query){
            $nextIP = self::hint_transformNextIP(long2ip(self::getNextIP()));
        } else {
            $nextIP = $_COOKIE['PUZZLE_HINT']['IP'];
            $system = new System();
            if(!$system->validate($nextIP, 'hintip')){
                self::hint_delCookie();
                $nextIP = self::hint_transformNextIP(long2ip(self::getNextIP()));
            }
            //untaint($nextIP);
        }
        
?>
                                <div class="puzzle-next">
                                    <span id="puzzle-next"><?php echo $nextIP; ?></span><br/>
                                    <span id="puzzle-solve" class="small nomargin">(<?php echo _('Solve the puzzle'); ?>)</span>
                                </div>
                                <div class="puzzle-result">
                                    <span id="puzzle-status"></span>
                                </div>
<?php
    }
?>

                            </div>
                        </div>
                    
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-puzzle_stats"></span></span>
                                <h5><?php echo _('Statistics'); ?></h5>
                            </div>
                            <div class="widget-content padding center">
<?php

$riddleStats = self::getRiddleStats($this->puzzleID);
$solved = number_format($riddleStats['SOLVED']);
$stuck = number_format($riddleStats['STUCK']);
$below = number_format($riddleStats['BELOW']);

if($this->puzzleID == 1){
    $below = $stuck;
    $stuck = 0;
    $stuckText = ' players are stuck here';
    $belowText = ' players did not start the riddle yet';
} else {
    $stuckText = ' players are stuck here';
    $belowText = ' players are below this point';
}

?>
                                <span class="green"><?php echo $solved; ?></span> <?php echo _('players solved this riddle'); ?><br/>
                                <span class="puzzle-tie"><?php echo $stuck; ?></span><?php echo _($stuckText); ?><br/>
                                <span class="red"><?php echo $below; ?></span><?php echo _($belowText); ?>
                            </div>                            
                        </div>
                    
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-actions"></span></span>
                                <h5><?php echo _('Options'); ?></h5>
                            </div>
                            <div class="widget-content padding center">
                                <ul class="soft-but">
                                    <li style="margin-top: 15px;">
                                        <a href="internet?view=software">
                                            <i class="icon-" style="background-image: none;"><span class="he32-root"></span></i>
                                            <?php echo _('Back to Software'); ?>
                                        </a>                                  
                                    </li>
                                </ul>
                            </div>
                        </div>
                    
<?php

if($this->puzzleInfo->credit){

    if($this->session->l == 'pt_BR'){
        $name = 'name_pt';
    } else {
        $name = 'name';
    }
    
    $gameName = $this->puzzleInfo->{$name};
    $credits = '<a href="'.$this->puzzleInfo->creditlink.'">'.$this->puzzleInfo->credit.'</a>';

?>
                    
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-puzzle_credits"></span></span>
                                <h5><?php echo _('Credits'); ?></h5>
                            </div>
                            <div class="widget-content padding center">
                                
                                <?php echo $gameName; ?> <?php echo _('was developed by'); ?> <?php echo $credits; ?>
                            </div>
                        </div>
<?php
        
        }

    }
    
    public function getFirstRiddle(){
        
        $key = 'PUZZLE/1';
        $this->session->newQuery();
        $sql = "SELECT npc.npcIP FROM npc INNER JOIN npc_key ON npc.id = npc_key.npcID WHERE npc_key.key = '".$key."' LIMIT 1";
        return long2ip($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip);
        
    }
    
    public function getNextIP($npcIP = ''){
        
        if(isset($this->nextIP)){
            return $this->nextIP;
        }
        
        if(!isset($this->puzzleID)){
            require_once '/var/www/classes/Player.class.php';
            $player = new Player();

            $npcInfo = $player->getIDByIP($npcIP, 'NPC');
            self::npcPuzzleID($npcInfo['0']['id']);
        }

        if(!isset($this->puzzleInfo)){
            if($this->whois){
                $this->puzzleInfo = json_decode(file_get_contents('json/riddle.json'))->{'WHOIS'.($this->puzzleID - 500)};
            } else {
                $this->puzzleInfo = json_decode(file_get_contents('json/riddle.json'))->{$this->puzzleID};
            }
        }
        
        $nextKey = $this->puzzleInfo->next;

        if($nextKey == 'TMPEND'){
            $nextKey = 'WHOIS/1';
        }
        
        if($this->puzzleID == 10){
            $nextKey = 'WHOIS/1';
        } elseif($this->puzzleID == 27){
            $nextKey = 'WHOIS/2';
        } elseif($this->puzzleID == 34){
            $nextKey = 'WHOIS/3';
        } elseif($this->puzzleID == 38){
            $nextKey = 'WHOIS/4';
        } elseif($this->puzzleID == 47){
            return ip2long('1.2.3.4');
        }
        
        $this->session->newQuery();
        $sql = "SELECT npc.npcIP FROM npc INNER JOIN npc_key ON npc.id = npc_key.npcID WHERE npc_key.key = '".$nextKey."' LIMIT 1";
        $this->nextIP = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;
        
        return $this->nextIP;
        
    }
    
    public function alreadySolvedRiddle($npcIP){
        
        require_once '/var/www/classes/Player.class.php';
        $player = new Player();
        
        $npcInfo = $player->getIDByIP($npcIP, 'NPC');
        $puzzleID = self::npcPuzzleID($npcInfo['0']['id']);

        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM puzzle_solved WHERE userID = '".$_SESSION['id']."' AND puzzleID = '".$puzzleID."' LIMIT 1";
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 0){
            $this->solved = FALSE;
            return FALSE;
        } else {
            $this->solved = TRUE;
            return TRUE;
        }
        
    }
    
    public function setAsSolved(){
        
        if($this->solved){
            return;
        }
        
        if(!isset($this->puzzleID)){
            exit("Missing puzzle ID");
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO puzzle_solved (puzzleID, userID) VALUES ('".$this->puzzleID."', '".$_SESSION['id']."')";
        $this->pdo->query($sql);
        
        if(self::cmpPuzzleID($this->puzzleID) > self::cmpPuzzleID(self::getLatestSolved())){
        
            $this->session->newQuery();
            $sql = "UPDATE users_puzzle SET puzzleID = '".$this->puzzleID."' WHERE userID = '".$_SESSION['id']."' LIMIT 1";
            $this->pdo->query($sql);
        
        }
        
        $this->solved = TRUE;
        
        $this->session->exp_add('PUZZLE');
        
    }
    
    public function validRiddleIP($npcIP){
        
        $this->session->newQuery();
        $sql = "SELECT npc_key.key FROM npc_key INNER JOIN npc ON npc.id = npc_key.npcID WHERE npc.npcIP = '".$npcIP."' LIMIT 1";
        $key = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->key;
        
        if(strpos($key, 'PUZZLE') !== FALSE){
            return TRUE;
        } elseif(strpos($key, 'WHOIS') !== FALSE) {
            return TRUE;
        }
        
        return FALSE;
        
        
    }
    
    public function getLatestSolved($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT puzzleID FROM users_puzzle WHERE userID = '".$uid."' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->puzzleid;
        
    }
        
    public function getRiddleStats($riddleID){
                
        $whereG = '';
        $whereS = '';
        $whereL = ' AND puzzleID <> 0';
        
        if($riddleID >= 500){
            $riddleID -= 500;
            if($riddleID == 1){
                $riddleID = 11;
                $whereG = ' AND puzzleID <> 501';
                $whereS = '501';
                $whereL .= '';
            } elseif($riddleID == 2){
                $riddleID = 28;
                $whereG = ' AND puzzleID <> 502';
                $whereS = '502';
                $whereL .= '';
            } elseif($riddleID == 3){
                
            } else {
                
            }            
        }
        
        if($riddleID == 0){
            $whereL = '';
        }
        
        if($whereS == ''){
            $whereS = $riddleID - 1;
        }
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM users_puzzle WHERE puzzleID > '.($riddleID - 1).$whereG;
        $totalGreater = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM users_puzzle WHERE puzzleID = '.($whereS);
        $totalSame = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM users_puzzle WHERE puzzleID < '.($riddleID - 1).$whereL;
        $totalLess = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
        return Array(
            'SOLVED' => $totalGreater,
            'STUCK' => $totalSame,
            'BELOW' => $totalLess
        );
        
    }

    public function hint_generate($hintLevel, $ip){

        $correctIP = long2ip(self::getNextIP());
        
        $totalX = 0;
        $occurrences = Array();
        
        for($i = 0; $i < strlen($ip); $i++){
            if($ip[$i] == 'X'){
                $totalX++;
                $occurrences[sizeof($occurrences)] = $i;
            }
        }

        if($totalX == 0) return $ip;
        
        switch($hintLevel){
            case 1: //1
            case 2: //1+1
            case 3: //1+1+1
                
                $discover = rand(0,sizeof($occurrences) - 1);
       
                $pos = $occurrences[$discover];
                $value = $correctIP[$pos];

                $ip = self::hint_addValueToIP($value, $pos, $ip);
                
                if($hintLevel > 1){
                    $ip = self::hint_generate($hintLevel - 1, $ip);
                }
                
                break;
            case 4: //2 (consecutivos)
            case 5: //3 (consecutivos)                                
                
                $exp = explode('.', $ip);

                if($exp[0] == 'XXX'){
                    $ip[0] = $correctIP[0];
                    $ip[1] = $correctIP[1];
                    if($hintLevel == 5) $ip[2] = $correctIP[2];
                } elseif($exp[1] == 'XXX'){
                    $ip[4] = $correctIP[4];
                    $ip[5] = $correctIP[5];
                    if($hintLevel == 5) $ip[6] = $correctIP[6];
                } elseif($exp[2] == 'XXX'){
                    $ip[8] = $correctIP[8];
                    $ip[9] = $correctIP[9];
                    if($hintLevel == 5) $ip[10] = $correctIP[10];
                } elseif($exp[3] == 'XXX'){
                    $ip[12] = $correctIP[12];
                    $ip[13] = $correctIP[13];
                    if($hintLevel == 5) $ip[14] = $correctIP[14];
                } elseif($exp[0] == 'XX'){
                    $ip[0] = $correctIP[0];
                    $ip[1] = $correctIP[1];
                } elseif($exp[1] == 'XX'){
                    $ip[4] = $correctIP[4];
                    $ip[5] = $correctIP[5];
                } elseif($exp[2] == 'XX'){
                    $ip[8] = $correctIP[8];
                    $ip[9] = $correctIP[9];
                } elseif($exp[3] == 'XX'){
                    $ip[12] = $correctIP[12];
                    $ip[13] = $correctIP[13];
                } else {
                    $ip = self::hint_generate(2, $ip);
                }
                
                break;
        }
        
        return $ip;
        
    }
    
    public function hint_addValueToIP($value, $pos, $ip){
                
        $ip[$pos] = $value;
        
        return $ip;
        
    }
 
    public function hint_getCookie($puzzleID = ''){
              
        $create = TRUE;
        
        if($puzzleID == ''){
            $puzzleID = $this->puzzleID;
        }
        
        if(isset($_COOKIE['PUZZLE_HINT'])){

            $create = FALSE;
            
            if($_COOKIE['PUZZLE_HINT']['ID'] != $puzzleID){
                $create = TRUE;
            }
            
            require_once '/var/www/classes/System.class.php';
            $system = new System();
            
            if(!$system->validate($_COOKIE['PUZZLE_HINT']['IP'], 'hintip')){
                $create = TRUE;
            }
            
            if(!ctype_digit($_COOKIE['PUZZLE_HINT']['ID'])){
                $create = TRUE;
            }
            
        }
        
        if($create){
            if($this->nextIP == NULL){
                self::getNextIP();
            }
            $nextMasked = self::hint_transformNextIP(long2ip($this->nextIP));
            self::hint_setCookie($nextMasked, $puzzleID); 
            return $nextMasked; //we still have to return because cookies are valid only on the next refresh
        } else {
            return $_COOKIE['PUZZLE_HINT']['IP'];
        }                
        
    }
    
    public function hint_setCookie($puzzleIP, $puzzleID = ''){
        
        setcookie("PUZZLE_HINT[IP]", $puzzleIP, time()+7200, "/");
        if($puzzleID != ''){
            setcookie("PUZZLE_HINT[ID]", $puzzleID, time()+7200, "/");
        }
        
    }
    
    public function hint_delCookie(){
        unset($_COOKIE['PUZZLE_HINT']['IP']);
        setcookie('PUZZLE_HINT[IP]', '', time() - 3600);
        unset($_COOKIE['PUZZLE_HINT']['ID']);
        setcookie('PUZZLE_HINT[ID]', '', time() - 3600);
    }
    
    public function hint_transformNextIP($nextIP){
        
        return preg_replace('/\d/', 'X', $nextIP);
        
    }
    
    public function solvedByHint($nextIP){
        
        if(strpos($nextIP, 'X') === FALSE){
            return 'true';
        }
        
        return 'false';
        
    }
 
    public function text_header($nextIP){
        
        return '<strong>'._('Congratulations, you solved the riddle. The next IP is ').'<a href=\"internet?ip='.$nextIP.'\">'.$nextIP.'</a> </strong><br/><br/>';
        
    }
    
    public function text_victory(){
        
        return '<span class=\"puzzle-win\"><strong>'._('WIN!').'</strong></span>';
        
    }
    
    public function text_hint(){
        
        return '<span style=\"color:'.sprintf( "#%06X", mt_rand( 0, 0xFFFFFF )).';\">'._('Hint!').'</span>';
        
    }
    
}