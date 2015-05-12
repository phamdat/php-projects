<?php

class Admin_ConfigurationController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('icon', 'cog');
        Zend_Layout::getMvcInstance()->assign('title', 'Configuration');
        
		if(!Zend_AdminAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/admin/login');
        }
    }

    public function indexAction()
    {		
		$configurations = Zend_Registry::get('configurations');			
        
        $configurationForm = $this->getConfigurationForm($configurations);
        
        $db = Zend_Registry::get('db');
        
        if($this->_request->isPost())
        {
            if($configurationForm->isValid($this->_request->getPost()))
			{
                foreach($configurations as $name => $conf)
                {
                    if($conf['type'] == 'IMAGE')
                    {
                        $data = array(
                            'value'             => $this->uploadFile($name, $configurationForm->getValue('value_' . $name))
                        );
                        $n = $db->update('simple_configuration', $data, array('id = ?' => $configurationForm->getValue('id_' . $name)));
                    }
                    else if($conf['type'] == 'TEXT' || $conf['type'] == 'RICH_TEXT')
                    {
                        $data = array(
                            'value'             => $configurationForm->getValue('value_' . $name)
                        );
                        $n = $db->update('simple_configuration', $data, array('id = ?' => $configurationForm->getValue('id_' . $name)));                        
                    }
                }
                
                $this->redirect('/admin/configuration');
            }
        }
        
        $this->view->configurationForm = $configurationForm;
    }
    
    public function detailAction()
    {		
		$name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
            ->setRequired(true)
            ->setAttrib('class', 'form-control validate[required]')
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Please input category name.');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $displayName = new Zend_Form_Element_Text('display_name');
        $displayName->setLabel('Display')            
            ->setRequired(true)
            ->setAttrib('class', 'form-control validate[required]')
            ->addValidator('NotEmpty', true)
            ->addErrorMessage('Please input display name.');

        //-------------------------------------------------------------------------------------------------------------------------
        
        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Type')
				->setRequired(false);        
        $type->addMultiOption('IMAGE', 'Image');
        $type->addMultiOption('TEXT', 'Text');
        $type->addMultiOption('RICH_TEXT', 'Rich text');
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $configurationForm = $configurationForm = new Zend_Form();
        $configurationForm->setAction($this->_request->getBaseUrl() . '/admin/configuration/detail')
                ->setMethod('post');
        $configurationForm->addElement($name);
        $configurationForm->addElement($displayName);
        $configurationForm->addElement($type);
        $configurationForm->addElement($submit);
        
        if($this->_request->isPost())
        {
            if($configurationForm->isValid($this->_request->getPost()))
			{
                $data = array(
                    'name'              => $configurationForm->getValue('name'),
                    'display_name'      => $configurationForm->getValue('display_name'),
                    'type'              => $configurationForm->getValue('type')
                    
                );
                $db = Zend_Registry::get('db');
                $n = $db->insert('simple_configuration', $data);
                
                $this->redirect('/admin/configuration');
            }
        }
        
        $this->view->configurationForm = $configurationForm;
    }   
    
    public function deleteAction()
    {
		$db = Zend_Registry::get('db');
        
        $n = $db->delete('simple_configuration', 'id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/configuration');
    }
    
    protected function uploadFile($name, $oldName)
    {
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();

        $fileAdapter->setDestination(MEDIA_DIRECTORY);

        $file = 'file_' . $name;

        $infos = $fileAdapter->getFileInfo();
        $info = $infos[$file];
        
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
    
    // this is right! dont delete
    public function orderAction()
    {
		$db = Zend_Registry::get('db');
        
        $l = json_decode($this->_request->getParam('data'));
        
        foreach($l as $i => $v)
        {            
            $v = (array) $v;
            if(isset($v['link_id']))
            {
                $n = $db->update('configuration', array('order_id' => $v['order_id'], 'link_id' => $v['link_id']), array('id = ?' => $v['id']));
            }
            else
            {
                $n = $db->update('configuration', array('order_id' => $v['order_id']), array('id = ?' => $v['id']));
            }
        }
        
		exit();
    }
	
    protected function getConfigurationForm($configurations)
    {

        $configurationForm = new Zend_Form();
        $configurationForm->setAction($this->_request->getBaseUrl().'/admin/configuration/index')
                ->setMethod('post');
            
        foreach($configurations as $name => $conf)
        {
            if($conf['type'] == 'IMAGE')
            {
                $id = new Zend_Form_Element_Hidden('id_' . $name);
                $id->setRequired(false)
                    ->setValue($conf['id']);

                //-------------------------------------------------------------------------------------------------------------------------

                $file = new Zend_Form_Element_File('file_' . $name);
                $file->setLabel($conf['display_name'] . ' (' . $name . ')')
                            ->setRequired(false)
                            ->addValidator('Count', false, 1)
                            ->addValidator('Size', false, 2097152)
                            ->addValidator('Extension', false, 'jpg,png,gif');

                $file->getValidator('Count')->setMessage('Chỉ được up một file.');
                $file->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
                $file->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');

                /*if(!$this->_request->getParam('value_' . $name))
                {
                    $file->setRequired(false)->addValidator('Upload', true);

                    $file->getValidator('Upload')->setMessage('Kích thước tối đa là 2MB.', Zend_Validate_File_Upload::INI_SIZE);
                    $file->getValidator('Upload')->setMessage('Vui lòng up hình đại diện.', Zend_Validate_File_Upload::NO_FILE);			
                }*/
                
                //-------------------------------------------------------------------------------------------------------------------------

                $im = new Zend_Form_Element_Image('image_' . $name);
                $im->setImage($conf['value']);
                
                //-------------------------------------------------------------------------------------------------------------------------

                $img = new Zend_Form_Element_Hidden('value_' . $name);
                $img->setRequired($conf['value'])
                    ->setValue($conf['value']);

                //-------------------------------------------------------------------------------------------------------------------------

                $configurationForm->addElement($id);
                $configurationForm->addElement($img);
                $configurationForm->addElement($file);
                $configurationForm->addElement($im);
            }
            else if($conf['type'] == 'TEXT' || $conf['type'] == 'RICH_TEXT')
            {
                $id = new Zend_Form_Element_Hidden('id_' . $name);
                $id->setRequired(false)
                    ->setValue($conf['id']);
                
                //-------------------------------------------------------------------------------------------------------------------------

                $text = new Zend_Form_Element_Textarea('value_' . $name);
                $text->setLabel($conf['display_name'] . ' (' . $name . ')')
                    ->setRequired(false)
                    ->setValue($conf['value'])
                    ->addValidator('NotEmpty', true)
                    ->addErrorMessage('Vui lòng nhập thông tin.')
                    ->setAttrib('rows', '2');
                
                //-------------------------------------------------------------------------------------------------------------------------
                
                $configurationForm->addElement($id);
                $configurationForm->addElement($text);
            }
        }
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary btn-sm');

        $configurationForm->addElement($submit);

        return $configurationForm;
    }
}

