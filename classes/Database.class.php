<?php

class LRSys {

    public $name;
    public $user;
    private $pass;
    public $email;
    public $keepalive;
    
    private $session;
    private $lang;
    private $pdo;
    private $process;
    private $forum;

    private $log;
    private $ranking;
    private $storyline;
    private $clan;
    
    function __construct() {

        $this->pdo = PDO_DB::factory();
        
        require_once 'Session.class.php';

        $this->session = new Session();


        require '/var/www/classes/Player.class.php';
        require '/var/www/classes/PC.class.php';
        require '/var/www/classes/Ranking.class.php';
        require '/var/www/classes/Storyline.class.php';
        require '/var/www/classes/Clan.class.php';

        $this->log = new LogVPC();
        $this->ranking = new Ranking();
        $this->storyline = new Storyline();
        $this->clan = new Clan();

        $this->keepalive = FALSE;
        
    }

    public function set_keepalive($keep){
        $this->keepalive = $keep;
    }

    public function register($regUser, $regPass, $regMail) {

        $this->user = $regUser;
        $this->pass = $regPass;
        $this->email = $regMail;

        $sql = 'SELECT COUNT(*) AS total FROM stats_register WHERE ip = :ip AND TIMESTAMPDIFF(MINUTE, registrationDate, NOW()) < 10';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':ip' => $_SERVER['REMOTE_ADDR']));
        $spamCheck = $stmt->fetch(PDO::FETCH_OBJ)->total; 

        if($spamCheck >= 1){
            exit('IP blocked for multiple registrations. Try again in 10 minutes.');
        }

        if ($this->verifyRegister()) {

            require 'BCrypt.class.php';
            $bcrypt = new BCrypt();
            
            $hash = $bcrypt->hash(htmlentities($this->pass));
            
            $gameIP1 = rand(0, 255);
            $gameIP2 = rand(0, 255);
            $gameIP3 = rand(0, 255);
            $gameIP4 = rand(0, 255);

            $gameIP = $gameIP1 . '.' . $gameIP2 . '.' . $gameIP3 . '.' . $gameIP4;

            require '/var/www/classes/Forum.class.php';
            $forum = new Forum();

            require '/var/www/classes/Python.class.php';
            
            $python = new Python();
            $python->createUser($this->user, $hash, $this->email, $gameIP);

            $sql = 'SELECT COUNT(*) AS total, id FROM users WHERE login = :user LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':user' => $this->user));
            $regInfo = $stmt->fetch(PDO::FETCH_OBJ); 
            
            if($regInfo->total == 0){
                $this->session->addMsg('Error while completing registration. Please, try again later.', 'error');
                return FALSE;
            }

            require '/var/www/classes/EmailVerification.class.php';
            $EmailVerification = new EmailVerification();
            
            if(!$EmailVerification->sendMail($regInfo->id, $this->email, $this->user)){
                $this->session->addMsg('Registration complete. You can login now.', 'notice');
                //return FALSE;
                //TODO: report to admin
            }
            
            require '/var/www/classes/Finances.class.php';
            $finances = new Finances();
            
            $finances->createAccount($regInfo->id);

            $forum->externalRegister($this->user, $this->pass, $this->email, $regInfo->id);
            
            $sql = "INSERT INTO stats_register (userID, ip) VALUES ('".$regInfo->id."', '".$_SERVER['REMOTE_ADDR']."')";
            $this->pdo->query($sql);

            $this->session->addMsg('Registration complete. You can login now.', 'notice');

            return TRUE;

        } else {

            return FALSE;
        }
    }

    private function verifyRegister() {

        $system = new System();
        
        if(!$system->validate($this->user, 'username')){
            $this->session->addMsg(sprintf(_('Invalid username. Allowed characters are %s.'), '<strong>azAZ09._-</strong>'), 'error');
            return FALSE;
        }
        
        if(!$system->validate($this->email, 'email')){
            $this->session->addMsg(sprintf(_('The email %s is not valid.'), '<strong>'.$this->email.'</strong>'), 'error');
            return FALSE;
        }

        //pegando spam dos fdp: rIcFCzREv2VOPIU@rIcFCzREv2VOPIU.com 
        //76437363@gmail.com
        //UdJ@jgD.com
        if ((strlen(preg_replace('![^A-Z]+!', '', $this->email)) >= 5 && preg_match_all("/[0-9]/", $this->email) >= 2) || preg_match_all("/[0-9]/", $this->email) >= 5){
            $this->session->addMsg(_('Registration complete. You can login now.'), 'notice');
            return FALSE;
        }

        if (strlen(preg_replace('![^A-Z]+!', '', $this->email)) >= 2 && strlen($this->email) <= 12){
            $this->session->addMsg(_('Registration complete. You can login now.'), 'notice');
            return FALSE;
        }
        
        
        
        //verifico no banco se já existe um usuário ou email cadastrado.
        $this->session->newQuery();
        $sqlQuery = "SELECT email FROM users WHERE login = ? OR email = ? LIMIT 1";
        $sqlLog = $this->pdo->prepare($sqlQuery);
        $sqlLog->execute(array($this->user, $this->email));

        if ($sqlLog->rowCount() == '1') {

            $dados = $sqlLog->fetch();

            if ($dados['email'] == $this->email) {
                $this->session->addMsg('This email is already used.', 'error');
            } else {
                $this->session->addMsg('This username is already taken.', 'error');
            }
            
            return FALSE;
            
            // 2019: what could possibly go wrong?
            //ainda falta verificar se email tá ok, se tem algum caracter especial, sql inject etc etc, mas fica pra depois                       
        } elseif (strlen($this->user) == '0' || strlen($this->pass) == '0' || strlen($this->email) == '0') {

            $this->session->addMsg('Some fields are empty.', 'error');
            
            return FALSE;
        }
        
        if(strlen($this->user) > 15){
            $this->session->addMsg('Yor username is too big :( Please, limit it to 15 characteres.', 'error');
            
            return FALSE;
        }
        
        return TRUE;
        
    }

    public function login($logUser, $logPass, $special = FALSE) {
                
        date_default_timezone_set('UTC');
        
        $facebook = $twitter = $remember = FALSE;
        
        if($special){
            if($special == 'remember'){
                $remember = TRUE;
            } elseif($special == 'facebook'){
                $facebook = TRUE;
            } elseif($special == 'twitter') {
                $twitter = TRUE;
                unset($_SESSION['twitter_data']);
            } else {
                exit("Edit special");
            }
        }
          
        if(!$this->session){
            $this->session = new Session();
        }
        
        require_once '/var/www/classes/Mission.class.php';        
        
        $this->mission = new Mission();

        $this->user = $logUser;
        $this->pass = $logPass;

        if ($this->verifyLogin($facebook, $remember, $twitter)) {

            require 'BCrypt.class.php';
            $bcrypt = new BCrypt();

            // 2019: There are some important security vulns here
            $this->session->newQuery();
            $sqlQuery = "   SELECT password, id 
                            FROM users
                            WHERE BINARY login = ?
                            LIMIT 1";
            $sqlLog = $this->pdo->prepare($sqlQuery);
            $sqlLog->execute(array($this->user));
            
            if ($sqlLog->rowCount() == '1') {

                $dados = $sqlLog->fetchAll();
                   
                if($bcrypt->verify($this->pass, $dados['0']['password']) || $facebook || $remember || $twitter){

                    $log = $this->log;
                    $ranking = $this->ranking;
                    $storyline = $this->storyline;
                    $clan = $this->clan;

                    if(!$facebook && !$twitter){
                    
                        $sql = "SELECT COUNT(*) AS total FROM users_facebook WHERE gameID = ".$dados['0']['id']." LIMIT 1";
                        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
                        
                        if($total == 1){
                            $this->session->addMsg('Facebook fail', 'error');
                            return FALSE;
                        }
                        
                        $sql = "SELECT COUNT(*) AS total FROM users_twitter WHERE gameID = ".$dados['0']['id']." LIMIT 1";
                        $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
                        
                        if($total == 1){
                            $this->session->addMsg('Twitter fail', 'error');
                            return FALSE;
                        }
                        
                    }
                    
                    $sql = "SELECT COUNT(*) AS total FROM users_premium WHERE id = ".$dados['0']['id']." LIMIT 1";
                    $total = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

                    if($total == 1){
                        $premium = 1;
                    } else {
                        $premium = 0;
                    }

                    require '/var/www/classes/Forum.class.php';
                    $forum = new Forum();

                    $forum->login($this->user, $this->pass, TRUE);
                    
                    $this->session->loginSession($dados['0']['id'], $this->user, $premium, $special);

                    self::loginDatabase($dados['0']['id']);
                    $certsArray = $ranking->cert_getAll();
        
                    $this->mission->restoreMissionSession($dados['0']['id']);

                    $this->session->certSession($certsArray);

                    if($clan->playerHaveClan($dados['0']['id'])){
                        $_SESSION['CLAN_ID'] = $clan->getPlayerClan($dados['0']['id']);
                    } else {
                        $_SESSION['CLAN_ID'] = 0;
                    }

                    $_SESSION['LAST_CHECK'] = new DateTime('now');
                    $_SESSION['ROUND_STATUS'] = $storyline->round_status();

                    if($_SESSION['ROUND_STATUS'] == 1){
                        $log->addLog($dados['0']['id'], $log->logText('LOGIN', Array(0)), '0');
                        $this->session->exp_add('LOGIN');
                    }
                    
                    return TRUE;

                } else {

                    $this->session->addMsg('Username and password doesnt match. Some accounts were lost, sorry!', 'error');
                    return FALSE;

                }
                
            } else {

                $this->session->addMsg('Username and password doesnt match. Some accounts were lost, sorry!', 'error');
                return FALSE;
                
            }
            
        }
    }
    
    private function loginDatabase($id){
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM users_online WHERE id = '.$id.' LIMIT 1';
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total > 0){
            $this->session->newQuery();
            $sql = 'DELETE FROM users_online WHERE id = '.$id.' LIMIT 1';
            $this->pdo->query($sql);
        }
        
        require_once '/var/www/classes/RememberMe.class.php';
        $key = pack("H*", 'REDACTED');
        $rememberMe = new RememberMe($key, $this->pdo);
        $rememberMe->remember($id, false, $this->keepalive);
                
        $this->session->newQuery();
        $sql = 'UPDATE users SET lastLogin = NOW() WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        
        setcookie('logged', '1', time() + 172800);
        
        
    }
    
    private function verifyLogin($fb, $tt, $rm) {

        if($fb || $rm || $tt){
            return TRUE;
        }
        
        if (strlen($this->user) == '0' || strlen($this->pass) == '0') {

            $this->session->addMsg('Some fields are empty.', 'error');
            return FALSE;
            
        } else {

            return TRUE;
            
        }
    }

}

?>
