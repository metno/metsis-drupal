<?php

/**
 * @param $form_state
 *
 * @return string
 */
function adc_get_csv_bokeh_download_query($form_state) {
  $tempstore = \Drupal::service('tempstore.private')->get('metsis_csv_bokeh');
  $config = \Drupal::config('metsis_csv_bokeh.settings');
  $sra = explode("=param",$tempstore->get('metsis_csv_bokeh_data_uri'));
  $download_file_format = $form_state->getValue('csv_file_format');
  $selected_variables = [];
  $selected_variables_string='';
  foreach ($form_state->getValue('selected_variables') as $sv) {
    if ($sv != NULL) {
      array_push($selected_variables, $sv);
    }
  }
  if(!empty($selected_variables)){
    $selected_variables_string = '&variable=';
    $selected_variables_string .= implode("&variable=", $selected_variables);
  }
  return($config->get('csv_bokeh_download_service') . '?resource_url=' . $sra[0] . '&output_format='.$download_file_format.$selected_variables_string);
}
