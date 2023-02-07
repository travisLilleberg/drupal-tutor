<?php

namespace Drupal\drupal_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class DrupalAPIForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['drupal_api.settings'];
  }

  public function getFormId() {
    return 'drupal_api_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $drupal_api_config = $this->config('drupal_api.settings');

    $form['automatic'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Automatic Import')
    ];
    $form['automatic']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $drupal_api_config->get('automatic_enabled')
    ];
    $form['automatic']['frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Retrieve New Data Every'),
      '#options' => [
        1800 => $this->t('@count minutes', ['@count' => 30]),
        3600 => $this->t('@count hour', ['@count' => 1]),
        6400 => $this->t('@count hours', ['@count' => 2]),
        14400 => $this->t('@count hours', ['@count' => 4]),
        43200 => $this->t('@count hours', ['@count' => 12]),
        86400 => $this->t('@count hours', ['@count' => 24])
      ],
      '#default_value' => $drupal_api_config->get('automatic_frequency'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE]
        ],
        'required' => [
          ':input[name="enabled"]' => ['checked' => TRUE]
        ]
      ]
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $drupal_api_config = $this->config('drupal_api.settings');
    $drupal_api_config->set('automatic_enabled', $form_state->getValue('enabled'))
      ->set('automatic_frequency', $form_state->getValue('frequency'))
      ->save();
  }
}
