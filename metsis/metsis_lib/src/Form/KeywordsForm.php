<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Display differnt keyword classes in horizontal tabs.
 */
class KeywordsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'keywords_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fields = NULL) {
    $form['keywords'] = [
      '#type' => 'horizontal_tabs',
        // '#tree' => true,.
      '#default_tab' => 'iso',
    ];
    if (isset($fields['iso_topic_category'])) {
      $form['iso'] = [
        '#type' => 'details',
        '#title' => $this->t('ISO Topic Keywords'),
        '#weight' => '0',
        '#group' => 'keywords',
        '#allowed_tags' => ['a'],
      ];
      foreach ($fields['iso_topic_category'] as $iso) {
        // dpm($iso);
        $form['iso'][$iso] = [
          '#prefix' => '<p>',
          '#type' => 'markup',
          '#markup' => '<a href="https://vocab.met.no/mmd/ISO_Topic_Category/' . $iso . '">' . $iso . '</a>',
          '#allowed_tags' => ['a', 'p'],
          '#suffix' => '</p>',
        ];

      }

    }

    foreach ($fields['keywords_vocabulary'] as $vocab) {
      $vocab_tag = str_replace(' ', '_', strtolower($vocab));
      if ($vocab === "GCMDSK") {
        $vocab = 'GCMD Science Keywords';
      }
      if ($vocab === "CFSTDN") {
        $vocab = 'CF Standard Name';
      }
      if ($vocab === "GCMDLOC") {
        $vocab = 'GCMD Location Keywords';
      }
      if ($vocab === "GCMDPROV") {
        $vocab = 'GCMD Provider Keywords';
      }
      if ($vocab === "None") {
        $vocab = 'Other Keywords';
      }

      $form[$vocab_tag] = [
        '#type' => 'details',
        '#title' => $this->t('@vocab', ['@bocab' => $vocab]),
        '#weight' => '0',
        '#group' => 'keywords',
        '#attributes' => [
          'class' => ['w3-cell-row'],
        ],
      ];
    }

    $i = 0;
    foreach ($fields['keywords_vocabulary'] as $vocab) {
      $vocab_tag = str_replace(' ', '_', strtolower($vocab));
      if ($vocab === "GCMDSK") {
        $vocab = 'GCMD Science Keywords';
      }
      if ($vocab === "CFSTDN") {
        $vocab = 'CF Standard Name';
      }
      if ($vocab === "GCMDLOC") {
        $vocab = 'GCMD Location Keywords';
      }
      if ($vocab === "GCMDPROV") {
        $vocab = 'GCMD Provider Keywords';
      }
      if ($vocab === "None") {
        $vocab = 'Other Keywords';
      }

      $form[$vocab_tag][$i] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $fields['keywords_keyword'][$i] . '</p>',
      ];
      $i++;
    }
    /*
    if (isset($fields['keywords_investigator_name'])) {
    $i = 0;
    foreach ($fields['keywords_investigator_name'] as $investigator) {
    $form['investigator'][$i] = [
    '#prefix' => '<div class="w3-card-2 w3-cell w3-container">',
    '#suffix' => '</div>',
    ];
    $form['investigator'][$i]['avatar'] = [
    '#type' => 'markup',
    '#prefix' => '<div class="w3-center">',
    '#markup' => '<i class="fa fa-user fa-2x" aria-hidden="true"></i>',
    '#suffix' => '</div>',
    '#allowed_tags' => ['i'],
    ];
    $form['investigator'][$i]['name'] = [
    '#type' => 'item',
    '#title' => $this->t('Name:'),
    '#markup' => $investigator,
    //'#suffix' => '</p>',
    ];
    $form['investigator'][$i]['email'] = [
    '#type' => 'item',
    '#title' => $this->t('Email:'),
    //'#prefix' => '<p>',
    '#markup' => '<a href="mailto://'.$fields['keywords_investigator_email'][$i].'">'.$fields['keywords_investigator_email'][$i].'</a>',
    '#attributes' => [
    'class' => ['contact-email'],
    ],
    //'#suffix' => '</p>',
    ];
    $form['investigator'][$i]['org'] = [
    '#type' => 'item',
    '#title' => $this->t('Organization:'),
    //'#prefix' => '<p>',
    '#markup' => $fields['keywords_investigator_organisation'][$i],
    //'#suffix' => '</p>',
    ];
    $i++;
    }
    }

    if (isset($fields['keywords_metadata_author_name'])) {
    $i = 0;
    foreach ($fields['keywords_metadata_author_name'] as $author) {
    $form['metadata_author'][$i] = [
    '#prefix' => '<div class="w3-card-2 w3-cell w3-container">',
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
    //'#suffix' => '</p>',
    ];
    $form['metadata_author'][$i]['email'] = [
    '#type' => 'item',
    '#title' => $this->t('Email:'),
    //'#prefix' => '<p>',
    '#markup' => '<a href="mailto://'.$fields['keywords_metadata_author_email'][$i].'">'.$fields['keywords_metadata_author_email'][$i].'</a>',
    '#attributes' => [
    'class' => ['contact-email'],
    ],
    //'#suffix' => '</p>',
    ];
    $form['metadata_author'][$i]['org'] = [
    '#type' => 'item',
    '#title' => $this->t('Organization:'),
    //'#prefix' => '<p>',
    '#markup' => $fields['keywords_metadata_author_organisation'][$i],
    //'#suffix' => '</p>',
    ];
    $i++;
    }
    }

    if (isset($fields['keywords_datacenter_name'])) {
    $i = 0;
    foreach ($fields['keywords_datacenter_name'] as $datacenter) {
    $form['data_center_contact'][$i] = [
    '#prefix' => '<div class="w3-card-2 w3-cell w3-container">',
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
    //'#suffix' => '</p>',
    ];
    $form['data_center_contact'][$i]['email'] = [
    '#type' => 'item',
    '#title' => $this->t('Email:'),
    //'#prefix' => '<p>',
    '#markup' => '<a href="mailto://'.$fields['keywords_datacenter_email'][$i].'">'.$fields['keywords_datacenter_email'][$i].'</a>',
    '#attributes' => [
    'class' => ['contact-email'],
    ],
    //'#suffix' => '</p>',
    ];
    $form['data_center_contact'][$i]['org'] = [
    '#type' => 'item',
    '#title' => $this->t('Organization:'),
    //'#prefix' => '<p>',
    '#markup' => $fields['keywords_datacenter_organisation'][$i],
    //'#suffix' => '</p>',
    ];
    $i++;
    }
    }


    if (isset($fields['keywords_technical_name'])) {
    $i = 0;
    foreach ($fields['keywords_technical_name'] as $technical) {
    $form['technical_contact'][$i] = [
    '#prefix' => '<div class="w3-card-2 w3-cell w3-container">',
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
    //'#suffix' => '</p>',
    ];
    $form['technical_contact'][$i]['email'] = [
    '#type' => 'item',
    '#title' => $this->t('Email:'),
    //'#prefix' => '<p>',
    '#markup' => '<a href="mailto://'.$fields['keywords_technical_email'][$i].'">'.$fields['keywords_technical_email'][$i].'</a>',
    '#attributes' => [
    'class' => ['contact-email'],
    ],
    //'#suffix' => '</p>',
    ];
    $form['technical_contact'][$i]['org'] = [
    '#type' => 'item',
    '#title' => $this->t('Organization:'),
    //'#prefix' => '<p>',
    '#markup' => $fields['keywords_technical_organisation'][$i],
    //'#suffix' => '</p>',
    ];
    $i++;
    }
    }
     */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
