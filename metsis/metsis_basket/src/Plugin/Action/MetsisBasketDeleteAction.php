<?php

namespace Drupal\metsis_basket\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Action description.
 *
 * @Action(
 *   id = "metsis_basket_delete_action",
 *   label = @Translation("ADC Delete items"),
 *   type = "metsis_basket_item",
 *   confirm = FALSE,
 *   requirements = {
 *     "_permission" = "access metsis basket",
 *     "_custom_access" = FALSE,
 *   }
 * )
 */
class MetsisBasketDeleteAction extends ViewsBulkOperationsActionBase
{
    use StringTranslationTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($entity = null)
    {
        //\Drupal::logger('metsis_basket')->debug('Executing metsis basket delete action');
        $user = \Drupal::currentUser();
        //\Drupal::logger('metsis_basket')->debug('Crrent user id is: ' . $user->id());
        //\Drupal::logger('metsis_basket')->debug('Current item id is: ' . $entity->id());
        //\Drupal::logger('metsis_basket')->debug('Current session user: ' . $entity->uid->value);


        /* TODO: SHould we add more sanity checks here?? Before delete?
         */
        if ($entity->uid->value === $user->id()) {
            $entity->delete();
            $session = \Drupal::request()->getSession();
            $ids = $session->get('basket_items');
            $new_ids = null;
            for ($i = 0; $i < count($ids); $i++) {
                if ($ids[$i] === $entity->metadata_identifier->value) {
                    unset($ids[$i]);
                    $new_ids =  array_values($ids);
                };
            }
            $session->set('basket_items', $new_ids);
            return $this->t('Basket item @item deleted.', ['@item' => $entity->metadata_identifier->value]);
        }
        // Don't return anything for a default completion message, otherwise return translatable markup.
        return $this->t('You are not the owner of this item');
    }

    /**
     * {@inheritdoc}
     * TODO: Make sure this access function behave as expected
     */
    public function access($object, AccountInterface $account = null, $return_as_object = true)
    {
        if ($object->getEntityTypeId() === 'metsis_basket_item') {
            /*          $access = $object->access('delete', $account, true)
                    ->andIf($object->status->access('delete', $account, true));
                        return $return_as_object ? $access : $access->isAllowed();
                        */
            return $object->access('delete', $account, $return_as_object);
        }

        // Other entity types may have different
        // access methods and properties.
        //dpm($object);
        return true;
    }

    /**
     * {@inheritdoc}
     */
/*
    public function buildPreConfigurationForm(array $element, array $values, FormStateInterface $form_state)
    {
        $form = [];
        dpm($element);
        dpm($values);
        dpm($form_state);

        return $form;
    }*/
}
