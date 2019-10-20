<?php

class System {

    private $pdo;
    private $session;
    
    function __construct(){

        //

    }
    
    public function changeHTML($id, $content){
        
        ?>
        
        <script>
        document.getElementById("<?php echo $id; ?>").innerHTML="<?php echo $content; ?>";
        </script>

        <?php

    }
    
    function issetGet($get){

        if(isset($_GET[$get])){

            if(!empty($_GET[$get])){
                return TRUE;
            }
                
        }
        
        return FALSE;


    }

    public function switchGet($get, $a, $b, $c = '', $d = '', $e = '', $f = '', $g = '', $h = '', $i = ''){

        $args = func_get_args();

        $tries = '0';

        for($i=0;$i<func_num_args();$i++){

            if($_GET[$get] == $args[$i]){

                $return = Array(
                    'ISSET_GET' => '1',
                    'GET_NAME' => $get,
                    'GET_VALUE' => $args[$i],
                );

                return $return;

            }

            $tries++;

        }

        if($tries == func_num_args()){

            $return = Array(
                'ISSET_GET' => '0',
                'GET_NAME' => '',
                'GET_VALUE' => '',
            );

        }

        return $return;

    }

    public function verifyNumericGet($get){
        
        if(self::issetGet($get)){
            
            $value = (int)$_GET[$get];
            
            if(is_int($value) && strlen($value != '0')){

                $return = Array(
                    'IS_NUMERIC' => '1',
                    'GET_VALUE' => $value,
                );

            } else {

                $return = Array(
                    'IS_NUMERIC' => '0',
                    'GET_VALUE' => '',
                );

            }

            return $return;

        } else {

            return FALSE;

        }

    }

    public function verifyStringGet($get){

        if(self::issetGet($get) && strlen($_GET[$get]) != '0'){

            $return = Array(
                'IS_NUMERIC' => '0',
                'GET_VALUE' => $_GET[$get],
            );

        } else {

            $return = Array(
                'IS_NUMERIC' => '',
                'GET_VALUE' => '',
            );

        }

        return $return;

    }

    public function handleError($error, $redirect = ''){

        switch($error){

            case '':
                $msg = '';
                break;
            case 'INVALID_GET':
                $msg = 'Invalid page';
                break;
            case 'INVALID_ID':
                $msg = 'Invalid ID';
                break;
            case 'WRONG_PASS':
                $msg = 'Ops! Wrong password.';
                break;
            case 'NO_PERMISSION':
                $msg = 'You do not have permission to perform this.';
                break;
            case 'NO_INSTALLER':
                $msg = 'Cant find doom installer.';
                break;
            case 'NO_COLLECTOR':
                $msg = 'You do not have a virus collector.';
                break;
            case 'DOWNGRADE':
                $msg = 'Downgrade hardware is not possible.';
                break;
            case 'NOT_LISTED':
                $msg = 'This IP is not listed on your hacked database.';
                break;
            case 'INEXISTENT_SERVER':
                $msg = 'This server does not exists.';
                break;
            case 'INEXISTENT_IP':
                $msg = 'This IP does not exists.';
                break;
            case 'INVALID_IP':
                $msg = 'This IP is invalid.';
                break;
            case 'BAD_XHD':
                $msg = 'Your external hd does not support this software.';
                break;
            case 'BAD_MONEY':
                $msg = 'You do not have enough money to complete this action.';
                break;
            case 'NO_LICENSE':
                $msg = 'You do not have the license to research this software.';
                break;
            case 'NO_CERTIFICATION':
                $msg = 'You do not have the needed certification in order to perform this action.';
                break;
            case 'NOT_INSTALLED':
                $msg = 'This software is not installed.';
                break;
            case 'CANT_UINSTALL':
                $msg = 'You can not uninstall this software.';
                break;
            case 'INSUFFICIENT_RAM':
                $msg = 'There isnt enough RAM to complete this action.';
                break;
            case 'ALREADY_INSTALLED':
                $msg = 'This software is already installed.';
                break;
            case 'NOT_EXECUTABLE':
                $msg = 'This software is not executable.';
                break;
            case 'VIRUS_ALREADY_INSTALLED':
                $msg = 'You already have installed a virus of this type on this computer.';
                break;
            case 'NO_SEEKER':
                $msg = 'You have no seeker.';
                break;
            case 'NO_HAVE_SOFT':
                $msg = 'You do not have this software.';
                break;
            case 'SOFT_HIDDEN':
                $msg = 'This software is hidden.';
                break;      
            case 'SOFT_ALREADY_HAVE':
                $msg = 'You already have this software.';
                break;            
            case 'INEXISTENT_SOFTWARE':
                $msg = 'This software does not exists.';
                break;            
            case 'CANT_DELETE':
                $msg = 'You can not delete this software.';
                break;
            case 'ALREADY_LISTED':
                $msg = 'You already have this IP on your hacked database.';
                break;
            case 'NO_SOFT':
                $msg = 'You do not have the needed software to perform this action.';
                break;          
            case 'NO_NMAP_VICTIM':
                $msg = 'Remote client does not have NMAP software.';
                break;
            case 'BAD_ACC':
                $msg = 'Invalid bank account.';
                break;
            case 'INEXISTENT_ACC':
                $msg = 'This bank account does not exists.';
                break;
            case 'BAD_BANK':
                $msg = 'This IP is not a bank.';
                break;
            case 'AUTO_TRANSFER':
                $msg = 'Well, you can not transfer money to yourself.';
                break;
            case 'BANK_NOT_LOGGED':
                $msg = 'You are not logged into a bank account.';
                break;
            case 'NO_AMOUNT':
                $msg = 'Amount of money is zero.';
                break;
            case 'BAD_CRACKER':
                $msg = 'Access denied: your cracker is not good enough.';
                break;
            case 'DOWNLOAD_INSUFFICIENT_HD':
                $msg = 'You need more disk space in order to download this software.';
                break;
            case 'UPLOAD_INSUFFICIENT_HD':
                $msg = 'There is not enough disk space to upload this software.';
                break;
            case 'UPLOAD_SOFT_ALREADY_HAVE':
                $msg = 'The remote client already have this software.';
                break;        
            case 'HIDE_INSTALLED_SOFTWARE':
                $msg = 'Cant hide an installed software.';
                break;
            case 'BAH_BAD_CRACKER':
                $msg = 'Access denied: your software can not crack this account.';
                break;
            case 'BAH_ALREADY_CRACKED':
                $msg = 'You already have this account listed.';
                break;
            case 'HXP_BAD_EXP':
                $msg = 'Access denied: your exploit is not good enough.';
                break;
            case 'HXP_NO_EXP':
                $msg = 'You do not have any running exploits.';
                break;
            case 'NO_FTP_EXP':
                $msg = 'You do not have a running FTP exploit.';
                break;
            case 'NO_SSH_EXP':
                $msg = 'You do not have a running SSH exploit.';
                break;
            case 'HXP_NO_SCAN':
                $msg = 'You do not have any running scanner.';
                break;
            case 'IPRESET_NO_TIME':
                $msg = 'You can not reset your IP now.';
                break;
            case 'PẂDRESET_NO_TIME':
                $msg = 'You can not change your password now.';
                break;
            case 'DOOM_CLAN_ONLY':
                $msg = 'Can only install doom on your clan server.';
                break;
            case 'FOLDER_INEXISTENT_SOFTWARE':
                $msg = 'This software is not on this folder.';
                break;
            case 'FOLDER_ALREADY_HAVE':
                $msg = 'This software is already in a folder.';
                break;
            case 'FOLDER_INEXISTENT':
                $msg = 'This folder does not exists.';
                break;
            case 'PROC_NOT_FOUND':
                $msg = 'Process not found.';
                break;
            case 'PROC_ALREADY_PAUSED':
                $msg = 'This process is already paused.';
                break;
            case 'PROC_NOT_PAUSED':
                $msg = 'This process is not paused.';
                break;
            case 'PROC_ALREADY_COMPLETED':
                $msg = 'This process is already completed.';
                break;
            default:
                $msg = $error;
                break;

        }

        if($msg != ''){

            if($this->session == NULL){
                $this->session = new Session();
            }

            $this->session->addMsg($msg, 'error');

        }

        if($redirect != ''){

            header("Location:$redirect");
            exit();        
  
        }

    }
    
