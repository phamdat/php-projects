<?php

class ThirdParty_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('section', "one");
		
		if(!Zend_ThirdPartyAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/third-party/login');
        }
    }

    public function indexAction()
    {
        // action body
		//$this->redirect('/third-party/product');
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Dashboard");
    }


}

