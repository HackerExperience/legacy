<p class="lead center"><?php echo _('Intermediate Hacking'); ?></p>
<?php echo _('Let\'s hack a bank! Now shit just got real. It\'s quite simple, tough.'); ?>

<br/>
<br/>

<?php echo _('As you might have noted, you have a bank account. This account have a six-digit number and is related to a bank. It also have a password.'); ?><br/>
<?php echo _('You can login to your account by going to your bank, inserting the number and password and pressing enter.'); ?>

<br/>
<br/>

<?php echo _('Cracking a bank account is as simple as cracking a server password. You will only need the account number, which you will eventually get from someone\'s log.'); ?><br/>
<?php echo _('In order to find it\'s password, you will use the <em>cracker software</em>, the same you use to brute-force servers.'); ?>

<br/>
<br/>

<?php echo _('If your cracker version is greater than or equal to the <em>bank\'s</em> password hash version, than you will be able to crack the account.'); ?>

<br/>
<br/>

<?php echo _('That\'s it. Simple, right? But this isn\'t the hard part.'); ?>

<br/>
<br/>

<?php echo _('Every time you login to an account, it\'s number will be stored on the bank\'s log file (and yours, of course).'); ?><br/>
<?php echo _('Money transfers are logged too. If you are transferring money from accounts located in different banks, the transaction details will be logged on <b>both</b> banks.'); ?>

<br/>
<br/>

<?php echo _('Please note:'); ?><br/>
- <?php echo _('When hacking an account, you won\'t have access to the bank server. Hack the bank server if you want so.'); ?><br/>
- <?php echo _('You can login to a bank account and then login to the bank server. You can\'t login first on the bank and then on the account.'); ?>

<br/>
<br/>

<div class="center">
    <a class="btn btn-danger" href="university?opt=certification&learn=3&page=1"><?php echo _('Previous page'); ?></a> | <a class="btn btn-success" href="university?opt=certification&complete=<?php echo md5('cert3'.$_SESSION['id']); ?>"><?php echo _('Complete this certification'); ?></a>
</div>