<?php
require_once('Zend/Db/Table/Rowset/Abstract.php');

abstract class Pb_Db_Table_Rowset_Abstract extends Zend_Db_Table_Rowset_Abstract
{
    // 内包するすべてのレコード情報に処理を行う
    // __getに関しては取得結果が一意にならないので対象外
    public function __call($method, $args)
    {
        $matches = array();

        if (!$this->valid()) { return; }

        if (preg_match("/^setAll(\w+)$/", $method, $matches)) {

            if (!is_scalar($args[0]) && !($args[0] instanceof Zend_Db_Expr)) {
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

/*
    // カラムのdefault値を設定するための処理、Zend_Db_Tableの設定でいけたので削除
    public function __construct(array $config)
    {

        // find系メソッドを通じて、Zendが生成する場合は、$config['table']は必ず設定される
        // 未設定の場合は、利用者が直接 Rowsetクラスを生成した場合のみ
        if (!isset($config['table'])) {
            $tableClass = $this->_tableClass;
            $config['table'] = new $tableClass();

            // primary key が重複するデータを削除する
            $primary = $config['table']->info('primary');  // primary key 名(array)
            $data    = array();                         // 精査後データ
            $exists  = array();                         // 既出primary key

            foreach ($config['data'] as $item) {
                $pri = array();
                foreach ($primary as $key) { $pri[] = $item[$key]; }
                if (in_array(array(null, ""), $pri)) { throw new Zend_Exception('invalid primary key'); }

                // 既存でないprimary keyを含むデータの場合は、保存する
                if (!in_array($pri, $exists)) { 
                    $data[]   = $item;      // 精査後データ
                    $exists[] = $pri;       // 既出primary key
                }
            }

            if (count($data) != 0) {
                $config['data']   = $data;  // 重複なしデータを保持データとして設定
                $config['stored'] = true;   // DB更新可能フラグをONにする
            }
        }

        parent::__construct($config);
    }
*/
}
?>
