(function(){
	function navbar() {
		myLib.get2({action:'cat_fetchall'}, function(json){
			for (var listItems = [], i = 0, cat; cat = json[i]; i++) {
				(listItems.push('<li id="cat' , parseInt(cat.catid) , '">'), listItems.push('<a href="/' + cat.catid + '-' + cat.name + '"> ' + cat.name + ' </a>'), listItems.push('</li>'));
			}
			el('navbar').innerHTML = listItems.join('');
		});		
	}
	navbar();
	
	function best_favour() {
		myLib.get2({action:'get_pic'}, function (json) {
			for (var a = [], b = [], i = 0, pic; pic = json[i]; i++) {
				if (i < 3) {
					(a.push('<li><a href="item-details.php?pid=' + pic.pid + '">'),
					a.push('<img src="' + pic.thumbdir + '" alt="' + pic.name + '" /></a></li>'));
				}
				else {
					(b.push('<li><a href="item-details.php?pid=' + pic.pid + '">'),
					b.push('<img src="' + pic.thumbdir + '" alt="' + pic.name + '"></a></li>'));
				}
			}
			el('best_sell').innerHTML = a.join('');
			el('special_favour').innerHTML = b.join('');
		});
	}
	best_favour();
}) ();