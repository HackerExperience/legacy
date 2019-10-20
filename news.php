<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Player.class.php';
require '/var/www/classes/Storyline.class.php';
require '/var/www/classes/News.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$news = new News();


$newsStr = 'active';
$titleStr = '';

$gotNew = 0;

if($system->issetGet('id')){

    $idInfo = $system->verifyNumericGet('id');

    if($idInfo['IS_NUMERIC'] == 0){
        $system->handleError('Invalid ID', 'news.php');
    }

    if(!$news->newsIsset($idInfo['GET_VALUE'])){
        $system->handleError('This news does not exists.', 'news.php');
    }

    $titleStr = 'active';
    $newsStr = '';
    $gotNew = 1;

}

?>
                <div class="span12">
                    <div class="widget-box">
                        <div class="widget-title">
                            <ul class="nav nav-tabs">
                                <li class="link <?php echo $newsStr; ?>"><a href="news.php"><span class="he16-news icon-tab"></span>News</a></li>
<?php
if($gotNew == 1){
?>
                                <li class="link <?php echo $titleStr; ?>"><a href="news?id=<?php echo $idInfo['GET_VALUE']; ?>"><span class="he16-news_list icon-tab"></span><?php echo $news->getTitle(); ?></a></li>
<?php
}
?>
                                <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                            </ul>
                        </div>
                        <div class="widget-content padding noborder">
<?php

if($gotNew == 0){

    $news->news_list();

} else {

    $news->show();

}

?>
                        </div>
                        <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
<?php                    

require 'template/contentEnd.php';


?>