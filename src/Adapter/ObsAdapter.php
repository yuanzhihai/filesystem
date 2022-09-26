<?php

declare( strict_types = 1 );

namespace yzh52521\Filesystem\Adapter;

use Obs\ObsClient;
use yzh52521\Filesystem\Contract\AdapterInterface;

class ObsAdapter implements AdapterInterface
{
    public function createAdapter(array $options)
    {
        $config            = [
            'key'      => $options['key'],
            'secret'   => $options['secret'],
            'bucket'   => $options['bucket'],
            'endpoint' => $options['endpoint'],
        ];
        $client            = new ObsClient( $config );
        $config['options'] = [
            'url'             => '',
            'endpoint'        => $options['endpoint'],
            'bucket_endpoint' => '',
            'temporary_url'   => '',
        ];
        return new \yzh52521\Flysystem\Obs\ObsAdapter( $client,$options['bucket'],$options['prefix'],null,null,$config['options'] );
    }
}