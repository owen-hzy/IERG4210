<?php
error_reporting(-1);
// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-error.log");

session_start();
include_once('lib/db.inc.php');

function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_cat_insert() {
	
	$check = nonce_check();
	if(!$check) {
		throw new Exception("You may now visit a fake site, Try log out and log in again");
	}
	else {
	// input validation or sanitization
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	$name = $_POST['name'];
	
	// DB manipulation
	$db = ierg4210_DB();
	if ($q = $db->prepare("INSERT INTO categories (name) VALUES (:name)")) {
		return $q->execute(array(':name'=>$name));
	}
	}
}

function ierg4210_cat_edit() {
	$check = nonce_check();
	if (!$check){
		throw new Exception("You may now visit a fake site, Try log out and log in again");
	}
	else {
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	$name = $_POST['name'];
	if (!is_numeric($_POST['catid']))
		throw new Exception("invalid-catid");
	$catid = $_POST['catid'];
	
	$db = ierg4210_DB();
	$q = $db->prepare("UPDATE categories SET name=(:name) WHERE catid=(:catid)");
	if ($q->execute(array(':name'=>$name, ':catid'=>$catid))) {
		return true;
	}
	}
}

function ierg4210_cat_delete() {
	// input validation or sanitization
	//$_POST['catid'] = (int) $_POST['catid'];
	if (!is_numeric($_POST['catid']))
		throw new Exception("invalid-catid");
	$catid = $_POST['catid'];
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	
	$q = $db->prepare("SELECT * FROM products WHERE catid = (:catid)");
	if ($q->execute(array(':catid'=>$catid)))
		if (count($q->fetchAll()) == 0){
			$q = $db->prepare("DELETE FROM categories WHERE catid = (:catid)");
			if ($q->execute(array(':catid'=>$catid))){
				return true;
			}else 
				return false;
		}
	else
		return 'Cannot delete category being linked by product(s)';
	
	//return $q->execute(array(':catid'=>$catid));
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.php
function ierg4210_prod_insert() {

	$check = nonce_check();
	if(!$check){
		header('Content-Type: text/html; charset=utf-8');
		echo 'You may now visit a fake site, Try log in again.<br /><a href="../login.php">Back to login page.</a>';
		exit();
	}
	else {
	// input validation or sanitization
	if (!is_numeric($_POST['catid']))
		throw new Exception("invalid-catid");
	$catid = $_POST["catid"];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-product-name");
	$name = $_POST["name"];
	if (!preg_match('/^\d+(\.\d{1,2})?$/', $_POST['price']))
		throw new Exception("invalid-price");
	$price = $_POST["price"];
	if (!preg_match('/^[\w\-, ]*$/', $_POST['description']))
		throw new Exception("invalid-description");
	$description = $_POST["description"];
	
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	// TODO: complete the rest of the INSERT command
	$q = $db->prepare("INSERT INTO products (catid, name, price, description) VALUES (:catid, :name, :price, :description)");//pid not needed, since auto_increment
	$q->execute(array(':catid'=>$catid, ':name'=>$name, ':price'=>$price, ':description'=>$description));
	
	
	// The lastInsertId() function returns the pid (primary key) resulted by the last INSERT command
	$lastId = $db->lastInsertId();

	// Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpeg
	$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES["file"]["tmp_name"]);
	if ($_FILES["file"]["error"] == 0
		&& (($_FILES["file"]["type"] == "image/jpeg" && $type == "image/jpeg") ||
			($_FILES["file"]["type"] == "image/gif" && $type == "image/gif")||
			($_FILES["file"]["type"] == "image/png" && $type == "image/png"))
		//&& $_FILES["file"]["size"] < 5000000) {
		&& $_FILES["file"]["size"] <= 1310720) {//1310720 bytes = 10MB
		// Note: Take care of the permission of destination folder (hints: current user is apache)
		$extension = str_replace('image/', '.', $type);
		$datetime = new DateTime();
		$name_stamp = $datetime->getTimestamp();
		$image_name = $lastId.'_'.$name_stamp.$extension;
		$image_dir = '/var/www/html/incl/img/';
		$thumb_name = $lastId.'_'.$name_stamp.'_thumb'.$extension;
		$thumb_dir = '/var/www/html/incl/img/thumb/';
		if (move_uploaded_file($_FILES["file"]["tmp_name"],$image_dir . $image_name)){
			//make thumbnail
			list($original_width, $original_height, $mime) = getimagesize($image_dir. $image_name);			 
			if ($original_width >= $original_height){
				$thumb_width = 300;
				$thumb_height = round($original_height * $thumb_width/$original_width);
			}else{
				$thumb_height = 300;
				$thumb_width = round($original_width * $thumb_height/$original_height);
			}
			$img_source = imagecreatefromstring(file_get_contents($image_dir. $image_name));
			$thumb_img = imagecreatetruecolor($thumb_width, $thumb_height);
			imagealphablending($thumb_img, false); //for preserving png transparent background
			imagesavealpha($thumb_img, true);  //for preserving png transparent background
			imagecopyresampled($thumb_img, $img_source, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height);			
			error_log($mime);
			switch ($mime){
				case IMAGETYPE_GIF:
					imagegif($thumb_img,$thumb_dir.$thumb_name);
					break;
				case IMAGETYPE_JPEG:
					imagejpeg($thumb_img,$thumb_dir.$thumb_name,100);
					break;
				case IMAGETYPE_PNG:
					imagepng($thumb_img,$thumb_dir.$thumb_name);
					break;				
			}
			imagedestroy($img_source);
			imagedestroy($thumb_img); 
			$image_dir = 'incl/img/'.$image_name;
			$thumb_dir = 'incl/img/thumb/'.$thumb_name;
			$q = $db->prepare("UPDATE products SET imagedir=(:imagedir),thumbdir=(:thumbdir) WHERE pid=(:lastId)");
			if(! $q->execute(array(':imagedir'=>$image_dir, ':thumbdir'=>$thumb_dir, ':lastId'=>$lastId))){
				throw new PDOException("error-product-insert");
			} 
			// redirect back to original page; you may comment it during debug			
			//header('Location: ../admin.php');
			return $catid;
			//exit();
		}
	}

	// Only an invalid file will result in the execution below
	
	// TODO: remove the SQL record that was just inserted
	$q = $db->prepare("DELETE FROM products WHERE pid=(:pid)");
	if(! $q->execute(array(':pid'=>$lastId))){
		throw new PDOException("error-remove-invalid-insert");
	}
	
	throw new Exception("Invalid file");
	// To replace the content-type header which was json and output an error message
	/* header('Content-Type: text/html; charset=utf-8');
	echo 'Invalid file detected. <br /><a href="../admin.php">Back to admin panel.</a>';
	exit(); */
	}
}

