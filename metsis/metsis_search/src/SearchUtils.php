<?php
/**
 *
 * @file
 * Contains \Drupal\metsis_search\SearchUtils
 *
 * utility functions for metsis_search
 *
 */

namespace Drupal\metsis_search;

use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\Component\Result\Facet\Pivot\PivotItem;
use Solarium\Component\Result\Facet\Pivot\Pivot;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

class SearchUtils
{
    /**
     * Called from  hook_search_api_solr_search_results_alter  in metsis_search.module
     *
     * Input param: Results from Solrarium query
     * Output: facet render array for facet block
     */
    public static function processGcmdFacet($result_set)
    {
        // Get the request referer for go back button
        $request = \Drupal::request();
        $referer = $request->headers->get('referer');
        $session = \Drupal::request()->getSession();
        // display facet results
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
     * Recursively render pivot facets
     *
     * @param $pivot
     */
    public static function displayPivotFacet($pivot, $referer)
    {
        //dpm($pivot.getValue());
        if ($pivot->getValue() != null) {
            $markup = '<ul>';
            $item = '<li><a href="'. $referer . '&' .$pivot->getField() .'=' .$pivot->getValue().'">'.$pivot->getValue() . '(' .$pivot->getCount() .')</a></li>';
            $item = '<li><a>'. $pivot->getValue() . '(' .$pivot->getCount() .')</a></li>';
            //\Drupal::logger('metsis_search-pivot-facets')->debug($item);
            $markup .= $item;
            foreach ($pivot->getPivot() as $nextPivot) {
                $markup .= SearchUtils::displayPivotFacet($nextPivot, $referer);
            }
            $markup .= '</ul>';
            return $markup;
        }
    }

    /**
     * Recursively render facet facets
     *
     * @param $facet
     */
    public static function displayFacet($facet, $referer)
    {
        //dpm($facet.getValue());
        if ($facet->getValue() != null) {
            $markup = '<ul>';
            $item = '<li><a href="'. $referer . '&' .$facet->getField() .'=' .$facet->getValue().'">'.$facet->getValue() . '(' .$facet->getCount() .')</a></li>';
            //\Drupal::logger('metsis_search-facet-facets')->debug($item);
            $markup .= $item;
            foreach ($pivot->getPivot() as $nextPivot) {
                $markup .= SearchUtils::displayPivotFacet($nextPivot, $referer);
            }
            $markup .= '</ul>';
            return $markup;
        }
    }
    /**
     * Called from  hook_search_api_solr_search_results_alter  in metsis_search.module
     *
     * Input param: Results from Solrarium query
     * Output: extracted info for search map
     */
    public static function getExtractedInfo($result_set)
    {

    //Get stored config.
        $config = \Drupal::config('metsis_search.settings');

        //Get the refering page url
        $query_from_request = \Drupal::request()->query->all();
        $query = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
        $request = \Drupal::request();
        $referer = $request->headers->get('referer');


        $metadata_div_counter = 0;
        $extracted_info = [];
        foreach ($result_set as $doc) {
            $fields = $doc->getFields();



            //$geographical_extent_north = isset($fields['geographic_extent_rectangle_north']) ? $fields['geographic_extent_rectangle_north'] : 1;
            //$geographical_extent_south = isset($fields['geographic_extent_rectangle_south']) ? $fields['geographic_extent_rectangle_north'] : 1;
            //$geographical_extent_east = isset($fields['geographic_extent_rectangle_east']) ? $fields['geographic_extent_rectangle_north'] : 1;
            //$geographical_extent_west = isset($fields['geographic_extent_rectangle_west']) ? $fields['geographic_extent_rectangle_north'] : 1;
            $geographical_extent_north = $fields['geographic_extent_rectangle_north'];
            $geographical_extent_south = $fields['geographic_extent_rectangle_south'];
            $geographical_extent_east = $fields['geographic_extent_rectangle_east'];
            $geographical_extent_west = $fields['geographic_extent_rectangle_west'];

            $geographical_extent = [
                      $geographical_extent_north,
                      $geographical_extent_south,
                      $geographical_extent_east,
                      $geographical_extent_west,
                    ];
            $latlon = [
                      ($geographical_extent_south + $geographical_extent_north) / 2,
                      ($geographical_extent_east + $geographical_extent_west) / 2,
            ];

            $address_o = isset($fields['data_access_url_opendap']) ? $fields['data_access_url_opendap'][0] : '';
            $address_w = isset($fields['data_access_url_ogc_wms']) ? $fields['data_access_url_ogc_wms'][0] : '';
            $address_h = isset($fields['data_access_url_http']) ? $fields['data_access_url_http'][0] : '';
            $address_od = isset($fields['data_access_url_opendap']) ? $fields['data_access_url_opendap'][0] : '';
            $access_const = isset($fields['ss_access']) ? $fields['ss_access'] : 'Unspecified';
            $use_const = isset($fields['use_constraint']) ? $fields['use_constraint'] : 'Unspecified';
            //$isotopic = isset($fields['iso_topic_category']) ? $fields['iso_topic_category'] : '';
            //$keywords = isset($doc['keywords_keyword'])? $fields['keywords_keyword'] : '' ;
            $activity = isset($fields['activity_type']) ? $fields['activity_type'] : '';
            $ds_prod_status = isset($fields['dataset_production_status']) ? $fields['dataset_production_status'] : '';
            $md_status = isset($fields['metadata_status']) ? $fields['metadata_status'] : '';
            $last_md_update = isset($fields['last_metadata_update_datetime']) ? $fields['last_metadata_update_datetime'] : '';
            $dc_sh = isset($fields['data_center_short_name']) ? $fields['data_center_short_name'] : '';
            $dc_ln = isset($fields['data_center_long_name']) ? $fields['data_center_long_name'] : '';
            $dc_url = isset($fields['data_center_url']) ? $fields['data_center_url'] : '';
            $dc_cr = isset($fields['personnel_datacenter_role']) ? $fields['personnel_datacenter_role'][0] : '';
            $dc_cn = isset($fields['personnel_datacenter_name']) ? $fields['personnel_datacenter_name'][0] : '';
            $dc_ce = isset($fields['personnel_datacenter_email']) ? $fields['personnel_datacenter_email'][0] : '';
            $related_lp_url = isset($fields['related_url_landing_page']) ? $fields['related_url_landing_page'] : '';
            $personnel_name = isset($fields['personnel_investigator_name'][0]) ? trim($fields['personnel_name'][0]) : '';

            //$dataset_name = $fields['metadata_identifier'];
            $dataset_name = $fields['id'];

            $institutions = !empty($fields['personnel_investigator_organisation'][0]) ? $fields['personnel_organisation'][0] : ' ';
            // get_data_access_markup('Button Text', url)
            $netcdf_download ="";
            $odata_download  = "";
            if (isset($fields['data_access_url_opendap'])) {
                $netcdf_download = SearchUtils::get_data_access_markup_opendap($config->get('dar_opendap'), $fields['data_access_url_opendap'][0]);
            }
            if (isset($fields['data_access_url_odata'])) {
                $odata_download  = SearchUtils::get_data_access_markup($config->get('dar_odata'), $fields['data_access_url_odata'][0]);
            }
            $temporal_extent_start_date = isset($fields['temporal_extent_start_date']) ? $fields['temporal_extent_start_date'] : "";
            $temporal_extent_end_date = isset($fields['temporal_extent_end_date']) ? $fields['temporal_extent_end_date'] : "";

            $mapthumb = "";
            if (isset($fields['thumbnail_data'])) {
                //$mapthumb = SearchUtils::get_map_thumb_divs($fields['thumbnail_data'], $fields['metadata_identifier']);
            }
            $target_url = '';
            if (isset($fields['data_access_url_ogc_wms'])) {
                $target_url = '/metsis/map/wms?dataset='.$fields['id'];
            }
            $related_lp = "";
            if ($related_lp_url != null &&  $related_lp_url != "") {
                $button_var = $config->get('lp_button_var');
                if ($button_var === 'title') {
                    $related_lp = SearchUtils::get_button_markup_nt($fields['title'][0], $related_lp_url[0]);
                }
                if ($button_var ==='metadata_identifier') {
                    $related_lp = SearchUtils::get_button_markup_nt($fields['metadata_identifier'], $related_lp_url[0]);
                } else {
                    $related_lp = $fields['title'];
                }
            } elseif (isset($fields['data_access_url_http'])) {
                $related_lp = SearchUtils::get_button_markup_nt($fields['title'][0], $fields['data_access_url_http'][0]);
            } else {
                $related_lp = $fields['title'];
            }
            $isotopic = "";
            $keywords = "";
            if (isset($fields['keywords_keyword'])) {
                //$keywords = SearchUtils::keywords_to_string($fields['keywords_keyword']);
                $keywords = '';
            }
            if (isset($fields['collection'])) {
                $collection =$fields['collection'];
            } else {
                $collection = '';
            }
            $project = isset($fields['project_long_name']) ? $fields['project_long_name'] : "";
            //Get the fimex link
            if (isset($fields['data_access_url_opendap']) && isset($fields['feature_type'])) {
                if (($fields['feature_type'] === "timeSeries") || ($fields['feature_type'] === "profile")) {
                    $fimex_link = "";
                } else {
                    //$fimex_link = SearchUtils::get_fimex_link($fields['metadata_identifier'], $referer);
                }
            } else {
                $fimex_link = "";
            }

            $visualize_button = "";
            $ascii_button = "";
            $leveltwo_button = "";
            if (isset($fields['isParent']) && ($fields['isParent'] == true)) {
                //$leveltwo_button = SearchUtils::get_leveltwo_links($fields['metadata_identifier'], $fields['isParent']);
            }
            /**
             * time series{
             */
            $feature_type = isset($fields['feature_type']) ? $fields['feature_type'] : 'NA';
            $visualize_button = '';
            $ascii_button = '';
            $server_type = $config->get('ts_server_type');
            if (isset($fields['feature_type']) && isset($fields['data_access_url_opendap'])) {
                $feature_type = $fields['feature_type'];
                if (($fields['feature_type'] === "timeSeries") || ($fields['feature_type'] === "profile")) {
                    if (isset($server_type) && $server_type  === 'pywps') {
                        $visualize_url = "/metsis/bokeh/plot?opendap_urls=" . $fields['data_access_url_opendap'][0];
                        $visualize_url .= "&calling_results_page=" . $referer;
                        $ascii_url = "/metsis/bokeh/csv?opendap_urls=" .  $fields['data_access_url_opendap'][0];
                        $ascii_url .= "&calling_results_page=" . $referer;
                        //$visualize_button = SearchUtils::get_data_access_markup($config->get('ts_button_text'), $visualize_url);
                        //$ascii_button = SearchUtils::get_data_access_markup($config->get('csv_button_text'), $ascii_url);
                    }


                    if (isset($server_type) && $server_type   === 'zoo') {
                        $visualize_url = "/metsis/ts?metadata_identifier=" . $fields['metadata_identifier'];
                        $visualize_url .= "&calling_results_page=" . $referer;
                        $ascii_url = "/metsis/csv?metadata_identifier=" .  $fields['metadata_identifier'];
                        $ascii_url .= "&calling_results_page=" . $referer;
                        //$visualize_button = SearchUtils::get_data_access_markup($config->get('ts_button_text'), $visualize_url);
                        //$ascii_button = SearchUtils::get_data_access_markup($config->get('csv_button_text'), $ascii_url);
                    }
                }
            }
            /**
             * time series}
             */

            //Get the wms layers
            $wms_layer = isset($fields['data_access_wms_layers']) ? $fields['data_access_wms_layers'][0] : "None";
            if ($wms_layer === null) {
                $wms_layer = 'None';
            }
            //Create extracted_info array from collected data
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
      //$fields['abstract'],
      'abstract',
      [$temporal_extent_start_date, $temporal_extent_end_date],
      [$mapthumb, $target_url],
      [$related_lp, $related_lp_url],
      [$isotopic, $keywords, $collection, $activity, $project],
      [$ds_prod_status, $md_status, $last_md_update],
      [$dc_sh, $dc_ln, $dc_url, $dc_cr, $dc_cn, $dc_ce],
      [
        $fimex_link,
        $visualize_button,
        $ascii_button,
        $leveltwo_button,
        $target_url,
      ],
      [$institutions, $personnel_name],
      [$access_const, $use_const],
      'metsis',
      $feature_type,
      $wms_layer,
    ];
            $metadata_div_counter += 1;



            //Do something with the fields
        }
        \Drupal::logger('metsis_search:SearchUtils::extractedInfo:')->debug('Creaded extractded info with elements: ' . $metadata_div_counter);
        return $extracted_info;
    }

