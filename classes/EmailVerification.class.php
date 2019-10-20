<?php

class EmailVerification {

    private $code;
    private $session;
    private $pdo;
    
    public function __construct(){
        
        $this->session = new Session();
        $this->pdo = PDO_DB::factory();        
        
    }
    
    private function generateKey(){        
        return uniqid('he', true);        
    }
    
    private function saveKey($userID, $email){
                
        $this->session->newQuery();

        $sql = 'INSERT INTO email_verification (userID, email, code) VALUES (:userID, :email, :code)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $userID, ':email' => $email, ':code' => $this->code));
                
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM email_verification WHERE userID = '.$userID.' LIMIT 1';
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function sendMail($userID, $email, $username){
        
        $this->code = self::generateKey();
        
        if(!self::saveKey($userID, $email)){
            return FALSE;
        }        
        
        require '/var/www/classes/SES.class.php';            
        $ses = new SES();
        return $ses->send('verify', Array('to' => $email, 'user' => $username, 'key' => $this->code));        
    }
    
    private function issetCode($userID){
        
        $this->session->newQuery();

        $sql = 'SELECT COUNT(*) AS total FROM email_verification WHERE userID = :userID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $userID));
        
        if($stmt->fetch(PDO::FETCH_OBJ)->total == 1){
            return TRUE;
        }
            
        return FALSE;
        
    }
    
    private function getCode($userID){
        
        $this->session->newQuery();

        $sql = 'SELECT code FROM email_verification WHERE userID = :userID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $userID));
        
        return $stmt->fetch(PDO::FETCH_OBJ)->code;
        
    }   
    
    private function removeKey($userID){
        
        $this->session->newQuery();
        $sql = 'SELECT login, email FROM users WHERE id = :userID LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $userID));
        $userInfo = $stmt->fetch(PDO::FETCH_OBJ);
        
        require '/var/www/classes/SES.class.php';            
        $ses = new SES();
        $ses->send('welcome', Array('to' => $userInfo->email, 'user' => $userInfo->login));
        
        $this->session->newQuery();
        $sql = 'DELETE FROM email_verification WHERE userID = '.$userID;
        $this->pdo->query($sql);
        
    }
    
    public function isVerified($userID){
        if(self::issetCode($userID)){
            return FALSE;
        }
        return TRUE;
    }
    
    public function verify($userID, $code){
        
        if(!self::issetCode($userID)){
            return TRUE;
        }
        
        if(self::getCode($userID) == $code){
            self::removeKey($userID);
            return TRUE;
        } else {
            return FALSE;
        }        
        
    }
    
    public function codeOnlyVerification($code){
                
        $this->session->newQuery();

        $sql = 'SELECT COUNT(*) AS total, userID FROM email_verification WHERE code = :code LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':code' => $code));
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if($data->total == 1){
            
            self::removeKey($data->userid);
            return $data->userid;
            
        }
        
        return 0;
        
        
        
    }
    
    
}