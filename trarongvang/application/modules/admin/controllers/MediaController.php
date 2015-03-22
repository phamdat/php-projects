<?php

class Admin_MediaController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName() . '-content');
		
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {
        $files = scandir(MEDIA_DIRECTORY);
        
        function sort_by_mtime($file1,$file2) 
        {
            $time1 = filemtime(MEDIA_DIRECTORY . $file1);
            $time2 = filemtime(MEDIA_DIRECTORY . $file2);
            if ($time1 == $time2) {
                return 0;
            }
            return ($time1 < $time2) ? 1 : -1;
        }
        
        usort($files, "sort_by_mtime");
        
        foreach($files as $i => $f)
        {
            if($f == '.' || $f == '..')
            {
                unset($files[$i]);
            }
        }
        
		$this->view->files = $files;
    }
	

	public function uploadAction()
	{
		$adapter = new Zend_File_Transfer_Adapter_Http();
 
		$adapter->setDestination(MEDIA_DIRECTORY);
		
		$db = Zend_Registry::get('db');
		
		$receivingOK = true; 
		
		foreach ($adapter->getFileInfo() as $file => $info) 
		{
			$date = Zend_Date::now();
			$name = md5($date->getTimestamp() . $info['name']);
			$extension = pathinfo($info['name'], PATHINFO_EXTENSION);
			$fullName = $name . '.' . $extension;
			$adapter->addFilter('Rename', MEDIA_DIRECTORY . $fullName, $file);
			if ($adapter->receive($file)) 
			{
			}
		}
		
		$this->redirect('/admin/media');
	}
	
	
	public function deleteAction()
    {		
		$this->redirect('/admin/media');
    }
}

