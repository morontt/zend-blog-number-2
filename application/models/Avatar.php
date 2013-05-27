<?php

class Application_Model_Avatar extends Zend_Db_Table_Abstract
{
    protected $_name = 'avatar';

    /**
     *
     * @param string $hash
     * @param int $default
     * @return array
     */
    public function getByHash($hash, $default)
    {
        $select = $this->select()
            ->where('hash = ?', $hash);

        $row = $this->fetchRow($select);
        if ($row) {
            $result = $row->toArray();
        } else {
            $result = array();
            $this->insert(array(
                'hash'          => $hash,
                'default'       => $default,
                'last_modified' => new Zend_Db_Expr('NOW()'),
            ));
        }

        return $result;
    }

    public function getOldenRow()
    {
        $select = $this->select()
            ->where("src = ''")
            ->order('last_modified ASC');

        $result = $this->fetchRow($select);

        if (!$result) {
            $select = $this->select()
                ->order('last_modified ASC');

            $result = $this->fetchRow($select);
        }

        return $result;
    }

}