// TODO: add other functions here to make the whole application complete
function ierg4210_prod_fetchAllBy_catid() {
	
	if(!is_numeric($_GET['catid']))
		throw new Exception('Invalid catid');
	$catid = $_GET["catid"];
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products WHERE catid=(:cid)");
	if ($q->execute(array(':cid' => $catid)))
		return $q->fetchAll();
}

function ierg4210_prod_delete() {
	if (!is_numeric($_POST['pid']))
		throw new Exception("invalid-pid");
	$pid = $_POST['pid'];
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	
	$q = $db->prepare("DELETE FROM products WHERE pid=(:pid)");
	if($q->execute(array(':pid'=>$pid))){
		if (! unlink('/var/www/html/incl/img/'.$pid.'.jpeg'))
		if (! unlink('/var/www/html/incl/img/'.$pid.'.png'))
			unlink('/var/www/html/incl/img/'.$pid.'.gif');
		return true;
	}else
		return 'Delete Failed';
}

function ierg4210_prod_fetch(){
	if(!is_numeric($_REQUEST['pid']))
		throw new Exception("invalid-pid");
	$pid = $_REQUEST['pid'];
	//DB manipulation
	global $db;
	$db = ierg4210_DB();
	
	$q = $db->prepare("SELECT * FROM products WHERE pid=(:pid)");
	if($q->execute(array(':pid' => $pid)))
		return $q->fetchAll();
}

