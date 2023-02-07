<?php

namespace Drupal\drupal_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\drupal_api\DrupalAPIManagerInterface;
use Drupal\KernelTests\Core\Database\FetchTest;

class DrupalAPIController extends ControllerBase {

  /**
   * @var DrupalAPIManagerInterface
   */
  private DrupalAPIManagerInterface $drupalAPIManager;

  /**
   * @var DateFormatterInterface
   */
  private DateFormatterInterface $dateFormatter;

  /**
   * @var Connection
   */
  private Connection $connection;

  public function __construct($drupal_api_manager, $date_formatter, $connection) {
    $this->drupalAPIManager = $drupal_api_manager;
    $this->dateFormatter = $date_formatter;
    $this->connection = $connection;
  }

  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('drupal_api.manager'),
      $container->get('date.formatter'),
      $container->get('database'),
    );
  }

  /**
   * Controller to display module page.
   *
   * @return string[]
   */
  public function latestModules() {
//    return $this->buildHtml($this->drupalAPIManager->getLatest('module'));
//    return $this->buildHtml($this->loadItems('project_module'));
    $build = [];
    foreach ($this->loadItems('project_module') as $module) {
      $build[] = [
        '#theme' => 'drupal_api_project',
        '#name' => $module['name'],
        '#url' => $module['url'],
        '#description' => $module['description'],
        '#created' => $this->dateFormatter->format($module['created'], 'olivero_medium'),
      ];
    }

    $build['#cache'] = [
      'tags' => ['drupal_api.project.list'],
    ];
    return $build;
  }

  /**
   * Controller to display theme page.
   *
   * @return string[]
   */
  public function latestThemes() {
//    return $this->buildHtml($this->drupalAPIManager->getLatest('theme'));
//    return $this->buildHtml($this->loadItems('project_module'));
    $build = [];
    foreach ($this->loadItems('project_theme') as $theme) {
      $build[] = [
        '#theme' => 'drupal_api_project',
        '#name' => $theme['name'],
        '#url' => $theme['url'],
        '#description' => $theme['description'],
        '#created' => $this->dateFormatter->format($theme['created'], 'olivero_medium'),
      ];
    }

    $build['#cache'] = [
      'tags' => ['drupal_api.project.list'],
    ];
    return $build;
  }

  /**
   * Load the themes and modules from the database.
   *
   * @param string $type
   *   project_module or project_theme.
   *
   * @return array
   */
  private function loadItems(string $type) {
    $results = $this->connection->select('drupal_api' ,'d')
      ->fields('d', ['url', 'name', 'created', 'description'])
      ->condition('type', $type)
      ->orderBy('created', 'DESC')
      ->range(0,10)
      ->execute()
      ->fetchAll(\PDO::FETCH_ASSOC);

    $items = [];
    foreach ($results as $result) {
      $items[] = [
        'name' => $result['name'],
        'url' => $result['url'],
        'created' => $result['created'],
        'description' => $result['description'],
      ];
    }

    return $items;
  }

  /**
   * Builds html for passed in module or theme info.
   *
   * @param $items
   *   The modules or themes to display.
   *
   * @return string[]
   */
  private function buildHtml($items) {
    $markup = '';
    foreach ($items as $item) {
      $markup .= "
        <div class='module'>
          <h2><a target='_blank' href='{$item['url']}'>{$item['name']}</a></h2>
          <div class='created'>{$this->dateFormatter->format($item['created'], 'olivero_medium')}</div>
          <div class='description'>{$item['description']}</div>
        </div>
      ";
    }

    return [
      '#markup' => $markup,
    ];
  }
}
