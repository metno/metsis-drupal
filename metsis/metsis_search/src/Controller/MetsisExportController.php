<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\search_api\Entity\Index;

/**
 * Use Drupal\serialization\Encoder\XmlEncoder as SerializationXMLEncoder;.
 */
class MetsisExportController extends ControllerBase {

  /**
   * Controller for exporting metaid to different formats.
   */
  public function export($id) {

    /**
     * Fetch the mmd_xml_file field from solr, given dataset id from $data
     */

    /**
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
   *
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
   *
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
   *
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

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    // \Drupal::logger('export_doc')->debug("found: " . $found);
    $mmd = NULL;
    foreach ($result as $doc) {
      $fields = $doc->getFields();
      // \Drupal::logger('export_doc')->debug($doc);
      $mmd = $fields['mmd_xml_file'];
    }
    return $mmd;
  }

  /**
   *
   */
  public function transformXml($xml, $style) {
    $xslt = new \XSLTProcessor();
    $xslt->importStylesheet(new \SimpleXMLElement($style));

    return $xslt->transformToXml(new \SimpleXMLElement($xml));

    // Return $newXml;.
  }

}
