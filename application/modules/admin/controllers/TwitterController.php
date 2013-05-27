<?php

class Admin_TwitterController extends Zend_Controller_Action
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

    }

    public function authAction()
    {
        $options  = Zend_Registry::get('options');
        $consumer = new Zend_Oauth_Consumer($options['twitter']);

        $session = new Zend_Session_Namespace('twitter');
        $session->requestToken = serialize($consumer->getRequestToken());

        $consumer->redirect();
    }

    public function callbackAction()
    {
        $options = Zend_Registry::get('options');

        $consumer = new Zend_Oauth_Consumer($options['twitter']);
        $session  = new Zend_Session_Namespace('twitter');

        $params = $this->_request->getParams();
        if (!empty($params) && !empty($session->requestToken)) {
            $token = $consumer->getAccessToken($this->_request->getParams(), unserialize($session->requestToken));
            $tokenString = serialize($token);

            $sysParameters = new Application_Model_SysParameters();
            $sysParameters->saveOption('twitter_token', $tokenString);

            unset($session->requestToken);

            return $this->_helper->redirector('index');
        } else {
            throw new Exception('Invalid access. No token provided.');
        }
    }

    public function testAction()
    {
        $sysParameters = new Application_Model_SysParameters();
        $accessToken   = $sysParameters->getOption('twitter_token');

        try {

            if (empty($accessToken)) {
                throw new Exception('You are not logged in. Please, try again.');
            }

            $token = unserialize($accessToken);
            $options = Zend_Registry::get('options');

            $config = $options['twitter'];
            $config['username'] = $token->getParam('screen_name');
            $config['accessToken'] = $token;

            $config['oauthOptions']['consumerKey'] = $config['consumerKey'];
            $config['oauthOptions']['consumerSecret'] = $config['consumerSecret'];

            $twitter = new Zend_Service_Twitter($config);

            $response = $twitter->account->verifyCredentials();

            if (!$response || !empty($response->error)) {
                throw new Exception('Wrong credentials. Please, try to login again.');
            }

            Zend_Debug::dump($twitter->statuses->userTimeLine(array('count' => 10)));
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}