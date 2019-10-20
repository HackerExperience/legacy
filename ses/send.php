<?php

require 'aws-autoloader.php';
use Aws\Ses\SesClient;

function send(){

    $client = SesClient::factory(array(
        'key'    => 'REDACTED',
        'secret' => 'REDACTED',
        'region' => 'us-west-2'
    ));

    $msg = array();
    $msg['Source'] = "renatosmassaro@gmail.com";
    //ToAddresses must be an array
    $msg['Destination']['ToAddresses'][] = "renatosmassaro@gmail.com";

    $msg['Message']['Subject']['Data'] = "Text only subject";
    $msg['Message']['Subject']['Charset'] = "UTF-8";

    $msg['Message']['Body']['Text']['Data'] ="Text data of email";
    $msg['Message']['Body']['Text']['Charset'] = "UTF-8";
    $msg['Message']['Body']['Html']['Data'] ="HTML Data of email<br />";
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
         $result = $client->sendEmail($msg);

         //save the MessageId which can be used to track the request
         $msg_id = $result->get('MessageId');
         echo("MessageId: $msg_id");

         //view sample output
         print_r($result);
    } catch (Exception $e) {
         //An error happened and the email did not get sent
         echo($e->getMessage());
    }
    //view the original message passed to the SDK 
    print_r($msg);

}

?>