<?php
/**
 * The Shipping rules table. 
 * This file runs inside the shipping class, generate_shipping_rules_table_html()
 *
 * @package Advanced Shipping Rates for WC
 * @since 1.0.0
 * @version 2.1.1
 */
 
defined( 'ABSPATH' ) || exit;

global $Fish_n_Ships, $Fish_n_Ships_Wizard;

// Global Group by is a must in the free version
if ( $this->global_group_by == 'no' && !$Fish_n_Ships->im_pro()  ) {
	$this->config_errors['global-group-by'] = 'Error: Only the Pro version allow distinct grouping criteria on every selection condition';
}

// Table header, used also on footer
$header = '
			<td class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all">' . esc_html__('Select all') . '</label>
				<input class="cb-select-all" type="checkbox">
			</td>
			<th class="order-number-head">N.</th>
			<th>'.esc_html__('Selection conditions', 'fish-and-ships').' <a class="woocommerce-help-tip woocommerce-fns-help-popup" data-fns-tip="sel_conditions" data-tip="' . esc_attr__('Here you can set one or more selection conditions for each rule. Each selection condition will met if at least one cart product or group of products matches. Click to open detailed help about every selection method.', 'fish-and-ships') . '"></a></th>
			<th>'.esc_html( __('Shipping costs', 'fish-and-ships').' (' . get_woocommerce_currency_symbol() . ')') . ' <a class="woocommerce-help-tip woocommerce-fns-help-popup" data-fns-tip="shipping_costs" data-tip="' . esc_attr__('The shipping costs will be applied only if the rule selection conditions match. Click to open detailed help about shipping costs.', 'fish-and-ships') . '"></a></th>
			<th>'.esc_html__('Special actions', 'fish-and-ships').' <a class="woocommerce-help-tip woocommerce-fns-help-popup" data-fns-tip="special_actions" data-tip="' . esc_attr__('The actions will be executed only if the rule selection conditions match. Click to open detailed help about every special action.', 'fish-and-ships') . '"></a></th>
			<th scope="col" class="manage-column column-handle"></th>
		';

// Extra header
$extra_header  = '<tbody class="fns-ruletype-title"><tr><th colspan="6"><span class="dashicons dashicons-arrow-down"></span> ';
$extra_header .= esc_html__('Extra fees, handling, discounts', 'fish-and-ships');
$extra_header .= ' <span class="dashicons dashicons-arrow-down"></span> <a class="woocommerce-help-tip woocommerce-fns-help-popup" data-fns-tip="extra_fees" data-tip="' . esc_attr__('Here you can add extra fees or discounts after shipping rate calculation (after all the above rules have been parsed)', 'fish-and-ships') . '"></a></th></tr></tbody>';

//First let's close table, envelope and clearfix:
$html = '
</table>
</div>
</div>
<div id="wrapper-shipping-rules-table-fns">';

// Let's put the tab currencies if there is more than one
$html .= $Fish_n_Ships->get_multicurrency_tabs();

$html .= '<table class="widefat striped css-conditions-table-fns ltr-text-direction" id="shipping-rules-table-fns">
	<thead>
		<tr>' . $header . '</tr>
	</thead>
';

// Let's load once the selection methods and actions
$this->selection_methods = apply_filters('wc_fns_get_selection_methods', array());
$all_actions = apply_filters('wc_fns_get_actions', array());

// There is no rules yet? Let's put empty one
if (!is_array($this->shipping_rules) || count($this->shipping_rules)==0) {
	$this->shipping_rules = array();
}

$rule_nr = 0;
$rule_nr_visual = 0;          // Human count only, for each type will start again
$last_rule_type  = 'unknown'; // or empty, or foo, whatever

