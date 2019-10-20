<?php

// 2019: If you host your own public game server, please update the Privacy Policy and TOS accordingly.

$l = 'en_US';

if(isset($_SERVER['HTTP_HOST'])){
    if($_SERVER['HTTP_HOST'] == 'br.hackerexperience.com' || $_SERVER['HTTP_HOST'] == 'www.br.hackerexperience.com'){
        $l = 'pt_BR';
    }
}

putenv("LANG=" . $l);
setlocale(LC_ALL, $l);

$domain = "messages";
bindtextdomain($domain, "locale");
bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);

?>
<html>
<head>

<meta charset="UTF-8">
<title><?php echo _('Terms of Service'); ?></title>

<style>

body {
	margin-left: 100px;
	margin-right: 100px;
}

h1, h6 {
	text-align: center;
}

li {
	font-weight: bold;
}

ul {
	margin: 8px;
}

</style>

</head>
<body>

<h1><?php echo _('Terms of Service'); ?></h1>
<h6>Hacker Experience TOS</h6>

<?php echo _('By using Hacker Experience, a service provided by NeoArt Labs, you are agreeing to be bound by the following terms and conditions.  Violation of any of the terms of service which are stated below will result in temporary or permanent termination of your account. If you do not agree with any of these terms, you must not use Hacker Experience or any subdomain within www.hackerexperience.com.'); ?><br/><br/>

<li>1 - <?php echo _('You agree'); ?></li>

<ul>1.1 - <?php echo _('to respect every member of the game, regardless of gender, sexual orientation, sexual identity, religion, age, civil status, race, ethnicity or any other trait.'); ?></ul>
<ul>1.2 - <?php echo _('to have only one account. Multiple accounts per IP are not allowed and will result on the termination of all of them. If you use a shared environment, contact us.'); ?></ul>
<ul>1.3 - <?php echo _('to not post or publish offensive content on the forum, the in-game mail system, the logs or any other user-generated content system.'); ?></ul>
<ul>1.4 - <?php echo _('to not exploit any vulnerability or system flaw you might encounter.'); ?></ul>
<ul>1.5 - <?php echo _('that the use of tools to try to hack or (D)DoS the game is forbidden and legal measures might be taken.'); ?></ul>
<ul>1.6 - <?php echo _('that you must not violate any law or make any unauthorized or illegal use while, and by, playing the game.'); ?></ul>
<ul>1.7 - <?php echo _('that your use of Hacker Experience is at your sole risk. The service is provided on an “as is” basis.'); ?></ul>
<ul>1.8 - <?php echo _('that NeoArt Labs does not warrant that the service will answer your needs, will be free of errors, will be secure or will be available at all times.'); ?></ul>
<ul>1.9 - <?php echo _('and expressly understand that NeoArt Labs shall not be liable for any direct or indirect damages, including but not limited to damages for loss of data, profits, or other intangible losses resulting from the direct or indirect use of the service.'); ?> </ul>
<ul>1.10 - <?php echo _('that NeoArt Labs holds its rights to remove, or not to remove, any content which us unlawful or offensive.'); ?> </ul>
<ul>1.11 - <?php echo _('that Hacker Experience is a free service and it can be shutdown at any moment without prior notice.'); ?></ul>
<ul>1.12 - <?php echo _('that you will be held responsible for every content and activity held under your account.'); ?></ul>
<ul>1.13 - <?php echo _('with our <a target="__blank" href="privacy">Privacy Policy</a>.'); ?></ul>
<ul>1.14 - <?php echo _('that any written abuse or threat made in an account will result in the immediate termination of that account.'); ?></ul>
<ul><strong>1.15 - <?php echo _('that sharing non-public IPs on the forum is prohibited and might lead to account termination.'); ?></strong></ul>
<ul><strong>1.16 - <?php echo _('that the use of any tool or script to gain unfair advantage over other players is forbidden.'); ?></strong></ul>
<ul>1.17 - <?php echo _('that objects in mirror are closer than they appear.'); ?></ul>

<li>2 - <?php echo _('You acknowledge that'); ?></li>

