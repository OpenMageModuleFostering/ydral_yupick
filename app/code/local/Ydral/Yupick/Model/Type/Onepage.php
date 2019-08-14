<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout processing model
 */
class Ydral_Yupick_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    
    /**
     * Specify quote shipping method
     *
     * @param   string $shippingMethod
     * @return  array
     */
    public function saveShippingMethod($shippingMethod)
    {
        $_cp = Mage::app()->getRequest()->getPost('oficinas_yupick_data');
        
        if ($shippingMethod == 'yupick_yupick') {
        	if (empty($_cp)) {
            	return array('error' => -1, 'message' => $this->_helper->__('No se ha especificado un punto de recogida.'));
        	}
        	$_alert = Mage::app()->getRequest()->getPost('yupick_type_alert');
        	$_email = Mage::app()->getRequest()->getPost('yupick_type_alert_email');
        	$_movil = Mage::app()->getRequest()->getPost('yupick_type_alert_phone');
        	
        	if ($_alert == 'Email') {
        		if ($_email == "" || !filter_var($_email, FILTER_VALIDATE_EMAIL)) {
					return array('error' => -1, 'message' => $this->_helper->__('Dirección de Correo no válida.'));        		
        		}        		
        	} else if ($_alert == 'SMS') {
        		if ($_movil == "" || strlen($_movil) < 9) {
        			return array('error' => -1, 'message' => $this->_helper->__('Número de móvil no válido.'));
        		} else {
        			if (strlen($_movil) == 9 && substr($_movil, 0, 1) != "6" && substr($_movil, 0, 1) != "7") {
        				return array('error' => -1, 'message' => $this->_helper->__('Número de móvil no pertenece a España.'));
        			} else if (strlen($_movil) == 11 && substr($_movil, 0, 2) == "34"
        				&& substr($_movil, 2, 1) != "6" && substr($_movil, 2, 1) != "7") {
        					return array('error' => -1, 'message' => $this->_helper->__('Número de móvil no pertenece a España.'));
                    } else if (strlen($_movil) == 12 && substr($_movil, 0, 3) == "+34"
        		        && substr($_movil, 3, 1) != "6" && substr($_movil, 3, 1) != "7") {
                    		return array('error' => -1, 'message' => $this->_helper->__('Número de móvil no pertenece a España.'));
        			} else if (strlen($_movil) >= 13) {
        				return array('error' => -1, 'message' => $this->_helper->__('Número de móvil no pertenece a España.'));
        		    }
        		}        		
        	}
        }
        
        return parent::saveShippingMethod($shippingMethod);
        
    }

}
