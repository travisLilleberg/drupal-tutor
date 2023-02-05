<?php

namespace Drupal\drupal_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\drupal_api\DrupalAPIManagerInterface;

class DrupalAPIController extends ControllerBase {

  /**
   * @var DrupalAPIManagerInterface
   */
  private DrupalAPIManagerInterface $drupalAPIManager;

  /**
   * @var DateFormatterInterface
   */
  private DateFormatterInterface $dateFormatter;

  public function __construct($drupal_api_manager, $date_formatter) {
    $this->drupalAPIManager = $drupal_api_manager;
    $this->dateFormatter = $date_formatter;
  }

  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('drupal_api.manager'),
      $container->get('date.formatter'),
    );
  }

  /**
   * Controller to display module page.
   *
   * @return string[]
   */
  public function latestModules() {
    return $this->buildHtml($this->drupalAPIManager->getLatest('module'));
  }

  /**
   * Controller to display theme page.
   *
   * @return string[]
   */
  public function latestThemes() {
    return $this->buildHtml($this->drupalAPIManager->getLatest('theme'));
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
