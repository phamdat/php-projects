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
		Zend_Layout::getMvcInstance()->assign('inIframe', true);
        
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
    
    public function addAction()
    {		
		$name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
            ->setRequired(true)
            ->setAttrib('class', 'form-control validate[required]')
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Please input menu name.');
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $configurationForm = $configurationForm = new Zend_Form();
        $configurationForm->setAction($this->_request->getBaseUrl() . '/admin/slide/add')
                ->setMethod('post')
                ->addElement($name)
                ->addElement($submit);
        
        if($this->_request->isPost())
        {
            if($configurationForm->isValid($this->_request->getPost()))
			{                
                $this->redirect('/admin/slide/detail/name/'.$configurationForm->getValue('name'));
            }
        }
        
        $this->view->configurationForm = $configurationForm;
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
					'type'             => SLIDE,
                    'description'      => $slideForm->getValue('description'),
                    'en_description'   => $slideForm->getValue('en_description'),
                    'thumbnail'        => $this->uploadFile($slideForm->getValue('thumbnail')),
                    'redirect_url'     => $slideForm->getValue('redirect_url'),
                    'price'            => $slideForm->getValue('price'),
                    'old_price'        => $slideForm->getValue('old_price'),
                    'creator_id'       => Zend_AdminAuth::getInstance()->getStorage()->read()->id
				);
				
				$db = Zend_Registry::get('db');
                
                if($slideForm->getValue('post_id')){
					$n = $db->update('post', $data, array('id = ?' => $slideForm->getValue('post_id')));
				}else{
					$n = $db->insert('post', $data);
                    $slideForm->getElement('post_id')->setValue($db->lastInsertId());
				}
                
				$data = array(
					'name'             => $slideForm->getValue('name'),
                    'post_id'          => $slideForm->getValue('post_id'),
                    'link_id'          => $slideForm->getValue('link_id')
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
        
        //---------------------------------------------------------------------------------------------------------------------------
        
        $postId = new Zend_Form_Element_Hidden('post_id');
		
        //---------------------------------------------------------------------------------------------------------------------------
        
        $name = new Zend_Form_Element_Hidden('name');
        $name->setValue($this->_request->getParam('name'));
        
        //---------------------------------------------------------------------------------------------------------------------------
                
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('Image')
					->addValidator('Count', false, 1)
					->addValidator('Size', false, 2097152)
					->addValidator('Extension', false, 'jpg,png,gif');
					
		$file->getValidator('Count')->setMessage('Chỉ được up một file.');
		$file->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
		$file->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');
		
		if(!$this->_request->getParam('thumbnail')){
			$file->setRequired(true)->addValidator('Upload', true);
						
			$file->getValidator('Upload')->setMessage('Kích thước tối đa là 2MB.', Zend_Validate_File_Upload::INI_SIZE);
			$file->getValidator('Upload')->setMessage('Vui lòng up hình đại diện.', Zend_Validate_File_Upload::NO_FILE);			
		}
		
		$thumbnail = new Zend_Form_Element_Hidden('thumbnail');

        //---------------------------------------------------------------------------------------------------------------------------
        
        $redirectUrl = new Zend_Form_Element_Text('redirect_url');
        $redirectUrl->setLabel('Redirect url');

        //---------------------------------------------------------------------------------------------------------------------------
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Description')
                    ->setAttrib('rows', '3');
        
        //---------------------------------------------------------------------------------------------------------------------------
        
        $enDescription = new Zend_Form_Element_Textarea('en_description');
        $enDescription->setLabel('English description')
                        ->setAttrib('rows', '3');
        
        //---------------------------------------------------------------------------------------------------------------------------
        
        $price = new Zend_Form_Element_Text('price');
        $price->setLabel('Position x');
        
        //---------------------------------------------------------------------------------------------------------------------------
        
        $oldPrice = new Zend_Form_Element_Text('old_price');
        $oldPrice->setLabel('Position y');
        
        //---------------------------------------------------------------------------------------------------------------------------
        
        $linkId = new Zend_Form_Element_Select('link_id');
        $linkId->setLabel('Linked post');

        $postList = $db->select()
					->from(array('p' => 'post'))
					->columns('*', 'p')
					->query()
					->fetchAll();
        
        $linkId->addMultiOption(null, 'None');
        foreach($postList as $post){
			$linkId->addMultiOption($post['id'], $post['title']);
		}
        
        //---------------------------------------------------------------------------------------------------------------------------
                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');

        //---------------------------------------------------------------------------------------------------------------------------
        
        if($slide){
            $id->setValue($slide['slide']['id']);
            $name->setValue($slide['slide']['name']);            
            $postId->setValue($slide['slide']['post_id']);
            $linkId->setValue($slide['slide']['link_id']);
            $thumbnail->setValue($slide['post']['thumbnail']);
            $redirectUrl->setValue($slide['post']['redirect_url']);
            $description->setValue($slide['post']['description']);
            $enDescription->setValue($slide['post']['en_description']);
            $price->setValue($slide['post']['price']);
            $oldPrice->setValue($slide['post']['old_price']);
        }
        
        $slideForm = new Zend_Form();
        $slideForm->setAction($this->_request->getBaseUrl().'/admin/slide/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($postId)
                    ->addElement($linkId)
                    ->addElement($thumbnail)
                    ->addElement($file)
                    ->addElement($redirectUrl)
                    ->addElement($description)
                    ->addElement($enDescription)
                    ->addElement($price)
                    ->addElement($oldPrice)
					->addElement($submit);

        return $slideForm;
    }
}

