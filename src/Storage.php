<?php

namespace yzh52521\Filesystem;

class Storage
{
    protected $config = [];
    protected $size = 1024 * 1024 * 10;
    protected $exts = []; //允许上传文件类型
    protected $adapter = '';
    protected $filesystem = null;


    public function __construct()
    {
        $this->config  = config('plugin.yzh52521.filesystem.app');
        $this->adapter = $this->config['default'] ?? 'local';
        $this->size    = $this->config['size'] ?? 1024 * 1024 * 10;
        $this->exts    = $this->config['exts'] ?? [];
    }

    /**
     * 存储路径
     * @param string $name
     * @return $this
     */
    public function disk(string $name)
    {
        $this->adapter = $name;
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
     *
     * 保存文件
     * @param string $path 路径
     * @param $file 文件
     * @param array $options 参数
     * @return false|string
     */
    public function putFile(string $path, $file, array $options = [])
    {
        return $this->putFileAs($path, $file, $this->hashName($file), $options);
    }

    /**
     * 指定文件名保存文件
     * @param string $path
     * @param $file
     * @param string $filename
     * @param array $options
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
        $this->filesystem = Filesystem::storage($this->adapter);
        $stream           = fopen($file->getRealPath(), 'rb+');
        $path             = trim($path . '/' . $filename, '/');
        $result           = $this->filesystem->writeStream($path, $stream, $options);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $path;
    }


    protected function concatPathToUrl($url, $path)
    {
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }

    public function url(string $path): string
    {
        if (isset($this->config['storage'][$this->adapter]['url'])) {
            return $this->concatPathToUrl($this->config['storage'][$this->adapter]['url'], $path);
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
     * @param  $file
     * @return string
     */
    private function hashName($file): string
    {
        return date('Ymd') . DIRECTORY_SEPARATOR . md5(microtime(true) . $file->getPathname()) . '.' . $file->getUploadExtension();
    }

    public function __call($method, $parameters)
    {
        return $this->filesystem->$method(...$parameters);
    }

}