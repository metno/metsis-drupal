<!--<div class="share_these_results_by_mail">
    <a href="mailto:?subject=Metadata&body=<?php //print($this_url);     ?>"
       title="Share via e-mail"><br>
        <img src="/<?php //print($icons_path);     ?>/mail.png">
    </a>
</div>-->
<h2><?php print t('Available Metadata'); ?></h2>
<?php $calling_results_page = isset($_GET['calling_results_page']) ? check_plain($_GET['calling_results_page']) : ''; ?>
<?php print($metadata_table) ?>
<div class="tsf5"><a href="<?php print($calling_results_page); ?>"
                     class="adc-button"><?php print t('Back to results') ?></a>
</div>

