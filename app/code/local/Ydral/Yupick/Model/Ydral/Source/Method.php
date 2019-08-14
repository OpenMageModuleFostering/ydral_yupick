<?php
class Ydral_Yupick_Model_Ydral_Source_Method
{
    
    protected $_options = array();
    
    public function toOptionArray()
    {
		if(!count($this->_options))
		{
		       
            $this->_options[1] = array('value' => 'etiquetador',
                                       'label' => 'Etiquetador');
            $this->_options[2] = array('value' => 'cliente',
                                       'label' => 'Cliente');
	        array_unshift($this->_options, array('value'=>'','label'=> ''));
		}

		return $this->_options;
    }
    
    
    public function getMethod($shippingMethod)
    {
        
        die("HOLA");
        
      $mte= explode("_",$shippingMethod);
      if ($mte[0]=="Yupick")
      {
        $label=$mte[1];
        foreach($this->toOptionArray() as $mta)
        {
          if ($mta["label"]==$label) $metodo=$mta;
        }
      }
      return $metodo;
    }
    
}
