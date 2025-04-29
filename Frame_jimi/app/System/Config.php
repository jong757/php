<?php
namespace App\System;

use App\Jimi;

class Config
{
    private static $config = [];
    private static $configFile = 'system.php'; // 配置文件名  更改为system.php

    public static function load(): array
    {
        $jimi = new Jimi(); // 创建 Jimi 类的实例

        $configPaths = $jimi->configPaths; // 获取 configPaths

        // 检查 configPaths 的类型
        if (!is_array($configPaths)) {
            // 如果 configPaths 不是数组，则将其转换为包含该字符串的数组
            if (is_string($configPaths)) {
                $configPaths = [$configPaths];
            } else {
                // 如果 configPaths 不是字符串也不是数组，记录错误并返回空数组
                error_log("\$jimi->configPaths is not a string or an array.");
                return [];
            }
        }


        // 遍历配置路径，查找配置文件
        foreach ($configPaths as $path) {
            $fullPath = rtrim($path, '/') . '/' . self::$configFile; // 拼接完整路径
            if (file_exists($fullPath)) {
                //将parse_ini_file改为直接引入文件
                $temp = include $fullPath;
                if(is_array($temp)){
                    self::$config = $temp;
                }else{
                    error_log("Config file is not valid php array.");
                    return [];
                }
                return self::$config; // 找到配置文件后立即返回
            }
        }

        // 如果所有路径都未找到配置文件
        self::$config = [];
        error_log("Config file not found in any of the configured paths.");
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
     *                                  - 其他任何值：返回默认值
     * @return mixed
     */
    public static function get(string $key, $default = null, string $arrayHandling = null)
    {
        if (empty(self::$config)) {
            self::load();
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
}
