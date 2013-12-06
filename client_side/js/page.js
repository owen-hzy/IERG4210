(function(){
	function navbar() {
		myLib.get2({action:'cat_fetchall'}, function(json){
			for (var listItems = [], i = 0, cat; cat = json[i]; i++) {
				(listItems.push('<li id="cat' , parseInt(cat.catid) , '">'), listItems.push('<a href="product.php?catid=' + cat.catid + '"> ' + cat.name + ' </a>'), listItems.push('</li>'));
			}
			el('navbar').innerHTML = listItems.join('');
		});		
	}
	navbar();
	
}) ();