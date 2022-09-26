<?php
return [
    'enable'  => true,
    'default' => 'local',
    'storage' => [
        'local'  => [
            'driver' => \yzh52521\Filesystem\Adapter\LocalAdapter::class,
            'root'   => public_path().'/storage',
            'url'    => '/storage'
        ],
        'memory' => [
            'driver' => \yzh52521\Filesystem\Adapter\MemoryAdapter::class,
        ],
        's3'     => [
            'driver'                  => \yzh52521\Filesystem\Adapter\S3Adapter::class,
            'key'                     => 'S3_KEY',
            'secret'                  => 'S3_SECRET',
            'region'                  => 'S3_REGION',
            'version'                 => 'latest',
            'bucket_endpoint'         => false,
            'use_path_style_endpoint' => false,
            'endpoint'                => 'S3_ENDPOINT',
            'bucket_name'             => 'S3_BUCKET',
        ],
        'oss'    => [
            'driver'        => \yzh52521\Filesystem\Adapter\AliyunAdapter::class,
            'access_id'     => 'OSS_ACCESS_ID',
            'access_secret' => 'OSS_ACCESS_SECRET',
            'bucket'        => 'OSS_BUCKET',
            'endpoint'      => 'OSS_ENDPOINT',
            'prefix'        => '',
            'isCName'       => false,
            'options'       => []
        ],
        'qiniu'  => [
            'driver'     => \yzh52521\Filesystem\Adapter\QiniuAdapter::class,
            'access_key' => 'QINIU_ACCESS_KEY',
            'secret_key' => 'QINIU_SECRET_KEY',
            'bucket'     => 'QINIU_BUCKET',
            'domain'     => 'QINBIU_DOMAIN',
        ],
        'cos'    => [
            'driver'        => \yzh52521\Filesystem\Adapter\CosAdapter::class,
            'region'        => 'COS_REGION',
            'app_id'        => 'COS_APPID',
            'secret_id'     => 'COS_SECRET_ID',
            'secret_key'    => 'COS_SECRET_KEY',
            // 可选，如果 bucket 为私有访问请打开此项
            // 'signed_url' => false,
            'bucket'        => 'COS_BUCKET',
            'prefix'        => '',
            'read_from_cdn' => false,
            // 'timeout' => 60,
            // 'connect_timeout' => 60,
            // 'cdn' => '',
            // 'scheme' => 'https',
        ]
    ],
];
