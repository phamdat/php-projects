<?php

class ThirdParty_UploadMediaController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {
		$db = Zend_Registry::get('db');
		
		$media = $db->select()
						->from(array('m' => 'media'))
						->join(array('u' => 'user'), 'u.id = m.creater_id', array('*', 'creater_id'=>'u.id'))
						->columns('*', 'm')
						->order(array('m.created_datetime DESC'))
						->query()
						->fetchAll();
						
		$adapter = new Zend_Paginator_Adapter_DbSelect(
			$db->select()
				->from(array('m' => 'media'))
				->join(array('u' => 'user'), 'u.id = m.creater_id', array('*', 'creater_id'=>'u.id'))
				->columns('*', 'm')
				->order(array('m.created_datetime DESC'))
		);
		
		$adapter->setRowCount(
			$db->select()
				->from(array('m' => 'media'))
				->join(array('u' => 'user'), 'u.id = m.creater_id', array('*', 'creater_id'=>'u.id'))
				->reset( Zend_Db_Select::COLUMNS )
				->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
		);
		
		$paginator = new Zend_Paginator($adapter);
		
		$paginator->setCurrentPageNumber($this->_request->getParam('page'));
						
		$this->view->paginator = $paginator;
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
				$data = array(
					'path' 			=> $fullName,
					'type'      	=> 'P',
					'creater_id'    => Zend_AdminAuth::getInstance()->getStorage()->read()->id
				);
				$n = $db->insert('media', $data);
			} 
			else 
			{
				$receivingOK = false;
			}
		}
		
		if (!$receivingOK) {
			//TODO: vang loi
		}
		
		$this->redirect('/admin/upload-media');
	}
	
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$media = $db->select()
					->from(array('m' => 'media'))
					->where('m.id = ?', $this->_request->getParam('id'))
					->query()
					->fetchAll();
		
		unlink(MEDIA_DIRECTORY . $media[0]['path']);
		
		$n = $db->delete('media', 'id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/upload-media');
    }
}

