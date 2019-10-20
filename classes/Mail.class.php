<?php

require_once '/var/www/classes/Player.class.php';

class Mail {

    private $pdo;
    private $player;
    private $session;

    private $storyline;
    private $mailInfo;
    
    public function __construct() {

        
        $this->pdo = PDO_DB::factory();
        $this->player = new Player();
        $this->session = new Session();
        
        $this->mailInfo = new stdClass();
        
    }

    public function handlePost(){
        
        $system = new System();
        
        $postRedirect = 'mail';
   
        if(isset($_POST['act'])){
            
            $act = $_POST['act'];
            
            switch($_POST['act']){
                
                case 'new':
                case 'reply':
                    
                    $player = new Player();
                    
                    $redirect = 'mail?action=new';
                    
                    if($act == 'new'){
                    
                        if(!isset($_POST['to'])){
                            $system->handleError('Missing information.', $redirect);
                        }

                        $to = $_POST['to'];

                        if(ctype_digit($to)){
                            $isName = 0;
                            $to = (int)$to;
                        } else {
                            $isName = 1;
                        }

                        if($isName == 1){

                            if(!$system->validate($to, 'username')){
                                $system->handleError(sprintf(_('The username %s is invalid.'), '<strong>'.$to.'</strong>'), $redirect);
                            }
                            
                            if(!$player->issetUser($to)){
                                $system->handleError(sprintf(_('The username %s does not exists.'), '<strong>'.$to.'</strong>'), $redirect);
                            }

                            $userID = $player->getIDByUser($to);

                        } else {

                            if(!$player->verifyID($to)){
                                $system->handleError(sprintf(_('The ID %s is invalid.'), '<strong>'.$to.'</strong>'), $redirect);
                            }

                            $userID = $to;

                        }

                        if($userID == $_SESSION['id']){
                            $system->handleError('Meh. You can not send email to yourself.', $redirect);
                        }
                        
                    } else {
                        
                        $redirect = 'mail';
                        
                        if(!isset($_POST['mid'])){
                            $system->handleError('Missing information.', $redirect);
                        }
                        
                        $mid = $_POST['mid'];
                        
                        if(!ctype_digit($mid)){
                            $system->handleError('Invalid ID.', $redirect);
                        }
                
                        if(!self::issetMail($mid)){
                            $system->handleError('The e-mail you are replying to does not exists.', $redirect);
                        }                        
                        
                        if(self::returnMailTo($mid) != $_SESSION['id']){
                            $system->handleError('You can not reply to this e-mail.', $redirect);
                        }
                        
                        $userID = self::returnMailFrom($mid);
                        
                        if(!$player->verifyID($userID)){
                            $system->handleError('The user you are replying to does not exists.', $redirect);
                        }
                        
                        $redirect = 'mail?id='.$mid;
                        
                    }
                    
                    if(!isset($_POST['subject']) || !isset($_POST['text'])){
                        $system->handleError('Missing information.', $redirect);
                    }
  
                    $subject = $_POST['subject'];
                    $text = $_POST['text'];
                    
                    if($subject == ''){
                        $system->handleError('Subject can not be empty.', $redirect);
                    }
                    
                    if(!$system->validate($subject, 'subject')){
                        $system->handleError('Subject contain invalid digits. Allowed are azAZ09.,!? _-$()"\'', $redirect);
                    }
                    
                    if($text == ''){
                        $system->handleError('Message can not be empty.', $redirect);
                    }
                    
                    self::newMail($userID, $subject, $text, '');
                    
                    $this->session->addMsg('E-mail sent.', 'notice');

                    break;
                case 'delete':
                    
                    if(!isset($_POST['id'])){
                        $system->handleError('Missing information.', 'mail');
                    }

                    $id = $_POST['id'];
                    
                    if(!ctype_digit($id)){
                        $system->handleError('Invalid ID.', 'mail');
                    }
                    
                    if(!self::issetMail($id) || self::isDeleted($id)){
                        $system->handleError('This email does not exists.', 'mail');
                    }
                    
                    if(self::returnMailTo($id) != $_SESSION['id']){
                        $system->handleError('This email does not exists.', 'mail');
                    }
                    
                    self::deleteMail($id);
                    
                    $this->session->addMsg('E-mail deleted.', 'notice');
                    
                    unset($_SESSION['CUR_MAIL']);
                    
                    break;
                
            }
            
        }
        
        header("Location:".$postRedirect);
        exit();

    }
    
