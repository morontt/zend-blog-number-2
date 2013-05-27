<?php

class Application_Model_Comments extends Zend_Db_Table_Abstract
{

    protected $_name = 'comments';

    public function getByTopicId($id)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('c' => $this->_name), array(
                'c.id',
                'c.parent_id',
                'c.user_id',
                'c.text',
                'c.ip_addr',
                'c.time_created',
                'c.commentator_id',
            ))
            ->joinLeft(array('t' => 'commentators'), 'c.commentator_id = t.id', array(
                't.name',
                't.mail',
                't.website',
            ))
            ->joinLeft(array('u' => 'users'), 'c.user_id = u.id', array(
                'user_mail' => 'u.mail',
                'u.username',
            ))
            ->where('c.post_id = ?', $id)
            ->order('c.id ASC');

        return $this->fetchAll($select);
    }

    public function writeChild($id, $valueArray, &$resultArray, $level)
    {
        foreach ($valueArray as $item) {
            if ($item['parent_id'] == $id) {
                $resultArray[] = array(
                    'id'    => $item['id'],
                    'level' => $level,
                );
                $this->writeChild($item['id'], $valueArray, $resultArray, $level + 1);
            }
        }
    }

    public function getTreeComments($id)
    {
        $dataArray = $this->getByTopicId($id)
            ->toArray();

        $treeData = array();
        foreach ($dataArray as $item) {
            $treeData[] = array(
                'id'        => (int) $item['id'],
                'parent_id' => (int) $item['parent_id'],
            );
        }

        $treeArray = array();
        $this->writeChild(0, $treeData, $treeArray, 0);

        $commentsTree = array();
        foreach ($treeArray as $item) {
            foreach ($dataArray as $comment) {
                if ($item['id'] == $comment['id']) {
                    $commentsTree[] = array(
                        'comment' => $comment,
                        'level'   => $item['level'],
                    );
                    break;
                }
            }
        }

        return $commentsTree;
    }

    public function saveComment($formData, $commentatorId, $clientIp)
    {
        $auth = Zend_Auth::getInstance();

        $trackingAgent = new Application_Model_TrackingAgent();
        $agentArray = $trackingAgent->getAgent();

        $dataArray = array(
            'post_id'       => $formData['topicId'],
            'text'          => $formData['comment_text'],
            'time_created'  => new Zend_Db_Expr('NOW()'),
            'user_agent_id' => $agentArray['id'],
            'ip_addr'       => $clientIp,
        );

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $dataArray['user_id'] = $identity->id;
        } else {
            $dataArray['commentator_id'] = $commentatorId;
        }

        if ($formData['parentId']) {
            $dataArray['parent_id'] = $formData['parentId'];
        }

        return $this->insert($dataArray);
    }

    /**
     * get count comments by topic_id
     *
     * @param integer $topicId
     * @return integer
     */
    public function getCountByTopicId($topicId)
    {
        $select = $this->select()
            ->from($this->_name, array(
                'cnt' => new Zend_Db_Expr('COUNT( id )'),
            ))
            ->where('post_id = ?', $topicId)
            ->where('deleted = 0');

        $data = $this->fetchAll($select);

        return (int) $data[0]['cnt'];
    }

    public function getCommentInfo($commentId)
    {
        $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array('c' => $this->_name), array())
            ->joinInner(array('p' => 'posts'), 'c.post_id = p.id', array(
                'p.title',
            ))
            ->joinLeft(array('ctr' => 'commentators'), 'c.commentator_id = ctr.id', array(
                'c_name' => 'ctr.name',
                'c_mail' => 'ctr.mail',
                'c_id'   => 'ctr.id',
            ))
            ->joinLeft(array('u' => 'users'), 'c.user_id = u.id', array(
                'u_name' => 'u.username',
                'u_mail' => 'u.mail',
            ))
            ->where('u.id <> 1 OR u.id IS NULL')
            ->where('c.id = ?', $commentId);

        $data = $this->fetchRow($select);

        $result = false;
        if ($data && $data->c_mail) {
            $result = array(
                'name'  => $data->c_name,
                'mail'  => $data->c_mail,
                'title' => $data->title,
                'hash'  => md5(Application_Model_Commentators::HASH_SALT . $data->c_id),
            );
        }
        if ($data && $data->u_mail) {
            $result = array(
                'name'  => $data->u_name,
                'mail'  => $data->u_mail,
                'title' => $data->title,
                'hash'  => false,
            );
        }

        return $result;
    }

}
