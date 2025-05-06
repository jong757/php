<?php

/**
 * 自动加载类
 *
 * 该函数将根据类名自动加载对应的 PHP 文件。
 * 约定：
 *   - 类名必须与文件名（不含 .php 扩展名）相同。
 *   - 类文件必须位于指定的命名空间目录下。
 *
 * @param string $className 要加载的类名（包含命名空间）。
 */
spl_autoload_register(function ($className) {
    // 定义根命名空间（基础路径）。
    $baseNamespace = 'MyApp\\'; // 你的应用的根命名空间。请根据实际情况修改。
    $baseDir = PATH . '/'; // 项目的根目录。  建议使用 __DIR__ 以确保路径正确。

    // 检查类名是否以根命名空间开始。
    $len = strlen($baseNamespace);
    if (strncmp($baseNamespace, $className, $len) !== 0) {
        // 如果类名不属于该命名空间，则不尝试加载。
        return;
    }

    // 获取相对于根命名空间的类名。
    $relativeClass = substr($className, $len);

    // 将命名空间分隔符转换为目录分隔符。
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // 如果文件存在，则加载该文件。
    if (file_exists($file)) {
        require $file;
    } else {
        // 可选：记录日志或抛出异常，以指示类文件未找到。
        error_log("自动加载失败：文件未找到 - " . $file); // 记录到错误日志
        // throw new Exception("找不到类文件: " . $file); //抛出异常，方便调试
    }
});


// 示例用法：
// 假设你有一个类 `MyApp\Models\User`，并且该类定义在 `models/User.php` 文件中。
// （注意：文件路径是相对于包含 autoloader 的文件。）

// 现在，你可以直接使用该类，而无需手动 `require` 或 `include`：
// use MyApp\Models\User; //如果你的PHP版本支持命名空间导入，推荐使用。
// $user = new MyApp\Models\User();
// $user->setName("John Doe");
// echo $user->getName();

/**
 * 重要说明:
 *  1. 确保你的类文件命名和类名完全一致（大小写敏感）。
 *  2. 根据你的项目结构修改 `$baseNamespace` 和 `$baseDir`。
 *  3. 如果你的类文件不在根目录下，需要根据实际情况调整路径。
 *  4. 建议将此自动加载器文件放在项目的入口文件中 (例如 index.php)。
 *  5. 使用命名空间可以更好地组织你的代码，并避免类名冲突。
 *  6. 错误处理很重要，建议添加错误日志记录或异常抛出。
 */
