<?php

namespace Drupal\metsis_fimex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\node\Entity\Node;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Url;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Drupal\metsis_lib\MetsisUtils;
use \Drupal\metsis_fimex\FimexUtils;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Result\Result;
use Solarium\QueryType\Select\Result\Document;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;

/**
 * Class MetsisFimexForm.
 */
class MetsisFimexForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_fimex_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user = \Drupal::currentUser();
    $config = \Drupal::config('metsis_fimex.settings');
    global $epsg;
 //if ($metsis_conf['metsis_fimex_authentication_required']['boolean'] === TRUE) {


 if ($config->get('transformation_message_visible')) {
    \Drupal::messenger()->addWarning(t($config->get('transformation_warning_msg')));
 }


 /*
  * service call parameter, user info etc. {
  */

 $email = isset($_GET['email']) ? $_GET['email'] : $user->getEmail();

 /*
  * service call parameter, user infor etc. }
  */

 //can probaby not assume opendap access and must have a
 //url paramter to indicate that there is opendap (i.e. &opendap=yes or something like that)
 //this is key. we need to send in default form values or ol3 wms params.

//Get http referer for go back
$request = \Drupal::request();
$referer = $request->headers->get('referer');

 /*
  * get the data from search form and OpeNDAP and/or SOLR
  */

  /**
   * TODO: Maybe this is better approache than $_GET
   */

 $query_params = \Drupal::request()->query->all();
 $page_inputs = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_params);
 //var_dump($page_inputs);
 $dataset_id = $page_inputs['dataset_id'];
 if (empty($dataset_id)) {
   //no dataset_id was sent in
   $form_state->setRedirect($referer);
 }
// $dataset_id = isset($_GET['dataset_id']) ? $_GET['dataset_id'] : '';
 $dataset_ids = explode(",", $dataset_id);
 //var_dump($dataset_ids);

//TODO: SOLR CORE (od service uses this)
 $opendap_global_attributes = MetsisUtils::adc_get_od_global_attributes($dataset_id, 'adc-l1')['data']['findAllAttributes'];
 $opendap_variables = MetsisUtils::adc_get_od_variables($dataset_id, 'adc-l1')['data']['findAllVariables'];

 $opendap_start_time_strings = $config->get('opendap_start_time_strings');
 $opendap_stop_time_strings = $config->get('opendap_stop_time_strings');
 //dpm($opendap_global_attributes,$opendap_variables);
 /*
  * you MUST initialize $start_time and $stop_time to have them in scope!!!
  *
  */
 $start_time = '';
 $stop_time = '';
 foreach ($opendap_global_attributes as $odga) {
   if (in_array(trim($odga['name']), $opendap_start_time_strings)) {

     $start_time = trim($odga['value']);
   }
   if (in_array(trim($odga['name']), $opendap_stop_time_strings)) {
     $stop_time = trim($odga['value']);
   }
 }
 /**
  * extract start and stop times}
  */
 $reshaped_vars = [];
 if (isset($metsis_conf['transformation_exclude_variables'])) {
   $transformation_exclude_variables = explode(',', $metsis_conf['transformation_exclude_variables']);
 }
 for ($i = 0; $i < count($opendap_variables); $i++) {
   if (isset($transformation_exclude_variables)) {
     if (in_array($opendap_variables[$i]['name'], $transformation_exclude_variables)) {
       continue;
     }
   }
   $reshaped_vars[$i]['name'] = $opendap_variables[$i]['name'];
   foreach ($opendap_variables[$i]['attributes'] as $k => $v) {
     $reshaped_vars[$i][$v['name']] = $v['value'];
     //            if ($v['name'] == 'proj4_string') {
     //                $reshaped_vars['proj4_string'] = $v['value'];
     //            }
   }
 }


 // these are SOLR data. They should probably be replaced with OpeNDAP data
 $fields = [
  "id",
  "metadata_identifier",
  "geographic_extent_rectangle_east",
  "geographic_extent_rectangle_west",
  "geographic_extent_rectangle_north",
  "geographic_extent_rectangle_south",
  "temporal_extent_start_date",
  "temporal_extent_end_date",
  "title",
  "abstract",
  "data_access_url_opendap",
 ];
 //we set up the variables form based on the first dataset in the list submitted.
 //ideally we need to use the common subset of the variables from ALL the submitted datasets
 //TODO
 // see scratchpad on phab
 //need to loop through all the datasets that were sent inn.
 $solr_data = [];
 //$solr_cores = adc_get_solr_core($dataset_ids);
 //for ($i = 0; $i < count($dataset_ids); $i++) {
   //todo
   //may need to use $dataset_ids as $solr_data[] keys...
   //$solr_data[] = msb_get_fields(SOLR_CORE_PARENT, $dataset_ids[$i], $fields);
   $solr_data = MetsisUtils::msb_get_fields($dataset_ids, $fields);
 //}
 //$solr_data = msb_get_fields(SOLR_CORE_PARENT, $dataset_ids[0], $fields);
 //    foreach ($solr_data as $sd) {
 //        if ($sd['response']['numFound'] == 0) {
 //            drupal_set_message("Invalid dataset ID", 'error');
 //        }
 //    }
 //    if ($solr_data['response']['numFound'] == 0) {
 //        //an invalid dataset_id was sent in
 //        drupal_goto("/metadata_search");
 //    }
 //$dar = msb_concat_data_access_resource($solr_data['response']['docs'][0][METADATA_PREFIX . 'data_access_resource']);
