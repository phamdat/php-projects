<?php

class Component_PostController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
	
	public function preDispatch()
    {
		$this->view->fullUrl = $this->getRequest()->getHttpHost() . $this->view->url();
    }

    public function getNewAction()
    {
		$db = Zend_Registry::get('db');
						
		$posts = $db->select()
						->from(array('p' => 'post'))
						->columns('*', 'p')
						->where('p.category = ?', $this->_request->getParam('category'))
                        ->where('p.is_filter_page != true')
                        ->limit($this->_request->getParam('size'), 0)
                        ->order(array('p.order_id DESC', 'p.id DESC'))
						->query()
						->fetchAll();
        
        foreach(Zend_Registry::get('postCategory') as $c)
        {
            if($c['name'] == $this->_request->getParam('category'))
            {
                $this->view->category = $c['display_name'];
            }
        }
		
		$this->view->posts = $posts;
    }
    
    public function getNewByListAction()
    {
		$db = Zend_Registry::get('db');
						
		$posts = $db->select()
						->from(array('p' => 'post'))
						->columns('*', 'p')
						->where('p.category = ?', $this->_request->getParam('category'))
                        ->where('p.is_filter_page != true')
                        ->limit($this->_request->getParam('size'), 0)
                        ->order(array('p.order_id DESC', 'p.id DESC'))
						->query()
						->fetchAll();
        
        foreach(Zend_Registry::get('postCategory') as $c)
        {
            if($c['name'] == $this->_request->getParam('category'))
            {
                $this->view->category = $c['display_name'];
            }
        }
		
		$this->view->posts = $posts;
    }
}

