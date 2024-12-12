<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Form\UserLoginForm;

/**
 * Defines a form that alters the user login form.
 */
class MetsisAjaxLoginForm extends UserLoginForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_ajax_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $datasetId = NULL) {
    $form = parent::buildForm($form, $form_state);
    $form['#prefix'] = '<div id="metsis-login-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'core/drupal.states';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $form['actions']['submit']['#ajax'] = [
      'callback' => '::customAjaxCallback',
      'wrapper' => 'metsis-login-wrapper',
      'event' => 'click',
    ];
    if (isset($datasetId)) {
      $form_state->set('dataset_id', $datasetId);
    }
    return $form;
  }

  /**
   * Custom AJAX callback for the user login form.
   */
  public function customAjaxCallback($form, &$form_state) {
    // Your custom AJAX logic here.
    $response = new AjaxResponse();
    if (empty($form_state->getErrors()) && !empty($uid = $form_state->get('uid'))) {
      $uid = $form_state->get('uid');
      $this->getLogger("ajaxLoginCallback")->info("User with uid: " . $uid . ". Loading netcdfOnDemand Form: " . $form_state->get('dataset_id'));
      $form = \Drupal::formBuilder()->getForm('Drupal\metsis_search\Form\NetCDFOnDemandForm', $form_state->get('dataset_id'));
      $form['#attached']['library'][] = 'core/drupal.states';
      $response->addCommand(new OpenModalDialogCommand('Request CF-NetCDF file.', $form, ['width' => '500']));
      return $response;
    }
    else {
      return $form;
    }
    return $form;
  }

}
