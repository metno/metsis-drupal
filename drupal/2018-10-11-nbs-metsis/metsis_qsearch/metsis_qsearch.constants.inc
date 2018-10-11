<?php

global $metsis_conf;
if (isset($metsis_conf['dar_http_button_text'])) {
    define('DAR_HTTP_BUTTON_TEXT', $metsis_conf['dar_http_button_text']);
} if (isset($metsis_conf['dar_odata_button_text'])) {
    define('DAR_ODATA_BUTTON_TEXT', $metsis_conf['dar_odata_button_text']);
} if (isset($metsis_conf['solr_metadata_button_text'])) {
    define('SOLR_METADATA_BUTTON_TEXT', $metsis_conf['solr_metadata_button_text']);
} define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);
define('BASKET_ELEMENTS_VISIBLE', $metsis_conf['basket_elements_visible']);
define('FORM_RESET_VISIBLE', $metsis_conf['form_reset_visible']);
define('TOPICS_AND_VARIABLES_VISIBLE', $metsis_conf['topics_and_variables_visible']);
define('INSTITUTIONS_VISIBLE', $metsis_conf['institutions_visible']);
define('INVESTIGATOR_VISIBLE', $metsis_conf['investigator_visible']);
define('ABSTRACT_VISIBLE', $metsis_conf['abstract_visible']);
define('DATASETNAME_VISIBLE', $metsis_conf['datasetName_visible']);
define('TITLE_VISIBLE', $metsis_conf['title_visible']);
define('PROJECT_VISIBLE', $metsis_conf['project_visible']);
define('KEYWORDS_VISIBLE', $metsis_conf['keywords_visible']);
define('COLLECTION_PERIOD_VISIBLE', $metsis_conf['collection_period_visible']);
define('PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['platform_long_name_visible']);
define('RESULTS_DATE_DISPLAY_FORMAT', $metsis_conf['results_date_display_format']);
define('SEARCH_MAXIMUM_ROWS_TO_FETCH', $metsis_conf['search_maximum_rows_to_fetch']);
if (isset($metsis_conf['search_max_metadata_age'])) {
    define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
} 