<?php

class Admin_UserController extends Zend_Controller_Action
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
        $db = Zend_Registry::get('db');
        
        $this->view->user = $db->select()
				                ->from(array('u' => 'user'))
                                ->query()
                                ->fetchAll();
    }
	

	public function detailAction()
	{
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
            
            $user = $db->select()
                            ->from(array('u' => 'user'))
                            ->where('u.id = ?', $this->_request->getParam('id'))
                            ->query()
                            ->fetchAll()[0];
            
            $userForm = $this->getUserForm($user);
		}
		else
		{
			$userForm = $this->getUserForm();
		}
		
		if($this->_request->isPost())
        {
            if($userForm->isValid($this->_request->getPost()))
			{
				$data = array(
					'name'          => $userForm->getValue('name'),
                    'username'      => $userForm->getValue('username'),
					'password'      => md5($userForm->getValue('password')),
                    'role'          => ',' . implode(',', $userForm->getValue('role')) . ','
				);
				
				$db = Zend_Registry::get('db');
				
				if($userForm->getValue('id')){
					$n = $db->update('user', $data, array('id = ?' => $userForm->getValue('id')));
				}else{
					$n = $db->insert('user', $data);
				}
				
				$this->redirect('/admin/user/index');
			}
		}
		
		$this->view->userForm = $userForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('user', 'id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/user/index');
    }    
	
    protected function getUserForm($user=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
		
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Display name')
                ->setRequired(true)
                ->setAttrib('class', 'form-control validate[required]')
                ->addValidator('NotEmpty', true)
				->addErrorMessage('Please input display name.');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username')
                ->setRequired(true)
                ->setAttrib('class', 'form-control validate[required]')
                ->addValidator('NotEmpty', true)
				->addErrorMessage('Please input username.');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password')
                ->setRequired(true)
                ->setAttrib('class', 'form-control validate[required]')
                ->addValidator('NotEmpty', true)
				->addErrorMessage('Please input password.');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $role = new Zend_Form_Element_Multiselect('role');
        $role->setLabel('Role')
                ->setRequired(true)
                ->addValidator('NotEmpty', true)
				->addErrorMessage('Please select role.');
        
        foreach(Zend_Registry::get('allCategory') as $category){
			$role->addMultiOption($category['name'] . ':READONLY', $category['display_name'] . '(readonly)');
            $role->addMultiOption($category['name'] . ':ADD', $category['display_name'] . '(add)');
            $role->addMultiOption($category['name'] . ':EDIT', $category['display_name'] . '(edit)');
            $role->addMultiOption($category['name'] . ':DELETE', $category['display_name'] . '(delete)');
		}
        
        $role->addMultiOption('MENU:READONLY', 'Menu(readonly)');
        $role->addMultiOption('MENU:ADD', 'Menu(add)');
        $role->addMultiOption('MENU:EDIT', 'Menu(edit)');
        $role->addMultiOption('MENU:DELETE', 'Menu(delete)');
        
        $role->addMultiOption('SIDEBAR:READONLY', 'Sidebar(readonly)');
        $role->addMultiOption('SIDEBAR:ADD', 'Sidebar(add)');
        $role->addMultiOption('SIDEBAR:EDIT', 'Sidebar(edit)');
        $role->addMultiOption('SIDEBAR:DELETE', 'Sidebar(delete)');
        
        $role->addMultiOption('SLIDE:READONLY', 'Slide(readonly)');
        $role->addMultiOption('SLIDE:ADD', 'Slide(add)');
        $role->addMultiOption('SLIDE:EDIT', 'Slide(edit)');
        $role->addMultiOption('SLIDE:DELETE', 'Slide(delete)');
        
        $role->addMultiOption('CONFIGURATION:READONLY', 'Configuration(readonly)');
        $role->addMultiOption('CONFIGURATION:EDIT', 'Configuration(edit)');
        
        $role->addMultiOption('MEDIA:READONLY', 'Media(readonly)');
        $role->addMultiOption('MEDIA:ADD', 'Media(add)');
        $role->addMultiOption('MEDIA:DELETE', 'Media(delete)');
        
        $role->addMultiOption('CATEGORY:READONLY', 'Category(readonly)');
        $role->addMultiOption('CATEGORY:ADD', 'Category(add)');
        $role->addMultiOption('CATEGORY:EDIT', 'Category(edit)');
        $role->addMultiOption('CATEGORY:DELETE', 'Category(delete)');
        
        $role->addMultiOption('USER:READONLY', 'User(readonly)');
        $role->addMultiOption('USER:ADD', 'User(add)');
        $role->addMultiOption('USER:ADD', 'User(edit)');
        $role->addMultiOption('USER:DELETE', 'User(delete)');
        
        $role->setAttrib('size', count(Zend_Registry::get('allCategory')) * 3 + 19);
        $role->setAttrib('class', 'form-control input-multiple-select validate[required]');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');

        //-------------------------------------------------------------------------------------------------------------------------        
        
        if($user){
            $id->setValue($user['id']);
            $name->setValue($user['name']);
            $username->setValue($user['username']);
            $password->setValue($user['password']);
            $role->setValue(explode(',', $user['role']));
        }
        
        $userForm = new Zend_Form();
        $userForm->setAction($this->_request->getBaseUrl().'/admin/user/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($name)
                    ->addElement($username)        
                    ->addElement($password)	
                    ->addElement($role)	
					->addElement($submit);

        return $userForm;
    }
}

