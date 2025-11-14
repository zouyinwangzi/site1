<?php
/**
 * This is a sample for the Advanced Shipping Rates for WC wizard
 *
 * @package Advanced Shipping Rates for WC
 */
 
$sample = array(

		'tab'       => 'snippets',
		'section'   => 'Always',
		'name'      => 'Shipping rate of 10%',
		'case'      => 'It will add a rule that always add 10% of products price. Other conditional rules can add other costs.',
		'only_pro'  => false,
		
		'config'    => array(
				
						'priority'  => 8,

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
												
												'actions' => array(),
											)
						),
		),
);
