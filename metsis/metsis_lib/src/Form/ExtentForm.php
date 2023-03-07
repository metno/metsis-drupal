<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExtentForm.
 */
class ExtentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'extent_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fields = NULL, $features = NULL, $isPoint = FALSE) {
    $form['extent'] = [
      '#type' => 'horizontal_tabs',
        // '#tree' => true,.
      '#default_tab' => 'temporal',
    ];

    $form['temporal'] = [
      '#type' => 'details',
      '#title' => $this->t('Temporal Extent'),
      '#weight' => '0',
      '#group' => 'extent',
    ];

    $form['temporal']['start'] = [
      '#type' => 'item',
      '#title' => $this->t('Start Date:'),
      // '#prefix' => '<p>',.
      '#markup' => $fields['temporal_extent_start_date'][0],
      // '#suffix' => '</p>',.
    ];
    if (isset($fields['temporal_extent_end_date'])) {
      $form['temporal']['end'] = [
        '#type' => 'item',
        '#title' => $this->t('End Date:'),
      // '#prefix' => '<p>',.
        '#markup' => $fields['temporal_extent_end_date'][0],
      // '#suffix' => '</p>',.
      ];
    }
    $form['geographical'] = [
      '#type' => 'details',
      '#title' => $this->t('Geographical Extent'),
      '#weight' => '0',
      '#group' => 'extent',
    ];

    if ($isPoint) {
      $form['geographical']['lon'] = [
        '#type' => 'item',
        '#title' => $this->t('Longitude:'),
        '#markup' => $fields['geographic_extent_rectangle_east'],
      ];

      $form['geographical']['lat'] = [
        '#type' => 'item',
        '#title' => $this->t('Latitude:'),
        '#markup' => $fields['geographic_extent_rectangle_north'],
      ];
    }
    else {
      $form['geographical']['north'] = [
        '#type' => 'item',
        '#title' => $this->t('North:'),
        '#markup' => $fields['geographic_extent_rectangle_north'],
      ];

      $form['geographical']['south'] = [
        '#type' => 'item',
        '#title' => $this->t('South:'),
        '#markup' => $fields['geographic_extent_rectangle_south'],
      ];

      $form['geographical']['east'] = [
        '#type' => 'item',
        '#title' => $this->t('East:'),
        '#markup' => $fields['geographic_extent_rectangle_east'],
      ];

      $form['geographical']['west'] = [
        '#type' => 'item',
        '#title' => $this->t('West:'),
        '#markup' => $fields['geographic_extent_rectangle_west'],
      ];
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

  }

}
