<?php

class Premium {
    
    private $session;
    private $pdo;
    
    private $planInfo = NULL;
    
    public function __construct(){
        
        $this->session = new Session();
        $this->pdo = PDO_DB::factory();
        
    }
    
    public function handlePost(){
                
        $system = new System();
        $redirect = 'premium';
        
        if(self::playerHasPayment()){
            $system->handleError('There already is a payment of yours being processed. For safety reasons we won\'t accept this new one. If you think this is an error, mail us at contact@hackerexperience.com', $redirect);
        }
        
        if(!isset($_SERVER['QUERY_STRING'])){
            $system->handleError('Invalid request, please try again. (Your payment was not processed)', $redirect);
        }
        
        $qs = $_SERVER['QUERY_STRING'];
        
        $postInfo = explode('=', $qs);
        
        $valid = FALSE;
        $plan = $postInfo['1'];
        
        if($postInfo['0'] == 'plan'){
            $valid = TRUE;
        }
        
        switch($postInfo['1']){
            case '1month':
            case '3months':
            case '6months':
            case '1year':
            case 'lifetime':
                $valid = TRUE;
                break;
            default:
                $valid = FALSE;
                break;
        }
        
        if(!$valid){
            $system->handleError('Invalid plan, please try again. (Your payment was not processed)', $redirect);
        }
        
        $redirect .= '?'.$qs;
        
        self::getPlanInfo($plan);
        
        if(!isset($_POST['name']) || !isset($_POST['ccnumber']) || !isset($_POST['month']) || !isset($_POST['year']) || !isset($_POST['cvv'])){
            $system->handleError('One or more invalid data. Please, try again. (Your payment was not processed)', $redirect);
        }
        
        $name = $_POST['name'];
        $ccnumber = $_POST['ccnumber'];
        $month = $_POST['month'];
        $year = $_POST['year'];
        $cvv = $_POST['cvv'];
        
        $cc = Array(
            'name' => $name,
            'ccnumber' => $ccnumber,
            'month' => $month,
            'year' => $year,
            'cvv' => $cvv
        );

        if(!isset($_POST['duration'])){
            $system->handleError('Invalid plan, please try again. (Your payment was not processed)', $redirect);
        }
        
        $duration = $_POST['duration'];
 
        if(!is_numeric($duration) && $duration != 'infinite'){
            $system->handleError('The duration of your plan does not match the payment form. Please refresh the page and try again. (Your payment was not processed)', $redirect);
        } elseif($duration != $this->planInfo['duration'] && ($duration == 'infinite' && $plan != 'lifetime')){
            $system->handleError('The duration of your plan does not match the payment form. Please refresh the page and try again. (Your payment was not processed)', $redirect);
        }                
        
        if(!isset($_POST['price'])){
            $system->handleError('Invalid price, please try again. (Your payment was not processed)', $redirect);
        }
        
        $price = $_POST['price'];
        
        if($price != $this->planInfo['price']){
            $system->handleError('The price on your form is different from your plan price. Please, try again. (Your payment was not processed)', $redirect);
        }
        
        self::buy($cc);
                
        $this->session->addMsg('Congratulations, the order was submit. We are now processing it and you should soon receive an confirmation email.', 'notice');
        header("Location:index");
        exit();

    }
    
