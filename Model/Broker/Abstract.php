<?php
// Broker基底クラス(Brokerはある種のリソースの読み出しを仲介してくれるクラス)
// bootstrapはFrontControllerから取得するので、FrontControllerが未作成状態の
// bootstrapの中では、newで生成できない。
abstract class Pb_Model_Broker_Abstract implements Pb_Model_Broker_Interface
{
    // static定義のメソッドは、Bootstrap等で初期設定を行うことを想定している

    protected static $_pluginLoader;
    protected static $_delimiter = '_';
    protected $_bootstrap;

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

    // delimiterに使える文字が少ないので一旦コメントアウト
/*
    public static function getDelimiter()
    {
        return self::$_delimiter;
    }
    
    public static function setDelimiter($delimiter = '_')
    {
        self::$_delimiter = $delimiter;
    }
*/

    // 指定されたクラス名をplubinLoaderに渡す形式に変更する
    // xxx_yyyの形式を、Xxx_Yyyに変更する
    protected function _getLoadClassName($name)
    {
        if (strpos($name, self::$_delimiter) === false) { return $name; }

        return str_replace(" ", "_", ucwords(str_replace(self::$_delimiter, " ", $name)));
    }

    protected function _getBootstrap()
    {
        if (is_null($this->_bootstrap)) {
            $this->_bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

            if (is_null($this->_bootstrap)) {
                throw new Pb_Model_Helper_Exception("frontController doesn't have bootstrap yet");
            }
        }

        return $this->_bootstrap;
    }
}
?>
