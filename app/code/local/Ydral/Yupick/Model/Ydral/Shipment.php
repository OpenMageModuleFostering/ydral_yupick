<?php
class Ydral_Yupick_Model_Ydral_Shipment extends Mage_Sales_Model_Order_Shipment
{
    
    /**
    *
    */
    protected function _beforeSave()
    {
                
        $envio = array();
        $_order  = $this->getOrder();
        

        /**
        *   solo para yupick!, otros metodos siguen igual
        */
        if ($_order->getShippingMethod() != 'yupick_yupick')
        {
            return parent::_beforeSave();
        }
        
        
        $_dataRecogida = Mage::getModel('yupick/ydral_recogeroficina')->readCheckoutData('order', $_order->getRealOrderId());
        
        if (!$_dataRecogida)
        {
            return false;
        } else {
            if (!Mage::getStoreConfig('yupick/general/active')) return false;
            if (!Mage::getStoreConfig('yupick/general/etiquetador')) return false;
        }
        

        $URL=Mage::helper('yupick')->getUrlRequest('entrega_url');
    
        if ($_dataRecogida['tipo_aviso'] == '')
        {
            $_tipoNotificacion = 'Email';
        } else {
            $_tipoNotificacion = $_dataRecogida['tipo_aviso'];
        }
        
        $_direccionEmail = '';
        $_numMovil = '';
        switch ($_tipoNotificacion)
        {
            case 'Email':
                if (empty($_dataRecogida['correo']))
                {
                    $_direccionEmail = $_order->getShippingAddress()->getEmail();
                } else {
                    $_direccionEmail = $_dataRecogida['correo'];
                }
                break;
                
            case 'SMS':
                if (empty($_dataRecogida['movil']))
                {
                    $_numMovil = $_order->getShippingAddress()->getTelephone();
                } else {
                    $_numMovil = $_dataRecogida['movil'];
                }
                break;
                
        }
        
            
        $_idCliente = $_order->getCustomerId();
        $_nombreCliente = $_order->getShippingAddress()->getFirstname() . " " . $_order->getShippingAddress()->getLastname();
        $_numTelefono = $_order->getShippingAddress()->getTelephone();
        $_numBultos = 1;
        $_pesoPaquete = $_order->getWeight();
        $_precioCobrar = '';
        $_fechaPedido = date('Y-m-d', strtotime($_order->getCreatedAt()));
        if (Mage::getStoreConfig('yupick/configuracion/seguro'))
        {
            $_importeSeguro = Mage::getStoreConfig('yupick/configuracion/valorseguro');
        } else {
            $_importeSeguro = '';
        }
        $_idPuntoEntrega = $_dataRecogida['oficina_recogida'];
        $_idPedido = $_order->getRealOrderId();

        //  peticion 
        try {
            
            $client = new SoapClient($URL);
            $result = $client->InsertarEntrega(Mage::getStoreConfig('yupick/general/etiquetador'), 
                                                $_nombreCliente, 
                                                $_numTelefono, 
                                                $_numMovil,
                                                $_direccionEmail,
                                                $_numBultos,
                                                $_pesoPaquete,
                                                $_precioCobrar,
                                                $_fechaPedido,
                                                $_tipoNotificacion,
                                                $_importeSeguro,
                                                $_idPuntoEntrega,
                                                $_idPedido
                                               );
                                
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('sales')->__('Error: ' . $e->getMessage())
            );
            return false;
        }


        list($_statusEnvio, $_statusInfo) = explode("-", $result);

        if (trim(strtolower($_statusEnvio)) == 'ok')
        {
            
            Mage::getSingleton('adminhtml/session')->addNotice('El pedido ha quedado registrado en el servicio de Yupick!.');
            Mage::getSingleton('adminhtml/session')->addNotice('Identificador de la entrega: ' . trim($_statusInfo));
            
            $track = Mage::getModel('sales/order_shipment_track')
                        ->setNumber(trim($_statusInfo))
                        ->setCarrierCode('Yupick')
                        ->setTitle('Seguimiento Yupick');
    
            $this->addTrack($track);
            
            $this->preregistroYupick($_idPedido, trim($_statusInfo));
            
        } else {
            
            Mage::throwException(
                Mage::helper('sales')->__('Error: ' . trim($_statusInfo))
            );
            return false;
            
        }
        
        $canSave = parent::_beforeSave();
        return $canSave;
        
                
    }

    protected function preregistroYupick($idOrder, $idYupick)
    {
        
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
         
        $sqlQuery  = 'UPDATE '.$tablePrefix.'yupick_recoger SET ' .
            'id_registro_yupick = "' . $idYupick . '" ' .
            ' WHERE order_id = "' . $idOrder . '" ' .
            ' AND entity_type = "order" LIMIT 1';
            
            
        $dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dbWrite->query($sqlQuery);
        
    }

}
