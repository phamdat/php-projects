<?php

class ThirdParty_LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Third Party Login");
		
		$this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
        if(Zend_ThirdPartyAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/third-party');
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
				
				$authAdapter->setTableName('third_party')
					->setIdentityColumn('username')
					->setCredentialColumn('password')
					->setIdentity($username)
                    ->setCredential($password);

                $auth = Zend_ThirdPartyAuth::getInstance();
                $result = $auth->authenticate($authAdapter);

                # is the user a valid one?
                if($result->isValid())
                {
                    # all info about this user from the login table
                    # ommit only the password, we don't need that
                    $userInfo = $authAdapter->getResultRowObject(null, 'password');

                    # the default storage is a session with namespace Zend_ThirdPartyAuth
                    $authStorage = $auth->getStorage();
                    $authStorage->write($userInfo);

                    $this->redirect('/third-party');
                }
                else
                {
                    $errorMessage = "Sai username hoặc password đã cấp.";
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
        $username->setLabel('Username:')
				->setAttribs(array('style' => 'width:96%;'))
                ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
				->setAttribs(array('style' => 'width:96%;'))
                ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel('Login')
				->setDecorators(array(
					array('HtmlTag', array('tag' => 'input', 'type' => 'submit', 'style' => 'background:transparent;border:none;color:#fff;height: 30px;width: 93px;')),
					array(array('b' => 'HtmlTag'), 'options' => array('tag'=>'span')),
					array(array('a' => 'HtmlTag'), 'options' => array('tag' => 'a', 'class'=>"black_button"))
				));

        $loginForm = new Zend_Form();
        $loginForm->setAction($this->_request->getBaseUrl().'/third-party/login')
                ->setMethod('post')
                ->addElement($username)
                ->addElement($password)
                ->addElement($submit);

        return $loginForm;
    }
	
	public function logoutAction()
    {
        # clear everything - session is cleared also!
        Zend_ThirdPartyAuth::getInstance()->clearIdentity();
        $this->_redirect('/third-party/login');
    }
}