function ierg4210_prod_edit() {
	
	$check = nonce_check();
	if(!$check) {
		header('Content-Type: text/html; charset=utf-8');
		echo 'You may now visit a fake site, Try log in again.<br /><a href="../login.php">Back to login page.</a>';
		exit();
	}
	else {
	//input validation or sanitization
	if (!is_numeric($_POST['pid']))
		throw new Exception("invalid-pid");
	$pid = $_POST['pid'];
	if (!preg_match('/^[\w\-, ]+$/', $_POST['name']))
		throw new Exception("invalid-product-name");
	$name = $_POST["name"];
	if (!preg_match('/^\d+(\.\d{1,2})?$/', $_POST['price']))
		throw new Exception("invalid-price");
	$price = $_POST["price"];
	if (!preg_match('/^[\w\-, ]*$/', $_POST['description']))
		throw new Exception("invalid-description");
	$description = $_POST["description"];
	
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("UPDATE products SET name=(:name), price=(:price), description=(:description) WHERE pid=(:pid)"); 
	$q->execute(array(':name'=>$name, ':price'=>$price, ':description'=>$description, ':pid'=>$pid));
	
	if ($_FILES['file']['tmp_name']) {
		// Delete the original image
		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES["file"]["tmp_name"]);
		if ($_FILES["file"]["error"] == 0
			&& (($_FILES["file"]["type"] == "image/jpeg" && $type == "image/jpeg") ||			
				($_FILES["file"]["type"] == "image/png" && $type == "image/png")  ||
				($_FILES["file"]["type"] == "image/gif" && $type == "image/gif"))
			//&& $_FILES["file"]["size"] < 5000000) {
			&& $_FILES["file"]["size"] <= 1310720) {//1310720 bytes = 10MB
			// Note: Take care of the permission of destination folder (hints: current user is apache)		
			if (!(unlink('/var/www/html/incl/img/'.$pid.'.jpeg')))
			if (!(unlink('/var/www/html/incl/img/'.$pid.'.png')))
				unlink('/var/www/html/incl/img/'.$pid.'.gif');
			if (!(unlink('/var/www/html/incl/img/thumb/'.$pid.'_thumb.jpeg')))
			if (!(unlink('/var/www/html/incl/img/thumb/'.$pid.'_thumb.png')))
				unlink('/var/www/html/incl/img/thumb/'.$pid.'_thumb.gif');
			$extension = str_replace('image/', '.', $_FILES["file"]["type"]);
			$datetime = new DateTime();
			$name_stamp = $datetime->getTimestamp();
			$image_name = $pid.'_'.$name_stamp.$extension;
			$image_dir = '/var/www/html/incl/img/';
			$thumb_name = $pid.'_'.$name_stamp.'_thumb'.$extension;
			$thumb_dir = '/var/www/html/incl/img/thumb/';
			if (move_uploaded_file($_FILES["file"]["tmp_name"], $image_dir . $image_name)) {
			//if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/incl/img/" . $_FILES["file"]["name"])) {
				// redirect back to original page; you may comment it during debug
				//make thumbnail
				list($original_width, $original_height, $mime) = getimagesize($image_dir. $image_name);			 
				if ($original_width >= $original_height){
					$thumb_width = 300;
					$thumb_height = round($original_height * $thumb_width/$original_width);
				}else{
					$thumb_height = 300;
					$thumb_width = round($original_width * $thumb_height/$original_height);
				}
				$img_source = imagecreatefromstring(file_get_contents($image_dir. $image_name));
				$thumb_img = imagecreatetruecolor($thumb_width, $thumb_height);
				imagealphablending($thumb_img, false); //for preserving png transparent background
				imagesavealpha($thumb_img, true);  //for preserving png transparent background
				imagecopyresampled($thumb_img, $img_source, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height);			
				error_log($mime);
				switch ($mime){
					case IMAGETYPE_GIF:
						imagegif($thumb_img,$thumb_dir.$thumb_name);
						break;
					case IMAGETYPE_JPEG:
						imagejpeg($thumb_img,$thumb_dir.$thumb_name,100);
						break;
					case IMAGETYPE_PNG:
						imagepng($thumb_img,$thumb_dir.$thumb_name);
						break;				
				}
				imagedestroy($img_source);
				imagedestroy($thumb_img); 
				$image_dir = 'incl/img/'.$image_name;
				$thumb_dir = 'incl/img/thumb/'.$thumb_name;
				$q = $db->prepare("UPDATE products SET imagedir=(:imagedir),thumbdir=(:thumbdir) WHERE pid=(:pid)");
				if(! $q->execute(array(':imagedir'=>$image_dir, ':thumbdir'=>$thumb_dir, ':pid'=>$pid))){
					throw new PDOException("error-product-insert");
				} 
				return $pid;
				/* header('Location: ../admin.php');
				exit(); */
			}
			else{
				return 'Product Edit failed';
				/*header('Content-Type: text/html; charset=utf-8');
				 echo 'Product Edit failed. <br /><a href="javascript:history.back();">Back to admin panel.</a>';
				exit();*/
			}
		}
		else {
			return 'Invalid image type';
			/* header('Content-Type: text/html; charset=utf-8');
			echo 'Check your image type. <br /><a href="../admin.php">Back to admin panel.</a>';
			exit(); */
		}
	}
	else {
		return $pid;
		/* header('Location: ../admin.php');
		exit(); */
	}
	}
}

