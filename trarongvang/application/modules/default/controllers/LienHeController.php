<?php

class LienHeController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Liên hệ");
    }

    public function indexAction()
    {
		$request = $this->getRequest();
        $yKienForm = $this->getYKienForm();
		
		$errorMessage = "";

        if($request->isPost())
        {
			if(Zend_Auth::getInstance()->hasIdentity())
			{
				$db = Zend_Registry::get('db');
											
				$db->insert('comment', array('name' => Zend_Auth::getInstance()->getStorage()->read()->name,
											'email' => Zend_Auth::getInstance()->getStorage()->read()->email,
											'phone' => Zend_Auth::getInstance()->getStorage()->read()->phone,
											'address' => Zend_Auth::getInstance()->getStorage()->read()->address,
											'comment' => $request->getParam('comment')));

				$this->sendCommentMail(Zend_Auth::getInstance()->getStorage()->read()->name,
										Zend_Auth::getInstance()->getStorage()->read()->email,
										Zend_Auth::getInstance()->getStorage()->read()->phone,
										Zend_Auth::getInstance()->getStorage()->read()->address,
										$request->getParam('comment'));
				
				$this->redirect('/lien-he/cam-on');
			}
			else
			{
				if($yKienForm->isValid($request->getPost()))
				{
					$db = Zend_Registry::get('db');
				
					$db->insert('comment', array('name' => $yKienForm->getValue('name'),
											'email' => $yKienForm->getValue('email'),
											'phone' => $yKienForm->getValue('phone'),
											'address' => $yKienForm->getValue('address'),
											'comment' => $yKienForm->getValue('comment')));

					$this->sendCommentMail($yKienForm->getValue('name'),
											$yKienForm->getValue('email'),
											$yKienForm->getValue('phone'),
											$yKienForm->getValue('address'),
											$yKienForm->getValue('comment'));
					
					$this->redirect('/lien-he/cam-on');
				}
			}
		}
		
		$this->view->yKienForm = $yKienForm;
    }
	
	protected function sendCommentMail($name, $email, $phone, $address, $comment){
	
		if (file_exists(COMMENT_MAIL_TEMPLATE)) {
				
			$content = file_get_contents(COMMENT_MAIL_TEMPLATE);
			
			$content = str_replace("#name#", $name, $content);
			$content = str_replace("#email#", $email, $content);
			$content = str_replace("#phone#", $phone, $content);
			$content = str_replace("#address#", $address, $content);
			$content = str_replace("#comment#", $comment, $content);
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyText($content);
			$mail->setBodyHtml($content);
			$mail->setFrom('maxi2013@maximark.vn', '[Hộp mail tự động] Không trả lời');
			foreach(Zend_Registry::get('ADMIN_MAIL_ADDRESS') as $item){
				$mail->addTo($item);
			}
			$mail->setSubject('[Hộp mail tự động] Thư góp ý');
			$mail->send();
		}
	}
	
	/**
     * Create and return the register form
     *
     * @return object
     */
    protected function getYKienForm()
    {
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
				
		$phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Số điện thoại:')
				->addValidator('Digits', true);
				
		$phone->getValidator('Digits')->setMessage('Vui lòng điền đúng số điện thoại.');
		
		$address = new Zend_Form_Element_Textarea('address');
        $address->setLabel('Địa chỉ:')
				->setAttrib('rows', '3');
		
		$comment = new Zend_Form_Element_Textarea('comment');
        $comment->setLabel('Ý kiến:')
				->setAttrib('rows', '5');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gửi')
				->removeDecorator('DtDdWrapper');
				
		if(Zend_Auth::getInstance()->hasIdentity()){
			$name->setValue(Zend_Auth::getInstance()->getStorage()->read()->name)
				->setAttrib('disable', true);
			$email->setValue(Zend_Auth::getInstance()->getStorage()->read()->email)
				->setAttrib('disable', true);
			$phone->setValue(Zend_Auth::getInstance()->getStorage()->read()->phone)
				->setAttrib('disable', true);
			$address->setValue(Zend_Auth::getInstance()->getStorage()->read()->address)
				->setAttrib('disable', true);
		}
		
        $yKienForm = new Zend_Form();
        $yKienForm->setAction($this->_request->getBaseUrl().'/lien-he')
                ->setMethod('post')
				->addElement($name)
				->addElement($email)
				->addElement($phone)
                ->addElement($address)
				->addElement($comment)
                ->addElement($submit);

        return $yKienForm;
    }
	
	public function quangCaoAction()
    {
		$request = $this->getRequest();
        $yKienForm = $this->getQuangCaoForm();
		
		$errorMessage = "";

        if($request->isPost())
        {
			if(Zend_Auth::getInstance()->hasIdentity())
			{
				$db = Zend_Registry::get('db');
											
				$db->insert('comment', array('name' => Zend_Auth::getInstance()->getStorage()->read()->name,
											'email' => Zend_Auth::getInstance()->getStorage()->read()->email,
											'phone' => Zend_Auth::getInstance()->getStorage()->read()->phone,
											'address' => Zend_Auth::getInstance()->getStorage()->read()->address,
											'comment' => $request->getParam('comment')));

				$this->sendDKQuangCaoMail(Zend_Auth::getInstance()->getStorage()->read()->name,
										Zend_Auth::getInstance()->getStorage()->read()->email,
										Zend_Auth::getInstance()->getStorage()->read()->phone,
										Zend_Auth::getInstance()->getStorage()->read()->address,
										$request->getParam('comment'));
				
				$this->redirect('/lien-he/cam-on');
			}
			else
			{
				if($yKienForm->isValid($request->getPost()))
				{
					$db = Zend_Registry::get('db');
				
					$db->insert('comment', array('name' => $yKienForm->getValue('name'),
											'email' => $yKienForm->getValue('email'),
											'phone' => $yKienForm->getValue('phone'),
											'address' => $yKienForm->getValue('address'),
											'comment' => $yKienForm->getValue('comment')));

					$this->sendDKQuangCaoMail($yKienForm->getValue('name'),
											$yKienForm->getValue('email'),
											$yKienForm->getValue('phone'),
											$yKienForm->getValue('address'),
											$yKienForm->getValue('comment'));
					
					$this->redirect('/lien-he/cam-on');
				}
			}
		}
		
		$this->view->yKienForm = $yKienForm;
    }
	
	protected function sendDKQuangCaoMail($name, $email, $phone, $address, $comment){
	
		if (file_exists(COMMENT_MAIL_TEMPLATE)) {
				
			$content = file_get_contents(COMMENT_MAIL_TEMPLATE);
			
			$content = str_replace("#name#", $name, $content);
			$content = str_replace("#email#", $email, $content);
			$content = str_replace("#phone#", $phone, $content);
			$content = str_replace("#address#", $address, $content);
			$content = str_replace("#comment#", $comment, $content);
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyText($content);
			$mail->setBodyHtml($content);
			$mail->setFrom('maxi2013@maximark.vn', '[Hộp mail tự động] Không trả lời');
			foreach(Zend_Registry::get('ADMIN_MAIL_ADDRESS') as $item){
				$mail->addTo($item);
			}
			$mail->setSubject('[Hộp mail tự động] Thư đăng ký quảng cáo');
			$mail->send();
		}
	}
	
	/**
     * Create and return the register form
     *
     * @return object
     */
    protected function getQuangCaoForm()
    {
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(true)
			->setValue('register')
			->removeDecorator('DtDdWrapper');
			
		$title = new Zend_Form_Element_Title('title');
		$title->setValue('Đăng ký quảng cáo')
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
				
		$phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Số điện thoại:')
				->addValidator('Digits', true);
				
		$phone->getValidator('Digits')->setMessage('Vui lòng điền đúng số điện thoại.');
		
		$address = new Zend_Form_Element_Textarea('address');
        $address->setLabel('Địa chỉ:')
				->setAttrib('rows', '3');
		
		$comment = new Zend_Form_Element_Textarea('comment');
        $comment->setLabel('Nội dung quảng cáo:')
				->setAttrib('rows', '5');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Gửi')
				->removeDecorator('DtDdWrapper');
				
		if(Zend_Auth::getInstance()->hasIdentity()){
			$name->setValue(Zend_Auth::getInstance()->getStorage()->read()->name)
				->setAttrib('disable', true);
			$email->setValue(Zend_Auth::getInstance()->getStorage()->read()->email)
				->setAttrib('disable', true);
			$phone->setValue(Zend_Auth::getInstance()->getStorage()->read()->phone)
				->setAttrib('disable', true);
			$address->setValue(Zend_Auth::getInstance()->getStorage()->read()->address)
				->setAttrib('disable', true);
		}
		
        $yKienForm = new Zend_Form();
        $yKienForm->setAction($this->_request->getBaseUrl().'/lien-he')
                ->setMethod('post')
				->addElement($id)
				->addElement($title)
				->addElement($name)
				->addElement($email)
				->addElement($phone)
                ->addElement($address)
				->addElement($comment)
                ->addElement($submit);

        return $yKienForm;
    }
	
	public function camOnAction()
	{
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Cám ơn");
	}
}

