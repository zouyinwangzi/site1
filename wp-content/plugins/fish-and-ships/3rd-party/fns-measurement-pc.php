<?php
/**
 * Add on for Skyverge Measurement Price Calculator (MPC)
 *
 * @package Advanced Shipping Rates for WC
 * @since 1.3
 * @version 1.4.13
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_MPC' ) ) {
	
	class Fish_n_Ships_MPC {
				
		/**
		 * Constructor.
		 *
		 * @since 1.3.0
		 * @version 1.4.1
		 */
		public function __construct() {
			
			add_filter('wc_fns_get_selection_methods', array ( $this, 'wc_fns_get_selection_methods_fn' ) , 20, 1);
		}

		/**
		 * Filter to get all selection methods
		 *
		 * @since 1.3.0
		 * @version 1.4.4
		 *
		 * @param $methods (array) maybe incomming  a pair method-id / method-name array
		 *
		 * @return $methods (array) a pair method-id / method-name array
		 *
		 */
		function wc_fns_get_selection_methods_fn($methods = array()) {

			$scope_all     = array ('normal', 'extra');

			$methods['mpc-length']        = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Length', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-width']         = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Width', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-height']        = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Height', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-weight']        = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Weight', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-volume']        = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Volume', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-area']          = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Area', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-perimeter']     = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Perimeter', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-area-surface']  = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Area surface', 'shorted, select-by conditional', 'fish-and-ships'));
			$methods['mpc-roomwalls']     = array('onlypro' => true, 'group' => 'Measurement Price Calc.', 'scope' => $scope_all,  'label' => 'MPC - ' . _x('Roomwalls', 'shorted, select-by conditional', 'fish-and-ships'));
			
			return $methods;
		}

	}
	global $Fish_n_Ships_MPC;
	$Fish_n_Ships_MPC = new Fish_n_Ships_MPC();
}

