<?php

class Admin_SlideController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('icon', 'indent');
        Zend_Layout::getMvcInstance()->assign('title', 'Slide');
        
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {
		if(isset(Zend_Registry::get('slide')[$this->_request->getParam('name')]))
        {
            $this->view->slide = Zend_Registry::get('slide')[$this->_request->getParam('name')];
        }
        else
        {
            $this->view->slide = array();
        }
    }
	

	public function detailAction()
	{
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
            foreach(Zend_Registry::get('slide')[$this->_request->getParam('name')] as $slide)
            {
                if($slide['slide']['id'] == $this->_request->getParam('id'))
                {
                    $slideForm = $this->getSlideForm($slide);
                    break;
                }
            }
		}
		else
		{
			$slideForm = $this->getSlideForm();
		}
		
		if($this->_request->isPost())
        {
            if($slideForm->isValid($this->_request->getPost()))
			{
				$data = array(
					'name'             => $slideForm->getValue('name'),
                    'value'            => $this->uploadFile($slideForm->getValue('value')),
                    'post_id'          => $slideForm->getValue('post_id')
				);
				
				$db = Zend_Registry::get('db');
				
				if($slideForm->getValue('id')){
					$n = $db->update('configuration', $data, array('id = ?' => $slideForm->getValue('id')));
				}else{
					$n = $db->insert('configuration', $data);
				}
				
				$this->redirect('/admin/slide/index/name/' . $slideForm->getValue('name'));
			}
		}
		
		$this->view->slideForm = $slideForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('configuration', 'id = ' . $this->_request->getParam('id'));
        
        $n = $db->delete('configuration', 'link_id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/slide/index/name/' . $this->_request->getParam('name'));
    }
    
    protected function uploadFile($oldName=null)
    {
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();

        $fileAdapter->setDestination(MEDIA_DIRECTORY);

        $file = 'file';

        $infos = $fileAdapter->getFileInfo();
        $info = $infos['file'];
        
        $newName = $oldName;

        if($info['name']){
            $date = Zend_Date::now();
            $name = md5($date->getTimestamp() . $info['name']);
            $extension = pathinfo($info['name'], PATHINFO_EXTENSION);
            $fullName = $name . '.' . $extension;
            $fileAdapter->addFilter('Rename', MEDIA_DIRECTORY . $fullName, $file);
            if ($fileAdapter->receive($file)) 
            {
                $newName = MEDIA_PATH . $fullName;
            }
        }
        
        return $newName;
    }
	
    protected function getSlideForm($slide=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
		
        //---------------------------------------------------------------------------------------------------------------------------
        
        $name = new Zend_Form_Element_Hidden('name');
        $name->setRequired(false)
			->setValue($this->_request->getParam('name'));
        
        //---------------------------------------------------------------------------------------------------------------------------
                
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('Image')
					->setRequired(false)
                    ->addValidator('Count', false, 1)
					->addValidator('Size', false, 2097152)
					->addValidator('Extension', false, 'jpg,png,gif');
					
		$file->getValidator('Count')->setMessage('Chỉ được up một file.');
		$file->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
		$file->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');
		
		if(!$this->_request->getParam('value')){
			$file->setRequired(true)->addValidator('Upload', true);
						
			$file->getValidator('Upload')->setMessage('Kích thước tối đa là 2MB.', Zend_Validate_File_Upload::INI_SIZE);
			$file->getValidator('Upload')->setMessage('Vui lòng up hình đại diện.', Zend_Validate_File_Upload::NO_FILE);			
		}
		
		$value = new Zend_Form_Element_Hidden('value');
		$value->setRequired(false);

        //---------------------------------------------------------------------------------------------------------------------------
        
        $postId = new Zend_Form_Element_Select('post_id');
        $postId->setLabel('Linked post')
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Please select linked post.');

        $postList = $db->select()
					->from(array('p' => 'post'))
					->columns('*', 'p')
					->query()
					->fetchAll();
        
        foreach($postList as $post){
			$postId->addMultiOption($post['id'], $post['title']);
		}
        
        //---------------------------------------------------------------------------------------------------------------------------
                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');

        //---------------------------------------------------------------------------------------------------------------------------
        
        if($slide){
            $id->setValue($slide['slide']['id']);
            $name->setValue($slide['slide']['name']);
            $value->setValue($slide['slide']['value']);
            $postId->setValue($slide['slide']['post_id']);
        }
        
        $slideForm = new Zend_Form();
        $slideForm->setAction($this->_request->getBaseUrl().'/admin/slide/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($value)
                    ->addElement($file)
                    ->addElement($postId)					
					->addElement($submit);

        return $slideForm;
    }
}

