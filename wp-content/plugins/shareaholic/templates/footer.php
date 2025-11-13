<div style="margin-top:45px;"></div>
<div class='clear'>
	<p class="text-muted">
	<?php printf( __( '%sShareaholic for WordPress v' . ShareaholicUtilities::get_version() . '%s | %sTerms of Service%s | %sSupport Center%s | %sAPI%s', 'shareaholic' ), '<a href="https://www.shareaholic.com/?src=wp_admin" target="_new">', '</a>', '<a href="https://www.shareaholic.com/terms/?src=wp_admin" target="_new">', '</a>', '<a href="https://support.shareaholic.com/" target="_new">', '</a>', '<a href="https://www.shareaholic.com/developers/?src=wp_admin" target="_new">', '</a>' ); ?>
	</p>
	<p class="text-muted">
	<?php printf( __( 'If you like our work, please show some love and %1$sleave a ⭐️⭐️⭐️⭐️⭐️ review%2$s. It would help us out a lot and we would really appreciate it.', 'shareaholic' ), '<a href="https://wordpress.org/support/plugin/shareaholic/reviews/?rate=5#new-post" target="_new">', '</a>' ); ?>
	</p>
</div>

<!-- Start of Async HubSpot Analytics -->
<script>
var _hsq = _hsq || [];
_hsq.push(["setContentType", "standard-page"]);
	(function(d,s,i,r) {
	if (d.getElementById(i)){return;}
	var n = d.createElement(s),e = document.getElementsByTagName(s)[0];
	n.id=i;n.src = '//js.hubspot.com/analytics/'+(Math.ceil(new Date()/r)*r)+'/210895.js';
	e.parentNode.insertBefore(n, e);
	})(document, "script", "hs-analytics",300000);
</script>
<!-- End of Async HubSpot Analytics Code -->

<script src="<?php echo ShareaholicUtilities::asset_url_admin( 'assets/platforms/wordpress/wordpress-admin.js' ); ?>"></script>
<script src="https://dsms0mj1bbhn4.cloudfront.net/assets/pub/loader-reachable.js" async></script>
