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

        // パラメタを同じ形式で、_initに渡す（５個まで許容）
        if (func_num_args() != 0) {
            list($param1, $param2, $params3, $param4, $param5) = func_get_args();
        }
        $this->_init($param1, $param2, $param3, $param4, $param5);
    }

    protected function _init()
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
