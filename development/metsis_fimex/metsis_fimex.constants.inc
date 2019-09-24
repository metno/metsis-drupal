<?php

global $metsis_conf;
if (isset($metsis_conf['transformation_server_getcapabilities'])) {
  define('TRANSFORMATION_SERVER_GETCAPABILITIES', $metsis_conf['transformation_server_getcapabilities']);
} define('TRANSFORMATION_ENDPOINT', $metsis_conf['transformation_endpoint']);
define('TRANSFORMATION_OUTPUT_FORMAT_VISIBLE', $metsis_conf['transformation_output_format_visible']);
