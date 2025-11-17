<?php

/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra_Child
 */

// 安全检查：防止直接访问
if (! defined('ABSPATH')) {
    exit;
}

/**
 * 排队加载父主题和子主题的样式表。
 */
function twentytwentyfour_child_enqueue_styles()
{
    // 加载父主题样式
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css'
    );

    // 加载子主题样式 (style.css)
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'), // 依赖关系：确保子主题样式在父主题之后加载
        wp_get_theme()->get('Version') // 版本号，可用于缓存清除
    );
}
add_action('wp_enqueue_scripts', 'twentytwentyfour_child_enqueue_styles');

// 从这里开始，您可以添加您的自定义PHP代码
// 例如，添加新的功能、修改钩子(hooks)、过滤器(filters)等








require_once get_stylesheet_directory() . '/opt.php';








add_action('woocommerce_share', function () {
    // echo do_shortcode('[social_warfare]');
    echo do_shortcode('[shareaholic]');

    //打开弹窗查询表单按钮
    // echo '<a rel="nofollow" class="button" href="#elementor-action%3Aaction%3Dpopup%3Aopen%26settings%3DeyJpZCI6IjU2MyIsInRvZ2dsZSI6ZmFsc2V9">Query Form</a>';
}, 20);




add_action('woocommerce_single_product_summary', function () {
    echo '<div class="pay_product_func_wrap" style="display: flex; align-items: center;"> 
    <a href="https://api.whatsapp.com/send?phone=8619700156758&text=Hello" target="_blank" style="display: flex; align-items: center;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="22" height="22" class="design-iconfont"><path d="M11.06,8.44h0a3,3,0,0,1,0,4.24L8.94,14.8a3,3,0,0,1-4.24,0h0a3,3,0,0,1,0-4.24l.7-.71" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path> <path d="M8.94,10.56h0a3,3,0,0,1,0-4.24L11.06,4.2a3,3,0,0,1,4.24,0h0a3,3,0,0,1,0,4.24l-.7.71" fill="none" stroke="currentcolor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
        <span>Consult immediately</span>
    </a>
    </div>';
}, 35, 1);

// 创建弹窗触发按钮短代码
// add_shortcode('popup_trigger', 'popup_trigger_shortcode');
// function popup_trigger_shortcode($atts) {
//     $atts = shortcode_atts(array(
//         'id' => '',
//         'text' => '打开弹窗',
//         'class' => 'popup-trigger-btn'
//     ), $atts);

//     if (empty($atts['id'])) {
//         return '请提供弹窗ID';
//     }

//     return sprintf(
//         '<button class="%s" data-popup-id="%s" onclick="triggerElementorPopup(%s)">%s</button>',
//         esc_attr($atts['class']),
//         esc_attr($atts['id']),
//         esc_attr($atts['id']),
//         esc_html($atts['text'])
//     );
// }




// ...existing code...

// 注册并注入触发 Elementor Popup 的脚本
// add_action('wp_enqueue_scripts', 'astra_child_enqueue_popup_trigger_script');
// function astra_child_enqueue_popup_trigger_script()
// {
//     // 注册空脚本以便注入 inline script（避免单独文件）
//     wp_register_script('astra-child-popup-trigger', '', array('jquery'), wp_get_theme()->get('Version'), true);
//     wp_enqueue_script('astra-child-popup-trigger');

//     $inline = <<<'JS'
// window.triggerElementorPopup = function(id){
//     if ( window.elementorProFrontend && window.elementorProFrontend.modules && window.elementorProFrontend.modules.popup ) {
//         window.elementorProFrontend.modules.popup.showPopup( { id: parseInt(id,10) } );
//     } else {
//         // 如果 elementor JS 还没加载，延迟重试（最多5次）
//         var tries = window._elementor_popup_tries || 0;
//         if ( tries < 5 ) {
//             window._elementor_popup_tries = tries + 1;
//             setTimeout(function(){ window.triggerElementorPopup(id); }, 300);
//         } else {
//             console.warn('Elementor Pro popup JS 未能加载，无法打开弹窗 #' + id );
//         }
//     }
// };

