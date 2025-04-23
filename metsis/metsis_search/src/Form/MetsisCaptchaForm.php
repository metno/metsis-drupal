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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $query_params = $this->getRequest()->query->all();

    // Extract the destination URL.
    $destination_url = $query_params['destination_url'] ?? NULL;

    // Remove the destination_url from the parameters array.
    unset($query_params['destination_url']);

    // Check if the destination URL is valid.
    if (filter_var($destination_url, FILTER_VALIDATE_URL)) {
      // Parse the URL to handle query parameters correctly.
      $parsed_url = parse_url($destination_url);
      $query = $parsed_url['query'] ?? '';

      // Reconstruct the full URL with original and additional query parameters.
      $full_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];

      // Add original query parameters.
      if ($query) {
        $full_url .= '?' . $query;
      }

      // Append additional query parameters from the request.
      if (!empty($query_params)) {
        $additional_query = http_build_query($query_params);
        $full_url .= $query ? '&' . $additional_query : '?' . $additional_query;
      }
      // dpm($full_url);
      // Redirect to the constructed URL.
      $form_state->setRedirectUrl(Url::fromUri($full_url));
      $this->messenger()->addStatus($this->t('The captcha has been solved successfully.'));
    }
    else {
      // If the URL is not valid, send a client error response.
      $response = new Response(
      'Invalid destination URL.',
      Response::HTTP_BAD_REQUEST
          );
      $response->send();
      exit;
    }
  }

}
