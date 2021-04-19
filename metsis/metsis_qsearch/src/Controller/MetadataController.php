<?php
/**
 * @file
 * Contains \Drupal\metsis_qsearch\MetadataController.
 */

namespace Drupal\metsis_qsearch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
//use Symfony\Component\HttpFoundation\Response;

/**
 * Default controller for the metsis_basket module.
 * {@inheritdoc}
 */
class MetadataController extends ControllerBase  {

  public function displayMetadata() {
    $metadata = adc_get_metadata();

    //$calling_results_page = isset($_GET['calling_results_page']) ? \Drupal\Component\Utility\Html::escape($_GET['calling_results_page']) : '';
    //$output = \Drupal::service('renderer')->render($metadata);
    //$response = drupal_render($metadata);
    //$response->setContent($output);
    /*return array(
      '#type' => 'inline_template',
      '#template' =>  '{{ metadatatable|raw }}',
      '#context' => array(
        'metadatatable' => $output,
      ),
      //'#markup' =>  $render_array,
      //'#markup' => drupal_render($metadata),
      '#suffix' => '<div> <a href="#" class="adc-button adc-back">' . $this->t('Back to results') . '</a></div>',
      '#allowed_tags' => ['div', 'a','link','br','anchor','link','p'],
      '#attached' => array(
        'library' => array('metsis_lib/tables', 'metsis_lib/adc_buttons'),
      ),
      //'#theme' => 'metadata_table',
    );*/
    $back_to_search =  Markup::create('<div> <a href="javascript:history.go(-1)" class="adc-button adc-back">' . $this->t('Back to results') . '</a></div>');

    return [
  '#type' => '#markup',
  '#markup' => render($metadata),
  //'#markup' => $output,
  '#attached' => array(
    'library' => array('metsis_lib/tables', 'metsis_lib/adc_buttons'),
  ),
  '#suffix' => $back_to_search,
  '#allowed_tags' => ['div', 'a', 'p','script', 'javascript'],
];
  }
}
