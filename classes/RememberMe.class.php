<?php

/*
 * 2019: Taken from <link>
 * Retirado de http://stackoverflow.com/questions/1354999/keep-me-logged-in-the-best-approach/17267718#17267718
 */

class RememberMe {
    private $key = null;
    private $pdo;

    function __construct($privatekey, $db) {
        $this->key = $privatekey;
        $this->pdo = $db;
    }

    public function auth() {

        // Check if remeber me cookie is present
        if (! isset($_COOKIE["auto"]) || empty($_COOKIE["auto"])) {
            return false;
        }

        // Decode cookie value
        if (! $cookie = @json_decode($_COOKIE["auto"], true)) {
            return false;
        }

        // Check all parameters
        if (! (isset($cookie['user']) || isset($cookie['token']) || isset($cookie['signature']))) {
            return false;
        }

        $var = $cookie['user'] . $cookie['token'];

        // Check Signature
        if (! $this->verify($var, $cookie['signature'])) {
            return -1;
        }

        // Check Database
        $info = $this->getdb($cookie['user']);
        if (! $info) {
            return false; // User must have deleted accout
        }

        // Check User Data
        if (! $info = json_decode($info, true)) {
            return -1;
        }

        // Verify Token
        if ($info['token'] !== $cookie['token']) {
            return -1;
        }

        /**
         * Important
         * To make sure the cookie is always change
         * reset the Token information
         */

        $this->remember($info['user'], true, true);
        return $info;
        
    }
    
    public function getdb($user){
              
        $sql = 'SELECT COUNT(*) AS total, token FROM users_online WHERE id = '.$user.' LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->token;
                
    }

    public function setdb($user, $encoded, $update, $expire){
        
        if($update){
            $sql = 'UPDATE users_online SET token = :token WHERE id = :id';
        } else {
            $sql = 'INSERT INTO users_online (id, token) 
                    VALUES (:id, :token)';
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $user, ':token' => $encoded));
        
        if($expire){
            
            $sql = 'REPLACE INTO users_expire (userID, expireDate) 
                    VALUES (:id, DATE_ADD(NOW(), INTERVAL 2 HOUR))';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':id' => $user));
            
        }
        
        $sql = 'INSERT INTO stats_login (userID) 
                VALUES (:id)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $user));
        
    }
    
    public function remember($user, $updateQuery, $createCookie) {
        $cookie = [
                "user" => $user,
                "token" => $this->getRand(64),
                "signature" => null
        ];
        $cookie['signature'] = $this->hash($cookie['user'] . $cookie['token']);
        $encoded = json_encode($cookie);

        // Add User to database
        $this->setdb($user, $encoded, $updateQuery, !$createCookie);

        /**
         * Set Cookies
         * In production enviroment Use
         * setcookie("auto", $encoded, time() + $expiration, "/~root/",
         * "example.com", 1, 1);
         */
        if($createCookie){
            setcookie("auto", $encoded, time() + 172800); // Sample
        }
    }

    public function verify($data, $hash) {
        $rand = substr($hash, 0, 4);
        return $this->hash($data, $rand) === $hash;
    }

    private function hash($value, $rand = null) {
        $rand = $rand === null ? $this->getRand(4) : $rand;
        return $rand . bin2hex(hash_hmac('sha256', $value . $rand, $this->key, true));
    }

    private function getRand($length) {
        switch (true) {
            case function_exists("mcrypt_create_iv") :
                $r = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
                break;
            case function_exists("openssl_random_pseudo_bytes") :
                $r = openssl_random_pseudo_bytes($length);
                break;
            case is_readable('/dev/urandom') : // deceze
                $r = file_get_contents('/dev/urandom', false, null, 0, $length);
                break;
            default :
                $i = 0;
                $r = "";
                while($i ++ < $length) {
                    $r .= chr(mt_rand(0, 255));
                }
                break;
        }
        return substr(bin2hex($r), 0, $length);
    }
    
    public function rememberlogin(){
                                
        $data = self::auth();

        if ($data) {

            require '/var/www/classes/Database.class.php';
            $database = new LRSys();
            
            require_once '/var/www/classes/Player.class.php';
            $player = new Player();

            if($player->verifyID($data['user'])){
                
                $redirect = 'index';
                if(array_key_exists('GOING_ON', $_SESSION)){
                    $redirect = $_SESSION['GOING_ON'];
                    unset($_SESSION['GOING_ON']);
                }
                
                $username = $player->getPlayerInfo($data['user'])->login;
                $database->login($username, '', 'remember');
                                
                header("Location:".$redirect);
                exit();
                
            }

        } elseif($data == -1){ //invalid or tampered cookie.
            $_SESSION = NULL;
            session_destroy();
            exit("Invalid token");
        }
        
    }
    
}

?>
