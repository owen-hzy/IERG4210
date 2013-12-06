<?php
error_reporting(-1);
// Same as error_reporting (E_ALL);
ini_set('error_reporting',E_ALL);

session_start();
include_once('lib/db.inc.php');

function ierg4210_sign_up() {
	
	if (empty($_POST['nonce'])){
		throw new Exception("You may now visit a fake site");
	}
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT nonce FROM form_nonces WHERE row=(:row)');
	if(($q->execute(array(':row'=>$_SESSION['row']))) && ($r = $q->fetch()) && ($r['nonce'] == $_POST['nonce'])) {
	
	$sanitized_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL))
		throw new Exception("invalid-email");
	
	if ($_POST['password'] !== $_POST['verify'])
		throw new Exception("password do not match");
	
	if (!preg_match('/^[A-Za-z]\w{2,19}$/', $_POST['password'])|| !preg_match('/^[A-Za-z]\w{2,19}$/', $_POST['verify']))
		throw new Exception("invalid-password");	
	
	$password = $_POST['password'];	
	$salt = generate_salt();
	$storePW = hash_hmac('sha1', $password, $salt);
	$q = $db->prepare("INSERT INTO users (email, salt, password) VALUES (:email, :salt, :password)");
	if($q->execute(array(':email'=>$sanitized_email, ':salt'=>$salt, ':password'=>$storePW))) {
		
		session_regenerate_id();
		return true;
	}
	else
		throw new PDOException("error-user-sign-up");
	}
	else
		throw new Exception("You may now visit a fake site");	
}		

	
header('Content-Type: application/json');

// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}
	
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode())
			error_log(print_r($db->errorInfo(), true));		
		echo json_encode(array('failed'=>$db->errorCode()));
		$db = null;
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
	$db = null;
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed'=> $e->getMessage()));
}
?>

	
	
	
	