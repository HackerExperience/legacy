<?php

class Finances {
    
    private $pdo;
    private $software;
    private $hardware;
    private $player;
    private $log;
    
    private $session;
	
    public function __construct(){
        
        $this->pdo = PDO_DB::factory();
	$this->session = new Session();
        
    }
    
    public function isUserRegisteredOnBank($uid, $bankID){
        
	$this->session->newQuery();
        $sql = 'SELECT id FROM bankAccounts WHERE bankID = :bankID AND bankUser = :uid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':bankID' => $bankID, ':uid' => $uid));
        $data = $stmt->fetchAll(); 
        
        if(count($data) != '0'){
            return TRUE;
        } else {
            return FALSE;
        }
            
    }
    
    public function bitcoin_getValue(){
        
        $price = file_get_contents('https://blockchain.info/q/24hrprice');
                
        if(!is_numeric($price)){
            $price = (int)$price;
        }
        
        if($price <= 0){
            $price = 472;
        }
        
        return round($price);
        
    }
    
    public function bitcoin_getID(){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM npc WHERE npcType = 40 LIMIT 1";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;
        
    }
    
    public function bitcoin_sell($address, $amount, $rate, $acc){
        
        $this->session->newQuery();
        $sql = "UPDATE bitcoin_wallets 
                SET amount = amount - :amount
                WHERE address = :address";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $amount, ':address' => $address));
        
        self::addMoney(ceil($amount * $rate), $acc);
        
    }
    
    public function bitcoin_buy($address, $amount, $rate, $acc){
        
        $buy = self::debtMoney(ceil($amount * $rate), $acc);

	if(!$buy){
		//exit("Lala");	
		$this->system->handleError("Ops", "internet");
		exit();
	}

        $this->session->newQuery();
        $sql = "UPDATE bitcoin_wallets 
                SET amount = amount + :amount
                WHERE address = :address";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $amount, ':address' => $address));
        
    }
    
    public function getWalletInfo($btcID, $uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT address, bitcoin_wallets.key, amount FROM bitcoin_wallets WHERE npcID = '".$btcID."' AND userID = '".$uid."' LIMIT 1";        
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

    }
    
    public function bitcoin_runWallet($userID){
        
        $bitcoinID = self::bitcoin_getID();
                
        if($userID == $_SESSION['id']){
            $redirect = 'software';
            $internet = FALSE;
        } else {
            $redirect = 'internet';
            $internet = TRUE;
        }
        
        if(!self::userHaveWallet($userID, $bitcoinID)){
            $system = new System();
            $system->handleError('This user does not have a wallet.', $redirect);            
        }
        
        $walletInfo = self::getWalletInfo($bitcoinID, $userID);
        
        $npc = New NPC();
        $npcInfo = $npc->getNPCInfo($bitcoinID);
        $bitcoinLink = 'internet?ip='.long2ip($npcInfo->npcip);
        
?>
                    <div class="widget-box">
                        <div class="widget-title">
                            <span class="icon"><span class="he16-bank_bag"></span></span>
                            <h5><?php echo _('Wallet Information'); ?></h5>
                        </div>
                        <div class="widget-content padding center">
                            <strong><?php echo _('Public address'); ?></strong><br/>
                            <?php echo $walletInfo->address; ?><br/><br/>
                            <strong><?php echo _('Total bitcoins'); ?></strong><br/>
                            <?php echo $walletInfo->amount; ?> BTC
                            
                            <br/><br/>
                            <a href="<?php echo $bitcoinLink; ?>" class="btn btn-lg btn-danger"><?php echo _('Bitcoin Market'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="span3 center">
                    <br/>
                    <ul class="soft-but">
                        <li>
                            <a href=<?php echo $redirect; ?>>
                                <i class="icon-"><span class="he32-root"></span></i>
                                <?php echo _('Back to root'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php if($internet){ ?>
                </div>
            <?php } ?>
            <div class="nav nav-tabs" style="clear: both;">&nbsp;</div>
                

<?php
        
    }
    
    public function getWalletInfoByAddress($address){

        $this->session->newQuery();
        $sql = "SELECT address, bitcoin_wallets.key, amount
                FROM bitcoin_wallets
                WHERE address = :address
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':address' => $address));
        return $stmt->fetch(PDO::FETCH_OBJ);
        
    }
    
    public function getWalletKey($address){

        $this->session->newQuery();

        $sql = 'SELECT bitcoin_wallets.key FROM bitcoin_wallets WHERE address = :address LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':address' => $address));

        return $stmt->fetch(PDO::FETCH_OBJ)->key;
        
    }
    
