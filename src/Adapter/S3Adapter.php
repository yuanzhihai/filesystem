<?php
declare(strict_types=1);

namespace yzh52521\Filesystem\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use yzh52521\Filesystem\Contract\AdapterInterface;

class S3Adapter implements AdapterInterface
{
    public function createAdapter(array $options)
    {
        $client  = new S3Client($options);
        return new AwsS3V3Adapter($client, $options['bucket_name'], '');
    }
}
