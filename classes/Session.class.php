<?php

require '/var/www/classes/PDO.class.php';

class Session {

    public $l;
    
    function __construct() {

        if (!isset($_SESSION)) {

            session_start();
            if(!isset($_SESSION['QUERY_COUNT'])){
                $_SESSION['QUERY_COUNT'] = '0';
            }
            if(!isset($_SESSION['BUFFER_QUERY'])){
                $_SESSION['BUFFER_QUERY'] = '0';
            }
            $_SESSION['EXEC_TIME'] = microtime(true);
            
        }
               
        $l = self::language_get();
                
        $this->l = $l;
        
        putenv("LANG=" . $l);
        setlocale(LC_ALL, $l);

        $domain = "messages";
        bindtextdomain($domain, "locale");
        bind_textdomain_codeset($domain, 'UTF-8');

        textdomain($domain);
        
    }

    public function addMsg($msg, $type) {

        $_SESSION['MSG'] = _($msg);
        $_SESSION['MSG_TYPE'] = $type; //notice, error
        
    }

    public function issetMsg() {

        if (isset($_SESSION['MSG'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function returnMsg($prv = '') {

        if($_SESSION['MSG_TYPE'] == 'error'){
            $type = 'error';
            $prvMsg = '<strong>'._('Error').'!</strong> ';
        } else {
            $type = 'success';
            $prvMsg = '<strong>'._('Success').'!</strong> ';
        }
        
        if($prv != ''){
            $prvMsg = '';
        }
        
?>
            <div class="alert alert-<?php echo $type; ?>">
                <button class="close" data-dismiss="alert">×</button>
                <?php echo $prvMsg.$_SESSION['MSG']; ?>
            </div>
<?php

        if($_SESSION['MSG_TYPE'] == 'mission'){
?>
            <span id="notify-mission"></span>
<?php
        }

        unset($_SESSION['MSG']);
        unset($_SESSION['MSG_TYPE']);
 
    }

    public function delMsg() {

        unset($_SESSION['MSG']);
        unset($_SESSION['MSG_TYPE']);
        
    }

    public function loginSession($id, $user, $premium, $special) {

        $_SESSION['id'] = $id;
        $_SESSION['user'] = $user;
        $_SESSION['premium'] = $premium;
        self::language_set(true);
        
        if($special){
            if($special == 'facebook'){
                $_SESSION['FBLOGIN'] = TRUE;
            } else {
                $_SESSION['TTLOGIN'] = TRUE;
            }
        }
        
    }

    public function issetLogin() {

        if (isset($_SESSION['id']) && ($_SESSION['id'] != '') && (is_numeric($_SESSION['id']))) {
            return TRUE;           
        } else {
            return FALSE;
        }
        
    }

    public function issetFBLogin(){
        if(isset($_SESSION['FBLOGIN'])){
            return TRUE;
        }
        return FALSE;
    }
    
    public function logout($query = 1, $redirect = false) {
        
        if($query == 1){
        
            $pdo = PDO_DB::factory();

            self::newQuery();
            $sql = "DELETE FROM users_online WHERE id = '".$_SESSION['id']."'";
            $pdo->query($sql);
            
            self::newQuery();
            $sql = "DELETE FROM users_expire WHERE userID = '".$_SESSION['id']."'";
            $pdo->query($sql);

        }
                

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

//        session_regenerate_id();
        $_SESSION = NULL;
        session_destroy();
        
        if($redirect){
            header("Location:index");
            exit();
        }
        
    }

    public function validLogin(){
        
        if(!isset($_SESSION)){
            return FALSE;
        }
        
        $pdo = PDO_DB::factory();

        self::newQuery();
        $sql = 'SELECT COUNT(*) AS t FROM users_online WHERE id = \''.$_SESSION['id'].'\' LIMIT 1';
        $total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->t;

        if($total == 1){
            return TRUE;
        }
        
        return FALSE;
        
    }
    
    public function issetProcessSession() {

        if (isset($_SESSION['pid'])) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function processID($param, $pid = '') {

        switch ($param) {

            case 'add':
                $_SESSION['pid'] = $pid;
                break;
            case 'show':
                return $_SESSION['pid'];
            case 'del':
                unset($_SESSION['pid']);
                break;
        }
    }

    public function createLogSession($lid, $local, $victimIP = '') {

        require_once "classes/Player.class.php";
        $player = new Player();

        $_SESSION['LID'] = $lid;

        if ($local == 'local') {
            $_SESSION['LOCAL'] = '1';
        } else {
            $_SESSION['LOCAL'] = '0';
        }

        if (strlen($victimIP) > '0') {

            $info = $player->getIDByIP($victimIP, '');

            if ($info['0']['pctype'] == 'NPC') {
                $_SESSION['IS_NPC'] = '1';
            } else {
                $_SESSION['IS_NPC'] = '0';
            }

            $_SESSION['VICTIM_ID'] = $info['0']['id'];
        } else {
            $_SESSION['IS_NPC'] = '0';
            $_SESSION['VICTIM_ID'] = '';
        }
    }

    public function deleteLogSession() {
        unset($_SESSION['LID']);
        unset($_SESSION['LOCAL']);
    }

    public function issetLogSession() {

        if (isset($_SESSION['LID']) && isset($_SESSION['LOCAL'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function createInternetSession($ip) {

        $_SESSION['CUR_IP'] = $ip;
    }

    public function issetInternetSession() {

        if (isset($_SESSION['CUR_IP']))
            return TRUE;
        else
            return FALSE;
    }

    public function deleteInternetSession() {

        unset($_SESSION['CUR_IP']);
        unset($_SESSION['LOGGED_IN']);
        unset($_SESSION['CUR_PAGE']);
        unset($_SESSION['CHMOD']);
        unset($_SESSION['LOGGED_USER']);
    }

    public function isInternetLogged() {

        if (isset($_SESSION['LOGGED_IN']))
            return TRUE;
        else
            return FALSE;
    }

    public function isHacking() {

        if (isset($_SESSION['HACKED']) && isset($_SESSION['METHOD']))
            return TRUE;
        else
            return FALSE;
    }

    public function deleteHackingSession() {

        unset($_SESSION['HACKED']);
        unset($_SESSION['METHOD']);
    }

    public function createBankSession($bankID, $bankAcc, $ip) {

        $_SESSION['BANK_ACC'] = $bankAcc;
        $_SESSION['BANK_ID'] = $bankID;
        $_SESSION['BANK_IP'] = $ip;
    }
    
    public function issetBankSession() {

        if (isset($_SESSION['BANK_ACC']) && isset($_SESSION['BANK_ID']) && isset($_SESSION['BANK_IP'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function deleteBankSession() {

        unset($_SESSION['BANK_ACC']);
        unset($_SESSION['BANK_ID']);
        unset($_SESSION['BANK_IP']);
        
    }

    public function createWalletSession($addr){
        $_SESSION['WALLET_ADDR'] = $addr;
    }

    public function issetWalletSession(){
        if(isset($_SESSION['WALLET_ADDR'])){
            return TRUE;
        }
        return FALSE;
    }
    
    public function deleteWalletSession(){
        unset($_SESSION['WALLET_ADDR']);
    }    
    
    public function issetMissionSession() {

        if (isset($_SESSION['MISSION_ID']))
            return TRUE;
        else
            return FALSE;
    }

    public function missionID() {

        return $_SESSION['MISSION_ID'];
    }

    public function missionType() {

        return $_SESSION['MISSION_TYPE'];
    }

    public function deleteMissionSession() {

        unset($_SESSION['MISSION_ID']);
        unset($_SESSION['MISSION_TYPE']);
        
    }

    public function newQuery() {

        $_SESSION['QUERY_COUNT']++;
    }

    public function issetQueryCount() {

        if (isset($_SESSION['QUERY_COUNT'])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function returnQueryCount() {

        echo $_SESSION['QUERY_COUNT'];
    }

    public function certSession($certLevel){
        
        $_SESSION['CERT'] = $certLevel;
        
    }
    
    public function skillSession($skillArray){
        
        $_SESSION['SKILL'] = $skillArray;
        
    }
    
    public function getSkill(){
        
        return $_SESSION['SKILL'];
        
    }
    
    public function getCert(){
        
        return $_SESSION['CERT'];
        
    }
    
    public function exp_add($action, $info = '', $uid = ''){
        
        $pdo = PDO_DB::factory();
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $totalToAdd = self::exp_getAmount($action, $info);
        
        self::newQuery();
        $sql = "UPDATE users_stats SET exp = exp + '$totalToAdd' WHERE uid = $uid";
        $pdo->query($sql);
        
    }
    
    private function exp_getAmount($action, $info = ''){
                
        switch($action){
                        
            case 'LOGIN':
                return 1;  // 2019: This ended up being a really bad idea
            case 'EDIT_LOG':
                if($info[0] == '1'){
                    return 1; //local
                } else {
                    return 3; //remote
                }
            case 'REMOTE_LOGIN':
                
                if($info[0] == 'download'){
                    return 1;
                } elseif($info[0] == 'bank'){
                    return 25;
                } elseif($info[0] == 'clan'){
                    return 3;
                }
                
                $attVersion = $info[2];
                $defVersion = $info[3];
                
                $diff = $attVersion - $defVersion;
                
                if($diff == 0){
                    $relative = 15;
                } else {
                    $relative = 15 - $diff;
                }
                if ($relative < 5){
                    $relative = 5;
                }
                
                return $relative;

            case 'HACK':                
                return 50 + round($info[1]); //hacked(vCRC + vPEC)[bf] OU (vCRC + vFWL)[exp] OU (vPEC)[bank]
            case 'CERT':
                return 100;
            case 'MISSION':
                return 50 + round($info[0] * 0.05);
            case 'TRANSFER':
                return 200 + round($info[0]/15);
            case 'RESET':
                if($info[0] == 'ip'){
                    return 50;
                } else {
                    return 15;
                }
            case 'RESEARCH':
                $bonus = round($info[1]);
                if($info[0] <= 4){
                    $bonus *= 2;
                }
                return 35 + $bonus;
            case 'BUY':
                if($info[0] == 'license'){
                    return 25 + round($info[1]);
                } elseif($info[0] == 'pc'){
                    if($info[1] == 2){
                        return 100;
                    } elseif($info[1] == 3){
                        return 500;
                    } else {
                        return 500 * $info[1];
                    }
                } elseif($info[0] == 'xhd'){
                    return 100 * $info[1];
                } else {
                    return 5 + round($info[1] / 10);
                }
                return;
            case 'ACTIONS':
                $bonus = round($info[1] / 10);
                if($info[0] == 'net'){
                    $bonus *= 2;
                }
                
                if($bonus >= 15){
                    $bonus = 15;
                } elseif($bonus <= 0){
                    $bonus = 1;
                }

                return $bonus;
            case 'AV':
                if($info[0] == 'local'){
                    return 10 * $info[1];
                } else {
                    return 15 * $info[1];
                }
            case 'DOOM':
                return 10000;
            case 'DDOS':
                $totalDamage = $info[1];
                $seizedBefore = $info[2];
                if($totalDamage == 0){
                    if($seizedBefore == 1){
                        return 100;
                    }
                }
                return 250 + round($info[0] * 0.01); //1% do total do DDoS
            case 'COLLECT':
                if($info['0'] == 'btc'){
                    return $info[1] * 600;
                } else {
                    return round($info[1] * 0.1);
                }
            case 'PUZZLE':
                return 25;
            case 'NMAP':
                return 25 + 25 * $info[0];
            case 'ANALYZE':
                return 15;
                
                
                


                            
        }
        
    }
    
    public function language_set($setSession = false){
        
        if($setSession){
            $_SESSION['language'] = self::language_set();
        }
        
        if(!isset($_SERVER['HTTP_HOST'])){
            return 'en_US';
        }
        
        if($_SERVER['HTTP_HOST'] == 'hackerexperience.com' || $_SERVER['HTTP_HOST'] == 'www.hackerexperience.com'){
            return 'en_US';
        }
        
        if($_SERVER['HTTP_HOST'] == 'br.hackerexperience.com' || $_SERVER['HTTP_HOST'] == 'www.br.hackerexperience.com'){
            return 'pt_BR';
        }
        
        return 'en_US';        
        
    }
    
    public function language_get(){
        
        if(isset($_SESSION['language'])){
            //this is disabled because the user could access en.he.com in pt or br.he.com in english
            return $_SESSION['language'];
        }
        
        return self::language_set(true);
        
    }
    
    public function help($page, $info = false){
        
        $ext = '';
        
        switch($page){
            case 'clan':
                return 'https://wiki.hackerexperience.com/'._('en').':clans';
            case 'missions':
                if($info == 'level'){
                    $ext = _('#mission_level');
                }
                return 'https://wiki.hackerexperience.com/'._('en').':missions'.$ext;
            case 'hardware':
                return 'https://wiki.hackerexperience.com/'._('en').':hardware';
            case 'log':
                return 'https://wiki.hackerexperience.com/'._('en').':log';
            case 'university':
                return 'https://wiki.hackerexperience.com/'._('en').':university';
            case 'finances':
                return 'https://wiki.hackerexperience.com/'._('en').':finances';
            case 'list':
                if($info == 'ddos'){
                    return 'https://wiki.hackerexperience.com/'._('en').':ddos';
                } elseif($info == 'collect'){
                    return 'https://wiki.hackerexperience.com/'._('en').':hacked_database';
                }
                return 'https://wiki.hackerexperience.com/'._('en').':hacked_database';
            case 'task':
                return 'https://wiki.hackerexperience.com/'._('en').':processes';
            case 'software':
                if($info == 'external'){
                    return 'https://wiki.hackerexperience.com/'._('en').':hardware'._('#external_hard_drive');
                }
                return 'https://wiki.hackerexperience.com/'._('en').':softwares';
            case 'internet':
                if($info == 'hack'){
                    return 'https://wiki.hackerexperience.com/'._('en').':hacking';
                }
                return 'https://wiki.hackerexperience.com/'._('en').':internet';
                
        }
        
    }
    
}



?>
