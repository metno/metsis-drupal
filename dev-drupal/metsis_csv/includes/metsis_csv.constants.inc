<?php

global $metsis_conf;

define('CSV_ENDPOINT', $metsis_conf['csv_endpoint']);

define('CSV_SERVER', $metsis_conf['csv_server']);
define('CSV_SERVER_PROTOCOL',"http");
define('CSV_SERVER_PORT', "443");
define('CSV_SERVICE_PATH', "/cgi-bin/adcwps");
define('CSV_SERVICE_PROVIDER', "");
define('CSV_METAPATH', "");
define('CSV_SERVICE_NAME', "WPS");
define('CSV_REQUEST', "execute");
define('CSV_WPS_VERSION', "1.0.0");
define('CSV_IDENTIFIER', "TimeSeriesCSV");

define('CSV_NPOINTS',$metsis_conf['csv_npoints']);
