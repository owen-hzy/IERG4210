<?php
include_once('php/lib/db.inc.php');
global $db;
$db = ierg4210_DB();
$q = $db->prepare('SELECT pid, name, imagedir FROM products ORDER BY RAND() LIMIT 5');
$q->execute();
for ($i = 0; $i < 5; $i++) {
	$r = $q->fetch();
	echo ('<li><a href="item-details.php?pid=' . $r['pid'] . '"><img src="' . $r['imagedir'] . '" alt="' . $r['name'] . '" /></a></li>');
}
$db = null;
?>