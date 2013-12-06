<?php readfile('header.html'); ?>
	
<article>
	<section>
	<!--Current Location Indicator-->
	<ul id="sitemap">
	</ul>
	<div class="products_list"><!--procuct-list area-->
		<ul id = "prod_lists">
			<input type="hidden" id="prod_catid" value="<?php
				echo htmlspecialchars($_GET['catid'], ENT_QUOTES | 'ENT_HTML%5');
			?>" />
		</ul>
	</div>
	
	<div class="ad"><!--Advertisement Area-->
		<img src="images/ad.jpeg" alt="advertisement" />
	</div>
	</section>
	<section><div class="fb-share-button" data-href="http://developers.facebook.com/docs/plugins/" data-type="button"></div></section>
</article>
<?php readfile('footer.html'); ?>	

