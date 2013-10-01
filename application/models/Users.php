<?php

/**
 * Created by JetBrains PhpStorm.
 * User: morontt
 * Date: 30.09.13
 * Time: 23:47
 */
class Application_Model_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';

    /**
     * @param string $hash
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function getUserByEmailHash($hash)
    {
        $select = $this->select()
            ->where('email_hash = ?', $hash);

        return $this->fetchRow($select);
    }
}