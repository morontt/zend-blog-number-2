<?php

class Application_Model_Posts extends Zend_Db_Table_Abstract
{
    protected $_name = 'posts';

    protected $_rowClass = 'Application_Model_Row_Post';

    protected $_dependentTables = array('Application_Model_PostsTags');

    public function getAllPosts()
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id',
                array(
                    'cat_name' => 'c.name',
                    'cat_url'  => 'c.url',
                )
            )
            ->order('p.time_created DESC');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $select->where('p.hide = 0');
        }

        return Zend_Paginator::factory($select);
    }

    public function getPostsByCategory($url)
    {
        $category = new Application_Model_Category();

        $categoryArray = $category->getCategoryIdArray();

        foreach ($categoryArray as $item) {
            if ($url == $item['url']) {
                $rootCategory = $item['id'];
                break;
            }
        }

        $childArray = array();
        if (isset($rootCategory)) {
            $childArray[] = (int)$rootCategory;
            do {
                $insert = false;
                foreach ($categoryArray as $item) {
                    if (!in_array($item['id'], $childArray)) {
                        if (in_array($item['parent_id'], $childArray)) {
                            $childArray[] = (int)$item['id'];
                            $insert = true;
                        }
                    }
                }
            } while ($insert);
        }

        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id',
                array(
                    'cat_name' => 'c.name',
                    'cat_url'  => 'c.url',
                )
            )
            ->where('p.hide = 0')
            ->order('p.time_created DESC');

        if (count($childArray)) {
            $select->where('c.id IN (?)', $childArray);
        } else {
            $select->where('c.url = ?', $url);
        }

        return Zend_Paginator::factory($select);
    }

    public function getPostsByTag($url)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id',
                array(
                    'cat_name' => 'c.name',
                    'cat_url'  => 'c.url',
                )
            )
            ->joinInner(array('rtt' => 'relation_topictag'), 'p.id = rtt.post_id', array())
            ->joinInner(array('t' => 'tags'), 't.id = rtt.tag_id', array())
            ->where('p.hide = 0')
            ->where('t.url = ?', $url)
            ->order('p.time_created DESC');

        return Zend_Paginator::factory($select);
    }

    public function getPostById($id)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id',
                array(
                    'cat_name' => 'c.name',
                    'cat_url'  => 'c.url',
                )
            )
            ->where('p.hide = 0')
            ->where('p.id = ?', $id);

        return $this->fetchRow($select);
    }

    public function getPostByUrl($url)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id',
                array(
                    'cat_name' => 'c.name',
                    'cat_url'  => 'c.url',
                )
            )
            ->where('p.url = ?', $url);

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $select->where('p.hide = 0');
        }

        return $this->fetchRow($select);
    }

    public function getTopicDataForNavigation($url)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('p' => $this->_name), array('p.url', 'p.title'))
            ->joinInner(
                array('c' => 'category'),
                'c.id = p.category_id', array(
                    'category_url' => 'c.url',
                )
            )
            ->where('p.url = ?', $url);

        return $this->fetchRow($select)->toArray();
    }

    public function getFeedData($feedType)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $host = $request->getHttpHost();
        $options = Zend_Registry::get('options');

        $router = Zend_Controller_Front::getInstance()->getRouter();

        $result = array(
            'title'       => $host,
            'link'        => BASE_URL . '/',
            'description' => $host . ' - последние записи',
            'language'    => 'ru-ru',
            'charset'     => 'utf-8',
            'author'      => 'morontt',
            'email'       => $options['sys_parameters']['mail'],
            'generator'   => 'Zend Framework Generator',
        );

        $select = $this->select()
            ->where('hide <> 1')
            ->order('time_created DESC')
            ->limit(25);

        $topics = $this->fetchAll($select);

        $lastDate = '';

        $entries = array();
        foreach ($topics as $topic) {

            list($year, $month, $day, $hour, $min, $sec) = sscanf($topic->time_created, "%d-%d-%d %d:%d:%d");
            $timestamp = mktime($hour, $min, $sec, $month, $day, $year);

            if (empty($lastDate)) {
                $lastDate = $timestamp;
            }

            $linkTopic = $router->assemble(array('url' => $topic->url), 'topic_url', false, true);

            if ($feedType == 'rss') {
                $item = array(
                    'title'       => $topic->title,
                    'link'        => BASE_URL . $linkTopic,
                    'description' => $topic->text_post,
                    'lastUpdate'  => $timestamp,
                    'comments'    => BASE_URL . $linkTopic,
                    'guid'        => 'topic_' . $topic->id
                );
            } else {
                $item = array(
                    'title'       => $topic->title,
                    'link'        => BASE_URL . $linkTopic,
                    'description' => $topic->text_post,
                    'lastUpdate'  => $timestamp,
                    'comments'    => BASE_URL . $linkTopic,
                );
            }
            $entries[] = $item;
        }

        $result['lastUpdate'] = $lastDate;
        $result['entries'] = $entries;

        return $result;
    }

    public function getSitemapTopic()
    {
        $select = $this->select()
            ->from($this->_name, array('url', 'last_update'))
            ->where('hide <> 1')
            ->order('time_created DESC');

        $result = $this->fetchAll($select)->toArray();

        return $result;
    }

    public function createNewTopic($formData)
    {
        // TODO - uniq url
        $url = Zml_Transform::ruTransform($formData['title']);

        $data = array(
            'category_id'    => $formData['category_id'],
            'hide'           => $formData['hide'],
            'title'          => $formData['title'],
            'text_post'      => $formData['text_post'],
            'description'    => $formData['description'],
            'url'            => $url,
            'time_created'   => new Zend_Db_Expr('NOW()'),
            'last_update'    => new Zend_Db_Expr('NOW()'),
            'comments_count' => 0,
            'views_count'    => 0,
        );

        $topicId = $this->insert($data);

        if (!empty($formData['tags'])) {
            $relation = new Application_Model_PostsTags();
            $relation->addRelation($formData['tags'], $topicId);
        }

        return $topicId;
    }

    public function editTopic($formData, $id)
    {
        $oldTopic = $this->getTopicDataForEdit($id);

        $data = array(
            'text_post'   => $formData['text_post'],
            'last_update' => new Zend_Db_Expr('NOW()'),
        );

        if ($formData['hide'] == '1') {
            $data['time_created'] = new Zend_Db_Expr('NOW()');
        }

        if ($oldTopic['title'] != $formData['title']) {
            // TODO - uniq url
            $url = Zml_Transform::ruTransform($formData['title']);

            $data['title'] = $formData['title'];
            $data['url'] = $url;
        }

        if ($oldTopic['description'] != $formData['description']) {
            $data['description'] = $formData['description'];
        }

        if ($oldTopic['category_id'] != $formData['category_id']) {
            $data['category_id'] = $formData['category_id'];
        }

        if ($oldTopic['hide'] != $formData['hide']) {
            $data['hide'] = $formData['hide'];
        }

        if ($oldTopic['tags'] != $formData['tags']) {
            $relation = new Application_Model_PostsTags();
            $relation->deleteRelation($id);

            if (!empty($formData['tags'])) {
                $relation->addRelation($formData['tags'], $id);
            }
        }

        return $this->update($data, 'id = ' . $id);
    }

    public function getTopicDataForEdit($topicId)
    {
        $select = $this->select()
            ->where('id = ?', $topicId);

        $data = $this->fetchRow($select)->toArray();

        $postTags = new Application_Model_PostsTags();

        $data['tags'] = $postTags->getStringTags($topicId);

        return $data;
    }

    public function getStatisticData()
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(
                array('p' => $this->_name),
                array('id', 'title', 'url', 'views_count AS views')
            )
            ->order('views_count DESC')
            ->limit(6);

        $dataArray = $this->fetchAll($select)->toArray();

        return $dataArray;
    }

    /**
     * @param integer $topicId
     */
    public function updateCommentsCount($topicId)
    {
        $this->getAdapter()
            ->query("CALL update_comments_count({$topicId})");
    }

    /**
     * @param int $id
     */
    public function updateViewCount($id)
    {
        $data = array('views_count' => new Zend_Db_Expr('(views_count + 1)'));

        $this->update($data, 'id = ' . $id);
    }

    /**
     * @param array $threads
     * @return array
     */
    public function getPostsByDisqusThreads(array $threads)
    {
        $select = $this->select()
            ->from(array('p' => $this->_name), array('p.id', 'p.disqus_thread'))
            ->where('p.disqus_thread IN (?)', $threads);

        $dataArray = $this->fetchAll($select);

        $result = array();
        foreach ($dataArray as $item) {
            $result[$item->disqus_thread] = $item->id;
        }

        $unknownThreads = array();
        foreach ($threads as $item) {
            if (!isset($result[$item])) {
                $unknownThreads[] = $item;
            }
        }

        $result['unknown'] = $unknownThreads;

        return $result;
    }

    /**
     * @param array $disqusThreads
     */
    public function saveDisqusThreads(array $disqusThreads)
    {
        foreach ($disqusThreads as $item) {
            foreach ($item->identifiers as $identificator) {
                $url = str_replace('/article/', '', $identificator);

                $post = $this->fetchRow($this->select()->where('url = ?', $url));
                if ($post) {
                    $post->disqus_thread = $item->id;
                    $post->save();
                }
            }
        }
    }
}
