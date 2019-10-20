<p class="lead center"><?php echo _('Welcome to Hacker Experience!'); ?></p>
<br/>
<?php echo _('Hello there, it\'s nice to have you around. This is a quick guide to explain how the game works.'); ?>
<br/>
<br/>
<?php echo _('You are a freelancer hacker trainned and hired by Numataka Corp. Their exact intention is unknown to the public, but they might be interested in the recent leak by Snowden.'); ?>
<br/><?php echo _('I can\'t speak for them, though. I believe they will soon contact you with their instructions.'); ?>
<br/>
<br/>
<?php echo _('The game itself is pretty easy, and you don\'t need to know any technical detail in order to play it. The gray menu on the left is your main navigation tool. First, let me explain what some pages do:'); ?>
<br/>
<div class='row-fluid'>
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-folder-open"></i>
                </span>
                <h6><?php echo _('Software'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('Here you can manage your softwares. Install, delete, hide, create folders, etc.'); ?>
                <br/>
                <br/>
                <?php echo _('Every software has a purpose. I\'ll show you some softwares later.'); ?>
            </div>
        </div>
    </div>
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-desktop"></i>
                </span>
                <h6><?php echo _('Hardware'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('This one is easy. The hardware page is where you manage your hardware<span class="small nomargin"> (duh)</span>.'); ?>
                <br/>
                <br/>
                <?php echo _('Buy new servers or upgrade your existing ones. Purchase an external disk and safely backup your data.'); ?>
            </div>
        </div>
    </div>
    <div class="span4">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-tasks"></i>
                </span>
                <h6><?php echo _('Tasks'); ?></h6>
            </div>
            <div class="widget-content">
            <?php echo _('Tasks, or processes, are actions that require some amount of time to complete. For example, installing or deleting a software might take a few seconds.'); ?>
            <br/>
            <br/>
            <?php echo _('The time taken to complete a task will depend of several factors, like hardware and software size.'); ?>
            </div>
        </div>
    </div>
</div>
<?php echo _('So far so good, right? These pages are essential, you will use them a lot during the game.'); ?>
<br/>
<?php echo _('Now let\'s take a look on some other pages:'); ?>
<div class='row-fluid'>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-globe"></i>
                </span>
                <h6><?php echo _('Internet'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('You can see the Internet page as a browser. Use it to navigate through every IP address. It\'s here where you will hack and remotly connect to others servers.'); ?>
                <br/>
                <br/>
                <?php echo _('There are some useful pages on the Internet, like banks or riddles. You will find them on the First Whois located at'); ?> <code>1.2.3.4</code> <span class="small nomargin">(<?php echo _('Don\'t worry, this is your home IP address'); ?>)</span>.
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-book"></i>
                </span>
                <h6><?php echo _('Log File'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('The Log File is probably the most important page of the game. <em>Every</em> action you do will be logged here. See the following example:'); ?>
                <br/>
                <pre>2014-06-06 14:49 - localhost logged in to [252.168.144.12] as root</pre>
                <?php echo _('You see, after the date, that the localhost (owner of the log) logged to IP <code>252.168.144.12</code> as root.'); ?>
                <br/>
                <br/>
                <?php echo _('Here is where you will find other\'s IP address, and where they will find you. So always delete the logs after you complete an action.'); ?>
            </div>
        </div>
    </div>
</div>

<div class='clear'>&nbsp;</div>

<?php echo _('Looking good! Let me show you some softwares.'); ?>

<br/>

<div class="center">
<a class="btn btn-success" href="university?opt=certification&learn=1&page=2"><?php echo _('Show me the softwares'); ?></a>
</div>