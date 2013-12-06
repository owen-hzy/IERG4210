<?php
session_start();
include_once('lib/db.inc.php');
			
	$nonce = generate_salt();
	global $db;
	$db = ierg4210_DBU();
				
	if (empty($_SESSION['row'])) {	
		$q = $db->prepare("INSERT INTO form_nonces (nonce) VALUES (:nonce)");
		if($q->execute(array(':nonce'=>$nonce))) {
			$_SESSION['row'] = $db->lastInsertId();
			echo htmlspecialchars($nonce, ENT_QUOTES | 'ENT_HTML5');
		}
	}
	else {
		$q = $db->prepare("UPDATE form_nonces SET nonce=(:nonce) WHERE row=(:row)");
		if($q->execute(array(':nonce'=>$nonce, ':row'=>$_SESSION['row'])))
			echo htmlspecialchars($nonce, ENT_QUOTES | 'ENT_HTML5');
		}
	$db = null;
?>