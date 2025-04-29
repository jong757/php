<?php
namespace App\System; // 定义命名空间

class Helpers
{
	/**
	 *  当调用不存在的静态方法时，此方法会被自动调用
	 *
	 * @param string $method  调用的方法名
	 * @param array  $args    传递给方法的参数
	 *
	 * @throws \Exception 如果调用的方法不存在，则抛出异常
	 */
	public static function __callStatic(string $method, array $args)
	{
	    throw new \Exception("Call to undefined method App\System\Helpers::$method()");
	}
	
	//测试方法
    public static function my_global_function($param)
    {
        return 'Hello, ' . $param;
    }
	
	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
    public static function safe_replace(string $string): string
    {
        $string = str_replace(['%20', '%27', '%2527', '*', '"', "'", ';', '{', '}', '\\'], '', $string);
        $string = str_replace(['<', '>'], ['&lt;', '&gt;'], $string);
        return $string;
    }
	
	/**
	 * xss过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	public static function remove_xss($string, $options = array(), $spec = '')
	{
	    // if (!is_array($string)) {
	    //     if (!function_exists('htmLawed')) {
	    //         pc_base::load_sys_func('htmLawed');
	    //     }
	    //     return htmLawed($string, array_merge(array('safe' => 1, 'balanced' => 0), $options), $spec);
	    // }
	    // foreach ($string as $k => $v) {
	    //     $string[$k] = remove_xss($v, $options, $spec);
	    // }
	    return $string;
	}
	
   /**
     * 过滤ASCII码从0-31的控制字符
     *
     * @param string $str  要过滤的字符串
     *
     * @return string 过滤后的字符串
     */
    public static function remove_control_chars(string $str): string
    {
        return preg_replace('/[\x00-\x1F]/u', '', $str);
    }
	
	/**
	 * 转义 javascript/iframe/frame 代码标记，防止 XSS 攻击.
	 *
	 * @param mixed $data 要转义的数据，可以是字符串或数组.
	 * @return mixed 转义后的数据.
	 */
	public static function escapeScriptTags(mixed $data): mixed
	{
	    if (is_array($data)) {
	        // 使用 array_map 递归处理数组
	        return array_map('escapeScriptTags', $data);
	    } elseif (is_string($data)) {
	        // 使用一次 preg_replace_callback 减少函数调用和正则匹配次数
	        return preg_replace_callback(
	            '/(<(\/?)script([^>]*)>)|(<(\/?)iframe([^>]*)>)|(<(\/?)frame([^>]*)>)|(javascript:)/si',
	            function ($matches) {
	                if (isset($matches[9])) {
	                    // 匹配到 javascript:
	                    return 'javascript：'; // 全角冒号
	                } else {
	                    // 匹配到 script/iframe/frame tag
	                    return '&lt;' . $matches[2] . substr($matches[1], strlen($matches[2])) . $matches[3] . '&gt;';
	                }
	            },
	            $data
	        );
	    } else {
	        // 如果不是数组或字符串，直接返回，避免类型错误.
	        return $data;
	    }
	}
	
	/**
	 * 计算程序执行时间，单位为毫秒 (ms).
	 *
	 * @return float 执行时间，单位毫秒.
	 */
	public static function getExecutionTime(): float
	{
	    static $startTime;
	    if ($startTime === null) {
	        $startTime = microtime(true) * 1000;
	    }
	    $endTime = microtime(true) * 1000;
	    return round($endTime - $startTime, 3);
	}
	