foreach ($this->shipping_rules as $shipping_rule) {

	// request the cells info
	$new_row = $Fish_n_Ships->shipping_rules_table_cells();
	
	// The new selector rule type since 1.4.0
	$rule_type = isset( $shipping_rule['type'] ) ? $shipping_rule['type'] : 'normal';
	if ( !in_array( $rule_type, array ( 'normal', 'extra' ) ) ) $rule_type = 'normal';

	// On change of rule type, open a new tbody section and restart visual counter
	if ( $last_rule_type != $rule_type) {
		$rule_nr_visual = 0; 
		if ( $last_rule_type  != 'unknown' ) $html .= '</tbody>';
		
		if ($rule_type == 'extra') $html .= $extra_header;
		
		$html .= '<tbody class="fns-ruletype-container-' . esc_attr($rule_type) . '">';
		$last_rule_type = $rule_type;
	}

	$new_row['check-column']['content'] = str_replace( '[rule_type_selector]', $Fish_n_Ships->get_rule_type_selector_html($rule_nr, $rule_type), $new_row['check-column']['content'] );

	$new_row['order-number']['content'] = '#' . ($rule_nr_visual + 1); // Human count starts on 1, not 0
	
	// The selection rules cell
	$sel_html = '';
	if( isset($shipping_rule['sel']) )
	{
		$logical_operator = $Fish_n_Ships->get_logical_operator($shipping_rule);
		
		if( $logical_operator == 'and' || $logical_operator == 'or' )
		{
			$block_html = $Fish_n_Ships->get_empty_selector_block_html();
			$sel_html .= str_replace( '[selector_block]', $this->generate_selector_block_html( $shipping_rule['sel'], $rule_nr, false, $rule_type ), $block_html );
		}

		if( $logical_operator == 'and-or-and' )
		{
			$n_block = 0; // Inner AND blocks counter
			
			foreach( $shipping_rule['sel'] as $n_key => $selector_block )
			{
				// Only key numbers are really selectors
				if ($n_key !== intval($n_key)) continue;
				
				// Error on format
				if( !isset($selector_block['block']) )
				{
					$idx = 'and-or-and-block';
					if ( !isset( $this->config_errors[$idx] ) ) {
						$this->config_errors[$idx] = 'Bad format in and-or-and selector: conditional block expected';
					}
					continue;
				}

				$block_html = $Fish_n_Ships->get_empty_selector_block_html();
				$sel_html .= str_replace( '[selector_block]', $this->generate_selector_block_html( $selector_block['block'], $rule_nr, $n_block, $rule_type ), $block_html );
				
				$n_block++;
			}
		}

		// There is only one logical operator pair of fields for every rule selection
		$new_row['selection-rules-column']['content'] = str_replace('[logical_operators]', $Fish_n_Ships->get_logical_operator_html( $rule_nr, $shipping_rule ), $new_row['selection-rules-column']['content'] );
		// Unknown method? Let's advice about it! (once)
		$idx = 'logical-operator-' . $Fish_n_Ships->get_logical_operator( $shipping_rule );
		if ( !isset( $this->config_errors[$idx] ) ) {
			$known = $Fish_n_Ships->is_known('logical_operator', $Fish_n_Ships->get_logical_operator( $shipping_rule ), $rule_type );
			if ($known !== true) $this->config_errors[$idx] = $known;
		}
	}
	
	// There is not any selection method? put an empty one
	if ($sel_html=='') {
		$sel_html = $Fish_n_Ships->get_selector_method_html(0, $this->selection_methods);
		$sel_html = str_replace('[selection_details]', '', $sel_html);
		$new_row['selection-rules-column']['content'] = str_replace('[logical_operators]', $Fish_n_Ships->get_logical_operator_html( $rule_nr, array() ), $new_row['selection-rules-column']['content'] );
	}
	
	$new_row['selection-rules-column']['content'] = str_replace('[selectors]', $sel_html, $new_row['selection-rules-column']['content']);


	// The cost cell

	$cost_method = '';
	$cost_values = array();

	if (isset($shipping_rule['cost']) && is_array($shipping_rule['cost'])) {
		foreach ($shipping_rule['cost'] as $cost) {
			if (is_array($cost) && isset($cost['method']) && isset($cost['values'])) {

				// Unknown method? Let's advice about it! (once)
				$idx = 'cost-' . $cost['method'];
				if ( !isset( $this->config_errors[$idx] ) ) {
					$known = $Fish_n_Ships->is_known('cost', $cost['method'], $rule_type);
					if ($known !== true) $this->config_errors[$idx] = $known;
				}

				$cost_method = $cost['method'];
				$cost_values = $cost['values'];
				if ( !is_array($cost_values) ) $cost_values = array();
				break;
			}
		}
	}

	$new_row['shipping-costs-column']['content'] = str_replace(
								'[cost_method_field]', 
								$Fish_n_Ships->get_cost_method_html(0, $cost_method), 
								$new_row['shipping-costs-column']['content']);

	$new_row['shipping-costs-column']['content'] = str_replace(
								'[cost_input_fields]', 
								apply_filters( 'wc_fns_get_html_price_fields', '', $rule_nr, $cost_values ),
								$new_row['shipping-costs-column']['content']);


	// The actions cell
	$actions_html = '';
	$action_nr = 0;
	if (isset($shipping_rule['actions'])) {
		foreach ($shipping_rule['actions'] as $action) {
			if (is_array($action) && isset($action['method'])) {

				// Unknown method? Let's advice about it! (once)
				$idx = 'action-' . $action['method'];
				if ( !isset( $this->config_errors[$idx] ) ) {
					$known = $Fish_n_Ships->is_known('action', $action['method'], $rule_type);
					if ($known !== true) $this->config_errors[$idx] = $known;
				}
				
				// The selector
				$this_action_html = $Fish_n_Ships->get_action_method_html(0, $all_actions, $action['method']);

				// His auxiliary fields
				$action_details = '';
				if (isset($action['values']) && is_array($action['values'])) {
					$action_details = apply_filters('wc_fns_get_html_details_action', '', $rule_nr, $action_nr, $action['method'], $action['values']);
				}
				
				$actions_html .= str_replace('[action_details]', $action_details, $this_action_html);

				$action_nr++;
			}
		}
	}	
	$new_row['special-actions-column']['content'] = str_replace('[actions]', $actions_html, $new_row['special-actions-column']['content']);

	// ...and parse it as HTML
	$wrapper = array ( 'class' => 'fns-ruletype-' . $rule_type . '  fns-logic_' . $logical_operator);
	$html .= apply_filters('wc_fns_shipping_rules_table_row_html', array( 'wrapper' => $wrapper, 'cells' => $new_row ) );
	
	$rule_nr++;
	$rule_nr_visual++;
}

