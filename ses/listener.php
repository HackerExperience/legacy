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
   $pdo = new PDO($dsn, $dbUser, $dbPass, $dbOptions);
    
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados');
}


require 'ipn_handler.class.php';

/**
 * Logs IPN messages to a file.
 */
class Logging_Ipn_Handler extends IPN_Handler
{
        public function process(array $post_data)
        {
                $data = parent::process($post_data);

                if($data === FALSE)
                {
                        header('HTTP/1.0 400 Bad Request', true, 400);
                        exit;
                }
                
                $output = implode("\t", array(time(), json_encode($data)));
                file_put_contents('log.txt', $output.PHP_EOL, FILE_APPEND);
                
                return $data;
        }
}

//date_default_timezone_set('Europe/Oslo');

//$handler = new Logging_Ipn_Handler();
//$output = $handler->process($_POST);

if($output == NULL){
    //exit();
}


// 2019: I think that's a test case. Not sure what it does. And I have no idea what this Paypal stuff is doing within the `ses` folder.......

$custom = 8;
$sign = 'renatooo';
$status = 'Denied';
$output = '1348944187	{"address_city":"San Jose","address_country":"United States","address_country_code":"US","address_name":"John Smith","address_state":"CA","address_status":"confirmed","address_street":"123, any street","address_zip":"95131","business":"seller@paypalsandbox.com","charset":"utf-8","charset_original":"windows-1252","custom":"xyz123","first_name":"John","item_name":"something","item_number":"AK-1234","last_name":"Smith","mc_currency":"USD","mc_fee":"0.44","mc_gross":"12.34","mc_gross_1":"9.34","notify_version":"2.1","payer_email":"buyer@paypalsandbox.com","payer_id":"TESTBUYERID01","payer_status":"verified","payment_date":"11:40:28 Sep 29, 2012 PDT","payment_status":"Completed","payment_type":"instant","quantity":"1","receiver_email":"seller@paypalsandbox.com","receiver_id":"TESTSELLERID1","residence_country":"US","shipping":"3.04","tax":"2.02","test_ipn":"1","txn_id":"289291840","txn_type":"web_accept","verify_sign":"REDACTED"}';


$login = 'napster';    
$to = 'renatosmassaro@gmail.com';

$footer = ' <br/><br/> -------- <br/> Best regards, <br/> <b>Hacker Experience Staff</b>';    

require "classes/PHPMailer.class.php";
$mail = new PHPMailer();

$link = 'http://beta.hackerexperience.com/premium.php?key='.md5($sign);

switch($output['payment_status']){
    
    case 'Completed':
        
        $subject = 'Premium Account Activated!';
        $text = $login.', I am very happy to announce that you now own a <b> premium account </b> on Hacker Experience. <br/><br/>
                Thank you very much for supporting the game, and helping to pay server expenses<br/><br/>
                All you have to do is click on this link: '.$link.'<br/><br/>
                Have fun and enjoy the game, now as a premium user :).'.$footer;
                    
        if(!$mail->sendMail($subject, $text, $to, 3, 1)){
            echo 'bad bad server';
        }
        
        break;
    case 'Pending':
        
        $subject = 'Premium Account Pending';
        $text = $login.', thank you for supporting the game. We received your request, but the status is pending. This usually occurs due to different currency exchange<br/>
                But no worries! Your premium account was activated, and we hope to solve the pending problem ASAP.<br/><br/>
                All you have to do is click on this link: '.$link.'<br/><br/>
                <b>If paypal does not complete your process, we will contact you direcly, and your premium status may be frozen for a few hours until the situation is solved.</b><br/><br/>
                Feel free to contact us at <u>contact@hackerexperience.com</u><br/>'.$footer;                     
        
        if(!$mail->sendMail($subject, $text, $to, 3, 1)){
            echo 'bad bad server';
        }
        
        break;
    case 'Denied':
    
        $subject = 'Premium account activated!';
        $text = $login.', I am very happy to announce that you now own a <b> premium account </b> on Hacker Experience. <br/>
                Thank you very much for supporting the game, and helping to pay server expenses<br/> Have fun and enjoy the game, now as a premium user.'.$footer;
                    
        if(!$mail->sendMail($subject, $text, $to, 3, 1)){
            echo 'bad bad server';
        }
        
        break;
    
    
}

?>