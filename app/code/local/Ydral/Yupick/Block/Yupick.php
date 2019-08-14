<?php 
class Ydral_Yupick_Block_Yupick extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ydral/yupick/yupick.phtml');
    }
    
}