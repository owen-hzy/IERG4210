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
			<form id="sign_up_form" method="POST" action="../php/sign-up.php?action=sign_up" onsubmit="return false;">
			<input type="hidden" name="nonce" value="<?php
				include_once('../php/nonce.php');
			?>" />
			<label for="email">Email</label>
			<div class="controls">
				<input type="email" name="email" required="true" pattern="^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$" />
			</div>
			<label for="password">Password</label>
			<div class="controls">
				<input type="password" id="password" name="password" pattern="^[A-Za-z]\w{2,19}$" required="ture"/>
				<span class="reminder">at least 3-20 characters which start with a letter and contain letter, number, underscore</span>
			</div>
			<label for="verify">Re-password</label>			
			<div class="controls">	
				<input type="password" id="verify" name="verify" pattern="^[A-Za-z]\w{2,19}$" required="true" />
			</div>
			<div class="form-actions">
				<input type="submit" value="Register" />
				<input type="button" id="login_panel_cancel" value="Cancel" />
			</div>	
			</form>
		</fieldset>
</section>

<script type="text/javascript" src="../js/myLib.js"></script>
<script type="text/javascript" src="../js/signup.js"></script>
</body>
</html>