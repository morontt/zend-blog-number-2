<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initCache()
    {
        $options = $this->getOptions();

        $frontendOptions = array(
            'lifetime'                => $options['cache']['lifetime'],
            'automatic_serialization' => true,
            'caching'                 => $options['cache']['caching'],
            'cache_id_prefix'         => 'zb2_',
        );

        if ($options['cache']['type'] == "apc") {
            $backendType = 'Apc';
            $backendOptions = array();
        } else {
            $backendType = 'File';
            $backendOptions = array(
                'cache_dir'         => realpath(__DIR__ . '/../cache'),
                'read_control_type' => 'adler32',
            );
        }

        $cache = Zend_Cache::factory('Core', $backendType, $frontendOptions, $backendOptions);
        Zend_Registry::set('cache', $cache);

        $cacheOutput = Zend_Cache::factory('Output', $backendType, $frontendOptions, $backendOptions);
        Zend_Registry::set('cacheOutput', $cacheOutput);

        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
    }

    protected function _initLayoute()
    {
        $layout = new Zend_Layout();
        $config = $this->getOptions();

        $layout->title = $config['blog']['title'];

        return $layout;
    }

    protected function _initLog()
    {
        if (in_array(getenv('APPLICATION_ENV'), array('development', 'testing'))) {
            $writer = new Zend_Log_Writer_Firebug();
        } else {
            $writer = new Zend_Log_Writer_Null();
        }
        $logger = new Zend_Log($writer);

        Zend_Registry::set('logger', $logger);

        //using
        //$logger = Zend_Registry::get('logger');
        //$logger->log('bla-bla-bla', Zend_Log::INFO);
    }

    protected function _initRouters()
    {
        $controller = Zend_Controller_Front::getInstance();
        $router = $controller->getRouter();

        $routeConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'routes');
        $router->addConfig($routeConfig, 'routes');

        $router->addRoute('blog-page', new Zend_Controller_Router_Route(
            '/:page',
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'index',
                'page'       => 1,
            ),
            array(
                'page' => '\d+',
            )
        ));

        $router->addRoute('category-page', new Zend_Controller_Router_Route(
            'category/:url/:page',
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'category',
                'page'       => 1,
            ),
            array(
                'page' => '\d+',
            )
        ));

        $router->addRoute('tag-page', new Zend_Controller_Router_Route(
            'tag/:url/:page',
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'tag',
                'page'       => 1,
            ),
            array(
                'page' => '\d+',
            )
        ));

        $controller->setRouter($router);

        $request = new Zend_Controller_Request_Http();
        $router->route($request);
        Zend_Registry::set('requestParams', $request->getParams());
    }

    protected function _initOptions()
    {
        $options = $this->getOptions();
        Zend_Registry::set('options', $options);

        define('IMG_DOMAIN', $options['img_domain']);
        define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);
    }
}

