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
    private $_cache;

    public function setCache($config) {
        $this->_cache = \Yii::createComponent($config);
    }
    public function getCache() {
        return $_cache;
    }

    public function getServers()
    {
        $cachedConfig = $this->getCache()->get('clusters');
        if (!$cachedConfig) {
            $servers = parent::getServers();
            foreach ($servers as $server) {
                $fp = fsockopen(, 11211);
                fwrite($fp, "config get cluster\r\n");
                $raw = '';
                while(!feof($fp)){
                     $raw .= fgets($fp, 128);
                }

            }
            $$cachedConfig = $this->createConfigs($memcache->get("config get cluster"));
            $this->getCache()->set('clusters', $cachedConfig, 60);
        }
        return $cachedConfig;
    }

    public function createConfigs($response, $parentConfig) {
        $allConfigs = [];
        $configs = explode("\n",$response)[2];
        $configs = preg_split ("/\s+/", $configs);
        foreach ($configs as $config) {
            $copyConfig = new \CMemCacheServerConfiguration((array) $parentConfig);
            $config = explode('|', $config);
            $copyConfig->host = $config[0];
            $copyConfig->port = (int) $config[2];
            $allConfigs[] = $copyConfig;
        }
        return $allConfigs;
    }
}
