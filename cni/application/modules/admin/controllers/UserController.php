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
		Zend_Layout::getMvcInstance()->assign('icon', 'users');
        Zend_Layout::getMvcInstance()->assign('title', 'User');
        
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
			$role->addMultiOption($category['name'] . ':1', $category['display_name'] . '(readonly)');
            $role->addMultiOption($category['name'] . ':2', $category['display_name'] . '(read/add/edit)');
            $role->addMultiOption($category['name'] . ':3', $category['display_name'] . '(read/add/edit/delete)');
		}
        
        $role->addMultiOption('MENU:1', 'Menu(readonly)');
        $role->addMultiOption('MENU:2', 'Menu(read/add/edit)');
        $role->addMultiOption('MENU:3', 'Menu(read/add/edit/delete)');
        
        $role->addMultiOption('SIDEBAR:1', 'Sidebar(readonly)');
        $role->addMultiOption('SIDEBAR:2', 'Sidebar(read/add/edit)');
        $role->addMultiOption('SIDEBAR:3', 'Sidebar(read/add/edit/delete)');
        
        $role->addMultiOption('SLIDE:1', 'Slide(readonly)');
        $role->addMultiOption('SLIDE:2', 'Slide(read/add/edit)');
        $role->addMultiOption('SLIDE:3', 'Slide(read/add/edit/delete)');
        
        $role->addMultiOption('CONFIGURATION:1', 'Configuration(readonly)');
        $role->addMultiOption('CONFIGURATION:2', 'Configuration(read/edit)');
        
        $role->addMultiOption('MEDIA:1', 'Media(readonly)');
        $role->addMultiOption('MEDIA:3', 'Media(read/delete)');
        
        $role->addMultiOption('CATEGORY:1', 'Category(readonly)');
        $role->addMultiOption('CATEGORY:2', 'Category(read/add/edit)');
        $role->addMultiOption('CATEGORY:3', 'Category(read/add/edit/delete)');
        
        $role->addMultiOption('USER:1', 'User(readonly)');
        $role->addMultiOption('USER:2', 'User(read/add/edit)');
        $role->addMultiOption('USER:3', 'User(read/add/edit/delete)');
        
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

