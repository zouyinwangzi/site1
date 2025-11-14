<?php
/**
 * Add on for StudioWombat Advanced Product Fields for WooCommerce (WAPF)
 * Legacy support for old methods (from version 2.0.1)
 *
 * @package Fish and Ships
 * @since 1.5
 * @version 2.1.1
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_WAPF' ) ) {
	
	class Fish_n_Ships_WAPF {
		
		private $fields  = false;
				
		/**
		 * Constructor.
		 *
		 * @since 1.5
		 */
		public function __construct() {
						
			add_filter('wc_fns_get_selection_methods', array ( $this, 'wc_fns_get_selection_methods_fn' ) , 20, 1);
						
			add_filter('wc_fns_get_html_details_method', array ( $this, 'wc_fns_get_html_details_method_fn' ), 20, 6);

			add_filter( 'wc-fns-groupable-selection-methods', array ($this, 'wc_fns_groupable_selection_methods_fn' ), 20, 1 );

			add_filter('wc_fns_sanitize_selection_fields', array ( $this, 'wc_fns_sanitize_selection_fields_fn' ), 20, 1);

			add_filter('wc_fns_check_matching_selection_method', array ( $this, 'wc_fns_check_matching_selection_method_fn' ) , 20, 5);
			
			add_filter( 'wc_fns_group_external_calculate', array ($this, 'wc_fns_group_external_calculate_fn' ), 10, 4 );

			/*
			add_filter( 'wc_fns_get_vars_for_math', array ($this, 'wc_fns_get_vars_for_math_fn' ), 10, 4 );
			*/
		}
				
		/**
		 * Get fields from WAPF. 
		 * Only we will query group title on backoffice for performance
		 *
		 * @since 1.5
		 *
		 */
		function get_fields( $title = false ) {
			
			// Only the first time
			if ( is_array( $this->fields) )
				return $this->fields;
			
			$this->fields = array();
			
			$field_groups = false;
			
			if ( class_exists('SW_WAPF_PRO\Includes\Classes\Field_Groups') )
			{
				$field_groups  = SW_WAPF_PRO\Includes\Classes\Field_Groups::get_all();
			}
			else if ( class_exists('SW_WAPF\Includes\Classes\Field_Groups') )
			{
				$field_groups  = SW_WAPF\Includes\Classes\Field_Groups::get_all();
			}

			if ( !is_array( $field_groups ) || count ( $field_groups ) == 0 )
				return array(); // Failed to get field groups, or simply there isn't anyone
			
			foreach ( $field_groups as $fieldgroup )
			{
				if ( !$fieldgroup || !isset( $fieldgroup->fields ) )
					continue;
				
				$group_title = $title ? get_post_field( 'post_title', $fieldgroup->id ) : '';
				
				foreach ( $fieldgroup->fields as $field )
				{
					if ( !$field || !isset( $field->type ) )
						continue;
					
					switch ( $field->type ) {
						
						case 'number' :
						
							// Multiple? Let's add two: SUM AND MUL
							if ( isset( $field->clone ) && isset( $field->clone['enabled'] ) && $field->clone['enabled'] == 1 
								 && isset( $field->clone['type'] ) && $field->clone['type'] == 'button' )
							{

								$this->fields[$field->id] = array (
																		'id'          => $field->id,
																		'type'        => 'number',
																		'group_name'  => $group_title,
																		'label'       => $field->label . ' [SUM]',
																		'method'      => 'sum'
																	);
								$this->fields[$field->id . '-mul'] = array (
																		'id'          => $field->id,
																		'type'        => 'number',
																		'group_name'  => $group_title,
																		'label'       => $field->label . ' [MUL]',
																		'method'      => 'mul'
																	);
							} else {
								$this->fields[$field->id] = array (
																		'id'          => $field->id,
																		'type'        => 'number',
																		'group_name'  => $group_title,
																		'label'       => $field->label,
																		'method'      => 'sum' // default
																	);
							}
							break;
							
						case 'calc' :

							$this->fields[$field->id] = array (
																	'id'          => $field->id,
																	'type'        => 'calc',
																	'group_name'  => $group_title,
																	'label'       => $field->label,
																	'method'      => 'sum' // default
																);
							break;
					}
				}
			}
			return $this->fields;
		}

		/**
		 * Filter to get all selection methods
		 *
		 * @since 1.5
		 *
		 * @param $methods (array) maybe incomming  a pair method-id / method-name array
		 *
		 * @return $methods (array) a pair method-id / method-name array
		 *
		 */
		function wc_fns_get_selection_methods_fn($methods = array()) {
			
			$scope_all     = array ('normal', 'extra');
			
			$fields = $this->get_fields( true );
			
			// Guys: if for some reason you need to show the legacy methods although they aren't used, 
			// define this constant in your wp-config.php file
			$legacy_keep_always_visible = defined('WC_FNS_WAPF_LEGACY_VISIBLE');
			
			if ( count( $fields ) == 0 )
			{
				if( $legacy_keep_always_visible ) 
					$methods['wapf-x'] = array('onlypro' => false, 'group' => 'Adv. Product Fields', 'scope' => $scope_all,  'label' => 'WAPF - ' . _x('No global fields', 'shorted, select-by conditional', 'fish-and-ships'));
			}
			else
			{
				foreach ( $fields as $id => $field_data )
				{
					$legacy_method = array( 'onlypro' => false, 'group' => 'Adv. Product Fields (legacy)', 'scope' => $scope_all,  'label' => $field_data[ 'group_name' ] . ' &gt; ' . $field_data[ 'label' ] );

					if( ! $legacy_keep_always_visible ) 
						$legacy_method['legacy'] = 'wapf';
					
					$methods[] = $legacy_method;
				}
			}
			return $methods;
		}
		
		/**
		 * Filter to get the HTML selection fields for one method
		 *
		 * @since 1.5
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

			$fields = $this->get_fields();
			
			foreach ( $fields as $id => $field_data ) {
				
				if ( $method_id != 'wapf-' . $id )
					continue;
				
				switch ( $field_data['type'] ) {
					
					case 'number':
					case 'calc':

						$html .= $Fish_n_Ships->get_min_max_comp_html($rule_nr, $sel_nr, $method_id, '', $values, 'sel', 'selection', 'val_info', 'ge', 'less')
								. $Fish_n_Ships->get_group_by_method_html($rule_nr, $sel_nr, $method_id, $values);
					
						break;
					}
				break;
			}

			return $html;
		}
	
		/**
		 * Filter to set the groupable methods
		 *
		 * @since 1.5
		 *
		 * @param $groupable_methods (array) methods keys
		 */
		function wc_fns_groupable_selection_methods_fn( $groupable_methods ) {
			
			$new_fields = array();

			$fields = $this->get_fields();
			
			foreach ( $fields as $id => $field_data ) {
				
				$new_fields[] = 'wapf-' . $id;
			}
			
			$groupable_methods = array_merge( $groupable_methods, $new_fields );
			
			return $groupable_methods;
		}
		
		/**
		 * Filter to sanitize one selection criterion and his auxiliary fields prior to save in the database
		 *
		 * @since 1.5
		 */
		function wc_fns_sanitize_selection_fields_fn($rule_sel) {
			
			//Prior failed?
			if (!is_array($rule_sel)) return $rule_sel;

			global $Fish_n_Ships;

			$fields = $this->get_fields();
			
			foreach ( $fields as $id => $field_data ) {
				
				if ( $rule_sel['method'] != 'wapf-' . $id )
					continue;

				switch ( $field_data['type'] ) {
					
					case 'number':
					case 'calc':

						//$allowed = array('min', 'max', 'min_comp', 'max_comp' );
						$allowed = array('min', 'max', 'min_comp', 'max_comp', 'group_by' );
					
						// Remove not allowed values
						foreach ($rule_sel['values'] as $field => $val) {
							if (!in_array($field, $allowed)) unset($rule_sel['values'][$field]);
						}

						// Add null for missing values (will be turned to default after)
						foreach ($allowed as $field) {
							if ( ! isset($rule_sel['values'][$field]) )
								$rule_sel['values'][$field] = null;
						}

						$rule_sel['values']['group_by'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['group_by'],
																							array_keys($Fish_n_Ships->get_group_by_options()));

						$rule_sel['values']['min'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['min'], 'positive-decimal');
						$rule_sel['values']['max'] = $Fish_n_Ships->sanitize_number($rule_sel['values']['max'], 'positive-decimal');
						$rule_sel['values']['min_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['min_comp'],
																							array ( 'ge', 'greater' ) );
						$rule_sel['values']['max_comp'] = $Fish_n_Ships->sanitize_allowed($rule_sel['values']['max_comp'],
																								array ( 'less', 'le' ) );
						break 2;
				}
			}
			return $rule_sel;
		}
			
		/**
		 * Filter to check matching elements for selection method
		 *
		 * @since 1.5
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

			$fields = $this->get_fields();
			
			foreach ( $fields as $id => $field_data ) {
				
				if ( $selector['method'] != 'wapf-' . $id )
					continue;

				switch ( $field_data['type'] ) {
					
					case 'number':
					case 'calc':

						// Prepare the selection auxiliary fields

						$min = 0; if (isset($selector['values']['min'])) $min = $selector['values']['min'];
						$max = '*'; if (isset($selector['values']['max'])) $max = $selector['values']['max'];

						if ( trim($min) == '' ) $min = 0;
						
						// MAX field set as 0, will be taken as wildcard
						if ( trim($max) == '' || $max==0 ) $max = '*';
						
						// This default values cover the 1.1.4 prior releases legacy
						$min_comp = 'ge';   if (isset($selector['values']['min_comp'])) $min_comp = $selector['values']['min_comp'];
						$max_comp = 'less'; if (isset($selector['values']['max_comp'])) $max_comp = $selector['values']['max_comp'];

						// Let's iterate in his group_by groups
						foreach ($rule_groups[$group_by] as $group) {
							
							// empty or previously unmatched? bypass for performance
							if ($group->is_empty() || !$group->is_match()) continue;

							$value = $group->get_total($selector['method']);

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
						}
						break 2;
				}
			}
			
			return $rule_groups;
		}

		/**
		 * calculate one total
		 *
		 * @since 1.5
		 */
		function wc_fns_group_external_calculate_fn ( $external, $what, $product, $qty ) {

			global $Fish_n_Ships;

			if ( !is_array( $product ) || !isset( $product[ 'wapf' ] ) )
				return $external;
			
			$fields     = $this->get_fields();
			$found      = false;
			$label_sum  = '';
			
			foreach ( $fields as $field_id => $field_data ) {
				
				if ( $what != 'wapf-' . $field_id )
					continue;
				
				switch ( $field_data['type'] ) {
					
					case 'number':
					case 'calc':

						foreach ( $product[ 'wapf' ] as $product_field )
						{	
							// On multiple fields, will be find one item per each field value, with the same ID:
							if ( $product_field[ 'id' ] == $field_data['id'] )
							{								
								if ( isset( $product_field[ 'raw' ] ) ) 
								{
									if( !isset( $external['value'] ) )
									{
										// First occurrence
										$found = true;
										$label = isset( $product_field[ 'label' ] ) ? $product_field[ 'label' ] : 'unknown';
										$external['value'] = (float) $product_field[ 'raw' ];
										$label_sum = $product_field[ 'raw' ];
									}
									else
									{
										if( $field_data['method'] == 'mul' )
										{
											// Next value, let's MULIPLY it
											$external['value']  = $external['value'] * (float) $product_field[ 'raw' ];
											$label_sum         .= ( $label_sum == '' ) ? '' : ' * ';
											$label_sum         .= $product_field[ 'raw' ];
										}
										else
										{
											// Next value, let's SUM it (default / fallback)
											$external['value']  = $external['value'] + (float) $product_field[ 'raw' ];
											$label_sum         .= ( $label_sum == '' ) ? '' : ' + ';
											$label_sum         .= $product_field[ 'raw' ];
										}
									}
								}
							}
						}

						if( $found )
						{
							$external['value']  = $external['value'] * $qty;
							$external['item_value']  = 'WAPF ' . $label . ': [' . $label_sum . ']';
							break;
						}

						$external['value']       = 0;
						$external['item_value']  = 'WAPF ' . $what . ': not found';
						
						break;
				}
			}	

			return $external;
		}

		/**
		 * set the variables for math calculation
		 *
		 * @since 1.5
		 */
		function wc_fns_get_vars_for_math_fn ( $vars, $group_by, $group, $shipping_class ) {
			
			//$vars['mpc_var'] = 15;
			
			return $vars;
		}

	}
	global $Fish_n_Ships_WAPF;
	$Fish_n_Ships_WAPF = new Fish_n_Ships_WAPF();
}

