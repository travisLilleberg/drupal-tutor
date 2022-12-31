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

  public function listAnimalsInHabitat($habitat) {
    $header = [
      $this->t('Name'),
      $this->t('Type'),
      $this->t('Age'),
      $this->t('Weight'),
    ];

    $rows = [];
    $results = $this->conn->query(
      "SELECT * FROM {zoo_animal} WHERE habitat_id = :habitat_id ORDER BY name",
      ['habitat_id' => $habitat]
    );

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

  public function listAnimalsInHabitatTitle($habitat) {
    $name = $this->conn->query(
      "SELECT name FROM {zoo_habitat} WHERE habitat_id = :habitat_id",
      ['habitat_id' => $habitat]
    )->fetchField();

    if (!empty($name)) {
      return $this->t('Animals in @habitat', ['@habitat' => $name]);
    }
    return $this->t('Habitat Not Found');
  }
}
