<?php

namespace Drupal\metsis_search;

use Drupal\Component\Utility\UrlHelper;
use Drupal\search_api\Entity\Index;

/**
 * Class for different search utils.
 */
class SearchUtils {

  /**
   * Called from  hook_search_api_solr_search_results_alter  in metsis_search.module.
   *
   * Input param: Results from Solrarium query
   * Output: facet render array for facet block.
   */
  public static function processGcmdFacet($result_set) {
    // Get the request referer for go back button.
    $request = \Drupal::request();
    $referer = $request->headers->get('referer');
    $session = \Drupal::request()->getSession();
    // Display facet results.
    $facet = $result_set->getFacetSet()->getFacet('gcmd');

    $list = [];
    $markup = "";
    foreach ($facet as $pivot) {
      $markup .= SearchUtils::displayPivotFacet($pivot, $referer);
    }

    return $markup;

    /*
    $keyw_level = $session->get('keyword_level');

    $markup = '<div class="gcmdlist">';
    $markup .= "<ul>";
    $keywords = [];
    foreach ($facet as $value => $count) {
    $value = rtrim($value, ", ");
    $lower = ucwords(strtolower($value));
    $string =  str_replace("\n","",$lower);
    $keywords[] =  $string;
    }

    $keys = array_unique($keywords);
    foreach( $keys as $value) {

    $param = str_replace(" ", "_", $value);
    $item = '<li><a href="'. $referer . '&gcmd'.$keyw_level.'=' .$param.'">'.$value .'</a></li>';
    \Drupal::logger('metsis_search_gcmd_proccess')->debug($item);
    $markup .= $item;
    }



    $markup .= "<ul>";
    $markup .= "</div>";
    $session->set('gcmd'.$keyw_level, $markup);
    return $markup;
     */
  }

  /**
   * Recursively render pivot facets.
   *
   * @param string $pivot
   *   The pivot facet.
   * @param string $referer
   *   The referer facet.
   */
  public static function displayPivotFacet($pivot, $referer) {
    // dpm($pivot.getValue());
    if ($pivot->getValue() != NULL) {
      $markup = '<ul>';
      $item = '<li><a href="' . $referer . '&' . $pivot->getField() . '=' . $pivot->getValue() . '">' . $pivot->getValue() . '(' . $pivot->getCount() . ')</a></li>';
      $item = '<li><a>' . $pivot->getValue() . '(' . $pivot->getCount() . ')</a></li>';
      // \Drupal::logger('metsis_search-pivot-facets')->debug($item);
      $markup .= $item;
      foreach ($pivot->getPivot() as $nextPivot) {
        $markup .= SearchUtils::displayPivotFacet($nextPivot, $referer);
      }
      $markup .= '</ul>';
      return $markup;
    }
  }

  /**
   * Recursively render facet facets.
   *
   * @param string $facet
   *   The facet facet.
   * @param string $referer
   *   The referer facet.
   */
  public static function displayFacet($facet, $referer) {
    // dpm($facet.getValue());
    if ($facet->getValue() != NULL) {
      $markup = '<ul>';
      $item = '<li><a href="' . $referer . '&' . $facet->getField() . '=' . $facet->getValue() . '">' . $facet->getValue() . '(' . $facet->getCount() . ')</a></li>';
      // \Drupal::logger('metsis_search-facet-facets')->debug($item);
      $markup .= $item;
      foreach ($pivot->getPivot() as $nextPivot) {
        $markup .= SearchUtils::displayPivotFacet($nextPivot, $referer);
      }
      $markup .= '</ul>';
      return $markup;
    }
  }

