<?php

namespace Drupal\metsis_search\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Plugin\views\filter\SearchApiFilterTrait;
use Drupal\views\Plugin\views\filter\Date;

/**
 * Defines a filter for filtering on dates.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("search_api_date_range")
 */
class SearchApiDateRange extends Date {

  use SearchApiFilterTrait;

  /**
   * {@inheritDoc}
   */
  protected function defineOptions() {

    $options = parent::defineOptions();
    // dpm($options, __FUNCTION__);.
    $options['value'] = [
      'contains' => [
        'min' => ['default' => ''],
        'max' => ['default' => ''],
      ],
    ];
    $options['value']['contains']['type']['default'] = 'date';

    // $options['operator'] = ['default' => 'Intersects'];
    // dpm($options, __FUNCTION__);.
    return $options;
  }

  /**
   * Add a type selector to the value form.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);
    // Update the form to fit our needs.
    unset($form['value']['value']);
    $form['value']['min']['#title'] = $this->t("Start date");
    $form['value']['max']['#title'] = $this->t("End date");
    $form['value']['min']['#type'] = 'date';
    $form['value']['max']['#type'] = 'date';
    $form['temporal_extent_period_dr_wrapper']['temporal_extent_period_dr_op']['#size'] = 1;
    // dpm($form, __FUNCTION__);.
  }

  /**
   * Defines the operators supported by this filter.
   *
   * @return array[]
   *   An associative array of operators, keyed by operator ID, with information
   *   about that operator:
   *   - title: The full title of the operator (translated).
   *   - short: The short title of the operator (translated).
   *   - method: The method to call for this operator in query().
   *   - values: The number of values that this operator expects/needs.
   */
  public function operators() {
    // $operators = parent::operators();
    // unset($operators['regular_expression']);
    // dpm($operators);
    $operators = [
      'Intersects' => [
        'title' => $this->t('Intersects'),
        'method' => 'opSolrDateRange',
        'short' => $this->t('Intersects'),
        'values' => 2,
      ],
      'Contains' => [
        'title' => $this->t('Contains'),
        'method' => 'opSolrDateRange',
        'short' => $this->t('Contains'),
        'values' => 2,
      ],
      'Within' => [
        'title' => $this->t('Within'),
        'method' => 'opSolrDateRange',
        'short' => $this->t('Within'),
        'values' => 2,
      ],
    ];
    return $operators;
  }

  /**
   * {@inheritdoc}
   */
  public function acceptExposedInput($input) {
    if (empty($this->options['exposed'])) {
      return TRUE;
    }
    // dpm($input, __FUNCTION__);.
    // Add to make parent acceptExposedInput to validate.
    $input['value'] = '';
    $rc = parent::acceptExposedInput($input);
    // We accept open start and end dates.
    if ($input['temporal_extent_period_dr']['min'] != '') {
      $rc = TRUE;
    }
    if ($input['temporal_extent_period_dr']['max'] != '') {
      $rc = TRUE;
    }
    // dpm($input, __FUNCTION__);
    // dpm($rc, __LINE__);.
    return $rc;
  }

  /**
   * Handle special solr date range query.
   */
  protected function opSolrDateRange($field) {
    $a = intval(strtotime($this->value['min'], 0));
    $b = intval(strtotime($this->value['max'], 0));
    // Handle date offsetts.
    if ($this->value['type'] == 'offset') {
      // Keep sign.
      $a = '***CURRENT_TIME***' . sprintf('%+d', $a);
      // Keep sign.
      $b = '***CURRENT_TIME***' . sprintf('%+d', $b);
    }

    // Add a bogus filter to be able to read the operator in the preQueryEvent.
    $this->query->addWhere($this->options['group'] + 1, $field, 0, $this->operator);

    // Let the parent classes handle the query generation using the parents operators.
    if ($a != 0 && $b == 0) {
      $this->query->addWhere($this->options['group'], $field, $a, '>=');
    }
    if ($b != 0 && $a == 0) {
      $this->query->addWhere($this->options['group'], $field, $b, '<=');
    }
    if (($a != 0 && $b != 0)) {
      $this->query->addWhere($this->options['group'], $field, [$a, $b], "between");
    }
    // dpm($this->query, __FUNCTION__);
    // $this->query->addWhereExpression($this->options['group'], "$field $operator $a AND $b");.
  }

}
