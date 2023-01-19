<?php

namespace Drupal\form_examples\Plugin\Block;

use Drupal\Core\Block\BlockBase;

  /**
   * Provides a 'Contact Form' block.
   *
   * @Block(
   *   id = "form_examples_contact_form",
   *   admin_label = @Translation("Form Examples: Contact Form"),
   *   category = @Translation("Examples")
   * )
   */
class ContactFormBlock extends BlockBase {

  /**
   * @inheritDoc
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('\Drupal\form_examples\Form\ContactForm');
  }
}
