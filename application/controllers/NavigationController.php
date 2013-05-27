<?php

class NavigationController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $category = new Application_Model_Category();
        $notEmpty = $category->getNotEmptyCategory();

        $this->view->category = $notEmpty;

        $sysParameters = new Application_Model_SysParameters();
        $accessToken = $sysParameters->getOption('twitter_token');

        $twitterBlock = false;
        if (!empty($accessToken) && !in_array(getenv('APPLICATION_ENV'), array('development', 'testing'))) {
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

                $this->view->twitterResponse = $response;
                $this->view->twitterStatus = $twitter->statuses->userTimeLine(array('count' => 10));
            }
        }

        $this->view->twitterBlock = $twitterBlock;
    }

    public function menuAction()
    {
        $requestParams = Zend_Registry::get('requestParams');
        $requestString = $requestParams['module'] . $requestParams['controller'] . $requestParams['action'];

        $breadcrumbs = array();

        if ($requestString == 'defaultindexcategory') {
            $this->getBreadcrumsCategory($requestParams['url'], $breadcrumbs);
        }

        if ($requestString == 'defaultindextopic') {

            $postsTable = new Application_Model_Posts();
            $topicData = $postsTable->getTopicDataForNavigation($requestParams['url']);

            $breadcrumbs[] = array(
                'name' => $topicData['title'],
                'link' => $this->view->url(array('url' => $topicData['url']), 'topic_url', true),
            );

            $this->getBreadcrumsCategory($topicData['category_url'], $breadcrumbs);
        }

        if ($requestString == 'defaultindextag') {

            $tagTable = new Application_Model_Tags();
            $tag = $tagTable->getTagByUrl($requestParams['url']);

            $breadcrumbs[] = array(
                'name' => 'тег: ' . $tag->name,
                'link' => $this->view->url(array('url' => $tag->url), 'tag-page', true),
            );
        }

        $breadcrumbsRevers = array_reverse($breadcrumbs, true);

        $this->view->breadcrumbs = $breadcrumbsRevers;
    }

    public function adminmenuAction()
    {
        // action body
    }

    public function adminsidebarAction()
    {
        // action body
    }

    protected function getBreadcrumsCategory($url, &$breadcrumbs)
    {
        $category = new Application_Model_Category();
        $notEmpty = $category->getNotEmptyCategory();

        foreach ($notEmpty as $category) {
            if ($category['url'] == $url) {
                $parentId = (int) $category['id'];
            }
        }

        while ($parentId != 0) {
            foreach ($notEmpty as $category) {
                if ((int) $category['id'] == $parentId) {
                    $breadcrumbs[] = array(
                        'name' => $category['name'],
                        'link' => $this->view->url(array('url' => $category['url']), 'category-page', true),
                    );
                    $parentId = (int) $category['parent_id'];
                }
            }
        }
    }

}
