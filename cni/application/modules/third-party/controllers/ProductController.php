<?php

class ThirdParty_ProductController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		Zend_Layout::getMvcInstance()->assign('section', "two");
		
		if(!Zend_ThirdPartyAuth::getInstance()->hasIdentity())
        {
            $this->redirect('/third-party/login');
        }
    }

    public function indexAction()
    {
		$db = Zend_Registry::get('db');
						
		$adapter = new Zend_Paginator_Adapter_DbSelect(
			$db->select()
				->from(array('p' => 'product'))
				->join(array('u' => 'third_party'), 'u.id = p.creater_id', array('*', 'creater_id'=>'u.id'))
				->join(array('c' => 'category'), 'p.category_id = c.id', array('*', 'category_name'=>'c.name'))
				->where('p.creater_id = ?', Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->id)
				->columns('*', 'p')
				->order(array('p.created_datetime DESC'))
		);
		
		$adapter->setRowCount(
			$db->select()
				->from(array('p' => 'product'))
				->join(array('u' => 'third_party'), 'u.id = p.creater_id', array('*', 'creater_id'=>'u.id'))
				->join(array('c' => 'category'), 'p.category_id = c.id', array('*', 'category_name'=>'c.name'))
				->where('p.creater_id = ?', Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->id)
				->reset( Zend_Db_Select::COLUMNS )
				->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
		);
		
		$paginator = new Zend_Paginator($adapter);
		
		$paginator->setCurrentPageNumber($this->_request->getParam('page'));
		
		$this->view->paginator = $paginator;
    }
	

	public function detailAction()
	{
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
			$product = $db->select()
							->from(array('p' => 'product'))
							->join(array('c' => 'category'), 'c.id = p.category_id', array('category_id'=>'c.id', 'category_name'=>'c.name'))
							->join(array('u' => 'third_party'), 'u.id = p.creater_id', array('*', 'creater_id'=>'u.id'))
							->columns('*', 'p')
							->where('p.id = ?', $this->_request->getParam('id'))
							->query()
							->fetchAll();

			$productForm = $this->getProductForm($product[0]['id'], $product[0]['thumbnail'], $product[0]['name'], $product[0]['category_id'], $product[0]['description'], $product[0]['provider'], $product[0]['producer'], $product[0]['old_price'], $product[0]['new_price'], $product[0]['promotion_price']);
		}
		else
		{
			$productForm = $this->getProductForm();
		}
		
		if($this->_request->isPost())
        {
			$db = Zend_Registry::get('db');
			
			$result = $db->select()
						->from(array('p' => 'product'))
						->where('p.creater_id = ?', Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->id)
						->reset( Zend_Db_Select::COLUMNS )
						->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
						->query()
						->fetchAll();
						
			if($result[0][Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN ] < Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->num_of_product){
		
				if($productForm->isValid($this->_request->getPost()))
				{
					// save hình ảnh
					$fileAdapter = new Zend_File_Transfer_Adapter_Http();
	 
					$fileAdapter->setDestination(MEDIA_DIRECTORY);
					
					$receivingOK = true;
					
					$changeThumbnail = false;
					
					$file = 'thumbnail';
					
					$infos = $fileAdapter->getFileInfo();
					$info = $infos['thumbnail'];

					if($info[name]){
						$date = Zend_Date::now();
						$name = md5($date->getTimestamp() . $info['name']);
						$extension = pathinfo($info['name'], PATHINFO_EXTENSION);
						$fullName = $name . '.' . $extension;
						$fileAdapter->addFilter('Rename', MEDIA_DIRECTORY . $fullName, $file);
						if ($fileAdapter->receive($file)) 
						{
							$data = array(
								'path' 			=> $fullName,
								'type'      	=> 'P',
								'creater_id'    => Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->id
							);
							$n = $db->insert('media', $data);
						} 
						else 
						{
							$receivingOK = false;
						}
						
						$changeThumbnail = true;
					}
				
					if(receivingOK){
						//save sản phẩm
						$data = array(
							'name' 				=> $productForm->getValue('name'),
							'category_id' 		=> $productForm->getValue('category'),
							'description' 		=> $productForm->getValue('description'),
							'provider' 			=> $productForm->getValue('provider'),
							'producer' 			=> $productForm->getValue('producer'),
							'old_price'      	=> $productForm->getValue('old_price'),
							'new_price'      	=> $productForm->getValue('new_price'),
							'promotion_price'	=> $productForm->getValue('promotion_price'),
							'creater_id'    	=> Zend_ThirdPartyAuth::getInstance()->getStorage()->read()->id
						);
						
						if($changeThumbnail){
							$data['thumbnail'] = MEDIA_PATH . $fullName;
						}
						
						if($productForm->getValue('id')){
							$n = $db->update('product', $data, array('id = ?' => $productForm->getValue('id')));
						}else{
							$n = $db->insert('product', $data);
						}
						
						$this->redirect('/third-party/product');
					}
				}
			}else{
				$this->view->errorMessage = 'Bạn không được tạo thêm sản phẩm. Vui lòng liên hệ với admin Maximark để biết thêm chi tiết.';
			}
		}
		
		$this->view->productForm = $productForm;
	}
	
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('product', 'id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/third-party/product');
    }
	
	/**
     * Create and return the login form
     *
     * @return object
     */
    protected function getProductForm($idV = null, $thumbnailV = null, $nameV = null, $categoryV = null, $descriptionV = null, $providerV = null, $producerV = null, $oldPriceV = null, $newPriceV = null, $promotionPriceV = null)
    {
		$db = Zend_Registry::get('db');
		
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false)
			->setDecorators(array(
				array('HtmlTag', array('tag' => 'input', 'type' => 'hidden', 'name' => 'id', 'value' => $idV)),
			))
			->setValue($idV);

        $thumbnail = new Zend_Form_Element_File('thumbnail');
        $thumbnail->setLabel('Hình thu nhỏ:')
					->setAttribs(array('class' => 'smallInput wide'))
					->addValidator('Count', false, 1)
					->addValidator('Size', false, 2097152)
					->addValidator('Extension', false, 'jpg,png,gif');
					
		$thumbnail->getValidator('Count')->setMessage('Chỉ được up một file.');
		$thumbnail->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
		$thumbnail->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');
		
		if(!$this->_request->getParam('thumbnail_real')){
			$thumbnail->setRequired(true)
						->addValidator('Upload', true);
						
			$thumbnail->getValidator('Upload')->setMessage('Kích thước tối đa là 2MB.', Zend_Validate_File_Upload::INI_SIZE);
			$thumbnail->getValidator('Upload')->setMessage('Vui lòng up hình sản phẩm.', Zend_Validate_File_Upload::NO_FILE);			
		}
		
		$thumbnailReal = new Zend_Form_Element_Hidden('thumbnail_real');
		$thumbnailReal->setRequired(false)
			->setDecorators(array(
				array('HtmlTag', array('tag' => 'input', 'type' => 'hidden', 'name' => 'thumbnail_real', 'value' => $thumbnailV)),
			))
			->setValue($thumbnailV);
		
		$thumbnailSrc = new Zend_Form_Element_Submit('thumbnail_scr');
        $thumbnailSrc->setDecorators(array(
							array('HtmlTag', array('tag' => 'img', 'src' => $thumbnailV, 'style' => 'height: 50px;'))
						));
		
		//name
		$name = new Zend_Form_Element_Text('name');
        $name->setLabel('Tên sản phẩm:')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập tên sản phảm.')
				->setValue($nameV);
		
		//category
		$categoryList = $db->select()
					->from(array('c' => 'category'))
					->columns('*', 'c')
					->query()
					->fetchAll();
		
		$category = new Zend_Form_Element_Select('category');
        $category->setLabel('Loại sản phẩm:')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng chọn loại sản phẩm.')
				->addMultiOption('', 'Chọn loại sản phẩm');
				
		foreach($categoryList as $categoryItem){
			$category->addMultiOption($categoryItem['id'], $categoryItem['name']);
		}
		
		$category->setSeparator(' ')
				->setValue($categoryV);
		
		// desciption
		$description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Mô tả:')
					->setAttribs(array('class' => 'smallInput wide'))
					->setRequired(true)
					->addValidator('NotEmpty', true)
					->addErrorMessage('Vui lòng nhập mô tả.')
					->setAttribs(array('rows' => '10'))
					->setValue($descriptionV);
				
		$provider = new Zend_Form_Element_Text('provider');
        $provider->setLabel('Nhà phân phối:')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập nhà phân phối.')
				->setValue($providerV);
				
		$producer = new Zend_Form_Element_Text('producer');
        $producer->setLabel('Nhà sản xuất:')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Vui lòng nhập nhà sản xuất.')
				->setValue($producerV);
				
		$oldPrice = new Zend_Form_Element_Text('old_price');
        $oldPrice->setLabel('Giá cũ (VND):')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('Int', true)
				->setValue($oldPriceV);
				
		$oldPrice->getValidator('NotEmpty')->setMessage('Vui lòng nhập giá cũ.');
		$oldPrice->getValidator('Int')->setMessage('Vui lòng nhập đúng giá trị.');
				
		$newPrice = new Zend_Form_Element_Text('new_price');
        $newPrice->setLabel('Giá mới (VND):')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('Int', true)
				->setValue($newPriceV);
				
		$newPrice->getValidator('NotEmpty')->setMessage('Vui lòng nhập giá mới.');
		$newPrice->getValidator('Int')->setMessage('Vui lòng nhập đúng giá trị.');
				
		$promotionPrice = new Zend_Form_Element_Text('promotion_price');
        $promotionPrice->setLabel('Giá khuyến mãi (VND):')
				->setAttribs(array('class' => 'smallInput wide'))
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addValidator('Int', true)
				->setValue($promotionPriceV);
				
		$promotionPrice->getValidator('NotEmpty')->setMessage('Vui lòng nhập giá khuyến mãi.');
		$promotionPrice->getValidator('Int')->setMessage('Vui lòng nhập đúng giá trị.');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
				->setDecorators(array(
					array('HtmlTag', array('tag' => 'input', 'type' => 'submit', 'style' => 'background:transparent;border:none;color:#fff;')),
					array(array('b' => 'HtmlTag'), 'options' => array('tag'=>'span')),
					array(array('a' => 'HtmlTag'), 'options' => array('tag' => 'a', 'class'=>"button"))
				));

        $productForm = new Zend_Form();
		$productForm->setAttribs(array('style' => 'margin: 0px auto;width: 900px;'));
		
		if($thumbnailV){
			$productForm->addElement($thumbnailSrc);
		}
		
        $productForm->setAction($this->_request->getBaseUrl().'/third-party/product/detail')
					->setMethod('post')
					->addElement($id)
					->addElement($thumbnail)
					->addElement($thumbnailReal)
					->addElement($name)
					->addElement($category)
					->addElement($provider)
					->addElement($producer)
					->addElement($oldPrice)
					->addElement($newPrice)
					->addElement($promotionPrice)
					->addElement($description)
					->addElement($submit);

        return $productForm;
    }
}

