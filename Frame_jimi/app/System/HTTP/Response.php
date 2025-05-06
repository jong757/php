<?php

namespace App\System\HTTP;

/**
 * HTTP 响应类，封装了响应相关的数据和操作.
 */
class Response
{
	/**
	 * 响应状态码.
	 *
	 * @var int
	 */
	private int $statusCode = 200;

	/**
	 * 响应头.
	 *
	 * @var array
	 */
	private array $headers = [];

	/**
	 * 响应体.
	 *
	 * @var string
	 */
	private string $content = '';

	/**
	 * 协议版本
	 * @var string
	 */
	private string $protocolVersion = '1.1';

	/**
	 * 状态码对应的描述信息
	 * @var array
	 */
	private array $statusTexts = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',            // WebDAV; RFC 2518
		103 => 'Early Hints',           // RFC 8297
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information', // RFC 7233
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',          // WebDAV; RFC 4918
		208 => 'Already Reported',      // WebDAV; RFC 5842
		226 => 'IM Used',               // RFC 3229
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',         // Previously "Moved Temporarily"
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',    // RFC 7538
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Content Too Large',        // RFC 7231
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Range Not Satisfiable',      // RFC 7233
		417 => 'Expectation Failed',
		418 => 'I\'m a teapot',            // RFC 2324
		421 => 'Misdirected Request',      // RFC 7540
		422 => 'Unprocessable Content',     // WebDAV; RFC 4918
		423 => 'Locked',                    // WebDAV; RFC 4918
		424 => 'Failed Dependency',         // WebDAV; RFC 4918
		425 => 'Too Early',                 // RFC 8470
		426 => 'Upgrade Required',          // RFC 2817
		428 => 'Precondition Required',     // RFC 6585
		429 => 'Too Many Requests',         // RFC 6585
		431 => 'Request Header Fields Too Large', // RFC 6585
		451 => 'Unavailable For Legal Reasons',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',    // RFC 2295
		507 => 'Insufficient Storage',       // WebDAV; RFC 4918
		508 => 'Loop Detected',            // WebDAV; RFC 5842
		510 => 'Not Extended',            // RFC 2774
		511 => 'Network Authentication Required', // RFC 6585
	];

	/**
	 * 设置响应状态码.
	 *
	 * @param int $statusCode 状态码
	 *
	 * @return $this
	 */
	public function setStatusCode(int $statusCode): self
	{
		$this->statusCode = $statusCode;
		return $this;
	}

	/**
	 * 获取响应状态码.
	 *
	 * @return int
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	/**
	 * 设置响应头.
	 *
	 * @param string $name  头名称
	 * @param string $value 头值
	 * @param bool $replace 是否覆盖已存在的header
	 *
	 * @return $this
	 */
	public function setHeader(string $name, string $value, bool $replace = true): self
	{
		if ($replace || !isset($this->headers[$name])) {
			$this->headers[$name] = $value;
		} else {
			$this->headers[$name] .= ', ' . $value;
		}
		return $this;
	}

	/**
	 * 获取响应头.
	 *
	 * @param string $name 头名称
	 *
	 * @return string|null
	 */
	public function getHeader(string $name): ?string
	{
		return $this->headers[$name] ?? null;
	}

	/**
	 * 移除响应头.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function removeHeader(string $name): self
	{
		unset($this->headers[$name]);
		return $this;
	}

	/**
	 * 设置响应体.
	 *
	 * @param string $content 响应内容
	 *
	 * @return $this
	 */
	public function setContent(string $content): self
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * 获取响应体.
	 *
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	 /**
	 * 设置 Content-Type 响应头.
	 *
	 * @param string $contentType Content-Type 值
	 *
	 * @return $this
	 */
	public function setContentType(string $contentType, string $charset = 'utf-8'): self
	{
		$this->setHeader('Content-Type', $contentType . '; charset=' . $charset);
		return $this;
	}

	/**
	 * 发送响应头.
	 *
	 * @return void
	 */
	protected function sendHeaders(): void
	{
		if (headers_sent()) {
			return;
		}

		// 发送状态行
		header(sprintf(
			'HTTP/%s %s %s',
			$this->protocolVersion,
			$this->statusCode,
			$this->statusTexts[$this->statusCode] ?? ''
		), true, $this->statusCode);


		// 发送其他 Header
		foreach ($this->headers as $name => $value) {
			header($name . ': ' . $value, false);
		}
	}

	/**
	 * 发送响应.
	 *
	 * @return void
	 */
	public function send(): void
	{
		$this->sendHeaders();
		
		echo $this->content;
	}

	/**
	 * 创建 JSON 响应.
	 *
	 * @param mixed $data     要编码成 JSON 的数据
	 * @param int   $statusCode 状态码
	 * @param array $headers  附加的响应头
	 *
	 * @return self
	 */
	public static function createJson(mixed $data, int $statusCode = 200, array $headers = []): self
	{
		$jsonString = json_encode($data, JSON_THROW_ON_ERROR);
		$response = new self();
		$response->setContent($jsonString);
		$response->setStatusCode($statusCode);
		$response->setContentType('application/json');

		foreach ($headers as $name => $value) {
			$response->setHeader($name, $value);
		}

		return $response;
	}

	/**
	 * 重定向到指定的 URL.
	 *
	 * @param string $url        重定向的 URL
	 * @param int    $statusCode 状态码 (默认为 302 Found)
	 *
	 * @return void
	 */
public function redirect(string $url, int $statusCode = 302): void
{
	
	// 尝试发送 PHP Header
	$this->setStatusCode($statusCode);
	$this->setHeader('Location', $url, true);
	$this->sendHeaders();

	// // 如果 Header 发送失败，则使用 JavaScript 重定向
	// if (headers_sent()) {
	//     echo '<script type="text/javascript">';
	//     echo 'window.location.href="' . $url . '";';
	//     echo '</script>';
	//     echo '<noscript>';
	//     echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
	//     echo '</noscript>';
	// }

	exit; // 确保重定向后脚本停止执行
}

}
