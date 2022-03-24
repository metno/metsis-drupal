<?php
/**
 * @file
 * Contains \Drupal\metsis_search\Plugin\Block\GcmdBlock
 *
 * BLock to show search map
 *
 */

namespace Drupal\metsis_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Block.
 *
 * @Block(
 *   id = "metsis_gcmd_block",
 *   admin_label = @Translation("GCMD Keywords Block"),
 *   category = @Translation("METSIS"),
 * )
 * {@inheritdoc}
 */
class GcmdBlock extends BlockBase implements BlockPluginInterface
{
  /**
   * {@inheritdoc}
   * Add js to block and return renderarray
   */
    public function build()
    {
        //\Drupal::logger('metsis_search')->debug("Building Gcmd block");
        $query_from_request = \Drupal::request()->query->all();
        $params = \Drupal\Component\Utility\UrlHelper::filterQueryParameters($query_from_request);
        if (isset($params['f'])) {
            foreach ($params['f'] as $param) {
                //dpm($param);
            }
        }

        //Check if we already have an active bboxFilter
        $session = \Drupal::request()->getSession();
        //$list = $session->get('gcmd');
        $build['wrapper'] = [
        '#prefix' => '<div id="gcmdblock">',
        '#suffix' => '</div>'
      ];

        $build['wrapper']['gcmd_l1'] = \Drupal::service('plugin.manager.block')
        ->createInstance('facet_block:gcmd_keywords')
        ->build();

        $build['wrapper']['gcmd_l1']['#prefix'] ='<div id="gcmd_l1">';
        $build['wrapper']['gcmd_l1']['#suffix'] ='</div>';

        if (isset($params['f'])) {
            foreach ($params['f'] as $param) {
                $q = explode(":", $param);
                //dpm($q);
                if ($q[0] == 'gcmd_keywords1') {
                    $build['wrapper']['delimeter1'] = [
              '#prefix' => '<div style="margin: 10px;" "class="delimeter">',
              '#markup' => '➤',
              '#suffix' => '</div>'
            ];
                    $build['wrapper']['gcmd_l2'] = \Drupal::service('plugin.manager.block')
              ->createInstance('facet_block:keywords_level2')
              ->build();
                    $build['wrapper']['gcmd_l2']['#prefix'] ='<div id="gcmd_l2">';
                    $build['wrapper']['gcmd_l2']['#suffix'] ='</div>';
                }
                if ($q[0] == 'keywords_level2') {
                    $build['wrapper']['delimeter2'] = [
                '#prefix' => '<div "class="delimeter">',
                '#markup' => '➤',
                '#suffix' => '</div>'
              ];
                    $build['wrapper']['gcmd_l3'] = \Drupal::service('plugin.manager.block')
                ->createInstance('facet_block:keywords_level3')
                ->build();
                    $build['wrapper']['gcmd_l3']['#prefix'] ='<div id="gcmd_l3">';
                    $build['wrapper']['gcmd_l3']['#suffix'] ='</div>';
                }

                if ($q[0] == 'keywords_level3') {
                    $build['wrapper']['delimeter3'] = [
                '#prefix' => '<div "class="delimeter">',
                '#markup' => '➤',
                '#suffix' => '</div>'
              ];
                    $build['wrapper']['gcmd_l4'] = \Drupal::service('plugin.manager.block')
                ->createInstance('facet_block:keywords_level4')
                ->build();
                    $build['wrapper']['gcmd_l4']['#prefix'] ='<div id="gcmd_l4">';
                    $build['wrapper']['gcmd_l4']['#suffix'] ='</div>';
                }
                if ($q[0] == 'keywords_level4') {
                    $build['wrapper']['delimeter4'] = [
                '#prefix' => '<div "class="delimeter">',
                '#markup' => '➤',
                '#suffix' => '</div>'
              ];
                    $build['wrapper']['gcmd_l5'] = \Drupal::service('plugin.manager.block')
                ->createInstance('facet_block:keywords_level5')
                ->build();
                    $build['wrapper']['gcmd_l5']['#prefix'] ='<div id="gcmd_l5">';
                    $build['wrapper']['gcmd_l5']['#suffix'] ='</div>';
                }
                if ($q[0] == 'keywords_level5') {
                    $build['wrapper']['delimeter5'] = [
                '#prefix' => '<div "class="delimeter">',
                '#markup' => '➤',
                '#suffix' => '</div>'
              ];
                    $build['wrapper']['gcmd_l6'] = \Drupal::service('plugin.manager.block')
                ->createInstance('facet_block:keywords_level6')
                ->build();
                    $build['wrapper']['gcmd_l6']['#prefix'] ='<div id="gcmd_l6">';
                    $build['wrapper']['gcmd_l6']['#suffix'] ='</div>';
                }
                if ($q[0] == 'keywords_level6') {
                    $build['wrapper']['delimeter6'] = [
                '#prefix' => '<div "class="delimeter">',
                '#markup' => '➤',
                '#suffix' => '</div>'
              ];
                    $build['wrapper']['gcmd_l7'] = \Drupal::service('plugin.manager.block')
                ->createInstance('facet_block:keywords_level7')
                ->build();
                    $build['wrapper']['gcmd_l7']['#prefix'] ='<div id="gcmd_l7">';
                    $build['wrapper']['gcmd_l7']['#suffix'] ='</div>';
                }
            }
        }
        $build['#cache'] = [
        'max-age' => 0,
        //'tags' =>$this->getCacheTags(),
          'contexts' => [
            //  'route',

            'url.path',
            'url.query_args',
          ],
        ];

        $build['#attached'] = [
          'library' => [
            'metsis_search/gcmd',
          ],
        ];

        return $build;
    }
    public function getCacheMaxAge()
    {
        return 1;
    }
}
