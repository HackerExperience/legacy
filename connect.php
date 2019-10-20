<?php


$dsn = 'mysql:host=localhost;port=3306;dbname=game';
$dbUser = 'he';
$dbPass = 'REDACTED';
$dbOptions = array(
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_CASE => PDO::CASE_LOWER
);

        if(!isset($_SESSION['PDO'])){
            $_SESSION['PDO'] = 0;
        }
        
        $_SESSION['PDO']++;

try {
    //$pdo = new PDO($dsn, $dbUser, $dbPass, $dbOptions);
    
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados');
}

?>
