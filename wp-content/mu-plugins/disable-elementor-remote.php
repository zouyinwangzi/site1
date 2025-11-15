<?php
return;
/*
Plugin Name: Disable Elementor Remote Requests
Description: 防止 Elementor 向 my.elementor.com 发起远程请求，减少不必要的 HTTP 调用。
Version: 1.0
Author: GitHub Copilot
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1) 短路对 elementor 远程 API 的 HTTP 请求，返回空数组响应体。
 *    这样 get_info_data() 会认为返回空并使用缓存，从而不发起真实请求。
 */
add_filter( 'pre_http_request', function( $preempt, $args, $url ) {
    if ( empty( $url ) ) {
        return $preempt;
    }

    // 拦截 elementor 的远端域名（可根据需要扩展）
    if ( false !== strpos( $url, 'my.elementor.com' ) || false !== strpos( $url, 'elementor.com/api' ) ) {
        return array(
            'response' => array( 'code' => 200 ),
            // 返回空 JSON 数组或对象，get_info_data() 会把它解析为空并写入 transient
            'body' => json_encode( array() ),
        );
    }

    return $preempt;
}, 10, 3 );

/**
 * 2) 额外：设置永久/长期的 transient，避免插件在管理页面首次载入时发起请求。
 */
add_action( 'init', function() {
    if ( defined( 'ELEMENTOR_VERSION' ) ) {
        $key = 'elementor_remote_info_api_data_' . ELEMENTOR_VERSION;
        if ( false === get_transient( $key ) ) {
            // 设置长期缓存（1 年），可以按需修改
            set_transient( $key, array(), YEAR_IN_SECONDS );
        }
    }

    // 设置 library/ feed 的 option 为空，防止后续读取时触发额外逻辑
    if ( false === get_option( 'elementor_remote_info_library', false ) ) {
        update_option( 'elementor_remote_info_library', array(), 'no' );
    }
    if ( false === get_option( 'elementor_remote_info_feed_data', false ) ) {
        update_option( 'elementor_remote_info_feed_data', array(), 'no' );
    }
}, 1 );