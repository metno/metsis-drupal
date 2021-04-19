<?php
/*
 * Utils methods for the metsis_ts_bokeh module
 *
 *
 */
//use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Form\FormStateInterface;
//use GuzzleHttp\Exception\RequestException;
//use GuzzleHttp\Exception\ServerException;
use \Drupal\Core\Link;

/*
 * @param $uri
 *
 * @return mixed
 */
function adc_get_ts_bokeh_plot_vars($data_uri) {

  //Get the backend service url from configuration
  $config = \Drupal::config('metsis_ts_bokeh.configuration');
  $backend_uri = $config->get('ts_bokeh_plot_service');
  \Drupal::logger('metsis_ts_bokeh')->debug('Got backend ur config: ' . $backend_uri );
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
    $json_response = \Drupal\Component\Serialization\Json::decode($data);
    return ($json_response);
  }
  catch (Exception $e){
    \Drupal::messenger()->addError("Service call did not succeed. Ensure that the dataset resource URL is correct.");
    \Drupal::messenger()->addError($data_uri);
    \Drupal::messenger()->addError(t("If the dataset resource URL is correct, the @link is wrong. Please check.",
      array('@link' => \Drupal\Core\Link::fromTextAndUrl('backend service URL',
        \Drupal\Core\Url::fromRoute('metsis_ts_bokeh.metsis_ts_bokeh_admin_settings_form'))->toString())));

    $response =  new RedirectResponse('/metsis/bokeh/init');
    $response->send();

  }

}

/*
 * @param string $uri
 *
 * @return mixed
 */
function adc_get_ts_bokeh_plot($data_uri, $yaxis) {

  //Get the backend service url from configuration
  $config = \Drupal::config('metsis_ts_bokeh.configuration');
  $backend_uri = $config->get('ts_bokeh_plot_service');

  //Do some debugging
  \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_ts_bokeh_plot backend: ' . $backend_uri );
  \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_ts_bokeh_plot( ' . $data_uri .', ' . $yaxis . ')');

  try {
    $client = \Drupal::httpClient();
    $request = $client->request('GET', $backend_uri, [
      'query' => ['get' => 'plot',
      'resource_url' => $data_uri,
      'variable' => $yaxis
      ],
    ]);
  }
  catch (RequestException $e){
    // Log the error.
    watchdog_exception('metsis_ts_bokeh', $e);
  }
  $responseStatus = $request->getStatusCode();
  $data = $request->getBody();
  if ($responseStatus != 200) {
    \Drupal::messenger()->addError("Service call did not succeed. Ensure that the following URL is correct.");
    \Drupal::messenger()->addError($uri);
    return new RedirectResponse(\Drupal\Core\Url::fromRoute('metsis_ts_bokeh.formplot'));
  }
  return ($data);
}

/*
 * @return array
 */
function adc_get_ts_bokeh_plot_y_vars() {

  $tempstore = \Drupal::service('tempstore.private')->get('metsis_ts_bokeh');
  $data_uri = $tempstore->get('data_uri');
  \Drupal::logger('metsis_ts_bokeh')->debug('adc_get_plot_y_vars data uri: ' . $data_uri );

  $ts_bokeh_plot_vars = adc_get_ts_bokeh_plot_vars($data_uri);
  $y_vars = $ts_bokeh_plot_vars['y_axis'];
  ksort($y_vars);
  $hy_vars = [];
  foreach ($y_vars as $yv) {
    $hy_vars[$yv] = $yv;
  }
  return ($hy_vars);

}
