<?php
//$icons_path = variable_get('file_public_path', conf_path() . '/files/icons');
//$this_url = url(current_path(), array('absolute' => TRUE, 'query' => drupal_get_query_parameters()));
?>
<!--<div class="share_these_results_by_mail">
    <a href="mailto:?subject=Metadata&body=<?php //print($this_url);     ?>"
       title="Share via e-mail"><br>
        <img src="/<?php //print($icons_path);     ?>/mail.png">
    </a>
</div>-->
<h2><?php print t('Available Metadata'); ?></h2>
<?php print($metadata_table) ?>
<div><a href="#"
        class="adc-button adc-back"><?php print t('Back to results') ?></a>
</div>

