<?php

namespace App;

use App\System\HTTP\Router;
use App\Controller\HomeController;

// 全局中间件可以在 Jimi.php 中定义，这里不再重复

Router::get('/', [HomeController::class, 'index']);  // 首页
Router::get('/about', [HomeController::class, 'about']); // 关于我们

//  其他 Web 页面路由，例如：
// Router::get('/contact', [HomeController::class, 'contact']);
