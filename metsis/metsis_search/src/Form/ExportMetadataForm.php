<?php
/*
 * @file
 * Contains \Drupal\metsis_search/ExportMetadataForm
 *
 * Form for selecting and exporting MMD as other metadata formats.
 *
 */

namespace Drupal\metsis_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

use Symfony\Component\HttpFoundation\Response;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;

use XSLTProcessor;
use SimpleXMLElement;

class ExportMetadataForm extends FormBase
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
     * Getter method for Form ID.
     *
     * @inheritdoc
     */
    public function getFormId()
    {
        return 'metsis_search.export.form';
    }


    /**
    * @param $form
    * @param $form_state
    *
    * @return mixed
    *
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state, string $id = '')
    {
        $form['#prefix'] = '<div id="metsis-export-form">';
        $form['#suffix'] = '</div>';

        $form['solr-id'] = [
        '#type' => 'hidden',
        '#value' => $id,
      ];
        if (!$form_state->has('mmd')) {
            $mmd = $this->getMmd($id);


            if ($mmd == null || $mmd == '') {
                $form['export'] = [
        '#type' => 'markup',
        '#markup' => $this->t('The export service is not yet available for this dataset.'),
        '#allowd_tags' => ['a'],
      ];
                return $form;
            } else {
                $form_state->set('mmd', $mmd);
            }
        }

        //  } else {
        $form['export'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Export metadata'),
        '#allowd_tags' => ['a'],
      ];

        $options = $this->config->get('export_list');
        $def_export = (null != $form_state->getValue('list')) ? $form_state->getValue('list') : 'mmd';
        //dpm($def_export);
        $form['export']['list'] = [
        '#type' => 'select',
        '#options' => $options,
        '#default_value' => $def_export,
        '#ajax' => [
          'wrapper' => 'metsis-export-form',
          'callback' => '::changeExportTypeCallback',
          'disable-refocus' => true,
        ],
      ];

        $form['export']['actions'] = [
        '#type' => 'actions',
      ];
        $form['export']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => 'Export ' . $options[$def_export],
        //'#ajax' => [
        //  'wrapper' => 'metsis-export-form',
        //],
      ];

        $form['export']['list']['#default_value'] = $def_export;
        $form['export']['list']['#description'] = $this->config->get('export_type_desc')[$def_export];

        //$form_state->disableCache();
        //$form_state->setRequestMethod('POST');

        return $form;
        //}
    }
    /**
     * AJAX callback for select list
     */
    public function changeExportTypeCallback(array $form, FormStateInterface $form_state)
    {
        //$values = $form_state->getValues();
        //dpm($values);
        return $form;
    }


    /**
     * {@inheritdoc}
     *
     * Here we validate and signal an error if there are no users selected.
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     *
     * On submit, show the user the names of the users they selected.
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValues();

        $id = $values['solr-id'];

        //Get the base64 encoded mmd from solr given the id.
        $mmd = $form_state->get('mmd');
        /*
                $mmd = $this->getMmd($id);

                if ($mmd == null || $mmd == '') {
                    $response = new AjaxResponse();
                    $response->addCommand(new ReplaceCommand('#metsis-export-form', 'The export service is not yet available for this dataset.'));
                    $form_state->setResponse($response);

                //return $response;
              } else {*/
        $export_type = $values['list'];
        //dpm($export_type);

        // return it as a response.
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
        //dpm($response);
        //return $response;
        //}
    }
    /**
     * Use solrium to fetch the mmd_xml_file field given the id.
     */
    public function getMmd($id)
    {
        /** @var Index $index  TODO: Change to metsis when prepeare for release */
        $index = Index::load('metsis');

        /** @var SearchApiSolrBackend $backend */
        $backend = $index->getServerInstance()->getBackend();

        $connector = $backend->getSolrConnector();

        $solarium_query = $connector->getSelectQuery();
        $solarium_query->setQuery('id:'. $id);
        //$solarium_query->addSort('sequence_id', Query::SORT_ASC);
        $solarium_query->setRows(1);
        $fields[] = 'id';
        $fields[] = 'mmd_xml_file';
        $solarium_query->setFields($fields);

        $result = $connector->execute($solarium_query);

        // The total number of documents found by Solr.
        $found = $result->getNumFound();
        //\Drupal::logger('export_doc')->debug("found: " . $found);
        $mmd = null;
        foreach ($result as $doc) {
            $fields = $doc->getFields();
            //\Drupal::logger('export_doc')->debug($doc);
            $mmd = $fields['mmd_xml_file'];
        }
        return $mmd;
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
