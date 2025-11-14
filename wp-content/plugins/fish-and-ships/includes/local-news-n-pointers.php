<?php
/**
 * Local news & pointers, loaded from wizard.php
 *
 * @package Advanced Shipping Rates for WC
 * @version 1.5.8
 */

defined( 'ABSPATH' ) || exit;

// We're inisde class Fish_n_Ships_Wizard, load_all_messages() function
global $Fish_n_Ships;

$local_news_n_pointers = array();

if( $wizard_on_method )
{
	
	$local_news_n_pointers['wizard-name'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( 'Name it' ),
		'content'   => __( 'This is the name your customers will see. And it will help them choose if more than one shipping method is available on the checkout page.<br><br>(You can conditionally rename it later using a special action).' ),

		'where'     => array( 'woocommerce_page_wc-settings' ), // unset? acts as wildcard
		'auto_open' => true,

		'anchor'    => '#woocommerce_fish_n_ships_title', // jQuery selector
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		// Vertical alignment
		'edge'      => 'top', // top | bottom | middle(over)
		'align'     => 'middle', // left | right | middle

		/* Horizontal alignment
		'edge'      => 'right', // left | right
		'align'     => 'top', // top | bottom | middle */
	);

	$local_news_n_pointers['wizard-group-by'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( 'Set the Group-by strategy' ),
		'content'   => __( 'It will determine how the cart products should be grouped (or not) to decide if they match the selection conditions.<br><br>
		<img src="' . WC_FNS_URL . 'help/img/group-by-tooltip.jpg" width="259" height="117" alt="Group-by strategy" /><br>
		<br>For example: to compare every product weight, set it to "None, no grouping". But to consider the weight of all products together, set it as "all grouped together". <a href="#" class="woocommerce-fns-help-popup" data-fns-tip="group_by">Read more</a>.' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => array('#woocommerce_fish_n_ships_global_group_by_method', '#woocommerce_fish_n_ships_global_group_by'),

		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		// Vertical alignment
		'edge'      => 'top',
		'align'     => 'middle',
	);

	$local_news_n_pointers['rules-table'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( 'Here the heart: the rules table' ),
		'content'   => __( 'Briefly: for each rule, when the <strong>Selection conditions</strong> are met, <strong>Shipping costs</strong> are applied and <strong>Special actions</strong> (if any) are executed.<br><br>
		<img src="' . WC_FNS_URL . 'help/img/table-tooltip.jpg" width="282" height="123" alt="Group-by strategy" />
		
		' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '#wrapper-shipping-rules-table-fns',
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		'edge'      => 'bottom',
		'align'     => 'middle',
	);

	$local_news_n_pointers['rules-table-sel'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( '1. Set the condition(s)' ),
		'content'   => __( 'Set the condition(s) for the first rule.<br><br>Note: You can add multiple conditions by click in the "Add a selector" button.' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '#wrapper-shipping-rules-table-fns .dropdown-submenu-wrapper:first',
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		'edge'      => 'bottom',
		'align'     => 'left',
	);

	$local_news_n_pointers['rules-table-cost'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( '2. Set the cost(s)' ),
		'content'   => __( 'Set the cost(s) for the first rule.<br><br>Note: You can choose between once, per quantity, percent, etc. ...or all together!' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '#wrapper-shipping-rules-table-fns .shipping-costs-column:first',
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		'edge'      => 'bottom',
		'align'     => 'left',
	);

	$local_news_n_pointers['rules-table-actions'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( '3. Optionally add special action(s)' ),
		'content'   => __( 'Here the super-powers ;)<br><br> For example: do you want to skip the next rules if this one is applicable? Add the special action "STOP. Ignore below rules" here.' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '#wrapper-shipping-rules-table-fns .special-actions-column:first',
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		'edge'      => 'bottom',
		'align'     => 'left',
	);

	$local_news_n_pointers['rules-table-add'] = array(

		'type'      => 'pointer',
		'priority'  => 5,

		'title'     => esc_html__( 'Add more rules' ),
		'content'   => __( 'Add as many rules as you need to cover all cases.' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '.button.add-rule',
		
		'close_bt'  => 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>',
		
		'edge'      => 'left',
		'align'     => 'middle',
	);

}
else
{
	// Show only for old users
	$local_news_n_pointers['add-case'] = array(

		'type'      => 'pointer',
		'priority'  => 10,

		'title'     => esc_html__( 'A quick way to get started' ),
		'content'   => __( '...is by selecting a pre-solved full case example that closely matches the configuration you need. You can make any changes you want after that.' ),

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,
		
		'anchor'    => '.woocommerce-fns-case',
		'edge'      => 'top',
		'align'     => 'left',
	);
}

// Shown in both cases: as novelty for old users, and in tour for new ones
$local_news_n_pointers['add-snippets'] = array(

	'type'      => 'pointer',
	'priority'  => $wizard_on_method ? 5 : 10,

	'title'     => esc_html__( 'Easy as cake...' ),
	'content'   => __( 'Enhance your shipping rules with pre-defined, useful snippets: a quick way to establish new conditions and learn.' ),

	'where'     => array( 'woocommerce_page_wc-settings' ),
	'auto_open' => true,

	'anchor'    => '.wc-fns-add-snippet',
	'edge'      => 'right',
	'align'     => 'middle',
);
// Custom button inside wizard for it!
if( $wizard_on_method )
{
	$local_news_n_pointers['add-snippets']['close_bt'] = 'Next <span class="dashicons dashicons-arrow-right-alt2"></span>';
}

// And we will end with this message for wizard:
if( $wizard_on_method )
{
	$local_news_n_pointers['wizard-free-shipping'] = array(

		'type'      	=> 'pointer',
		'priority'  	=> 5,

		'title'     	=> esc_html__( 'Allow free shipping?' ),
		'content'   	=> __( 'If you want to offer free shipping, activate this option.<br><br>Note: otherwise, a price of zero will not be offered.' ),

		'where'     	=> array( 'woocommerce_page_wc-settings' ),
		'auto_open' 	=> true,

		'anchor'    	=> '#woocommerce_fish_n_ships_free_shipping',

		'close_bt'  	=> '<span class="dashicons dashicons-dismiss"></span> Finish',
		'extra_action'  => 'wizard-end',
		
		'edge' 			=> 'bottom',
		'align'			=> 'left',
	);
}


// Announce the new feature only for old users
if( 
	$Fish_n_Ships->im_pro() 
	&& version_compare($Fish_n_Ships->get_option('first_version'), '1.5.8', '<' ) 
) {
	$local_news_n_pointers['nf-order-notes'] = array(

		'type'      => 'pointer',
		'priority'  => 15,
		'unique'    => true,

		'title'     => esc_html__( 'New feature!' ),
		'content'   => 'Now you can add private or customer <strong>order notes</strong> conditionally. Look into Text/Information actions.',

		'where'     => array( 'woocommerce_page_wc-settings' ),
		'auto_open' => true,

		'anchor'    => '#wrapper-shipping-rules-table-fns .special-actions-column:first',

		//'close_bt'  => '<span class="dashicons dashicons-dismiss"></span> Finish',
		//'extra_action'  => 'wizard-end',
		
		'edge'      => 'top',
		'align'     => 'left',
	);
}

/* Where 
add_action( 'admin_enqueue_scripts', function( $page ) {
	echo '$page: ' . $page;
	print_r($_GET);
	print_r($_POST);
	die('F&S local-news-n-pointers.php');
});
*/