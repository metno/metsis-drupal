<?php
/*
 * @file
 * Contains \Drupal\metsis_csv_bokeh\Form\MetsisCsvBokehPlotForm
 *
 * Form to download opendap as ascii or netcdf
 *
 */
namespace Drupal\metsis_csv_bokeh\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\HttpFoundation\RedirectResponse;
/*
 * Class for the CSV bokeh  form
 *
 * {@inheritdoc}
 *
 */
class MetsisCsvBokehDownloadForm extends FormBase {
/*
   *
   * Returns a unique string identifying the form.
   *  
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * {@inheritdoc}
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'csv_bokeh_download';
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

   $tempstore = \Drupal::service('tempstore.private')->get('metsis_csv_bokeh');
   $config = \Drupal::config('metsis_csv_bokeh.settings');
   $form = [];

   /**
    * TODO multiple OPeNDAP URLs can be requested as a comma separated list, but
    *      only the first is used for downloading. Issues to address: response time, HTTP request/response size limits.
    */
    // Get the request referer for go back button
  $request = \Drupal::request();
  $referer = $request->headers->get('referer');

  /**
   * Check if we got opendap urls from http request. then overwite
   * data_uri variable
   */
   $query_from_request = \Drupal::request()->query->all();
   $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

   $opendap_urls = isset($query['opendap_urls']) ? $query['opendap_urls'] : '';
   $opendap_urls = array_map('trim', explode(',', $opendap_urls));

   //TODO: Change this to save whole array when multiple urls are supported
   $tempstore->set('metsis_csv_bokeh_data_uri', $opendap_urls[0]);

   $bokeh_plot_vars = adc_get_cvs_bokeh_plot_vars($tempstore->get('metsis_csv_bokeh_data_uri'));
   if (isset($bokeh_plot_vars['y_axis'])) {
     $odv_object = $bokeh_plot_vars['y_axis'];
   }
   if (isset($bokeh_plot_vars['x_axis'])) {
     $odv_object = $bokeh_plot_vars['x_axis'];
   }

   $options = [];
   foreach ($odv_object as $odvo) {
     $options[$odvo] = [
       'standard_name' => $odvo,
     ];
   }
   $form['od_variables'] = [
     '#type' => 'container',
   ];
   $header = [
     'standard_name' => t('Variable'),
   ];
   $form['od_variables_tabular'] = [
     '#type' => 'container',
   ];
   $form['od_variables_tabular']['selected_variables'] = [
     '#type' => 'tableselect',
     '#required' => TRUE,
     '#header' => $header,
     '#options' => $options,
     '#attributes' => [
       'class' => [
         'csv-vars-table',
       ],
     ],
   ];
   $form['csv_file_format'] = array(
     '#type' => 'select',
     '#options' => array(
       'csv' => 'CSV',
       'nc' => 'netcdf'
     ),
     '#default_value' => 'csv',
     '#description' => t(''),
     '#empty' => t(''),
     );
   $form['actions'] = [
     '#type' => 'actions',
   ];
   $form['actions']['submit'] = [
     '#type' => 'submit',
     '#value' => t('Submit'),
   ];

   $results = $form_state->getValue('results');
   if (isset($results)) {
     $form['results'] = ['#value' => $form_state->getValue('results'),];
   }

   //Add go back putton
   $form['actions']['go_back'] = [
     '#type' => 'markup',
     '#markup' => '<a class="adc-button adc-sbutton" href="' .$referer .'">Go back to search</a>'
   ];

   $form['#attached']['library'][] = 'metsis_csv_bokeh/style';
   $form['#attached']['library'][] = 'metsis_lib/adc_buttons';
   $form['#attached']['library'][] = 'metsis_lib/tables';
   return $form;
 }

  /*
   *
   * {@inheritdoc}
   *
   * TODO: Impletment form validation here
   **/
  public function validateForm(array &$form, FormStateInterface $form_state) {
}
  /*
   * {@inheritdoc}
   **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
  /*
   * Download ASCII csv
   */
    $tempstore = \Drupal::service('tempstore.private')->get('metsis_csv_bokeh');
    $res = adc_get_csv_bokeh_download_query($form_state);
    $tempstore->set('metsis_csv_bokeh_download_query', $res);
    $response =  new RedirectResponse($res);
    $response->send();

    //$this->redirect($tempstore->get('metsis_csv_bokeh_download_query'));
  }



}
