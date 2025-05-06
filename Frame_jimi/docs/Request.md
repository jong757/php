好的，下面我将从多个角度提供 `App\System\HTTP\Request` 类的使用示例，包括：

1.  **基本用法：**  创建 Request 对象，获取请求参数。
2.  **中间件场景：**  在中间件中使用 Request 对象进行预处理。
3.  **控制器场景：**  在控制器中使用 Request 对象获取请求数据。
4.  **单元测试：**  如何创建模拟的 Request 对象进行单元测试。
5.  **处理文件上传：** 如何处理文件上传的例子

**1. 基本用法**

```php
<?php

require_once 'Request.php'; // 假设 Request.php 文件包含了 Request 类的定义

use App\System\HTTP\Request;

// 模拟全局变量 (在实际环境中这些变量由 PHP 自动填充)
$_GET['name'] = 'John Doe';
$_POST['age'] = 30;
$_COOKIE['session_id'] = '1234567890';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
$_SERVER['CONTENT_TYPE'] = 'application/json';

$jsonBody = json_encode(['city' => 'New York']);
$rawBody = $jsonBody ? $jsonBody : '';

// 创建 Request 对象
$request = new Request($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE, $rawBody);

// 获取 GET 参数
$name = $request->getGet('name');
echo "Name: " . $name . PHP_EOL; // 输出: Name: John Doe

// 获取 POST 参数
$age = $request->getPost('age');
echo "Age: " . $age . PHP_EOL; // 输出: Age: 30

// 获取 Cookie
$sessionId = $request->getCookies('session_id');
echo "Session ID: " . $sessionId . PHP_EOL; // 输出: Session ID: 1234567890

// 获取请求头
$userAgent = $request->getHeader('User-Agent');
echo "User Agent: " . $userAgent . PHP_EOL; // 输出: User Agent: Mozilla/5.0

// 获取请求方法
$method = $request->getMethod();
echo "Method: " . $method . PHP_EOL; // 输出: Method: POST

// 判断请求方法
$isPost = $request->isMethod(Request::POST);
echo "Is POST: " . ($isPost ? 'true' : 'false') . PHP_EOL; // 输出: Is POST: true

// 获取所有参数
$allParams = $request->all();
print_r($allParams);
//输出:
//Array
//(
//    [name] => John Doe
//    [age] => 30
//    [city] => New York
//)

// 获取参数 (优先从 POST, 然后 GET, 然后 Request Body)
$city = $request->getVar('city');
echo "City: " . $city . PHP_EOL; // 输出: City: New York

$nonExistent = $request->getVar('non_existent', 'default_value');
echo "Non-existent: " . $nonExistent . PHP_EOL; // 输出: Non-existent: default_value

// 从请求体中获取数据
$cityFromBody = $request->getRequestData('city');
echo "City from Body: " . $cityFromBody . PHP_EOL; // 输出: City from Body: New York
```

**2. 中间件场景**

```php
<?php

require_once 'Request.php';

use App\System\HTTP\Request;

class AuthMiddleware
{
    public function handle(Request $request): Request
    {
        // 模拟从 Header 中获取 Authorization Token
        $authToken = $request->getHeader('Authorization');

        if (!$authToken) {
            // 模拟未授权处理
            header('HTTP/1.1 401 Unauthorized');
            echo 'Unauthorized';
            exit;
        }

        // 模拟验证 Token 的逻辑
        if ($authToken !== 'Bearer valid_token') {
            header('HTTP/1.1 403 Forbidden');
            echo 'Forbidden';
            exit;
        }

        // 模拟将用户信息添加到 Request 对象 (可以创建一个专门的 User 对象)
        $request->user = ['id' => 1, 'name' => 'Authenticated User'];

        return $request;
    }
}

// 模拟全局变量
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_token';

// 创建 Request 对象
$request = Request::createFromGlobals();

// 创建并执行中间件
$middleware = new AuthMiddleware();
$request = $middleware->handle($request);

// 在路由或控制器中使用 Request 对象
if (isset($request->user)) {
    echo "Welcome, " . $request->user['name'] . PHP_EOL; // 输出: Welcome, Authenticated User
}
```

**3. 控制器场景**

```php
<?php

require_once 'Request.php';

use App\System\HTTP\Request;

class UserController
{
    public function create(Request $request)
    {
        // 从 POST 请求中获取用户数据
        $name = $request->getPost('name');
        $email = $request->getPost('email');

        // 验证数据
        if (empty($name) || empty($email)) {
            return ['error' => 'Name and email are required.'];
        }

        // 模拟创建用户
        $user = ['name' => $name, 'email' => $email];

        // 返回 JSON 响应
        header('Content-Type: application/json');
        echo json_encode($user);
    }
}

// 模拟全局变量
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'New User';
$_POST['email'] = 'new.user@example.com';

// 创建 Request 对象
$request = Request::createFromGlobals();

// 创建并调用控制器方法
$controller = new UserController();
$controller->create($request);
```

**4. 单元测试**

```php
<?php

use PHPUnit\Framework\TestCase; // 确保安装了 PHPUnit
use App\System\HTTP\Request;

class RequestTest extends TestCase
{
    public function testGetGet()
    {
        $request = new Request(
            [],
            ['name' => 'TestName'],
            [],
            [],
            []
        );

        $this->assertEquals('TestName', $request->getGet('name'));
        $this->assertEquals(null, $request->getGet('nonexistent'));
        $this->assertEquals('default', $request->getGet('nonexistent', 'default'));
    }

    public function testIsMethod()
    {
        $request = new Request(
            ['REQUEST_METHOD' => 'POST'],
            [],
            [],
            [],
            []
        );
        $this->assertTrue($request->isMethod(Request::POST));
        $this->assertFalse($request->isMethod(Request::GET));
    }

    public function testCreateFromGlobals()
    {
        $_GET['test'] = 'test_value';
        $request = Request::createFromGlobals();

        $this->assertEquals('test_value', $request->getGet('test'));
        unset($_GET['test']);  // Clean up the global state
    }
}
```

