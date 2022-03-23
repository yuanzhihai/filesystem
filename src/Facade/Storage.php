<?php

namespace yzh52521\Filesystem\Facade;

/**
 * @see \yzh52521\Filesystem\Storage
 * @mixin \yzh52521\Filesystem\Storage
 * @method \yzh52521\Filesystem\Storage disk(string $name) static 设置选定器
 * @method string size(string $size) static 允许单文件大小
 * @method string exts(array $ext) static 允许上传文件类型
 * @method string url(string $path) static 获取文件访问地址
 * @method object putFile(string $path, $file, $options) static 保存文件
 * @method object putFileAs($path, string $file, $filename, $options) static 指定文件名保存文件
 */
class Storage
{
    protected static $_instance = null;


    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new \yzh52521\Filesystem\Storage();
        }
        return static::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }
}