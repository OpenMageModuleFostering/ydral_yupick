<?php
class Ydral_Yupick_Model_Mysql4_Yupick extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('yupick/yupick', 'yupick_id');
    }	
}
