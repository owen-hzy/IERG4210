(function(){
	
	if (self == top)
		document.body.style.display = "block";
	else
		top.location = self.location;
	
	el('login_form').onsubmit = function() {
		return myLib.submit(this, function(json){
			if(json == true){
					top.location.href = 'admin.php';
				}
				else
					alert("Error: " + json);
			});
	}
	
	el('login_panel_signup').onclick = function() {
		top.location.href = 'register.php';
	}

})();