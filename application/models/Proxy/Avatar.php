<?php

class Application_Model_Proxy_Avatar extends Application_Model_Avatar
{
    protected $_cache;

    public function init()
    {
        parent::init();

        $this->_cache = Zend_Registry::get('cache');
    }

    /**
     *
     * @param string $hash
     * @param int $default
     * @return array
     */
    public function getByHash($hash, $default)
    {
        $cacheKey = 'avatar_' . $hash;

        $cacheData = $this->_cache->load($cacheKey);
        if (!$cacheData) {
            $cacheData = parent::getByHash($hash, $default);
            $this->_cache->save($cacheData, $cacheKey);
        }

        return $cacheData;
    }
}