    public function getMailTitle($mid){
        
        $valid = 0;
        if(self::issetMail($mid)){
            
            if(self::returnMailFrom($mid) == $_SESSION['id'] || self::returnMailTo($mid) == $_SESSION['id']){
                
                $this->session->newQuery();
                $sql = 'SELECT subject FROM mails WHERE id = :mid AND isDeleted = 0 LIMIT 1';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(array(':mid' => $mid));
                $data = $stmt->fetchAll(); 
                
                if(count($data) == 1){
                    $valid = 1;
                    $subject = $data['0']['subject'];
                }
                
            }
            
        }
        
        if($valid == 1){
            return $subject;
        }
        
    }
    
    public function countMails() {

        $id = $_SESSION['id'];

        $this->session->newQuery();
        $sql = 'SELECT id FROM mails WHERE mails.to = :id AND isDeleted = 0';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        $data = $stmt->fetchAll();


        return count($data);
        
    }

    public function countSentMails() {

        $id = $_SESSION['id'];

        $this->session->newQuery();
        $sql = 'SELECT id FROM mails WHERE mails.from = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        $data = $stmt->fetchAll(); 

        return count($data);
        
    }

    public function countUnreadMails() {

        $id = $_SESSION['id'];

        $this->session->newQuery();
        $sql = 'SELECT id FROM mails WHERE mails.to = :id AND isRead = 0 AND isDeleted = 0';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':id' => $id));
        $data = $stmt->fetchAll();

        return count($data);
        
    }

    public function returnMailTo($mid) {

        $this->session->newQuery();
        $sql = 'SELECT mails.to FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        return $stmt->fetch(PDO::FETCH_OBJ)->to; 
        
    }

    public function returnMailFrom($mid){
        
        $this->session->newQuery();
        $sql = 'SELECT mails.from FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        return $stmt->fetch(PDO::FETCH_OBJ)->from; 
        
    }
    
    public function listMails() {

        $id = $_SESSION['id'];

        if (self::countMails() != '0') {

            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate($id, 'mailAll', '5', 'page', 1);
            $pagination->showPages('5', 'page');
            
        } else {

            echo 'You have no mails';
        }
        
    }

    public function issetMail($mid) {

        $this->session->newQuery();
        $sql = 'SELECT id FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        $data = $stmt->fetchAll(); 

        if (count($data) == '1') {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function unread($mid) {

        $this->session->newQuery();
        $sql = 'SELECT id FROM mails WHERE id = :mid AND isRead = 0 AND isDeleted = 0';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        $data = $stmt->fetchAll(); 

        if (count($data) == '1') {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function setAsRead($mid) {
        
        $this->session->newQuery();
        $sql = 'SELECT isRead FROM mails WHERE id = :mid LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        $isRead = $stmt->fetch(PDO::FETCH_OBJ)->isread; 
                
        if($isRead == 0){

            $this->session->newQuery();
            $sql = 'UPDATE mails SET isRead = 1 WHERE id = :mid LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(':mid' => $mid));

        }
        
    }

    public function showMail($mid) {
        
        self::setAsRead($mid);

        $_SESSION['CUR_MAIL'] = $mid;
        $this->mid = $mid;

        ?>

        <div class="span8">

            <?php

            self::showMail_text();

            ?>
            
        </div>
        <div class="span4">
            
        <?php    
        
            self::showMail_stats();
        
    }
    
    public function html_replyRules(){
        
        ?>

            <div class="reply-rules" style="display:none;">
                <?php self::show_sendMail_rules(); ?>
            </div>
            
        <?php
        
    }
    
    public function html_replyMessage(){
        
        ?>

            <div class="reply-area" style="display:none;">
                <div class="widget-box">                   
                     <div class="widget-title">                                                            
                         <span class="icon"><i class="fa fa-arrow-right"></i></span>
                         <h5><?php echo _('Reply message to '); ?><?php echo $this->mailInfo->sentby; ?></h5>
                     </div>

                     <div class="widget-content nopadding"> 

                         <form action="" method="POST" class="form-horizontal" />
                            <input type="hidden" name="act" value="reply">
                            <input type="hidden" name="mid" value="<?php echo $this->mid; ?>">
                            <div class="control-group">
                                <label class="control-label"><?php echo _('Subject'); ?></label>
                                <div class="controls">
                                    <input type="text" value="Re: <?php echo $this->mailInfo->subject; ?>" name="subject"/>
                                </div>
                            </div>                
                            <div class="control-group">
                                <label class="control-label"><?php echo _('Message'); ?><br/><span class="small link wysiwyg label-editor"><?php echo _('Show editor'); ?></span></label>
                                <div class="controls">
                                    <textarea name="text" id="wysiwyg"></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-inverse"><?php echo _('Reply'); ?></button>
                            </div>
                         </form>                

                     </div>

                 </div>

            </div>
            
        <?php
        
    }
    
    public function showMail_text(){
        
        $this->session->newQuery();
        $sql = 'SELECT mails.to, mails.type, mails.from, subject, text, dateSent, isRead FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $this->mid));
        $this->mailInfo = $stmt->fetch(PDO::FETCH_OBJ);

        $this->mailInfo->sentby = self::mail_getSender($this->mailInfo->from);        
             
        $sentby = $this->mailInfo->sentby;        
        $openLink = $closeLink = '';
        
        $showIP = _('Unknown');
        
        if($this->mailInfo->from > 0){
            $openLink = '<a href="profile?id='.$this->mailInfo->from.'">';
            $closeLink = '</a>';
        } else {
            
            require '/var/www/classes/Storyline.class.php';
            $this->storyline = new Storyline();
            
            switch($this->mailInfo->from){
                case -2:
                    $fbiIP = long2ip($this->storyline->fbi_getIP());
                    $openLink = '<a href="internet?ip='.$fbiIP.'">';
                    $closeLink = '</a>';
                    $showIP = $openLink.$fbiIP.$closeLink;
                    break;
                case -3:
                    $safenetIP = long2ip($this->storyline->safenet_getIP());
                    $openLink = '<a href="internet?ip='.$safenetIP.'">';
                    $closeLink = '</a>';
                    $showIP = $openLink.$safenetIP.$closeLink;
                    break;
            }
        }
        
        $profilePic = $this->player->getProfilePic($this->mailInfo->from, $this->mailInfo->sentby, TRUE);

        
        require '/var/www/classes/Purifier.class.php';
        $purifier = new Purifier();
        $purifier->set_config('mail');

        $formatedText = nl2br($purifier->purify($this->mailInfo->text));
        
        if(strpos($formatedText, 'img')){
                        
            if(!strpos($formatedText, '<img src="../images/emoticons/')){
                
                $purifier = new Purifier();
                $purifier->set_config('text');
                $formatedText = nl2br($purifier->purify($formatedText));
                
                
            }
            
        }

        if(strpos($formatedText, '<a h')){

            if(!strpos($formatedText, '<a href="profile?')){
                
                $purifier = new Purifier();
                $purifier->set_config('text');
                $formatedText = nl2br($purifier->purify($formatedText));
                
                
            }
            
        }
        

        ?>

        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                <h5><?php echo $this->mailInfo->subject; ?></h5>
            </div>            
            <div class="widget-content padding"> 
            
            <ul class="recent-posts">
                <li>
                    <div class="mail-thumb pull-left">
                        <?php
                        echo $openLink; 
?>

                        <img height="60" width="60" title="<?php echo $sentby; ?>" src="<?php echo $profilePic; ?>" />
                        <?php
                        echo $closeLink; 
?>

                    </div>
                    <div class="article-post">
                        <span class="user-info"> <?php echo _('By'); ?>: <?php echo $openLink.$sentby.$closeLink; ?> <?php echo _('on'); ?> <?php echo substr($this->mailInfo->datesent, 0, -3); ?>, IP: <?php echo $showIP; ?>, <?php echo _('Subject'); ?>: <?php echo $this->mailInfo->subject; ?> </span>
                        <p>
                            <?php
                            echo $formatedText;
                            ?>
                        </p>
                    </div>
                </li>
            </ul>                                                

<?php 
if($this->mailInfo->from != $_SESSION['id']){
    
    if($this->mailInfo->from < 0){
        $replyClass = 'disabled';
    } else {
        $replyClass = 'mail-reply';
    }
    
?>

            <br/>

            <span class="btn btn-success <?php echo $replyClass; ?>"><?php echo _('Reply'); ?></span>
            <span class="btn btn-danger mail-delete" value="<?php echo $this->mid; ?>"><?php echo _('Delete'); ?></span>
            <span id="modal"></span>
            
        <?php } ?>
            
            </div>
        </div>

        <?php

        self::html_replyMessage();
        
        $system = new System();
        $system->changeHTML('mail-title', $this->mailInfo->subject);
        
    }
    
    public function showMail_stats(){
        
        
        ?>

        <div class="widget-box">

        <?php

        if($this->mailInfo->from > 0){
            
            require '/var/www/classes/Clan.class.php';

            $player = new Player();
            $ranking = new Ranking();
            $clan = new Clan();

            $idToDisplay = $this->mailInfo->from;
            $nick = $this->mailInfo->sentby;
            
            if($this->mailInfo->from == $_SESSION['id']){
                $idToDisplay = $this->mailInfo->to;
                $nick = $player->getPlayerInfo($idToDisplay)->login;
            }

            $reputation = number_format($ranking->exp_getTotal($idToDisplay, 1));
            $rank = number_format($ranking->getPlayerRanking($idToDisplay, 1));
            
            if($player->isPremium($idToDisplay)){
                $labelColor = 'label-warning';
                $membership = _('Premium');
            } else {
                $labelColor = 'label-success';
                $membership = _('Basic');
            }

            $clanName = '';
            if($clan->playerHaveClan($idToDisplay)){

                $clanInfo = $clan->getClanInfo($clan->getPlayerClan($idToDisplay));

                $clanName = '<a href="clan?id='.$clanInfo->cid.'" class="black">['.$clanInfo->nick.'] '.$clanInfo->name.'</a>';

            }

            ?>

                <div class="widget-title">
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5><?php echo _('User information'); ?></h5>
                    <span class="label <?php echo $labelColor; ?> hide1024"><?php echo $membership; ?></span>
                </div>

                <div class="widget-content nopadding"> 
                    <table class="table table-cozy table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td><span class="item"><?php echo _('Nick'); ?></span></td>
                                <td><?php echo $nick; ?></td>
                            </tr>
                            <tr>
                                <td><span class="item"><?php echo _('Reputation'); ?></span></td>
                                <td><?php echo $reputation; ?><span class="small"><?php echo _('Ranked'); ?> #<?php echo $rank; ?></span></td>
                            </tr>             
                            <?php if($clanName != ''){ ?>
                                <tr>
                                    <td><span class="item"><?php echo _('Clan'); ?></span></td>
                                    <td><?php echo $clanName; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
 
<?php
        } else { 
                        
            $player = new Player();
            $ranking = new Ranking();
            
            $fromID = $this->mailInfo->from;
            $nick = $this->mailInfo->sentby;            
            
            $displayArr = Array();
            
            switch($fromID){
                
                case 0: //unknown
                    
                    break;
                case -1: //evilcorp
                                        
                    $ip = long2ip($this->storyline->evilcorp_getIP());
                                        
                    $displayArr[0]['item'] = 'From';
                    $displayArr[0]['info'] = '<a class="black" href="internet?ip='.$ip.'">'.$nick.'</a>';                    
                    
                    $displayArr[1]['item'] = 'Address';
                    $displayArr[1]['info'] = '<a class="black" href="internet?ip='.$ip.'">'.$ip.'</a>';
                    
                    $displayArr[2]['item'] = 'Motivation';
                    $displayArr[2]['info'] = _('Global domination');
                    
                    break;
                case -2: //fbi
                    
                    $ip = $this->storyline->fbi_getIP();
                    $userIP = $this->player->getPlayerInfo($_SESSION['id'])->gameip;
                    
                    $mailHistory = self::mail_getHistoryInfo();

                    $displayArr[0]['item'] = 'From';
                    $displayArr[0]['info'] = '<a class="black" href="internet?ip='.long2ip($ip).'">'.$nick.'</a>';                         
                    
                    $displayArr[1]['item'] = 'Address';
                    $displayArr[1]['info'] = '<a class="black" href="internet?ip='.long2ip($ip).'">'.long2ip($ip).'</a>';                    

                    if(sizeof($mailHistory) > 0 && $this->mailInfo->type != 3){
                        
                        $bounty = $mailHistory[0]['info1'];
                        $suspectUntil = $mailHistory[0]['infodate'];

                        $displayArr[2]['item'] = 'Bounty';
                        $displayArr[2]['info'] = '<font color="green">$'.number_format($bounty).'</font>';                    

                        $displayArr[3]['item'] = 'Suspect until';
                        $displayArr[3]['info'] = $suspectUntil;                                  

                    }                    
                    
          
                    
                    break;
                case -3: //safenet
                    
                    $ip = $this->storyline->safenet_getIP();
                    $userIP = $this->player->getPlayerInfo($_SESSION['id'])->gameip;
                                        
                    $displayArr[0]['item'] = 'From';
                    $displayArr[0]['info'] = '<a class="black" href="internet?ip='.long2ip($ip).'">'.$nick.'</a>';                         
                    
                    $displayArr[1]['item'] = 'Address';
                    $displayArr[1]['info'] = '<a class="black" href="internet?ip='.long2ip($ip).'">'.long2ip($ip).'</a>';                    
                    
                    
                    
                    break;
                case -7: //evilcorp
                                                                                
                    $displayArr[0]['item'] = 'From';
                    $displayArr[0]['info'] = $nick;                    
             
                    
                    
                    break;
                
            }
            
?>

                <div class="widget-title">
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5><?php echo _('User information'); ?></h5>
                </div>

                <div class="widget-content nopadding"> 
                    <table class="table table-cozy table-bordered table-striped">
                        <tbody>
<?php

for($i = 0; $i < sizeof($displayArr); $i++){
    
?>

                            <tr>
                                <td><span class="item"><?php echo _($displayArr[$i]['item']); ?></span></td>
                                <td><?php echo $displayArr[$i]['info']; ?></td>
                            </tr>                    
<?php                            
    
}

?>
                        </tbody>
                    </table>
                </div>

        <?php } ?>

                 </div>   

        <?php
        
        self::html_replyRules();
        
    }
    
    public function mail_getHistoryInfo(){
 
        $this->session->newQuery();
        $sql = "SELECT infoDate, info1 FROM mails_history WHERE mid = '".$this->mid."' LIMIT 1";
        return $this->pdo->query($sql)->fetchAll();        
        
    }
    
    public function mail_getSender($from = ''){
        
        if($from == ''){
            $from = $this->mailInfo->from;
        }
        
        if($from < 1){

            switch($from){
                case 0:
                    return _('Unknown');
                case -1:
                    return _('Numataka Corporation');
                case -2:
                    return 'FBI';
                case -3:
                    return 'Safenet';
                case -4:
                    return _('Social Clan');
                case -5:
                    return _('Clan news');
                case -6:
                    return 'Social';
                case -7:
                    return _('Badge Advisor');
            }

        } else {
            return $this->player->getPlayerInfo($from)->login;
        }
        
    }
    
    public function listSentMails() {

        $id = $_SESSION['id'];

        require_once '/var/www/classes/Pagination.class.php';
        $pagination = new Pagination();

        $pagination->paginate($id, 'mailSent', '5', 'action=outbox&page', 1);
        $pagination->showPages('5', 'action=outbox&page');
        
    }

    public function isDeleted($mid){
        
        $this->session->newQuery();
        $sql = 'SELECT isDeleted FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        
        if($data->isdeleted == '1'){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function deleteMail($mid) {

        $this->session->newQuery();
        $sql = 'UPDATE mails SET isDeleted = 1 WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));

    }

    public function returnMailInfo($mid) {

        $this->session->newQuery();
        $sql = 'SELECT mails.to, mails.from, text, subject, dateSent, isDeleted FROM mails WHERE id = :mid';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array(':mid' => $mid));
        return $stmt->fetch(PDO::FETCH_OBJ); 
        
    }

    public function show_sendMail(){
        
        ?>

        <div class="span9">

        <?php
        
        self::show_sendMail_form();
        
        ?>
            
        </div>
        <div class="span3">
            
        <?php
        
        self::show_sendMail_rules();
        
    }
    
    public function show_sendMail_form(){
        ?>

        <div class="widget-box reply-area">                   
            <div class="widget-title">                                                            
                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                <h5><?php echo _('Compose new message'); ?></h5>
            </div>

            <div class="widget-content nopadding"> 

                <form action="" method="POST" class="form-horizontal" />
                    <input type="hidden" name="act" value="new">
                    <div class="control-group">
                        <label class="control-label"><?php echo _('To'); ?></label>
                        <div class="controls">
                            <input type="text" placeholder="<?php echo _('Username or ID'); ?>" name="to" autofocus="1"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo _('Subject'); ?></label>
                        <div class="controls">
                            <input type="text" name="subject"/>
                        </div>
                    </div>                
                    <div class="control-group">
                        <label class="control-label"><?php echo _('Message'); ?><br/><span class="small link wysiwyg label-editor hide-editor"><?php echo _('Hide editor'); ?></span></label>
                        <div class="controls">
                            <textarea name="text" id="wysiwyg"></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-inverse"><?php echo _('Send'); ?></button>
                    </div>
                </form>                

            </div>

        </div>

        <?php
    }
    
    public function show_sendMail_rules(){
        
        ?>
            
            <div class="widget-box">                   
                <div class="widget-title">                                                            
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5><?php echo _('Rules'); ?></h5>
                    <span class="label label-important"><span class="hide1024"><?php echo _('Important'); ?></span>!</span>
                </div>

                <div class="widget-content padding"> 

                    <ul>
                        <li><?php echo _('Do not share IPs'); ?></li>
                        <li><?php echo _('Do not offend other players'); ?></li>
                        <li><?php echo _('Be nice'); ?></li>
                    </ul>

                </div>

            </div>     
            
        <?php
        
    }
    
    public function newMail($to, $subject, $text, $type, $from = '') {

        if($from == ''){
            $id = $_SESSION['id'];
        } elseif($from == 'unknown'){
            $id = '0';
        } else {
            $id = $from;
        }                
        
        $this->session->newQuery();
        $sql = "INSERT INTO mails (id, mails.from, mails.to, mails.type, subject, text, dateSent) VALUES ('', ?, ?, ?, ?, ?, NOW())";
        $sqlMail = $this->pdo->prepare($sql);
        $sqlMail->execute(array($id, $to, $type, $subject, $text));
        
        switch($id){
            
            case -2:
                
                if($type == 1 || $type == 2){

                    require_once '/var/www/classes/Storyline.class.php';
                    $storyline = new Storyline();
                    
                    $userIP = $this->player->getPlayerInfo($_SESSION['id'])->gameip;
                    
                    $bounty = $storyline->fbi_getBounty($userIP);
                    $until = $storyline->fbi_until($userIP);
                    
                    $this->session->newQuery();
                    $sql = "INSERT INTO mails_history (mid, infoDate, info1) VALUES (?, ?, ?)";
                    $sqlMail = $this->pdo->prepare($sql);
                    $sqlMail->execute(array($this->pdo->lastInsertId(), $until, $bounty));

                }

                break;
            case -3:
                
                require_once '/var/www/classes/Storyline.class.php';
                $storyline = new Storyline();

                $userIP = $this->player->getPlayerInfo($_SESSION['id'])->gameip;
                $until = $storyline->safenet_until($userIP);

                $this->session->newQuery();
                $sql = "INSERT INTO mails_history (mid, infoDate) VALUES (?, ?)";
                $sqlMail = $this->pdo->prepare($sql);
                $sqlMail->execute(array($this->pdo->lastInsertId(), $until));                
                
                break;
            
        }
        
    }

    public function sendGreetingMail(){
        
        $to = $_SESSION['id'];
        $from = 1;
        $type = 1;
        $subject = _('Welcome to Hacker Experience!');
        $text = sprintf(_('Greetings, %s!<br/><br/>Thanks for trying out Hacker Experience, we are very excited to have you on board. Congratulations on completing the tutorial. It wasn\'t that hard, right? <br/><br/>There are <strong>a lot</strong> more things to do in the game, if you get stuck don\'t hesitate in talking to us.<br/><br/>The fastest way of getting help is posting at our <a href="http://forum.hackerexperience.com/">community board</a>. We will happly guide you through the game. You can also find a great resource information on our <a href="http://wiki.hackerexperience.com/">Wiki page</a>.<br/>Replying to this mail is another option. I\'d be thrilled to talk to you :)<br/><br/>(By the way, I just sent you 1.337 BTC. You can see it on the Finances page. Enjoy!)<br/><br/>Pardon the pun, but we hope you have a great <i>experience</i> here!!<br/><br/>Happy hacking!<br/>Renato.'), $this->player->getPlayerInfo($_SESSION['id'])->login);
        
        self::newMail($to, $subject, $text, $type, $from);
        
    }
    
}

?>