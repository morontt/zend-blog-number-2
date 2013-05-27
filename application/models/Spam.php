<?php

class Application_Model_Spam extends Zend_Db_Table_Abstract
{

    protected $_name = 'spam';

    public function savePostData($data)
    {

        $dataArray = array(
            'post_data' => serialize($data),
        );

        $this->insert($dataArray);
    }

}

