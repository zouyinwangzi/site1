<?php
if ($_REQUEST['desc']) {
    $desc = $_REQUEST['desc'];
    $desc = strip_tags($desc);
    // $desc = str_replace(array("\r\n", "\r", "\n"),"<br/>",$desc);
    $desc = nl2br($desc);
    // echo $desc;

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>字符串过滤</title>
</head>

<body>
    <h1>字符串过滤 - 去除html代码</h1>
    <form action="">
        <textarea name="desc" id="" cols="80" rows="10"></textarea><br />
        <button>过滤描述</button>
    </form>

    <div style="margin-top:20px;">
        <textarea name="" id="" cols="80" rows="10"><?php
                                                    if (isset($desc)) {
                                                        echo $desc;
                                                    }
                                                    ?></textarea>

    </div>
</body>

</html>


<div class="pay_product_params_wrap">
    <div class="pay_product_param"><span class="pay_product_param_title">Brand</span><span class="pay_product_param_value"><span class="colon">：</span>Customized for you</span></div>
    <div class="pay_product_param"><span class="pay_product_param_title">Advantage</span><span class="pay_product_param_value"><span class="colon">：</span>Brand quality, men's shorts</span></div>
    <div class="pay_product_param"><span class="pay_product_param_title">Raw Material:</span><span class="pay_product_param_value"><span class="colon">：</span>First-tier cities in China</span></div>
    <div class="pay_product_param"><span class="pay_product_param_title">Packaging:</span><span class="pay_product_param_value"><span class="colon">：</span>According to brand packaging standards</span></div>
</div>