<?php

namespace Drupal\metsis_dashboard_bokeh\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Render\Markup;
use Drupal\metsis_basket\Entity\BasketItem;

class DashboardBokehController extends ControllerBase
{
    public function build()
    {
        $config = \Drupal::config('metsis_dashboard_bokeh.configuration');
        $backend_uri = $config->get('dashboard_bokeh_service');
        //$backend_uri = 'https://metsis.metsis-api.met.no/dashboard';
        //Get the user_id
        $user_id = (int) \Drupal::currentUser()->id();

        //Get the refering page
        $session = \Drupal::request()->getSession();
        $referer = $session->get('back_to_search');

        //For testing dashboard. To be removed
        //$resources_test = 'http://hyrax.epinux.com/opendap/SN99938.nc,http://hyrax.epinux.com/opendap/ctdiaoos_gi2007_2009.nc,http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc';

        \Drupal::logger('metsis_dashboard_bokeh')->debug('Configured backend: @backend', ['@backend' => $backend_uri ]);
        //$resources = $store->get('basket');
        $resources = $this->getOpendapUris($user_id);

        //Json data
        $json_data = $this->getJsonData($user_id);
        $resources = $json_data;
        //dpm($json_data);

        //\Drupal::logger('metsis_dashboard_bokeh_json')->debug("@string", ['@string' => \Drupal\Component\Serialization\Json::encode($json_data)]);

        /**
         * FIXME: This IF-caluse is for testing only. Should be removed for prod
         */
        //if($resources == NULL) { $resources = explode(',', $resources_test); }

        $markup = $this->getDashboard($backend_uri, $resources);
        //\Drupal::logger('metsis_dashboard_bokeh_get')->debug(t("@markup", ['@markup' => $markup ]));

        // Build page
        //Create content wrapper
        $build['content'] = [
        '#prefix' => '<div class="w3-container">',
        '#suffix' => '</div>'
      ];


        $build['content']['back'] = [
        '#markup' => '<a class="w3-btn" href="'. $referer . '">Go back to search </a>',
      ];

        $build['content']['dashboard-wrapper'] = [
        '#type' => 'markup',
        '#markup' => '<div id="bokeh-dashboard" class="w3-card">',
        '#attached' => [
          'library' => [
            'metsis_dashboard_bokeh/dashboard',
          ],
        ],
      ];
        $build['content']['dashboard-wrapper']['dashboard|'] = [
        '#type' => 'markup',
        '#markup' => $markup,
        '#suffix' => '</div>',
        '#allowed_tags' => ['script','div'],

      ];


        return $build;
    }