// there is no rules yet? let's put an empty one
if ($rule_nr == 0) {

	$html .= '<tbody class="fns-ruletype-container-normal">';

	// request the cells info
	$new_row = $Fish_n_Ships->shipping_rules_table_cells();
	
	// The new selector rule type since 1.4.0
	$new_row['check-column']['content'] = str_replace( '[rule_type_selector]', $Fish_n_Ships->get_rule_type_selector_html(0), $new_row['check-column']['content'] );
	
	$new_row['order-number']['content'] = '#1';
	
	$selector = $Fish_n_Ships->get_selector_method_html(0, $this->selection_methods);
	$selector = str_replace('[selection_details]', '', $selector);

	$new_row['selection-rules-column']['content'] = str_replace('[selectors]', $selector, $new_row['selection-rules-column']['content']);
	$new_row['selection-rules-column']['content'] = str_replace('[logical_operators]', $Fish_n_Ships->get_logical_operator_html( $rule_nr, array() ), $new_row['selection-rules-column']['content'] );
	
	$new_row['shipping-costs-column']['content'] = str_replace(
								'[cost_input_fields]', 
								apply_filters( 'wc_fns_get_html_price_fields', '', 0, array() ),
								$new_row['shipping-costs-column']['content']);

	$new_row['shipping-costs-column']['content'] = str_replace(
								'[cost_method_field]',
								$Fish_n_Ships->get_cost_method_html(0, ''), 
								$new_row['shipping-costs-column']['content']);
	
	$new_row['special-actions-column']['content'] = str_replace('[actions]', '', $new_row['special-actions-column']['content']);

	// ...and parse it as HTML
	$wrapper = array ( 'class' => 'fns-ruletype-normal fns-logic_and' );  // AND logic by default
	$html .= apply_filters('wc_fns_shipping_rules_table_row_html', array( 'wrapper' => $wrapper, 'cells' => $new_row ) );

	// Close tbody normal, add the extra header and open the type extra
	$html .= '</tbody>' . $extra_header . '<tbody class="fns-ruletype-container-extra">';

} else if ( $last_rule_type == 'normal' ) {

	// There isn't any extra rule? let's open the extra header anyway
	$html .= '</tbody>' . $extra_header;
	$html .= '<tbody class="fns-ruletype-container-extra">';
}

