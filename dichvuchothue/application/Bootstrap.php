<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDatabase()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/database.ini', APPLICATION_ENV);
		
		$db = Zend_Db::factory($config->database->adapter, $config->database->params);
		
		Zend_Db_Table::setDefaultAdapter($db);
		
		Zend_Registry::set('db', $db);
	}
    
    protected function _initConfiguration()
	{
		$db = Zend_Registry::get('db');
        	
		$configs = $db->select()
                ->from(array('c' => 'configuration'))
                ->columns('*', 'c')
                ->query()
                ->fetchAll();
        
        $configurations = array();
        
        foreach($configs as $item){
            $configurations[$item['name']] = $item;
        }
        
        Zend_Registry::set('configurations', $configurations);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('configurations', $configurations);
	}
    
    protected function _initCustomConfig()
	{
        $config = $this->getOptions();
        
		defined('TITLE_CONF') || define('TITLE_CONF', 'TITLE_CONF');
        
        defined('DESCRIPTION_CONF') || define('DESCRIPTION_CONF', 'DESCRIPTION_CONF');
        
        defined('META_HEADER_CONF') || define('META_HEADER_CONF', 'META_HEADER_CONF');
        
        defined('LOGO_TEXT_CONF') || define('LOGO_TEXT_CONF', 'LOGO_TEXT_CONF');
        
        defined('LOGO_IMG_CONF') || define('LOGO_IMG_CONF', 'LOGO_IMG_CONF');
        
        defined('LOGO_IMG_2_CONF') || define('LOGO_IMG_2_CONF', 'LOGO_IMG_2_CONF');
        
        defined('MENU_CONF') || define('MENU_CONF', 'MENU_CONF');
        
        defined('HEADER_MENU_CONF') || define('HEADER_MENU_CONF', 'HEADER_' . MENU_CONF);
        
        defined('SLIDE_CONF') || define('SLIDE_CONF', 'SLIDE_CONF');
        
        defined('MAIN_SLIDE_CONF') || define('MAIN_SLIDE_CONF', 'MAIN_' . SLIDE_CONF);
        
        defined('SIDEBAR_CONF') || define('SIDEBAR_CONF', 'SIDEBAR_CONF');
        
        defined('RIGHT_SIDEBAR_CONF') || define('RIGHT_SIDEBAR_CONF', 'RIGHT_' . SIDEBAR_CONF);
        
        defined('TOP_SIDEBAR_CONF') || define('TOP_SIDEBAR_CONF', 'TOP_' . SIDEBAR_CONF);
        
        defined('FOOTER_INFO_CONF') || define('FOOTER_INFO_CONF', 'FOOTER_INFO_CONF');
        
        defined('SOCIAL_LINK_CONF') || define('SOCIAL_LINK_CONF', 'SOCIAL_LINK_CONF');
        
        defined('PLUGIN_FOR_ALL_PAGE_CONF') || define('PLUGIN_FOR_ALL_PAGE_CONF', 'PLUGIN_FOR_ALL_PAGE_CONF');
        
        defined('POST_ADVERTISMENT_CONF') || define('POST_ADVERTISMENT_CONF', 'POST_ADVERTISMENT_CONF');
        
        //post type
        defined('PAGE') || define('PAGE', 'PAGE');
        
        defined('SIDEBAR') || define('SIDEBAR', 'SIDEBAR');
			
        
        Zend_Registry::set('CONFIGURATION_IMG', $config['configuration']['img']);
        
        Zend_Registry::set('CONFIGURATION_TEXT', $config['configuration']['text']);
        
        
		Zend_Registry::set('ADMIN_MAIL_ADDRESS', $config['mail']['address']['admin']);

        defined('MEDIA_DIRECTORY') || define('MEDIA_DIRECTORY', $config['custom']['media']['directory']);
        
        defined('MEDIA_PATH') || define('MEDIA_PATH', $config['custom']['media']['path']);
        
        defined('CONTACT_MAIL_TEMPLATE') || define('CONTACT_MAIL_TEMPLATE', $config['mail']['template']['admin']['contact']);
	}
    
    protected function _initViewHelpers()
	{
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		
		$view->doctype('XHTML1_STRICT');
		
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
		
		$view->headLink()->appendStylesheet('/public/css/bootstrap.css');
        $view->headLink()->appendStylesheet('/public/css/font-awesome.css');
        $view->headLink()->appendStylesheet('/public/css/default.css');
		
		$view->headScript()->appendFile('/public/js/jquery-1.11.1.js');
		$view->headScript()->appendFile('/public/js/bootstrap.js');
		$view->headScript()->appendFile('/public/js/ckeditor/ckeditor.js');
        $view->headScript()->appendFile('/public/js/default.js');
        
        $view->headTitle()->setSeparator(' - ');
		$view->headTitle(Zend_Registry::get('configurations')[TITLE_CONF]['value']);
        
        $view->headMeta()->appendName('description', Zend_Registry::get('configurations')[DESCRIPTION_CONF]['value']);
        $view->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
	}
    
    protected function _initMenu()
	{
		$db = Zend_Registry::get('db');
        
        $menus = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array('order_id'=>'p.order_id'))
                ->where('c.name like ?', '%'.MENU_CONF.'%')
                ->where('c.link_id is null or c.link_id = 0')
                ->columns('*', 'c')
                ->order(array('p.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $menu = array();
        
        foreach($menus as $item)
        {            
            $post = $db->select()
                ->from(array('p' => 'post'))
                ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.id = ?', $item['post_id'])
                ->columns('*', 'p')
                ->query()
                ->fetchAll()[0];
            
            $submenus = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array('order_id'=>'p.order_id'))
                ->where('c.name like ?', '%'.MENU_CONF.'%')
                ->where('c.link_id = ?', $item['id'])
                ->columns('*', 'c')
                ->order(array('p.order_id DESC'))
                ->query()
                ->fetchAll();

            $submenu = array();
            
            foreach($submenus as $subitem)
            {
                $subpost = $db->select()
                    ->from(array('p' => 'post'))
                    ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                    ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                    ->where('p.id = ?', $subitem['post_id'])
                    ->columns('*', 'p')
                    ->query()
                    ->fetchAll()[0];
                
                array_push($submenu, array('menu' => $subitem, 'post' => $subpost));
            }
            
            if(!isset($menu[$item['name']]))
            {
                $menu[$item['name']] = array();
            }
            array_push($menu[$item['name']], array('menu' => $item, 'post' => $post, 'submenu' => $submenu));
        }
        
        Zend_Registry::set('menu', $menu);

        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('menu', $menu);
	}
    
    protected function _initSlide()
	{
		$db = Zend_Registry::get('db');
        
        $slides = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array('order_id'=>'p.order_id'))
                ->where('c.name like ?', '%'.SLIDE_CONF.'%')
                ->where('c.post_id is not null')
                ->where('c.post_id != 0')
                ->columns('*', 'c')
                ->order(array('p.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $slide = array();
        
        foreach($slides as $item)
        {
            $post = $db->select()
                ->from(array('p' => 'post'))
                ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.id = ?', $item['post_id'])
                ->columns('*', 'p')
                ->query()
                ->fetchAll()[0];
            
            if(!isset($slide[$item['name']]))
            {
                $slide[$item['name']] = array();
            }
            array_push($slide[$item['name']], array('slide'=> $item, 'post'=> $post));
        }
        
        Zend_Registry::set('slide', $slide);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('slide', $slide);
	}
    
    protected function _initSidebar()
	{
		$db = Zend_Registry::get('db');
        
        $sidebars = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array('order_id'=>'p.order_id'))
                ->where('c.name like ?', '%'.SIDEBAR_CONF.'%')
                ->where('c.post_id is not null')
                ->where('c.post_id != 0')
                ->columns('*', 'c')
                ->order(array('p.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $sidebar = array();
        
        foreach($sidebars as $item)
        {
            $post = $db->select()
                ->from(array('p' => 'post'))
                ->joinLeft(array('c' => 'post_category'), 'c.name = p.category', array('*', 'category_id'=>'c.id'))
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.id = ?', $item['post_id'])
                ->columns('*', 'p')
                ->query()
                ->fetchAll()[0];
            
            if(!isset($sidebar[$item['name']]))
            {
                $sidebar[$item['name']] = array();
            }
            array_push($sidebar[$item['name']], array('sidebar'=> $item, 'post'=> $post));
        }
        
        Zend_Registry::set('sidebar', $sidebar);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('sidebar', $sidebar);
	}
    
    protected function _initPostCategory()
	{
		$db = Zend_Registry::get('db');
        
        $category = $this->getCategory();
        
        $leafCategory = $db->select()
            ->from(array('p' => 'post_category'))
            ->columns('*', 'p')
            ->joinLeft(array('c' => 'post_category'), 'p.id = c.link_id', array())
            ->where('c.id is null')
            ->query()
            ->fetchAll();
        
        $allCategory = $db->select()
            ->from(array('p' => 'post_category'))
            ->columns('*', 'p')
            ->query()
            ->fetchAll();
        
        Zend_Registry::set('postCategory', $category);
        Zend_Registry::set('leafCategory', $leafCategory);
        Zend_Registry::set('allCategory', $allCategory);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('postCategory', $category);
        $layout->assign('leafCategory', $leafCategory);
        $layout->assign('allCategory', $allCategory);
	}
    
    private function getCategory($category = null)
    {   
        $db = Zend_Registry::get('db');
        
        $categories = $db->select()
            ->from(array('p' => 'post_category'))
            ->columns('*', 'p');
        
        if($category)
        {
            $categories = $categories->where('p.link_id = ?', $category['id']);
        }
        else
        {
            $categories = $categories->where('p.link_id is null or p.link_id = 0');
        }
        
        $categories = $categories->query()
            ->fetchAll();
        
        $res = array();
            
        foreach($categories as $item)
        {  
            $re = array('id' => $item['id'], 'name' => $item['name'], 'displayName' => $item['display_name'], 'link_id' => $item['link_id']);
            $re['subCategory'] = $this->getCategory($item);
            array_push($res, $re);
        }
        
        return $res;
    }
    
    protected function _initSocialLink()
	{
		$db = Zend_Registry::get('db');
        
        $social = $db->select()
                ->from(array('c' => 'configuration'))
                ->where('c.name = ?', SOCIAL_LINK_CONF)
                ->columns('*', 'c')
                ->query()
                ->fetchAll();
        
        Zend_Registry::set('socialLink', $social);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('socialLink', $social);
	}    
	
	protected function _initAuth()
	{
		if(Zend_Auth::getInstance()->hasIdentity())
        {
			$this->bootstrap('layout');
			$layout = $this->getResource('layout');
			$layout->assign('username', Zend_Auth::getInstance()->getStorage()->read()->username);
        }
        if(Zend_AdminAuth::getInstance()->hasIdentity())
        {
			$this->bootstrap('layout');
			$layout = $this->getResource('layout');
			$layout->assign('admin_username', Zend_AdminAuth::getInstance()->getStorage()->read()->username);
        }
	}
    
    protected function _initRewrite() 
    {		

        $db = Zend_Registry::get('db');
        
        $posts = $db->select()
                ->from(array('p' => 'post'))
                ->where('p.seo_url is not null')
                ->columns('*', 'p')
                ->query()
                ->fetchAll();
        
        $front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
        
        foreach($posts as $post){
            $route = new Zend_Controller_Router_Route(
                $post['seo_url'],
                array(
                    'module' => 'default',
                    'controller' => 'post',
                    'action'     => 'index',
                    'id' => $post['id']
                )
            );

            $router->addRoute($post['seo_url'], $route);
        }
        
        
        
        
        $post = $db->select()
                    ->from(array('p' => 'post'))
                    ->columns('*', 'p')                    
                    ->where('p.is_front_page = 1')
                    ->query()
                    ->fetchAll();
        
        if(count($post) == 0){
            $post = $db->select()
                    ->from(array('p' => 'post'))
                    ->columns('*', 'p')
                    ->query()
                    ->fetchAll()[0];
        }
        
        $route = new Zend_Controller_Router_Route(
            '/',
            array(
                'module' => 'default',
                'controller' => 'post',
                'action'     => 'index',
                'id' => $post[0]['id']
            )
        );

        $router->addRoute('/', $route);
        
        
        
        
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', APPLICATION_ENV);

		$router->addConfig($config, 'routes');
	}
    
    protected function _initHostInfo()
	{
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$layout->assign('hostName', $protocol.'/'.$_SERVER['HTTP_HOST']);
		
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->hostName = $protocol.'/'.$_SERVER['HTTP_HOST'];
	}
	
}

