<?php
// 启用session
session_start();

// 临时文件存储路径
$tempFile = 'temp_media_data.json';

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process'])) {
    processXMLFile($tempFile);
}

// 从临时文件加载数据
$mediaData = loadMediaData($tempFile);

// 分页设置
$itemsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$viewMode = isset($_GET['view']) ? $_GET['view'] : 'table';

// 如果有数据，显示结果
if (!empty($mediaData)) {
    displayResults($mediaData, $itemsPerPage, $currentPage, $viewMode);
}

function processXMLFile($tempFile)
{
    // 检查文件上传
    if (!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] !== UPLOAD_ERR_OK) {
        echo '<div class="error">文件上传失败，请重试。</div>';
        return;
    }

    $uploadedFile = $_FILES['xml_file'];
    $fileName = $uploadedFile['name'];
    $fileTmpPath = $uploadedFile['tmp_name'];

    // 检查文件类型
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExtension !== 'xml') {
        echo '<div class="error">请上传XML格式的文件。</div>';
        return;
    }

    // 读取并解析XML文件
    $xmlContent = file_get_contents($fileTmpPath);
    $xml = simplexml_load_string($xmlContent);

    if ($xml === false) {
        echo '<div class="error">XML文件解析失败，请检查文件格式。</div>';
        return;
    }

    // 提取媒体数据
    $mediaItems = [];
    foreach ($xml->channel->item as $item) {
        $postType = $item->children('wp', true)->post_type;

        if ($postType == 'attachment') {
            $title = (string)$item->title;
            $url = (string)$item->children('wp', true)->attachment_url;

            if (empty($url)) {
                $url = (string)$item->guid;
            }

            if (!empty($title) && !empty($url)) {
                $mediaItems[] = [
                    'title' => $title,
                    'url' => $url
                ];
            }
        }
    }

    if (empty($mediaItems)) {
        echo '<div class="error">在XML文件中未找到媒体数据。</div>';
        return;
    }

    // 保存到临时文件
    $dataToSave = [
        'media_items' => $mediaItems,
        'csv_content' => generateCSVContent($mediaItems),
        'created_at' => date('Y-m-d H:i:s')
    ];

    file_put_contents($tempFile, json_encode($dataToSave));

    // 设置成功消息
    $_SESSION['success_message'] = '成功提取 ' . count($mediaItems) . ' 个媒体文件！';

    // 重定向到当前页面，避免重复提交
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

function loadMediaData($tempFile)
{
    if (file_exists($tempFile)) {
        $data = json_decode(file_get_contents($tempFile), true);
        return $data['media_items'] ?? [];
    }
    return [];
}

function generateCSVContent($mediaItems)
{
    $csvContent = "文件名,文件链接\n";
    foreach ($mediaItems as $media) {
        $csvContent .= '"' . str_replace('"', '""', $media['title']) . '","' . $media['url'] . "\"\n";
    }
    return $csvContent;
}

function displayResults($mediaItems, $itemsPerPage, $currentPage, $viewMode)
{
    $totalItems = count($mediaItems);
    $totalPages = ceil($totalItems / $itemsPerPage);

    // 确保当前页在有效范围内
    if ($currentPage < 1) $currentPage = 1;
    if ($currentPage > $totalPages) $currentPage = $totalPages;

    // 计算分页数据
    $startIndex = ($currentPage - 1) * $itemsPerPage;
    $endIndex = min($startIndex + $itemsPerPage, $totalItems);
    $currentPageItems = array_slice($mediaItems, $startIndex, $itemsPerPage);

    // 显示成功消息
    if (isset($_SESSION['success_message'])) {
        echo '<div class="success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    } else {
        echo '<div class="success">已加载 ' . $totalItems . ' 个媒体文件</div>';
    }

    echo '<div class="stats">';
    echo '<strong>统计信息：</strong> 共找到 ' . $totalItems . ' 个媒体文件';
    echo ' | 生成时间：' . date('Y-m-d H:i:s');
    echo '<br><a href="download_csv.php" class="download-btn" target="_blank">下载CSV文件</a>';
    echo ' | <a href="clear_data.php" class="download-btn" style="background: #dc3545;">清除数据</a>';
    echo '</div>';

    // 显示视图选项和分页控件
    displayViewControls($totalItems, $totalPages, $currentPage, $itemsPerPage, $viewMode);

    // 显示分页控件
    displayPagination($totalPages, $currentPage, $itemsPerPage, $viewMode);


    // 根据视图模式显示内容
    if ($viewMode === 'thumbnail') {
        displayThumbnailView($currentPageItems);
    } else {
        displayTableView($currentPageItems);
    }

    // 显示分页控件
    displayPagination($totalPages, $currentPage, $itemsPerPage, $viewMode);
}

