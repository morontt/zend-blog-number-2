<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->redirect($this->view->url(array(), 'login'));
    }

    public function loginAction()
    {
        $this->view->browsertitle = 'Вход';

        $form = new Application_Form_Login();

        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {

                $formData = $form->getValues();

                $auth        = Zend_Auth::getInstance();
                $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter(), 'users');

                $authAdapter->setIdentityColumn('username')
                            ->setCredentialColumn('password')
                            ->setIdentity($formData['username'])
                            ->setCredential($formData['password'])
                            ->setCredentialTreatment('SHA2(CONCAT( ?, \'{\', password_salt, \'}\'), 384)');

                $result = $auth->authenticate($authAdapter);
                if ($result->isValid()) {

                    $storage = new Zend_Auth_Storage_Session();
                    $storage->write($authAdapter->getResultRowObject(array(
                        'id',
                        'username',
                        'mail',
                    )));

                    $this->redirect('/');
                } else {
                    $this->view->error = true;
                }
            }
        }

        $this->view->form = $form;
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();

        $this->redirect($this->view->url(array(), 'login'));
    }

}

