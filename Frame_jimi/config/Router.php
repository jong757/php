<?php
// config/routes.php

return [
    'routes' => [
        // [
        //     'path' => '/',
        //     'method' => 'GET',
        //     'handler' => function (\App\System\HTTP\Request $request, array $params) {
        //         return new \App\System\HTTP\Response('Home Page');
        //     }
        // ],
        // [
        //     'path' => '/about',
        //     'method' => 'GET',
        //     'handler' => 'AboutController@index',
        //     'middleware' => [\App\Middlewares\ExampleMiddleware::class]
        // ],
        // [
        //     'path' => '/contact',
        //     'method' => 'POST',
        //     'handler' => 'ContactController@store',
        // ],
        [
			'path' => '/users/{id}',
			'method' => 'POST',
			'handler' => 'App\Controllers\UserController@update', // 指向控制器方法
         ],
    ]
];
