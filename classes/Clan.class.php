<?php

require_once '/var/www/classes/PDO.class.php';

class Clan {
    
    private $pdo;
    private $session;
    public $player;
    
    private $clanID;
    private $canJoin;
    
    private $clanStats;
    private $clanInfo;
    private $userClan;
    
    private $warStartDate;
    private $warMembersInvolved;
    
    public $myClan;
    
    function __construct(){
        
        $this->pdo = PDO_DB::factory();

        $this->session = new Session();
        $this->player = new Player();
        
    }
    
    public function handlePost(){
        
        $system = new System();

        if(isset($_POST['act'])){
            
            $act = $_POST['act'];
            
            $postRedirect = 'clan';
            
            switch($act){
                case 'img':
                    $type = 'img';
                    break;
                case 'kick':
                    
                    $redirect = 'clan?action=admin';
                    $postRedirect = 'clan?action=admin';
                    $type = 'kick';

                    $id = '';
                    if(isset($_POST['id'])){
                        if(is_int((int)$_POST['id'])){
                            $id = (int)$_POST['id'];
                            $redirect = 'clan?action=admin&opt=manage&id='.$id.'&do=kick';
                        }
                    }

                    if(!isset($_POST['kickConfirm'])){
                        $type = 'noop';
                        $postRedirect = 'clan?action=admin&opt=manage&id='.$id.'&do=kick';
                    } else {
                        
                        if(!isset($_POST['pass'])){
                            //$system->handleError('Missing information.', $redirect);
                            $pass = 'facebook';
                        } else {
                            $pass = $_POST['pass'];
                        }
                        
                        if($pass == ''){
                            $pass = 'facebook';
                        }
                        
                        if(strlen($pass) == 0){
                            $system->handleError('Missing information.', $redirect);
                        }
                        
                    }                                       
                    
                    if(!isset($_POST['text']) || (!isset($_POST['id']))){
                        $system->handleError('Missing information.', $redirect);
                    }

                    require '/var/www/classes/Purifier.class.php';
                    $purifier = new Purifier();
                    $purifier->set_config('text');
                    
                    $txt = $purifier->purify($_POST['text']);
                    
                    if(strlen($txt) == 0 || strlen($id) == 0){
                        $system->handleError('Missing information.', $redirect);
                    }                    

                    if(!is_numeric((int)$id)){
                        $system->handleError('INVALID_ID', $postRedirect);
                    }

                    break;
                case 'request':
                    
                    $redirect = 'clan';
                    
                    $type = 'request';
                    
                    if(isset($_POST['text'])){
                        
                        require '/var/www/classes/Purifier.class.php';
                        $purifier = new Purifier();
                        $purifier->set_config('text');
                        
                        $msg = $purifier->purify($_POST['text']);
                        
                    } else {
                        $msg = '';
                    }
                    
                    if(!isset($_POST['clanID'])){
                        $system->handleError('Invalid page.', $redirect);
                    }
                    
                    $clanID = (int)$_POST['clanID']; 
                    
                    if(strlen($clanID) == 0 || !is_int($clanID)){
                        $system->handleError('Invalid page.', $redirect);
                    } elseif(!self::issetClan($clanID)){
                        $system->handleError('This clan does not exists.', $redirect);
                    } else {
                        $redirect = 'clan?id='.$clanID;
                    }
                                        
                    break;
                case 'join':
                case 'reject':
                    
                    $postRedirect = 'clan?action=admin';
                    $type = $act;
                    $redirect = 'clan?action=admin';
                    
                    if(!isset($_POST['req'])){
                        $system->handleError('Invalid page.', $redirect);
                    }
                    
                    $req = (int)$_POST['req'];
                    
                    if(!is_int($req)){
                        $system->handleError('INVALID_ID', $redirect);
                    }
                    
                    break;
                case 'create':
                    
                    $postRedirect = 'clan';
                    $type = 'create';
                    $redirect = 'clan?action=create';
                    
                    if(!isset($_POST['ctag']) || (!isset($_POST['cname']) || (!isset($_POST['acc'])))){
                        $system->handleError('Missing information.', $redirect);
                    }
                    
                    $ctag = htmlspecialchars($_POST['ctag']);
                    $cname = htmlspecialchars($_POST['cname']);
                    $acc = $_POST['acc'];
                    
                    if(strlen($acc) == 0 || strlen($cname) == 0 || strlen($ctag) == 0){
                        $system->handleError('Missing information.', $redirect);
                    }
                    
                    if(!ctype_digit($acc)){
                        $system->handleError('Invalid bank account.', $redirect);
                    }
                    
                    break;
                case 'leave':
                    
                    $type = 'leave';
                    $redirect = 'clan?action=leave';
                    $postRedirect = 'clan';
                    
                    if(isset($_POST['text'])){
                        
                        require '/var/www/classes/Purifier.class.php';
                        $purifier = new Purifier();
                        $purifier->set_config('text');
                        
                        $txt = $purifier->purify($_POST['text']);
                    } else {
                        $txt = '';
                    }
                    
                    if(!isset($_POST['pass'])){
                        $system->handleError('Missing information.', $redirect);
                    }
                    
                    $pass = $_POST['pass'];
                    
                    if(strlen($pass) == 0){
                        //$system->handleError('Missing information.', $redirect);
                        $pass = 'facebook';
                    }
                    
                    break;
                case 'edit-desc':
                    
                    $type = 'edit-desc';
                    $redirect = 'clan?action=admin';
                    $postRedirect = 'clan';
                    
                    if(!isset($_POST['desc-text'])){
                        $system->handleError('Missing information.', $redirect);
                    }
                    
                    require '/var/www/classes/Purifier.class.php';
                    $purifier = new Purifier();
                    $purifier->set_config('clan-desc');

                    $text = $purifier->purify($_POST['desc-text']);

                    if(strpos($text, 'img')){

 
                        if(!strpos($text, '<img src="../images/emoticons/')){
                            
                            $purifier = new Purifier();
                            $purifier->set_config('text');
                            $text = nl2br($purifier->purify($text));
                            
                        }

                    }

                    break;
                default:
                    exit();
            }

        }
        
        switch($type){
            
            case 'noop':
                return;
                break;
            case 'join':
                
                if(!self::playerHaveClan()){
                    $system->handleError('What? Ahm? You do not have a clan...', $redirect);
                }
                
                $adminClan = self::getPlayerClan($_SESSION['id']);
                
                if(self::getUserAuthAndHierarchy($_SESSION['id'])->authlevel != 4){
                    $system->handleError('You do not have the permission to add a user to the clan.', 'clan');
                }
                
                $requestData = self::getRequestInfo($req);
                
                if($requestData['ISSET'] == 0){
                    $system->handleError('This request is invalid.', $redirect);
                }
                
                if($requestData['CLAN_ID'] != $adminClan){
                    $system->handleError('Please, stop.', $redirect);
                }
                
                if(self::playerHaveClan($requestData['USER_ID'])){
                    $system->handleError('The user you are trying to add is already part of a clan.', $redirect);
                }
                
                self::acceptRequest($req, $requestData);
                
                $this->session->addMsg('The user has been added to your clan. Welcome him to the other members!', 'success');
                
                require '/var/www/classes/Mail.class.php';
                $mail = new Mail();

                $this->session->newQuery();
                $sqlSelect = "SELECT lang FROM users_language WHERE userID = ".$requestData['USER_ID']." LIMIT 1";
                $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;

                if($userLang == 'en'){
                    $subject = 'Request to join clan accepted.';
                    $text = 'Hey there, this is a message informing that your pending clan request was accepted.';
                } elseif($userLang == 'br'){
                    $subject = 'Pedido para participar de clan aceito.';
                    $text = 'Olá, essa é uma mensagem informando que seu recente pedido para participar de um clan foi aceito.';
                }
                
                $mail->newMail($requestData['USER_ID'], $subject, $text, 2, -4);
                                
                
                break;   
            case 'reject':
                
                if(!self::playerHaveClan()){
                    $system->handleError('What? Ahm? You do not have a clan...', $redirect);
                }
                
                $adminClan = self::getPlayerClan($_SESSION['id']);
                
                if(self::getUserAuthAndHierarchy($_SESSION['id'])->authlevel != 4){
                    $system->handleError('You do not have the permission to reject a request', 'clan');
                }                                
                
                $requestData = self::getRequestInfo($req);
                
                if($requestData['ISSET'] == 0){
                    $system->handleError('This request is invalid.', $redirect);
                }
                
                if($requestData['CLAN_ID'] != $adminClan){
                    $system->handleError('Please, stop.', $redirect);
                }                
                
                self::deleteRequest($req);

                $this->session->addMsg('Request denied.', 'success');
                
                $this->session->newQuery();
                $sqlSelect = "SELECT lang FROM users_language WHERE userID = ".$requestData['USER_ID']." LIMIT 1";
                $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;
                
                require '/var/www/classes/Mail.class.php';
                $mail = new Mail();

                if($userLang == 'br'){
                    $subject = 'Pedido para participar de clan negado.';
                    $text = 'Olá, essa é uma mensagem informando que seu recente pedido para participar de um clan foi negado.';
                } else {
                    $subject = 'Request to join clan denied.';
                    $text = 'Hey there, this is a message informing that your recent clan request was denied.';
                }
                
                $mail->newMail($requestData['USER_ID'], $subject, $text, 3, -4);          
                
                break;                
            case 'img':
                
                if(self::getUserAuthAndHierarchy($_SESSION['id'])->authlevel != 4){
                    $system->handleError('You do not have the permission to edit clan image', 'clan');
                }                
                
                require 'uploadImage.php';
                
                $this->session->addMsg('Clan photo updated.', 'success');
                
                break;
            case 'kick':

                $pdo = PDO_DB::factory();

                $sql = "SELECT password FROM users WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':id' => $_SESSION['id']));
                $data = $stmt->fetch(PDO::FETCH_OBJ);

                require '/var/www/classes/BCrypt.class.php';
                $bcrypt = new BCrypt();     
                
                if($bcrypt->verify($pass, $data->password) || $data->password == '0' || $data->password == ''){

                    if(!self::playerHaveClan()){
                        $system->handleError('What? Ahm? You do not have a clan...', $redirect);
                    }

                    $adminClanID = self::getPlayerClan($_SESSION['id']); 
                    
                    if(self::getUserAuthAndHierarchy($_SESSION['id'])->authlevel != 4){
                        $system->handleError('You do not have the permission to kick a user', 'clan');
                    }
                    
                    if(!self::playerHaveClan($id)){
                        $system->handleError('The user you are trying to kick is not part of any clan', $postRedirect);
                    }
                    
                    $kickedClanID = self::getPlayerClan($id);
                    
                    if($adminClanID != $kickedClanID){
                        $system->handleError('Please, stop.', $redirect);
                    }
                    
                    self::kick($id);
                    
                    $this->session->addMsg('User kicked.');

                    $this->session->newQuery();
                    $sqlSelect = "SELECT lang FROM users_language WHERE userID = ".$id." LIMIT 1";
                    $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;
                    
                    if($userLang == 'br'){
                        $subject = 'Kickado do clan.';
                        $text = 'Olá, essa é uma mensagem informando que você foi kickado do clan. <br/><br/><strong>Motivo:</strong> '.htmlspecialchars($txt);
                    } else {
                        $subject = 'Kicked from clan.';
                        $text = 'Hello, this is a message informing that you were kicked from the clan. <br/><br/><strong>Reason:</strong> '.htmlspecialchars($txt);
                    }
                    
                    require '/var/www/classes/Mail.class.php';
                    $mail = new Mail();

                    $mail->newMail($id, $subject, $text, 4, -4);

                } else {
                    $system->handleError('Invalid password.', $redirect);
                }

                break;
            case 'request':
                
                if(self::playerHaveClan()){
                    $system->handleError('You already are in a clan', $redirect);
                }
                
                if(self::playerHavePendingRequest()){
                    $system->handleError('You already have a pending request. Delete that one before.', $redirect);
                }
                
                self::join_request($clanID, $msg, $_SESSION['id']);
                                
                $this->session->addMsg('Request sent.', 'success');

                break;
            case 'create':
                
                if(self::playerHaveClan()){
                    $system->handleError('You already are in a clan', $redirect);
                }
                
                if(!$system->validate($cname, 'clan_name')){
                    $system->handleError('Your clan name contain invalid characters. Allowed are <strong>azAZ09_ .!-</strong> starting with <strong>azAZ09</strong>.', $redirect);
                }
                
                if(!$system->validate($ctag, 'clan_tag')){
                    $system->handleError('Your clan tag contain invalid characters. Allowed are <strong>azAZ09_.-</strong>', $redirect);
                }
                
                $price = self::createClanCost();
                
                require '/var/www/classes/Finances.class.php';
                $finances = new Finances();

                if($finances->totalMoney() < $price){
                    $system->handleError('BAD_MONEY', $redirect);
                }        

                $accInfo = $finances->bankAccountInfo($acc);

                if($accInfo['0']['exists'] == '0'){
                    $system->handleError('BAD_ACC', $redirect);
                }        

                $accInfo = $finances->getBankAccountInfo($_SESSION['id'], '', $acc);

                if($accInfo['VALID_ACC'] == 0 || $accInfo['USER'] != $_SESSION['id']){
                    $system->handleError('BAD_ACC', $redirect);
                }             

                if(self::playerHavePendingRequest()){
                    $system->handleError('You must delete your pending requests before creating a clan.', 'clan');
                }
                
                $this->session->newQuery();
                $sql = "SELECT COUNT(*) AS total FROM clan WHERE name = :cname OR nick = :cnick";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':cname' => $cname, ':cnick' => $ctag));
                $total = $stmt->fetch(PDO::FETCH_OBJ)->total;
                
                if($total == 1){                    
                    $system->handleError('This clan name or tag is already in use. Please choose another.', $redirect);                    
                }
                                
                self::createClan($cname, $ctag, $acc);
                
                $this->session->addMsg('Your clan was created.', 'success');
                
                break;
            case 'leave':
                
                $pdo = PDO_DB::factory();

                $sql = "SELECT password FROM users WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':id' => $_SESSION['id']));
                $data = $stmt->fetch(PDO::FETCH_OBJ);

