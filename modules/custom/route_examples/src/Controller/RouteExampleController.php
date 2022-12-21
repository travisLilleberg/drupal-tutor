<?php

namespace Drupal\route_examples\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\user\UserInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Session\AccountInterface;

class RouteExampleController extends ControllerBase {

  public function helloWorld() {
    return [ '#markup' => $this->t('Hello World') ];
  }

  public function helloUser() {
    return [
      '#markup' => $this->t(
        'Hello @user',
        ['@user' => \Drupal::currentUser()->getDisplayName()]
      )
    ];
  }

  public function helloUserTitle() {
    return $this->t(
      'Hello @user',
      ['@user' => \Drupal::currentUser()->getDisplayName()]
    );
  }

  public function userInfo(UserInterface $user) {
    $date_formatter = \Drupal::service('date.formatter');

    $markup = '<div>' .
      $this->t('Name: @name', ['@name' => $user->getDisplayName()]) .
      '</div>';
    $markup .= '<div>' .
      $this->t('Email: @email', ['@email' => $user->getEmail()]) .
      '</div>';
    $markup .= '<div>' .
      $this->t('Created: @created', [
        '@created' => $date_formatter->format($user->getCreatedTime())
      ]) .
      '</div>';
    $markup .= '<div>' .
      $this->t('Last login: @login', [
        '@login' => $date_formatter->format($user->getLastLoginTime())
      ]) .
      '</div>';

    return [ '#markup' => $markup ];
  }

  public function userInfoTitle(UserInterface $user) {
    return $this->t('Information About @user', ['@user' => $user->getDisplayName()]);
  }

  public function userInfoAccess(AccountInterface $account, UserInterface $user) {
    if ($account->hasPermission('view any user info')) {
      return AccessResult::allowed();
    }
    \Drupal::logger('route_examples')->info('no to 1');
    if ($account->hasPermission('view own user info') && $account->id() == $user->id()) {
      return AccessResult::allowed();
    }
    return AccessResult::forbidden();
  }

  public function nodeList($limit, $type) {
    $query = \Drupal::entityQuery('node');
    if ($type !== 'all') {
      $query = $query->condition('type', $type);
    }
    $nids = $query->range(0, $limit)->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

    $header = [
      $this->t('ID'),
      $this->t('Type'),
      $this->t('Title'),
    ];
    $rows = [];
    foreach ($nodes as $node) {
      $rows[] = [
        $node->id(),
        $node->bundle(),
        $node->getTitle(),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows
    ];
  }

  public function nodeCompare(NodeInterface $node1, NodeInterface $node2) {
    $diff = $node1->getCreatedTime() - $node2->getCreatedTime();

    return [
      '#markup' => t(
        'Created Time Difference: @diff seconds.',
        ['@diff' => $diff]
      )
    ];
  }
}
