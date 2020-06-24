<?php

/**
 * @param $uri
 *
 * @return mixed
 */
function adc_get_ts_bokeh_plot_vars($uri) {
  $request = drupal_http_request(TS_BOKEH_PLOT_SERVICE . '?get=param&resource_url=' . $uri);
  /**
   * TODO - validate the data URI - issue waring if no data is found
   */
  $json_response = drupal_json_decode($request->data);
  return ($json_response);
}

/**
 * @param string $uri
 *
 * @return mixed
 */

/**
 * @return array
 */
function adc_get_ts_bokeh_plot_y_vars() {
  $ts_bokeh_plot_vars = adc_get_ts_bokeh_plot_vars($_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_data_uri']);

  if (isset($ts_bokeh_plot_vars['y_axis'])) {
    $y_vars = $ts_bokeh_plot_vars['y_axis'];
  }
  if (isset($ts_bokeh_plot_vars['x_axis'])) {
    $y_vars = $ts_bokeh_plot_vars['x_axis'];
  }
  ksort($y_vars);
  $hy_vars = [];
  foreach ($y_vars as $yv) {
    $hy_vars[$yv] = $yv;
  }
  unset($y_vars);
  return ($hy_vars);
}

/**
 * @param $form_state
 *
 * @return string
 */
function adc_get_ts_bokeh_plot_query($form_state) {
  $sra = explode("=param", $_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_data_uri']);
  return (TS_BOKEH_PLOT_SERVICE . '?get=plot&resource_url=' . $sra[0] . '&metadata=true&variable=' . $form_state['values']['y_axis']);
}
