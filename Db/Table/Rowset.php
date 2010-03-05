<?php
require_once('Zend/Db/Table/Rowset/Abstract.php');

class Pb_Db_Table_Rowset extends Zend_Db_Table_Rowset_Abstract
{
    // 内包するすべてのレコード情報に処理を行う
    // __getに関しては取得結果が一意にならないので対象外

    public function __call($method, $args)
    {
        $matches = array();

        if (!$this->valid()) { return; }

        if (preg_match("/^setAll(\w+)$/", $method, $matches)) {

            if (!is_scalar($args[0])) {
                throw new Zend_Db_Table_Rowset_Exception('invalid param : ' . $args[0]);
            }
            $column = strtolower($matches[1]);
            foreach ($this as $row) { $row->{$column} = $args[0]; }
            $this->rewind();    // 位置ポインタを先頭に戻す
        }
    }

    public function saveAll()
    {
        if (!$this->valid()) { return; }

        foreach ($this as $row) { $row->save(); }
        $this->rewind();    // 位置ポインタを先頭に戻す
    }

    public function deleteAll()
    {
        if (!$this->valid()) { return ; }

        foreach ($this as $row) { $row->delete(); }
        $this->rewind();    // 位置ポインタを正常に戻す
    }
}
?>
