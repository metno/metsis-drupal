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

    // Prepare ajax response object.
    $response = new AjaxResponse();

    // Redirect anonymous users to login page.
    if (!$current_user->isAuthenticated()) {
      $this->getLogger("netcdfOnDemand")->info("User not logged in. Loading login form");
      $login_form = [];
      $login_form['messges'] = [
        '#type' => 'markup',
        '#markup' => '<div data-drupal-messages=></div>',
        '#allowed_tags' => ['div'],
      ];
      $login_form['login'] = $this->formBuilder->getForm('Drupal\metsis_lib\Form\MetsisAjaxLoginForm', $datasetId);
      $login_form['register'] = [
        '#type' => 'markup',
        '#markup' => 'Or <a class="w3-button w3-border w3-theme-border button" href="/user/register">register</a> an account',
        '#allowed_tags' => ['a'],
      ];
      $response->addCommand(new OpenModalDialogCommand('Please login to request CF-NetCDF file.', $login_form, ['width' => '550']));
      return $response;
    }
    else {
      // This user is authenticated.
      $this->getLogger("netcdfOnDemand")->info("User authenticated. Loading netCDFOnDemandForm form");
      $form = $this->formBuilder->getForm('Drupal\metsis_search\Form\NetCDFOnDemandForm', $datasetId);
      $form['#attached']['library'][] = 'core/drupal.states';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $form['#attached']['library'][] = 'core/drupal';
      $form['#attached']['library'][] = 'core/jquery';
      $response->addCommand(new OpenModalDialogCommand('Request CF-NetCDF file.', $form, ['width' => '550']));
      return $response;
    }
  }

}
