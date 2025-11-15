<?php
// 彻底禁用所有自动更新和检查
// define( 'WP_AUTO_UPDATE_CORE', false ); // 禁用核心自动更新
add_filter( 'automatic_updater_disabled', '__return_true' ); // 完全禁用自动更新
add_filter( 'auto_update_core', '__return_false' ); // 禁用核心更新
add_filter( 'auto_update_plugin', '__return_false' ); // 禁用插件自动更新
add_filter( 'auto_update_theme', '__return_false' ); // 禁用主题自动更新

// 移除更新检查的核心计划任务
function remove_core_updates(){
    global $wp_version;
    return (object) array(
        'last_checked' => time(),
        'version_checked' => $wp_version,
        'updates' => array()
    );
}
add_filter( 'pre_site_transient_update_core', 'remove_core_updates' );
add_filter( 'pre_site_transient_update_plugins', 'remove_core_updates' );
add_filter( 'pre_site_transient_update_themes', 'remove_core_updates' );






/**
 * 禁用 Yoast SEO 的远程请求和许可证检查
 */
add_filter( 'pre_http_request', 'disable_yoast_remote_requests', 10, 3 );

function disable_yoast_remote_requests( $preempt, $args, $url ) {
    // 检查是否是 Yoast 的 API 请求
    if ( strpos( $url, 'my.yoast.com/api/sites/current' ) !== false ) {
        // 返回一个模拟的成功响应来阻止实际请求
        return array(
            'response' => array( 'code' => 200 ),
            'body'     => json_encode( array( 'success' => true ) )
        );
    }
    
    // 拦截其他 Yoast API 请求（可选）
    if ( strpos( $url, 'my.yoast.com/api/' ) !== false ) {
        return array(
            'response' => array( 'code' => 200 ),
            'body'     => json_encode( array( 'success' => true ) )
        );
    }
    
    return $preempt;
}

/**
 * 全面禁用 Yoast SEO 的远程功能
 */
// add_filter( 'wpseo_enable_tracking', '__return_false' ); // 禁用数据追踪
// add_filter( 'wpseo_should_index_links', '__return_false' ); // 禁用链接索引

// // 禁用许可证检查
// add_filter( 'yoast-license-valid', '__return_true' );
// add_filter( 'yoast-license-active', '__return_true' );

// // 禁用 Ryte 服务（网站健康检查）
// add_filter( 'wpseo_ryte_indexable', '__return_false' );

// // 禁用 XML 站点地图的 ping 功能
// add_filter( 'wpseo_allow_xml_sitemap_ping', '__return_false' );

// // 阻止 Yoast 检查远程状态
// add_action( 'init', 'disable_yoast_remote_checks' );
// function disable_yoast_remote_checks() {
//     if ( class_exists( 'WPSEO_Slug_Change_Watcher' ) ) {
//         remove_action( 'admin_enqueue_scripts', array( 'WPSEO_Slug_Change_Watcher', 'enqueue_assets' ) );
//     }
// }



/**
 * 完全禁用购物车更新滚动 + 3秒自动消失
 */
add_action( 'wp_head', 'disable_cart_scroll_completely' );
function disable_cart_scroll_completely() {
    if ( is_cart() ) {
        ?>
        <style>
        /* 禁用页面滚动行为 */
        html {
            scroll-behavior: auto !important;
        }
        
        /* 固定消息样式 */
        .woocommerce-message {
            position: fixed !important;
            top: 100px !important;
            right: 20px !important;
            max-width: 300px !important;
            z-index: 9999 !important;
            animation: slideInRight 0.3s ease-out, slideOutRight 0.3s ease-in 3s forwards !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            border-radius: 8px !important;
            margin: 0 !important;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
                visibility: hidden;
            }
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .woocommerce-message {
                top: 46px !important;
                right: 10px !important;
                left: 10px !important;
                max-width: none !important;
            }
        }
        </style>
        <?php
    }
}

/**
 * 使用 JavaScript 完全阻止滚动行为
 */
add_action( 'wp_footer', 'disable_cart_scroll_js' );
function disable_cart_scroll_js() {
    if ( is_cart() ) {
        ?>
        <script type="text/javascript">
        jQuery( function( $ ) {
            // 方法1: 移除 WooCommerce 的滚动事件监听
            $( document.body ).off( 'updated_cart_totals' );
            
            // 方法2: 重写滚动行为
            var originalUpdateCart = null;
            if ( $.fn.updateCart ) {
                originalUpdateCart = $.fn.updateCart;
                $.fn.updateCart = function() {
                    // 调用原始函数但阻止滚动
                    var result = originalUpdateCart.apply( this, arguments );
                    // 移除滚动事件
                    $( document.body ).off( 'updated_cart_totals' );
                    return result;
                };
            }
            
            // 方法3: 拦截 fragment 加载
            $( document.body ).on( 'updated_cart_totals', function( e, data ) {
                // 阻止任何滚动行为
                e.stopPropagation();
                if ( data && typeof data === 'object' ) {
                    data.scrollTo = false;
                }
                return false;
            });
            
            // 方法4: 阻止默认的 Ajax 完成处理
            $( document ).ajaxComplete( function( event, xhr, settings ) {
                if ( settings.url && settings.url.indexOf( 'wc-ajax=update_cart' ) > -1 ) {
                    // 确保页面不会滚动
                    $( 'html, body' ).stop( true );
                }
            });
        });
        </script>
        <?php
    }
}




// 禁用 YITH 插件仪表盘小工具
// add_action( 'plugins_loaded', function() {
//     // 移除 YITH 注册到仪表盘的动作
//     remove_action( 'wp_dashboard_setup', 'YITH_Dashboard::dashboard_widget_setup' );
//     remove_action( 'admin_enqueue_scripts', 'YITH_Dashboard::enqueue_scripts', 20 );
// }, 20 );
if(class_exists('\Wpmet\Libs\Stories')) {
    add_action( 'wp_dashboard_setup', function() {
        $Wpmet_instance = \Wpmet\Libs\Stories::instance();
        remove_action( 'wp_dashboard_setup', array( $Wpmet_instance, 'show_story_widget' ), 111 );
    }, 1 );

}
// $Wpmet_instance = \Wpmet\Libs\Stories::instance();
// remove_action( 'wp_dashboard_setup', array( $Wpmet_instance, 'show_story_widget' ), 111 );
