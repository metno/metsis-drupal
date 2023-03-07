<?php

namespace Drupal\metsis_basket\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 *
 *  {@inheritdoc}
 */
class MetsisBasketConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_basket.configuration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_basket.metsis_basket_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_basket.configuration');
    $form['metsis_basket_server'] = [
      '#type' => 'textfield',
      '#size' => 80,
      '#title' => $this->t('Metsis Basket Server URL'),
      '#description' => $this->t('Enter the URL for the Basket Server'),
      '#default_value' => $config->get('metsis_basket_server'),
      '#required' => TRUE,
    ];
    $form['metsis_basket_server_port'] = [
      '#type' => 'number',
      '#size' => 10,
      '#title' => $this->t('Metsis Basket Server Port'),
      '#description' => $this->t('Enter the Basket Server Port'),
      '#default_value' => $config->get('metsis_basket_server_port'),
      '#required' => TRUE,
    ];
    $form['metsis_basket_server_service'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#title' => $this->t('Metsis Basket Server Service'),
      '#description' => $this->t('Enter the Basket Server Service'),
      '#default_value' => $config->get('metsis_basket_server_service'),
      '#required' => TRUE,
    ];
    $form['metsis_basket_endpoint'] = [
      '#type' => 'textfield',
      '#size' => 20,
      '#title' => $this->t('Metsis Basket Endpoint'),
      '#description' => $this->t('Enter the Basket Endpoint'),
      '#default_value' => $config->get('metsis_basket_endpoint'),
      '#required' => FALSE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Add validation to the rest of the form elements.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('metsis_basket.configuration')
      ->set('metsis_basket_server', $form_state->getValue('metsis_basket_server'))
      ->save();
    $this->config('metsis_basket.configuration')
      ->set('metsis_basket_server_port', $form_state->getValue('metsis_basket_server_port'))
      ->save();
    $this->config('metsis_basket.configuration')
      ->set('metsis_basket_server_service', $form_state->getValue('metsis_basket_server_service'))
      ->save();
    $this->config('metsis_basket.configuration')
      ->set('metsis_basket_endpoint', $form_state->getValue('metsis_basket_endpoint'))
      ->save();
  }

}
