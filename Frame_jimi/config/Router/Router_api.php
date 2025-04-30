<?php

namespace App;

use App\System\HTTP\Router;
use App\Controller\UserController;
use App\Middleware\AuthMiddleware; // 假设你有一个 AuthMiddleware

	// API 版本控制和路由分组示例
	Router::group(['prefix' => '/api/v1', 'middleware' => [AuthMiddleware::class]], function () {
    // 用户资源路由
    Router::resource('/users', UserController::class);

    //  其他 API 路由，例如：
    // Router::get('/profile', [UserController::class, 'profile']); // 获取当前用户资料
});

//  你可以定义其他版本的 API，例如：
// Router::group(['prefix' => '/api/v2', 'middleware' => [AuthMiddleware::class]], function () {
//     //  v2 版本的路由
// });
