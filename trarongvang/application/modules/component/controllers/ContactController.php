<?php

class Component_ContactController extends Zend_Controller_Action
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

    public function contactAction()
    {
		$request = $this->getRequest();
        $contactForm = $this->getContactForm();
        $recaptcha = new Zend_Service_ReCaptcha('6Le5kL8SAAAAACBclv8qe2MQR41a4Y1TKTaGBQFM', '6Le5kL8SAAAAAOsm-96QG3GzUAFg3TFNPB8XyACB');
        
        if($request->isPost())
        {
            if($contactForm->isValid($request->getPost()))
            {
                $result = $recaptcha->verify(
                    '6Ld-y_8SAAAAAOydw0Lj9WNk6RFZCbRogqzaDgUA',
                    $_POST['g-recaptcha-response']
                );
                
                if (!$result->isValid()) 
                {
                    $db = Zend_Registry::get('db');


                    $this->sendContactMail($contactForm->getValue('name'),
                                            $contactForm->getValue('email'),
                                            $contactForm->getValue('phone'),
                                            $contactForm->getValue('address'),
                                            $contactForm->getValue('comment'));

                    $this->view->successMessage = 'Cảm ơn đã liên hệ với chúng tôi. Chúng tối sẽ liên hệ với bạn trong ngày hôm nay.';
                }
                else
                {
                    $this->view->errorMessage = 'Mã chứng thực ko đúng. Vui lòng nhập lại';
                }
            }
            else
            {
                $this->view->errorMessage = 'Nhập thiếu thông tin. Vui lòng nhập thêm thông tin cần thiết.';
            }
        }

        $this->view->recaptcha = $recaptcha;
		$this->view->contactForm = $contactForm;
    }
	
	protected function sendContactMail($name, $email, $phone, $address, $comment)
    {
		if (file_exists(CONTACT_MAIL_TEMPLATE)) {
				
			$content = file_get_contents(CONTACT_MAIL_TEMPLATE);
			
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
	
    protected function getContactForm()
    {
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Họ tên:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng điền họ tên.')
                ->setValue($this->_request->getParam('name'));
				
		$email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email:')
                ->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('EmailAddress', true)
                ->setValue($this->_request->getParam('email'));
				
		$email->getValidator('NotEmpty')->setMessage('Vui lòng điền email.');
		$email->getValidator('EmailAddress')->setMessage('Vui lòng điền đúng email.');
				
		$phone = new Zend_Form_Element_Text('phone');
        $phone->setLabel('Số điện thoại:')
				->addValidator('Digits', true)
                ->setValue($this->_request->getParam('phone'));
				
		$phone->getValidator('Digits')->setMessage('Vui lòng điền đúng số điện thoại.');
		
		$address = new Zend_Form_Element_Text('address');
        $address->setLabel('Địa chỉ:')
                ->setValue($this->_request->getParam('address'));
		
		$comment = new Zend_Form_Element_Textarea('comment');
        $comment->setLabel('Ý kiến:')
				->setAttrib('rows', '3')
                ->setValue($this->_request->getParam('comment'));

        $submit = new   Zend_Form_Element_Button('submit');
        $submit->setLabel('Gửi')
                ->setAttrib('class', 'btn btn-primary')
                ->setAttrib('disabled', 'disabled');
		
        $contactForm = new Zend_Form();
        $contactForm->setAction($this->_request->getBaseUrl().'/component/contact/contact')
                ->setMethod('post')
				->addElement($name)
				->addElement($email)
				->addElement($phone)
                ->addElement($address)
				->addElement($comment)
                ->addElement($submit);

        return $contactForm;
    }
	
	public function camOnAction()
	{
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Cám ơn");
	}
}

