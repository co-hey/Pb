<?php
abstract class Pb_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    protected $_cols;

    public function init()
    {
        $this->setDefaultSource(self::DEFAULT_DB);
    }

    // findByカラム名('値')にて、DBからのレコード取得を実現する
    public function __call($method, $args)
    {
        $matches = array();

        if (is_null($this->_cols)) {
            $this->_cols = $this->info(Zend_Db_Table_Abstract::COLS);
        }

        if (preg_match("/^findBy(\w+)$/", $method, $matches)) {
            $column = strtolower($matches[1]);
            if (in_array($column, $this->_cols)) {
                $select = $this->select()->where("$column = ?", $args[0]);
                return $this->fetchAll($select);
            }

            throw new Zend_Db_Table_Exception('invalid column : ' . $column);
        }

        if (preg_match("/^findOneBy(\w+)$/", $method, $matches)) {
            $column = strtolower($matches[1]);
            if (in_array($column, $this->_cols)) {
                $select = $this->select()->where("$column = ?", $args[0]);
                return $this->fetchRow($select);
            }

            throw new Zend_Db_Table_Exception('invalid column : ' . $column);
        }

        throw new Zend_Db_Table_Exception("Unrecognized method '$method()'");
    }
}
