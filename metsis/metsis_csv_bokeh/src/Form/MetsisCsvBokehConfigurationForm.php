<?php

namespace Drupal\metsis_csv_bokeh\Form;

/*
 *
 * @file
 * Contains \Drupal\metsis_csv_bokeh\Form\MetsisCsvBokehConfigurationForm
 *
 * Form for Metsis TS Bokeh Admin Configuration
 *
 **/

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * Class ConfigurationForm.
 *
 * {@inheritdoc}
 */
class MetsisCsvBokehConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_csv_bokeh.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_csv_bokeh.admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_csv_bokeh.settings');
    $form['csv_bokeh_service'] = [
      '#type' => 'url',
      '#size' => 80,
      '#title' => $this->t('CSV Bokeh Backend Service URL'),
      '#description' => $this->t('Enter the URL for the backend service for use with METSIS CSV Bokeh'),
      '#default_value' => $config->get('csv_bokeh_download_service'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * NOTE: url-validation already provided by url form element type.
   * Implement custom validation here if needed.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('csv_bokeh_service');

    if (!UrlHelper::isValid($value, TRUE)) {
      $form_state->setErrorByName('metsis_csv_bokeh_service', t('The CSV service url is not valid.', ['%csv_plot_service' => $value]));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->configFactory->getEditable('metsis_csv_bokeh.settings')
      ->set('csv_bokeh_download_service', $form_state->getValue('csv_bokeh_service'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
