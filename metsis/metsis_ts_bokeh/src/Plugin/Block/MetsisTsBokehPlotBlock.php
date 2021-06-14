<?php
/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Plugin\Block\MetsisTsBokehPlotBlock
 *
 * Block to Show the TS Plot form
 */
namespace Drupal\metsis_ts_bokeh\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "ts_bokeh_plot_block",
 *   admin_label = @Translation("Timeseries Bokeh Plot Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MetsisTsBokehPlotBlock extends BlockBase implements BlockPluginInterface {

  /*
   * {@inheritdoc}
   * Add Form to BLock
   */
  public function build() {
     return \Drupal::formBuilder()->getForm('Drupal\metsis_ts_bokeh\Form\MetsisTsBokehPlotForm');
  }
}
