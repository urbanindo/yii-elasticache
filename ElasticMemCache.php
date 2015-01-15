<?php

/**
 * ElasticMemCache class file.
 * 
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */

namespace Urbanindo\Yii\Component\Cache;

/**
 * Auto discovery of ElastiCache Node.
 *
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */
class ElasticMemCache extends \CMemCache
{
    private $_cache = null;

    public $cacheTime = 60;
    private $_serverConfigs = array();
    private $_servers = array();

    public function setCache($config) {
        if ($config) {
            $this->_cache = \Yii::createComponent($config);
        }
    }

    public function getCache() {
        return $this->_cache;
    }

    public function setServerConfigs($configs) {
        foreach ($configs as $config) {
            if (!$config['host']) {
                throw new \Exception('Host configuration need to be set.');
            }
        }
        $this->_serverConfigs = $configs;
    }

    public function getServerConfigs() {
        return $this->_serverConfigs;
    }

    public function init()
    {
        $this->setServers($this->loadNodes());
        parent::init();
    }

    public function loadNodes()
    {
        try {
            $cacheable = null != $this->getCache();
            if ($cacheable) {
                $cachedConfig = $this->getCache()->get('clusters');
            }
            if (!$cacheable || !$cachedConfig) {
                $servers = $this->getServerConfigs();
                $cachedConfig = [];
                foreach ($servers as $server) {
                    $fp = fsockopen($server['host'], $server['port']);
                    fwrite($fp, "config get cluster\r\n");
                    $raw = '';
                    while(substr($raw,-5,3)!=='END'){
                         $raw .= fgets($fp, 1024);
                    }
                    fclose($fp);
                    $cachedConfig = array_merge($cachedConfig,$this->createConfigs($raw, $server));
                }
                if ($cacheable) {
                    $this->getCache()->set('clusters', $cachedConfig, $cacheTime);
                }
            }
            return $cachedConfig;
        } catch (\Exception $ex) {
            \Yii::log("unable to retrieve cluster configuration because of {$ex->getMessage()}. Defaults to server configuration", \CLogger::LEVEL_WARNING);
            return  $this->getServerConfigs();
        }
    }

    public function createConfigs($response, $parentConfig) {
        $allConfigs = [];
        $configs = explode("\n",$response)[2];
        $configs = preg_split ("/\s+/", $configs);
        foreach ($configs as $config) {
            $config = explode('|', $config);
            $copyConfig = [];
            foreach ($parentConfig as $key => $value) {
                $copyConfig[$key] = $value;
            }
            $copyConfig['host'] = $config[0];
            $copyConfig['port'] = $config[2];
            $allConfigs[] = $copyConfig;
        }
        return $allConfigs;
    }
}
