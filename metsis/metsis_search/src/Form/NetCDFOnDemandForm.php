<?php

declare(strict_types=1);

namespace Drupal\metsis_search\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\search_api\Entity\Index;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\RequestException;

/**
 * Provides a NetCDF OnDemand Form for one product.
 */
class NetCDFOnDemandForm extends FormBase {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The RendererInterfae.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The FormBuilder service .
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new NetCDFOnDemandForm object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The formBuilder service.
   */
  public function __construct(
    ClientInterface $http_client,
    RendererInterface $renderer,
    FormBuilderInterface $formBuilder,
  ) {
    $this->httpClient = $http_client;
    $this->renderer = $renderer;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('http_client'),
    $container->get('renderer'),
    $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'metsis_search_netcdf_on_demand';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $datasetId = NULL): array {
    // Attach the library necessary for using Form API's #states.
    $form['#attached']['library'][] = 'core/drupal.states';
    // Get current user.
    $current_user = $this->currentUser();
    // Redirect anonymous users to login page.
    if ($current_user->isAnonymous()) {
      // This user is anonymous.
      $response = new AjaxResponse();
      // $response->addCommand(new RedirectCommand(\Drupal\Core\Url::fromRoute('user.login')->toString()));
      $login_form = [];
      $login_form['login'] = $this->formBuilder->getForm('\Drupal\user\Form\UserLoginForm');
      $login_form['register'] = [
        '#type' => 'markup',
        '#markup' => 'Or <a class="w3-button w3-border w3-theme-border button" href="/user/register">register</a> an account',
        '#allowed_tags' => ['a'],
      ];
      $response->addCommand(new OpenModalDialogCommand('Please login to request netCDF OnDemand.', $login_form, ['width' => '500']));
      return $response;
    }
    $index = Index::load('metsis');

    /** @var \Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend $backend */
    $backend = $index->getServerInstance()->getBackend();

    $connector = $backend->getSolrConnector();

    $solarium_query = $connector->getSelectQuery();
    $solarium_query->setQuery('id:' . $datasetId);
    // $solarium_query->addSort('sequence_id', Query::SORT_ASC);.
    $solarium_query->setRows(1);
    $fields[] = 'metadata_identifier,title';
    // $fields[] = 'mmd_xml_file';
    $solarium_query->setFields($fields);
    $result = $connector->execute($solarium_query);
    // The total number of documents found by Solr.
    $found = $result->getNumFound();
    /* Throw not found exception to make drupal create 404 page when not in index */
    if ($found === 0) {
      throw new NotFoundHttpException();
    }
    foreach ($result as $doc) {
      $fields = $doc->getFields();
    }
    $mid = $fields['metadata_identifier'] ?? '';
    $form_state->set('product_id', $mid);
    $title = $fields['title'][0] ?? '';
    $form_state->set('product_id', trim($title));
    // Get current user's email.
    $user_email = $current_user->getEmail();

    $form['message'] = [
      '#type' => 'markup',
      '#prefix' => '<div class="w3-container>',
      '#suffix' => '</div>',
      '#markup' => "A netCDF-file will be generated from product <strong>$title</strong>. An email with a download link will be sent to <strong><em>$user_email</em></strong>",
      '#required' => TRUE,
    ];
    $form['confirm'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Are you sure you want to order netCDF on demand?'),
      '#required' => TRUE,
    ];

    // Status message placeholder.
    $form['msg-wrapper'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="edit-status-messages-wrapper" data-drupal-selector="edit-status-messages-wrapper">',
      '#suffix' => '</div>',
      '#markup' => '',
    ];
    $form['msg-wrapper']['api-status-messages'] = [
      '#prefix' => '<div id="edit-status-messages" data-drupal-selector="edit-status-messages">',
      '#suffix' => '</div>',
      '#type' => 'status-messages',

    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#ajax' => [
        'callback' => '::ajaxSubmit',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Processing...'),
        ],
      ],
      '#states' => [
        'disabled' => [
          ':input[name="confirm"]' => ['checked' => FALSE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * AJAX callback handler for the form submission.
   */
  public function ajaxSubmit(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If api_message exists, add the message.
    $api_message = $form_state->get('api_message');
    if ($api_message == NULL) {
      // Update the status messages area in the form.
      $form['msg-wrapper']['api-status-messages'] = [
        '#prefix' => '<div id="edit-status-messages" data-drupal-selector="edit-status-messages">',
        '#suffix' => '</div>',
        '#type' => 'status_messages',
      ];

      // Replace the old status messages area with the new one.
      $response->addCommand(new ReplaceCommand('#edit-status-messages', $form['msg-wrapper']['api-status-messages']));
    }
    if ($api_message != NULL) {
      // Clear all previous messages.
      $this->messenger()->deleteAll();
      if ($api_message['success']) {
        $this->messenger()->addMessage($api_message['message']);
      }
      else {
        $this->messenger()->addError($api_message['message']);
      }

      // Replace the status messages area again to include api_message.
      $form['msg-wrapper']['api-status-messages'] = [
        '#prefix' => '<div id="edit-status-messages" data-drupal-selector="edit-status-messages">',
        '#suffix' => '</div>',
        '#type' => 'status_messages',
      ];
      $button = &$form['actions']['submit'];
      $button['#attributes']['disabled'] = 'disabled';
      $response->addCommand(new ReplaceCommand('#edit-status-messages-wrapper', $form['msg-wrapper']));
    }

    // If the form doesn't have any errors, disable the submit button.
    if (!$form_state->hasAnyErrors()) {
      $button = &$form['actions']['submit'];
      $button['#attributes']['disabled'] = 'disabled';
      // dpm($form);
      // dpm($form_state);
      $response->addCommand(new InvokeCommand('#edit-submit', 'attr', ['disabled', 'true']));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Validate your form here.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Don't use a page redirect when form is submitted via AJAX.
    if (empty($form_state->getErrors())) {
      $pid = $form_state->get('product_id');
      // Send the JSON request to the API endpoint.
      $response = $this->sendJsonRequest($pid);
      $form_state->set('api_message', $response);
    }
  }

  /**
   * Sends a JSON request to the API endpoint.
   *
   * @return array
   *   An associative array with keys:
   *   - success: A boolean indicating whether the request was successful.
   *   - message: The response message from the API, or an error message.
   */
  private function sendJsonRequest(string $product_id) {
    $config = $this->config('metsis_search.settings');
    $api_endpoint = $config->get('netcdf_ondemand_service_endpoint');

    $current_user = $this->currentUser();
    $user_email = $current_user->getEmail();

    $data = [
      'inputs' => [
        'email' => $user_email,
    // Replace with your actual products.
        'products' => [$product_id],
      ],
    ];

    try {
      $response = $this->httpClient->request('POST', $api_endpoint, [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($data),
      ]);

      $status = $response->getStatusCode();
      if ($status === 200) {
        $body = json_decode($response->getBody()->getContents(), TRUE);
        return [
          'success' => TRUE,
        // Adjust this based on your API's response structure.
          'message' => $body['value'],
        ];
      }
      else {
        return [
          'success' => FALSE,
          'message' => $this->t('Received non-200 response status (@status).', ['@status' => $status]),
        ];
      }
    }
    catch (RequestException $e) {
      return [
      'success' => FALSE,
      'message' => $this->t('An error occurred while trying to send the request: @error', ['@error' => $e->getMessage()])
      ];
    }
    catch (ConnectException $e) {
      $response = $e->getRequest();
      $responseBody = (string) $response->getBody();
      return [
      'success' => FALSE,
      'message' => $this->t('An error occurred while trying to send the request: @error', ['@error' => $responseBody])
      ];
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
      $responseBody = (string) $response->getBody();
      return [
      'success' => FALSE,
      'message' => $this->t('An error occurred while trying to send the request: @error', ['@error' => $responseBody])
      ];
    }
  }

}
