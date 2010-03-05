<?php
require_once('Zend/Db/Table/Row/Abstract.php');

class Pb_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract
{
    protected $_tblNamespace;
    protected $_cols;

    final public function __construct(array $config=array())
    {
        parent::__construct($config);

        // $configが空場合は、レコード新規作成のためdefault値を読み出して設定する
        if (count($config) == 0) {
            $this->_data = $this->getTable()->createRow()->toArray();
        }
    }

    // 渡された連想配列の中からカラム名に一致するものだけを設定する
    public function assign(array $params)
    {
        if (is_null($this->_cols)) {
            $this->_cols = $this->getTable()->info(Zend_Db_Table_Abstract::COLS);
        }

        foreach ($params as $key => $value) {
            if (in_array($key, $this->_cols)) { $this->{$key} = $value; }
        }

        return $this;
    }

    public function __call($method, $args)
    {
        $matches = array();
        $select  = null;

        if (count($args)) {
            if (is_scalar($args[0])) { $param = $args[0]; }
        }

        // findまたはfindByカラム名のマジックメソッド用変換処理
        if (preg_match("/^find(?:By(\w+))?$/", $method)) {
            if ($method == "find") {
                // 指定されたプライマリーキーに対応するレコード情報を取得
                $row = $this->getTable()->find($param)->current();
            } else {
                // 指定されたカラム名、カラム値に対応するレコード情報を取得
                $row = $this->getTable()->{str_replace("find", "findOne", $method)}($param);
            }

            if (count($row) != 0) {
                $this->_data = $row->toArray();
                $this->_cleanData = $this->_data;
                $this->_modifiedFields = array();
                return $this;
            }
            throw new Zend_Db_Table_Row_Exception('no data');
        }

        if (count($args) != 0) {
            if ($args[0] instanceof Zend_Db_Select) { $select = $args[0]; }
        }

        // findParentクラス名のマジックメソッド用変換処理
        if (preg_match("/^findParent(\w+)(?:By(\w+))?$/", $method, $matches)) {
            $class = $this->_getDbTableClass($matches[1]);
            $rule  = isset($matches[2]) ? $matches[2] : null;
            return $this->findParentRow($class, $rule, $select);
        }

        // findクラス名Viaクラス名のマジックメソッド用変換処理
        if (preg_match("/^find(\w+)Via(\w+)(?:By(\w+)(And(\w+))?)?$/", $method, $matches)) {
            $class    = $this->_getDbTableClass($matches[1]);
            $viaClass = $this->_getDbTableClass($matches[2]);
            $rule1    = isset($matches[3]) ? $matches[3] : null;
            $rule2    = isset($matches[4]) ? $matches[4] : null;
            return $this->findManyToManyRowset($class, $viaClass, $rule1, $rule2, $select);
        }

        // findクラス名のマジックメソッド用変換処理
        if (preg_match("/^find(\w+)(?:By(\w+))?$/", $method, $matches)) {
            $class = $this->_getDbTableClass($matches[1]);
            $rule  = isset($matches[2]) ? $matches[2] : null;
            return $this->findDependentRowset($class, $rule, $select);
        }

        require_once 'Zend/Db/Table/Row/Exception.php';
        throw new Zend_Db_Table_Row_Exception("Unrecognized method '$method()'");
    }

    private function _getDbTableClass($className)
    {
        if (is_null($this->_tblNamespace)) {
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            $resourceTypes = $bootstrap->getResourceLoader()->getResourceTypes();
            $this->_tblNamespace = $resourceTypes['dbtable']['namespace'];
        }

        if (strstr($className, $this->_tblNamespace) === false) {
            $className = $this->_tblNamespace . "_" . $className;
        }

        return $className;
    }
}
?>
