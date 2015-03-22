<?php

class LoginRegisterController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
    }

    public function indexAction()
    {
        if(Zend_Auth::getInstance()->hasIdentity())
        {
            $this->redirect('/');
        }

        $request = $this->getRequest();
        $loginForm = $this->getLoginForm();
		$registerForm = $this->getRegisterForm();
		$forgetPasswordForm = $this->getForgetPasswordForm();

        $errorMessage = "";

        if($request->isPost())
        {
			$id = $this->_request->getParam('id');
			if($id == 'login'){
				if($loginForm->isValid($request->getPost()))
				{
					# get the username and password from the form
					$username = $loginForm->getValue('username');
					$password = $loginForm->getValue('password');
		
					$db = Zend_Registry::get('db');
					
					$authAdapter = new Zend_Auth_Adapter_DbTable($db);
					
					$authAdapter->setTableName('customer')
						->setIdentityColumn('username')
						->setCredentialColumn('password')
						->setCredentialTreatment('PASSWORD(?)')
						->setIdentity($username)
						->setCredential($password);

					$auth = Zend_Auth::getInstance();
					$result = $auth->authenticate($authAdapter);

					# is the user a valid one?
					if($result->isValid())
					{
						# all info about this user from the login table
						# ommit only the password, we don't need that
						$userInfo = $authAdapter->getResultRowObject(null, 'password');

						# the default storage is a session with namespace Zend_Auth
						$authStorage = $auth->getStorage();
						$authStorage->write($userInfo);

						$this->redirect('/');
					}
					else
					{
						$errorMessage = "Bạn nhập sai username hoặc password. Vui lòng thử lại.";
					}
				}
			} else if($id == 'register'){
				if($registerForm->isValid($request->getPost()))
				{
					if($registerForm->getValue('password') != $registerForm->getValue('confirmPassword')){
						$registerForm->getElement('confirmPassword')->addError('Confirm password không khớp với password đã nhập.');
					}else{
						$db = Zend_Registry::get('db');

						$data = array(
							'username' 		=> $registerForm->getValue('username'),
							'password' 		=> new Zend_Db_Expr('PASSWORD(' . $db->quote($registerForm->getValue('password')) . ')'),
							'name' 			=> $registerForm->getValue('name'),
							'email' 		=> $registerForm->getValue('email'),
							'phone'      	=> $registerForm->getValue('phone'),
							'address'    	=> $registerForm->getValue('address')
						);
						
						$n = $db->insert('customer', $data);
						
						$authAdapter = new Zend_Auth_Adapter_DbTable($db);
						
						$authAdapter->setTableName('customer')
							->setIdentityColumn('username')
							->setCredentialColumn('password')
							->setCredentialTreatment('PASSWORD(?)')
							->setIdentity($registerForm->getValue('username'))
							->setCredential($registerForm->getValue('password'));

						$auth = Zend_Auth::getInstance();
						$result = $auth->authenticate($authAdapter);

						# all info about this user from the login table
						# ommit only the password, we don't need that
						$userInfo = $authAdapter->getResultRowObject(null, 'password');

						# the default storage is a session with namespace Zend_Auth
						$authStorage = $auth->getStorage();
						$authStorage->write($userInfo);

						$this->redirect('/');
					}
				}
			}else if($id == 'forget-password'){
				if($forgetPasswordForm->isValid($request->getPost()))
				{
					$date = Zend_Date::now();
			
					$newPass = substr(md5($date->getTimestamp()), 0, 6);
					
					$db = Zend_Registry::get('db');
					
					$data = array(
						'email' 		=> $forgetPasswordForm->getValue('email'),
						'password' 		=> new Zend_Db_Expr('PASSWORD(' . $db->quote($newPass) . ')')
					);
					
					$db = Zend_Registry::get('db');
					
					$n = $db->update('customer', $data, array('email = ?' => $forgetPasswordForm->getValue('email')));
					
					$this->sendResetPasswordMail($forgetPasswordForm->getValue('email'), $newPass);
					
					$this->_redirect('/login-register');
				}
			}
        }
        $this->view->errorMessage = $errorMessage;
        $this->view->loginForm = $loginForm;
		$this->view->registerForm = $registerForm;
		$this->view->forgetPasswordForm = $forgetPasswordForm;
    }
	
	protected function sendResetPasswordMail($email, $newPass){
	
		if (file_exists(RESET_PASSWORD_MAIL_TEMPLATE)) {
				
			$content = file_get_contents(RESET_PASSWORD_MAIL_TEMPLATE);
			
			$content = str_replace("#newPass#", $newPass, $content);
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyText($content);
			$mail->setBodyHtml($content);
			$mail->setFrom('maxi2013@maximark.vn', '[Hộp mail tự động] Không trả lời');
			$mail->addTo($email);
			$mail->setSubject('[Hộp mail tự động] Reset password');
			$mail->send();
		}
	}

    /**
     * Create and return the login form
     *
     * @return object
     */
    protected function getLoginForm()
    {
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(true)
			->setValue('login')
			->removeDecorator('DtDdWrapper');
			
		$title = new Zend_Form_Element_Title('title');
		$title->setValue('Đăng nhập')
				->setAttrib('class', 'title');
		
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng điền username.');

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng điền password.');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Login')
				->removeDecorator('DtDdWrapper');
				
		$registerLink = new Zend_Form_Element_A('register-link');
        $registerLink->setValue('Nhấp vào đây nếu bạn chưa có account')
				->setAttrib('class', 'linkform')
				->setAttrib('rel', 'register');
				
		$forgetPasswordLink = new Zend_Form_Element_A('forget-password-link');
        $forgetPasswordLink->setValue('Nhấp vào đây nếu bạn quên password')
				->setAttrib('class', 'linkform')
				->setAttrib('rel', 'forget-password');

		$formClass = 'login';
				
		if($this->_request->getParam('id') == 'login' || $this->_request->getParam('id') == null){
			$formClass .= ' active';
		}
		
		$loginForm = new Zend_Form();
        $loginForm->setAction($this->_request->getBaseUrl().'/login-register')
				->setAttrib('class', $formClass)
                ->setMethod('post')
				->addElement($id)
				->addElement($title)
                ->addElement($username)
                ->addElement($password)
                ->addElement($submit)
				->addElement($registerLink)
				->addElement($forgetPasswordLink);

        return $loginForm;
    }
	
	/**
     * Create and return the register form
     *
     * @return object
     */
    protected function getRegisterForm()
    {
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(true)
			->setValue('register')
			->removeDecorator('DtDdWrapper');
			
		$title = new Zend_Form_Element_Title('title');
		$title->setValue('Đăng ký')
				->setAttrib('class', 'title');
				
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Họ tên:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng điền họ tên.');
				
		$email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('EmailAddress', true);
				
		$email->getValidator('NotEmpty')->setMessage('Vui lòng điền email.');
		$email->getValidator('EmailAddress')->setMessage('Vui lòng điền đúng email.');
				
		$emailValidator = new Zend_Validate_Db_NoRecordExists('customer', 'email');
		$emailValidator->setMessage('Email đã được sử dụng. Vui lòng chọn Email khác hoặc vào mục quên password ở mục đăng nhập.');
		$email->addValidator($emailValidator, true);
				
		$username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username:')
                ->setRequired(true)
				->addValidator('NotEmpty', true);
				
		$username->getValidator('NotEmpty')->setMessage('Vui lòng điền username.');
				
		$usernameValidator = new Zend_Validate_Db_NoRecordExists('customer', 'username');
		$usernameValidator->setMessage('Username đã được sử dụng. Vui lòng chọn Username khác.');
		$username->addValidator($usernameValidator, true);
		
		$password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng điền password.');
				
		$confirmPassword = new Zend_Form_Element_Password('confirmPassword');
        $confirmPassword->setLabel('Confirm Password:')
                ->setRequired(true)
				->addValidator('NotEmpty', true);
				
		$confirmPassword->getValidator('NotEmpty')->setMessage('Vui lòng điền confirm password.');
				
		$phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Số điện thoại:')
				->addValidator('Digits', true);
				
		$phone->getValidator('Digits')->setMessage('Vui lòng điền đúng số điện thoại.');
		
		$address = new Zend_Form_Element_Textarea('address');
        $address->setLabel('Địa chỉ:')
				->setAttrib('rows', '12');;

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Đăng ký')
				->removeDecorator('DtDdWrapper');
				
		$loginLink = new Zend_Form_Element_A('login-link');
        $loginLink->setValue('Nhấp vào đây để login')
				->setAttrib('class', 'linkform')
				->setAttrib('rel', 'login');

		$formClass = 'register';
				
		if($this->_request->getParam('id') == 'register'){
			$formClass .= ' active';
		}
		
        $registerForm = new Zend_Form();
        $registerForm->setAction($this->_request->getBaseUrl().'/login-register')
				->setAttrib('class', $formClass)
                ->setMethod('post')
				->addElement($id)
				->addElement($title)
				->addElement($name)
				->addElement($email)
                ->addElement($username)
                ->addElement($password)
				->addElement($confirmPassword)
				->addElement($phone)
                ->addElement($address)
                ->addElement($submit)
				->addElement($loginLink);

        return $registerForm;
    }
	
	/**
     * Create and return the login form
     *
     * @return object
     */
    protected function getForgetPasswordForm()
    {
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(true)
			->setValue('forget-password')
			->removeDecorator('DtDdWrapper');
			
		$title = new Zend_Form_Element_Title('title');
		$title->setValue('Nhắc password')
				->setAttrib('class', 'title');
		
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('EmailAddress', true);
				
		$email->getValidator('NotEmpty')->setMessage('Vui lòng điền email.');
		$email->getValidator('EmailAddress')->setMessage('Vui lòng điền đúng email.');
				
		$emailValidator = new Zend_Validate_Db_RecordExists('customer', 'email');
		$emailValidator->setMessage('Email chưa được sử dụng.');
		$email->addValidator($emailValidator, true);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Submit')
				->removeDecorator('DtDdWrapper');
		
		$loginLink = new Zend_Form_Element_A('login-link');
        $loginLink->setValue('Nhấp vào đây để login')
				->setAttrib('class', 'linkform')
				->setAttrib('rel', 'login');
				
		$registerLink = new Zend_Form_Element_A('register-link');
        $registerLink->setValue('Nhấp vào đây nếu bạn chưa có account')
				->setAttrib('class', 'linkform')
				->setAttrib('rel', 'register');

		$formClass = 'forget-password';
				
		if($this->_request->getParam('id') == 'forget-password'){
			$formClass .= ' active';
		}
		
        $forgetPasswordForm = new Zend_Form();
        $forgetPasswordForm->setAction($this->_request->getBaseUrl().'/login-register')
				->setAttrib('class', $formClass)
                ->setMethod('post')
				->addElement($id)
				->addElement($title)
                ->addElement($email)
                ->addElement($submit)
				->addElement($loginLink)
				->addElement($registerLink);

        return $forgetPasswordForm;
    }
	
	public function logoutAction()
    {
        # clear everything - session is cleared also!
        Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::destroy();
        $this->_redirect('/login-register');
    }
}

