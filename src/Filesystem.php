<?php

namespace yzh52521\Filesystem;

use yzh52521\Filesystem\Adapter\LocalAdapter;
use yzh52521\Filesystem\Contract\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\Filesystem as FilesystemBase;
use support\Container;

class Filesystem
{
    protected static $_instance = null;

    public static function instance()
    {
        return static::$_instance;
    }

    public static function storage($adapterName = null): FilesystemBase
    {
        if (!static::$_instance) {
            $options           = config('plugin.yzh52521.filesystem.app', [
                'default' => 'local',
                'storage' => [
                    'local' => [
                        'driver' => LocalAdapter::class,
                        'root'   => \public_path(),
                    ],
                ],
            ]);
            $adapter           = static::getAdapter($options, $adapterName);
            static::$_instance = new FilesystemBase($adapter, $options['storage'][$adapterName] ?? []);
        }
        return static::$_instance;
    }


    public static function getAdapter($options, $adapterName)
    {
        if (!$options['storage'] || !$options['storage'][$adapterName]) {
            throw new \Exception("file configurations are missing {$adapterName} options");
        }
        /** @var AdapterInterface $driver */
        $driver = Container::get($options['storage'][$adapterName]['driver']);
        return $driver->createAdapter($options['storage'][$adapterName]);
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
