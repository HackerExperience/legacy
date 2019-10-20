<?php

session_start();

require '/var/www/classes/Session.class.php';
$session = new Session();

$result = Array();
$result['status'] = 'ERROR';
$result['redirect'] = '';
$result['msg'] = 'STOP SPYING ON ME!';

$loggedOut = FALSE;

if(isset($_POST['func'])){
    switch($_POST['func']){
        case 'check-user':
        case 'check-mail':
            $loggedOut = TRUE;
            break;
    }
}

if($session->issetLogin() || $loggedOut){

    $result['status'] = 'OK';
    
    if(!$loggedOut){
    
        // 2019: Sorry, no idea what I had in mind below
        //TESTE!!!! RETIRAR \/ (COMENTÁRIO OU A LINHA)
        //require 'template/gameHeader.php'; //TODO: is it really needed? (perhaps for set_time, but..)
    
    }

    if(isset($_POST['func'])){

        $func = $_POST['func'];
        
        switch($func){
            
            case 'gettext':
                
                if(!isset($_POST['id'])){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $id = $_POST['id'];
                
                //leave this empty space (' ' instead of ''), otherwise gettext will freak out.
                $title = ' ';
                $text = ' ';
                $btn = ' ';
                $img = ' ';
                $extra = ' ';
                
                switch($id){
                    
                    case 'tutorial_prepare':
                        $title = 'Prepare your tools.';
                        $text = sprintf(_('Before hacking the victim, you need to run your cracker. Go to the %ssoftware page%s.'), '<a class=\"notify-link\" href=\"software\">', '</a>');
                        break;
                    case 'tutorial_install_cracker':
                        $title = 'Install your cracker';
                        $text = sprintf(_('%sInstall%s the highlighted software, so you can hack the victim.'), '<a class=\"notify-link\" href=\"software?action=install&id='.$_POST['info'].'\">', '</a>');
                        break;
                    case 'tutorial_goto_vic_80':
                        $title = 'Navigate to the victim';
                        $text = sprintf(_('Good job on installing your cracker. Now, go to the %sinternet page%s and navigate to the victim IP.'), '<a href=\"internet\" class=\"notify-link\">', '</a>');
                        break;
                    case 'tutorial_goto_vic_83':
                        $title = 'Navigate to the victim';
                        $text = sprintf(_('Go to the %sinternet page%s and navigate to the victim IP address.'), '<a href=\"internet\" class=\"notify-link\">', '</a>');
                        break;
                    case 'tutorial_goto_vic':
                        $title = 'Navigate to the victim';
                        $text = sprintf(_('Now that you are here, enter the victim IP address: %s%s%s'), '<a class=\"notify-link\" href=\"internet?ip='.$_POST['info'].'\">', $_POST['info'], '</a>');
                        break;
                    case 'tutorial_hacktab':
                        $title = 'Good!';
                        $text = sprintf(_('Now click on the %shack tab%s to display the hacking options.'), '<a class=\"notify-link\" href=\"internet?action=hack\">', '</a>');
                        break;
                    case 'tutorial_hack':
                        $title = 'Hack him!';
                        $text = sprintf(_('Click on %sBruteforce Attack%s to use your cracker.'), '<a class=\"notify-link\" href=\"internet?action=hack&method=bf\">', '</a>');
                        break;
                    case 'tutorial_login1':
                        $title = 'Abracadabra!';
                        $text = 'Congratulations, you just hacked your first IP. Now do the login, don\'t forget to quickly delete your logs.';
                    case 'tutorial_login2':
                        $title = 'Nice!';
                        $text = 'You have his password. Again, don\'t forget to quickly hide your logs.';
                        break;
                    case 'tutorial_deletelog':
                        $title = 'Delete your logs!';
                        $text = 'Hide your IP. Quick!';
                        break;
                    case 'tutorial_80':
                        $title = 'What?';
                        $text = sprintf(_('Someone must have deleted the file Users.dat. Check the %slog page%s!'), '<a class=\"notify-link\" href=\"internet?view=logs\">', '</a>');
                        break;
                    case 'tutorial_81':
                        $title = 'Someone deleted it!';
                        $text = sprintf(_('Look! That guy deleted our file. Fine, I have an idea. Check your %smission page%s again, I\'ve updated it.'), '<a class=\"notify-link\" href=\"missions\">', '</a>');
                        break;
                    case 'tutorial_logout':
                        $title = 'Logout from this one.';
                        $text = 'We are done here. Click on Logout.';
                        break;
                    case 'tutorial_upload1':
                        $title = 'Upload him a gift.';
                        $text = 'Give that man a gift. Upload the virus using the menu on the right.';
                        break;
                    case 'tutorial_upload2':
                        $title = 'Almost there.';
                        $text = sprintf(_('Now %sinstall%s the virus.'), '<a class=\"notify-link\" href=\"internet?view=software&cmd=install&id='.$_POST['info'].'\">', '</a>');
                        break;
                    case 'tutorial_end':
                        $title = 'Hurray!';
                        $text = sprintf(_('Nice job, son. I am proud of you. I hope this SOB learned his lesson. Go to the %smission page%s to retrieve your reward.'), '<a href=\"missions\" class=\"notify-link\">', '</a>');
                        break;
                    case 'tutorial_collect':
                        $title = 'Collect your money.';
                        $text = 'Congratulations on completing the mission. Now, collect your hard-earned money clicking on \'Complete mission\'.';
                        break;
                    case 'abort':
                        $title = 'Abort Mission';
                        $text = sprintf(_('Are you sure you want to abort this misson?<br/><br/>You will lose %s of your reputation and pay a penalty of %s.'), '<span class=\"red\">10%</span>', '<span class=\"red\">$500 + '._('reward').'</span>');
                        $btn = '<input type=\"submit\" class=\"btn btn-danger\" value=\"'._('Abort').'\">';
                        break;
                    case 'accept_m':
                        $title = 'Accept Mission';
                        $text = 'Are you sure you want to accept this mission?';
                        $btn = '<input type=\"submit\" class=\"btn btn-success\" value=\"'._('Accept').'\">';
                        break;
                    case 'm_completed_notify':
                        $title = 'Mission completed';
                        $text = sprintf(_('Congratulations, you completed the mission. Go to the %smission page%s to retrieve your reward.'), '<a class=\"notify-link\" href=\"missions\">', '</a>');
                        break;
                    case 'm_completed_inform':
                        $title = 'Complete mission';
                        $text = sprintf(_('You completed the mission. Inform the account you want to add your %s. %s'), '<font color=\"red\"><strong>$'.$_POST['info'].'</strong></font>', '<br/><br/><div id=\"loading\"><img src=\"images/ajax-money.gif\">'._('Loading...').'</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\"></span>');
                        $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-success\" value=\"'._('Complete Mission').'\" DISABLED>';
                        break;
                    case 'certdiv':
                        
                        $completed = _('You have already completed this certification.');
                        $completed_label = _('Completed ');
                        $completed_all = sprintf(_('%sCongratulations!%s You have completed all certifications.%s'), '<div class=\"alert alert-success\"><strong>', '</strong>', '</div> ');
                        
                        $take = _('Take certification');
                        $learning_label = _('Learning');
                        
                        $certify_free = _('Get certified (free)');
                        $ceritify_paid = _('Get certified for $');
                        
                        $locked = sprintf(_('%sThis certification is locked.'), '<span title=\"'._('This means you need to take another certification before doing this one.').'\">', '</span>');
                        
                        $c1 = 'Basic Tutorial';
                        $d1 = 'Game basics.';
                        $c2 = 'Hacking 101';
                        $d2 = 'Learn the basics of hacking.';
                        $c3 = 'Intermediate Hacking';
                        $d3 = 'Improve your hacking techniques.';
                        $c4 = 'Advanced Hacking';
                        $d4 = 'Meet the wonders of DDoS world.';
                        $c5 = 'DDoS Security';
                        $d5 = 'Learn to protect yourself from DDoS.';
                        
                        $extra = ',"c":"'.$completed.'","c_label":"'.$completed_label.'","c_all":"'.$completed_all.'","take":"'.$take.'","learning":"'.$learning_label.'","cert_free":"'.$certify_free.'","cert_paid":"'.$ceritify_paid.'","locked":"'.$locked.'"';
                        $extra .= ',"c1":"'._($c1).'","c2":"'._($c2).'","c3":"'._($c3).'","c4":"'._($c4).'","c5":"'._($c5).'"';
                        $extra .= ',"d1":"'._($d1).'","d2":"'._($d2).'","d3":"'._($d3).'","d4":"'._($d4).'","d5":"'._($d5).'"';
                        
                        break;
                    case 'certbuy':
                        
                        $key = $_POST['info'];
                        if(!ctype_digit($key) || $key <= 0 || $key > 5){
                            $result['status'] = 'ERROR';
                            break;
                        }
                        
                        switch($key){
                            case 1:
                                $name = 'Basic Tutorial';
                                $p = 0;
                                break;
                            case 2:
                                $name = 'Hacking 101';
                                $p = 0;
                                break;
                            case 3:
                                $name = 'Intermediate Hacking';
                                $p = 50;
                                break;
                            case 4:
                                $name = 'Advanced Hacking';
                                $p = 200;
                                break;
                            case 5:
                                $name = 'DDoS Security';
                                $p = 500;
                                break;
                        }
                        
                        if($p > 0){
                            $disabled = 'DISABLED';
                        } else {
                            $disabled = '';
                        }

                        require '/var/www/classes/Finances.class.php';
                        $finances = new Finances();

                        $totalMoney = $finances->totalMoney();
                        
                        $title = _('Buy certification');
                        $text = sprintf(_('Are you sure you want to buy the certification %s for %s?'), '<strong>'._($name).'</strong>', '<span class=\"red\">$'.$p.'</span>');
                        $text .= '<br/><div id=\"loading\"><img src=\"images/ajax-money.gif\">'._('Loading...').'</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\"></span>';
                        $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Buy').'\" '.$disabled.'>';
                        $extra = ',"n":"'.$name.'","p":"'.$p.'"';
                        
                        if($totalMoney < $p && $p > 0){
                            $text = 'You do not have enough money to buy this certification.';
                            $btn = '<input type=\"submit\" data-dismiss=\"modal\" class=\"btn btn-info\" value=\"Okay\">';
                        }
                        
                        break;
                    case 'loadsoft':

                        $loading = '<div id=\"loading\"><img src=\"images/ajax-software.gif\"> '._('Loading...').'</div>';
                        $ph = _('Choose a software...');
                        
                        $extra = ',"loading":"'.$loading.'","placeholder":"'.$ph.'"';
                        
                        break;
                    case 'adb':

                        $title = sprintf(_('Hey, looks like you are using AdBlock. <br/>We hate ads too, and we totally support your right to not be tracked! However, ads keep this game free.<br/> Please, consider disabling AdBlock for this site or purchasing a %spremium account</a> to get rid of them.<br/>'), '<a href=\"premium\">');
                        
                        break;
                        
                }
                
                if($title == '' && $text == '' && $extra == ''){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $result['msg'] = '[{"title":"'._($title).'","text":"'._($text).'","btn":"'._($btn).'"'.$extra.'}]';                
                
                break;
            case 'credits':

                // 2019: Feel free to add your name(s) below, as well as remove
                // 2019: any if they are no longer used (e.g. if you completely 
                // 2019 removed one of the JS libraries). Otherwise, please keep
                // 2019: the corresponding credits.
                
                $title = _('Credits');
                $text = '';
                
                $text .= '<p><span class=\"credit-category\">'._('Developer').'</span><br/><span class=\"credit-sole\"><a href=\"http://fb.com/RenatoMassaro\">Renato Massaro</a></span></p>';
                $text .= '<p><span class=\"credit-category\">'._('Game Designer').'</span><br/><span class=\"credit-sole\"><a href=\"http://fb.com/RenatoMassaro\">Renato Massaro</a></span></p>';
                $text .= '<p><span class=\"credit-category\">'._('System Administrator').'</span><br/><span class=\"credit-sole\"><a href=\"http://fb.com/RenatoMassaro\">Renato Massaro</a></span></p>';
                $text .= '<p><span class=\"credit-category\">'._('Design').'</span><br/>';
                $text .= '<span class=\"credit-text\">Framework</span><span class=\"credit-right\"><a href=\"http://getbootstrap.com/\">Twitter Bootstrap</a></span><br/>';
                $text .= '<span class=\"credit-text\">Template</span><span class=\"credit-right\"><a href=\"http://bootstrap-hunter.com/\">diablo9983</a></span><br/>';
                $text .= '<span class=\"credit-text\">Icon Pack</span><span class=\"credit-right\"><a href=\"http://www.famfamfam.com/lab/icons/\">Fam Fam Fam</a></span><br/>';
                $text .= '<span class=\"credit-text\">&nbsp;</span><span class=\"credit-right\"><a href=\"http://www.fatcow.com/free-icons\">Fat Cow</a></span><br/>';
                $text .= '<span class=\"credit-text\">Landing page</span><span class=\"credit-right\"><a href=\"fb.com/RenatoMassaro\">Renato Massaro</a></span><br/>';
                $text .= '<span class=\"credit-text\">Blinking cursor</span><span class=\"credit-right\">Victor Scattone</span><br/></p>';
                $text .= '<p><span class=\"credit-category\">'._('Javascript Libraries').'</span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://jquery.com/\">jQuery</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://ivaynberg.github.io/select2/\">Select2</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://rendro.github.io/easy-pie-chart/\">Easy pie chart</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://drewwilson.com/\">TipTip</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"https://github.com/plentz/jquery-maskmoney\">jQuery-maskmoney</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"https://github.com/peachananr/onepage-scroll\">One page scroll</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"https://github.com/mattboldt/typed.js/\">typed.js</a></span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://www.sceditor.com/\">SCEditor</a></span><br/></p>';
                $text .= '<p><span class=\"credit-category\">'._('Special thanks').'</span><br/>';
                $text .= '<span class=\"credit-sole\">Eduardo Estevão</span><br/>';
                $text .= '<span class=\"credit-sole\">Gabriel Oliveira</span><br/>';
                $text .= '<span class=\"credit-sole\">Victor Scattone</span><br/>';
                $text .= '<span class=\"credit-sole\"><a href=\"http://stackoverflow.com/\">Stack Overflow ♥</a></span><br/></p>';
                
                $text .= '<br/><span class=\"small\">'._('You should be here?').' <a class=\"black\" href=\"mailto:'._('contact@hackerexperience.com').'\">'._('Contact us').'</a>!</span>';
                
                $text .= '';
                $btn = '<a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Ok').'</a>';
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                
                
                break;
            case 'getBRLvalue':
                
                require '/var/www/classes/Premium.class.php';
                $premium = new Premium();
                
                $result['msg'] = $premium->exchange_rate('USD', 'BRL', 1, 2);
                
                break;
            case 'deleteDdos':
            
                $title = 'Delete DDoS reports';
                $text = 'Are you sure you want to delete all DDoS reports? This action can\'t be undone.';
                $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"Yes\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                
                break;
            case 'bankChangePass':            
                
                $title = 'Change account password';
                $text = 'Are you sure you want to change your account password? It is <strong>free</strong>.';
                $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"Change\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                
                break;
            case 'bankCloseAcc':            
                
                if(!$session->issetBankSession()){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                require '/var/www/classes/Finances.class.php';
                
                $finances = new Finances();
                
                $accInfo = $finances->getBankAccountInfo($_SESSION['id'], $_SESSION['BANK_ID']);
                
                if($accInfo['VALID_ACC'] == '0'){
                    $result['status'] = 'ERROR';
                    break;
                } elseif($accInfo['USER'] != $_SESSION['id']){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                if($accInfo['CASH'] > 0){
                    $text = 'You can not close an account with money.';
                    $btn = '<input  data-dismiss=\"modal\" type=\"submit\" class=\"btn btn-info\" value=\"Okay\">';
                } else {
                    $text = 'Are you sure you want to close your account? It can\'t be undone.';
                    $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-danger\" value=\"Close\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                }

                $title = 'Close account';
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                
                break;
            case 'buyLicense':
                
                $result['status'] = 'ERROR';
                
                if(!isset($_POST['id'])){
                    break;
                }
                
                $softID = $_POST['id'];
                
                if(!ctype_digit($softID)){
                    break;
                }
                
                require '/var/www/classes/Player.class.php';
                require '/var/www/classes/PC.class.php';
                require '/var/www/classes/Finances.class.php';
                
                $software = new SoftwareVPC();
                $finances = new Finances();
                
                $external = FALSE;
                if(!$software->issetSoftware($softID, $_SESSION['id'], 'VPC')){
                    if(!$software->issetExternalSoftware($softID)){
                        break;
                    }
                    $external = TRUE;
                }
                
                if($external){
                    $softInfo = $software->getExternalSoftware($softID);
                } else {
                    $softInfo = $software->getSoftware($softID);
                }

                if(!$software->isResearchable($softInfo->softtype)){
                    break;
                }
                
                $result['status'] = 'OK';
                
                $researchPrice = $software->license_studyPrice($softInfo->softtype, $softInfo->softversion, $softInfo->licensedto);

                if($researchPrice > $finances->totalMoney()){
                    $textMoney = '<br/><span class=\"red pull-left\" style=\"margin-left: 9%;\">'._('You do not have enough money to buy this license.').'</span>';
                    $canBuy = FALSE;
                    $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Buy').'\" DISABLED><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                } else {
                    $textMoney = '<br/><div id=\"loading\" class=\"pull-left\" style=\"margin-left: 9%;\"><img src=\"images/ajax-money.gif\">'._('Loading...').'</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\" class=\"pull-left\" style=\"margin-left: 9%;\"></span>';
                    $canBuy = TRUE;
                    $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Buy').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                }
                
                $licenseText = '<span class=\"pull-left\">'._('Buy license of the software ').'<strong>'.$softInfo->softname.$software->getExtension($softInfo->softtype).'</strong>';
                $licenseText .= _(' for ').'<span class=\"green\">$'.number_format($researchPrice).'</span>?</span>';
                
                $title = _('Buy license');
                $text = '<span class=\"pull-left\" style=\"margin-left: 9%;\">'. $licenseText .'</span>';
                $text .= $textMoney;

                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'","canBuy":"'.$canBuy.'"}]';
                
                break;

            // 2019: I don't remember if the reportBug feature works. If it does,
            // 2019: I'm pretty sure there's no interface to see them, other than
            // 2019: inspecting the database directly.
            case 'reportBug':

                $result['status'] = 'OK';
                
                if(isset($_POST['follow']) && isset($_POST['rlt'])){
                    $follow = $_POST['follow'];
                    $rlt = $_POST['rlt'];
                    
                    if($follow != 1){
                        $follow = 0;
                    }
                    
                    if(strlen(trim($rlt)) == 0){
                        $result['status'] = 'ERROR';
                        break;
                    }
                    
                    $sessionDB = '';
                    foreach($_SESSION as $key => $value){
                        if ($value instanceof DateTime) {
                          $value = $value->format('Y-m-d H:i:s');
                        }
                        $sessionDB .= '<strong>'.$key.'</strong> - '.$value.'<br/>';
                    }
                    
                    $server = '';
                    foreach($_SERVER as $key => $value){
                        $server .= '<strong>'.$key.'</strong> - '.$value.'<br/>';
                    }
                    
                    $pdo = PDO_DB::factory();
                    $session->newQuery();
                    $sql = 'INSERT INTO bugreports (bugText, reportedBy, sessionContent, serverContent, follow) 
                            VALUES (:text, :reporter, :session, :server, :follow)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':text' => $rlt, ':reporter' => $_SESSION['id'], ':session' => $sessionDB, ':server' => $server, ':follow' => $follow));
                    
                } else {

                    $title = '<center>'._('Report a bug').'</center>';
                    $text = '<center><div class=\"control-group\"><div class=\"controls\"><textarea id=\"bug-content\" class=\"bugtext\" name=\"bugcontent\" rows=\"5\" placeholder=\"'._('Please explain in details what happened. In which page were you? Which action were you doing? Is it possible to reproduce the bug? Thanks!').'\" style=\"width: 80%;\"></textarea><br/><span class=\"small pull-left link\" style=\"margin-left: 9%;\"><label name=\"contact\">'._('Contact me with bug information').': <input id=\"bug-follow\" style=\"margin-left: 5px;\" type=\"checkbox\" name=\"contact\" value=\"1\" CHECKED></label></span></div></div></center>';
                    $btn = '<input id=\"bug-submit\" type=\"submit\" class=\"btn btn-info\" value=\"'._('Report').'\">';
                    $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';

                }

                break;
            case 'getBadge':
                
                $result['status'] = 'ERROR';
                
                if(!array_key_exists('HTTP_REFERER', $_SERVER)){
                    break;
                }
                
                if(strpos($_SERVER['HTTP_REFERER'], 'clan')){
                    $clan = TRUE;
                } else {
                    $clan = FALSE;
                }
                                
                if(!isset($_POST['badgeID']) || !isset($_POST['userID'])){
                    break;
                }
                
                $badgeID = $_POST['badgeID'];
                $userID = $_POST['userID'];
                
                if(strlen($badgeID) == 0 || !ctype_digit($badgeID) || $badgeID < 1){
                    break;
                }
                
                if(strlen($userID) == 0 || !ctype_digit($userID) || $userID < 1){
                    break;
                }
                
                $pdo = PDO_DB::factory();

                if(!$clan){
                
                    $session->newQuery();
                    $sql = 'SELECT login FROM users WHERE id = :userID LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':userID' => $userID));
                    $user = $stmt->fetch(PDO::FETCH_OBJ)->login;

                    $session->newQuery();
                    $sql = 'SELECT round, dateAdd, COUNT(badgeID) AS total 
                            FROM users_badge 
                            WHERE badgeID = :badgeID AND userID = :userID 
                            GROUP BY badgeID';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':badgeID' => $badgeID, ':userID' => $userID));
                    $badgeInfo = $stmt->fetch(PDO::FETCH_OBJ);

                    $title = 'Badges of user '.$user;
                
                } else {
                    
                    $session->newQuery();
                    $sql = 'SELECT name FROM clan WHERE clanID = :clanID LIMIT 1';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':clanID' => $userID));
                    $user = $stmt->fetch(PDO::FETCH_OBJ)->name;

                    $session->newQuery();
                    $sql = 'SELECT round, dateAdd, COUNT(badgeID) AS total 
                            FROM clan_badge 
                            WHERE badgeID = :badgeID AND clanID = :clanID 
                            GROUP BY badgeID';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(':badgeID' => $badgeID, ':clanID' => $userID));
                    $badgeInfo = $stmt->fetch(PDO::FETCH_OBJ);

                    $title = 'Badges of clan '.$user;
                    
                }
                
                if(!empty($badgeInfo)){
                    
                    $result['status'] = 'OK';

                    $badgeJSON = json_decode(file_get_contents('json/badges.json'));
           
                    $badgeName = $badgeJSON->$badgeID->name;
                    $badgeDesc = $badgeJSON->$badgeID->desc;
                   
                    if($badgeDesc != ''){
                        $badgeDesc = ' - '.$badgeDesc;
                    }
                    
                    $totalAwarded = $awardedStr = '';
                    
                    if($badgeJSON->$badgeID->collectible){
                        if($badgeInfo->total == 1){
                            $totalAwarded = 'Awarded 1 time.';
                            $awardedStr = substr($badgeInfo->dateadd, 0, -9).' - Round #'.$badgeInfo->round;
                        } else {
                            $totalAwarded = 'Awarded '.$badgeInfo->total.' times.';
                            $awardedStr = '';

                            if($clan){
                                $session->newQuery();
                                $sql = "SELECT round, dateAdd
                                        FROM clan_badge
                                        WHERE badgeID = '".$badgeID."' AND clanID = '".$userID."'";
                                $data = $pdo->query($sql);
                            } else {
                                $session->newQuery();
                                $sql = "SELECT round, dateAdd
                                        FROM users_badge
                                        WHERE badgeID = '".$badgeID."' AND userID = '".$userID."'";
                                $data = $pdo->query($sql);
                            }

                            while($dateInfo = $data->fetch(PDO::FETCH_OBJ)){

                                $awardedStr .= substr($dateInfo->dateadd, 0, -9).' - Round #'.$dateInfo->round;
                                $awardedStr .= '<br/>';

                            }

                            $awardedStr = substr($awardedStr, 0, -5);

                        }
                        
                        $totalAwarded .= '<br/>';
                        $awardedStr .= '<br/><br/>';
                        
                    }
                    
                    if($clan){

                        $session->newQuery();
                        $sql = "SELECT COUNT(*)
                                FROM clan_badge
                                WHERE badgeID = '".$badgeID."'
                                GROUP BY clanID
                                HAVING clanID <> '".$userID."'";
                        $totalPlayers = sizeof($pdo->query($sql)->fetchAll());

                        if($totalPlayers == 0){
                            $playersStr = 'Only <a href=\"clan?id='.$userID.'\">'.$user.'</a> have this badge.';
                        } elseif($totalPlayers == 1) {
                            $playersStr = '<a href=\"clan?id='.$userID.'\">'.$user.'</a> and <strong>1</strong> other clan have this badge.';
                        } else {
                            $playersStr = '<a href=\"clan?id='.$userID.'\">'.$user.'</a> and <strong>'.$totalPlayers .'</strong> others clans have this badge.';
                        }
                        
                    } else {
                        
                        $session->newQuery();
                        $sql = "SELECT COUNT(*)
                                FROM users_badge
                                WHERE badgeID = '".$badgeID."'
                                GROUP BY userID
                                HAVING userID <> '".$userID."'";
                        $totalPlayers = sizeof($pdo->query($sql)->fetchAll());

                        if($totalPlayers == 0){
                            $playersStr = 'Only <a href=\"profile?id='.$userID.'\">'.$user.'</a> have this badge.';
                        } elseif($totalPlayers == 1) {
                            $playersStr = '<a href=\"profile?id='.$userID.'\">'.$user.'</a> and <strong>1</strong> other player have this badge.';
                        } else {
                            $playersStr = '<a href=\"profile?id='.$userID.'\">'.$user.'</a> and <strong>'.$totalPlayers .'</strong> others players have this badge.';
                        }
                        
                    }
                    
                    $text = '<strong>'.$badgeName.'</strong>'.$badgeDesc.'<br/><br/>'.$totalAwarded.'<span class=\"small nomargin\">'.$awardedStr.'</span>'.$playersStr;
                    
                }
                
                if($result['status'] == 'ERROR'){
                    $text = 'Ops! This badge does not exists. :/';
                }
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'"}]';

                break;
            
            case 'check-user':
            case 'check-mail':
                
                require '/var/www/classes/System.class.php';
                
                $pdo = PDO_DB::factory();
                $system = new System();
                
                $result = false;
                
                if($func == 'check-user'){
                    
                    if(!isset($_POST['username'])){
                        break;
                    }

                    $user = $_POST['username'];

                    if(ctype_digit($user)){
                        break;
                    }

                    if(strlen($user) > 15 || strlen($user) < 1){
                        break;
                    }
                    
                    if(!$system->validate($user, 'username')){
                        break;
                    }
                    
                    $cond = 'login';
                    $var = $user;
                
                } elseif($func == 'check-mail'){
                    
                    if(!isset($_POST['email'])){
                        break;
                    }

                    $mail = $_POST['email'];

                    if(strlen($mail) < 1){
                        break;
                    }
                    
                    if(!$system->validate($mail, 'email')){
                        break;
                    }
                    
                    $cond = 'email';
                    $var = $mail;
                    
                }
                
                $sql = 'SELECT id FROM users WHERE '.$cond.' = ? LIMIT 1';
                                
                $sqlReg = $pdo->prepare($sql);
                $sqlReg->execute(array($var));
                
                if($sqlReg->rowCount() == 1){
                    $result = FALSE;
                } else {
                    $result = TRUE;
                }
                
                break;
            
            case 'getResearchStats':
                
                $pdo = PDO_DB::factory();
                
                if(!isset($_POST['round'])){
                    $result['status'] = 'ERROR';
                    break;
                }

                $round = $_POST['round'];
                
                if($round == 'all'){
                    $select = 'SUM(researchCount) AS researchcount, SUM(moneyResearch) AS moneyresearch';
                    $where = '';
                } else { 
                    $select = 'researchCount, moneyResearch';
                    $where = 'ORDER BY id DESC LIMIT 1';
                }
                
                $session->newQuery();
                $sql = 'SELECT '.$select.' FROM round_stats '.$where;
                $info = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                
                $moneySpent = $info->moneyresearch;
                $totalResearched = $info->researchcount;
                
                require '/var/www/classes/Ranking.class.php';
                $ranking = new Ranking();
                
                $rankInfo = $ranking->getResearchRank($_SESSION['id']);
                
                $myRank = $rankInfo['rank'];

                $return = number_format($moneySpent).'#'.number_format($totalResearched).'#'.number_format($myRank);
        
                $result['msg'] = $return;
                
                break;
            
            case 'getFileActionsModal':
            
                if(isset($_POST['type'])){
                    
                    if($_POST['type'] == 'text'){
                        $type = 'text';
                    } else {
                        $type = 'folder';
                    }
                    
                    if(isset($_POST['remote'])){
                        $remote = TRUE;
                        if($_POST['remote'] == 0){
                            $remote = FALSE;
                        }
                    } else {
                        $remote = FALSE;
                    }
                    
                    if(!isset($_POST['action'])){
                        $result['status'] = 'ERROR';
                        break;
                    }
                    
                    $action = $_POST['action'];
                    
                    if($remote){
                        $host = long2ip($_SESSION['LOGGED_IN']);
                    } else {
                        $host = 'localhost';
                    }

                    $pdo = PDO_DB::factory();

                    $id = $_POST['id'];

                    if(!ctype_digit($id)){      
                        if($action != 'create'){
                            $result['status'] = 'ERROR';
                            break;
                        }
                    }

                    if($type == 'text'){
                        
                        switch($action){
                            
                            case 'create':
                                
                                $title = _('Create text file at ').$host;
                                $text = '<div class=\"control-group\"><div class=\"input-prepend\"><div class=\"controls\"><input class=\"name\" type=\"text\" name=\"name\" placeholder=\"'._('File name').'\" style=\"width: 67%;\"/><span class=\"add-on\" style=\"width: 10%;\">.txt</span></div></div></div><div class=\"control-group\"><div class=\"controls\"><textarea id=\"wysiwyg\" class=\"text\" name=\"text\" rows=\"5\" placeholder=\"'._('Content').'\" style=\"width: 80%;\"></textarea><br/><span class=\"small pull-left link text-show-editor\" style=\"margin-left: 9%;\">'._('Show editor').'</span></div></div>';
                                $btn = '<input type=\"submit\" class=\"btn btn-primary\" value=\"'._('Create text file').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                                
                                break;
                            case 'edit':
                           
                                $session->newQuery();
                                $sql = "SELECT software_texts.text, software.softName
                                        FROM software_texts 
                                        INNER JOIN software ON software.id = software_texts.id
                                        WHERE software_texts.id = '".$id."' 
                                        LIMIT 1";
                                $txtInfo = $pdo->query($sql)->fetchAll();     

                                if(sizeof($txtInfo) == 0){
                                    $result['status'] = 'ERROR';
                                    break;
                                }                                
                                
                                $txtName = str_replace('"', '\"', $txtInfo['0']['softname']);
                                $txtContent = str_replace('"', '\"', $txtInfo['0']['text']);
                                                                
                                
                                $txtContent = str_replace("\r\n", "\n", $txtContent);
                                $txtContent = str_replace("\r", "\n", $txtContent);
                                
                                $txtContent = str_replace("\n", "\\n", $txtContent);
                                
                                $title = _('Edit text file at ').$host;
                                $text = '<div class=\"control-group\"><div class=\"input-prepend\"><div class=\"controls\"><input class=\"name\" type=\"text\" name=\"name\" value=\"'.$txtName.'\" style=\"width: 67%;\"/><span class=\"add-on\" style=\"width: 10%;\">.txt</span></div></div></div><div class=\"control-group\"><div class=\"controls\"><textarea id=\"wysiwyg\" name=\"text\" rows=\"5\" style=\"width: 80%;\">'.$txtContent.'</textarea><br/><span class=\"small pull-left link text-show-editor\" style=\"margin-left: 9%;\">'._('Show editor').'</span></div></div>';
                                $btn = '<input type=\"submit\" class=\"btn btn-primary\" value=\"'._('Edit text file').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';

                                break;
                            default:
                                $result['status'] = 'ERROR';
                                break;
                            
                        }
                        
                    } else {
                        
                        switch($action){
                            
                            case 'create':
                                
                                $title = _('Create folder at ').$host;
                                $text = '<div class=\"control-group\"><div class=\"controls\"><input class=\"name\" type=\"text\" name=\"name\" placeholder=\"'._('Folder name').'\" style=\"width: 80%;\"/></div></div>';
                                $btn = '<input type=\"submit\" class=\"btn btn-primary\" value=\"'._('Create folder').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';    
                                
                                break;
                            case 'edit':
                                
                                $session->newQuery();
                                $sql = "SELECT softname FROM software WHERE id = '".$id."' LIMIT 1";
                                $folderInfo = $pdo->query($sql)->fetchAll();                                
                                
                                if(sizeof($folderInfo) == 0){
                                    $result['status'] = 'ERROR';
                                    break;
                                }
                                
                                $folderName = str_replace('"', '\"', $folderInfo['0']['softname']);
                                
                                $title = _('Edit folder at ').$host;
                                $text = '<div class=\"control-group\"><div class=\"controls\"><input class=\"name\" type=\"text\" name=\"name\" value=\"'.$folderInfo['0']['softname'].'\" placeholder=\"Folder name\" style=\"width: 80%;\"/></div></div>';
                                $btn = '<input type=\"submit\" class=\"btn btn-primary\" value=\"'._('Edit folder').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';                                
                                
                                break;
                            case 'delete':
                                
                                $session->newQuery();
                                $sql = "SELECT softname, softversion FROM software WHERE id = '".$id."' LIMIT 1";
                                $data = $pdo->query($sql)->fetchAll();

                                if(sizeof($data) == 0){
                                    $result['status'] = 'ERROR';
                                    break;                    
                                }                                
                                
                                $folderName = $data['0']['softname'];
                                
                                if($remote){
                                    $link = 'internet?view=software';
                                } else {
                                    $link = 'software';
                                }
                                
                                $title = 'Delete folder '.$folderName;

                                if($data['0']['softversion'] > 0){

                                    $text = 'Folder <strong>'.$folderName.'</strong> have softwares and they must be deleted before.';
                                    $btn = '<a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Ok').'</a>';

                                } else {

                                    $text = 'Are you sure you want to delete the folder <strong>'.$folderName.'</strong>?';
                                    $btn = '<input type=\"submit\" class=\"btn btn-primary\" value=\"'._('Delete folder').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';

                                }                                
                                
                                break;
                            default:
                                $result['status'] = 'ERROR';
                                break;
                            
                        }
  
                    }
                    
                    if($result['status'] == 'ERROR'){
                        break;
                    }
                    
                    $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                    
                }
                
                break;
            case 'getPwdInfo':
                
                require '/var/www/classes/Process.class.php';
                $process = new Process();
                $player = new Player();
                
                $btn = '';
                $select2 = False;
                
                $title = _('Change password');
                
                if(!$process->issetProcess($_SESSION['id'], 'RESET_PWD', '', 'local', '', '', '')){
                    
                    $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Change').'\" DISABLED><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                    
                    $pwdInfo = $player->pwd_info();

                    if($pwdInfo['PRICE'] > 0){
                        
                        $finances = new Finances();                        
                        
                        $text = sprintf(_('You can wait %s<strong>%s</strong></span> for your next free reset or pay <strong>$%s</strong> for our charged password reset service.'), '<span class=\"red\">', $pwdInfo['NEXT_RESET'], number_format($pwdInfo['PRICE']));
                        $text .= '</br></br>';
                        if($finances->totalMoney() >= $pwdInfo['PRICE']){
                        
                            $text .= '<div id=\"loading\"><img src=\"images/ajax-money.gif\">'._('Please wait').'</div><input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\"></span>';
                            
                            $select2 = True;
                            
                        } else {
                            $btn = '<a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                            $text .= _('You do not have enough money.');
                        }
                        
                    } else {
                        $text = _('You can reset your password now <b>for free!</b>');
                        $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Change').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                    }
                    
                } else {
                    $text = sprintf(_('There already is a password change in process. Refeer to the %sTask Manager%s.'), '<a href=\"processes\">', '</a>');
                }
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'","select2":"'.$select2.'"}]';
                
                break;
            case 'getPartModal':

                if(!isset($_POST['opts'])){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $opts = $_POST['opts'];
                
                if(!isset($opts['part']) || !isset($opts['price'])){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                if(!ctype_digit($opts['price'])){
                    $result['status'] = 'ERROR';
                    exit();
                }                
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $totalMoney = $finances->totalMoney();
                
                $title = _('Upgrade ').strtoupper($opts['part']);
                
                $text = sprintf(_('Are you sure you want to buy %s for %s<strong>$%s</strong></span>?'), strtoupper($opts['part']), '<span class=\"red\">', number_format($opts['price']));
                
                $text .= '<br/><br/>';
                
                if($totalMoney >= $opts['price']){
                    $text .= '<input type=\"hidden\" id=\"accSelect\" value=\"\"><span id=\"desc-money\"></span>';
                    $btn = '<input id=\"modal-submit\" type=\"submit\" class=\"btn btn-primary\" value=\"'._('Buy').'\"><a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                } else {
                    $text .= _('You do not have enough money.');
                    $btn = '<a data-dismiss=\"modal\" class=\"btn\" href=\"#\">'._('Cancel').'</a>';
                }
                                
                
                $result['msg'] = '[{"title":"'.$title.'","text":"'.$text.'","btn":"'.$btn.'"}]';
                
                break;
            case 'getStatic':
            
                $pdo = PDO_DB::factory();
                
                $session->newQuery();
                $sql = "SELECT gameip, login, cache.reputation, ranking_user.rank
                        FROM users
                        LEFT JOIN cache
                        ON cache.userID = users.id
                        LEFT JOIN ranking_user
                        ON ranking_user.userID = users.id
                        WHERE id = '".$_SESSION['id']."'";
                $staticInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                
                $return = '[{"ip":"'.long2ip($staticInfo->gameip).'","user":"'.$staticInfo->login.'","reputation":"'.number_format($staticInfo->reputation).'","rank":"'.number_format($staticInfo->rank).'","rep_title":"'._('Reputation').'","rank_title":"'._('Ranking').'"}]';
                
                $result['msg'] = $return;
                
                break;
            case 'getCommon':
                
                require '/var/www/classes/Mail.class.php';
                require '/var/www/classes/Finances.class.php';
                
                $mail = new Mail();
                $player = new Player();
                $finances = new Finances();
                
                $common['online'] = $player->countLoggedInUsers();
                $common['unread'] = $mail->countUnreadMails();
                $common['mission_complete'] = 0;
                $common['finances'] = number_format($finances->totalMoney());
                
                if($session->issetMissionSession()){
                    require '/var/www/classes/Mission.class.php';
                    $mission = new Mission();
                    
                    if($mission->missionStatus($_SESSION['MISSION_ID']) == 3){
                        $common['mission_complete'] = 1;
                    }
                }
                
                $common['unread_title'] = sprintf(ngettext('Unread message.', 'Unread messages.', $common['unread']), $common['unread']);
                $common['unread_text'] = sprintf(ngettext('You have %d unread message.', 'You have %d unread messages.', $common['unread']), $common['unread']);
                $common['online_title'] = sprintf(_('%d online players'), $common['online']);
                $common['finances_title'] = _('Finances');
                
                $return = '[{';
                foreach($common as $key => $item){
                    $return .= '"'.$key.'":"'.$item.'",';
                }
                $return = substr($return, 0, -1).'}]';

                $result['msg'] = $return;            

                break;
            case 'getTutorialVirusID':
                
                require '/var/www/classes/Mission.class.php';
                $mission = new Mission();
                
                $return = '[{"id":"0","ip":"0"}]';
                if(isset($_SESSION['MISSION_ID'])){
                    if($_SESSION['MISSION_TYPE'] == 83 ||$_SESSION['MISSION_TYPE'] == 84){
                        
                        if($mission->issetMission($_SESSION['MISSION_ID'])){
                            
                            $return = '[{"id":"'.$mission->missionNewInfo($_SESSION['MISSION_ID']).'",';
                            $return .= '"ip":"'.long2ip($mission->missionVictim($_SESSION['MISSION_ID'])).'"}]';
                            
                        }
                        
                    }
                }
                
                $result['msg'] = $return;
                
                break;            
            case 'getTutorialFirstVictim':
            
                require '/var/www/classes/Mission.class.php';
                $mission = new Mission();
                                
                if(isset($_SESSION['MISSION_ID'])){
                    if($_SESSION['MISSION_TYPE'] >= 80){
                        
                        if($mission->issetMission($_SESSION['MISSION_ID'])){
                            
                            if($_SESSION['MISSION_TYPE'] < 82){
                                $result['msg'] = long2ip($mission->missionVictim($_SESSION['MISSION_ID']));
                            } elseif($_SESSION['MISSION_TYPE'] >= 82){
                                $result['msg'] = long2ip($mission->missionInfo2($_SESSION['MISSION_ID']));
                            }
                            
                        }
                        
                    }
                }
                
                break;
            case 'getPlayerLearning':
            
                $player = new Player();
                
                $result['msg'] = $player->playerLearning();
                
                break;
            case 'getTotalMoney':
            
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();
                
                $result['msg'] = $finances->totalMoney();
                
                break;
            case 'getBankAccs':
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();

                $acc = $finances->htmlSelectBankAcc(1);
                
                $acc = _('Account: ').$acc;
                
                if(strpos($acc, 'option') !== FALSE){
                    $result['msg'] = $acc;
                } else {
                    $result['msg'] = 0;
                }

                break;
            case 'manageViruses':
                
                require '/var/www/classes/System.class.php';
                $system = new System();
                
                $listID = $_POST['id'];
                
                if(!$system->validate($_POST['ip'], 'ip')){
                    $result['status'] = 'ERROR';
                    break;
                }
                
                $listIP = ip2long($_POST['ip']);
                
                if(!ctype_digit($listID)){
                    $result['status'] = 'ERROR';
                    break;
                }

                require_once '/var/www/classes/List.class.php';
                $list = new Lists();

                if(!$list->issetID($listID, 1)){
                    $result['status'] = 'ERROR';
                    break;
                }

                $pdo = PDO_DB::factory();
                
                $session->newQuery();
                $sql = "SELECT virus.virusID, virus.virusType, software.softname
                        FROM virus
                        INNER JOIN software
                        ON virus.virusID = software.id
                        WHERE installedIp = '".$listIP."' AND installedBy = '".$_SESSION['id']."'";
                $data = $pdo->query($sql)->fetchAll();
                
                if(sizeof($data) <= 1){
                    $result['msg'] = '';
                    break;
                }
                
                $msg = '[{';
                
                for($i = 0; $i<sizeof($data); $i++){
                    
                    switch($data[$i]['virustype']){
                        case 1:
                            $ext = '.vspam';
                            break;
                        case 2:
                            $ext = '.vwarez';
                            break;
                        case 3:
                            $ext = '.vddos';
                            break;
                        case 4:
                            $ext = '.vminer';
                            break;
                        default:
                            $ext = '.?';
                            break;
                    }
                    
                    if($msg != '[{'){
                        $msg .= '{';
                    }
                    
                    $msg .= '"id":"'.$data[$i]['virusid'].'","text":"'.$data[$i]['softname'].$ext.'"},'; 
                    
                }
                
                $msg = substr($msg, 0, -1).']';
                                                
                $result['msg'] = $msg;
                
                break;
            case 'searchClan':

                $html = '<li>
                            <div class="span12">
                                '._('Type at least two caracteres').'
                            </div>
                            <div style="clear: both;"></div>
                        </li>';

                $query = trim(preg_replace("/[^A-Za-z0-9]/", " ", $_POST['query']));
                if(strlen($query) > 1){

                    $html = '';

                    $pdo = PDO_DB::factory();

                    $session->newQuery();
                    $sql = 'SELECT clanID, name, nick, power, slotsUsed FROM clan WHERE name LIKE "%'.$query.'%" OR nick LIKE "%'.$query.'%" LIMIT 10';

                    $data = $pdo->query($sql)->fetchAll();

                    if(sizeof($data) > 0){

                        foreach($data as $clanInfo){

                            $imagePath = 'images/clan/'.md5($clanInfo['name']).'.jpg';
                            $nameStr = '['.$clanInfo['nick'].'] '.$clanInfo['name'];
                            $members = $clanInfo['slotsused'];
                            $power = number_format($clanInfo['power']);

                            $html .= '

                            <li>
                                <div class="span3">
                                    <div class="clanwar-all-left">
                                        <img src="'.$imagePath.'" width="50" height="50">
                                    </div>
                                </div>
                                <div class="span6">
                                    <div class="clanwar-all-score">
                                        <a href="clan?id='.$clanInfo['clanid'].'" style="color: #000;">'.$nameStr.'</a><br/>
                                        <span class="small nomargin">'.$members.' '._('members').'</span>
                                    </div>
                                </div>
                                <div class="span2">
                                    <div class="clanwar-all-bounty">
                                        <span class="small">'._('Power').'</span><br/>
                                        '.$power.'                     
                                    </div>
                                </div>
                                <div class="span1"></div>
                                <div style="clear: both;"></div>
                            </li>';

                        }

                    } else {

                        $html = '<li>
                                    <div class="span12">
                                        '._('No results found').'
                                    </div>
                                    <div style="clear: both;"></div>
                                </li>';

                    }



                }

                $result['msg'] = $html;

                break;

            case 'warHistory':

                require '/var/www/classes/Player.class.php';
                $player = new Player();

                require '/var/www/classes/Clan.class.php';
                $clan = new Clan();

                $npc = new NPC();

                $pdo = PDO_DB::factory();

                $wid = $_POST['warid'];

                if(!ctype_digit($wid)){
                    $result['status'] = 'ERROR';
                    $result['msg'] = '';
                }

                if($result['status'] == 'OK'){

                    //hist_clans_war

                    $session->newQuery();
                    $sql = 'SELECT COUNT(*) AS total, idWinner, idLoser, scoreWinner, scoreLoser, startDate, endDate, bounty, round, TIMESTAMPDIFF(DAY, startDate, endDate) AS duration FROM hist_clans_war WHERE id = '.$wid.' LIMIT 1';
                    $warInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                    if($warInfo->total == 1){

                        $myClan = $_SESSION['CLAN_ID'];

                        $string = '<div class="center">';

                        if($myClan == $warInfo->idwinner){
                            $otherClan = $warInfo->idloser;
                            $string .= '<font color="green">';
                            $stringEnd = ' victory ';
                            $myColor = 'green';
                            $otherColor = 'red';
                            $myScore = $warInfo->scorewinner;
                            $otherScore = $warInfo->scoreloser;
                        } else {
                            $otherClan = $warInfo->idwinner;
                            $string .= '<font color="red">';
                            $stringEnd = ' defeat';
                            $myColor = 'red';
                            $otherColor = 'green';
                            $myScore = $warInfo->scoreloser;
                            $otherScore = $warInfo->scorewinner;
                        }

                        $myClanInfo = $clan->getClanInfo($myClan);
                        $otherClanInfo = $clan->getClanInfo($otherClan);


                        $scoreDiff = $warInfo->scorewinner - $warInfo->scoreloser;

                        if($scoreDiff > 10000){
                            $string .= 'Major ';
                        } elseif($scoreDiff > 5000) {
                            $string .= 'Regular ';
                        } else {
                            $string .= 'Minor ';
                        }

                        $string .= $stringEnd.'</font> against <a href="clan?id='.$otherClan.'"><b>'. $otherClanInfo->name .'</b></a>';                    

                        $string .= '<span class="small">From '.substr($warInfo->startdate, 0, -9).' to '.substr($warInfo->enddate, 0, -9).' ('.$warInfo->duration.' days)</span><br/></div><br/>';

                        $html = '<ul class="list">
                                    <li>
                                        <div class="span4">
                                            <div class="clanwar-all-left">';
                        $html .= '<span class="small nomargin">vs</span> '.$otherClanInfo->name.'</span>';

                        $html .= '</div></div>
                                        <div class="span5">
                                            <div class="clanwar-all-score">';

                        $html .= '<span class="'.$myColor.'">'.number_format($myScore).'</span> / <span class="'.$otherColor.'">'.number_format($otherScore).'</span><br/>';

                        $html .= '</div></div>
                                        <div class="span3">
                                            <div class="clanwar-all-bounty">';

                        $html .= '<font color="green">$'.number_format($warInfo->bounty).'</font>';

                        $html .= '</div></div><div style="clear: both;"></div></li></ul>';

                        $string .= $html;

                        $sql = 'SELECT COUNT(*) AS total, attackerClan, victimClan, ddosID FROM clan_ddos_history WHERE warID = '.$wid;
                        $ddosHistInfo = $pdo->query($sql)->fetchAll();

                        if(sizeof($ddosHistInfo) > 0){

                            for($i = 0; $i < sizeof($ddosHistInfo); $i++){

                                if($warInfo->round == $curRound){

                                    $session->newQuery();
                                    $sql = 'SELECT COUNT(*) AS total, attID, vicID, power, servers, date, vicNPC FROM round_ddos WHERE id = '.$ddosHistInfo[$i]['ddosid'];
                                    $ddosInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                                } else {

                                    $session->newQuery();
                                    $sql = 'SELECT COUNT(*) AS total, attID, vicID, power, servers, date, vicNPC FROM round_ddos WHERE id = '.$ddosHistInfo[$i]['ddosid'].' AND round = '.$ddosHistInfo[$i]['round'];
                                    $ddosInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                                }

                                $attackerInfo = $player->getPlayerInfo($ddosInfo->attid);
                                $attackerName = $attackerInfo->login;
                                $attackerIP = $attackerInfo->gameip;
                                if($ddosInfo->vicnpc == 0){
                                    $victimInfo = $player->getPlayerInfo($ddosInfo->vicid);
                                    $victimIP = $victimInfo->gameip;
                                } else {
                                    $victimInfo = $npc->getNPCInfo($ddosInfo->vicid);
                                    $victimIP = $victimInfo->npcip;
                                }                            

                                $ddosString = '<span class="small">'.substr($ddosInfo->date, 0, -9).'</span> <b><a href="internet?ip='.long2ip($attackerIP).'">'.$attackerName.'</a></b> attacked <a href="internet?ip='.long2ip($victimIP).'">'.long2ip($victimIP).'</a> with a power of '.number_format($ddosInfo->power).' using '.$ddosInfo->servers.' servers';

                            }

                        } else {

                            $ddosString = 'Ops. We couldnt find the logs of this war. Sorry';

                        }

                        $string .= $ddosString;

                        $result['msg'] = $string;

                    } else {
                        $result['status'] = 'ERROR';
                        $result['msg'] = 'Sorry, we couldnt find the logs of this war.';
                    }

                }

                break;        
            case 'completeProcess':


                $id = $_POST['id'];

                if(!ctype_digit($id)){
                    $result['status'] = 'ERROR';
                    $result['msg'] = '';
                }

                require_once '/var/www/classes/Process.class.php';
                $process = new Process();

                if($process->issetPID($id)){
                    $process->getProcessInfo($id);
                } else {
                    $result['status'] = 'ERROR';
                    $result['msg'] = 'This process is invalid.';
                }

                if($process->pCreatorID != $_SESSION['id']){
                    $result['status'] = 'ERROR';
                    $result['msg'] = 'This process is invalid.';    
                }

                if($result['status'] == 'OK'){

                    if($process->pTimeLeft <= 0){

                        $process->completeProcess($id);
                        $result['redirect'] = $process->studyRedirect();
                        
                    } else {
                        $result['status'] = 'FALSE';
                    }

                }

                break;

            case 'loadSoftware':
                
                require '/var/www/classes/Player.class.php';
                
                if(isset($_POST['external'])){
                    $isExternal = TRUE;
                } else {
                    $isExternal = FALSE;
                }
                
                if(isset($_POST['folder']) && isset($_POST['remote'])){

                    $isFolder = TRUE;
                    
                    if($_POST['remote'] == 1){

                        if(!$session->isInternetLogged()){
                            $result['status'] = 'ERROR';
                            break;
                        }

                        $player = new Player();
                        $playerInfo = $player->getIDByIP($_SESSION['LOGGED_IN'], '');
                        
                        $id = $playerInfo['0']['id'];
                        if($playerInfo['0']['pctype'] == 'NPC'){
                            $npc = 1;
                        } else {
                            $npc = 0;
                        }
                        
                    } else {

                        $npc = 0;
                        $id = $_SESSION['id'];
                        
                    }
                    
                } else {
                    $isFolder = FALSE;
                    $id = $_SESSION['id'];
                }
                
                require '/var/www/classes/PC.class.php';
                $software = new SoftwareVPC();

                $pdo = PDO_DB::factory();

                if(!$isFolder){
                    if(!$isExternal){
                        $sql = "SELECT id, softname, softversion, softsize, softtype, softhidden, softhiddenwith, isFolder, originalFrom
                                FROM software 
                                WHERE userid = $id AND isNPC = 0 AND isFolder = 0 
                                GROUP BY softType, softVersion DESC, softLastEdit ASC";
                    } else {
                        $sql = "SELECT software.id, software.softName, software.softType, software.softVersion, software.softhidden
                                FROM software 
                                LEFT JOIN software_external
                                ON software.id = software_external.id
                                WHERE software.userID = '".$id."' AND software_external.id IS NULL AND software.isNPC = 0
                                ORDER BY software.softType, software.softVersion DESC";
                    }
                } else {
                    $sql = "SELECT id, softname, softversion, softType, softhidden
                            FROM software 
                            WHERE userid = $id AND isNPC = $npc AND isFolder = 0 AND softHidden = 0 AND softType <> 31
                            GROUP BY softType, softVersion DESC, softLastEdit ASC";
                }
           
                $session->newQuery();
                $data = $pdo->query($sql);            

                if(!$isExternal && !$isFolder){

                    if(!isset($_SESSION['LOGGED_IN'])){
                        $result['status'] = 'ERROR';
                        return;
                    }                    
                    
                    $pertinentID = 0;
                    if($session->issetMissionSession()){
                        if ($_SESSION['MISSION_TYPE'] == 2 || $_SESSION['MISSION_TYPE'] == 83) {

                            require '/var/www/classes/Mission.class.php';
                            $mission = new Mission();

                            if($_SESSION['MISSION_TYPE'] == 2){
                                $pertinentID = $mission->missionNewInfo($_SESSION['MISSION_ID']);
                                $pertinentIP = $mission->missionHirer($_SESSION['MISSION_ID']);
                            } else {
                                $pertinentID = $mission->missionInfo($_SESSION['MISSION_ID']);
                                $pertinentIP = $mission->missionVictim($_SESSION['MISSION_ID']);
                            }

                        }
                    }
                
                }

                $return = '';
                $prevType = 0;
                $times = 0;
                $strPlace = 0;
                $added = 0;
                $i = 0;
                $tagStart = $tagEnd = '';
                while($softInfo = $data->fetch(PDO::FETCH_OBJ)){

                    $valid = 1;
                    if($softInfo->softhidden == 1){
                        $valid = 0;
                    } elseif($softInfo->softtype == 19 || $softInfo->softtype == 26 || $softInfo->softtype == 31 || $softInfo->softtype >= 90){
                        $valid = 0;
                    }

                    if($valid == 1){

                        if(!$isExternal && !$isFolder){
                            if($pertinentID == $softInfo->id){
                                if($_SESSION['LOGGED_IN'] == $pertinentIP){
                                    $result['redirect'] = $softInfo->id;
                                    $tagStart = '<span class=\"red\"><strong>';
                                    $tagEnd = '</strong></span>';
                                }
                            }
                        }

                        $softName = $tagStart.$softInfo->softname.$software->getExtension($softInfo->softtype);
                        if($softInfo->softtype != 30){
                            $softName .= ' ('.$software->dotVersion($softInfo->softversion).')';
                        }
                        $softName .= $tagEnd;
                        
                        
                        if($softInfo->softtype == $prevType){

                            $return .= ',{"id":"'.$softInfo->id.'","text":"'.$softName.'"}';

                            $times++;
                            
                        } else {

                            if($times == 0 && $prevType != ''){
                                $i++;
                            }

                            if($prevType != ''){
                                $return .= ']},';
                            }

                            $strPlace = strlen($return);
                            $return .= '{"text":"'._($software->int2stringSoftwareType($softInfo->softtype)).'","children":[{';
                            $return .= '"id":"'.$softInfo->id.'","text":"'.$softName.'"}';

                            $times = 0;
                            $added = strlen($return) - $strPlace;
                            
                        }

                        $prevType = $softInfo->softtype;

                        if($tagStart != ''){ //is mission

                            $aux = $return;
                            $return = '{"text":"'._('Mission').'","children":[{"id":"'.$softInfo->id.'","text":"'.$softName.'"}]},'.$aux;

                            $tagStart = $tagEnd = '';
                            
                        }                    

                    }

                }

                $aux = $return;
                $return = '['.$aux;

                $return .= ']}]';

                $result['msg'] = $return;

                break;
            case 'loadHistory':

                require '/var/www/classes/Internet.class.php';
                $internet = new Internet();

                $result['msg'] = $internet->history_getJSON();

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