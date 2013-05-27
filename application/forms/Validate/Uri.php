<?php

class Application_Form_Validate_Uri extends Zend_Validate_Abstract
{

    const MSG_URI = 'msgUri';

    protected $_messageTemplates = array(
        self::MSG_URI => 'Invalid URL',
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        $valid = Zend_Uri::check($value);

        if ($valid) {
            $result = true;
        } else {
            $this->_error(self::MSG_URI);
            $result = false;
        }

        return $result;
    }

}