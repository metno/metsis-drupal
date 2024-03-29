<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Show the diferrnet personnele in a horizontal tab.
 */
class PersonnelForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'personnel_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fields = NULL) {
    $form['personnel'] = [
      '#type' => 'horizontal_tabs',
        // '#tree' => true,.
      '#default_tab' => 'investigator',
    ];
    foreach ($fields['personnel_role'] as $role) {
      $role_tag = str_replace(' ', '_', strtolower($role));
      $form[$role_tag] = [
        '#type' => 'details',
        '#title' => $this->t('@role', ['@role' => $role]),
        '#weight' => '0',
        '#group' => 'personnel',
        '#attributes' => [
          'personnel' => 'yes',
        ],
      ];
    }

    if (isset($fields['personnel_investigator_name'])) {
      $i = 0;
      foreach ($fields['personnel_investigator_name'] as $investigator) {
        $form['investigator'][$i] = [
          '#prefix' => '<div class="w3-card-2 w3-margin w3-padding">',
          '#suffix' => '</div>',
        ];
        $form['investigator'][$i]['avatar'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="w3-center">',
          '#markup' => '<i class=" fa fa-user fa-2x" aria-hidden="true"></i>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['i'],
        ];
        $form['investigator'][$i]['name'] = [
          '#type' => 'item',
          '#title' => $this->t('Name:'),
          '#markup' => $investigator,
        // '#suffix' => '</p>',.
        ];
        $form['investigator'][$i]['email'] = [
          '#type' => 'item',
          '#title' => $this->t('Email:'),
        // '#prefix' => '<p>',.
          '#markup' => '<a href="mailto://' . $fields['personnel_investigator_email'][$i] . '">' . $fields['personnel_investigator_email'][$i] . '</a>',
          '#attributes' => [
            'class' => ['contact-email'],
          ],
          // '#suffix' => '</p>',.
        ];
        $personnel_org = $fields['personnel_investigator_organisation'] ?? NULL;
        if (!NULL == $personnel_org) {
          if (array_key_exists($i, $fields['personnel_investigator_organisation'])) {
            $form['investigator'][$i]['org'] = [
              '#type' => 'item',
              '#title' => $this->t('Organization:'),
            // '#prefix' => '<p>',.
              '#markup' => $fields['personnel_investigator_organisation'][$i],
            // '#suffix' => '</div>',.
            ];
          }
        }
        $i++;
      }
    }

    if (isset($fields['personnel_metadata_author_name'])) {
      $i = 0;
      foreach ($fields['personnel_metadata_author_name'] as $author) {
        $form['metadata_author'][$i] = [
          '#prefix' => '<div class="w3-card-2 w3-margin w3-padding">',
          '#suffix' => '</div>',
        ];
        $form['metadata_author'][$i]['avatar'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="w3-center">',
          '#markup' => '<i class="fa fa-user fa-2x" aria-hidden="true"></i>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['i'],
        ];
        $form['metadata_author'][$i]['name'] = [
          '#type' => 'item',
          '#title' => $this->t('Name:'),
          '#markup' => $author,
        // '#suffix' => '</p>',.
        ];
        $form['metadata_author'][$i]['email'] = [
          '#type' => 'item',
          '#title' => $this->t('Email:'),
        // '#prefix' => '<p>',.
          '#markup' => '<a href="mailto://' . $fields['personnel_metadata_author_email'][$i] . '">' . $fields['personnel_metadata_author_email'][$i] . '</a>',
          '#attributes' => [
            'class' => ['contact-email'],
          ],
          // '#suffix' => '</p>',.
        ];
        $form['metadata_author'][$i]['org'] = [
          '#type' => 'item',
          '#title' => $this->t('Organization:'),
        // '#prefix' => '<p>',.
          '#markup' => $fields['personnel_metadata_author_organisation'][$i],
        // '#suffix' => '</p>',.
        ];
        $i++;
      }
    }

    if (isset($fields['personnel_datacenter_name'])) {
      $i = 0;
      foreach ($fields['personnel_datacenter_name'] as $datacenter) {
        $form['data_center_contact'][$i] = [
          '#prefix' => '<div class="w3-card-2 w3-margin w3-padding">',
          '#suffix' => '</div>',
        ];
        $form['data_center_contact'][$i]['avatar'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="w3-center">',
          '#markup' => '<i class="fa fa-user fa-2x" aria-hidden="true"></i>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['i'],
        ];
        $form['data_center_contact'][$i]['name'] = [
          '#type' => 'item',
          '#title' => $this->t('Name:'),
          '#markup' => $datacenter,
        // '#suffix' => '</p>',.
        ];
        $form['data_center_contact'][$i]['email'] = [
          '#type' => 'item',
          '#title' => $this->t('Email:'),
        // '#prefix' => '<p>',.
          '#markup' => '<a href="mailto://' . $fields['personnel_datacenter_email'][$i] . '">' . $fields['personnel_datacenter_email'][$i] . '</a>',
          '#attributes' => [
            'class' => ['contact-email'],
          ],
          // '#suffix' => '</p>',.
        ];
        $form['data_center_contact'][$i]['org'] = [
          '#type' => 'item',
          '#title' => $this->t('Organization:'),
        // '#prefix' => '<p>',.
          '#markup' => $fields['personnel_datacenter_organisation'][$i],
        // '#suffix' => '</p>',.
        ];
        $i++;
      }
    }

    if (isset($fields['personnel_technical_name'])) {
      $i = 0;
      foreach ($fields['personnel_technical_name'] as $technical) {
        $form['technical_contact'][$i] = [
          '#prefix' => '<div class="w3-card-2 w3-margin w3-padding">',
          '#suffix' => '</div>',
        ];
        $form['technical_contact'][$i]['avatar'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="w3-center">',
          '#markup' => '<i class="fa fa-user fa-2x" aria-hidden="true"></i>',
          '#suffix' => '</div>',
          '#allowed_tags' => ['i'],
        ];
        $form['technical_contact'][$i]['name'] = [
          '#type' => 'item',
          '#title' => $this->t('Name:'),
          '#markup' => $technical,
              // '#suffix' => '</p>',.
        ];
        $form['technical_contact'][$i]['email'] = [
          '#type' => 'item',
          '#title' => $this->t('Email:'),
          // '#prefix' => '<p>',.
          '#markup' => '<a href="mailto://' . $fields['personnel_technical_email'][$i] . '">' . $fields['personnel_technical_email'][$i] . '</a>',
          '#attributes' => [
            'class' => ['contact-email'],
          ],
                  // '#suffix' => '</p>',.
        ];
        if (isset($fields['personnel_technical_organisation'])) {
          $form['technical_contact'][$i]['org'] = [
            '#type' => 'item',
            '#title' => $this->t('Organization:'),
          // '#prefix' => '<p>',.
            '#markup' => $fields['personnel_technical_organisation'][$i],
          // '#suffix' => '</p>',.
          ];
        }
        $i++;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
