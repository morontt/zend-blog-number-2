<?php

class Admin_Form_Topic extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $title       = new Zend_Form_Element_Text('title');
        $description = new Zend_Form_Element_Text('description');
        $textarea    = new Zend_Form_Element_Textarea('text_post');
        $categoryId  = new Zend_Form_Element_Select('category_id');
        $tagString   = new Zend_Form_Element_Text('tags');
        $hide        = new Zend_Form_Element_Select('hide');
        $submit      = new Zend_Form_Element_Submit('submit');

        $title->setLabel('Заголовок:')
            ->setAttrib('size', 96)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('stringLength', false, array(1, 255));

        $description->setLabel('Description:')
            ->setAttrib('size', 96)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('stringLength', false, array(1, 255));

        $textarea->setLabel('Текст записи:')
            ->setRequired(true)
            ->setAttribs(array(
                'cols' => 110,
                'rows' => 40,
            ))
            ->addFilter('StringTrim');

        $tagString->setLabel('Теги:')
            ->setAttrib('size', 96)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('stringLength', false, array(1, 1000));

        $category     = new Application_Model_Category();
        $nameCategory = $category->getCategoryList();
        $categoryId->setLabel('Категория:')
            ->addMultiOptions($nameCategory);

        $hide->setLabel('Скрытие:');
        $hide->addMultiOption('0', 'Видно всем')
            ->addMultiOption('1', 'Скрыто');

        $this->addElements(array($title, $description, $textarea, $tagString, $categoryId,
            $hide, $submit));
    }

}
