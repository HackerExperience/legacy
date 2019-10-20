<?php
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


// intantiate the IPN listener
include('ipnlistener.php');
$listener = new IpnListener();

// tell the IPN listener to use the PayPal test sandbox
$listener->use_sandbox = FALSE;

// try to process the IPN POST
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    trigger_error($e->getMessage());
    exit(0);
}

require '../config.php';
require '../classes/Session.class.php';
require '../classes/Player.class.php';
require '../classes/System.class.php';
require '../classes/Premium.class.php';

$session = new Session();
$system = new System();
$premium = new Premium();

if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST)){
    exit();
}

//if(!isset($_POST['id']) || !isset($_POST['fingerprint'])){
//    exit();
//}

$premium->debug();

$id = $_POST['custom'];
$error = FALSE;


//if(!PagarMe::validateFingerprint($id, $fingerprint)) {
//    //TODO: report (tentativa de roubo)
//    exit();
//}

//$status = $_POST['current_status'];

if($verified){
    
    if ($_POST['receiver_email'] != 'renatosmassaro@gmail.com' ) {
        $errorMsg= "'receiver_email' does not match: " . $_POST['receiver_email'];
    }

    $usuario_id = $_POST['custom'];
    
    if ( !is_numeric($usuario_id) || $usuario_id == 0 ) {
        $errorMsg = 'Invalid or unknown user ID';
    }
    
    $StatusTransacao = $_POST['payment_status'];
    $TransacaoID = $_POST['txn_id'];
    $TipoTransacao = $_POST['txn_type'];
    
    /* Dados do usu�rio */
    $CliNome = $_POST['first_name'] . $_POST['last_name'];
    $CliNomeEntrega = $_POST['address_name'];
    $CliEmail = $_POST['payer_email'];
    $CliEndereco = $_POST['address_street'];
    $CliCidade = $_POST['address_city'];
    $CliEstado = $_POST['address_state'];
    $CliCEP = $_POST['address_zip'];
    $CliPais = $_POST['address_country'];
    $CliPaisID = $_POST['address_country_code'];
    
    $Data = date('Y-m-d H:i:s', strtotime( $_POST['payment_date'] ) );
    
}

if($verified){   
    
    $premium->setAsPaid($id);
    
} else {    
    
    
    exit();
    if($error){
        $reason = $errorMsg;
    } else {
    
        if(isset($_POST['refuse_reason'])){
            $reason = $_POST['refuse_reason'];
        } else {
            $reason = '';
        }

    }
    
    $premium->refused($id, $reason);
    
}
