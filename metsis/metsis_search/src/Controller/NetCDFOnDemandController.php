<?php

namespace Drupal\metsis_search\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller to wrap the NetCDF OnDemandForm.
 */
class NetCDFOnDemandController extends ControllerBase {

  /**
   * Drupal\Core\Form\FormBuilderInterface definition.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * NetCDFOnDemandController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Display the NetCDFOnDemandForm.
   *
   * @return array
   *   Return NetCDFOnDemandForm.
   */
  public function content(Request $request, $datasetId) {
    // Get current user.
    $current_user = $this->currentUser();
    // Redirect anonymous users to login page.
    if ($current_user->isAnonymous()) {
      // This user is anonymous.
      $referer = $request->headers->get('referer');
      $refurl = parse_url($referer);
      $refpath = $refurl['path'];
      $refquery = $refurl['query'] ?? NULL;
      $ref_dest = $refpath;
      if ($refquery !== NULL) {
        $ref_dest .= '?' . $refquery;
      }
      $response = new AjaxResponse();
      $login_form = [];
      $login_form['login'] = $this->formBuilder->getForm('Drupal\user\Form\UserLoginForm');
      $login_form['login']['#action'] .= '?destination=' . urlencode($ref_dest);
      $login_form['register'] = [
        '#type' => 'markup',
        '#markup' => 'Or <a class="w3-button w3-border w3-theme-border button" href="/user/register">register</a> an account',
        '#allowed_tags' => ['a'],
      ];
      $response->addCommand(new OpenModalDialogCommand('Please login to request netCDF OnDemand.', $login_form, ['width' => '500']));
      return $response;
    }
    // This user is authenticated.
    $form = $this->formBuilder->getForm('Drupal\metsis_search\Form\NetCDFOnDemandForm', $datasetId);
    $form['#attached']['library'][] = 'core/drupal.states';
    return $form;
  }

}
