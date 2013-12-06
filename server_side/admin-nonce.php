<?php
include_once('lib/db.inc.php');		
	
	global $db;
	$db = ierg4210_DBU();
	
	$q = $db->prepare("SELECT nonce FROM form_nonces WHERE row=(:row)");
	if ($q->execute(array(':row'=>$_SESSION['row'])) && $r = $q->fetch()) {
		echo htmlspecialchars($r['nonce'], ENT_QUOTES | 'ENT_HTML5');
	}
	
	$db = null;
?>