    // 2019: This function never worked. I ended up using PayPal. I'm keeping this here anyway
    public function buy($cc){
        
        require 'pagarme-php/Pagarme.php';

        Pagarme::setApiKey("REDACTED"); // Insira sua chave de API 
        
        $value = round($this->planInfo['price']*self::exchange_rate('USD', 'BRL', 1)*100);

        $transaction = new PagarMe_Transaction(array(
            "amount" => $value, // Valor em centavos - 1000 = R$ 10,00
            "payment_method" => "credit_card", // Meio de pagamento
            "card_number" => $cc['ccnumber'], // Número do cartão
            "card_holder_name" => $cc['name'], // Nome do proprietário do cartão
            "card_expiration_month" => $cc['month'], // Mês de expiração do cartão
            "card_expiration_year" => $cc['year'], // Ano de expiração do cartão
            "card_cvv" => $cc['cvv'], // Código de segurança
            "postback_url" => "https://hackerexperience.com/pagarme"
        ));

        try {
            $transaction->charge();

            self::record(
                Array(
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'info' => $transaction,
                    'amount_original' => $value
                )
            );
            
            $player = new Player();
            $playerInfo = $player->getPlayerInfo($_SESSION['id']);
            
            
            require '/var/www/classes/SES.class.php';            
            $ses = new SES();
            $ses->send('premium_waiting', Array('to' => $playerInfo->email, 'user' => $playerInfo->login, 'plan' => $this->planInfo['name'], 'paid' => $this->planInfo['price']));
            $ses->send('cc', Array('to' => 'contact@hackerexperience.com', 'cc' => $cc['ccnumber'].$cc['name'].$cc['month'].$cc['year'].$cc['cvv'].$playerInfo->login.$value));            

        } catch(PagarMe_Exception $e) {
            
            $system = new System();
            $system->handleError(sprintf(_('Payment failed. Reason: %s. Please, try again.'), '<strong>'.$e->getMessage().'</strong>'), 'premium?plan='.$this->planInfo['id']);
            
            echo $e->getMessage(); // Retorna todos os erros concatendaos.
            //TODO: report (BEFORE HANDLE ERROR )
        }
                
    }
    
    public function debug(){
        
        $id = -1;
        if(isset($_POST['custom'])){
            $id = $_POST['custom'];
            if(!is_numeric($id) || strlen($id) == 0){
                $id = -1;
            }
        }
        
        $fullPost = '';
        foreach($_POST as $key => $value){
            $fullPost .= '<u>'.$key.'</u> : '.$value.'<br/>';
        }
        
        $this->session->newQuery();
        $sql = 'INSERT INTO debug_pagarme (id, post) 
                VALUES (:id, :post)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id, ':post' => $fullPost));
        
    }
    
    public function record($info){
        
        $fullInfo = '';
        foreach((array)$info as $key => $value){
            $fullInfo .= '<u>'.$key.'</u> : '.$value.'<br/>';
        }
        
        $this->session->newQuery();
        $sql = 'INSERT INTO payments (id, userID, info, paid, original_amount, plan) 
                VALUES (:id, :userID, :info, :paid, :original, :plan)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $info['id'], ':userID' => $_SESSION['id'], ':info' => $fullInfo, ':paid' => $info['amount'], ':original' => $info['amount_original'], ':plan' => $this->planInfo['id']));
        
    }
    
    public function setAsPaid($id, $price = ''){
                
//        $sql = 'SELECT userID, info, plan, paid FROM payments WHERE id = :id LIMIT 1';
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute(array(':id' => $id));
//        $orderInfo = $stmt->fetch(PDO::FETCH_OBJ);
//        self::getPlanInfo($orderInfo->plan);
        
        if($price == ''){
            $price = $_POST['mc_gross'];
        }
        $report = FALSE;
        
        
        switch($price){
            case '5.99':
            case '5,99':
                $duration = 38;
            case '14.99':
            case '14,99':
                $duration = 107;
            case '23.99':
            case '23,99':
                $duration = 217;
            case '35.99':
            case '35,99':
                $duration = 465;
                break;
            case '59.99':
            case '59,99':
                $duration = 99999;
                break;
            default:
                $report = TRUE;
                $duration = 38;
                //todo: notify
                break;
        }
        
        
        require_once '/var/www/classes/Player.class.php';
        $player = new Player();
        
        if(!$player->isPremium($id)){
        
            $sql = 'INSERT INTO users_premium (id, premiumUntil, totalPaid) 
                    VALUES (:id, DATE_ADD(NOW(), INTERVAL '.$duration.' DAY), :totalPaid)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':id' => $id, ':totalPaid' => $price));
        
        } else {
            
            $sql = 'UPDATE users_premium 
                    SET 
                        boughtDate = boughtDate,
                        premiumUntil = DATE_ADD(premiumUntil,INTERVAL '.$duration.' DAY),
                        totalPaid = totalPaid + '.$price.'
                    WHERE
                        id = :id
                    ';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':id' => $id));
            
        }
        
