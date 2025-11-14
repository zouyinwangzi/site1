<?php
/**
 * The freemium panels: Free and Pro panels. (Save serial for Pro, check license, etc)
 *
 * @package Advanced Shipping Rates for WC
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

global $Fish_n_Ships;

$options = $Fish_n_Ships->get_options();

if ($Fish_n_Ships->im_pro()) {

} else {
	$html = '
	</table>
	<div class="fns-clearfix">
	<div id="wc-fns-freemium-panel" class="free-version ' . ($options['close_freemium'] < time() ? 'opened' : 'closed') . '">
		<h2>Advanced Shipping Rates for WC (free)</h2>
		<a href="#" class="close_panel"><span class="dashicons dashicons-dismiss"></span></a>
		<a href="#" class="open_panel"><span class="dashicons dashicons-plus-alt"></span></a>
		<div class="wrap_content">';
		
		$html .= '<p>' . esc_html__('This plugin is free and updates through the official WordPress plugins repository. The free version has some feature limitations.', 'fish-and-ships') . '</p>';
		

		$html .= '<div class="can_close">' . wp_kses( 
			'<h3>' . __('Upgrade to <strong>Pro</strong> and unlock the full potential:', 'fish-and-ships')
			. '</h3><ul>'
			. '<li>' . __('28 extra selectors & 7 range assistants', 'fish-and-ships') . '</li>'
			. '<li>' . __('Advanced conditions & multiple group-by strategies', 'fish-and-ships') . '</li>'
			. '<li>' . __('15 special actions: Flow control, custom notices & messages, extra fees, packer algorythm and more', 'fish-and-ships') . '</li>'
			. '<li>' . __('Priority support and regular updates', 'fish-and-ships') . '</li>'
			. '</ul>', array('strong'=>array(),'li'=>array(),'ul'=>array(),'h3'=>array()) ) . '</div>';

		$html .= '		
		<p class="center go_button"><a href="https://www.wp-centrics.com/advanced-shipping-rates-woocommerce-free-vs-pro/" class="button button-primary" target="_blank">Compare Free vs PRO</a> &nbsp; 
		<a href="https://app.instawp.io/launch?t=advanced-shipping-rates-for-woocommerce-demo&d=v2" class="button" target="_blank">Run Demo</a></p>
		</div>
	</div>
	<div id="wc-fns-table_envelope">
	<table class="form-table">';
}