<?php

class Zend_AdminAuth extends Zend_Auth
{
    protected static $_instance = null;

    protected $_storage = null;

    protected function __construct()
    {}

    protected function __clone()
    {}
	
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getStorage()
    {
        if (null === $this->_storage) {
            require_once 'Zend/Auth/Storage/Session.php';
            $this->setStorage(new Zend_Auth_Storage_Session("Zend_AdminAuth"));
        }

        return $this->_storage;
    }
}
