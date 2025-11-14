<?php
$zone_name = 'Tanzania';


$flat_rate_setting = array(
	'title' => 'Flat rate',
	'tax_status' => 'taxable',
	'cost' => '0',
	'class_costs' => '',
	'type' => 'class',
	'no_class_cost' => '100*[qty]*1.3',
	'class_cost_38' => '300*0.22848*[qty]*1.3', //38	包包--38公斤的包裹

	'class_cost_39' => '350*0.162*[qty]*1.3', //39	品牌鞋子30对-28-30公斤的包裹
	'class_cost_37' => '350*0.09375*[qty]*1.3', //37	鞋子-25公斤的包裹

	'class_cost_40' => '400*0.09975*[qty]*1.3', //40	衣服-37公斤的包裹
	'class_cost_41' => '400*0.09975*[qty]*1.3', //41	衣服-38公斤的包裹
	'class_cost_42' => '400*0.09975*[qty]*1.3', //42	衣服-39公斤的包裹
	'class_cost_43' => '400*0.084175*[qty]*1.3', //43	衣服-40公斤的包裹
	'class_cost_34' => '400*0.091875*[qty]*1.3', //34	衣服-45公斤的包裹
	'class_cost_44' => '400*0.144375*[qty]*1.3', //44	衣服-50公斤的包裹
	'class_cost_45' => '400*0.193875*[qty]*1.3', //45	衣服-70公斤的包裹
	'class_cost_46' => '400*0.20625*[qty]*1.3', //46	衣服-75公斤的包裹
	'class_cost_47' => '400*0.226875*[qty]*1.3', //47	衣服-80公斤的包裹
	'class_cost_35' => '400*0.185625*[qty]*1.3', //35	衣服-90公斤的包裹
	'class_cost_48' => '400*0.2268*[qty]*1.3', //48	衣服-100公斤的包裹
);

echo "\n" . serialize($flat_rate_setting) . "\n\n";





require_once dirname(__DIR__) . '/../../../../wp-load.php';
global $wpdb;


$zone_id = $wpdb->get_var("SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_code = 'TZ' AND location_type = 'country' LIMIT 1;");

if ($zone_id) { //找到国家/地区代码
	//查找该区域下的配送方式ID
	$method_id = $wpdb->get_var("SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = {$zone_id} AND method_id = 'flat_rate' LIMIT 1;");
	if ($method_id) {
		//更新配送方式的序列化数据
		$serialized_data = serialize($a);
		$option_name = sprintf('woocommerce_flat_rate_%d_settings',$method_id);
		$flat_rate_value = get_option($option_name);

		if($flat_rate_value){
			update_option($option_name, $a);
			
		}
	}
}


