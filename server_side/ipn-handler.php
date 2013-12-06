<?php // RAY_paypal_ipn.php - CUSTOM PayPal IPN PROCESSING

include_once('lib/db.inc.php');

// READ THE POST FROM PayPal AND ADD 'cmd'
$req      = 'cmd=_notify-validate';
$postdata = '';
foreach ($_POST as $key => $value)
{
    $postdata .= PHP_EOL . " $key = $value ";      // SAVE THE COLLECTION
    $$key     = trim(stripslashes($value));        // ASSIGN LOCAL VARIABLES
    $value    = urlencode(stripslashes($value));   // ENCODE FOR BOUNCE-BACK
    $req      .= "&$key=$value";                   // APPEND TO VERIFICATION STRING
}

// SET THE HEADERS FOR THE CONFIRMATION POST BACK TO PayPal
$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: www.sandbox.paypal.com\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

// OPEN THE HTTP PIPE FOR THE POST BACK TO PayPal
$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

// TEST FOR SUCCESSFUL OPENNING OF THE HTTP PIPE
if (!$fp) // HTTP ERROR
{
	return $false;
}

// WITH HTTP OPEN - WRITE HEADER AND REQUEST
fputs ($fp, $header . $req);

// WITH HTTP OPEN - READ PayPal RESPONSE
$paypal_reply   = '';
$paypal_headers = '';
while (!feof($fp))
{
    $paypal_reply    = fgets($fp, 1024);
    $paypal_headers .= $paypal_reply;
}
fclose ($fp);

// IF THIS IS TRULY A POST FROM PAYPAL, PROCESS ORDER NOTIFICATION
if ($paypal_reply == "VERIFIED")
{	
	$i = 1;
	$list_array = array();
	$results_array = array();
	$item_number = array();
	$quantity = array();
	$price = array();
	$payment_status = $_POST['payment_status'];
	$payment_amount = $_POST['payment_gross'];
	$payment_currency = $_POST['mc_currency'];
	$txn_id = $_POST['txn_id'];
	$receiver_email = $_POST['receiver_email'];
	$custom = $_POST['custom'];
	$txn_type = $_POST['txn_type'];
	$invoice = $_POST['invoice'];
	while(!empty($_POST['item_number' . $i . '']))
	{	
		$item_number[$i] = $_POST['item_number' . $i . ''];
		$quantity[$i] = $_POST['quantity' . $i . ''];
		$price[$i] = $_POST['mc_gross_' . $i . ''];
		
		$i += 1;
	}	
	$total = $_POST['mc_gross'];
	$status = 'PAID';
	
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT txn_id FROM txn WHERE txn_id=(:txn_id)');
	$q->execute(array(':txn_id'=>$txn_id));
	$r = $q->fetch();
	if (! empty($r['txn_id'])) {
		error_log(date(' [Y-m-d H:i e] ') . $txn_id . " transaction Id already exists, duplicate process" . PHP_EOL, 3, "/var/www/html/php/ipn_error_log");
		$db = null;
		die();
	}
	if ($txn_type != 'cart') {
		error_log(date(' [Y-m-d H:i e] ') . $txn_id . " transaction type is not cart" . PHP_EOL, 3, "/var/www/html/php/ipn_error_log");
		$db = null;
		die();
	}
	if ($payment_status != 'Completed') {
		error_log(date(' [Y-m-d H:i e] ') . $txn_id . " Payment status is not completed" . PHP_EOL, 3, "/var/www/html/php/ipn_error_log" );
		$db = null;
		die();
	}
	
	$q = $db->prepare('SELECT salt FROM orders WHERE invoice=(:invoice)');
	$q->execute(array(':invoice'=>$invoice));
	$t = $q->fetch();
		
	for ($j = 1; $j < $i; $j++) {
		$list_array[$item_number[$j]] = $quantity[$j];
		$result_array[$item_number[$j]] = $price[$j];
	}
	$data = $payment_currency . $receiver_email . $t['salt'] . $list_array . $result_array . $total;
	$digest = hash_hmac('sha1', $data, $t['salt']);
	
	if ($digest == $custom) {
		$q = $db->prepare('INSERT INTO txn (txn_id, invoice, pid, item_price, quantity, total, status) VALUES (:txn_id, :invoice, :pid, :item_price, :quantity, :total, :status)');
		$q->execute(array(':txn_id'=>$txn_id, ':invoice'=>$invoice, ':pid'=>$item_number[1], ':item_price'=>$price[1], ':quantity'=>$quantity[1], ':total'=>$total, ':status'=>$status));
		for ($j = 2; $j < $i; $j++) {
			$q = $db->prepare('INSERT INTO txn (pid, item_price, quantity) VALUES (:pid, :item_price, :quantity)');
			$q->execute(array(':pid'=>$item_number[$j], ':item_price'=>$price[$j], ':quantity'=>$quantity[$j]));
		}
		error_log(date(' [Y-m-d H:i e] ') . $txn_id . " Successfully Validated and Paid" . PHP_EOL, 3, "/var/www/html/php/ipn_log" );
		$db = null;
		die();
	}
    error_log(date(' [Y-m-d H:i e] ') . $txn_id . " Validation Failed" . PHP_EOL, 3, '/var/www/html/php/ipn_error_log');
	$db = null;
	die();
}

// LOG INVALID POSTS FOR MANUAL INVESTIGATION AND INTERVENTION
if ($paypal_reply == "INVALID")
{
    error_log(date(' [Y-m-d H:i e] ') . $txn_id . " " . 'INVALID', 3, '/var/www/html/php/ipn_log');
    die();
}

// OTHERWISE, PayPal RETURNED BAD DATA (OR INTERNET HTTP ERRORS OR TIMEOUT)
error_log(date(' [Y-m-d H:i e] ') . $txn_id . " " . 'unknown', 3, '/var/www/html/php/ipn_log');
die();
?>