<?php
class Ydral_Yupick_Model_Observer
{				
	
	public function checkout_controller_onepage_save_shipping_method($observer)
	{
	    
	    $request = $observer->getEvent()->getRequest();
		$quote   = $observer->getEvent()->getQuote();
		
		$oficinaRecogida = $request->getPost('oficinas_yupick', '');
		
		Mage::log("Oficina: " . $oficinaRecogida);
		
		$quote->setYupickRecogida($oficinaRecogida);
		$quote->save();
		
		return $this;
	}
		
}