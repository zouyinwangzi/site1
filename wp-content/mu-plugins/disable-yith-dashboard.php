<?php
/*
Plugin Name: Disable YITH Dashboard Widgets
Description: 通过过滤器阻止 YITH 在仪表盘注册 widget。
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
add_filter( 'yith_plugin_fw_show_dashboard_widgets', '__return_false' );