	/**
	 * 生成指定长度的随机字符串。
	 *
	 * @param int    $length 要生成的字符串长度.
	 * @param string $chars  可选的字符集，默认为数字 '0123456789'.
	 * @return string 生成的随机字符串.
	 * @throws InvalidArgumentException 当 $length 小于等于 0 时抛出异常.
	 */
	public static function random(int $length, string $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ'): string
	{
	    if ($length <= 0) {
	        throw new InvalidArgumentException('$length must be a positive integer.');
	    }
	    $max = strlen($chars) - 1;
	    $result = '';
	    // 使用更安全的随机数生成器
	    for ($i = 0; $i < $length; $i++) {
	        $result .= $chars[random_int(0, $max)];
	    }
	    return $result;
	}

	
	/**
	 * 将 JSON 字符串转换为数组.
	 *
	 * @param string $data JSON 字符串.
	 * @return array 解码后的数组，如果 $data 为空，则返回空数组.
	 * @throws JsonException 如果 JSON 解码失败.
	 */
	public static function jsonStringToArray(string $data): array
	{
	    $data = trim($data);
	    if ($data === '') {
	        return [];
	    }
	    // 移除可能存在的转义反斜杠
	    if (strpos($data, '{\\') === 0) {
	        $data = stripslashes($data);
	    }
	    try {
	        $array = json_decode($data, true, 512, JSON_THROW_ON_ERROR);  // 使用 JSON_THROW_ON_ERROR
	    } catch (JsonException $e) {
	        throw new JsonException("JSON decode error: " . $e->getMessage(), $e->getCode(), $e);
	    }
	    // 根据字符集进行转换
	    if (strtolower(CHARSET) === 'gbk') {
	        $array = mult_iconv("UTF-8", "GBK//IGNORE", $array);
	    }
	    return $array;
	}
	/**
	 * 将数组转换为 JSON 字符串.
	 *
	 * @param array $data 要编码的数组.
	 * @param bool $isFormData 是否使用 new_stripslashes 处理数据，默认为 true.
	 * @return string JSON 字符串，如果 $data 为空，则返回空字符串.
	 * @throws JsonException 如果 JSON 编码失败.
	 */
	public static function arrayToJsonString(array $data, bool $isFormData = true): string
	{
	    if (empty($data)) {
	        return '';
	    }
	    // 处理数据
	    if ($isFormData) {
	        $data = new_stripslashes($data);
	    }
	    // 根据字符集进行转换
	    if (strtolower(CHARSET) === 'gbk') {
	        $data = mult_iconv("GBK", "UTF-8", $data);
	    }
	    $jsonOptions = JSON_UNESCAPED_UNICODE; // JSON 选项，默认不转义 Unicode 字符
	    // 根据 PHP 版本选择不同的 JSON 选项
	    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	       // $jsonOptions |= JSON_FORCE_OBJECT; // PHP < 5.3 没有 JSON_FORCE_OBJECT
	    } else {
	        $jsonOptions |= JSON_FORCE_OBJECT; // PHP >= 5.3 才可以使用 JSON_FORCE_OBJECT
	    }
	    try {
	        $jsonString = json_encode($data, $jsonOptions | JSON_THROW_ON_ERROR); // 使用 JSON_THROW_ON_ERROR
	    } catch (JsonException $e) {
	        throw new JsonException("JSON encode error: " . $e->getMessage(), $e->getCode(), $e);
	    }
	    return addslashes($jsonString);
	}
	
	/**
	 * 数组或字符串的字符编码转换。
	 *
	 * @param string $in_charset  输入的字符集，例如：'UTF-8'，'GBK'，'ISO-8859-1' 等。
	 * @param string $out_charset 输出的字符集，例如：'UTF-8'，'GBK' 等。函数内部会自动添加 `//IGNORE`。
	 * @param mixed  $data        需要进行字符编码转换的数据。可以是字符串、数组或其它类型。
	 * @return mixed              转换编码后的数据。如果输入为非字符串或非数组，则原样返回。
	 */
	public static function mult_iconv(string $in_charset, string $out_charset, $data)
	{
	    $out_charset_with_ignore = $out_charset . '//IGNORE';
	
	    if (is_array($data)) {
	        $result = [];
	        foreach ($data as $key => $value) {
	            $new_key = is_string($key) ? iconv($in_charset, $out_charset_with_ignore, $key) : $key;
	            $result[$new_key] = mult_iconv($in_charset, $out_charset, $value);
	        }
	        return $result;
	    } elseif (is_string($data)) {
	        return iconv($in_charset, $out_charset_with_ignore, $data);
	    } else {
	        return $data;
	    }
	}
	
	/**
	 * 转换字节数为其他单位
	 *
	 * @param int|float $bytes 字节大小
	 * @param int       $decimals 小数位数 (默认: 2)
	 * @return string             返回格式化后的大小，例如 "1.23 MB"
	 */
	public static function formatBytes(int|float $bytes, int $decimals = 2): string
	{
	    if ($bytes < 0) {
	        return '0 Bytes';
	    }
	
	    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
	    $factor = floor(log($bytes, 1024));
	
	    return sprintf("%.{$decimals}f %s", $bytes / pow(1024, $factor), $units[$factor]);
	}


	
}
