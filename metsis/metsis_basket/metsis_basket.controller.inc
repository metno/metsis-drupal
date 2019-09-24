<?php

class MetsisBasketController extends DrupalDefaultEntityController {

  public function buildContent($entity, $view_mode = 'full', $langcode = NULL, $content = array()) {
    $build = parent::buildContent($entity, $view_mode, $langcode, $content);
    $build['name'] = array('#type' => 'markup', '#markup' => check_plain($entity->name), '#prefix' => '<div class="metsis_basket-name">', '#suffix' => '</div>',);
    $build['basket_timestamp'] = array('#type' => 'markup', '#markup' => date('d F, Y', check_plain($entity->basket_timestamp)), '#prefix' => '<p> Timestamp: ', '#suffix' => '</p>',);
    return $build;
  }

}
