<?php

global $metsis_conf;
define('TS_ENDPOINT', $metsis_conf['ts_endpoint']);
define('TS_PLOT_NPOINTS', $metsis_conf['ts_plot_npoints']);
define('TS_SERVER', $metsis_conf['ts_server']);
define('TS_SERVER_PROTOCOL', "http");
define('TS_SERVER_PORT', "443");
define('TS_SERVICE_PATH', "cgi-bin/adcwps");
define('TS_SERVICE_PROVIDER', "");
define('TS_METAPATH', "");
define('TS_SERVICE_NAME', "WPS");
define('TS_REQUEST', "execute");
define('TS_WPS_VERSION', "1.0.0");
define('TS_IDENTIFIER', "TimeSeries");
