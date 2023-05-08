<?php
declare(strict_types=1);

namespace PhpRest2;

if (! function_exists( 'PhpRest2\dump' )) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump(...$vars): void
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
    }
}

if (! function_exists( 'PhpRest2\uncamelize' )) {
    /**
     * 驼峰转下划线
     */
    function uncamelize(string $str, string $separator = '_'): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $str));
    }
}

if (! function_exists( 'PhpRest2\camelize' )) {
    /**
     * 下划线转驼峰
     */
    function camelize(string $str, string $separator = '_'): string
    {
        $str = $separator. str_replace($separator, ' ', strtolower($str));
        return ltrim(str_replace(' ', '', ucwords($str)), $separator);
    }
}

if (! function_exists( 'PhpRest2\isAssocArray' )) {
    /**
     * 判断是否为关联数组
     * @param array $ary
     * @return bool
     */
    function isAssocArray(mixed $ary): bool
    {
        if (is_array($ary) === false) return false;
        if (count($ary) <= 0) return false;
        return array_keys($ary) !== range(0, count($ary) - 1);
    }
}