<?php
/**
 * @file
 * implements NewsletterProvider using Optivos API.
 */

namespace Drupal\campaignion_newsletters_optivo;

use \Drupal\campaignion_newsletters\NewsletterList;
use \Drupal\campaignion_newsletters\ProviderBase;
use \Drupal\campaignion_newsletters\Subscription;

class Optivo extends ProviderBase {
  protected $account;
  protected $session;
  protected $sessionId;

  /**
   * Constructor. Creates an active session with Optivo.
   */
  public function __construct(array $params) {
    $this->account = $params['name'];
    $this->session = BroadmailApiSoap::SessionWebservice();
    $this->sessionId = $this->session->login(
        $params['madatorId'],
        $params['username'],
        $params['password']
    );
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of associative array
   *   (properties: identifier, title, source, language).
   */
  public function getLists() {
    $service = BroadmailApiSoap::RecipientListWebservice();
    $list_ids = $service->getAllIds($this->sessionId);
    $lists = array ();
    foreach ( $list_ids as $id ) {
      $name = $service->getName($this->sessionId, $id);
      $attributes = $service->getAttributeNames($this->sessionId, $id, 'en');
      $lists [] = NewsletterList::fromData( array (
        'identifier' => $id,
        'title' => $name,
        'source' => 'Optivo-' . $this->account,
        'data' => ( object ) $attributes
      // @TODO: find out what to do with language settings.
      ));
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
    $service = BroadmailApiSoap::RecipientWebservice();
    $receivers = $service->getAll(
      $this->sessionId,
      $list->identifier,
      'email'
    );
    return $receivers;
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * @return: True on success.
   */
  public function subscribe($list, $mail, $data, $opt_in = 0) {
    $service = BroadmailApiSoap::RecipientWebservice();
    $recipientId = $mail; // @TODO: Check docs what this should be.
    $address = ''; // Perhaps $mail.
    $attributeNames = array();
    $attributeValues = array();
    $service->add2(
      $this-sessionId,
      $list->identifier,
      $opt_in,
      $recipientId,
      $address,
      $attributeNames,
      $attributeValues
    );

    return true;
  }

  /**
   * Unsubscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   *
   * @return: True on success.
   */
  public function unsubscribe($list, $mail) {
    $service = BroadmailApiSoap::RecipientWebservice();
    $service->remove($this-sessionId, $list->identifier, $mail);

    return true;
  }

  /**
   * Wraps Optivo API calls to deal with it's results and errors.
   */
  protected function call($service, $function) {
//     $arguments = func_get_args();
//     array_shift($arguments);
//     $result = array('data' => NULL);
//     try {
//       $result = call_user_func_array(
//         array($service, $function),
//         $arguments
//       );
//     }
//     catch(\Optivo_ValidationError $e) {
//       throw new \Drupal\campaignion_newsletters\ApiPersistentError('Optivo', $e->getMessage(), array(), $e->getCode(), $e->getFile(), $e);
//     }
//     catch(\Optivo_Error $e) {
//       $v['@error'] = $e->getMessage();
//       watchdog('Optivo', 'Optivo API Exception: "@error"', $v, WATCHDOG_INFO);
//     }
//     if (!empty($result['errors'])) {
//       foreach ($result['errors'] as $error) {
//         $v['@error'] = $error['error'];
//         $v['@code'] = $error['code'];
//         watchdog('Optivo', '@error (@code)', $v);
//         return false;
//       }
//     }
//     else {
//       return $result['data'];
//     }
  }

  /**
   * Protected clone method to prevent cloning of the singleton instance.
   */
  protected function __clone() {}

  /**
   * Protected unserialize method to prevent unserializing of singleton.
   */
  protected function __wakeup() {}
}


class BroadmailApiSoap {
  /**
   * @return RecipientListWebservice
   */
  public static function RecipientListWebservice()
  {
    return new SoapClient("https://api.broadmail.de/soap11/RpcRecipientList?wsdl");
  }

  /**
   * @return RecipientWebservice
   */
  public static function RecipientWebservice()
  {
    return new SoapClient("https://api.broadmail.de/soap11/RpcRecipient?wsdl");
  }

  /**
   * @return SessionWebservice
   */
  public static function SessionWebservice()
  {
    return new SoapClient("https://api.broadmail.de/soap11/RpcSession?wsdl");
  }
}
