<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StorageInformationForm.
 */
class StorageInformationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'storage_information_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fields = NULL) {
    if (isset($fields['storage_information_file_name'])) {
      $form['file_name'] = [
        '#type' => 'item',
        '#title' => $this->t('File name:'),
        '#markup' => $fields['storage_information_file_name'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    if (isset($fields['storage_information_file_location'])) {
      $form['file_location'] = [
        '#type' => 'item',
        '#title' => $this->t('File location:'),
        '#markup' => $fields['storage_information_file_location'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    if (isset($fields['storage_information_file_format'])) {
      $form['file_format'] = [
        '#type' => 'item',
        '#title' => $this->t('File format:'),
        '#markup' => $fields['storage_information_file_format'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }
    if (isset($fields['storage_information_file_size_unit'])) {
      $form['file_size_unit'] = [
        '#type' => 'item',
        '#title' => $this->t('File size unit:'),
        '#markup' => $fields['storage_information_file_size_unit'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }
    if (isset($fields['storage_information_file_size'])) {
      $form['file_size'] = [
        '#type' => 'item',
        '#title' => $this->t('File size:'),
        '#markup' => $fields['storage_information_file_size'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }
    if (isset($fields['storage_information_file_checksum_type'])) {
      $form['file_checksum_type'] = [
        '#type' => 'item',
        '#title' => $this->t('File checksum type:'),
        '#markup' => $fields['storage_information_file_checksum_type'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    if (isset($fields['storage_information_file_checksum'])) {
      $form['file_checksum'] = [
        '#type' => 'item',
        '#title' => $this->t('File checksum:'),
        '#markup' => $fields['storage_information_file_checksum'],
        '#allowed_tags' => ['a', 'strong'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @todo Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
