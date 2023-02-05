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

  public function getLatestModules() {
    $module_data = $this->pullModuleData();
    if (empty($module_data) || empty($module_data->list)) {
      return [];
    }

    $modules = [];
    foreach ($module_data->list as $module) {
      $modules[] = [
        'url' => $module->url,
        'name' => $module->title,
        'created' => $module->created,
        'description' => empty($module->body) ? '' : $module->body->value,
      ];
    }
    return $modules;
  }

  private function pullModuleData() {
    $url = 'https://www.drupal.org/api-d7/node.json?type=project_module&limit=10&sort=created&direction=DESC&field_project_type=full';
    try {
      $response = $this->client->request('GET', $url);
      if ($response->getStatusCode() === 200) {
        return json_decode($response->getBody()->getContents());
      }
      else {
        $this->messenger->addMessage(t("Couldn't pull module data."), 'error');
      }
    }
    catch (\Exception $ex) {
      $this->messenger->addMessage(t("Couldn't pull module data."), 'error');
    }
  }
}
