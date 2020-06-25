<?php

/**
 * The globals must be able to be set form the module admin form
 * Only constants should be set here
 * Constants should have defaults values
 * Constants should be read from the global $metsis_csv_bokeh; if they are
 * defined
 */
global $metsis;
if (isset($metsis['csv_bokeh_download_node'])) {
  define('CSV_BOKEH_DOWNLOAD_NODE', $metsis['csv_bokeh_download_node']);
}
else {
  drupal_set_message('METSIS CSV Bokeh module is not configured. Visit the module admin page.', 'warning');
}
if (isset($metsis['csv_bokeh_download_service'])) {
  define('CSV_BOKEH_DOWNLOAD_SERVICE', $metsis['csv_bokeh_download_service']);
}
else {
  drupal_set_message('METSIS CSV Bokeh module is not configured. Visit the module admin page.', 'warning');
}