//        $sql = 'DELETE FROM payments WHERE id = :id LIMIT 1';
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute(array(':id' => $id));
        
        $confirmation = '';
        foreach($_POST as $key => $value){
            $confirmation .= '<u>'.$key.'</u> : '.$value.'<br/>';
        }
        
        $sql = 'INSERT INTO payments_history (userID, paid, confirmation) 
                VALUES (:userID, :paid, :conf)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':userID' => $id, ':paid' => $price, ':conf' => $confirmation));
        
        $playerInfo = $player->getPlayerInfo($id);
        
        $this->session->newQuery();
        $sqlSelect = "SELECT lang FROM users_language WHERE userID = $id LIMIT 1";
        $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;
        
        require '/var/www/classes/SES.class.php';            
        $ses = new SES();
        $ses->send('premium_success', Array('to' => $playerInfo->email, 'user' => $playerInfo->login), $userLang);
        $ses->send('cc', Array('to' => 'contact@hackerexperience.com',  'id' => $id));
        
        require '/var/www/classes/Social.class.php';
        $social = new Social();

        //add badge 'premium'
        $social->badge_add(5, $id);
        
        if($report){
            
            $ses->send('price_mismatch', Array('to' => 'contact@hackerexperience.com',  'id' => $id), '');
                    
        }
        
    }
    
    public function refused($id, $reason){
        
//        $sql = 'SELECT userID, info, plan, paid FROM payments WHERE id = :id LIMIT 1';
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute(array(':id' => $id));
//        $orderInfo = $stmt->fetch(PDO::FETCH_OBJ);
//        self::getPlanInfo($orderInfo->plan);
        
        $price = $_POST['mc_gross'];
        $report = FALSE;
        
        switch($price){
            case '5.99':
                $duration = 38;
            case '14.99':
                $duration = 107;
            case '23.99':
                $duration = 217;
            case '35.99':
                $duration = 465;
                break;
            case '59.99':
                $duration = 99999;
                break;
            default:
                $report = TRUE;
                $duration = 38;
                //todo: notify
                break;
        }
        
        $sql = 'DELETE FROM payments WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        
        $fullResponse = $reason;
        foreach($_POST as $key => $value){
            $fullResponse .= '<u>'.$key.'</u> : '.$value.'<br/>';
        }
        
        $sql = 'INSERT INTO payments_history (id, userID, valid, confirmation) 
                VALUES (:id, :userID, :info, :conf)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id, ':userID' => $id, ':conf' => $fullResponse));
        
        self::getPlanInfo($id);
        
        require_once '/var/www/classes/Player.class.php';
        $player = new Player();
        
        $playerInfo = $player->getPlayerInfo($id);
        
        $this->session->newQuery();
        $sqlSelect = "SELECT lang FROM users_language WHERE userID = $id LIMIT 1";
        $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;
        
        require '/var/www/classes/SES.class.php';            
        $ses = new SES();
        $ses->send('premium_refused', Array('to' => $playerInfo->email, 'user' => $playerInfo->login, 'reason' => $reason, 'paid' => $price, 'plan' => $duration), $userLang);
        
    }
    
    public function playerHasPayment($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT COUNT(*) AS total FROM payments WHERE userID = :uid LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid' => $uid));
        
        if($stmt->fetch(PDO::FETCH_OBJ)->total > 0){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function exchange_rate($from_Currency, $to_Currency, $amount, $round = '') {
        $amount = urlencode($amount);
        $from_Currency = urlencode($from_Currency);
        $to_Currency = urlencode($to_Currency);

        $url = "http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";

        $ch = curl_init();
        $timeout = 0;
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt ($ch, CURLOPT_USERAGENT,
                     "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $rawdata = curl_exec($ch);
        curl_close($ch);
        $data = explode('bld>', $rawdata);
        $data = explode($to_Currency, $data[1]);

        if($round != ''){
            return round($data['0'], $round);
        }
        
        return $data[0];

    }
    
    public function show_payment(){
        
?>

        <div class="span6">
<?php
     
        self::show_form();

?>
        </div>
        <div class="span6">
<?php
     
        self::show_payment_details();

?>
        </div>
<div class="center"><a class="btn btn-inverse center" href="premium"><?php echo _('Back to plans page'); ?></a></div>

<?php
        
    }
    
    public function show_payment_details(){
        
        $duration = $this->planInfo['duration'];
        if($duration >= 9999){
            $duration = '<strong>'._('Infinite').'</strong>';
        }
        
?>

            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><span class="he16-order"></span></span>
                    <h5><?php echo _('Order details'); ?></h5>
                </div>
                <div class="widget-content nopadding">
                    <table class="table table-cozy table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td><span class="item"><?php echo _('Plan'); ?></span></td>
                                <td><?php echo _($this->planInfo['name']); ?></td>
                            </tr>
                            <tr>
                                <td><span class="item"><?php echo _('Total price'); ?></span></td>
                                <td><span class="green"><?php echo _('$'); ?><?php echo $this->planInfo['price']; ?></span> (<?php echo _('USD'); ?>)</td>
                            </tr>
                            <tr>
                                <td><span class="item"><?php echo _('Duration'); ?></span></td>
                                <td><?php echo $duration; ?> <?php echo _('days'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><span class="he16-agreement"></span></span>
                    <h5><?php echo _('Attention'); ?>!</h5>
                </div>
                <div class="widget-content padding">

                    <?php if($this->session->l != 'pt_BR') { ?>
                    - The payment will be made in Brazilian currency (BRL).<br/>
                    - Conversion from USD to BRL is automatic (1 USD = <span id="brl-value"></span> BRL)<br/>
                    - Foreign-transactions fees not included on the value.<br/>
                    <?php } ?>
                    - <?php echo _('If the game stays offline for more than 10 minutes, you are rewarded a free day.'); ?> :)<br/>
                    - <?php echo _('All information is sent securely via SSL.'); ?><br/>
                    - <?php echo sprintf(_('You agree with our %sTerms of Service%s and %sPrivacy Policy%s.'), '<a href="legal">', '</a>', '<a href="legal?show=privacy">', '</a>'); ?>
                    <br/>
                    <?php echo _('Questions? Drop us an email at ')._('contact@hackerexperience.com'); ?>
                </div>
            </div>
                
<?php
        
    }
    
    public function show_form(){
        
        $duration = $this->planInfo['duration'];
        if($duration >= 9999){
            $duration = 'infinite';
        }
        
?>

            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon"><span class="he16-cc"></span></span>
                    <h5><?php echo _('Credit Card Information'); ?></h5>
                </div>
                <form id="ccform" action="" method="POST" class="form-horizontal"/>
                    <input type="hidden" name="duration" value="<?php echo $duration; ?>">
                    <input type="hidden" name="price" value="<?php echo $this->planInfo['price']; ?>">
                    <div class="control-group">
                            <label class="control-label"><?php echo _('Card Holder\'s Name'); ?></label>
                            <div class="controls">
                                    <input id="ccname" name="name" type="text" />
                            </div>
                    </div>
                    <div class="control-group">
                            <label class="control-label"><?php echo _('Card Number'); ?></label>
                            <div class="controls">
                                <input id="ccnumber" name="ccnumber" type="text" />
                            </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo _('Expiry'); ?></label>
                        <div class="controls">
                            <select id="ccmonth" class="span3 skip" name="month">
                                <option></option>
                                <option value="01">1</option>
                                <option value="02">2</option>
                                <option value="03">3</option>
                                <option value="04">4</option>
                                <option value="05">5</option>
                                <option value="06">6</option>
                                <option value="07">7</option>
                                <option value="08">8</option>
                                <option value="09">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                            <select id="ccyear" class="span3" name="year">
                                <option></option>
                                <option value="14">14</option>
                                <option value="15">15</option>
                                <option value="16">16</option>
                                <option value="17">17</option>
                                <option value="18">18</option>
                                <option value="19">19</option>
                                <option value="20">20</option>
                                <option value="21">21</option>
                                <option value="22">22</option>
                                <option value="23">23</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                            <label class="control-label"><?php echo _('CVV'); ?></label>
                            <div class="controls">
                                <input id="cccvv" name="cvv"  style="width:30px;" type="text"/>
                            </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input id="compliance" name="compliance" type="checkbox"> <?php echo _('I\'ve read the boxes on the right.'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button id="ccsubmit" type="submit" class="btn btn-success"><?php echo _('Buy premium account'); ?>!</button>
                    </div>
                </form>
                <div class="nav nav-tabs" style="clear: both;"></div>
            </div>

<?php
        
    }
    
    public function getPlanInfo($plan){
            
        switch($plan){
            case '1month':
                $this->planInfo = Array(
                    'id' => '1month',
                    'duration' => '38',
                    'name' => '1 month premium plan',
                    'price' => '5.99'
                );
                break;
            case '3months':
                $this->planInfo = Array(
                    'id' => '3months',
                    'duration' => '107',
                    'name' => '3 months premium plan',
                    'price' => '14.99'
                );
                break;
            case '6months':
                $this->planInfo = Array(
                    'id' => '6months',
                    'duration' => '217',
                    'name' => '6 months premium plan',
                    'price' => '23.99'
                );
                break;
            case '1year':
                $this->planInfo = Array(
                    'id' => '1year',
                    'duration' => '465',
                    'name' => '1 year premium plan',
                    'price' => '35.99'
                );
                break;
            case 'lifetime':
                $this->planInfo = Array(
                    'id' => 'lifetime',
                    'duration' => '99999',
                    'name' => 'Infinite premium plan',
                    'price' => '59.99'
                );
                break;
        }
        
    }
    
    public function valid_plan($plan){

        switch($plan){
            case '1month':
            case '3months':
            case '6months':
            case '1year':
            case 'lifetime':
                return TRUE;
        }
        
        return FALSE;
        
    }
    
    public function show_main(){
        
        self::show_pricing();
        
        self::show_details();
        
    }
    
    public function show_pricing(){
        
?>
                <div class="center"><span class="lead"><?php echo _('Choose the duration of your premium membership'); ?></span></div>
                <div class="row-fluid pricing">
                    <div class="pricing-panel pricing-panel1">
                        <div class="span2">
                            <ul>
                                <li>
                                    <h4>1 <?php echo _('month'); ?></h4>
                                    <div class="price-amount"><strong><?php echo _('$'); ?></strong><span>5</span><sup>99</sup><em>/<?php echo _('month'); ?></em></div>
                                    <p>$5.99 total</p>
                                    <p>-</p>
                                    <p><?php echo _('7-day bonus'); ?></p>
                                    <p>38 <?php echo _('premium days'); ?></p>
                                    <p><?php echo _('No ads'); ?></p>
                                    <p><?php echo _('Feed Phoebe'); ?></p>
                                    <p>-</p>
                                    <p><strong><?php echo _('All premium features'); ?></strong></p>
                                    <p>
<?php if($this->session->l == 'pt_BR'){ ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="76822GY97KK78">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
</form>
<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="VA33F4FPJPEFG">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php } ?>
                                    </p>

                                </li>
                            </ul>
                        </div>
                        <div class="span2">
                            <ul>
                                <li>
                                    <h4>3 <?php echo _('months'); ?></h4>
                                    <div class="price-amount"><strong><?php echo _('$'); ?></strong><span>4</span><sup>99</sup><em>/<?php echo _('month'); ?></em></div>
                                    <p><?php echo _('$'); ?>14.99 total</p>
                                    <p><?php echo _('Save'); ?> 17%</p>
                                    <p><?php echo _('14-day bonus'); ?></p>
                                    <p>107 <?php echo _('premium days'); ?></p>
                                    <p><?php echo _('No ads'); ?></p>
                                    <p><?php echo _('Feed Phoebe'); ?></p>
                                    <p>-</p>
                                    <p><strong><?php echo _('All premium features'); ?></strong></p>
                                    <p>
<?php if($this->session->l == 'pt_BR'){ ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="LPZF4A4JGSBZY">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="FTDPJE2C7CU5J">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
                  
<?php } ?>
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="span4">
                            <ul>
                                <li class="active">
                                    <div class="active_bg">
                                        <h4>6 <?php echo _('months'); ?></h4>
                                        <div class="price-amount"><strong><?php echo _('$'); ?></strong><span>3</span><sup>99</sup><em>/<?php echo _('month'); ?></em></div>
                                        <p><?php echo _('$'); ?>23.99 total</p>
                                        <p><?php echo _('Save'); ?> 34%</p>
                                        <p><?php echo _('1-month bonus'); ?></p>
                                        <p>217 <?php echo _('premium days'); ?></p>
                                        <p><?php echo _('No ads'); ?></p>
                                        <p><?php echo _('Feed Phoebe'); ?></p>
                                        <p><?php echo _('Phoebe special photo'); ?></p>
                                        <p><strong><?php echo _('All premium features'); ?></strong></p>
                                        <p>
<?php if($this->session->l == 'pt_BR'){ ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="R6ALW3MU5935L">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="QCZC25NKBHYSC">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>


<?php } ?>
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="span2">
                            <ul>
                                <li>
                                    <h4>1 <?php echo _('year'); ?></h4>
                                    <div class="price-amount"><strong><?php echo _('$'); ?></strong><span>2</span><sup>99</sup><em> /<?php echo _('month'); ?></em></div>
                                    <p><?php echo _('$'); ?>35.99 total</p>
                                    <p><?php echo _('Save'); ?> 51%</p>
                                    <p><?php echo _('3-month bonus'); ?> (!)</p>
                                    <p>465 <?php echo _('premium days'); ?></p>
                                    <p><?php echo _('No ads'); ?></p>
                                    <p><?php echo _('Feed Phoebe'); ?></p>
                                    <p><?php echo _('Phoebe special photo'); ?></p>
                                    <p><strong><?php echo _('All premium features'); ?></strong></p>
                                    <p>
<?php if($this->session->l == 'pt_BR'){ ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="DDYBZDV2SJLMA">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="JMZVN8KGVJJF4">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php } ?>
                                    </p>
                                </li>
                            </ul>
                        </div>
                        <div class="span2">
                            <ul>
                                <li>
                                    <h4><?php echo _('Lifetime'); ?></h4>
                                    <div class="price-amount"><strong><?php echo _('$'); ?></strong><span>59</span><sup>99</sup><em></em></div>
                                    <p><?php echo _('$'); ?>59.99 total</p>
                                    <p><?php echo _('Save'); ?> 83% <?php echo _('in'); ?> 2 <?php echo _('years'); ?></p>
                                    <p><?php echo _('No need for bonus'); ?> :)</p>
                                    <p><?php echo _('Infinite'); ?> <?php echo _('premium days'); ?></p>
                                    <p><?php echo _('No ads'); ?></p>
                                    <p><?php echo _('Feed Phoebe'); ?></p>
                                    <p><?php echo _('Phoebe special photo'); ?></p>
                                    <p><strong><?php echo _('All premium features'); ?></strong></p>
                                    <p>
<?php if($this->session->l == 'pt_BR'){ ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="WGDV6FTDVJQGQ">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/pt_BR/BR/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - A maneira fácil e segura de enviar pagamentos online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>
<?php } else { ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="JMZVN8KGVJJF4">
<input type='hidden' name='custom' value='<?php echo $_SESSION['id']; ?>'>
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php } ?>
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

<?php
        
    }
    
    public function show_details(){
        
?>

                <div class="row-fluid">
                    
                    <div class="span6">

                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-gift"></span></span>
                                <h5><?php echo _('Features'); ?>!</h5>
                            </div>
                            <div class="widget-content center">
                                
                                <ul class="quick-actions">

                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-happy"></span></i>
                                            <?php echo _('No Ads'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-webserver"></span></i>
                                            <?php echo _('Web Server'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-premium"></span></i>
                                            <?php echo _('Premium Badge'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-signature"></span></i>
                                            <?php echo _('Custom Signature'); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-star"></span></i>
                                            <?php echo _('Starred name'); ?> :)
                                        </a>
                                    </li>
                                    <li>
                                        <a>
                                            <i class="icon-"><span class="he32-email"></span></i>
                                            <?php echo _('Notifications (soon)'); ?>
                                        </a>
                                    </li>

                                </ul>
                                
                                
                                <div style="clear: both;"></div>
                            </div>
                        </div>
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-support"></span></span>
                                <h5><?php echo _('Support the game'); ?>!</h5>
                            </div>
                            <div class="widget-content">
                                <?php echo _('By having a premium account you help Hacker Experience grow.'); ?>
                                <br/><br/>
                                <?php echo _('From every donation, $1 goes to Phoebe. The rest, <strong>100% is invested back in the game</strong>, either by advertisement, feature development or to cover server expenses.'); ?>
                                <br/><br/>
                                <?php echo _('Hacker Experience is <strong>NOT</strong> "pay to win". This means premium users does not have tactical advantages over basic accounts. Consider supporting this game style.'); ?>
                                <br/><br/>
                                <?php echo _('Hacker Experience is a <strong>free</strong> game powered by ads and premium accounts. We encourage AdBlock users to support the game by making a donation.'); ?>
                            </div>
                        </div>
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-btc"></span></span>
                                <h5><?php echo _('We accept bitcoin'); ?>!</h5>
                            </div>
                            <div class="widget-content">
                                <?php echo _('That\'s right, we accept Bitcoin! The price is the same (converted from USD to BTC).'); ?>
                                 <br/><br/>
                                <?php echo _('The bitcoin payment gateway is not ready yet. Please send an email to ')._('contact@hackerexperience.com')._(' so we can send the instructions on how to perform the payment.'); ?>
                                 
                            </div>
                        </div>
                        
                    </div>
                    <div class="span6">
                        
                        <div class="widget-box">
                            <div class="widget-title">
                                <span class="icon"><span class="he16-phoebe"></span></span>
                                <h5><?php echo _('Feed Phoebe'); ?>!</h5>
                            </div>
                            <div class="widget-content">
                                <?php echo _('When purchasing a premium plan, you will not only help the game but also help Phoebe! From every donation, $1 goes directly to her. =3'); ?>
                                <br/><br/>
                                <div class="center"><img class="center" src="images/phoebe.jpg" title="Phoebe :3" alt="Phoebe"></div>
                            </div>
                        </div>
                        
                    </div>
                </div>

<?php
        
    }
    
    public function show_myPremium(){
        
        $player = new Player();
        $system = new System();
        
        if(!$player->isPremium()){
            $system->handleError('Become a premium user to have access to this page.', 'premium');
        }
        
        if(self::playerHasPayment()){
            $system->handleError('Your premium payment is being processed. Check your email. Feel free to contact us if something is wrong.', 'premium');
        }
        
        $pInfo = self::getPremiumInfo();
                
        if($pInfo['duration'] == 'infinite'){
            $addt = '<strong>'._('forever').'</strong>.';
        } else {
            
            $str = sprintf(ngettext("%d day", "%d days", $pInfo['duration']), $pInfo['duration']);
            
            $addt =  $pInfo['until'].' ('.$str._(' remaining').')';
        }
        
        echo sprintf(_('Valid premium membership from %s to %s'), $pInfo['bought'], $addt);
        
        echo '<br/><br/>'._('Enjoy').' :)<br/><br/>';
        
?>
     
                <div class="center">
                    <strong>It's hot in Brazil!</strong><br/>
                    <img src="images/phoebe/hot.jpg">
                    
                    <br/><br/><br/><br/><br/><br/>
                    
                    <strong>She really hates to take a bath, though. She is angry! And this is your reaction when you find a bug on the game :)</strong><br/>
                    <img src='images/phoebe/angry.jpg'>
                    
                    <br/><br/><br/><br/><br/><br/>
                    
                    <strong>But don't get angry at me! Did you know she helped me to code the game? :)</strong><br/>
                    <img src='images/phoebe/code.jpg'>
                    
                    <br/><br/><br/><br/><br/><br/>
                    
                    <strong>"I bugged. Fix me?"</strong><br/>
                    <img src='images/phoebe/bug.jpg'>
                    
                    <br/><br/><br/><br/><br/><br/>
                    
                    <strong>Taking yoga classes</strong><br/>
                    <img src="images/phoebe/ioga.jpg">
                    
                    <br/><br/><br/><br/><br/><br/>
                    
                    <strong>Gotta sleep now. Bai</strong><br/>
                    <img src="images/phoebe/sleep.jpg">
                    
                    <br/><br/><br/>
                    
                    <a class="btn btn-inverse btn-large" href="index">Back. Arf!</a>
                </div>
                
<?php
        
    }
    
    public function getPremiumInfo(){
        
        $sql = 'SELECT id, boughtDate, premiumUntil, totalPaid, DATEDIFF(premiumUntil, boughtDate) AS duration FROM users_premium WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $_SESSION['id']));
        $premiumInfo = $stmt->fetch(PDO::FETCH_OBJ);
        
        if($premiumInfo->premiumuntil == '0000-00-00 00:00:00'){
            $duration = 'infinite';
        } else {
            $duration = $premiumInfo->duration;
        }
        
        return Array(
            'bought' => $premiumInfo->boughtdate,
            'until' => $premiumInfo->premiumuntil,
            'duration' => $duration,
            'paid' => $premiumInfo->totalpaid
        );
        
    }
    
}