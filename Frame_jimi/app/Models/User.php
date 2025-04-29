<?php

namespace App\Models; // 修改命名空间

use App\System\Helpers;
use App\System\Define;
use App\System\Config;
use App\System\HTTP\Request; //请求
use App\System\HTTP\Response;//响应
use App\System\HTTP\Router;//响应

class User
{
    private $name;

    public function setName(string $name)
    {
        $this->name = $name;
		// echo Helpers::my_global_function('World,....》'); // 正常调用		
		// echo Helpers::nonExistentMethod('World.?.....<'); // 调用不存在的方法，会抛出异常
		
		// echo Define::SITE_URL; // 输出：My Awesome App
		// echo Config::get('database.db_host'); // 获取 SITE_URL
		// echo Define::get('db_host'); // 获取 DB_HOST
		
		echo Helpers::random(20);
		// // 示例用法




		
    }

    public function getName(): string
    {
        return $this->name;
    }
}
