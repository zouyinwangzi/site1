<?php
/**
 * This is a sample for the Advanced Shipping Rates for WC wizard
 *
 * @package Advanced Shipping Rates for WC
 */
 
$sample = array(

		'tab'       => 'snippets',
		'section'   => 'Always',
		'name'      => 'Shipping rate of 10%, with relative minimum and maximum',
		'case'      => 'It will add a rule that always add 10% of products price, with ' . $this->loc_price(20, true) . ' minimum and ' . $this->loc_price(60, true) . ' maximum. Other conditional rules can add other costs, without min/max limits.',
		'only_pro'  => true,
		
		'config'    => array(
				
						'priority'  => 10,

						'rules'     => array(
											array(
												'type' => 'normal',
												'sel' => array(
															// Selector 1
															array(
																'method'   => 'always',
																'values'   => array()
															),
															// Operators for all selectors
															'operators' => $this->get_operator_and(),
												),
												'cost' => array(
															array(
																'method'  => 'percent',
																'values'  => array(
																				'cost' => 10
																			 )
															)
												),
												
												'actions' => array(
															array(
																'method'  => 'min_max',
																'values'  => array(
																				'min' => $this->loc_price(20),
																				'max' => $this->loc_price(60),
																			 )
															)
												),
											)
						),
		),
);
