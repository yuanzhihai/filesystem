<?php

namespace yzh52521\Filesystem;

use yzh52521\Filesystem\Contract\AdapterInterface;
use League\Flysystem\Config;
use support\Container;
use Webman\File;

class Filesystem
{

    protected $config = [];
    protected $size = 1024 * 1024 * 10;
    protected $exts = []; //允许上传文件类型
    protected $adapterName = '';

    /** @var Filesystem */
    protected $filesystem;

    public function __construct()
    {
        $this->config      = config('plugin.yzh52521.filesystem.app');
        $this->adapterName = $this->config['default'] ?? 'local';
        $this->size        = $this->config['size'] ?? 1024 * 1024 * 10;
        $this->exts        = $this->config['exts'] ?? [];
        $this->filesystem  = $this->createFilesystem();
    }


    /**
     * 存储路径
     * @param string $name
     * @return $this
     */
    public function disk(string $name)
    {
        $this->adapterName = $name;
        $this->filesystem  = $this->createFilesystem();
        return $this;
    }

    /**
     * 允许上传文件类型
     * @param array $ext
     * @return $this
     */
    public function exts(array $ext)
    {
        $this->exts = $ext;
        return $this;
    }

    /**
     * 设置允许文件大小
     * @param int $size
     * @return $this
     */
    public function size(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return \League\Flysystem\Filesystem
     * @throws \Exception
     */
    protected function createFilesystem()
    {
        $adapter = $this->createAdapter($this->config, $this->adapterName);
        return new \League\Flysystem\Filesystem($adapter, $this->config['storage'][$this->adapterName] ?? []);
    }


    /**
     * @param $config
     * @param $adapter
     * @return mixed
     * @throws \Exception
     */
    protected function createAdapter($config, $adapter)
    {
        if (!$config['storage'] || !$config['storage'][$adapter]) {
            throw new \Exception("file configurations are missing {$adapter} options");
        }
        /** @var AdapterInterface $driver */
        $driver = Container::get($config['storage'][$adapter]['driver']);
        return $driver->createAdapter($config['storage'][$adapter]);
    }


    /**
     *
     * 保存文件
     * @param string $path 路径
     * @param File $file
     * @param array $options 参数
     * @return false|string
     */
    public function putFile(string $path,File $file, array $options = [])
    {
        return $this->putFileAs($path, $file, $this->hashName($file), $options);
    }

    /**
     * 指定文件名保存文件
     * @param string $path
     * @param File $file
     * @param string $filename
     * @param array $options
     * @return false|string
     */
    public function putFileAs(string $path,File $file, string $filename, array $options = [])
    {
        if (!empty($this->exts) && !in_array($file->getUploadMineType(), $this->exts)) {
            throw new \Exception('不允许上传文件类型' . $file->getUploadMineType());
        }
        if ($file->getSize() > $this->size) {
            throw new \Exception("上传文件超过限制");
        }
        $stream = fopen($file->getRealPath(), 'rb+');
        $path   = trim($path . '/' . $filename, '/');
        $result = $this->filesystem->writeStream($path, $stream, $options);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $path;
    }

    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }

    /**
     * 获取保存文件地址
     * @param string $path
     * @return string
     */
    public function url(string $path): string
    {
        if (isset($this->config['storage'][$this->adapterName]['url'])) {
            return $this->concatPathToUrl($this->config['storage'][$this->adapterName]['url'], $path);
        } else {
            if (!method_exists($this->filesystem, 'getUrl')) {
                throw new \RuntimeException('This driver does not support retrieving URLs.');
            }
            return $this->filesystem->getUrl($path);
        }

    }


    /**
     * 自动生成文件名
     * @access private
     * @param File $file
     * @return string
     */
    private function hashName(File $file): string
    {
        return date('Ymd') . DIRECTORY_SEPARATOR .hash_file('md5', $file->getPathname()).'.' . $file->getUploadExtension();
    }


    public function __call($method, $args)
    {
        return $this->filesystem->$method(...$args);
    }
}