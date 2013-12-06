<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Food On-line</title>
	<link href="../css/login.css" rel="stylesheet" type="text/css">
</head>
<body>

<header class="logo">
	<a href="index.php">Food On-line</a>
</header>

<section>
		<fieldset>
			<legend>User Login</legend>
			<form id="login_form" method="POST" action="../php/auth-process.php?action=login" onsubmit="return false;">
			<input type="hidden" name="nonce" value="<?php 
				include_once('../php/nonce.php');
			?>" />			
			<label for="email">Email</label>
			<div class="controls">
				<input type="email" name="email" id="email" required="true" pattern="^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$" />
			</div>
			<label for="password">Password</label>
			<div class="controls">
				<input type="password" name="password" id="password" pattern="^[A-Za-z]\w{2,19}$" required="true" />
			</div>
			<div class="form-actions">
				<input type="submit" value="Log In" />
				<input type="button" id="login_panel_signup" value="Sign Up" />
			</div>	
			</form>
		</fieldset>
</section>

<script type="text/javascript" src="../js/myLib.js"></script>
<script type="text/javascript" src="../js/login.js"></script>
</body>
</html>