<?php

namespace Drupal\block_examples\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;

/**
 * Provides a 'Last Logged In' block.
 *
 * @Block(
 *   id = "block_examples_last_logged_in",
 *   admin_label = @Translation("Block Example: Last Logged In"),
 *   category = @Translation("Examples")
 * )
 */
class LastLoggedInBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['message' => 'You registered @time ago!'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Registration Message'),
      '#required' => FALSE,
      '#default_value' => $this->configuration['message'],
      '#description' => $this->t(
        'Write your registration message here. Use @time to represent the time value.'
      )
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['message'] = $form_state->getValue('message');
    }

    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = User::load(\Drupal::currentUser()->id());

    $last_logged_in = \Drupal::time()->getRequestTime() - $user->getCreatedTime();
    $days = floor($last_logged_in / 86400);
    $left = $last_logged_in % 86400;
    $hours = floor($left / 3600);
    $left = $left % 3600;
    $minutes = floor($left / 60);
    $seconds = $left % 60;

    $message = str_replace(
      '@time',
      '@day days, @hour hours, @min minutes and @sec seconds',
      $this->configuration['message']
    );

    return [
      '#markup' => $this->t(
        $message, [
          '@day' => $days,
          '@hour' => $hours,
          '@min' => $minutes,
          '@sec' => $seconds
        ]
      ),
    ];
  }
}
