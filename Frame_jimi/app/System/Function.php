<?php

namespace App\Helpers;

if (! function_exists('App\Helpers\my_global_function')) {
    function my_global_function($param) {
        return 'Hello, ' . $param;
    }
}
