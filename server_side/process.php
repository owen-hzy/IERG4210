<?php
error_reporting(-1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

include_once('lib/db.inc.php');

function ierg4210_fetchProducts(){
	$list = $_POST['list'];
	$list_array = json_decode($list, true);  //the request JSON format:	{"5":4,"6":4,"8":1}
	$pid_array = array_keys($list_array);
	
	$results_array = array();
	
	//DB manipulation
	global $db;
	$db = ierg4210_DB();
	
	foreach ($pid_array as $pid){
		if (!is_numeric($pid))
			throw new Exception("invalid-pid");
	
		$q = $db->prepare("SELECT * FROM products WHERE pid=(:pid)");
		if($q->execute(array(':pid' => $pid))){
			if (empty($results_array))
				$results_array = $q->fetchAll();
			else
				array_push($results_array, $q->fetch());
		}
	}
	return $results_array;
}

function ierg4210_buildOrder() {
	$list_array = json_decode($_POST['list'], true);
	$pid_array = array_keys($list_array);
	$total = 0;
	$result_array = array();
	
	global $db;
	$db = ierg4210_DB();
	
	foreach ($pid_array as $pid){
		if(!is_numeric($pid) || !is_numeric($list_array[$pid]) || 0 >= $list_array[$pid] )
			throw new Exception("invalid pid or invalid quantity");
		
		$q = $db->prepare("SELECT price FROM products WHERE pid=(:pid)");
		if($q->execute(array(':pid' => $pid))){
			$r = $q->fetch();
			$r['price'] = (float)$r['price'];
			$r['price'] = sprintf("%.2f", $r['price']);
			$result_array[$pid] = $r['price'];
		}
		
		$total += ($list_array[$pid] * $result_array[$pid]); 
	}
	
	$total = (float)$total;
	$total = sprintf("%.2f", $total);
	$salt = generate_salt();
	$email = "hz011-seller@ie.cuhk.edu.hk";
	$currency = "HKD";
	$data = $currency . $email . $salt . $list_array . $result_array . $total;
	$digest = hash_hmac('sha1', $data, $salt);
			
	$db = ierg4210_DBU();
	$q = $db->prepare("INSERT INTO orders (digest, salt, quantity, price, total, status) VALUES (:digest, :salt, :quantity, :price, :total, :status)");
	$q->execute(array(':digest' => $digest, ':salt' => $salt, ':quantity'=>json_encode($list_array), ':price'=>json_encode($result_array), ':total'=>$total, ':status'=>'Unpaid'));
	
	$lastId = $db->lastInsertId();
	$back_array = array(0=>array("digest"=>$digest, "invoice"=>$lastId));
	
	return $back_array;
}

function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories");
	if ($q->execute())
		return $q->fetchAll();
	
}

function ierg4210_cat_fetchbyid() {
	
	if (!is_numeric($_GET['catid']))
		throw new Exception('invalid-catid');
	
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare('SELECT * FROM categories WHERE catid=(:catid)');
	$q->execute(array(':catid'=>$_GET['catid']));
	return $q->fetchAll();
	
}

function ierg4210_prod_fetchbyid() {
	
	if (!is_numeric($_GET['pid']))
		throw new Exception('invalid-pid');
	
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare('SELECT * FROM products WHERE pid=(:pid)');
	$q->execute(array(':pid'=>$_GET['pid']));
	return $q->fetchAll();
}

function ierg4210_prod_fetchAllBy_catid() {
	
	if (!is_numeric($_GET["catid"]))
		throw new Exception("invalid-catid");
	$catid = $_GET["catid"];
	
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare('SELECT * FROM products WHERE catid=(:catid)');
	$q->execute(array(':catid'=>$catid));
	return $q->fetchAll();
}

header('Content-Type: application/json');

// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}

// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		//echo json_encode(array('failed'=>'1'));
		echo json_encode(array('failed'=>$db->errorCode()));
		$db = null;
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
	$db = null;
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>