    public function post_datasource()
    {
        $config = \Drupal::config('metsis_dashboard_bokeh.configuration');
        //$config = $this->configFactory('metsis_dashboard_bokeh.configuration');

        $backend_uri = $config->get('dashboard_bokeh_service');
        //$backend_uri = 'https://pybasket.epinux.com/post_jsondict';
        //Get the user_id
        $user_id = (int) \Drupal::currentUser()->id();
        //$user_id = (int) $this->currentUser->id();

        //Get the refering page
        $session = \Drupal::request()->getSession();
        $referer = $session->get('back_to_search');


        //\Drupal::logger('metsis_dashboard_bokeh_testpost')->debug("Got backend @backend", ['@backend' => $backend_uri ]);

        $json_data = $this->getJsonData($user_id);
        //dpm($json_data);


        /*
                $json_data =  \Drupal\Component\Serialization\Json::decode('{
                  "data": {
                    "id1": {
                      "title": "osisaf sh icearea seasonal",
                      "feature_type": "timeSeries",
                      "resources": {
                        "opendap": [
                          "http://hyrax.epinux.com/opendap/osisaf_sh_icearea_seasonal.nc"
                        ]
                      }
                    },
                    "id2": {
                      "title": "osisaf nh iceextent daily",
                      "feature_type": "timeSeries",
                      "resources": {
                        "opendap": [
                          "http://hyrax.epinux.com/opendap//osisaf_nh_iceextent_daily.nc"
                        ]
                      }
                    },
                    "id3": {
                      "title": "itp01_itp1grd2042",
                      "feature_type": "profile",
                      "resources": {
                        "opendap": [
                          "http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc"
                        ]
                      }
                    },
                    "id4": {
                      "title": "itp01_itp1grd2042",
                      "feature_type": "NA",
                      "resources": {
                        "opendap": [
                          "http://hyrax.epinux.com/opendap/itp01_itp1grd2042.nc"
                        ]
                      }
                    },
                    "id5": {
                      "title": "ctdiaoos gi2007 2009",
                      "feature_type": "timeSeriesProfile",
                      "resources": {
                        "opendap": [
                          "http://hyrax.epinux.com/opendap/ctdiaoos_gi2007_2009.nc"
                        ]
                      }
                    },
                    "id6": {
                      "title": "High resolution sea ice concentration",
                      "feature_type": "NA",
                      "resources": {
                        "OGC:WMS": [
                          "https://thredds.met.no/thredds/wms/cmems/si-tac/cmems_obs-si_arc_phy-siconc_nrt_L4-auto_P1D_aggregated?service=WMS&version=1.3.0&request=GetCapabilities"
                        ]
                      }
                    },
                    "id7": {
                      "title": "S1A EW GRDM",
                      "feature_type": "NA",
                      "resources": {
                        "OGC:WMS": [
                          "http://nbswms.met.no/thredds/wms_ql/NBS/S1A/2021/05/18/EW/S1A_EW_GRDM_1SDH_20210518T070428_20210518T070534_037939_047A42_65CD.nc?SERVICE=WMS&REQUEST=GetCapabilities"
                        ]
                      }
                    }
                  },
                  "email": "epiesasha@me.com",
                  "project": "METSIS",
                  "notebook": true,
                  "notebooks": {
                    "UseCase2": {
                      "name": "UseCase",
                      "purpose": "cool science",
                      "resource": "https://raw.githubusercontent.com/UseCase.ipynb"
                    }
                  }
                }');

        */
        //\Drupal::logger('metsis_dashboard_bokeh_json_testpost')->debug("json_body: @string", ['@string' => $json_body ]);
        //\Drupal::logger('metsis_dashboard_bokeh_json_testpost')->debug("json_data: @string", ['@string' => $json_data ]);

        //Get markup from bokeh dashboard endpoint. post json_data.
        $markup = "<h2> Ooops Something went wrong!!</h2> Contact Administraor or see logs";
        try {
            $client = \Drupal::httpClient();

            //$client->setOptions(['debug' => TRUE]);
            $request = $client->request(
                'POST',
            //'https://pybasket.epinux.com/post_baskettable0',
            $backend_uri,
                [
              'json' => $json_data,
              'Accept' => 'text/html',
              'Content-Type' => 'application/json',
              'debug' => false,
            ],
            );


            $responseStatus = $request->getStatusCode();
            //\Drupal::logger('metsis_dashboard_bokeh_json_testpost')->debug("response status: @string", ['@string' => $responseStatus ]);
            $data = (string) $request->getBody();
            //dpm($data);
            //\Drupal::logger('metsis_dashboard_bokeh_testpost')->debug(t("Got original response: @markup", ['@markup' => $data]));


            //$markup = \Drupal\Component\Serialization\Json::decode($data);
        //$data = str_replace("\n", "", $data);
        //$data = str_replace("\r", "", $data);
        //$data = preg_replace(‘\/\s+\/’, ‘ ‘, trim($data)).”\n”;

        //$markup = $data;
        //return ($json_response);
        } catch (Exception $e) {
            \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", [ '@uri' => $backend_uri]);
            \Drupal::messenger()->addError($e);
        }
        //$markup = preg_replace("/\n/"," ",$data);
        //$markup = trim(preg_replace('/\s\s+/', ' ', $data));
        //$markup = str_replace("/\n", " " , $data);
        /*  $data = str_replace("\\n"," ", $data);
          $data = str_replace("\n"," ", $data);
          $data = str_replace("\r", " ", $data);
          $data = ltrim($data, '"');
          $data = rtrim($data, '"');
*/
        $markup = $data;
        //dpm($markup);
        //$markup = str_replace(array("\n","\r\n","\r"), '', $data);
        //$markup = $this->getDashboard($backend_uri, $resources);
        //\Drupal::logger('metsis_dashboard_bokeh_testpost')->debug(t("Got markup response: @markup", ['@markup' => $markup ]));
        //\Drupal::logger('metsis_dashboard_bokeh')->debug("Using endpoint: " . $backend_uri);
        //  \Drupal::logger('metsis_dashboard_bokeh')->debug("Got status code: " . $responseStatus);

        // Build page
        //Create content wrapper
        $build = [];
        $build['dashboard'] = [
        '#prefix' => '<div class="w3-container w3-border w3-padding dashBoardCard">',
      #  '#type' => 'markup'
        '#suffix' => '</div>',
        '#type' => 'container',
      ];
        /*
                $build['dashboard']['header'] = [
              '#markup' => '<header class="w3-center w3-container"><h2> My dashboard</h2></header>',
              '#type' => 'markup',
            ];
        */
        /*    $build['dashboard']['back'] = [
              '#markup' => '<a class="w3-btn" href="'. $referer . '">Go back to search </a>',
            ];
*/
        /*
              $build['dashboard']['endpoint'] = [
                '#type' => 'markup',
                '#markup' => '<p>Using endpoint : ' .   $backend_uri . '</p>',


              ];

              $build['dashboard']['status'] = [
                '#type' => 'markup',
                '#markup' => '<p>Got statusCode: ' . $responseStatus . '</p>',


              ];
        *//*
            $build['dashboard']['dashboard-wrapper'] = [
              '#type' => 'markup',
              '#markup' => //'<div id="bokeh-dashboard" class="dashboard">',

            ]; */
        /*
                $build['dashboard']['dashboard-wrapper'] = [
                    '#prefix' => '<div id="bokeh-dashboard" class="w3-center dashboard">',
                    '#type' => 'markup',
                    '#markup' => $markup,
                    '#suffix' => '</div>',
                    '#allowed_tags' => ['script','div','tr','td', 'css', 'button'],

                  ]; */
        $build['dashboard']['dashboard-wrapper'] = [
              '#prefix' => '<div id="bokeh-dashboard" class="w3-center dashboard" allowfullscreen>',
              '#type'     => 'inline_template',
                '#template' => '{{ dashboard | raw }}',
                '#context'  => [
                  'dashboard' => $markup
                ],
                '#suffix' => '</div>',
              ];


        //Add bokeh libraries
        //  $build['#attached'] = [
        //  'library' => [
        //    'metsis_dashboard_bokeh/dashboard',
        //  ],
        //];
        return $build;
    }



