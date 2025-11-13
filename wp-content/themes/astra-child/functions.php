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



// 禁用Yoast SEO的API请求
add_filter('yoast_seo_development_mode', '__return_true');

// 或者更具体地阻止站点信息请求
add_filter('pre_http_request', 'block_yoast_api_requests', 10, 3);
function block_yoast_api_requests($preempt, $args, $url) {
    // 阻止对my.yoast.com的API请求
    if (strpos($url, 'my.yoast.com/api/sites/current') !== false) {
        return array(
            'response' => array('code' => 403, 'message' => 'Forbidden'),
            'body' => '',
        );
    }
    return $preempt;
}





add_action('woocommerce_share',function(){
    // echo do_shortcode('[social_warfare]');
    echo do_shortcode('[shareaholic]');
    echo '<a href="#" class="show-query-form button">Query Form</a>';
});




// ...existing code...
// add_action( 'wp_footer', 'swf_add_line_share_button' );
function swf_add_line_share_button() {
    ?>
    <script>
    (function(){
        var selectors = ['.swp_social_panel'];
        var pageUrl = window.location.href;
        var href = 'https://line.me/R/msg/text?' + encodeURIComponent(pageUrl);

        selectors.forEach(function(sel){
            document.querySelectorAll(sel).forEach(function(container){
                if (!container || container.querySelector('.swp_line')) return; // 防止重复添加

                // 找到“More”按钮所在的容器（我们要在它之前插入）
                var moreDiv = container.querySelector('.swp_share_button.swp_more');

                // 创建与其它按钮相同的包裹结构
                var wrapper = document.createElement('div');
                wrapper.className = 'nc_tweetContainer swp_share_button swp_line';
                wrapper.setAttribute('data-network', 'line');

                var a = document.createElement('a');
                a.className = 'nc_tweet swp_share_link';
                a.rel = 'nofollow noreferrer noopener';
                a.target = '_blank';
                a.href = href;
                a.setAttribute('data-link', href);

                // 使用与其他按钮相似的内部结构（可替换为 SVG 图标）
                a.innerHTML = '<span class="swp_count swp_hide"><span class="iconFiller"><span class="spaceManWilly"><svg t="1763025271046" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1841" width="22" height="22"><path d="M938.666667 456.106667c0 76.245333-29.312 145.066667-90.581334 212.224-89.6 102.997333-289.621333 228.821333-335.530666 247.978666-45.824 19.242667-38.869333-12.245333-37.290667-22.912l5.845333-36.266666c1.450667-11.178667 2.901333-27.733333-1.365333-38.4-4.778667-11.818667-23.722667-18.090667-37.589333-20.992C237.141333 770.517333 85.333333 627.2 85.333333 456.106667c0-190.933333 191.445333-346.368 426.666667-346.368 235.178667 0 426.666667 155.434667 426.666667 346.368z m-153.6 154.666666c47.488-52.053333 68.266667-100.736 68.266666-154.666666 0-139.434667-149.76-261.034667-341.333333-261.034667s-341.333333 121.6-341.333333 261.034667c0 123.946667 116.394667 234.965333 282.709333 257.024l6.272 1.109333c45.994667 9.642667 80.384 26.197333 99.370667 72.874667l1.536 4.096c77.056-50.176 178.090667-127.146667 224.469333-180.437334z m-11.178667-170.666666a22.442667 22.442667 0 0 1 0 44.8h-62.421333v40.021333h62.378666a22.4 22.4 0 1 1 0 44.757333H689.066667a22.442667 22.442667 0 0 1-22.272-22.357333V377.685333c0-12.245333 10.026667-22.4 22.4-22.4h84.821333a22.4 22.4 0 0 1-0.128 44.8h-62.378667v40.021334h62.378667z m-137.088 107.221333a22.357333 22.357333 0 0 1-22.442667 22.272 21.973333 21.973333 0 0 1-18.133333-8.874667l-86.869333-117.930666v104.533333a22.4 22.4 0 0 1-44.672 0V377.685333a22.272 22.272 0 0 1 22.186666-22.314666c6.912 0 13.312 3.669333 17.578667 9.002666l87.552 118.4V377.685333c0-12.245333 10.026667-22.4 22.4-22.4 12.245333 0 22.4 10.154667 22.4 22.4v169.642667z m-204.117333 0a22.485333 22.485333 0 0 1-22.442667 22.357333 22.442667 22.442667 0 0 1-22.314667-22.357333V377.685333c0-12.245333 10.069333-22.4 22.4-22.4 12.330667 0 22.357333 10.154667 22.357334 22.4v169.642667z m-87.68 22.357333H260.138667a22.528 22.528 0 0 1-22.4-22.357333V377.685333a22.485333 22.485333 0 0 1 44.8 0v147.2h62.464a22.4 22.4 0 0 1 0 44.8z" p-id="1842" fill="#ffffff"></path></svg><span class="swp_share">LINE</span></span></span></span>';

                wrapper.appendChild(a);

                if (moreDiv) {
                    container.insertBefore(wrapper, moreDiv);
                } else {
                    // 若找不到 more 按钮，则追加到末尾
                    container.appendChild(wrapper);
                }
            });
        });
    })();
    </script>

    <style>
    .swp_share_button.swp_line{
        background: #06C755;
    }
    .swp_line .swp_share { color: #fff; }
    .swp_line .sw { /* 可自定义 line 图标颜色或字体图标样式 */ }
    /* 也可以隐藏多余的 swp_count 显示样式，根据需要调整 */
    </style>
    <?php
}
// ...existing code...


// // 为菜单购物车创建自定义模板
// add_filter('wc_get_template', 'override_astra_mini_cart_template', 10, 5);
// function override_astra_mini_cart_template($located, $template_name, $args, $template_path, $default_path) {
//     // 如果是迷你购物车模板且在Elementor上下文中

//     var_dump($template_name);
//     if ($template_name === 'cart/mini-cart.php' && is_elementor_menu_cart_context()) {
//         $custom_template = get_stylesheet_directory() . '/woocommerce/cart/mini-cart-elementor.php';
//         if (file_exists($custom_template)) {
//             return $custom_template;
//         }
//     }
//     return $located;
// }

// function is_elementor_menu_cart_context() {
//     // 通过HTTP referrer或AJAX action判断
//     if (wp_doing_ajax() && isset($_POST['action']) && $_POST['action'] === 'elementor_menu_cart_fragments') {
//         return true;
//     }
    
//     // 检查是否是Elementor的菜单购物车请求
//     if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'elementor') !== false) {
//         return true;
//     }
    
//     return false;
// }