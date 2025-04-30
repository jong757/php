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
		// echo Config::get('database.db_host'); // 获取 SITE_URL
		// echo Define::get('SYS_TIME'); // 获取 DB_HOST

		// // 示例用法




		
    }

    public function getName(): string
    {
        return $this->name;
    }
}