    public function issetWallet($address){
        
        $this->session->newQuery();

        $sql = 'SELECT COUNT(*) AS total FROM bitcoin_wallets WHERE address = :address LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':address' => $address));
        
        if($stmt->fetch(PDO::FETCH_OBJ)->total > 0){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function bitcoin_createAcc($btcID){
        
        function generateRandomString($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            return $randomString;
        }

        $address = '1'.generateRandomString(33);
        $key = generateRandomString(64);
        
        $this->session->newQuery();
        $sql = "INSERT INTO bitcoin_wallets 
                    (address, userID, npcID, bitcoin_wallets.key, amount) 
                VALUES 
                    (:address, :userID, :npcID, :key, '1.3370000')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':address' => $address, ':userID' => $_SESSION['id'], ':npcID' => $btcID, ':key' => $key));
        
        $this->session->newQuery();
        $sql = 'INSERT INTO software (userID, softName, softVersion, softSize, softRam, softType) 
                VALUES (:userID, :softName, 10, 1, 0, 19)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $_SESSION['id'], ':softName' => 'wallet'));
        
    }
    
    public function bitcoin_transfer($addressFrom, $amount, $addressTo){
        
        $this->session->newQuery();
        $sql = "UPDATE bitcoin_wallets 
                SET amount = amount + :amount
                WHERE address = :address";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $amount, ':address' => $addressTo));
        
        if($addressFrom != ''){
        
            $this->session->newQuery();
            $sql = "UPDATE bitcoin_wallets 
                    SET amount = amount - :amount
                    WHERE address = :address";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':amount' => $amount, ':address' => $addressFrom));

        }
        
    }
    
    public function userHaveWallet($uid, $btcID){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM bitcoin_wallets WHERE npcID = '".$btcID."' AND userID = '".$uid."' LIMIT 1";
        if($this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total > 0){
            return TRUE;
        } else {
            return FALSE;
        }

    }
    
    public function issetBankAccount($bankAcc, $bankID){
        
	$this->session->newQuery();
        $sql = 'SELECT id FROM bankAccounts WHERE bankID = :bankID AND bankAcc = :bankAcc';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':bankID' => $bankID, ':bankAcc' => $bankAcc));
        $data = $stmt->fetchAll(); 
        
        if(count($data) != '0')
            return TRUE;
        else
            return FALSE;
        
    }
    
    public function setExpireDate($acc, $deleteIn){
        
        $this->session->newQuery();
        $sql = 'INSERT INTO bankaccounts_expire (accID, expireDate) VALUES (:acc, DATE_ADD(NOW(), INTERVAL \''.$deleteIn.'\' DAY))';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':acc' => $acc));
        
    }
    
    public function closeAccount($bankAcc){
        
        $this->session->newQuery();
        $sql = 'DELETE FROM bankAccounts WHERE bankAcc = \''.$bankAcc.'\' LIMIT 1';
        $this->pdo->query($sql);
        
    }
    
    public function changeAccountPassword($bankAcc){
        
        function randString2($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'){

            $str = '';
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
            return $str;

        }
        
        $pwd = randString2(6);
        
        $this->session->newQuery();
        $sql = 'UPDATE bankAccounts SET bankPass = \''.$pwd.'\' WHERE bankAcc = \''.$bankAcc.'\' LIMIT 1';
        $this->pdo->query($sql);
        
        return $pwd;
        
    }
    
    public function listBankAccounts($uid){
        
        $return = '';
        
	$this->session->newQuery();
        $sql = 'SELECT bankAcc, cash FROM bankAccounts WHERE bankUser = :uid ORDER BY cash DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid' => $uid));
        $info = $stmt->fetchAll();
                
        $total = sizeof($info);

        if($total != '0'){
            $return['exists'] = '1';
            
            for($g=0;$g<$total;$g++){
                
                $return[$g] = $info[$g]['bankacc'];
                $return['cash'][$g] = $info[$g]['cash'];
                
            }

            $return['total'] = $g;
            
        } else {
            $return['exists'] = '0';
            $return['total'] = '0';
        }        
        
        return $return;
                
    }
    
    public function bankAccountInfo($bankAcc){
        
	$this->session->newQuery();
        $sql = 'SELECT id, bankID, bankUser, cash FROM bankAccounts WHERE bankAcc = :bankAcc LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':bankAcc' => $bankAcc));
        $info = $stmt->fetchAll();

        
        if(count($info) != '0')
            $info['0']['exists'] = '1';
        else
            $info['0']['exists'] = '0';
        
        return $info;
        
    }
    
    public function getIDByBankAccount($bankAcc, $bankID){
        
        $return = '0';
        
        if(self::issetBankAccount($bankAcc, $bankID)){
            
            $this->session->newQuery();
            $sql = 'SELECT bankUser FROM bankAccounts WHERE bankID = :bankID AND bankAcc = :bankAcc';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':bankID' => $bankID, ':bankAcc' => $bankAcc));
            $info = $stmt->fetchAll(); 
            
            if(count($info) > '0'){
                $return = $info['0']['bankuser'];
            }
            
        }
        
        return $return;
        
    }
    
    public function getBankAccountInfo($uid, $bankID, $bankAcc = ''){
     
        $return = Array(
          'VALID_ACC' => '0',  
        );
        
        if($bankAcc == ''){

            if(self::isUserRegisteredOnBank($uid, $bankID)){

                $this->session->newQuery();
                $sql = 'SELECT bankAcc, bankPass, cash FROM bankAccounts WHERE bankID = :bankID AND bankUser = :uid LIMIT 1';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':bankID' => $bankID, ':uid' => $uid));
                $info = $stmt->fetchAll();

                $return = Array(

                    'VALID_ACC' => '1',
                    'BANK_ACC' => $info['0']['bankacc'],
                    'BANK_PASS' => $info['0']['bankpass'],
                    'CASH' => $info['0']['cash'],
                    'USER' => $uid,

                );

            }
        
        } else {
            
            $this->session->newQuery();
            $sql = 'SELECT COUNT(*) AS total, bankUser, bankPass, cash FROM bankAccounts WHERE bankAcc = :bankAcc LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':bankAcc' => $bankAcc));
            $info = $stmt->fetchAll();            
         
            if($info['0']['total'] == 1){

                $return = Array(

                    'VALID_ACC' => '1',
                    'BANK_ACC' => $bankAcc,
                    'BANK_PASS' => $info['0']['bankpass'],
                    'CASH' => $info['0']['cash'],
                    'USER' => $info['0']['bankuser'],

                );            
            
            }

        }

        return $return;
        
    }
    
    public function transferMoney($from, $to, $amount, $bankFrom, $bankTo, $userTo, $userIP){

        require '/var/www/classes/Storyline.class.php';
        $storyline = new Storyline();
        
        $storyline->safenet_monitorTransfers($amount, $userIP);

        $newAmount = $amount;
        
        //elimina-se amount da conta from:
        $this->session->newQuery();
        $sql = 'UPDATE bankAccounts SET cash = cash - :amount WHERE bankAcc = :from AND bankID = :bankFrom LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $amount, ':from' => $from, ':bankFrom' => $bankFrom));
        
        //adiciona-se amount na conta to:
        $this->session->newQuery();
        $sql = 'UPDATE bankAccounts SET cash = cash + :newAmount WHERE bankAcc = :to AND bankID = :bankTo LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':newAmount' => $newAmount, ':to' => $to, ':bankTo' => $bankTo));
        
        require_once '/var/www/classes/Ranking.class.php';
        $ranking = new Ranking();

        $ranking->updateMoneyStats('3', $newAmount, $userTo);
        
    }

    public function addMoney($amount, $bankAcc){
        
        $this->session->newQuery();
        $sql = 'UPDATE bankAccounts SET cash = cash+:amount WHERE bankAcc = :bankAcc LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $amount, ':bankAcc' => $bankAcc));
        
    }
    
    public function debtMoney($amount, $bankAccount){
        
        $uid = $_SESSION['id'];
                
        $accInfo = self::bankAccountInfo($bankAccount);
        
        if($accInfo['0']['exists'] == '0'){
            $accInfo = self::bankAccountInfo(self::getWealthiestBankAcc());
        }
        
        if($accInfo['0']['cash'] >= $amount){
            
            $newAmount = $accInfo['0']['cash'] - $amount;
            
        } else {
            
            $newAmount = '0';
            $remaining = $amount - $accInfo['0']['cash'];

            while($remaining > '0'){
                
                $this->session->newQuery();
                $sql = 'SELECT bankAcc, cash FROM bankAccounts WHERE bankUser = :uid AND cash <> 0 AND bankAcc <> :bankAccount LIMIT 1';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':uid' => $uid, ':bankAccount' => $bankAccount));
                $tmpAcc = $stmt->fetch(PDO::FETCH_OBJ); 

                if($tmpAcc->cash > $remaining){
                    
                    $newTmpAmount = $tmpAcc->cash - $remaining;
                    
                } else {
                    
                    $newTmpAmount = '0';
                    
                }
                
                $remaining -= $tmpAcc->cash;

                $this->session->newQuery();
                $sql = 'UPDATE bankAccounts SET cash = :newTmpAmount WHERE bankAcc = :tmpAccBankacc LIMIT 1';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':newTmpAmount' => $newTmpAmount, ':tmpAccBankacc' => $tmpAcc->bankacc));

            }
            
        }
        
        $this->session->newQuery();
        $sql = 'UPDATE bankAccounts SET cash = :newAmount WHERE bankAcc = :bankAccount LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':newAmount' => $newAmount, ':bankAccount' => $bankAccount));

	return TRUE;

	// exit(':(');
	// return FALSE;
                
    }
    
    private function setLastCollect($id){

        $curDate = date("Y-m-d H:i:s");

        $this->session->newQuery();
        $sql = 'UPDATE virus SET lastCollect = :curDate WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':curDate' => $curDate, ':id' => $id));

    }
    
    public function totalMoney($uid=''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT SUM(cash) AS total FROM bankAccounts WHERE bankUser = \''.$uid.'\'';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;

    }

    public function getBankIP($bankID){
        
        $this->session->newQuery();
        $sql = "SELECT npcIP FROM npc WHERE id = '".$bankID."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->npcip;
        
    }
    
    public function htmlSelectBankAcc($type = ''){
        
        $accInfo = self::listBankAccounts($_SESSION['id']);

        $return = '';
        
        if($accInfo['exists'] == '1'){
            
            $return .= '<select id="select-bank-acc" name="acc">';

            for($i=0;$i<$accInfo['total'];$i++){

                $bankAcc = $accInfo[$i];
                $bankCash = $accInfo['cash'][$i];

                $return .=  "<option value=\"$bankAcc\">#$bankAcc ($".number_format($bankCash, '0', '.', ',').")</option>";
                
            }

            $return .= '</select>';

        } else {
            $return .= _('Create one bank account before buying this certification');
        }        
        
        if($type == ''){
            echo $return;
        } else {
            return $return;
        }
        
    }
    
    public function getWealthiestBankAcc($userID=''){
        
        if($userID == ''){
            $userID = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT bankAcc FROM bankAccounts WHERE bankUser = '".$userID."' ORDER BY cash DESC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        return $data['0']['bankacc'];
        
    }
    
    private function getFirstBankID(){
        
        $this->session->newQuery();
        $sql = 'SELECT id FROM npc WHERE npcType = 1 LIMIT 1';
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;
        
    }
    
    public function createAccount($userID, $bankID = ''){
        
        if($bankID == ''){
            $bankID = self::getFirstBankID();
        }
        
        function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'){

            $str = '';
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
            return $str;

        }
        
        $acc = rand(111111111,999999999);
        $pass = randString(6);
        
        $this->session->newQuery();
        $sql = 'INSERT INTO bankAccounts (id, bankAcc, bankPass, bankID, bankUser, cash) 
                VALUES (\'\', :acc, :passInfo, :deepInfo, :SESSION, \'0\')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':acc' => $acc, ':passInfo' => $pass, ':deepInfo' => $bankID, ':SESSION' => $userID));

    }
    
    public function isPlayerAccount($accountID, $playerID = ''){
        
        if($playerID == ''){
            $playerID = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = "SELECT bankUser FROM bankAccounts WHERE bankAcc = '".$accountID."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            
            if($data['0']['bankuser'] == $playerID){
                return TRUE;
            }
            
        }
        
        return FALSE;
        
    }
    
  public function show_totalMoney(){
        
        ?>
                                <ul class="finance-box">
                                    <li>
                                        <div class="left"><span class="he32-money32"></span></div>
                                        <div class="right">
                                            <strong>$<?php echo number_format(self::totalMoney($_SESSION['id'])); ?></strong>
                                        </div>
                                    </li>
                                </ul>
        <?php
        
    }

    public function listFinances(){

        self::show_totalMoney();
        
        $id = $_SESSION['id'];

	$this->session->newQuery();
        $sqlSelect = "SELECT bankAcc, bankPass, bankID, cash FROM bankAccounts WHERE bankUser = $id ORDER BY bankID ASC";
        $bankInfo= $this->pdo->query($sqlSelect)->fetchAll();

        $totalAccs = sizeof($bankInfo);

        if($totalAccs > '0'){

            require '/var/www/classes/NPC.class.php';
            $npc = new NPC();
            
            $firstEmptyDiv = 0;
            $secondEmptyDiv = 0;

            switch($totalAccs){
                case 1:
                    $firstEmptyDiv = 4;
                    break;
                case 2:
                    $firstEmptyDiv = 2;
                    break;
                case 4:
                    $firstEmptyDiv = 2;
                    $secondEmptyDiv = 2;
                    break;
                case 5:
                    $secondEmptyDiv = 2;
                    break;
            }

            ?>
            
                                <div class="row-fluid">
                                    
                                    
            <?php

            if($firstEmptyDiv != 0){
                ?>

                                <div class="span<?php echo $firstEmptyDiv; ?>"></div>

                <?php
            }
            
            $toClose = 0;
            for($i = 0; $i < $totalAccs; $i++){

                $npcInfo = $npc->getNPCInfo($bankInfo[$i]['bankid'], $this->session->l);

                ?>
                                    
                                    <div class="span4">

                                        <div class="widget-box collapsible" style="text-align: left;">

                                            <div class="widget-title">
                                                <a href="#acc<?php echo $i + 1; ?>" data-toggle="collapse">
                                                    <span class="icon"><span class="he16-safe"></span></span>
                                                    <h5><span class="small1024"><?php echo _('Account'); ?> #<?php echo $i + 1; ?></span></h5>
                                                </a>
                                                <a href="internet?ip=<?php echo long2ip($npcInfo->npcip); ?>"><span class="label"><?php echo _('Access bank'); ?></span></a>
                                            </div>

                                            <div class="collapse in" id="acc<?php echo $i + 1; ?>">

                                                <div class="widget-content padding center">        

                                                    <strong><?php echo $npcInfo->name; ?></strong><br/><br/>
                                                    
                                                    <strong><?php echo _('Account Balance'); ?></strong><br/>
                                                    <span class="green">$<?php echo number_format($bankInfo[$i]['cash']); ?></span> at #<?php echo $bankInfo[$i]['bankacc']; ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    
                <?php
                
                if(($i == 2 && $totalAccs > 4) || ($i == 1 && $totalAccs == 4)){
                    
                    $toClose = 1;
                    
                    ?>

                                    <div class="row-fluid" style="text-align: center;">

                                        <div class="span12 center" style="text-align: left;">                                    
                                
                    <?php if($secondEmptyDiv > 0){ ?>

                                            <div class="span<?php echo $secondEmptyDiv; ?>"></div>

                    <?php }
                                        
                }
                
            }
            
            if($toClose == 1){
                
                ?>
                                        </div>
                                    </div>
                <?php
                
            }

?>
                            </div>                           
<?php
            
            $bitcoinID = self::bitcoin_getID();

            if(self::userHaveWallet($_SESSION['id'], $bitcoinID)){
                
                $npcInfo = $npc->getNPCInfo($bitcoinID, $this->session->l);
                $walletInfo = self::getWalletInfo($bitcoinID, $_SESSION['id']);
            
?>
                                
<div class="row-fluid">
    <div class="span4"></div>
                                    <div class="span4">
                                        <div class="widget-box collapsible" style="text-align: left;">

                                            <div class="widget-title">
                                                <a href="#btc" data-toggle="collapse">
                                                    <span class="icon"><span class="he16-bank_bag"></span></span>
                                                    <h5><span class="small1024"><?php echo _('BTC Wallet'); ?></span></h5>
                                                </a>
                                                <a href="internet?ip=<?php echo long2ip($npcInfo->npcip); ?>"><span class="label"><?php echo _('Access Market'); ?></span></a>
                                            </div>

                                            <div class="collapse in" id="btc">

                                                <div class="widget-content padding center">        

                                                    <strong><?php echo _('Public Address'); ?></strong><br/><?php echo $walletInfo->address; ?><br/><Br/>
                                                    
                                                    <strong><?php echo _('Bitcoins'); ?></strong><br/> <?php echo $walletInfo->amount; ?> BTC<br/>

                                                </div>

                                            </div>

                                        </div>
                                    </div>
</div>
<?php
            
            }
            
        } else {

            echo "You do not have a bank account.";

        }

    }
    
    
}



?>
