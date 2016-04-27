<?php

class Admin_TopicController extends Zend_Controller_Action
{

    protected $_flashMessenger = null;

    public function init()
    {
        $this->_helper->layout->setLayout('admin');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect($this->view->url(array(), 'login'));
        }

        $this->_flashMessenger = $this->_helper->FlashMessenger;
    }

    public function indexAction()
    {
        $page = $this->_getParam('page');

        $topics = new Application_Model_Posts();

        $paginator = $topics->getAllPosts();

        $paginator->setItemCountPerPage(30);
        $paginator->SetCurrentPageNumber($page);

        $this->view->messages = $this->_flashMessenger->getMessages();

        $this->view->paginator = $paginator;
    }

    public function createAction()
    {
        $form = new Admin_Form_Topic();
        $form->submit->setLabel('Создать запись');
        $this->view->form = $form;

        $tags = new Application_Model_Tags();
        $this->view->availableTags = $tags->getAvailableTags();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $form->getValues();
                if (empty($formData['title'])) {
                    $formData['title'] = 'no subject';
                }

                $topic = new Application_Model_Posts();
                $topic->createNewTopic($formData);

                $this->_flashMessenger->addMessage('Запись создана');

                $this->clearCache();

                $this->redirect($this->view->url(array(), 'admin_topic'));
            }
        }
    }

    public function editAction()
    {
        $id = (int)$this->_getParam('id');

        $topic = new Application_Model_Posts();

        $form = new Admin_Form_Topic();
        $form->submit->setLabel('Изменить запись');
        $this->view->form = $form;
        $this->view->topicId = $id;

        $tags = new Application_Model_Tags();
        $this->view->availableTags = $tags->getAvailableTags();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $form->getValues();
                if (empty($formData['title'])) {
                    $formData['title'] = 'no subject';
                }

                $topic->editTopic($formData, $id);

                $this->_flashMessenger->addMessage('Запись отредактирована');

                $this->clearCache();
            }
        } else {
            $data = $topic->getTopicDataForEdit($id);

            $form->populate($data);
        }
    }

    public function deleteAction()
    {
        // action body
    }

    protected function clearCache()
    {
        $cacheOutput = Zend_Registry::get('cacheOutput');
        $cacheOutput->clean(Zend_Cache::CLEANING_MODE_ALL);

        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    public function retwittAction()
    {
        $id = (int)$this->_getParam('id');

        $topicTable = new Application_Model_Posts();
        $topic = $topicTable->getPostById($id);

        $sysParameters = new Application_Model_SysParameters();
        $accessToken = $sysParameters->getOption('twitter_token');

        $twitterBlock = false;
        if (!empty($accessToken)) {
            $token = unserialize($accessToken);

            $options = Zend_Registry::get('options');

            $config = $options['twitter'];
            $config['username'] = $token->getParam('screen_name');
            $config['accessToken'] = $token;

            $config['oauthOptions']['consumerKey'] = $config['consumerKey'];
            $config['oauthOptions']['consumerSecret'] = $config['consumerSecret'];

            $twitter = new Zend_Service_Twitter($config);

            $response = $twitter->account->verifyCredentials();
            if ($response && empty($response->error)) {
                $twitterBlock = true;

                $longUrl = BASE_URL . $this->view->url(array('url' => $topic->url), 'topic_url');
                $bitly = new Zend_Service_ShortUrl_BitLy($options['bitly']['login'], $options['bitly']['apiKey']);
                $shortUrl = $bitly->shorten($longUrl);

                $tags = $topic->findManyToManyRowset('Application_Model_Tags', 'Application_Model_PostsTags');
                $tagsArray = array();
                $countTags = 0;
                foreach ($tags as $item) {
                    $tagsArray[] = '#' . str_replace(' ', '', $item['name']);
                    $countTags++;
                    if ($countTags == 3) {
                        break;
                    }
                }
                if (count($tagsArray)) {
                    $tagsString = ' ' . implode(' ', $tagsArray);
                } else {
                    $tagsString = '';
                }

                $message = $topic->title . ' ' . $shortUrl . $tagsString;

                $twitter->statuses->update($message);

                $this->view->message = $message;
            }
        }

        $this->view->twitterBlock = $twitterBlock;
    }
}
