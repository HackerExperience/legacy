<?php

class Python {

    private $python_path;
    private $game_path;
    private $path;
    private $file;
    private $args;
    private $queries = 0;
    
    function __construct(){
        
        $this->python_path = '/usr/bin/env python';
        $this->game_path = '/var/www/';
        $this->args = '';
        $this->log = ' 2>&1 /var/log/game/python.log';
        
    }
    
    public function add_badge($userID, $badgeID, $clan = ''){
        
        if($clan == ''){
            $userBadge = 'user';
        } else {
            $userBadge = 'clan';
        }
        
        $this->path = 'python/';
        $this->file = 'badge_add.py';
        $this->args = escapeshellarg($userBadge).' '.escapeshellarg($userID).' '.escapeshellarg($badgeID);
        $this->queries = 15;

        self::call();
        
    }
    
    public function generateProfile($id, $l = 'en'){
        
        $this->path = 'python/';
        $this->file = 'profile_generator.py';
        $this->args = escapeshellarg($id).' '.escapeshellarg($l);
        $this->queries = 20;

        self::call();

    }
    
    public function createUser($username, $password, $email, $ip, $facebook = 0, $social_network = ''){
        
        $this->path = 'python/';
        $this->file = 'create_user.py';
        $this->args = escapeshellarg($username).' \''.$password.'\' '.escapeshellarg($email).' '.escapeshellarg($ip).' '.escapeshellarg($facebook).' '.escapeshellarg($social_network);
        $this->queries = 10;

        self::call();        
        
    }
    
    private function call(){
                
        exec($this->python_path.' '.$this->game_path.$this->path.$this->file.' '.$this->args.$this->log);
        exec($this->python_path.' '.$this->game_path.$this->path.'query_counter.py '.$this->queries);
                
    }
    
}

?>
