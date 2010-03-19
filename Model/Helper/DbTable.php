<?php
class Pb_Model_Helper_DbTable extends Pb_Model_Broker_Abstract implements Pb_Model_Helper_Interface
{
    protected $_tables = array();

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

    public function getName()
    {
        return get_class($this);
    }
}
?>
