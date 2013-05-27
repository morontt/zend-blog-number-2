<?php

class Application_Model_SysParameters extends Zend_Db_Table_Abstract
{

    protected $_name = 'sys_parameters';

    public function saveOption($option, $value)
    {
        $select = $this->select()
            ->where('optionkey = ?', $option);

        $row = $this->fetchRow($select);
        if ($row) {
            $row->value = $value;
            $row->save();
        } else {
            $this->insert(array(
                'optionkey' => $option,
                'value'     => $value,
            ));
        }
    }

    public function getOption($option)
    {
        $select = $this->select()
            ->where('optionkey = ?', $option);

        $row = $this->fetchRow($select);
        if ($row) {
            $result = $row->value;
        } else {
            $result = null;
        }

        return $result;
    }

}

