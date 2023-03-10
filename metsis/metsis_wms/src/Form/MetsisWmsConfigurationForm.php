<?php

namespace Drupal\metsis_wms\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 *
 * {@inheritdoc}
 */
class MetsisWmsConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_wms.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_wms.admin_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_wms.settings');
    // $form = array();
    // Choose view_mode for display landing page draft
    $form['wmsmap'] = [
      '#type' => 'fieldset',
      '#title' => 'Configure WMS map',
      '#tree' => TRUE,
    ];
    $form['wmsmap']['base_layer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter name of base layer'),
      '#description' => $this->t("the name of the base layer"),
      '#size' => 20,
      '#default_value' => $config->get('wms_base_layer'),
    ];

    $form['wmsmap']['overlay_border'] = [
      '#type' => 'select',
      '#title' => $this->t('Draw overlay border'),
      '#description' => $this->t("draw the overlay border or not"),
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
      '#default_value' => $config->get('wms_overlay_border'),
    ];
    $form['wmsmap']['product_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Posibility to select products?'),
      '#description' => $this->t("Posibility to select products or not"),
      '#options' => [
        'true' => $this->t('Yes'),
        'false' => $this->t('No'),
      ],
      '#default_value' => $config->get('wms_product_select'),
    ];
    /*
    $form['wmsmap']['init_proj'] = [
    '#type' => 'select',
    '#title' => t('Select map projection'),
    '#description' => t("Select map projection"),
    '#options' => [
    'EPSG:4326' => t('EPSG:4326'),
    'EPSG:32661' => t('UPS North'),
    'EPSG:32761' => t('UPS South'),
    ]
    '#default_value' => $config->get('wms_init_proj'),
    ];
     */
    /*
    $form['wmsmap']['additional_layers'] = [
    '#type' => 'select',
    '#title' => t('Use additional layers'),
    '#description' => t("Select whethever to use additional layers"),
    '#options' => [
    'true' => t('Yes'),
    'false' => t('No'),
    ],
    '#default_value' => $config->get('wms_additional_layers'),
    ];
     */
    $form['wmsmap']['zoom'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter the initial zoom value of map'),
      '#description' => $this->t("the initial zoom of the map"),
      '#size' => 20,
      '#default_value' => $config->get('wms_zoom'),
    ];
    $form['wmsmap']['location'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial map location'),
      '#description' => $this->t("Select initial map location"),
      '#options' =>
      array_combine(array_keys($config->get('wms_locations')), array_keys($config->get('wms_locations'))),

      '#default_value' => 'longyearbyen',
    ];

    // var_dump($config->get('wms_locations'));
    // $form['#attached']['library'][] = 'landing_page_creator/landing_page_creator';.
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: Implement form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get user and pass from admin configuration.
    $values = $form_state->getValues();

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /*
     * Save the configuration.
     */
    $values = $form_state->getValues();

    $this->configFactory->getEditable('metsis_wms.settings')
      ->set('wms_base_layer', $values['wmsmap']['base_layer'])
      ->set('wms_overlay_border', $values['wmsmap']['overlay_border'])
      ->set('wms_product_select', $values['wmsmap']['product_select'])
      // ->set('wms_init_proj', $values['init_proj'])
      // ->set('wms_additional_layers', $values['wmsmap']['additional_layers'])
      ->set('wms_zoom', $values['wmsmap']['zoom'])
      ->set('wms_selected_location', $values['wmsmap']['location'])
      ->save();
    parent::submitForm($form, $form_state);
  }

}
