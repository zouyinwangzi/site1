<?php ShareaholicAdmin::show_header(); ?>

<script>
window.ShareaholicConfig = {
	apiHost: "<?php echo Shareaholic::API_URL; ?>",
	serviceHost: "<?php echo Shareaholic::URL; ?>",
	assetHost: "<?php echo ShareaholicUtilities::asset_url_admin(); ?>",
	assetFolders: true,
	origin: "wp_plugin",
	language: "<?php echo strtolower( get_bloginfo( 'language' ) ); ?>"
};
</script>

<div class='wrap'>
<h2></h2>

<div class='reveal-modal' id='editing_modal'>
	<div id='iframe_container' class='bg-loading-img' allowtransparency='true'></div>
	<a class="close-reveal-modal">&#215;</a>
</div>

<div class="container-fluid">
	<div class="row">
	<div class="col-sm-8">
		<form name="settings" method="post" action="<?php echo $action; ?>">
		<?php wp_nonce_field( $action, 'nonce_field' ); ?>
		<input type="hidden" name="already_submitted" value="Y">

		<div id='app_settings'>
	
		<div class="app">
		<h2>In-Page App Code Blocks</h2>
		<div class="callout-container callout-blue"><?php printf( __( 'To customize settings for each In-Page App Code Block, visit the %1$sCloud Settings%2$s section.', 'shareaholic' ), '<a href="' . admin_url( 'admin.php?page=shareaholic-settings' ) . '">', '</a>' ); ?>
		</div>
		<p>
			<i class="icon icon-share_buttons"></i> <?php printf( __( 'In-Page Share Buttons', 'shareaholic' ) ); ?>
			<?php printf( __( '- Pick where you want your In-Page code blocks to be inserted', 'shareaholic' ) ); ?>
		</p>
		
		<?php foreach ( array( 'post', 'page', 'index', 'category' ) as $page_type ) { ?>
		<fieldset id='sharebuttons'>
			<legend><?php echo ucfirst( $page_type ); ?></legend>
			<?php foreach ( array( 'above', 'below' ) as $position ) { ?>
				<div class="location">
					<input type="hidden" name="share_buttons[<?php echo "{$page_type}_{$position}_content"; ?>]" value="off" />
					<input type="checkbox" id="share_buttons_<?php echo "{$page_type}_{$position}_content"; ?>" name="share_buttons[<?php echo "{$page_type}_{$position}_content"; ?>]" class="check"
					<?php if ( isset( $share_buttons[ "{$page_type}_{$position}_content" ] ) ) { ?>
						<?php echo ( $share_buttons[ "{$page_type}_{$position}_content" ] == 'on' ? 'checked' : '' ); ?>
					<?php } ?> />
					<label for="share_buttons_<?php echo "{$page_type}_{$position}_content"; ?>"><?php echo ucfirst( $position ); ?> Content</label>
				</div>
			<?php } ?>
		</fieldset>
		<?php } ?>
		
		<div class='fieldset-footer'>
			<p>
			<input type="checkbox" id="share_buttons_excerpts" name="shareaholic[share_buttons_display_on_excerpts]" class="check"	
			<?php if ( isset( $settings['share_buttons_display_on_excerpts'] ) ) { ?>
				<?php echo ( $settings['share_buttons_display_on_excerpts'] == 'on' ? 'checked' : '' ); ?>
			<?php } ?>>
			<label for="share_buttons_excerpts">Display on excerpts</label>
			</p>
		</div>
		
		<div class='clear'></div>
		
		<p>
			<i class="icon icon-recommendations"></i> <?php printf( __( 'Related Content', 'shareaholic' ) ); ?>
			<?php printf( __( '- Pick where you want your In-Page code blocks to be inserted', 'shareaholic' ) ); ?>
		</p>
		<?php foreach ( array( 'post', 'page', 'index', 'category' ) as $page_type ) { ?>
			<?php foreach ( array( 'below' ) as $position ) { ?>
			<fieldset id='recommendations'>
				<legend><?php echo ucfirst( $page_type ); ?></legend>
				<div class="location">
					<input type="hidden" name="recommendations[<?php echo "{$page_type}_below_content"; ?>]" value="off" />
					<input type="checkbox" id="recommendations_<?php echo "{$page_type}_below_content"; ?>" name="recommendations[<?php echo "{$page_type}_below_content"; ?>]" class="check"
					<?php if ( isset( $recommendations[ "{$page_type}_below_content" ] ) ) { ?>
						<?php echo ( $recommendations[ "{$page_type}_below_content" ] == 'on' ? 'checked' : '' ); ?>
					<?php } ?> />
					<label for="recommendations_<?php echo "{$page_type}_below_content"; ?>"><?php echo ucfirst( $position ); ?> Content</label>
				</div>
				<?php } ?>
			</fieldset>
		<?php } ?>
				
		<div class='fieldset-footer'>
			<p>
			<input type="checkbox" id="recommendations_excerpts" name="shareaholic[recommendations_display_on_excerpts]" class="check"	
			<?php if ( isset( $settings['recommendations_display_on_excerpts'] ) ) { ?>
				<?php echo ( $settings['recommendations_display_on_excerpts'] == 'on' ? 'checked' : '' ); ?>
			<?php } ?>>
			<label for="recommendations_excerpts">Display on excerpts</label>
			</p>
		</div>
		
		<div class='fieldset-footer'>
			<p>
				<?php printf( __( 'Note: Shareaholic offloads Related Posts processing to the cloud, which means there is no additional load on your server or database, giving you the fastest and most efficient Related Posts solution on the market. The %1$scloud API%2$s starts working as soon as your site is live. Until the cloud-based system starts, we use a basic placeholder API powered by this plugin. This placeholder API is temporary and does not respect advanced settings such as content exclusion rules.', 'shareaholic' ), '<a href="https://shrlc.com/1IzOGiI" target="_blank">', '</a>' ); ?>
			</p>
		</div>
		</div>
		</div>
	
		<div class="app">
		<input type='submit' class="btn btn-primary btn-lg btn-block" onclick="this.value='<?php printf( __( 'Saving Changes...', 'shareaholic' ) ); ?>';" value='<?php printf( __( 'Save Changes', 'shareaholic' ) ); ?>'>
		</div>
		</form>
	</div>
	<?php
		ShareaholicUtilities::load_template( 'why_to_sign_up', array( 'url' => Shareaholic::URL ) );

		$sync_status = ShareaholicUtilities::location_sync_status();
	if ( $sync_status == 409 ) {
		// Try syncing plugin locations if not in sync with Cloud
		ShareaholicUtilities::location_sync_to_cloud();
	}
	?>
	</div>
	</div>
</div>
<?php ShareaholicAdmin::show_footer(); ?>
<?php ShareaholicAdmin::include_chat(); ?>
