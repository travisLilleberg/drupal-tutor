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
    $query = $this->conn->select('zoo_animal', 'a')
      ->fields('a')
      ->range(0,10);

    if ($habitat === 'all') {
      $header[] = $this->t('Habitat');
      $query->leftJoin('zoo_habitat', 'h', 'a.habitat_id = h.habitat_id');
      $query->addField('h', 'name', 'habitat_name');
      $query->orderBy('habitat_name');
    }
    else {
      $query->condition('a.habitat_id', $habitat);
    }
    $results = $query->orderBy('name')
      ->execute();

    foreach ($results as $record) {
      $age = floor((\Drupal::time()->getRequestTime() - $record->birthday) / (365 * 24 * 3600));
      $row = [
        $record->name,
        $record->type,
        $this->t('@age years', ['@age' => $age]),
        $this->t('@weight kg', ['@weight' => $record->weight]),
      ];
      if($habitat === 'all') {
        $row[] = \Drupal\Core\Link::fromTextAndUrl(
          $record->habitat_name,
          \Drupal\Core\Url::fromRoute('zoo.habitat_list', ['habitat' => $record->habitat_id])
        );
      }

      $rows[] = $row;
    }

    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }

  public function listAnimalsInHabitatTitle($habitat) {
    if ($habitat === 'all') {
      return $this->t('Animals in All Habitats');
    }

    $name = $this->conn->select('zoo_habitat', 'h')
      ->fields('h', ['name'])
      ->condition('habitat_id', $habitat)
      ->execute()
      ->fetchField();

    if (!empty($name)) {
      return $this->t('Animals in @habitat', ['@habitat' => $name]);
    }
    return $this->t('Habitat Not Found');
  }

  public function listAnimalsInHabitatStaticAPI($habitat) {
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

  public function listAnimalsInHabitatTitleStaticAPI($habitat) {
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