<?php

namespace Drupal\metsis_basket\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\search_api\Entity\Index;


use Drupal\metsis_dashboard_bokeh\Controller\DashboardBokehController;

/**
 * Default controller for the metsis_basket module.
 *
 * {@inheritdoc}.
 */
class MetsisBasketController extends DashboardBokehController {

  /**
   * Show the basket.
   */
  public function myBasket() {
    // Get the current user_id.
    $user_id = (int) \Drupal::currentUser()->id();

    // Get the refering page.
    $session = \Drupal::request()->getSession();
    // $referer = $session->get('back_to_search');.
    $referer = \Drupal::request()->headers->get('referer');
    $pattern1 = '/metsis\/search/i';
    if (TRUE === preg_match($pattern1, (string) $referer)) {
      $referer = '/metsis/search';
    }
    else {
      $session->set('basket_ref', $referer);
    }
    $build['content'] = [
      '#type' => 'container',
    // Create content wrapper.
    ];
    $build['content']['back'] = [
      '#prefix' => '<div class="w3-container w3-panel w3-leftbar"><span>',
      '#suffix' => '</span></div>',

      '#markup' => '<a class="w3-btn w3-border-black" href="' . $session->get('basket_ref') . '">Go back to search </a>',
    ];

    // Get markup for the Bokeh Dashboard.
    $build[] = self::postDatasource();

    // Show the basket table, if it has any entries.
    if ($this->getUserItemCount($user_id) > 0) {
      $build['content']['basket'] = [
        '#prefix' => '<div class="w3-container w3-leftbar w3-panel">',
        '#suffix' => '</div>',
        '#type' => 'details',
        '#title' => $this->t('Show my basket (remove items)'),
        '#attributes' => ['class' => ['basketDetails']],
      ];
      $build['content']['basket']['view'] = views_embed_view('basket_view', 'embed_1');
    }
    /*  $build['content']['basket']['view']['#cache'] = [

    'max-age' => 0,
    ];
     */

    $build['content']['loading'] = [
      '#type' => "markup",
      '#prefix' => '<div id="dash-loader-wrapper">',
      '#markup' => $this->t("Dashboard is loading..."),
      '#suffix' => '<img id="dashTrobber" src="/core/misc/throbber-active.gif"></div>',
      '#allowed_tags' => ['img'],
      '#attributes' => [
        'class' => 'dashLoader',
      ],
    ];

    $build['#cache'] = [
      'contexts' => ['user', 'session'],
      'tags' => ['basket:user:' . $user_id],
      'keys' => ['views', 'basket_view', 'embed_1'],
      // 'max-age' => 25,.
    ];
    // $build['#theme'] = 'dashboard_page';
    // $build['#type'] = 'container';
    // $build['#theme'] = 'metsis_basket-template';.
    $build['#attached'] = [
      'library' => [
        'core/jquery.ui',
        'leaflet/leaflet',
        'metsis_basket/basket_view',
    // 'metsis_dashboard_bokeh/dashboard',.
      ],
    ];
    $build['#attributes'] = [
      'class' => ['myBasket'],
    ];

    return $build;
  }

  /**
   * Get basket listing.
   */
  public function listing($iid) {
    \Drupal::logger('metsis_basket')->debug("Listing item with iid: " . $iid);
    $view_builder = \Drupal::entityTypeManager()
      ->getViewBuilder('metsis_basket_item');
    $entity = \Drupal::entityTypeManager()
      ->getStorage('metsis_basket_item')->load($iid);
    return $view_builder
      ->view($entity, 'full');
  }

