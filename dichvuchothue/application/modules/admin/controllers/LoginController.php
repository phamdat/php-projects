<?php

class Admin_LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */		
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Admin Login");
    }

    public function indexAction()
    {
        if(Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin');
        }

        $request = $this->getRequest();
        $loginForm = $this->getLoginForm();

        $errorMessage = "";

        if($request->isPost())
        {
            if($loginForm->isValid($request->getPost()))
            {
                # get the username and password from the form
                $username = $loginForm->getValue('username');
                $password = $loginForm->getValue('password');
	
                $db = Zend_Registry::get('db');
				
				$authAdapter = new Zend_Auth_Adapter_DbTable($db);
				
				$authAdapter->setTableName('user')
					->setIdentityColumn('username')
					->setCredentialColumn('password')
					->setCredentialTreatment('PASSWORD(?)')
					->setIdentity($username)
                    ->setCredential($password);

                $auth = Zend_AdminAuth::getInstance();
                $result = $auth->authenticate($authAdapter);

                # is the user a valid one?
                if($result->isValid())
                {
                    # all info about this user from the login table
                    # ommit only the password, we don't need that
                    $userInfo = $authAdapter->getResultRowObject(null, 'password');

                    # the default storage is a session with namespace Zend_AdminAuth
                    $authStorage = $auth->getStorage();
                    $authStorage->write($userInfo);

                    $this->redirect('/admin');
                }
                else
                {
                    $errorMessage = "Wrong username or password provided. Please try again.";
                }
            }
        }
        $this->view->errorMessage = $errorMessage;
        $this->view->loginForm = $loginForm;
    }

    /**
     * Create and return the login form
     *
     * @return object
     */
    protected function getLoginForm()
    {
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username')
                ->setRequired(true)
                ->setAttrib('class', 'form-control');

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password')
                ->setRequired(true)
                ->setAttrib('class', 'form-control');

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login')
                ->setAttrib('class', 'btn btn-primary');

        $loginForm = new Zend_Form();
        $loginForm->setAction($this->_request->getBaseUrl().'/admin/login')
                ->setMethod('post')
                ->addElement($username)
                ->addElement($password)
                ->addElement($submit);

        return $loginForm;
    }
	
	public function logoutAction()
    {
        # clear everything - session is cleared also!
        Zend_AdminAuth::getInstance()->clearIdentity();
        $this->_redirect('/admin/login');
    }
}

