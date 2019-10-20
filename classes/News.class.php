<?php

require_once '/var/www/classes/Player.class.php';

class News {

    private $pdo;
    private $session;

    private $id;
    private $author;
    private $title;
    private $content;
    private $date;
    private $authorIP;
    private $authorID;
    private $type;
    
    public function __construct() {

        $this->pdo = PDO_DB::factory();
        $this->session = new Session();

    }
    
    public function getID(){
        return $this->id;
    }    
    
    public function getAuthor(){
        return $this->author;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function getContent(){
        return $this->content;
    }
    
    public function getDate(){
        return $this->date;
    }    
    
    public function totalNews(){
                
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total FROM news";
        return $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ)->total;
        
    }
    
    public function findAuthor($author){
        
        switch($author){
            
            case 0:
            case -8:
                return 'Game news';
            case -1:
                return 'Numataka Corp';
            case -2:
                return 'FBI';
            case -5:
                return 'Clan Battle Advisor';
            default:
                return 'Unknown';
            
        }        
        
    }
    
    private function getAuthorIP(){
        
        switch($this->author){

            case 0:
                return 'Unknown';
            case -1:
                return 'evilcorp ip'; //TODO
            case -2:
                return 'FBI'; //TODO
            default:
                return 'Unknown';

        }
        
    }
    
    public function newsIsset($id){
        
        $this->session->newQuery();
        $sql = "SELECT COUNT(*) AS total, author, title, content, date, type FROM news WHERE id = '".$id."' LIMIT 1";
        $newsInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

        if($newsInfo->total == 0){
            
            return FALSE;
            
        }
        
        $this->id = $id;
        $this->author = self::findAuthor($newsInfo->author);
        $this->authorID = $newsInfo->author;
        $this->title = $newsInfo->title;
        $this->content = $newsInfo->content;
        $this->date = $newsInfo->date;
        $this->type = $newsInfo->type;
        
        return TRUE;
        
    }

    public function getImage(){
                
        switch($this->authorID){
            case 0:
                return 'images/profile/tux.png';
            case -1:
                return 'images/profile/tux-ec.png';
            case -2:
                return 'images/profile/tux-fbi.png';
            case -3:
                return 'images/profile/tux-safenet.png';
            case -4:
                return 'images/profile/tux-clan2.png';
            case -5:
                return 'images/profile/tux-clan.png';
            case -6:
                return 'images/profile/tux-social.png';
            case -7:
                return 'images/profile/tux-badges.png';
            case -8:
                return 'images/profile/tux.png';
        }
        
    }
    
    public function show(){
        
        $this->authorIP = self::getAuthorIP();
        
        require '/var/www/classes/Purifier.class.php';
        $purifier = new Purifier();
        $purifier->set_config('news');

        $formatedText = $purifier->purify($this->content);
        
        $image = self::getImage();
        
?>
                                <div class="span12">
                                    <div class="span8">
                                        <ul class="recent-posts">
                                            <li>
                                                <div class="mail-thumb pull-left">
                                                    <img width="60" height="60" alt="User" src="<?php echo $image; ?>" />
                                                </div>
                                                <div class="article-post">
                                                    <strong><?php echo $this->title; ?></strong>
                                                    <p class="news-content">
                                                        <?php echo $this->content;?>

                                                    </p>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="mission-margin"></div>
                                        <a href="news" class="btn btn-info">Back to news</a>
                                    </div>
<?php

self::show_newsSideBar();

?>
                                </div>
<?php
        
    }
    
    public function show_newsSideBar(){
        
?>
                                    <div class="span4">
                                        <div class="widget-box">
                                            <div class="widget-title">
                                                <span class="icon"><span class="he16-asterisk"></span></span>
                                                <h5>News information</h5>
                                            </div>
                                            <div class="widget-content nopadding"> 
                                                <table class="table table-cozy table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <td><span class="item">Author</span></td>
                                                            <td>
                                                                <?php
                                                                echo $this->author;

if($this->authorIP != 'Unknown'){
    
?>
                                                                <span class="small nomargin"> <?php echo long2ip($this->authorIP); ?></span>
<?php
    
}

?>

                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><span class="item">Posted Time</span></td>
                                                            <td><?php echo substr($this->date, 0, -3); ?></td>   
                                                        </tr>   
<?php

self::getAuthorSpecifics();

?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
<?php
        
    }
    
