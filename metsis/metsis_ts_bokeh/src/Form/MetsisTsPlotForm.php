<?php
/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Form\MetsisTsPlotForm
 *
 * Form to show and manipulate the Plot
 *
 */

namespace Drupal\metsis_ts_bokeh\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/*
 * Class for the TS bokeh Plot form
 *
 * {@inheritdoc}
 *
 */
class MetsisTsPlotForm extends FormBase
{
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
    public function getFormId()
    {
        return 'metsis_ts.form';
    }

    /*
     * @param $form
     * @param $form_state
     *
     * @return mixed
     *
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {

      //Get the API endpoint config.
      $config = \Drupal::config('metsis_ts_bokeh.configuration');
      $backend_uri = $config->get('ts_bokeh_plot_service');

      //Get the query parameters for this request.
      $query_from_request = \Drupal::request()->query->all();
      $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);

      $form['plotform'] = [
        '#type' => 'container',
        '#prefix' => '<div id="plot-wrapper" height="800px">',
        '#suffix' => '</div>',
        '#attributes' => [
      'class' => ['w3-container', 'w3-display-container', 'clearfix'],

          ],
      ];


if(!isset($query['url'])) {
      $form['plotform']['input'] = [
   '#type' => 'container',
  '#attributes' => [
    'class' => ['plot-input'],
    ],
  ];


        $form['plotform']['input']['data_uri'] = [
     '#type' => 'url',
     '#sze' => 100,
     '#title' => t("Enter dataset resource URL:"),
     '#label' => 'URL',
     '#label_display' => 'before',
     '#default_value' => $form_state->getValue('data_uri'),
     '#required' => true,
     '#attributes' => [
       //'class' => ['w3-animate-input'],
       ],
   ];

        $form['plotform']['input']['actions'] = [
     '#type' => 'actions',
   ];


        $form['plotform']['input']['actions']['submit'] = [
     '#type' => 'submit',
     '#value' => t('Plot'),
     '#ajax' => [
       'callback' => '::getPlotCallback',
       'wrapper' => 'plot-wrapper',
       'method' => 'replace',
     ],

   ];

   $form['plotform']['plot-container'] = [
  //   '#type' => 'markup',
  //   '#prefix' => '<div class="w3-row">',
  //   '#suffix' => '</div>',
   ];

}
else {

  $form['plotform']['plot-container'] =  [
'#type' => 'inline_template',
'#allowed_tags' => ['iframe', 'div','script'],
'#template' => '<iframe src="{{ url }}" width="100%" height="800px"  frameborder=0 scrolling=no> title="Timeseries Bokeh Plot"</iframe>',
'#context' => [
  'url' => $backend_uri . '?url=' . $query['url'] . '',
],
'#attributes' => [
  //'class' => ['w3-display-container', 'w3-display-middle']
]
];

}

        /*
         * Attach some js libraries to this form
         */
        $form['#attached']['library'][] = 'metsis_ts_bokeh/style';
        $form['#attached']['library'][] = 'media/oembed.formatter';
        //$form['#attached']['library'][] = 'media/oembed.frame';
  /*      $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_js';
        $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_widgets';
        $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_tables';
        $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_api';
        $form['#attached']['library'][] = 'jquery_ui_draggable/draggable';
*/
        return $form;
    }



    /*
     *
     * {@inheritdoc}
     *
     * TODO: Impletment form validation here
     **/
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $config = \Drupal::config('metsis_ts_bokeh.configuration');
        $backend_uri = $config->get('ts_bokeh_plot_service');
        if (!isset($backend_uri)) {
            $link = \Drupal\Core\Link::fromTextAndUrl(
                'configure backend',
                \Drupal\Core\Url::fromRoute('metsis_ts_bokeh.metsis_ts_bokeh_admin_settings_form')
            )->toString();
            \Drupal::messenger()->addError(t("Backend not configured. Please  @link first.", array('@link' => $link)));
        }
        return parent::validateForm($form, $form_state);
    }
    /*
     * {@inheritdoc}
     **/
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
       //$form_state->setValue('data_uri', '');
       //$form_state->disableCache();
       $form_state->setRebuild();
    }

    /*
     * Ajax callback function
     */
    public function getPlotCallback(array $form, FormStateInterface $form_state)
    {
        $config = \Drupal::config('metsis_ts_bokeh.configuration');
        $backend_uri = $config->get('ts_bokeh_plot_service');

        //Get the opendapUrl
        $opendap_url = $form_state->getValue('data_uri');
        //$form_state->setValue('data_uri', '');
        $form['plotform']['input']['data_uri']['#value'] = '';
        $form['plotform']['plot-container'] =  [
    '#type' => 'inline_template',
    '#allowed_tags' => ['iframe', 'div','script'],
    '#template' => '<iframe class="w3-card media-oembed-content bokehplot" src="{{ url }}" width="100%" height="720px" frameborder=0 scrolling=no> title="Timeseries Bokeh Plot"</iframe>',
    '#context' => [
        'url' => $backend_uri . '?url=' . $opendap_url . '',
      ],
      '#attributes' => [
        'class' => ['w3-display-container', 'w3-display-middle']
      ]
    ];
        return $form['plotform'];
    }
}