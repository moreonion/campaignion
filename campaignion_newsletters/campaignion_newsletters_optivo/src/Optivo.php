<?php
/**
 * @file
 * implements NewsletterProvider using Optivos API.
 */

namespace Drupal\campaignion_newsletters_optivo;

use \Drupal\campaignion_newsletters\ApiError;
use \Drupal\campaignion_newsletters\ApiPersistentError;
use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\ProviderBase;
use \Drupal\campaignion_newsletters\QueueItem;
use \Drupal\campaignion_newsletters\Subscription;

class Optivo extends ProviderBase {
  protected $account;
  protected $optinProcessId;
  protected $sessionService;
  protected $recipientListService;
  protected $recipientService;
  protected $sessionId = NULL;
  protected $credentials = [];

  public static function fromParameters(array $params) {
    return new static(
      $params,
      new Client("https://api.broadmail.de/soap11/RpcSession?wsdl"),
      new SessionClient("https://api.broadmail.de/soap11/RpcRecipientList?wsdl"),
      new RecipientServiceClient("https://api.broadmail.de/soap11/RpcRecipient?wsdl"),
      new SessionClient("https://api.broadmail.de/soap11/RpcOptinProcess?wsdl")
    );
  }

  /**
   * Constructor. Creates an active session with Optivo.
   */
  public function __construct(array $params, $session_service, $recipient_list_service, $recipient_service, $optin_service) {
    $this->account = $params['name'];
    $this->optinProcessId = $params['optinProcessId'];
    $this->sessionService = $session_service;
    $this->recipientListService = $recipient_list_service;
    $this->recipientService = $recipient_service;
    $this->optinService = $optin_service;
    $this->credentials = [$params['mandatorId'], $params['username'], $params['password']];
  }

  /**
   * Login using stored credentials. If needed.
   */
  protected function ensureLogin() {
    if (!$this->sessionId) {
      list($m, $u, $p) = $this->credentials;
      $this->sessionId = $this->login($m, $u, $p);
    }
  }

  /**
   * Log out at the end of the object's life-time.
   */
  public function __destruct() {
    if ($this->sessionId) {
      $this->logout($this->sessionId);
    }
  }

  public function login($mandatorId, $username, $password) {
    $session_id = $this->sessionService->login($mandatorId, $username, $password);
    $this->recipientListService->setSessionId($session_id);
    $this->recipientService->setSessionId($session_id);
    $this->optinService->setSessionId($session_id);
    return $session_id;
  }

  public function logout($session_id) {
    $this->sessionService->logout($session_id);
    $this->recipientListService->setSessionId(NULL);
    $this->recipientService->setSessionId(NULL);
    $this->optinService->setSessionId(NULL);
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
    $this->ensureLogin();
    $service = $this->recipientListService;
    $list_ids = $service->getAllIds();
    $lists = [];
    foreach ( $list_ids as $id ) {
      $name = $service->getName($id);
      $attributes = $service->getAttributeNames($id, 'en');
      $lists[] = NewsletterList::fromData([
        'identifier' => $id,
        'title' => $name,
        'source' => 'Optivo-' . $this->account,
        'data' => (object) ['attributeNames' => $attributes],
      ]);
    }
    return $lists;
  }

  /**
   * Fetches current lists of subscribers from the provider.
   *
   * @return array
   *   an array of subscribers.
   */
  public function getSubscribers($list) {
    $this->ensureLogin();
    $service = $this->recipientService;
    $receivers = $service->getAll($list->identifier, 'email');
    return $receivers;
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   */
  public function subscribe(NewsletterList $list, QueueItem $item) {
    $this->ensureLogin();
    $service = $this->recipientService;
    $mail = $item->email;
    $recipientId = $mail;
    $address = $mail;
    $data = $item->data + ['names' => [], 'values' => []];
    $status = $service->add2(
      $list->identifier,
      $item->optIn() && $this->optinProcessId ? $this->optinProcessId : 0,
      $recipientId,
      $address,
      $data['names'],
      $data['values']
    );
    // Status codes and comments according to v1.13 of the SOAP API.
    switch ($status) {
      case 0: // Recipient has been added.
        return TRUE;
      case 1: // Recipient validation failed.
        throw new ApiPersistentError('Optivo', 'Recipient validation failed (@mail)', ['@mail' => $mail], $status);
      case 2: // Recipient is unsubscribed.
        throw new ApiError('Optivo', 'Recipient is unsubscribed (@mail)', ['@mail' => $mail], $status);
      case 3: // Recipient is blacklisted.
        throw new ApiPersistentError('Optivo', 'Recipient is blacklisted (@mail)', ['@mail' => $mail], $status);
      case 4: // Recipient is bounce overflowed.
        throw new ApiPersistentError('Optivo', 'Recipient is bounce overflowed (@mail)', ['@mail' => $mail], $status);
      case 5: // Recipient is already in the list so update the data.
        $service->setAttributes($list->identifier, $mail, $data['names'], $data['values']);
        return TRUE;
      case 6: // Recipient has been filtered.
        throw new ApiError('Optivo', 'Recipient has been filtered (@mail)', ['@mail' => $mail], $status);
      case 7: // A general error occured.
        throw new ApiError('Optivo', 'A general error occured when adding @mail', ['@mail' => $mail], $status);
    }

    throw new ApiError('Optivo', 'API returned unexpected status code', [], $status);
  }

  /**
   * Update user data.
   */
  public function update(NewsletterList $list, QueueItem $item) {
    $this->ensureLogin();
    $service = $this->recipientService;
    $data = $item->data + ['names' => [], 'values' => []];
    $service->setAttributes($list->identifier, $item->email, $data['names'], $data['values']);
  }


  /**
   * Unsubscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   */
  public function unsubscribe(NewsletterList $list, QueueItem $item) {
    $this->ensureLogin();
    $service = $this->recipientService;
    $service->remove($list->identifier, $item->email);
    return TRUE;
  }

  /**
   * Get the subscriber-data for a subscription object.
   */
  protected function attributeData($subscription) {
    $list = $subscription->newsletterList();
    $names = [];
    $values = [];

    if ($source = $this->getSource($subscription, 'optivo')) {
      foreach ($list->data->attributeNames as $lname) {
        $name = strtr(drupal_clean_css_identifier(strtolower($lname)), '-', '_');
        if ($value = $source->value($name)) {
          $names[] = $lname;
          $values[] = $value;
        }
      }
    }
    return ['names' => $names, 'values' => $values];
  }

  /**
   * {@inheritdoc}
   */
  public function data(Subscription $subscription) {
    $data = $this->attributeData($subscription);
    $fingerprint = sha1(serialize($data));
    return array($data, $fingerprint);
  }

  /**
   * Get a list of all optin processes for this account.
   */
  public function getOptinProcessOptions() {
    $this->ensureLogin();
    $service = $this->optinService;
    $ids = $service->getIds();
    $options = [];
    foreach ($ids as $id) {
      $options[$id] = $service->getName($id);
    }
    return $options;
  }

}
