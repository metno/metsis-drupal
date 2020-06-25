<?php $calling_results_page = isset($_GET['calling_results_page']) ? check_plain($_GET['calling_results_page']) : ''; ?>

<!--    The variable table appears here.-->
<?php $_SESSION['metsis_csv_bokeh'][session_id()]['metsis_csv_bokeh_download_query'] = NULL; ?>
<div class="csvbox contenta">
  <div
    class="csvbox csvc1"><?php print render($form['od_variables_tabular']); ?>
  </div>

  <div class="csvbox contentb">
    Output format<?php print render($form['csv_file_format']); ?>
  </div>
  <div class="vars-container">
    <div
      class="plot-submit"><?php print render($form['actions']['submit']); ?></div>
    <div class="tsf5"><a href="<?php print($calling_results_page); ?>"
                         class="adc-button"><?php print t('Back to results') ?></a>
    </div>
  </div>
</div>
<!-- Render any remaining elements, such as hidden inputs (token, form_id, etc). -->
<?php print drupal_render_children($form); ?>
