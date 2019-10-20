<?php

ini_set('display_errors', 'on');

require 'classes/Session.class.php';

if(isset($_SESSION['id'])){
	header("Location:index");
	exit();
}

$msg = '';

if(isset($_POST['email'])){

	require 'classes/System.class.php';

	$pdo = PDO_DB::factory();
	$system = new System();

	$email = $_POST['email'];

	if(!$system->validate($email, 'email')){
		$msg = 'Invalid email';
	}

	$sql = 'SELECT id, login, COUNT(*) AS total FROM users WHERE email = :email LIMIT 1';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':email' => $email));
	$userInfo = $stmt->fetch(PDO::FETCH_OBJ);

	if($userInfo->total == 0){
		$msg = 'This email is not registered';
	}

	$code = uniqid();

    $sql = 'INSERT INTO email_reset (userID, code) VALUES (:uid, :code)';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':uid' => $userInfo->id, ':code' => $code));    

    require '/var/www/classes/SES.class.php';            
    $ses = new SES();
    $ses->send('request_reset', Array('to' => $email, 'user' => $userInfo->login, 'code' => $code));

	$_SESSION['MSG'] = 'Reset email sent.';
	$_SESSION['TYP'] = 'REG';
	$_SESSION['MSG_TYPE'] = 'success';

	header("Location:index");
	exit();

}  elseif(isset($_POST['code'])){

	$pdo = PDO_DB::factory();

	$code = $_POST['code'];

	if(strlen($code) != 13){
		exit("Bad code");
	}

	$sql = 'SELECT userID, COUNT(*) AS total FROM email_reset WHERE code = :code LIMIT 1';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':code' => $code));
	$resetInfo = $stmt->fetch(PDO::FETCH_OBJ);

	if($resetInfo->total == 0){
		exit("This code is invalid.");
	}

	if($_POST['pwd'] != $_POST['pwd2']){
		exit("Passwords are different");
	}

	$pwd = $_POST['pwd'];

	if(strlen(htmlentities($pwd)) <= 5){
		exit("Please use at least 6 characteres");
	}

	require 'classes/BCrypt.class.php';
	$bcrypt = new BCrypt();

	$newPwd = $bcrypt->hash(htmlentities($pwd));

	$sql = 'UPDATE users SET password = :pwd WHERE id = :uid';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':uid' => $resetInfo->userid, ':pwd' => $newPwd));

	$sql = 'DELETE FROM email_reset WHERE code = :code';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':code' => $code));

	exit("Password changed");

} elseif(isset($_GET['code'])){

	$pdo = PDO_DB::factory();

	$code = $_GET['code'];

	if(strlen($code) != 13){
		exit("Bad code");
	}

	$sql = 'SELECT userID, COUNT(*) AS total FROM email_reset WHERE code = :code LIMIT 1';
	$stmt = $pdo->prepare($sql);
	$stmt->execute(Array(':code' => $code));
	$resetInfo = $stmt->fetch(PDO::FETCH_OBJ);

	if($resetInfo->total == 0){
		exit("This code is invalid.");
	}

?>
	
	<form action="" method="POST">
		<input type="hidden" name="code" value="<?php echo $_GET['code']; ?>">
		Password: <input type="password" name="pwd"> (6 or more characters)<br/>
		Repeat plz: <input type="password" name="pwd2"><br/>
		<input type="submit" value="Change password">

	</form>

<?php	

	exit();

}

?>

<html>
<head>
</head>
<body>

<?php if($msg != ''){ echo $msg.'<br/><br/>'; } ?>

	<form action="reset" method="POST">
		<input type="text" name="email" placeholder="Please insert your email"><br/><br/>
		<input type="submit" value="Request password reset">
	</form>
</body>	
</html>