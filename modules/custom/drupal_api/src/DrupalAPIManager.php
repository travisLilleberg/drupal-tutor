<?php

namespace Drupal\drupal_api;

use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;

class DrupalAPIManager implements DrupalAPIManagerInterface {

  private ClientInterface $client;
  private MessengerInterface $messenger;

  public function __construct(ClientInterface $client, MessengerInterface $messenger) {
    $this->client = $client;
    $this->messenger = $messenger;
  }

  /**
   * Gets the latest modules or themes from drupal.org.
   *
   * @param string $type
   *   'module' or 'theme'. Default is 'module.
   *
   * @return array
   */
  public function getLatest(string $type = 'module') {
    $data = $this->pullData($type);
    if (empty($data) || empty($data->list)) {
      return [];
    }

    $items = [];
    foreach ($data->list as $item) {
      $items[] = [
        'url' => $item->url,
        'name' => $item->title,
        'created' => $item->created,
        'description' => empty($item->body) ? '' : $item->body->value,
      ];
    }
    return $items;
  }

  /**
   * Pulls module or theme data from drupal.org.
   *
   * @param string $type
   *   'module' or 'theme'.
   */
  private function pullData(string $type) {
    $url = 'https://www.drupal.org/api-d7/node.json?type=project_' .
      $type .
      '&limit=10&sort=created&direction=DESC&field_project_type=full';
    try {
      $response = $this->client->request('GET', $url);
      if ($response->getStatusCode() === 200) {
        return json_decode($response->getBody()->getContents());
      }
      else {
        $this->messenger->addMessage(t("Couldn't pull module data."), 'error');
      }
    }
    catch (\GuzzleHttp\Exception\GuzzleException $ex) {
      $this->messenger->addMessage(t("Couldn't pull module data."), 'error');
    }
  }
}
