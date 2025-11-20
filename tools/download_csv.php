<?php
$tempFile = "temp_media_data.json";
if (file_exists($tempFile)) {
    $data = json_decode(file_get_contents($tempFile), true);
    if (isset($data["csv_content"])) {
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=wordpress_media_" . date("Ymd_His") . ".csv");
        echo $data["csv_content"];
        exit;
    }
}
echo "没有可下载的数据";
?>