
<?php
if (isset($form['results']['#value'])) {
    $plot_url = '"<img src="' . $form['results']['#value'][0][0] . '"></img>"';
}
$calling_results_page = isset($_GET['calling_results_page']) ? check_plain($_GET['calling_results_page']) : '';
?>

<div class="tswrapper">
    <div class="tsbox tscontent">
        <img src=<?php print render($form['results']['#value'][0][0]); ?>>
        </img>
    </div>
    <!--
            <table>
    <?php
    if (isset($form['results']['#value'])) {
        $results = $form['results']['#value'];
        foreach ($results as $row) {
            echo '<tr><td>';
            echo "<img src=" . $row[0] . "></img>";
            echo "</td></tr>";
        }
    }
    ?>
            </table>
    -->
    <div class="tsbox tsfooter">
        <div class="tsf1">X-axis variable<?php print render($form['x_axis']); ?></div>
        <div class="tsf3">Plot every Nth data point<?php print render($form['ts_plot_npoints']); ?></div>
        <div class="tsf2">Y-axis variable<?php print render($form['y_axis']); ?></div>
        <div class="tsf4">Output file format<?php print render($form['ts_plot_file_format']); ?></div>
        <div class="tsf5"><?php print render($form['submit']); ?></div>
        <div class="tsf5"> <a href="<?php print($calling_results_page); ?>" class="adc-button"><?php print t('Back to results') ?></a></div>
    </div>
</div>
<?php print drupal_render_children($form); ?>
