<?php
/**
 * Compatibility with WOO Discount Rules: In some checkout refreshes, the price isn't discounted
 *
 * @package Advanced Shipping Rates for WC
 * @since 2.1.2
 */

defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'Fish_n_Ships_WOO_DR' ) ) {
	
	class Fish_n_Ships_WOO_DR {
		
		private $fields  = false;
				
		/**
		 * Constructor.
		 *
		 * @since 2.1.2
		 */
		public function __construct() {
			
			// Let's hook the product price: Low priority!
			add_filter( 'wc_fns_get_product_price', array ( $this, 'filter_product_price' ), 100, 2 );
		}

		/**
		 * Pointers to announce support
		 *
		 * @since 2.1.2
		 *
		 */
		public function filter_product_price( $price, $product )
		{
			global $Fish_n_Ships;
			
			if ( class_exists('\Wdr\App\Router') && class_exists('\Wdr\App\Controllers\ManageDiscount') )
			{
				// In some cases, the array is empty, and discount isn't applied. Here use it as detector:
				$manage_discount = \Wdr\App\Router::$manage_discount;
				if( ! is_array($manage_discount::$calculated_cart_item_discount) || count($manage_discount::$calculated_cart_item_discount) == 0 )
				{
					// We will force initial calculation in this cases
					$price_wcdt = \Wdr\App\Controllers\ManageDiscount::calculateInitialAndDiscountedPrice( $product['data'], 1 );
					
					// error_log(print_r($price_wcdt, true));
					
					if( is_array($price_wcdt) && isset($price_wcdt['discounted_price']) ) {
						$price = $price_wcdt['discounted_price'];
						// As WOO DR haven't multicurrency compatibility
						$price = $Fish_n_Ships->currency_abstraction('cart-currency', $price);
					}
				}
			}
			return $price;
		}

	}
	global $Fish_n_Ships_WOO_DR;
	$Fish_n_Ships_WOO_DR = new Fish_n_Ships_WOO_DR();
}

