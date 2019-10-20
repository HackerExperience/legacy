<?php

require_once '/var/www/classes/Player.class.php';

class Social {

    private $pdo;
    private $player;
    private $session;
    private $ranking;
    private $clan;
    
    public $profileID;
    private $isClan;
    
    public function __construct() {

        $this->pdo = PDO_DB::factory();
        
        $this->player = new Player();
        $this->session = new Session();
        
    }
    
    public function showProfile($id) {

        if(!isset($_SESSION['CLICKED'])){
            if($id != $_SESSION['id']){
                self::clickProfile($id);
                $_SESSION['CLICKED'] = $id;
            }
        } else {
            if($_SESSION['CLICKED'] != $id && $_SESSION['CLICKED'] != $_SESSION['id']){
                self::clickProfile($id);
                $_SESSION['CLICKED'] = $id;
            }
        }

        $this->profileID = $id;
        
        $l = 'en';
        if($this->session->l == 'pt_BR'){
            $l = 'br';
        }
        
        $generate = FALSE;
        if(file_exists('/var/www/html/profile/'.$id.'_'.$l.'.html')){
            
            if(!self::isProfileValid()){
                $generate = TRUE;
            }
            
        } else {
            $generate = TRUE;
        }

        if($generate){

            require '/var/www/classes/Python.class.php';
            $python = new Python();

            $python->generateProfile($id, $l);

        }

        self::profile_show($l);
        
    }    
    
    
    public function profile_show($l){

        require 'html/profile/'.$this->profileID.'_'.$l.'.html';
  
        $friends = 1;
        if($this->profileID != $_SESSION['id']){
            if(!self::friend_isset($this->profileID) && !self::friend_issetRequest($this->profileID)){
                $friends = 0;
            }
        }
        
?>
<script type="text/javascript">
var fr = <?php echo $friends; ?>;
var uid = <?php echo $this->profileID; ?>;
</script>
<?php

    }
    
    public function isProfileValid(){

        $this->session->newQuery();
        $sql = 'SELECT TIMESTAMPDIFF(SECOND, expireDate, NOW()) AS timeSinceGenerated
                FROM cache_profile
                WHERE userID = \''. $this->profileID .'\'';
        $profileInfo = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($profileInfo) > 0){
            
            if($profileInfo['0']['timesincegenerated'] < 3600){
                return TRUE;
            }
            
        }
        
