<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\search_api\Entity\Index;
use Symfony\Component\HttpFoundation\Response;

/**
 * Use Drupal\serialization\Encoder\XmlEncoder as SerializationXMLEncoder;.
 */
class MetsisExportController extends ControllerBase {

  /**
   * Controller for exporting metaid to different formats.
   */
  public function export($id) {

    /*
     * Fetch the mmd_xml_file field from solr, given dataset id from $data
     */

    /*
     * Create render array for choosing different export methods
     */

    // Hidden field.
    /*  $build['mmd'] = [
    '#type' => 'hidden',
    '#value' => $mmd,
    ];*/

    // Container.
    $build['container'] = [
      '#type' => 'container',
    ];
    $export_url = Url::fromRoute('metsis_search.export.mmd', ['id' => $id]);
    $build['container']['export-mmd'] = [
      '#title' => $this
        ->t('Export MMD'),
      '#type' => 'link',
      '#url' => $export_url,
      '#attributes' => [
        'class' => ['w3-button', 'w3-border'],
      ],
    ];

    $export_url = Url::fromRoute('metsis_search.export.dif', ['id' => $id]);
    $build['container']['export-dif'] = [
      '#title' => $this
        ->t('Export DIF'),
      '#type' => 'link',
      '#url' => $export_url,
      '#attributes' => [
        'class' => ['w3-button', 'w3-border'],
      ],
    ];

    $export_url = Url::fromRoute('metsis_search.export.iso', ['id' => $id]);
    $build['container']['export-iso'] = [
      '#title' => $this
        ->t('Export ISO'),
      '#type' => 'link',
      '#url' => $export_url,
      '#attributes' => [
        'class' => ['w3-button', 'w3-border'],
      ],
    ];

    return $build;
  }

  /**
   * Export as MMD.
   */
  public function exportMmd($id) {
    // Set the mmd.
    $mmd = $this->getMmd($id);

    // This is the "magic" part of the code.  Once the id is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/xml');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '_mmd.xml"');

    // This line physically adds the CSV id we created.
    $response->setContent(base64_decode($mmd));
    return $response;
  }

  /**
   * Export as DIF.
   */
  public function exportDif($id) {
    // Set the mmd.
    $mmd = $this->getMmd($id);

    $mmd_xml = base64_decode($mmd);
    // dpm(DRUPAL_ROOT);
    $dif_style = file_get_contents(DRUPAL_ROOT . '/libraries/mmd/xslt/mmd-to-dif.xsl');

    $dif_xml = $this->transformXml($mmd_xml, $dif_style);
    // This is the "magic" part of the code.  Once the id is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/xml');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '_dif.xml"');

    // This line physically adds the CSV id we created.
    $response->setContent($dif_xml);
    return $response;
  }

  /**
   * Export as ISO.
   */
  public function exportIso($id) {
    // Set the mmd.
    $mmd = $this->getMmd($id);

    $mmd_xml = base64_decode($mmd);
    // dpm(DRUPAL_ROOT);
    $iso_style = file_get_contents(DRUPAL_ROOT . '/libraries/mmd/xslt/mmd-to-iso.xsl');

    $iso_xml = $this->transformXml($mmd_xml, $iso_style);
    // This is the "magic" part of the code.  Once the id is built, we can
    // return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL
    // used by this Controller to return a CSV file called "article-report.csv".
    $response->headers->set('Content-Type', 'text/xml');
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $id . '_iso.xml"');

    // This line physically adds the CSV id we created.
    $response->setContent($iso_xml);
    return $response;
  }

  /**
   * Get mmd xml string from Solr input id.
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

    $mmd = NULL;
    foreach ($result as $doc) {
      $fields = $doc->getFields();
      // \Drupal::logger('export_doc')->debug($doc);
      $mmd = $fields['mmd_xml_file'];
    }
    return $mmd;
  }

  /**
   * Transform the XML given stylesheet.
   */
  public function transformXml($xml, $style) {
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
    // Return $newXml;.
  }

}