    private function getAuthorSpecifics(){
        
        switch($this->authorID){

            case 0: //game news
                return 'Game News';
                break;
            case -1: //evilcorp
                return '<FBIIP>';
                break;
            case -2:
                
                $player = new Player();
                $ranking = new Ranking();
                
                $newsHistory = self::news_history();
                
                if(sizeof($newsHistory > 0)){
                
                    $name = $player->getPlayerInfo($newsHistory[0]['info1'])->login;
                    $reputation = number_format($ranking->exp_getTotal($newsHistory[0]['info1'], 1));
                    $rank = number_format($ranking->getPlayerRanking($newsHistory[0]['info1'], 1));
                
?>

                                        <tr>
                                            <td><span class="item"><?php echo $name; ?>'s reputation</span></td>
                                            <td><?php echo $reputation; ?><span class="small">Ranked #<?php echo $rank; ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><span class="item">Bounty paid</span></td>
                                            <td><font color="green">$<?php echo number_format($newsHistory[0]['info2']); ?></font></td>
                                        </tr>                                        

<?php

                }

                break;
            case -5: //clan news

                $newsHistory = self::news_history();
                
                if(sizeof($newsHistory > 0)){

                    require '/var/www/classes/Clan.class.php';
                    $clan = new Clan();
                    
                    if(!$clan->issetClan($newsHistory['0']['info1'])){
                        $winnerClanName = 'Unknown';
                        $rank = '?';
                    } else {
                        $clanInfo = $clan->getClanInfo($newsHistory['0']['info1']);
                        
                        $winnerClanName = '['.$clanInfo->nick.'] '.$clanInfo->name;
                        
                        $rank = $clanInfo->rank;
                        
                    }
                    
                    
                    
?>

                                        <tr>
                                            <td><span class="item">Winner Clan</span></td>
                                            <td><?php echo $winnerClanName; ?><span class="small">Ranked #<?php echo $rank; ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><span class="item">Bounty paid</span></td>
                                            <td><font color="green">$<?php echo number_format($newsHistory['0']['info2']); ?></font></td>
                                        </tr>                                        

<?php

                }

                
                break;
            default:
       
                return 'Unknown';
                break;

        }
        
    }
    
    private function news_history(){
        
        $this->session->newQuery();
        $sql = "SELECT infoDate, info1, info2 FROM news_history WHERE newsID = '".$this->id."' LIMIT 1";
        return $this->pdo->query($sql)->fetchAll();  
        
    }
    
    public function news_list(){
                
        if(self::totalNews() > 0){

            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate($_SESSION['id'], 'news', '15', 'page', '1', '0');
            $pagination->showPages('15', 'page');

        } else {
            
            echo 'Ops! There are no news at the moment.';
            
        }

    }
    
    public function listIndex($total){

                $this->session->newQuery();
                $sqlQuery = "SELECT id, title, date FROM news ORDER BY date DESC LIMIT $total";
                $newsInfo = $this->pdo->query($sqlQuery)->fetchAll();


?>
                                <table class="table table-cozy table-bordered table-striped">
                                    <tbody>
<?php

                for($i = 0; $i < sizeof($newsInfo); $i++){
                
?>
                                        <tr>
                                            <td>
                                                <a href="news?id=<?php echo $newsInfo[$i]['id']; ?>"><?php echo $newsInfo[$i]['title']; ?></a></span><span class="small"><?php echo $newsInfo[$i]['date']; ?></span>
                                            </td>
                                        </tr>                                        
<?php
                    
                }

?>
                                    </tbody>
                                </table>
<?php
        
    }
    
    public function news_add($author, $title, $content, $infoArray){

        $sql = 'INSERT INTO news (id, author, title, content, date)
                VALUES (\'\', :author, :title, :content, NOW())';
        $data = $this->pdo->prepare($sql);
        $data->execute(array(':author' => $author, ':title' => $title, ':content' => $content));

        $this->session->newQuery();
        $sql = "INSERT INTO news_history (newsID, info1, info2, infoDate) 
                VALUES ('".$this->pdo->lastInsertId()."', '".$infoArray[0]."', '".$infoArray[1]."', '".$infoArray[2]."')";
        $this->pdo->query($sql);
        
    }
    
}