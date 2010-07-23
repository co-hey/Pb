<?php
//class Pb_Model_Helper_DbSelector extends Pb_Model_Broker_Abstract implements Pb_Model_Helper_Interface
class Pb_Model_Helper_DbSelector extends Pb_Model_Helper_Abstract implements Pb_Model_Broker_Interface
{
    protected static $_pluginLoader;
    protected static $_delimiter = '_';
    protected $_selectors = array();

    public static function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        self::$_pluginLoader = $loader;
    }

    public static function getPluginLoader()
    {
        if (is_null(self::$_pluginLoader)) {
            require_once('Zend/Loader/PluginLoader.php');
            self::$_pluginLoader = new Zend_Loader_PluginLoader();
        }

        return self::$_pluginLoader;
    }

    // リソース読み出し元追加(prefixからpathも設定)
    public static function addPrefix($prefix)
    {
        $prefix = rtrim($prefix, '_');
        $path   = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
        self::addPath($path, $prefix);
    }

    // リソース読み出し元追加(prefix、pathを別に設定)
    public static function addPath($path, $prefix = null)
    {
        self::getPluginLoader()->addPrefixPath($prefix, $path);
    }

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

    // 指定されたクラス名をplubinLoaderに渡す形式に変更する
    // xxx_yyyの形式を、Xxx_Yyyに変更する
    protected function _getLoadClassName($name)
    {
        if (strpos($name, self::$_delimiter) === false) { return $name; }

        return str_replace(" ", "_", ucwords(str_replace(self::$_delimiter, " ", $name)));
    }
}
?>
