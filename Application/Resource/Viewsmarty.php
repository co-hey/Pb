<?php
class Pb_Application_Resource_Viewsmarty extends Zend_Application_Resource_ResourceAbstract
{
    protected $_view;

    public function init()
    {
        $view = $this->getView();

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
        $viewRenderer->setView($view);

        $options = $this->getOptions();
        // helperにテンプレートディレクトリを絶対パスで設定（相対パスだとエラーとなる）
        $viewRenderer->setViewBasePathSpec(realpath($options['template_dir']) . '/');
        if (!is_null($options['suffix'])) { $viewRenderer->setViewSuffix($options['suffix']); }

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        return $view;
    }

    public function getView()
    {
        if (is_null($this->_view)) {
            $options = $this->getOptions();
            // パス情報が"/"で終わってない場合には付与する
            if (substr($options['template_dir'], -1) != "/") { $options['template_dir'] .= "/"; }
            if (substr($options['compile_dir'], -1) != "/") { $options['compile_dir'] .= "/"; }

            $this->_view = new Pb_View_Smarty(null, $options);
        }
        return $this->_view;
    }
}

