<?php

namespace Drupal\metsis_search\Solarium;

use Solarium\Core\Query\Helper;

/**
 * This class extends the Solarium Query Helper to add cu functionality.
 */
class MetsisQueryHelper extends Helper {

  /**
   * Render a bbox filter query with predicate and ENVELOPE wkt string.
   *
   * @param string $field
   *   The field name.
   * @param array $bbox
   *   The 4 coordinates of the bounding box.
   * @param string $predicate
   *   The predicate to use for the filter query.
   * @param bool $overlapSort
   *   (optional) If true, sort by overlap ratio. Defaults to FALSE.
   *
   * @return string
   *   Returns the solr query string for this filter query.
   */
  public function metsisBbox(
    string $field,
    array $bbox,
    string $predicate,
    bool $overlapSort = FALSE,
  ): string {
    $envelope = $this->envelope($bbox);
    $op = ucfirst(strtolower($predicate));
    if ($overlapSort) {
      return "{!field f=$field score=overlapRatio}$op($envelope)";
    }
    else {
      return "{!field f=$field}$op($envelope)";
    }
  }

  /**
   * Convert bbox array with coordinates to ENVELOPE wkt string.
   *
   * @param array $bbox
   *   The boundingbox coordinates.
   *
   * @return string
   *   The ENVELOPE wkt string
   */
  protected function envelope(array $bbox): string {
    // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
    return sprintf('ENVELOPE(%s, %s, %s, %s)',
      floatval($bbox[0]), floatval($bbox[1]),
      floatval($bbox[2]), floatval($bbox[3]));
  }

}
