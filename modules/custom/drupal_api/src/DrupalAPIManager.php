<?php

namespace Drupal\drupal_api;

use Drupal\drupal_api\DrupalAPIManagerInterface;

class DrupalAPIManager implements DrupalAPIManagerInterface {
  public function getLatestModules() {
    $modules = [];
    $modules[] = [
      'name' => 'Module #1',
      'created' => '1527163200',
      'description' => 'This is module #1',
      'url' => 'https://example.com',
    ];
    $modules[] = [
      'name' => 'Module #2',
      'created' => '1527076800',
      'description' => 'This is module #2',
      'url' => 'https://example.com',
    ];
    $modules[] = [
      'name' => 'Module #3',
      'created' => '1526990400',
      'description' => 'This is module #3',
      'url' => 'https://example.com',
    ];
    
    return $modules;
  }
}
