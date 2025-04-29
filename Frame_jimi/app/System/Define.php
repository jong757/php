<?php
namespace App\System;

class Define
{
    private static $config = [];

    /**
     * 获取配置项
     *
     * @param string $key 配置项的键名
     * @param mixed $default 默认值，如果配置项不存在则返回该值
     * @return mixed 配置项的值，如果配置项不存在则返回默认值
     */
    public static function get(string $key, $default = null)
    {
        if (empty(self::$config)) {
            self::loadConfig();
        }
        return self::$config[$key] ?? $default;
    }

    private static function loadConfig()
    {
        self::$config = [
			'IN_JIMI' => true, //加载框架
			'SYS_TIME' => time(), //系统时间戳
            'SITE_PROTOCOL' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://', //主机协议
			'SYS_START_TIME' => microtime(true), //系统开始时间, 使用浮点数
        ];
    }
}
