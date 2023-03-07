<?php

namespace Drupal\metsis_basket\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\File\Url;

/**
 * Action description.
 *
 * @Action(
 *   id = "metsis_basket_transform_action",
 *   label = @Translation("ADC Transform items"),
 *   type = "metsis_basket_item",
 *   confirm = False,
 * )
 */
class MetsisBasketTransformAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    \Drupal::logger('metsis_basket')->debug('Executing metsis basket Transform action');
    $user = \Drupal::currentUser();
    \Drupal::logger('metsis_basket')->debug('Crrent user id is: ' . $user->id());
    \Drupal::logger('metsis_basket')->debug('Current item id is: ' . $entity->id());
    \Drupal::logger('metsis_basket')->debug('Current item bundle is: ' . $entity->bundle());

    // $item = \Drupal::entityTypeManager()->getStorage($entity->bundle())->load($entity->id());
    // $identifier = $item->get('metadata_identifier')->get(0);.
    \Drupal::logger('metsis_basket')->debug('metadata_identifier is: ' . $entity->get('metadata_identifier')->value);

    /* Get the metadata identifiers */
    $metadata_identifiers = [];
    $metadata_identifiers[] = $entity->metadata_identifier->value;
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    $options = [
      'dataset_id' => implode(",", $metadata_identifiers),
      'referer' => $referer,
    ];

    /* TODO: Get the metadata identifiers and call TRANSFOTMATION SERVICE?
     */
    $default_config = \Drupal::config('metsis_basket.settings');
    $transformation_endpoint = $default_config->get("constants.transformation_endpoint");
    $output = print_r($options, 1);
    \Drupal::logger('metsis_basket')->debug("Calling transformation service with options: " . $output);
    /*
     * Don't return anything for a default completion message,
     * otherwise return translatable markup.
     */
    $url = Url::fromRoute('metsis_fimex.fimexform', $options);
    $response = new RedirectResponse($url);
    return $response->send();

  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $objects) {
    $metadata_identifiers = [];
    foreach ($objects as $entity) {
      $metadata_identifiers[] = $entity->metadata_identifier->value;

    }
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    $options = [
      'dataset_id' => implode(",", $metadata_identifiers),
      'referer' => $referer,
    ];
    $output = print_r($options, 1);
    \Drupal::logger('metsis_basket')->debug("Calling multiple transformation service with options: " . $output);

    /*
     * TODO: Call transformation endpoint width $options
     */
    $url = Url::fromRoute('metsis_fimex.fimexform', $options);
    // Return $metadata_identifiers;.
    $response = new RedirectResponse($url);
    return $response->send();
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

}
