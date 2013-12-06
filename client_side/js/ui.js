(function() {
	function j(d) {
		for (var a = [], b = 0, e = 0, g = 1, h, i, f; f = d[e]; e++, g++) c[f.pid] && (a.push("<li>"), a.push('<input type="hidden" name="item_number_' + g + '" value="' + f.pid + '" />'), a.push('<img src="' + f.imagedir + '" alt="' + f.name.escapeHTML() + '">'), a.push('<span class="item_name">' + f.name.escapeHTML() + '</span>'), a.push('<input type="hidden" name="item_name_' + g + '" value="' + f.name.escapeHTML() + '" />'), h = Math.abs(parseInt(c[f.pid])), a.push('<input type="number" name="quantity_' + g + '" min="0" max="99" maxlength="2" class="qty" value="' + h + '" onblur="ui.cart.update(' + f.pid + ',this.value)" />'), i = Math.abs(parseFloat(f.price)), a.push('<input type="hidden" name="amount_' + g + '" value="' + parseFloat(f.price) + '" />'), a.push('<span class="item_price">$' + i + '</span>'),  a.push('<span class="item_delete" onclick="ui.cart.remove(' + f.pid + ')">Delete</span>'), b += h * i, a.push("</li>"));
		document.getElementById("cartTotal").innerHTML = parseFloat(b).toFixed(2);
		document.getElementById("cart").innerHTML = 1 < a.length ? a.join("") : "No item!";
	}
	var e = window.myLib = window.myLib || {};
	e.post2 = function(b, a) {
		e.processJSON("php/process.php?rnd=" + (new Date).getTime(), b, a, {
			method: "POST"
		})
	};
	window.ui = window.ui || {
		cart: {
			storage: {}
		}
	};
	var b = ui.cart,
	c = b.storage;
	b.getSavedStore = function() {
		c = (c = window.localStorage.getItem("cart_storage")) ? JSON.parse(c) : {}
	};
	b.add = function(pid) {
		b.update(pid, (c[pid] || 0) + 1)
	};
	b.remove = function(pid) {
		delete c[pid];
		window.localStorage.setItem("cart_storage", JSON.stringify(c));
		b.display()
	}
	b.setVisibility = function(b) {
		var a = document.querySelector(".cartList").classList;
		b ? a.add("display") : a.remove("display")
	};
	b.toggleVisibility = function() {
		document.querySelector(".cartList").classList.toggle("display")
	};
	b.update = function(pid, qty) {
		var e = !1,
		qty = parseInt(qty);	
		if(isNaN(qty)){
			alert("Please input an integer!");
			document.querySelector(".qty").value = c[pid]
		}
		else{
		0 == qty ? delete c[pid] : 0 > qty || (e = (c[pid] ? !1 : !0), c[pid] = qty);
		window.localStorage.setItem("cart_storage", JSON.stringify(c));
		b.display(e)		
		}
	};
	b.display = function(d) {
		(d || !b.prodDetails) ? c && e.post2({
			action: "fetchProducts",
			list: JSON.stringify(c)
		},
		function(a) {
			j(b.prodDetails = a)
		}) : j(b.prodDetails)
	};
	b.reset = function() {
		c = {};
		window.localStorage.setItem("cart_storage", JSON.stringify(c));
		document.getElementById("cartTotal").innerHTML = "0";
		document.getElementById("cart").innerHTML = "No item!"
	};
	b.submit = function(b) {
		var a = parseFloat(document.getElementById("cartTotal").innerHTML);
		if (!c || 0 >= a) return ! 1;
		e.post2({
			action: "buildOrder",
			list: JSON.stringify(c)
		},
		function(a) {
			var p = a[0], s = [];
			if (!p.digest && !p.invoice) return alert("error occurred");
			c = {};
			window.localStorage.setItem("cart_storage", JSON.stringify(c));
			(s.push('<input type="hidden" name="custom" value="' + p.digest + '">'), 
			s.push('<input type="hidden" name="invoice" value="' +p.invoice + '">'));
			document.getElementById('append').innerHTML = s.join('');
			b.submit()
		});
		return ! 1;
	};
	b.getSavedStore();
	b.display()
})();