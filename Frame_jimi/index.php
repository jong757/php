<?php
use App\Jimi;
use Jimi\Autoloader;
define('DIR', DIRECTORY_SEPARATOR);
define('PATH', __DIR__ . DIR);
//测试User方法
use App\Models\User; // 修改 use 语句

try {
	

	
	
	/**
	 * jimi index file.
	 *
	 * @author Jong <sdxwal@gmail.com>
	 * @copyright Copyright &copy; 2025 Yii Software LLC
	 * @package system
	 * @since 1.0
	 */
	/*
	 *---------------------------------------------------------------
	 * 检查PHP版本
	 *---------------------------------------------------------------
	 */
	$minPhpVersion = '8.1';
	if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
		$message = sprintf(
			'Your PHP version must be %s or higher to run jimi. Current version: %s',
			$minPhpVersion,
			PHP_VERSION,
		);
		header('HTTP/1.1 503 Service Unavailable.', true, 503);
		echo $message;
		exit(1);
	}

	/*
	 *---------------------------------------------------------------
	 * 加载路径
	 *---------------------------------------------------------------
	 */
	require(dirname(__FILE__). DIR . 'app' . DIR . 'Jimi.php');
	$paths = new Jimi();

	/*
	 *---------------------------------------------------------------
	 * 自动加载
	 *---------------------------------------------------------------
	 */
	$autoloading = $paths->SystemPaths . DIR . 'Autoloading' . DIR . 'Autoloading.php';

	if (file_exists($autoloading)) {
		require $autoloading;
		// 在文件末尾直接实例化和注册
		$autoloader = new Autoloader();
		
		// 自动加载整个app目录
		Autoloader::register('App\\', $paths->appPaths.DIR);
	}


	
	
	// 测试使用 User 类
	$user = new User();
	$user->setName("");
	echo $user->getName();

} catch (\Exception $e) {
    echo 'Exception: ' . $e->getMessage();
}