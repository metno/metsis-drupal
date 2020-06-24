<?php

/**
 * @param $form_state
 *
 * @return string
 */
function adc_get_csv_bokeh_download_query($form_state) {
  $sra = explode("=param", $_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_data_uri']);
  $download_file_format = $form_state['values']['csv_file_format'];
  $selected_variables = [];
  $selected_variables_string='';
  foreach ($form_state['input']['selected_variables'] as $sv) {
    if ($sv != NULL) {
      array_push($selected_variables, $sv);
    }
  }
  if(!empty($selected_variables)){
    $selected_variables_string = '&variable=';
    $selected_variables_string .= implode("&variable=", $selected_variables);
  }
  return(CSV_BOKEH_DOWNLOAD_SERVICE . '?resource_url=' . $sra[0] . '&output_format='.$download_file_format.$selected_variables_string);
}
