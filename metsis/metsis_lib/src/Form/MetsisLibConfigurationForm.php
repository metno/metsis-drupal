<?php
/**
 *
 * @file
 * Contains \Drupal\metsis_lib\MetsisSearchConfigurationForm
 *
 * Form for Landing Page Creator Admin Configuration
 *
 */
namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Url;

/*
 *  * Class ConfigurationForm.
 *
 *  {@inheritdoc}
 *
 *   */
class MetsisLibConfigurationForm extends ConfigFormBase {

  /*
   * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'metsis_lib.settings',
      ];
  }

  /*
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_lib.admin_config_form';
  }

  /*
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_lib.settings');

    $form['opendap_parser'] = [
      '#type' => 'fieldset',
      '#title' => 'Configure OPeNDAP parser service',
      '#tree' => TRUE,
    ];
    $form['opendap_parser']['opendap_parser_ip'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the IP of the OPeNDAP parser service.'),
      //'#description' => t("url northern base map"),
      '#size' => 20,
      '#default_value' => $config->get('metsis_opendap_parser_ip'),
    ];
    $form['opendap_parser']['opendap_parser_port'] = [
      '#type' => 'number',
      '#title' => t('Enter the port number of the OPeNDAP parser service.'),
      //'#description' => t("url southern base map"),
      '#size' => 10,
      '#default_value' => $config->get('metsis_opendap_parser_port'),
    ];

    $form['opendap_parser']['metsis_opendap_parser_service'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the service string for the OPeNDAP parser service.'),
      //'#description' => t("url northern base map"),
      '#size' => 30,
      '#default_value' => $config->get('metsis_opendap_parser_service'),
    ];





        return parent::buildForm($form, $form_state);
     }

      /*
       * {@inheritdoc}
       *
       * NOTE: Implement form validation here
       */
      public function validateForm(array &$form, FormStateInterface $form_state) {
        //get user and pass from admin configuration
        $values = $form_state->getValues();

      }

      /*
       * {@inheritdoc}
       */
      public function submitForm(array &$form, FormStateInterface $form_state) {

        /**
         * Save the configuration
        */


        $this->configFactory->getEditable('metsis_lib.settings')
          ->set('metsis_opendap_parser_ip', $values['opendap_parser']['opendap_parser_ip'])
          ->set('metsis_opendap_parser_port', $values['opendap_parser']['opendap_parser_port'])
          ->set('metsis_opendap_parser_service', $values['opendap_parser']['metsis_opendap_parser_service'])

          ->save();

        parent::submitForm($form, $form_state);
      }
    }
