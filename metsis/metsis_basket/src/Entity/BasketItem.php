<?php

namespace Drupal\metsis_basket\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

use Drupal\metsis_basket\BasketItemInterface;

/**
 * Defines the metsis basket entity.
 *
 * @ingroup metsis_basket
 *
 * @ContentEntityType(
 *  id = "metsis_basket_item",
 *  label = @Translation("Metsis Basket Item"),
 *  handlers = {
 *    "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *    "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *    "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *    "views_data" = "Drupal\metsis_basket\BasketItemViewsData",
 *    "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *    "storage_schema" = "Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema",
 *    "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *    "form" = {
 *      "default" = "Drupal\Core\Entity\ContentEntityForm",
 *      "add" = "Drupal\Core\Entity\ContentEntityForm",
 *      "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *    },
 *  },
 *  base_table = "metsis_basket",
 *  admin_permission = "administer contact entity",
 *  fieldable = TRUE,
 *  entity_keys = {
 *    "id" = "iid",
 *    "uuid" = "uuid",
 *    "label" = "title",
 *   },
 * )
 */
class BasketItem extends ContentEntityBase implements BasketItemInterface {

  /**
   * Field definitions basket.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['iid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('IID'))
      ->setDescription(t('The primary identifier for a METSIS metsis_basket item (item id)'))
      ->setReadOnly(TRUE);

    // Internal UUID field for entity.
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the BasketItem entity.'))
      ->setReadOnly(TRUE);

    // Contains the Userid UID.
    $fields['uid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('UID'))
      ->setDescription(t('Currently authenticated Drupal user ID.'))
      ->setReadOnly(TRUE);

    // Solr core name.
    $fields['solr_core'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Solr core name'))
      ->setDescription(t("The name of the solr core"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 256,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // User name field.
    $fields['user_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('User name'))
      ->setDescription(t("Drupal user name"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Title field.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t("The title of the dataset. The language in the title is specificed in the xml:lang attribute. To be compatible with DIF the title cannot be longer than 220 characters. Required. Repeat - Yes, but each repetition should have a different language."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Session id field.
    $fields['session_id'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Session ID'))
      ->setDescription(t("Drupal session id"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Solr id field.
    $fields['solr_id'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Solr id'))
      ->setDescription(t("Solr id"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Solr _version_ field.
    $fields['_version_'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('SOLR _version_'))
      ->setDescription(t("Solr _version_ field"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Last last_metadata_update field.
    $fields['last_metadata_update'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Last metadata update'))
      ->setDescription(t("The last update of the metadata record. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 11,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // personnel_email field.
    $fields['personell_email'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Personnel Email'))
      ->setDescription(t("The email address to the contact. not required. repeat, but we only recommend having a single contact per role"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // platform_long_name field.
    $fields['platform_long_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Platform long name'))
      ->setDescription(t("The full name of the platform used to acquire the data. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_center_contact_name field.
    $fields['data_center_contact_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center contact name'))
      ->setDescription(t("The data center contact person. See /mmd/contact for more information. The role of a data center contact must be 'Data center contact'. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // Collection field.
    $fields['collection'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Collection'))
      ->setDescription(t("
      The purpose of this tag is the same as for the ownertag in XMD files. It is used to identify which collection a dataset belong to. This is used to identify sets when serving metadata
      through e.g. OAI-PMH or to identify which data to present in e.g. a project specific portal when all metadata records are in the same repository. The keyword used to identify the
      collection should be short (e.g. NMDC, NMAP, SIOS, ...). See Collection keywords for details.
      Required. Repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // geographic_extent_rectangle_east.
    $fields['geographic_extent_rectangle_east'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Geographic extent east'))
      ->setDescription(t("The easternmost point covered by the dataset. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // geographic_extent_rectangle_south.
    $fields['geographic_extent_rectangle_south'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Geographic extent south'))
      ->setDescription(t("The southernmost point covered by the dataset. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // geographic_extent_rectangle_west.
    $fields['geographic_extent_rectangle_west'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Geographic extent west'))
      ->setDescription(t("The westernmost point covered by the dataset. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // geographic_extent_rectangle_north.
    $fields['geographic_extent_rectangle_north'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Geographic extent north'))
      ->setDescription(t("The northernmost point covered by the dataset. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_center_data_center_url.
    $fields['data_center_data_center_url'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center URL'))
      ->setDescription(t("URL to the data center's main website. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // platform_short_name.
    $fields['platform_short_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Platform short name'))
      ->setDescription(t("The abbreviated name of the platform used to acquire the data. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // related_information_resource.
    $fields['related_information_resource'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Related information resource'))
      ->setDescription(t("Description of how to access the data in the dataset. This element has the following child elements. The URL used. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // project_long_name.
    $fields['project_long_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Project long name'))
      ->setDescription(t("Project where the dataset was generated or collected. long_name: is the full name of the project from which the data were collected. not required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_access_resource_http.
    $fields['data_access_resource_http'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data access HTTP resource'))
      ->setDescription(t("Direct access to the full data file. May require authentication, but should point directly to the data file or a catalogue containing the data."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_access_resource_opendap.
    $fields['data_access_resource_opendap'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data access OpENDAP resource'))
      ->setDescription(t("Open-source Project for a Network Data Access Protocol."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_access_resource_ogc_wms.
    $fields['data_access_resource_ogc_wms'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data access OGC WMS resource'))
      ->setDescription(t("OGC Web Mapping Service, URI to GetCapabilities Document."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_access_resource_odata.
    $fields['data_access_resource_odata'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Direct data access resource'))
      ->setDescription(t("URI for direct access to dataset file"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // dataset_production_status.
    $fields['dataset_production_status'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Dataset production status'))
      ->setDescription(t("See 5.2 in MMD spec"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // access_constraint.
    $fields['access_constraint'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Access contraint'))
      ->setDescription(t("Limitations on the access to the dataset. See 5.4 of MMD spec for a list of valid values. not required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // iso_topic_category.
    $fields['iso_topic_category'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('ISO Topic Category'))
      ->setDescription(t("ISO topic category fetched from a controlled vocabulary. Valid keywords are listed in ISO Topic categories. This field is required for compatibility with DIF and ISO. required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // temporal_extent_start_date.
    $fields['temporal_extent_start_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Temporal extent start date'))
      ->setDescription(t("The start date for data collection or model coverage. required. repeat - Repetition is used when there are gaps in the dataset."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // temporal_extent_end_date.
    $fields['temporal_extent_end_date'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Temporal extent end date'))
      ->setDescription(t("The end date for data collection or model coverage. If the dataset is not complete, the end_date element is left empty. required. repeat - Repetition is used when there are gaps in the dataset."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_center_data_center_name_long_name.
    $fields['data_center_data_center_name_long_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center long name'))
      ->setDescription(t("Description about the datacenter responsible for the distribution of the datasaet. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // dataset_language.
    $fields['dataset_language'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Dataset language'))
      ->setDescription(t("The language used in production, storage etc. of the dataset. The default for all datasets is English. not required. no repeat"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_center_contact_role.
    $fields['data_center_contact_role'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center contact role'))
      ->setDescription(t("The data center contact person. See /mmd/contact for more information. The role of a data center contact must be 'Data center contact'. not required. repeat"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_access_type.
    $fields['dar'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Data access resources as properties'))
      ->setDescription(t("See section 5.14 MMD spec."));
    /*    ->setSettings(array(
    'default_value' => '',
    'max_length' => 4096,
    'text_processing' => 0,
    ))
    ->setDisplayOptions('view', array(
    'label' => 'above',
    'type' => 'string',
    'weight' => -5,
    ))
    ->setDisplayOptions('form', array(
    'type' => 'string_textfield',
    'weight' => -5,
    ))
    ->setDisplayConfigurable('form', TRUE)
    ->setDisplayConfigurable('view', TRUE ); */

