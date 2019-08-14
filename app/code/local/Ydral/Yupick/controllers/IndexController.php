<?php

class Ydral_Yupick_IndexController extends Mage_Core_Controller_Front_Action 
{
	public function getdataAction()
	{
		Mage::log($this->getRequest()->isPost());
	    if ($this->getRequest()->isPost()) 
	    {
    
	        $data['codigoPostal'] = $this->getRequest()->getPost('codigoPostal');
	        //OJO cambiado
	        $data['direccion'] = $this->getRequest()->getPost('direccion');
	        	     
	        $data['option_parking'] = $this->getRequest()->getPost('option_parking');
	        $data['option_wifi'] = $this->getRequest()->getPost('option_wifi');
	        $data['option_alimentacion'] = $this->getRequest()->getPost('option_alimentacion');
	        $data['option_prensa'] = $this->getRequest()->getPost('option_prensa');
	        $data['option_tarjeta'] = $this->getRequest()->getPost('option_tarjeta');
	        $data['option_mas20'] = $this->getRequest()->getPost('option_mas20');
	        $data['option_sabados'] = $this->getRequest()->getPost('option_sabados');
	        $data['option_domingos'] = $this->getRequest()->getPost('option_domingos');
	        
	    }
	    
	    //$data['codigoPostal'] = '48005';
	    //$data['option_parking'] = $data['option_wifi'] = $data['option_alimentacion'] = $data['option_prensa'] = $data['option_tarjeta'] = $data['option_mas20'] = $data['option_sabados'] = $data['option_domingos'] = '';
	    //print_r($data); die();
	    $_htmlOficinas = Mage::getModel('yupick/ydral_recogeroficina')->getHtmlOficinas($data); 
	    $this->getResponse()->setBody($_htmlOficinas);
	    
	}
	
}
