(function(){
	function site_map() {
		
		if(el('item_pid')) {
			var item_pid = el('item_pid').value;			
			myLib.get2({action:'prod_fetchbyid', pid:item_pid}, function(json){
				var item = json[0], b = [];
				myLib.get2({action:'cat_fetchbyid', catid:item.catid}, function(json){
					var cat = json[0], a = [];
					(a.push('<li>'), a.push('<a href = "index.php">Home</a>'), a.push('</li>'),
					a.push('<li>'), a.push('<a href = "product.php?catid=' + cat.catid + '">>' + cat.name + '</a>'), a.push('</li>'),
					a.push('<li class="active">'), a.push('<a href="item-details.php?pid=' + item.pid + '">>' + item.name + '</a>'), a.push('</li>'));
					el('sitemap').innerHTML = a.join('');
				});
				(b.push('<div class="item_photo">'), 
				b.push('<img src="' + item.imagedir + '" alt="' + item.name + '" />'),
				b.push('</div>'),
				b.push('<div class="item_descrip">'),
				b.push('<h2>' + item.name + '</h2>'),
				b.push('<p>' + item.description + '</p>'),
				b.push('<div class="item_price">' + item.price + '</div>'),
				b.push('<div class="add_to_buttons">'),
				b.push('<button class="add_cart" onclick="ui.cart.add(' + item.pid + ')">Add to Cart</button>'),
				b.push('</div>'),
				b.push('</div>'));
				el('item_detail').innerHTML = b.join('');
			});
		}
		else 
			return false;
	}
	site_map();

}) ();