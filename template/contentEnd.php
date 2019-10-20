<?php

$requestURI = $_SERVER['REQUEST_URI'];
$phpSelf = $_SERVER['PHP_SELF'];

if(!strpos($requestURI, '.php')){
    $requestURI .= '.php';
}

$crudePage = substr(substr($phpSelf, 1), 0, -4);

?>
                        </div>
                    </div>
                </div>
<?php 

if($crudePage != 'internet' && $crudePage != 'processes'){ 
    echo '            </div>'; //pra ficar ~bunitim e formatado~ no html.
}



switch($crudePage){
    
    case 'processes':
    case 'mail':
    case 'university':
        if(isset($_GET['learn'])) break;
    case 'software':
    case 'hardware':
    case 'finances':
    case 'list':
    case 'clan':
        if($_SESSION['premium'] == 1) break;
    ?>
                
        <div class="center" style="margin-bottom: 20px;">

            <div id="lower-ad">
<style type="text/css">
@media (min-width : 320px) and (max-width : 480px) { .adslot_lower { width: 300px; height: 50px; } }
@media (min-width : 768px) and (max-width : 1024px) { .adslot_lower { width: 320px; height: 100px; } }
@media (min-width:1024px) { .adslot_lower { width: 728px; height: 90px; } }
@media (min-width:1280px) { .adslot_lower { width: 728px; height: 90px; } }
@media (min-width:1366px) { .adslot_lower { width: 970px; height: 90px; } }
@media (min-width:1824px) { .adslot_lower { width: 970px; height: 90px; } }
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- lower responsive -->
<ins class="adsbygoogle adslot_lower"
     style="display:inline-block"
     data-ad-client="ca-pub-7193007468156667"
     data-ad-slot="4300176557"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
            </div>
            
        </div>
                
    <?php
        break;
    
}


$queries = 0;
if(isset($_SESSION['QUERY_COUNT'])){
    $queries = $_SESSION['QUERY_COUNT'];
    $_SESSION['QUERY_COUNT'] = 0;
    $_SESSION['BUFFER_QUERY'] += $queries;
}

if(isset($_SESSION['EXEC_TIME'])){
    $time = (round((microtime(true) - $_SESSION['EXEC_TIME']), 3))*1000;
?>

        <div id="breadcrumb" class="center">
            <span class="pull-left hide-phone" style="margin-left: 10px;"><a href="legal" ><font color=""><?php echo _("Terms of Use"); ?></font></a></span>
            <span class="pull-left hide-phone"><a href="https://forum.hackerexperience.com/" ><font color=""><?php echo _("Forum"); ?></font></a></span>
            <span class="pull-left hide-phone"><a href="stats" ><?php echo _("Stats"); ?></a></span>
            
            <span class="center">2014 &copy; <b>NeoArt Labs</b><a href="https://status.hackerexperience.com/"><?php echo $queries; ?> <?php echo _("queries in"); ?> <?php echo $time; ?> ms</a></span>
            
            <span id="credits" class="pull-right hide-phone link"><a><?php echo _("Credits"); ?></a></span>
            <span id="report-bug" class="pull-right hide-phone link"><a><?php echo _("Report Bug"); ?></a></span>
            <span class="pull-right hide-phone"><a href="premium" ><font color=""><?php echo _("Premium"); ?></font></a></span>
            <span class="pull-right hide-phone"><a href="changelog">v1.0.12</a></span>
<?php
}
?>
        </div>
        <!--[if IE]><script src="js/excanvas.min.js"></script><![endif]-->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/jquery.flot.min.js"></script>
        <!--<script src="js/jquery.flot.resize.min.js"></script>-->
        <!--<script src="js/jquery.peity.min.js"></script>-->
        <script src="js/jquery.validate.js"></script> <!-- tmp -->
        
        

<?php

$queryProcess = 0;

switch($crudePage){

    case 'processes':
        $queryProcess = 1;
        $strProcess = '';
        if($phpSelf != $requestURI){
            if(isset($_GET['page'])){
                switch($_GET['page']){
                    case 'running':
                        $queryProcess = 0;
                        break;
                    case 'cpu':
                        $strProcess = ' AND (pAction <> 1 AND pAction <> 2) ';
                        break;
                    case 'net':
                        $strProcess = ' AND (pAction = 1 OR pAction = 2) ';
                        break;
                }
            }
        }

        break;
        
}

$valid = $issetPLoad = $issetPDoom = FALSE;
if(isset($_SESSION['pLoad'])){
    $issetPLoad = $valid = TRUE;
} elseif(isset($_SESSION['pDoom'])){
    $issetPDoom = $valid = TRUE;
    unset($_SESSION['pDoom']);
}

