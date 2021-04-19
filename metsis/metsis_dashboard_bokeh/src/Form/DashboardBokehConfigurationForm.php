<?php
/**
 *
 * @file
 *
 * Form for Metsis Dashboard Bokeh Admin
 */
namespace Drupal\metsis_dashboard_bokeh\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/**
 *  Class ConfigurationForm.
 *
 *  {@inheritdoc}
 *
 *
 */
class DashboardBokehConfigurationForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'metsis_dashboard_bokeh.configuration',
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dashboard_bokeh.admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_dashboard_bokeh.configuration');
    $form['dashboard_bokeh_service'] = [
      '#type' => 'url',
      '#size' => 80,
      '#title' => $this->t('Dashboard bokeh Endpoint URL'),
      '#description' => $this->t('Enter the endpoint for Bokeh Dashboard'),
      '#default_value' => $config->get('dashboard_bokeh_service'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
 }

  /**
   * {@inheritdoc}
   *
   * TODO: Add validation to the rest of the form elements
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('dashboard_bokeh_service');

    if (!UrlHelper::isValid($value,TRUE)) {
      $form_state->setErrorByName('dashboard_bokeh_service', t('The dashboard service url is not valid: @dashboard_bokeh_service', array('@dashboard_bokeh_service' => $value)));
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->configFactory->getEditable('metsis_dashboard_bokeh.configuration')
        ->set('dashboard_bokeh_service', $form_state->getValue('dashboard_bokeh_service'))
        ->save();
        parent::submitForm($form, $form_state);
  }
}
