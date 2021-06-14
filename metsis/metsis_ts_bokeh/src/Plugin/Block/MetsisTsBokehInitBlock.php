<?php
/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Plugin\Block\MetsisTsBokehInitBlock
 *
 * BLock to show TS bokeh Init form
 *
 */
namespace Drupal\metsis_ts_bokeh\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a Block.
 *
 * @Block(
 *   id = "ts_bokeh_init_block",
 *   admin_label = @Translation("Timeseries Bokeh Init Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MetsisTsBokehInitBlock extends BlockBase implements BlockPluginInterface {

  /*
   * {@inheritdoc}
   * Add Form to block
   */
  public function build() {
  return \Drupal::formBuilder()->getForm('Drupal\metsis_ts_bokeh\Form\MetsisTsBokehInitForm');
 }
}
