<?php

class Admin_DashboardController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */		
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('inIframe', true);
        
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {
        
    }

}

