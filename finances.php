<?php

require 'config.php';
require '/var/www/classes/Session.class.php';
require '/var/www/classes/Finances.class.php';

$session = new Session();

require 'template/contentStart.php';

?>
                    <div class="span12 center" style="text-align: center;">
                       <div class="widget-box">
                           <div class="widget-title">
                               <ul class="nav nav-tabs">
                                   <li class="link active"><a href="finances"><span class="icon-tab he16-wallet"></span>Finances</a></li>
                                   <a href="<?php echo $session->help('finances'); ?>"><span class="label label-info"><?php echo _("Help"); ?></span></a>
                               </ul>
                           </div>
                           <div class="widget-content padding noborder">
<?php

$finances = new Finances();

$finances->listFinances();

?>

                            </div>
                            <div style="clear: both;" class="nav nav-tabs">&nbsp;</div>
                               
<?php

require 'template/contentEnd.php';

?>