  /**
   * Called from  hook_search_api_solr_search_results_alter  in metsis_search.module.
   *
   * Input param: Results from Solrarium query
   * Output: extracted info for search map.
   */
  public static function getExtractedInfo($result_set) {

    // Get stored config.
    $config = \Drupal::config('metsis_search.settings');

    // Get the refering page url.
    $query_from_request = \Drupal::request()->query->all();
    $query = UrlHelper::filterQueryParameters($query_from_request);
    $request = \Drupal::request();
    $referer = $request->headers->get('referer', '/metsis/search');

    $metadata_div_counter = 0;
    $extracted_info = [];
    foreach ($result_set as $doc) {
      $fields = $doc->getFields();

      // $geographical_extent_north = isset($fields['geographic_extent_rectangle_north']) ? $fields['geographic_extent_rectangle_north'] : 1;
      // $geographical_extent_south = isset($fields['geographic_extent_rectangle_south']) ? $fields['geographic_extent_rectangle_north'] : 1;
      // $geographical_extent_east = isset($fields['geographic_extent_rectangle_east']) ? $fields['geographic_extent_rectangle_north'] : 1;
      // $geographical_extent_west = isset($fields['geographic_extent_rectangle_west']) ? $fields['geographic_extent_rectangle_north'] : 1;
      $geographical_extent_north = $fields['geographic_extent_rectangle_north'] ?? 90;
      $geographical_extent_south = $fields['geographic_extent_rectangle_south'] ?? -90;
      $geographical_extent_east = $fields['geographic_extent_rectangle_east'] ?? 180;
      $geographical_extent_west = $fields['geographic_extent_rectangle_west'] ?? -180;

      $geographical_extent = [
        (float) $geographical_extent_north,
        (float) $geographical_extent_south,
        (float) $geographical_extent_east,
        (float) $geographical_extent_west,
      ];
      $latlon = [
        ($geographical_extent_south + $geographical_extent_north) / 2,
        ($geographical_extent_east + $geographical_extent_west) / 2,
      ];

      $address_o = isset($fields['data_access_url_opendap']) ? $fields['data_access_url_opendap'][0] : '';
      $address_w = isset($fields['data_access_url_ogc_wms']) ? $fields['data_access_url_ogc_wms'][0] : '';
      $address_h = isset($fields['data_access_url_http']) ? $fields['data_access_url_http'][0] : '';
      $address_od = isset($fields['data_access_url_opendap']) ? $fields['data_access_url_opendap'][0] : '';
      $access_const = $fields['ss_access'] ?? 'Unspecified';
      $use_const = $fields['use_constraint'] ?? 'Unspecified';
      // $isotopic = isset($fields['iso_topic_category']) ? $fields['iso_topic_category'] : '';
      // $keywords = isset($doc['keywords_keyword'])? $fields['keywords_keyword'] : '' ;
      $activity = $fields['activity_type'] ?? '';
      $ds_prod_status = $fields['dataset_production_status'] ?? '';
      $md_status = $fields['metadata_status'] ?? '';
      $last_md_update = $fields['last_metadata_update_datetime'] ?? '';
      $dc_sh = $fields['data_center_short_name'] ?? '';
      $dc_ln = $fields['data_center_long_name'] ?? '';
      $dc_url = $fields['data_center_url'] ?? '';
      $dc_cr = isset($fields['personnel_datacenter_role']) ? $fields['personnel_datacenter_role'][0] : '';
      $dc_cn = isset($fields['personnel_datacenter_name']) ? $fields['personnel_datacenter_name'][0] : '';
      $dc_ce = isset($fields['personnel_datacenter_email']) ? $fields['personnel_datacenter_email'][0] : '';
      $related_lp_url = $fields['related_url_landing_page'] ?? '';
      $personnel_name = isset($fields['personnel_investigator_name'][0]) ? trim($fields['personnel_name'][0]) : '';

      // $dataset_name = $fields['metadata_identifier'];
      $dataset_name = $fields['id'];

      $institutions = !empty($fields['personnel_investigator_organisation'][0]) ? $fields['personnel_organisation'][0] : ' ';
      // getDataAccessMarkupOpenDapOpenDap('Button Text', url)
      $netcdf_download = "";
      $odata_download  = "";
      if (isset($fields['data_access_url_opendap'])) {
        $netcdf_download = SearchUtils::getDataAccessMarkupOpenDapOpenDapHtml($config->get('dar_opendap'), $fields['data_access_url_opendap'][0]);
      }
      if (isset($fields['data_access_url_odata'])) {
        $odata_download = SearchUtils::getDataAccessMarkupOpenDapOpenDap($config->get('dar_odata'), $fields['data_access_url_odata'][0]);
      }
      $temporal_extent_start_date = $fields['temporal_extent_start_date'] ?? "";
      $temporal_extent_end_date = $fields['temporal_extent_end_date'] ?? "";

      $mapthumb = "";
      if (isset($fields['thumbnail_data'])) {
        // $mapthumb = SearchUtils::getMapThumbDivs($fields['thumbnail_data'], $fields['metadata_identifier']);
      }
      $target_url = '';
      if (isset($fields['data_access_url_ogc_wms'])) {
        $target_url = '/metsis/map/wms?dataset=' . $fields['id'];
      }
      $related_lp = "";
      if ($related_lp_url != NULL &&  $related_lp_url != "") {
        $button_var = $config->get('lp_button_var');
        if ($button_var === 'title') {
          $related_lp = SearchUtils::getButtonMarkupNt($fields['title'][0], $related_lp_url[0]);
        }
        if ($button_var === 'metadata_identifier') {
          $related_lp = SearchUtils::getButtonMarkupNt($fields['metadata_identifier'], $related_lp_url[0]);
        }
        else {
          $related_lp = $fields['title'];
        }
      }
      elseif (isset($fields['data_access_url_http'])) {
        $related_lp = SearchUtils::getButtonMarkupNt($fields['title'][0], $fields['data_access_url_http'][0]);
      }
      else {
        $related_lp = $fields['title'];
      }
      $isotopic = "";
      $keywords = "";
      if (isset($fields['keywords_keyword'])) {
        // $keywords = SearchUtils::keywordsToString($fields['keywords_keyword']);
        $keywords = '';
      }
      if (isset($fields['collection'])) {
        $collection = $fields['collection'];
      }
      else {
        $collection = '';
      }
      $project = $fields['project_long_name'] ?? "";
      // Get the fimex link.
      if (isset($fields['data_access_url_opendap']) && isset($fields['feature_type'])) {
        if (($fields['feature_type'] === "timeSeries") || ($fields['feature_type'] === "profile")) {
          $fimex_link = "";
        }
        else {
          // $fimex_link = SearchUtils::getFimexLink($fields['metadata_identifier'], $referer);
        }
      }
      else {
        $fimex_link = "";
      }

      $visualize_button = "";
      $ascii_button = "";
      $leveltwo_button = "";
      if (isset($fields['isParent']) && ($fields['isParent'] == TRUE)) {
        // $leveltwo_button = SearchUtils::getLevelTwoLinks($fields['metadata_identifier'], $fields['isParent']);
      }
      /*
       * time series{
       */
      $feature_type = $fields['feature_type'] ?? 'NA';
      $visualize_button = '';
      $ascii_button = '';
      $server_type = $config->get('ts_server_type');
      if (isset($fields['feature_type']) && isset($fields['data_access_url_opendap'])) {
        $feature_type = $fields['feature_type'];
        if (($fields['feature_type'] === "timeSeries") || ($fields['feature_type'] === "profile")) {
          if (isset($server_type) && $server_type === 'pywps') {
            $visualize_url = "/metsis/bokeh/plot?opendap_urls=" . $fields['data_access_url_opendap'][0];
            $visualize_url .= "&calling_results_page=" . $referer;
            $ascii_url = "/metsis/bokeh/csv?opendap_urls=" . $fields['data_access_url_opendap'][0];
            $ascii_url .= "&calling_results_page=" . $referer;
            // $visualize_button = SearchUtils::getDataAccessMarkupOpenDapOpenDap($config->get('ts_button_text'), $visualize_url);
            // $ascii_button = SearchUtils::getDataAccessMarkupOpenDapOpenDap($config->get('csv_button_text'), $ascii_url);
          }

          if (isset($server_type) && $server_type === 'zoo') {
            $visualize_url = "/metsis/ts?metadata_identifier=" . $fields['metadata_identifier'];
            $visualize_url .= "&calling_results_page=" . $referer;
            $ascii_url = "/metsis/csv?metadata_identifier=" . $fields['metadata_identifier'];
            $ascii_url .= "&calling_results_page=" . $referer;
            // $visualize_button = SearchUtils::getDataAccessMarkupOpenDapOpenDap($config->get('ts_button_text'), $visualize_url);
            // $ascii_button = SearchUtils::getDataAccessMarkupOpenDapOpenDap($config->get('csv_button_text'), $ascii_url);
          }
        }
      }
      /*
       * time series}
       */

      // Get the wms layers.
      $wms_layer = isset($fields['data_access_wms_layers']) ? $fields['data_access_wms_layers'][0] : "None";
      if ($wms_layer === NULL) {
        $wms_layer = 'None';
      }
      // Create extracted_info array from collected data.
      $extracted_info[$metadata_div_counter] = [
      [
        $address_o,
        $address_w,
        $address_h,
        $address_od,
        $netcdf_download,
        $odata_download,
      ],
        $dataset_name,
        $geographical_extent,
        $latlon,
        $fields['title'],
      // $fields['abstract'],
        'abstract',
      [$temporal_extent_start_date, $temporal_extent_end_date],
      [$mapthumb, $target_url],
      [$related_lp, $related_lp_url],
      [$isotopic, $keywords, $collection, $activity, $project],
      [$ds_prod_status, $md_status, $last_md_update],
      [$dc_sh, $dc_ln, $dc_url, $dc_cr, $dc_cn, $dc_ce],
      [
      // $fimex_link,
      // $visualize_button,
      // $ascii_button,
      // $leveltwo_button,
      // $target_url,
      ],
      [$institutions, $personnel_name],
      [$access_const, $use_const],
        'metsis',
        $feature_type,
        $wms_layer,
      ];
      $metadata_div_counter += 1;

      // Do something with the fields.
    }
    // \Drupal::logger('metsis_search:SearchUtils::extractedInfo:')->debug('Creaded extractded info with elements: ' . $metadata_div_counter);
    return $extracted_info;
  }

