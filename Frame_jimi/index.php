<?php
use App\Jimi;

define('DIR', DIRECTORY_SEPARATOR);//斜杠定义
define('PATH', __DIR__ . DIR);//项目路径

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
	 * 加载路径 or 自动加载
	 *---------------------------------------------------------------
	 */
	require(dirname(__FILE__). DIR . 'app' . DIR . 'Jimi.php');
	$paths = new Jimi();
	echo $paths->autoLoading();//自动加载
	


	
	
	// 测试使用 User 类
	$user = new User();
	$user->setName("");
	echo $user->getName();

} catch (\Exception $e) {
    echo 'Exception: ' . $e->getMessage();
}