<ul>2.1 - <?php echo _('this is a work of fiction. Names, characters, businesses, places, events and incidents are either the products of the author\'s imagination or used in a fictitious manner. Any resemblance to actual persons, living or dead, or actual events is purely coincidental.'); ?></ul>
<ul>2.2 - <?php echo _('the game does not use any real hacking technique. It\'s entirely fictional.'); ?></ul>
<ul>2.3 - <?php echo _('no potential real hacking knowledge can be learned from the game mechanics.'); ?></ul>


<li>3 - <?php echo _('We guarantee that'); ?></li>

<ul>3.1 - <?php echo _('every in-game IP is randomly generated and do not represent real user information.'); ?></ul>
<ul>3.2 - <?php echo _('we do not spam.'); ?></ul>
<ul>3.3 - <?php echo _('we do not sell or rent your data to anyone. (See <a target="__blank" href="privacy">Privacy Policy</a>)'); ?></ul>

<li>4 - <?php echo _('When purchasing premium membership, you agree that'); ?></li>

<ul>4.1 - <?php echo _('a valid credit card, which you have the right to use, is required for any paying account.'); ?></ul>
<ul>4.2 - <?php echo _('the payments you make are non-refundable and are billed in advance. There will be no refunds of any sort or future credits for partial months usage of the service.'); ?></ul>
<ul>4.3 - <?php echo _('all fees are exclusive of any kind of taxes, levies or duties imposed by taxing authorities.'); ?></ul>
<ul>4.4 - <?php echo _('you will not be billed again if you wish to downgrade to the Basic account.'); ?></ul>
<ul>4.5 - <?php echo _('price changes to the Premium Membership will affect only new upgrades from Basic to Premium plan. Current paying customers will keep the old price.'); ?> </ul>

<li>5 - <?php echo _('Account termination'); ?></li>

<ul>5.1 - <?php echo _('Currently, the only way to cancel your account is by requesting it manually to contact@hackerexperience.com.'); ?></ul>
<ul>5.2 - <?php echo _('If you are a recurring-paying premium member, you will not be billed after your account is terminated.'); ?></ul>
<ul>5.3 - <?php echo _('NeoArt Labs has the right to terminate your account. This will result in the deactivation or deletion of your account and you will be prevented from any access to the game.'); ?></ul>
<ul><strong>5.4 - <?php echo _('Due to limited personnel, deleting an account might take several days.'); ?></strong></ul>

<li>6 - <?php echo _('Unenforceable provisions'); ?></li>

<ul>6.1 <?php echo _('If any provision of this website disclaimer is, or is found to be, unenforceable under applicable law, that will not affect the enforceability of the other provisions of this website disclaimer.'); ?></ul>

<li>7 - <?php echo _('Applicable law and competent court'); ?></li>

<ul>7.1 - <?php echo _('Hacker Experience and NeoArt Labs are governed by Brazilian law. In case of disputes or arguments only the courts of Ribeirão Preto will be competent.'); ?></ul>
<ul>7.2 - <?php echo _('In case of disputes a printed version of these terms and conditions of use will be accepted in legal or administrative procedures.'); ?></ul>

<li>8 - <?php echo _('Changes to the TOS'); ?></li>

<ul>8.1 - <?php echo _('NeoArt Labs reserves the right to update and change the Terms of Service from time to time without notice.'); ?></ul>
<ul>8.2 - <?php echo _('Any changes or updates made to the application are subject to these Terms of Service.'); ?></ul>
<ul>8.3 - <?php echo _('Continuing to use the service after such changes or updates are made will constitute your consent to those changes.'); ?> </ul>
<ul>8.4 - <?php echo _('Efforts will be made to publish major TOS changes on the Forum Announcements Board, but this does not invalidates item 8.1.'); ?></ul>

<li><?php echo _('Please do not sue me'); ?></li>
<ul><?php echo _('kthxbye'); ?></ul>


<br/><br/>

<center>
<strong><?php echo _('We use, follow and recommend the ACM code of ethics'); ?></strong><br/>

<a href="http://www.acm.org/about/code-of-ethics">http://www.acm.org/about/code-of-ethics</a>

<br/>
</center>

<br/><br/>

<center>
<?php echo sprintf(_('This Term of Service was first published on %s and last revised on %s.'), '30/08/2014', '27/09/2014'); ?>

</center>
<br/>
</body>
</html>
