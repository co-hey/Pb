<?php
class Pb_Controller_Plugin_TemplateSelector extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $dispatcher = Zend_Controller_Front::getInstance()->getDispatcher();
        $script = array();

        if (($request->getModuleName() != "") 
            && ($request->getModuleName() != $dispatcher->getDefaultModule())) {
            $script[] = ':module';
        }

        // どんな場合でも、コントローラー名はテンプレ指定に必要
        $script[] = ':controller';

        if ($request->getActionName() != $dispatcher->getDefaultAction()) {
            $script[] = ':action';
        }

        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view->setViewScriptPathSpec(implode("/", $script) . '.:suffix');
    }
}
?>
