<?php
$tempFile = "temp_media_data.json";
if (file_exists($tempFile)) {
    unlink($tempFile);
}
header("Location: /tools/explain_media_xml.php");
exit;
?>