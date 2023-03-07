<?php

namespace Drupal\metsis_lib;

/**
 *
 */
class XmlElement {

  public $name;
  public $attributes;
  public $content;
  public $children;

  /**
   *
   */
  public function xml_to_object($xml) {
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $xml, $tags);
    xml_parser_free($parser);

    // The currently filling [child] XmlElement array.
    $elements = [];
    $stack = [];
    foreach ($tags as $tag) {
      $index = count($elements);
      if ($tag['type'] == "complete" || $tag['type'] == "open") {
        $elements[$index] = new XmlElement();
        $elements[$index]->name = $tag['tag'];
        $elements[$index]->attributes = $tag['attributes'];
        $elements[$index]->content = $tag['value'];
        // Push.
        if ($tag['type'] == "open") {
          $elements[$index]->children = [];
          $stack[count($stack)] = &$elements;
          $elements = &$elements[$index]->children;
        }
      }
      // Pop.
      if ($tag['type'] == "close") {
        $elements = &$stack[count($stack) - 1];
        unset($stack[count($stack) - 1]);
      }
    }
    // Return $elements[0];  // the single top-level element
    // the single top-level element.
    return $elements;
  }

}
