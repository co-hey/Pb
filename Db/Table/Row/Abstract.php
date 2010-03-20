<?php
require_once('Zend/Db/Table/Row/Abstract.php');

class Pb_Db_Table_Row_Abstract extends Zend_Db_Table_Row_Abstract
{
    protected $_tblNamespace;
    protected $_dependents = array();

/*
    final public function __construct(array $config=array())
    {
        parent::__construct($config);

        // $configが空場合は、レコード新規作成のためdefault値を読み出して設定する
        if (count($config) == 0) {
            $this->_data = $this->getTable()->createRow()->toArray();
        }
    }
*/

    // 渡された連想配列の中からカラム名に一致するものだけを設定する
    // 既存のRowにsetFromArrayというメソッドがあったので、それを利用する
    public function assign(array $params)
    {
        return $this->setFromArray($params);
    }

/*
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

        return parent::__call($method, $args);
    }
*/

    public function createDependentRow($dependentTable, $ruleKey = null)
    {
        if (is_string($dependentTable)) {
            $dependentTable = $this->_getTableFromString($dependentTable);
        }

        $map = $this->_prepareReference($dependentTable, $this->_getTable());
        $row = $dependentTable->createRow();

        $this->_dependents[] = array('row' => $row, 'map' => $map);

        return $row;
    }

    public function addChild(Zend_Db_Table_Row_Abstract $row)
    {
        $map = $this->_prepareReference($row->getTable(), $this->_getTable());
        $this->_dependents[] = array('row' => $row, 'map' => $map);
        return $this;
    }

    public function save()
    {
        $ret = parent::save();

        if (count($this->_dependents) == 0) { return $ret; }
        
        $adapter = $this->_getTable()->getAdapter();
        foreach ($this->_dependents as $dependent) {
            $row = $dependent['row'];
            $map = $dependent['map'];

            foreach ($map[Zend_Db_Table_Abstract::REF_COLUMNS] as $num => $column) {
                // カラム名をルールにしたがって整形する(upper,lower等)
                $column          = $adapter->foldCase($column);
                $dependentColumn = $adapter->foldCase($map[Zend_Db_Table_Abstract::COLUMNS][$num]);

                $row->{$dependentColumn} = $this->_data[$column];
            }
            $row->save();
        }

        return $ret;
    }


    // クラス名からテーブルクラスを生成する際に、文字列補完機能を付与する
    protected function _getTableFromString($tableName)
    {
        return parent::_getTableFromString($this->_getDbTableClass($tableName));
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
