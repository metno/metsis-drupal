<?php

namespace Drupal\metsis_basket\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Action description.
 *
 * @Action(
 *   id = "metsis_basket_download_action",
 *   label = @Translation("ADC Download items"),
 *   type = "metsis_basket_item",
 *   confirm = False,
 * )
 */
class MetsisBasketDownloadAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    \Drupal::logger('metsis_basket')->debug('Executing metsis basket Download action');
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

    $records = array_keys($entity);
    $uris = $this->getDownloadActionUris("metsis_basket", $records, "http");
    $this->createDownloadActionOrder($uris);

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

    $records = array_keys($objects);
    $uris = $this->getDownloadActionUris("metsis_basket", $records, "http");
    $this->createDownloadActionOrder($uris);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make sure this access function behave as expected.
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'metsis_basket_item  ') {
      $access = $object->access('transform', $account, TRUE)
        ->andIf($object->status->access('transform', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

  /**
   * Get urls for download.
   */
  public function getDownloadActionUris($table, $records, $resource) {
    $uris = [];
    $data_access_resource = "data_access_resource";
    switch ($resource) {
      case "http": $data_access_resource = $data_access_resource . "_http";
        break;
    } foreach ($records as $record) {
      $query = \Drupal::database()->select($table, 't');
      $query->condition('t.iid', $record, '=')->fields('t', [$data_access_resource]);
      $result = $query->execute();
      foreach ($result as $r) {
        $uris[] = $r->$data_access_resource;
      }
    } return $uris;
  }

  /**
   * Get order download.
   */
  public function createDownloadActionOrder($uris) {
    $user = \Drupal::currentUser();
    global $base_url;

    /*
     * Get the configured basket service from admin form
     */
    $config = \Drupal::config('metsis_basket.configuration');
    $basket_server = $config->get('metsis_basket_server');
    $basket_server_port = $config->get('metsis_basket_server_port');
    $basket_server_service = $config->get('metsis_basket_server_service');

    $req_params = [
      'userId' => $user->getAccountName(),
      'email' => $user->getEmail(),
      'site' => $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url,
      'format' => $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz",
      'uri' => implode(";", $uris),
    ];
    $receipt = adc_basket_query($basket_server, $basket_server_port, $basket_server_service, $req_params);

    \Drupal::messenger()
      ->addMessage($this
        ->t('Your download request has been queued for processing. An email with further instructions will be sent to @email',
      ['@email' => $receipt['email']]
      )
    );
  }

}
