<?php

class Admin_MenuController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('icon', 'sitemap');
        Zend_Layout::getMvcInstance()->assign('title', 'Menu');
        
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {	
        
        
		if(isset(Zend_Registry::get('menu')[$this->_request->getParam('name')]))
        {
            $this->view->menu = Zend_Registry::get('menu')[$this->_request->getParam('name')];
        }
        else
        {
            $this->view->menu = array();
        }
    }
	

	public function detailAction()
	{
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
            foreach(Zend_Registry::get('menu')[$this->_request->getParam('name')] as $menu)
            {
                if($menu['menu']['id'] == $this->_request->getParam('id'))
                {
                    $menuForm = $this->getMenuForm($menu);
                    break;
                }
                else
                {
                    foreach($menu['submenu'] as $submenu)
                    {
                        if($submenu['menu']['id'] == $this->_request->getParam('id'))
                        {
                            $menuForm = $this->getMenuForm($submenu);
                            break;
                        }
                    }
                }
            }
		}
		else
		{
			$menuForm = $this->getMenuForm();
		}
		
		if($this->_request->isPost())
        {
            if($menuForm->isValid($this->_request->getPost()))
			{
				$data = array(
					'name'             => $menuForm->getValue('name'),
                    'value'             => $menuForm->getValue('value'),
                    'post_id'          => $menuForm->getValue('post_id'),
					'link_id'          => $menuForm->getValue('link_id')
				);
				
				$db = Zend_Registry::get('db');
				
				if($menuForm->getValue('id')){
					$n = $db->update('configuration', $data, array('id = ?' => $menuForm->getValue('id')));
				}else{
					$n = $db->insert('configuration', $data);
				}
				
				$this->redirect('/admin/menu/index/name/' . $menuForm->getValue('name'));
			}
		}
		
		$this->view->menuForm = $menuForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('configuration', 'id = ' . $this->_request->getParam('id'));
        
        $n = $db->delete('configuration', 'link_id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/menu/index/name/' . $this->_request->getParam('name'));
    }
	
    protected function getMenuForm($menu=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
		
        $name = new Zend_Form_Element_Hidden('name');
        $name->setRequired(false)
			->setValue($this->_request->getParam('name'));
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $linkId = new Zend_Form_Element_Select('link_id');
        $linkId->setLabel('Parent Menu')
				->setRequired(false);
        
        $linkId->addMultiOption(null, 'Root');
        if(isset(Zend_Registry::get('menu')[$this->_request->getParam('name')]))
        {
            foreach(Zend_Registry::get('menu')[$this->_request->getParam('name')] as $im)
            {
                $linkId->addMultiOption($im['menu']['id'], $im['post']['title']);
            }
        }
        
        $linkId->setValue($this->_request->getParam('link_id'));

        //-------------------------------------------------------------------------------------------------------------------------        
        
        $value = new Zend_Form_Element_Text('value');
        $value->setLabel('Name')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $postId = new Zend_Form_Element_Select('post_id');
        $postId->setLabel('Post')
				->setRequired(true)
                ->setAttrib('class', 'form-control validate[required]')
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng chọn bài viết.');

        $postList = $db->select()
					->from(array('p' => 'post'))
                    ->where('p.type = ?', PAGE)
                    ->where('p.category like ?', '%,MAIN_PAGE,%')
					->columns('*', 'p')
					->query()
					->fetchAll();
        
        foreach($postList as $post){
			$postId->addMultiOption($post['id'], $post['title']);
		}
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');

        //-------------------------------------------------------------------------------------------------------------------------        
        
        if($menu){
            $id->setValue($menu['menu']['id']);
            $name->setValue($menu['menu']['name']);
            $value->setValue($menu['menu']['value']);
            $linkId->setValue($menu['menu']['link_id']);
            $postId->setValue($menu['menu']['post_id']);
        }
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $menuForm = new Zend_Form();
        $menuForm->setAction($this->_request->getBaseUrl().'/admin/menu/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($value)
                    ->addElement($linkId)        
                    ->addElement($postId)					
					->addElement($submit);

        return $menuForm;
    }
}
