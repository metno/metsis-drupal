<?php

namespace Drupal\metsis_search\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\search_api\Plugin\views\filter\SearchApiFilterTrait;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Defines a filter for filtering on dates.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("metsis_search_bbox_filter")
 */
class SearchApiBbox extends FilterPluginBase implements ContainerFactoryPluginInterface {
  use SearchApiFilterTrait;

  /**
   * Disable the possibility to force a single value.
   *
   * @var bool
   */
  protected $alwaysMultiple = TRUE;

  /**
   * {@inheritDoc}
   */
  protected function defineOptions() {
    // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
    $options = parent::defineOptions();
    $options['value'] = [
      'contains' => [
        'minX' => ['default' => ''],
        'maxX' => ['default' => ''],
        'maxY' => ['default' => ''],
        'minY' => ['default' => ''],
      ],
    ];
    $options['operator'] = ['default' => 'intersects'];
    // dpm($options, __LINE__);.
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    if (!empty($this->value)) {
      return $this->t('Coordinates: @coordinates',
        ['@coordinates' => implode(', ', $this->value)]);
    }
    else {
      return $this->t('No coordinates');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function operators() {
    $operators = [
      "contains" => [
        'title' => $this->t('Contains'),
        'method' => 'opBbox',
        'short' => 'Contains',
        'values' => 4,
      ],
      "within" => [
        'title' => $this->t('Within'),
        'method' => 'opBbox',
        'short' => 'Within',
        'values' => 4,
      ],
      "intersects" => [
        'title' => $this->t('Intersects'),
        'method' => 'opBbox',
        'short' => 'Intersects',
        'values' => 4,
      ],
    ];

    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {

    parent::valueForm($form, $form_state);
    $form['value'] = [
      '#tree' => TRUE,
    ];
    // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
    // Add the coordinate elements.
    $coordinates = ['minX', 'maxX', 'maxY', 'minY'];
    foreach ($coordinates as $coordinate) {
      $form['value'][$coordinate] = [
        '#type' => 'textfield',
        '#title' => $this->t('@coordinate coordinate', ['@coordinate' => $coordinate]),
        '#default_value' => !empty($this->value[$coordinate]) ? $this->value[$coordinate] : '',
      ];
    }
    $form['value']['minX']['#description'] = $this->t('Westernmost Longitude');
    $form['value']['maxX']['#description'] = $this->t('Easternmost Longitude');
    $form['value']['minY']['#description'] = $this->t('Southernmost Latitude');
    $form['value']['maxY']['#description'] = $this->t('Northernmost Latitude');

    // $form['bbox_wrapper']['bbox_op']['#size'] = 1;
    // // Set the default operator for the exposed form.
    // $form['operator']['#default_value'] = 'Intersects';
    return $form;
  }

  /**
   * Provide a list of all the numeric operators.
   */
  public function operatorOptions($which = 'title') {
    $options = [];
    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    if (empty($this->value)) {
      return;
    }
    // Get the field alias.
    $maxX = $this->value['maxX'];
    $minX = $this->value['minX'];
    $maxY = $this->value['maxY'];
    $minY = $this->value['minY'];

    if (
      !is_numeric($maxX)
      || !is_numeric($minX)
      || !is_numeric($maxY)
      || !is_numeric($minY)
    ) {
      return;
    }
    $field = "$this->realField";
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function operatorForm(&$form, FormStateInterface $form_state) {
    parent::operatorForm($form, $form_state);
    $form['operator']['#default_value'] = 'intersects';
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    // dpm($input, __FUNCTION__);.
    if (empty($this->options['exposed'])) {
      return TRUE;
    }
    $rc = parent::acceptExposedInput($input);
    /*
     * Make sure all coordinates are set and numeric.
     */
    foreach ($input['bbox'] as $key => $value) {
      if (empty($value || empty($key))) {
        $rc = FALSE;
      }
      else {
        if (!is_numeric($value)) {
          $rc = FALSE;
        }
        else {
          $rc = TRUE;
        }
      }
    }
    return $rc;
  }

  /**
   * {@inheritdoc}
   *
   * {@todo} Fix when webprofiler is not making problems anymore.
   */
  public function validateExposed(&$form, FormStateInterface $form_state) {
    // parent::validateExposed($form, $form_state);.
    if (empty($this->options['exposed'])) {
      return;
    }

    // dpm($form_state->getValues(), __FUNCTION__);
    // Validate BBox values for exposed form.
    $coordinates = ['minX', 'maxX', 'maxY', 'minY'];
    foreach ($coordinates as $coordinate) {
      $value = &$form_state->getValue(['bbox', $coordinate]);
      // dpm($value, __FUNCTION__ . " $coordinate");
      // dpm($form, __FILE__ . ':' . __LINE__);
      // $elem = $form['bbox_wrapper']['bbox_wrapper'][$this->options['expose']['identifier']];
      // dpm($elem);
      if ($value == NULL || $value === '') {
        // $form_state->setError($elem[$coordinate],
        // $this->t('The @coordinate coordinate is required.',
        // ['@coordinate' => $coordinate]));
      }
      else {
        $value = trim($value);
        if (!is_numeric($value)) {
          // $form_state->setErrorByName('bbox][' . $coordinate . ']',
          // $this->t('The @coordinate coordinate must be a number.',
          // ['@coordinate' => $coordinate]));
          // $form_state->setErrorByName('bbox][maxX]',
          // $this->t('The maxX coordinate must be a number.'));
        }
      }
    }
  }

  /**
   * Handle special solr date range query.
   */
  protected function opBbox($field) {
    // Get the field alias.
    $maxX = $this->value['maxX'];
    $minX = $this->value['minX'];
    $maxY = $this->value['maxY'];
    $minY = $this->value['minY'];
    // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
    $this->query->addWhere('bbox', $field, [$minX, $maxX, $maxY, $minY], $this->operator);
  }

}
