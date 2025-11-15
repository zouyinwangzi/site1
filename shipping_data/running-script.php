<?php 


require_once dirname(__DIR__) . '/wp-load.php';
global $wpdb;


$zone_id = $wpdb->get_var("SELECT zone_id FROM {$wpdb->prefix}woocommerce_shipping_zone_locations WHERE location_code = '{$location_code}' AND location_type = 'country' LIMIT 1;");

if ($zone_id) { //找到国家/地区代码
	//查找该区域下的配送方式ID
	$method_id = $wpdb->get_var("SELECT instance_id FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = {$zone_id} AND method_id = 'flat_rate' LIMIT 1;");
	if ($method_id) {
		//更新配送方式的序列化数据
		// $serialized_data = serialize($a);
		$option_name = sprintf('woocommerce_flat_rate_%d_settings',$method_id);
		$flat_rate_old = get_option($option_name);

		// var_dump($option_name);
		// var_dump($flat_rate_setting);
		// var_dump($flat_rate_old);


		if($flat_rate_old){
			update_option($option_name, $flat_rate_setting);
			echo "\nMethod Id:{$method_id}, Update OK.\n\n";
		}else{
			echo "Can Not Find {$location_code} Flat Rate Setting.";
		}
	}
}