  /**
   * Add item to basket.
   */
  public function add($metaid) {
    if (\Drupal::currentUser()->isAuthenticated()) {
      // This user is logged in.
      $user_id = (int) \Drupal::currentUser()->id();
      $user_name = \Drupal::currentUser()->getAccountName();

      // Generate uuid from uuid service.
      $uuid_service = \Drupal::service('uuid');
      $uuid = $uuid_service->generate();
      // Get info from solr given metaid that we put in the basket.
      $arr = $this->msbGetResources($metaid);

      $feature_type = $arr[0];
      $title = $arr[1];
      $dar = $arr[2];

      /*
      \Drupal::logger('metsis_basket')
      ->debug("Adding product to basket:");
      \Drupal::logger('metsis_basket')
      ->debug("title: @title", ['@title'  => $title]);
      \Drupal::logger('metsis_basket')
      ->debug("feature_type: @ft", ['@ft'  => $feature_type]);
      \Drupal::logger('metsis_basket')
      ->debug("dar: @ft", ['@ft'  => Json::encode($dar)]);
       */

      // Te fields we put in database.
      $fields = [
        'uid' => $user_id,
        'uuid' => $uuid,
        'user_name' => $user_name,
        'title' => $title,
        'session_id' => session_id(),
        'basket_timestamp' => time(),
        'metadata_identifier' => $metaid,
        'feature_type' => $feature_type,
        'dar' => serialize($dar),
      ];
      // dpm($res);
      \Drupal::database()->insert('metsis_basket')->fields($fields)->execute();

      // \Drupal::logger('metsis_basket')
      // ->debug("dashboard json: " . $dashboard_json);.
      $basket_count = $this->getUserItemCount($user_id);
      $ids = $this->getUserItemIds($user_id);
      $session = \Drupal::request()->getSession();
      $session->set('basket_items', $ids);

      $selector = '#myBasketCount';
      $markup = '<span id="myBasketCount" class="w3-badge w3-green">' . $basket_count . '</span>';
      $selector_id = str_replace('_', '-', $metaid);
      $selector_id = str_replace('.', '-', $selector_id);

      $response = new AjaxResponse();
      $response->addCommand(new HtmlCommand('#addtobasket-' . $selector_id, 'Add to Basket &#10004;'));
      $response->addCommand(new HtmlCommand($selector, $markup));
      // $response->addCommand(new MessageCommand("Dataset %added to basket.", ['%added' => $metaid]));
      Cache::invalidateTags(['basket:user:' . $user_id]);
      return $response;
    }
    else {
      // This user is anonymous.
      $response = new AjaxResponse();
      // $response->addCommand(new RedirectCommand(\Drupal\Core\Url::fromRoute('user.login')->toString()));
      $login_form = [];
      $login_form['login'] = \Drupal::formBuilder()->getForm('\Drupal\user\Form\UserLoginForm');
      $login_form['register'] = [
        '#type' => 'markup',
        '#markup' => 'Or <a class="w3-button w3-border w3-theme-border button" href="/user/register">register</a> an account',
        '#allowed_tags' => ['a'],
      ];
      $response->addCommand(new OpenModalDialogCommand('Please login to add products to the basket', $login_form, ['width' => '500']));
      return $response;
    }
  }

  /**
   * Count basket items for current user.
   */
  public static function getUserItemCount($user_id) {
    $query = \Drupal::database()->select('metsis_basket', 'm');
    $query->fields('m', ['iid']);
    $query->condition('m.uid', $user_id, '=');
    $results = $query->execute()->fetchAll();
    if (is_null($results)) {
      $count = 0;
    }
    else {
      $count = count($results);
    }
    // dpm($count);
    return $count;
  }

  /**
   * Get user item ids.
   */
  public static function getUserItemIds($user_id) {
    $query = \Drupal::database()->select('metsis_basket', 'm');
    $query->fields('m', ['metadata_identifier']);
    $query->condition('m.uid', $user_id, '=');
    $results = $query->execute()->fetchCol();
    return $results;
  }

  /**
   * Get resources (WMS, DAP, HTTP)
   */
  public static function msbGetResources($metadata_identifier) {
    /** @var \Drupal\search_api\Entity\Index $index  TODO: Change to metsis when prepeare for release */
    $index = Index::load('metsis');

    /** @var SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();

    \Drupal::logger('metsis_basket_solr_query')->debug("metadata_identifier: " . $metadata_identifier);
    $solarium_query->setQuery('metadata_identifier:' . $metadata_identifier);

    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);
    // $solarium_query->setRows(2);.
    $solarium_query->setFields([
      'data_access_url_http',
      'data_access_url_ftp',
      'data_access_url_odata',
      'data_access_url_opendap',
      'data_access_url_ogc_wms',
      'feature_type',
      'title',
    ]);

    $result = $connector->execute($solarium_query);

    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    \Drupal::logger('metsis_basket_solr_query')->debug("found :" . $found);
    // The total number of documents returned from the query.
    // $count = $result->count();
    // Check the Solr response status (not the HTTP status).
    // Can't find much documentation for this apart from https://lucene.472066.n3.nabble.com/Response-status-td490876.html#a3703172.
    // $status = $result->getStatus();
    $title = 'NA';
    $feature_type = 'NA';
    $dar = [];
    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }
    if (isset($fields['data_access_url_http'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['http'] = $fields['data_access_url_http'];
    }
    if (isset($fields['data_access_url_ftp'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['ftp'] = $fields['data_access_url_ftp'];
    }
    if (isset($fields['data_access_url_odata'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['odata'] = $fields['data_access_url_odata'];
    }
    if (isset($fields['data_access_url_opendap'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['opendap'] = $fields['data_access_url_opendap'];
    }
    if (isset($fields['data_access_url_ogc_wms'])) {
      // An array of documents. Can also iterate directly on $result.
      $dar['OGC:WMS'] = $fields['data_access_url_ogc_wms'];
    }
    if (isset($fields['feature_type'])) {
      // An array of documents. Can also iterate directly on $result.
      $feature_type = $fields['feature_type'];
    }
    if (isset($fields['title'])) {
      // An array of documents. Can also iterate directly on $result.
      $title = $fields['title'][0];
    }
    return [$feature_type, $title, $dar];
  }

}