  /**
   * Get the markup for the opendap url.
   */
  public static function getDataAccessMarkupOpenDapOpenDap($dataset_id, $data_access) {
    $url = '' . '<div class="botton-wrap ext_data_source">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '">' . $dataset_id . '</a>' . '</div>';
    return $url;
  }

  /**
   * Get the markup for the opendap html landing page.
   */
  public static function getDataAccessMarkupOpenDapOpenDapHtml($dataset_id, $data_access) {
    $url = '' . '<div class="botton-wrap ext_data_source">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '.html' . '">' . $dataset_id . '</a>' . '</div>';
    return $url;
  }

  /**
   * Get thumbnail div markup.
   */
  public static function getMapThumbDivs($thumbnail, $dataset_id) {
    /*  $string = <<<EOD
    <div class="media media--blazy media--loading media--image">
    <a href="/metsis/map/wms?dataset=$dataset_id">
    <img  class="b-lazy media__image media__element img-responsive" alt="Embedded Thumbnail" data-src="$thumbnail"
    src="/modules/metsis/metsis_search/images/Blank.gif" typeof="Image"/>
    </a></div>
    EOD;*/
    $string = <<<EOD
    <div class = "thumbnail_container">
      <div class = "thumbnail overlay_image">
        <a href = "/metsis/map/wms?dataset=$dataset_id">
          <img
            src = "$thumbnail"
           />
        </a>
      </div>
    </div>
EOD;
    return $string;
  }