                require '/var/www/classes/BCrypt.class.php';
                $bcrypt = new BCrypt();

                if($bcrypt->verify($pass, $data->password) || $data->password == '' || $data->password == '0'){
                    
                    if(!self::playerHaveClan()){
                        $system->handleError('You are not part of any clan, how can you leave?', $postRedirect);
                    }
                    
                    $auth = self::getUserAuthAndHierarchy()->authlevel;
                    
                    if($auth != 4){

                        $clanOwnerID = self::getClanOwnerID(self::getPlayerClan())->userid;
                        
                        $this->session->newQuery();
                        $sqlSelect = "SELECT lang FROM users_language WHERE userID = ".$clanOwnerID." LIMIT 1";
                        $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;

                        $player = new Player();
                        
                        if($userLang == 'br'){
                            $subject = 'Membro saiu do clan.';
                            $text = 'Olá, esse é um aviso informando que o jogador '. $player->getPlayerInfo($_SESSION['id'])->login .' saiu do clan. <br/><br/><strong>Motivo:</strong> '.$txt;
                        } else {
                            $subject = 'Clan member has left.';
                            $text = 'Hello, this is a notice to inform you that player '. $player->getPlayerInfo($_SESSION['id'])->login .' left the clan. <br/><br/><strong>Reason:</strong> '.$txt;
                        }
                        
                        require '/var/www/classes/Mail.class.php';
                        $mail = new Mail();

                        
                        
                        $mail->newMail($clanOwnerID, $subject, $text, 5, -4);

                    }
                    
                    self::leave($txt, $auth);
                    
                    $this->session->addMsg('You left the clan.', 'notice');

                    
                } else {
                    $system->handleError('Invalid password.', $redirect);
                }
                
                break;
            case 'edit-desc':
                
                if(!self::playerHaveClan()){
                    $system->handleError('What? Ahm? You do not have a clan...', 'clan');
                }
                
                $adminClan = self::getPlayerClan($_SESSION['id']);
                
                if(self::getUserAuthAndHierarchy($_SESSION['id'])->authlevel != 4){
                    $system->handleError('You do not have permission to edit the clan description.', 'clan?action=admin');
                }
                
                self::editClanSig($text, $adminClan);
                
                $this->session->addMsg('Clan description edited.', 'notice');
                
