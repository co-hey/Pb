<?php
require_once('Smarty/Smarty.class.php');

class Pb_View_Smarty implements Zend_View_Interface
{
    protected $_smarty;

    private $_helper       = array();
    private $_helperLoaded = array();
    private $_filter       = array();
    private $_filterLoaded = array();
    private $_filterClass  = array();

    private $_loaders = array();
    private $_loaderTypes = array('filter', 'helper');


    public function __construct($tplPath = null, $config = array())
    {
        $this->_smarty = new Smarty;

        if (!is_null($tplPath)) {
            $this->setScriptPath($tplPath);
        }

        foreach ($config as $key => $value) {
            $this->_smarty->$key = $value;
        }
    }

    public function getEngine()
    {
        return $this->_smarty;
    }

    public function setScriptPath($path)
    {
        if (!Zend_Loader::isReadable($path)) {
            throw new Zend_Exception('無効なパスが設定されました');
        }

        // パス名が"/"で終わっていない場合は、"/"を付与する
        if (!preg_match("@/$@", $path)) { $path . "/"; }
        $this->_smarty->template_dir = $path;
    }

    public function getScriptPaths()
    {
        //return $this->_smarty->template_dir;
        return array($this->_smarty->template_dir);
    }

    public function setBasePath($path, $prefix='Zend_View')
    {
        $this->setScriptPath($path);
    }

    public function addBasePath($path, $prefix='Zend_View')
    {
        $this->setScriptPath($path);
    }

    public function __set($key, $value)
    {
        $this->_smarty->assign($key, $value);
    }

    public function __get($key)
    {
        return $this->_smarty->get_template_vars($key);
    }

    public function __isset($key)
    {
        return !is_null($this->_smarty->get_template_vars($key));
    }

    public function __unset($key)
    {
        $this->_smarty->clear_assign($key);
    }

    public function __call($name, $args)
    {
        $helper = $this->getHelper($name);

        return call_user_func_array(array($helper, $name), $args);
    }

    public function getHelper($name)
    {
        return $this->_getPlugin('helper', $name);
    }

    private function _getPlugin($type, $name)
    {
        $name = ucfirst($name);
        switch ($type) {
            case 'filter':
                $storeVar = '_filterClass';
                $store    = $this->_helper;
                break;

            case 'helper':
                $storeVar = '_helper';
                $store    = $this->_helper;
                break;
        }

        if (!isset($store[$name])) {
            $class = $this->getPluginLoader($type)->load($name);
            $store[$name] = new $class();
            if (method_exists($store[$name], 'setView')) {
                $store[$name]->setView($this);
            }

        }

        $this->$storeVar = $store;
        return $store[$name];
    }

    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            require_once('Zend/View/Exception.php');
            throw new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
        }

        if (!array_key_exists($type, $this->_loaders)) {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Zend_Loader_PluginLoader(array($prefix => $pathPrefix));
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }
        return $this->_loaders[$type];
    }

    public function getHelperPaths()
    {
        return $this->getPluginLoader('helper')->getPaths();
    }

    public function assign($spec, $value = null)
    {
        if (is_array($spec)) {
            $this->_smarty->assign($spec);
        } else {
            $this->_smarty->assign($spec, $value);
        }
    }

    public function clearVars()
    {
        $this->_smarty->clear_all_assign();
    }

    public function render($tplName)
    {
        return $this->_smarty->fetch($tplName);
    }
}
?>