function ierg4210_get_order_info() {
	/*DB manipulation*/
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT invoice,txn_id,total,status FROM orders');
	$q->execute();
	return $q->fetchAll();
}

function ierg4210_get_order_detail() {
	
	$invoice = $_POST['invoice'];
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT quantity, price FROM orders WHERE invoice = (:invoice)');
	$q->execute(array(':invoice'=>$invoice));
	$r = $q->fetch();
	$quantity_array = json_decode($r['quantity'], true);
	$price_array = json_decode($r['price'], true);
	
	$db = ierg4210_DB();
	$tmp_array = array();
	$i = 0;
		foreach ($quantity_array as $key => $value) {
		if (!is_numeric($key)) {
			throw new exception('invalid-pid');
		}
		$q = $db->prepare('SELECT pid, name, thumbdir FROM products WHERE pid=(:pid)');
		$q->execute(array(':pid'=>$key));
		$r = $q->fetch();
		$tmp_array += array($i=>array("pid"=>$r['pid'], "name"=>$r['name'], "thumbdir"=>'../' . $r['thumbdir'], "quantity"=>$value, "price"=>$price_array[$key]));
		$i++;
}
	return $tmp_array;
}
	
function auth() {
	if (!empty($_SESSION['auth']))
		return $_SESSION['auth']['em'];
	
	if (!empty($_COOKIE['auth'])) {
		if ($t = json_decode($_COOKIE['auth'], true)) {
			if (time() > $t['exp']) return false;
			
			global $db;
			$db = ierg4210_DBU();
			$q = $db->prepare('SELECT salt, password FROM users WHERE email = (:email)');
			if (($q->execute(array(':email'=>$t['em']))) && ($r = $q->fetch()) && ($t['k'] == hash_hmac('sha1', $t['exp'] . $r['password'], $r['salt']))) {
				$_SESSION['auth'] = $_COOKIE['auth'];
				return $t['em'];
			}
			return false;
		}
	}
	return false;
}

function nonce_check() {
	if (empty($_POST['nonce']) || empty($_SESSION['row'])) {
		return false;
	}
	global $db;
	$db = ierg4210_DBU();
	$q = $db->prepare('SELECT nonce FROM form_nonces WHERE row=(:row)');
	if (($q->execute(array(':row'=>$_SESSION['row']))) && ($r = $q->fetch()) && ($r['nonce'] == $_POST['nonce'])) {
		return true;
	}
	else {
		return false;
	}
}

	
if(($validate = auth()) === false) {
		echo 'while(1);' . json_encode(array('success' => 'redirect'));
}
else {




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
}
?>
