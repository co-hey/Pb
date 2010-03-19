<?php
class Pb_Model_Helper_DbSelector extends Pb_Model_Broker_Abstract implements Pb_Model_Helper_Interface
{
    protected $_selectors = array();

    public function __construct()
    {
        // autoload設定に、'dbselector'がある場合は、prefixに追加する
        $resourceTypes = $this->_getBootstrap()->getResourceLoader()->getResourceTypes();
        if (isset($resourceTypes['dbselector'])) {
            $prefix = $resourceTypes['dbselector']['namespace'];
            $path   = $resourceTypes['dbselector']['path'];
            self::addPath($path, $prefix);
        }
    }

    // DbSelectorインスタンスを取得する
    public function getDbSelector($name)
    {
        // 読み出し先パスが存在しない場合はエラーとする
        if (count(self::getPluginLoader()->getPaths()) == 0) {
            require_once('Pb/Model/Broker/Exception.php');
            throw new Pb_Model_Broker_Exception('no paths');
        }

        $selectorName = $this->_getDbSelectorFullName($name);
        if (!array_key_exists($selectorName, $this->_selectors)) { $this->_loadDbSelector($name); }
        return $this->_selectors[$selectorName];
    }

    public function __get($name)
    {
        return $this->getDbSelector($name);
    }

    // DbSelectorクラスを生成し、配列に格納する
    protected function _loadDbSelector($name)
    {
        $selectorName = $this->_getDbSelectorFullName($name);
        $selector = new $selectorName();


        $this->_selectors[$selectorName] = $selector;
    }

    // DbSelectorクラス名を取得する
    protected function _getDbSelectorFullName($name)
    {
        try {
            return self::getPluginLoader()->load($this->_getLoadClassName($name));
        } catch (Zend_Loader_PluginLoader_Exception $e) {
            require_once('Pb/Model/Helper/Exception.php');
            throw new Pb_Model_Helper_Exception('Model DbSelector by name ' . $name . ' not found');
        }
    }

    public function getName()
    {
        return get_class($this);
    }
}
?>
