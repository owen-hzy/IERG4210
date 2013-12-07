(function(){

	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			if (json == 'redirect') {
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
			// loop over the server response json
			//   the expected format (as shown in Firebug):
			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<li id="cat' + parseInt(cat.catid) + '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
			el('categoryList').innerHTML = listItems.join('');
			}
		});
		el('productList').innerHTML = '';
	}
	updateUI();
	
	function updateprodUI(catid){
		var id=arguments[0];
		myLib.get({action:'prod_fetchAllBy_catid',catid:id}, function(json){
			if (json == 'redirect') {
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
			for (var listItems = [], i = 0, prod; prod = json[i]; i++){
				listItems.push('<li id="prod', parseInt(prod.pid), '"><span class="name">' , prod.name.escapeHTML() , '</span> <span id="cat' , parseInt(prod.catid) , '" class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('productList').innerHTML = listItems.join('');
			}
		});
	}
		
	function updateOrderInfo() {
		myLib.get({action:'get_order_info'}, function(json) {
			if (json == 'redirect') {
				alert('You need to login again');
				top.location.href= 'login.php';
			}
			else {
				for (var Items = [], i = 0, order; order = json[i]; i++) {
					(Items.push('<li id="' + order.invoice + '"><span class="small first">' + order.invoice + '</span>'),
					Items.push('<span class="medium">' + order.txn_id + '</span>'),
					Items.push('<span class="small">' + order.total + '</span>'),
					Items.push('<span class="small">' + order.status + '</span></li>'));
				}
				el('order_info').innerHTML += Items.join('');
			}
		});
	}
	updateOrderInfo();	
	
	el('order_info').onmouseover= function (e) {
		if (e.target.tagName != 'SPAN' || e.target.className != 'small first') {
			return false;
		}
		else {
			
		var target = e.target,
		parent = target.parentNode,
		id = target.parentNode.id;
		
		myLib.post({action: 'get_order_detail', invoice: id}, function (json) {
			if(json == 'redirect') {
					alert('You need to login again');
					top.location.href = 'login.php';
			}
			else {
				for (var a = [], e = 0, f; f = json[e]; e++) {
					(a.push('<li><img src="' + f.thumbdir + '" alt="' + f.name.escapeHTML() + '" />'),
					a.push('<span class="item_name">' + f.name.escapeHTML() + '</span>'),
					a.push('<input type="number" disabled="true" min="0" max="99" maxlength="2" class="qty" value="' + parseInt(f.quantity) + '" />'),
					a.push('<span class="item_price">$' + parseFloat(f.price) + '</span></li>'));
				}
				el('order_detail').innerHTML = a.join('');
			}
		});
		el('OrderInfoDetail').show();
		}
	}

	el('order_info').onmouseout = function () {
		el('OrderInfoDetail').hide();
	}
	
	el('categoryList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^cat/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Delete category: '+name+' \nConfirm?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
				if(json == 'redirect') {
					alert('You need to login again');
					top.location.href = 'login.php';
				}
				else if(json==true){
					alert('"' + name + '" is deleted successfully!');
					updateUI();
				}
				else {
					alert("Error: " + json);
				}
			    });
		
		// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('categoryEditPanel').show();
			el('categoryPanel').hide();
			
			// fill in the editing form with existing values
			el('cat_edit_name').value = name;
			el('cat_edit_catid').value = id;
		
		//handle the click on the category name
		} else {
			// populate the product list or navigate to admin.php?catid=<id>
			updateprodUI(id);
			el('productPanel').show();
			el('productEditPanel').hide();
			el('prod_insert_catid').value = id;
			//el('productList').innerHTML = '<li> Product 1 of "' + name + '" [Edit] [Delete]</li><li> Product 2 of "' + name + '" [Edit] [Delete]</li>';
		}
	}
	
	el('productList').onclick = function(e){
		if(e.target.tagName!= 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			catid = target.id.replace(/^cat/,''),
			id = target.parentNode.id.replace(/^prod/,''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		//handle the delete click
		if('delete' === target.className){
			confirm('Delete product: '+name+' \nConfirm?')&& myLib.post({action: 'prod_delete', pid: id},function(json){
				if (json == 'redirect') {
					alert('You need to login again');
					top.location.href = 'login.php';
				}
				else if(json==true){
				alert('"' + name + '" is deleted successfully!');
				updateprodUI(catid);
				}
				else {
				alert("Error: "+json);
				}
			});
		}
		
		//handle the edit click
		else if('edit'===e.target.className) {
			myLib.get({action:'prod_fetch', pid: id}, function(json){
				if (json == 'redirect') {
					alert('You need to login again');
					top.location.href = 'login.php';
				}
				else {
				el('productPanel').hide();
				el('productEditPanel').show();	
				var prod = json[0];
				el("prod_edit_name").value = prod.name;
				el("prod_edit_price").value = prod.price;
				el("prod_edit_description").value = prod.description;
				el("prod_edit_pid").value=prod.pid;
				el("prod_edit_img").src="../" + prod.imagedir;
				el("prod_edit_img").alt=prod.name;
				}
			});
			
		}
	}	

	el('cat_insert').onsubmit = function() {
		return myLib.submit(this, function(json) {
			if (json == 'redirect'){
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
				updateUI();
			}
		});
	}
	
	el('cat_edit').onsubmit = function() {
		return myLib.submit(this, function(json) {
			if (json == 'redirect'){
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
			// toggle the edit/view display
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
			updateUI();
			}
		});
	}
		
	el('prod_insert').onsubmit = function(){
		myLib.newAJAXSubmit(this, function(response){
			if (response == 'redirect'){
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
				updateprodUI(response);
			}
		});
		return false;
	}
	
	el('prod_edit').onsubmit = function(){
		myLib.newAJAXSubmit(this, function(response){
			if (response == 'redirect'){
				alert('You need to login again');
				top.location.href = 'login.php';
			}
			else {
				alert('Edit success!');
				myLib.get({action:'prod_fetch', pid: response}, function(prod_response){
				if (prod_response == 'redirect') {
					alert('You need to login again');
					top.location.href = 'login.php';
				}else {
					el('productPanel').hide();
					el('productEditPanel').show();	
					var prod = prod_response[0];
					el("prod_edit_name").value = prod.name;
					el("prod_edit_price").value = prod.price;
					el("prod_edit_description").value = prod.description;
					el("prod_edit_pid").value=prod.pid;
					el("prod_edit_img").src="../" + prod.imagedir;
					el("prod_edit_img").alt=prod.name;
				}
			});
			}
		});
		return false;
	}
	
	el('cat_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('categoryEditPanel').hide();
		el('categoryPanel').show();
	}

	el('prod_edit_cancel').onclick = function() {
		el('productEditPanel').hide();
		el('productPanel').show();
	}
	
	el('log_out').onclick = function() {
		myLib.ajax({
		url: '../php/auth-process.php?action=logout',
		success: function(json) {
			json = JSON.parse(json);
			if (json.success) {
				top.location.href = 'login.php';
			}
			else
				alert('Error: ' + json.failed);
		}	
		});
	}
	
})();