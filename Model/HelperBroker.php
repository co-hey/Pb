<?php
class Pb_Model_HelperBroker extends Pb_Model_Broker_Abstract
{
    // $helperNameは、Helperのクラスフルネーム
    // $nameは、Helperのクラスprefixなし
    // static定義のメソッドは、Bootstrap等で初期設定を行うことを想定している

    protected static $_helpers = array();

    public static function getPluginLoader()
    {
        if (is_null(self::$_pluginLoader)) {
            require_once('Zend/Loader/PluginLoader.php');
            self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Pb_Model_Helper' => 'Pb/Model/Helper',
            ));
        }

        return self::$_pluginLoader;
    }

    // helper読み出し元追加、prefixとpathを別々に追加
    public static function addPath($path, $prefix = 'Pb_Model_Helper')
    {
        if (is_null($prefix)) { $prefix = 'Pb_Model_Helper'; }
        parent::addPath($path, $prefix);
    }

    // 登録helper追加(予め生成してつっこむときに使う）
    public static function addHelper(Pb_Model_Helper_Abstract $helper)
    {
        if (!self::hasHelper($helper->getName())) {
            self::$_helpers[$helper->getName()] = $helper;
        }
    }

    // 登録helper存在チェック
    public static function hasHelper($helperName)
    {
        return array_key_exists($helperName, self::$_helpers);
    }

    // 登録helper削除
    public static function removeHelper($helperName)
    {
        if (self::hasHelper($helperName)) {
            unset(self::$_helpers[$helperName]);
        }
    }

    // helperクラスを取得する
    // 未生成のhelperは生成してから返却する
    public function getHelper($name)
    {
        $helperName = $this->_getHelperFullName($name);
        if (!array_key_exists($helperName, self::$_helpers)) { $this->_loadHelper($name); }
        return self::$_helpers[$helperName];
    }

    // helper取得のショートカット
    public function __get($name)
    {
        return $this->getHelper($name);
    }

    // helperクラスを生成し、配列に格納する
    protected function _loadHelper($name)
    {
        $helperName = $this->_getHelperFullName($name);
        $helper = new $helperName();
        if (!($helper instanceof Pb_Model_Helper_Interface)) {
            require_once('Pb/Model/Helper/Exception.php');
            $msg = 'Helper name ' . $name . ' -> class ' . $helperName . ' is not Pb_Model_Helper_Interface';
            throw new Pb_Model_Helper_Exception($msg);
        }

        self::$_helpers[$helperName] = $helper;
    }

    // Helperのクラス名を取得する
    protected function _getHelperFullName($name)
    {
        try {
            return self::getPluginLoader()->load($this->_getLoadClassName($name));
        } catch (Zend_Loader_PluginLoader_Exception $e) {
            require_once('Pb/Model/Helper/Exception.php');
            throw new Pb_Model_Helper_Exception('Model Helper by name ' . $name . ' not found');
        }
    }
}
?>
