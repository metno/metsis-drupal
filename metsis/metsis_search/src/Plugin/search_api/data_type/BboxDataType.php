<?php

namespace Drupal\metsis_search\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides the location data type.
 *
 * @SearchApiDataType(
 *   id = "solr_bbox",
 *   label = @Translation("SolR Bbox field"),
 *   description = @Translation("Solr Bbx field data type implementation")
 * )
 */
class BboxDataType extends DataTypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getValue($value) {
    // Example: ENVELOPE(-10, 20, 15, 10) which is minX, maxX, maxY, minY order.
    $format = "ENVELOPE(%f, %f, %f, %f)";

    // Parse the string and assign the values to variables.
    sscanf($value, $format, $minX, $maxX, $maxY, $minY);
    return "$minX,$maxX,$maxY,$minY";
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackType() {
    // By returning NULL, we prevent that this data type is handled as a string
    // and e.g. text processors won't run on this value since string is the
    // default fallback type.
    return NULL;
  }

}
