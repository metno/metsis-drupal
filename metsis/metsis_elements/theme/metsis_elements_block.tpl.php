<?php
$calling_results_page = isset($_GET['calling_results_page']) ? \Drupal\Component\Utility\Html::escape($_GET['calling_results_page']) : '';
$params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters();
$parent = adc_get_datasets_fields(SOLR_SERVER_IP, SOLR_SERVER_PORT, SOLR_CORE_PARENT, array($params['metadata_identifier']), array(METADATA_PREFIX . 'title', METADATA_PREFIX . 'abstract'), 0, 1);
?>

<div class="mewrapper">
    <div class="mebox">
        <div class="elementsbox elementsc1"><?php print render($form['elements_tabular_form']); ?>
            <b>Parent data information</b><br>

            <b>Title:</b> <?php print($parent['response']['docs'][0][METADATA_PREFIX . 'title'][0]); ?><br>

            <b>Metadata identifier:</b> <?php print($params['metadata_identifier']); ?><br>

            <b>Abstract:</b> <?php print($parent['response']['docs'][0][METADATA_PREFIX . 'abstract'][0]); ?><br>

        </div><br>
        <div class="elementsbox contentb">

            <?php print drupal_render_children($form); ?>
<!--            <a href="<?php print($calling_results_page); ?>" class="adc-button"><?php print t('Back to results') ?>
            </a>-->
<!--            <a href="<?php print($calling_results_page); ?>" class="button"><?php print t('Back to results') ?>
            </a>-->
        </div>
    </div>
</div>
