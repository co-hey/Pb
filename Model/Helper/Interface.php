<?php
// Model Helper インタフェース
interface Pb_Model_Helper_Interface
{
    // クラス名返却
    // HelperBroker内で利用しているため
    // Broker内で、get_classしてもいいんですが、ActionHelperBrokerにあわせました
    public function getName();
}
?>
