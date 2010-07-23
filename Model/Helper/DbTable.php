<?php
//class Pb_Model_Helper_DbTable extends Pb_Model_Broker_Abstract implements Pb_Model_Helper_Interface
class Pb_Model_Helper_DbTable extends Pb_Model_Helper_Abstract implements Pb_Model_Broker_Interface
{
    protected static $_pluginLoader;
    protected static $_delimiter = '_';
    protected $_tables = array();

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
        // Autoloaderに'dbtable'設定がある場合は、読み出し先パスとして追加する
        $resourceTypes = $this->_getBootstrap()->getResourceLoader()->getResourceTypes();
        if (isset($resourceTypes['dbtable'])) {
            $prefix = $resourceTypes['dbtable']['namespace'];
            $path   = $resourceTypes['dbtable']['path'];
            self::addPath($path, $prefix);
        }
    }

    // DbTableクラスのインスタンスを取得する
    public function getDbTable($name)
    {
        if (count(self::getPluginLoader()->getPaths()) == 0) {
            require_once('Pb/Model/Broker/Exception.php');
            throw new Pb_Model_Broker_Exception('no paths');
        }

        $tableName = $this->_getDbTableFullName($name);
        if (!array_key_exists($tableName, $this->_tables)) { $this->_loadDbTable($name); }
        return $this->_tables[$tableName];
    }

    public function __get($name)
    {
        return $this->getDbTable($name);
    }

    // DbTableクラスを生成し、配列に格納する
    protected function _loadDbTable($name)
    {
        $tableName = $this->_getDbTableFullName($name);
        $table = new $tableName();

        if (!($table instanceof Zend_Db_Table_Abstract)) {
            require_once('Pb/Model/Helper/Exception.php');
            $msg = 'Table name ' . $name . ' -> class ' . $tableName . ' is not Zend_Db_Table_Abstract';
            throw new Pb_Model_Helper_Exception($msg);
        }

        $this->_tables[$tableName] = $table;
    }

    // DbTableのクラス名を取得する
    protected function _getDbTableFullName($name)
    {
        try {
            return self::getPluginLoader()->load($this->_getLoadClassName($name));
        } catch (Zend_Loader_PluginLoader_Exception $e) {
            require_once('Pb/Model/Helper/Exception.php');
            throw new Pb_Model_Helper_Exception('Model DbTable by name ' . $name . ' not found');
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