// 其他函数保持不变（displayViewControls, displayTableView, displayThumbnailView, displayMediaPreview, displayPagination, buildPageUrl）
?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress媒体提取工具</title>
    <style>
        /* 样式保持不变，与之前相同 */
        body {
            font-family: Arial, sans-serif;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .upload-form {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
        }

        .upload-form input[type="file"] {
            margin: 10px 0;
        }

        .upload-form button {
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .upload-form button:hover {
            background: #005a87;
        }

        .results {
            margin-top: 30px;
        }

        .media-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .media-table th,
        .media-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .media-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .media-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .preview-img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.2s;
        }

        .preview-img:hover {
            transform: scale(1.5);
            z-index: 100;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .download-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        .download-btn:hover {
            background: #218838;
        }

        .copy-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
        }

        .copy-btn:hover {
            background: #5a6268;
        }

        .copy-btn.copied {
            background: #28a745;
        }

        .stats {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .success {
            color: #155724;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .pagination {
            margin: 20px 0;
            text-align: center;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #007cba;
        }

        .pagination a:hover {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }

        .pagination .current {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }

        .pagination .disabled {
            color: #6c757d;
            cursor: not-allowed;
        }

        .page-info {
            text-align: center;
            margin: 10px 0;
            color: #666;
        }

        .items-per-page {
            margin: 10px 0;
            text-align: right;
        }

        .items-per-page select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .view-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .thumbnail-view {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .thumbnail-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: white;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }

        .thumbnail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .thumbnail-img {
            max-width: 100%;
            max-height: 150px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .thumbnail-title {
            font-weight: bold;
            margin-bottom: 5px;
            word-break: break-word;
        }

        .thumbnail-url {
            font-size: 12px;
            color: #666;
            word-break: break-all;
            margin-bottom: 10px;
        }

        .view-toggle {
            display: flex;
            gap: 10px;
        }

        .view-toggle button {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .view-toggle button.active {
            background: #007cba;
            color: white;
            border-color: #007cba;
        }

        .copy-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: opacity 0.3s;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>WordPress媒体提取工具</h1>

        <div class="upload-form">
            <form method="post" enctype="multipart/form-data">
                <h3>上传WordPress导出的XML文件</h3>
                <input type="file" name="xml_file" accept=".xml" required>
                <br>
                <button type="submit" name="process">处理XML文件</button>
            </form>
        </div>

        <?php
        // 显示视图控件函数
        function displayViewControls($totalItems, $totalPages, $currentPage, $itemsPerPage, $viewMode)
        {
            echo '<div class="view-options">';
            echo '<div class="items-per-page">';
            echo '每页显示：';
            echo '<select onchange="updatePerPage(this.value)">';
            $options = [100, 200, 500, 1000];
            foreach ($options as $option) {
                $selected = $itemsPerPage == $option ? 'selected' : '';
                echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
            }
            echo '</select>';
            echo '</div>';

            echo '<div class="view-toggle">';
            echo '<button onclick="changeViewMode(\'table\')" class="' . ($viewMode === 'table' ? 'active' : '') . '">表格视图</button>';
            echo '<button onclick="changeViewMode(\'thumbnail\')" class="' . ($viewMode === 'thumbnail' ? 'active' : '') . '">缩略图视图</button>';
            echo '</div>';
            echo '</div>';

            echo '<div class="page-info">';
            echo '显示 ' . (($currentPage - 1) * $itemsPerPage + 1) . ' - ' . min($currentPage * $itemsPerPage, $totalItems) . ' 条，共 ' . $totalItems . ' 条记录';
            echo '</div>';
        }

        function displayTableView($items)
        {
            echo '<table class="media-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th width="100">预览</th>';
            echo '<th>文件名</th>';
            echo '<th>文件链接</th>';
            echo '<th width="100">操作</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($items as $index => $media) {
                echo '<tr>';
                echo '<td>';
                displayMediaPreview($media);
                echo '</td>';
                echo '<td><strong>' . htmlspecialchars($media['title']) . '</strong></td>';
                echo '<td><a href="' . htmlspecialchars($media['url']) . '" target="_blank" title="点击打开链接">' . htmlspecialchars($media['url']) . '</a></td>';
                echo '<td><button class="copy-btn" onclick="copyUrlWrapper(\'' . htmlspecialchars($media['url']) . '\', this)">复制链接</button></td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }

        function displayThumbnailView($items)
        {
            echo '<div class="thumbnail-view">';
            foreach ($items as $media) {
                echo '<div class="thumbnail-item">';
                displayMediaPreview($media, 'thumbnail-img');
                echo '<div class="thumbnail-title">' . htmlspecialchars($media['title']) . '</div>';
                echo '<div class="thumbnail-url">' . htmlspecialchars($media['url']) . '</div>';
                echo '<button class="copy-btn" onclick="copyUrlWrapper(\'' . htmlspecialchars($media['url']) . '\', this)">复制链接</button>';
                echo '</div>';
            }
            echo '</div>';
        }

        function displayMediaPreview($media, $imgClass = 'preview-img')
        {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileExtension = strtolower(pathinfo($media['url'], PATHINFO_EXTENSION));

            if (in_array($fileExtension, $imageExtensions)) {
                echo '<img src="' . htmlspecialchars($media['url']) . '" alt="' . htmlspecialchars($media['title']) . '" class="' . $imgClass . '" onerror="handleImageError(this)">';
            } else {
                echo '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px; text-align: center;">';
                echo strtoupper($fileExtension);
                echo '</div>';
            }
        }

        function displayPagination($totalPages, $currentPage, $itemsPerPage, $viewMode)
        {
            if ($totalPages <= 1) return;

            echo '<div class="pagination">';

            // 上一页
            if ($currentPage > 1) {
                echo '<a href="' . buildPageUrl($currentPage - 1, $itemsPerPage, $viewMode) . '">上一页</a>';
            } else {
                echo '<span class="disabled">上一页</span>';
            }

            // 页码
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);

            for ($i = $startPage; $i <= $endPage; $i++) {
                if ($i == $currentPage) {
                    echo '<span class="current">' . $i . '</span>';
                } else {
                    echo '<a href="' . buildPageUrl($i, $itemsPerPage, $viewMode) . '">' . $i . '</a>';
                }
            }

            // 下一页
            if ($currentPage < $totalPages) {
                echo '<a href="' . buildPageUrl($currentPage + 1, $itemsPerPage, $viewMode) . '">下一页</a>';
            } else {
                echo '<span class="disabled">下一页</span>';
            }

            echo '</div>';
        }

        function buildPageUrl($page, $perPage, $viewMode)
        {
            return '?' . http_build_query([
                'page' => $page,
                'per_page' => $perPage,
                'view' => $viewMode
            ]);
        }
        ?>
    </div>

    <script>
        function updatePerPage(perPage) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // 重置到第一页
            window.location.href = url.toString();
        }

        function changeViewMode(mode) {
            const url = new URL(window.location);
            url.searchParams.set('view', mode);
            url.searchParams.set('page', 1); // 重置到第一页
            window.location.href = url.toString();
        }

        function handleImageError(img) {
            const parent = img.parentElement;
            const alt = img.alt || '文件';
            const ext = alt.split('.').pop().toUpperCase();
            parent.innerHTML = '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px; text-align: center; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">' + ext + '</div>';
        }

        function copyUrl(url, button) {
            // 创建临时文本区域
            const textArea = document.createElement('textarea');
            textArea.value = url;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                // 尝试执行复制命令
                const successful = document.execCommand('copy');
                if (successful) {
                    // 复制成功
                    const originalText = button.innerHTML;
                    button.innerHTML = '已复制!';
                    button.classList.add('copied');

                    // 显示通知
                    showNotification('链接已复制到剪贴板');

                    // 2秒后恢复按钮状态
                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.classList.remove('copied');
                    }, 2000);
                } else {
                    // 复制失败
                    showNotification('复制失败，请手动选择文本复制', true);
                }
            } catch (err) {
                // 复制出错
                console.error('复制失败:', err);
                showNotification('复制失败，请手动选择文本复制', true);
            } finally {
                // 清理临时元素
                document.body.removeChild(textArea);
            }
        }

        function showNotification(message, isError = false) {
            // 移除现有的通知
            const existingNotification = document.querySelector('.copy-notification');
            if (existingNotification) {
                existingNotification.remove();
            }

            // 创建新通知
            const notification = document.createElement('div');
            notification.className = 'copy-notification';
            notification.style.background = isError ? '#dc3545' : '#28a745';
            notification.textContent = message;
            document.body.appendChild(notification);

            // 3秒后自动隐藏
            setTimeout(function() {
                notification.style.opacity = '0';
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // 备用方案：使用现代 Clipboard API（如果可用）
        function copyUrlModern(url, button) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    // 复制成功
                    const originalText = button.innerHTML;
                    button.innerHTML = '已复制!';
                    button.classList.add('copied');
                    showNotification('链接已复制到剪贴板');
                    setTimeout(function() {
                        button.innerHTML = originalText;
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(function(err) {
                    // 如果现代API失败，回退到传统方法
                    console.error('Clipboard API 失败:', err);
                    copyUrl(url, button);
                });
            } else {
                // 浏览器不支持 Clipboard API，使用传统方法
                copyUrl(url, button);
            }
        }

        // 更新复制函数调用，使用更兼容的版本
        function copyUrlWrapper(url, button) {
            copyUrlModern(url, button);
        }
    </script>
</body>

</html>

<?php
// 创建下载CSV文件
file_put_contents('download_csv.php', '<?php
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
?>');

// 创建清除数据文件
file_put_contents('clear_data.php', '<?php
$tempFile = "temp_media_data.json";
if (file_exists($tempFile)) {
    unlink($tempFile);
}
header("Location: ' . $_SERVER['PHP_SELF'] . '");
exit;
?>');
?>