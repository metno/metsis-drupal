<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AquisitionForm.
 */
class AquisitionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aquasition_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fields = NULL) {
    $form['aquisition'] = [
      '#type' => 'vertical_tabs',
        // '#tree' => true,.
      '#default_tab' => 'platform',
    ];
    $form['platform'] = [
      '#type' => 'details',
      '#title' => $this->t('Platform'),
      '#weight' => '0',
      '#group' => 'aquisition',
    ];
    foreach ($fields['platform_short_name'] as $platform) {
      $i = 0;
      $form['platform'][] = [
        '#type' => 'markup',
        '#markup' => '<a alt="' . $fields['platform_long_name'][$i] . '"href="' . $fields['platform_resource'][$i] . '">' . $platform . '</a>',
        '#allowed_tags' => ['a'],
      ];
      $i++;
    }
    $form['instrument'] = [
      '#type' => 'details',
      '#title' => $this->t('Instrument'),
      '#weight' => '0',
      '#group' => 'aquisition',
    ];
    if (isset($fields['platform_instrument_short_name'])) {
      foreach ($fields['platform_instrument_short_name'] as $platform) {
        $i = 0;
        $form['instrument'][] = [
          '#type' => 'markup',
          '#markup' => '<a alt="' . $fields['platform_instrument_long_name'][$i] . '"href="' . $fields['platform_instrument_resource'][$i] . '">' . $platform . '</a>',
          '#allowed_tags' => ['a'],
        ];
        $i++;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @todo Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    /* foreach ($form_state->getValues() as $key => $value) {
    \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
     */

  }

}
