<?php

class Admin_Form_Category extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $category = new Zend_Form_Element_Text('name');
        $parent   = new Zend_Form_Element_Select('parent_id');
        $submit   = new Zend_Form_Element_Submit('submit');

        $category->setLabel('Имя категории:')
            ->setRequired(true)
            ->setAttrib('size', 96)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('stringLength', false, array(1, 100));

        $categoryTable = new Application_Model_Category();
        $nameCategory  = $categoryTable->getCategoryList();

        $parent->setLabel('Родительская категория:')
            ->addMultiOptions(array('' => '...'))
            ->addMultiOptions($nameCategory);

        $this->addElements(array($category, $parent, $submit));
    }
}