    public function validate($var, $type){
        
        switch($type){
            
            case 'ip':
            case 'IP':
                
                //ipv4
                return filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);                
                
            case 'hintip':
                
                //XXX.XXX.XXX.XXX
                return preg_match('/^[0-9.xX]{7,15}$/', $var);
                
            case 'user':
            case 'username':
                
                //<caracter> ou ._-
                return preg_match('/^[a-zA-Z0-9_.-]{1,15}$/', $var);
            
            case 'soft':
            case 'software':
               
                //<caracter>+(<caracter> ou _-<espaço>) (não permite ponto)
                return preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_ -]{1,}$/", $var);
                
            case 'subject':
                
                //<caracter>+(<caracter> ou _!,-$.<espaço>)
                return preg_match("/^[a-zA-Z0-9áÁÀàóÓêÊõíÍúÚçÇñã][a-zA-Z0-9çÇáÁÀàóÓêÊõíÍúÚñã.,_$!?():'\" -]{1,}$/", $var);
                
            case 'text': 
                //<caracter>+(<caracter> ou _!,-$.<espaço>@#$%*(()+={}<>)
                return preg_match("/^[a-zA-Z0-9áÁÀàóÓêÊõíÍúÚñãçÇ][a-zA-Z0-9çÇáÁÀàóÓêÊõíÍúÚñã.,_$!()'\"@#%*+={}<> -?!]{1,}$/", $var);
                
            case 'email':
                
                return filter_var($var, FILTER_VALIDATE_EMAIL);
                
            case 'clan_name':
                
                return preg_match("/^[a-zA-Z0-9áÁÀàóÓêÊõíçÇÍúÚñã][a-zA-Z0-9áÁÀàóÓêÊçÇõíÍúÚñã_.! -]{1,}$/", $var);
                
            case 'clan_tag':
                
                return preg_match('/^[a-zA-Z0-9_.-]{1,3}$/', $var);
                
            case 'qa-answer':
                
                return preg_match('/^[a-zA-Z0-9öéáóíõçáÁÀàóÓêÊõíÍúÚñãÇ ,.=+-\/*]{1,}$/', $var);
                
            case 'pricing_plan':
                
                return preg_match('/^[a-zA-Z0-9]{1,}$/', $var);
                
                
        }
        
        echo 'Undefined type';
        
    }

}

?>
