<?php
/**
 * This is a sample for the Advanced Shipping Rates for WC wizard
 *
 * @package Advanced Shipping Rates for WC
 */
 
$sample = array(

		'tab'       => 'snippets',
		'section'   => 'Date & time',
		'priority'  => 10,
		'name'      => 'Disable shipping method on Weekend',
		'case'      => 'It will disable the method from 17:00 of friday to 00:00 of monday.',
		'only_pro'  => true,
		
		'config'    => array(
				
						'priority'  => 1,

						'rules'     => array(

											// Friday
											array(
											
												'type' => 'normal',
												
												'sel' => array(
													// Selector 1
													array(
														'method'   => 'date-weekday',
														'values'   => array(
																		'date_weekday' => array(5),
																		'group_by' => array(), // Maybe more than one option allowed?!
																	  )
													),
													array(
														'method'   => 'date-time',
														'values'   => array(
																		'min_comp' => 'ge',
																		'min'      => '17:00',
																		'max_comp' => 'le',
																		'max'      => '23:59',
																		'group_by' => array(), // all together, required
																	  )
													),
													'operators' => $this->get_operator_and(),
												),
												
												'cost' =>  $this->get_cost_zero(),
												
												'actions' => array(
													// Selector 1
													array(
														'method'   => 'abort',
														'values'   => array()
													),
												),
											),
											
											// Saturday & Sunday
											array(
											
												'type' => 'normal',
												
												'sel' => array(
													// Selector 1
													array(
														'method'   => 'date-weekday',
														'values'   => array(
																		'date_weekday' => array(0,6),
																		'group_by' => array(), // Maybe more than one option allowed?!
																	  )
													),
													'operators' => $this->get_operator_and(),
												),
												
												'cost' =>  $this->get_cost_zero(),
												
												'actions' => array(
													// Selector 1
													array(
														'method'   => 'abort',
														'values'   => array()
													),
												),
											),

						),
		),

);
