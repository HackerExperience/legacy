<?php

class NPC {

    private $pdo;
    private $session;

    function __construct(){

        //

        $this->pdo = PDO_DB::factory();
	$this->session = new Session();

    }

    public function issetNPC($nid){

        if(is_numeric($nid)){

            $this->session->newQuery();
            $sqlSelect = "SELECT npcType FROM npc WHERE id = $nid LIMIT 1";
            $data = $this->pdo->query($sqlSelect)->fetchAll();

            if (count($data) == '1') {

                return TRUE;

            } else {

                return FALSE;

            }

        }

    }

    public function getNPCInfo($nid, $lang = ''){

        if(self::issetNPC($nid)){

            $table = 'npc_info_en';
                            
            if($lang == 'pt' || $lang == 'br'){
                $table = 'npc_info_pt';
            }
            
            $this->session->newQuery();
            $sqlSelect = "  SELECT npcIP, npcType, $table.web AS npcWeb, npcPass, $table.name 
                            FROM npc
                            LEFT JOIN $table
                            ON $table.npcID = npc.id
                            WHERE id = $nid 
                            LIMIT 1";
            $data = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ);

            return $data;

        } else {

            die("Invaalid ID");

        }

    }
    
    public function downNPC($nid){

        $this->session->newQuery();
        $sqlSelect = "SELECT COUNT(*) AS total FROM npc_down WHERE npcID = $nid LIMIT 1";
        $total = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->total;
        
        if($total == 1){
            return TRUE;
        } else {
            return FALSE;
        }
          
    }
    
    public function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'){
        
            $str = '';
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
            return $str;
            
    }
    
    public function generateNPC($type){
        
        $gameIP1 = rand(0, 255);
        $gameIP2 = rand(0, 255);
        $gameIP3 = rand(0, 255);
        $gameIP4 = rand(0, 255);

        $gameIP = $gameIP1 . '.' . $gameIP2 . '.' . $gameIP3 . '.' . $gameIP4;
        $genIP = ip2long($gameIP);        
        
        $this->session->newQuery();
        $sql = 'INSERT INTO npc (id, npcType, npcIP, npcPass, downUntil)
                VALUES (\'\', \''.$type.'\', \''.$genIP.'\', \''.self::randString(8).'\', \'\')';
        $this->pdo->query($sql);
        
        $npcID = $this->pdo->lastInsertId();

        $this->session->newQuery();
        $sql = "INSERT INTO npc_info_en (npcID, name, web)
                VALUES ('".$npcID."', 'Initech Corp', '')";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "INSERT INTO npc_info_pt (npcID, name, web)
                VALUES ('".$npcID."', 'Initech Corp', '')";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sqlQuery = "INSERT INTO hardware (userID, cpu, net, isNPC) VALUES (?, '1500', '10', '1')";
        $sqlReg = $this->pdo->prepare($sqlQuery);
        $sqlReg->execute(array($npcID));
        
        $this->session->newQuery();
        $sql = 'INSERT INTO log (userID, text, isNPC)
                VALUES (\''.$npcID.'\', \'\', \'1\')';
        $this->pdo->query($sql);
        
        return $npcID;
        
    }
    
    public function getNPCByKey($key){
        
        $this->session->newQuery();
        $sql = "SELECT npc.id, npc.npcIP FROM npc INNER JOIN npc_key ON npc.id = npc_key.npcID WHERE npc_key.key = '".$key."' LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
    }

}

?>