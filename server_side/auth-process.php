<?php
error_reporting(-1);
// Same as error_reporting (E_ALL);
ini_set('error_reporting',E_ALL);

session_start();
include_once('lib/db.inc.php');

function ierg4210_login() {
	
	if (empty($_POST['nonce'])){
		throw new Exception("You may now visit a fake site");
	}
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT nonce FROM form_nonces WHERE row=(:row)');
	if(($q->execute(array(':row'=>$_SESSION['row']))) && ($r = $q->fetch()) && ($r['nonce'] == $_POST['nonce'])){	    
		
		$sanitized_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		if(!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL))
			throw new Exception("invalid-email");

		if(!preg_match('/^[A-Za-z]\w{2,19}$/', $_POST['password']))
			throw new Exception("invalid-password");
		
		$q = $db->prepare('SELECT * FROM users WHERE email = (:email)');
		if (($q->execute(array(':email'=>$sanitized_email))) && ($r = $q->fetch()) && ($r['password'] == hash_hmac('sha1', $_POST['password'], $r['salt']))){
				
			$q = $db->prepare('DELETE FROM form_nonces WHERE nonce = (:nonce)');
			if ($q->execute(array(':nonce'=>$_POST['nonce']))) {
				unset($_SESSION['row']);
				$exp = time() + 3600 * 24 *3;
				$token = array('em'=>$r['email'], 'exp'=>$exp, 'k'=>hash_hmac('sha1', $exp . $r['password'], $r['salt']));
	
				setcookie('auth', json_encode($token), $exp, '/', null, null, true);
				$_SESSION['auth'] = $token;
				session_regenerate_id();
				return true;
			}
		}
		else
			throw new Exception('email or password wrong');
		
	}
	else
		throw new Exception('You may now visit a fake site');
}	

function ierg4210_logout() {
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('DELETE FROM form_nonces WHERE row=(:row)');
	$q->execute(array(':row'=>$_SESSION['row']));
	session_unset();
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	setcookie('auth', '', time() -42000, '/', null, null, true);
	
	session_destroy();
	return true;
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