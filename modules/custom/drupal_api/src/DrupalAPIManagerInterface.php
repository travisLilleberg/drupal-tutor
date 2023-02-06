<?php

namespace Drupal\drupal_api;

interface DrupalAPIManagerInterface {

  /**
   * @return array
   */
  public function getLatest();

  public function fetchLatestProjects();
}
