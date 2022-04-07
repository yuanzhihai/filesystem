<?php

namespace yzh52521\Filesystem\Facade;

/**
 * @see \yzh52521\Filesystem\Filesystem
 * @mixin \yzh52521\Filesystem\Filesystem
 * @method \yzh52521\Filesystem\Filesystem disk(string $name) static 设置选定器
 * @method size(string $size) static 允许单文件大小
 * @method exts(array $ext) static 允许上传文件类型
 * @method url(string $path) static 获取文件访问地址
 * @method put(string $path, $content, array $options) static 保存文件
 * @method putFile(string $path, $file, array $options) static 保存文件
 * @method putFileAs($path, $file, string $filename, array $options) static 指定文件名保存文件
 * @method delete($paths) static 删除文件
 * @method allFiles(string $directory) static 获取目录下所有的文件
 * @method files(string $directory) static 获取目录下所有的文件
 */
class Filesystem
{
    protected static $_instance = null;


    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new \yzh52521\Filesystem\Filesystem();
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