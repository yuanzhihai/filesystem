<?php

declare(strict_types=1);

namespace yzh52521\Filesystem\Adapter;

use yzh52521\Filesystem\Contract\AdapterInterface;

class QiniuAdapter implements AdapterInterface
{
    public function createAdapter(array $options)
    {
        return new \Overtrue\Flysystem\Qiniu\QiniuAdapter(
            $options['access_key'],
            $options['secret_key'],
            $options['bucket'],
            $options['domain']
        );
    }
}