    // project_short_name.
    $fields['feature_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Feature type'))
      ->setDescription(t("The abbreviated name of the project from which the data were collected. not required. no repeat"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Abstract.
    $fields['abstract'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Abstract'))
      ->setDescription(t("he abstract should summarize and described the dataset. The following guidelines for a good abstract follows (from DIF): Capitalization should follow standard constructs. For readability, all capital letters or all lower case letters should not be used. Use the appropriate case where applicable. Acronyms should be expanded to provide understanding. Where applicable, the abstract should also include brief statements on the following information: Data processing     information (gridded, binned, swath, raw, algorithms used, necessary ancillary data sets). Date available. Data set organization (description of how data are organized within and by file). Scientific methodology or analytical tools. Time gaps in data set coverage. Units and unit resolution. Similarities and differences of these data to other closely-related data sets. Other pertinent information. . Required. Repeat - Yes, but each repetition should have a different language."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 8192,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // activity_type.
    $fields['activity_type'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Activity type'))
      ->setDescription(t("The activity used to collect the data. Valid keywords are listed in Activity type. not required. repeat"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // keywords_keyword.
    $fields['keywords_keyword'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Keywords'))
      ->setDescription(t("Unclear in MMD spec section 3.20. A single keyword describing the dataset. This can be split  hierarchically like GCMD, but does not have to be split. required. repeat - It is expected that different keyword elements have different vocabulary child elements."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // related_information_type.
    $fields['related_information_type'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Related information type'))
      ->setDescription(t("Description of the type of information. not required. repeat"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_access_wms_layers_wms_layer.
    $fields['data_access_wms_layers_wms_layer'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data access WMS layers'))
      ->setDescription(t("List of WMS layers available"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // operational_status.
    $fields['operational_status'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Operational status'))
      ->setDescription(t("The current operational status of the product. Valid keywords are listed in Operational status. not required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // instrument_long_name.
    $fields['instrument_long_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Instrument long name'))
      ->setDescription(t("The full name of the instrument used to acquire the data. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // personnel_organisation.
    $fields['personnel_organisation'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Personnel organisation'))
      ->setDescription(t("The name of the organisation where the person is employed. not required. repeat - yes, but we only recommend having a single contact per role."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_center_contact_email.
    $fields['data_center_contact_email'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center contact email'))
      ->setDescription(t("The name of the organisation where the person is employed. not required. repeat - yes, but we only recommend having a single contact per role."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // instrument_short_name.
    $fields['instrument_short_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Instrument short name'))
      ->setDescription(t("The abbreviated name of the instrument used to acquire the data. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // personnel_role.
    $fields['personnel_role'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Personnel role'))
      ->setDescription(t("The role the person has related to this dataset. The value should come from Contact roles. not required. repeat - Yes, but we only recommend having a single contact per role."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // data_access_description.
    $fields['data_access_description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data access description'))
      ->setDescription(t("Textual description about the access mechanism. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // cloud_cover_value.
    $fields['cloud_cover_value'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Cloud cover value'))
      ->setDescription(t("The actual cloud cover in percentage of the valid pixels. This is indicated with one attribute. not required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['metadata_identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Metadata Identifier'))
      ->setDescription(t("Unique identifier for the dataset described by the metadata document. This identifier is used to identify a dataset across different systems. required. no repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // data_center_data_center_name_short_name.
    $fields['data_center_data_center_name_short_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data center short name'))
      ->setDescription(t("Description about the datacenter responsible for the distribution of the datasaet. not required. repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    // metadata_status.
    $fields['metadata_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Metadata status'))
      ->setDescription(t("Status for the metadata record. This is configuration metadata and should not be misinterpreted as dataset_production_status. The only purpose of this tag is to determine whether the dataset should be indexed or not. Required. No repeat."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 256,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // personnel_name.
    $fields['personnel_name'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Personnel role'))
      ->setDescription(t("The full name of the relevant contact person for the dataset."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // personnel_name.
    $fields['bbox'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('BoundingBox'))
      ->setDescription(t("Geographic extent BoundingBox"))
      ->setSettings([
        'default_value' => '',
        'max_length' => 4096,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Contains the Userid UID.
    $fields['node_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Node ID'))
      ->setDescription(t('A Drupal node related to this record.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 11,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'snumber',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['basket_timestamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Basket Timestamp'))
      ->setDescription(t("Timestamp for when item was added to basket."))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'date',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    return $fields;
  }

}
