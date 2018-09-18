<?php

namespace Drupal\campaignion_email_to_target\Api;

use \Dflydev\Hawk\Credentials\Credentials;
use \Dflydev\Hawk\Client\ClientBuilder;

use \Drupal\little_helpers\Rest\Client as _Client;
use \Drupal\little_helpers\Rest\HttpError;

class Client extends _Client {
  CONST API_VERSION = 'v2';
  protected $hawk;
  protected $credentials;

  public static function fromConfig() {
    $c = variable_get('campaignion_email_to_target_credentials', []);
    foreach (['url', 'public_key', 'secret_key'] as $v) {
      if (!isset($c[$v])) {
        throw new ConfigError(
          'No valid e2t_api credentials found. The credentials must contain ' .
          'at least values for "url", "public_key" and "private_key".'
        );
      }
    }
    return new static($c['url'], $c['public_key'], $c['secret_key']);
  }

  public function __construct($url, $pk, $sk) {
    parent::__construct($url . '/' . static::API_VERSION);
    $this->credentials = new Credentials($sk, 'sha256', $pk);
    $this->hawk = ClientBuilder::create()->build();
  }

  /**
   * Return the endpoint URL.
   */
  public function getEndpoint() {
    return $this->endpoint;
  }

  /**
   * Add HAWK authentication headers to the request.
   */
  protected function sendRequest($url, array $options) {
    $options += ['method' => 'GET'];
    $method = $options['method'];
    $hawk_options = [];
    if (!empty($options['data']) || in_array($method, ['POST', 'PUT'])) {
      $options += ['data' => '', 'headers' => []];
      $options['headers'] += ['Content-Type' => ''];
      $hawk_options['payload'] = $options['data'];
      $hawk_options['content_type'] = $options['headers']['Content-Type'];
    }
    $hawk = $this->hawk->createRequest($this->credentials, $url, $method, $hawk_options);
    $header = $hawk->header();
    $options['headers'][$header->fieldName()] = $header->fieldValue();
    return parent::sendRequest($url, $options);
  }

  public function getDatasetList() {
    $datasets = [];
    $dataset_list = $this->get('');
    foreach ($dataset_list as $dataset) {
      $datasets[] = Dataset::fromArray($dataset);
    }
    return $datasets;
  }

  public function getDataset($key) {
    foreach ($this->getDatasetList() as $dataset) {
      if ($dataset->key == $key) {
        return $dataset;
      }
    }
    return NULL;
  }

  /**
   * Get targets by dataset key and a selector value.
   *
   * @param string $dataset_key
   *   The key of the dataset which’s targets we want to query.
   * @param string|null $selector
   *   An optional selector to narrow down the number of targets. The meaning
   *   of this selector depends on the dataset.
   *
   * @param array
   *   A nested array of targets.
   */
  public function getTargets($dataset_key, $selector) {
    $ds = $this->getDataset($dataset_key);
    try {
      if ($ds->isCustom) {
        return $this->getAllTargets($dataset_key);
      }
      else {
        return $this->getTargetsByPostcode($dataset_key, $selector);
      }
    }
    catch (HttpError $e) {
      if (in_array($e->getCode(), [400, 404])) {
        return [];
      }
      throw $e;
    }
  }

  /**
   * Gets all contacts for a dataset.
   *
   * This should only be used for custom datasets.
   *
   * @param string $dataset_key
   *   The key of the dataset.
   *
   * @param array
   *   A nested array of targets.
   */
  protected function getAllTargets($dataset_key) {
    $targets = $this->get("$dataset_key/contact");
    $constituences[]['contacts'] = $targets;
    return $constituences;
  }

  public function getTargetsByPostcode($dataset_key, $postcode) {
    $postcode = urlencode($postcode);
    return $this->get("$dataset_key/postcode/$postcode");
  }

  public function getAccessToken() {
    $res = $this->post('access-token');
    return $res['access_token'];
  }

}