//var_dump($solr_data);
 if ($solr_data->getNumFound() == 0) {
  \Drupal::messenger()->addError("Invalid dataset ID");
 }
 $dar = [];
 $dtitle = [];
 $dabstract = [];
 $d_geo_north = [];
 $d_geo_south = [];
 $d_geo_east = [];
 $d_geo_west = [];
 foreach ($solr_data as $doc) {
  $fields = $doc->getFields();

   $dar = $fields['data_access_url_opendap'];
   $dtitle = $fields['title'];
   $dabstract = $fields['abstract'];
   $d_geo_north = $fields['geographic_extent_rectangle_north'];
   $d_geo_south = $fields['geographic_extent_rectangle_south'];
   $d_geo_east = $fields['geographic_extent_rectangle_east'];
   $d_geo_west = $fields['geographic_extent_rectangle_west'];



 }
//var_dump($sd['response']['docs'][0][METADATA_PREFIX . 'data_access_resource']);
 //todo
 //use metadata from the first dataset in URL is used
//var_dump($d_geo_north);
if(isset($dar[0])) {
 $opendap_ddx = $dar[0] . ".ddx";
 //$opendap_ddx = $dar[0]['OPeNDAP']['url'];
 //var_dump($dar[0]['OPeNDAP']['url']);
 //test{
 //    $feature_types = adc_get_od_feature_type($opendap_ddx);
 //    foreach ($feature_types['Attribute'] as $a) {
 //        foreach ($a['Attribute'] as $aa) {
 //            if (isset($aa['value'])) {
 //                if ($aa['value'] == 'timeSeries') {\
 //                    //we have feature type time series. Do the markup
 //                }
 //            }
 //        }
 //    }
 // $feature_types['Attribute'][0]['Attribute'][24]['@attributes']['name']
 //$feature_types['Attribute'][0]['Attribute'][24]['value']
 //$opendap_ddx ="http://super-monitor.met.no/thredds/dodsC/lustreMntB/users/heikok/Meteorology/ecdiss-internet.met.no/ecdiss/NBS/S2A_MSIL1C_20170126T105321_N0204_R051_T32VNL_20170126T105315.nc.ddx";
 //test}
 $jod_data = FimexUtils::adc_get_od_data($opendap_ddx);
}
else {
  \Drupal::messenger()->addError("Selected datasets does not contain OPeNDAP resource");
/*  $form_state->setRedirectUrl(Url::fromUri($referer));

  $form['error_message'] = [
    '#weight' => 10,
    '#markup' => 'Selected datasets does not contain OPeNDAP resource',
  ];
 $form['back_to_search'] = [
   '#weight' => 14,
   '#markup' => '<a href="' .$referer . '" class="adc-button adc-back">Back to results</a>',
 ];
  return $form;*/
  //return new RedirectResponse(Url::fromUri($referer));
  //var_dump($referer);
  //return new RedirectResponse($referer);
}
 //$od_temporal_extent = adc_get_od_temporal_extent($jod_data);
 /**
  * test{
  */
 foreach ($opendap_global_attributes as $odga) {
   if ($odga['name'] == 'start_date' || $odga['name'] == 'min_time' || $odga['name'] == 'start_time' || $odga['name'] == 'calculated_start_time') {
     $od_temporal_extent['start_date'] = $odga['value'];
   }
   else {
     $od_temporal_extent['start_date'] = "";
   }
   if ($odga['name'] == 'stop_date' || $odga['name'] == 'max_time' || $odga['name'] == 'stop_time' || $odga['name'] == 'calculated_stop_time') {
     $od_temporal_extent['stop_date'] = $odga['value'];
   }
   else {
     $od_temporal_extent['stop_date'] = '';
   }
 }
 /**
  * test}
  */
 $epsg = FimexUtils::get_proj4_strings();
 $od_proj4 = FimexUtils::adc_get_od_proj4($jod_data);


 /**
  * TODO 3
  * hack hack hack{
  */
 if (isset($od_proj4['Original'])) {
   $epsg['Original'] = $od_proj4['Original'];
 }
 elseif (isset($reshaped_vars['proj4_string'])) {
   $epsg['Original'] = $reshaped_vars['proj4_string'];
 }
 else {
   $epsg['Original'] = "";
 }
 /**
  * hack hack hack}
  */
 //    $od_global_attributes = array();
 //    foreach ($jod_data['Attribute']['Attribute'] as $d) {
 //        $od_global_attributes[$d['@attributes']['name']] = $d['value'];
 //    }
 //TODO must determine which attributes are mandatory and flag them as MISSING
 // this hack is to suppress errors for now
 // a list of required attributes must be defined in the configuration file for the site
 // and checked here.
 //if several datasets are passed in (basket) the abstracts should be omitted,
 //the titles can be listed, but should probably be omitted?
 //we use $solr_data[0] for now, but may need to change to $solr_data[<dataset id key>]
 //see above comment. My be usefull if one dataset is considered to be master
 //    if (!array_key_exists('title', $od_global_attributes)) {
 //        $od_global_attributes['title'] = "Title (discovery metadata): " . implode(",", $solr_data['response']['docs'][0][METADATA_PREFIX . 'title']);
 //    }
 //    if (!array_key_exists('abstract', $od_global_attributes)) {
 //        $od_global_attributes['abstract'] = "Abstract (discovery metadata): " . implode(",", $solr_data['response']['docs'][0][METADATA_PREFIX . 'abstract']);
 //    }
 //    if (!array_key_exists('description', $od_global_attributes)) {
 //        $od_global_attributes['description'] = "Description: MISSING";
 //    }
 /**
  * test{
  */
 $od_global_attributes = $opendap_global_attributes;
 //    $od_global_attributes = array();
 //    foreach($opendap_global_attributes as $k => $v){
 //        $od_global_attributes[$k['name']] = $v['value'];
 //    }
 //
 /**
  * test}
  */
 if (!array_key_exists('title', $od_global_attributes)) {
   $od_global_attributes['title'] = "Title (discovery metadata): " . implode(",", $dtitle);
 }
 if (!array_key_exists('abstract', $od_global_attributes)) {
   $od_global_attributes['abstract'] = "Abstract (discovery metadata): " . implode(",", $dabstract);
 }
 if (!array_key_exists('description', $od_global_attributes)) {
   $od_global_attributes['description'] = "Description: MISSING";
 }
 //multivalues here
 //todo
 //refactor
 //need to construct the final url in a better way as base_url + data_ids[]
 $opendap_urls = [];
 foreach ($dar as $dd) {
   $opendap_urls[] = $dd;
 }

 $form['opendap'] = [
   'opendap' => [
     '#type' => 'hidden',
     //multivalues
     //todo
     //'#value' => $dar[0]['OPeNDAP']['url'],
     '#value' => implode(";", $opendap_urls),
   ],
 ];
 $form['od_title'] = [
   '#markup' => '<h1>' . $od_global_attributes['title'] . '</h1>',
 ];
 $form['od_abstract'] = [
   '#markup' => '<h4>' . $od_global_attributes['abstract'] . '</h4>',
 ];
 /*
  * wps fimex info {
  *
  * the WPS fimex should be amenable to interrogation and return
  * complete usage information as XML. This would allow for the
  * metsis_fimex form to be dynamically constructed - that is changes to the service
  * can be made visible in the form automatically.
  * As of 2016-10-12 the XML returned at $wps_fimex_url
  * does not contain enough information for the proposed automation
  *
  * $wps_fimex_url="http://157.249.176.174/cgi-bin/pywps.cgi?service=wps&version=1.0.0&request=describeprocess&identifier=transformation";
  * get_wps_fimex_info($wps_fimex_url);
  *
  */
 /*
  * wps fimex info }
  */

 /*
  * form actions {
  */
 $form['actions'] = ['#type' => 'actions'];
 //  $form['actions']['email'] = array(
 //    '#type' => 'textfield',
 //    '#title' => t('Send results to:'),
 //    '#weight' => 1,
 //    '#required' => TRUE,
 //    '#value' => t($email),
 //    '#element_validate' => array('geographical_region_validate'),
 //    '#attributes' => array(
 //      //'placeholder' => t('The e-mail address to send results to'),
 //      'class' => array(
 //        'geographical-area',
 //        'beautytips'),
 //      'title' => "The e-mail address to send the results to",
 //    ),
 //  );

 /*
  *
  */


 /*
  * form actions }
  */

 /*
  * email {
  */
 $form['user_info'] = [
   '#type' => 'fieldset',
   '#title' => t('The e-mail address to send the results to'),
   '#weight' => 1,
   '#collapsible' => TRUE,
   '#collapsed' => FALSE,
   '#attributes' => [
     'class' => [
       'user-info-fieldset',
     ],
     'msb-tooltip' => "Enter the e-mail you wish the results to be sent to.",
   ],
 ];
 $form['user_info']['email'] = [
   'email' => [
     '#title' => t('Send results to:'),
     '#type' => 'textfield',
     '#required' => TRUE,
     '#default_value' => t($email),
     '#element_validate' => $this->email_validate(),
     '#attributes' => [
       //'placeholder' => t('The e-mail address to send the results to'),
       'class' => [
         'user-info',
         'beautytips',
       ],
       'title' => "The e-mail address to send the results to",
     ],
   ],
 ];
 /*
  * email }
  */

 /*
  * spatial {
  */
 $form['geographical_area'] = [
   '#type' => 'fieldset',
   '#title' => t('Select spatial extent'),
   '#weight' => 2,
   '#collapsible' => TRUE,
   '#collapsed' => FALSE,
   '#attributes' => [
     'class' => [
       'geographical-area-fieldset',
     ],
     'msb-tooltip' => "Geographical area of desired output data (in degrees, relative to zero meridian/equator)",
   ],
 ];
 $form['geographical_area'][] = [
   //  '#type' => 'item',
   'north' => [
     '#title' => t('Degrees north'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     '#default_value' => $d_geo_north,
     '#element_validate' => $this->geographical_region_validate(),
     //'#element_validate' => ['geographical_region_validate'],
     '#attributes' => [
       //'placeholder' => t('Degrees relative to zero meridian/equator'),
       'class' => [
         'geographical-area',
         'beautytips',
       ],
       'title' => "Degrees north relative to zero meridian/equator",
     ],
   ],
   'south' => [
     '#title' => t('Degrees south'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     '#default_value' => $d_geo_south,
     '#element_validate' => $this->geographical_region_validate(),
     '#attributes' => [
       //'placeholder' => t('Degrees relative to zero meridian/equator'),
       'class' => [
         'geographical-area',
         'beautytips',
       ],
       'title' => "Degrees south relative to zero meridian/equator",
     ],
   ],
   'east' => [
     '#title' => t('Degrees east'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     '#default_value' => $d_geo_east,
     '#element_validate' => $this->geographical_region_validate(),
     '#attributes' => [
       //'placeholder' => t('Degrees relative to zero meridian/equator'),
       'class' => [
         'geographical-area',
         'beautytips',
       ],
       'title' => "Degrees east relative to zero meridian/equator",
     ],
   ],
   'west' => [
     '#title' => t('Degrees west'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     '#default_value' => $d_geo_west,
     '#element_validate' => $this->geographical_region_validate(),
     '#attributes' => [
       //'placeholder' => t('Degrees relative to zero meridian/equator'),
       'class' => [
         'geographical-area',
         'beautytips',
       ],
       'title' => "Degrees west relative to zero meridian/equator",
     ],
   ],
 ];

 /*
  * spatial }
  */

 /*
  * temporal {
  */

 $form['temporal_extent'] = [
   '#type' => 'fieldset',
   '#title' => t('Select temporal extent'),
   '#weight' => 3,
   '#collapsible' => TRUE,
   '#collapsed' => FALSE,
   '#attributes' => [
     'class' => [
       'temporal-extent-fieldset',
     ],
     'msb-tooltip' => "Temporal extent of output data",
   ],
 ];
 $form['temporal_extent'][] = [
   'start_date' => [
     '#title' => t('Start date'),
     '#type' => 'textfield',
     //'#type' => 'date_popup',
     //'#date_format' => 'Y-m-d H:i:s',
     //'#date_format' => 'Y-m-d',
     //'#date_year_range' => '-50:+2',
     //'#datepicker_options' => array(
     //  'changeMonth' => TRUE,
     //  'changeYear' => TRUE,
     // 'minDate' => 0,
     // 'maxDate' => 0
     //),
     //'#required' => TRUE,
     //'#default_value' => "1980-09-01 00:00:00",
     //'#default_value' => $od_temporal_extent['start_date'],
     //'#default_value' => adc_trim_string($od_global_attributes['start_date'], "UTC"),
     '#default_value' => $start_time,
     '#element_validate' => $this->metsis_date_validate(),
     '#attributes' => [
       //'placeholder' => t('Temporal extent of output data'),
       'class' => [
         'temporal-extent',
         'beautytips',
       ],
       'title' => "Start date of output data",
     ],
   ],
   'stop_date' => [
     '#title' => t('Stop date'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     //'#default_value' => "1982-10-01 00:00:00",
     //'#default_value' => $od_temporal_extent['stop_date'],
     //'#default_value' => adc_trim_string($od_global_attributes['stop_date'], "UTC"),
     '#default_value' => $stop_time,
     '#element_validate' => $this->metsis_date_validate(),
     '#attributes' => [
       //'placeholder' => t('Temporal extent of output data'),
       'class' => [
         'temporal-extent',
         'beautytips',
       ],
       'title' => "Stop date of output data",
     ],
   ],
 ];
 /*
  * temporal }
  */
 /*
  * variables {
  */
 //$variables = deprecated_adc_get_od_variables($jod_data);
 //deprecated_adc_get_od_variables is not used anymore
 //see above for definition of $reshaped_vars
 $variables = $reshaped_vars;
 $od_vars = [];
 foreach ($variables as $v) {
   $name = isset($v['name']) ? $v['name'] : "";
   $standard_name = isset($v['standard_name']) ? $v['standard_name'] : "";
   $long_name = isset($v['long_name']) ? $v['long_name'] : "";
   $units = isset($v['units']) ? $v['units'] : "";

   $od_vars[] = [
     'name' => $name,
     // 'description' => '',
     'standard_name' => $standard_name,
     'long_name' => $long_name,
     'units' => $units,
   ];
 }
 sort($od_vars);
 $header = [
   'name' => t('Name'),
   // 'description' => t('Description'),
   'standard_name' => t('Standard name'),
   'long_name' => t('Long name'),
   'units' => t('Units'),
 ];
 $options = [];
 foreach ($od_vars as $v) {
   //foreach ($od_vars_sort as $v) {
   $options[$v['name']] = [
     'name' => $v['name'],
     //'description' => $v['description'],
     'standard_name' => $v['standard_name'],
     'long_name' => $v['long_name'],
     'units' => $v['units'],
   ];
 }
 $form['od_variables'] = [
   '#type' => 'fieldset',
   '#title' => t('Select variables'),
   '#weight' => 5,
   '#collapsible' => TRUE,
   '#collapsed' => FALSE,
   '#element_validate' => $this->select_variables_validate($form, $form_state),
   '#attributes' => [
     'class' => [
       'od-variables-fieldset',
     ],
     'msb-tooltip' => "Variables",
   ],
 ];
 $form['od_variables']['selected_variables'] = [
   '#type' => 'tableselect',
   '#header' => $header,
   '#options' => $options,
   '#empty' => t(''),
   '#weight' => 5,
 ];
 /*
  * variables }
  */
 $projection_options = [];
 foreach ($epsg as $key => $value) {
   global $epsg;
   if (!isset($value['description'])) {
     $projection_options[$key] = "No description found";
   }
   else {
     $projection_options[$key] = $value['description'];
   }
 }
 $form['projection'] = [
   '#type' => 'fieldset',
   '#title' => t('Select map projection'),
   '#weight' => 7,
   '#collapsible' => TRUE,
   '#collapsed' => FALSE,
   '#attributes' => [
     'class' => [
       'projection-fieldset',
     ],
     'msb-tooltip' => "Map projection",
   ],
 ];
 $form['projection']['selected_projection'] = [
   '#type' => 'select',
   '#title' => t('Projection'),
   '#options' => $projection_options,
   '#default_value' => "Original",
   '#description' => t(''),
   '#empty' => t(''),
   '#weight' => 7,
 ];
 /*
  * map region {
  */
 $form['projection'][] = [
   'x_axis_from' => [
     '#title' => t('x-axis from:'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     //'#default_value' => "-300000.0",
     // '#weight' => 11,
     '#element_validate' => $this->map_region_validate(),
     '#attributes' => [
       'placeholder' => t('Minimum value of x-coordinate'),
       'class' => [
         'map-region',
         'beautytips',
       ],
       'title' => "x-axis from",
     ],
   ],
   'x_axis_to' => [
     '#title' => t('x-axis to:'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     //'#default_value' => "1000000.0",
     '#element_validate' => $this->map_region_validate(),
     '#attributes' => [
       'placeholder' => t('Maximum value of x-coordinate'),
       'class' => [
         'map-region',
         'beautytips',
       ],
       'title' => "x-axis to",
     ],
   ],
   'y_axis_from' => [
     '#title' => t('y-axis from:'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     //'#default_value' => "-2000000.0",
     '#element_validate' => $this->map_region_validate(),
     '#attributes' => [
       'placeholder' => t('Minmum value of y-coordinate'),
       'class' => [
         'map-region',
         'beautytips',
       ],
       'title' => "y-axis from",
     ],
   ],
   'y_axis_to' => [
     '#title' => t('y-axis to:'),
     '#type' => 'textfield',
     //'#required' => TRUE,
     //'#default_value' => "-1000000.0",
     '#element_validate' => $this->map_region_validate(),
     '#attributes' => [
       'placeholder' => t('Maximum value of y-coordinate'),
       'class' => [
         'map-region',
         'beautytips',
       ],
       'title' => "y-axis to",
     ],
   ],
 ];
 /*
  * map region }
  */
 /*
  * interpolation {
  */

 $interpolations = [
   "nearestneighbor" => "nearestneighbor",
   "bilinear" => "bilinear",
   "bicubic" => "bicubic",
   "coord_nearestneighbor" => "coord_nearestneighbor",
   "coord_kdtree" => "coord_kdtree",
   "forward_max" => "forward_max",
   "forward_mean" => "forward_mean",
   "forward_median" => "forward_median",
   "forward_sum" => "forward_sum",
 ];
 $form['projection']['selected_interpolation'] = [
   '#type' => 'select',
   '#title' => t('Interpolation'),
   '#options' => $interpolations,
   //'#default_value' => "Original",
   '#description' => t(''),
   '#empty' => t(''),
   '#weight' => 7,
 ];
 /*
  * interpolation steps {
  */
 $form['projection']['steps'] = [
   '#type' => 'textfield',
   '#title' => t('Number of steps'),
   //'#required' => TRUE,
   //'#default_value' => "500",
   '#description' => t(''),
   '#empty' => t(''),
   '#element_validate' => $this->integer_validate(),
   '#attributes' => [
     'placeholder' => t('Number of point to interpolate to'),
     'class' => [
       'interpolation-steps',
       'beautytips',
     ],
     'title' => "Number of steps for interpolation",
   ],
 ];
 /*
  * interpolation steps }
  */
 /*
  * interpolation }
  */
 /*
  * projection }
  */

 /**
  * output file format{
  */
 /**
  * test{
  */
 if ($config->get('transformation_output_format_visible')) {
   $form['output_format'] = [
     '#type' => 'fieldset',
     '#title' => t('Select output format'),
     '#weight' => 8,
     '#collapsible' => TRUE,
     '#collapsed' => FALSE,
     '#attributes' => [
       'class' => [
         'output-format-fieldset',
       ],
       'msb-tooltip' => "Output file format",
     ],
   ];
   $output_file_formats = [
     "NetCDF" => "NetCDF",
     "NetCDF-4" => "NetCDF-4",
     "GeoTIFF" => "GeoTIFF",
   ];
   $form['output_format']['selected_output_format'] = [
     '#type' => 'select',
     '#title' => t('Output format'),
     '#options' => $output_file_formats,
     '#description' => t(''),
     '#empty' => t(''),
     '#weight' => 8,
   ];
 }


 /**
  * test}
  */
 /**
  * output file format}
  */
 /**
  * map test{
  * todo1
  * the map for transformation with fimex needs to implement proj4
  */
 //    $jquery_path = "/sites/all/modules/jquery_update/replace/jquery/1.10/jquery.min.js";
 //    $form['projection']['map'] = array(
 //      '#prefix' => ' ',
 //      '#markup' => adc_get_geographical_search_map(),
 //      '#suffix' => ' ',
 //            '#attached' => array(
 //        'js' => array(
 //          $jquery_path,
 //        ),
 //      ),
 //    );
 //    $form['#validate'][] = 'msb_all_or_none_latlon_validate';
 /**
  * map test}
  *//*
  *
  */;
 // $form['#validate'] = array('select_variables_validate');
 //$form['#submit'][] = 'metsis_fimex_submit';
 //$form['#submit'][] = 'metsis_fimex_extra_submit';
 $form['submit'] = [
   '#type' => 'submit',
   '#value' => t('Submit'),
   '#weight' => 13,
   // '#access' => FALSE,
 ];

 /**
  * back button{
  */
 $form['back_to_search'] = [
   '#weight' => 14,
   '#markup' => '<a href="' . $referer . '" class="adc-button adc-back">Back to results</a>',
 ];

 /**
  * back button}
  */
 /*
  * theme the form{
  * TODO
  */
 $form['#attached']['library'][] = 'metsis_lib/adc_buttons';
  $form['#attached']['library'][] = 'metsis_lib/tables';
 $form['#attached']['library'][] = 'metsis_fimex/fimex';
 /*
  * theme the form}
  */

 return $form;
}

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      foreach ($form_state->getValues() as $key => $value) {
        // @TODO: Validate fields.
      }
      parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('metsis_fimex');
    $tempstore->set('isSubmitted', true);
    $receipt = $this->adc_get_fimex_query($form_state);
    FimexUtils::adc_set_message($receipt);
    $form_state->setRebuild();
  }

  function metsis_fimex_extra_submit($form, &$form_state) {

    }

    function foo_valid() {
      \Drupal::messenger()->addWarning("TODO: foo validate");
    }

    function bar_valid() {

    }

    function select_variables_validate(array &$form, FormStateInterface $form_state) {
      /* var_dump($form['od_variables']['selected_variables']);
      if (!array_filter($form_state->get('selected_variables'))) {
         $form_state->setErrorByName('', $this->t('You must choose atleast one variable!'));
      }*/
    }

    function geographical_region_validate() {

    }

    function metsis_date_validate() {

    }

    function map_region_validate() {

    }

    function email_validate() {

    }

    function integer_validate() {

    }

    function get_wps_fimex_info($wps_fimex_url) {

    $res = null;
    try {
      $res = \Drupal::httpClient()->get($wps_fimex_url);

    }
    catch (RequestException $e) {

    }

      $data = new SimpleXMLElement($res->getBody());
      $data = Json::decode( Json::encode($data));
      return $data;
    }

    function create_transformation_order($form, &$form_state) {
      $user = \Drupal::currentUser();
      global $base_url;
      global $metsis_conf;
      if (DEBUG == 1) {
        $q = "";
        $qh = "";
        $qt = "";
        $qh .= "http://";
        $qh .= $metsis_conf['metsis_basket_server'];
        $qh .= ":";
        $qh .= $metsis_conf['metsis_basket_server_port'];
        $qh .= "/";
        $qh .= $metsis_conf['metsis_basket_server_service'];
        $qh .= "?";
        $qt .= "userId=";
        $qt .= $user->name;
        $qt .= "&email=";
        $qt .= $form_state['values']['email'];
        $qt .= "&site=";
        $qt .= $metsis_conf['drupal_site_data_center_desc'] ? $metsis_conf['drupal_site_data_center_desc'] : $base_url;
        $qt .= "&format=";
        $qt .= $metsis_conf['default_data_archive_format'] ? $metsis_conf['default_data_archive_format'] : "tgz";
        $qt .= "&uri=";
        $qt .= $form_state['values']['opendap'];
        $qt .= "&fiInterpolateProjString=";
        $qt .= urlencode(get_proj4_strings($form_state['values']['selected_projection']));
        $qt .= "&fiInterpolateMethod=";
        $qt .= $form_state['values']['selected_interpolation'];
        $qt .= "&fiSelectVariables=";
        $qt .= implode(",", array_filter($form_state['values']['selected_variables']));
        $qt .= "&fiReducetimeStart=";
        $qt .= $form_state['values']['start_date'];
        $qt .= "&fiReducetimeEnd=";
        $qt .= $form_state['values']['stop_date'];
        $qt .= "&fiInterpolateXAxisMin=";
        $qt .= $form_state['values']['x_axis_from'];
        $qt .= "&fiInterpolateXAxisMax=";
        $qt .= $form_state['values']['x_axis_to'];
        $qt .= "&fiInterpolateYAxisMin=";
        $qt .= $form_state['values']['y_axis_from'];
        $qt .= "&fiInterpolateYAxisMax=";
        $qt .= $form_state['values']['y_axis_to'];
        $qt .= "&fiInterpolateHorSteps=";
        $qt .= $form_state['values']['steps'];
        $qt .= "&fiOutputType=";
        $qt .= $form_state['values']['output_format'];
        $q .= $qh;
        $q .= $qt;
        \Drupal::logger('METSIS fimex query')->debug('<pre>' . print_r($q, TRUE) . '</pre>', []);
      }
    }

    function adc_get_fimex_query($form_state) {
      $user = \Drupal::currentUser();
      global $base_url;
      $config = \Drupal::config('metsis_fimex.settings');
      global $metsis_conf;
      global $epsg;
      $basket_wps_date_format = "Y-m-d H:i:s";
      if ($config->get('transformation_server_getcapabilities')) {
        $transformation_server_getcapabilities = $config->get('transformation_server_getcapabilities');
      }
      else {
        $transformation_server_getcapabilities = "";
      }
      $req_params_mandatory = [
        'wpsUrl' => $transformation_server_getcapabilities,
        'userId' => $user->getAccountName(),
        'email' => $form_state->getValue('email'),
        'site' =>  $base_url, //TODO: Should get this from config
        'format' => 'tgz', //TODO: Should get this from config
        'uri' => $form_state->getValue('opendap'),
        'fiSelectVariables' => implode(",", array_filter($form_state->getValue('selected_variables'))),
        'fiReducetimeStart' => MetsisUtils::get_metsis_date($form_state->getValue('start_date'), $basket_wps_date_format),
        'fiReducetimeEnd' => MetsisUtils::get_metsis_date($form_state->getValue('stop_date'), $basket_wps_date_format),
        'fiOutputType' => $form_state->getValue('selected_output_format'),
      ];
      //var_dump($req_params_mandatory);
      $req_params_mandatory['fiReduceboxNorth'] = $form_state->getValue('north');
      $req_params_mandatory['fiReduceboxSouth'] = $form_state->getValue('south');
      $req_params_mandatory['fiReduceboxEast'] = $form_state->getValue('east');
      $req_params_mandatory['fiReduceboxWest'] = $form_state->getValue('west');
      if (!empty($form_state->getValue('x_axis_from')) && !empty($form_state->getValue('x_axis_to')) && !empty($form_state->getValue('y_axis_from')) && !empty($form_state->getValue('y_axis_to')) && !empty($form_state->getValue('steps'))) {
        $req_params_projection = [
          'fiInterpolateProjString' => $epsg[$form_state->getValue('selected_projection')]['proj4string'],
          'fiInterpolateMethod' => $form_state->getValue('selected_interpolation'),
          'fiInterpolateXAxisMin' => $form_state->getValue('x_axis_from'),
          'fiInterpolateXAxisMax' => $form_state->getValue('x_axis_to'),
          'fiInterpolateYAxisMin' => $form_state->getValue('y_axis_from'),
          'fiInterpolateYAxisMax' => $form_state->getValue('y_axis_to'),
          'fiInterpolateHorSteps' => $form_state->getValue('steps'),
        ];
      }
      if (!empty($req_params_projection)) {
        $req_params = array_merge($req_params_projection, $req_params_mandatory);
      }
      else {
        $req_params = $req_params_mandatory;
      }
      $basket_config = \Drupal::config('metsis_basket.configuration');
      $basekt_server = $basket_config->get('metsis_basket_server');
      $basket_server_port =  $basket_config->get('metsis_basket_server_port');
      $basket_server_service = $basket_config->get('metsis_basket_server_service');

      return adc_basket_query($basekt_server, $basket_server_port, $basket_server_service, $req_params);
    }
}
