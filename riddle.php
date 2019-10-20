<?php

session_start();

require '/var/www/classes/Session.class.php';
$session = new Session();

$result = Array();
$result['status'] = 'ERROR';
$result['redirect'] = '';
$result['msg'] = 'STOP SPYING ON ME!';

require '/var/www/classes/Riddle.class.php';
$riddle = new Riddle();

if(!$session->issetInternetSession()){
    exit();
}

if($session->issetLogin() && $session->issetInternetSession()){

    if(!$riddle->validRiddleIP($_SESSION['LOGGED_IN'])){
        exit("Not a riddle IP");
    }
    
    if($riddle->alreadySolvedRiddle($_SESSION['LOGGED_IN'])){
        exit("Already solved");
    }
    
    $result['status'] = 'OK';

    require 'template/gameHeader.php';
    
    if(isset($_POST['func'])){

        $func = $_POST['func'];

        switch($func){

            case 'tictactoe':
                
                if(!isset($_POST['status'])){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $status = $_POST['status'];
                
                if(!ctype_digit($status)){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                if($status > 3 || $status < 0){
                    $result['status'] = 'ERROR';
                    break;
                }
                                
                $nextIP = 'XXX.XXX.XXX.XXX';
                $header = '';
                $isSolved = 'false';
                
                if($status == 0){ //tie
                    $resultText = '<span class=\"puzzle-tie\">'._('Tie ').'</span>';
                } elseif($status == 1){ //player won
                    $resultText = $riddle->text_victory();
                    $nextIP = long2ip($riddle->getNextIP());
                    $header = $riddle->text_header($nextIP);
                    $isSolved = 'true';
                    $riddle->setAsSolved();
                } else { //player lost
                    $resultText = '<span class=\"puzzle-lost\">'._('You lose').'</span>';
                }
                
                $result['msg'] = '[{"result":"'.$resultText.'","next":"'.$nextIP.'","header":"'.$header.'","isSolved":"'.$isSolved.'"}]';
                
                break;
            case 'sudoku':
            case 'minesweeper':
            case 'lightsout':

                $resultText = $riddle->text_victory();
                $nextIP = long2ip($riddle->getNextIP());
                $header = $riddle->text_header($nextIP);
                $riddle->setAsSolved();

                $result['msg'] = '[{"result":"'.$resultText.'","next":"'.$nextIP.'","header":"'.$header.'","isSolved":"true"}]';
                
                break;
            case '2048':
                
                if(!isset($_POST['type'])){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $type = $_POST['type'];
                if(!ctype_digit($type)){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                if($type <= 0 || $type >= 6){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                switch($type){
                    case 1: //128 (displays 1)
                    case 2: //256 (displays 1+1)
                    case 3: //512 (displays 2)
                    case 4: //1024 (displays 3)

                        $hintValue = $type;
                        if($type > 2){
                            $hintValue += 2;
                        }
                        if($hintValue > 5){
                            $hintValue = 5;
                        }
                        
                        $nextIP = $riddle->hint_generate($hintValue, $riddle->hint_getCookie());
                        $riddle->hint_setCookie($nextIP);

                        $header = '';
                        $resultText = $riddle->text_hint();
                        $isSolved = $riddle->solvedByHint($nextIP);
                        if($isSolved == 'true'){
                            $header = $riddle->text_header($nextIP);
                            $riddle->setAsSolved();
                            $resultText = $riddle->text_victory();
                        }
                        
                        break;
                    case 5: //2048, end game, cookie-independent
                        
                        // 2019: I have no idea what I meant by the comment below, so I can't translate it.
                        //ERA TODO ENTÃO ALTEREI PELO DE CIMA
                        //FAZER AQUI (OU VER POR QUE ERA TODO)
                        //(SOMENTE O CASE 5)

                        $resultText = $riddle->text_victory();
                        $nextIP = long2ip($riddle->getNextIP());
                        $header = $riddle->text_header($nextIP);
                        $riddle->setAsSolved();
                        
                        
                        break;
                }
                
                $result['msg'] = '[{"result":"'.$resultText.'","next":"'.$nextIP.'","header":"'.$header.'","isSolved":"'.$isSolved.'"}]';
                
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