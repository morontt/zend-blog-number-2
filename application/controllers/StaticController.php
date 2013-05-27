<?php

class StaticController extends Zend_Controller_Action
{

    public function init()
    {
        $this->view->noHomePage = true;
    }

    public function indexAction()
    {
        // action body
    }

    public function displayAction()
    {
        $pageName = $this->_request->getParam('pagename');

        $scriptPathArray = $this->view->getScriptPaths();
        $path = $scriptPathArray[0] . "static";
        $file = realpath($path) . DIRECTORY_SEPARATOR . $pageName . '.' . $this->viewSuffix;

        if (file_exists($file)) {
            $this->render($pageName);
        } else {
            throw new Zend_Controller_Action_Exception('Page not found', 404);
        }

        if ($pageName == 'info') {
            $this->view->browsertitle = 'Информация';
        }
    }
}

