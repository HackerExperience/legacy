<p class="lead center"><?php echo _('Getting Help'); ?></p>
<div class='row-fluid'>
    <div class='span12'>
        <div class="span7">
<?php echo _('If you find any trouble during your game play, there are several ways to look for help.'); ?>
<br/>
<br/>
<?php echo _('We have a wiki page with detailed information about important game aspects. Take a look there later.'); ?><br/>
<?php echo _('Also, every game page will have, on the upper-right corner, a link to the corresponding wiki page.'); ?>
<br/>
<br/>
<?php echo _('We also have an active community at our forum (or Bulletin Board System, if you are old school).'); ?><br/>
<?php echo _('Introduce yourself there. It\'s always great to make internet friends :)'); ?>
<br/>
<br/>
<?php echo _('If you prefer, you can mail us at'); ?> <code><?php echo _('contact@hackerexperience.com'); ?></code>.<br/>
<?php echo _('We will be thrilled to reply you as soon as possible.'); ?>
<br/>
        </div>
        <div class="span5">
            <div class="widget-box">
                <div class="widget-title">
                    <span class="icon">
                        <i class="fa fa-sitemap"></i>
                    </span>
                    <h6>Links</h6>
                </div>
                <div class="widget-content center">
                    <strong>Wiki</strong><br/>
                    <a href="https://wiki.hackerexperience.com">https://wiki.hackerexperience.com/</a>
                    <br/>
                    <br/>
                    <strong><?php echo _('Forum'); ?></strong><br/>
                    <a href="https://forum.hackerexperience.com">https://forum.hackerexperience.com/</a>
                </div>
            </div>
        </div>
    </div>
</div>

<br/><br/>

<div class="center">
    <a class="btn btn-danger" href="university?opt=certification&learn=1&page=2"><?php echo _('Previous page'); ?></a> | <a class="btn btn-success" href="university?opt=certification&complete=<?php echo md5('cert1'.$_SESSION['id']); ?>"><?php echo _('Complete this certification'); ?></a>
</div>