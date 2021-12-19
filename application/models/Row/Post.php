<?php

class Application_Model_Row_Post extends Zend_Db_Table_Row_Abstract
{
    public function getComments()
    {
        $commentsTable = new Application_Model_Comments();

        return $commentsTable->getTreeComments($this->id);
    }
}
