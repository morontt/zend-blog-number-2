<?php

class Admin_TopicControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    public function testRetwittAction()
    {
        $params = array('action' => 'retwitt', 'controller' => 'Topic', 'module' => 'admin');
        $urlParams = $this->urlizeOptions($params);
        $url = $this->url($urlParams);
        $this->dispatch($url);

        // assertions
        $this->assertModule($urlParams['module']);
        $this->assertController($urlParams['controller']);
        $this->assertAction($urlParams['action']);
        $this->assertQueryContentContains(
            'div#view-content p',
            'View script for controller <b>' . $params['controller'] . '</b> and script/action name <b>' . $params['action'] . '</b>'
            );
    }
}

