<?php

namespace Drupal\drupal_api;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Database\Connection;
use GuzzleHttp\ClientInterface;

class DrupalAPIManager implements DrupalAPIManagerInterface {

  private ClientInterface $client;
  private MessengerInterface $messenger;
  private Connection $connection;

  public function __construct(ClientInterface $client, MessengerInterface $messenger, Connection $connection) {
    $this->client = $client;
    $this->messenger = $messenger;
    $this->connection = $connection;
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

  public function fetchLatestProjects() {
    $data = $this->pullData('everything');
    if (empty($data) || empty($data->list)) {
      return;
    }

    foreach ($data->list as $item) {
      $nid = $item->nid;
      if ($this->itemExists($nid)) {
        continue;
      }

      $item = [
        'id' => $item->nid,
        'type' => $item->type,
        'name' => $item->title,
        'created' => $item->created,
        'description' => empty($item->body) ? '' : $item->body->value,
        'url' => $item->url,
      ];

      try {
        $this->connection->insert('drupal_api')
          ->fields($item)
          ->execute();
      } catch (\Exception $e) {
        $this->messenger->addMessage(t("Failed inserting new themes and modules."));
      }

      $this->messenger->addMessage(t("New modules and themes imported."));
    }
  }

  /**
   * Pulls module or theme data from drupal.org.
   *
   * @param string $type
   *   'module' or 'theme'.
   */
  private function pullData(string $type) {
    if ($type === 'everything') {
      $url = "https://www.drupal.org/api-d7/node.json?type[]=project_theme&type[]=project_module&limit=100&sort=created&direction=DESC&field_project_type=full";
    }
    else {
      $url = 'https://www.drupal.org/api-d7/node.json?type=project_' .
        $type .
        '&limit=10&sort=created&direction=DESC&field_project_type=full';
    }
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

  /**
   * Does the nid already exist in drupal_api table?
   *
   * @param string $nid
   *
   * @return bool
   */
  private function itemExists(string $nid) {
    return boolval(
      $this->connection->select('drupal_api', 'd')
        ->condition('d.id', $nid)
        ->countQuery()
        ->execute()
        ->fetchField()
    );
  }
}
