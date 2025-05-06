<?php

namespace App\System\HTTP;

/**
 * HTTP 请求类，封装了请求相关的数据和操作.
 */
class Request
{
    /**
     * GET 请求参数.
     *
     * @var array
     */
    private array $get;

    /**
     * POST 请求参数.
     *
     * @var array
     */
    private array $post;

    /**
     * 上传的文件.
     *
     * @var array
     */
    private array $files;

    /**
     * Cookie 数据.
     *
     * @var array
     */
    private array $cookies;

    /**
     * 服务器变量.
     *
     * @var array
     */
    private array $server;

    /**
     * 解析后的请求体数据.
     *
     * @var array
     */
    private array $request;

    /**
     * 原始请求体数据.
     *
     * @var string
     */
    private string $rawBody;

    /**
     * GET 请求方法常量.
     *
     * @var string
     */
    const GET = 'GET';

    /**
     * POST 请求方法常量.
     *
     * @var string
     */
    const POST = 'POST';

    /**
     * PUT 请求方法常量.
     *
     * @var string
     */
    const PUT = 'PUT';

    /**
     * DELETE 请求方法常量.
     *
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * PATCH 请求方法常量.
     *
     * @var string
     */
    const PATCH = 'PATCH';

    /**
     * 构造函数.
     *
     * @param array  $server  服务器变量
     * @param array  $get     GET 请求参数
     * @param array  $post    POST 请求参数
     * @param array  $files   上传的文件
     * @param array  $cookies Cookie 数据
     * @param string $rawBody 原始请求体数据
     */
    public function __construct(
        array $server = [],
        array $get = [],
        array $post = [],
        array $files = [],
        array $cookies = [],
        string $rawBody = ''
    ) {
        $this->server = $server;
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->cookies = $cookies;
        $this->rawBody = $rawBody;
        $this->request = $this->parseRequestBody();
    }

    /**
     * 从全局变量创建 Request 对象.
     *
     * @return self
     */
    public static function createFromGlobals(): self
    {
        $get = $_GET;
        $post = $_POST;
        $files = $_FILES;
        $cookies = $_COOKIE;
        $server = $_SERVER;
        $rawBody = file_get_contents('php://input');

        return new self($server, $get, $post, $files, $cookies, $rawBody);
    }

    /**
     * 获取请求方法.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? self::GET);
    }

    /**
     * 获取 GET 参数.
     *
     * @param string $key     参数名
     * @param ?string $default 默认值
     *
     * @return ?string 参数值，如果不存在则返回默认值
     */
    public function getGet(string $key, ?string $default = null): ?string
    {
        $value = $this->get[$key] ?? $default;
        return $value === null ? null : htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 获取 POST 参数.
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值，如果不存在则返回默认值
     */
    public function getPost(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * 获取上传的文件.
     *
     * @param string $key     文件字段名
     * @param mixed  $default 默认值
     *
     * @return mixed 文件信息，如果不存在则返回默认值
     */
    public function getFiles(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    /**
     * 获取 Cookie 值.
     *
     * @param string $key     Cookie 名称
     * @param mixed  $default 默认值
     *
     * @return mixed Cookie 值，如果不存在则返回默认值
     */
    public function getCookies(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * 获取请求头.
     *
     * @param string $header  请求头名称
     * @param ?string $default 默认值
     *
     * @return ?string 请求头值，如果不存在则返回默认值
     */
    public function getHeader(string $header, ?string $default = null): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        return $this->server[$headerKey] ?? $default;
    }

    /**
     * 判断请求方法是否匹配.
     *
     * @param string $method 请求方法
     *
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    /**
     * 获取所有请求参数（GET, POST, Request Body）.
     *
     * @return array
     */
    public function all(): array
    {
        return iterator_to_array($this->getAllParams());
    }

    /**
     * 生成所有请求参数的迭代器.
     *
     * @return iterable
     */
    private function getAllParams(): iterable
    {
        yield from $this->get;
        yield from $this->post;
        yield from $this->request;
    }

    /**
     * 获取请求参数（依次从 POST, GET, Request Body 中查找）.
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值，如果不存在则返回默认值
     */
    public function getVar(string $key, mixed $default = null): mixed
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }
        if (isset($this->get[$key])) {
            return $this->get[$key];
        }
        if (isset($this->request[$key])) {
            return $this->request[$key];
        }

        return $default;
    }

    /**
     * 获取解析后的请求体数据.
     *
     * @param string $key     参数名
     * @param mixed  $default 默认值
     *
     * @return mixed 参数值，如果不存在则返回默认值
     */
    public function getRequestData(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * 解析请求体数据.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function parseRequestBody(): array
    {
        $data = [];
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? self::GET);

        if (in_array($method, [self::PUT, self::DELETE, self::PATCH, self::POST])) {
            $contentType = $this->getHeader('Content-Type');

            if ($contentType === null) {
                if (strpos($this->rawBody, '{') === 0 || strpos($this->rawBody, '[') === 0) {
                    $contentType = 'application/json';
                } elseif (strpos($this->rawBody, '=') > 0 && strpos($this->rawBody, '&') > 0) {
                    $contentType = 'application/x-www-form-urlencoded';
                } else {
                    return $data;
                }
            }

            if (preg_match('/application\/json/i', $contentType)) {
                $jsonData = json_decode($this->rawBody, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $jsonData;
                } else {
                    error_log('JSON 解析失败: ' . json_last_error_msg()); // 记录错误日志
                    throw new InvalidArgumentException('Invalid JSON data in request body.'); // 抛出异常
                }
            } elseif (preg_match('/application\/x-www-form-urlencoded/i', $contentType)) {
                parse_str($this->rawBody, $data);
            } else {
                return [];
            }
        }

        return $data;
    }
}
