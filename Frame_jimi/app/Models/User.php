<?php

namespace App\Models; // 修改命名空间

use App\System\Helpers;//方法
use App\System\Define;//常量
use App\System\Config;//配置
use App\System\HTTP\Request; //请求
use App\System\HTTP\Response;//响应
use App\System\HTTP\Router;//路由

class User
{
    private $name;

    public function setName(string $name)
    {
        $this->name = $name;
		// echo Helpers::my_global_function('World,....》'); // 正常调用		
		// echo Helpers::nonExistentMethod('World.?.....<'); // 调用不存在的方法，会抛出异常
		// echo Helpers::random(20);
		// echo Define::get('SYS_TIME'); // 获取 DB_HOST
		// 获取默认系统配置
		// echo Config::get('database.db_host'); // 获取 SITE_URL
		// 指定单个配置路径和配置文件名
		// $configPath = Config::load('Router');
		
		
// 模拟 $_SERVER 变量
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/users/1232';
$configPath = Config::load('Router');
Router::loadRoutes($configPath);
$request = new Request($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
$response = Router::dispatch($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";



    }

    public function getName(): string
    {
        return $this->name;
    }
}
