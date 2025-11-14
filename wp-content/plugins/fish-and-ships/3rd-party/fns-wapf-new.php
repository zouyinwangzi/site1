<?php
/**
 * Add on for StudioWombat Advanced Product Fields for WooCommerce (WAPF)
 * Since 2.0.1, previous methods still supported on the fns-wapf-legacy.php file
 *
 * @package Fish and Ships
 * @since 2.0.1
 * @version 2.1.3
 */
 
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_WAPF_NEW' ) ) {
	
	class Fish_n_Ships_WAPF_NEW {
		
		private $fields  = false;
				
		/**
		 * Constructor.
		 *
		 * @since 2.0.1
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
		 * @since 2.0.1
		 *
		 */
		public function add_message( $news_and_pointers, $wizard_on_method )
		{
			if( ! $wizard_on_method ) 
			{
				$news_and_pointers['wapf-support'] = array(

					'type'      => 'pointer',
					'priority'  => 8,

					'content'   => __( 'From now on, we support <strong>Advanced Product Fields</strong> for WooCommerce, from <strong>StudioWombat</strong>. <br><br>Here you can set conditions based on Product Fields.' ),
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
		 * @since 2.0.1
		 *
		 * @param $methods (array) maybe incomming  a pair method-id / method-name array
		 *
		 * @return $methods (array) a pair method-id / method-name array
		 *
		 */
		function wc_fns_get_selection_methods_fn($methods = array()) {

			$scope_all     = array ('normal', 'extra');
			
			$methods[ 'wapf-min-max-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by ID) value MIN/MAX' );					
			$methods[ 'wapf-label-min-max-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by Label) value MIN/MAX' );					

			$methods[ 'wapf-min-max-price' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by ID) price MIN/MAX' );	
			$methods[ 'wapf-label-min-max-price' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by Label) price MIN/MAX' );	
			
			$methods[ 'wapf-equal-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by ID) value equals' );					
			$methods[ 'wapf-label-equal-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by Label) value equals' );					

			$methods[ 'wapf-not-equal-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by ID) value NOT equals' );					
			$methods[ 'wapf-label-not-equal-value' ] = array( 'onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF (by Label) value NOT equals' );					

			return $methods;
		}
		
		/**
		 * Filter to get the HTML selection fields for one method
		 *
		 * @since 2.0.1
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
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
			
					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_minmax_repeatable', $values, 'wapf_repeatable')
							 . $Fish_n_Ships->get_min_max_comp_html($rule_nr, $sel_nr, $method_id, '', $values, 'sel', 'selection', 'val_info', 'ge', 'less')
							 . $Fish_n_Ships->get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
					
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
				
					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_minmax_repeatable', $values, 'wapf_repeatable')
							 . $Fish_n_Ships->get_min_max_comp_html($rule_nr, $sel_nr, $method_id, '', $values, 'sel', 'selection', 'val_info', 'ge', 'less')
							 . $Fish_n_Ships->get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'wapf-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '','', 'wc-fns-ajax-info-field wc-fns-mid-input'  ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_eqne_repeatable', $values, 'wapf_repeatable')
							 . '<span class="envelope-fields">&nbsp;' . 'EQUALS TO: ' . $Fish_n_Ships->get_text_field('wapf_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'wapf-label-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_eqne_repeatable', $values, 'wapf_repeatable')
							 . '<span class="envelope-fields">&nbsp;' . 'EQUALS TO: ' . $Fish_n_Ships->get_text_field('wapf_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
				
				case 'wapf-not-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field ID: #' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '','', 'wc-fns-ajax-info-field wc-fns-mid-input'  ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_eqne_repeatable', $values, 'wapf_repeatable')
							 . '<span class="envelope-fields">&nbsp;' . 'NOT EQUALS TO: ' . $Fish_n_Ships->get_text_field('wapf_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;

				case 'wapf-label-not-equal-value' : 

					$html .= '<span class="envelope-fields">&nbsp;' . 'Field Label: ' . $Fish_n_Ships->get_text_field('wapf_field', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '', '', 'wc-fns-ajax-info-field wc-fns-mid-input' ) . '</span>'
							 . '<br />' . $this->get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, 'wapf_eqne_repeatable', $values, 'wapf_repeatable')
							 . '<span class="envelope-fields">&nbsp;' . 'NOT EQUALS TO: ' . $Fish_n_Ships->get_text_field('wapf_equals', $rule_nr, $sel_nr, $method_id, '', '', $values, 'sel', 'selection', '' ) . '</span>'
							 . $Fish_n_Ships->cant_get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
					$html .= '<div class="wc-fns-ajax-info"><span class="wc-fns-spinner"></span></div>';

					break;
			}
			return $html;
		}
	
		/**
		 * Filter to set the groupable methods
		 *
		 * @since 2.0.1
		 *
		 * @param $groupable_methods (array) methods keys
		 */
		function wc_fns_groupable_selection_methods_fn( $groupable_methods ) {

			$new_methods = array( 'wapf-min-max-value', 'wapf-label-min-max-value', 'wapf-min-max-price', 'wapf-label-min-max-price' );
			
			$groupable_methods = array_merge( $groupable_methods, $new_methods );

			return $groupable_methods;
		}
		
		/**
		 * Filter to sanitize one selection criterion and his auxiliary fields prior to save in the database
		 *
		 * @since 2.0.1
		 */
		function wc_fns_sanitize_selection_fields_fn($rule_sel) {
						
			global $Fish_n_Ships;

			//Prior failed?
			if( ! is_array($rule_sel) ) return $rule_sel;

			$allowed = false;
			
			switch( $rule_sel['method'] )
			{
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
				
					$allowed = array('min', 'max', 'min_comp', 'max_comp', 'group_by', 'wapf_field', 'wapf_repeatable' );

					break;

				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 
				
					$allowed = array( 'wapf_field', 'wapf_equals', 'wapf_repeatable' );
					break;
			}
			
			// A WAPF method? Let's unset not allowed fields
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
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
				
					$rule_sel['values']['group_by'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['group_by'],
																						array_keys($Fish_n_Ships->get_group_by_options()));
					
					// This field has distinct sanitization, due the method:
					if( $rule_sel['method'] == 'wapf-min-max-value' || $rule_sel['method'] == 'wapf-min-max-price' )
					{
						// IDs are also alphanumeric
						$rule_sel['values']['wapf_field'] = $Fish_n_Ships->sanitize_text($rule_sel['values']['wapf_field']);
					}
					else
					{
						$rule_sel['values']['wapf_field'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['wapf_field']);
					}
					
					$rule_sel['values']['wapf_repeatable'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['wapf_repeatable'],
																array_keys( $this->get_repeatable_minmax_opts() )
					);

					$rule_sel['values']['min'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['min'], 'positive-decimal');
					$rule_sel['values']['max'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['max'], 'positive-decimal');
					$rule_sel['values']['min_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['min_comp'],
																						array ( 'ge', 'greater' ) );
					$rule_sel['values']['max_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['max_comp'],
																							array ( 'less', 'le' ) );
					break;

				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 
				
					// This field has distinct sanitization, due the method:
					if( $rule_sel['method'] == 'wapf-equal-value' || $rule_sel['method'] == 'wapf-not-equal-value' )
					{
						// IDs are also alphanumeric
						$rule_sel['values']['wapf_field'] = $Fish_n_Ships->sanitize_text($rule_sel['values']['wapf_field']);
					}
					else
					{
						$rule_sel['values']['wapf_field'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['wapf_field']);
					}

					$rule_sel['values']['wapf_equals'] = $Fish_n_Ships->sanitize_html($rule_sel['values']['wapf_equals']);

					$rule_sel['values']['wapf_repeatable'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['wapf_repeatable'],
																array_keys( $this->get_repeatable_eqne_opts() )
					);

					break;
			}
			return $rule_sel;
		}
			
		/**
		 * Filter to check matching elements for selection method
		 *
		 * @since 2.0.1
		 * @version 2.1.3
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
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
					
					$min = 0; if (isset($selector['values']['min'])) $min = $selector['values']['min'];
					$max = '*'; if (isset($selector['values']['max'])) $max = $selector['values']['max'];

					if ( trim($min) == '' ) $min = 0;
					
					// MAX field set as 0, will be taken as wildcard
					if ( trim($max) == '' || $max==0 ) $max = '*';
					
					$min_comp = 'ge';   if (isset($selector['values']['min_comp'])) $min_comp = $selector['values']['min_comp'];
					$max_comp = 'less'; if (isset($selector['values']['max_comp'])) $max_comp = $selector['values']['max_comp'];
					
					break;
					
				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 

					// Get the comparation F&S field value
					$wapf_equals = isset( $selector['values']['wapf_equals']) ? $selector['values']['wapf_equals'] : '';
					
					break;
				
				default:
					// For performance, let's skip the following code, so we aren't on a WAPF method
					return $rule_groups;
					break;
			}
			
			// Get the target to lookin for
			$target = $this->get_looking_target( $selector['method'] );

			// What to do in repeatables
			$wapf_repeatable = isset( $selector['values']['wapf_repeatable']) ? $selector['values']['wapf_repeatable'] : 'one';

			// Get the WAPF field
			$lookin_field = isset( $selector['values']['wapf_field']) ? $selector['values']['wapf_field'] : '';
			
			if( $lookin_field == '' )
			{
				$shipping_class->debug_log('WAPF ERROR : empty field \'' . $target . '\' in method: [' . $selector['method'] . ']', 2);
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
						$shipping_class->debug_log('WAPF info, method: [' . $selector['method'] . ']: no product in group have the field: \'' . $target . '\' / \''.$lookin_field . '\'.', 3);
					} else {
						// Get the ID of the unique product, for logging:
						$id = $Fish_n_Ships->get_prod_or_variation_id( $group->elements[ array_key_first($group->elements) ] );
						$shipping_class->debug_log('WAPF info, method: [' . $selector['method'] . ']: the product #'.$id.' have not the field: \'' . $target . '\' / \''.$lookin_field . '\'.', 3);
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
					case 'wapf-min-max-value' :
					case 'wapf-min-max-price' :
					case 'wapf-label-min-max-value' :
					case 'wapf-label-min-max-price' :

						// Here we will read the WAPF field value
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

					case 'wapf-equal-value' :
					case 'wapf-not-equal-value' :
					case 'wapf-label-equal-value' : 
					case 'wapf-label-not-equal-value' : 
						
						//$result = false;
						$results = array();
						
						// Although this methods can't be grouped, the product is contained into array
						foreach ($group->elements as $product)
						{
							if( isset( $product[ 'wapf' ] ) )
							{
								// Looping through WAPF fields
								foreach( $product['wapf'] as $field )
								{									
									$field_target = $field[$target];

									// Not matching the lookin field? skip it
									if( $target == 'label' ) 
									{
										// If we're looking for label, we will apply the same sanitization here
										// that WAPF applies in includes/classes/class-cart.php: esc_html() (it htmlentitize all)
										if( esc_html($field_target) != esc_html($lookin_field) )
											continue;
									}
									else
									{
										if( $field_target != $lookin_field )
											continue;
									}
									
									/*
									$shipping_class->debug_log('WAPF DEV INFO, method: [' . $selector['method'] . '], ' . $target . ': ['.$lookin_field.'], value: [' . (isset($field['raw']) ? $field['raw'] : 'no raw key' ) . '], lookin value: [' . $wapf_equals . ']', 3);
									
									// The field haven't raw key? Bump it
									if( ! isset( $field['raw'] ) )
									{
										$shipping_class->debug_log('WAPF info, method: [' . $selector['method'] . ']: field \'raw\' unset in field ID: ' . $lookin_field, 3);
										$results[] = false;
										break 2;
									}
									*/

									// Value is equal?
									if( ( $selector['method'] == 'wapf-equal-value' || $selector['method'] == 'wapf-label-equal-value' ) )
									{
										/*
										// We will apply the same sanitization here that we're applied to the rules input field, to ensure matching
										// Debug for Slawomir
									    if ( is_array( $field['raw'] ) ) {
											error_log('FnS: unexpected array! lookin for: ' . $wapf_equals);
											error_log(print_r($field, true));
											$results[]=false;
										}
										else
										{
											error_log(print_r($field, true));
											$results[] = $Fish_n_Ships->sanitize_html($field['raw']) == $wapf_equals;
										}
										*/
										$result = $this->deep_seek_value( $field, 'label', $wapf_equals );
										$results[] = $result;

										$shipping_class->debug_log('WAPF DEV INFO, method: [' . $selector['method'] . '], ' . $target . ': ['.$lookin_field.'], lookin value: [' . $wapf_equals . '], result: [' . ($result ? 'TRUE' : 'FALSE' ) . ']', 3);
									}

									// Value is NOT equal?
									if( ( $selector['method'] == 'wapf-not-equal-value' || $selector['method'] == 'wapf-label-not-equal-value' ) )
									{
										/*
										// We will apply the same sanitization here that we're applied to the rules input field, to ensure matching
										// Debug for Slawomir
									    if ( is_array( $field['raw'] ) ) {
											error_log('FnS: unexpected array! lookin for: ' . $wapf_equals);
											error_log(print_r($field, true));
											$results[]=true;
										}
										else
										{
											error_log(print_r($field, true));
											$results[] = $Fish_n_Ships->sanitize_html($field['raw']) != $wapf_equals; // here the logic is reverse: not equal is true
										}
										*/
										// Reversed here:
										$result = ! $this->deep_seek_value( $field, 'label', $wapf_equals );
										$results[] = $result;

										$shipping_class->debug_log('WAPF DEV INFO, method: [' . $selector['method'] . '], ' . $target . ': ['.$lookin_field.'], lookin value: [' . $wapf_equals . '], result: [' . ($result ? 'TRUE' : 'FALSE' ) . ']', 3);
									}
								}
							} // if closure, not a closing loop!!!
						}

						// if is repeteable, can be more than one result value
						if( count($results) == 0 )
						{
							$result = false;
						}
						else if( count($results) == 1 )
						{
							$result = $results[0];
						}
						else
						{
							$result = true;
							foreach( $results as $one_result )
							{
								if( $one_result && $wapf_repeatable=='one' )
								{
									$result = true;
									break;
								}
								if( ! $one_result )
									$result = false;
							}
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
		 *  deep_seek_value()
		 *
		 *  Beta for Slawomir: in some cases, the sought value is included in the "values" array
		 *  instead of "raw". For now, we will check in both.
		 * 
		 *  Example input:
		 *
		 *  [raw] => Array
		 *		(
		 *			[0] => fhc7a
		 *		)
		 *
		 *	[values] => Array
		 *		(
		 *			[0] => Array
		 *				(
		 *					[label] => the_value
		 *					[price] => 199
		 *					[price_type] => fixed
		 *					[slug] => fhc7a
		 *					[calc_price] => 199
		 *					[pricing_hint] => (<span class="wapf-addon-price">+164,46&nbsp;&#36;</span>)
		 *				)
		 *
		 *		)
		 *
		 *
		 *  Instead of:
		 *
		 *  [raw] => the_value
		 *
		 * @since 2.1.3
		 */
		function deep_seek_value( $field, $key, $seek_value )
		{
			// NOTE: We will apply the same sanitization through $Fish_n_Ships->sanitize_html()
			// that we're applied to the rules input field, to ensure matching
			global $Fish_n_Ships;
			
			// Let's look into values first:
			if( isset( $field['values'] ) )
			{
				if( is_array( $field['values'] ) )
				{
					foreach( $field['values'] as $seek )
					{
						if( is_array($seek) && isset($seek[$key]) && $Fish_n_Ships->sanitize_html($seek[$key]) == $seek_value )
							return true;
					}
				}
			}
			// Let's look into raw as fallback:
			elseif( isset( $field['raw'] ) )
			{
				// Comes into array?
				if( is_array( $field['raw'] ) )
				{
					foreach( $field['raw'] as $seek )
					{
						if( $Fish_n_Ships->sanitize_html($seek) == $seek_value )
							return true;
					}
				}
				else
				{
					return $Fish_n_Ships->sanitize_html($field['raw']) == $seek_value;
				}
			}
			
			return false;
		}
		

		/**
		 * calculate one total
		 *
		 * Here we use the new 5th parameter, used for getting the WAPF field ID to looking for.
		 *
		 * @since 2.0.1
		 */
		function wc_fns_group_external_calculate_fn ( $external, $what, $product, $qty, $selector ) {

			global $Fish_n_Ships;

			if ( ! is_array( $product ) || ! is_array( $selector ) )
				return $external;
			
			$target         = $this->get_looking_target( $selector['method'] );
			$looking_index  = $this->get_looking_index(  $selector['method'] );

			if( $target === false )
			{
				// NO F&S WAPF selectors. Nothing to do, we will return $external.
				return $external;
			}

			switch ( $what )
			{
				case 'wapf-min-max-value':
				case 'wapf-min-max-price':
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :

					// Get the WAPF field
					$lookin_field = isset( $selector['values']['wapf_field']) ? $selector['values']['wapf_field'] : '';
					
					// Get the repeatable strategy (maybe are repeatable)
					$wapf_repeatable = isset( $selector['values']['wapf_repeatable']) ? $selector['values']['wapf_repeatable'] : '';
					
					// Empty field? Let's bump it
					if( $lookin_field == '' )
					{
						$external = array(
										'value'       => 0,
										'item_value'  => 'WAPF ERROR : empty field ' . $target . ': 0'
						);
						return $external;
					}
					
					if( ! isset( $product[ 'wapf' ] ) )
					{
						$external = array(
										'value'       => 0,
										'item_value'  => 'Haven\'t this WAPF field: 0'
						);
						return $external;
					}
					else
					{
						// Maybe the field is repeatable, so we will store it in array by now		
						$values       = array();
						$item_values  = array();
						
						// We will detect also the clone type, for repeatable  per qty case
						$clone_type   = '';
						
						// Looping through groups
						foreach( $product['wapf'] as $field )
						{							
							$field_target = $field[$target];

							// Not matching the lookin field? skip it
							if( $target == 'label' ) 
							{
								// If we're looking for label, we will apply the same sanitization here
								// that WAPF applies in includes/classes/class-cart.php: esc_html() (it htmlentitize all)
								if( esc_html($field_target) != esc_html($lookin_field) )
									continue;
							}
							else
							{
								if( $field_target != $lookin_field )
									continue;
							}
							
							// Check for supported types for min-max methods based on value (all types can be used to min/max price)
							$supported_types = array( 'number', 'calc' );
							
							// 2.1.0 patch: not always the field type is here!
							if( isset( $field['type'] ) )
							{
								if( ( $what == 'wapf-min-max-value' || $what == 'wapf-label-min-max-value' ) && ! in_array( $field['type'], $supported_types ) )
								{
									$external = array(
													'value'       => 0,
													'item_value'  => 'WAPF ERROR: unsupported type: [' . $field['type'] . ']: 0'
									);
									return $external;
								}
							}
							
							// 2.1.0 patch: not always $field contains a values array!
							if( isset($field['values']) && isset($field['values'][0]) )
							{
								$unique_field = $field['values'][0]; // Unique way before 2.1.0
							}
							else
							{
								$unique_field = array(
													'type'          => ! empty( $field['type'] ) ? $field['type'] : 'Unknown',
													$looking_index  => ! empty( $field['raw']  ) ? $field['raw']  : 0,
												);
							}
															
							if( ! empty( $unique_field[$looking_index] ) )
							{
								$values[]       = $unique_field[$looking_index];
								$item_values[]  = 'field found [' . $unique_field['type'] . '], value: ' . $unique_field[$looking_index];
								$clone_type     = isset( $field['clone_type'] ) ? $field['clone_type'] : '';
							}
							else
							{
								$external = array(
												'value'       => 0,
												'item_value'  => 'WAPF ERROR: field unset: [' . $looking_index . ']: 0'
								);
								return $external;
							}
						}
					}
					
					// if is repeteable, can be more than one result value
					if( count($values) == 0 )
					{
						$external = array(
										'value'       => 0,
										'item_value'  => 'Haven\'t this WAPF field: 0'
						);
					}
					else if( count($values) == 1 )
					{
						$external = array(
										'value'       => floatval($values[0]) * $qty,
										'item_value'  => $item_values[0]
						);
					}
					else
					{
						$repeatable_value  = 0;
						$repeatable_log    = '';
						$first             = true; // ugly, but effective
						
						// Let's iterate to calculate the reply that need looping
						foreach( $values as $key=>$value )
						{
							switch( $wapf_repeatable )
							{
								case 'sum':
								case 'average':
									$repeatable_value += $value;
									break;
									
								case 'mul':
									$repeatable_value = ( $repeatable_value === 0 ? 1 : $repeatable_value ) * $value;
									break;

								case 'highest':
									if( $repeatable_value < $value ) $repeatable_value = $value;
									break;
					
								case 'lowest':
									if( $repeatable_value > $value || $first ) $repeatable_value = $value;
									break;
							}
							$first = false;
						}
						
						// Let's end the average calculation, set the non-looping values and build the log
						switch( $wapf_repeatable )
						{
							case 'sum':
								// no need more calculations
								$repeatable_log = 'WAPF: repeatable, SUM( ' . implode( ' + ', $item_values ) . ') = ' . $repeatable_value;
								break;
								
							case 'mul':
								// no need more calculations
								$repeatable_log = 'WAPF: repeatable, MUL( ' . implode( ' * ', $item_values ) . ') = ' . $repeatable_value;
								break;

							case 'average':
								$repeatable_value = $repeatable_value / count($values);
								$repeatable_log = 'WAPF: repeatable, AVERAGE( ' . implode( ' + ', $item_values ) . ') / ' . count($values) . ' = ' . $repeatable_value;
								break;
								
							case 'highest':
								// no need more calculations
								$repeatable_log = 'WAPF: repeatable, HIGHEST( ' . implode( ' , ', $item_values ) . ') = ' . $repeatable_value;
								break;

							case 'lowest':
								// no need more calculations
								$repeatable_log = 'WAPF: repeatable, LOWEST( ' . implode( ' , ', $item_values ) . ') = ' . $repeatable_value;
								break;

							case 'first':
								$repeatable_value = $values[0];
								$repeatable_log = 'WAPF: repeatable, FIRST( ' . implode( ' , ', $item_values ) . ') = ' . $repeatable_value;
								break;
				
							case 'last':
								$repeatable_value = $values[ count($values)-1 ];
								$repeatable_log = 'WAPF: repeatable, LAST( ' . implode( ' , ', $item_values ) . ') = ' . $repeatable_value;
								break;

							case 'zero':
								$repeatable_value = 0;
								$repeatable_log = 'WAPF: repeatable => ZERO';
								break;
						}

						// Here we take in consideration the clone type: if it is per quantity, 
						// we will no multiply the calculated result by product quantity
						$external = array(
										'value'       => $repeatable_value * ( $clone_type == 'qty' ? 1 : $qty),
										'item_value'  => $repeatable_log . ( $clone_type == 'qty' ? ' [CLONE TYPE QTY, FORCE 1 PROD. QTY FOR CALC]' : '' ),
						);
					} // end repeatable else
			} // end switch( $what )
			
			return $external;
		}

		// Get the target to lookin for
		function get_looking_target($selector_method)
		{
			switch( $selector_method )
			{
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :

					return 'id';
					break;
					
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 
				
					return 'label';
					break;

				default:
					
					// NO F&S WAPF selectors.
					return false;
					break;
			}
		}
		
		// Get the index to lookin for
		function get_looking_index($selector_method)
		{
			switch( $selector_method )
			{
				case 'wapf-min-max-value' :
				case 'wapf-label-min-max-value' :

				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 

					return 'label';
					break;

				case 'wapf-min-max-price' :
				case 'wapf-label-min-max-price' :
				
					return 'calc_price';
					break;

				default:
					
					// NO F&S WAPF selectors.
					return false;
					break;
			}
		}

		/**
		 * Check if no product in group has the lookin field
		 *
		 * @since 2.0.1
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
				if( ! isset( $product['wapf'] ) )
					continue;

				// Looping through WAPF fields
				foreach( $product['wapf'] as $field )
				{
					$field_target = $field[$target];

					// Matching the lookin field? return true
					if( $target == 'label' ) 
					{
						// If we're looking for label, we will apply the same sanitization here
						// that WAPF applies in includes/classes/class-cart.php: esc_html() (it htmlentitize all)
						if( esc_html($field_target) == esc_html($lookin_field) )
							return true;
					}
					else
					{
						if( $field_target == $lookin_field )
							return true;
					}
				}
			}
			return false;
		}


		/**
		 * AJAX info based on user input fields
		 *
		 * @since 2.0.1
		 */
		function wc_fns_get_messages_method_fn( $message, $type, $method_id, $raw_params )
		{
			global $Fish_n_Ships;
			
			// Els camps globals son guardats com a CPT wapf_product, i ho guarda al post_content
			// Els camps locals es guarden en un post_meta del producte: _wapf_fieldgroup
			
			switch( $method_id )
			{
				// Cases per ID
				case 'wapf-min-max-value' :
				case 'wapf-min-max-price' :
				case 'wapf-equal-value' :
				case 'wapf-not-equal-value' :
				
					$field_id = isset( $raw_params['wapf_field'] ) ? $Fish_n_Ships->sanitize_text( $raw_params['wapf_field'] ) : '';
					
					if( $field_id == '' )
						return 'Please, insert a WAPF field ID';

					// Seek into global fields first
					$field_found = $this->seek_into_globals( $field_id, 'id' );
					if( is_array( $field_found ) && count($field_found) > 0 )
					{
						// Only one field, comes into array form
						return 'Global WAPF field. Group: "' . $field_found[0]['group_name'] . '", field: "' . ( ! empty( $field_found[0]['label'] ) ? $field_found[0]['label'] : '(unlabeled)' ) . '"';
					}
					
					// Seek into local fields then
					$field_found = $this->seek_into_locals( $field_id, 'id' );
			
					if( ! $field_found )
					{
						return 'Error: there is no WAPF field with ID #' . $field_id;
					}

					if( count( $field_found ) == 1 )
					{			
						return 'Local WAPF field. Product: #' . $field_found[0]['product_id'] . ' "' . $field_found[0]['product_name'] . '", field: "' . ( ! empty( $field_found[0]['label'] ) ? $field_found[0]['label'] : '(unlabeled)' ) . '"';
					}

					return count($field_found) . ' WAPF products/fields will match.'; // unexpected, should be one!
					
					break;
					
				// Cases per label
				case 'wapf-label-min-max-value' :
				case 'wapf-label-min-max-price' :
				case 'wapf-label-equal-value' : 
				case 'wapf-label-not-equal-value' : 
				
					$results      = array();
					$field_label  = isset( $raw_params['wapf_field'] ) ? $Fish_n_Ships->sanitize_html( $raw_params['wapf_field'] ) : '';
					
					if( $field_label == '' )
						return 'Please, insert a WAPF label field';

					// Seek into global fields first
					$globals_found  = $this->seek_into_globals( $field_label, 'label' );

					if( is_array( $globals_found ) && count($globals_found) > 1 )
					{
						$results[] = count( $globals_found ) . ' WAPF global fields will match.'; 
					}
					else if( is_array( $globals_found ) && count($globals_found) == 1 )
					{
						$results[] = 'WAPF global field. Group: "' . $globals_found[0]['group_name'] . '", field: "' . ( ! empty( $globals_found[0]['label'] ) ? $globals_found[0]['label'] : '(unlabeled)' ) . '".';
					}


					// Seek into local fields then
					$locals_found   = $this->seek_into_locals(  $field_label, 'label' );

					if( is_array( $locals_found ) && count($locals_found) > 1 )
					{
						$results[] = count( $locals_found ) . ' local fields will match.';
					}
					else if( is_array( $locals_found ) && count($locals_found) == 1 )
					{
						$results[] = 'WAPF local field. Product: #' . $locals_found[0]['product_id'] . ' "' . $locals_found[0]['product_name'] . '", field: "' . ( ! empty( $locals_found[0]['label'] ) ? $locals_found[0]['label'] : '(unlabeled)' ) . '"';
					}

			
					if( count( $results ) == 0 )
					{
						return 'Error: there is no WAPF field with label "' . $field_label . '"';
					}
					
					return implode( ' + ', $results );
					
					break;
					
				default:
			}
		
			return $message; // Nothing to do, isn't a WAPF method
		}

		/**
		 * Get a selector for multicable cases
		 *
		 * @since 1.0.0
		 * @version 1.4.13
		 *
		 * @param $rule_nr (integer) rule ordinal (starting 0)
		 * @param $sel_nr (integer) selector ordinal inside rule (starting 0)
		 * @param $method_id (mixed) method id
		 * @param $datatype (mixed) the data type which we will offer values ( user_roles or taxonomy )
		 * @param $values (array) for populate fields
		 * @param $field_name (mixed) select name field
		 * @param $ambit_field (mixed) for class reference only
		 * @param $ambit(mixed) for class reference only
		 *
		 * @return $html (HTML code) form code for the fields min / max
		 *
		 */
		public function get_repeatable_sel_html($rule_nr, $sel_nr, $method_id, $datatype, $values, $field_name, $ambit_field='sel', $ambit='selection') {
			
			global $Fish_n_Ships, $Fish_n_Ships_Shipping;

			// Securing output
			$rule_nr       = intval($rule_nr);
			$sel_nr        = intval($sel_nr);
			$method_id     = esc_attr($method_id);
			$field_name    = esc_attr($field_name);
			$ambit_field   = esc_attr($ambit_field);
			$ambit         = esc_attr($ambit);

			$html = '<span class="field field-select '.$ambit.'-'.$method_id.' '.$ambit.'-'.$method_id.'-'.$field_name.'">
					In repeatables: <span class="woocommerce-help-tip" aria-label="Help"></span> <select autocomplete="off" required name="shipping_rules['.$rule_nr.']['.$ambit_field.']['.$method_id.']['.$field_name.']['.$sel_nr.']">';

			if ( $datatype == 'wapf_minmax_repeatable' ) {
				$options = $this->get_repeatable_minmax_opts();
			} 
			else if ( $datatype == 'wapf_eqne_repeatable' )
			{
				$options = $this->get_repeatable_eqne_opts();
			}
			else
			{
				return '';
			}
			
			foreach ($options as $key => $caption) {

				$selected = (in_array($key, $values)) ? ' selected ' : '';

				$html .= '<option value="' . esc_attr($key) . '"'.$selected .'>' . esc_html($caption) . '</option>';
			}
			$html .= '</select></span>';

			return $html;
		}

		function get_repeatable_minmax_opts()
		{
			return array( 
					'sum'      => 'SUM of values', 
					'mul'      => 'Multiplication',
					'average'  => 'Average value', 
					'highest'  => 'Get the highest',
					'lowest'   => 'Get the lowest',
					'first'    => 'Get first value',
					'last'     => 'Get last value', 
					'zero'     => 'Evaluate 0' 
					);
		}

		function get_repeatable_eqne_opts()
		{
			return array( 
					'one'      => 'At least one', 
					'all'      => 'All must',
					);
		}

		function seek_into_globals( $needle, $what = 'id' )
		{
			// We only expect here one product/field by ID, but can be more by label
			$found_fields = array();

			$args = [
				'post_type'   => 'wapf_product',
				'posts_per_page'    => -1,
				// 'suppress_filters'  => false,
			];
			$seek_globals = get_posts($args);
			
			if( is_array( $seek_globals ) )
			{
				foreach( $seek_globals as $seek )
				{
					$unserialized = $this->wapf_process_data($seek->post_content);
					
					if( ! is_array( $unserialized) || empty( $unserialized['fields'] ) || ! is_array( $unserialized['fields'] ) ||
						! isset( $unserialized['type'] ) || $unserialized['type'] != 'wapf_product' 
					)
						continue;
					
					foreach( $unserialized['fields'] as $field )
					{
						$field['group_name'] = $seek->post_title;
						if( $what=='id' && !empty( $field['id'] ) && $field['id'] == $needle )
							$found_fields[] = $field;

						if( $what=='label' && !empty( $field['label'] ) && $field['label'] == $needle )
							$found_fields[] = $field;
					}
				}
			}
			
			return count($found_fields) > 0 ? $found_fields : false;
		}

		function seek_into_locals( $needle, $what = 'id' )
		{
			global $wpdb;
			
			$sql = "
				SELECT * 
				FROM `{$wpdb->base_prefix}posts` AS p
				JOIN `{$wpdb->base_prefix}postmeta` AS pm ON p.ID = pm.post_id
				WHERE p.post_type IN ('product', 'product_variation') 
				AND p.post_status = 'publish' 
				AND pm.meta_key = '_wapf_fieldgroup' 
			";
			
			if( $what == 'id' )
			{
				// seeking something like: s:2:"id";s:13:"673f27c585089"
				$like_string = 's:2:"id";s:'.strlen($needle).':"'.$needle.'"';
				// Twice %% is needed to prevent prepare() enqueuing
				$sql = $wpdb->prepare( $sql . " AND pm.meta_value LIKE '%%%s%%';", $wpdb->esc_like($like_string) );
			}
			else if( $what == 'label' )
			{
				// seeking something like: s:5:"label";s:10:"Camp local"
				$like_string = 's:5:"label";s:'.strlen($needle).':"'.$needle.'"';
				// Twice %% is needed to prevent prepare() enqueuing
				$sql = $wpdb->prepare( $sql . " AND pm.meta_value LIKE '%%%s%%';", $wpdb->esc_like($like_string) );
			}
			else
			{
				return false;
			}

			$results = $wpdb->get_results( $sql );
			
			if( ! is_array( $results ) || count( $results) == 0 )
				return false;
			
			// We only expect here one product/field by ID, but can be more by label
			$found_fields = array();
			
			foreach( $results as $match )
			{
				$unserialized = $this->wapf_process_data($match->meta_value);
					
				if( ! is_array( $unserialized) || empty( $unserialized['fields'] ) || ! is_array( $unserialized['fields'] ) ||
					! isset( $unserialized['type'] ) || $unserialized['type'] != 'wapf_product' 
				)
					continue;
				
				// Here we loop the fields of the product
				foreach( $unserialized['fields'] as $field )
				{
					$field['product_name'] = $match->post_title;
					$field['product_id']   = $match->ID;
					if( $what=='id' && !empty( $field['id'] ) && $field['id'] == $needle )
					{
						$found_fields[] = $field;
					}

					if( $what=='label' && !empty( $field['label'] ) && $field['label'] == $needle )
					{
						$found_fields[] = $field;
					}
				}
			}					
			return $found_fields;
		}
		
		
		// Inspired in the WAPF process data function 
        function wapf_process_data($data)
		{
	        if( is_serialized( $data ) ) 
			{
	        	try
				{
	        		$unserialized = unserialize( $data, ['allowed_classes' => false] ); // safe
					if( is_array( $unserialized ) )
						return $unserialized;
		        } 
				catch( \Exception $e ) 
				{
					return false;
				}
			}

	        if( is_array( $data ) ) 
			{
				return $data;
			}

	        return false;
        }

		/**
		 * set the variables for math calculation
		 *
		 * @since 2.0.1
		 */
		function wc_fns_get_vars_for_math_fn ( $vars, $group_by, $group, $shipping_class ) {
			
			//$vars['mpc_var'] = 15;
			
			return $vars;
		}

	}
	global $Fish_n_Ships_WAPF_NEW;
	$Fish_n_Ships_WAPF_NEW = new Fish_n_Ships_WAPF_NEW();
}