if($queryProcess || $issetPLoad || $issetPDoom){

    if(!$issetPLoad){

        $uid = $_SESSION['id'];

        if(!is_numeric($uid)){
            exit();
        }

        if($issetPDoom){
            $sql = "SELECT doomID, TIMESTAMPDIFF(SECOND, NOW(), doomDate) AS pTimeLeft FROM virus_doom WHERE status = 1 ORDER BY doomDate DESC";
        } else {
            $sql = "SELECT pid, isPaused, TIMESTAMPDIFF(SECOND, NOW(), pTimeEnd) AS pTimeLeft FROM processes WHERE pcreatorid = $uid $strProcess ORDER BY ptimeend DESC";
        }

        $pData = $pdo->query($sql)->fetchAll();  
        
        if(sizeof($pData) > 0){
            $valid = TRUE;
        }

    }
    
    if($valid){

?>
<script src="js/jquery.ui.custom.js"></script>
<script type="text/javascript">

$(document).ready(function(){
jQuery.fn.anim_progressbar = function (aOpts) {
var iCms = 1000;
var iMms = 60 * iCms;
var iHms = 3600 * iCms;
var iDms = 24 * 3600 * iCms;
var vPb = this;

// each progress bar
return this.each(
    function() {
        var iDuration = aOpts.finish - aOpts.start;
        $(vPb).children('.pbar').progressbar();
        var vInterval = setInterval(
            function(){
                
                var iLeftMs = aOpts.finish - new Date(); // left time in MS
                var iElapsedMs = new Date() - aOpts.start, // elapsed time in MS
                    iDays = parseInt(iLeftMs / iDms), // elapsed days
                    iHours = parseInt((iLeftMs - (iDays * iDms)) / iHms), // elapsed hours
                    iMin = parseInt((iLeftMs - (iDays * iDms) - (iHours * iHms)) / iMms), // elapsed minutes
                    iSec = parseInt((iLeftMs - (iDays * iDms) - (iMin * iMms) - (iHours * iHms)) / iCms), // elapsed seconds
                    iPerc = (iElapsedMs > 0) ? iElapsedMs / iDuration * 100 : 0; // percentages

                // display current positions and progress
                $(vPb).children('.percent').html('<b>'+iPerc.toFixed(1)+'%</b>');
                $(vPb).children('.elapsed').html(iHours+'h:'+iMin+'m:'+iSec+'s</b>');
                $(vPb).children('.pbar').children('.ui-progressbar-value').css('width', iPerc+'%');

                // in case of Finish
                if (iPerc >= 100) {
                    clearInterval(vInterval);
                    $(vPb).children('.percent').html('<b>100%</b>');
                    $(vPb).children('.elapsed').html('<b><?php echo _('Finished'); ?></b>');

                    if(aOpts.loaded){

                        <?php
                        if($issetPLoad){
                            ?>

                            $.ajax({
                                type: "POST",
                                url: "ajax.php",
                                data: {func: 'completeProcess', id: '<?php echo $_SESSION['pLoadID'];?>'}, 
                                 success:function(data) {
                                     if(data.status == 'OK'){
                                        window.location = data.redirect;
                                     } else {
                                         location.reload();
                                     }
                                 }
                            });

                            <?php
                        } else { ?>
                            
                            document.getElementById('complete'+aOpts.id).innerHTML = '<form action="" method="GET"><input type="hidden" name="pid" value="'+aOpts.id+'"><input type="submit" class="btn btn-mini" value="<?php echo _('Complete'); ?>"></form>';                                               
                        <?php } ?>
                                                    
                    }
                                                
                } else {


                }
            } ,aOpts.interval
        );
    }
);
}

var iNow = new Date().setTime(new Date().getTime() -1);<?php

        if(!$issetPLoad){

            for($i = 0; $i < sizeof($pData); $i++){

                $loaded = 'true';
                if($pData[$i]['ptimeleft'] < 0){
                    $pData[$i]['ptimeleft'] = 0;
                    $loaded = 'false';
                }

                if($issetPDoom){
                    $id = $pData[$i]['doomid'];
                    $paused = 0;
                } else {
                    $id = $pData[$i]['pid'];
                    $paused = $pData[$i]['ispaused'];
                }
                
                if($paused == 0){

                    ?>
var iEnd=new Date().setTime(new Date().getTime()+<?php echo $pData[$i]['ptimeleft']; ?>*1000);
$('#process<?php echo $id; ?>').anim_progressbar({start:iNow,finish:iEnd,interval:100,id:<?php echo $id; ?>,loaded:<?php echo $loaded; ?>});<?php

                }

            }

        } else {

            if($_SESSION['pLoad'] != 'p'){

                ?>
                 var iEnd=new Date().setTime(new Date().getTime()+<?php echo $_SESSION['pLoad']; ?>*1000);
                 $('#process0').anim_progressbar({start:iNow,finish:iEnd,interval:100,id:<?php echo $_SESSION['pLoadID'];?>,loaded:true});<?php

            }
            
            unset($_SESSION['pLoad']);
            unset($_SESSION['pLoadID']);

        }

        ?>
});      

</script>
<?php   

    }

}

?>
        
        <script src="js/main.js"></script>

<?php
if($crudePage == 'internet'){
   if(isset($_SESSION['START_NPC'])){
?>

<script type="text/javascript">
        function start(method) {
            if (window.$){
                $.getScript("js/npc.js", function(){bitcoin();})
            } else {
                setTimeout(function(){start(method);}, 50);
            }
        }

        start();    
        
</script>

<?php
   }
}
?>
    </body>
<!--
    Hello! I've just got to let you know.
    www.neoartgames.com
-->
</html>
<?php

if(array_key_exists('BUFFER_QUERY', $_SESSION)){
    if($_SESSION['BUFFER_QUERY'] >= 500 || rand(1,20) == 1){
        exec('/usr/bin/env python /var/www/python/query_counter.py '.$_SESSION['BUFFER_QUERY']);
        $_SESSION['BUFFER_QUERY'] = 0;
    }
}

?>
