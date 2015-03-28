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
                foreach(Zend_Registry::get('CONFIGURATION_IMG') as $value){
                    $data = array(
                        'name'              => $configurationForm->getValue('name_' . $value),
                        'value'             => $this->uploadFile($value, $configurationForm->getValue('value_' . $value))
                    );
                    
                    if($configurationForm->getValue('id_' . $value)){
					    $n = $db->update('configuration', $data, array('id = ?' => $configurationForm->getValue('id_' . $value)));
                    }else{
                        $n = $db->insert('configuration', $data);
                    }                  
                }
                
                foreach(Zend_Registry::get('CONFIGURATION_TEXT') as $value){
                    $data = array(
                        'name'              => $configurationForm->getValue('name_' . $value),
                        'value'             => $configurationForm->getValue('value_' . $value)
                    );
                    
                    if($configurationForm->getValue('id_' . $value)){
					    $n = $db->update('configuration', $data, array('id = ?' => $configurationForm->getValue('id_' . $value)));
                    }else{
                        $n = $db->insert('configuration', $data);
                    }                  
                }
                
                $this->redirect('/admin/configuration');
            }
        }
        
        $this->view->configurationForm = $configurationForm;
    }
    
    protected function uploadFile($key, $oldName)
    {
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();

        $fileAdapter->setDestination(MEDIA_DIRECTORY);

        $file = 'file_' . $key;

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
	
    protected function getConfigurationForm($configurations=null)
    {

        $configurationForm = new Zend_Form();
        $configurationForm->setAction($this->_request->getBaseUrl().'/admin/configuration/index')
                ->setMethod('post');
            
        foreach(Zend_Registry::get('CONFIGURATION_IMG') as $value)
        {
            $id = new Zend_Form_Element_Hidden('id_' . $value);
            $id->setRequired(false);
            
            $name = new Zend_Form_Element_Hidden('name_' . $value);
            $name->setRequired(false);
            
            $file = new Zend_Form_Element_File('file_' . $value);
            $file->setLabel($value)
                        ->setRequired(false)
                        ->addValidator('Count', false, 1)
                        ->addValidator('Size', false, 2097152)
                        ->addValidator('Extension', false, 'jpg,png,gif');

            $file->getValidator('Count')->setMessage('Chỉ được up một file.');
            $file->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
            $file->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');

            /*if(!$this->_request->getParam('value_' . $value))
            {
                $file->setRequired(false)->addValidator('Upload', true);

                $file->getValidator('Upload')->setMessage('Kích thước tối đa là 2MB.', Zend_Validate_File_Upload::INI_SIZE);
                $file->getValidator('Upload')->setMessage('Vui lòng up hình đại diện.', Zend_Validate_File_Upload::NO_FILE);			
            }*/

            $im = new Zend_Form_Element_Image('image_' . $value);
            $im->setImage($configurations[$value]['value']);
                
            $img = new Zend_Form_Element_Hidden('value_' . $value);
            $img->setRequired($configurations[$value]['value']);
            
            if(isset($configurations[$value]))
            {
                $id->setValue($configurations[$value]['id']);
                $name->setValue($configurations[$value]['name']);
                $img->setValue($configurations[$value]['value']);
            }
            
            $configurationForm->addElement($id);
            $configurationForm->addElement($name);
            $configurationForm->addElement($img);
            $configurationForm->addElement($file);
            $configurationForm->addElement($im);
        }
        
        foreach(Zend_Registry::get('CONFIGURATION_TEXT') as $value)
        {
            $id = new Zend_Form_Element_Hidden('id_' . $value);
            $id->setRequired(false);
            
            $name = new Zend_Form_Element_Hidden('name_' . $value);
            $name->setRequired(false);
            
            $text = new Zend_Form_Element_Textarea('value_' . $value);
            $text->setLabel($value)
				->setRequired(false)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập thông tin.')
                ->setAttrib('rows', '2');
            
            if(isset($configurations[$value]))
            {
                $id->setValue($configurations[$value]['id']);
                $name->setValue($configurations[$value]['name']);
                $text->setValue($configurations[$value]['value']);
            }
            
            $configurationForm->addElement($id);
            $configurationForm->addElement($name);
            $configurationForm->addElement($text);
        }
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary');

        $configurationForm->addElement($submit);

        return $configurationForm;
    }
}

