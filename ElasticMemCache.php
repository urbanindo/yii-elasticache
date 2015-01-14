<?php

/**
 * Fluentd Log Route class file.
 * 
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */

namespace Urbanindo\Yii\Component\Cache;

/**
 * Log route using fluentd.
 *
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */
class ElasticMemCache extends \CMemCache
{
    private $_cache = null;

    public function setCache($config) {
        if ($config) {
            $this->_cache = \Yii::createComponent($config);
        }
    }
    public function getCache() {
        return $this->_cache;
    }

    public function getServers()
    {
        $cacheable = null != $this->getCache();
        if ($cacheable) {
            $cachedConfig = $this->getCache()->get('clusters');
        }
        if (!$cacheable || !$cachedConfig){
            $servers = parent::getServers();
            foreach ($servers as $server) {
                $fp = fsockopen($server->host, $server->port);
                fwrite($fp, "config get cluster\r\n");
                $raw = '';
                while(substr($raw,-5,3)!=='END'){
                     $raw .= fgets($fp, 1024);
                }
                $cachedConfig = $this->createConfigs($raw, $server);
            }
            if ($cacheable) {
                $this->getCache()->set('clusters', $cachedConfig, 60);
            }
        }
        return $cachedConfig;
    }

    public function createConfigs($response, $parentConfig) {
        $allConfigs = [];
        $configs = explode("\n",$response)[2];
        $configs = preg_split ("/\s+/", $configs);
        $parentConfig = get_object_vars( $parentConfig );
        foreach ($configs as $config) {
            $config = explode('|', $config);
            $parentConfig['host'] = $config[0];
            $parentConfig['port'] = $config[2];
            print_r($parentConfig);
            $copyConfig = new \CMemCacheServerConfiguration($parentConfig);
            $allConfigs[] = $copyConfig;
        }
        return $allConfigs;
    }
}
