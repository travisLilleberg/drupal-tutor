<?php

namespace Drupal\zoo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;

class AnimalListController extends ControllerBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $conn;

  public function __construct(Connection $conn) {
    $this->conn = $conn;
  }

  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function listAnimals() {
    $header = [
      $this->t('Name'),
      $this->t('Type'),
      $this->t('Age'),
      $this->t('Weight'),
    ];

    $rows = [];
    $results = $this->conn->query("SELECT * FROM {zoo_animal}");
    foreach ($results as $record) {
      $age = floor((\Drupal::time()->getRequestTime() - $record->birthday) / (365 * 24 * 3600));
      $rows[] = [
        $record->name,
        $record->type,
        $this->t('@age years', ['@age' => $age]),
        $this->t('@weight kg', ['@weight' => $record->weight]),
      ];
    }

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }
}