**关键点:**

*   你需要安装 PHPUnit (或其他单元测试框架)。
*   在测试用例中，你创建 `Request` 对象的实例，并传入模拟的 `$_GET`, `$_POST`, `$_SERVER` 等数据。
*   使用断言 (`$this->assertEquals`, `$this->assertTrue` 等) 来验证 `Request` 对象的方法是否按预期工作。
*   记得清理全局状态 (例如，使用 `unset($_GET['test'])` 在测试后删除全局变量)，以避免测试用例之间的干扰。

要运行此测试：

1.  确保你的项目有一个 `composer.json` 文件，其中包含 `phpunit/phpunit` 作为依赖项。  运行 `composer install` 来安装 PHPUnit。
2.  将上面的测试代码保存为 `RequestTest.php` 文件（例如，放在 `tests` 目录下）。
3.  在命令行中，导航到你的项目根目录，然后运行  `./vendor/bin/phpunit tests/RequestTest.php`  (或者根据你的 PHPUnit 安装方式调整命令)。

**5. 处理文件上传**

```php
<?php

require_once 'Request.php';

use App\System\HTTP\Request;

// 模拟 $_FILES 数组 (实际环境中由 PHP 自动填充)
$_FILES['avatar'] = [
    'name' => 'avatar.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '/tmp/php5Wx0xD', // 模拟临时文件路径
    'error' => 0, // UPLOAD_ERR_OK
    'size' => 102400, // 100KB
];

// 模拟 $_SERVER 数组
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'multipart/form-data';

// 创建 Request 对象
$request = Request::createFromGlobals();

// 获取上传的文件信息
$avatar = $request->getFiles('avatar');

if ($avatar) {
    // 检查是否有上传错误
    if ($avatar['error'] === UPLOAD_ERR_OK) {
        // 定义允许的文件类型和大小
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 204800; // 200KB

        // 验证文件类型
        if (!in_array($avatar['type'], $allowedTypes)) {
            echo "Error: Invalid file type. Allowed types are JPEG, PNG, and GIF." . PHP_EOL;
        } // 验证文件大小
        elseif ($avatar['size'] > $maxSize) {
            echo "Error: File size exceeds the maximum allowed size of 200KB." . PHP_EOL;
        } else {
            // 定义上传目录
            $uploadDir = 'uploads/';

            // 生成唯一的文件名
            $filename = uniqid('avatar_') . '.' . pathinfo($avatar['name'], PATHINFO_EXTENSION);

            // 移动上传的文件到目标目录
            if (move_uploaded_file($avatar['tmp_name'], $uploadDir . $filename)) {
                echo "File uploaded successfully to " . $uploadDir . $filename . PHP_EOL;
            } else {
                echo "Error: Failed to move uploaded file." . PHP_EOL;
            }
        }
    } else {
        // 处理上传错误
        switch ($avatar['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo "Error: The uploaded file exceeds the upload_max_filesize directive in php.ini." . PHP_EOL;
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." . PHP_EOL;
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "Error: The uploaded file was only partially uploaded." . PHP_EOL;
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "Error: No file was uploaded." . PHP_EOL;
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Error: Missing a temporary folder." . PHP_EOL;
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Error: Failed to write file to disk." . PHP_EOL;
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "Error: File upload stopped by extension." . PHP_EOL;
                break;
            default:
                echo "Error: An unknown error occurred during file upload." . PHP_EOL;
        }
    }
} else {
    echo "No file uploaded." . PHP_EOL;
}
```

**关键点：**

*   **`$_FILES` 数组：**  PHP 将上传的文件信息存储在 `$_FILES` 超全局变量中。  `Request::createFromGlobals()`  方法会读取这个数组。
*   **`getFiles()` 方法：**  使用 `getFiles()` 方法获取上传文件的信息。  它返回一个关联数组，包含  `name`、`type`、`tmp_name`、`error` 和 `size` 键。
*   **错误处理：**  `$_FILES['avatar']['error']`  存储上传过程中发生的任何错误。  你应该始终检查这个值，并根据错误代码采取适当的措施。  常见的错误代码包括 `UPLOAD_ERR_OK` (上传成功)、`UPLOAD_ERR_INI_SIZE` (文件大小超过 php.ini 限制) 等。
*   **验证：**  在处理上传的文件之前，务必验证文件类型、大小和其他属性，以防止安全漏洞。
*   **`move_uploaded_file()` 函数：**  使用 `move_uploaded_file()` 函数将上传的临时文件从临时目录移动到目标目录。  这个函数是一个安全措施，可以防止恶意用户上传任意文件。
*   **上传目录：**  确保上传目录存在，并且 PHP 进程具有写入权限。
*   **安全性：**
    *   **文件类型验证：**  不要仅仅依赖 `$_FILES['avatar']['type']`  来确定文件类型。  可以使用 `mime_content_type()` 函数或 `exif_imagetype()` 函数来更可靠地检测文件类型。
    *   **文件名：**  不要直接使用用户上传的文件名。  应该生成唯一的文件名，以防止文件名冲突和安全问题。
    *   **权限：**  确保上传目录的权限设置正确，以防止未经授权的访问。
    *   **防止目录遍历：**  在保存文件时，使用绝对路径，并防止用户通过文件名进行目录遍历。

希望这些示例能帮助你更好地理解和使用 `App\System\HTTP\Request` 类。  记住，在实际开发中，要根据你的具体需求进行适当的调整和扩展。
