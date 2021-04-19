<?php
/*
 *
 * @file
 * Contains \Drupal\metsis_fimex\MetsisSearchConfigurationForm
 *
 * Form for Landing Page Creator Admin Configuration
 *
 **/
namespace Drupal\metsis_fimex\Form;

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
class MetsisFimexConfigurationForm extends ConfigFormBase {

  /*
   * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      'metsis_fimex.settings',
      ];
  }

  /*
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metsis_fimex.admin_config_form';
  }

  /*
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metsis_fimex.settings');

    $form['fimex'] = [
      '#type' => 'fieldset',
      '#title' => 'Configure fimex service backend.',
      '#tree' => TRUE,
    ];
    $form['fimex']['fimex_server'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the IP of the fimex server service.'),
      //'#description' => t("url northern base map"),
      '#size' => 20,
      '#default_value' => $config->get('fimex_server'),
    ];
    $form['fimex']['fimex_server_port'] = [
      '#type' => 'number',
      '#title' => t('Enter the port number of the fimex server service.'),
      //'#description' => t("url southern base map"),
      '#size' => 10,
      '#default_value' => $config->get('fimex_server_port'),
    ];

    $form['fimex']['fimex_server_service'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the service string for the fimex server service.'),
      //'#description' => t("url northern base map"),
      '#size' => 30,
      '#default_value' => $config->get('fimex_server_service'),
    ];

    $form['fimex']['fimex_getcapablities'] = [
      '#type' => 'textfield',
      '#title' => t('Enter the transformation getCapabilities URI string.'),
      //'#description' => t("url northern base map"),
      '#size' => 150,
      '#default_value' => $config->get('transformation_server_getcapabilities'),
    ];

    $form['fimex']['fimex_exlude_variables'] = [
      '#type' => 'textfield',
      '#title' => t('Enter comma seperated list of OPeNDAP variables to exclude'),
      //'#description' => t("url northern base map"),
      '#size' => 200,
      '#default_value' => $config->get('transformation_exclude_variables'),
    ];
    

    $form['fimex']['transformation_output_format_visible'] = [
      '#type' => 'select',
      '#title' => t('Transformation output format visible?'),
    //  '#description' => t("Show pins on map or not."),
      '#options' => [
        1 => t('Yes'),
        0 => t('No'),
      ],
      '#default_value' => $config->get('transformation_output_format_visible'),
    ];

    $form['fimex_messages'] = [
      '#type' => 'fieldset',
      '#title' => 'Configure fimex service related messages.',
      '#tree' => TRUE,
    ];

    $form['fimex_messages']['show_warning message'] = [
      '#type' => 'select',
      '#title' => t('Show transformation warning message?'),
    //  '#description' => t("Show pins on map or not."),
      '#options' => [
        1 => t('Yes'),
        0 => t('No'),
      ],
      '#default_value' => $config->get('transformation_message_visible'),
    ];

    $form['fimex_messages']['transformation_warning_msg'] = [
      '#type' => 'textfield',
      '#title' => t('Enter transformation warning message.'),
      //'#description' => t("url northern base map"),
      '#size' => 150,
      '#default_value' => $config->get('transformation_warning_msg'),
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


        $this->configFactory->getEditable('metsis_fimex.settings')
          ->set('fimex_server', $values['fimex']['fimex_server'])
          ->set('fimex_server_port', $values['fimex']['fimex_server_port'])
          ->set('fimex_server_service', $values['fimex']['fimex_server_service'])
          ->set('transformation_server_getcapabilities', $values['fimex']['fimex_getcapablities'])
          ->set('transformation_exclude_variables', $values['fimex']['fimex_exlude_variables'])
          ->set('transformation_warning_msg', $values['fimex_messages']['transformation_warning_msg'])
          ->set('transformation_output_format_visible', $values['fimex']['transformation_output_format_visible'])
          ->set('transformation_message_visible', $values['fimex_messages']['show_warning message'])
          ->save();

        parent::submitForm($form, $form_state);
      }
    }
