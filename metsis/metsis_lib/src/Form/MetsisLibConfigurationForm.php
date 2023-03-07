<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 *
 * {@inheritdoc}
 */
class MetsisLibConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_lib.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_lib.admin_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_lib.settings');

    $form['enable_landing_pages'] = [
      '#type' => 'checkbox',
      '#title' => 'Enable dynamic landing pages',
      '#default_value' => $config->get('enable_landing_pages'),
    ];
    $form['landing_pages_prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter id prefix for the landing pages that should be rendered'),
      '#description' => $this->t("example: no-met-adc or no-met"),
      '#size' => 15,
      '#default_value' => $config->get('landing_pages_prefix'),
      '#states' => [
        'visible' => [
          ':input[name="enable_landing_pages"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enable_landing_pages"]' => ['checked' => TRUE],
        ],
      ],
    ];

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
     * Save the configuration
     */

    $this->configFactory->getEditable('metsis_lib.settings')
      ->set('enable_landing_pages', $form_state->getValue('enable_landing_pages'))
      ->set('landing_pages_prefix', $form_state->getValue('landing_pages_prefix'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
