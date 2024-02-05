<?php

namespace Drupal\metsis_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\search_api\Entity\Index;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a Metsis Search form.
 */
class DownloadDatasetForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_search_download_dataset';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $datasetId = NULL) {

    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('id:' . $datasetId);
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);.
    $solarium_query->setRows(1);
    // $fields[] = 'id';
    // $fields[] = 'mmd_xml_file';
    // $solarium_query->setFields($fields);
    $result = $connector->execute($solarium_query);
    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    /* Throw not found exception to make drupal create 404 page when not in index */
    if ($found === 0) {
      throw new NotFoundHttpException();
    }
    $fields = [];
    $httpArr = [];
    $dodsArr = [];
    $odataArr = [];
    $wmsArr = [];
    $wfsArr = [];
    $wcsArr = [];
    $ftpArr = [];

    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }

    $form['data_access'] = [
      '#type' => 'horizontal_tabs',
      // Open the second tab by default.
      // Find the target element's ID by using your browser's debugger.
      // '#default_tab' => 'edit-demofieldset2',.
    ];

    if (isset($fields['data_access_url_http'])) {
      foreach ($fields['data_access_url_http'] as $resource) {
        $httpArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . $resource . '</a>'];
      }
      if (count($httpArr) > 0) {
        $form['http'] = [
          '#type' => 'details',
          '#title' => $this
            ->t('HTTP Download'),
          '#group' => 'data_access',
        ];
        $form['http']['links'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          // '#title' => 'HTTP Download',
          '#items' => $httpArr,
          '#group' => 'data_access',
        ];

      }
    }
    if (isset($fields['data_access_url_opendap'])) {
      foreach ($fields['data_access_url_opendap'] as $resource) {
        $dodsArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '.html">' . $resource . '</a>'];
      }
      if (count($httpArr) > 0) {
        $form['opendap'] = [
          '#type' => 'details',
          '#title' => $this
            ->t('OPeNDAP Download'),
          '#group' => 'data_access',
        ];
        $form['opendap']['links'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
         // '#title' => 'HTTP Download',
          '#items' => $dodsArr,
          '#group' => 'data_access',
        ];

      }
    }

    if (isset($fields['data_access_url_odata'])) {
      foreach ($fields['data_access_url_odata'] as $resource) {
        $odataArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . $resource . '</a>'];
      }
      if (count($odataArr) > 0) {
        $form['odata'] = [
          '#type' => 'details',
          '#title' => $this
            ->t('ODATA Download'),
          '#group' => 'data_access',
        ];
        $form['odata']['links'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
         // '#title' => 'HTTP Download',
          '#items' => $odataArr,
          '#group' => 'data_access',
        ];

      }
    }

    if (isset($fields['data_access_url_ogc_wms'])) {
      foreach ($fields['data_access_url_ogc_wms'] as $resource) {
        if (str_contains($resource, '?')) {
          $capLink = explode('?', $resource)[0];
        }
        else {
          $capLink = $resource;
        }
        $wmsArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $capLink . 'service=WMS&version=1.3.0&request=GetCapabilities">' . $capLink . '</a>'];
      }
      if (count($wmsArr) > 0) {
        $form['wms'] = [
          '#type' => 'details',
          '#title' => $this
            ->t('OGC WMS Download'),
          '#group' => 'data_access',
        ];
        $form['wms']['links'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
         // '#title' => 'HTTP Download',
          '#items' => $wmsArr,
          '#group' => 'data_access',
        ];

      }
    }

    if (isset($fields['data_access_url_ogc_wfs'])) {
      foreach ($fields['data_access_url_ogc_wfs'] as $resource) {
        $wfsArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . $resource . '</a>'];
      }
    }
    if (isset($fields['data_access_url_ogc_wcs'])) {
      foreach ($fields['data_access_url_ogc_wcs'] as $resource) {
        $wcsArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . $resource . '</a>'];
      }
    }
    if (isset($fields['data_access_url_ftp'])) {
      foreach ($fields['data_access_url_ftp'] as $resource) {
        $ftpArr[] = ['#markup' => '<a class="w3-text-blue" href="' . $resource . '">' . $resource . '</a>'];
      }
    }

    // Do not render fieldset if we do not have any info.
    // if (count($form['data_access']) <= 3) {
    // $form['data_access'] = NULL;
    // }.

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (mb_strlen($form_state->getValue('message')) < 10) {
      $form_state->setErrorByName('message', $this->t('Message should be at least 10 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
