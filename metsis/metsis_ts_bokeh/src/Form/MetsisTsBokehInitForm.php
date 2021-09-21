<?php
/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Form\MetsisTsBokehInitForm
 *
 * Form to initiate the plot
 *
 */

namespace Drupal\metsis_ts_bokeh\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Link;

/*
 * {@inheritdoc}
 * Form class for the bokeh init form
 */
class MetsisTsBokehInitForm extends FormBase {
 /*
  * Returns a unique string identifying the form.
  *
  * The returned ID should be a unique string that can be a valid PHP function
  * name, since it's used in hook implementation names such as
  * hook_form_FORM_ID_alter().
  *
  * @return string
  *   The unique string identifying the form.
  *
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'ts_bokeh_init_form';
  }

 /*
  * @param $form
  * @param $form_state
  *
  * @return mixed
  *
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /*
     * Clean up tempstore
     */
    $session = \Drupal::request()->getSession();
    $session->remove('data_uri');
    //$session->remove('yaxis');
    $session->remove('isinit');
    /*
     * Add helper variable to check for init
     */
    $session->set('isinit', true);


    /*
     * Display error message if backend url configuration is not set
     */
    $config = \Drupal::config('metsis_ts_bokeh.configuration');
    $backend_uri = $config->get('ts_bokeh_plot_service');
    if(!isset($backend_uri)) {
      $link = \Drupal\Core\Link::fromTextAndUrl('configure backend',
        \Drupal\Core\Url::fromRoute('metsis_ts_bokeh.metsis_ts_bokeh_admin_settings_form'))->toString();
      \Drupal::messenger()->addError(t("Backend not configured. Please  @link first.", array('@link' => $link)));
    }

    $form['data_uri'] = [
      '#type' => 'url',
      '#sze' => '150',
      '#title' => t("Enter dataset resource URL:"),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit'),
    ];
    return $form;
  }

  /*
   * {@inheritdoc}
   * TODO: Impletment form validation here
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }
 	/*
   * {@inheritdoc}
   * Redirect init form to plot
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session = \Drupal::request()->getSession();
    $session->set('data_uri', $form_state->getValue('data_uri'));
    $form_state->setRedirect('metsis_ts_bokeh.plot');
  }
}
