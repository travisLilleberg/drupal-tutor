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
        \Drupal\Core\Link::fromTextAndUrl(
          $record->name,
          \Drupal\Core\Url::fromRoute(
            'zoo.animal_view', ['animal_id' => $record->animal_id]
          )
        ),
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
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(3);

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
      'data' => [
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $header,
      ],
      'pager' => [
        '#type' => 'pager',
        '#weight' => -1,
      ],
      '#attached' => [
        'library' => [
          'zoo/animal-list'
        ]
      ],
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

  public function demoInsert() {
    // Single Insert
    $fields = [
      'name' => 'Mike',
      'type' => 'Monkey',
      'birthday' => strtotime('2015-06-17'),
      'weight' => 5.3,
      'description' => 'Mike is great',
      'habitat_id' => 4,
    ];

    $this->conn->insert('zoo_animal')
      ->fields($fields)
      ->execute();

    // Multiple insert
    $fields = [
      'name',
      'type',
      'birthday',
      'weight',
      'description',
      'habitat_id',
    ];

    $query = $this->conn->insert('zoo_animal')
      ->fields($fields);

    $values = [
      [
        'name' => 'Polly',
        'type' => 'Parrotfish',
        'birthday' => strtotime('2013-09-10'),
        'weight' => 8.1,
        'description' => 'Polly is colorful',
        'habitat_id' => 2,
      ],
      [
        'name' => 'Maggie',
        'type' => 'Macaw',
        'birthday' => strtotime('2014-02-02'),
        'weight' => 2.5,
        'description' => 'Maggie is grumpy',
        'habitat_id' => 3,
      ],
    ];

    foreach ($values as $value) {
      $query->values($value);
    }
    $query->execute();

    return [
      '#markup' => $this->t('Inserts completed')
    ];
  }

  public function demoUpdate() {
    $fields_to_change = [
      'type' => 'Howler Monkey',
      'weight' => 6.7
    ];

    $this->conn->update('zoo_animal')
      ->fields($fields_to_change)
      ->condition('name', 'Mike')
      ->execute();

    return [
      '#markup' => $this->t('Update completed')
    ];
  }

  public function demoDelete() {
    $this->conn->delete('zoo_animal')
      ->condition('name', 'Mike')
      ->execute();
    $this->conn->delete('zoo_animal')
      ->condition('name', ['Polly', 'Maggie'], 'IN')
      ->execute();

    return [
      '#markup' => $this->t('Deletes completed')
    ];
  }
}
