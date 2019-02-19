<?php

/**
 * metsis_basket_insert{
 */
function metsis_basket_insert($basket_object) {

    /**
     * test{
     * For now we do not allow datasets that have child datasets 
     * to be added. The user is require to inspect that child datasets and 
     * choose a subset
     * 0. this is too late in the process to check for level 2. has to be done earlier
     * 1. filter out those datasets that have related_datasets
     * 2. warn the user that we have done so and that these selections have not
     *    been added to basket
     * 3. tell the user to inspect the child/level 2 data and choose
     * 
     */
    $cores_basket_object = adc_get_solr_core(array_keys($basket_object));
    //sdpm($cores_basket_object);
    /**
     * test}
     */
    //sdpm($basket_object);
    global $metsis_conf;
    /**
     * test{
     */
    //if ($metsis_conf['authentication_required'] === TRUE) {
    if ($metsis_conf['authentication_required'] === 1) {
        adc_require_login(drupal_get_destination(), $metsis_conf['authentication_default_message']);
    }
    /**
     * test}
     */
    $user_id = (int) $GLOBALS['user']->uid;
    $user_name = $GLOBALS['user']->name;

    foreach ($basket_object as $dataset) {

        /**
         * todo 0
         * hack to get around inconsistent naming of fields - should be cleaned up{
         */
        if (isset($dataset[METADATA_PREFIX . 'metadata_identifier'])) {
            $dataset_id = $dataset[METADATA_PREFIX . 'metadata_identifier'];
        }
        elseif (isset($dataset[METADATA_PREFIX . 'dataset_id'])) {
            $dataset_id = $dataset[METADATA_PREFIX . 'dataset_id'];
        }
        elseif (isset($dataset[METADATA_PREFIX . 'id'])) {
            $dataset_id = $dataset[METADATA_PREFIX . 'id'];
        }
        elseif (isset($dataset['id'])) {
            $dataset_id = $dataset['id'];
        }
        else {
            //todo
            //this must be made conditional on whether the metadata is 
            //optional or not according to the MMD spec documents

            $dataset_id = MISSING_METADATA;
        }
        /*
         * hack to get around inconsistent naming of fields - should be cleaned up}
         */
        $solr_id = isset($dataset['solr_id']) ? $dataset['solr_id'] : MISSING_METADATA;
        $_version_ = isset($dataset['_version_']) ? $dataset['_version_'] : MISSING_METADATA;
        //
        $last_metadata_update = isset($dataset[METADATA_PREFIX . 'last_metadata_update']) ? $dataset[METADATA_PREFIX . 'last_metadata_update'] : MISSING_METADATA;
        $personell_email = isset($dataset[METADATA_PREFIX . 'personnel_email']) ? $dataset[METADATA_PREFIX . 'personnel_email'] : MISSING_METADATA;
        $platform_long_name = isset($dataset[METADATA_PREFIX . 'platform_long_name']) ? $dataset[METADATA_PREFIX . 'platform_long_name'] : MISSING_METADATA;
        $data_center_contact_name = isset($dataset[METADATA_PREFIX . 'data_center_contact_name']) ? $dataset[METADATA_PREFIX . 'data_center_contact_name'] : MISSING_METADATA;
        $collection = isset($dataset[METADATA_PREFIX . 'collection']) ? $dataset[METADATA_PREFIX . 'collection'] : MISSING_METADATA;
        $geographic_extent_rectangle_east = isset($dataset[METADATA_PREFIX . 'geographic_extent_rectangle_east']) ? $dataset[METADATA_PREFIX . 'geographic_extent_rectangle_east'] : MISSING_METADATA;
        $geographic_extent_rectangle_south = isset($dataset[METADATA_PREFIX . 'geographic_extent_rectangle_south']) ? $dataset[METADATA_PREFIX . 'geographic_extent_rectangle_south'] : MISSING_METADATA;
        $geographic_extent_rectangle_west = isset($dataset[METADATA_PREFIX . 'geographic_extent_rectangle_west']) ? $dataset[METADATA_PREFIX . 'geographic_extent_rectangle_west'] : MISSING_METADATA;
        $geographic_extent_rectangle_north = isset($dataset[METADATA_PREFIX . 'geographic_extent_rectangle_north']) ? $dataset[METADATA_PREFIX . 'geographic_extent_rectangle_north'] : MISSING_METADATA;
        $data_center_data_center_url = isset($dataset[METADATA_PREFIX . 'data_center_data_center_url']) ? $dataset[METADATA_PREFIX . 'data_center_data_center_url'] : MISSING_METADATA;
        $platform_short_name = isset($dataset[METADATA_PREFIX . 'platform_short_name']) ? $dataset[METADATA_PREFIX . 'platform_short_name'] : MISSING_METADATA;
        $related_information_resource = isset($dataset[METADATA_PREFIX . 'related_information_resource']) ? $dataset[METADATA_PREFIX . 'related_information_resource'] : MISSING_METADATA;
        $project_long_name = isset($dataset[METADATA_PREFIX . 'project_long_name']) ? $dataset[METADATA_PREFIX . 'project_long_name'] : MISSING_METADATA;
        $data_access_resource_HTTP = isset($dataset[METADATA_PREFIX . 'data_access_resource']['HTTP']['url']) ? $dataset[METADATA_PREFIX . 'data_access_resource']['HTTP']['url'] : MISSING_METADATA;
        $data_access_resource_OPeNDAP = isset($dataset[METADATA_PREFIX . 'data_access_resource']['OPenDAP']['url']) ? $dataset[METADATA_PREFIX . 'data_access_resource']['OPenDAP']['url'] : MISSING_METADATA;
        $data_access_resource_OGC_WMS = isset($dataset[METADATA_PREFIX . 'data_access_resource']['OGC_WMS']['url']) ? $dataset[METADATA_PREFIX . 'data_access_resource']['OGC_WMS']['url'] : MISSING_METADATA;
//        $data_access_resource_HTTPserver = isset($dataset[METADATA_PREFIX . 'data_access_resource']['HTTPserver']['url']) ? $dataset[METADATA_PREFIX . 'data_access_resource']['HTTPserver']['url'] : MISSING_METADATA;
        $data_access_resource_ODATA = isset($dataset[METADATA_PREFIX . 'data_access_resource']['ODATA']['url']) ? $dataset[METADATA_PREFIX . 'data_access_resource']['ODATA']['url'] : MISSING_METADATA;
        $dataset_production_status = isset($dataset[METADATA_PREFIX . 'dataset_production_status']) ? $dataset[METADATA_PREFIX . 'dataset_production_status'] : MISSING_METADATA;
        $access_constraint = isset($dataset[METADATA_PREFIX . 'access_constraint']) ? $dataset[METADATA_PREFIX . 'access_constraint'] : NOT_APPLICABLE_METADATA;
        $iso_topic_category = isset($dataset[METADATA_PREFIX . 'iso_topic_category']) ? $dataset[METADATA_PREFIX . 'iso_topic_category'] : MISSING_METADATA;
        $temporal_extent_start_date = isset($dataset[METADATA_PREFIX . 'temporal_extent_start_date']) ? $dataset[METADATA_PREFIX . 'temporal_extent_start_date'] : "";
        $temporal_extent_end_date = isset($dataset[METADATA_PREFIX . 'temporal_extent_end_date']) ? $dataset[METADATA_PREFIX . 'temporal_extent_end_date'] : "";
        $data_center_data_center_name_long_name = isset($dataset[METADATA_PREFIX . 'data_center_data_center_name_long_name']) ? $dataset[METADATA_PREFIX . 'data_center_data_center_name_long_name'] : MISSING_METADATA;
        $dataset_language = isset($dataset[METADATA_PREFIX . 'dataset_language']) ? $dataset[METADATA_PREFIX . 'dataset_language'] : MISSING_METADATA;
        $data_center_contact_role = isset($dataset[METADATA_PREFIX . 'data_center_contact_role']) ? $dataset[METADATA_PREFIX . 'data_center_contact_role'] : MISSING_METADATA;
        $data_access_type = isset($dataset[METADATA_PREFIX . 'data_access_type']) ? $dataset[METADATA_PREFIX . 'data_access_type'] : array(MISSING_METADATA);
        $project_short_name = isset($dataset[METADATA_PREFIX . 'project_short_name']) ? $dataset[METADATA_PREFIX . 'project_short_name'] : MISSING_METADATA;
        $abstract = isset($dataset[METADATA_PREFIX . 'abstract']) ? $dataset[METADATA_PREFIX . 'abstract'] : MISSING_METADATA;
        $activity_type = isset($dataset[METADATA_PREFIX . 'activity_type']) ? $dataset[METADATA_PREFIX . 'activity_type'] : array(MISSING_METADATA);
        $keywords_keyword = isset($dataset[METADATA_PREFIX . 'keywords_keyword']) ? $dataset[METADATA_PREFIX . 'keywords_keyword'] : MISSING_METADATA;
        $related_information_type = isset($dataset[METADATA_PREFIX . 'related_information_type']) ? $dataset[METADATA_PREFIX . 'related_information_type'] : array(MISSING_METADATA);
        $data_access_wms_layers_wms_layer = isset($dataset[METADATA_PREFIX . 'data_access_wms_layers_wms_layer']) ? $dataset[METADATA_PREFIX . 'data_access_wms_layers_wms_layer'] : array(MISSING_METADATA);
        $operational_status = isset($dataset[METADATA_PREFIX . 'operational_status']) ? $dataset[METADATA_PREFIX . 'operational_status'] : MISSING_METADATA;
        $instrument_long_name = isset($dataset[METADATA_PREFIX . 'instrument_long_name']) ? $dataset[METADATA_PREFIX . 'instrument_long_name'] : array(MISSING_METADATA);
        $personnel_organisation = isset($dataset[METADATA_PREFIX . 'personnel_organisation']) ? $dataset[METADATA_PREFIX . 'personnel_organisation'] : MISSING_METADATA;
        $data_center_contact_email = isset($dataset[METADATA_PREFIX . 'data_center_contact_email']) ? $dataset[METADATA_PREFIX . 'data_center_contact_email'] : MISSING_METADATA;
        $instrument_short_name = isset($dataset[METADATA_PREFIX . 'instrument_short_name']) ? $dataset[METADATA_PREFIX . 'instrument_short_name'] : array(MISSING_METADATA);
        $personnel_role = isset($dataset[METADATA_PREFIX . 'personnel_role']) ? $dataset[METADATA_PREFIX . 'personnel_role'] : "";
        $data_access_description = isset($dataset[METADATA_PREFIX . 'data_access_description']) ? $dataset[METADATA_PREFIX . 'data_access_description'] : array(MISSING_METADATA);
        $cloud_cover_value = isset($dataset[METADATA_PREFIX . 'cloud_cover_value']) ? $dataset[METADATA_PREFIX . 'cloud_cover_value'] : -1.0;
        $metadata_identifier = isset($dataset[METADATA_PREFIX . 'metadata_identifier']) ? $dataset[METADATA_PREFIX . 'metadata_identifier'] : MISSING_METADATA;
        $data_center_data_center_name_short_name = isset($dataset[METADATA_PREFIX . 'data_center_data_center_name_short_name']) ? $dataset[METADATA_PREFIX . 'data_center_data_center_name_short_name'] : MISSING_METADATA;
        $metadata_status = isset($dataset[METADATA_PREFIX . 'metadata_status']) ? $dataset[METADATA_PREFIX . 'metadata_status'] : MISSING_METADATA;
        $personnel_name = isset($dataset[METADATA_PREFIX . 'personnel_name']) ? $dataset[METADATA_PREFIX . 'personnel_name'] : MISSING_METADATA;
        $title = isset($dataset[METADATA_PREFIX . 'title']) ? $dataset[METADATA_PREFIX . 'title'] : MISSING_METADATA;
        $bbox = isset($dataset['bbox']) ? $dataset['bbox'] : MISSING_METADATA;


        $fields = array();
        $fields['uid'] = $user_id;
        $fields['user_name'] = $user_name;
        $fields['session_id'] = session_id();
        $fields['basket_timestamp'] = time();
        /**
         * test{
         */
        $fields['solr_core'] = adc_get_solr_core(array($metadata_identifier))[$metadata_identifier];
        /**
         * test}
         */
        $fields['solr_id'] = $solr_id;
        $fields['_version_'] = $_version_;
        //        
        $fields['last_metadata_update'] = adc_date_to_int($last_metadata_update);
        $fields['personell_email'] = $personell_email[0];
        $fields['platform_long_name'] = $platform_long_name[0];
        $fields['data_center_contact_name'] = $data_center_contact_name;
        $fields['collection'] = $collection[0];
        $fields['geographic_extent_rectangle_east'] = $geographic_extent_rectangle_east;
        $fields['geographic_extent_rectangle_south'] = $geographic_extent_rectangle_south;
        $fields['geographic_extent_rectangle_west'] = $geographic_extent_rectangle_west;
        $fields['geographic_extent_rectangle_north'] = $geographic_extent_rectangle_north;
        $fields['data_center_data_center_url'] = $data_center_data_center_url;
        $fields['platform_short_name'] = $platform_short_name[0];
        $fields['related_information_resource'] = $related_information_resource[0];
        $fields['project_long_name'] = $project_long_name[0];
        $fields['data_access_resource_HTTP'] = $data_access_resource_HTTP;
        $fields['data_access_resource_OPeNDAP'] = $data_access_resource_OPeNDAP;
        $fields['data_access_resource_OGC_WMS'] = $data_access_resource_OGC_WMS;
//        $fields['data_access_resource_HTTPserver'] = $data_access_resource_HTTPserver;
        $fields['data_access_resource_ODATA'] = $data_access_resource_ODATA;
        $fields['dataset_production_status'] = $dataset_production_status;
        $fields['access_constraint'] = $access_constraint;
        // TODO 7
        // implode is getting wrong argument for datasets that do not define
        //mmd_iso_topic_category. put in checks for this ...
        if ($iso_topic_category != MISSING_METADATA) {
            $fields['iso_topic_category'] = implode(",", $iso_topic_category);
        }

        if ($temporal_extent_start_date != "") {
            $fields['temporal_extent_start_date'] = adc_date_to_int($temporal_extent_start_date);
        }
        if ($temporal_extent_end_date != "") {
            $fields['temporal_extent_end_date'] = adc_date_to_int($temporal_extent_end_date);
        }
        $fields['data_center_data_center_name_long_name'] = $data_center_data_center_name_long_name;
        $fields['dataset_language'] = $dataset_language;
        $fields['data_center_contact_role'] = $data_center_contact_role;
        $fields['data_access_type'] = implode(",", $data_access_type);
        $fields['project_short_name'] = $project_short_name;
        $fields['abstract'] = implode(",", $abstract);
        $fields['activity_type'] = implode(",", $activity_type);
        $fields['keywords_keyword'] = implode(",", $keywords_keyword);
        $fields['related_information_type'] = implode(",", $related_information_type);
        $fields['data_access_wms_layers_wms_layer'] = implode(",", $data_access_wms_layers_wms_layer);
        $fields['operational_status'] = $operational_status;
        $fields['instrument_long_name'] = implode(",", $instrument_long_name);
        $fields['personnel_organisation'] = $personnel_organisation[0];
        $fields['data_center_contact_email'] = $data_center_contact_email;
        $fields['instrument_short_name'] = implode(",", $instrument_short_name);
        if ($personnel_role != "") {
            $fields['personnel_role'] = implode(",", $personnel_role);
        }
        $fields['data_access_description'] = implode(",", $data_access_description);
        $fields['cloud_cover_value'] = $cloud_cover_value;
        $fields['metadata_identifier'] = $dataset_id;
        $fields['data_center_data_center_name_short_name'] = $data_center_data_center_name_short_name;
        $fields['metadata_status'] = $metadata_status;
        $fields['personnel_name'] = $personnel_name[0];
        $fields['title'] = implode(",", $title);
        $fields['bbox'] = $bbox;
        //
        /**
         * test{
         */
        //TODO 7
        // should probably reshape the $fields array to ensure that each element
        // is a simple string. 
//        sdpm($fields);
//        foreach ($fields as $f) {
//            if (is_array($f)) {
//                sdpm($f);
//            }
//        }
        /**
         * test}
         */
        $query = db_insert('metsis_basket')

            //$uid = db_insert('metsis_basket')
            ->fields($fields
            )
            ->execute();
    }
}

/**
 * metsis_basket_insert}
 */

/**
 * get_user_item_count{
 */
//todo9 refactor to use adc_get_basket_items($user_id,$field)
function get_user_item_count($user_id) {
    $query = db_select('metsis_basket', 'm');
    $query->fields('m', array('iid'));
    $query->condition('m.uid', $user_id, '=');
    $query->execute();
    $result = $query->execute();
    return $result->rowCount();
}

/*
 * get_user_item_count}
 */

/**
 * adc_get_basket_items($user_id,$field){
 */
function adc_get_basket_items($user_id, $field) {
    $query = db_select('metsis_basket', 'm');
    $query->fields('m', array($field));
    $query->condition('m.uid', $user_id, '=');
    $query->execute();
    $result = $query->execute();
    return $result;
}

/*
 * get_user_item_count}
 */

/*
 * adc_basket_query{
 */

function adc_basket_query($server, $port, $service, $req_params) {
    //sdpm($req_params);
    $con = new HttpConnection($server, $port);
    $res = $con->get($service, $req_params);
    $res_body = new SimpleXMLElement($res['body']);
    $jres_body = drupal_json_decode(drupal_json_encode($res_body));
    return $jres_body;
}

/*
 * adc_basket_query}
 */