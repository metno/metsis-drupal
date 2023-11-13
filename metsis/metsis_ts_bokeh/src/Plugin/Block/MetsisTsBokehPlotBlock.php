<?php

namespace Drupal\metsis_ts_bokeh\Plugin\Block;

/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Plugin\Block\MetsisTsBokehPlotBlock
 *
 * Block to Show the TS Plot form
 */
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class MetsisTsBokehPlotBlock extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {
  /**
   * Form builder will be used via Dependency Injection.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The container create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container dependency injection interface.
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
       $configuration,
       $plugin_id,
    $plugin_definition,
    $container->get('form_builder')
     );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   *
   * Add Form to BLock.
   */
  public function build() {
    return $this->formBuilder->getForm('Drupal\metsis_ts_bokeh\Form\MetsisTsBokehPlotForm');
  }

}
