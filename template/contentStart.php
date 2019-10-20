<?php 

require 'template/gameHeader.php';

$requestURI = $_SERVER['REQUEST_URI'];
$phpSelf = $_SERVER['PHP_SELF'];

$crudePage = substr(substr($phpSelf, 1), 0, -4);

if(!strpos($requestURI, '.php')){
    $requestURI .= '.php';
}

$headerArr = Array();
$menu = Array();

$menu['software'] = $menu['internet'] = $menu['processes'] = $menu['missions'] = $menu['hardware'] = $menu['clan'] = $menu['list'] = $menu['index'] = $menu['finances'] = $menu['university'] = $menu['ranking'] = $menu['log'] = $menu['fame'] = $menu['doom'] = '';
$nav['profile'] = $nav['mail'] = $nav['settings'] = '';

$css['select2'] = 0;
$css['login'] = 0;
$css['uniform'] = 0;
$css['wysiwyg'] = 0;
$css['fa'] = 0;

$bodyClass = $crudePage;

switch($crudePage){
    
    case 'software':
        
        $menu['software'] = ' class="active"';
        $sub = 'Software';
        
        $headerArr['0']['name'] = 'Software';
        $headerArr['0']['link'] = 'software';
        
        if($phpSelf != $requestURI){
            if(isset($_GET['page'])){
                if($_GET['page'] == 'external'){
                    $headerArr[1]['name'] = 'External Hard Drive';
                    $headerArr[1]['link'] = 'software?page=external';
                    $css['select2'] = 1;
                    $bodyClass .= ' external pie';
                }
            } elseif(isset($_GET['id'])){
                if(is_numeric($_GET['id'])){
                    $headerArr[1]['name'] = 'Software Information';
                    $headerArr[1]['link'] = 'software?id='.$_GET['id'];
                    $bodyClass .= ' id';
                }
            } elseif(isset($_GET['action'])){
                switch($_GET['action']){
                    case 'folder':
                        $bodyClass .= ' file-actions folder';
                        if(isset($_GET['view'])){
                            $headerArr[1]['name'] = 'Folder';
                            $headerArr[1]['link'] = 'software?action=folder&view='.$_GET['view'];
                            $css['select2'] = 1;                         
                        } elseif(isset($_GET['edit'])){
                            $headerArr[1]['name'] = 'Edit folder';
                            $headerArr[1]['link'] = 'software?action=folder&edit='.$_GET['edit'];                             
                        }
                        break;
                    case 'text':
                        $bodyClass .= ' file-actions text';
                        if(isset($_GET['view'])){
                            $headerArr[1]['name'] = 'Text file';
                            $headerArr[1]['link'] = 'software?action=text&view='.$_GET['view'];  
                        } elseif(isset($_GET['edit'])) {
                            $headerArr[1]['name'] = 'Edit text file';
                            $headerArr[1]['link'] = 'software?action=text&edit='.$_GET['edit'];                              
                        }
                        break;
                }
            }
        } else {
            $bodyClass .= ' file-actions pie';
        }
        break;
        
    case 'internet':

        $menu['internet'] = ' class="active"';
        $sub = 'Internet';
        
        $headerArr['0']['name'] = 'Internet';
        $headerArr['0']['link'] = 'internet';     
               
        $index = 1;
        $curIP = '';
        
        if(isset($_GET['ip'])){
            $system = new System();
            if(!$system->validate(trim($_GET['ip']), 'ip')){
                $system->handleError(sprintf(_('The IP address %s is invalid.'), '<strong>'.htmlentities($_GET['ip']).'</strong>'), 'internet');
            }
            $curIP = trim($_GET['ip']);
            //untaint($curIP);
            $bodyClass .= ' history';
        } elseif(isset($_SESSION['CUR_IP'])){
            $curIP = long2ip($_SESSION['CUR_IP']);
        }

        if($curIP != ''){
            $headerArr[$index]['name'] = $curIP;
            $headerArr[$index]['link'] = 'internet?ip='.$curIP;     
            $index++; 
        }

        if($phpSelf != $requestURI && !isset($_GET['ip'])){
            if(isset($_GET['action'])){
                switch($_GET['action']){
                    case 'login':
                        $bodyClass .= ' history';
                        if(isset($_GET['type'])){
                            if($_GET['type'] == 'bank'){
                                $headerArr[$index]['name'] = 'Account login';
                                $headerArr[$index]['link'] = 'internet?action=login&type=bank';     
                                $index++;                                   
                            }
                        } else {
                            $headerArr[$index]['name'] = 'Login';
                            $headerArr[$index]['link'] = 'internet?action=login';     
                            $index++; 
                        }
                        $css['login'] = 1;
                        break;
                    case 'hack':
                        $bodyClass .= ' history';
                        if(isset($_GET['type'])){
                            if($_GET['type'] == 'bank'){
                                $headerArr[$index]['name'] = 'Hack account';
                                $headerArr[$index]['link'] = 'internet?action=hack&type=bank';     
                                $index++;                                   
                            }
                        } else {                        
                            $headerArr[$index]['name'] = 'Hack';
                            $headerArr[$index]['link'] = 'internet?action=hack';     
                            $index++;  
                        }
                        if(isset($_GET['method'])){
                            switch($_GET['method']){
                                case 'xp':
                                    $headerArr[$index]['name'] = 'Exploit attack';
                                    $headerArr[$index]['link'] = 'internet?action=hack&method=xp';     
                                    $index++;                                          
                                    break;
                                case 'bf':
                                    $headerArr[$index]['name'] = 'Bruteforce attack';
                                    $headerArr[$index]['link'] = 'internet?action=hack&method=bf';     
                                    $index++;                                          
                                    break;
                            }
                        }
                        break;
                    case 'buy':
                    case 'upgrade':
                    case 'internet':    
                        $headerArr[$index]['name'] = 'Clan server';
                        $headerArr[$index]['link'] = 'internet?view=clan';     
                        $index++;
                        $css['select2'] = 1;
                        $bodyClass .= ' hardware';
                        switch($_GET['action']){
                            case 'buy':
                                $headerArr[$index]['name'] = 'Buy server';
                                $headerArr[$index]['link'] = 'internet?view=clan&action=buy';     
                                $index++;                                   
                                break;
                            case 'upgrade':
                                $headerArr[$index]['name'] = 'Upgrade server';
                                $headerArr[$index]['link'] = 'internet?view=clan&action=upgrade&server='.$_GET['server'];     
                                $index++;   
                                break;
                            case 'internet':
                                $headerArr[$index]['name'] = 'Internet';
                                $headerArr[$index]['link'] = 'internet?view=clan&action=internet';     
                                $index++;                                   
                                break;
                        }
                        break;
                }
            } elseif(isset($_GET['view'])){
                switch($_GET['view']){
                    case 'clan':
                        $headerArr[$index]['name'] = 'Clan server';
                        $headerArr[$index]['link'] = 'internet?view=clan';     
                        $index++;
                        $bodyClass .= ' hardware';
                        break;
                    case 'logs':
                        $headerArr[$index]['name'] = 'Log File';
                        $headerArr[$index]['link'] = 'internet?view=logs';     
                        $index++;
                        $bodyClass .= ' page-log';
                        break;
                    case 'software':
                        $headerArr[$index]['name'] = 'Softwares';
                        $headerArr[$index]['link'] = 'internet?view=software';     
                        $index++;
                        $css['select2'] = 1;
                        if(!isset($_GET['cmd'])){
                            $bodyClass .= ' upload file-actions pie';
                        }
                        if(isset($_GET['id'])){
                            $headerArr[$index]['name'] = 'Software information';
                            $headerArr[$index]['link'] = 'internet?view=software&id='.$_GET['id'];     
                            $index++;                                   
                        } elseif(isset($_GET['cmd'])){
                            switch($_GET['cmd']){
                                case 'txt':
                                    $bodyClass .= ' file-actions text';
                                    if(isset($_GET['txt'])){
                                        $headerArr[$index]['name'] = 'Text file';
                                        $headerArr[$index]['link'] = 'software?action=text&view='.$_GET['txt'];  
                                    } elseif(isset($_GET['edit'])) {
                                        $headerArr[$index]['name'] = 'Edit text file';
                                        $headerArr[$index]['link'] = 'software?action=text&edit='.$_GET['edit'];                              
                                    } 
                                    $index++;
                                    break;
                                case 'folder':
                                    $bodyClass .= ' file-actions folder';
                                    if(isset($_GET['folder'])){
                                        $headerArr[$index]['name'] = 'Folder';
                                        $headerArr[$index]['link'] = 'internet?view=software&cmd=folder&folder='.$_GET['folder'];
                                        $css['select2'] = 1;
                                    } elseif(isset($_GET['edit'])){
                                        $headerArr[$index]['name'] = 'Edit folder';
                                        $headerArr[$index]['link'] = 'internet?view=software&cmd=folder&edit='.$_GET['edit'];                             
                                    }   
                                    $index++;
                                    break;
                            }
                        }
                        break;
                    case 'index':
                        $bodyClass .= ' history';
                        break;
                }
            } elseif(isset($_GET['bAction'])){
                $bodyClass .= ' money';
                if($_GET['bAction'] == 'show'){
                    $headerArr[$index]['name'] = 'Account Overview';
                    $headerArr[$index]['link'] = 'internet?bAction=show';     
                    $index++;
                }
            }
        } elseif(isset($_SESSION['LOGGED_IN']) && !isset($_GET['ip'])){
            switch($_SESSION['CUR_PAGE']){
                case 'logs':
                    $headerArr[$index]['name'] = 'Log file';
                    $headerArr[$index]['link'] = 'internet?view=logs';
                    $index++;
                    $bodyClass .= ' page-log';
                    break;
                case 'software':
                    $headerArr[$index]['name'] = 'Softwares';
                    $headerArr[$index]['link'] = 'internet?view=software';     
                    $index++;
                    $css['select2'] = 1;
                    $bodyClass .= ' upload';
                    $bodyClass .= ' file-actions';
                    $bodyClass .= ' pie';
                    break;
                case 'bank':
                    $headerArr[$index]['name'] = 'Account Overview';
                    $headerArr[$index]['link'] = 'internet?bAction=show';     
                    $index++;
                    break;
            }
        } elseif(isset($_SESSION['LOGGED_IN']) && isset($_GET['ip'])){
            if(ip2long($_GET['ip']) == $_SESSION['LOGGED_IN']){
                switch($_SESSION['CUR_PAGE']){
                    case 'logs':
                        $headerArr[$index]['name'] = 'Log file';
                        $headerArr[$index]['link'] = 'internet?view=logs';     
                        $index++;
                        $bodyClass .= ' page-log';
                        break;
                    case 'software':
                        $headerArr[$index]['name'] = 'Softwares';
                        $headerArr[$index]['link'] = 'internet?view=software';     
                        $index++;
                        $css['select2'] = 1;
                        $bodyClass .= ' upload';
                        $bodyClass .= ' file-actions';
                        $bodyClass .= ' pie';
                        break;
                    case 'bank':
                        $headerArr[$index]['name'] = 'Account Overview';
                        $headerArr[$index]['link'] = 'internet?bAction=show';     
                        $index++;
                        break;
                }
            }
        } elseif(isset($_SESSION['BANK_ACC'])){
            $headerArr[$index]['name'] = 'Account Overview';
            $headerArr[$index]['link'] = 'internet?bAction=show';     
            $index++;
            $bodyClass .= ' money';
        } else {
            if($session->isHacking()){
                $css['login'] = 1;
            }
            $bodyClass .= ' history';
        }
        
        break;
    
    case 'processes':
         
       $menu['processes'] = ' class="active"';
        $sub = 'Task manager';
        
        $headerArr[0]['name'] = 'Task manager';
        $headerArr[0]['link'] = 'processes';  
        
        $index = 1;          
        
        if($phpSelf != $requestURI){
            if(isset($_GET['page'])){
                switch($_GET['page']){
                    case 'all':
                        $headerArr[$index]['name'] = 'All tasks';
                        $headerArr[$index]['link'] = 'processes?page=all';     
                        $index++;                                   
                        break;
                    case 'cpu':
                        $headerArr[$index]['name'] = 'CPU tasks';
                        $headerArr[$index]['link'] = 'processes?page=cpu';     
                        $index++;
                        break;
                    case 'network':
                        $headerArr[$index]['name'] = 'Download manager';
                        $headerArr[$index]['link'] = 'processes?page=network';     
                        $index++;
                        break;
                    case 'running':
                        $headerArr[$index]['name'] = 'Running softwares';
                        $headerArr[$index]['link'] = 'processes?page=running';     
                        $index++;
                        $bodyClass .= ' pie';
                        break;
                }
            }
        }
        
        break;
    case 'missions':
        $sub = 'Missions';

        $menu['missions'] = ' class="active"';
        
        $headerArr['0']['name'] = 'Missions';
        $headerArr['0']['link'] = 'missions';     
               
        $index = 1;
        
        $issetMission = 0;
        if(isset($_SESSION['MISSION_ID'])){
            $issetMission = 1;
            $missionID = $_SESSION['MISSION_ID'];
        }
        
        if($_GET != NULL){
            if(isset($_GET['view'])){
                if($_GET['view'] == 'completed'){
                    $headerArr[$index]['name'] = 'Completed missions';
                    $headerArr[$index]['link'] = 'missions?view=completed'; 
                } else {
                    $headerArr[$index]['name'] = 'Available missions';
                    $headerArr[$index]['link'] = 'missions?view=all';    
                }
            } elseif(isset($_GET['id'])){
                if($issetMission == 1){
                    if($_GET['id'] == $missionID){
                        $headerArr[$index]['name'] = 'Current mission';
                        $headerArr[$index]['link'] = 'missions';
                        $headerArr[0]['link'] = 'missions?view=all';
                    } else {
                        $headerArr[$index]['name'] = 'Mission';
                        $headerArr[$index]['link'] = 'missions';
                    }
                }
            }
        } else {
            if($issetMission == 1){
                $headerArr[$index]['name'] = 'Current mission';
                $headerArr[$index]['link'] = 'missions';
                $headerArr[0]['link'] = 'missions?view=all';
            } else {
                $headerArr[$index]['name'] = 'Available missions';
                $headerArr[$index]['link'] = 'missions?view=all';    
            }
        }

        break;
    case 'hardware':
        $sub = 'Hardware';
        
        $menu['hardware'] = ' class="active"';
        
        $headerArr['0']['name'] = 'Hardware';
        $headerArr['0']['link'] = 'hardware';     
        
        $index = 1;        

        if($_GET != NULL){
            if(isset($_GET['opt'])){
                switch($_GET['opt']){
                    case 'upgrade':
                        $headerArr[$index]['name'] = 'Upgrade Server';
                        $headerArr[$index]['link'] = 'hardware?opt=upgrade';   
                        $index++;
                        if(isset($_GET['id'])){
                            $headerArr[$index]['name'] = 'My Server';
                            $headerArr[$index]['link'] = 'hardware?opt=upgrade&id='.$_GET['id'];   
                            $css['select2'] = 1;
                        }
                        break;
                    case 'xhd':
                        $headerArr[$index]['name'] = 'External Disk';
                        $headerArr[$index]['link'] = 'hardware?opt=xhd';
                        $index++;
                        if(isset($_GET['id'])){
                            $headerArr[$index]['name'] = 'Upgrade external disk';
                            $headerArr[$index]['link'] = 'hardware?opt=xhd';  
                            $css['select2'] = 1;
                        }                              
                        break;
                    case 'buy':
                        $headerArr[$index]['name'] = 'Buy Server';
                        $headerArr[$index]['link'] = 'hardware?opt=buy';
                        $css['select2'] = 1;
                        break;
                    case 'internet':
                        $headerArr[$index]['name'] = 'Internet';
                        $headerArr[$index]['link'] = 'hardware?opt=internet';   
                        $css['select2'] = 1;
                        break;
                }       

            }
        }
        
        break;
    
    case 'clan':
        
        $sub = 'Clan';
        
        $menu['clan'] = ' class="active"';
        
        $headerArr[0]['name'] = 'Clan';
        $headerArr[0]['link'] = 'clan';

        $index = 1;
        
        if($_GET != NULL){
            if(isset($_GET['action'])){
                switch($_GET['action']){
                    case 'list':
                        $headerArr[$index]['name'] = 'Member list';
                        $headerArr[$index]['link'] = 'clan?action=list';                        
                        break;
                    case 'war':
                        $headerArr[$index]['name'] = 'War';
                        $headerArr[$index]['link'] = 'clan?action=war';
                        $index++;
                        if(isset($_GET['show'])){
                            switch($_GET['show']){
                                case 'history':
                                    $headerArr[$index]['name'] = 'History';
                                    $headerArr[$index]['link'] = 'clan?action=war&show=history';
                                    $index++;
                                    if(isset($_GET['round'])){
                                        $headerArr[$index]['name'] = 'Round '.$_GET['round'];
                                        $headerArr[$index]['link'] = 'clan?action=war&show=history&round='.$_GET['round'];
                                    }
                                    break;
                                case 'current':
                                    $headerArr[$index]['name'] = 'Current round';
                                    $headerArr[$index]['link'] = 'clan?action=war&show=current';
                                    $index++;                                                             
                                    break;
                            }
                        }
                        break;
                    case 'admin':
                        $css['uniform'] = 1;
                        $bodyClass .= ' admin';
                        $headerArr[$index]['name'] = 'Admin Panel';
                        $headerArr[$index]['link'] = 'clan?action=admin';
                        $index++;
                        if(isset($_GET['opt'])){
                            if($_GET['opt'] == 'manage'){
                                $headerArr[$index]['name'] = 'Manage';
                                $headerArr[$index]['link'] = 'clan?action=admin&opt=manage';
                                $index++;             
                                if(isset($_GET['id'])){
                                    $headerArr[$index]['name'] = 'Member';
                                    $headerArr[$index]['link'] = 'clan?action=admin&opt=manage&id='.$_GET['id'];
                                    $index++;                          
                                    if(isset($_GET['do'])){
                                        if($_GET['do'] == 'kick'){
                                            $headerArr[$index]['name'] = 'Kick';
                                            $headerArr[$index]['link'] = 'clan?action=admin&opt=manage&id='.$_GET['id'].'&do=kick';
                                            $index++;                                                                            
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'settings':
                        $headerArr[$index]['name'] = 'Settings';
                        $headerArr[$index]['link'] = 'clan?action=settings';
                        break;
                    case 'leave':
                        $headerArr[$index]['name'] = 'Leave';
                        $headerArr[$index]['link'] = 'clan?action=leave';                        
                        break;
                    case 'create':
                        $headerArr[$index]['name'] = 'Create clan';
                        $headerArr[$index]['link'] = 'clan?action=create';
                        $css['select2'] = 1;
                        break;
                }
            } elseif(isset($_GET['id'])){
                $bodyClass .= ' profile view';
            }
        } else {            
            if($_SESSION['CLAN_ID'] != 0){
                $headerArr[1]['name'] = 'Name';
                $headerArr[1]['link'] = 'clan';
                $bodyClass .= ' profile view';
            }
        }
        
        break;
    case 'list':
        
        $bodyClass = 'hackeddb';
        
        $menu['list'] = ' class="active"';
        $sub = 'Hacked Database';
        
        $headerArr[0]['name'] = 'Hacked Database';
        $headerArr[0]['link'] = 'list';          
        
        $index = 1;
        
        if(isset($_GET['show'])){
            if($_GET['show'] == 'bankaccounts'){
                $headerArr[$index]['name'] = 'Bank Accounts';
                $headerArr[$index]['link'] = 'list?view=bankaccounts';                  
            } else {
                $headerArr[1]['name'] = 'IP List';
                $headerArr[1]['link'] = 'list';                  
            }
        } elseif(isset($_GET['action'])){
            if($_GET['action'] == 'ddos'){
                $headerArr[1]['name'] = 'DDoS';
                $headerArr[1]['link'] = 'list?action=ddos';
            } elseif($_GET['action'] == 'collect'){
                $headerArr[1]['name'] = 'Collect';
                $headerArr[1]['link'] = 'list?action=collect';
                $bodyClass .= ' collect';
            }
        } else {
            $headerArr[1]['name'] = 'IP List';
            $headerArr[1]['link'] = 'list';      
            $css['select2'] = 1;
        }
        
        break;
    case 'profile':
        
        $nav['profile'] = ' open';
        $sub = 'Profile';
        
        $headerArr[0]['name'] = 'Profile';
        $headerArr[0]['link'] = 'profile';          
        
        if(!isset($_GET['view'])){
            $bodyClass .= ' view';
        }
        
        $index = 1;
        
        break;
    case 'mail':

        $nav['mail'] = ' open';
        $sub = 'E-mail';
        
        $headerArr[0]['name'] = 'E-mail';
        $headerArr[0]['link'] = 'mail';          
        
        $index = 1;
        
        if(isset($_GET['action'])){
            if($_GET['action'] == 'new'){
                $css['wysiwyg'] = 1;
                $bodyClass .= ' new';
            }
        }
        
        break;
    case 'index':
        
        $menu['index'] = ' class="active"';
        $sub = 'Control Panel';
        
        break;
    case 'finances':
        
        $sub = 'Finances';
        
        $menu['finances'] = ' class="active"';
        
        $headerArr[0]['name'] = 'Finances';
        $headerArr[0]['link'] = 'finances';          
        
        $index = 1;        
        
        break;
    case 'university':
        
        $sub = 'University';
        
        $menu['university'] = ' class="active"';
        
        $headerArr[0]['name'] = 'University';
        $headerArr[0]['link'] = 'university';       
        
        if(isset($_GET['opt'])){
            $bodyClass .= ' certification';
            if(isset($_GET['learn']) || isset($_GET['page']) || isset($_GET['complete'])){
                $bodyClass .= ' learn';
                if(!isset($_GET['complete'])){
                    $css['fa'] = 1;
                }
            } else {
                $css['select2'] = 1;
            }
        } else {
            $bodyClass .= ' research';
            if(isset($_GET['id'])){
                $bodyClass .= ' selected'; 
            }
        }
        
        break;
    case 'ranking':

        $menu['ranking'] = ' class="active"';

        $sub = 'Ranking';

        if(isset($_GET['show'])){
            switch($_GET['show']){
                case 'clan':
                    $bodyClass .= ' r-clan';
                    break;
            }
        } else {
            $bodyClass .= ' r-user';
        }
        
        break;
        
    case 'news':

        $sub = 'News';
        
        $headerArr[0]['name'] = 'News';
        $headerArr[0]['link'] = 'news';          
        
        break;
    case 'log':

        $sub = 'Log File';
        
        $menu['log'] = ' class="active"';
        
        $headerArr[0]['name'] = 'Log File';
        $headerArr[0]['link'] = 'log';          
        
        $bodyClass = 'page-log';
        
        break;
    case 'fame':

        $sub = 'Hall of Fame';
                
        $menu['fame'] = ' class="active"';
        
        $headerArr[0]['name'] = 'Hall of Fame';
        $headerArr[0]['link'] = 'fame';        
        
        break;
    case 'doom':

        $sub = 'Doom';
        
        $menu['doom'] = ' class="active"';
        
        $headerArr[0]['name'] = 'Doom';
        $headerArr[0]['link'] = 'doom';
        
        break;
    case 'stats':
        
        $sub = 'Statistics';
                
        $headerArr[0]['name'] = 'Stats';
        $headerArr[0]['link'] = 'stats';
        
        $headerArr[1]['name'] = 'Current round stats';
        $headerArr[1]['link'] = 'stats';
        
        if(isset($_GET['round'])){
            if($_GET['round'] == 'all'){
                $headerArr[1]['name'] = 'All-time stats';
                $headerArr[1]['link'] = 'stats?round=all';
            }
        }
        
        break;
    case 'legal':
        
        $sub = 'Legal';
        
        $headerArr[0]['name'] = 'Legal';
        $headerArr[0]['link'] = 'legal';
                
        break;
    case 'premium':
        
        $sub = 'Premium';
        
        $headerArr[0]['name'] = 'Premium';
        $headerArr[0]['link'] = 'premium';
                
        if(isset($_GET['plan'])){
            $bodyClass .= ' payment';
        }
        
        break;
    case 'settings':
        
        $sub = 'Settings';
        
        $headerArr[0]['name'] = 'Settings';
        $headerArr[0]['link'] = 'settings';
        
        $css['select2'] = 1;
        
        break;
                    
}

//$_SESSION['MISSION_TYPE'] = 82;
if(isset($_SESSION['MISSION_ID'])){
    if($_SESSION['MISSION_TYPE'] >= 80){
        $bodyClass .= ' tutorial';
        $bodyClass .= ' '.$_SESSION['MISSION_TYPE'];
        
        switch($_SESSION['MISSION_TYPE']){
            case 80:
                
                $pdo = PDO_DB::factory();
                $_SESSION['QUERY_COUNT'] += 2;

                $sql = 'SELECT COUNT(*) AS total, software.id, COUNT(software_running.id) AS totalRunning
                        FROM software
                        LEFT JOIN software_running
                        ON software.id = software_running.softID
                        WHERE softType = \'1\' AND software.userID = \''.$_SESSION['id'].'\' AND software.isNPC = \'0\' 
                        LIMIT 1';
                $softInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                $sql = 'SELECT COUNT(*) AS total, id, isRead FROM mails WHERE mails.from = \'-1\' AND mails.to = \''.$_SESSION['id'].'\' LIMIT 1';
                $mailInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                if($mailInfo->isread == 1){

                    if($softInfo->totalrunning == 0){

                        if($crudePage != 'software'){

                            $bodyClass .= ' color';
                            $bodyClass .= ' menu-software';

                            if($crudePage == 'missions'){
                                $bodyClass .= ' action software';
                            }
                            
                        }                        

                    } else {

                        if($crudePage != 'internet'){

                            $bodyClass .= ' color';
                            $bodyClass .= ' menu-internet';

                        } else {

                            $session = new Session();

                            $session->newQuery();
                            $sql = 'SELECT victim FROM missions WHERE type = \'80\' AND userID = \''.$_SESSION['id'].'\'';
                            $netInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                            if($session->isInternetLogged()){

                                if(isset($_GET['view'])){
                                    if($_GET['view'] == 'logs'){
                                        $curPage = 'logs';
                                    } else {
                                        $curPage = 'software';
                                    }
                                } else {
                                    $curPage = $_SESSION['CUR_PAGE'];
                                }

                                if($curPage == 'logs' && $_SESSION['LOGGED_IN'] == $netInfo->victim){
                                    
                                    $session->newQuery();
                                    $sql = 'SELECT gameIP FROM users WHERE id = \''.$_SESSION['id'].'\'';
                                    $userIP = long2ip($pdo->query($sql)->fetch(PDO::FETCH_OBJ)->gameip);
                                                                        
                                    $session->newQuery();
                                    $sql = 'SELECT text FROM log WHERE isNPC = 1 AND userID = (
                                                SELECT id FROM npc WHERE npcIP = \''.$netInfo->victim.'\' LIMIT 1
                                            )';
                                    $logText = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->text;
                                    
                                    if(strpos($logText, $userIP) !== FALSE){
                                        $bodyClass .= ' remove-log';
                                    }

                                }                                
                                
                            } else {

                                if(isset($_GET['ip'])){
                                    $curIP = ip2long($_GET['ip']);
                                } elseif(isset($_SESSION['CUR_IP'])){
                                    $curIP = $_SESSION['CUR_IP'];
                                } else {
                                    $curIP = 0;
                                }

                                if($curIP == $netInfo->victim){

                                    $session->newQuery();
                                    $sql = 'SELECT COUNT(*) AS total FROM lists WHERE ip = \''.$netInfo->victim.'\' AND userID = \''.$_SESSION['id'].'\' LIMIT 1';
                                    $listed = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

                                    if($listed == 1){

                                        if(isset($_GET['action'])){
                                            if($_GET['action'] == 'login'){
                                                $bodyClass .= ' action login';
                                            } else {
                                                $bodyClass .= ' action tab-login';
                                            }
                                        } else {
                                            $bodyClass .= ' action tab-login';
                                        }
                                        
                                        

                                    } else {

                                        if(!isset($_GET['method'])){

                                            if(isset($_GET['action'])){
                                                if($_GET['action'] == 'hack'){
                                                    $bodyClass .= ' action hack';
                                                }
                                            } else {
                                                $bodyClass .= ' action tab-hack';
                                            }

                                        }

                                    }

                                } else {

                                    $bodyClass .= ' navigate';
                                    $bodyClass .= '" value="'.long2ip($netInfo->victim);

                                }

                            }

                        }

                    }
                
                }

                if($crudePage == 'mail'){
                
                    if(isset($_GET['id'])){
                    
                        if($mailInfo->total > 0){ //TODO2: verificar se a missão não foi completada

                            if($mailInfo->id == $_GET['id'] || 1==1){ //TODO: remover segunda cond.
                                
                                $bodyClass .= ' color';
                                $bodyClass .= ' menu-mission';
                                
                            }

                        }
                    
                    }

                } elseif($crudePage == 'software'){

                    if($softInfo->totalrunning == 0 && $_GET == NULL){

                        $bodyClass .= ' highlight" value="'.$softInfo->id;

                    }

                }
                
                break;
            case 81:
                
                if($crudePage != 'internet'){
                    
                    $bodyClass .= ' color menu-internet';
                    
                }
                
                break;
            case 82:
                
                if($crudePage != 'missions'){
                    
                    $bodyClass .= ' color menu-mission';
                    
                }
                
                break;
            case 83:
                
                if($crudePage != 'internet'){
                    $bodyClass .= ' color menu-internet';
                }
                
                $session->newQuery();
                $sql = 'SELECT victim FROM missions WHERE type = \'83\' AND userID = \''.$_SESSION['id'].'\'';
                $netInfo = $pdo->query($sql)->fetch(PDO::FETCH_OBJ);                
                
                if($session->isInternetLogged()){
                    
                    if(isset($_GET['view'])){
                        if($_GET['view'] == 'logs'){
                            $curPage = 'logs';
                        } else {
                            $curPage = 'software';
                        }
                    } else {
                        $curPage = $_SESSION['CUR_PAGE'];
                    }
                    
                    if($curPage == 'software'){
                        
                        
                        
                    } else {

                        $session->newQuery();
                        $sql = 'SELECT gameIP FROM users WHERE id = \''.$_SESSION['id'].'\'';
                        $userIP = long2ip($pdo->query($sql)->fetch(PDO::FETCH_OBJ)->gameip);

                        $session->newQuery();
                        $sql = 'SELECT text FROM log WHERE isNPC = 1 AND userID = (
                                    SELECT id FROM npc WHERE npcIP = \''.$netInfo->victim.'\' LIMIT 1
                                )';
                        $logText = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->text;

                        if(strpos($logText, $userIP) !== FALSE){
                            $bodyClass .= ' remove-log';
                        }
                        
                    }
                    
                } elseif($crudePage == 'internet') {
                    
                    if(isset($_GET['ip'])){
                        $curIP = ip2long($_GET['ip']);
                    } elseif(isset($_SESSION['CUR_IP'])){
                        $curIP = $_SESSION['CUR_IP'];
                    } else {
                        $curIP = 0;
                    }

                    if($curIP == $netInfo->victim){
                        
                        $session->newQuery();
                        $sql = 'SELECT COUNT(*) AS total FROM lists WHERE ip = \''.$netInfo->victim.'\' AND userID = \''.$_SESSION['id'].'\' LIMIT 1';
                        $listed = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

                        if($listed == 1){

                            if(isset($_GET['action'])){
                                if($_GET['action'] == 'login'){
                                    $bodyClass .= ' action login';
                                } else {
                                    $bodyClass .= ' action tab-login';
                                }
                            } else {
                                $bodyClass .= ' action tab-login';
                            }
                            
                            

                        } else {

                            if(!isset($_GET['method'])){

                                if(isset($_GET['action'])){
                                    if($_GET['action'] == 'hack'){
                                        $bodyClass .= ' action hack';
                                    }
                                } else {
                                    $bodyClass .= ' action tab-hack';
                                }

                            }

                        }                        
                        
                    } else {
                        
                        $bodyClass .= ' navigate" value="'.long2ip($netInfo->victim);
                        
                    }
                    
                }
                
                break;

        }
        
    }
}

$doomMenu = $label = '';

$session->newQuery();
$sql = 'SELECT COUNT(*) AS total FROM virus_doom WHERE status = 1 LIMIT 1';
$doomCurrent = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

$session->newQuery();
$sql = 'SELECT COUNT(*) AS total FROM virus_doom WHERE status = 2 LIMIT 1';
$doomFailed = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

if($doomCurrent + $doomFailed > 0){
    if($doomCurrent > 0){
        $label = '<span class="label">'.$doomCurrent.'</span>';
    }
    $doomMenu = '<li'.$menu['doom'].'><a href="doom"><i class="fa fa-bullseye" style="opacity: 1;"></i> <span>Doom!</span>'.$label.'</a></li>
'; //pra deixar o html bonitinho
}

$clanBadge = '';
if($_SESSION['CLAN_ID'] != 0){
    $session->newQuery();
    $sql = 'SELECT authLevel FROM clan_users WHERE userID = '.$_SESSION['id'];
    if($pdo->query($sql)->fetch(PDO::FETCH_OBJ)->authlevel == 4){
        $session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM clan_requests WHERE clanID = '.$_SESSION['CLAN_ID'];
        $total = $pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        if($total > 0){
            $clanBadge = '<span class="label">'.$total.'</span>';
        }
    }
    $session->newQuery();
    $sql = 'SELECT COUNT(*) AS total FROM clan_war WHERE clanID1 = '.$_SESSION['CLAN_ID'].' OR clanID2 = '.$_SESSION['CLAN_ID'];
    if($pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total > 0){
        if(strlen($clanBadge) > 0){
            $str = '!';
        } else {
            $str = 'War';
        }
        $clanBadge .= '<span class="label">'.$str.'</span>';
    }
}

//02-20 08:16 pm

$clock = date('Y-m-d H:i');

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo _($sub); ?> - Hacker Experience</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <link rel="stylesheet" href="css/bootstrap-responsive.min.css" />
        <link rel="stylesheet" href="css/he.css" />
        
<?php

if($css['select2'] == 1){
?>
        <link rel="stylesheet" href="css/select2.css" />
<?php
}

if($css['login'] == 1){
?>
        <link rel="stylesheet" href="css/he_login.css" />
<?php
}

if($css['uniform'] == 1){
?>
        <link rel="stylesheet" href="css/uniform.css" />
<?php
}

if($css['wysiwyg'] == 1){
?>
        <link rel="stylesheet" href="css/wysiwyg.css" />
<?php
}

if($css['fa'] == 1){
    //fa completo
?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" />
<?php
} else {
    //fa customizado (menu)
?>
        <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
<?php
}

?>
    </head>
    <body class="<?php echo $bodyClass; ?>">
        <div id="header">
            <h1><a href="#">Hacker Experience</a></h1>
        </div>
        <div id="user-nav" class="navbar navbar-inverse">
            <ul class="nav btn-group">
                <li class="btn btn-inverse<?php echo $nav['profile']; ?>"><a href="profile"><i class="fa fa-inverse fa-user"></i> <span class="text"><?php echo _("My Profile"); ?></span></a></li>
                <li class="btn btn-inverse<?php echo $nav['mail']; ?>"><a href="mail"><i class="fa fa-inverse fa-envelope"></i> <span class="text"><?php echo _("E-Mail"); ?></span> <span class="mail-unread"></span></a></li>
                <li class="btn btn-inverse<?php echo $nav['settings']; ?>"><a href="settings"><i class="fa fa-inverse fa-wrench"></i> <span class="text"><?php echo _("Settings"); ?></span></a></li>
                <li class="btn btn-inverse"><a href="logout"><i class="fa fa-power-off fa-inverse"></i> <span class="text"><?php echo _("Logout"); ?></span></a></li>
            </ul>
        </div>
        <span id="notify"></span>
        <div id="sidebar">
            <a href="#" class="visible-phone"><i class="fa fa-inverse fa-chevron-down"></i> <?php echo _($sub); ?></a>
            <ul>
                <li<?php echo $menu['index']; ?>><a href="index"><i class="fa fa-inverse fa-home"></i> <span><?php echo _("Home"); ?></span></a></li>
                <li<?php echo $menu['processes']; ?>><a href="processes"><i class="fa fa-inverse fa-tasks"></i> <span><?php echo _("Task Manager"); ?></span></a></li>
                <li id="menu-software"<?php echo $menu['software']; ?>><a href="software"><i class="fa fa-inverse fa-folder-open"></i> <span><?php echo _("Software"); ?></span></a></li>
                <li id="menu-internet"<?php echo $menu['internet']; ?>><a href="internet"><i class="fa fa-inverse fa-globe"></i> <span><?php echo _("Internet"); ?></span></a></li>
                <li<?php echo $menu['log']; ?>><a href="log"><i class="fa fa-inverse fa-book"></i> <span><?php echo _("Log File"); ?></span></a></li>
                <li<?php echo $menu['hardware']; ?>><a href="hardware"><i class="fa fa-inverse fa-desktop"></i> <span><?php echo _("Hardware"); ?></span></a></li>
                <li<?php echo $menu['university']; ?>><a href="university"><i class="fa fa-inverse fa-flask"></i> <span><?php echo _("University"); ?></span></a></li>
                <li<?php echo $menu['finances']; ?>><a href="finances"><i class="fa fa-inverse fa-briefcase"></i> <span><?php echo _("Finances"); ?></span></a></li>
                <li<?php echo $menu['list']; ?>><a href="list"><i class="fa fa-inverse fa-terminal"></i> <span><?php echo _("Hacked Database"); ?></span></a></li>
                <li id="menu-mission"<?php echo $menu['missions']; ?>><a href="missions"><i class="fa fa-inverse fa-building-o"></i> <span><?php echo _("Missions"); ?></span></a></li> 
                <li<?php echo $menu['clan']; ?>><a href="clan"><i class="fa fa-inverse fa-users"></i> <span><?php echo _("Clan"); ?></span><?php echo $clanBadge; ?></a></li>
                <li<?php echo $menu['ranking']; ?>><a href="ranking"><i class="fa fa-inverse fa-bars"></i> <span><?php echo _("Ranking"); ?></span></a></li>
                <li<?php echo $menu['fame']; ?>><a href="fame"><i class="fa fa-inverse fa-star"></i> <span><?php echo _("Hall of Fame"); ?></span></a></li>
                <?php echo $doomMenu; ?>
            </ul>
        </div>
        <div id="content">
            <div id="content-header">
                <h1><?php echo _($sub); ?></h1>
                <div class="header-ip hide-phone">
                    <div style="text-align: right;">
                        <span class="header-ip-show"></span>
                    </div>
                    <div class="header-info">
                        <div class="pull-right">
                            <span class="icon-tab he16-time" title="<?php echo _("Server Time"); ?>"></span> <span class="small nomargin" style="margin-right: 7px;"><?php echo $clock; ?></span>
                            <span class="online"></span>
                            <div class="reputation-info"></div><div class="finance-info"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="breadcrumb">
                <a href="index" title="Go to Home" class="tip-bottom"><i class="fa fa-home"></i> <?php echo _("Home"); ?></a>
<?php

                $size = sizeof($headerArr);
                
                $cur = '';
                for($i=0;$i<$size;$i++){
                    if($i == $size - 1){
                        $cur = 'class="current"';
                    }
?>                    
                <a href="<?php echo $headerArr[$i]['link']; ?>" id="link<?php echo $i; ?>" <?php echo $cur; ?>> <?php echo _($headerArr[$i]['name']); ?></a>
<?php
                }
      
?>
            </div>
            <div class="container-fluid">
                <div class="row-fluid">
