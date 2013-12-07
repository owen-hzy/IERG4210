(function(){
	function prod_lists() {
		
		if (el('prod_catid')) {
			var prod_catid = el('prod_catid').value;
			myLib.get2({action:'prod_fetchAllBy_catid', catid:prod_catid}, function(json){
				for (var a = [], i = 0, prod; prod = json[i]; i++){
					(a.push('<li>'), a.push('<a class="product_img" href="item-details.php?pid=' + prod.pid + '">'), a.push('<img src="' + prod.thumbdir +'" alt="' + prod.name +'" />'), a.push('</a>'),
					a.push('<div class="product_info">'), a.push('<span><a href="item-details.php?pid=' + prod.pid + '">' + prod.name +'</a></span>'), a.push('<div class="price_info">'), a.push('<span>' + prod.price + '</span>'),
					a.push('</div>'), a.push('</div>'), a.push('<div class="add_to_buttons">'), a.push('<button class="add_cart" onclick="ui.cart.add(' + prod.pid + ')">Add to Cart'), a.push('</button>'),
					a.push('</div>'), a.push('</li>'));
				}
				el('prod_lists').innerHTML = a.join('');
			});
		}
		else
			return false;
	}	
	prod_lists();
	
	function sitemap() {
		
		if (el('prod_catid')) {
			var catid = el('prod_catid').value;
			myLib.get2({action:'cat_fetchbyid', catid:catid}, function(json){
				var cat = json[0], a = [];
				(a.push('<li>'), a.push('<a href = "/">Home</a>'), a.push('</li>'),
				a.push('<li class="active">'), a.push('<a href="/' + cat.catid + '-' + cat.name + '"> >' + cat.name), a.push('</a></li>'));
					
				el('sitemap').innerHTML = a.join('');
			});
		}
		else 
			return false;
	}
	sitemap();
	
}) ();