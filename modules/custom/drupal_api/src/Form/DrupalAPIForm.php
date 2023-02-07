<?php

namespace Drupal\drupal_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\drupal_api\DrupalAPIManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DrupalAPIForm extends ConfigFormBase {

  private DrupalAPIManagerInterface $APIManager;

  public function __construct(ConfigFactoryInterface $config_factory, DrupalAPIManagerInterface $api_manager) {
    parent::__construct($config_factory);
    $this->APIManager = $api_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('drupal_api.manager'),
    );
  }

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

    $form['import'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import Now'),
      '#name' => 'import_now',
      '#submit' => ['::importNow'],
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

  /**
   * Imports the items immediately.
   */
  public function importNow(array &$form, FormStateInterface $form_state) {
    $this->APIManager->fetchLatestProjects();
  }
}