        return FALSE;
        
    }

    public function clickProfile($id){
                
        $this->session->newQuery();
        $sql = "UPDATE users_stats SET profileViews = profileViews + 1 WHERE uid = '".$id."'";
        $this->pdo->query($sql);
                
    }

    public function profile_search($id){
        
        ?>

                <div class="span8">
                    <div class="widget-box">

                        <div class="widget-title">
                            <span class="icon"><i class="fa fa-arrow-right"></i></span>
                            <h5>Search an users</h5>
                        </div>

                        <div class="widget-content padding">        

                            Search: <input type="text" id="search" autocomplete="off" placeholder="User search is TODO">
                            <br/>
                            <span id="results-text" class="small">Showing results for: <b id="search-string"></b></span>
                            <ul id="results" class="list"></ul>

                        </div>

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
                                url: "ajax",
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
        <div class="span4">
            <div class="widget-box">

                <div class="widget-title">
                    <span class="icon"><i class="fa fa-arrow-right"></i></span>
                    <h5>Most viewed users</h5>
                </div>

                <div class="widget-content padding">        
                    to do
                </div>

            </div>
        </div>

        <?php

    }
    
    public function profile_edit(){
        
        ?>
        
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon"><i class="fa fa-arrow-right"></i></span>
                <h5>Change user image</h5>
            </div>

            <div class="alert alert-error">
                Will replace current one
            </div>

            <div class="widget-content nopadding center">        

                <form action="uploadImage" method="POST" enctype="multipart/form-data" class="form-horizontal">
                    
                    <input type="hidden" name="act" value="img">
                    <input type="hidden" name="t" value="1">
                    <input type="file" name="image_upload" />
                    <input type="submit" name="submit" class="btn btn-inverse" value="Upload" />

                </form>
                <br/>
            </div>   
        </div>
        
        <?php
        
    }

    public function badge_add($badgeID, $user, $clan = ''){

        require_once '/var/www/classes/Python.class.php';
        $python = new Python();

        $python->add_badge($user, $badgeID, $clan);

    }
    
    public function badge_list($clanID){
        
        $this->session->newQuery();
        $sql = 'SELECT
                        clan_badge.badgeID,
                        COUNT(clan_badge.badgeID) AS total
                FROM clan_badge
                JOIN badges_clans
                ON badges_clans.badgeID = clan_badge.badgeID
                WHERE clan_badge.clanID = \''.$clanID.'\'
                GROUP BY clan_badge.badgeID
                ORDER BY badges_clans.priority, badges_clans.badgeID';
        $badgeInfo = $this->pdo->query($sql)->fetchAll();
        
        $system = new System();
        
        $system->changeHTML('total-badges', sizeof($badgeInfo));
        
        if(sizeof($badgeInfo) == 0){
            return;
        }
        
        $badgeHTML = '';
        
        $badgeJSON = json_decode(file_get_contents('json/badges.json'));
        
        for($i = 0; $i < sizeof($badgeInfo); $i++){
            
            $badgeID = $badgeInfo[$i]['badgeid'];

            $badgeName = $badgeJSON->$badgeID->name;
            $badgeDesc = $badgeJSON->$badgeID->desc;

            if($badgeDesc != ''){
                $badgeDesc = ' - '._($badgeDesc);
            }
            
            $title = '<strong>'._($badgeName).'</strong>'.$badgeDesc;
            
            $badgeHTML .= '<img src="images/badges/'.$badgeID.'.png" class="profile-tip" title="'.$title.'" value="'.$badgeID.'">';
            
        }
        
        echo $badgeHTML;
        
    }
    
    public function profile_friends($uid){
        
        $this->session->newQuery();
        $sql = "SELECT userID, friendID, dateAdd FROM users_friends WHERE userID = '".$uid."' OR friendID = '".$uid."' ORDER BY dateAdd ASC";
        $friendInfo = $this->pdo->query($sql)->fetchAll();
                
        if(sizeof($friendInfo) > 0){

?>

    <div class="widget-box">
	<div class="widget-title">
		<span class="icon"><i class="fa fa-arrow-right"></i></span>
		<h5>Friends</h5>
		<a href="profile?id=8&action=friends"><span class="label label-info">8</span></a>
	</div>
	<div class="widget-content padding">
	

<?php            
            
            $totalFriends = sizeof($friendInfo);
            
            $friendsOnLeft = ceil($totalFriends / 2);
            $friendsOnRight = $totalFriends - $friendsOnLeft;
            
?>
            <div class="span6">
<?php

        for($i = 0; $i < $friendsOnLeft; $i++){

            self::friend_display($friendInfo[$i]);
            
        }

?>          
            </div>
            <div class="span6">
<?php

        for($k = $i; $k < $totalFriends; $k++){

            self::friend_display($friendInfo[$k]);
            
        }

?>          
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>



            <?php
            
        } else {
            echo 'This user has no friends :(';
        }
                
    }
    
    public function friend_display($friendInfo){
        
        require_once '/var/www/classes/Clan.class.php';
        require_once '/var/www/classes/Ranking.class.php';
        
        $this->clan = new Clan();
        $this->ranking = new Ranking();
        
        if(isset($_SESSION['PROFILE_ID'])){
            $this->profileID = $_SESSION['PROFILE_ID'];
        } else {
            $this->profileID = $_SESSION['id'];
        }
        
        if($friendInfo['friendid'] == $this->profileID){
            $friendID = $friendInfo['userid'];
        } else {
            $friendID = $friendInfo['friendid'];
        }

        $friendDetails = $this->player->getPlayerInfo($friendID);

        $friendName = $friendDetails->login;
        $friendRep = number_format($this->ranking->exp_getTotal($friendID, 1));
        $friendRank = number_format($this->ranking->getPlayerRanking($friendID, 1));
        
        $clanDisplay = FALSE;
        if($this->clan->playerHaveClan($friendID)){
            $clanID = $this->clan->getPlayerClan($friendID);
            $clanInfo = $this->clan->getClanInfo($clanID);
            
            $clanDisplay = TRUE;
            
        }
        
?>
                <ul class="list">
                    <a href="profile?id=<?php echo $friendID; ?>">
                        <li  class="li-click" title="Friends since <?php echo substr($friendInfo['dateadd'], 0, -9); ?>">
                            <div class="span2 hard-ico">
                                <img src="<?php echo $this->player->getProfilePic($friendID, $friendName, TRUE); ?>">
                            </div>
                            <div class="span10">
                                <div class="list-ip">
                                    <?php echo $friendName; ?>
                                </div>
                                
                                <div class="list-user">
                                    <span class="he16-reputation heicon"></span>
                                    <small><?php echo $friendRep; ?></small>
                                    <span class="he16-ranking heicon"></span>
                                    <small>#<?php echo $friendRank; ?></small>
<?php
if($clanDisplay){
?>
                                    <span class="he16-clan heicon"></span>
                                    <small><a href="clan?id=<?php echo $clanID; ?>"><?php echo $clanInfo->name; ?></a></small>
<?php
}
?>
                                </div>
                            </div>
                            <div style="clear: both;"></div>
                        </li>
                    </a>
                </ul>                
<?php        
        
    }
    
    public function friend_isset($friendID){
        
        $this->session->newQuery();
        $sql = "SELECT userID from users_friends WHERE ( userID = '".$friendID."' AND friendID = '".$_SESSION['id']."' ) OR ( userID = '".$_SESSION['id']."' AND friendID = '".$friendID."' ) LIMIT 1";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function friend_issetRequest($friendID){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM friend_requests WHERE userID = '".$friendID."' AND requestedBy = '".$_SESSION['id']."'";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    public function friend_request($friendID){
        
        $this->session->newQuery();
        $sql = "INSERT INTO friend_requests (id, userID, requestedBy) 
                VALUES ('', '".$friendID."', '".$_SESSION['id']."')";
        $this->pdo->query($sql);
        
        $requestID = $this->pdo->lastInsertID();
        
        require_once '/var/www/classes/Mail.class.php';
        
        $mail = new Mail();
        
        $to = $friendID;
        
        $this->session->newQuery();
        $sqlSelect = "SELECT lang FROM users_language WHERE userID = ".$to." LIMIT 1";
        $userLang = $this->pdo->query($sqlSelect)->fetch(PDO::FETCH_OBJ)->lang;

        $userInfo = $this->player->getPlayerInfo($_SESSION['id']);
        
        if($userLang == 'br'){
            $subject = 'Nova solicitação de amizade de '.$userInfo->login;
            $text = 'Hey! '.$userInfo->login.' adicionou você como amigo. Se você não o conhece, apenas ignore esse email. <br/><a href="profile?view=friends&request='.$requestID.'&opt=add&friend='.$_SESSION['id'].'">Aceitar pedido</a>';
        } else {
            $subject = 'New friend request from '.$userInfo->login;
            $text = 'Hey! '.$userInfo->login.' added you as a friend. If you don\'t know him, just ignore this email. <br/><a href="profile?view=friends&request='.$requestID.'&opt=add&friend='.$_SESSION['id'].'">Accept request</a>';
        }
               
        $mail->newMail($to, $subject, $text, 1, -6);
        
    }
    
    public function friend_validRequest($requestID, $requestedBy){
        
        $this->session->newQuery();
        $sql = "SELECT id FROM friend_requests WHERE id = '".$requestID."' AND userID = '".$_SESSION['id']."' AND requestedBy = '".$requestedBy."'";
        $data = $this->pdo->query($sql)->fetchAll();
        
        if(sizeof($data) == 1){
            return TRUE;
        } else {
            return FALSE;
        }
        
    }
    
    public function friend_add($requestID, $friendID){
        
        $this->session->newQuery();
        $sql = "DELETE FROM friend_requests WHERE id = '".$requestID."'";
        $this->pdo->query($sql);
        
        $this->session->newQuery();
        $sql = "INSERT INTO users_friends (userID, friendID, dateAdd)
                VALUES ('".$_SESSION['id']."', '".$friendID."', NOW())";
        $this->pdo->query($sql);
        
        $myFriends = self::friend_count($_SESSION['id']);
        $hisFriends = self::friend_count($friendID);
        $social = FALSE;
        
        if($myFriends == 10){
            require '/var/www/classes/Social.class.php';
            $social = new Social();
            $social->badge_add(48, $_SESSION['id']);
        } elseif($myFriends == 50){
            require '/var/www/classes/Social.class.php';
            $social = new Social();
            $social->badge_add(49, $_SESSION['id']);
        }
        
        if($hisFriends == 10){
            if(!$social){
                require '/var/www/classes/Social.class.php';
                $social = new Social();
            }
            $social->badge_add(48, $friendID);
        } elseif($hisFriends == 50){
            if(!$social){
                require_once '/var/www/classes/Social.class.php';
                $social = new Social();
            }
            $social->badge_add(49, $friendID);
        }
        
    }
    
    private function friend_count($userID){
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM users_friends WHERE userID = '".$userID."' OR friendID = '".$userID."'";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
    }

}

?>