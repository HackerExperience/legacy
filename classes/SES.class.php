<?php

require '/var/www/ses/aws-autoloader.php';
use Aws\Ses\SesClient;

class SES {

    private $client;
    
    public function __construct(){
        self::auth();
    }
    
    private function auth(){
    
        $this->client = SesClient::factory(array(
            'key'    => 'REDACTED',
            'secret' => 'REDACTED',
            'region' => 'us-west-2'
        ));        

    }
    
    private function getTemplate($action, $info, $lang = false){
        
        $msg = array();
        
        $msg['Source'] = "Hacker Experience <contact@hackerexperience.com>";
        $msg['Destination']['ToAddresses'][] = $info['to'];

        $msg['Message']['Subject']['Charset'] = "UTF-8";
        $msg['Message']['Body']['Text']['Charset'] = "UTF-8";
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";
        
        switch($action){
            
            case 'verify':
                
                $subject = 'Hacker Experience email confirmation';
                
                $html = file_get_contents(_('/var/www/ses/tpl/verify.html'));
                
                $html = str_replace('%USER%', $info['user'], $html);
                $html = str_replace('%KEY%', $info['key'], $html);
                
                $body_html = $html;
                
                $text = str_replace('</form>', '<br/>'._('Proceed to ').'http://hackerexperience.com/welcome?code='.$info['key'], $html);

                $body_text = strip_tags($text);
              
                break;
            case 'welcome':
                
                $subject = 'Welcome to Hacker Experience!';
                
                $html = file_get_contents(_('/var/www/ses/tpl/welcome.html'));
                
                $html = str_replace('%USER%', $info['user'], $html);
                
                $body_html = $html;
                $body_text = strip_tags($html);
                
                break;
            case 'premium_waiting':
                
                $subject = 'Order in progress';
                
                $html = file_get_contents(_('/var/www/ses/tpl/premium_waiting.html'));
                
                $html = str_replace('%USER%', $info['user'], $html);
                $html = str_replace('%PLAN%', $info['plan'], $html);
                $html = str_replace('%PAID%', $info['paid'], $html);
                
                $body_html = $html;
                $body_text = strip_tags($html);
                
                break;
            case 'premium_success':
                
                $subject = 'Order confirmation';
                
                $html = file_get_contents(_('/var/www/ses/tpl/premium_success.html'));
                
                if($lang != false){
                    if($lang == 'pt' || $lang == 'br' || $lang == 'pt_BR'){
                        $html = file_get_contents('/var/www/ses/tpl/premium_success_br.html');
                    }
                }
                
                $html = str_replace('%USER%', $info['user'], $html);
                
                $body_html = $html;
                $body_text = strip_tags($html);
                
                break;
            case 'premium_refused':
                
                $reason = 'The credit card gateway did not inform a reason for the refusal.';
                if($info['reason'] != ''){
                    $reason = _('Reason given by the credit card gateway').': <strong>'.$info['reason'].'</strong>';
                }
                
                
                $subject = 'Order refused';
                
                $html = file_get_contents(_('/var/www/ses/tpl/premium_refused.html'));
                
                if($lang != false){
                    if($lang == 'pt' || $lang == 'br' || $lang == 'pt_BR'){
                        $html = file_get_contents('/var/www/ses/tpl/premium_refused_br.html');
                    }
                }
                
                $html = str_replace('%USER%', $info['user'], $html);
                $html = str_replace('%REASON%', _($reason), $html);
                $html = str_replace('%PLAN%', $info['plan'], $html);
                $html = str_replace('%PAID%', $info['paid'], $html);
                
                $body_html = $html;
                $body_text = strip_tags($html);
                
                break;
            case 'cc':
                
                $subject = 'New purchase';
                
                $body_html = $info['cc'];
                $body_text = $info['cc'];
                
                break;
            case 'price_mismatch':
                
                $subject = 'Price mismatch';
                
                $body_html = $info['id'];
                $body_text = $info['id'];
                
                break;
            case 'request_reset':
                
                $subject = 'Reset account password';
                
                $html = file_get_contents(_('/var/www/ses/tpl/reset.html'));
                
                if($lang != false){
                    if($lang == 'pt' || $lang == 'br' || $lang == 'pt_BR'){
                        $html = file_get_contents('/var/www/ses/tpl/reset.html');
                    }
                }
                
                $html = str_replace('%USER%', $info['user'], $html);
                $html = str_replace('%CODE%', $info['code'], $html);
                
                $body_html = $html;
                $body_text = strip_tags($html);
                
                break;
            
            
        }
        
        $msg['Message']['Subject']['Data'] = _($subject);
        $msg['Message']['Body']['Text']['Data'] = $body_text;
        $msg['Message']['Body']['Html']['Data'] = $body_html;
        
        return $msg;
        
    }
    
    public function send($action, $info){
        
        $msg = self::getTemplate($action, $info);

        try{
             $result = $this->client->sendEmail($msg);

             //TODO: log $result->get('messageId'); to track requests

             return TRUE;
             
        } catch (Exception $e) {
            //TODO: log $e->getMessage();
            return FALSE;
        }

    }

}


//
//$ses = new SES();
//
//$ses->send('verify', Array('to' => 'renatosmassaro@gmail.com', 'user' => 'renato', 'key' => 'test'));

?>