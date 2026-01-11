<?php
declare(strict_types=1);

namespace PhpRest2\Utils;

final class EnvHelper
{
    private static array $config = [];

    /**
     * 加载配置文件
     * @access public
     * @param string $filePath 配置文件路径
     * @return void
     */
    public static function loadFile(string $filePath) : void
    {
        self::$config = parse_ini_file($filePath, true);
    }

    /**
     * 获取环境变量值
     * @access public
     * @param string $name 环境变量名（支持二级 . 号分割）
     * @param string $default 默认值
     * @return string|array
     */
    public static function get(string $name, string $default = ''): string|array
    {
        $config = self::$config;
        $keys = explode('.', $name);
        foreach ($keys as $key) {
            if (array_key_exists($key, $config)) {
                $config = $config[$key];
            } else {
                return $default;
            }
        }
        return $config;
    }
}
