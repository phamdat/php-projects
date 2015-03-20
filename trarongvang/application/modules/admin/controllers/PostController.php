<?php

class Admin_PostController extends Zend_Controller_Action
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
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Posts");
		
		$db = Zend_Registry::get('db');
						
		$adapter = new Zend_Paginator_Adapter_DbSelect(
			$db->select()
				->from(array('p' => 'post'))
                ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.type = ?', PAGE)
                ->where('p.category like ?', $this->_request->getParam('category')?'%,'.$this->_request->getParam('category').',%':'%')
                ->columns('*', 'p')                
				->order(array('p.order_id DESC', 'p.id DESC'))
		);
        
		$adapter->setRowCount(
			$db->select()
				->from(array('p' => 'post'))
                ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.type = ?', PAGE)
                ->where('p.category like ?', $this->_request->getParam('category')?'%,'.$this->_request->getParam('category').',%':'%')
                ->columns('*', 'p')                
				->reset( Zend_Db_Select::COLUMNS )
				->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
		);
		
		$paginator = new Zend_Paginator($adapter);
        
        $paginator->setItemCountPerPage(30);
		
		$paginator->setCurrentPageNumber($this->_request->getParam('page'));
		
		$this->view->paginator = $paginator;
    }
	

	public function detailAction()
	{
		Zend_Layout::getMvcInstance()->assign('titleOfPage', "Add/Edit Post");
		
		if($this->_request->getParam('id'))
		{
			$db = Zend_Registry::get('db');
			
			$post = $db->select()
							->from(array('p' => 'post'))
							->columns('*', 'p')
							->where('p.id = ?', $this->_request->getParam('id'))
							->query()
							->fetchAll()[0];

			$postForm = $this->getPostForm($post);
		}
		else
		{
			$postForm = $this->getPostForm();
		}
		
		if($this->_request->isPost())
        {
            if($postForm->isValid($this->_request->getPost()))
			{
				$data = array(
					'type'             => PAGE,
                    'category'         => ',' . implode(',', $postForm->getValue('category')) . ',',
					'thumbnail'        => $this->uploadFile($postForm->getValue('thumbnail')),
                    'media'            => trim($postForm->getValue('media') . ';' . $this->uploadFile(null, 'filemedia'), ';'),
					'title'            => $postForm->getValue('title'),
					'description'      => $postForm->getValue('description'),
					'content'          => $postForm->getValue('content'),
                    'en_title'         => $postForm->getValue('en_title'),
					'en_description'   => $postForm->getValue('en_description'),
					'en_content'       => $postForm->getValue('en_content'),
                    'price'            => $postForm->getValue('price'),
                    'old_price'        => $postForm->getValue('old_price'),
                    'seo_url'          => $postForm->getValue('seo_url'),
                    'seo_title'        => $postForm->getValue('seo_title'),
                    'seo_description'  => $postForm->getValue('seo_description'),
                    'en_seo_url'       => $postForm->getValue('en_seo_url'),
                    'en_seo_title'     => $postForm->getValue('en_seo_title'),
                    'en_seo_description'  => $postForm->getValue('en_seo_description'),
                    'has_slide'        => $postForm->getValue('has_slide'),
                    'has_left_sidebar' => $postForm->getValue('has_left_sidebar'),
                    'has_right_sidebar'   => $postForm->getValue('has_right_sidebar'),
                    'has_top_sidebar'     => $postForm->getValue('has_top_sidebar'),
                    'has_breadcrumb'      => $postForm->getValue('has_breadcrumb'),
                    'has_title'        => $postForm->getValue('has_title'),
                    'has_description'  => $postForm->getValue('has_description'),
                    'has_content'      => $postForm->getValue('has_content'),
                    'has_media'      => $postForm->getValue('has_media'),
                    'has_similar_post' => $postForm->getValue('has_similar_post'),
                    'has_comment'      => $postForm->getValue('has_comment'),
                    'has_like_share'   => $postForm->getValue('has_like_share'),
                    'has_advertisment' => $postForm->getValue('has_advertisment'),
                    'is_front_page'    => $postForm->getValue('is_front_page'),
                    'is_filter_page'   => $postForm->getValue('is_filter_page'),
                    'category_to_list' => ',' . implode(',', $postForm->getValue('category_to_list')) . ',',
                    'link_id'          => $postForm->getValue('link_id'),
                    'redirect_link_id' => $postForm->getValue('redirect_link_id'),
					'creator_id'       => Zend_AdminAuth::getInstance()->getStorage()->read()->id
				);				
                
				$db = Zend_Registry::get('db');
                
                if($postForm->getValue('is_front_page'))
                {
                    $n = $db->update('post', array('is_front_page' => false));
                }
				
				if($postForm->getValue('id')){
					$n = $db->update('post', $data, array('id = ?' => $postForm->getValue('id')));
				}else{
					$n = $db->insert('post', $data);
				}
				
				$this->redirect('/admin/post/index/category/' . $postForm->getValue('category')[0]);
			}
		}
		
		$this->view->postForm = $postForm;
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
		
		$this->redirect('/admin/post/index/category/' . $this->_request->getParam('category'));
    }
    
	public function deleteAction()
    {
		$db = Zend_Registry::get('db');
		
		$n = $db->delete('post', 'id = ' . $this->_request->getParam('id'));
        
        $n = $db->delete('configuration', 'post_id = ' . $this->_request->getParam('id'));
		
		$this->redirect('/admin/post/index/category/' . $this->_request->getParam('category'));
    }
    
    protected function uploadFile($oldName=null, $paramname='file')
    {
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();

        $fileAdapter->setDestination(MEDIA_DIRECTORY);

        $file = $paramname;

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
	
    protected function getPostForm($post=null)
    {
        $db = Zend_Registry::get('db');
        
		$id = new Zend_Form_Element_Hidden('id');
		$id->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $category = new Zend_Form_Element_Multiselect('category');
        $category->setLabel('Category')
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Please select category.')
                ->setValue($this->_request->getParam('category'));

        foreach(Zend_Registry::get('allCategory') as $categoryItem){
			$category->addMultiOption($categoryItem['name'], $categoryItem['display_name']);
		}
        $category->setAttrib('size', count(Zend_Registry::get('allCategory')));
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $file = new Zend_Form_Element_File('file');
        $file->setLabel('Thumbnail')
					->setRequired(false)
                    ->addValidator('Count', false, 1)
					->addValidator('Size', false, 2097152)
					->addValidator('Extension', false, 'jpg,png,gif');
					
		$file->getValidator('Count')->setMessage('Just allow to upload one file.');
		$file->getValidator('Size')->setMessage('Max file size is 2MB.');
		$file->getValidator('Extension')->setMessage('Just allow to upload file has extension: jpg, png, gif.');
		
		if(!$this->_request->getParam('thumbnail')){
			//$file->setRequired(true)->addValidator('Upload', true);
						
			$file->getValidator('Upload')->setMessage('Max file size is 2MB.', Zend_Validate_File_Upload::INI_SIZE);
			//$file->getValidator('Upload')->setMessage('Vui lòng up hình đại diện.', Zend_Validate_File_Upload::NO_FILE);			
		}
		
		$thumbnail = new Zend_Form_Element_Hidden('thumbnail');
		$thumbnail->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
                
        $filemedia = new Zend_Form_Element_File('filemedia');
        $filemedia->setLabel('Product images')
					->setRequired(false)
                    ->addValidator('Count', false, 1)
					->addValidator('Size', false, 2097152)
					->addValidator('Extension', false, 'jpg,png,gif');
					
		$filemedia->getValidator('Count')->setMessage('Chỉ được up một file.');
		$filemedia->getValidator('Size')->setMessage('Kích thước tối đa là 2MB.');
		$filemedia->getValidator('Extension')->setMessage('Chỉ được up file có định dạng jpg, png, gif.');
		
		$media = new Zend_Form_Element_Hidden('media');
		$media->setRequired(false);
                
        //-------------------------------------------------------------------------------------------------------------------------
		
		$title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title')
				->setRequired(true)
				->addValidator('NotEmpty', true)
				->addErrorMessage('Please input title.');
        
        //-------------------------------------------------------------------------------------------------------------------------        
				
		$description = new Zend_Form_Element_Textarea('description');
        $description->setLabel('Short description')
                    ->setAttrib('rows', '3');		
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
		$content = new Zend_Form_Element_Textarea('content');
        $content->setLabel('Content')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $enTitle = new Zend_Form_Element_Text('en_title');
        $enTitle->setLabel('English Title')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
				
		$enDescription = new Zend_Form_Element_Textarea('en_description');
        $enDescription->setLabel('English Short description')
                    ->setAttrib('rows', '3');		
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
		$enContent = new Zend_Form_Element_Textarea('en_content');
        $enContent->setLabel('English Content')
				->setRequired(false);
            
        //-------------------------------------------------------------------------------------------------------------------------
        
        $price = new Zend_Form_Element_Text('price');
        $price->setLabel('Price')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $oldPrice = new Zend_Form_Element_Text('old_price');
        $oldPrice->setLabel('Old price')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $seoUrl = new Zend_Form_Element_Text('seo_url');
        $seoUrl->setLabel('SEO url')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        

        $seoTitle = new Zend_Form_Element_Text('seo_title');
        $seoTitle->setLabel('SEO title')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $seoDescription = new Zend_Form_Element_Textarea('seo_description');
        $seoDescription->setLabel('SEO description')
				->setRequired(false)
                ->setAttrib('rows', '3');
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $enSeoUrl = new Zend_Form_Element_Text('en_seo_url');
        $enSeoUrl->setLabel('English SEO url')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        

        $enSeoTitle = new Zend_Form_Element_Text('en_seo_title');
        $enSeoTitle->setLabel('English SEO title')
				->setRequired(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $enSeoDescription = new Zend_Form_Element_Textarea('en_seo_description');
        $enSeoDescription->setLabel('English SEO description')
				->setRequired(false)
                ->setAttrib('rows', '3');
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $hasSlide = new Zend_Form_Element_Checkbox('has_slide');
        $hasSlide->setLabel('Show slide')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $hasLeftSidebar = new Zend_Form_Element_Checkbox('has_left_sidebar');
        $hasLeftSidebar->setLabel('Show left sidebar')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $hasRightSidebar = new Zend_Form_Element_Checkbox('has_right_sidebar');
        $hasRightSidebar->setLabel('Show right sidebar')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasTopSidebar = new Zend_Form_Element_Checkbox('has_top_sidebar');
        $hasTopSidebar->setLabel('Show top sidebar')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasBreadcrumb = new Zend_Form_Element_Checkbox('has_breadcrumb');
        $hasBreadcrumb->setLabel('Show breadcrumb')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasTitle = new Zend_Form_Element_Checkbox('has_title');
        $hasTitle->setLabel('Show title')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasDescription = new Zend_Form_Element_Checkbox('has_description');
        $hasDescription->setLabel('Show description')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasContent = new Zend_Form_Element_Checkbox('has_content');
        $hasContent->setLabel('Show content')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasMedia = new Zend_Form_Element_Checkbox('has_media');
        $hasMedia->setLabel('Show images')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasSimilarPost = new Zend_Form_Element_Checkbox('has_similar_post');
        $hasSimilarPost->setLabel('Show the same posts')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasMostOfViewPost = new Zend_Form_Element_Checkbox('has_most_of_post');
        $hasMostOfViewPost->setLabel('Show high view posts')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $hasComment = new Zend_Form_Element_Checkbox('has_comment');
        $hasComment->setLabel('Show comment')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $hasLikeShare = new Zend_Form_Element_Checkbox('has_like_share');
        $hasLikeShare->setLabel('Show like share')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(true);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $hasAdvertisment = new Zend_Form_Element_Checkbox('has_advertisment');
        $hasAdvertisment->setLabel('Show advertisment')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $isFrontPage = new Zend_Form_Element_Checkbox('is_front_page');
        $isFrontPage->setLabel('Make home page')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $isFilterPage = new Zend_Form_Element_Checkbox('is_filter_page');
        $isFilterPage->setLabel('Make filter page')
				->setRequired(false)
                ->setCheckedValue(true)
                ->setUncheckedValue(false)
                ->setValue(false);
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $category2List = new Zend_Form_Element_Multiselect('category_to_list');
        $category2List->setLabel('Select category to list')
                ->setRequired(false);
        
        foreach(Zend_Registry::get('allCategory') as $categoryItem){
			$category2List->addMultiOption($categoryItem['name'], $categoryItem['display_name']);
		}
        $category2List->setAttrib('size', count(Zend_Registry::get('allCategory')));
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $linkId = new Zend_Form_Element_Select('link_id');
        $linkId->setLabel('Parent post')
				->setRequired(false);
        
        $linkId->addMultiOption(null, 'Root');
        
        $posts = $db->select()
                        ->from(array('p' => 'post'))
                        ->columns('*', 'p')
                        ->where('p.type = ?', PAGE)
                        ->where('p.id != ?', $post['id']?$post['id']:-1)
                        ->where('p.is_filter_page = ? or p.is_front_page = ?', true)
                        ->query()
                        ->fetchAll();

        foreach($posts as $p)
        {
            $linkId->addMultiOption($p['id'], $p['title']);
        }
        
        //-------------------------------------------------------------------------------------------------------------------------        
        
        $redirectLinkId = new Zend_Form_Element_Select('redirect_link_id');
        $redirectLinkId->setLabel('Redirect link id')
                 ->setRequired(false);
        
        $redirectLinkId->addMultiOption(null, 'None');
        
        foreach($posts as $p)
        {
            $redirectLinkId->addMultiOption($p['id'], $p['title']);
        }
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Save')
                ->setAttrib('class', 'btn btn-primary');

        //-------------------------------------------------------------------------------------------------------------------------        
        
        if($post){
            $id->setValue($post['id']);
            $category->setValue(explode(',', $post['category']));
            $thumbnail->setValue($post['thumbnail']);
            $media->setValue($post['media']);
            $title->setValue($post['title']);
            $description->setValue($post['description']);
            $content->setValue($post['content']);
            $enTitle->setValue($post['en_title']);
            $enDescription->setValue($post['en_description']);
            $enContent->setValue($post['en_content']);
            $price->setValue($post['price']);
            $oldPrice->setValue($post['old_price']);
            $seoUrl->setValue($post['seo_url']);
            $seoTitle->setValue($post['seo_title']);
            $seoDescription->setValue($post['seo_description']);
            $enSeoUrl->setValue($post['en_seo_url']);
            $enSeoTitle->setValue($post['en_seo_title']);
            $enSeoDescription->setValue($post['en_seo_description']);
            $hasSlide->setValue($post['has_slide']);
            $hasLeftSidebar->setValue($post['has_left_sidebar']);
            $hasRightSidebar->setValue($post['has_right_sidebar']);
            $hasTopSidebar->setValue($post['has_top_sidebar']);
            $hasBreadcrumb->setValue($post['has_breadcrumb']);
            $hasTitle->setValue($post['has_title']);
            $hasDescription->setValue($post['has_description']);
            $hasContent->setValue($post['has_content']);
            $hasMedia->setValue($post['has_media']);
            $hasSimilarPost->setValue($post['has_similar_post']);            
            $hasMostOfViewPost->setValue($post['has_most_of_view_post']);
            $hasComment->setValue($post['has_comment']);
            $hasLikeShare->setValue($post['has_like_share']);
            $hasAdvertisment->setValue($post['has_advertisment']);
            $isFrontPage->setValue($post['is_front_page']);
            $isFilterPage->setValue($post['is_filter_page']);
            $category2List->setValue(explode(',', $post['category_to_list']));
            $linkId->setValue($post['link_id']);
            $redirectLinkId->setValue($post['redirect_link_id']);
        }
        
        //-------------------------------------------------------------------------------------------------------------------------
        
        $postForm = new Zend_Form();
        $postForm->setAction($this->_request->getBaseUrl().'/admin/post/detail')
					->setMethod('post')
					->addElement($id)
                    ->addElement($category)
					->addElement($thumbnail)
                    ->addElement($file)
                    ->addElement($media)
                    ->addElement($filemedia)					
                    ->addElement($title)
					->addElement($description)
					->addElement($content)
                    ->addElement($enTitle)
					->addElement($enDescription)
					->addElement($enContent)
                    ->addElement($price)
                    ->addElement($oldPrice)
                    ->addElement($seoUrl)
                    ->addElement($seoTitle)
                    ->addElement($seoDescription)
                    ->addElement($enSeoUrl)
                    ->addElement($enSeoTitle)
                    ->addElement($enSeoDescription)
                    ->addElement($hasSlide)
                    ->addElement($hasLeftSidebar)
                    ->addElement($hasRightSidebar)
                    ->addElement($hasTopSidebar)
                    ->addElement($hasBreadcrumb)
                    ->addElement($hasTitle)
                    ->addElement($hasDescription)
                    ->addElement($hasContent)
                    ->addElement($hasMedia)
                    ->addElement($hasSimilarPost)
                    ->addElement($hasMostOfViewPost)
                    ->addElement($hasComment)
                    ->addElement($hasLikeShare)
                    ->addElement($hasAdvertisment)
                    ->addElement($isFrontPage)
                    ->addElement($isFilterPage)
                    ->addElement($category2List)
                    ->addElement($linkId)
					->addElement($submit);

        return $postForm;
    }
}

