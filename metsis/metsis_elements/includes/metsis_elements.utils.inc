<?php

function elements_select_validate($form, &$form_state) {

}

function adc_elements_add_to_basket(&$form, &$form_state) {
  $user = \Drupal::currentUser();
  global $metsis_conf;
  \Drupal::logger('metsis_elements')->debug("Entering adc_elements_add_to_basket");
/*  if ($metsis_conf['authentication_required'] === 1) {
    adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
  }*/
  if (array_filter($form_state->getValue('table'))) {
    $basket_items = array_filter($form_state->getValue('table'));
    $fields = array("id", "_version_", METADATA_PREFIX . "last_metadata_update", METADATA_PREFIX . "personell_email", METADATA_PREFIX . "platform_long_name", METADATA_PREFIX . "data_center_contact_name", METADATA_PREFIX . "collection", METADATA_PREFIX . "geographic_extent_rectangle_east", METADATA_PREFIX . "geographic_extent_rectangle_south", METADATA_PREFIX . "geographic_extent_rectangle_west", METADATA_PREFIX . "geographic_extent_rectangle_north", METADATA_PREFIX . "data_center_data_center_url", METADATA_PREFIX . "platform_short_name", METADATA_PREFIX . "related_information_resource", METADATA_PREFIX . "project_long_name", METADATA_PREFIX . "data_access_resource", METADATA_PREFIX . "dataset_production_status", METADATA_PREFIX . "access_constraint", METADATA_PREFIX . "iso_topic_category", METADATA_PREFIX . "temporal_extent_start_date", METADATA_PREFIX . "temporal_extent_end_date", METADATA_PREFIX . "data_center_data_center_name_long_name", METADATA_PREFIX . "dataset_language", METADATA_PREFIX . "data_center_contact_role", METADATA_PREFIX . "data_access_type", METADATA_PREFIX . "project_short_name", METADATA_PREFIX . "abstract", METADATA_PREFIX . "activity_type", METADATA_PREFIX . "keywords_keyword", METADATA_PREFIX . "related_information_type", METADATA_PREFIX . "data_access_wms_layers_wms_layer", METADATA_PREFIX . "operational_status", METADATA_PREFIX . "instrument_long_name", METADATA_PREFIX . "personnel_organisation", METADATA_PREFIX . "data_center_contact_email", METADATA_PREFIX . "instrument_short_name", METADATA_PREFIX . "personnel_role", METADATA_PREFIX . "data_access_description", METADATA_PREFIX . "cloud_cover_value", METADATA_PREFIX . "metadata_identifier", METADATA_PREFIX . "data_center_data_center_name_short_name", METADATA_PREFIX . "metadata_status", METADATA_PREFIX . "personnel_name", METADATA_PREFIX . "title", "bbox",);
    $my_items = array();
    foreach ($basket_items as $bi) {
      $my_items[$bi] = msb_get_fields(SOLR_CORE_CHILD, $bi, $fields);
      foreach ($my_items[$bi] as $mibi) {
        if (isset($mibi['error'])) {
          \Drupal::messenger()->addError($mibi['error']['msg']);
        }
      } foreach ($my_items[$bi]['response']['docs'] as $doc) {
        if (h_adc_has_data_access_resource($doc) == 0) {

        } if (!isset($doc[METADATA_PREFIX . 'data_access_resource'])) {
          $message = $bi . " : " . MISSING_DATA_ACCESS_RESOURCE;
          \Drupal::messenger()->addWarning($message);
          unset($my_items[$bi]);
        }
        else {
          $doc[METADATA_PREFIX . 'data_access_resource'] = msb_concat_data_access_resource($doc[METADATA_PREFIX . 'data_access_resource']);
          $my_items[$bi] = $doc;
        }
      }
    } $basket_query = adc_get_basket_items($user->id(), 'metadata_identifier');
    $basket_query_results = $basket_query->fetchAllAssoc('metadata_identifier');
    $user_basket_content = [];
    foreach ($basket_query_results as $bqr) {
      $user_basket_content[] = $bqr->metadata_identifier;
    } $not_in_basket = array_diff_key($my_items, array_flip($user_basket_content));
    $my_items = $not_in_basket;
    metsis_basket_insert($my_items);
    if (count($my_items) > 0) {
      // @FIXME
// theme() has been renamed to _theme() and should NEVER be called directly.
// Calling _theme() directly can alter the expected output and potentially
// introduce security issues (see https://www.drupal.org/node/2195739). You
// should use renderable arrays instead.
//
//
// @see https://www.drupal.org/node/2195739
//\Drupal::messenger()->addMessage(t('The following <b>' . count($my_items) . '</b> datasets have been added to your basket:!values', array('!values' => theme('item_list', array('items' => array_keys($my_items))))));
\Drupal::messenger()->addMessage(t('The following <b>' . count($my_items) . '</b> datasets have been added to your basket'));
    }
    else {
      \Drupal::messenger()->addMessage(t('No new datasets to place in basket.'));
    }
  }
}


/**
 * @FIXME: Redirect back to search
 */

function adc_go_to_calling_results_page() {
  $crp_url = metsis_get_session_var('calling_results_page');
  metsis_del_session_var('calling_results_page');
  drupal_goto($crp_url);
  return;
}