    public static function get_data_access_markup($dataset_id, $data_access)
    {
        $url = '' . '<div class="botton-wrap ext_data_source">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '">' . $dataset_id . '</a>' . '</div>';
        return $url;
    }

    public static function get_data_access_markup_opendap($dataset_id, $data_access)
    {
        $url = '' . '<div class="botton-wrap ext_data_source">' . '<a class="adc-button adc-sbutton ext_data_source" href="' . $data_access . '.html'.'">' . $dataset_id . '</a>' . '</div>';
        return $url;
    }

    public static function get_map_thumb_divs($thumbnail, $dataset_id)
    {
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

    public static function get_button_markup_nt($metadata_identifier, $button_uri)
    {
        $url = '' . '<div class="botton-wrap">' . '<a class="adc-button adc-sbutton" target="_blank" href="' . $button_uri . '">' . $metadata_identifier . '</a>' . '</div>';
        return $url;
    }

    public static function get_fimex_link($dataset_id, $calling_results_page)
    {
        $url = '';
        $url .= '<div class="botton-wrap">';
        $url .= '<a class="adc-button adc-sbutton" href="' . '/metsis_fimex?dataset_id=' . $dataset_id . '&calling_results_page=' . $calling_results_page . '" >Transform</a>';
        $url .= '</div>';
        return $url;
    }

    public static function get_leveltwo_links($dataset_id, $isParent)
    {
        if ($isParent) {
            $url = <<<EOD
    <div id="metachild" class="metachild" data-id="$dataset_id" isparent="$isParent">
    <a id="metachildlink" class="visually-hidden adc-button adc-sbutton" href="/metsis/elements/$dataset_id/search>"</a>
    </div>
    EOD;
        } else {
            $url = "";
        }
        return $url;
    }
    public static function keywords_to_string($keywords_array)
    {
        $glue = "<br>";
        return implode($glue, $keywords_array);
    }

    /* Get a list of available collections in the index */
    public static function getCollections()
    {
        /** @var Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        $connector = $backend->getSolrConnector();

        $solarium_query = $connector->getSelectQuery();
        // get the facetset component
        $facetSet = $solarium_query->getFacetSet();

        // create a facet field instance and set options
        $facetSet->createFacetField('collection')->setField('collection');

        $result = $connector->execute($solarium_query);

        // The total number of documents found by Solr.
        //$found = $result->getNumFound();
        $facet = $result->getFacetSet()->getFacet('collection');
        $collection = [];
        foreach ($facet as $value => $count) {
            $collection[$value] = $value;
        }
        return $collection;
    }
}
