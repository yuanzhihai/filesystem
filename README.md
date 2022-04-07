# 安装

```
composer require yzh52521/filesystem
```

配置文件在 config/plugin/yzh52521/filesystem 下

```
return [
    'default' => 'local',
    'storage' => [
        'local' => [
            'driver' => \yzh52521\Filesystem\Adapter\LocalAdapter::class,
            'root' => public_path(),
        ],
        'memory' => [
            'driver' => \yzh52521\Filesystem\Adapter\MemoryAdapter::class,
        ],
        's3' => [
            'driver' => \yzh52521\Filesystem\Adapter\S3Adapter::class,
            'credentials' => [
                'key' => 'S3_KEY',
                'secret' => 'S3_SECRET',
            ],
            'region' => 'S3_REGION',
            'version' => 'latest',
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => false,
            'endpoint' => 'S3_ENDPOINT',
            'bucket_name' => 'S3_BUCKET',
        ],
      
        'oss' => [
            'driver' => \yzh52521\Filesystem\Adapter\AliyunAdapter::class,
            'accessId' => 'OSS_ACCESS_ID',
            'accessSecret' => 'OSS_ACCESS_SECRET',
            'bucket' => 'OSS_BUCKET',
            'endpoint' => 'OSS_ENDPOINT',
            // 'timeout' => 3600,
            // 'connectTimeout' => 10,
            // 'isCName' => false,
            // 'token' => null,
            // 'proxy' => null,
        ],
        'qiniu' => [
            'driver' => \yzh52521\Filesystem\Adapter\QiniuAdapter::class,
            'accessKey' => 'QINIU_ACCESS_KEY',
            'secretKey' => 'QINIU_SECRET_KEY',
            'bucket' => 'QINIU_BUCKET',
            'domain' => 'QINBIU_DOMAIN',
        ],
        'cos' => [
            'driver' => \yzh52521\Filesystem\Adapter\CosAdapter::class,
            'region' => 'COS_REGION',
            'app_id' => 'COS_APPID',
            'secret_id' => 'COS_SECRET_ID',
            'secret_key' => 'COS_SECRET_KEY',
            // 可选，如果 bucket 为私有访问请打开此项
            // 'signed_url' => false,
            'bucket' => 'COS_BUCKET',
            'read_from_cdn' => false,
            // 'timeout' => 60,
            // 'connect_timeout' => 60,
            // 'cdn' => '',
            // 'scheme' => 'https',
        ],
        'obs'    => [
            'driver'     => \yzh52521\Filesystem\Adapter\ObsAdapter::class,
            'key'        => 'OBS_ACCESS_ID',
            // <Your Huawei OBS AccessKeyId>
            'secret'     => 'OBS_ACCESS_KEY',
            // <Your Huawei OBS AccessKeySecret>
            'bucket'     => 'OBS_BUCKET',
            // <OBS bucket name>
            'endpoint'   => 'OBS_ENDPOINT',
            // <the endpoint of OBS, E.g: (https:// or http://).obs.cn-east-2.myhuaweicloud.com | custom domain, E.g:img.abc.com> OBS 外网节点或自定义外部域名
            'cdn_domain' => 'OBS_CDN_DOMAIN',
            //<CDN domain, cdn域名> 如果isCName为true, getUrl会判断cdnDomain是否设定来决定返回的url，如果cdnDomain未设置，则使用endpoint来生成url，否则使用cdn
            'ssl_verify' => 'OBS_SSL_VERIFY',
            // <true|false> true to use 'https://' and false to use 'http://'. default is false,
            'debug'      => 'APP_DEBUG',
            // <true|false>
        ],
    ],
];
```


- 阿里云 OSS 适配器

```
composer require "yzh52521/flysystem-oss:^2.0"
```
- S3 适配器

```
composer require "league/flysystem-aws-s3-v3:^2.0"
```
- 七牛云适配器

```
composer require "overtrue/flysystem-qiniu:^2.0"
```
- 内存适配器

```
composer require "league/flysystem-memory:^2.0"
```
- 腾讯云 COS 适配器

```
composer require "overtrue/flysystem-cos:^4.0"
```

- 华为云 OBS 适配器

```
composer require "yzh52521/flysystem-obs:^2.0"
```
# 使用

通过Filesystem::disk('local') 来调用不同的适配器


# 使用
```
 use yzh52521\Filesystem\Facade\Filesystem;
        public function upload(\support\Request $request){
            $file = $request->file('file');
            $result = Filesystem::putFile('webman',$file);
            //文件判断
           
            try {
                    validate(
                        [
                            'image' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 4 * 1024 * 1000,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png,jpeg'
                            ]
                        ]
                    )->check(['image' => $file]);
                $path = Filesystem::disk('local')->putFile('webman',$file);
             }catch (\Exception $e){
                print($e->getMessage());
             }
            //获取上传文件
            $fileUrl = Filesystem::url($path);    
            
            //指定选定器外网
            $fileUrl = Filesystem::disk('oss')->url($path); 
         }
           
             
```

>用例中使用了 validate 验证器
 
 安装 validate
```
 composer require "yzh52521/webman-validate"
 ```


###静态方法（可单独设定）
| 方法      | 描述            | 默认                 |
|---------|---------------|--------------------|
| disk | 选定器           | config中配置的default  | 
| url    | 获取文件访问地址         |  |
| putFile  | 保存文件      |   |
| putFileAs  | 指定文件名保存文件      |   |
| put   | 保存文件      |   |

