```markdown
# Jimi Framework 路由使用说明

本文档详细介绍了 Jimi Framework 中路由的使用方法。

## 头部引入

```php
use App\System\HTTP\Router;
```

## 基本概念

Jimi Framework 的路由系统负责将 HTTP 请求映射到相应的处理程序（Controller 方法或 Closure）。

*   **URI:**  请求的 URL，例如 `/`, `/users`, `/products/{id}`.
*   **方法:**  HTTP 请求方法，例如 `GET`, `POST`, `PUT`, `DELETE`.
*   **Action:**  处理请求的函数或方法，可以是：
    *   **Closure (匿名函数):**  一个没有名字的函数。
    *   **Controller 方法:**  一个类的静态方法，例如 `'App\Controller\HomeController@index'`.
    *   **数组:** 包含了类名和方法名的数组，例如 `[App\Controller\HomeController::class, 'index']`.
*   **中间件:**  在请求到达 Action 之前或之后执行的代码，用于处理身份验证、日志记录等任务。

## 路由定义

路由规则定义在 `routes/web.php` 文件中（你可以根据需要更改文件名）。

```php
<?php

namespace App;

use App\System\HTTP\Router;
use App\Controller\HomeController;
use App\Middleware\AuthMiddleware;
use App\Middleware\LogMiddleware;

Router::middleware([LogMiddleware::class]); // 全局中间件

Router::get('/', [HomeController::class, 'index']); // 使用数组
Router::get('/about', 'App\Controller\HomeController@about'); // 使用字符串

Router::get('/users/{id}', function ($request, $params) { // 使用 Closure
    return new \App\System\HTTP\Response('User ID: ' . $params['id']);
}, [AuthMiddleware::class]); // 路由专属中间件

Router::post('/users', 'App\Controller\UserController@store');
```

### 路由方法

以下方法用于定义路由规则：

*   `Router::get(string $uri, $action, array $middleware = [])`:  定义一个 GET 路由。
*   `Router::post(string $uri, $action, array $middleware = [])`: 定义一个 POST 路由。
*   `Router::put(string $uri, $action, array $middleware = [])`: 定义一个 PUT 路由。
*   `Router::delete(string $uri, $action, array $middleware = [])`: 定义一个 DELETE 路由。

**参数说明:**

*   `$uri`:  请求的 URI。  可以使用参数，例如 `/users/{id}`.
*   `$action`:  处理请求的 Action (Closure 或 Controller 方法)。
*   `$middleware`:  一个包含中间件类名的数组，用于处理路由专属的请求前后的逻辑（可选）。

### Action 的类型

*   **Closure (匿名函数):**

    ```php
    Router::get('/hello', function ($request, $params) {
        return new \App\System\HTTP\Response('Hello, World!');
    });
    ```

    `$request` 参数是 `App\System\HTTP\Request` 的实例，包含请求的信息。
    `$params` 参数是一个关联数组，包含从 URI 中提取的参数。

*   **Controller 方法 (字符串):**

    ```php
    Router::get('/about', 'App\Controller\HomeController@about');
    ```

    这将调用 `App\Controller\HomeController` 类的 `about` 方法。

*   **Controller 方法 (数组):**

    ```php
    Router::get('/', [HomeController::class, 'index']);
    ```
    这将调用 `App\Controller\HomeController` 类的 `index` 方法。  使用 `::class` 可以在 IDE 中提供更好的自动完成和重构支持。

### 参数

URI 中可以使用参数，参数用花括号 `{}` 包围。

```php
Router::get('/users/{id}', function ($request, $params) {
    return new \App\System\HTTP\Response('User ID: ' . $params['id']);
});
```

在这个例子中，`/users/123` 会将 `123` 作为 `$params['id']` 传递给 Closure。

### 中间件

中间件用于在请求到达 Action 之前或之后执行代码。

*   **全局中间件:**  使用 `Router::middleware()` 方法添加全局中间件。 全局中间件会应用到所有路由。

    ```php
    Router::middleware([LogMiddleware::class]);
    ```

*   **路由专属中间件:**  将中间件类名添加到路由定义的 `$middleware` 数组中。

    ```php
    Router::get('/users/{id}', function ($request, $params) {
        // ...
    }, [AuthMiddleware::class]);
    ```

#### 中间件的实现

中间件类需要实现一个 `handle` 方法，该方法接收 `$request` 和 `$next` 作为参数。

```php
<?php
namespace App\Middleware;

use App\System\HTTP\Request;
use App\System\HTTP\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // 身份验证逻辑
        if ($request->header('Authorization') != 'Bearer mytoken'){
             return new Response('Unauthorized', 401);
        }

        // 继续执行下一个中间件或 Controller
        return $next($request);
    }
}
```

`$next` 参数是一个 callable (可以理解为函数或闭包)，用于调用下一个中间件或最终的 Action。 如果你想阻止请求继续执行，可以直接在中间件中返回一个 `Response` 对象。

## 如何运行路由

在 `Jimi.php`  入口文件中，需要调用 `Router::dispatch($request)` 方法来运行路由。

```php
<?php
namespace App;

use App\System\Autoloading\Autoloading;
use App\System\Config;
use App\System\HTTP\Request;
use App\System\HTTP\Response;
use App\System\HTTP\Router;

class Jimi
{
    public static function run()
    {
        Autoloading::register();
        Config::load(dirname(__DIR__) . '/config/system.php');

        $request = new Request();
        $response = new Response();

        // 定义路由
        require_once dirname(__DIR__) . '/routes/web.php';

        // 运行路由
        $response = Router::dispatch($request);  // 获取 response
        $response->send();
    }
}
```

`Router::dispatch($request)` 方法会返回一个 `App\System\HTTP\Response` 对象，你需要调用 `$response->send()` 方法将其发送给客户端。

## 获取当前路由信息

* **获取当前路由URI：**

  ```php
  $currentRouteUri = Router::getCurrentRouteUri();
  ```

* **获取当前路由参数：**

  ```php
  $currentRouteParams = Router::getCurrentRouteParams();
  ```

## 示例 Controller

```php
<?php
namespace App\Controller;

use App\System\HTTP\Request;
use App\System\HTTP\Response;

class HomeController
{
    public function index(Request $request, array $params): Response
    {
        return new Response('Hello, World!');
    }

    public function about(Request $request, array $params): Response
    {
        return new Response('About Us');
    }
}
```

Controller 方法接收 `$request` 和 `$params` 作为参数，并返回一个 `App\System\HTTP\Response` 对象。

## 总结

Jimi Framework 的路由系统提供了一种简单、灵活和高性能的方式来处理 HTTP 请求。  通过定义路由规则、使用 Closure 或 Controller 方法作为 Action，以及使用中间件，你可以轻松地构建 Web 应用程序。

记住，路由只是 Web 应用程序的一部分。  你还需要考虑其他方面，例如：

*   模板引擎
*   数据库访问
*   表单验证
*   安全性

希望本文档对你有所帮助！
```
