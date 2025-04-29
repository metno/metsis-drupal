<?php

namespace Drupal\metsis_lib\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\search_api\Entity\Index;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DynamicLandingPagesController. Create dynamic landing pages.
 */
class DynamicLandingPagesController extends ControllerBase {


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
   * The CacheBackend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Cache-Invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheInvalidator;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The geoPhpWrapper service.
   *
   * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
   */
  protected $geoPhpWrapper;


  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The leaflet map service.
   *
   * @var \Drupal\leaflet\LeafletService
   */
  protected $leaflet;

  /**
   * License constant.
   */
  public const LICENCES = [
    'CC0-1.0' => [
      'url' => 'https://spdx.org/licenses/CC0-1.0',
      'img' => '/modules/metsis/metsis_search/icons/CC0.webp',
    ],
    'CC-BY-3.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-3.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBY.webp',
    ],
    'CC-BY-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBY.webp',
    ],
    'CC-BY/NLOD' => [
      'url' => 'https://spdx.org/licenses/CC-BY-4.0',
      'img' => '"/modules/metsis/metsis_search/icons/CCBY.webp',
    ],
    'CC BY/NLOD' => [
      'url' => 'https://spdx.org/licenses/CC-BY-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBY.webp',
    ],
    'CC-BY-SA-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-SA-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYSA.webp',
    ],
    'CC-BY-NC-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-NC-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYNC.webp',
    ],
    'CC-BY-NC' => [
      'url' => 'https://spdx.org/licenses/CC-BY-NC-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYNC.webp',
    ],
    'CC-BY-NC-SA-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-NC-SA-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYNCSA.webp',
    ],
    'CC-BY-ND-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-ND-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYND.webp',
    ],
    'CC-BY-NC-ND-4.0' => [
      'url' => 'https://spdx.org/licenses/CC-BY-NC-ND-4.0',
      'img' => '/modules/metsis/metsis_search/icons/CCBYNCND.webp',
    ],
    'Not provided' => NULL,
  ];

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->loggerFactory = $container->get('logger.factory');
    $instance->configFactory = $container->get('config.factory');
    $instance->solariumQueryHelper = $container->get('solarium.query_helper');
    $instance->json = $container->get('serialization.json');
    $instance->geoPhpWrapper = $container->get('geofield.geophp');
    $instance->request = $container->get('request_stack')->getCurrentRequest();
    $instance->leaflet = $container->get('leaflet.service');
    $instance->cache = $container->get('cache.dynamic_landingpages');
    $instance->renderer = $container->get('renderer');
    $instance->cacheInvalidator = $container->get('cache_tags.invalidator');
    return $instance;
  }

  /**
   * Getlandingpage.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return Http response.
   */
  public function getLandingPage(string $id, Request $request) {
    // Get the host of this drupal instance.
    $host = $this->request->getHost();
    $fullhost = $this->request->getSchemeAndHttpHost();
    // dpm($this->request);
    // $host = \Drupal::request()->getHost();
    // $fullhost = \Drupal::request()->getSchemeAndHttpHost();
    // Get the configured prefix for the landingpage lookup.
    $main_config = $this->configFactory->get('metsis_lib.settings');
    $id_prefix = $main_config->get('landing_pages_prefix');
    $export_list = $main_config->get('export_metadata');
    // \Drupal::logger('metsis_lib')->debug(implode(', ', $export_list));
    /* Handle caching. */
    $dataset_id = $id_prefix . '-' . $id;
    // The cache id (cid).
    $cid = 'dataset:' . $dataset_id;

    // Check if the content is already cached.
    if ($cache = $this->cache->get($cid)) {
      $renderArray = $cache->data;
      $last_modified = $renderArray['timestamp']['#value'];
      $this->getLogger('dynamic_landing_page')->notice("Cache HIT: " . $cid);
    }
    else {
      // Fetch the solr document and generate the render array.
      $result = $this->generateLandingPage($main_config, $host, $fullhost, $id, $id_prefix, $dataset_id, $export_list);
      // Check if the data has changed.
      $renderArray = $result['renderArray'];
      $last_modified = $renderArray['timestamp']['#value'];

      $this->getLogger('dynamic_landing_page')->notice("Cache MISS: " . $cid);
      // Cache the rendered HTML.
      $this->cache->set($cid, $renderArray, CacheBackendInterface::CACHE_PERMANENT, [$cid]);
    }
    // Handle metadata export.
    if (NULL != $request->query->get('export_type')) {
      $response = new Response();
      $export_type = $request->query->get('export_type');
      $fields = $this->fetchXml($id, $id_prefix);
      $id = $fields['id'];
      $mmd = $fields['mmd_xml_file'];
      // By setting these 2 header options, the browser will see the URL
      // and provide a download dialog.
      $response->headers->set('Content-Type', 'text/xml');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '_' . $export_type . '.xml"');

      if ($export_type === 'mmd') {
        $response->setContent(base64_decode($mmd));
      }
      else {
        $mmd_xml = base64_decode($mmd);
        $content = $this->transformXml($mmd_xml, $export_type);
        $response->setContent($content);
      }
      return $response;
    }

    // Check if the metadata has changed.
    $current_data = $this->fetchDocument($id, $id_prefix);
    $current_last_modified = strtotime($current_data['timestamp']);

    if ($current_last_modified > $last_modified) {
      $this->getLogger('dynamic_landing_page')->notice("Landingpage have been updated. Updating cache for: " . $cid);
      // Update the cache if the data has changed.
      $current_result = $this->generateLandingPage($main_config, $host, $fullhost, $id, $id_prefix, $dataset_id, $export_list);
      // Check if the data has changed.
      $current_last_modified = $current_result['timestamp'];
      $renderArray = $current_result['renderArray'];
      $this->cacheInvalidator->invalidateTags([$cid]);

      $this->cache->set($cid, $renderArray, CacheBackendInterface::CACHE_PERMANENT, [$cid]);
    }

    // Handle caching.
    $this->renderer->addCacheableDependency($renderArray, $main_config);
    return $renderArray;

  }

  /**
   * Fetch solrDocument to get fields.
   */
  protected function fetchDocument($id, $id_prefix) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');
    $fields = ['id', 'metadata_identifier', 'timestamp'];
    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    if ($id_prefix === 'no-met-nbs') {
      $solarium_query->setQuery('id:' . $id);
    }
    else {
      $solarium_query->setQuery('id:' . $id_prefix . '-' . $id);
    }
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);.
    $solarium_query->setRows(1);
    // $fields[] = 'id';
    // $fields[] = 'mmd_xml_file';
    $solarium_query->setFields($fields);
    $result = $connector->execute($solarium_query);
    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    /* Throw not found exception to make drupal create 404 page when not in index */
    if ($found === 0) {
      throw new NotFoundHttpException();
    }

    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }
    if ($id_prefix === 'no-met-nbs') {
      $mid = $fields['metadata_identifier'];
      $fields['metadata_identifier'] = $id_prefix . ':' . $mid;
    }
    return $fields;

  }

  /**
   * Fetch solrDocument XML for metadata export.
   */
  protected function fetchXml($id, $id_prefix) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');
    $fields = ['id', 'mmd_xml_file', 'timestamp', 'metadata_identifier'];
    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    if ($id_prefix === 'no-met-nbs') {
      $solarium_query->setQuery('id:' . $id);
    }
    else {
      $solarium_query->setQuery('id:' . $id_prefix . '-' . $id);
    }
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);.
    $solarium_query->setRows(1);
    // $fields[] = 'id';
    // $fields[] = 'mmd_xml_file';
    $solarium_query->setFields($fields);
    $result = $connector->execute($solarium_query);
    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    /* Throw not found exception to make drupal create 404 page when not in index */
    if ($found === 0) {
      throw new NotFoundHttpException();
    }

    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }
    if ($id_prefix === 'no-met-nbs') {
      $mid = $fields['metadata_identifier'];
      $fields['metadata_identifier'] = $id_prefix . ':' . $mid;
    }
    return $fields;

  }

  /**
   * Generate the landingpage render array from the solrDocument.
   */
  protected function generateLandingPage($main_config, $host, $fullhost, $id, $id_prefix, $dataset_id, $export_list) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    if ($id_prefix === 'no-met-nbs') {
      $solarium_query->setQuery('id:' . $id);
    }
    else {
      $solarium_query->setQuery('id:' . $id_prefix . '-' . $id);
    }
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);.
    $solarium_query->setRows(1);
    // $fields[] = 'id';
    // $fields[] = 'mmd_xml_file';
    // $solarium_query->setFields($fields);
    $result = $connector->execute($solarium_query);
    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    /* Throw not found exception to make drupal create 404 page when not in index */
    if ($found === 0) {
      throw new NotFoundHttpException();
    }

    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }
    if ($id_prefix === 'no-met-nbs') {
      $mid = $fields['metadata_identifier'];
      $fields['metadata_identifier'] = $id_prefix . ':' . $mid;
    }

    // Add article prefix.
    $renderArray = [];
    $renderArray['#prefix'] = '<div class=dynamic-landing-page>';
    $renderArray['#suffix'] = '</div>';

    $renderArray['timestamp'] = [
      '#type' => 'value',
      '#value' => strtotime($fields['timestamp']),
    ];
    // Add page title.
    $renderArray['#title'] = $fields['title'][0];
    // Render the title.
    $renderArray['title'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<h2> @title </h2>', ['@title' => $fields['title'][0]]),

    ];

    // Render the abstract. create links of urls in text.
    // $abstract = $fields['abstract'][0];.
    $abstract = preg_replace('/(http[s]?:\\/\\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+[^\.,"!\) ])/', '<a class="w3-text-blue" href="$1">$1</a>', $fields['abstract'][0]);
    // If ((substr($abstract, -1)) === '.' || substr($abstract, -1) === ',') {
    // }.
    $renderArray['abstract'] = [
      '#type' => 'markup',
      '#markup' => '<div class="w3-container"><p> <em>' . $abstract . '</em></p></div>',
      '#allowed_tags' => ['a', 'em', 'div'],
    ];

    /*
     *  Render map with dataset location
     */
    // Check if we got point or polygon.
    $isPoint = FALSE;
    if (($fields['geographic_extent_rectangle_west'] === $fields['geographic_extent_rectangle_east'])
    && ($fields['geographic_extent_rectangle_south'] === $fields['geographic_extent_rectangle_north'])) {
      $features = [[
        'type' => 'point',
        'lat' => $fields['geographic_extent_rectangle_south'],
        'lon' => $fields['geographic_extent_rectangle_west'],
      ],
      ];
      $isPoint = TRUE;
      // $settings['zoom'] = 10;.
    }
    else {
      $features = [
      /*
      [
      'type' => 'json',
      'json' => $geo_json_decoded,

      ],*/
      [
        'type' => 'polygon',
        'points' => [
      ['lon' => $fields['geographic_extent_rectangle_west'], 'lat' => $fields['geographic_extent_rectangle_south']],
      ['lon' => $fields['geographic_extent_rectangle_west'], 'lat' => $fields['geographic_extent_rectangle_north']],
      ['lon' => $fields['geographic_extent_rectangle_east'], 'lat' => $fields['geographic_extent_rectangle_north']],
      ['lon' => $fields['geographic_extent_rectangle_east'], 'lat' => $fields['geographic_extent_rectangle_south']],
      ['lon' => $fields['geographic_extent_rectangle_west'], 'lat' => $fields['geographic_extent_rectangle_south']],

        ],
      ],
      ];
    }
    /*
    $features = [[
    'type' => 'json',
    'json' => Json::decode($geo_json),
    ]];
    // set map type (default leaflet OSM)
    // $map = [
    /*
    $settings['zoom'] = 3;
    $settings['map_position']['force'] = false;

     */
    // $settings['map_position']['center']['lat'] = $features['lat'];
    // $settings['map_position']['center']['lon'] = $features['lon'];
    // Set $map array with leafletMapGetInfo.
    $map = $this->leaflet->leafletMapGetInfo();
    // $map = leaflet_leaflet_map_info();
    $map['OSM Mapnik']['settings']['leaflet_markercluster'] = [

      'control' => FALSE,

    ];
    $map['OSM Mapnik']['settings']['reset_map'] = [

      'control' => FALSE,

    ];

    // Set manual zoom for points.
    if ($isPoint) {
      $map['OSM Mapnik']['settings']['zoom'] = 7;
      // $map['settings']['map_position_force'] = true;.
    }
    // dpm($map);
    // dpm($features);
    // $map['settings']['zoom'] = 1;
    // render the map.
    $map_result = $this->leaflet->leafletRenderMap($map['OSM Mapnik'], $features, $height = '400px');
    // Add the rendered map to the renderArray.
    $renderArray['map'] = $map_result;

    // Get the form for displaying temporal and geographial extent in tabs.
    $renderArray['extentGroup'] = $this->formBuilder()->getForm('Drupal\metsis_lib\Form\ExtentForm', $fields, $features, $isPoint);

    /*
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
    if (isset($fields['dataset_citation_publication_date'])) {
      $pub_time = strtotime($fields['dataset_citation_publication_date'][0]);
      $renderArray['citation_wrapper']['publisher_date'] = [
        '#type' => 'item',
        '#title' => $this->t('Publication date:'),
        '#markup' => date('Y-m-d', $pub_time),
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
      // '#title' => $this->t('DOI:'),.
        '#markup' => '<i class="ai ai-doi"></i> <a class="w3-text-blue" href="' . $fields['dataset_citation_doi'][0] . '">' . $fields['dataset_citation_doi'][0] . '</a>',
        '#allowed_tags' => ['a', 'strong', 'i'],
      ];
    }
    if (isset($fields['dataset_citation_url'])) {
      $renderArray['citation_wrapper']['publisher_url'] = [
        '#type' => 'item',
        '#title' => $this->t('Publication Url:'),
        '#url' => Url::fromUri($fields['dataset_citation_url'][0]),
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    // dpm(sizeof($renderArray['citation_wrapper']));.
    if (count($renderArray['citation_wrapper']) <= 2) {
      $renderArray['citation_wrapper'] = NULL;
    }
    // $render.
    $renderArray['constraints_and_info'] = [
      '#prefix' => '<div class="w3-cell-row">',
      '#suffix' => '</div>',
    ];

    $access_constraint = $fields['access_constraint'] ?? NULL;
    $use_constraint = $fields['use_constraint_identifier'] ?? NULL;
    if ((!NULL == $access_constraint) || (!NULL == $use_constraint)) {
      $renderArray['constraints_and_info']['constraints'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Use and Access Constraints'),
        '#attributes' => [
      // 'class' => ['w3-cell'],.
      ],
        '#prefix' => '<div class="w3-container w3-cell">',
        '#suffix' => '</div>',
      ];

      if (isset($fields['access_constraint'])) {
        $renderArray['constraints_and_info']['constraints']['access'] = [
          '#type' => 'item',
          '#title' => $this->t('Access Constraint:'),
          '#markup' => '<a class="w3-text-blue" href="https://vocab.met.no/mmd/Access_Constraint/' . $fields['access_constraint'] . '">' . $fields['access_constraint'] . '</a>',
          '#allowed_tags' => ['a', 'strong'],

        ];
      }
      if (isset($fields['use_constraint_identifier'])) {

        if (NULL != self::LICENCES[$fields['use_constraint_identifier']]) {
          $renderArray['constraints_and_info']['constraints']['licence_img'] = [
            '#type' => 'markup',
            '#markup' => '<a rel="license" class="w3-text-blue" title="Link to license information" href="' . self::LICENCES[$fields['use_constraint_identifier']]['url'] . '"><img loading="lazy" width="100px" height="35px" alt="Use constraint icon for licence ' . $fields['use_constraint_identifier'] . '" ' . 'src = "' . self::LICENCES[$fields['use_constraint_identifier']]['img'] . '" /> </a> ',
            '#allowed_tags' => ['a', 'img'],
          ];
        }
        else {
          if (isset($fields['use_constraint_license_text'])) {
            $lic_text = $fields['use_constraint_license_text'];
            $lic_text_formatted = $this->convertUrlsToLinks($lic_text);
            $renderArray['constraints_and_info']['constraints']['licence_txt'] = [
              '#type' => 'markup',
              '#prefix' => '<span>',
              '#markup' => $lic_text_formatted,
              '#suffix' => '</p>',
              '#allowed_tags' => ['span', 'a'],
            ];
          }
        }
      }
    }
    // dpm($fields, __FUNCTION__);.
    $renderArray['constraints_and_info']['metadata_information'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Metadata Information'),
      '#attributes' => [
      // 'class' => ['w3-cell'],.
    ],
      '#prefix' => '<div class="w3-container w3-cell">',
      '#suffix' => '</div>',
    ];
    $renderArray['constraints_and_info']['metadata_information']['identifier'] = [
      '#type' => 'item',
      '#title' => $this->t('Metadata Identifier:'),
      '#markup' => '<a class="w3-text-blue" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '">' . $fields['metadata_identifier'] . '</a>',
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
    if (isset($fields['last_metadata_update_datetime'])) {
      $renderArray['constraints_and_info']['metadata_information']['metadata_update'] = [
        '#type' => 'item',
        '#title' => $this->t('Last Metadata Update:'),
        '#markup' => end($fields['last_metadata_update_datetime'])  ,
        '#allowed_tags' => ['a', 'strong'],

      ];
    }
    $exportMarkup = [
      'iso' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=iso">ISO-Inspire</a>',
      'iso2' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=iso">ISO-Inspire-2</a>',
      'geonorge' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=geonorge">ISO-Norge-Inspire</a>',
      'inspire' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=inspire">Inspire</a>',
      'wmo' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=wmo">WMO</a>',
      'dif' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=dif">NASA DIF 9.8</a>',
      'dif10' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=dif">NASA DIF 10</a>',
      'mmd' => '<a class="w3-button w3-border" href="https://' . $host . '/dataset/' . explode(':', $fields['metadata_identifier'])[1] . '?export_type=mmd">METNO MMD</a>',
    ];

    $_markup = '';
    foreach ($export_list as $key => $value) {
      $_markup .= $exportMarkup[$key];
    }
    $renderArray['constraints_and_info']['metadata_information']['metadata_download'] = [
      '#type' => 'item',
      '#title' => $this->t('Download Machine Readable Metadata:'),
      '#markup' => '<br>' . $_markup,
    // Add br here for line break.
      '#allowed_tags' => ['a', 'strong', 'br'],

    ];
    /*
     * DATA ACCESS
     */

    $renderArray['data_access'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Data Access'),
      '#attributes' => [
      // 'class' => ['w3-cell'],.
    ],
      // '#prefix' => '<div class="w3-container w3-cell">',
      // '#suffix' => '</div>',.
    ];
    if (isset($fields['data_access_url_http'])) {
      foreach ($fields['data_access_url_http'] as $index => $resource) {
        $renderArray['data_access']['http'] = [
          '#type' => 'item',
          '#title' => $this->t('HTTP:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }
    if (isset($fields['data_access_url_opendap'])) {
      foreach ($fields['data_access_url_opendap'] as $index => $resource) {
        $renderArray['data_access']['opendap' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('OPeNDAP:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '.html">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['data_access_url_odata'])) {
      foreach ($fields['data_access_url_odata'] as $index => $resource) {
        $renderArray['data_access']['odata' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('ODATA:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['data_access_url_ogc_wms'])) {
      foreach ($fields['data_access_url_ogc_wms'] as $index => $resource) {
        if (str_contains($resource, '?')) {
          $capLink = explode('?', mb_strimwidth($resource, 0, 80, '...'))[0];
        }
        else {
          $capLink = mb_strimwidth($resource, 0, 80, '...');
        }
        $renderArray['data_access']['ogc_wms' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WMS:'),
          '#markup' => '<a class="w3-text-blue" href="' . $capLink . '?service=WMS&version=1.3.0&request=GetCapabilities">' . $capLink . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['data_access_url_ogc_wfs'])) {
      foreach ($fields['data_access_url_ogc_wfs'] as $index => $resource) {
        $renderArray['data_access']['ogc_wfs' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WFS:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }
    if (isset($fields['data_access_url_ogc_wcs'])) {
      foreach ($fields['data_access_url_ogc_wcs'] as $index => $resource) {
        $renderArray['data_access']['ogc_wcs' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('OGC WCS:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }
    if (isset($fields['data_access_url_ftp'])) {
      foreach ($fields['data_access_url_ftp'] as $index => $resource) {
        $renderArray['data_access']['ftp' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('FTP:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    // Do not render fieldset if we do not have any info.
    if (count($renderArray['data_access']) <= 3) {
      $renderArray['data_access'] = NULL;
    }

    /*
     * RELATED Information
     */

    $renderArray['related_information'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Related Information'),
      '#attributes' => [
      // 'class' => ['w3-cell'],.
    ],
    ];
    if (isset($fields['isChild']) && isset($fields['related_dataset'])) {
      if (($fields['isChild']) && ($fields['related_dataset'][0] !== NULL)) {
        $parent_id = $fields['related_dataset'][0];
        $parent = substr($parent_id, strlen($id_prefix) + 1);
        $renderArray['related_information']['parent'] = [
          '#prefix' => '<div class="w3-container w3-bar">',
          '#type' => 'markup',
          '#markup' => '<p><em>This is a child dataset. See the <a class="w3-text-blue" href="/dataset/' . $parent . '">Parent dataset landing page</a> for more information.</em></p>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['a', 'em'],
        ];
      }
    }

    if (isset($fields['isParent'])) {
      if ($fields['isParent'] == TRUE) {
        $renderArray['related_information']['parent'] = [
          '#prefix' => '<div class="w3-container w3-bar">',
          '#type' => 'markup',
          '#markup' => '<p><em>This is a parent dataset. See the <a class="w3-text-blue" href="/metsis/search?related_dataset_id=' . $id_prefix . '-' . $id . '">list of children</a> for more information.</em></p>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['a', 'em'],
        ];
        $renderArray['related_information']['collection_image'] = [
          '#theme' => 'image',
          '#uri' => '/modules/metsis/metsis_search/images/collection.webp',
        // '#style_name' => 'your_image_style',
          '#alt' => 'Collection icon',
        // '#title' => $image['ImageTitle'],
          '#attributes' => [
            'class' => 'align-right',
            'width' => '96px',
            'height' => '34px',
          ],
        ];
      }
    }

    $landingPage = new Url('<current>');
    $landingPage = $fullhost . $landingPage->toString();
    $renderArray['related_information']['landing_page'] = [
      '#type' => 'item',
      '#title' => $this->t('Landing Page:'),
      '#markup' => '<a class="w3-text-blue" href="' . $landingPage . '" title="Dataset landing page">' . $landingPage . '</a>',
      '#allowed_tags' => ['a', 'strong'],
    ];

    if (isset($fields['related_url_user_guide'])) {
      foreach ($fields['related_url_user_guide'] as $index => $resource) {
        $desc_title = 'Users guide';
        if (isset($fields['related_url_user_guide_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_user_guide_desc'][$index]);
        }
        $renderArray['related_information']['user_guide' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Users Guide:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_home_page'])) {
      foreach ($fields['related_url_home_page'] as $index => $resource) {
        $desc_title = 'Home page';
        if (isset($fields['related_url_home_page_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_home_page_desc'][$index]);
        }
        $renderArray['related_information']['user_guide' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Home Page:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_obs_facility'])) {
      foreach ($fields['related_url_obs_facility'] as $index => $resource) {
        $desc_title = 'Observation facility';
        if (isset($fields['related_url_obs_facility_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_obs_facility_desc'][$index]);
        }
        $renderArray['related_information']['obs_facility' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Observation Facility:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_ext_metadata'])) {
      foreach ($fields['related_url_ext_metadata'] as $index => $resource) {
        $desc_title = 'External metadata';
        if (isset($fields['related_url_ext_metadata_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_ext_metadata_desc'][$index]);
        }

        $renderArray['related_information']['ext_metadata' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('External Metadata:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_scientific_publication'])) {
      // $i = 0;
      foreach ($fields['related_url_scientific_publication'] as $index => $resource) {
        $pub_desc = 'A scientific publication';
        if (isset($fields['related_url_scientific_publication_desc'][$index])) {
          $pub_desc = htmlspecialchars($fields['related_url_scientific_publication_desc'][$index]);
          if ($pub_desc === '' || $pub_desc === 'Not Available') {
            $pub_desc = 'A scientific publication';
          }
        }
        $renderArray['related_information']['scientific_publication' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Scientific Publication:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $pub_desc . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];

        // $i++;
      }
    }

    if (isset($fields['related_url_software'])) {
      foreach ($fields['related_url_software'] as $index => $resource) {
        $desc_title = 'External metadata';
        if (isset($fields['related_url_software_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_software_desc'][$index]);
        }

        $renderArray['related_information']['software' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Software:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_data_paper'])) {

      foreach ($fields['related_url_data_paper'] as $index => $resource) {
        $desc_title = 'Data paper';
        if (isset($fields['related_url_data_paper_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_data_paper_desc'][$index]);
        }
        $renderArray['related_information']['data_paper' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Data Paper:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_data_management_plan'])) {
      foreach ($fields['related_url_data_management_plan'] as $index => $resource) {
        $desc_title = 'Data management plan';
        if (isset($fields['related_url_data_management_plan_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_data_management_plan_desc'][$index]);
        }
        $renderArray['related_information']['data_management_plan'] = [
          '#type' => 'item',
          '#title' => $this->t('Data Management Plan:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_other_documentation'])) {
      foreach ($fields['related_url_other_documentation'] as $index => $resource) {
        $desc_title = 'Other documentation';
        if (isset($fields['related_url_other_documentation_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_other_documentation_desc'][$index]);
        }
        $renderArray['related_information']['other_documentation' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Other Documentation:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . $resource . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    if (isset($fields['related_url_data_server_landing_page'])) {
      foreach ($fields['related_url_data_server_landing_page'] as $index => $resource) {
        $desc_title = 'Data server landing page';
        if (isset($fields['related_url_data_server_landing_page_desc'][$index])) {
          $desc_title = htmlspecialchars($fields['related_url_data_server_landing_page_desc'][$index]);
        }
        $renderArray['related_information']['data_server_landing_page' . $index] = [
          '#type' => 'item',
          '#title' => $this->t('Data server landing page:'),
          '#markup' => '<a class="w3-text-blue" href="' . $resource . '" title="' . $desc_title . '">' . mb_strimwidth($resource, 0, 80, '...') . '</a>',
          '#allowed_tags' => ['a', 'strong'],
        ];
      }
    }

    /*
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
        '#markup' => '<a class="w3-text-blue" href="' . $fields['data_center_url'][0] . '">' . $fields['data_center_url'][0] . '</a>',
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    /*
     * PERSONNEL
     */
    $renderArray['personnel_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Personnel'),

    ];
    $renderArray['personnel_wrapper']['personnel_tabs'] = $this->formBuilder()->getForm('Drupal\metsis_lib\Form\PersonnelForm', $fields);

    /*
     * KEYWORDS
     */
    $renderArray['keywords_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Keywords'),
      '#allowed_tags' => ['a'],

    ];

    $renderArray['keywords_wrapper']['keywords_tabs'] = $this->formBuilder()->getForm('Drupal\metsis_lib\Form\KeywordsForm', $fields);

    /*
     * Platform and Instrument
     */
    if (isset($fields['platform_short_name']) || isset($fields['platform_instrument_short_name'])) {
      $renderArray['aquisition_wrapper'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Aquisition Information'),

      ];
      $renderArray['aquisition_wrapper']['aquisition_tabs'] = $this->formBuilder()->getForm('Drupal\metsis_lib\Form\AquisitionForm', $fields);
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
        '#markup' => $value . ' | ' . ($fields['last_metadata_update_type'][$i] ?? '') . ' | ' . ($fields['last_metadata_update_note'][$i] ?? ''),
        '#suffix' => '</p>',
      ];
      $i++;
    }

    if (isset($fields['storage_information_file_name'])) {
      $renderArray['storage_information_wrapper'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Storage Information'),

      ];
      $renderArray['storage_information_wrapper']['information'] = $this->formBuilder()->getForm('Drupal\metsis_lib\Form\StorageInformationForm', $fields);

    }
    $renderArray['#attached']['library'][] = 'metsis_lib/landing_page';
    $renderArray['#attached']['library'][] = 'metsis_lib/fa_academia';
    $renderArray['#cache'] = [
      'contexts' => ['url.path'],
      'bin' => 'dynamic_landingpages',
      'tags' => ['dataset:' . $dataset_id],
      'max-age' => Cache::PERMANENT,
    ];

    // ADD JSONLD META.
    $jsonld = $this->getJsonld($fields, $host, $id_prefix);
    $renderArray['#attached']['html_head'][] = [
    [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => json_encode($jsonld, JSON_UNESCAPED_SLASHES),
      '#attributes' => ['type' => 'application/ld+json'],
    ],
      'schema_metatag',
    ];

    return [
      'renderArray' => $renderArray,
      'timestamp' => strtotime($fields['timestamp']),
    ];

  }

  /**
   * A custom access check.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Request $request) {
    // Get the metsis general config (metsis_lib)
    $config = $this->configFactory->get('metsis_lib.settings');
    // Only enable access if dynamica landing pages are enabled
    // Check the current host and give access accordenly.
    if ($config->get('enable_landing_pages')) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * Convert mmd xml and download.
   */
  public function transformXml($xml, $type) {
    // Get some config variables:
    $config = $this->configFactory->get('metsis_search.export.settings');
    // dpm($config);
    $xslt_path = $config->get('xslt_path');
    $prefix = $config->get('xslt_prefix');
    $stylepath = $xslt_path . $prefix . $type . '.xsl';
    $style = file_get_contents(DRUPAL_ROOT . $stylepath);

    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet(new \SimpleXMLElement($style));

    // Return the transformed XML.
    return $xslt->transformToXml(new \SimpleXMLElement($xml));
  }

  /**
   * Get json-ld.
   */
  public function getJsonld($fields, $host, $id_prefix) {

    $start_date = "";
    $end_date = "";

    if (isset($fields['temporal_extent_start_date'])) {
      $start_date = $fields['temporal_extent_start_date'][0];
    }

    if (isset($fields['temporal_extent_end_date'])) {
      $end_date = $fields['temporal_extent_end_date'][0];
    }
    else {
      $end_date = '..';
    }
    $mid = $fields['metadata_identifier'];
    if ($id_prefix === 'no-met-nbs') {
      $mid = explode(':', $fields['metadata_identifier'])[1];
    }
    $keywords = [];
    if (isset($fields['keywords_gcmd'])) {
      foreach ($fields['keywords_gcmd'] as $gcmd) {
        $keywords[] = [
          '@type' => 'DefinedTerm',
          'name' => $gcmd,
          'inDefinedTermSet' => 'https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/sciencekeywords',
        ];
      }
    }
    if (isset($fields['keywords_cfstdn'])) {
      foreach ($fields['keywords_cfstdn'] as $cfstdn) {
        $keywords[] = [
          '@type' => 'DefinedTerm',
          'name' => $cfstdn,
          'inDefinedTermSet' => 'https://vocab.nerc.ac.uk/standard_name/',
          'url' => 'https://vocab.nerc.ac.uk/standard_name/' . $cfstdn,
        ];
      }
    }
    if (isset($fields['project_long_name'])) {
      $projects = [];
      foreach ($fields['project_long_name'] as $projectln) {
        $projects[] = [
          '@type' => 'MonetaryGrant',
          'name' => $projectln,
        ];
      }
    }
    if (isset($fields['personnel_investigator_name'])) {
      $creators = [];
      foreach ($fields['personnel_investigator_name'] as $idx => $creator) {
        $creators[$idx] = [
          '@type' => 'Person',
          'name' => $creator,
        ];
        if (isset($fields['personnel_investigator_email'][$idx])) {
          $email_string = $fields['personnel_investigator_email'][$idx];
          if (filter_var($email_string, FILTER_VALIDATE_EMAIL)) {
            $creators[$idx]['email'] = $email_string;
          }
        }
        if (isset($fields['personnel_investigator_organisation'][$idx])) {
          $affiliation = $fields['personnel_investigator_organisation'][$idx];
          $creators[$idx]['affiliation'] = [
            '@type' => 'Organization',
            'name' => $affiliation,
          ];
        }
      }
    }
    if (isset($fields['personnel_investigator_organisation'])) {
      $providers = [];
      foreach ($fields['personnel_investigator_organisation'] as $provider) {
        $providers[] = [
          '@type' => 'Organization',
          'name' => $provider,
        ];
      }
    }
    if (isset($fields['personnel_technical_name']) or isset($fields['personnel_metadata_author_name'])) {
      $contributors = [];
      if (isset($fields['personnel_technical_name'])) {
        foreach ($fields['personnel_technical_name'] as $idx => $contributor) {
          $contributors[$idx] = [
            '@type' => 'Person',
            'name' => $contributor,
          ];
          if (isset($fields['personnel_technical_email'][$idx])) {
            $email_string = $fields['personnel_technical_email'][$idx];
            if (filter_var($email_string, FILTER_VALIDATE_EMAIL)) {
              $contributors[$idx]['email'] = $email_string;
            }
          }
          if (isset($fields['personnel_technical_organisation'][$idx])) {
            $affiliation = $fields['personnel_technical_organisation'][$idx];
            $contributors[$idx]['affiliation'] = [
              '@type' => 'Organization',
              'name' => $affiliation,
            ];
          }
        }
      }
      if (isset($fields['personnel_metadata_author_name'])) {
        foreach ($fields['personnel_metadata_author_name'] as $idx => $contributor) {
          $contributors[$idx] = [
            '@type' => 'Person',
            'name' => $contributor,
          ];
          if (isset($fields['personnel_metadata_author_email'][$idx])) {
            $email_string = $fields['personnel_metadata_author_email'][$idx];
            if (filter_var($email_string, FILTER_VALIDATE_EMAIL)) {
              $contributors[$idx]['email'] = $email_string;
            }
          }
          if (isset($fields['personnel_metadata_author_organisation'][$idx])) {
            $affiliation = $fields['personnel_metadata_author_organisation'][$idx];
            $contributors[$idx]['affiliation'] = [
              '@type' => 'Organization',
              'name' => $affiliation,
            ];
          }
        }
      }
    }
    if (isset($fields['isChild']) && isset($fields['related_dataset'])) {
      if (($fields['isChild']) && ($fields['related_dataset'][0] !== NULL)) {
        $parent_id = $fields['related_dataset'][0];
        $parent = 'https://' . $host . '/dataset/' . substr($parent_id, strlen($id_prefix) + 1);
      }
    }
    if (isset($fields['data_access_url_http'])) {
      $datadownloads = [];
      foreach ($fields['data_access_url_http'] as $datadownload) {
        $datadownloads[] = [
          '@type' => 'DataDownload',
          'description' => 'Direct dowload',
          'contentUrl' => $datadownload,
        ];
      }
    }
    if (isset($fields['geographic_extent_rectangle_north'])) {
      $spatialcoverage = [
        '@type' => 'Place',
        'geo' => [
          '@type' => 'GeoShape',
          'box' => implode(" ", [$fields['geographic_extent_rectangle_south'],
            $fields['geographic_extent_rectangle_west'],
            $fields['geographic_extent_rectangle_north'],
            $fields['geographic_extent_rectangle_east'],
          ]),
        ],
        'additionalProperty' => [
          '@type' => 'PropertyValue',
          'propertyID' => 'http://inspire.ec.europa.eu/glossary/SpatialReferenceSystem',
          'value' => 'http://www.opengis.net/def/crs/EPSG/0/'
          . $fields['geographic_extent_rectangle_srsName'],
        ],
      ];
    }
    if (in_array("Created", $fields['last_metadata_update_type'])) {
      $i = 0;
      foreach ($fields['last_metadata_update_type'] as $mdupdatedt) {
        if ($mdupdatedt == 'Created') {
          $datecreated = $fields['last_metadata_update_datetime'][$i];
        }
        $i++;
      }
    }
    $json = [
      '@context' => ['@vocab' => 'https://schema.org/'],
      '@type' => 'Dataset',
      '@id' => $fields['metadata_identifier'],
      'identifier' => [
        '@type' => 'PropertyValue',
        'url' => $fields['related_url_landing_page'][0] ?? '',
        'value' => $mid,
      ],
      'name' => $fields['title'][0],
      'description' => $fields['abstract'][0],
      'url' => $fields['related_url_landing_page'][0] ?? '',
      'dateCreated' => $datecreated ?? '',
      'license' => $fields['use_constraint_resource'] ?? '',
      'keywords' => $keywords,
      'includedInDataCatalog' => [
        '@type' => 'DataCatalog',
        'url' => 'https://' . $host,
      ],
      'temporalCoverage' => $start_date . '/' . $end_date ,
      'spatialCoverage' => $spatialcoverage,
      'isPartOf' => $parent,
      'conditionsOfAccess' => $fields['access_constraint'] ?? '',
      'creator' => $creators ?? '',
      'contributor' => $contributors ?? '',
      'provider' => $providers ?? '',
      'publisher' => [
        '@type' => 'Organization',
        'name' => $fields['data_center_long_name'][0] ?? '',
        'url' => $fields['data_center_url'][0] ?? '',
      ],
      'funding' => $projects ?? '',
    ];
    if (isset($fields['personnel_datacenter_email'][0])) {
      $email_string = $fields['personnel_datacenter_email'][0];
      if (filter_var($email_string, FILTER_VALIDATE_EMAIL)) {
        $json['publisher']['email'] = $email_string;
      }
    }
    if (isset($fields['personnel_datacenter_name'][0])) {
      $json['publisher']['contactPoint'] = [
        '@type' => "ContactPoint",
        'name' => $fields['personnel_datacenter_name'][0],
        'email' => $fields['personnel_datacenter_email'][0],
        'contactType' => "Data center contact",
      ];
    }
    if ($host === 'adc.met.no') {
      $json['includedInDataCatalog']['name'] = "Arctic Data Centre";
    }
    return $json;
  }

  /**
   * Convert URLs in text to HTML links.
   *
   * @param string $text
   *   The text containing potential URLs.
   *
   * @return string
   *   The text with URLs converted to HTML links.
   */
  public function convertUrlsToLinks($text) {
    // Define a regular expression pattern to match URLs.
    $pattern = '/(https?:\/\/[^\s]+)/';

    // Replace URLs with HTML anchor tags.
    $replacement = '<a class="w3-text-blue" href="$1" target="_blank">$1</a>';

    // Perform the replacement.
    return preg_replace($pattern, $replacement, $text);
  }

}
