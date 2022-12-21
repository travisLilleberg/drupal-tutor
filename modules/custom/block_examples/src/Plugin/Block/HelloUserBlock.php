<?php

namespace Drupal\block_examples\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Provides a 'Hello User' block.
 *
 * @Block(
 *   id = "block_examples_hello_user",
 *   admin_label = @Translation("Block Example: Hello User"),
 *   category = @Translation("Examples")
 * )
 */
class HelloUserBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    $route = \Drupal::routeMatch();
    if($route->getRouteName() === 'entity.user.canonical') {
      return AccessResult::forbidden();
    }

    $route_acc = $route->getParameter('user');
    if(!empty($route_acc) && $route_acc->id() === $account->id()) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());
    $date_formatter = \Drupal::service('date.formatter');
    return [
      '#markup' => $this->t('Hello %name!!! You logged in on @login',
        [
          '%name' => $user->getDisplayName(),
          '@login' => $date_formatter->format($user->getLastLoginTime(), 'short'),
        ]
      ),
    ];
  }
}
