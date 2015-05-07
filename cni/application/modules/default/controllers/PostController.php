<?php

class PostController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		Zend_Layout::getMvcInstance()->assign('mainClassesOfPage', $this->getRequest()->getControllerName());
		
		$this->view->fullUrl = $this->getRequest()->getHttpHost() . $this->view->url();
    }

    public function indexAction()
    {
		$db = Zend_Registry::get('db');

        $post = $db->select()
                    ->from(array('p' => 'post'))
                    ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                    ->where('p.id = ?', $this->_request->getParam('id'))
                    ->columns('*', 'p')
                    ->query()                        
                    ->fetchAll()[0];
        
        //-------------------------------------------------------------------------------increase view
        $post['num_of_view']++;
        $db->update('post', array('num_of_view' => $post['num_of_view']), array('id = ?' => $post['id']));
        
        //-------------------------------------------------------------------------------breadcrumb
        $breadcrumb = array();
        $parent = array($post);
        while(count($parent) > 0)
        {
            array_push($breadcrumb, $parent[0]);
            
            if($parent[0]['link_id'] == null)
            {
                break;
            }
            
            $parent = $db->select()
						->from(array('p' => 'post'))
						->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
						->where('p.id = ?', $parent[0]['link_id'])
						->columns('*', 'p')
                        ->query()                        
						->fetchAll();
        }
        
        //-------------------------------------------------------------------------------similar post
        $adapter = new Zend_Paginator_Adapter_DbSelect(
            $db->select()
                    ->from(array('p' => 'post'))
                    ->columns('*', 'p')
                    ->where('p.category = ?', $post['category'])
                    ->where('p.is_filter_page != true')
                    ->where('p.id != ?', $this->_request->getParam('id'))
                    ->order(new Zend_Db_Expr('RAND()'))
        );

        $adapter->setRowCount(
            $db->select()
                    ->from(array('p' => 'post'))
                    ->columns('*', 'p')
                    ->where('p.category = ?', $post['category'])
                    ->where('p.is_filter_page != true')
                    ->where('p.id != ?', $this->_request->getParam('id'))
                ->reset( Zend_Db_Select::COLUMNS )
                ->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
        );

        $similarPosts = new Zend_Paginator($adapter);

        $similarPosts->setItemCountPerPage(9999);

        $similarPosts->setCurrentPageNumber($this->_request->getParam('page'));
        
        //-------------------------------------------------------------------------------most of view post
        $mostOfViewPosts = $db->select()
                            ->from(array('p' => 'post'))
                            ->columns('*', 'p')
                            ->where('p.category = ?', $post['category'])
                            ->where('p.is_filter_page != true')
                            ->where('p.id != ?', $this->_request->getParam('id'))
                            ->limit(6, 0)
                            ->order('num_of_view DESC')
                            ->query()
                            ->fetchAll();                        
        
        //-------------------------------------------------------------------------------seo
        if(isset($post['seo_title']) && !empty($post['seo_title']))
        {
            $this->view->headTitle()->set($post['seo_title']);
        }
        if(isset($post['seo_description']) && !empty($post['seo_description']))
        {
            $this->view->headMeta()->setName('description', $post['seo_description']);
        }
        if(isset($post['seo_keyword']) && !empty($post['seo_keyword']))
        {
            $this->view->headMeta()->setName('keywords', $post['seo_keyword']);
        }
        Zend_Layout::getMvcInstance()->assign('seoh1', $post['title']);
        Zend_Layout::getMvcInstance()->assign('seoh2', $post['description']);
        
        //-------------------------------------------------------------------------------category to list
        $categories = array();
        foreach(explode(',', $post['category_to_list']) as $ca)
        {
            foreach(Zend_Registry::get('allCategory') as $category)
            {
                if($ca == $category['name'])
                {
                    $adapter = new Zend_Paginator_Adapter_DbSelect(
                        $db->select()
                            ->from(array('p' => 'post'))
                            ->columns('*', 'p')
                            ->where('p.category like ?', '%,'.$ca.',%')
                            //->where('p.category not like ?', '%,MAIN_PAGE,%')
                            ->order('p.order_id DESC')
                    );
                    
                    $adapter->setRowCount(
                        $db->select()
                            ->from(array('p' => 'post'))
                            ->columns('*', 'p')
                            ->where('p.category like ?', '%,'.$ca.',%')
                            //->where('p.category not like ?', '%,MAIN_PAGE,%')
                            ->reset( Zend_Db_Select::COLUMNS )
                            ->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
                    );
                    
                    $paginator = new Zend_Paginator($adapter);
                    
                    $paginator->setItemCountPerPage(9999);

                    $paginator->setCurrentPageNumber($this->_request->getParam('page'));

                    array_push($categories, array('category' => $category, 'posts' => $paginator));
                }
            }
        }

        //-------------------------------------------------------------------------------
        Zend_Layout::getMvcInstance()->assign('hasSlide', $post['has_slide']);
        Zend_Layout::getMvcInstance()->assign('hasLeftSidebar', $post['has_left_sidebar']);
        Zend_Layout::getMvcInstance()->assign('hasRightSidebar', $post['has_right_sidebar']);
        Zend_Layout::getMvcInstance()->assign('hasTopSidebar', $post['has_top_sidebar']);
		
        $this->view->breadcrumb = array_reverse($breadcrumb);
		$this->view->post = $post;
        $this->view->categories = $categories;
        $this->view->similarPosts = $similarPosts;
        $this->view->mostOfViewPosts = $mostOfViewPosts;
    }
    
    public function searchAction()
    {
        $db = Zend_Registry::get('db');
        
        $adapter = new Zend_Paginator_Adapter_DbSelect(
            $db->select()
                    ->from(array('p' => 'post'))
                    ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                    ->where('p.is_filter_page = ?', false)
                    ->where('p.type = ?', PAGE)
                    ->where('p.content like ? or p.description like ? or p.title like ?'
                            . ' or CONVERT(CAST(CONVERT(p.content USING latin1) AS BINARY) USING utf8) like ?'
                            . ' or CONVERT(CAST(CONVERT(p.description USING latin1) AS BINARY) USING utf8) like ?'
                            . ' or CONVERT(CAST(CONVERT(p.title USING latin1) AS BINARY) USING utf8) like ?', '%' . $this->_request->getParam('keyword') . '%')
                    ->order('p.order_id DESC')
        );
        
        $adapter->setRowCount(
            $db->select()
                    ->from(array('p' => 'post'))
                    ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                    ->where('p.is_filter_page = ?', false)
                    ->where('p.type = ?', PAGE)
                    ->where('p.content like ? or p.description like ? or p.title like ?'
                            . ' or CONVERT(CAST(CONVERT(p.content USING latin1) AS BINARY) USING utf8) like ?'
                            . ' or CONVERT(CAST(CONVERT(p.description USING latin1) AS BINARY) USING utf8) like ?'
                            . ' or CONVERT(CAST(CONVERT(p.title USING latin1) AS BINARY) USING utf8) like ?', '%' . $this->_request->getParam('keyword') . '%')
                    ->order('p.order_id DESC')
                    ->reset( Zend_Db_Select::COLUMNS )
                    ->columns(array(Zend_Paginator_Adapter_DbSelect::ROW_COUNT_COLUMN =>'count(*)'))
        );
        
        $paginator = new Zend_Paginator($adapter);

        $paginator->setCurrentPageNumber($this->_request->getParam('page'));

        Zend_Layout::getMvcInstance()->assign('hasSlide', true);
        Zend_Layout::getMvcInstance()->assign('hasRightSidebar', true);
        Zend_Layout::getMvcInstance()->assign('hasTopSidebar', true);
        
        $this->view->paginator = $paginator;
        $this->view->keyword = $this->_request->getParam('keyword');
    }
}

