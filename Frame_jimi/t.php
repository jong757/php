<?php

/**
 * XSS 过滤函数，用于清理用户输入。
 *
 * @param string|array $data 要过滤的数据，可以是字符串或数组。
 * @return string|array 过滤后的数据。
 */
function xss_clean(string|array $data): string|array
{
    if (is_array($data)) {
        $cleaned_data = [];
        foreach ($data as $key => $value) {
            $cleaned_data[$key] = xss_clean($value); // 递归处理数组
        }
        return $cleaned_data;
    }

    // 1. HTML 实体编码：将特殊字符转换为 HTML 实体
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    // 2. 移除 HTML 注释
    $data = preg_replace('#<!--.*?-->#si', '', $data);

    // 3. 移除 JavaScript 代码
    $data = preg_replace('#<script.*?\/script>#si', '', $data);

    // 4. 移除 iframe 标签
    $data = preg_replace('#<iframe.*?\/iframe>#si', '', $data);

    // 5. 移除 object 和 embed 标签
    $data = preg_replace('#<object.*?\/object>#si', '', $data);
    $data = preg_replace('#<embed.*?\/embed>#si', '', $data);

    // 6. 移除 on 事件属性 (例如: onload, onclick, onmouseover)
    $data = preg_replace('#(<[^>]+? [\s\r\n"\']*)on[a-z]+?[\s\r\n"\']*=([\'"]?).*?\\2#si', '\\1', $data);

    // 7. 移除 JavaScript 协议 (例如: javascript: 或 vbscript:)
    $data = preg_replace('#([a-z]*)[\s\r\n]*=[\s\r\n]*([\`\'"]*)[\s\r\n]*j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:.*?\2#si', '\\1=', $data);
    $data = preg_replace('#([a-z]*)[\s\r\n]*=([\'"]*)[\s\r\n]*v\s*b\s*s\s*c\s*r\s*i\s*p\s*t\s*:.*?\2#si', '\\1=', $data);
    $data = preg_replace('#([a-z]*)[\s\r\n]*=([\'"]*)[\s\r\n]*-moz-binding[\s\r\n]*:.*?\2#si', '\\1=', $data);

    // 8. 移除表达式 (例如: expression( ))
    $data = preg_replace('#(<[^>]+?)style[\s\r\n]*=[\s\r\n]*([\'"]*)expression\s*\(.*?\2#si', '\\1style=', $data);
    $data = preg_replace('#(<[^>]+?)style[\s\r\n]*=[\s\r\n]*([\'"]*)-moz-binding[\s\r\n]*:.*?\\2#si', '\\1style=', $data);

    // 9. 移除 data: URI 协议
    $data = preg_replace('#data:.+#i', '', $data);

    // 10. 移除危险的 HTML 标签 (如果需要)
    // $data = strip_tags($data, '<p><br><a>'); // 只允许 p, br, a 标签

    // 11. 递归清理（可选，用于处理复杂的嵌套情况，但会降低性能）
    // $new_data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // if ($new_data !== $data) {
    //    $data = xss_clean($new_data);
    // }

    return $data;
}

// 示例用法
$dirty_html = "<script>alert('XSS');</script><p onclick=\"alert('XSS')\">This is a paragraph with <a href=\"javascript:void(0)\">a link</a></p><!-- Comment -->";
$clean_html = xss_clean($dirty_html);

echo "Original1: " . $dirty_html . "\n";
echo "Cleaned2: " . $clean_html . "\n";

$dirty_array = [
    'name' => "<script>alert('XSS');</script>John Doe",
    'comment' => "This is a comment with <a href=\"javascript:void(0)\">a link</a>"
];

$clean_array = xss_clean($dirty_array);

// print_r($clean_array);
?>
