<?php

namespace App\System\HTTP;

use Closure;

class Router
{
    private static $routes = []; // 存储所有路由规则
    private static $middleware = []; // 存储全局中间件
    private static $currentRoute = null; // 当前匹配的路由

    // 添加一个 GET 路由
    public static function get(string $uri, $action, array $middleware = []): void
    {
        self::addRoute('GET', $uri, $action, $middleware);
    }

    // 添加一个 POST 路由
    public static function post(string $uri, $action, array $middleware = []): void
    {
        self::addRoute('POST', $uri, $action, $middleware);
    }

    // 添加一个 PUT 路由
    public static function put(string $uri, $action, array $middleware = []): void
    {
        self::addRoute('PUT', $uri, $action, $middleware);
    }

    // 添加一个 DELETE 路由
    public static function delete(string $uri, $action, array $middleware = []): void
    {
        self::addRoute('DELETE', $uri, $action, $middleware);
    }

    // 添加一个路由规则
    private static function addRoute(string $method, string $uri, $action, array $middleware = []): void
    {
        self::$routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $middleware,
        ];
    }

    // 添加全局中间件
    public static function middleware(array $middleware): void
    {
        self::$middleware = array_merge(self::$middleware, $middleware);
    }

    // 运行路由
    public static function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        // 1. 查找匹配的路由
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $routeUri => $route) {
                if (self::matchRoute($routeUri, $uri, $params)) {
                    self::$currentRoute = ['uri' => $routeUri, 'params' => $params, 'middleware' => $route['middleware'], 'action' => $route['action']];
                    return self::runRoute($request);
                }
            }
        }

        // 2. 如果没有找到匹配的路由，返回 404 响应
        return new Response('404 Not Found', 404);
    }

    // 匹配路由
    private static function matchRoute(string $routeUri, string $uri, &$params): bool
    {
        // 1. 完全匹配
        if ($routeUri === $uri) {
            $params = [];
            return true;
        }

        // 2. 使用参数匹配 (例如：/users/{id})
        $routeParts = explode('/', $routeUri);
        $uriParts = explode('/', $uri);

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/\{([a-zA-Z0-9_]+)\}/', $routeParts[$i], $matches)) {
                $params[$matches[1]] = $uriParts[$i];
            } elseif ($routeParts[$i] !== $uriParts[$i]) {
                return false;
            }
        }

        return true;
    }

    // 执行路由
    private static function runRoute(Request $request): Response
    {
        // 1. 合并全局中间件和路由中间件
        $middleware = array_merge(self::$middleware, self::$currentRoute['middleware']);
        $action = self::$currentRoute['action'];
        $params = self::$currentRoute['params'];

        // 2. 创建中间件链
        $next = function (Request $request) use ($action, $params): Response {
            return self::executeAction($request, $action, $params);
        };

        // 3. 依次执行中间件
        foreach (array_reverse($middleware) as $middlewareClass) {
            $next = function (Request $request) use ($middlewareClass, $next): Response {
                $middlewareInstance = new $middlewareClass();
                return $middlewareInstance->handle($request, $next);
            };
        }

        // 4. 启动中间件链
        return $next($request);
    }

    // 执行 Action (Controller 方法或 Closure)
    private static function executeAction(Request $request, $action, array $params): Response
    {
        if ($action instanceof Closure) {
            // 1. 如果 Action 是一个 Closure
            return $action($request, $params);
        } elseif (is_string($action) && strpos($action, '@') !== false) {
            // 2. 如果 Action 是一个 Controller 方法 (例如：'UserController@index')
            list($controllerClass, $method) = explode('@', $action);
            $controllerInstance = new $controllerClass();
            return $controllerInstance->$method($request, $params);
        } else {
            // 3. 如果 Action 是无效的，返回 500 响应
            return new Response('Invalid Route Action', 500);
        }
    }

    public static function getCurrentRouteUri(): ?string
    {
        return self::$currentRoute['uri'] ?? null;
    }

    public static function getCurrentRouteParams(): array
    {
        return self::$currentRoute['params'] ?? [];
    }
}