    public function jsonTest()
    {
        $config = \Drupal::config('metsis_dashboard_bokeh.configuration');
        $backend_uri = $config->get('dashboard_bokeh_service');
        //$backend_uri = 'https://pybasket.epinux.com/post_jsondict';
        //Get the user_id
        $user_id = (int) \Drupal::currentUser()->id();

        //Get the refering page
        $session = \Drupal::request()->getSession();
        $referer = $session->get('back_to_search');


        //\Drupal::logger('metsis_dashboard_bokeh_testpost')->debug(t("@backend", ['@backend' => $backend_uri ]));

        $json_data = $this->getJsonData($user_id);
        //dpm($json_data);



        $json_body =  \Drupal\Component\Serialization\Json::encode($json_data);
        $build['content'] = [
        '#type' => 'markup',
        '#title' => 'JSON',
        '#markup' => '<code>' . $json_body. '</code>',
        '#allowed_tags' => ['code'],
      ];




        return $build;
    }
    public function getDashboard($backend_uri, $resources)
    {

      //Create datasources query parameters from resources
        $res_list = $resources;
        $query_params = "?";
        /*
        foreach ($res_list as $r) {
          $query_params .= 'datasources=' . urlencode($r) . '&';
        }
        */
        $query_params .= 'datasources=' . \Drupal\Component\Serialization\Json::encode($resources);
        // Add user email query parameter
        $query_params .= 'email=' .  \Drupal::currentUser()->getEmail();
        /*    try {
                 $client = \Drupal::httpClient();
                 //$client->setOptions(['debug' => TRUE]);
                 $request = $client->request('GET', $backend_uri,
                 ['debug' => TRUE,
                   'headers' => [
                   'Accept' => 'application/json',
                   ],
                   'query' => [
                     'datasources' => $resources,
                     'email' =>  \Drupal::currentUser()->getEmail(),
                   ],
                 ]
             );
             $responseStatus = $request->getStatusCode();
             $data = $request->getBody();
             $json_response = \Drupal\Component\Serialization\Json::decode($data);
           }*/
        $params = [
       'query' => [
         'datasources' => \Drupal\Component\Serialization\Json::encode($resources),
         'email' => \Drupal::currentUser()->getEmail(),
       ],
     ];
        try {
            $client = \Drupal::httpClient();

            //$client->setOptions(['debug' => TRUE]);
            $request = $client->request(
                'GET',

          //'https://pybasket.epinux.com/post_baskettable0',
          $backend_uri,
                $params,
                [
          //  'json' => $json_data,
            'Accept' => 'text/html',
            'Content-Type' => 'application/json',
            'debug' => false,
          ],
            );

            $responseStatus = $request->getStatusCode();
            $data = $request->getBody();
            //\Drupal::logger('metsis_dashboard_bokeh_get_data')->debug(t("@markup", ['@markup' => $data ]));
            $json_response = \Drupal\Component\Serialization\Json::decode($data);
            //return ($json_response);
        } catch (Exception $e) {
            \Drupal::messenger()->addError("Could not contact bokeh dashboard api at @uri .", [ '@uri' => $backend_uri]);
            \Drupal::messenger()->addError($e);
        }
        return $data;
        //return $json_response;
    }