  /**
   * Get the button markup.
   */
  public static function getButtonMarkupNt($metadata_identifier, $button_uri) {
    $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton" target="_blank" href="' . $button_uri . '">' . $metadata_identifier . '</a>' . '</div>';
    return $url;
  }

  /**
   * Get the fimex link.
   */
  public static function getFimexLink($dataset_id, $calling_results_page) {
    $url = '';
    $url .= '<div class="botton-wrap">';
    $url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '&calling_results_page=' . $calling_results_page . '" >Transform</a>';
    $url .= '</div>';
    return $url;
  }

  /**
   * Get level two links.
   */
  public static function getLevelTwoLinks($dataset_id, $isParent) {
    if ($isParent) {
      $url = <<<EOD
    <div id="metachild" class="metachild" data-id="$dataset_id" isparent="$isParent">
    <a id="metachildlink" class="visually-hidden adc-button adc-sbutton" href="/metsis/elements/$dataset_id/search>"</a>
    </div>
    EOD;
    }
    else {
      $url = "";
    }
    return $url;
  }

  /**
   * Get keywords as string.
   */
  public static function keywordsToString($keywords_array) {
    $glue = "<br>";
    return implode($glue, $keywords_array);
  }

  /**
   * Get a list of available collections in the index .
   */
  public static function getCollections() {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    // Get the facetset component.
    $facetSet = $solarium_query->getFacetSet();

    // Create a facet field instance and set options.
    $facetSet->createFacetField('collection')->setField('collection');

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    // $found = $result->getNumFound();
    $facet = $result->getFacetSet()->getFacet('collection');
    $collection = [];
    foreach ($facet as $value => $count) {
      $collection[$value] = $value;
    }
    return $collection;
  }

}
