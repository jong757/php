好的，下面我将详细讲解这个 `Response` 类的使用方式，并提供一些示例，帮助你理解如何在实际项目中使用它。

```
// 引入 Response 类 (假设你的自动加载已经配置好)
use App\System\HTTP\Response;
```
**类概览**

这个 `Response` 类封装了 HTTP 响应的各种属性，包括：

*   **状态码 (`statusCode`)**:  如 200 (OK), 404 (Not Found), 500 (Internal Server Error) 等。
*   **响应头 (`headers`)**:  键值对，如 `Content-Type: application/json`, `Cache-Control: no-cache`。
*   **响应体 (`content`)**:  实际返回给客户端的数据，可以是 HTML, JSON, 文本等。
*   **协议版本 (`protocolVersion`)**:  HTTP 协议版本，默认为 '1.1'
*   **状态码描述信息 (`statusTexts`)**:  状态码对应的文本描述，例如 200 对应 "OK"。

**核心方法**

*   `setStatusCode(int $statusCode): self`:  设置状态码。
*   `getStatusCode(): int`:  获取状态码。
*   `setHeader(string $name, string $value, bool $replace = true): self`:  设置响应头。 `$replace` 参数控制是否覆盖已存在的同名 header。
*   `getHeader(string $name): ?string`:  获取指定名称的响应头。
*   `removeHeader(string $name): self`: 移除指定的响应头。
*   `setContent(string $content): self`:  设置响应体。
*   `getContent(): string`:  获取响应体。
*   `setContentType(string $contentType, string $charset = 'utf-8'): self`:  设置 `Content-Type` 头，方便指定返回的数据类型。
*   `sendHeaders(): void`:  发送响应头 (内部使用)。  注意：需要在任何输出之前调用。
*   `send(): void`:  发送完整的响应 (包括 headers 和 content)。
*   `createJson(mixed $data, int $statusCode = 200, array $headers = []): self`:  静态方法，创建 JSON 响应。
*   `redirect(string $url, int $statusCode = 302): void`:  重定向到指定的 URL。

**使用示例**

```php
<?php

// 1. 创建一个简单的文本响应
$response = new Response();
$response->setContent('Hello, World!');
$response->setHeader('X-Custom-Header', 'My Value'); //添加自定义header
$response->send(); // 发送响应
exit; //确保脚本结束，避免后续代码干扰响应
```

```php
<?php

// 2. 创建一个 JSON 响应
use App\System\HTTP\Response;

$data = [
    'message' => 'Success',
    'data' => [
        'id' => 123,
        'name' => 'Example'
    ]
];

$response = Response::createJson($data, 200, ['Cache-Control' => 'no-cache']);
$response->send();
exit;
```

```php
<?php

// 3. 创建一个 404 错误响应
use App\System\HTTP\Response;

$response = new Response();
$response->setStatusCode(404);
$response->setContentType('text/html'); // 设置内容类型为 HTML
$response->setContent('<h1>Page Not Found</h1><p>The requested page could not be found.</p>');
$response->send();
exit;
```

```php
<?php
// 4. 重定向到一个新的 URL
use App\System\HTTP\Response;

$response = new Response();
$response->redirect('https://www.example.com'); // 默认 302 重定向
// 或者使用 301 永久重定向
// $response->redirect('https://www.example.com', 301);
exit;
```

```php
<?php
// 5.  设置cookie
use App\System\HTTP\Response;

$response = new Response();
// 设置 Cookie
setcookie('my_cookie', 'cookie_value', time() + 3600, '/', '', false, true); // 设置一个 Cookie
$response->setContent('Cookie set successfully!');
$response->send();
exit;
```

**关键点和注意事项**

1.  **命名空间**:  确保你的代码在使用 `Response` 类时，正确引入了命名空间 `App\System\HTTP`。

2.  **自动加载**:  使用 Composer 的自动加载功能，可以避免手动 `require_once` 引入类文件。

3.  **`headers_sent()` 检查**:  `sendHeaders()` 方法内部有 `headers_sent()` 检查，防止在已经有输出的情况下发送 headers 导致错误。  **重要：**  在调用 `send()` 方法之前，不要有任何 `echo`, `print_r` 等输出，否则 `header()` 函数调用会失败。

4.  **重定向后的 `exit`**:  在 `redirect()` 方法中，`exit;` 非常重要。  它确保在发送重定向 header 后，脚本立即停止执行，防止继续执行后续代码，造成不可预料的结果。

5.  **异常处理**: `json_encode` 可能会抛出 `JsonException` 异常，建议在使用 `createJson` 方法时，用 `try...catch` 包裹，处理异常情况。

6.  **错误处理**:  可以扩展此类以包含更强大的错误处理机制。 例如，你可以添加一个 `setError()` 方法来设置错误消息，然后 `send()` 方法可以检查是否有错误，如果有，则发送带有适当状态代码（例如，500）的错误响应。

7.  **可扩展性**: 这个类可以进一步扩展，例如添加对文件下载的支持，或者更复杂的响应格式处理。

8.  **Cookie设置**:  `Response` 类本身不直接处理 Cookie。 Cookie 是通过 PHP 的 `setcookie()` 函数设置的。  需要在 `send()` 方法之前调用 `setcookie()`。

**实际项目中的应用**

在你的 Web 应用（例如，使用 MVC 框架）中，`Response` 类可以作为 Controller 的返回值。  Controller 处理用户请求，生成响应数据，然后使用 `Response` 类将数据封装成 HTTP 响应返回给客户端。

例如：

```php
<?php

namespace App\Controller;

use App\System\HTTP\Response;

class UserController
{
    public function getUser(int $id): Response
    {
        // 假设你从数据库中获取了用户信息
        $user = [
            'id' => $id,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        if ($user) {
            return Response::createJson($user);
        } else {
            return (new Response())->setStatusCode(404)->setContent('User not found');
        }
    }
}

// 使用示例 (在你的路由处理代码中)
$controller = new UserController();
$response = $controller->getUser(123);
$response->send();
```

希望这些详细的解释和示例能够帮助你更好地理解和使用 `Response` 类。  如果你有任何其他问题，请随时提出。
