<p class="lead center"><?php echo _('Basics of Hacking'); ?></p>
<?php echo _('<em>Hacking</em> is the act of successfully connecting to another server (computer) with special permissions.'); ?><br/>
<?php echo _('The first thing to do is browse to the IP address you want to hack.'); ?>
<br/>
<br/>
<?php echo _('There are two methods of hacking: <em>brute-force attack</em> and <em>exploit attack</em>. We will learn the first one now.'); ?>
<br/><br/>
<?php echo _('Every server is protected with a password. The brute-force method uses the Cracker software to systematically check all possible passwords until the correct one is found.'); ?>
<br/>
<div class='row-fluid'>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-crosshairs"></i>
                </span>
                <h6><?php echo _('Attack'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('Your objective is to discover the root (special user) password. In order to do that, you will use a software called <em>cracker</em>.'); ?>
                <br/><br/>
                <?php echo _('Greater versions means better chance to successfully hack the target IP.'); ?>
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-shield"></i>
                </span>
                <h6><?php echo _('Defense'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('Having complex passwords is a must if you want to be protected. The software <em>password hasher</em> will do this for you.'); ?>
                <br/><br/>
                <?php echo _('Greater versions means better chance to defend yourself from brute-force attempts.'); ?>
            </div>
        </div>
    </div>
</div>
<div class="alert alert-info">
    <?php echo _('<strong>Hacking math!</strong> The math is simple: if your <em>cracker</em> version is better than or equal to the victim\'s <em>hasher</em> version, you are in.'); ?>
</div>

<?php echo _('Hacked IPs go to the <em>Hacked Database</em>.'); ?><br/>
<?php echo _('There, you are able to manage every server you\'ve hacked. You can also manage your installed viruses, and even collect their money.'); ?>

<br/>
<br/>

<div class="center">
    <a class="btn btn-success" href="university?opt=certification&learn=2&page=2"><?php echo _('Don\'t forget the logs'); ?></a>
</div>