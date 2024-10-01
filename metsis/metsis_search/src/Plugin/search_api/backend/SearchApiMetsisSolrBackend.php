<?php

namespace Drupal\metsis_search\Plugin\search_api\backend;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\metsis_search\Solarium\MetsisQueryHelper;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api\Utility\DataTypeHelperInterface;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Drupal\search_api_solr\SolrConnector\SolrConnectorPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a Solr backend with support for bbox queries.
 *
 * @SearchApiBackend(
 *   id = "search_api_solr_metsis",
 *   label = @Translation("Solr search backend with extended features needed by metsis-drupal"),
 *   description = @Translation("Metsis Drupal Solr Search API Backend.")
 * )
 */
class SearchApiMetsisSolrBackend extends SearchApiSolrBackend implements ContainerFactoryPluginInterface {
  use LoggerTrait;
  /**
   * A config object for 'metsis_search.settings'.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $metsisSearchSettings;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    ModuleHandlerInterface $module_handler,
    Config $search_api_solr_settings,
    LanguageManagerInterface $language_manager,
    SolrConnectorPluginManager $solr_connector_plugin_manager,
    FieldsHelperInterface $fields_helper,
    DataTypeHelperInterface $dataTypeHelper,
    MetsisQueryHelper $query_helper,
    EntityTypeManagerInterface $entityTypeManager,
    EventDispatcherInterface $eventDispatcher,
    TimeInterface $time,
    StateInterface $state,
    MessengerInterface $messenger,
    LockBackendInterface $lock,
    ModuleExtensionList $module_extension_list,
    Config $metsis_search_settings,
  ) {
    $this->metsisSearchSettings = $metsis_search_settings;
    parent::__construct($configuration,
      $plugin_id,
      $plugin_definition,
      $module_handler,
      $search_api_solr_settings,
      $language_manager,
      $solr_connector_plugin_manager,
      $fields_helper,
      $dataTypeHelper,
      $query_helper,
      $entityTypeManager,
      $eventDispatcher,
      $time,
      $state,
      $messenger,
      $lock,
      $module_extension_list);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('config.factory')->get('search_api_solr.settings'),
      $container->get('language_manager'),
      $container->get('plugin.manager.search_api_solr.connector'),
      $container->get('search_api.fields_helper'),
      $container->get('search_api.data_type_helper'),
      $container->get('metsis_search.query_helper'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('datetime.time'),
      $container->get('state'),
      $container->get('messenger'),
      $container->get('lock'),
      $container->get('extension.list.module'),
      $container->get('config.factory')->get('metsis_search.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function createFilterQuery($field, $value, $operator, FieldInterface $index_field, array &$options) {

    // Iterate over the conditions.
    if ($index_field->getOriginalType() === 'solr_bbox') {
      if (!in_array($operator, ['contains', 'intersects', 'within'])) {
        throw new SearchApiSolrException('Unsupported operator for bbox searches');
      }
      // Do we score overlap ratio? Given in metsis search config.
      $bbox_filter_overlap = $this->metsisSearchSettings->get('bbox_overlap_sort');
      $query = $this->queryHelper->metsisBbox($field, $value, $operator, $bbox_filter_overlap);
      // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY.
    }
    else {
      // dpm($index_field['#type']);.
      // Handle the 'solr_bbox' field type.
      // Replace this with your logic to handle the 'solr_bbox' field type.
      // $query->add($field, $value, $operator);.
      $query = parent::createFilterQuery($field, $value, $operator, $index_field, $options);
    }
    return $query;

  }

}