                break;
            default:
                die("erro");
                break;
            
        }
        
        header("Location:".$postRedirect);
        exit();
        
    }

    public function editClanSig($text, $clanID){
               
        $this->session->newQuery();
        $sql = "UPDATE clan SET clan.desc = ? WHERE clanID = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($text, $clanID));
        
    }
    
    public function playerAuth($uid=''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT authLevel FROM clan_users WHERE userID = :uid LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':uid' => $uid));
        $data = $stmt->fetchAll();

        if(count($data) == 1){
            return $data['0']['authlevel'];
        }
        
    }
    
    public function playerHaveClan($uid=''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }

        $this->session->newQuery();
        $sql = 'SELECT clanID FROM clan_users WHERE userID = :uid LIMIT 1';
        $sdata = $this->pdo->prepare($sql);
        $sdata->execute(array(':uid' => $uid)); 
        $data = $sdata->fetchAll();
        
        
        if(count($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function playerHavePendingRequest($uid = ''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT id FROM clan_requests WHERE userID = :uid LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':uid' => $uid)); 
        $cdata = $data->fetchAll();        
        
        if(count($cdata) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function clanHavePendingRequest($cid){
        
        $this->session->newQuery();
        $sql = 'SELECT id FROM clan_requests WHERE clanID = :cid LIMIT 1';
        $sdata = $this->pdo->prepare($sql);
        $sdata->execute(array(':cid' => $cid));
        $cdata = $sdata->fetchAll(); 
        
        if(count($cdata) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }    
    
    public function issetClan($clanID){
        
        $this->session->newQuery();
        $sql = 'SELECT clanID FROM clan WHERE clanID = :clanID LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID));
        $cdata = $data->fetchAll(); 
        
        if(count($cdata) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function view_search(){
        
        ?>

<div class="widget-box">

        <div class="widget-title">
            <span class="icon"><span class="he16-search"></span></span>
            <h5><?php echo _('Search for a clan'); ?></h5>
        </div>

        <div class="widget-content padding">        

            <?php echo _('Search'); ?>: <input type="text" id="search" autocomplete="off" placeholder="<?php echo _('Enter clan name'); ?>">
            <br/>
            <span id="results-text" class="small"><?php echo _('Showing results for'); ?>: <b id="search-string"></b></span>
            <ul id="results" class="list"></ul>
        
        </div>

</div>
        
<script>
window.onload = function(){
    
    function search() {

        var query_value = $('input#search').val();
        $('b#search-string').html(query_value);
        if(query_value !== ''){
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'searchClan', query: query_value },
                cache: false,
                success: function(data){
                    $("ul#results").html(data.msg);
                }
            });
        }return false;    
    }

    $("input#search").on("keyup", function(e) {

        clearTimeout($.data(this, 'timer'));

        var search_string = $(this).val();

        if (search_string == '') {
            $("ul#results").fadeOut();
            $('span#results-text').fadeOut();
        }else{
            $("ul#results").fadeIn();
            $('span#results-text').fadeIn();
            $(this).data('timer', setTimeout(search, 100));
        };

    });

};
</script>
        
</div>
<div class="span4">
    <div class="widget-box">

            <div class="widget-title">
                <span class="icon"><span class="he16-list"></span></span>
                <h5><?php echo _('Most viewed clans'); ?></h5>
            </div>

            <div class="widget-content padding">        

            <?php

                self::mostViewedClans();

            ?>


                
            </div>

    </div>    
    
        
        <?php        
        
    }
    
    public function mostViewedClans(){
  
        $this->session->newQuery();
        $sql = 'SELECT clan_stats.members, clan.name, clan.nick, clan.power, clan.clanID, ranking_clan.rank
                FROM clan_stats
                INNER JOIN clan
                ON clan_stats.cid = clan.clanID
                LEFT JOIN ranking_clan
                ON clan_stats.cid = ranking_clan.clanID
                ORDER BY clan_stats.pageClicks DESC
                LIMIT 3';
        $data = $this->pdo->query($sql);
        
        while($clanInfo = $data->fetch(PDO::FETCH_OBJ)){
            
?>
    
                <ul class="list">
                    <a href="clan?id=<?php echo $clanInfo->clanid; ?>">
                        <li  class="li-click">
                            <div class="span2 hard-ico">
                                <img src="images/clan/<?php echo md5($clanInfo->name.$clanInfo->clanid); ?>.jpg" width="38" height="38"><br/>
                            </div>
                            <div class="span10">
                                <div class="list-ip">
                                    [<?php echo $clanInfo->nick; ?>] <?php echo $clanInfo->name; ?>
                                </div>
                                <div class="list-user">
                                    <span class="he16-reputation" style="margin-top: 4px;"></span>
                                    <small><?php echo number_format($clanInfo->power); ?></small>
                                    <span class="he16-ranking" style="margin-top: 4px;"></span>
                                    <small>#<?php echo $clanInfo->rank; ?></small>
                                   <span class="he16-clan_members" style="margin-top: 4px;"></span>
                                    <small><?php echo $clanInfo->members; ?> <?php echo _('members'); ?></small>
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                        </li>
                    </a>
                </ul>
    
<?php    
            
        }                   
        
    }
    
    public function getClanInfo($cid){
                
        $this->session->newQuery();
        $sql = 'SELECT 
                    clan.name, clan.clanID AS cid, clan.nick, hist_clans_current.reputation AS power, clan.clanIP, 
                    clan_stats.won, clan_stats.lost, clan_stats.pageClicks AS clicks, ranking_clan.rank, 
                    (   SELECT COUNT(*) 
                        FROM clan_users
                        WHERE clan_users.clanID = :cid
                    ) AS members
                FROM clan
                INNER JOIN clan_stats
                ON clan_stats.cid = clan.clanID
                LEFT JOIN ranking_clan
                ON clan.clanID = ranking_clan.clanID
                LEFT JOIN hist_clans_current
                ON hist_clans_current.cid = clan.clanID
                WHERE clan.clanID = :cid 
                LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':cid' => $cid));
        
        return $data->fetch(PDO::FETCH_OBJ); 
        
    }
    
    public function getClanOwnerID($cid){
        
        $this->session->newQuery();
        $sql = 'SELECT userID FROM clan_users WHERE clanID = :cid AND authLevel = \'4\' LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':cid' => $cid));
        
        return $data->fetch(PDO::FETCH_OBJ); 
        
    }
    
    public function getClanSig($cid){

        $this->session->newQuery();
        $sql = 'SELECT clan.desc FROM clan WHERE clanID = :cid LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':cid' => $cid));
        
        return $data->fetch(PDO::FETCH_OBJ)->desc; 
        
    }
    
    public function getClanCoOwnerInfo($cid){
        
        $return = Array();

        $this->session->newQuery();
        $sql = 'SELECT userID FROM clan_users WHERE clanID = :cid AND authLevel = 3';
        $sdata = $this->pdo->prepare($sql);
        $sdata->execute(array(':cid' => $cid));
        $data = $sdata->fetchAll(); 
        
        if(count($data) > 0){
            
            for($i=0;$i<count($data);$i++){
                
                $return[$i]['ID'] = $data[$i]['userid'];
                $return[$i]['NAME'] = $this->player->getPlayerInfo($return[$i]['ID'])->login;
                
            }
            
            $return['TOTAL'] = $i;
            
        } else {
            $return['TOTAL'] = 0;
        }
        
        return $return;
        
    }
    
    public function war_recordDDoS($victimID, $victimClan){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM round_ddos WHERE vicID = '".$victimID."' AND attID = '".$_SESSION['id']."' ORDER BY date DESC";
        $id = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->id;
        
        $this->session->newQuery();
        $sql = "INSERT INTO clan_ddos (attackerClan, victimClan, ddosID)
                VALUES ('".self::getPlayerClan()."', '".$victimClan."', '".$id."')";
        $this->pdo->query($sql);
        
    }
    
    public function clan_inWar($cid){
        
        $this->session->newQuery();
        $sql = "SELECT clanID1 FROM clan_war WHERE clanID1 = '".$cid."' OR clanID2 = '".$cid."' LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function issetWar($clan1, $clan2){
        
        $this->session->newQuery();
        $sql = "SELECT clanID1 FROM clan_war WHERE (clanID1 = '".$clan1."' AND clanID2 = '".$clan2."') OR (clanID1 = '".$clan2."' AND clanID2 = '".$clan1."') LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
                
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function show_myWar(){
        
        ?>

        <div class="widget-title">
            <span class="icon"><span class="he16-war_info"></span></span>
            <h5><?php echo _('War info'); ?></h5>
            <span class="label label-important" id="total-war"></span>      
        </div>

        <div class="widget-content padding">        

        <?php
        
        $clanID = $this->myClan;
        
        if(self::clan_inWar($clanID)){
            
            $myClanInfo = self::getClanInfo($clanID);
            
            $name = '['.$myClanInfo->nick.'] '.$myClanInfo->name;
            
            ?>

            <?php echo $name; ?><span class="small">versus</span><br/>

            <ul class="list">

            <?php
            
            
            $this->session->newQuery();
            $sql = "SELECT clanID1, clanID2, score1, score2, bounty, startDate, TIMESTAMPDIFF(HOUR, NOW(), endDate) AS ending FROM clan_war WHERE clanID1 = '".$clanID."' OR clanID2 = '".$clanID."'";
            $data = $this->pdo->query($sql)->fetchAll();
            
            for($i=0;$i<sizeof($data);$i++){
                
                if($data[$i]['clanid1'] == $clanID){
                    
                    $vicID = $data[$i]['clanid2'];
                    $vicScore = $data[$i]['score2'];
                    $myScore = $data[$i]['score1'];
                    
                } else {
                    
                    $vicID = $data[$i]['clanid1'];
                    $vicScore = $data[$i]['score1'];
                    $myScore = $data[$i]['score2'];    
                    
                }
                                
                $clanInfo = self::getClanInfo($vicID);

                ?>

                <li>
                    <div class="span5">
                        <div class="clanwar-left">
                            <a href="clan?id=<?php echo $vicID; ?>" style="color: #000;"><?php echo $clanInfo->name; ?></a>
                        </div>
                    </div>       
                    <div class="span4">
                        <div class="clanwar-score fsize19">
                            <span class="green"><?php echo number_format($myScore); ?></span> / <span class="red"><?php echo number_format($vicScore); ?></span><br/>
                            <span class="small nomargin"><a href="?action=war&clan=<?php echo $vicID; ?>"><?php echo _('View history'); ?></a></span>
                        </div>
                    </div>                                                                
                    <div class="span3">
                        <div class="clanwar-bounty">
                            <font color="green">$<?php echo number_format($data[$i]['bounty']); ?></font>
                        </div>
                    </div>                                                                                                                                

                    <div style="clear: both;"></div>    
                </li>
                    
                <?php
                
            }
            
            ?>
                
            </ul>
                
            <?php
                            
        } else {
            
            $i = 0;
            ?>
            
            <?php echo _('We are in peace. For now.'); ?>

            <?php
        }
        
        $system = new System();
        $system->changeHTML('total-war', $i);
        
        ?>
            
        </div>
            
        <?php    
        
        
    }
    
    public function show_warSelect(){
        
        ?>

        <div class="widget-box">

        <?php

        self::show_myWar();

        ?>

        </div>

        <div class="widget-box">

        <?php

        self::show_curWar();

        ?>
            
        </div>

    </div>
    <div class="span4">

        <div class="widget-box">

        <?php

        self::show_myWarHistory();

        ?>        

        </div>
        
        <div class="widget-box">
    
        <?php
        
        self::show_myWarOptions();
        
        ?>
        
        </div>
            
        <?php
        
    }
    
    public function show_myWarOptions(){
        
        ?>
        
        <div class="widget-title">
            <span class="icon"><span class="he16-war_hist"></span></span>
            <h5><?php echo _('War History'); ?></h5>
        </div>


        <div class="widget-content padding center">        

            <span class="pull-left"><?php echo _('View wars from:'); ?></span>
            <br/><br/>

            <ul class="soft-but">
                <li>
                    <a href="clan?action=war&show=current">
                        <i class="icon- he32-current"></i>
                        <?php echo _('Current round'); ?>
                    </a>
                </li>
                <li>
                    <a href="clan?action=war&show=history">
                        <i class="icon- he32-previous"></i>
                        <?php echo _('Previous rounds'); ?>
                    </a>
                </li>
            </ul>

        </div>
        
        
        <?php
        
    }
    
    public function show_myWarHistory(){
        
        ?>
        
        <div class="widget-title">
            <span class="icon"><span class="he16-war_recent"></span></span>
            <h5><?php echo _('Recent wars'); ?></h5>
            <span class="label label-info" id="recent-wars">?</span>      
        </div>

        <div class="widget-content padding">
        
        <?php
        
        $clanID = $this->myClan;

        $this->session->newQuery();
        $sql = 'SELECT id, idWinner, idLoser, scoreWinner, scoreLoser
                FROM clan_war_history
                WHERE (idWinner = '.$clanID.' OR idLoser = '.$clanID.') AND TIMESTAMPDIFF(DAY, endDate, NOW()) > 30
                ORDER BY endDate DESC';
        $data = $this->pdo->query($sql)->fetchAll();
                
        if(sizeof($data) > 0){
                        
            ?>

            <span class="small">versus</span><br/>

            <ul class="list">            
            
            <?php
            
            for($i=0; $i<sizeof($data); $i++){
                
                if($data[$i]['idwinner'] == $clanID){
                    $otherClan = $data[$i]['idloser'];
                    $string = '<span class="green">';
                    $stringEnd = ' victory!';
                } else {
                    $otherClan = $data[$i]['idwinner'];
                    $string = '<span class="red">';
                    $stringEnd = ' defeat!';
                }
                
                $scoreDiff = $data[$i]['scorewinner'] - $data[$i]['scoreloser'];
                
                if($scoreDiff > 10000){
                    $string .= 'Major ';
                } elseif($scoreDiff > 5000) {
                    $string .= 'Regular ';
                } else {
                    $string .= 'Minor ';
                }
                
                $string .= $stringEnd.'</span>';
                
                $otherClanInfo = self::getClanInfo($otherClan);
                
                ?>
                
                    <a href="#w<?php echo $data[$i]['id']; ?>"  onclick="getWarHistory('<?php echo $data[$i]['id']; ?>');">
                        <li class="li-click">

                            <div class="span7">
                                <div class="clanwar-recent-left">
                                    [<?php echo $otherClanInfo->nick; ?>] <?php echo $otherClanInfo->name; ?>
                                </div>
                            </div>
                            <div class="span5">
                                <div class="clanwar-recent-score center green">
                                    <span class="green"><?php echo _($string); ?></span>
                                </div>
                            </div>                                                                                                                                                                                               

                            <div style="clear: both;"></div>

                        </li>
                    </a>
                

                <div id="war" class="modal hide">
                        <div class="modal-header">
                                <button data-dismiss="modal" class="close" type="button">×</button>
                                <h3><?php echo _('War log'); ?></h3>
                        </div>
                        <div class="modal-body" id="war-body">
                                
                        </div>
                        <div class="modal-footer">
                                <a data-dismiss="modal" class="btn btn-primary" href="#">Ok</a>
                        </div>
                </div>
                
                <script>

function getWarHistory(wid){
    
    jQuery.ajax({
        type: "POST",
        url: 'ajax.php',
        data: {func: 'warHistory', warid: ''+wid+''},
         success:function(data) {
             if(data.status == 'OK'){
                //document.getElementById("war").id = "w"+wid;

                document.getElementById("war-body").innerHTML = data.msg;

                $('#war').modal('show');
             } else {
                alert(_("This war log was not found. Please report to the administrator."));
             }
         }
    })   
    
}
                                            
                </script>

                <?php
                
            }
            
            ?>
            
            </ul>
            
            <?php
            
        } else {
            
            $i = 0;
            ?>
        
            <?php echo _('There are no wars logged in the last 30 days.'); ?>
        
            <?php
        }
        
        $system = new System();
        $system->changeHTML('recent-wars', $i);
        
        ?>
        
        </div>
        
        <?php
        
    }
    
    public function show_curWar(){
        
        $sql = 'SELECT 
                    w.clanID1 AS cid1, w.clanID2 AS cid2, w.score1, w.score2, w.bounty, 
                    c1.nick AS clan1Nick, c1.name AS clan1Name,  c2.nick AS clan2Nick, c2.name AS clan2Name
                FROM clan_war w
                INNER JOIN clan c1
                ON c1.clanID = w.clanID1
                INNER JOIN clan c2
                ON c2.clanID = w.clanID2                
                ORDER BY bounty DESC
                LIMIT 10';
        $warInfo = $this->pdo->query($sql)->fetchAll();
        
        ?>
        
        <div class="widget-title">
            <span class="icon"><span class="he16-war_now"></span></span>
            <h5><?php echo _('Wars happening now'); ?></h5>
            <span class="label label-important"><?php echo sizeof($warInfo); ?></span>      
        </div>

        <div class="widget-content padding">
            
<?php if(sizeof($warInfo) > 0){ ?>
            
                                                            <ul class="list">
                                                                
<?php

    for($i = 0; $i < sizeof($warInfo); $i++){

        if($warInfo[$i]['score1'] > $warInfo[$i]['score2']){
            $colorAttacker = 'green';
            $colorVictim = 'red';
        } elseif($warInfo[$i]['score1'] < $warInfo[$i]['score2']){
            $colorAttacker = 'red';
            $colorVictim = 'green';
        } else {
            $colorAttacker = $colorVictim = 'black';
        }

?>
                                                                <li>
                                                                    <div class="span6">
                                                                        <div class="clanwar-all-left">
                                                                            <a href="clan?id=<?php echo $warInfo[$i]['cid1']; ?>">
                                                                                [<?php echo $warInfo[$i]['clan1nick']; ?>] <?php echo $warInfo[$i]['clan1name']; ?>
                                                                            </a>
                                                                            <span class="small nomargin">vs</span>
                                                                            <a href="clan?id=<?php echo $warInfo[$i]['cid2']; ?>">
                                                                                [<?php echo $warInfo[$i]['clan2nick']; ?>] <?php echo $warInfo[$i]['clan2name']; ?>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                    <div class="span4">
                                                                        <div class="clanwar-all-score">
                                                                            <span class="<?php echo $colorAttacker; ?>"><?php echo number_format($warInfo[$i]['score1']); ?></span> / <span class="<?php echo $colorVictim; ?>"><?php echo number_format($warInfo[$i]['score2']); ?></span><br/>
                                                                        </div>
                                                                    </div>                                                                
                                                                    <div class="span2">
                                                                        <div class="clanwar-all-bounty">
                                                                            <span class="green">$<?php echo number_format($warInfo[$i]['bounty']); ?></span>
                                                                        </div>
                                                                    </div>                                                                                                                                

                                                                    <div style="clear: both;"></div>
                                                                </li>
<?php
    
    }

} else { ?>
          
                                                           <?php echo _('No wars happening at the moment.'); ?>
                                                            
<?php } ?>
            
        </div>
            
        <?php
        
    }
    
    public function show_sideBarRound(){
        
        ?>
        
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><span class="he16-select"></span></span>
                <h5><?php echo _('Select a round'); ?></h5>
                <span class="label label-info" id="total-rounds">?</span>      
            </div>

            <div class="widget-content padding">                    
                
                <ul class="list">
                
                <?php

                $storyline = new Storyline();
                
                $roundInfo = $storyline->round_getAll();

                for($i=0; $i<sizeof($roundInfo); $i++){
                    
                    ?>
                    
                    <a href="?action=war&show=history&round=<?php echo $roundInfo[$i]['id']; ?>">
                        <li class="li-click">

                            <div class="list-ip">
                                Round #<?php echo $roundInfo[$i]['id']; ?>                                            
                            </div>
                            <div class="list-user">
                                <?php echo $roundInfo[$i]['name']; ?>
                            </div>
                            <div style="clear: both;"></div>

                        </li>
                    </a>
                    
                    <?php
                    
                }
                
                $system = new System();
                $system->changeHTML('total-rounds', $i);
                
                ?>
                
                </ul>
                    
            </div>
        </div>        
        
        <?php
        
    }

    public function show_warHistoryAll($getRound = ''){

        ?>
        
        <div class="widget-box">
            
            <div class="widget-title">
                <span class="icon"><span class="he16-war_info"></span></span>
                <h5><?php echo _('Wars from previous rounds'); ?></h5>
                <span class="label label-info" id="total-war">.</span>      
            </div>

            <div class="widget-content padding">        
        
        <?php
            
        require '/var/www/classes/Storyline.class.php';
        $storyline = new Storyline();            
        
        $system = new System();
        
        $clanID = self::getPlayerClan();
        $curRound = $storyline->round_current();
        
        $table = 'hist_clans_war';
        $extra = ', round';

        if($getRound == 'current'){
            
            $table = 'clan_war_history';
            $extra = '';
            $where = '';         
            
            $getRound = $curRound;
            
        } elseif($getRound != ''){

            $where = 'AND ROUND = \''.$getRound.'\'';
            
            if(!is_numeric($getRound)){
                $system->handleError('Invalid round.', 'clan?action=war&show=history');
            }            
            
            if($getRound > $curRound || $getRound < 1){
                $system->handleError('Invalid round.', 'clan?action=war&show=history');
            }
            
        } else {
            $where = '';
        }
        
        $this->session->newQuery();
        $sql = "SELECT id, idWinner, idLoser, scoreWinner, scoreLoser, startDate, endDate, bounty".$extra."
                FROM ".$table."
                WHERE (idWinner = '".$clanID."' OR idLoser = '".$clanID."') ".$where."
                ORDER BY endDate DESC";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) > 0){
 
            $this->clanInfo = self::getClanInfo($clanID);

            echo '<b>'.$this->clanInfo->name.'</b> vs: <br/>';
            
            if($getRound == ''){
                $prevRound = $data['0']['round'];
            } else {
                ?>
                <center><b><?php echo _('Wars from round '); echo $getRound; ?></b></center><br/>
                <?php
            }
            
            ?>
                
            <ul class="list">
                
            <?php
            
            for($i=0;$i<sizeof($data);$i++){            
                
                if($getRound == ''){
                    $round = $data[$i]['round'];
                    
                    if($round < $prevRound || $i == 0){
                        if($i > 0){
                            $prevRound = $round;
                        }
                        ?>
                
                        </ul>
                        <center><b>Round <?php echo $round; ?></b></center><br/>
                        <ul class="list">
                
                        <?php
                    }
                }
                
                if($data[$i]['idwinner'] == $clanID){
                    
                    $vicID = $data[$i]['idloser'];
                    $vicScore = $data[$i]['scoreloser'];
                    $myScore = $data[$i]['scorewinner'];
                    
                } else {
                    
                    $vicID = $data[$i]['idwinner'];
                    $vicScore = $data[$i]['scorewinner'];
                    $myScore = $data[$i]['scoreloser'];
                    
                }
                
                $clanInfo = self::getClanInfo($vicID);
                
                ?>

                <a href="#w<?php echo $data[$i]['id']; ?>" onClick="getWarHistory(<?php echo $data[$i]['id']; ?>);">
                    <li class="li-click">

                            <div class="span5">
                                <div class="clanwar-all-left">
                                    <?php echo $clanInfo->name; ?>
                                </div>
                            </div>       
                            <div class="span4">
                                <div class="clanwar-all-score fsize19">
                                    <span class="green"><?php echo number_format($myScore); ?></span> / <span class="red"><?php echo number_format($vicScore); ?></span><br/>
                                </div>
                            </div>     
                            <div class="span3">
                                <div class="clanwar-all-bounty">
                                    <font color="green">$<?php echo number_format($data[$i]['bounty']); ?></font>
                                </div>
                            </div>     

                        <div style="clear: both;"></div>  
                    </li>
                </a>

                <?php
                                
            }

            ?>
            
            </ul>
            
                <div id="war" class="modal hide">
                        <div class="modal-header">
                                <button data-dismiss="modal" class="close" type="button">×</button>
                                <h3><?php echo _('War log'); ?></h3>
                        </div>
                        <div class="modal-body" id="war-body">
                                
                        </div>
                        <div class="modal-footer">
                                <a data-dismiss="modal" class="btn btn-primary" href="#">Ok</a>
                        </div>
                </div>
                
                <script>

function getWarHistory(wid){
    
    jQuery.ajax({
        type: "POST",
        url: 'ajax.php',
        data: {func: 'warHistory', warid: ''+wid+''},
         success:function(data) {
             if(data.status == 'OK'){
                //document.getElementById("war").id = "w"+wid;

                document.getElementById("war-body").innerHTML = data.msg;

                $('#war').modal('show');
             } else {
                alert(_("This war log was not found. Please report to the administrator."));
             }
         }
    })   
    
}
                                            
                </script>                        
                        
            <?php
            
        } else {
            $i = 0;
            ?>
                
                <?php echo _('No war recorded'); ?>
                
            <?php
        }
        
        $system->changeHTML('total-war', $i);
        
        ?>
        
            </div>
        </div>
    </div>
    <div class="span4">
                        
        <?php

        self::show_sideBarRound();
        
        ?>
        
        <button class="btn btn-inverse" onClick="parent.location='clan?action=war'"><?php echo _('Back to war panel'); ?></button>        
        
        <?php
        
        if(isset($_GET['round'])){
        
            ?>

            <button class="btn btn-inverse" onClick="parent.location='clan?action=war&show=history'"><?php echo _('Back to history'); ?></button>
            
            <?php
            
        }
        
        
    }
    
    public function show_warLog($victimClan){

        ?>
            
            <div class="widget-title">
                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                <h5>Attacks Log</h5>
                <span class="label label-info" id="total-attacks">.</span>      
            </div>

            <div class="widget-content padding">   
                  
            
        <?php
        
        $where = "(attackerClan = '".$this->myClan."' AND victimClan = '".$victimClan."') OR (attackerClan = '".$victimClan."' AND victimClan = '".$this->myClan."')";        
        
        $this->session->newQuery();
        $sql = "SELECT 
                    d.attackerClan, d.ddosID, d.displayAttacker, d.displayVictim, r.attID, r.vicID, r.power, r.servers, r.vicNPC, r.date, 
                    att.login AS attacker, vicUser.login AS victimUser, att.gameIP AS attackerIP, vicUser.gameIP AS victimUserIP
                FROM clan_ddos d
                INNER JOIN round_ddos r
                ON r.id = d.ddosID
                INNER JOIN users att
                ON att.id = r.attID
                LEFT JOIN users vicUser
                ON vicUser.id = r.vicID
                WHERE ".$where."
                ORDER BY r.date DESC";
        $data = $this->pdo->query($sql)->fetchAll();

        if(sizeof($data) > 0){
            
            $warStartAux = FALSE;
            for($i=0;$i<sizeof($data);$i++){
                                
                if($data[$i]['vicnpc'] == 0){

                    $this->session->newQuery();
                    $sql = "SELECT clan_users.clanID
                            FROM clan_users
                            LEFT JOIN clan
                            ON clan.clanID = clan_users.clanID
                            WHERE clan_users.userID = '".$data[$i]['vicid']."'";
                    $vicInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                    
                    if($vicInfo->clanid == $this->myClan){
                        $style = '<font color="red"><b>';
                    } else {
                        $style = '<font color="green"><b>';
                    }

                    if($data[$i]['displayvictim'] == 1){
                        $vicLink = 'internet?ip='.long2ip($data[$i]['victimuserip']);
                    } else {
                        $vicLink = 'profile?id='.$data[$i]['vicid'];
                    }
                    
                    
                    $victimName = $data[$i]['victimuser'];
                                        
                } else {
                    
                    $this->session->newQuery();
                    $sql = "SELECT clan.clanID, clan.clanIP, clan.name
                            FROM clan
                            LEFT JOIN npc
                            ON clan.clanIP = npc.npcIP
                            WHERE npc.id = '".$data[$i]['vicid']."'";
                    $vicInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
                                        
                    if($vicInfo->clanid == $this->myClan){
                        $style = '<font color="red"><b>';
                    } else {
                        $style = '<font color="green"><b>';
                    }
                    
                    if($data[$i]['displayvictim'] == 1){
                        $vicLink = 'internet?ip='.long2ip($vicInfo->clanip);
                    } else {
                        $vicLink = 'internet';
                    }
                    
                    
                    
                    $victimName = $vicInfo->name;
                    
                }
                
                $attackerInfo = $data[$i]['attacker'];

                if($data[$i]['displayattacker'] == 1){
                    $attLink = 'internet?ip='.long2ip($data[$i]['attackerip']);
                } else {
                    $attLink = 'profile?id='.$data[$i]['attid'];
                }
                
                $now = new DateTime($data[$i]['date']);
                $dateDiff = $now->diff(new DateTime($this->warStartDate));
                
                if($dateDiff->invert == 0){
                    $style = $styleEnding = '';
                    $prevSpanStart = '<span class="small nomargin">';
                    $prevSpanEnding = '</span>';
                } else {
                    $prevSpanStart = $prevSpanEnding = '';
                    $styleEnding = '</b></font>';

                }
                
                ?>

<?php 


if(!$warStartAux && $prevSpanStart != ''){ 
    $warStartAux = TRUE;
?>
                 <span class="small"><?php echo substr($this->warStartDate, 0, -3); ?></span> - [Clan war started]<br/>
<?php 
}
echo $prevSpanStart;
?>
                <span class="small"><?php echo substr($data[$i]['date'], 0, -3); ?></span> - 
                <a href="<?php echo $attLink; ?>"><?php echo $attackerInfo; ?></a> 
                attacked  
                <a href="<?php echo $vicLink; ?>"><?php echo $victimName; ?></a> 
                with <?php echo $data[$i]['servers']; ?> servers 
                at a DDoS power of <?php echo $style.number_format($data[$i]['power']).$styleEnding; ?><br/>
<?php
echo $prevSpanEnding;

            }

        } else {
            
            $i = 0;
            echo 'No DDoS attacks atm';
            
        }
     
        $system = new System();
        $system->changeHTML('total-attacks', $i);        
        
        ?>
            
            </div>
            
        <?php
        
    }
    
    public function show_warResult($victimClan){

        ?>

        <div class="widget-title">
            <span class="icon"><i class="fa fa-arrow-right"></i></span>
            <h5>War against <span id="war-against">clan</span></h5>
        </div>

        <div class="widget-content nopadding">   

            <?php
            
            $this->session->newQuery();
            $sql = 'SELECT clanID1, clanID2, score1, score2, startDate, endDate, bounty, TIMESTAMPDIFF(HOUR, NOW(), endDate) AS ending FROM clan_war WHERE (clanID1 = \''.$this->myClan.'\' AND clanID2 = \''.$victimClan.'\') OR (clanID1 = \''.$victimClan.'\' AND clanID2 = \''.$this->myClan.'\') LIMIT 1';
            $warInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

            $iWin = 1;
            $myColor = 'green';
            $otherColor = 'red';          
            $myScore = $warInfo->score1;
            $otherScore = $warInfo->score2;
            if($warInfo->clanid1 == $this->myClan){
                $otherClan = $warInfo->clanid2;
                if($warInfo->score1 < $warInfo->score2){
                    $iWin = 0;
                    $myColor = 'red';
                    $otherColor = 'green';
                }
            } else {
                $myScore = $warInfo->score2;
                $otherScore = $warInfo->score1;
                $otherClan = $warInfo->clanid1;
                if($warInfo->score2 < $warInfo->score1){
                    $iWin = 0;
                    $myColor = 'red';
                    $otherColor = 'green';
                }
            }
            
            $myClanInfo = self::getClanInfo($this->myClan);
            $otherClanInfo = self::getClanInfo($otherClan);
            
            $now = new DateTime('now');
            $duration = $now->diff(new DateTime($warInfo->startdate));

            if($duration->d == 0){
                $startedStr = _('Today');
            } elseif($duration->d == 1) {
                $startedStr = _('1 day ago');
            } else {
                $startedStr = $duration->d._(' days ago');
            }
            
            $endDays = (int)floor($warInfo->ending / 24);
            $endHours = $warInfo->ending % 24;

            if($endDays <= 0){
                $endingStr = '';
            } elseif($endDays == 1){
                $endingStr = '1 day';
            } else {
                $endingStr = $endDays.' days';
            }
            
            if($endHours <= 0){
                if($endingStr == ''){
                    $endingStr = _('Now');
                }
            } elseif($endHours == 1){
                if($endingStr != ''){
                    $endingStr .= ' and ';
                }
                $endingStr .= '1 hour';
            } else {
                if($endingStr != ''){
                    $endingStr .= ' and ';
                }
                $endingStr .= $endHours.' hours';
            }
      
            $this->warStartDate = $warInfo->startdate;
            
            $where = "(attackerClan = '".$this->myClan."' AND victimClan = '".$victimClan."') OR (attackerClan = '".$victimClan."' AND victimClan = '".$this->myClan."')";
            $this->session->newQuery();
            $sql = "SELECT 
                        d.attackerClan, d.victimClan,
                        att.login AS attackerName, att.id AS attackerID,
                        vic.login AS victimName, vic.id AS victimID
                    FROM clan_ddos d
                    INNER JOIN round_ddos r
                    ON r.id = d.ddosID
                    INNER JOIN users att
                    ON att.id = r.attID
                    INNER JOIN users vic
                    ON vic.id = r.vicID
                    WHERE ".$where." AND r.vicNPC = 0
                    ORDER BY r.date DESC";
            $data = $this->pdo->query($sql)->fetchAll();
            
            $usedArr = Array();
            $membersInvolved = Array();
            $membersInvolvedStr = '';
            $k = 0;
            for($i = 0; $i < sizeof($data); $i++){
                if(!array_key_exists($data[$i]['attackerid'], $usedArr)){
                    $membersInvolved[$k]['id'] = $data[$i]['attackerid'];
                    $membersInvolved[$k]['name'] = $data[$i]['attackername'];
                    $membersInvolved[$k]['clan'] = $data[$i]['attackerclan'];
                    $k++;
                    $membersInvolvedStr .= '<a href="profile?id='.$data[$i]['attackerid'].'">'.$data[$i]['attackername'].'</a>, ';
                    $usedArr[$data[$i]['attackerid']] = 1;
                }
                
                if(!array_key_exists($data[$i]['victimid'], $usedArr)){
                    $membersInvolved[$k]['id'] = $data[$i]['victimid'];
                    $membersInvolved[$k]['name'] = $data[$i]['victimname'];
                    $membersInvolved[$k]['clan'] = $data[$i]['victimclan'];
                    $k++;
                    $membersInvolvedStr .= '<a href="profile?id='.$data[$i]['victimid'].'">'.$data[$i]['victimname'].'</a>, ';
                    $usedArr[$data[$i]['victimid']] = 1;
                }
            }
            if(sizeof($usedArr) > 0){
                $membersInvolvedStr = substr($membersInvolvedStr, 0, -2);
            } else {
                $membersInvolvedStr = _('None');
            }

            $this->warMembersInvolved = $membersInvolved;
            
            ?>
            
            <table class="table table-cozy table-bordered table-striped table-fixed">
                <tbody> 
                    <tr>
                        <td><b><?php echo $myClanInfo->name; ?></b> <?php echo _('score'); ?></td>
                        <td><font color="<?php echo $myColor; ?>"><?php if($iWin == 1){ echo '<b>'; } echo number_format($myScore); if($iWin == 1){ echo '</b>'; }?></font></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $otherClanInfo->name; ?></b> <?php echo _('score'); ?></td>
                        <td><font color="<?php echo $otherColor; ?>"><?php if($iWin == 0){ echo '<b>'; } echo number_format($otherScore); if($iWin == 0){ echo '</b>'; }?></font></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Current bounty'); ?></td>
                        <td><span class="green">$<?php echo number_format($warInfo->bounty); ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Start date'); ?></td>
                        <td><?php echo substr($warInfo->startdate, 0, -9); ?> <span class="small"><?php echo $startedStr; ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo _('End date'); ?></td>
                        <td><?php echo substr($warInfo->enddate, 0, -3); ?> <span class="small"><?php echo $endingStr; ?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Members involved'); ?></td>
                        <td><span class="nomargin small"><?php echo $membersInvolvedStr; ?></span></td>
                    </tr>
                    
                </tbody>
            </table>
            
            
        </div>

        <?php

        $system = new System();
        $system->changeHTML('war-against', $otherClanInfo->name);
        
    }
    
    public function show_warSideBar($victimClan){
        
        $where = "(clanID1 = '".$this->myClan."' AND clanID2 = '".$victimClan."') OR (clanID1 = '".$victimClan."' AND clanID2 = '".$this->myClan."')";    
        $this->session->newQuery();
        $sql = "SELECT 
                    startDate
                FROM clan_war
                WHERE ".$where."
                LIMIT 10";
        $clanWarStart = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->startdate;
                
        for($i = 0; $i < sizeof($this->warMembersInvolved); $i++){
            
            $this->session->newQuery();
            $sql = 'SELECT 
                        SUM(power) AS totalPower
                    FROM 
                    (
                        SELECT 
                            r.power, r.attID, r.date
                        FROM clan_ddos d
                        INNER JOIN round_ddos r
                        ON r.id = d.ddosID
                        WHERE (
                            (attackerClan = \''.$this->myClan.'\' AND victimClan = \''.$victimClan.'\') OR 
                            (attackerClan = \''.$victimClan.'\' AND victimClan = \''.$this->myClan.'\')
                        )
                        ORDER BY r.date DESC
                        LIMIT 10
                    ) a
                    WHERE 
                        attID = \''.$this->warMembersInvolved[$i]['id'].'\' AND
                        TIMESTAMPDIFF(SECOND, date, \''.$clanWarStart.'\') < 0
                    ';
            $power = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->totalpower;
            
            $this->warMembersInvolved[$i]['power'] = (int)$power;
            
        }
        
        
        ?>
            
        
        <div class="widget-title">
            <span class="icon"><i class="fa fa-arrow-right"></i></span>
            <h5><?php echo _('Top players'); ?></h5>
        </div>

<?php if(sizeof($this->warMembersInvolved) > 0){ ?>            
            
        <div class="widget-content nopadding">  
            
            <table class="table table-cozy table-bordered table-striped table-fixed">

                <thead> 
                    <th><?php echo _('Player'); ?></th>
                    <th><?php echo _('War points'); ?></th>
                </thead>
                <tbody>
                    
<?php



function cmpUserField($a,$b){
  return $b['power']-$a['power'];
}

uasort($this->warMembersInvolved, 'cmpUserField'); 

foreach($this->warMembersInvolved as $info){
    
    if($info['clan'] == $this->myClan){
        $font = '<font color="green">';
    } else {
        $font = '<font color="red">';
    }
    $endFont = '</font>';
    
?>
     
                    <tr>
                        <td><a href="profile?id=<?php echo $info['id']; ?>"><?php echo $info['name']; ?></a></td>
                        <td><?php echo $font.number_format($info['power']).$endFont; ?></td>
                    </tr>
                    
<?php
    
}

?>
                    
                </tbody>
                
            </table>
                
        </div>
            
<?php } else { ?>      
            
        <div class="widget-content">  

            <?php echo _('Ops! There are no war attacks yet.'); ?>
            
        </div>
            
<?php } ?>
        <?php
  
        
    }
    
    public function show_warHistory($victimClan){
                
        ?>
            
        <div class="widget-box">

            <?php
            
            self::show_warResult($victimClan);
            
            ?>
            
        </div>
            
            
        <div class="widget-box">
        
        
            <?php

            self::show_warLog($victimClan);

            ?>
                      
        </div>
    </div>
        <div class="span4">
            <div class="widget-box">
                
            <?php

            self::show_warSideBar($victimClan);

            ?>
                
            </div>
        
        <?php

        
    }
    
    public function show_clanWar(){

        if(self::clan_inWar($this->clanID)){
            
            $this->session->newQuery();
            $sql = "SELECT clanID1, clanID2, startDate, TIMESTAMPDIFF(HOUR, NOW(), endDate) AS ending FROM clan_war WHERE clanID1 = '".$this->clanID."' OR clanID2 = '".$this->clanID."'";
            $data = $this->pdo->query($sql)->fetchAll();

            ?>
            
            <tr>
                <td><span class="red"><?php echo _('War'); ?></span></td>
                <td>
            
            <?php
            
            for($i=0;$i<sizeof($data);$i++){

                if($data[$i]['clanid1'] == $this->clanID){
                    $warID = $data[$i]['clanid2'];
                } else {
                    $warID = $data[$i]['clanid1'];
                }

                $clanInfo = self::getClanInfo($warID);

                $endDays = (int)floor($data[$i]['ending'] / 24);
                $endHours = $data[$i]['ending'] % 24;
                
                $endingStr = sprintf(ngettext('%d day', '%d days', $endDays), $endDays);
                if($endHours >= 1){
                    $endingStr .= ' '._('and').' ';
                    $endingStr .= sprintf(ngettext('%d hour', '%d hours', $endHours), $endHours);
                }
                                
                $details = '<a href="clan?action=war&clan='.$warID.'"><span class="small nomargin">'._('Details').'</span></a>';
                
                $conectivo = '';
                if(sizeof($data) > 0 && $i < sizeof($data)-1){
                    
                    if($i == sizeof($data)-2){
                        $conectivo = ' '._('and').' ';
                    } else {
                        $conectivo = ' ';
                    }
                    
                }

                ?>
                <a href="clan?id=<?php echo $warID; ?>"><?php echo $clanInfo->name; ?></a> <?php echo $details.$conectivo; ?>
                <?php             

            }
            
            ?>
                
                </td>
            </tr>
                
            <?php    

        }
        
    }
    
    public function claninfo_basicInfo(){
        
        $ownerID = self::getClanOwnerID($this->clanID)->userid;
        $owner = $this->player->getPlayerInfo($ownerID)->login;
        
        $coOwnerInfo = self::getClanCoOwnerInfo($this->clanID);

        $clanPower = number_format($this->clanInfo->power, '0', '.', ',');
        
        if($this->clanInfo->won + $this->clanInfo->lost > 0){
            $clanRate = round(($this->clanInfo->won / ($this->clanInfo->won + $this->clanInfo->lost))*100).'%';
        } else {
            $clanRate = '';
        }
        
        $clanClicks = number_format($this->clanInfo->clicks, '0', '.', ',');
        
        if($this->clanInfo->rank == -1){
            $sql = "SELECT COUNT(*) AS total FROM ranking_clan";
            $this->clanInfo->rank = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        }
        
        ?>

        <table class="table table-cozy table-bordered table-striped">
                <tbody>
                        <tr>
                                <td><span class="item"><?php echo _('Power'); ?></span></td>
                                <td><?php echo $clanPower; ?> <span class="small">(<?php echo _('Ranked'); ?> #<?php echo $this->clanInfo->rank; ?>)</span></td>
                        </tr>
                        <tr>
                                <td><span class="item"><?php echo _('Master'); ?></span></td>
                                <td><a href="profile?id=<?php echo $ownerID; ?>"><?php echo $owner; ?></a></td>
                        </tr>
                        <?php self::show_clanWar(); ?>
                        <tr>
                                <td><span class="item"><?php echo _('Win / Loses'); ?></span></td>
                                <td><font color="green"><?php echo $this->clanInfo->won; ?></font> / <font color="red"><?php echo $this->clanInfo->lost; ?></font> <span class="small"><?php echo $clanRate; ?></span></td>
                        </tr>
                        <tr>
                                <td><span class="item"><?php echo _('Members'); ?></span></td>
                                <td><?php echo $this->clanInfo->members; ?></td>
                        </tr>   
                        <tr>
                                <td><span class="item"><?php echo _('Profile clicks'); ?></span></td>
                                <td><?php echo $clanClicks; ?></td>
                        </tr>
                </tbody>
        </table>

        <?php
        
    }

    public function click($cid){
        
        $this->session->newQuery();
        $sql = 'UPDATE clan_stats SET pageClicks = pageClicks + 1 WHERE cid = :cid';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':cid' => $cid));
        
    }
    
    public function show_desc(){
        
        $clanSig = self::getClanSig($this->clanID);
        echo $clanSig;
        
    }
    
    public function show_clanSettings(){
        
        self::show_playerProfile($_SESSION['id']);
     
        ?>
        
        </div>
        <div class="span4">
            <div class="widget-box">
                
                <?php
                
                self::show_settingsOptions();
                
                ?>
                
            </div>
        
        <?php
        
    }
    
    public function show_settingsOptions(){
        
        ?>
        
        <div class="widget-title">
            <span class="icon"><i class="fa fa-arrow-right"></i></span>
            <h5><?php echo _('Options'); ?></h5>
        </div>

        <div class="widget-content center">        

            <ul class="soft-but">

                <li>
                    <a href="clan?action=leave">
                        <i class="icon-" style="background-image: url('../img/icons/32/calendar.png');"></i>
                        <span><?php echo _('Leave clan'); ?></span>
                    </a>
                </li>

                <li>
                    <a href="#">
                        <i class="icon-" style="background-image: url('../img/icons/32/calendar.png');"></i>
                        <span><?php echo _('Talk to Master'); ?></span>
                    </a>
                </li>

            </ul>       

        </div>  
    
        <?php
        
    }
    
    public function show_clanInfo($cid = ''){

        if($cid == ''){
            $ownClan = 1;
            $this->clanID = $this->myClan;
        } else {
            $ownClan = 0;
            $this->clanID = $cid;
        }
        
        if($this->clanID != ''){
        
            if($ownClan == 0){
                if(!isset($_SESSION['CLICKED_CLAN'])){
                    self::click($this->clanID);
                } else {
                    if($_SESSION['CLICKED_CLAN'] != $this->clanID){
                        self::click($this->clanID);
                    }
                }
                $_SESSION['CLICKED_CLAN'] = $this->clanID;
            }

            $this->clanInfo = self::getClanInfo($this->clanID);
                        
            $system = new System();
            $system->changeHTML('link1', $this->clanInfo->name);
            
            ?>

            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-clan"></span></span>
                    <h5><?php echo '['.$this->clanInfo->nick.'] '.$this->clanInfo->name; ?></h5>
                </div>

                <div class="widget-content nopadding">        

                    <?php

                    self::claninfo_basicInfo();

                    ?>
                    
                </div>
                
            </div>
        
            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-clan_desc"></span></span>
                    <h5><?php echo _('Clan description'); ?></h5>
                </div>

                <div class="widget-content padding">        

                    <?php
                    
                    self::show_desc();
                            
                    ?>
                    
                </div>
                
            </div>
        
        </div>
        <div class="span4">

            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-profile"></span></span>
                    <h5><?php echo _('Photo & Badges'); ?></h5>
                    <span id="total-badges" class="label label-info">5</span>                                                                
                </div>

                <div class="widget-content padding noborder">
                    
                    <?php
                    
                    self::claninfo_sideBar();
                    
                    ?>

                </div>

                <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                
            </div>

            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-actions"></span></span>
                    <h5><?php echo _('Actions'); ?></h5>
                </div>


                <div class="widget-content padding center">        

                    <?php self::show_clanOptions(); ?>
                    
                </div>

            </div>                                                     

            <?php

        } else {
            
            echo 'You are not part of any clan';
            
        }
        
        
    }
    
    public function claninfo_sideBar(){
        
        ?>

            <?php
            
        $path = 'images/clan/';
        $name = md5($this->clanInfo->name.$this->clanID);

        
        if(file_exists($path.$name.'.jpg')){
            $src = $path.$name.'.jpg';
        } else {
            $src = 'images/misc/question.png';
        }

        ?>

	        <div class="span12">
				<div class="span12" style="text-align: center; margin-right: 15px; margin-bottom: 5px;">
					<img src="<?php echo $src; ?>">
				</div>
                <div class="row-fluid">
                    <div class="span12 badge-div">
                        <?php
                        require '/var/www/classes/Social.class.php';
                        $social = new Social();

                        $social->badge_list($this->clanID);
                        ?>
                    </div>
            	</div>
            </div>
            <span id="modal"></span>
<script type="text/javascript">
var fr = 0;
var uid = <?php echo $this->clanID; ?>;
</script>
        <?php
        
    }
    
    public function show_listMembers($cid, $admin = 0){
                
        $this->clanInfo = self::getClanInfo($cid);

        if($admin > 0){
            $widgetTitle = _('Manage members');
        } else {
            $widgetTitle = _('Member list of ').$this->clanInfo->name;
        }
        
        if($admin != 1){
        
            ?>
                
            <div class="widget-box">
            
            <?php 
        
        } 
        
        ?>

            <div class="widget-title">
                <span class="icon"><span class="he16-clan_manage"></span></span>
                <h5><?php echo $widgetTitle; ?></h5>
                <span class="label label-info pull-right" id="total-members"></span>
            </div>
            <div class="widget-content nopadding">        
                <table class="table table-cozy table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo _('Member'); ?></th>
                            <th><?php echo _('Reputation'); ?></th>
                            <th class="hide-phone"><?php echo _('Date joined'); ?></th>
                            <?php if($admin > 0){ ?>
                            <th><?php echo _('Actions'); ?></th>
                            <?php } ?>    
                        </tr>
                    </thead>
                        <tbody>            

                <?php    

                $this->session->newQuery();
                $sql = 'SELECT userID, memberSince, authLevel, hierarchy FROM clan_users WHERE clanID = :cid ORDER BY id ASC';
                $data = $this->pdo->prepare($sql);
                $data->execute(array(':cid' => $cid));

                $ranking = new Ranking();
                $now = new DateTime();

                $i = 0;
                while($memberInfo = $data->fetch(PDO::FETCH_OBJ)){

                    $i++;

                    if($i%2==1){
                        $str = ' class="odd"';
                    } else {
                        $str = '';
                    }
                    if($memberInfo->authlevel == 4){
                        $label = '<span class="hide-phone label label-info pull-right">'._('Master').'</span>';
                    } else {
                        $label = '';
                    }

                    $playerInfo = $this->player->getPlayerInfo($memberInfo->userid);
                    $power = $ranking->exp_getTotal($memberInfo->userid);

                    $memberSince = new DateTime($memberInfo->membersince);


                    if($memberSince->diff($now)->days < 8){
                        if($label == ''){
                            $label = '<span class="hide-phone label label-success pull-right">'._('New').'</span>';
                        }
                    }

                    ?>

                    <tr<?php echo $str; ?>>

                        <td><a href="profile?id=<?php echo $memberInfo->userid; ?>"><?php echo $playerInfo->login; ?></a><?php echo $label; ?></td>
                        <td><div class="center"><?php echo number_format($power); ?></div></td>
                        <td class="hide-phone"><div class="center"><?php echo $memberInfo->membersince; ?></div></td>
                        <?php if ($admin > 0) { ?>
                        <td><div class="center"><a href="?action=admin&opt=manage&id=<?php echo $memberInfo->userid; ?>"><span class="he16-clan_edit" title="<?php echo _('Manage'); ?>"></span></a></div></td>
                        <?php } ?>
                    </tr>

                    <?php


                }
                
                $system = new System();
                $system->changeHTML('total-members', $i);

                ?>

                    </tbody>
                </table>
            </div>
                
        <?php 
        
        if($admin != 1){
            
            ?>
            </div>
            <?php
            
        }
        
    }

    public function show_clanOptions(){
        
        $cid = $this->clanID;
        
        $playerHaveClan = self::playerHaveClan();
        if(self::playerHaveClan($_SESSION['id'])){
            $this->userClan = $this->myClan;
        } else {
            $this->userClan = NULL;
        }

        
        ?>
                
            <ul class="quick-actions">
                
        <?php
                
        
        if(!$playerHaveClan){
            
            if(!self::playerHavePendingRequest()){
                $this->canJoin = 1;
                ?>
                
                <li>
                        <a href="?action=join&id=<?php echo $cid; ?>">
                                <i class="icon-" style="background-image: url('../img/icons/32/calendar.png');"></i>
                                <?php echo _('Join Clan'); ?>
                        </a>
                </li>
                
                <?php
            } else {
                ?>
                <span class="he16-clan_join" title="<?php echo _('You cant join because you have pending requests'); ?>"></span>
                <?php
            }
            
        }

        $memberLink = '?action=list&id='.$cid;
        if($this->userClan == $cid){
            
            $memberLink = '?action=list';
            
            require '/var/www/classes/Forum.class.php';
            $forum = new Forum();
            
            $forumClanID = $forum->getForumClanID($cid);
            
            if($_SERVER['SERVER_NAME'] == 'localhost'){
                $forumLink = '/forum/viewforum?f='.$forumClanID['forum_id'];
            } else {
                $forumLink = 'https://forum.hackerexperience.com/viewforum.php?f='.$forumClanID['forum_id'];
            }
            
            ?>
        
					

            <li>
                <a href="internet?ip=<?php echo long2ip($this->clanInfo->clanip); ?>">
                    <i class="icon- he32-server"></i>
                    <?php echo _('Go to server'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $forumLink; ?>">
                    <i class="icon- he32-forum"></i>
                    <?php echo _('Go to forum'); ?>
                </a>
            </li>

            <?php
            
        }
        
        ?>
           
                <li>
                    <a href="<?php echo $memberLink; ?>">
                        <i class="icon- he32-members"></i>
                        <?php echo _('View members'); ?>
                    </a>
                </li>                                                                
                                                        
            </ul>
                
        <?php
        
    }
    
    public function getRequestInfo($requestID){
        
        $return = Array();
        
        $this->session->newQuery();
        $sql = 'SELECT clanID, userID, adminID, type, askedDate, msg FROM clan_requests WHERE id = :requestID LIMIT 1';
        $sdata = $this->pdo->prepare($sql);
        $sdata->execute(array(':requestID' => $requestID));
        $data = $sdata->fetchAll(); 
        
        if(count($data) == 1){
            
            $return ['ISSET'] = 1;
            $return ['REQ_ID'] = $requestID;
            $return ['CLAN_ID'] = $data['0']['clanid'];
            $return ['USER_ID'] = $data['0']['userid'];
            $return ['ADMIN_ID'] = $data['0']['adminid'];
            $return ['TYPE'] = $data['0']['type'];
            $return ['ASKED_DATE'] = $data['0']['askeddate'];
            $return ['MSG'] = $data['0']['msg'];
            
        } else {
            $return['ISSET'] = 0;
        }
        
        return $return;
        
    }
        
    public function deleteRequest($requestID){
        
        $this->session->newQuery();
        $sql = 'DELETE FROM clan_requests WHERE id = :requestID LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':requestID' => $requestID));
        
    }
    
    public function acceptRequest($requestID, $requestInfo){
        
        require '/var/www/classes/Forum.class.php';
        $forum = new Forum();

        $forumID = $forum->getForumIDByGameID($requestInfo['USER_ID']);
        $forumClanID = $forum->getForumClanID($requestInfo['CLAN_ID']);

        $forum->setPermission($forumID, 'viewer', $forumClanID['parent_id']);
        $forum->setPermission($forumID, 'viewer', $forumClanID['forum_id']);
        
        $this->session->newQuery();
        $sql = 'INSERT INTO clan_users (id,clanID, userID, memberSince, authLevel, hierarchy) 
                VALUES (\'\', :requestClanID, :requestUserID, NOW(), \'1\', \'1\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':requestClanID' => $requestInfo['CLAN_ID'], ':requestUserID' => $requestInfo['USER_ID']));
        
        $this->session->newQuery();
        $sql = 'UPDATE clan SET slotsUsed = slotsUsed + 1 WHERE clanID = :requestClanID';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':requestClanID' => $requestInfo['CLAN_ID']));
        
        $this->session->newQuery();
        $sql = 'DELETE FROM clan_requests WHERE id = :requestID LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':requestID' => $requestID));
        
        self::increaseMemberCount($requestInfo['CLAN_ID']);
        
        // 2019: I do this to reset the remote session. Otherwise the user will find a few bugs for being part of a clan but not having its ID on the user session.
        //faço isso pra poder resetar a sessão remota. caso contrário o usuário vai encontrar alguns bugs por participar de clan mas não ter a id dele na sessão.
        $this->session->newQuery();
        $sql = 'DELETE FROM users_online WHERE id = :requestUserID LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':requestUserID' => $requestInfo['USER_ID']));
        
    }
    
    public function getPlayerClan($uid=''){
        
        if($uid == ''){
            return $_SESSION['CLAN_ID'];
        }
        
        $this->session->newQuery();
        $sql = 'SELECT clanID FROM clan_users WHERE userID = :uid LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':uid' => $uid));
        $result = $data->fetchAll();        
        
        return $result['0']['clanid'];
        
    }
    
    public function show_pendingRequests($admin = ''){
        
        if($admin == ''){
            
            if(self::playerHavePendingRequest()){
                
                $uid = $_SESSION['id'];
                
                $this->session->newQuery();
                $sql = 'SELECT id, clanID FROM clan_requests WHERE userID = :uid LIMIT 1';
                $data = $this->pdo->prepare($sql);
                $data->execute(array(':uid' => $uid));
                $sqlData = $data->fetch(PDO::FETCH_OBJ); 
                
                $clanInfo = self::getClanInfo($sqlData->clanid);
                
                ?>
                
                
                <br/><br/><?php echo _('Pending request'); ?>:
                <br/><?php echo _('Join '); ?><a href="?id=<?php echo $sqlData->clanid; ?>"><?php echo $clanInfo->name; ?></a> - [<a href="?opt=del&request=<?php echo $sqlData->id; ?>"><?php echo _('delete'); ?></a>]
                
                <?php
                
            }
            
        } else {
            
            ?>
                
            <div class="widget-title">
                <span class="icon"><span class="he16-clan_add"></span></span>
                <h5><?php echo _('Join requests'); ?></h5>
                <span class="label label-important" id="total-requests"></span>                                                                
            </div>


            <div class="widget-content padding border">        
                
            <?php
            
            $clanID = $this->myClan;
            $i = 0;
            
            if(self::clanHavePendingRequest($clanID)){
                            
                ?>

                <ul class="list clan-padding link-clean">

                <?php
                
                $player = new Player();
                
                $this->session->newQuery();
                $sql = 'SELECT id, userID, TIMESTAMPDIFF(SECOND, askedDate, NOW()) AS timediff FROM clan_requests WHERE clanID = :clanID LIMIT 5';
                $data = $this->pdo->prepare($sql);
                $data->execute(array(':clanID' => $clanID));
                
                $modalArr = Array();

                while($requestInfo = $data->fetch(PDO::FETCH_OBJ)){
                    
                    $playerInfo = $player->getPlayerInfo($requestInfo->userid);

                    $modalArr[$i]['INFO'] = self::getRequestInfo($requestInfo->id);
                    
                    $timeDiff = $requestInfo->timediff;
                                        
                    if($timeDiff < 60){
                        $t = sprintf(ngettext('%d second', '%d seconds', $timeDiff), $timeDiff);
                    } elseif($timeDiff < 3600){
                        $minDiff = (int)($timeDiff/60);
                        $t = sprintf(ngettext('%d minute', '%d minutes', $minDiff), $minDiff);
                    } elseif($timeDiff < 3600 * 24){
                        $hourDiff = (int)($timeDiff/3600);
                        $t = sprintf(ngettext('%d hour', '%d hours', $hourDiff), $hourDiff);
                    } else {
                        $dayDiff = (int)($timeDiff/86400);
                        $t = sprintf(ngettext('%d day', '%d days', $dayDiff), $dayDiff);
                    }
                    $t .= _(' ago');
                    
                    ?>
                    <a href="#request<?php echo $requestInfo->id; ?>" data-toggle="modal">
                        <li class="li-click">

                            <i class="fa fa-user"></i>
                            <?php echo $playerInfo->login; ?>
                            <span class="small"> <?php echo $t; ?></span>
                        </li>
                    </a>

                    <?php
                    
                    $i++;
                    
                }
                
                ?>
                    
                </ul>    
                    
                <?php    
                
                self::showModal($modalArr, 'requests');
                                
            } else {
                echo _('No requests ATM.');
                
            }
            
            $system = new System();
            $system->changeHTML('total-requests', $i);
            
            ?>
                    
            </div>
                    
            <?php
            
        }
        
    }
    
    public function showModal($modalArr, $page){
        
        switch($page){
            case 'requests':
                
                for($i = 0; $i < sizeof($modalArr); $i++){
                                        
                    ?>

                    <div id="request<?php echo $modalArr[$i]['INFO']['REQ_ID']; ?>" class="modal hide">
                        
                        <div class="modal-header">
                            <button data-dismiss="modal" class="close" type="button">×</button>
                            <h3><?php echo _('Join request'); ?></h3>
                        </div>
                        <div class="modal-body">
                            <?php self::show_request($modalArr[$i]['INFO']); ?>
                               
                        </div>
                        <div class="modal-footer">
                            <form action="" method="POST">
                                <input type="hidden" name="act" value="join">
                                <input type="hidden" name="req" value="<?php echo $modalArr[$i]['INFO']['REQ_ID']; ?>">
                                <input type="submit" class="btn btn-success" value="<?php echo _('Approve'); ?>">
                                <a class="btn btn-danger" href="clan?action=admin&reject=<?php echo $modalArr[$i]['INFO']['REQ_ID']; ?>"><?php echo _('Reject'); ?></a>
                                <a data-dismiss="modal" class="btn" href=""><?php echo _('I\'ll decide later'); ?></a>
                            </form>
                        </div>
                    </div>                                                                  
                
                    <?php
                    
                }
                
                break;
            case 'request_deny':
                
                ?>
                
                <div id="reject" class="modal hide"  data-backdrop="static">

                    <div class="modal-header">
                        <button data-dismiss="modal" class="close" type="button">×</button>
                        <h3><?php echo _('Join request'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <p><?php echo _('Are you sure you want to <strong>reject</strong> this request?'); ?></p><br/>
                        
                        <?php self::show_request($modalArr['0']['INFO']); ?>

                    </div>
                    <div class="modal-footer">
                        <form action="" method="POST">
                            <input type="hidden" name="act" value="reject">
                            <input type="hidden" name="req" value="<?php echo $modalArr[0]['INFO']['REQ_ID']; ?>">
                            <input type="submit" class="btn btn-success" value="<?php echo _('Yes, reject'); ?>">
                            <a class="btn btn-danger" href="clan?action=admin"><?php echo _('No, go back.'); ?></a>
                        </form>
                    </div>
                </div> 

                <script>

                window.onload = function () {
                    $('#reject').modal('show');
                }           

                </script>
                
                <?php
                
                break;
            case 'kick':
                
                if(isset($_POST['act'])){
                    if($_POST['act'] == 'kick'){
                        
                        ?>

                        <div id="reject" class="modal hide"  data-backdrop="static">

                            <div class="modal-header">
                                <button data-dismiss="modal" class="close" type="button">×</button>
                                <h3><?php echo _('Kick player'); ?></h3>
                            </div>
                            <div class="modal-body">
                                <p><?php echo sprintf(_('Are you sure you want to <strong>kick</strong> user %s from clan?'), $modalArr[0]['NAME']); ?></p><br/>

                                <form action="" method="POST">
                                
                                    <?php echo _('Password'); ?>: <input type="password" name="pass">(Not needed if you use facebook or twitter)

                                </div>
                                <div class="modal-footer">
                                        <input type="hidden" name="act" value="kick">
                                        <input type="hidden" name="id" value="<?php echo $_POST['id']; ?>">
                                        <input type="hidden" name="text" value="<?php echo $_POST['text']; ?>">
                                        <input type="hidden" name="kickConfirm" value="1">
                                        <input type="submit" class="btn btn-success" value="<?php echo _('Yes, kick'); ?>">
                                        <a class="btn btn-danger" href="clan?action=admin&opt=manage&id=<?php echo $_POST['id']; ?>"><?php echo _('No, go back.'); ?></a>
                                </form>
                            </div>
                        </div> 

                        <script>

                        window.onload = function () {
                            $('#reject').modal('show');
                        }           

                        </script>

                        <?php
                        
                    }
                }
                
                break;
            default:
                echo 'edit modal page';
                break;
        }
        
        ?>
                
        <?php
        
        
    }
    
    public function show_request($requestInfo){
        
        $player = new Player();
        
        ?>
        
        <?php echo _('Asked by'); ?> <a href="profile?id=<?php echo $requestInfo['USER_ID']; ?>"><?php echo $player->getPlayerInfo($requestInfo['USER_ID'])->login; ?></a>
        <?php echo _('on '); echo $requestInfo['ASKED_DATE']; ?>            
        
        <?php
                        
        if($requestInfo['MSG'] != ''){
            echo "<br/><br/><b>"._('Text')."</b>: ".$requestInfo['MSG'];
        }
        
        ?>
                
        <?php
        
    }
    
    public function show_adminPanel(){
        
        ?>

        <div class="widget-box">

            <div class="widget-title">
                <span class="icon"><span class="he16-clan_desc_edit"></span></span>
                <h5><?php echo _('Clan description'); ?></h5>
            </div>

            <div class="widget-content padding">        

                <div class="center">
                    <span class="btn btn-danger edit-clan-desc"><?php echo _('Edit clan description'); ?></span>
                </div>
                
                <form action="" method="POST">
                    <input type="hidden" name="act" value="edit-desc">
                    <div id="clan-desc" style="margin-left: 18%; display: none;">
                        <textarea name="desc-text" id="wysiwyg"><?php echo self::getClanSig($this->myClan); ?></textarea>
                    </div>                
                    <div id="save-desc" class="center" style="display:none;">
                        <br/><br/>
                        <input type="submit" class="btn btn-inverse save-clan-desc" value="<?php echo _('Save '); ?>">
                    </div>
                </form>
                    
            </div>


        </div>       
        
        <div class="row-fluid">

            <div class="span8">

                <div class="widget-box">

                    <?php self::show_listMembers($this->myClan, 1); ?>

                </div>

            </div>

            <div class="span4">

                    <div class="widget-box">

                        <?php self::show_pendingRequests(1); ?>                                                            

                    </div>

                    <div class="widget-box">

                        <?php self::show_updateImage(); ?>



                    </div>                                                     

            </div>

        </div>

        <?php
        
        
        
    }
    
    public function show_updateImage(){
        
        ?>
        
            <div class="widget-title">
                <span class="icon"><span class="he16-image"></span></span>
                <h5><?php echo _('Change clan image'); ?></h5>
            </div>

            <div class="alert alert-error">
                <?php echo _('Will replace current one'); ?>
            </div>

            <div class="widget-content nopadding center">

                <form action="" method="post" enctype="multipart/form-data" class="form-horizontal">
                    
                    <input type="hidden" name="act" value="img">
                    <input type="hidden" name="t" value="2">
                    <input type="file" name="image_upload" />
                    <input type="submit" name="submit" class="btn btn-inverse" value="<?php echo _('Upload'); ?>" />

                </form>
                <br/>
            </div>        
        
        <?php
        
    }
    
    public function createClanCost(){
        
        return 1000;
        
    }
    
    public function show_createClan(){

        ?>
        

        <div class="widget-box text-left">

        <div class="widget-title">
            <span class="icon"><span class="he16-buy"></span></span>
            <h5><?php echo _('Buy Clan'); ?></h5>
        </div>
        <div class="widget-content padding">                   

        
        <?php
        
        $clanCost = self::createClanCost();

        require_once '/var/www/classes/Finances.class.php';
        $finances = new Finances();

        if($finances->totalMoney() >= $clanCost){

            ?>

            <strong><?php echo _('Price'); ?>: </strong>$<?php echo number_format($clanCost); ?></strong>
            <br/><br/>
            
            <form action="#create" method="GET">

                <input type="hidden" name="action" value="create">
                <?php echo _('Name'); ?>: <input type="text" name="cname"><br/>
                <?php echo _('Tag'); ?>: <input type="text" name="ctag" maxlength="3"><br/>
                <?php echo _('Account'); ?>: <?php echo $finances->htmlSelectBankAcc(); ?><br/><br/>

                <input type="submit" class="btn btn-success" value="Buy...">

            </form>

            <?php

            self::showCreateModal();
            
        } else {
            echo _('You dont have money to buy a clan server. You need $').number_format(self::createClanCost());
        }

        ?>

            </div>
        </div>            
            
        <?php
        
    }
    
    public function showCreateModal(){
        
        if(isset($_GET['acc']) && isset($_GET['cname']) && isset($_GET['ctag'])){
        
            $clanCost = self::createClanCost();
            
            $input = '<input type="hidden" name="acc" value="'.$_GET['acc'].'">
                      <input type="hidden" name="ctag" value="'.$_GET['ctag'].'">'."\n".'
                        <input type="hidden" name="cname" value="'.$_GET['cname'].'">'."\n";        

            ?>

                <script>

                window.onload = function () {
                    $('#create').modal('show');
                }           

                </script>

                <div id="create" class="modal hide" data-backdrop="static">
                        <div class="modal-header">
                                <h3><?php echo _('Confirm buy clan'); ?></h3>
                        </div>
                        <div class="modal-body">
                                <p></p>
                                <p><?php echo sprintf(_('Are you sure you want to create the clan %s for %s?'), '<strong>['.$_GET['ctag'].'] '.$_GET['cname'].'</strong>', '<span class="red">$'.number_format($clanCost).'</span>)'); ?></p>
                        </div>
                        <div class="modal-footer">
                            <form action="#" method="POST">
                                <?php echo $input; ?>
                                <input type="hidden" name="act" value="create">
                                <input type="submit" value="<?php echo _('Yes, I want to create it.'); ?>" class="btn btn-primary">
                                <a class="btn" href="clan?action=create"><?php echo _('No, cancel'); ?></a>
                            </form>


                        </div>                
                </div>

            <?php
        
        }
        
    }
    
    public function getUserAuthAndHierarchy($uid=''){
        
        if($uid == ''){
            $uid = $_SESSION['id'];
        }

        $this->session->newQuery();
        $sql = 'SELECT authLevel, hierarchy FROM clan_users WHERE userID = :uid';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':uid' => $uid));
        return $data->fetch(PDO::FETCH_OBJ); 
        
    }
    
    public function show_leavePage($admin = 0){
        
        if($admin == 4){
            $msg = 'You are the admin. Leaving the clan will result in destroying it and any member part of it. F-o-r-e-v-e-r. Think twice.';
        } else {
            $msg = _('This action can not be undone.');
        }
        
        ?>

        <div class="widget-box">

                                
            <div class="widget-title">
                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                <h5><?php echo _('Leave clan'); ?></h5>
            </div>                
                
            <div class="alert alert-error">
                <?php echo $msg; ?>
            </div>

            <div class="widget-content padding">

                <h3><?php echo _('Leave clan'); ?></h3>

                <form action="" method="POST">

                    <input type="hidden" name="act" value="leave">
                    <p><span class="item"><?php echo _('Reason (optional)'); ?>: </span><br/></p>
                    <p><textarea name="text" id="styled" style="width:57%; height: 70px;" onfocus=" setbg('#F5F5F5');" onblur="setbg('white');"></textarea></p>
                    <span class="item"><?php echo _('Password'); ?>: </span> <input type="password" name="pass"size="20"> (Not needed if you use facebook or twitter)
                    <input type="submit" value="<?php echo _('Leave clan'); ?>">

                </form>

            </div>
            
        </div>
        
        
        <?php
        
    }
    
    public function leave($reason, $auth = ''){
        
        $playerClan = self::getPlayerClan();
        $clanIP = self::getClanInfo($playerClan)->clanip;
        
        $sql = 'DELETE FROM clan_users WHERE userID = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $_SESSION['id']));
        
        $sql = 'UPDATE clan SET slotsUsed = slotsUsed - 1 WHERE clanID = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $playerClan));        
        
        self::decreaseMemberCount($playerClan);
        
        if($auth == 4){
            
            // 2019: TODO: This DELETE section is untested. XD
            // 2019: BTW. Who needs transactions anyway?
            //TODO: NAO TESTEI ESSA SEÇÃO DE DELETE's...
            $sql = 'DELETE FROM log WHERE userID = :clanID AND isNPC = 1 LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM npc WHERE npcIP = :clanIP LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanIP' => $clanIP));
            
            $sql = 'DELETE FROM clan_badge WHERE clanID = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));
            
            $sql = 'DELETE FROM clan_ddos WHERE attackerClan = :clanID OR victimClan = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));             
            
            $sql = 'DELETE FROM clan_defcon WHERE attackerClanID = :clanID OR victimClanID = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM clan_requests WHERE clanID = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));             
            
            $sql = 'DELETE FROM clan_users WHERE clanID = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));
            
            $sql = 'DELETE FROM clan_war WHERE clanID1 = :clanID OR clanID2 = :clanID';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM hardware WHERE userID = :clanID AND isNPC = 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));             
            
            $sql = 'DELETE FROM internet_connections WHERE ip = :clanIP';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanIP' => $clanIP)); 
            
            $sql = 'DELETE FROM processes WHERE pVictimID = :clanID AND pNPC = 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM ranking_clan WHERE clanID = :clanID LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM software WHERE userID = :clanID AND isNPC = 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM software_running WHERE userID = :clanID AND isNPC = 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan));             
            
            $sql = 'DELETE FROM software_texts WHERE userID = :clanID AND isNPC = 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanID' => $playerClan)); 
            
            $sql = 'DELETE FROM virus WHERE installedIp = :clanIP';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanIP' => $clanIP));             
            
            $sql = 'UPDATE virus_doom SET status = 2 WHERE doomIP = :clanIP';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanIP' => $clanIP)); 
            
            $sql = 'DELETE FROM lists WHERE ip = :clanIP';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':clanIP' => $clanIP)); 
            
            //TODO: delete phpbb forum
            
        }
        
        require '/var/www/classes/Forum.class.php';
        $forum = new Forum();
        
        $forumUser = $forum->getForumIDByGameID($_SESSION['id']);
        $clanID = $forum->getForumClanID($playerClan);
        $forum->setPermission($forumUser, 'viewer_del', $clanID['forum_id']);
        $forum->setPermission($forumUser, 'viewer_del', $clanID['parent_id']);
        
    }
    
    public function show_playerProfile($uid, $playerInfo = ''){
        
        if($playerInfo == ''){
            $playerInfo = $this->player->getPlayerInfo($uid);
        } else {
            $system = new System();
            $system->changeHTML('link3', $playerInfo->login);
        }

        $this->session->newQuery();
        $sql = 'SELECT memberSince FROM clan_users WHERE userID = :uid LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':uid' => $uid));
        $memberSince = $data->fetch(PDO::FETCH_OBJ)->membersince;

        $ranking = new Ranking();
        $now = new DateTime();                

        $days = $now->diff(new DateTime($memberSince))->d;

        $label = '<span class="label label-success pull-right">New member</span>';

        if($days == 0){
            $days = _('Today');
        } elseif($days == 1){
            $days = _('Yesterday');
        } else {
            if($days > 7){
                $label = '';
            }
            $days .= _(' days ago');
        }

        ?>

            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><span class="he16-profile"></span></span>
                    <h5><?php echo $playerInfo->login; ?></h5>
                    <?php echo $label; ?>
                </div>

                <div class="widget-content nopadding">        

                    <table class="table table-cozy table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td><span class="item"><?php echo _('Reputation'); ?></span></td>
                                <td><?php echo number_format($ranking->exp_getTotal($uid)); ?> <span class="small">(<?php echo _('Ranked'); ?> #<?php echo number_format($ranking->getPlayerRanking($uid)); ?>)</span></td>
                            </tr>
                            <tr>
                                <td><span class="item"><?php echo _('War points'); ?></span></td>
                                <td><span class="small nomargin"><?php echo _('Coming soon'); ?></span></td>
                            </tr>
                            <tr>
                                <td><span class="item"><?php echo _('Member since'); ?></span></td>
                                <td><?php echo $memberSince; ?><span class="small"><?php echo $days; ?></span></td>
                            </tr>
                        </tbody>
                    </table>

                </div>



            </div>

        <?php
        
    }
    
    public function show_manageSelf(){
        
        $clanInfo = self::getClanInfo($this->myClan);
        
        if($clanInfo->members == 1){
            $class = '';
            $text = _('I am sorry but this feature is not yet implemented. Please contact us if you wish to delete (or leave) your clan. Send an in-game mail to renato or post on the forums');
        } else {
            $class = 'red';
            $text = _('You can not delete your clan while there are other members than you');
        }
        
        ?>
        
                    <ul class="quick-actions-horizontal">
                        <li>
                            <a>
                                <i class="icon-" style="background-image: none;"><span class="heicon he32-text_delete"></span></i>
                                <span class="<?php echo $class; ?>">Delete clan</span>
                            </a>
                        </li> (<?php echo $text; ?>)
                    </ul>       
        <br/><br/>
        <a class="btn btn-inverse" href="clan?action=admin"><?php echo _('Back'); ?></a>
        
        <?php
        
    }
    
    public function show_manage($uid, $page){
        
        $playerInfo = $this->player->getPlayerInfo($uid);
        $this->clanInfo = self::getClanInfo($this->myClan);
        $userPrivileges = self::getUserAuthAndHierarchy($uid);
        
        switch($page){
        
            case 'index':
            
                    self::show_playerProfile($uid, $playerInfo);
                
                    ?>
                
                    <button class="btn" onClick="parent.location='?action=admin'"><i class="icon-refresh"></i> <?php echo _('Back to administration panel'); ?></button>
                    <button class="btn" onClick="parent.location='?action=admin&opt=manage'"><i class="icon-refresh"></i> <?php echo _('Member management list'); ?></button>
                
                </div>
                <div class="span4 center">
                    <div class="widget-box">

                        <?php
                        
                        self::show_manageOptions($uid, 'manage');
                        
                        ?>
                        
                    </div>

                <?php
        
                break;
            case 'privileges':
die("Deprecated"); //But I'm going to use it in the future, so do not delete it
                if($userPrivileges->authlevel > 1){
                    
                    $type = 'admin';
                    
                    if($userPrivileges->authlevel == 2){
                        $current = 'Admin';
                        $g = 2;
                    } elseif($userPrivileges->authlevel == 3){
                        $current = 'Co-owner';
                        $g = 3;
                    } else {
                        $current = 'Owner';
                        $g = 4;
                    }
          
                    ?>

                    <h3>Privileges of <?php echo '['.$this->clanInfo->nick.'] '.$playerInfo->login; ?></h3>
                    <br/>
                    <span class="item">Current:</span> <span class="itemcontent"><?php echo $current; ?></span><br/><br/>

                    <?php

                    if($type == 'admin'){

                        ?>    

                        <form action="" method="GET">

                            <input type="hidden" name="action" value="admin">
                            <input type="hidden" name="opt" value="manage">
                            <input type="hidden" name="id" value="<?php echo $uid; ?>">
                            <input type="hidden" name="do" value="privileges">

                            <span class="item">New admin status:</span>
                            <select name="new">
                                <?php

                                for($i=1;$i<5;$i++){

                                    switch($i){

                                        case 1:
                                            $str = 'Member';
                                            break;
                                        case 2:
                                            $str = 'Admin';
                                            break;
                                        case 3:
                                            $str = 'Co-Owner';
                                            break;
                                        case 4:
                                            $str = 'Owner';
                                            break;

                                    }

                                    if($g != $i){
                                    ?>
                                    <option value="<?php echo $i; ?>"><?php echo $str; ?></option>
                                    <?php
                                    }
                                }

                                ?>
                            </select>
                            <input type="submit" value="Change">

                        </form>
                    
                        <br/><br/>
                    
                        <input type="submit" onClick="parent.location='clan?action=admin'" value="Admin panel">
                        <input type="submit" onClick="parent.location='clan?action=admin&opt=manage&id=<?php echo $uid; ?>'" value="Player index">

                        <?php

                    }
                    
                } else {
                    header("Location:clan?action=admin&opt=manage&id=$uid&do=hierarchy");
                    exit();
                }
                
                break;
            case 'hierarchy':
die("Deprecated"); //But I'm going to use it in the future, so do not delete it                
                if($userPrivileges->authlevel == 1){
                    
                    switch($userPrivileges->hierarchy){
                        
                        case 1:
                            $g = 1;
                            $current = 'H1';
                            break;
                        case 2:
                            $g = 2;
                            $current = 'H2';
                            break;
                        case 3:
                            $g = 3;
                            $current = 'H3';
                            break;
                        case 4:
                            $g = 4;
                            $current = 'H4';
                            break;
                        case 5:
                            $g = 5;
                            $current = 'H5';
                            break;
                        
                    }

                    ?>
                    
                    <h3>Hierarchy of <?php echo '['.$this->clanInfo->nick.'] '.$playerInfo->login; ?></h3>
                    <br/>                    
                    
                    <span class="item">Current:</span> <span class="itemcontent"><?php echo $current; ?></span><br/><br/>
                    <form action="" method="GET">

                        <input type="hidden" name="action" value="admin">
                        <input type="hidden" name="opt" value="manage">
                        <input type="hidden" name="id" value="<?php echo $uid; ?>">
                        <input type="hidden" name="do" value="hierarchy">

                        <span class="item">New member status:</span>                    
                        <select name="new">
                        
                    <?php

                    for($i=1;$i<8;$i++){
                        
                        if($g != $i){
                            
                            switch($i){
                                
                                case 1:
                                    $str = 'H1';
                                    break;
                                case 2:
                                    $str = 'H2';
                                    break;
                                case 3:
                                    $str = 'H3';
                                    break;
                                case 4:
                                    $str = 'H4';
                                    break;
                                case 5:
                                    $str = 'H5';
                                    break;
                                case 6:
                                    $str = 'Admin';
                                    break;
                                case 7:
                                    $str = 'Co-Owner';
                                    break;
                            }
                            
                            ?>
                            
                            <option value="<?php echo $i; ?>"><?php echo $str; ?></option>
                        
                            <?php
                            
                        }
                        
                    }

                    ?>
                        
                        </select>
                        <input type="submit" value="Change">
                        
                    </form>
                    <br/><br/>
                    <input type="submit" onClick="parent.location='clan?action=admin'" value="Admin panel">
                    <input type="submit" onClick="parent.location='clan?action=admin&opt=manage&id=<?php echo $uid; ?>'" value="Player index">
                    
                    <?php
                    
                } else {
                    header("Location:clan?action=admin&opt=manage&id=$uid&do=privileges");
                    exit();
                }
                
                break;
            case 'kick':

                $modalArr = Array();
                $modalArr[0]['NAME'] = $playerInfo->login;
                $modalArr[0]['ID'] = $uid;
                
                $system = new System();
                $system->changeHTML('link3', $playerInfo->login);
                
                ?>
                
                    <div class="widget-box">
                        <div class="widget-title">
                            <span class="icon"><i class="fa fa-arrow-right"></i></span>
                            <h5>Kick <?php echo '['.$this->clanInfo->nick.'] '.$playerInfo->login; ?></h5>
                        </div>

                        <div class="widget-content">        

                            <form action="" method="POST">

                                <input type="hidden" name="act" value="kick">
                                <input type="hidden" name="id" value="<?php echo $uid; ?>">
                                <p><textarea name="text" id="styled" style="width:80%; height: 70px;" placeholder="<?php echo _('Please, specify the reason to kick'); ?> <?php echo $playerInfo->login; ?>"></textarea></p>
                                <br/><input type="submit" class="btn btn-danger" value="Kick...">

                            </form>
                            
                        </div> 
                    </div>

                    <?php self::showModal($modalArr, 'kick'); ?>
                
                </div>
                <div class="span4 center">
                    <div class="widget-box">

                        <?php
                        
                        self::show_manageOptions($uid, 'kick');
                        
                        ?>
                        
                    </div>
                    
                <?php    
                    
                break;
        
        }
        
    }
    
    public function show_manageOptions($uid, $page){
        
        if($page == 'manage'){
            
            $liLink = 'clan?action=admin&opt=manage&id='.$uid.'&do=kick';
            $liName = 'Kick from clan';
            $liIcon = 'kick';
            
        } else {
            
            $liLink = 'clan?action=admin&opt=manage&id='.$uid;
            $liName = 'Manage user';
            $liIcon = 'manage';
            
        }
        
        ?>
                
        <div class="widget-title">
            <span class="icon"><span class="he16-actions"></span></span>
            <h5><?php echo _('Actions'); ?></h5>
        </div>

        <div class="widget-content">        

            <ul class="quick-actions-horizontal">
                    <li>
                        <a href="profile?id=<?php echo $uid; ?>">
                            <i class="icon-" style="background-image: none;"><span class="heicon he32-profile"></span></i>
                            <span><?php echo _('View user profile'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="icon-" style="background-image: none;"><span class="heicon he32-mail"></span></i>
                            <span><?php echo _('Send message'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $liLink; ?>">
                            <i class="icon-" style="background-image: none;"><span class="heicon he32-<?php echo $liIcon; ?>"></span></i>
                            <span><?php echo _($liName); ?></span>
                        </a>
                    </li>

                    <li>
                        <a href="#">
                            <i class="icon-" style="background-image: none;"><span class="heicon he32-privileges"></span></i>
                            <span><?php echo _('Edit privileges'); ?></span>
                        </a>
                    </li>
            </ul>       

        </div>                
                
        <?php
        
    }
    
    public function kick($id){
        
        require '/var/www/classes/Forum.class.php';
        $forum = new Forum();
        
        $forumUser = $forum->getForumIDByGameID($id);
        $clanID = $forum->getForumClanID($_SESSION['CLAN_ID']);
        $forum->setPermission($forumUser, 'viewer_del', $clanID['forum_id']);
        $forum->setPermission($forumUser, 'viewer_del', $clanID['parent_id']);
        
        $this->session->newQuery();
        $sql = 'DELETE FROM clan_users WHERE userID = :id LIMIT 1';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':id' => $id));
        
        $this->session->newQuery();
        $sql = 'UPDATE clan SET slotsUsed = slotsUsed - 1 WHERE clanID = :clan';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clan' => self::getPlayerClan()));
        
    }
    
    public function changePrivileges($id, $type, $new){
        
        $this->session->newQuery();
        if($type == 'admin'){
            
            if($new == 1){
                $addStr = ', hierarchy = \'1\'';
            } else {
                $addStr = '';
            }

            $sql = 'UPDATE clan_users SET authLevel = :new WHERE userID = :id';
            $data = $this->pdo->prepare($sql);
            $data->execute(array(':new' => $new.$addStr, ':id' => $id));            
            
        } else {
            
            $sql = 'UPDATE clan_users SET hierarchy = :new WHERE userID = :id';
            $data = $this->pdo->prepare($sql);
            $data->execute(array(':new' => $new, ':id' => $id));

            
        }
        
    }
    
    public function createClan($clanName, $clanNick, $acc){
        
        function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'){

            $str = '';
            $count = strlen($charset);
            while ($length--) {
                $str .= $charset[mt_rand(0, $count-1)];
            }
            return $str;

        }
        
        $creatorID = $_SESSION['id'];
        $clanCost = self::createClanCost();

        $randIP = rand(1,255).'.'.rand(1,255).'.'.rand(1,255).'.'.rand(1,255);

        $this->session->newQuery();
        $sql = 'INSERT INTO npc (id, npcType, npcIP, npcPass) 
                VALUES (\'\', \'10\', :randIP, :pwd)';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':randIP' => ip2long($randIP), ':pwd' => randString(8)));
        
        $clanNPCID = $this->pdo->lastInsertID();
        
        $this->session->newQuery();
        $sql = 'INSERT INTO npc_info_en (npcID, name, web) 
                VALUES (:npcID, :name, :webDesc)';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':npcID' => $clanNPCID, ':name' => $clanName, ':webDesc' => 'Clan server of <strong>'.htmlspecialchars($clanName).'</strong>'));

        $this->session->newQuery();
        $sql = 'INSERT INTO hardware (userID, name, isNPC) 
                VALUES (:clanNPCID, \'Server #1\', \'1\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanNPCID' => $clanNPCID));
        
        $this->session->newQuery();
        $sql = 'INSERT INTO log (userID, text, isNPC) 
                VALUES (:clanNPCID, \'\', \'1\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanNPCID' => $clanNPCID));        
        
        $this->session->newQuery();
        $sql = 'INSERT INTO clan (clanID, clanIP, name, nick, clan.desc, slotsUsed, slotsTotal, createdOn, createdBy, moneyTax, money, power) 
                VALUES (\'\', :randIP, :clanName, :clanNick, \'\', \'1\', \'6\', NOW(), :creatorID, \'20\', \'0\', \'0\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':randIP' => ip2long($randIP), ':clanName' => $clanName, ':clanNick' => $clanNick, ':creatorID' => $creatorID));

        $clanID = $this->pdo->lastInsertID('clanID');
        
        $this->session->newQuery();
        $sql = 'INSERT INTO clan_users (clanID, userID, memberSince, authLevel, hierarchy) 
            VALUES (:clanID, :creatorID, NOW(), \'4\', \'0\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID, ':creatorID' => $creatorID));

        $this->session->newQuery();
        $sql = 'INSERT INTO clan_stats(cid, totalMemberPower, averageMemberPower, averageMemberRanking, totalMoneyEarned, averageMoneyEarned, servers, members, pageClicks) 
                VALUES (:clanID, \'0\', \'0\', \'0\', \'0\', \'0\', \'1\', \'1\', \'0\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID));

        $this->session->newQuery();
        $sql = 'INSERT INTO hist_clans_current (id, cid, clanIP, name, nick, members, won, lost, rate, clicks) 
                VALUES (\'\', :clanID, :randIP, :clanName, :clanNick, \'1\', \'0\', \'0\', \'0\', \'1\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID, ':randIP' => ip2long($randIP), ':clanName' => $clanName, ':clanNick' => $clanNick));        
        
        $this->session->newQuery();
        $sql = 'INSERT INTO ranking_clan (clanID, rank) 
                VALUES (:clanID, \'-1\')';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID));        
        
        $finances = new Finances();
        
        $finances->debtMoney($clanCost, $acc);

        require '/var/www/classes/Forum.class.php';
        $forum = new Forum();

        $forum->createForum($clanName, $clanID);

        $forumID = $forum->getForumIDByGameID($_SESSION['id']);
        $forumClanID = $forum->getForumClanID($clanID);
        $forum->setPermission($forumID, 'viewer', $forumClanID['parent_id']);
        $forum->setPermission($forumID, 'viewer', $forumClanID['forum_id']);
        $forum->setPermission($forumID, 'mod', $forumClanID['forum_id']);
        
        $_SESSION['CLAN_ID'] = $clanID;
        
    }
    
    public function join_request($clanID, $msg, $userID, $adminID = '0'){
        
        if($adminID == '0'){
            // 2019: User asked (to join, I suppose)
            $type = '1'; //user que pediu
        } else {
            // 2019: Admin invited (to join, I suppose)
            $type = '2'; //admin que convidou
        }
        
        $this->session->newQuery();
        $sql = "INSERT INTO clan_requests (id, clanID, userID, adminID, type, askedDate, msg)
                VALUES ('', :clanID, :userID, :adminID, :type, NOW(), :msg)";
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':clanID' => $clanID, ':userID' => $userID, ':adminID' => $adminID, ':type' => $type, ':msg' => $msg));
        
    }
    
    public function defcon_exist($attackerID, $attackerClan, $victimID, $victimClan, $clanServer = 0){
        
        $this->session->newQuery();
        $sql = "SELECT id 
                FROM clan_defcon 
                WHERE 
                    attackerID = :attacker AND 
                    attackerClanID = :attackerClan AND 
                    victimID = :victim AND 
                    victimClanID = :victimClan AND 
                    clanServer = :clanServer
                    LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':attacker' => $attackerID, ':attackerClan' => $attackerClan, ':victim' => $victimID, ':victimClan' => $victimClan, ':clanServer' => $clanServer));
        
        if($stmt->rowCount()){
            return TRUE;
        } else {
            return FALSE;
        }

        
        
    }
    
    public function defcon($attackerID, $attackerClan, $victimID, $victimClan, $clanServer = 0){
        
        $this->session->newQuery();
        $sql = "INSERT INTO clan_defcon (id, attackerID, attackerClanID, victimID, victimClanID, attackDate, clanServer)
                VALUES ('', :attacker, :attackerClan, :victim, :victimClan, NOW(), :clanServer)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':attacker' => $attackerID, ':attackerClan' => $attackerClan, ':victim' => $victimID, ':victimClan' => $victimClan, ':clanServer' => $clanServer));

    }    
    
    public function DDoS_addPoints($type, $amount, $clanID){
        
        switch($type){
            
            // 2019: DDoSing ordinary NPC
            case 1: //DDoSando npc comum
                
                $multiplier = 1;
                
                break;
            // 2019: DDoSing player without clan
            case 2: //DDoSando player sem clan
                
                $multiplier = 3;
                
                break;
            // 2019: DDoSing player with clan but not at war
            case 3: //DDoSando player com clan mas sem guerra
                
                $multiplier = 5;
                
                break;
            // 2019: DDoSing player with clan at war OR DDoSing clan server
            case 4: //DDoSando player com clan e em guerra OU clan server
                
                $multiplier = 10;
                
                break;
            default:
                return FALSE;
                break;
            
        }

        $new = $amount * $multiplier * 0.01; //1% vz o resto aí

        $this->session->newQuery();
        $sql = "UPDATE clan SET power = power + :amount WHERE clanID = :cid  LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':amount' => $new, ':cid' => $clanID));
        
        return $new;
        
    }
    
    public function war_update($vicClan, $ddosPower){

        $myClan = self::getPlayerClan();
        
        $this->session->newQuery();
        $sql = "SELECT clanID1 FROM clan_war WHERE (clanID1 = '".$vicClan."' AND clanID2 = '".$myClan."') OR (clanID2 = '".$vicClan."' AND clanID1 = '".$myClan."') LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if($data['0']['clanid1'] == $myClan){
            $field = 'score1';
        } else {
            $field = 'score2';
        }
        
        //, endDate = DATE_ADD(endDate, INTERVAL 1 HOUR)
        $this->session->newQuery();
        $sql = "UPDATE clan_war SET ".$field." = ".$field." + '".$ddosPower."', bounty = bounty + '".$ddosPower / 10 ."' WHERE (clanID1 = '".$vicClan."' AND clanID2 = '".$myClan."') OR (clanID2 = '".$vicClan."' AND clanID1 = '".$myClan."')";
        $this->pdo->query($sql);
        
    }
    
    public function warHistory_valid($id){
        
        $myClan = self::getPlayerClan();
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM clan_war_history WHERE id = '".$id."' AND (idWinner = '".$myClan."' OR idLoser = '".$myClan."') LIMIT 1";
        $data = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);
        
        if($data->total == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    private function increaseMemberCount($cid){
        
        $this->session->newQuery();
        $sql = "UPDATE clan_stats SET members = members + 1 WHERE cid = '".$cid."' LIMIT 1";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "UPDATE hist_clans_current SET members = members + 1 WHERE cid = '".$cid."' LIMIT 1";
        $this->pdo->query($sql);        
        
    }
    
    private function decreaseMemberCount($cid){
        
        $this->session->newQuery();
        $sql = "UPDATE clan_stats SET members = members - 1 WHERE cid = '".$cid."' LIMIT 1";
        $this->pdo->query($sql); 
        
        $this->session->newQuery();
        $sql = "UPDATE hist_clans_current SET members = members - 1 WHERE cid = '".$cid."' LIMIT 1";
        $this->pdo->query($sql);         
        
    }
    
}

?>
