<?php
namespace App;

use Jimi\Autoloader;

/**
 * jimi core file.
 *
 * @author Jong <sdxwal@gmail.com>
 * @copyright Copyright &copy; 2025 Yii Software LLC
 * @package system
 * @since 1.0
 */
class Jimi
{
	/*
	 *---------------------------------------------------------------
	 * 自动加载
	 *---------------------------------------------------------------
	 */
	public function autoLoading()
	{
		$autoloading = $this->SystemPaths . DIR . 'Autoloading' . DIR . 'Autoloading.php';
		if (file_exists($autoloading)) {
			require $autoloading;
			// 在文件末尾直接实例化和注册
			$autoloader = new Autoloader();
			// 自动加载整个app目录
			return Autoloader::register('App\\', $this->appPaths.DIR);
		}
	}
	
	/*
	 *---------------------------------------------------------------
	 * 加载路由
	 *---------------------------------------------------------------
	 */
	public function run()
	{
		// Autoloading::register();
		// Config::load(dirname(__DIR__) . '/config/system.php');
		
		// $request = new Request();
		// $response = new Response();
		
		// // 定义路由
		// require_once dirname(__DIR__) . '/routes/web.php';
		
		// // 运行路由
		// $response = Router::dispatch($request);  // 获取 response
		// $response->send();
	}
	
	/**
	 * ---------------------------------------------------------------
	 * 配置文件夹名称
	 * ---------------------------------------------------------------
	 */
	public string $configPaths = PATH . 'config';
	
	/**
	 * ---------------------------------------------------------------
	 * 核心目录名称
	 * ---------------------------------------------------------------
	 */
	public string $appPaths = PATH . 'app';
	
	/**
	 * ---------------------------------------------------------------
	 * 缓存目录名称
	 * ---------------------------------------------------------------
	 */
	public string $cachePaths = PATH . 'cache';

	/**
	 * ---------------------------------------------------------------
	 * 测试目录名称
	 * ---------------------------------------------------------------
	 */
	public string $testsPaths = PATH . 'tests';

	/**
	 * ---------------------------------------------------------------
	 * 应用程序文件夹名称
	 * ---------------------------------------------------------------
	 */
	public string $SystemPaths = __DIR__ . DIR.'System';

	/**
	 * ---------------------------------------------------------------
	 * 视图目录名称
	 * ---------------------------------------------------------------
	 */
	public string $viewPaths = __DIR__ . DIR.'Views';
	
	/**
	 * ---------------------------------------------------------------
	 * 模型目录名称
	 * ---------------------------------------------------------------
	 */
	public string $ModelPaths = __DIR__ . DIR.'Model';

	/**
	 * ---------------------------------------------------------------
	 * 控制器目录名称
	 * ---------------------------------------------------------------
	 */
	public string $ControllerPaths = __DIR__ . DIR.'Controller';
	
}