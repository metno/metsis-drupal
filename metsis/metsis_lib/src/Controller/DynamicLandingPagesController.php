<?php

namespace Drupal\metsis_lib\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Drupal\Component\Serialization\Json;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;


use XSLTProcessor;
use SimpleXMLElement;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Class DynamicLandingPagesController.
 */
class DynamicLandingPagesController extends ControllerBase
{
    /**
     * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
     *
     * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
     */
    protected $loggerFactory;

    /**
     * Drupal\Core\Config\ConfigFactoryInterface definition.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * Solarium\Core\Query\Helper definition.
     *
     * @var \Solarium\Core\Query\Helper
     */
    protected $solariumQueryHelper;


    /**
     * Json serializer.
     *
     * @var \Drupal\Component\Serialization\Json
     */
    protected $json;


    /**
     * The geoPhpWrapper service.
     *
     * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
     */
    protected $geoPhpWrapper;


    public const LICENCES = [
      'CC0-1.0' => [
        'url' => 'https://spdx.org/licenses/CC0-1.0',
        'img' => '/modules/metsis/metsis_search/icons/CC0.png',
      ],
      'CC-BY-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBY.png',
      ],
      'CC-BY/NLOD' => [
        'url' => 'https://spdx.org/licenses/CC-BY-4.0',
        'img' => '"/modules/metsis/metsis_search/icons/CCBY.png',
      ],
      'CC BY/NLOD' => [
        'url' => 'https://spdx.org/licenses/CC-BY-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBY.png',
      ],
      'CC-BY-SA-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-SA-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBYSA.png',
      ],
      'CC-BY-NC-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-NC-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBYNC.png',
      ],
      'CC-BY-NC' => [
          'url' => 'https://spdx.org/licenses/CC-BY-NC-4.0',
          'img' => '/modules/metsis/metsis_search/icons/CCBYNC.png',
      ],
      'CC-BY-NC-SA-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-NC-SA-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBYNCSA.png',
      ],
      'CC-BY-ND-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-ND-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBYND.png',
      ],
      'CC-BY-NC-ND-4.0' => [
        'url' => 'https://spdx.org/licenses/CC-BY-NC-ND-4.0',
        'img' => '/modules/metsis/metsis_search/icons/CCBYNCND.png',
      ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->loggerFactory = $container->get('logger.factory');
        $instance->configFactory = $container->get('config.factory');
        $instance->solariumQueryHelper = $container->get('solarium.query_helper');
        $instance->json = $container->get('serialization.json');
        $instance->geoPhpWrapper = $container->get('geofield.geophp');
        return $instance;
    }

    /**
     * Getlandingpage.
     *
     * @return string
     *   Return Hello string.
     */
    public function getLandingPage($id)
    {
        //Determine the id_prefix given the host
        $host = \Drupal::request()->getHost();


        $fullhost = \Drupal::request()->getSchemeAndHttpHost();
        //dpm($host);
        if ($host === 'adc.met.no') {
            $id_prefix = 'no-met-adc-';
        }
        if ($host === 'data.met.no') {
            $id_prefix = 'no-met-data-';
        }
        $id_prefix = 'no-met-adc-';
        /** @var Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        $connector = $backend->getSolrConnector();

        $solarium_query = $connector->getSelectQuery();
        $solarium_query->setQuery('id:'.$id_prefix.$id);
        //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
        $solarium_query->setRows(1);
        //$fields[] = 'id';
        //$fields[] = 'mmd_xml_file';
        //$solarium_query->setFields($fields);

        $result = $connector->execute($solarium_query);
        // The total number of documents found by Solr.
        $found = $result->getNumFound();
        //\Drupal::logger('found')->debug("found: " . $found);

        foreach ($result as $doc) {
            $fields = $doc->getFields();
        }
        //dpm($fields);

        if (null != \Drupal::request()->query->get('export_type')) {
            $response = new Response();
            $export_type = \Drupal::request()->query->get('export_type');
            $id = $fields['id'];
            $mmd = $fields['mmd_xml_file'];
            // By setting these 2 header options, the browser will see the URL
            // and provide a download dialog.
            $response->headers->set('Content-Type', 'text/xml');
            $response->headers->set('Content-Disposition', 'attachment; filename="'. $id.'_' .$export_type.'.xml"');

            if ($export_type === 'mmd') {
                $response->setContent(base64_decode($mmd));
            } else {
                $mmd_xml = base64_decode($mmd);
                $content = $this->transformXml($mmd_xml, $export_type);
                $response->setContent($content);
            }
            return $response;
        }
        //Add article prefix.
        $renderArray['#prefix'] ='<div class=dynamic-landing-page>';
        $renderArray['#suffix'] ='</div>';
        $renderArray = [];
        //if this is a child dataset,  give some information.
        /*
        if (($fields['isChild']) && ($fields['related_dataset'][0] !== null)) {
            $parent_id = $fields['related_dataset'][0];
            $parent = substr($parent_id, strlen($id_prefix));
            $renderArray['parent'] = [
                    '#prefix' => '<div class="w3-container">',
                    '#type' => 'markup',
                    '#markup' => '<p>This is a child dataset. See the parent <a class="w3-text-blue" href="/dataset/'.$parent.'">Landing Page</a> for more information.</p>',
                    '#suffix' => '</div>',
                    '#allowed_tags' => ['a'],
          ];
        }
*/

        //Render the title
        $renderArray['title'] = [
          '#type' => 'markup',
          '#markup' => $this->t('<h2> @title </h2>', ['@title' => $fields['title'][0]]),

        ];

        //Render the abstract. create links of urls in text.
        //$abstract = $fields['abstract'][0];
        $abstract = preg_replace('/(http[s]?:\\/\\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+[^\.,"!\) ])/', '<a class="w3-text-blue" href="$1">$1</a>', $fields['abstract'][0]);
        //if ((substr($abstract, -1)) === '.' || substr($abstract, -1) === ',') {
        //}
        $renderArray['abstract'] = [
          '#type' => 'markup',
          '#markup' => '<div class="w3-container"><p> <em>'.$abstract.'</em></p></div>',
          '#allowed_tags' => ['a','em','div'],
        ];

        /**
         *  Render map with dataset location
         */
        //Check if we got point or polygon
        $isPoint = false;
        if (($fields['bbox__minX'] === $fields['bbox__maxX']) && ($fields['bbox__minY'] === $fields['bbox__maxY'])) {
            $features = [[
              'type' => 'point',
              'lat' => $fields['bbox__minY'],
              'lon' => $fields['bbox__minX'],
            ]];
            $isPoint = true;
        //$settings['zoom'] = 10;
        } else {
            $features = [
          /*
        [
          'type' => 'json',
          'json' => $geo_json_decoded,

        ],*/
        [
          'type' => 'polygon',
          'points' => [
            ['lon' => $fields['bbox__minX'],'lat' => $fields['bbox__minY']],
            ['lon' => $fields['bbox__minX'],'lat' => $fields['bbox__maxY']],
            ['lon' => $fields['bbox__maxX'],'lat' => $fields['bbox__maxY']],
            ['lon' => $fields['bbox__maxX'],'lat' => $fields['bbox__minY']],
            ['lon' => $fields['bbox__minX'],'lat' => $fields['bbox__minY']],

        ],
      ],
      ];
        }
        /*
        $features = [[
          'type' => 'json',
          'json' => Json::decode($geo_json),
          ]];
          */
        // set map type (default leaflet OSM)
        $settings['leaflet_map'] = 'OSM Mapnik';
        /*
        $settings['zoom'] = 3;
        $settings['map_position']['force'] = false;

        */
        //$settings['map_position']['center']['lat'] = $features['lat'];
        //$settings['map_position']['center']['lon'] = $features['lon'];

        // set $map array with leafletMapGetInfo
        $map = \Drupal::service('leaflet.service')->leafletMapGetInfo($settings['leaflet_map']);

        //Set manual zoom for points
        if ($isPoint) {
            $map['settings']['zoom'] = 7;
            //$map['settings']['map_position_force'] = true;
        }
        //dpm($map);
        //dpm($features);
        //$map['settings']['zoom'] = 1;
        // render the map
        $map_result = \Drupal::service('leaflet.service')->leafletRenderMap($map, $features, $height = '400px');
        //Add the rendered map to the renderArray
        $renderArray['map'] = $map_result;

        //Get the extent form for displaying temporal and geographial extent in tabs
        $renderArray['extentGroup'] = \Drupal::formBuilder()->getForm('Drupal\metsis_lib\Form\ExtentForm', $fields, $features, $isPoint);


        /**
        * Dataset Citation
        */
        $renderArray['citation_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Dataset Citation'),

         ];

        if (isset($fields['dataset_citation_title'])) {
            $renderArray['citation_wrapper']['title'] = [
            '#type' => 'item',
            '#title' => $this->t('Title:'),
            '#markup' => $fields['dataset_citation_title'][0],
            '#allowed_tags' => ['a', 'strong'],
          ];
        }

        if (isset($fields['dataset_citation_author'])) {
            $renderArray['citation_wrapper']['author'] = [
            '#type' => 'item',
            '#title' => $this->t('Author:'),
            '#markup' => $fields['dataset_citation_author'][0],
            '#allowed_tags' => ['a', 'strong'],
          ];
        }

        if (isset($fields['dataset_citation_publisher'])) {
            $renderArray['citation_wrapper']['publisher'] = [
           '#type' => 'item',
           '#title' => $this->t('Publisher:'),
           '#markup' => $fields['dataset_citation_publisher'][0],
           '#allowed_tags' => ['a', 'strong'],
         ];
        }

        if (isset($fields['dataset_citation_doi'])) {
            $renderArray['citation_wrapper']['doi'] = [
            '#type' => 'item',
            //'#title' => $this->t('DOI:'),
            '#markup' => '<i class="ai ai-doi"></i> <a class="w3-text-blue" href="'.$fields['dataset_citation_doi'][0].'">' .$fields['dataset_citation_doi'][0].'</a>',
            '#allowed_tags' => ['a', 'strong','i'],
          ];
        }
        //dpm(sizeof($renderArray['citation_wrapper']));
        if (sizeof($renderArray['citation_wrapper']) <= 2) {
            $renderArray['citation_wrapper'] = null;
        }
        //$render

        $renderArray['constraints_and_info'] = [
          '#prefix' => '<div class="w3-cell-row">',
          '#suffix' => '</div>',
        ];

        if ((!null == $fields['access_constraint']) && (!null == $fields['use_constraint_identifier'])) {
            $renderArray['constraints_and_info']['constraints'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Use and Access Constraints'),
          '#attributes' => [
            //'class' => ['w3-cell'],
          ],
          '#prefix' => '<div class="w3-container w3-cell">',
          '#suffix' => '</div>',
        ];

            if (isset($fields['access_constraint'])) {
                $renderArray['constraints_and_info']['constraints']['access'] = [
          '#type' => 'item',
          '#title' => $this->t('Access Constraint:'),
          '#markup' => '<a class="w3-text-blue" href="https://vocab.met.no/mmd/Access_Constraint/'.$fields['access_constraint'].'">' .$fields['access_constraint'].'</a>',
          '#allowed_tags' => ['a', 'strong'],

        ];
            }
            if (isset($fields['use_constraint_identifier'])) {
                if (null != self::LICENCES[$fields['use_constraint_identifier']]) {
                    /*            $renderArray['constraints_and_info']['constraints']['licence_identifier'] = [
                      '#type' => 'item',
                      '#title' => $this->t('Licence:'),
                      '#markup' => '<a class="w3-text-blue" href="'.self::LICENCES[$fields['use_constraint_identifier']]['url'].'">' .$fields['use_constraint_identifier'].'</a>',
                      '#allowed_tags' => ['a', 'strong'],
                    ];
*/
                    $renderArray['constraints_and_info']['constraints']['licence_img'] = [
          '#type' => 'markup',
          //'#prefix' => '<p>',
          '#markup' => '<a rel="license" class="w3-text-blue" href="'.self::LICENCES[$fields['use_constraint_identifier']]['url'].'"><img class="w3-image" loading="lazy" width="100" height="35" src="'.self::LICENCES[$fields['use_constraint_identifier']]['img'].'"/></a>',
          //'#suffix' => '</p>',
          '#allowed_tags' => ['img'],
        ];
                }
            }
        }

        $renderArray['constraints_and_info']['metadata_information'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Metadata Information'),
          '#attributes' => [
            //'class' => ['w3-cell'],
          ],
          '#prefix' => '<div class="w3-container w3-cell">',
          '#suffix' => '</div>',
        ];
        $renderArray['constraints_and_info']['metadata_information']['identifier'] = [
            '#type' => 'item',
            '#title' => $this->t('Metadata Identifier:'),
            '#markup' => '<a class="w3-text-blue" href="https://'.$host.'/dataset/'.explode(':', $fields['metadata_identifier'])[1].'">' .$fields['metadata_identifier'].'</a>',
            '#allowed_tags' => ['a', 'strong'],

          ];
        /*
                $renderArray['constraints_and_info']['metadata_information']['status'] = [
                      '#type' => 'item',
                      '#title' => $this->t('Metadata Status:'),
                      '#markup' => $fields['metadata_status'],
                      '#allowed_tags' => ['a', 'strong'],

                    ];
        */
        $renderArray['constraints_and_info']['metadata_information']['metadata_update'] = [
              '#type' => 'item',
              '#title' => $this->t('Last Metadata Update:'),
              '#markup' => end($fields['last_metadata_update_datetime'])  ,
              '#allowed_tags' => ['a', 'strong'],

            ];

        $renderArray['constraints_and_info']['metadata_information']['metadata_download'] = [
              '#type' => 'item',
              '#title' => $this->t('Download Machine Readable Metadata:'),
              '#markup' => '<br><a class="w3-button w3-border" href="https://'.$host.'/dataset/'.explode(':', $fields['metadata_identifier'])[1].'?export_type=iso">ISO-Inspire</a>
              <a class="w3-button w3-border" href="https://'.$host.'/dataset/'.explode(':', $fields['metadata_identifier'])[1].'?export_type=geonorge">ISO-Norge-Inspire</a>
              <a class="w3-button w3-border" href="https://'.$host.'/dataset/'.explode(':', $fields['metadata_identifier'])[1].'?export_type=dif">NASA DIF 9.8</a>',
              '#allowed_tags' => ['a', 'strong','br'], //add br here for line break

                ];
        //$renderArray['constraints_and_info']['metadata_information']['metadata_download_actions'] = \Drupal::formBuilder()->getForm('Drupal\metsis_lib\Form\ExportForm', $fields);

        /**
         * DATA ACCESS
         */

        $renderArray['data_access'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Data Access'),
          '#attributes' => [
            //'class' => ['w3-cell'],
          ],
          //'#prefix' => '<div class="w3-container w3-cell">',
          //'#suffix' => '</div>',
        ];
        if (isset($fields['data_access_url_http'])) {
            foreach ($fields['data_access_url_http'] as $resource) {
                $renderArray['data_access']['http'] = [
          '#type' => 'item',
          '#title' => $this->t('HTTP:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
          ];
            }
        }
        if (isset($fields['data_access_url_opendap'])) {
            foreach ($fields['data_access_url_opendap'] as $resource) {
                $renderArray['data_access']['opendap'] = [
          '#type' => 'item',
          '#title' => $this->t('OPeNDAP:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }

        if (isset($fields['data_access_url_odata'])) {
            foreach ($fields['data_access_url_odata'] as $resource) {
                $renderArray['data_access']['odata'] = [
          '#type' => 'item',
        '#title' => $this->t('ODATA:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }


        if (isset($fields['data_access_url_ogc_wms'])) {
            foreach ($fields['data_access_url_ogc_wms'] as $resource) {
                $renderArray['data_access']['ogc_wms'] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WMS:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }

        if (isset($fields['data_access_url_ogc_wfs'])) {
            foreach ($fields['data_access_url_ogc_wfs'] as $resource) {
                $renderArray['data_access']['ogc_wfs'] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WFS:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }
        if (isset($fields['data_access_url_ogc_wcs'])) {
            foreach ($fields['data_access_url_ogc_wcs'] as $resource) {
                $renderArray['data_access']['ogc_wcs'] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WCS:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }
        if (isset($fields['data_access_url_ftp'])) {
            foreach ($fields['data_access_url_ftp'] as $resource) {
                $renderArray['data_access']['ftp'] = [
          '#type' => 'item',
          '#title' => $this->t('FTP:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }

        //Do not render fieldset if we do not have any info.
        if (sizeof($renderArray['data_access']) <= 3) {
            $renderArray['data_access'] = null;
        }

        /**
         * RELATED Information
         */

        $renderArray['related_information'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Related Information'),
          '#attributes' => [
            //'class' => ['w3-cell'],
          ],
        ];

        if (($fields['isChild']) && ($fields['related_dataset'][0] !== null)) {
            $parent_id = $fields['related_dataset'][0];
            $parent = substr($parent_id, strlen($id_prefix));
            $renderArray['related_information']['parent'] = [
                    '#prefix' => '<div class="w3-container w3-bar">',
                    '#type' => 'markup',
                    '#markup' => '<p><em>This is a child dataset. See the parent <a class="w3-text-blue" href="/dataset/'.$parent.'">Landing Page</a> for more information.</em></p>',
                    '#suffix' => '</div>',
                    '#allowed_tags' => ['a', 'em'],
          ];
        }
        $landingPage = new Url('<current>');
        $landingPage = $fullhost.$landingPage->toString();
        $renderArray['related_information']['landing_page'] = [
          '#type' => 'item',
          '#title' => $this->t('Landing Page:'),
          '#markup' => '<a class="w3-text-blue" href="'.$landingPage.'">' .$landingPage.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];

        if (isset($fields['related_url_user_guide'])) {
            foreach ($fields['related_url_user_guide'] as $resource) {
                $renderArray['related_information']['user_guide'] = [
          '#type' => 'item',
          '#title' => $this->t('Users Guide:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }

        if (isset($fields['related_url_home_page'])) {
            foreach ($fields['related_url_home_page'] as $resource) {
                $renderArray['related_information']['user_guide'] = [
          '#type' => 'item',
          '#title' => $this->t('Home Page:'),
          '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
            }
        }

        if (isset($fields['related_url_obs_facility'])) {
            foreach ($fields['related_url_obs_facility'] as $resource) {
                $renderArray['related_information']['obs_facility'] = [
                 '#type' => 'item',
                 '#title' => $this->t('Observation Facility:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];
            }
        }

        if (isset($fields['related_url_ext_metadata'])) {
            foreach ($fields['related_url_ext_metadata'] as $resource) {
                $renderArray['related_information']['ext_metadata'] = [
                 '#type' => 'item',
                 '#title' => $this->t('External Metadata:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];
            }
        }

        if (isset($fields['related_url_scientific_publication'])) {
            $i = 0;
            foreach ($fields['related_url_scientific_publication'] as $resource) {
                $renderArray['related_information']['scientific_publication'] = [
                 '#type' => 'item',
                 '#title' => $this->t('Scientific Publication:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];


                if (isset($fields['related_url_scientific_publication_desc'])) {
                    $renderArray['related_information']['scientific_publication']['desc'] = [
                '#type' => 'markup',
                //'#title' => $this->t('Scientific Publication Citation:'),
                '#markup' => '<div class="w3-panel w3-leftbar">
                  <p><i class="fa fa-quote-right w3-large"></i> <br>
                    <i class="w3-serif ">'.$fields['related_url_scientific_publication_desc'][$i].'</i></p>
                      </div> ',
                '#allowed_tags' => ['a', 'strong','div', 'i','p'],
              ];
                }
                $i++;
            }
        }

        if (isset($fields['related_url_data_paper'])) {
            foreach ($fields['related_url_data_paper'] as $resource) {
                $renderArray['related_information']['data_paper'] = [
                 '#type' => 'item',
                 '#title' => $this->t('Data Paper:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];
            }
        }

        if (isset($fields['related_url_data_management_plan'])) {
            foreach ($fields['related_url_data_management_plan'] as $resource) {
                $renderArray['related_information']['data_management_plan'] = [
                 '#type' => 'item',
                 '#title' => $this->t('Data Management Plan:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];
            }
        }

        if (isset($fields['related_url_other_documentation'])) {
            foreach ($fields['related_other_documentation'] as $resource) {
                $renderArray['related_information']['other_documentation'] = [
                 '#type' => 'item',
                 '#title' => $this->t('Other Documentation:'),
                 '#markup' => '<a class="w3-text-blue" href="'.$resource.'">' .$resource.'</a>',
                 '#allowed_tags' => ['a', 'strong'],
               ];
            }
        }

        /**
        * Datacenter
        */
        $renderArray['datacenter_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Data Center'),

         ];

        if (isset($fields['data_center_short_name'])) {
            $renderArray['datacenter_wrapper']['short'] = [
             '#type' => 'item',
             '#title' => $this->t('Short name:'),
             '#markup' => $fields['data_center_short_name'][0],
             '#allowed_tags' => ['a', 'strong'],
           ];
        }

        if (isset($fields['data_center_long_name'])) {
            $renderArray['datacenter_wrapper']['long'] = [
             '#type' => 'item',
             '#title' => $this->t('Name:'),
             '#markup' => $fields['data_center_long_name'][0],
             '#allowed_tags' => ['a', 'strong'],
           ];
        }

        if (isset($fields['data_center_url'])) {
            $renderArray['datacenter_wrapper']['url'] = [
             '#type' => 'item',
             '#title' => $this->t('URL:'),
             '#markup' => '<a class="w3-text-blue" href="'.$fields['data_center_url'][0].'">'.$fields['data_center_url'][0].'</a>',
             '#allowed_tags' => ['a', 'strong'],
           ];
        }

        /**
         * PERSONNEL
         */
        $renderArray['personnel_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Personnel'),

         ];
        $renderArray['personnel_wrapper']['personnel_tabs'] = \Drupal::formBuilder()->getForm('Drupal\metsis_lib\Form\PersonnelForm', $fields);




        /**
         * KEYWORDS
         */
        $renderArray['keywords_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Keywords'),

         ];

        $renderArray['keywords_wrapper']['keywords_tabs'] = \Drupal::formBuilder()->getForm('Drupal\metsis_lib\Form\KeywordsForm', $fields);



        /**
         * Platform and Instrument
         */
        if (isset($fields['platform_short_name']) || $fields['platform_instrument_short_name']) {
            $renderArray['aquisition_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Aquisition Information'),

         ];
            $renderArray['aquisition_wrapper']['aquisition_tabs'] = \Drupal::formBuilder()->getForm('Drupal\metsis_lib\Form\AquisitionForm', $fields);
        }


        $renderArray['metadata_update_wrapper'] = [
           '#type' => 'fieldset',
           '#title' => $this->t('Metadata Update Information'),

         ];
        $i = 0;
        foreach ($fields['last_metadata_update_datetime'] as $value) {
            $renderArray['metadata_update_wrapper']['update'][] = [
             '#type' => 'markup',
             '#prefix' => '<p>',
             '#markup' => $value. ' | '. $fields['last_metadata_update_type'][$i].' | ' .$fields['last_metadata_update_note'][$i],
             '#suffix' => '</p>'
           ];
            $i++;
        }
        //$renderArray['#group_children']['temporal'] = 'extent';
        //$renderArray['#group_children']['geographical'] = 'extent';
        //$renderArray['extent']['tabs']['temporal']['temp_tab']['#tree'] = true;
        //$renderArray['extent']['tabs']['geographical']['geo_tab']['#tree'] = true;

        //$renderArray['extent']['tabs']['temporal']['temp_tab']['#parents'] = ['extent', 'tabs', 'temporal', 'temp_tab'];
        //$renderArray['extent']['tabs']['geographical']['geo_tab']['#parents'] = ['extent', 'tabs', 'geographical', 'geo_tab'];
        //$renderArray['#fieldgroups']['extent']->children[] = 'geographical';


        //$renderArray['#attached']['library'][] = 'field_group/core';
        //$renderArray['#attached']['library'][] = 'field_group/tabs';
        //$renderArray['#attached']['library'][] = 'field_group/formatter.horizontal_tabs';
        $renderArray['#attached']['library'][] = 'metsis_lib/landing_page';
        $renderArray['#attached']['library'][] = 'metsis_lib/fa_academia';
        $renderArray['#cache']['max-age'] = 0;


        //ADD JSONLD META
        $jsonld = $this->getJsonld($fields);
        $renderArray['#attached']['html_head'][] = [
        [
          '#type' => 'html_tag',
          '#tag' => 'script',
          '#value' => Json::encode($jsonld),
          '#attributes' => ['type' => 'application/ld+json'],
        ],
        'schema_metatag',
      ];

        return $renderArray;
    }

    /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
    public function access(AccountInterface $account)
    {
        //Check the current host and give access accordenly
        $host = \Drupal::request()->getHost();
        if (($host === 'adc.met.no') || ($host === 'data-test.met.no')|| ($host === 'metsis-dev.local')|| ($host === 'metsis-staging.met.no')) {
            return AccessResult::allowed();
        } else {
            return  AccessResult::forbidden();
        }
    }

    public function transformXml($xml, $type)
    {
        //Get some config variables:
        $config = $this->configFactory->get('metsis_search.export.settings');
        //dpm($config);
        $xslt_path = $config->get('xslt_path');
        $prefix = $config->get('xslt_prefix');
        $stylepath = $xslt_path . $prefix . $type . '.xsl';
        $style = file_get_contents(DRUPAL_ROOT.$stylepath);


        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new SimpleXMLElement($style));

        //Return the transformed XML
        return $xslt->transformToXml(new SimpleXMLElement($xml));
    }

    public function getJsonld($fields)
    {
        $json = [
          '@context' => 'https://schema.org/',
          '@type' => 'Dataset',
          '@id' => $fields['related_url_landing_page'][0],
          'name' => $fields['title'][0],
          'description' => $fields['abstract'][0],
          'url' => $fields['related_url_landing_page'][0],
          'identifier' => [
            $fields['metadata_identifier'],
          ],
          'keywords' => [
            $fields['keywords_keyword']
          ],
          'license' => $fields['use_constraint_resource'],
        ];
        $string = <<<EOF
       {
        "@context":"https://schema.org/",
        "@type":"Dataset",
        "name":"{$fields['title'][0]}",
        "description":"{$fields['abstract'][0]}",
        "url":"{$fields['related_url_landing_page'][0]}",
        "identifier": ["{$fields['related_url_landing_page'][0]}",
                       "https://identifiers.org/ark:/12345/fk1234"],
        "keywords":[
          {$keywords}
        ],
        "license" : "{$fields['use_constraint_resource'][0]}",


        "creator":{
           "@type":"Organization",
           "url": "https://www.ncei.noaa.gov/",
           "name":"OC/NOAA/NESDIS/NCEI > National Centers for Environmental Information, NESDIS, NOAA, U.S. Department of Commerce",
           "contactPoint":{
              "@type":"ContactPoint",
              "contactType": "customer service",
              "telephone":"+1-828-271-4800",
              "email":"ncei.orders@noaa.gov"
           }
        },
        "funder":{
           "@type": "Organization",
           "sameAs": "https://ror.org/00tgqzw13",
           "name": "National Weather Service"
        },
        "includedInDataCatalog":{
           "@type":"DataCatalog",
           "name":"adc.met.no"
        },
        "distribution":[
           {
              "@type":"DataDownload",
              "encodingFormat":"CSV",
              "contentUrl":"http://www.ncdc.noaa.gov/stormevents/ftp.jsp"
           },
           {
              "@type":"DataDownload",
              "encodingFormat":"XML",
              "contentUrl":"http://gis.ncdc.noaa.gov/all-records/catalog/search/resource/details.page?id=gov.noaa.ncdc:C00510"
           }
        ],
        "temporalCoverage":"1950-01-01/2013-12-18",
        "spatialCoverage":{
           "@type":"Place",
           "geo":{
              "@type":"GeoShape",
              "box":"18.0 -65.0 72.0 172.0"
           }
        }
      }

EOF;
        return $json;
    }
}
