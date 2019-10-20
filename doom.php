<?php

require '/var/www/classes/Session.class.php';
require '/var/www/classes/System.class.php';
require '/var/www/classes/Storyline.class.php';
require '/var/www/classes/Process.class.php';

$session = new Session();
$system = new System();

require 'template/contentStart.php';

$display = 'current';
$current = 'active';
$failed = '';
if($system->issetGet('show')){
    if($_GET['show'] == 'failed'){
        $display = 'failed';
        $current = '';
        $failed = 'active';
    } else {
        header("Location:doom");
        exit();
    }
}

?>

<div class="span12">
<?php

if($session->issetMsg()){
    $session->returnMsg();
}    

?>
    <div class="widget-box">
        <div class="widget-title">
            <ul class="nav nav-tabs">
                <li class="link <?php echo $current; ?>"><a href="doom"><span class="icon-tab he16-doom"></span><span class="hide-phone">Doom Progress</span></a></li>
                <li class="link <?php echo $failed; ?>"><a href="?show=failed"><span class="icon-tab he16-doom_failed"></span><span class="hide-phone">Failed Attempts</span></a></li>
                <a href="#"><span class="label label-info"><?php echo _("Help"); ?></span></a>
            </ul>
        </div>
        <div class="widget-content padding noborder">
<?php

$storyline = new Storyline();
$storyline->doom_displayProgress($display);

?>
        </div>
        <div class="nav nav-tabs" style="clear: both;">
    </div>
<?php

require 'template/contentEnd.php';

?>