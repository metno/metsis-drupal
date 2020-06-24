<?php
$bokeh_js_options = [
  'type' => 'external',
  'weight' => -100,
  //  'browsers' => array(
  //    array('IE' => 'lte IE 9'),
  //  ),
];
drupal_add_js('https://cdn.bokeh.org/bokeh/release/bokeh-2.0.2.min.js', $bokeh_js_options);
drupal_add_js('https://cdn.bokeh.org/bokeh/release/bokeh-widgets-2.0.2.min.js', $bokeh_js_options);
drupal_add_js('https://cdn.bokeh.org/bokeh/release/bokeh-tables-2.0.2.min.js', $bokeh_js_options);
drupal_add_js('https://cdn.bokeh.org/bokeh/release/bokeh-api-2.0.2.min.js', $bokeh_js_options);
?>
<script>
  fetch(<?php echo('"' . $_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_plot_query'] . '"');  ?>)
    .then(function (response) {
      return response.json();
    })
    .then(function (item) {
      Bokeh.embed.embed_item(item);
    })
</script>
<?php $calling_results_page = isset($_GET['calling_results_page']) ? check_plain($_GET['calling_results_page']) : ''; ?>
<div class="row">
  <div class="plot-container">
    <div id="tsplot">
      <!--    The plot appears here.-->
      <?php $_SESSION['metsis_ts_bokeh'][session_id()]['metsis_ts_bokeh_plot_query'] = NULL; ?>
    </div>
  </div>
  <div class="vars-container">
    <!--    <div class="fixed-width">-->
    <?php //print render($form['x_axis']); ?><!--</div>-->
    <div class="flex-width"><?php print render($form['y_axis']); ?></div>
    <div
      class="plot-submit"><?php print render($form['actions']['submit']); ?></div>
    <div class="tsf5"><a href="<?php print($calling_results_page); ?>"
                         class="adc-button"><?php print t('Back to results') ?></a>
    </div>

  </div>

</div>
<!-- Render any remaining elements, such as hidden inputs (token, form_id, etc). -->
<?php print drupal_render_children($form); ?>
