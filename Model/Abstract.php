<?php
abstract class Pb_Model_Abstract
{
    protected $_frontController;
    protected $_bootstrap;
    protected $_helper;     // helperブローカー
    protected $_storage;

    final public function __construct()
    {
        $this->_helper  = new Pb_Model_HelperBroker($this);
        $this->_storage = new Zend_Registry();

        $params = func_get_args();
        $this->_init($params);
    }

    protected function _init(array $config)
    {
        // 初期化処理するときはこちらで
    }

    // helper取得 $this->_helper->{$helperName}でもOK
    final protected function _getHelper($helperName)
    {
        return empty($helperName) ? $this->_helper : $this->_helper->{$helperName};
    }

    // storage取得 $this->_storageでもOK
    final protected function _getStorage()
    {
        return $this->_storage;
    }

    // bootstrap取得。なかったら生成するので、$this->_getBootstrap()でとった方がいい
    final protected function _getBootstrap()
    {
        if (is_null($this->_bootstrap)) {
            $this->_bootstrap = $this->getFrontController()->getParam('bootstrap');
        }

        return $this->_bootstrap;
    }

    // frontController取得。なかったら生成するので、$this->_getFontController()でとった方がいい
    final protected function _getFrontController()
    {
        if (is_null($this->_frontController)) {
            $this->_frontController = Zend_Controller_Front::getInstance();
        }

        return $this->_frontController;
    }

    protected function __get($key)
    {
        // 存在しない変数名が呼ばれた場合は、helperを探す
        // 見つからなかった場合は、何もしない
        try { return $this->_helper->{$key}; } catch (Zend_Exception $e) {}
    }
}
?>
