<?php

class Admin_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */		
    }
	
	public function preDispatch()
    {

    }

    public function indexAction()
    {
        // action body
		$this->redirect('/admin/post');
    }

}

