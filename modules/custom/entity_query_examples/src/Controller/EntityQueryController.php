<?php

namespace Drupal\entity_query_examples\Controller;

use Drupal\Core\Controller\ControllerBase;

class EntityQueryController extends ControllerBase {
  public function userList() {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $results = $user_storage->getQuery()
      ->condition('name',  'b%', 'LIKE')
      ->execute();
    ksm($results);
    $users = $user_storage->loadMultiple($results);

    $header = [
      $this->t('Username'),
      $this->t('Email'),
    ];
    $rows = [];
    foreach ($users as $user) {
      $rows[] = [
        $user->getDisplayName(),
        $user->getEmail()
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  public function nodeList() {
    $node_storage = $this->entityTypeManager()->getStorage('node');
    $results = $node_storage->getQuery()
      ->condition('type', 'article')
      ->exists('field_state')
      ->sort('title')
      ->execute();
    $nodes = $node_storage->loadMultiple($results);

    $header = [
      $this->t('ID'),
      $this->t('Type'),
      $this->t('Title'),
      $this->t('Author'),
      $this->t('Post Date')
    ];
    $rows = [];
    $authors = [];
    foreach ($nodes as $node) {
      $rows[] = [
        $node->id(),
        $node->bundle(),
        $node->getTitle(),
        $node->getOwner()->getDisplayName(),
        \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'long')
      ];
      $authors[] = $node->getOwner()->id();
    }

    $cache_tags = ['node_list'];
    foreach ($authors as $uid) {
      $cache_tags[] = 'user:' . $uid;
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => ['timezone']
      ]
    ];
  }
}
