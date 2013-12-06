<?php
function ierg4210_DB() {
	// connect to the database
	// TODO: change the following path if needed
	// Warning: NEVER put your db in a publicly accessible location
	//$db = new PDO('sqlite:/var/www/db/shop.db');
	$db = new PDO("mysql:host=localhost;dbname=ierg4210;","4210","ierg4210");
	
	// enable foreign key support
	$db->query('PRAGMA foreign_keys = ON;');
	
	// FETCH_ASSOC: 
	// Specifies that the fetch method shall return each row as an
	// array indexed by column name as returned in the corresponding
	// result set. If the result set contains multiple columns with
	// the same name, PDO::FETCH_ASSOC returns only a single value
	// per column name. 
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	//makes sure the statement and the values aren't parsed by PHP before sending it to the MySQL server (giving a possible attacker no chance to inject malicious SQL)
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	return $db;
}

function ierg4210_DBU() {
	$db = new PDO("mysql:host=localhost;dbname=ierg4210_users;","4210","ierg4210");
	
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	return $db;
}

function generate_salt() {
	$str = '';
	$length = 12;
	$l = 0;
	while ($l < $length)
	{
		$l = strlen($str);
		$str .= hash('sha1', uniqid('',true));
	}
	$str = base64_encode($str);
	$str =strlen($str) > $length ? substr($str, 0, $length) : $str;
	return trim(strtr($str, '/+=', '   '));
}

$image_wholedir_prefix = '/var/www/html/incl/img/';
$image_webdir_prefix = 'incl/img/';
?>
