<?php

class Layout extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
		switch($request->getModuleName()){
			case 'admin':
				$layout = Zend_Layout::getMvcInstance();
			
				$layout->setLayout('admin');
				
				break;
			case 'third-party':
				$layout = Zend_Layout::getMvcInstance();
			
				$layout->setLayout('third-party');
				
				break;
            case 'component':
				$layout = Zend_Layout::getMvcInstance();
			
				$layout->setLayout('component');
				
				break;
			case 'default':
			default:
				$layout = Zend_Layout::getMvcInstance();
			
				$view = $layout->getView();
				
				break;
		}
    }
}

