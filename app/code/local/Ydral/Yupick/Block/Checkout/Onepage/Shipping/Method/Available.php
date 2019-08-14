<?php 
class Ydral_Yupick_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function verOficinas()
    {
        $codigoPostal = $this->getQuote()->getShippingAddress()->getPostcode();
        
        //Mage::log($codigoPostal);
        
        $_htmlOficinas = Mage::getModel('yupick/ydral_recogeroficina')->getHtmlOficinas($codigoPostal);

        if ($_htmlOficinas == false)
        {
            echo "<script type=\"text/javascript\">$('s_method_yupick_yupick').toggle();</script>";
            echo $this->__('No hay oficinas disponibles para mostrar');
            return false;
        } else {
            echo $_htmlOficinas;
        }
        
    }
    
    
}
