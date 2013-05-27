<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'username', array(
            'label'    => 'Логин:',
            'required' => true,
            'filters'  => array('StripTags', 'StringTrim'),
        ));

        $this->addElement('password', 'password', array(
            'label'    => 'Пароль:',
            'required' => true,
        ));

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label'  => 'LOG IN',
        ));
    }

}

