<?php
$calling_results_page = isset($_GET['calling_results_page']) ? \Drupal\Component\Utility\Html::escape($_GET['calling_results_page']) : '';
?>
<div class="csvwrapper">
    <div class="csvbox feedback">
        <?php
        if (isset($form['results']['#value'][0][0])) {
          $CSVFileURL = $form['results']['#value'][0][0];
          echo '<div><a href="';
          echo $CSVFileURL;
          echo '" download="filename">You can download your data by following this link.</a></div>';
        }
        ?>
    </div>
    <div class="csvbox contenta">
        <div class="csvbox csvc1"><?php print render($form['od_variables_tabular']); ?>
        </div>

        <div class="csvbox contentb">
            Output format<?php print render($form['csv_file_format']); ?>
        </div>
        <div class="csvbox contentc">
            <?php print render($form['submit']); ?>
        </div>
        <div class="csvbox contentc"> 
            <a href="<?php print($calling_results_page); ?>" class="adc-button"><?php print t('Back to results') ?>
            </a>
        </div>
        <div class="csvbox contentd">
            <?php print render($form['csv_npoints']); ?>
        </div>
    </div>
    <?php print drupal_render_children($form); ?>

