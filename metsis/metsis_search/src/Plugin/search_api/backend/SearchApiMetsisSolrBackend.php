<?php

namespace Drupal\metsis_search\Plugin\search_api\backend;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\metsis_search\MetsisSearchState;
use Drupal\metsis_search\Solarium\MetsisQueryHelper;
use Drupal\search_api\Item\Field;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\LoggerTrait;
use Drupal\search_api\Plugin\search_api\data_type\value\TextValue;
use Drupal\search_api\Query\Condition;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Utility\DataTypeHelperInterface;
use Drupal\search_api\Utility\FieldsHelperInterface;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Drupal\search_api_solr\SolrConnector\SolrConnectorPluginManager;
use Drupal\search_api_solr\Utility\Utility;
use Solarium\Core\Query\Result\ResultInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $requestStack;

  /**
   * MetsisSearchState service for holding data between events during request.
   *
   * @var array
   */
  protected $metsisState;

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
    RequestStack $requestStack,
    MetsisSearchState $metsis_state,
  ) {
    $this->metsisSearchSettings = $metsis_search_settings;
    $this->requestStack = $requestStack;
    $this->metsisState = $metsis_state;
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
      $container->get('config.factory')->get('metsis_search.settings'),
      $container->get('request_stack'),
      $container->get('metsis_search.state')
    );
  }

  /**
   * Add functionality to handle filter queries on solr_bbox field type.
   *
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

  /**
   * Add the found and total childrens count fields from the solr result.
   *
   * {@inheritdoc}
   */
  protected function extractResults(QueryInterface $query, ResultInterface $result, $languages = []) {
    // Call the parent method to get the default list of fields.
    $result_set = parent::extractResults($query, $result);
    // dpm($query);
    // Get some variables that we will need later on.
    $index = $query->getIndex();
    // $fields = $index->getFields(TRUE);
    // dpm($fields, __FUNCTION__ . ': fields');.
    // $site_hash = $this->getTargetedSiteHash($index);
    // We can find the item ID and the score in the special 'search_api_*'
    // properties. Mappings are provided for these properties in
    // SearchApiSolrBackend::getSolrFieldNames().
    $language_unspecific_field_names = $this->getSolrFieldNames($index);
    $id_field = $language_unspecific_field_names['search_api_id'];

    // Get the solr documents from the results.
    $docs = $result->getDocuments();
    // dpm($docs, __FUNCTION__);
    // Extract the values of custom result_set from Solarium result set.
    $updated_result_set = [];
    // Store id of parent document whos subquery returned 0 children.
    $zero_children = [];
    // Variable to store the parent info.
    $parent_info = [];
    foreach ($docs as $doc) {
      if (is_array($doc)) {
        $doc_fields = $doc;
      }
      else {
        /** @var \Solarium\QueryType\Select\Result\Document $doc */
        $doc_fields = $doc->getFields();
      }
      if (empty($doc_fields[$id_field])) {
        throw new SearchApiSolrException(sprintf('The result does not contain the essential ID field "%s".', $id_field));
      }

      // For an unknown reason we sometimes get arrays here.
      // @see https://www.drupal.org/project/search_api_solr/issues/3281703
      // @see https://www.drupal.org/project/search_api_solr/issues/3320713
      $item_id = $doc_fields[$id_field];
      if (is_array($item_id)) {
        $item_id = current($item_id);
      }
      // dpm($item_id, __FUNCTION__); .
      if (isset($doc_fields['isParent']) && $doc_fields['isParent'] == 'true') {
        if (Utility::hasIndexJustSolrDatasources($index)) {
          $datasource = '';
          if ($index->isValidDatasource('solr_document')) {
            $datasource = 'solr_document';
          }
          $id = '';
          if (isset($doc_fields['id'])) {
            $id = $doc_fields['id'];
          }
          if (isset($doc_fields['total_children']['numFound'])
            && isset($doc_fields['found_children']['numFound'])) {
            $total_children = $doc_fields['total_children']['numFound'];

            $found_children = (int) $doc_fields['found_children']['numFound'];
            if ($found_children == 0) {
              $zero_children[] = $datasource . '/' . $item_id;
            }
            // Create value.
            $num_children_value = new TextValue(
              sprintf('%d of %d', $found_children, $total_children)
            );

            // Create and add values to the num_children field.
            $num_children_field = new Field($index, 'num_children');
            $num_children_field->setDatasourceID($datasource);
            $num_children_field->setPropertyPath('num_children');
            $num_children_field->setLabel('Number of Children');
            $num_children_field->setType('string');
            $num_children_field->setValues([$num_children_value]);

            // Generate the query params for the child query.
            $query_args = $this->requestStack->getCurrentRequest()->query->all();
            $params = UrlHelper::filterQueryParameters($query_args);
            // dpm($params, __FUNCTION__);.
            $search_string = '?';
            foreach ($params as $key => $value) {
              if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                  $search_string .= '&' . $key . '[' . $key2 . ']=' . $value2;
                }
              }
              else {
                $search_string .= "&$key=$value";
              }
            }
            $search_string .= "&related_dataset_id=$id";
            if ($search_string === '?') {
              $search_string = '';
            }
            // dpm($search_string, __FUNCTION__);.
            // Create th children search string field.
            $search_string_value = new TextValue($search_string);
            $children_search_string_field = new Field($index, 'children_search_string');
            $children_search_string_field->setDatasourceID($datasource);
            $children_search_string_field->setPropertyPath('children_search_string');
            $children_search_string_field->setLabel('Children search string');
            $children_search_string_field->setType('string');
            $children_search_string_field->setValues([$search_string_value]);

            // Update the result items.
            $result_items = $result_set->getResultItems();
            if (isset($result_items[$datasource . '/' . $item_id])) {
              $result_item = $result_items[$datasource . '/' . $item_id];
              // dpm($result_item->getFields(), __FUNCTION__);.
              $result_item->setField('num_children', $num_children_field);
              $result_item->setField('children_search_string', $children_search_string_field);
              $updated_result_set[$datasource . '/' . $item_id] = $result_item;
            }
          }
        }
      }
      if (isset($doc_fields['isChild']) && $doc_fields['isChild'] == 'true') {
        if (Utility::hasIndexJustSolrDatasources($index)) {
          $datasource = '';
          if ($index->isValidDatasource('solr_document')) {
            $datasource = 'solr_document';
          }

          if (isset($doc_fields['parent']['docs'])) {
            $_parent_info = $doc_fields['parent']['docs'];
            if (is_array($_parent_info) && count($_parent_info) > 0) {
              if (empty($parent_info)) {
                $parent_info = $_parent_info[0];
                $this->metsisState->set('parent_info', $parent_info);
              }
              $parent_info = $_parent_info[0];
              $parent_info_field_value = [];
              $parent_info_field_value[] = $parent_info['id'] ?? '';
              $parent_info_field_value[] = $parent_info['metadata_identifier'] ?? '';
              $parent_info_field_value[] = $parent_info['title'][0] ?? '';
              $parent_info_field_value[] = $parent_info['related_url_landing_page'][0] ?? '';
              $parent_info_field_value[] = $parent_info['abstract'][0] ?? '';
              $parent_info_field_value[] = $parent_info['temporal_extent_start_date'][0] ?? '';
              $parent_info_field_value[] = $parent_info['temporal_extent_end_date'][0] ?? '';
              // dpm($parent_info_field_value, __FUNCTION__);.
              $parent_info_field = new Field($index, 'parent_info');
              $parent_info_field->setDatasourceID($datasource);
              $parent_info_field->setPropertyPath('parent_info');
              $parent_info_field->setLabel('Parent Info');
              $parent_info_field->setType('string');
              $parent_info_field->setValues($parent_info_field_value);

              // Add parentinfo for the item to our metsis-state.
              $this->metsisState->set($item_id, $parent_info);

              // Update the result items.
              $result_items = $result_set->getResultItems();
              if (isset($result_items[$datasource . '/' . $item_id])) {
                $result_item = $result_items[$datasource . '/' . $item_id];
                // dpm($result_item->getFields(), __FUNCTION__);.
                $result_item->setField('parent_info', $parent_info_field);
                $updated_result_set[$datasource . '/' . $item_id] = $result_item;
              }
            }
            else {
              $this->metsisState->set('parent_info', []);
            }
          }
        }
      }
    }
    // Merge the $result_set from the parent method with the
    // custom $updated_result_set array.
    $merged_results = array_merge($result_set->getResultItems(), $updated_result_set);
    // Set the merged result items on the result set object before returning it.
    // dpm($result_set->getResultItems(), __FUNCTION__);
    // Loop through each result item
    // and remove it if num_children is equal to 0.
    $remove_count = 0;
    foreach ($result_set->getResultItems() as $item_id => $result_item) {
      if (in_array($item_id, $zero_children)) {
        unset($merged_results[$item_id]);
        $remove_count++;
      }
    }
    $result_set->setResultItems($merged_results);
    $update_count = $result_set->getResultCount() - $remove_count;
    $result_set->setResultCount($update_count);
    // Speacial handeling for simple search.
    if ($query->getSearchId(FALSE) === "views_page:metsis_simple_search__results") {
      $conditions = $query->getConditionGroup()->getConditions();
      // dpm($conditions, __FUNCTION__);.
      $got_parent_filter = FALSE;
      foreach ($conditions as $condition) {
        if ($condition instanceof ConditionGroup) {
          $conds = $condition->getConditions();
          foreach ($conds as $cond) {
            // Check if the condition is a filter.
            if ($cond instanceof Condition) {
              // Get the field name and value of the filter.
              $fieldName = $cond->getField();
              if ($fieldName === 'related_dataset_id') {
                // dpm("Got parent filter", __FUNCTION__);
                // dpm($cond->getValue(), __FUNCTION__);.
                $got_parent_filter = TRUE;
              }
            }
          }
        }
        else {
          if ($condition instanceof Condition) {
            $fieldName = $condition->getField();
            if ($fieldName === 'related_dataset_id') {
              $got_parent_filter = TRUE;
            }
          }
        }
      }
      if (!$got_parent_filter) {
        // dpm("Emptying parent_info", __FUNCTION__ . "()");.
        $this->metsisState->set('parent_info', []);
      }
    }
    return $result_set;
  }

}
