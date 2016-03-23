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
use \Drupal\campaignion_newsletters\Subscription;

class Optivo extends ProviderBase {
  protected $account;
  protected $sessionService;
  protected $recipientListService;
  protected $recipientService;
  protected $session;
  protected $sessionId;

  public static function fromParams(array $params) {
    return new static(
      $params,
      new Client("https://api.broadmail.de/soap11/RpcSession?wsdl"),
      new SessionClient("https://api.broadmail.de/soap11/RpcRecipientList?wsdl"),
      new SessionClient("https://api.broadmail.de/soap11/RpcRecipient?wsdl")
    );
  }

  /**
   * Constructor. Creates an active session with Optivo.
   */
  public function __construct(array $params, $session_service, $recipient_list_service, $recipient_service) {
    $this->account = $params['name'];
    $this->sessionService = $session_service;
    $this->recipientListService = $recipient_list_service;
    $this->recipientService = $recipient_service;
    $this->login($params['mandatorId'], $params['username'], $params['password']);
  }

  public function login($mandatorId, $username, $password) {
    $session_id = $this->sessionService->login($mandatorId, $username, $password);
    $this->recipientListService->setSessionId($session_id);
    $this->recipientService->setSessionId($session_id);
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
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
    $service = $this->recipientService;
    $receivers = $service->getAll($list->identifier, 'email');
    return $receivers;
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * @return: True on success.
   */
  public function subscribe($list, $mail, $data, $opt_in = 0) {
    $service = $this->recipientService;
    $recipientId = $mail;
    $address = $mail;
    $attributeNames = array();
    $attributeValues = array();
    $status = $service->add2(
      $list->identifier,
      $opt_in,
      $recipientId,
      $address,
      $attributeNames,
      $attributeValues
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
      case 5: // Recipient is already in the list.
        return TRUE;
      case 6: // Recipient has been filtered.
        throw new ApiError('Optivo', 'Recipient has been filtered (@mail)', ['@mail' => $mail], $status);
      case 7: // A general error occured.
        throw new ApiError('Optivo', 'A general error occured when adding @mail', ['@mail' => $mail], $status);
    }

    throw new ApiError('Optivo', 'API returned unexpected status code', [], $status);
  }

  /**
   * Unsubscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   *
   * @return: True on success.
   */
  public function unsubscribe($list, $mail) {
    $service = $this->recipientService;
    $service->remove($list->identifier, $mail);
    return TRUE;
  }

}
