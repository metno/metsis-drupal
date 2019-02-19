<?php

global $metsis_conf;

define('WMS_PRODUCT_SELECT', $metsis_conf['wms_product_select']);
//wms_layers_from_solr is intended to replace wms_restrict_layers 
//and the need for specifying the initially visible layer since 
//the order in which these are defined in SOLR will be used. 
//wms_layers_from_solr is not implemented yet.
//see the site metsis config file as well
//define('WMS_LAYERS_FROM_SOLR', $metsis_conf['wms_layers_from_solr']);