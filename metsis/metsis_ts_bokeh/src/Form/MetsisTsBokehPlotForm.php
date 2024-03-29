<?php

namespace Drupal\metsis_ts_bokeh\Form;

/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Form\MetsisTsBokehPlotForm
 *
 * Form to show and manipulate the Plot
 *
 */
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class for the TS bokeh Plot form.
 *
 * {@inheritdoc}
 */
class MetsisTsBokehPlotForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * {@inheritdoc}
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'ts_bokeh_plot_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /*
     * build form based on JSON object returned by pybasket
     *
     * {@inheritdoc}
     */
    $session = $this->getRequest()->getSession();
    // $data_uri = $session->get('metsis_ts_bokeh')->get('data_uri');
    // $items = $session->get('items');
    $isinit = $session->get('isinit');

    // Get the request referer for go back button.
    // $request = $this->getRequest();
    // $referer = $request->headers->get('referer');.
    /*
     * Check if we got opendap urls from http request. then overwite
     * data_uri variable
     */
    $query_from_request = $this->getRequest()->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    if (isset($query['opendap_urls'])) {
      $session->set('data_uri', $query['opendap_urls']);
    }

    /*
     * Attach some js libraries to this form
     */
    $form['#attached']['library'][] = 'metsis_ts_bokeh/style';
    $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_js';
    $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_widgets';
    $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_tables';
    $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_api';
    $form['#attached']['library'][] = 'metsis_lib/adc_buttons';
    $form['#attached']['library'][] = 'system/title';
    // $form['#attached']['library'][] = 'jquery_ui_draggable/draggable';
    /* We display the form above the plot with subit button on top */
    /*$form['actions'] = [
    '#type' => 'actions',
    ];*/
    $form['submit'] = [
      '#type' => 'button',
      '#value' => $this->t('Plot'),
      '#ajax' => [
        'callback' => '::getPlotData',
      ],
    ];

    $default_x_axis = "no default set";
    $form['x_axis'] = [
      '#type' => 'select',
      '#options' => ['time' => 'time'],
      '#default_value' => $default_x_axis,
      '#description' => $this->t('The x axis.'),
      '#empty' => $this->t('Empty x value'),
    ];

    // $default_y_axis = "no default set";
    $form['y_axis'] = [
      '#type' => 'select',
      '#options' => adc_get_ts_bokeh_plot_y_vars(),
      '#default_value' => $form_state->get('y_axis'),
      '#description' => $this->t('Th y axis'),
      '#empty' => $this->t('Empty y value'),
    ];

    $form['items'] = [
      '#type' => 'value',
      '#value' => $this->t('This is my stored value'),
    ];
    /*
     * Here we will display the plot
     */
    if ($isinit) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => '<div class="plot-container"><h3>Select variable to plot</h3></div>',
      ];
    }
    else {

      $form['message'] = [
        '#type' => 'markup',
        '#markup' => '<div class="plot-container"></div>',
      ];
    }

    $session->set('isinit', FALSE);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Impletment form validation here.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    /*
     * We use ajax on this form, so this function is empty
     */
  }

  /**
   * Ajax callback function.
   */
  public function getPlotData(array $form, FormStateInterface $form_state) {
    $this->getLogger('metsis_ts_bokeh')->debug('Ajax callback y-axis: ' . $form_state->getValue('y_axis'));
    // Get data resource url from tempstore.
    $session = $this->getRequest()->getSession();
    $data_uri = $session->get('data_uri');

    // Get plot json data.
    $items = adc_get_ts_bokeh_plot($data_uri, $form_state->getValue('y_axis'));
    // Create ajax response and add javascript.
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.plot-container',
        '<div id="tsplot"><script>Bokeh.embed.embed_item(' . $items . ')</script></div>'),
      );
    return $response;

  }

}
