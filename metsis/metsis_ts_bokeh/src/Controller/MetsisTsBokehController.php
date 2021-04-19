<?php

namespace Drupal\metsis_ts_bokeh\Controller;

use Drupal\Core\Controller\ControllerBase;

/*
 *  * Defines HelloController class.
 */
class MetsisTsBokehController extends ControllerBase {

    /*
     * Display the markup.
     *
     * @return array
     * Return markup array.
     */
      public function content() {
        return [
          '#type' => 'markup',
          '#markup' => $this->t('Hello, World!'),
        ];
    }
}

