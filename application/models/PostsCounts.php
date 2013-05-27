<?php

class Application_Model_PostsCounts extends Zend_Db_Table_Abstract
{

    protected $_name = 'posts_counts';

    /**
     * update comments count
     *
     * @param integer $topicId
     */
    public function updateCommentsCount($topicId)
    {
        $this->getAdapter()
            ->query("CALL update_comments_count({$topicId})");
    }

    public function updateViewCount($id)
    {
        $data = array('views' => new Zend_Db_Expr('(views + 1)'));

        $this->update($data, 'post_id = ' . $id);
    }

    public function addNewRow($topicId)
    {
        return $this->insert(array('post_id' => $topicId));
    }

}