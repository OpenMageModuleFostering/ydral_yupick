<?php 
class Ydral_Yupick_Block_Email extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('yupick/email.phtml');
    }
    
}