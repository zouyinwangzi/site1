<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra_Child
 */

// 安全检查：防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 排队加载父主题和子主题的样式表。
 */
function twentytwentyfour_child_enqueue_styles() {
    // 加载父主题样式
    wp_enqueue_style( 
        'parent-style', 
        get_template_directory_uri() . '/style.css' 
    );
    
    // 加载子主题样式 (style.css)
    wp_enqueue_style( 
        'child-style', 
        get_stylesheet_directory_uri() . '/style.css',
        array( 'parent-style' ), // 依赖关系：确保子主题样式在父主题之后加载
        wp_get_theme()->get('Version') // 版本号，可用于缓存清除
    );
}
add_action( 'wp_enqueue_scripts', 'twentytwentyfour_child_enqueue_styles' );

// 从这里开始，您可以添加您的自定义PHP代码
// 例如，添加新的功能、修改钩子(hooks)、过滤器(filters)等


require_once get_stylesheet_directory() . '/opt.php';