<?php 

class Ydral_Yupick_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
    *
    */
    public function setOficinaYupickRecogida($observer)
    {
        
        $idQuote = Mage::getSingleton('checkout/session')->getQuoteId();
        $idSelector = trim(strip_tags($this->_getRequest()->getPost('oficinas_yupick')));
        $nombrePunto = $this->_getRequest()->getPost('oficinas_yupick_data'); 
        
        $_tipoAviso      = $this->_getRequest()->getPost('yupick_type_alert'); 
        $_tipoAvisoSms   = $this->_getRequest()->getPost('yupick_type_alert_phone'); 
        $_tipoAvisoEmail = $this->_getRequest()->getPost('yupick_type_alert_email'); 
                
        if (!empty($idSelector))
        {
            $observer->getEvent()->getQuote()->setPuntoRecogida(trim(strip_tags($idSelector)));
            $observer->getEvent()->getQuote()->setPuntoOficina(trim(strip_tags($nombrePunto)))->save();

            Mage::getModel('yupick/ydral_recogeroficina')->saveCheckoutData('quote', $idQuote, $idSelector, $nombrePunto, $_tipoAviso, $_tipoAvisoSms, $_tipoAvisoEmail);

            return $this;   
        }
        
        
    }


    public function setOficinaYupickRecogidaOrder($observer)
    {
     
        $idQuote = Mage::getSingleton('checkout/session')->getLastQuoteId();  
        $idOrder = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        
        $_checkoutData = Mage::getModel('yupick/ydral_recogeroficina')->readCheckoutData('quote', $idQuote);
        
        if ($_checkoutData)
        {
            Mage::getModel('yupick/ydral_recogeroficina')->saveOrderData($idQuote, $idOrder);
        }
        
    }
    
    
    
    public function setOficinaYupickRecogidaOrderAdminhtml($observer)
    {
     
        $order = $observer->getOrder();
        $idQuote = $order->getQuoteId();
        $idOrder = $order->getIncrementId();
        
        $idSelector = trim(strip_tags($this->_getRequest()->getPost('oficinas_yupick')));
        
        Mage::log("punto id 1: " . $idSelector);
        if (empty($idSelector)) {
        	$idSelector = Mage::getSingleton('core/session', array('name' => 'adminhtml'))->getYupickId();
        	Mage::log("punto id 2: " . $idSelector);
        }
        
        $nombrePunto = $this->_getRequest()->getPost('oficinas_yupick_data');

        Mage::log("punto nombre 1: " . $nombrePunto);
        if (empty($nombrePunto)) {
        	$nombrePunto = Mage::getSingleton('core/session', array('name' => 'adminhtml'))->getYupickInfo();
        	Mage::log("punto nombre 2: " . $nombrePunto);
        }
        
        $_tipoAviso      = $this->_getRequest()->getPost('yupick_type_alert'); 
        $_tipoAvisoSms   = $this->_getRequest()->getPost('yupick_type_alert_phone'); 
        $_tipoAvisoEmail = $this->_getRequest()->getPost('yupick_type_alert_email'); 

        if (!empty($idSelector))
        {
            Mage::getModel('yupick/ydral_recogeroficina')->saveCheckoutData('quote', $idQuote, $idSelector, $nombrePunto, $_tipoAviso, $_tipoAvisoSms, $_tipoAvisoEmail);   
            Mage::getModel('yupick/ydral_recogeroficina')->saveOrderData($idQuote, $idOrder);
        }
        
    }
    
    
    


    public function getUrlRequest($section)
    {
        if (!Mage::getStoreConfig('yupick/general/entorno_produccion'))
        {
            //  pre-produccion
            return Mage::getStoreConfig('yupick/general/'.$section.'_pre');
            
        } else {
            //  produccion
            return Mage::getStoreConfig('yupick/general/'.$section);
        }
        
    }
    public function getUrlRequestCarrier()
    {
        if (!Mage::getStoreConfig('yupick/general/entorno_produccion'))
        {
            //  pre-produccion
            return Mage::getStoreConfig('carriers/yupick/gateway_url_pre');
            
        } else {
            //  produccion
            return Mage::getStoreConfig('carriers/yupick/gateway_url');
        }
        
    }

}
