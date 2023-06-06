<?php

namespace Drupal\sirac_sso\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Error;
use Drupal\sirac_sso\SiracServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SSOController.
 */
class SSOController extends ControllerBase {

  private $siracService;

  /**
   * The Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  public function __construct(SiracServiceInterface $siracService, RequestStack $request_stack, MessengerInterface $messenger) {
    $this->siracService = $siracService;
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('sirac'),
      $container->get('request_stack'),
      $container->get('messenger')
    );
  }

  /**
   * Samllogin.
   *
   * si arriva qui solo da richiesta in reverse proxy da Sirac, con gli header di autenticazione
   */
  public function samlLogin(Request $request) {
    $url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();


    //$headers = $request->headers;
    $headers = apache_request_headers();


    try {
      $this->siracService->siracSSO($headers);
      $url = Url::fromRoute('user.page', [], ['absolute' => TRUE])->toString();
    }
    catch (\Exception $e) {
      $this->handleException($e, 'processing Sirac authentication headers');
    }

    return new RedirectResponse($url);
  }


  /**
   * Displays error message and logs full exception.
   *
   * @param \Exception $exception
   *   The exception thrown.
   * @param string $while
   *   A description of when the error was encountered.
   */
  protected function handleException(\Exception $exception, $while = '') {
    if ($while) {
      $while = " $while";
    }
    // We use the same format for logging as Drupal's ExceptionLoggingSubscriber
    // except we also specify where the error was encountered. (The options are
    // limited, so we make this part of the message, not a context parameter.)
    $error = Error::decodeException($exception);
    unset($error['severity_level']);
    $this->getLogger('sirac_sso')
      ->critical("%type encountered while $while: @message in %function (line %line of %file).", $error);
    // Don't expose the error to prevent information leakage; the user probably
    // can't do much with it anyway. But hint that more details are available.
    $this->messenger->addError("Error $while; details have been logged.");
  }

}
