<?php

namespace Drupal\metsis_ts_bokeh\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines HelloController class.
 */
class MetsisTsBokehController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $session = \Drupal::request()->getSession();

    // Get the request referer for go back button.
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $config = \Drupal::config('metsis_ts_bokeh.configuration');
    $backend_uri = $config->get('ts_bokeh_plot_service');
    // $data_uri = $session->get('metsis_ts_bokeh')->get('data_uri');
    // $items = $session->get('items');
    // \Drupal::logger('metsis_ts_bokeh')->debug('buildForm: yaxis form_state is: ' . $form_state->getValue('y_axis'));
    $isinit = $session->get('isinit');
    $opendap_url = $session->get('data_uri');
    $json_data['data']['id1'] = [
      'title' => 'Custom dataset',
      'feature_type' => 'timeSeries',
      'resources' => [
        'opendap' => $opendap_url,
      ],
    ];

    $json_data['email'] = \Drupal::currentUser()->getEmail();
    $json_data['project'] = 'METSIS';

    // \Drupal::logger('metsis_ts_bokeh_json')->debug("@string", ['@string' => \Drupal\Component\Serialization\Json::encode($json_data)]);
    $markup = "<h2> Ooops Something went wrong!!</h2> Contact Administraor or see logs";
    try {
      $client = \Drupal::httpClient();

      // $client->setOptions(['debug' => TRUE]);
      $request = $client->request(
            'POST',
      // 'https://pybasket.epinux.com/post_baskettable0',
      $backend_uri,
            [
              'json' => $json_data,
              'Accept' => 'text/html',
              'Content-Type' => 'application/json',
              'debug' => FALSE,
            ],
        );

      $responseStatus = $request->getStatusCode();
      // \Drupal::logger('metsis_dashboard_bokeh_json_testpost')->debug("response status: @string", ['@string' => $responseStatus ]);
      $data = $request->getBody();
      // \Drupal::logger('metsis_dashboard_bokeh_testpost')->debug(t("Got original response: @markup", ['@markup' => $data] ) );
      // $markup = \Drupal\Component\Serialization\Json::decode($data);
      // $data = str_replace("\n", "", $data);
      // $data = str_replace("\r", "", $data);
      // $data = preg_replace(‘\/\s+\/’, ‘ ‘, trim($data)).”\n”;
      // $markup = $data;
      // return ($json_response);
    }
    catch (Exception $e) {
      \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", ['@uri' => $backend_uri]);
      \Drupal::messenger()->addError($e);
    }
    // $markup = preg_replace("/\n/"," ",$data);
    // $markup = trim(preg_replace('/\s\s+/', ' ', $data));
    // $markup = str_replace("/\n", " " , $data);
    /*  $data = str_replace("\\n"," ", $data);
    $data = str_replace("\n"," ", $data);
    $data = str_replace("\r", " ", $data);
    $data = ltrim($data, '"');
    $data = rtrim($data, '"');
     */
    $markup = $data;

    // $markup = str_replace(array("\n","\r\n","\r"), '', $data);
    // $markup = $this->getDashboard($backend_uri, $resources);
    // \Drupal::logger('metsis_dashboard_bokeh_testpost')->debug(t("Got markup response: @markup", ['@markup' => $markup ] ) );
    // \Drupal::logger('metsis_dashboard_bokeh')->debug("Using endpoint: " . $backend_uri);
    // \Drupal::logger('metsis_dashboard_bokeh')->debug("Got status code: " . $responseStatus);
    // Build page
    // Create content wrapper
    $build['content'] = [
      '#prefix' => '<div class="w3-container clearfix">',
      '#suffix' => '</div>',
    ];

    $build['content']['back'] = [
      '#markup' => '<a class="w3-btn" href="' . $referer . '">Go back</a>',
    ];

    /*
    $build['content']['endpoint'] = [
    '#type' => 'markup',
    '#markup' => '<p>Using endpoint : ' .   $backend_uri . '</p>',


    ];

    $build['content']['status'] = [
    '#type' => 'markup',
    '#markup' => '<p>Got statusCode: ' . $responseStatus . '</p>',


    ];
     */
    $build['content']['dashboard-wrapper'] = [
      '#type' => 'markup',
      '#markup' => '<div id="bokeh-dashboard" class="dashboard">',
    ];
    $build['content']['dashboard-wrapper']['dashboard'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#suffix' => '</div>',
      '#allowed_tags' => ['script', 'div', 'tr', 'td'],

    ];

    // Add bokeh libraries.
    $build['#attached'] = [
      'library' => [
        'metsis_ts_bokeh/style',
        'metsis_ts_bokeh/bokeh_js',
        'metsis_ts_bokeh/bokeh_widgets',
        'metsis_ts_bokeh/bokeh_tables',
        'metsis_ts_bokeh/bokeh_api',
      ],
    ];
    return $build;
  }

  /**
   * Get the plot markup.
   */
  public function getPlot() {
    $session = \Drupal::request()->getSession();

    // Get the request referer for go back button.
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');

    $config = \Drupal::config('metsis_ts_bokeh.configuration');
    $backend_uri = $config->get('ts_bokeh_plot_service');
    $opendap_url = $session->get('data_uri');

    /*$markup = "<h2> Ooops Something went wrong!!</h2> Error from service: " . $backend_uri . '<br><br>';
    try {
    $client = \Drupal::httpClient();

    //$client->setOptions(['debug' => TRUE]);
    $request = $client->request(
    'GET',
    $backend_uri,
    [
    'query' => [
    'url' => $opendap_url
    ],
    'debug' => FALSE,
    ],



    );

    $responseStatus = $request->getStatusCode();
    \Drupal::logger('metsis_tsplot')->debug("response status from" . $backend_uri . " : @string", ['@string' => $responseStatus ]);
    $data = (string) $request->getBody();


    //$markup = \Drupal\Component\Serialization\Json::decode($data);
    //$data = str_replace("\n", "", $data);
    //$data = str_replace("\r", "", $data);
    //$data = preg_replace(‘\/\s+\/’, ‘ ‘, trim($data)).”\n”;

    //$markup = $data;
    //return ($json_response);
    }
    catch (Exception $e){
    \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", [ '@uri' => $backend_uri]);
    \Drupal::messenger()->addError($e);
    $markup .= $e;
    }
    //$markup = preg_replace("/\n/"," ",$data);
    //$markup = trim(preg_replace('/\s\s+/', ' ', $data));
    //$markup = str_replace("/\n", " " , $data);

    $markup = $data;

    //$markup = str_replace(array("\n","\r\n","\r"), '', $data);
    //$markup = $this->getDashboard($backend_uri, $resources);
    //dpm($markup);
    //\Drupal::logger('metsis_dashboard_bokeh')->debug("Using endpoint: " . $backend_uri);
    //\Drupal::logger('metsis_dashboard_bokeh')->debug("Got status code: " . $responseStatus);

    // Build page
    //Create content wrapper
    $build['content'] = [
    '#prefix' => '<div class="w3-container clearfix">',
    '#suffix' => '</div>'
    ];


    $build['content']['back'] = [
    '#markup' => '<a class="w3-btn" href="'. $referer . '">Go back</a>',
    ];


    $build['content']['tsplot-wrapper'] = [
    '#type' => 'markup',
    '#prefix' => '<div id="bokeh-tsplot" class="tsplot">',
    '#suffix' => '</div>',
    ];
    $build['content']['tsplot-wrapper']['plot'] = [
    '#type' => 'markup',
    '#prefix' => '<script>',
    '#markup' => $markup,
    '#suffix' => '</script>',
    '#allowed_tags' => ['script','div','tr','td'],

    ];


    //Add bokeh libraries
    $build['#attached'] = [
    'library' => [
    'metsis_ts_bokeh/style',
    'metsis_ts_bokeh/bokeh_js',
    'metsis_ts_bokeh/bokeh_widgets',
    'metsis_ts_bokeh/bokeh_tables',
    'metsis_ts_bokeh/bokeh_api',
    ],
    ];
    return $build; */
    \Drupal::logger('metsis_tsplot')->debug("Calling endpoint: " . $backend_uri . '?url=' . $opendap_url);
    $build['content'] = [
      '#prefix' => '<div class="w3-container clearfix">',
      '#suffix' => '</div>',
    ];

    $build['content']['back'] = [
      '#markup' => '<a class="w3-btn" href="' . $referer . '">Go back</a>',
    ];

    $build['plot'] = [
      '#type' => 'inline_template',
      '#template' => '<iframe src="{{ url }}" width="100%" height="725" frameborder=0 scrolling=no> title="Timeseries Bokeh Plot"</iframe>',
      '#context' => [
        'url' => $backend_uri . '?url=' . $opendap_url . '&bokeh_log_level=debug',
      ],
    ];
    $build['#cache'] = [
    // 'disabled' => true,
  ];

    return $build;
  }

}
