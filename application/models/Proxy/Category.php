<?php

class Application_Model_Proxy_Category extends Application_Model_Category
{
    protected $_cache;

    public function init()
    {
        parent::init();

        $this->_cache = Zend_Registry::get('cache');
    }

    public function getNotEmptyCategory()
    {
        $cacheData = $this->_cache->load('notEmptyCategory');
        if (!$cacheData) {
            $cacheData = parent::getNotEmptyCategory();
            $this->_cache->save($cacheData, 'notEmptyCategory');
        }

        return $cacheData;
    }
}

