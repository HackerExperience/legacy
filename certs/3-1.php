<p class="lead center"><?php echo _('Intermediate Hacking'); ?></p>
<?php echo _('Hey there. Now that you know your way around brute-forcing servers, why not learn a new attacking method, huh?'); ?><br/>
<?php echo _('On this certification you will also learn how to hack a bank account. It\'s quite easy.'); ?>
<br/>
<br/>
<?php echo _('Let\'s start with the new method first. It is called <em>exploit attack</em>.'); ?>
<br/><br/>
<?php echo _('Every online server have a few essential services running. Two of them are <em>SSH</em> and <em>FTP</em> services. Each one has it\'s own function, as described below.'); ?><br/>
<?php echo _('As the attack name says, you will exploit possible vulnerabilities of these services.'); ?>
<br/>
<div class='row-fluid'>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-download"></i>
                </span>
                <h6><?php echo _('FTP Service'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('The FTP (File Transfer Protocol) is responsible for transferring files. In other words, you are using FTP when downloading or uploading files.'); ?>
                <br/><br/>
                <?php echo _('Exploit this service and you will be able to download and upload files to the victim.'); ?>
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="widget-box">
            <div class="widget-title">
                <span class="icon">
                    <i class="fa fa-terminal"></i>
                </span>
                <h6><?php echo _('SSH Service'); ?></h6>
            </div>
            <div class="widget-content">
                <?php echo _('The SSH service, as known as Secure Shell, is a remote command-line and command execution service. It allows you to perform CPU-related actions.'); ?>
                <br/><br/>
                <?php echo _('Exploit this service and you will be able to (un)install, (un)hide and delete files.'); ?>
            </div>
        </div>
    </div>
</div>

<?php echo _('The hacking math is simple here too. You will attack the victim using <em>exploit</em> softwares (one for each service), and the victim will defend using a <em>firewall</em>. If the exploit  version is greater than or equal to the firewall\'s version, you are in.'); ?><br/>
    <?php echo _('There is a third software, though: the <em>port scanner</em>. It will tell you if the victim is vulnerable to your exploits.'); ?>

<br/>
<br/>

<?php echo _('Just a few more notes:'); ?><br/>
- <?php echo _('You can exploit more than one service at the same time, if the victim is vulnerable. In this case, you will login as root and have full permissions.'); ?><br/>
- <?php echo _('Regardless of your permissions, you will always be able to read and edit the log file.'); ?><br/>
- <?php echo _('If one of your exploits (or both) stop working, your permissions will be downgraded and/or you will be disconnected.'); ?><br/>
- <?php echo _('You can see your exploited servers on the Hacked Database.'); ?>

<br/>
<br/>

<div class="center">
    <a class="btn btn-success" href="university?opt=certification&learn=3&page=2"><?php echo _('Ok. What about the bank?'); ?></a>
</div>
