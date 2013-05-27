<?php

class Admin_CategoryController extends Zend_Controller_Action
{

    protected $_flashMessenger = null;

    public function init()
    {
        $this->_helper->layout->setLayout('admin');

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect($this->view->url(array(), 'login'));
        }

        $this->_flashMessenger = $this->_helper->FlashMessenger;
    }

    public function indexAction()
    {
        $page = $this->_getParam('page');

        $category = new Application_Model_Category();

        $paginator = $category->getAdminCategoryList();

        $paginator->setItemCountPerPage(30);
        $paginator->SetCurrentPageNumber($page);

        $this->view->messages = $this->_flashMessenger->getMessages();

        $this->view->paginator = $paginator;
    }

    public function createAction()
    {
        $form = new Admin_Form_Category();
        $form->submit->setLabel('Создать категорию');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $form->getValues();

                $category = new Application_Model_Category();
                $category->createCategory($formData);

                $this->_flashMessenger->addMessage('Категория создана');

                $this->clearCache();

                $this->redirect($this->view->url(array(), 'admin_category'));
            }
        }
    }

    public function editAction()
    {
        $id = (int) $this->_getParam('id');

        $form = new Admin_Form_Category();
        $form->submit->setLabel('Отредактировать категорию');
        $this->view->form = $form;

        $category = new Application_Model_Category();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                $formData = $form->getValues();

                $category->editCategory($formData, $id);

                $this->_flashMessenger->addMessage('Категория отредактирована');

                $this->clearCache();

                $this->redirect($this->view->url(array(), 'admin_category'));
            }
        } else {
            $data = $category->getCategoryById($id)->toArray();

            $form->populate($data);
        }

    }

    public function deleteAction()
    {
        // action body
    }

    protected function clearCache()
    {
        $cacheOutput = Zend_Registry::get('cacheOutput');
        $cacheOutput->clean(Zend_Cache::CLEANING_MODE_ALL);

        $cache = Zend_Registry::get('cache');
        $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

}

