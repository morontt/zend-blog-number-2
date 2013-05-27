<?php

class Application_Model_Proxy_Tracking extends Application_Model_Tracking
{
    protected $_cache;

    public function init()
    {
        parent::init();

        $this->_cache = Zend_Registry::get('cache');
    }

    public function getTrackingData($period)
    {
        $key = 'tracking_data_' . $period;

        $cacheData = $this->_cache->load($key);
        if (!$cacheData) {
            $cacheData = parent::getTrackingData($period);
            $this->_cache->save($cacheData, $key);
        }

        return $cacheData;
    }

    public function getTrackingDataByArticle()
    {
        $cacheData = $this->_cache->load('article_tracking');
        if (!$cacheData) {
            $cacheData = parent::getTrackingDataByArticle();
            $this->_cache->save($cacheData, 'article_tracking');
        }

        return $cacheData;
    }
}