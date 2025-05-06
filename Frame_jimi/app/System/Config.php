<?php
namespace App\System;

use App\Jimi;
use Exception;

class Config
{
    private static $config = [];
    private const CONFIG_FILE_EXTENSION = '.php'; // 配置文件扩展名

    /**
     * 加载配置文件
     *
     * @param string $configFile 配置文件名 (不包含扩展名)
     * @return array
     * @throws Exception 如果配置文件加载失败
     */
    public static function load(string $configFile = 'system'): array
    {
        $jimi = new Jimi();

        $configPaths = $jimi->configPaths;

        // 检查 configPaths 的类型
        if (!is_array($configPaths)) {
            if (is_string($configPaths)) {
                $configPaths = [$configPaths];
            } else {
                $errorMessage = "\$jimi->configPaths is not a string or an array.";
                error_log($errorMessage);
                // 抛出异常，方便上层代码处理
                throw new Exception($errorMessage);
            }
        }

        self::$config = []; // 初始化配置，以便合并多个配置文件

        // 遍历配置路径，查找配置文件
        foreach ($configPaths as $path) {
            $fullPath = rtrim($path, '/') . '/' . $configFile . self::CONFIG_FILE_EXTENSION; // 拼接完整路径
            if (file_exists($fullPath)) {
                try {
                    $temp = include $fullPath; // 直接引入文件
                    if (is_array($temp)) {
                        self::$config = array_merge(self::$config, $temp); // 合并配置
                    } else {
                        $errorMessage = "Config file {$fullPath} is not a valid php array.";
                        error_log($errorMessage);
                        throw new Exception($errorMessage); // 抛出异常
                    }
                } catch (\Throwable $e) {
                    $errorMessage = "Error including config file {$fullPath}: " . $e->getMessage();
                    error_log($errorMessage);
                    throw new Exception($errorMessage);
                }
            }
        }

        if (empty(self::$config)) {
            $errorMessage = "Config file not found in any of the configured paths.";
            error_log($errorMessage);
            // 抛出异常，方便上层代码处理
            throw new Exception($errorMessage);
        }
        return self::$config;
    }

    /**
     * 获取配置项的值
     *
     * @param string $key 配置项的键名
     * @param mixed $default 默认值
     * @param string|null $arrayHandling  如果配置项是数组，如何处理：
     *                                  - null:  直接返回数组 (默认)
     *                                  - 'implode': 使用逗号连接数组元素
     *                                  - 'first':  返回数组的第一个元素
     *                                  - 'json':  返回JSON格式
     *                                  - 其他任何值：返回默认值
     * @return mixed
     */
    public static function get(string $key, $default = null, string $arrayHandling = null)
    {
        if (empty(self::$config)) {
            try {
                self::load();
            } catch (Exception $e) {
                // 在这里处理配置加载失败的情况，例如返回默认值或抛出异常
                error_log("Failed to load config: " . $e->getMessage());
                return $default; // 或者抛出异常
            }
        }

        $keys = explode('.', $key); // 支持多层级的配置项，例如 database.db_host
        $value = self::$config;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default; // 如果任何一个层级不存在，则返回默认值
            }
            $value = $value[$k];
        }

        // 处理数组
        if (is_array($value)) {
            switch ($arrayHandling) {
                case 'implode':
                    return implode(',', $value);
                case 'first':
                    return reset($value); // 返回数组的第一个元素
                case 'json':
                    return json_encode($value); //返回JSON
                default:
                    return $value; // 直接返回数组
            }
        }
        return $value ?? $default;
    }
	
	/**
	 * 获取所有配置
	 *
	 * @return array
	 */
	public function all(): array
	{
		if (empty($this->config)) {
			$this->load();
		}
		return $this->config;
	}
}
