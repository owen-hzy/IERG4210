(function(){

	el('sign_up_form').onsubmit = function() {
			var password = el('password').value,
				verify = el('verify').value;
			
			if (password == verify) { 
			return myLib.submit(this, function(json){
				if(json == true){
					alert('Signed up successfully!');
					top.location.href = 'login.php';
				}
				else
					alert("Error: " + json);
			});
			}
			else {
				alert('Your password do not match!');
				return false;
			}
	}
	
	el('login_panel_cancel').onclick = function() {
		top.location.href = 'login.php';
	}
	
})();