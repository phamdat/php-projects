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
    
    /*******************************************************************
     *   Get all configuration 
     *******************************************************************/
    protected function _initConfiguration()
	{
		$db = Zend_Registry::get('db');
        	
		$configs = $db->select()
                ->from(array('c' => 'simple_configuration'))
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
    
    /*******************************************************************
     *   Defined global variables
     *******************************************************************/
    protected function _initCustomConfig()
	{
        $config = $this->getOptions();        
                        
        defined('MENU_CONF') || define('MENU_CONF', 'MENU_CONF');
        
        defined('SLIDE_CONF') || define('SLIDE_CONF', 'SLIDE_CONF');        
        
        defined('SIDEBAR_CONF') || define('SIDEBAR_CONF', 'SIDEBAR_CONF');                                
        
        /*******************************************************************
         *   Post type
         *******************************************************************/        
        defined('PAGE') || define('PAGE', 'PAGE');
        
        defined('SIDEBAR') || define('SIDEBAR', 'SIDEBAR');
        
        defined('SLIDE') || define('SLIDE', 'SLIDE');
        
        /*******************************************************************
         *   Other configuration
         *******************************************************************/
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
		
        /*******************************************************************
         *   parse less to css
         *******************************************************************/
        $lessfiles = array('default');
        
        foreach($lessfiles as $item)
        {
            $parser = new lessc();
            $less_code = file_get_contents('public/less/' . $item . '.less');
            $processed_css = $parser->parse($less_code);
            file_put_contents('public/css/' . $item . '.css', $processed_css);
        }
        
        /*******************************************************************
         *   import css and js
         *******************************************************************/
		$view->headLink()->appendStylesheet('/public/css/bootstrap.css');
        $view->headLink()->appendStylesheet('/public/css/font-awesome.css');
        		
		$view->headScript()->appendFile('/public/js/jquery.js');
		$view->headScript()->appendFile('/public/js/bootstrap.js');		        
        
        if(preg_match('/admin/', $_SERVER['REQUEST_URI']))
        {
            $view->headLink()->appendStylesheet('/public/css/bootstrap.datatables.css');
            $view->headLink()->appendStylesheet('/public/css/validationEngine.css');
            $view->headLink()->appendStylesheet('/public/css/tagsinput.css');
            $view->headLink()->appendStylesheet('/public/css/chosen.css');
            $view->headLink()->appendStylesheet('/public/css/tokenize.css');
            $view->headLink()->appendStylesheet('/public/css/nestable.css');
            
            $view->headLink()->appendStylesheet('/public/css/metis-main.css');
            $view->headLink()->appendStylesheet('/public/css/metis-menu.css');            
            
            $view->headScript()->appendFile('/public/js/ckeditor/ckeditor.js');
            $view->headScript()->appendFile('/public/js/ckeditor/adapters/jquery.js');
                        
            $view->headScript()->appendFile('/public/js/modernizr.js');
            
            $view->headScript()->appendFile('/public/js/jquery.ui.js');
            $view->headScript()->appendFile('/public/js/jquery.datatables.js');
            $view->headScript()->appendFile('/public/js/jquery.tablesorter.js');
            $view->headScript()->appendFile('/public/js/jquery.ui.touchpunch.js');
            $view->headScript()->appendFile('/public/js/jquery.validationEngine.js');
            $view->headScript()->appendFile('/public/js/jquery.validationEngine-en.js');
            $view->headScript()->appendFile('/public/js/jquery.validate.js');
            $view->headScript()->appendFile('/public/js/jquery.inputlimiter.js');
            $view->headScript()->appendFile('/public/js/jquery.tagsinput.js');
            $view->headScript()->appendFile('/public/js/jquery.chosen.js');
            $view->headScript()->appendFile('/public/js/jquery.chosen.order.js');
            $view->headScript()->appendFile('/public/js/jquery.tokenize.js');
            $view->headScript()->appendFile('/public/js/jquery.nestable.js');
            
            $view->headScript()->appendFile('/public/js/bootstrap.datatables.js');
            
            $view->headScript()->appendFile('/public/js/metis-core.js');
            $view->headScript()->appendFile('/public/js/metis-menu.js');
            
            $view->headScript()->appendFile('/public/js/admin.js');
        }
        else
        {            
            $view->headLink()->appendStylesheet('/public/css/default.css');
            
            $view->headScript()->appendFile('/public/js/default.js');
        }
        
        /*******************************************************************
         *   seo info
         *******************************************************************/
        $view->headTitle()->setSeparator(' - ');
		$view->headTitle(isset(Zend_Registry::get('configurations')['TITLE_CONF']) ? Zend_Registry::get('configurations')['TITLE_CONF']['value'] : '');
        
        $view->headMeta()->appendName('description', isset(Zend_Registry::get('configurations')['DESCRIPTION_CONF']) ? Zend_Registry::get('configurations')['DESCRIPTION_CONF']['value'] : '');
        $view->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
	}
    
    /*******************************************************************
     *   Get menu
     *******************************************************************/
    protected function _initMenu()
	{
		$db = Zend_Registry::get('db');
        
        $menus = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array())
                ->where('c.name like ?', '%'.MENU_CONF.'%')
                ->where('c.link_id is null or c.link_id = 0')
                ->columns('*', 'c')
                ->order(array('c.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $menu = array();
        
        foreach($menus as $item)
        {            
            $post = $db->select()
                ->from(array('p' => 'post'))                
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.id = ?', $item['post_id'])
                ->columns('*', 'p')
                ->query()
                ->fetchAll()[0];
            
            $submenus = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array())
                ->where('c.name like ?', '%'.MENU_CONF.'%')
                ->where('c.link_id = ?', $item['id'])
                ->columns('*', 'c')
                ->order(array('c.order_id DESC'))
                ->query()
                ->fetchAll();

            $submenu = array();
            
            foreach($submenus as $subitem)
            {
                $subpost = $db->select()
                    ->from(array('p' => 'post'))
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
    
    /*******************************************************************
     *   Get slide
     *******************************************************************/
    protected function _initSlide()
	{
		$db = Zend_Registry::get('db');
        
        $slides = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array())
                ->where('c.name like ?', '%'.SLIDE_CONF.'%')
                ->where('c.post_id is not null')
                ->where('c.post_id != 0')
                ->columns('*', 'c')
                ->order(array('c.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $slide = array();
        
        foreach($slides as $item)
        {
            $post = $db->select()
                ->from(array('p' => 'post'))                
                ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                ->where('p.id = ?', $item['post_id'])
                ->columns('*', 'p')
                ->query()
                ->fetchAll()[0];
            
            $link = array();
            
            if($item['link_id'])
            {
                $link = $db->select()
                    ->from(array('p' => 'post'))                    
                    ->joinLeft(array('u' => 'user'), 'u.id = p.creator_id', array('*', 'creater_id'=>'u.id'))
                    ->where('p.id = ?', $item['link_id'])
                    ->columns('*', 'p')
                    ->query()
                    ->fetchAll();
            }
            
            if(count($link) > 0)
            {
                $link = $link[0];
            }
            else
            {
                $link = array();
            }
            
            if(!isset($slide[$item['name']]))
            {
                $slide[$item['name']] = array();
            }
            array_push($slide[$item['name']], array('slide' => $item, 'post' => $post, 'link' => $link));
        }
        
        Zend_Registry::set('slide', $slide);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
		$layout->assign('slide', $slide);
	}
    
    /*******************************************************************
     *   Get sidebar
     *******************************************************************/
    protected function _initSidebar()
	{
		$db = Zend_Registry::get('db');
        
        $sidebars = $db->select()
                ->from(array('c' => 'configuration'))
                ->joinLeft(array('p' => 'post'), 'c.post_id = p.id', array())
                ->where('c.name like ?', '%'.SIDEBAR_CONF.'%')
                ->where('c.post_id is not null')
                ->where('c.post_id != 0')
                ->columns('*', 'c')
                ->order(array('c.order_id DESC'))
                ->query()
                ->fetchAll();
        
        $sidebar = array();
        
        foreach($sidebars as $item)
        {
            $post = $db->select()
                ->from(array('p' => 'post'))                
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
    
    /*******************************************************************
     *   Get all category 
     *******************************************************************/
    protected function _initPostCategory()
	{
		$db = Zend_Registry::get('db');
        
        $allCategory = $db->select()
            ->from(array('p' => 'post_category'))
            ->columns('*', 'p')
            ->query()
            ->fetchAll();
        
        foreach($allCategory as &$item)
        {
            if($item['link_id'])
            {
                $this->pushCategory($allCategory, $item);
            }
        }
                
        Zend_Registry::set('allCategory', $allCategory);
        
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $layout->assign('allCategory', $allCategory);
	}
    
    private function pushCategory(&$allCategory, $category)
    {                     
        foreach($allCategory as &$item)
        {  
            if($item['id'] == $category['link_id'])
            {
                if(!isset($item['subCategory']))
                {
                    $item['subCategory'] = array();
                }
                array_push($item['subCategory'], $category);
            }
            if(isset($item['subCategory']))
            {
                $this->pushCategory($item['subCategory'], $category);
            }
        }
    }  
	
    /*******************************************************************
     *   Get authenication info
     *******************************************************************/
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
            $layout->assign('admin_name', Zend_AdminAuth::getInstance()->getStorage()->read()->name);
            
            if(Zend_AdminAuth::getInstance()->getStorage()->read()->role != 'super')
            {
                $ro = array();
                foreach(explode(',', Zend_AdminAuth::getInstance()->getStorage()->read()->role) as $role)
                {                    
                    $ro[$role] = true;                    
                }                
                $layout->assign('admin_role', $ro);
            }else{
                $layout->assign('admin_issuper', true);
            }            
        }
	}
    
    /*******************************************************************
     *   Rewrite url    
     *******************************************************************/
    protected function _initRewrite() 
    {		

        $db = Zend_Registry::get('db');
        
        /*******************************************************************
         *   Rewrite by seo url   
         *******************************************************************/
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
        
        
        
        /*******************************************************************
         *   Rewrite front page    
         *******************************************************************/
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
    
    /*******************************************************************
     *   Get host info
     *******************************************************************/
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

