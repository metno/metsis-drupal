<?php


/**
 * @param $form
 * @param $form_state
 *
 * @return mixed
 */


function metsis_csv_bokeh_download_form($form, &$form_state) {
  global $metsis_conf;
  $form = [];
  $opendap_urls = isset($_GET['opendap_urls']) ? check_plain($_GET['opendap_urls']) : '';
  $opendap_urls = array_map('trim', explode(',', $opendap_urls));
  /**
   * TODO multiple OPeNDAP URLs can be requested as a comma separated list, but
   *      only the first is used for downloading. Issues to address: response time, HTTP request/response size limits.
   */
  $_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_data_uri'] = $opendap_urls[0];

  $bokeh_plot_vars = adc_get_ts_bokeh_plot_vars($_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_data_uri']);
  if (isset($bokeh_plot_vars['y_axis'])) {
    $odv_object = $bokeh_plot_vars['y_axis'];
  }
  if (isset($bokeh_plot_vars['x_axis'])) {
    $odv_object = $bokeh_plot_vars['x_axis'];
  }

  $options = [];
  foreach ($odv_object as $odvo) {
    $options[$odvo] = [
      'standard_name' => $odvo,
    ];
  }
  $form['od_variables'] = [
    '#type' => 'container',
  ];
  $header = [
    'standard_name' => t('Variable'),
  ];
  $form['od_variables_tabular'] = [
    '#type' => 'container',
  ];
  $form['od_variables_tabular']['selected_variables'] = [
    '#type' => 'tableselect',
    '#header' => $header,
    '#options' => $options,
    '#attributes' => [
      'class' => [
        'csv-vars-table',
      ],
    ],
  ];
  $form['csv_file_format'] = array(
    '#type' => 'select',
    '#options' => array(
      'csv' => 'CSV',
      'nc' => 'netcdf'
    ),
    '#default_value' => 'csv',
    '#description' => t(''),
    '#empty' => t(''),
    );
  $form['actions'] = [
    '#type' => 'actions',
  ];
  $form['actions']['submit'] = [
    '#type' => 'submit',
    '#value' => t('Submit'),
    '#submit' => ['metsis_csv_bokeh_download_submit'],
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
function metsis_csv_bokeh_download_submit($form, &$form_state) {
  $_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_download_query'] = adc_get_csv_bokeh_download_query($form_state);
  drupal_goto($_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_download_query']);
}
