<?php

namespace Drupal\metsis_ts_bokeh\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigurationForm.
 *
 * {@inheritdoc}
 */
class MetsisTsBokehConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'metsis_ts_bokeh.configuration',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_ts_bokeh.metsis_ts_bokeh_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_ts_bokeh.configuration');
    $form['ts_bokeh_plot_service'] = [
      '#type' => 'url',
      '#size' => 80,
      '#title' => $this->t('Timeseries Bokeh Backend Service URL'),
      '#description' => $this->t('Enter the URL for the backend service for use with METSIS TS Bokeh'),
      '#default_value' => $config->get('ts_bokeh_plot_service'),
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
    $value = $form_state->getValue('ts_bokeh_plot_service');

    if (!UrlHelper::isValid($value, TRUE)) {
      $form_state->setErrorByName('ts_bokeh_plot_service', t('The plot service url is not valid.', ['%ts_bokeh_plot_service' => $value]));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->configFactory->getEditable('metsis_ts_bokeh.configuration')
      ->set('ts_bokeh_plot_service', $form_state->getValue('ts_bokeh_plot_service'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
