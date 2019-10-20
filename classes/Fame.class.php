<?php

require '/var/www/classes/Storyline.class.php';

class Fame {
    
    protected $pdo;
    public $session;
    
    public function __construct(){
        
        $this->pdo = PDO_DB::factory();
        $this->session = new Session();
        
    }
    
    public function HallOfFame($display, $roundGet, $top = FALSE){

        $storyline = new Storyline();
        
        $curRound = $storyline->round_current();

        if($_SESSION['ROUND_STATUS'] != 2){
            $curRound -= 1;
        }

        if($roundGet > $curRound){
            $roundGet = '';
        }
                
        if($curRound <= 1 && $_SESSION['ROUND_STATUS'] != 1){
            $system = new System();
            $system->handleError('Hall of Fame is just available after the second round =)', 'ranking');
        }
        
        switch($display){
            case 'user':
                
                $baseLink = '?';
                $pathName = 'user';
                $page = '';
                $th = Array(
                    '0' => '#',
                    '1' => 'User',
                    '2' => 'Reputation',
                    '3' => 'Best Software',
                    '4' => 'Clan'
                );
                
                break;
            case 'clan':
                
                $baseLink = '?show=clan&';
                $page = 'show=clan&';
                $pathName = 'clan';
                $th = Array(
                    '0' => '#',
                    '1' => 'Clan',
                    '2' => 'Power',
                    '3' => 'Win / Losses',
                    '4' => 'Members',
                );
                
                break;
            case 'software':
                
                $baseLink = '?show=software&';
                $page = 'show=software&';
                $pathName = 'soft';
                $th = Array(
                    '0' => '#',
                    '1' => 'Software Name',
                    '2' => 'Version',
                    '3' => 'Owner',
                    '4' => 'Type',
                );
                
                break;
            case 'ddos':
                
                $baseLink = '?show=ddos&';
                $page = 'show=ddos&';
                $pathName = 'ddos';
                $th = Array(
                    '0' => '#',
                    '1' => 'Attacker',
                    '2' => 'Victim',
                    '3' => 'Power',
                    '4' => 'Servers',
                );
                
                break;
        }
        
        if($top){
            $roundGet = 'all';
        }

        if($roundGet == '' && !$top){

            for($j = 0; $j < $curRound + 2; $j++){

                if($j == 0){

                    $title = 'All-time ranking';
                    $sub = '<span class="small">From first round until now</span>';

                    $require = 'html/fame/top_'.$pathName.'_preview.html';

                    $link = $baseLink.'round=all';

                } elseif($j == 1) {

                    if($_SESSION['ROUND_STATUS'] == 0){
                        continue;
                    }
                    
                    $this->session->newQuery();
                    $sql = "SELECT name, startDate, endDate FROM round ORDER BY id DESC LIMIT 1";
                    $roundInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                    $title = 'Current Round';
                    $sub = '<span class="small"> Started on '.substr($roundInfo->startdate, 0, -9).'</span>';

                    $require = 'html/fame/rank_'.$pathName.'_preview.html';
                    
                    $link = 'ranking';
                    if($display != 'user'){
                        $link .= '?show='.$display;
                    }

                } else {

                    $round = $curRound - $j + 2;

                    $this->session->newQuery();
                    $sql = "SELECT name, startDate, endDate FROM round WHERE id = '".$round."' LIMIT 1";
                    $roundInfo = $this->pdo->query($sql)->fetch(PDO::FETCH_OBJ);

                    $title = 'Round #'.$round.' - '.$roundInfo->name;
                    $sub = '<span class="small"> '.substr($roundInfo->startdate, 0, -9).' - '.substr($roundInfo->enddate, 0, -9).'</span>';

                    $require = 'html/fame/'.$round.'_'.$pathName.'_preview.html';

                    $link = $baseLink.'round='.$round;

                }
                
                if($display == 'user'){
                    if($j == 1){
                        $th[3] = 'Hacked Database IPs';
                    } else {
                        $th[3] = 'Best Software';
                    }
                }

    ?>
                        <div style="padding-top:5px;">
                            <center>
                                <span style="font-size: 1.5em;"><?php echo $title; ?></span>
                                <span style="margin-left: 0px;"><?php echo $sub; ?></span>
                            </center>
                        </div><br/>
                        <table class="table table-cozy table-bordered table-striped table-hover with-check">
                            <thead>
                                <tr>
<?php
foreach($th as $thName){
?>
                                    <th><?php echo $thName; ?></th>
<?php
}
?>
                                </tr>
                            </thead>
                            <tbody>
<?php
require $require;
?>
                            </tbody>
                        </table>
                <a href="<?php echo $link; ?>" class="btn btn-default btn-success">View Full List</a><br/><br/><br/>
    <?php

            }

        } else {

            $page .= 'round='.$roundGet.'&page';
            
            require_once '/var/www/classes/Pagination.class.php';
            $pagination = new Pagination();

            $pagination->paginate($top, 'fame', 50, $page, 1);
            $pagination->showPages(50, $page);
            
        }
        
    }
    
}

?>
