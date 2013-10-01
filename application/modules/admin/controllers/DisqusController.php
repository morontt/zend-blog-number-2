<?php

/**
 * Created by JetBrains PhpStorm.
 * User: morontt
 * Date: 01.10.13
 * Time: 22:32
 */
class Admin_DisqusController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->layout->setLayout('admin');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect($this->view->url(array(), 'login'));
        }
    }

    public function indexAction()
    {
    }

    public function recalculatehashAction()
    {
        $commentatorsTable = new Application_Model_Commentators();

        $commentators = $commentatorsTable->fetchAll(
            $commentatorsTable->select()
                ->where('email_hash IS NULL')
        );

        $result = array();
        foreach ($commentators as $item) {
            $hash = Application_Model_Commentators::getAvatarHash($item->name, $item->mail, $item->website);

            $item->email_hash = $hash;
            $item->save();

            $result[] = array($item->id, $hash);
        }

        $this->view->result = $result;
    }
}