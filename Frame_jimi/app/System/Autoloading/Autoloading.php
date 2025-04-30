<?php
namespace Jimi;
/*
 *---------------------------------------------------------------
 * 自动加载器
 *---------------------------------------------------------------
 */
class Autoloader
{
    public $SystemPaths;
	public array $autoloadNamespaces = [];

    public function __construct()
    {
        $this->SystemPaths = dirname(dirname(__DIR__));
    }

    /**
     * 注册自动加载器
     *
     * @param string $baseNamespace 基础命名空间
     * @param string $baseDir 基础目录
     * @return void
     */
    public static function register(string $baseNamespace, string $baseDir)
    {
        spl_autoload_register(function ($class) use ($baseNamespace, $baseDir) {
            // 检查类是否在基础命名空间下
            $len = strlen($baseNamespace);
			
            if (strncmp($baseNamespace, $class, $len) !== 0) {
                // 如果不是，则返回
                return;
            }

            // 获取类相对于基础命名空间的相对类名
            $relativeClass = substr($class, $len);

            // 将相对类名转换为文件路径
            $file = $baseDir . str_replace('\\', DIR, $relativeClass) . '.php';

            // 如果文件存在，则引入它
            if (file_exists($file)) {
                require $file;
            }
        });
    }
		
		
	/**
     * 加载指定的文件
     *
     * @param string $filePath 要加载的文件的完整路径
     * @return void
     */
    public function setNsPath(string $filePath): void
    {
		$filename = basename($filePath);
        if (file_exists($filePath)) {
            require_once $filePath .DS. $filename;
        } else {
            error_log("Autoloader: File not found: " . $filePath);
        }
    }
	
	
}

