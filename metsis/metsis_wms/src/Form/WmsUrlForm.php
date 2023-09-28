<?php

namespace Drupal\metsis_wms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a METSIS WMS form.
 */
class WmsUrlForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_wms_wms_url';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['wms_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Enter wms url for capabilities document'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Plot'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  /*
  public function validateForm(array &$form, FormStateInterface $form_state) {
  if (mb_strlen($form_state->getValue('message')) < 10) {
  $form_state->setErrorByName('message', $this->t('Message should be at least 10 characters.'));
  }
  }
   */

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $wms_url = $form_state->getValue('wms_url');
    $this->messenger()->addStatus($this->t('url @', ['@' => $wms_url]));
    $option = [
      'wms_url' => $wms_url,
    ];
    $url = Url::fromRoute('metsis_wms.wms', $option);

    $form_state->setRedirectUrl($url);
    // $form_state->setRedirect();
  }

}
