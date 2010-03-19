<?php
interface Pb_Model_Broker_Interface
{
    // static定義のメソッドは、Bootstrap等で初期設定を行うことを想定している

    // リソースプラグインローダー設定
    public static function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader);

    // リソースプラグインローダー取得
    public static function getPluginLoader();

    // リソース読み出し元追加、prefixからpathも生成
    public static function addPrefix($prefix);

    // リソース読み出し元追加、prefixとpathを別々に設定
    public static function addPath($path, $prefix = null);
}
?>
