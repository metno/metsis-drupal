<?php
/**
 * @param $form
 * @param $form_state
 *
 * @return mixed
 */


function metsis_ts_bokeh_plot_form($form, &$form_state) {
  $opendap_urls = isset($_GET['opendap_urls']) ? check_plain($_GET['opendap_urls']) : '';
  $opendap_urls = array_map('trim', explode(',', $opendap_urls));
  /**
   * TODO multiple OPeNDAP URLs can be requested as a comma separated list, but
   *      only the first is used for plotting. Issues to address: JS conflicts
   *      arising from multiple bokeh plots on page, response time, HTTP request/response size limits.
   */
  $_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_data_uri'] = $opendap_urls[0];
  /**
   * build form based on JSON object returned by pybasket
   */
  //  $default_x_axis = "no default set";
  //  $form['x_axis'] = [
  //    '#type' => 'select',
  //    '#options' => ['time' => 'time'],
  //    '#default_value' => $default_x_axis,
  //    '#description' => t(''),
  //    '#empty' => t(''),
  //  ];

  $default_y_axis = "no default set";
  $form['y_axis'] = [
    '#type' => 'select',
    '#options' => adc_get_ts_bokeh_plot_y_vars(),
    '#default_value' => $default_y_axis,
    '#description' => t(''),
    '#empty' => t(''),
  ];
  $form['actions'] = [
    '#type' => 'actions',
  ];
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Submit'),
    '#submit' => ['metsis_ts_bokeh_plot_submit'],
  ];

  if (isset($form_state['storage']['results'])) {
    $form['results'] = ['#value' => $form_state['storage']['results'],];
  }
  return $form;
}

/**
 * @param $form
 * @param $form_state
 */
function metsis_ts_bokeh_plot_submit($form, &$form_state) {
  $_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_plot_query'] = adc_get_ts_bokeh_plot_query($form_state);
}
