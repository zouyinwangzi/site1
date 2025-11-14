<?php
/**
 * Add on for Plugin Republic's WooCommerce Product Add-Ons Ultimate (PR_PAU)
 *
 * @package Advanced Shipping Rates for WC
 * @since 2.1.1
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_PR_PAU' ) ) {
	
	class Fish_n_Ships_PR_PAU {
		
		private $fields  = false;
				
		/**
		 * Constructor.
		 *
		 * @since 1.6.2
		 */
		public function __construct() {
			
			// Let's advice about support for this plugin
			add_filter( 'wc_fns_wizard_messages', array ( $this, 'add_message' ), 10, 2 );
			
			add_filter('wc_fns_get_selection_methods', array ( $this, 'wc_fns_get_selection_methods_fn' ) , 20, 1);
						
			add_filter('wc_fns_get_html_details_method', array ( $this, 'wc_fns_get_html_details_method_fn' ), 20, 6);

			add_filter( 'wc-fns-groupable-selection-methods', array ($this, 'wc_fns_groupable_selection_methods_fn' ), 20, 1 );

			add_filter('wc_fns_sanitize_selection_fields', array ( $this, 'wc_fns_sanitize_selection_fields_fn' ), 20, 1);

			add_filter('wc_fns_check_matching_selection_method', array ( $this, 'wc_fns_check_matching_selection_method_fn' ) , 20, 5);
			
			add_filter( 'wc_fns_group_external_calculate', array ($this, 'wc_fns_group_external_calculate_fn' ), 10, 5 );

			add_filter( 'wc_fns_get_messages_method', array ($this, 'wc_fns_get_messages_method_fn' ), 10, 4 );

			/*
			add_filter( 'wc_fns_get_vars_for_math', array ($this, 'wc_fns_get_vars_for_math_fn' ), 10, 4 );
			*/
		}

		/**
		 * Pointers to announce support
		 *
		 * @since 1.6.2
		 *
		 */
		public function add_message( $news_and_pointers, $wizard_on_method )
		{
			if( ! $wizard_on_method ) 
			{
				$news_and_pointers['pr_pau-support'] = array(

					'type'      => 'pointer',
					'priority'  => 8,

					'content'   => __( 'From now on, we support <strong>Product Add-Ons Ultimate</strong> for WC, from <strong>Plugin Republic</strong>. <br><br>Here you can set conditions based on Product Fields.' ),
					'auto_open' => true,
					
					'anchor'    => array('div.wc-fns-selection-method:first', '#toplevel_page_woocommerce'),
					'edge'      => 'left',
					'align'     => 'middle',
				);
			}
			return $news_and_pointers;
			
		}
		
		/**
		 * Filter to get all selection methods
		 *
		 * @since 1.6.2
		 *
		 * @param $methods (array) maybe incomming  a pair method-id / method-name array
		 *
		 * @return $methods (array) a pair method-id / method-name array
		 *
		 */
		function wc_fns_get_selection_methods_fn($methods = array()) {
			
			$scope_all     = array ('normal', 'extra');
			
			$methods[ 'pr_pau-min-max-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by ID) value MIN/MAX' );					
			$methods[ 'pr_pau-label-min-max-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by Label) value MIN/MAX' );					

			$methods[ 'pr_pau-min-max-price' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by ID) price MIN/MAX' );	
			$methods[ 'pr_pau-label-min-max-price' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by Label) price MIN/MAX' );	
			
			$methods[ 'pr_pau-equal-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by ID) value equals' );					
			$methods[ 'pr_pau-label-equal-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by Label) value equals' );					

			$methods[ 'pr_pau-not-equal-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by ID) value NOT equals' );					
			$methods[ 'pr_pau-label-not-equal-value' ] = array( 'onlypro' => false, 'group' => 'PR Product Add-Ons Ult.', 'scope' => $scope_all,  'label' => 'PAU (by Label) value NOT equals' );					

			return $methods;
		}
		
		/**
		 * Filter to get the HTML selection fields for one method
		 *
		 * @since 1.6.2
		 *
		 * @param $html (HTML) maybe incomming html
		 * @param $rule_nr (integer) the rule number
		 * @param sel_nr (integer) the selection number inside rule or total?
		 * @param $method_id (mixed) the method-id
		 * @param $values (array) the saved values 
		 * @param $previous (bootlean) true: JS array of empty fields | false: real field or AJAX insertion
		 *
		 * @return $html (HTML) the HTML selection fields
		 *
		 */
		function wc_fns_get_html_details_method_fn($html, $rule_nr, $sel_nr, $method_id, $values, $previous) {

			global $Fish_n_Ships;
			
			switch ( $method_id )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
			
					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_integer_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', true, 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $Fish_n_Ships->get_min_max_comp_html($rule_nr, $sel_nr, $method_id, '', $values, 'sel', 'selection', 'val_info', 'ge', 'less')
							 . $Fish_n_Ships->get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
					
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
				
					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $Fish_n_Ships->get_min_max_comp_html($rule_nr, $sel_nr, $method_id, '', $values, 'sel', 'selection', 'val_info', 'ge', 'less')
							 . $Fish_n_Ships->get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'pr_pau-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_integer_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '','', true, 'wc-fns-ajax-info-field wc-fns-mid-input'  ) . '</span>'
							 . '<br /><span class="envelope-fields">&nbsp;' . 'EQUALS TO: ' . $Fish_n_Ships->get_text_field('pr_pau_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'pr_pau-label-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br /><span class="envelope-fields">&nbsp;' . 'EQUALS TO: ' . $Fish_n_Ships->get_text_field('pr_pau_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
				
				case 'pr_pau-not-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_integer_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '','', true, 'wc-fns-ajax-info-field wc-fns-mid-input'  ) . '</span>'
							 . '<br /><span class="envelope-fields">&nbsp;' . 'NOT EQUALS TO: ' . $Fish_n_Ships->get_text_field('pr_pau_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'pr_pau-label-not-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('pr_pau_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br /><span class="envelope-fields">&nbsp;' . 'NOT EQUALS TO: ' . $Fish_n_Ships->get_text_field('pr_pau_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
			}
			return $html;
		}
	
		/**
		 * Filter to set the groupable methods
		 *
		 * @since 1.6.2
		 *
		 * @param $groupable_methods (array) methods keys
		 */
		function wc_fns_groupable_selection_methods_fn( $groupable_methods ) {

			$new_methods = array( 'pr_pau-min-max-value', 'pr_pau-label-min-max-value', 'pr_pau-min-max-price', 'pr_pau-label-min-max-price' );
			
			$groupable_methods = array_merge( $groupable_methods, $new_methods );

			return $groupable_methods;
		}
		
		/**
		 * Filter to sanitize one selection criterion and his auxiliary fields prior to save in the database
		 *
		 * @since 1.6.2
		 */
		function wc_fns_sanitize_selection_fields_fn($rule_sel) {
						
			global $Fish_n_Ships;

			//Prior failed?
			if( ! is_array($rule_sel) ) return $rule_sel;

			$allowed = false;
			
			switch( $rule_sel['method'] )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
				
					$allowed = array('min', 'max', 'min_comp', 'max_comp', 'group_by', 'pr_pau_field' );

					break;

				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 
				
					$allowed = array( 'pr_pau_field', 'pr_pau_equals' );
					break;
			}
			
			// A PR_PAU method? Let's unset not allowed fields
			if (is_array($allowed))
			{
				// Remove not allowed values
				foreach ($rule_sel['values'] as $field => $val) {
					if (!in_array($field, $allowed)) unset($rule_sel['values'][$field]);
				}

				// Add null for missing values (will be turned to default after)
				foreach ($allowed as $field) {
					if ( ! isset($rule_sel['values'][$field]) )
						$rule_sel['values'][$field] = null;
				}
			}
			
			// Now let's sanitize known fields
			switch( $rule_sel['method'] )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
				
					$rule_sel['values']['group_by'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['group_by'],
																						array_keys($Fish_n_Ships->get_group_by_options()));
					
					// This field has distinct sanitization, due the method:
					if( $rule_sel['method'] == 'pr_pau-min-max-value' || $rule_sel['method'] == 'pr_pau-min-max-price' )
					{
						$rule_sel['values']['pr_pau_field'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['pr_pau_field'], 'positive-integer');
					}
					else
					{
						$rule_sel['values']['pr_pau_field'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['pr_pau_field']);
					}

					$rule_sel['values']['min'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['min'], 'positive-decimal');
					$rule_sel['values']['max'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['max'], 'positive-decimal');
					$rule_sel['values']['min_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['min_comp'],
																						array ( 'ge', 'greater' ) );
					$rule_sel['values']['max_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['max_comp'],
																							array ( 'less', 'le' ) );
					break;

				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 
				
					// This field has distinct sanitization, due the method:
					if( $rule_sel['method'] == 'pr_pau-equal-value' || $rule_sel['method'] == 'pr_pau-not-equal-value' )
					{
						$rule_sel['values']['pr_pau_field'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['pr_pau_field'], 'positive-integer');
					}
					else
					{
						$rule_sel['values']['pr_pau_field'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['pr_pau_field']);
					}

					$rule_sel['values']['pr_pau_equals'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['pr_pau_equals']);

					break;
			}
			return $rule_sel;
		}
			
		/**
		 * Filter to check matching elements for selection method
		 *
		 * @since 1.6.2
		 *
		 * @param $rule_groups (array) all the groups of current rule
		 * @param $selector (array) the selector criterion
		 * @param $group_by (mixed) the group method 
		 * @param $shipping_class (reference) the class reference 
		 * @param $logical_operator and | or
		 *
		 * @return $rule_groups (array)
		 *
		 */
		function wc_fns_check_matching_selection_method_fn($rule_groups, $selector, $group_by, $shipping_class, $logical_operator = 'and') {

			global $Fish_n_Ships;
			
			// Prepare the selection auxiliary fields
			switch( $selector['method'] )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
					
					$min = 0; if (isset($selector['values']['min'])) $min = $selector['values']['min'];
					$max = '*'; if (isset($selector['values']['max'])) $max = $selector['values']['max'];

					if ( trim($min) == '' ) $min = 0;
					
					// MAX field set as 0, will be taken as wildcard
					if ( trim($max) == '' || $max==0 ) $max = '*';
					
					$min_comp = 'ge';   if (isset($selector['values']['min_comp'])) $min_comp = $selector['values']['min_comp'];
					$max_comp = 'less'; if (isset($selector['values']['max_comp'])) $max_comp = $selector['values']['max_comp'];
					
					break;
					
				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 

					// Get the comparation F&S field value
					$pr_pau_equals = isset( $selector['values']['pr_pau_equals']) ? $selector['values']['pr_pau_equals'] : '';

					break;
				
				default:
					// For performance, let's skip the following code, so we aren't on a PR_PAU method
					return $rule_groups;
					break;
			}
			
			// Get the target to lookin for
			$target = $this->get_looking_target( $selector['method'] );

			// Get the PR_PAU field
			$lookin_field = isset( $selector['values']['pr_pau_field']) ? $selector['values']['pr_pau_field'] : '';
			
			if( $lookin_field == '' )
			{
				$shipping_class->debug_log('PR_PAU ERROR : empty field \'' . $target . '\' in method: [' . $selector['method'] . ']', 2);
			}
			

			// Let's iterate in his group_by groups
			foreach ($rule_groups[$group_by] as $group)
			{	
				// empty or previously unmatched? bypass for performance
				if( $group->is_empty() || ! $group->is_match() ) continue;
				
				// Let's check if no product in group has the field
				$field_in_group = $this->is_field_in_group( $group, $target, $lookin_field );
				
				// We will advice this if there isn't a lookin field empty error before
				if( $lookin_field != '' && ! $field_in_group )
				{
					if( $group_by != 'none' )
					{
						$shipping_class->debug_log('PR_PAU info, method: [' . $selector['method'] . ']: no product in group have the field: \'' . $target . '\' / \''.$lookin_field . '\'.', 3);
					} else {
						// Get the ID of the unique product, for logging:
						$id = $Fish_n_Ships->get_prod_or_variation_id( $group->elements[ array_key_first($group->elements) ] );
						$shipping_class->debug_log('PR_PAU info, method: [' . $selector['method'] . ']: the product #'.$id.' have not the field: \'' . $target . '\' / \''.$lookin_field . '\'.', 3);
					}
				}
				
				// In both cases, we will act as no match and skip to next group:
				if( $lookin_field == ''	|| ! $field_in_group )
				{
					// Same logic as evaluate false here
					if ($logical_operator == 'and')
						// unmatch this group
						$Fish_n_Ships->unmatch_group($group, $rule_groups);
						
					continue;
				}
				
				// Let's check if the group matches:
				switch( $selector['method'] )
				{
					case 'pr_pau-min-max-value' :
					case 'pr_pau-min-max-price' :
					case 'pr_pau-label-min-max-value' :
					case 'pr_pau-label-min-max-price' :

						// Here we will read the PR_PAU field value
						$value = $group->get_total($selector['method'], $selector);

						// The MIN/MAX comparison 
						if (
								(// Min field comparison
									//$min != 0 // MIN don't needs 0 as wildcard
									//&& 
									(
										(
											$min_comp == 'greater' && $min >= $value
										) || (
											$min_comp != 'greater' && $min > $value // ge, by default
										)
									) 
								) || (
									(// Max field comparison
										$max !== '*' // not wildcard
										&&
										(
											(
												$max_comp == 'le' && $max < $value
											) || (
												$max_comp != 'le' && $max <= $value // less, by default
											)
										)
									)
								)
						) {
							if ($logical_operator == 'and')
								// unmatch this group
								$Fish_n_Ships->unmatch_group($group, $rule_groups);
							
						} else {
							if ($logical_operator == 'or')
								// this grup matches
								$group->or_flag = true;
						}
					
						break;

					case 'pr_pau-equal-value' :
					case 'pr_pau-not-equal-value' :
					case 'pr_pau-label-equal-value' : 
					case 'pr_pau-label-not-equal-value' : 
						
						$result = false;
						
						// Although this methods can't be grouped, the product is contained into array
						foreach ($group->elements as $product)
						{
							if( isset( $product[ 'product_extras' ] ) )
							{
								// Looping through PR_PAU product groups
								foreach( $product['product_extras']['groups'] as $pe_group )
								{
									// Looping through fields inside group
									foreach( $pe_group as $field )
									{
										$shipping_class->debug_log('PR_PAU DEV INFO, method: [' . $selector['method'] . '], ' . $target . ': ['.$lookin_field.'], value: [' . (isset($field['value']) ? $field['value'] : 'no value key' ) . '], lookin value: [' . $pr_pau_equals . ']', 2);
										
										$field_target = $field[$target];

										// Not matching the lookin field? skip it
										if( $target == 'label' ) 
										{
											// If we're looking for label, we will apply the same sanitization here
											// that PAU applies in inc/functions-cart.php: sanitize_text_field() (i.e. remove html tags)
											if( sanitize_text_field($field_target) != sanitize_text_field($lookin_field) )
												continue;
										}
										else
										{
											if( $field_target != $lookin_field )
												continue;
										}

										// Not matching the lookin field? skip it
										if( $field_target != $lookin_field )
											continue;
										
										// The field haven't value key? Bump it
										if( ! isset( $field['value'] ) )
										{
											$shipping_class->debug_log('PR_PAU info, method: [' . $selector['method'] . ']: field \'value\' unset in field ID: ' . $lookin_field, 3);
											$result = false;
											break 3;
										}
										
										// Value is equal?
										// We will apply the same sanitization here that we're applied to the rules input field, to ensure matching
										if( 
											( $selector['method'] == 'pr_pau-equal-value' || $selector['method'] == 'pr_pau-label-equal-value' ) && 
											$Fish_n_Ships->sanitize_html($field['value']) == $pr_pau_equals
										){
											$result = true;
										}

										// Value is NOT equal?
										if( 
											( $selector['method'] == 'pr_pau-not-equal-value' || $selector['method'] == 'pr_pau-label-not-equal-value' ) && 
											$Fish_n_Ships->sanitize_html($field['value']) != $pr_pau_equals 
										){
											$result = true;
										}

										// End loops
										break 3;
									}
								}
							} // if closure, not a closing loop!!!
						}

						if( ! $result ) {
							
							if ($logical_operator == 'and')
								// unmatch this group
								$Fish_n_Ships->unmatch_group($group, $rule_groups);
							
						} else {
							
							if ($logical_operator == 'or')
								// this grup matches
								$group->or_flag = true;
						}

						break;
				}
			}
			
			return $rule_groups;
		}

		/**
		 * calculate one total
		 *
		 * Here we use the new 5th parameter, used for getting the PR_PAU field ID to looking for.
		 *
		 * @since 1.6.2
		 */
		function wc_fns_group_external_calculate_fn ( $external, $what, $product, $qty, $selector ) {

			global $Fish_n_Ships;

			if ( ! is_array( $product ) || ! is_array( $selector ) )
				return $external;
			
			$target         = $this->get_looking_target( $selector['method'] );
			$looking_index  = $this->get_looking_index(  $selector['method'] );

			if( $target === false )
			{
				// NO F&S PR_PAU selectors. Nothing to do, we will return $external.
				return $external;
			}

			switch ( $what )
			{
				case 'pr_pau-min-max-value':
				case 'pr_pau-min-max-price':
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :

					// Get the PR_PAU field
					$lookin_field = isset( $selector['values']['pr_pau_field']) ? $selector['values']['pr_pau_field'] : '';
					
					// Empty field? Let's bump it
					if( $lookin_field == '' )
					{
						$external = array(
										'value'       => 0,
										'item_value'  => 'PR_PAU ERROR : empty field ' . $target . ': 0'
						);
						return $external;
					}
					
					if( isset( $product[ 'product_extras' ] ) )
					{
						// Looping through groups
						foreach( $product['product_extras']['groups'] as $pe_group )
						{
							// Looping through fields inside group
							foreach( $pe_group as $field )
							{
								$field_target = $field[$target];

								// Not matching the lookin field? skip it
								if( $target == 'label' ) 
								{
									// If we're looking for label, we will apply the same sanitization here
									// that PAU applies in inc/functions-cart.php: sanitize_text_field() (i.e. remove html tags)
									if( sanitize_text_field($field_target) != sanitize_text_field($lookin_field) )
										continue;
								}
								else
								{
									if( $field_target != $lookin_field )
										continue;
								}
								
								// Check for supported types for min-max methods based on value (all types can be used to min/max price)
								$supported_types = array( 'number', 'calculation' );
								
								if( ( $what == 'pr_pau-min-max-value' || $what == 'pr_pau-label-min-max-value' ) && ! in_array( $field['type'], $supported_types ) )
								{
									$external = array(
													'value'       => 0,
													'item_value'  => 'PR_PAU ERROR: unsupported type: [' . $field['type'] . ']: 0'
									);
									return $external;
								}
																
								if( isset( $field[$looking_index] ) )
								{
									$external = array(
													'value'       => $field[$looking_index] * $qty,
													'item_value'  => 'PR_PAU: field found [' . $field['type'] . '], value: ' . $field[$looking_index]
									);
								}
								else
								{
									$external = array(
													'value'       => 0,
													'item_value'  => 'PR_PAU ERROR: field unset: [' . $looking_index . ']: 0'
									);
								}
								return $external;
							}
						}
					}
					
					$external = array(
									'value'       => 0,
									'item_value'  => 'Haven\'t this PR_PAU field: 0'
					);
					break;
			}
			
			return $external;
		}

		// Get the target to lookin for
		function get_looking_target($selector_method)
		{
			switch( $selector_method )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :

					return 'field_id';
					break;
					
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 
				
					return 'label';
					break;

				default:
					
					// NO F&S PR_PAU selectors.
					return false;
					break;
			}
		}
		
		// Get the index to lookin for
		function get_looking_index($selector_method)
		{
			switch( $selector_method )
			{
				case 'pr_pau-min-max-value' :
				case 'pr_pau-label-min-max-value' :

				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 

					return 'value';
					break;

				case 'pr_pau-min-max-price' :
				case 'pr_pau-label-min-max-price' :
				
					return 'price';
					break;

				default:
					
					// NO F&S PR_PAU selectors.
					return false;
					break;
			}
		}

		/**
		 * Check if no product in group has the lookin field
		 *
		 * @since 1.6.2
		 *
		 * @param $group (class object) the group of products to lookin into
		 * @param $target (string) field_id | label
		 * @param $lookin_field (string) the label / field ID what we lookin for (introduced in the table rule's selector field)
		 *
		 * @return boolean
		 *
		 */
		function is_field_in_group( $group, $target, $lookin_field )
		{
			global $Fish_n_Ships;
			
			foreach ($group->elements as $product)
			{
				if( ! isset( $product['product_extras'] ) )
					continue;

				// Looping through PR_PAU product groups
				foreach( $product['product_extras']['groups'] as $pe_group )
				{
					// Looping through fields inside group
					foreach( $pe_group as $field )
					{
						$field_target = $field[$target];

						// Matching the lookin field? return true
						if( $target == 'label' ) 
						{
							// If we're looking for label, we will apply the same sanitization here
							// that PAU applies in inc/functions-cart.php: sanitize_text_field() (i.e. remove html tags)
							if( sanitize_text_field($field_target) == sanitize_text_field($lookin_field) )
								return true;
						}
						else
						{
							if( $field_target == $lookin_field )
								return true;
						}
					}
				}
			}
			return false;
		}


		/**
		 * AJAX info based on user input fields
		 *
		 * @since 1.6.2
		 */
		function wc_fns_get_messages_method_fn( $message, $type, $method_id, $raw_params )
		{
			switch( $method_id )
			{
				// Cases per ID
				case 'pr_pau-min-max-value' :
				case 'pr_pau-min-max-price' :
				case 'pr_pau-equal-value' :
				case 'pr_pau-not-equal-value' :
				
					$field_id     = isset( $raw_params['pr_pau_field'] ) ? intval( $raw_params['pr_pau_field'] ) : 0;
					$check_field  = get_post( $field_id );
					
					if( $field_id == 0 )
						return 'Please, insert a field ID';
					
					if( ! $check_field instanceof WP_Post || $check_field->post_type != 'pewc_field' || $check_field->post_status != 'publish') 
						return 'Error: there is no PR PAU field with ID #' . $field_id;
					
					$group_id     = $check_field->post_parent;
					$check_group  = get_post( $group_id );

					if( $check_field->post_status != 'publish' || ! $check_group instanceof WP_Post || $check_group->post_type != 'pewc_group' ) 
						return 'Error: PR PAU field with ID #' . $field_id . ' unavailable';
					
					if( $check_group->post_parent == 0 )
					{
						return 'Global field. Group: "' . get_post_meta( $group_id, 'group_title', true ) . '", field: "' . get_post_meta( $field_id, 'field_label', true ) . '"';
					} 
					else
					{
						$product_id     = $check_group->post_parent;
						$check_product  = wc_get_product($product_id);

						if( ! $check_product )
						{
							return 'Error: Local PR PAU field with ID #' . $field_id . ' no belongs to any product';
						}
						else
						{
							return 'Local field. Product: #' . $product_id . ' "' . $check_product->get_name() . '", field: "' . get_post_meta( $field_id, 'field_label', true ) . '"';
						}
					}
					
					break;
					
				// Cases per label
				case 'pr_pau-label-min-max-value' :
				case 'pr_pau-label-min-max-price' :
				case 'pr_pau-label-equal-value' : 
				case 'pr_pau-label-not-equal-value' : 
				
					$last_valid_field    = null;
					$last_valid_group    = null;
					$last_valid_product  = null;
				
					$field_label = isset( $raw_params['pr_pau_field'] ) ? $raw_params['pr_pau_field'] : '';
					
					if( trim( $field_label ) == '' )
						return 'Please, insert a label field';

					// Get fields by label:
					$args = [
						'post_type'   => 'pewc_field',
						'meta_query'  => [
							[
								'key'       => 'field_label',
								'value'     => $field_label,
								'compare'   => '=' // Comparador, pots canviar-lo per LIKE, >, <, etc.
							]
						],
						'posts_per_page'    => -1,
						'suppress_filters'  => false,
					];
					$fields = get_posts($args);

					$local_fields   = 0;
					$global_fields  = 0;
					
					if( ! empty($fields) )
					{
						foreach( $fields as $field )
						{
							$check_group  = get_post( $field->post_parent );

							if( $field->post_status != 'publish' || ! $check_group instanceof WP_Post || $check_group->post_type != 'pewc_group' ) 
								continue;
							
							if( $check_group->post_parent == 0 )
							{
								$global_fields++;

								// Let's remember it, we will need it if there is unique valid field
								$last_valid_group  = $check_group;
								$last_valid_field  = $field;
							} 
							else
							{
								$product_id     = $check_group->post_parent;
								$check_product  = wc_get_product($product_id);

								if( $check_product )
								{
									$local_fields++;

									// Let's remember it, we will need it if there is unique valid field
									$last_valid_group    = $check_group;
									$last_valid_field    = $field;
									$last_valid_product  = $check_product;
								}
							}

						}
					}
					
					if( $local_fields == 0 && $global_fields == 0 )
					{
						return 'Error: there is no PR PAU field that matches with this label';
					}
					else if( ( $local_fields == 1 && $global_fields == 0 ) || ( $local_fields == 0 && $global_fields == 1 ) )
					{
						// Unique field? Let's give detailed info about:
						if( $last_valid_group->post_parent == 0 )
						{
							return 'Global field. Group: "' . get_post_meta( $last_valid_group->ID, 'group_title', true ) . '", field: "' . get_post_meta( $last_valid_field->ID, 'field_label', true ) . '"';
						}
						else
						{
							return 'Local field. Product: #' . $last_valid_product->get_id() . ' "' . $last_valid_product->get_name() . '", field: "' . get_post_meta( $last_valid_field->ID, 'field_label', true ) . '"';
						}
					}
					else if( $local_fields == 0 )
					{
						return sprintf( '%u global PR PAU fields match with this label.', $global_fields );
					}
					else if( $global_fields == 0 )
					{
						return sprintf( '%u local PR PAU fields match with this label.', $local_fields );
					}
					else
					{
						return sprintf( '%u global and %u local PR PAU fields match with this label.', $global_fields, $local_fields );
					}
					break;
					
				default:
			}
		
			return $message; // Nothing to do, isn't a PR_PAU method
		}

		/**
		 * set the variables for math calculation
		 *
		 * @since 1.6.2
		 */
		function wc_fns_get_vars_for_math_fn ( $vars, $group_by, $group, $shipping_class ) {
			
			//$vars['mpc_var'] = 15;
			
			return $vars;
		}

	}
	global $Fish_n_Ships_PR_PAU;
	$Fish_n_Ships_PR_PAU = new Fish_n_Ships_PR_PAU();
}

