<?php
/*
 * @file
 * Contains \Drupal\metsis_csv\Plugin\Block\MetsisCsvFormBlock
 *
 * BLock to show metsis CSV form
 *
 */
namespace Drupal\metsis_csv\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;


/*
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_csv_form_block",
 *   admin_label = @Translation("CSV Form Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class MetsisCsvFormBlock extends BlockBase implements BlockPluginInterface {

  /*
   * {@inheritdoc}
   * Add Form to block
   */
  public function build() {
    //if(\Drupal::currentUser()->hasPermission('access content')) {
    return \Drupal::formBuilder()->getForm('Drupal\metsis_csv\Form\MetsisCsvForm');
  //}
 }
}
