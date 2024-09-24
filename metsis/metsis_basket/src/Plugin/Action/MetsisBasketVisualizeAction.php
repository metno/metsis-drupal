<?php

namespace Drupal\metsis_basket\Plugin\Action;

use Drupal\Core\File\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Action description.
 *
 * @Action(
 *   id = "metsis_basket_visualize_action",
 *   label = @Translation("ADC Visualize items"),
 *   type = "metsis_basket_item",
 *   confirm = False,
 * )
 */
class MetsisBasketVisualizeAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    \Drupal::logger('metsis_basket')->debug('Executing metsis basket Visualize action');
    $user = \Drupal::currentUser();
    \Drupal::logger('metsis_basket')->debug('Crrent user id is: ' . $user->id());
    \Drupal::logger('metsis_basket')->debug('Current item id is: ' . $entity->id());
    \Drupal::logger('metsis_basket')->debug('Current item bundle is: ' . $entity->bundle());

    /*
     * Get the default endpoint names from default config
     */
    $default_config = \Drupal::config('metsis_basket.settings');
    $basket_endpoint = $default_config->get("constants.basket_endpoint");
    $wms_endpoint = $default_config->get("constants.wms_endpoint");
    $ts_endpoint = $default_config->get("constants.ts_endpoint");

    \Drupal::logger('metsis_basket')->debug('metadata_identifier is: ' . $entity->get('metadata_identifier')->value);

    /* Get the metadata identifiers */
    $metadata_identifiers = [];
    $metadata_identifiers[] = $entity->metadata_identifier->value;

    $solr_core = adc_get_solr_core([$metadata_identifiers[0]]);
    // $solr_core = "adc-test";.
    $options = [
      'query' => [
        'dataset' => implode(",", $metadata_identifiers),
        'solr_core' => $solr_core[$metadata_identifiers[0]],
        'calling_results_page' => $basket_endpoint,
      ],
    ];
    // var_dump($metadata_identifiers);
    if (count($metadata_identifiers) > 1) {
      \Drupal::messenger()->addWarning('Time series plotting for basket items is not fully implemented.');
    }
    if (adc_has_feature_type($metadata_identifiers[0], 'timeSeries') === 1) {
      $options['query']['metadata_identifier'] = $metadata_identifiers[0];
      // drupal_goto($ts_endpoint, $options);.
      \Drupal::messenger()->addMessage("Calling Visualization TS service for single item: " . $entity->metadata_identifier->value);
      $output = print_r($options, 1);
      \Drupal::logger('metsis_basket')->debug("TS Endpoint is: " . $ts_endpoint);
      \Drupal::logger('metsis_basket')->debug("Calling single visualization service with options: " . $output);
      $url = Url::fromRoute('metsis_timseries.tsform', $options['query']);
      // $response = new RedirectResponse('metsis_timseries.tsform', $options);.
      $response = new RedirectResponse($url);
      return $response->send();
    }
    else {
      // drupal_goto($wms_endpoint, $options);.
      \Drupal::messenger()->addMessage("Calling Visualization WMS service for single item: " . $entity->metadata_identifier->value);
      $output = print_r($options, 1);
      \Drupal::logger('metsis_basket')->debug("WMS Endpoint is: " . $wms_endpoint);
      \Drupal::logger('metsis_basket')->debug("Calling single visualization service with options: " . $output);
      $url = Url::fromRoute('metsis_qsearch.wms', $options['query']);
      // $response = new RedirectResponse('metsis_qsearch.wms', $options);.
      $response = new RedirectResponse($url);
      // Return new RedirectResponse('metsis_qsearch.wms', $options);.
      return $response->send();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {

    /*
     * Get the default endpoint names from default config
     */
    $default_config = \Drupal::config('metsis_basket.settings');
    $basket_endpoint = $default_config->get("constants.basket_endpoint");
    $wms_endpoint = $default_config->get("constants.wms_endpoint");
    $ts_endpoint = $default_config->get("constants.ts_endpoint");

    $metadata_identifiers = [];
    foreach ($objects as $entity) {
      $metadata_identifiers[] = $entity->metadata_identifier->value;

    }
    $solr_core = adc_get_solr_core([$metadata_identifiers[0]]);
    $options = [
      'query' => [
        'dataset' => implode(",", $metadata_identifiers),
        'solr_core' => $solr_core[$metadata_identifiers[0]],
        'calling_results_page' => $basket_endpoint,
      ],
    ];

    if (count($metadata_identifiers) > 1) {
      \Drupal::messenger()->addWarning('Time series plotting for basket items is not fully implemented.');
    }
    if (adc_has_feature_type($metadata_identifiers[0], 'timeSeries') === 1) {
      $options['query']['metadata_identifier'] = $metadata_identifiers[0];
      // drupal_goto($ts_endpoint, $options);.
      \Drupal::messenger()->addMessage("Calling Visualization TS service for multiple items: " . $entity->metadata_identifier->value);
      $output = print_r($options, 1);
      \Drupal::logger('metsis_basket')->debug("TS Endpoint is: " . $ts_endpoint);
      \Drupal::logger('metsis_basket')->debug("Calling multiple visualization service with options: " . $output);
      // var_dump($options);
      $url = Url::fromRoute('metsis_timseries.tsform', $options['query']);
      // $response = new RedirectResponse('metsis_timseries.tsform', $options);.
      $response = new RedirectResponse($url);
      return $response->send();
    }
    else {
      // drupal_goto($wms_endpoint, $options);.
      \Drupal::messenger()->addMessage("Calling Visualization WMS service for multiple item: " . $entity->metadata_identifier->value);
      $output = print_r($options, 1);
      \Drupal::logger('metsis_basket')->debug("WMS Endpoint is: " . $wms_endpoint);
      \Drupal::logger('metsis_basket')->debug("Calling multiple visualization service with options: " . $output);
      $url = Url::fromRoute('metsis_qsearch.wms', $options['query']);
      // $response = new RedirectResponse('metsis_qsearch.wms', $options);.
      $response = new RedirectResponse($url);
      return $response->send();
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make sure this access function behave as expected.
   */
  public function access($object, AccountInterface $account, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'metsis_basket_item  ') {
      $access = $object->access('transform', $account, TRUE)
        ->andIf($object->status->access('transform', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
