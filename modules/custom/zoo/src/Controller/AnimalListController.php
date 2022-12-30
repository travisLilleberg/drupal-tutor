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
      $this->t('Habitat'),
    ];

    $rows = [];
    $results = $this->conn->query("SELECT a.*, h.name AS habitat_name FROM {zoo_animal} a
      LEFT JOIN {zoo_habitat} h ON a.habitat_id = h.habitat_id ORDER BY h.name");
    foreach ($results as $record) {
      $age = floor((\Drupal::time()->getRequestTime() - $record->birthday) / (365 * 24 * 3600));
      $rows[] = [
        $record->name,
        $record->type,
        $this->t('@age years', ['@age' => $age]),
        $this->t('@weight kg', ['@weight' => $record->weight]),
        $record->habitat_name,
      ];
    }

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }
}
