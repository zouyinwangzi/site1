<?php
/**
 * This is a sample for the Advanced Shipping Rates for WC wizard
 *
 * @package Advanced Shipping Rates for WC
 */
 
$sample = array(

		'tab'       => 'snippets',
		'section'   => 'Always',
		'priority'  => 10,
		'name'      => 'Fixed rate of ' . $this->loc_price(10, true),
		'case'      => 'It will add a rule that always add ' . $this->loc_price(10, true) . '. Other conditional rules can add other costs.',
		'only_pro'  => false,

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
																'method'  => 'once',
																'values'  => array(
																				'cost' => $this->loc_price(10)
																			 )
															)
												),
												
												'actions' => array(),
											)
						),
		),
);
