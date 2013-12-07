<?php readfile('header.html'); ?>

<article>
			<ul id="sitemap"><!--Current Location Indicator-->
				<li class="active"><a href="index.php">Home</a></li>
			</ul>
			<!--Slide Show-->
			<section class="slider" id="slider">
				<ul id="slides">
					<?php include_once('slides.php'); ?>
				</ul>
			</section>
			<!--Content Slide-->
			<section class="list">
				<div class="best_sell_products">
					<h2>Best Sell</h2>
					<ul id="best_sell">
					</ul>
				</div>
				<div class="business_time">
					<h2>Business Time</h2>
					<p>Monday-Friday:</p>
					<p>9:00am-10:00pm</p>
				</div>
				<div class="special">
					<h2>Special Favour</h2>
					<ul id="special_favour">
					</ul>
				</div>
			</section>
			<section>
			<div class="fb-like" data-href="https://developers.facebook.com/docs/plugins/" data-layout="standard" data-action="like" data-show-faces="true" data-share="true"></div>
			</section>
</article>

<?php readfile('footer.html'); ?>
	