// Here we close the last tbody
$html .= '</tbody>';


$html .= '
	<tfoot>
		<tr class="fns-footer-header">' . $header . '</tr>
		<tr>
			<td colspan="6">
				<a href="#" class="button add-rule"><span class="dashicons dashicons-plus"></span> ' . esc_html__('Add a new rule', 'fish-and-ships') . '</a>
				<a href="#" class="button add-rule-extra"><span class="dashicons dashicons-plus"></span> ' . esc_html__('Extra fee / discount', 'fish-and-ships') . '</a>
				<a href="#" class="button duplicate-rules"><span class="dashicons dashicons-admin-page"></span> ' . esc_html__('Duplicate selected rules', 'fish-and-ships') . '</a>
				<a href="#" class="button delete-rules"><span class="dashicons dashicons-no"></span> ' . esc_html__('Delete selected rules', 'fish-and-ships') . '</a>
				<a href="#" class="button wc-fns-add-snippet button-primary"><span class="dashicons dashicons-plus"></span> ' . esc_html__('Add snippet', 'fish-and-ships') . '</a>
			</td>
		</tr>
	</tfoot>
</table>
<div class="overlay"><span class="wc-fns-spinner"></span></div>
</div>
<table class="form-table">
';

// Import / Export buttons. Later will be moved by JavaScript
$html .= '<p class="wc-fns-export-buttons">
<a href="#" class="button wc-fns-export"><span class="dashicons dashicons-arrow-up-alt"></span> ' . esc_html__('Export settings', 'fish-and-ships') . '</a>
<a href="#" class="button wc-fns-import"><span class="dashicons dashicons-arrow-down-alt"></span> ' . esc_html__('Import settings', 'fish-and-ships') . '</a>
</p>';

// Errors? put it before
if (count($this->config_errors) > 0) {
	
	$err_message  = '<div class="error inline"><h3>Warning! Errors found!</h3>';
	$err_message .= '<p><strong>Please, solve this issues before save this method options, otherwise you will lose your configuration.</strong></p>';
	
	foreach ($this->config_errors as $error) {
	
		$error = esc_html($error);
		$error = str_replace('Error:', '<span class="wc-fns-error-text">Error:</span>', $error);
		$error = str_replace('Warning:', '<span class="wc-fns-error-text">Warning:</span>', $error);
		$err_message .= '<p>&#8226; ' . $error . '</p>';
	}
	$err_message .= '<p><a href="https://wordpress.org/support/plugin/fish-and-ships/" class="button" target="_blank">' . esc_html__('Get support on WordPress repository', 'fish-and-ships') . '</a> &nbsp; <a href="https://www.wp-centrics.com/contact-support/" class="button" target="_blank">' . esc_html__('Contact us', 'fish-and-ships') . '</a></p></div>';
	
	$html = $err_message . $html;
}

// Put the samples helper
$html .= $Fish_n_Ships_Wizard->get_samples_helper();

// Put the range wizard
$html .= '<div class="fns-range-wizard-wrapper"><div class="fns-range-wizard">';

if( ! $Fish_n_Ships->im_pro() )
{
	$html .= '<div class="fns-notice-pro">
		<p><strong>This feature is part of Advanced Shipping Rates for WooCommerce PRO.</strong> This is just a demo: no charges will apply.</p>
		</div>';
}

