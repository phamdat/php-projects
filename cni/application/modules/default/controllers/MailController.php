<?php

class MailController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {

    }

    public function sendContactAction()
    {
        $request = $this->getRequest();

        if($request->isPost()) 
        {
            $this->sendContactMail($this->_request->getParam('name'),
                                    $this->_request->getParam('email'),
                                    $this->_request->getParam('phone'),
                                    $this->_request->getParam('address'),
                                    $this->_request->getParam('comment'),
                                    $this->_request->getParam('subject'));
        }
        exit();	
    }
	
	protected function sendContactMail($name, $email, $phone, $address, $comment, $subject){
	
		if (file_exists(CONTACT_MAIL_TEMPLATE)) {
				
			$content = file_get_contents(CONTACT_MAIL_TEMPLATE);
			
			$content = str_replace("#name#", $name, $content);
			$content = str_replace("#email#", $email, $content);
			$content = str_replace("#phone#", $phone, $content);
			$content = str_replace("#address#", $address, $content);
			$content = str_replace("#comment#", $comment, $content);
            $content = str_replace("#subject#", $subject, $content);
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setBodyText($content);
			$mail->setBodyHtml($content);
			$mail->setFrom('infovietnam@cni-global.com', '[Hộp mail tự động] Không trả lời');
			foreach(Zend_Registry::get('ADMIN_MAIL_ADDRESS') as $item){
				$mail->addTo($item);
			}
			$mail->setSubject('[Hộp mail tự động] Thư góp ý');
			$mail->send();
		}
	}
}

