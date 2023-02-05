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

  /**
   * {inheritdoc}
   */
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

  public function latestModules() {
    $markup = '';
    $modules = $this->drupalAPIManager->getLatestModules();
    foreach ($modules as $mod) {
      $markup .= "
        <div class='module'>
          <h2><a href='{$mod['url']}'>{$mod['name']}</a></h2>
          <div class='created'>{$this->dateFormatter->format($mod['created'], 'olivero_medium')}</div>
          <div class='description'>{$mod['description']}</div>
        </div>
      ";
    }

    return [
      '#markup' => $markup,
    ];
  }
}
