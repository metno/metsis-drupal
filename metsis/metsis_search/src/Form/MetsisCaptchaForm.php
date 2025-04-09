<?php

declare(strict_types=1);

namespace Drupal\metsis_search\Form;

use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\honeypot\HoneypotService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Metsis Search form.
 */
class MetsisCaptchaForm extends FormBase {


  /**
   * The injected honeypot service.
   *
   * @var \Drupal\honeypot\HoneypotService
   */
  protected $honeypot;

  /**
   * The constructor.
   *
   * @param \Drupal\honeypot\HoneypotService $honeypot
   *   The honeypot service.
   */
  public function __construct(HoneypotService $honeypot) {
    $this->honeypot = $honeypot;
  }

  /**
   * Create the container instance.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('honeypot')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'metsis_search_metsis_captcha';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => $this->t("Solve the captcha to coninue to the search interface."),
    ];

    $form['captcha'] = [
      '#type' => 'captcha',
      '#captcha_type' => 'captcha/Math',
    ];

    $form['confirm-submit'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I do not have bad intentions.'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Confirm'),
      ],
      '#states' => [
        'disabled' => [
          ':input[name="confirm-submit"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $this->honeypot->addFormProtection($form, $form_state, ['honeypot', 'time_restriction']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $destination_url = $this->getRequest()->query->get('destination_url');

    // Check if the destination URL is valid.
    if (filter_var($destination_url, FILTER_VALIDATE_URL, FILTER_SANITIZE_URL)) {
      // dpm($destination_url);
      $dest_url = filter_var($destination_url, FILTER_VALIDATE_URL, FILTER_SANITIZE_URL);
      // dpm($dest_url);
      $form_state->setRedirectUrl(Url::fromUri($dest_url));
    }
    else {
      // If the URL is not valid, send a client error response.
      $response = new Response(
      'Invalid destination URL.',
      Response::HTTP_BAD_REQUEST
      );
      $response->send();
    }
  }

}
