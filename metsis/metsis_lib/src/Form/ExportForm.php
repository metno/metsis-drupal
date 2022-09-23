<?php

namespace Drupal\metsis_lib\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\metsis_search\Form\ExportMetadataForm;
use Drupal\Core\Ajax\AjaxResponse;

use XSLTProcessor;
use SimpleXMLElement;

/**
 * Class ExportForm.
 */
class ExportForm extends FormBase
{
    /**
     * Config factory config
     * @var \Drupal\Core\Config\Config
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        $instance = parent::create($container);
        $instance->config =  $container->get('config.factory')->get('metsis_search.export.settings');
        return $instance;
    }
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'export_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $fields = null)
    {
        $form_state->set('mmd', $fields['mmd_xml_file']);
        $form_state->set('id', $fields['id']);

        $form['export'] = [
        '#type' => 'actions',
        //'#tree' => true,

      ];

        $form['export']['iso'] = [
      '#type' => 'submit',
      '#value' => $this->t('ISO-Inspire'),
      '#export_type' => 'iso',
      '#ajax' => [
        'callback' => '::ajaxCallback',
      ],

    ];

        $form['export']['iso-norge'] = [
  '#type' => 'submit',
  '#value' => $this->t('ISO-Norge-Inspire'),
  '#export_type' => 'geonorge',
  /*'#ajax' => [
    'callback' => '::submitForm',
  ],
*/

  ];
        $form['export']['dif'] = [
'#type' => 'submit',
'#value' => $this->t('NASA DIF 9.8'),
'#export_type' => 'dif',
'#ajax' => [
  'callback' => '::ajaxCallback',
],

];


        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        foreach ($form_state->getValues() as $key => $value) {
            // @TODO: Validate fields.
        }
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function ajaxCallback(array &$form, FormStateInterface $form_state)
    {
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Display result.
        //foreach ($form_state->getValues() as $key => $value) {
        //  \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
        //}
        $mmd = $form_state->get('mmd');
        $id = $form_state->get('id');
        //dpm($form_state->getTriggeringElement());

        $export_type=$form_state->getTriggeringElement()['#export_type'];
        //dpm($export_type);


        $response = new Response();

        // By setting these 2 header options, the browser will see the URL
        // and provide a download dialog.
        $response->headers->set('Content-Type', 'text/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $id.'_' .$export_type.'.xml"');

        if ($export_type === 'mmd') {
            $response->setContent(base64_decode($mmd));
        } else {
            $mmd_xml = base64_decode($mmd);
            $content = $this->transformXml($mmd_xml, $export_type);
            $response->setContent($content);
        }

        //Return the response in the form
        $form_state->setResponse($response);
        $form_state->setRebuild(true);
        return $response;
    }

    /**
     * Translate the  xml using the given export_type
     */
    public function transformXml($xml, $type)
    {
        //Get some config variables:
        $xslt_path = $this->config->get('xslt_path');
        $prefix = $this->config->get('xslt_prefix');
        $stylepath = $xslt_path . $prefix . $type . '.xsl';
        $style = file_get_contents(DRUPAL_ROOT.$stylepath);


        $xslt = new XSLTProcessor();
        $xslt->importStylesheet(new SimpleXMLElement($style));

        //Return the transformed XML
        return $xslt->transformToXml(new SimpleXMLElement($xml));
    }
}
