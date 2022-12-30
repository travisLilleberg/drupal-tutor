<?php

namespace Drupal\zoo\Controller;

use Drupal\Core\Controller\ControllerBase;

class AnimalListController extends ControllerBase {
  public function listAnimals() {
    $header = [
      $this->t('Name'),
      $this->t('Type'),
      $this->t('Age'),
      $this->t('Weight'),
    ];

    $rows = [];

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }
}
