<?php

namespace yzh52521\Filesystem;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
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
    /**
     * The Flysystem adapter implementation.
     */
    protected $adapter;

    /** @var Filesystem */
    protected $driver;

    public function __construct()
    {
        $this->config      = config('plugin.yzh52521.filesystem.app');
        $this->adapterName = $this->config['default'] ?? 'local';
        $this->size        = $this->config['size'] ?? 1024 * 1024 * 10;
        $this->exts        = $this->config['exts'] ?? [];
        $this->driver      = $this->createFilesystem();
    }


    /**
     * 存储路径
     * @param string $name
     * @return $this
     */
    public function disk(string $name)
    {
        $this->adapterName = $name;
        $this->driver      = $this->createFilesystem();
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
     * Determine if a file or directory exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        return $this->driver->has($path);
    }

    /**
     * Determine if a file or directory is missing.
     *
     * @param string $path
     * @return bool
     */
    public function missing($path)
    {
        return !$this->exists($path);
    }

    /**
     * Determine if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function fileExists($path)
    {
        return $this->driver->fileExists($path);
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
        $this->adapter = $this->createAdapter($this->config, $this->adapterName);
        return new \League\Flysystem\Filesystem($this->adapter, $this->config['storage'][$this->adapterName] ?? []);
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
     * Write the contents of a file.
     *
     * @param string $path
     * @param File|string|resource $contents
     * @param array $options
     * @return string|bool
     */
    public function put(string $path, $contents, array $options = [])
    {
        if ($contents instanceof File) {
            return $this->putFile($path, $contents, $options);
        }
        try {
            is_resource($contents)
                ? $this->driver->writeStream($path, $contents, $options)
                : $this->driver->write($path, $contents, $options);
        } catch (\Throwable $e) {
            throw new \RuntimeException($e);
            return false;
        }
        return true;
    }


    /**
     *
     * 保存文件
     * @param string $path 路径
     * @param File|string $file 文件
     * @param array $options 参数
     * @return false|string
     */
    public function putFile(string $path, $file, array $options = [])
    {
        $file = is_string($file) ? new File($file) : $file;

        return $this->putFileAs($path, $file, $this->hashName($file), $options);
    }

    /**
     * 指定文件名保存文件
     * @param string $path 路径
     * @param File|string $file 文件
     * @param string $filename 文件名
     * @param array $options 参数
     * @return false|string
     */
    public function putFileAs(string $path, $file, string $filename, array $options = [])
    {
        if (!empty($this->exts) && !in_array($file->getUploadMineType(), $this->exts)) {
            throw new \Exception('不允许上传文件类型' . $file->getUploadMineType());
        }
        if ($file->getSize() > $this->size) {
            throw new \Exception("上传文件超过限制");
        }
        $stream = fopen(is_string($file) ? $file : $file->getRealPath(), 'r');

        $result = $this->put(
            $path = trim($path . '/' . $filename, '/'), $stream, $options
        );

        if (is_resource($stream)) {
            fclose($stream);
        }
        return $result ? $path : false;
    }

    protected function getLocalUrl($path)
    {
        if (isset($this->config['storage'][$this->adapterName]['url'])) {
            return $this->concatPathToUrl($this->config['storage'][$this->adapterName]['url'], $path);
        }
        $path = '/storage/' . $path;
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
        $adapter = $this->adapter;
        if (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        } elseif (method_exists($this->driver, 'getUrl')) {
            return $this->driver->getUrl($path);
        } elseif ($adapter instanceof LocalFilesystemAdapter) {
            return $this->getLocalUrl($path);
        } else {
            throw new \RuntimeException('This driver does not support retrieving URLs.');
        }
    }


    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                $this->driver->delete($path);
            } catch (\RuntimeException $e) {
                throw new \RuntimeException($e);
                $success = false;
            }
        }

        return $success;
    }


    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string|null $directory
     * @return array
     */
    public function allFiles(string $directory = null): array
    {
        return $this->files($directory, true);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        return $this->driver->listContents($directory ?? '', $recursive)
            ->filter(function (StorageAttributes $attributes) {
                return $attributes->isFile();
            })
            ->map(function (StorageAttributes $attributes) {
                return $attributes->path();
            })
            ->toArray();
    }


    /**
     * 自动生成文件名
     * @access private
     * @param File $file
     * @return string
     */
    private function hashName(File $file): string
    {
        return date('Ymd') . DIRECTORY_SEPARATOR . hash_file('md5', $file->getPathname()) . '.' . $file->getUploadExtension();
    }


    public function __call($method, $args)
    {
        return $this->driver->{$method}(...$args);
    }
}