$html .= '<div class="fns_fields_popup">
		<p class="fns-ranges-based"><label><strong>Ranges based on:</strong></label>
		<select name="dialog-range_based">
			<option value="by-weight" selected>' . _x('Weight', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="by-volume">' . _x('Volume', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="volumetric">' . _x('Volumetric', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="volumetric-set">' . _x('Volumetric set', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="quantity">' . _x('Quantity', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="lwh-dimensions">' . _x('Length+Width+Height', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
			<option value="lgirth-dimensions">' . _x('Length+Girth (L+2W+2H)', 'shorted, select-by conditional', 'fish-and-ships') . '</option>
		</select></p>' .		
		$Fish_n_Ships->get_multicurrency_tabs() . '
		<div class="fns_range_fields">';
		
			$currencies      = $Fish_n_Ships->get_currencies();
			
			$weight_unit     = get_option('woocommerce_weight_unit');
			$dimension_unit  = get_option('woocommerce_dimension_unit');
			
			$units_switcher  = '<span class="fns-unit-switcher"><span>' . $weight_unit . '</span><span>' . $dimension_unit . '<sup>3</sup></span>'
							 . '<span>' . $weight_unit . '</span><span>' . $weight_unit . '</span><span></span><span>' . $dimension_unit . '</span><span>' 
							 . $dimension_unit . '</span></span>';

			$mcfields = array(
							'range_base' => array(
													'label' => 'Flat cost',
													'description' => 'the flat part, base charge:',
													'class' => 'wc_fns_input_decimal',
													'wrap_class' => 'fns-base-cost-col'
												 ),
							'range_charge' => array(
													'label' => 'Per-range charge',
													'description' => 'charge it per each range',
													'class' => 'wc_fns_input_positive_decimal',
													'wrap_class' => 'fns-charge-col'
												 ),
			);
													
				
			// Multicurrency fields:
			foreach ( $mcfields as $field_name => $field )
			{
				$html .= '<p class="' . esc_html($field['wrap_class']) . '"><label><strong>' . esc_html($field['label']) . '</strong><br>' . esc_html($field['description']) . '</label><span class="field_wrapper"><span class="currency-switcher-fns-wrapper">';

				$n = 0;
				foreach ( $currencies as $currency=>$symbol )
				{	
					$n++;
					// Main currency haven't sufix, it brings legacy with previous releases
					$curr_sufix = ''; if ( $n > 1 ) $curr_sufix = '-' . $currency;

					$html .= '<span class="currency-fns-field currency-' . $currency . ($n==1 ? ' currency-main' : '') . '">';
					$html .= '<input type="text" name="dialog-' . $field_name . $curr_sufix . '" value="0" class="' . esc_attr( $field['class'] . ' fns-'.$field_name ) . '" autocomplete="off" size="4">';
					$html .= ' <span class="units">' . esc_html($symbol) . '</span></span>';
				}
				$html .= '</span></span></p>';
			}
			
			$html .= '<p class="fns-for-each-col"><label><strong>Foreach range</strong><br>the interval ranges value</label><input type="text" name="dialog-range_foreach" value="0" class="wc_fns_input_positive_decimal fns-range_foreach" autocomplete="off" size="4"> ' . $units_switcher . '</p>';
			$html .= '<p class="fns-range-over-col"><label><strong>Flat cost until</strong><br>without range charges until:</label><input type="text" name="dialog-range_over" value="0" class="wc_fns_input_positive_decimal fns-range_over" autocomplete="off" size="4"> ' . $units_switcher . '</p>';
			
			$html .= '<p class="fns-grouping-range-col"><label><strong>Group products for range charge</strong><br>apply ranges separately to each group:'
					. '</label><select name="dialog-range_group_by">';

					foreach ($Fish_n_Ships->get_group_by_options() as $key=>$caption) {
						$html .= '<option value="' . esc_attr($key) . '">' . esc_html($caption) . '</option>';
					}

			$html .= '</select></p>';
			
			// Added DIM in range dialog (2.0.1)
			$html .= '<p class="fns-dim-col"><label><strong>DIM (' . __( 'Volumetric Weight Factor', 'fish-and-ships' ) . ' ' . get_option('woocommerce_dimension_unit') . '<sup style="font-size:0.75em; vertical-align:0.25em">3</sup>/' . get_option('woocommerce_weight_unit') . ')</strong>:'
					. '</label><input type="text" name="dialog-range_dim" value="0" class="wc_fns_input_positive_decimal fns-range_dim" autocomplete="off" size="4"></p>';
			
			$html .= '</div><div class="fns-simulation-wrapper"><p><strong>Charge preview:</strong></p><div class="fns-simulation"></div></div></div>
</div></div>';
