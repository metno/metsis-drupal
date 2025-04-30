<?php

namespace Drupal\metsis_search\Form;

/*
 * @file
 * Contains \Drupal\metsis_search/ExportMetadataForm
 *
 * Form for selecting and exporting MMD as other metadata formats.
 *
 */


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Entity\Index;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Form for exporting metadata.
 */
class ExportMetadataForm extends FormBase {
  /**
   * Config factory config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Config factory config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $metsisLibConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->config = $container->get('config.factory')->get('metsis_search.export.settings');
    $instance->metsisLibConfig = $container->get('config.factory')->get('metsis_lib.settings');
    return $instance;
  }

  /**
   * Getter method for Form ID.
   *
   * @inheritdoc
   */
  public function getFormId() {
    return 'metsis_search.export.form';
  }

  /**
   * Build the export form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $id = '') {
    $form['#prefix'] = '<div id="metsis-export-form">';
    $form['#suffix'] = '</div>';

    $form['solr-id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];
    if (!$form_state->has('mmd')) {
      $mmd = $this->getMmd($id);

      if ($mmd == NULL || $mmd == '') {
        $form['export'] = [
          '#type' => 'markup',
          '#markup' => $this->t('The export service is not yet available for this dataset.'),
          '#allowd_tags' => ['a'],
        ];
        return $form;
      }
      else {
        $form_state->set('mmd', $mmd);
      }
    }

    // } else {
    $form['export'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Export metadata'),
      '#allowd_tags' => ['a'],
    ];

    $options = $this->config->get('export_list');
    $conf_options = $this->metsisLibConfig->get('export_metadata');
    $def_export = (NULL != $form_state->getValue('list')) ? $form_state->getValue('list') : current($conf_options);
    // dpm($options);
    // dpm($conf_options);
    // dpm($def_export);
    foreach ($options as $key => $value) {
      if (!in_array($key, $conf_options)) {
        unset($options[$key]);
      }
    }
    $form['export']['list'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $def_export,
      '#ajax' => [
        'wrapper' => 'metsis-export-form',
        'callback' => '::changeExportTypeCallback',
        'disable-refocus' => TRUE,
      ],
    ];

    $form['export']['actions'] = [
      '#type' => 'actions',
    ];
    $form['export']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Export ' . $options[$def_export],
      // '#ajax' => [
      // 'wrapper' => 'metsis-export-form',
      // ],
    ];

    $form['export']['list']['#default_value'] = $def_export;
    $form['export']['list']['#description'] = $this->config->get('export_type_desc')[$def_export];

    // $form_state->disableCache();
    // $form_state->setRequestMethod('POST');
    return $form;
    // }
  }

  /**
   * AJAX callback for select list.
   */
  public function changeExportTypeCallback(array $form, FormStateInterface $form_state) {
    // $values = $form_state->getValues();
    // dpm($values);
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * On submit, show the user the names of the users they selected.
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();

    $id = $values['solr-id'];

    // Get the base64 encoded mmd from solr given the id.
    $mmd = $form_state->get('mmd');
    /*
    $mmd = $this->getMmd($id);

    if ($mmd == null || $mmd == '') {
    $response = new AjaxResponse();
    $form_state->setResponse($response);

    //return $response;
    } else {*/
    $export_type = $values['list'];
    // dpm($export_type);
    // Return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // and provide a download dialog.
    $response->headers->set('Content-Type', 'text/xml');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '_' . $export_type . '.xml"');

    if ($export_type === 'mmd') {
      $response->setContent(base64_decode($mmd));
    }
    else {
      $mmd_xml = base64_decode($mmd);
      $content = $this->transformXml($mmd_xml, $export_type);
      $response->setContent($content);
    }

    // Return the response in the form.
    $form_state->setResponse($response);
    // dpm($response);
    // return $response;
    // }.
  }

  /**
   * Use solrium to fetch the mmd_xml_file field given the id.
   */
  public function getMmd($id) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('id:' . $id);
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    $solarium_query->setRows(1);
    $fields[] = 'id';
    $fields[] = 'mmd_xml_file';
    $solarium_query->setFields($fields);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    // \Drupal::logger('export_doc')->debug("found: " . $found);.
    $mmd = NULL;
    foreach ($result as $doc) {
      $fields = $doc->getFields();
      // \Drupal::logger('export_doc')->debug($doc);
      if (isset($fields['mmd_xml_file'])) {
        $mmd = $fields['mmd_xml_file'];
      }
      else {
        $mmd = '';
      }
    }
    return $mmd;
  }

  /**
   * Translate the  xml using the given export_type.
   */
  public function transformXml($xml, $type) {
    // Get some config variables:
    $xslt_path = $this->config->get('xslt_path');
    $prefix = $this->config->get('xslt_prefix');
    $stylepath = $xslt_path . $prefix . $type . '.xsl';
    $style = file_get_contents(DRUPAL_ROOT . $stylepath);

    // Define the base directory for resolving relative paths.
    define('BASE_DIR', DRUPAL_ROOT . '/libraries/mmd/');

    // Define custom entityloader function for making the relative thesauri load.
    $entityLoader = function ($public, $system, $context) {
      // Resolve the path if it's relative.
      if (str_contains($system, 'thesauri/mmd-vocabulary.xml')) {
        $this->getLogger('export_xml')->notice("thesauri-dir is %system",
          ['%system' => $system]);
        $system = realpath(BASE_DIR . 'thesauri/mmd-vocabulary.xml');
        $this->getLogger('export_xml')->notice("thesauri-dir rewritten to: %system",
             ['%system' => $system]);

      }
      return $system;
    };
    // Set the custom entity loader for libxml.
    libxml_set_external_entity_loader($entityLoader);

    // Load the XSLT stylesheet.
    $xslDoc = new \DOMDocument();
    $xslDoc->loadXML($style);

    // Initialize the XSLTProcessor.
    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet($xslDoc);

    // Load the XML document.
    $xmlDoc = new \DOMDocument();
    $xmlDoc->loadXML($xml);

    // Return the transformed XML.
    return $xslt->transformToXml($xmlDoc);
  }

}
