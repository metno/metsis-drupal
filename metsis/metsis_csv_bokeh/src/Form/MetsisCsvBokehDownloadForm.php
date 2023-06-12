<?php

namespace Drupal\metsis_csv_bokeh\Form;

/**
 * @file
 * Contains \Drupal\metsis_csv_bokeh\Form\MetsisCsvBokehPlotForm.
 *
 * Form to download opendap as ascii or netcdf.
 */

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class for the CSV bokeh  form.
 *
 * {@inheritdoc}
 */
class MetsisCsvBokehDownloadForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * {@inheritdoc}
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'csv_bokeh_download';
  }

  /**
   * Build the CSV downloawd form from opendap variables.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // $tempstore = \Drupal::service('tempstore.private')->get('metsis_csv_bokeh');
    $config = \Drupal::config('metsis_csv_bokeh.settings');
    $form = [];

    // Backend uri ncplot for extracting variables and exporting
    // hardcoded for now:
    $form_state->set('download_uri', $config->get('csv_bokeh_download_service'));
    $form_state->set('backend_uri', 'https://ncapi.metsis-api.met.no/ncplot/plot');

    /*
     * @todo multiple OPeNDAP URLs can be requested as a comma separated list,
     * but only the first is used for downloading. Issues to address:
     * response time, HTTP request/response size limits.
     */
    // Get the request referer for go back button.
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    $form_state->set('referer', $referer);

    /*
     * Check if we got opendap urls from http request. then overwite
     * data_uri variable
     */
    $query_from_request = $request->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);

    $opendap_urls = $query['opendap_urls'] ?? '';
    $opendap_urls = array_map('trim', explode(',', $opendap_urls));
    // \Drupal::logger('csv')
    // ->debug('odresource:  <pre><code>' . print_r($opendap_urls, true) . '</code></pre>');
    // @todo Change this to save whole array when multiple urls are supported
    // $tempstore->set('metsis_csv_bokeh_data_uri', $opendap_urls[0]);
    $form_state->set('resource_url', $opendap_urls[0]);
    $bokeh_plot_vars = self::adcGetCsvBokehPlotVars($form_state->get('resource_url'), $form_state);
    if (NULL !== $bokeh_plot_vars) {
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
        '#required' => TRUE,
        '#header' => $header,
        '#options' => $options,
        '#attributes' => [
          'class' => [
            'csv-vars-table',
          ],
        ],
      ];
      $form['csv_file_format'] = [
        '#type' => 'select',
        '#options' => [
          'csv' => 'CSV',
          'nc' => 'netcdf',
        ],
        '#default_value' => 'csv',
      ];
      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];
      /*
      $results = $form_state->getValue('results');
      if (isset($results)) {
      $form['results'] = ['#value' => $form_state->getValue('results'),];
      }
       */
      // Add go back putton.
      $form['actions']['go_back'] = [
        '#type' => 'markup',
        '#markup' => '<a class="adc-button adc-sbutton" href="' . $referer . '">Go back to search</a>',
      ];

      $form['#attached']['library'][] = 'metsis_csv_bokeh/style';
      $form['#attached']['library'][] = 'metsis_lib/adc_buttons';
      $form['#attached']['library'][] = 'metsis_lib/tables';
      return $form;
    }
    else {
      $url = Url::fromUri($form_state->get('referer'));
      $form_state->setRedirectUrl($url);
      $form['actions']['go_back'] = [
        '#type' => 'markup',
        '#markup' => '<a class="adc-button adc-sbutton" href="' . $referer . '">Go back to search</a>',
      ];
      return $form;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Impletment form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /*
     * Download ASCII csv
     */
    // $tempstore = \Drupal::service('tempstore.private')->get('metsis_csv_bokeh');
    $res = self::adcGetCsvBokehdownloadQuery($form_state);
    // $tempstore->set('metsis_csv_bokeh_download_query', $res);
    $response = new RedirectResponse($res);
    $response->send();

    // $this->redirect($tempstore->get('metsis_csv_bokeh_download_query'));
  }

  /**
   * Create downloadquery for the form.
   */
  public function adcGetCsvBokehDownloadQuery(FormStateInterface $form_state) {
    $download_uri = $form_state->get('download_uri');
    $opendap_url = $form_state->get('resource_url');
    $download_file_format = $form_state->getValue('csv_file_format');
    $selected_variables = [];
    $selected_form_variables = $form_state->getValue('selected_variables');

    $selected_variables_string = '';
    foreach ($selected_form_variables as $sv) {
      if ($sv !== 0) {
        array_push($selected_variables, $sv);
      }
    }
    if (!empty($selected_variables)) {
      $selected_variables_string = '&variable=';
      $selected_variables_string .= implode("&variable=", $selected_variables);
    }
    return($download_uri . '?resource_url=' . $opendap_url . '&output_format=' . $download_file_format . $selected_variables_string);
  }

  /**
   * Get the variables.
   */
  public function adcGetCsvBokehPlotVars($data_uri, FormStateInterface $form_state) {

    // Get the backend service url from configuration.
    /* $config = \Drupal::config('metsis_ts_bokeh.configuration'); */
    $backend_uri = $form_state->get('backend_uri');
    // \Drupal::logger('metsis_ts_bokeh')
    // ->debug('Got backend ur config: ' . $backend_uri);
    try {
      $client = \Drupal::httpClient();
      $request = $client->request('GET', $backend_uri, [
        'query' => [
          'get' => 'param',
          'resource_url' => $data_uri,
        ],
      ]);

      $responseStatus = $request->getStatusCode();
      $data = $request->getBody();
      $json_response = Json::decode($data);
      return ($json_response);
    }
    catch (ClientException $e) {
      \Drupal::messenger()->addError("Service call did not succeed. Ensure that the dataset resource URL is correct.");
      \Drupal::messenger()->addError($data_uri);
      /*  \Drupal::messenger()->addError(t(
      "If the dataset resource URL is correct, the @link is wrong. Please check.",
      array('@link' => \Drupal\Core\Link::fromTextAndUrl(
      'backend service URL',
      \Drupal\Core\Url::fromRoute('metsis_ts_bokeh.metsis_ts_bokeh_admin_settings_form')
      )->toString())
      ));*/

      // $response =  new RedirectResponse($form_state->get('referer'));
      // $response->send();
      $url = Url::fromUri($form_state->get('referer'));
      $form_state->setRedirectUrl($url);

      // $form_state->setRedirect($form_state->get('referer'));
      return NULL;
    }
  }

  /**
   * Get the plot data.
   */
  public function adcGetCsvBokehPlot($data_uri, $yaxis) {

    // Get the backend service url from configuration.
    // Config = \Drupal::config('metsis_ts_bokeh.configuration');
    // $backend_uri = $config->get('ts_bokeh_plot_service');.
    $backend_uri = $form_state->get('backend_uri');

    // Do some debugging
    // \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_ts_bokeh_plot backend: ' . $backend_uri);
    // \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_ts_bokeh_plot( ' . $data_uri .', ' . $yaxis . ')');.
    try {
      $client = \Drupal::httpClient();
      $request = $client->request('GET', $backend_uri, [
        'query' => [
          'get' => 'plot',
          'resource_url' => $data_uri,
          'variable' => $yaxis,
        ],
      ]);
    }
    catch (ClientException $e) {
      // Log the error.
      \Drupal::messenger()->addError("Service call did not succeed. Ensure that the dataset resource URL is correct.");
      \Drupal::messenger()->addError($data_uri);
      // watchdog_exception('metsis_ts_bokeh', $e);.
      \Drupal::logger('csv')->error($e);
    }
    $responseStatus = $request->getStatusCode();
    $data = $request->getBody();
    if ($responseStatus != 200) {
      \Drupal::messenger()->addError("Service call did not succeed. Ensure that the following URL is correct.");
      \Drupal::messenger()->addError($data_uri);
      // Return new RedirectResponse(\Drupal\Core\Url::fromRoute('metsis_ts_bokeh.formplot'));
      // return new RedirectResponse($form_state->get('referer'));.
      $url = Url::fromUri($form_state->get('referer'));
      $form_state->setRedirectUrl($url);
    }
    return ($data);
  }

  /**
   * Get the y variables.
   */
  public function adcGetCsvBokehPlotYvars() {
    $data_uri = $tempstore->get('data_uri');
    // \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_plot_y_vars data uri: ' . $data_uri);
    $ts_bokeh_plot_vars = adc_get_ts_bokeh_plot_vars($data_uri);
    $y_vars = $ts_bokeh_plot_vars['y_axis'];
    ksort($y_vars);
    $hy_vars = [];
    foreach ($y_vars as $yv) {
      $hy_vars[$yv] = $yv;
    }
    return ($hy_vars);
  }

}
