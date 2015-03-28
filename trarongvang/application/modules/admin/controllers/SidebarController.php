<?php

class Admin_SidebarController extends Zend_Controller_Action
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
		if(isset(Zend_Registry::get('sidebar')[$this->_request->getParam('name')]))
        {
            $this->view->sidebar = Zend_Registry::get('sidebar')[$this->_request->getParam('name')];
        }
        else
        {
            $this->view->sidebar = array();
        }
    }
	

	public function detailAction()
	{
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
            foreach(Zend_Registry::get('sidebar')[$this->_request->getParam('name')] as $sidebar)
            {
                if($sidebar['sidebar']['id'] == $this->_request->getParam('id'))
                {
                    $sidebarForm = $this->getSidebarForm($sidebar);
                    break;
                }
            }
		}
		else
		{
			$sidebarForm = $this->getSidebarForm();
		}
		
		if($this->_request->isPost())
        {
            if($sidebarForm->isValid($this->_request->getPost()))
			{
                $data = array(
					'type'             => SIDEBAR,
                    'title'            => $sidebarForm->getValue('title'),
                    'content'          => $sidebarForm->getValue('content'),
                    'has_title'        => $sidebarForm->getValue('has_title'),
                    'creator_id'       => Zend_AdminAuth::getInstance()->getStorage()->read()->id
				);
				
				$db = Zend_Registry::get('db');
                
                if($sidebarForm->getValue('post_id')){
					$n = $db->update('post', $data, array('id = ?' => $sidebarForm->getValue('post_id')));
				}else{
					$n = $db->insert('post', $data);
                    $sidebarForm->getElement('post_id')->setValue($db->lastInsertId());
				}
                
				$data = array(
					'name'             => $sidebarForm->getValue('name'),
                    'post_id'          => $sidebarForm->getValue('post_id')
				);
				
				if($sidebarForm->getValue('id')){
					$n = $db->update('configuration', $data, array('id = ?' => $sidebarForm->getValue('id')));
				}else{
					$n = $db->insert('configuration', $data);
				}
				
				$this->redirect('/admin/sidebar/index/name/' . $sidebarForm->getValue('name'));
			}
		}
		
		$this->view->sidebarForm = $sidebarForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('configuration', 'id = ' . $this->_request->getParam('id'));
        
        $n = $db->delete('post', 'id = ' . $this->_request->getParam('post_id'));
		
		$this->redirect('/admin/sidebar/index/name/' . $this->_request->getParam('name'));
    }
    
    public function swapAction()
    {
		$db = Zend_Registry::get('db');
        
        $orderId = 1;
        $aboveOrderId = 2;
        if($this->_request->getParam('order_id'))
        {
            $orderId = $this->_request->getParam('order_id');
        }
        if($this->_request->getParam('above_order_id'))
        {
            $aboveOrderId = $this->_request->getParam('above_order_id');
        }
        if($orderId == $aboveOrderId)
        {
            $aboveOrderId++;
        }
		
		$n = $db->update('post', array('order_id' => $aboveOrderId), array('id = ?' => $this->_request->getParam('post_id')));
        
        $n = $db->update('post', array('order_id' => $orderId), array('id = ?' => $this->_request->getParam('above_post_id')));
		
		$this->redirect('/admin/sidebar/index/name/' . $this->_request->getParam('name'));
    }
    
	
    protected function getSidebarForm($sidebar=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
		
        $name = new Zend_Form_Element_Hidden('name');
        $name->setRequired(false)
			->setValue($this->_request->getParam('name'));
        
        $postId = new Zend_Form_Element_Hidden('post_id');
        $postId->setRequired(false);
        
        
        
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title')
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập tiêu đề.');
        
        
                
        $content = new Zend_Form_Element_Textarea('content');
        $content->setLabel('Content')
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập nội dung.')
                ->setAttrib('rows', '5')
                ->setAttrib('class', 'form-control input-editor');
        
        
        $hasTitle = new Zend_Form_Element_Checkbox('has_title');
        $hasTitle->setLabel('Hiện title')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary');

        
        
        
        if($sidebar){
            $id->setValue($sidebar['sidebar']['id']);
            $name->setValue($sidebar['sidebar']['name']);            
            $postId->setValue($sidebar['sidebar']['post_id']);
            $title->setValue($sidebar['post']['title']);
            $content->setValue($sidebar['post']['content']);
            $hasTitle->setValue($sidebar['post']['has_title']);
        }
        
        $sidebarForm = new Zend_Form();
        $sidebarForm->setAction($this->_request->getBaseUrl().'/admin/sidebar/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($postId)
                    ->addElement($title)
                    ->addElement($content)
                    ->addElement($hasTitle)
					->addElement($submit);

        return $sidebarForm;
    }
}

