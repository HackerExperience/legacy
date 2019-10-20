<?php

require '/var/www/classes/PDO.class.php';

$pdo = PDO_DB::factory();

$sql = 'SELECT COUNT(*) AS total FROM users_premium WHERE TIMESTAMPDIFF(SECOND, NOW(), premiumUntil) < 0';
$totalExpired = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

if($totalExpired > 0){
    
    $sql = 'SELECT id FROM users_premium WHERE TIMESTAMPDIFF(SECOND, NOW(), premiumUntil) < 0';
    $data = $pdo->query($sql)->fetchAll();
    
    for($i=0;$i<sizeof($data);$i++){
        
        $sql = 'UPDATE users SET premium = 0 WHERE id = '.$data[$i]['id'];
        $pdo->query($sql);
        
        $sql = 'UPDATE profile SET premium = 0 WHERE id = '.$data[$i]['id'];
        $pdo->query($sql);
        
    }
    
}

?>
