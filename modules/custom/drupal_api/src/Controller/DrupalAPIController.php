<?php

namespace Drupal\drupal_api\Controller;

use Drupal\Core\Controller\ControllerBase;
class DrupalAPIController extends ControllerBase {

  public function latestModules() {
    return [
      '#markup' => '
<div class="module">
  <h2><a href="https://example.com">Module #1</a></h2>
  <div class="created">2018-05-24 12:00:00</div>
  <div class="description">This is module #1</div>
</div>
<div class="module">
  <h2><a href="https://example.com">Module #2</a></h2>
  <div class="created">2018-05-23 12:00:00</div>
  <div class="description">This is module #2</div>
</div>
<div class="module">
  <h2><a href="https://example.com">Module #3</a></h2>
  <div class="created">2018-05-22 12:00:00</div>
  <div class="description">This is module #3</div>
</div>
'
    ];
  }
}
