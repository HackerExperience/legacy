<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Ranking.class.php';

$session = new Session();

if($session->issetLogin()){

    $system = new System();
    
    $ranking = new Ranking();
    
    require 'template/contentStart.php';
    
    $all = ' class="active"';
    $cur = '';


    
    $show = 'game';
    $game = ' active';
    $server = $forum = '';
    if($system->issetGet('show')){
        $showInfo = $system->switchGet('show', 'game', 'server', 'forum');
        
        if($showInfo['ISSET_GET'] == 1){
            switch($showInfo['GET_VALUE']){
                case 'game':
                    $show = 'game';
                    break;
                case 'server':
                    $show = 'server';
                    $game = '';
                    $server = ' active';
                    break;
                case 'forum':
                    $show = 'forum';
                    $game = '';
                    $forum = ' active';
                    break;
            }
        }
        
    }
    
    ?>
    
        <div class="span12">
<?php
    if($session->issetMsg()){
        $session->returnMsg();
    }
?>
            <div class="widget-box ">
                <div class="widget-title">
                    <ul class="nav nav-tabs">                                  
                        <li class="link <?php echo $game; ?>"><a href="stats.php"><span class="icon-tab he16-stats"></span>Game Stats</a></li>
                        <li class="link<?php echo $server; ?>"><a href="?show=server"><span class="icon-tab he16-server_stats"></span>Server Stats</a></li>
                        <li class="link<?php echo $forum; ?>"><a href="?show=forum"><span class="icon-tab he16-forum_stats"></span>Forum Stats</a></li>
                        <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                    </ul>
                </div>
                <div class="widget-content padding noborder">

    <?php


    
    switch($show){
        case 'game':
                $display = '';
                if($system->issetGet('round')){
                    if($_GET['round'] == 'all'){
                        $display = 'all';
                    }
                }

                $ranking->serverStats_list($display);
            break;
        case 'server':
            $system->handleError('Sorry, this page isn\'t implemented yet.', 'stats.php');
            break;
        case 'forum':
            $system->handleError('Sorry, this page isn\'t implemented yet.', 'stats.php');
            break;
    }
    

    
?>
            </div>
            <div class="nav nav-tabs" style="clear: both;"></div>
<?php
    
    require 'template/contentEnd.php';

} else {
    header("Location:index.php");
}

?>