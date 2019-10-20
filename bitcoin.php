<?php

session_start();

require '/var/www/classes/Session.class.php';
$session = new Session();

$result = Array();
$result['status'] = 'ERROR';
$result['redirect'] = '';
$result['msg'] = 'STOP SPYING ON ME!';

if($session->issetLogin()){

    $result['status'] = 'OK';
        
    require 'template/gameHeader.php'; //TODO: is it really needed? (perhaps for set_time, but..)
    
    if(isset($_POST['func'])){

        $func = $_POST['func'];

        switch($func){
            
            case 'btcTransfer':
                            
                if(!$session->issetWalletSession()){
                    break;
                }
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $walletInfo = $finances->getWalletInfoByAddress($_SESSION['WALLET_ADDR']);
                $btcValue = $finances->bitcoin_getValue();
                
                if(isset($_POST['amount'])){
                    
                    $amountToTransfer = round($_POST['amount'], 7);

                    if(!is_numeric($amountToTransfer)){ //esse realmente é is_numeric!
                        $session->addMsg('Invalid amount.', 'error');
                        break;
                    }

                    if(!isset($_POST['destination'])){
                        $session->addMsg('Missing destination address.', 'error');
                        break;
                    }
                    
                    $destination = $_POST['destination'];

                    if(strlen($destination) != 34){
                        $session->addMsg('Invalid destination address.', 'error');
                        break;
                    }
                    
                    if(!$finances->issetWallet($destination)){
                        $session->addMsg('This address is invalid.', 'error');
                        break;
                    }
                    
                    if($destination == $_SESSION['WALLET_ADDR']){
                        $session->addMsg('LOL, you cant transfer to yourself.', 'error');
                        break;
                    }

                    if($amountToTransfer > $walletInfo->amount){
                        $session->addMsg('Yo transfering more bitcoins than you have.', 'error');
                        break;
                    }

                    if($amountToTransfer < 0){
                        exit();
                    }
                    
                    $finances->bitcoin_transfer($walletInfo->address, $amountToTransfer, $destination);
                    
                    $session->addMsg(sprintf(_('%s BTC transfered from %s to %s.'), $amountToTransfer, $walletInfo->address, $destination), 'notice');
                    
                    require '/var/www/classes/PC.class.php';
                    $log = new LogVPC();
                    
                    $log->addLog($finances->bitcoin_getID(), $amountToTransfer.' BTC transfered from '.$walletInfo->address.' to '.$destination, 'NPC');
                    
                } else {

                    // 2019: Function to format bitcoin number to 7-digit precision
                    //função pra formatar o número de bitcoins pra precisão 7
                    $rightZeroFill = '';
                    $explode = explode('.', $walletInfo->amount);
                    if(!is_int($walletInfo->amount)){
                        $afterComma = $explode[1];                        
                    } else {
                        $afterComma = 0;
                    }
                    if(strlen($afterComma) == 0){
                        $rightZeroFill = '0000000';
                    } elseif(strlen($afterComma) < 6){
                        for($i = 0; $i < 6 - (strlen($afterComma) - 1); $i++){
                            $rightZeroFill .= '0';
                        }
                    }
                    
                    $title = 'Transfer bitcoins';
                    $text = '<div class=\"control-group\"><div class=\"controls\">From <span class=\"item\">'.$_SESSION['WALLET_ADDR'].'</span></div><br/><div class=\"controls\"><input id=\"btc-amount\" class=\"name\" type=\"text\" name=\"btc-amount\" placeholder=\"BTC Amount\" style=\"width: 80%;\" value=\"'.$walletInfo->amount.$rightZeroFill.'\"/></div></div><div class=\"control-group\"><div class=\"controls\"><input id=\"btc-to\" class=\"name\" type=\"text\" name=\"btc-to\" placeholder=\"Destination address\" style=\"width: 80%;\"/></div></div>';

                    $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","value":"'.$btcValue.'"}]';

                }
                
                break;
            case 'btcBuy':
                
                if(!$session->issetWalletSession()){
                    break;
                }
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $walletInfo = $finances->getWalletInfoByAddress($_SESSION['WALLET_ADDR']);
                $btcValue = $finances->bitcoin_getValue();

                if(isset($_POST['amount'])){
                    
                    $amountToBuy = round($_POST['amount'], 7);

                    if(!is_numeric($amountToBuy)){ //esse realmente é is_numeric!
                        $session->addMsg('Invalid amount', 'error');
                        break;
                    }

                    if($amountToBuy < 0){
                        $session->addMsg('Invalid amount', 'error');
                        break;
                    }

                    if(!isset($_POST['acc'])){
                        $session->addMsg('Missing bank account.', 'error');
                        break;
                    }
                    
                    $acc = $_POST['acc'];

                    if($acc == '' || !ctype_digit($acc)){
                        $session->addMsg('Invalid bank account.', 'error');
                        break;
                    }

                    $accInfo = $finances->bankAccountInfo($acc);

                    if($accInfo['0']['exists'] == '0'){
                        $session->addMsg('This bank account does not exists.', 'error');
                        break;
                    }

                    $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                    if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                        $session->addMsg('This bank account is not valid.', 'error');
                        break;
                    }
                    
                    if($finances->totalMoney() < $amountToBuy * $btcValue){
                        $session->addMsg('Dude! You dont have money for this :/', 'error');
                        break;
                    }
                
                    $finances->bitcoin_buy($walletInfo->address, $amountToBuy, $btcValue, $acc);
                    
                    $session->addMsg(sprintf(_('%s BTC bought for $%s.'), $amountToBuy, number_format($amountToBuy * $btcValue)), 'notice');
                    
                    require '/var/www/classes/PC.class.php';
                    $log = new LogVPC();
                    
                    $log->addLog($finances->bitcoin_getID(), $walletInfo->address.' bought '.$amountToBuy.' BTC for $'.number_format(ceil($amountToBuy * $btcValue)), 'NPC');
                    
                } else {
                
                    $textAcc = '<br/><div id=\"loading\" class=\"pull-left\" style=\"margin-left: 9%;\"><img src=\"images/ajax-money.gif\">Loading...</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\" class=\"pull-left\" style=\"margin-left: 9%;\"></span>';

                    $title = 'Buy bitcoins';
                    $text = '<div class=\"control-group\"><div class=\"controls\"><input id=\"btc-amount\" class=\"name\" type=\"text\" name=\"btc-amount\" placeholder=\"BTC Amount\" style=\"width: 80%;\" value=\"1.0\"/></div><div class=\"controls\"><span class=\"pull-left\" style=\"margin-left: 9%;\"><span class=\"item\">Rate: </span>1 BTC = $'.$btcValue.'</span></div><br/><div class=\"controls\"><span class=\"pull-left\" style=\"margin-left: 9%;\"><span class=\"item\">Value: </span><span class=\"green\">$<span id=\"btc-total\"></span></span></span></div></div>';
                    $text .= $textAcc;

                    $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","value":"'.$btcValue.'"}]';

                }
                
                break;
            case 'btcSell':
                
                if(!$session->issetWalletSession()){
                    break;
                }
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $walletInfo = $finances->getWalletInfoByAddress($_SESSION['WALLET_ADDR']);
                $btcValue = $finances->bitcoin_getValue();
                $walletInfo->amount = $walletInfo->amount;
                
                if(isset($_POST['amount'])){
                    
                    $amountToSell = round($_POST['amount'], 7);

                    if(!is_numeric($amountToSell)){ //esse realmente é is_numeric!
                        $session->addMsg('Invalid amount.', 'error');
                        break;
                    }
                    
                    if($amountToSell < 1){
                        exit();
                    }

                    if($amountToSell > $walletInfo->amount || $_POST['amount'] > $walletInfo->amount){
                        $session->addMsg('You cant sell more than you have :/', 'error');
                        break;
                    }

                    if(!isset($_POST['acc'])){
                        $session->addMsg('Missing bank account.', 'error');
                        break;
                    }
                    
                    $acc = $_POST['acc'];

                    if($acc == '' || !ctype_digit($acc)){
                        $session->addMsg('Invalid bank account.', 'error');
                        break;
                    }

                    $accInfo = $finances->bankAccountInfo($acc);

                    if($accInfo['0']['exists'] == '0'){
                        $session->addMsg('This bank account does not exists.', 'error');
                        break;
                    }

                    $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                    if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                        $session->addMsg('This bank account is not valid.', 'error');
                        break;
                    }
                
                    $finances->bitcoin_sell($walletInfo->address, $amountToSell, $btcValue, $acc);
                    
                    $session->addMsg(sprintf(_('$%s transfered to account #%s'), number_format(ceil($amountToSell * $btcValue)), $acc), 'notice');
                    
                    require '/var/www/classes/PC.class.php';
                    $log = new LogVPC();
                    
                    $log->addLog($finances->bitcoin_getID(), $walletInfo->address.' sold '.$amountToSell.' BTC for $'.number_format(ceil($amountToSell * $btcValue)), 'NPC');
                    
                } else {
                    
                    if($walletInfo->amount >= 1){
                        $textSell = '<br/><div id=\"loading\" class=\"pull-left\" style=\"margin-left: 9%;\"><img src=\"images/ajax-money.gif\">Loading...</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\" class=\"pull-left\" style=\"margin-left: 9%;\"></span>';
                    } else {
                        $textSell = '<br/><span class=\"pull-left red\" style=\"margin-left: 9%;\">You need at least 1 BTC in order to sell.</span>';
                    }
                    
                    //função pra formatar o número de bitcoins pra precisão 7
                    $rightZeroFill = '';
                    $explode = explode('.', $walletInfo->amount);
                    if(!is_int($walletInfo->amount)){
                        $afterComma = $explode[1];                        
                    } else {
                        $afterComma = 0;
                    }
                    if(strlen($afterComma) == 0){
                        $rightZeroFill = '0000000';
                    } elseif(strlen($afterComma) < 6){
                        for($i = 0; $i < 6 - (strlen($afterComma) - 1); $i++){
                            $rightZeroFill .= '0';
                        }
                    }
                    
                    $title = 'Sell bitcoins';
                    $text = '<div class=\"control-group\"><div class=\"controls\"><input id=\"btc-amount\" class=\"name\" type=\"text\" name=\"btc-amount\" placeholder=\"BTC Amount\" style=\"width: 80%;\" value=\"'.$walletInfo->amount.$rightZeroFill.'\"/></div><div class=\"controls\"><span class=\"pull-left\" style=\"margin-left: 9%;\"><span class=\"item\">Rate: </span>1 BTC = $'.$btcValue.'</span></div><br/><div class=\"controls\"><span class=\"pull-left\" style=\"margin-left: 9%;\"><span class=\"item\">Value: </span><span class=\"green\">$<span id=\"btc-total\"></span></span></span></div></div>';
                    $text .= $textSell;

                    $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","value":"'.$btcValue.'","amount":"'.$walletInfo->amount.'"}]';
                }
                
                break;
            case 'btcLogout':
                
                if($session->issetWalletSession()){
                    $addr = $_SESSION['WALLET_ADDR'];
                    $session->deleteWalletSession();
                    $session->addMsg(sprintf(_('Logged out from address <strong>%s</strong>.'), $addr), 'notice');
                }
                
                break;
            case 'btcLogin':
            
                $result['status'] = 'ERROR';
                
                if(!isset($_POST['addr']) || !isset($_POST['key'])){
                    $session->addMsg('Missing information.', 'error');
                    break;
                }
                
                $addr = $_POST['addr'];
                $key = $_POST['key'];
                
                if(strlen($addr) != 34){
                    $session->addMsg('Invalid address.', 'error');
                    break;
                }
                
                if(strlen($key) != 64){
                    $session->addMsg('Invalid key.', 'error');
                    break;
                }
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                if(!$finances->issetWallet($addr)){
                    $session->addMsg('This address does not exists.', 'error');
                    break;
                }
                //untaint($key);
                if($key != $finances->getWalletKey($addr)){
                    $session->addMsg('This key is invalid.', 'error');
                    break;
                }
                                
                require '/var/www/classes/NPC.class.php';
                require '/var/www/classes/PC.class.php';
                
                $npc = new NPC();
                $btcInfo = $npc->getNPCByKey('BITCOIN');
                
                $btcIP = $btcInfo->npcip;
                
                $player = new Player();
                $playerInfo = $player->getPlayerInfo($_SESSION['id']);
                
                $log = new LogVPC();
                
                $session->addMsg(sprintf(_('You logged in to the address <strong>%s</strong>.'), $addr), 'notice');
                $session->createWalletSession($addr);
                
                $paramInfoHacked = Array(1, long2ip($playerInfo->gameip), $addr);
                $paramInfoHacker = Array(2, long2ip($btcIP), $addr, $key);

                $log->addLog($btcInfo->id, $log->logText('BTCLOGIN', $paramInfoHacked), 1);
                $log->addLog($_SESSION['id'], $log->logText('BTCLOGIN', $paramInfoHacker), '0');
                
                break;
            case 'btcRegister':
            
                $result['status'] = 'OK';
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $btcID = $finances->bitcoin_getID();
                
                if($finances->userHaveWallet($_SESSION['id'], $btcID)){
                    $session->addMsg('You already have an account :S', 'error');
                    break;
                }
                
                $finances->bitcoin_createAcc($btcID);
                                    
                $session->addMsg('Your wallet was created! You can find it\'s information on the Finances page.', 'success');
                
                break;
            default:
                $result['status'] = 'ERROR';
                break;

        }

    } else {
        $result['status'] = 'ERROR';
    }
    
}

header('Content-type: application/json');
die(json_encode($result));