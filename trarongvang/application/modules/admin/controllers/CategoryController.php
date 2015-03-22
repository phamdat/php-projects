<?php

class Admin_CategoryController extends Zend_Controller_Action
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
        $this->view->category = Zend_Registry::get('postCategory');
    }

	public function detailAction()
	{	
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
            $category = $db->select()
					->from(array('p' => 'post_category'))
					->columns('*', 'p')
                    ->where('p.id = ?', $this->_request->getParam('id'))
					->query()
					->fetchAll()[0];
            
            $categoryForm = $this->getCategoryForm($category);
		}
		else
		{
			$categoryForm = $this->getCategoryForm();
		}
		
		if($this->_request->isPost())
        {
            if($categoryForm->isValid($this->_request->getPost()))
			{
				$data = array(
					'name'             => $categoryForm->getValue('name'),
                    'display_name'     => $categoryForm->getValue('display_name'),
					'link_id'          => $categoryForm->getValue('link_id')
				);
				
				$db = Zend_Registry::get('db');
				
				if($categoryForm->getValue('id')){
					$n = $db->update('post_category', $data, array('id = ?' => $categoryForm->getValue('id')));
				}else{
					$n = $db->insert('post_category', $data);
				}
				
				$this->redirect('/admin/category/index');
			}
		}
		
		$this->view->categoryForm = $categoryForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('post_category', 'id = ' . $this->_request->getParam('id'));
        
        $n = $db->delete('post_category', 'link_id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/category/index');
    }
	
    protected function getCategoryForm($category=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
		
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Vui lòng nhập tên.');
        
        
        
        
        $displayName = new Zend_Form_Element_Text('display_name');
        $displayName->setLabel('Display')
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Vui lòng nhập tên hiển thị.')
            ->setRequired(true);

        
        
        
        $linkId = new Zend_Form_Element_Select('link_id');
        $linkId->setLabel('Parent category')
				->setRequired(false);

        $categoryList = $db->select()
					->from(array('p' => 'post_category'))
					->columns('*', 'p')
					->query()
					->fetchAll();
        
        $linkId->addMultiOption(null, 'Root');
        foreach($categoryList as $item){
			$linkId->addMultiOption($item['id'], $item['display_name']);
		}
        
        $linkId->setValue($this->_request->getParam('link_id'));
        
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary');

        
        
        
        if($category){
            $id->setValue($category['id']);
            $name->setValue($category['name']);
            $displayName->setValue($category['display_name']);
            $linkId->setValue($category['link_id']);
        }
        
        $categoryForm = new Zend_Form();
        $categoryForm->setAction($this->_request->getBaseUrl().'/admin/category/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($displayName)        
                    ->addElement($linkId)					
					->addElement($submit);

        return $categoryForm;
    }
}