// jQuery(function($){
//     // 绑定通用按钮（class: popup-trigger-btn，data-popup-id 存放弹窗 id）
//     $(document).on('click', '.popup-trigger-btn', function(e){
//         e.preventDefault();
//         var id = $(this).data('popup-id') || $(this).attr('data-popup-id');
//         if ( id ) {
//             window.triggerElementorPopup(id);
//         }
//     });
// });
// JS;

//     wp_add_inline_script('astra-child-popup-trigger', $inline);
// }

// // 短代码 [popup_trigger id="563" text="打开查询表单" class="my-custom-btn"]
// add_shortcode('popup_trigger', 'popup_trigger_shortcode');
// function popup_trigger_shortcode($atts)
// {
//     $atts = shortcode_atts(array(
//         'id'    => '',
//         'text'  => '打开弹窗',
//         'class' => 'popup-trigger-btn'
//     ), $atts);

//     if (empty($atts['id'])) {
//         return '请提供弹窗ID';
//     }

//     return sprintf(
//         '<button class="%s" data-popup-id="%s">%s</button>',
//         esc_attr($atts['class']),
//         esc_attr($atts['id']),
//         esc_html($atts['text'])
//     );
// }

// ...existing code...




// ...existing code...
// add_action( 'wp_footer', 'swf_add_line_share_button' );
function swf_add_line_share_button()
{
?>
    <script>
        (function() {
            var selectors = ['.swp_social_panel'];
            var pageUrl = window.location.href;
            var href = 'https://line.me/R/msg/text?' + encodeURIComponent(pageUrl);

            selectors.forEach(function(sel) {
                document.querySelectorAll(sel).forEach(function(container) {
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
        .swp_share_button.swp_line {
            background: #06C755;
        }

        .swp_line .swp_share {
            color: #fff;
        }

        .swp_line .sw {
            /* 可自定义 line 图标颜色或字体图标样式 */
        }

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





/******************************    询盘表单联动  Start   ****************************/


/*************** 国家地区联动选择 Start *************/
// 洲和国家数据
function get_continents_countries_data()
{
    require_once get_stylesheet_directory() . '/continents_countries_data.php';

    return $continents_countries_data;
}

// 在footer输出数据
function output_continents_countries_data()
{
    if (!is_admin()) {
        $data = get_continents_countries_data();
        echo '<script type="text/javascript">';
        echo 'var continentsCountriesData = ' . json_encode($data) . ';';
        echo "\n";
        $_js = file_get_contents(__DIR__ . '/js/continents-countries.js');
        echo $_js;
        echo '</script>';
    }
}
add_action('wp_footer', 'output_continents_countries_data');







// 调试Elementor表单提交数据
// 确保字符编码正确处理
// add_action('elementor_pro/forms/new_record', function($record, $handler) {
//     $raw_fields = $record->get('fields');
    
//     // 调试：记录原始数据
//     error_log('Raw form fields: ' . print_r($raw_fields, true));
    
//     // 如果有编码问题，尝试转换
//     foreach ($raw_fields as $id => $field) {
//         if ($id === 'country') {
//             // 确保UTF-8编码
//             $country_value = mb_convert_encoding($field['value'], 'UTF-8', 'auto');
//             error_log('Country field after encoding: ' . $country_value);
//         }
//     }
// }, 10, 2);



// 在保存前处理表单数据
// add_filter('elementor_pro/forms/process', function($record, $handler) {
//     $fields = $record->get_field([]);
    
//     // 确保国家字段被正确处理
//     if (isset($fields['country'])) {
//         error_log('Processing country field: ' . $fields['country']);
//     }
    
//     return $record;
// }, 10, 2);

/*************** 国家地区联动选择 End *************/


/*************** 产品加入到询盘 Start *************/
add_action('woocommerce_share', function () {
    // 只在产品页面显示
    if (!is_product()) {
        return;
    }

    $product_id = get_the_ID();
    $product = wc_get_product($product_id);

    if (!$product) {
        return;
    }

    // 获取产品数据
    $product_title = htmlspecialchars($product->get_name(), ENT_QUOTES, 'UTF-8');
    $product_url = esc_url(get_permalink($product_id));

    // 获取产品特色图
    $featured_image_url = '';
    $image_id = $product->get_image_id();
    if ($image_id) {
        $featured_image_url = esc_url(wp_get_attachment_image_url($image_id, 'woocommerce_single'));
    }

    // 备用：使用中等尺寸图片
    if (empty($featured_image_url) && $image_id) {
        $featured_image_url = esc_url(wp_get_attachment_image_url($image_id, 'medium'));
    }

    // 构建数据属性
    $data_attributes = sprintf(
        'data-product-id="%d" data-product-title="%s" data-product-url="%s" data-product-image="%s"',
        $product_id,
        $product_title,
        $product_url,
        $featured_image_url
    );

    // 输出按钮
    printf(
        '<a href="#%s" id="reservation-now-btn" class="reservation-now-btn button" %s>Reservation Now</a>',
        urlencode(sprintf('elementor-action:action=popup:open&settings=%s', base64_encode('{"id":"667","toggle":false}'))),
        $data_attributes
    );
}, 1);




// 在footer输出数据
function output_query_product_js_css()
{
    if (!is_admin() && function_exists('is_product') && is_product()) {
        echo '<script type="text/javascript">';
        $_js = file_get_contents(__DIR__ . '/js/query-product.js');
        echo $_js;
        echo '</script>';
    }

    if (!is_admin() && function_exists('is_product') && is_product()) {
        echo '<style type="text/css">';
        $_css = file_get_contents(__DIR__ . '/css/query-product.css');
        echo $_css;
        echo '</style>';
    }
}
add_action('wp_footer', 'output_query_product_js_css');





//后端处理JSON数据的PHP代码
// add_action('elementor_pro/forms/new_record', function($record, $handler) {
//     $raw_fields = $record->get('fields');
    
//     // 查找包含产品数据的字段
//     foreach ($raw_fields as $field) {
//         if ($field['id'] === 'query_product' && !empty($field['value'])) {
//             $product_data = json_decode($field['value'], true);
            
//             if (json_last_error() === JSON_ERROR_NONE && is_array($product_data)) {
//                 error_log('Inquiry Products (JSON):');
//                 error_log('Total products: ' . count($product_data));
                
//                 foreach ($product_data as $index => $product) {
//                     error_log(sprintf(
//                         "Product %d: %s (ID: %s, URL: %s)",
//                         $index + 1,
//                         $product['title'],
//                         $product['id'],
//                         $product['url']
//                     ));
//                 }
//             } else {
//                 // 如果不是JSON，按原文本处理
//                 error_log('Inquiry Products (Text): ' . $field['value']);
//             }
//             break;
//         }
//     }
// }, 10, 2);



// function send_custom_webhook( $record, $handler ) {

// 	$form_name = $record->get_form_settings( 'form_name' );

//     error_log('Form Name: ' . $form_name);

// 	// Replace MY_FORM_NAME with the name you gave your form
// 	if ( 'Query Form' !== $form_name ) {
// 		return;
// 	}

// 	$raw_fields = $record->get( 'fields' );
// 	$fields = [];
// 	foreach ( $raw_fields as $id => $field ) {
// 		$fields[ $id ] = $field['value'];
// 	}

// 	wp_remote_post(
// 		'https://api.example.com/',
// 		[
// 			'body' => $fields,
// 		]
// 	);
// }
// add_action( 'elementor_pro/forms/new_record', 'send_custom_webhook', 10, 2 );




/*************** 产品加入到询盘 End *************/




/******************************    询盘表单联动  End   ****************************/
