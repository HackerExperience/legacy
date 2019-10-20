<?php

class PDO_DB {

    public $dbh; 
    private static $dsn1  = 'mysql:unix_socket=';
    private static $dsn2  = ';port=3306;dbname=game';
    private static $user = 'he'; 
    private static $pass = 'REDCATED'; 
    private static $dbOptions = array(
        //PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION //TODO: remove this line on production (maybe not, just hide php errors, so I can see logs)
    );

    public static function factory() { 
        
        //$sock = '/var/run/mysql/mysql.sock'; //localhost
        $sock = '/var/lib/mysql/mysql.sock';
            
        if(!isset(self::$dbh)){
            $dbh = new PDO(self::$dsn1.$sock.self::$dsn2,self::$user,self::$pass, self::$dbOptions); 
        }
        return $dbh;
    }
    
}

?>
