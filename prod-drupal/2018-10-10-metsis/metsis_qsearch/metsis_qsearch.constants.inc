<?php

global $metsis_conf;
define('METSIS_QSEARCH_ENDPOINT', $metsis_conf['metsis_qsearch_endpoint']);
if (isset($metsis_conf['dar_http_button_text'])) {
    define('DAR_HTTP_BUTTON_TEXT', $metsis_conf['dar_http_button_text']);
} if (isset($metsis_conf['dar_odata_button_text'])) {
    define('DAR_ODATA_BUTTON_TEXT', $metsis_conf['dar_odata_button_text']);
} if (isset($metsis_conf['solr_metadata_button_text'])) {
    define('SOLR_METADATA_BUTTON_TEXT', $metsis_conf['solr_metadata_button_text']);
} define('TS_VISUALIZATION_BUTTON_TEXT', $metsis_conf['ts_visualization_button_text']);
define('CSV_BUTTON_TEXT', $metsis_conf['csv_button_text']);
define('SOLR_SERVER_IP', $metsis_conf['solr_server_ip']);
define('SOLR_SERVER_PORT', $metsis_conf['solr_server_port']);
define('BASKET_ELEMENTS_VISIBLE', $metsis_conf['basket_elements_visible']);
define('FORM_RESET_VISIBLE', $metsis_conf['form_reset_visible']);
define('TOPICS_AND_VARIABLES_VISIBLE', $metsis_conf['topics_and_variables_visible']);
define('TOPICS_AND_VARIABLES_INITIALLY_COLLAPSED', $metsis_conf['topics_and_variables_initially_collapsed']);
define('INSTITUTIONS_VISIBLE', $metsis_conf['institutions_visible']);
define('INSTITUTIONS_INITIALLY_COLLAPSED', $metsis_conf['institutions_initially_collapsed']);
define('INVESTIGATOR_VISIBLE', $metsis_conf['investigator_visible']);
define('INVESTIGATOR_INITIALLY_COLLAPSED', $metsis_conf['investigator_initially_collapsed']);
define('ABSTRACT_VISIBLE', $metsis_conf['abstract_visible']);
define('DATASETNAME_VISIBLE', $metsis_conf['datasetName_visible']);
define('TITLE_VISIBLE', $metsis_conf['title_visible']);
define('PROJECT_VISIBLE', $metsis_conf['project_visible']);
define('KEYWORDS_VISIBLE', $metsis_conf['keywords_visible']);
define('COLLECTION_PERIOD_VISIBLE', $metsis_conf['collection_period_visible']);
define('DATA_COLLECTION_PERIOD_INITIALLY_COLLAPSED', $metsis_conf['data_collection_period_initially_collapsed']);
define('PLATFORM_LONG_NAME_VISIBLE', $metsis_conf['platform_long_name_visible']);
define('PLATFORM_LONG_NAME_INITIALLY_COLLAPSED', $metsis_conf['platform_long_name_initially_collapsed']);
define('CLOUD_COVER_VALUE_INITIALLY_COLLAPSED', $metsis_conf['cloud_cover_value_initially_collapsed']);
define('RESULTS_DATE_DISPLAY_FORMAT', $metsis_conf['results_date_display_format']);
define('SEARCH_MAXIMUM_ROWS_TO_FETCH', $metsis_conf['search_maximum_rows_to_fetch']);
if (isset($metsis_conf['search_max_metadata_age'])) {
    define('SEARCH_MAX_METADATA_AGE', $metsis_conf['search_max_metadata_age']);
} define('BOUNDING_BOX_INITIALLY_COLLAPSED', $metsis_conf['bounding_box_initially_collapsed']);
define('FULL_TEXT_SEARCH_INITIALLY_COLLAPSED', $metsis_conf['full_text_search_initially_collapsed']);
