<?php
// Model Helper基底クラス
abstract class Pb_Model_Helper_Abstract implements Pb_Model_Helper_Interface
{
    protected $_bootstrap;

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

    // helper名を取得
    // ActionHelperBrokerは、"_"で分割した最後を取得しているが、
    // Model Helperは複数階層に対応するために、上記処理は行わない
    public function getName()
    {
        return get_class($this);
    }
}
?>
