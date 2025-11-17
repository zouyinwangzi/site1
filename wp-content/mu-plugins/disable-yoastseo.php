<?php
/*
Plugin Name: Disable Yoast SEO Notices
Description: 只移除 Yoast SEO API中站点的信息
Version: 1.0
Author: GitHub Copilot
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'pre_http_request', function( $preempt, $args, $url ) {
    if ( empty( $url ) ) {
        return $preempt;
    }

    // 仅针对 elementor info API 拦截（精确匹配）
    if ( false !== strpos( $url, 'my.yoast.com/api/sites/current' ) ) {
        return $preempt;
    }

    // 防止递归调用
    static $in_filter = false;
    if ( $in_filter ) {
        return $preempt;
    }
    $in_filter = true;

    // 执行真实请求（使用传入 args 以保持行为一致）
    $response = wp_remote_get( $url, $args );

    $in_filter = false;

    if ( is_wp_error( $response ) ) {
        // 不处理，保留原始行为（让调用端处理错误）
        return $preempt;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( is_array( $data ) ) {
        // 移除升级通知与管理通知
        if ( isset( $data['upgrade_notice'] ) ) {
            unset( $data['upgrade_notice'] );
        }
        if ( isset( $data['admin_notice'] ) ) {
            unset( $data['admin_notice'] );
        }
    } else {
        // 无法解析为 JSON，就直接返回原始响应
        return array(
            'response' => array( 'code' => wp_remote_retrieve_response_code( $response ) ),
            'body'     => $body,
            'headers'  => wp_remote_retrieve_headers( $response ),
        );
    }

    return array(
        'response' => array( 'code' => wp_remote_retrieve_response_code( $response ) ),
        'body'     => wp_json_encode( $data ),
        'headers'  => wp_remote_retrieve_headers( $response ),
    );
}, 10, 3 );