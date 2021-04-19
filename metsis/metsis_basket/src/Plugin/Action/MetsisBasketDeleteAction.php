<?php

namespace Drupal\metsis_basket\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Action description.
 *
 * @Action(
 *   id = "metsis_basket_delete_action",
 *   label = @Translation("ADC Delete items"),
 *   type = "metsis_basket_item",
 *   confirm = TRUE,
 * )
 */
class MetsisBasketDeleteAction extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    \Drupal::logger('metsis_basket')->debug('Executing metsis basket delete action');
    $user = \Drupal::currentUser();
    \Drupal::logger('metsis_basket')->debug('Crrent user id is: ' . $user->id());
    \Drupal::logger('metsis_basket')->debug('Current item id is: ' . $entity->id());
    \Drupal::logger('metsis_basket')->debug('Current session user: ' . $entity->uid->value);


    /* TODO: SHould we add more sanity checks here?? Before delete?
     */
     if($entity->uid->value === $user->id()) {
       $entity->delete();
       $tempstore = \Drupal::service('user.private_tempstore')->get('metsis_basket');
       $ids = $tempstore->get('basket_items');
      $new_ids = null;
       for ($i = 0; $i < count($ids); $i++)  {
            if($ids[$i] === $entity->metadata_identifier->value) {
              unset($ids[$i]);
              $new_ids =  array_values($ids);
            };
        }
       $tempstore->get('basket_items', $new_ids);
       return $this->t('Basket Item Deleted');
     }
    // Don't return anything for a default completion message, otherwise return translatable markup.
    return $this->t('You are not the owner of this item');
  }

  /**
   * {@inheritdoc}
   * TODO: Make sure this access function behave as expected
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'metsis_basket_item') {
      $access = $object->access('delete', $account, TRUE)
        ->andIf($object->status->access('delete', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
  return TRUE;
  }
}
