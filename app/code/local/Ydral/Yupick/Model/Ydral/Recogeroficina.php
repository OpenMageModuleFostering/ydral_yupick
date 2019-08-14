<?php
class Ydral_Yupick_Model_Ydral_Recogeroficina extends Mage_Shipping_Model_Carrier_Abstract
{
    
    protected $_code = 'yupick';
    public $_codigoPostal = '';
    
    
    /**
    *   + visualizacion en checkout
    */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {

        if (!$this->getConfigFlag('active')) 
        { 
		    return false;
		}

        $err = null;
        $envio = array();


        /**
        *
        */
        if (!$request->getOrig()) {
	        $request->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $request->getStore()))
	                ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $request->getStore()));
	    }
		
	    //Yupick limite de kilos
	    if ($request->getPackageWeight() > 20)
	    {
	    	return false;
	    }
	     

        /**
        *
        */
        $_datosEnvio['origen']  = Mage::getStoreConfig('shipping/origin/postcode', $request->getStore());
        
        $_datosEnvio['destino'] = $request->getDestPostcode();
        $_datosEnvio['peso']    = $request->getPackageWeight();
        $_datosEnvio["bultos"]  = 1;
        $_datosEnvio["precioProducto"] = $request->getPackageValue();
            
        
        $result = Mage::getModel('shipping/rate_result');
        
        
        $rate = Mage::getModel('shipping/rate_result_method');
        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('yupick');
        

        $rate->setMethodTitle('Recogida en punto de conveniencia: ');
       
        $this->setCodigoPostal($request->getDestPostcode());
        
        $rate->setPrice($this->getConfigData('price_shipping'));
        $result->append($rate);
        
        

        return $result;
	}
	
	public function setCodigoPostal($codPostal)
	{
	    $this->_codigoPostal = $codPostal;
	}
	
	public function getCodigoPostal()
	{
	    return $this->_codigoPostal;
	}

	public function isTrackingAvailable()
	{
		return true;
	}

	public function getTrackingInfo($tracking_number)
	{
        $tracking_result = $this->getTracking($tracking_number);
        if ($tracking_result instanceof Mage_Shipping_Model_Tracking_Result) {
            $trackings = $tracking_result->getAllTrackings();
            if ($trackings){ return $trackings[0]; }
        }
        elseif (is_string($tracking_result) && !empty($tracking_result)){
            return $tracking_result;
        }
        else return false;
	}


    /**
    *   prepara el selector de oficinas
    */
    public function getHtmlOficinas ($data)
    {
                
        if (!$_oficinas = $this->pedirOficinas($data))
        {
            return false;
        } else {

            $_oficinas = $this->objectToArray($_oficinas);

            if (!isset($_oficinas['puntoentrega'][0]))
            {
                $_tmpPunto = $_oficinas['puntoentrega'];
                unset($_oficinas['puntoentrega']);
                $_oficinas['puntoentrega'][0] = $_tmpPunto;
            }

            //  convertimos toda la informacion a json
            $_returnJson = json_encode($_oficinas);

            return $_returnJson;
            
        }
        
    }
    
    
    /**
    *
    */
    public function objectToArray( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
        {
            return $object;
        }
        if( is_object( $object ) )
        {
            $object = get_object_vars( $object );
        }
        return array_map( array($this, 'objectToArray'), $object );
    }
    
    
    
    /**
    * OJO, creo que esta función no se una
    */
    public function dataPunto($codOficina, $codPostal)
    {
        
        $_oficinas = $this->pedirOficinas($codPostal);

        if (!is_array($_oficinas))
        {
            return false;
        } else {
            while(list(,$_oficina) = each($_oficinas)) 
            {
                if ($_oficina->unidad == $codOficina)
                {
                    return $_oficina;
                }
            }
            return false;
        }
        
    }
    
    
    /**
    *   llama a yupick para comprobar las oficinas relativas al codigo postal
    *
    */
    protected function pedirOficinas ($data)
    {
        if (!is_array($data)) { return false; }
        if (!Mage::getStoreConfig('yupick/general/active')) return false;
        if (!Mage::getStoreConfig('yupick/general/etiquetador')) return false;
        
        //Mage::log($data);
        $URL=Mage::helper('yupick')->getUrlRequestCarrier();
        //Mage::log($URL);
        $client = new SoapClient($URL);
        //  Realizamos la peticion
        $result = $client->getBuscarPuntos(Mage::getStoreConfig('yupick/general/etiquetador'),$data['codigoPostal'],$data['direccion'],$data['option_parking'],$data['option_wifi'],$data['option_alimentacion'],$data['option_prensa'],$data['option_tarjeta'],$data['option_mas20'],$data['option_sabados'],$data['option_domingos']);
		//Mage::log($result);
        //  reparamos los datos para sacar un xml bien formado
        $result = str_replace("<?xml version='1.0' encoding='ISO-8859-1'?><puntoentrega>", "<?xml version='1.0' encoding='ISO-8859-1'?><puntos><puntoentrega>", $result . '</puntos>');
        //$result = iconv("UTF-8", "ISO-8859-1//IGNORE", utf8_decode(trim($result)));
        $result = utf8_decode($result);
        $result = preg_replace('/<tarjetacredito>([A-Za-z]*)<tarjetacredito>/e', "'<tarjetacredito>'.strtolower('\\1').'</tarjetacredito>'",$result); 
        $result = preg_replace('/<poslongitud>([A-Za-z0-9\.-]*)<poslongitud>/e', "'<poslongitud>' . strtolower('\\1') . '</poslongitud>'",$result); 
        $result = str_replace('</puntos>', '', $result);
    	
        if ($this->is_valid_xml($result))
        {
            $dataXml = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        } else {
            return false;
        }
        
        return $dataXml;
        
    }


    /**
    *   comprueba que el xml sea correcto
    */
    function is_valid_xml ($xml)
    {
        libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->loadXML($xml);
        
        $errors = libxml_get_errors();
        
        return empty($errors);
    }


    /**
    *
    */
    public function saveCheckoutData($entityType, $idQuote, $idSelector, $nombrePunto, $_tipoAviso, $_tipoAvisoSms, $_tipoAvisoEmail, $order_id = '')
    {
        
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        
        $sqlQuery = 'SELECT id FROM '.$tablePrefix.'yupick_recoger WHERE entity_type = "' . $entityType . '" AND '.$entityType.'_id = "' . $idQuote . '" LIMIT 1';
        $dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $row = $dbRead->fetchAll($sqlQuery);

        if (!$row)
        {
            $sqlQuery  = 'INSERT INTO '.$tablePrefix.'yupick_recoger values (\'\', ?, ?, ?, ?, ?, ?, ?, ?, \'\')';
            $dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        
            $dbWrite->query($sqlQuery, array($entityType, $idQuote, $order_id, addslashes($idSelector), addslashes($_tipoAviso), addslashes($_tipoAvisoSms), addslashes($_tipoAvisoEmail), addslashes($nombrePunto)));
            
        } else {
         
            $sqlQuery  = 'UPDATE '.$tablePrefix.'yupick_recoger SET ' .
                'oficina_recogida = "' . $idSelector . '", ' .
                'tipo_aviso = "' . $_tipoAviso . '", ' .
                'movil = "' . $_tipoAvisoSms . '", ' .
                'correo = "' . $_tipoAvisoEmail . '", ' .
                'info_oficina = "' . $nombrePunto . '" ' .
                ' WHERE quote_id = "' . $idQuote . '" ' .
                ' AND entity_type = "' . $entityType . '" LIMIT 1';
                
                
            $dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $dbWrite->query($sqlQuery);
            
        }
    }
    
    
    /**
    *
    */
    public function saveOrderData($idQuote, $idOrder = '')
    {
        
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
         
        $sqlQuery  = 'UPDATE '.$tablePrefix.'yupick_recoger SET ' .
            'order_id = "' . $idOrder . '", ' .
            ' entity_type = "order" ' .
            ' WHERE quote_id = "' . $idQuote . '" ' .
            ' AND entity_type = "quote" LIMIT 1';
            
            
        $dbWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $dbWrite->query($sqlQuery);
    }
    

    /**
    *
    */   
    public function readCheckoutData ($entityType, $checkoutId)
    {
        
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        $sqlQuery  = 'SELECT * FROM '.$tablePrefix.'yupick_recoger WHERE '.$entityType.'_id = "'.$checkoutId.'" AND entity_type = "'.$entityType.'" ORDER BY id DESC LIMIT 1';
        $dbRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $row = $dbRead->fetchAll($sqlQuery);
        
        if (!$row)
        {
            return false;
        } else {
            return $row[0];
        }
        
    }
    
    
    /**
    *
    */
    public function readUltimoEstado($order, $yupickId)
    {               
        
        if (!Mage::getStoreConfig('yupick/general/active')) return false;
        if (!Mage::getStoreConfig('yupick/general/etiquetador')) return false;
        
        
        $URL=Mage::helper('yupick')->getUrlRequest('situacion_url');
        $client = new SoapClient($URL);
        
        //  Realizamos la peticion
        //$result = $client->getSituacionActual(Mage::getStoreConfig('yupick/general/etiquetador'),$order,$yupickId);
        $result = $client->getSituacionActual(Mage::getStoreConfig('yupick/general/etiquetador'),'',$yupickId);
        
        
        return $result;
        
    }
    
    
    
    /**
    *
    */
    public function readEstadoPedido($order, $yupickId)
    {        
        
        if (!Mage::getStoreConfig('yupick/general/active')) return false;
        if (!Mage::getStoreConfig('yupick/general/etiquetador')) return false;
        
        
        $URL=Mage::helper('yupick')->getUrlRequest('trazabilidad_url');
        $client = new SoapClient($URL);
        
        //  Realizamos la peticion
        $result = $client->getTrazabilidad(Mage::getStoreConfig('yupick/general/etiquetador'),$order,$yupickId);
        
        
        if ($this->is_valid_xml($result))
        {
            $dataXml = simplexml_load_string($result);
            $dataXml = $this->objectToArray($dataXml);

            if (!isset($dataXml['accion'][0]))
            {
                $_tmpData = $dataXml['accion'];
                unset($dataXml);
                $dataXml['accion'][0] = $_tmpData;
            }
            
        } else {
            
            
            return false;
        }

        return $dataXml;
        
    }

}
