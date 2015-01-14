# yii-elasticache
Yii1 Cache component for elasticache without installing Amazon Auto Discovery plugin


```
'components' => [

     [
      'class' => '\Urbanindo\Yii\Component\Cache\ElasticMemCache',
      'keyPrefix' => 'prefix',
      'hashKey' => false,
      'servers' => [
         [
           'host' => 'urbanindo.g4eo3j.cfg.apse1.cache.amazonaws.com',
           'port' => 11211,
         ]
      ],
      'cache' => [
         'class' => 'CFileCache',
         'cachePath' => '/tmp/',
         'embedExpiry' => true,
      ]
    ]
]
```