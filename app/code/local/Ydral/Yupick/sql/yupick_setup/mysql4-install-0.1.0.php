<?php 
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('yupick_recoger')} (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) NOT NULL,
  `quote_id` int(12) NOT NULL,
  `order_id` int(12) NOT NULL,
  `oficina_recogida` varchar(10) NOT NULL,
  `tipo_aviso` varchar(20) NOT NULL,
  `movil` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `info_oficina` text NOT NULL,
  `id_registro_yupick` int(12) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `checkout_id` (`quote_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
");
$installer->endSetup();