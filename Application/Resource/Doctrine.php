<?php
class Pb_Application_Resource_Doctrine extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        // 【注意】今はMysqlだけに対応
        // Doctrineライブラリにinclude_pathが設定されてない場合はエラーとする
        if (!Zend_Loader::isReadable('Doctrine.php')) {
            throw new Zend_Application_Bootstrap_Exception('no include_path to dctrine lib');
        }
        require_once('Doctrine.php');

       // Doctrineクラスがautoload対象になってない場合は追加する
        $loader = Zend_Loader_Autoloader::getInstance();
        $namespaces = $loader->getRegisteredNamespaces();
        if (!in_array('Doctrine', $namespaces) && !in_array('Doctrine_', $namespaces)) {
            $loader->registerNamespace('Doctrine_');
        }
        Doctrine_Manager::getInstance();

        $options = $this->getOptions();
        foreach ($options as $key => $params) {
            if (!is_array($params)) { continue; }
            Doctrine_Manager::connection($this->_getDsn($params), $key);
        }

        // autoloaderでdoctrineクラスを読む方法もあるので、このパラメタは必須にはしない
        // 指定された場合のみ、該当ディレクトリのファイルを全てloadする
        //if (!Zend_Loader::isReadable($options['modelsDirectory'])) {
        //    throw new Zend_Application_Bootstrap_Exception('invalid doctrine models directory');
        //}
        if (Zend_Loader::isReadable($options['modelsDirectory'])) {
            Doctrine::loadModels($options['modelsDirectory']);
        }
    }

    private function _getDsn(array $params)
    {
        // パスワードDB名がない場合はエラー
        if (empty($params['password']) || empty($params['dbname'])) {
            $msg = array();
            foreach ($params as $item => $value) { $msg[] = $item . "=" . $value; }
            throw new Zend_Application_Bootstrap_Exception('bad dns ' . implode(",", $msg));
        }

        $dsn  = "mysql://";
        $dsn .= (!empty($params['username']) ? $params['username'] : 'root') . ':';
        $dsn .= $params['password'] . '@';
        $dsn .= (!empty($params['hostname']) ? $params['hostname'] : 'localhost') . '/';
        $dsn .= $params['dbname'];

        $dnsOptions = array();
        if (!empty($params['charset'])) { $dnsOptions[] = "chaset=" . $params['charset']; }
        if (!empty($params['link_new'])) { $dnsOptions[] = "link_new=" . $parmas['link_new']; }
        if (count($dnsOptions) != 0) { $dsn .= "?" . implode("&", $dnsOptions); }

        return $dsn;
    }
}

