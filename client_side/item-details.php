<?php readfile('header.html'); ?>
	
<div class="content_container">
	<section>
	<ul id="sitemap">
		<input type="hidden" id="item_catid" />
		<input type="hidden" id="item_pid" value="<?php 
			echo htmlspecialchars($_GET['pid'], ENT_QUOTES | 'ENT_HTML5');
		?>" />
	</ul>
	<div id="item_detail">	
	</div>
	<div class="fb-share-button" data-href="http://developers.facebook.com/docs/plugins/" data-type="button"></div>
	</section>
</div>
<?php readfile('footer.html'); ?>

			