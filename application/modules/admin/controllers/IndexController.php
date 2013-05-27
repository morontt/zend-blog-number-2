<?php

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout->setLayout('admin');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect($this->view->url(array(), 'login'));
        }
    }

    public function indexAction()
    {
        // action body
    }

    public function clearcacheAction()
    {
        $cacheOutput = Zend_Registry::get('cacheOutput');
        $cacheOutput->clean(Zend_Cache::CLEANING_MODE_ALL);

        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);

        $this->redirect($this->view->url(array(), 'admin'));
    }

}