    public function getOpendapUris($user_id)
    {
        //Fetch opendap uris:
        $query = \Drupal::database()->select('metsis_basket', 'm');
        $query->fields('m', ['data_access_resource_opendap']); //['data_access_resource_opendap']);
        $query->condition('m.uid', $user_id, '=');
        $results = $query->execute()->fetchCol();
        $opendap_uris = [];


        foreach ($results as $record) {
            $opendap_uris[] = $record;
        }

        return $opendap_uris;
    }
    public function getJsonData($user_id)
    {
        //Fetch opendap uris:
        $query = \Drupal::database()->select('metsis_basket', 'm');
        $query->fields('m', ['metadata_identifier', 'feature_type', 'title', 'dar' ]); //['data_access_resource_opendap']);
        $query->condition('m.uid', $user_id, '=');
        $results = $query->execute();
        $json_data = [];

        foreach ($results as $record) {
            //dpm($record);
            $json_data['data'][(string) $record->metadata_identifier] = [
            'title' => (string) $record->title,
            'feature_type' => (string) $record->feature_type,
            'resources' => unserialize($record->dar),
        ];
        }
        if (is_null($results) || !array_key_exists('data', $json_data)) {
            $json_data['data'] = [];
        }
        $json_data['email'] = \Drupal::currentUser()->getEmail();
        $json_data['project'] = 'METSIS';
        //ADD notebook to dashboard
        $config = \Drupal::config('metsis_dashboard_bokeh.configuration');
        $notebook = $config->get('dashboard_notebook_service');
        if ($notebook) {
            $json_data['notebook'] = true;
            $json_data['notebooks'] = [
              "UseCase2" =>  [
                "name" =>  "UseCase",
                "purpose" =>  "cool science",
                "resource" =>  "https://raw.githubusercontent.com/UseCase.ipynb",
              ],
            ];
        //$json_data['notebooks'] = '{}';
        } else {
            $json_data['notebook'] = false;
        }
        //dpm($json_data);
        return $json_data;
    }
}
