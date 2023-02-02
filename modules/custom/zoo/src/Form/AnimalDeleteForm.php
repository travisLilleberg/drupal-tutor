<?php

namespace Drupal\zoo\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Database\Connection;
use \Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

class AnimalDeleteForm extends ConfirmFormBase {

  /**
   * @var int
   */
  private $animalId;

  /**
   * @var Connection
   */
  private $connection;

  /**
   * @var CacheTagsInvalidatorInterface
   */
  private $cacheTagInvalidator;

  public function __construct(Connection $connection, CacheTagsInvalidatorInterface $cacheTagInvalidator) {
    $this->connection = $connection;
    $this->cacheTagInvalidator = $cacheTagInvalidator;
  }

  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('cache_tags.invalidator')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state, $animal_id = NULL) {
    $this->animalId = $animal_id;
    return parent::buildForm($form, $form_state);
  }

  public function getFormId() {
    return 'zoo_animal_delete_form';
  }

  public function getCancelUrl() {
    return \Drupal\Core\Url::fromRoute('zoo.animal_view',
      ['animal_id' => $this->animalId]
    );
  }

  public function getQuestion() {
    return $this->t('Are you sure you want to delete this animal?');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->connection->delete('zoo_animal')
      ->condition('animal_id', $this->animalId)
      ->execute();
    \Drupal::messenger()->addMessage($this->t('Animal deleted.'));
    $form_state->setRedirect('zoo.habitat_list');
    $this->cacheTagInvalidator->invalidateTags(['animal.' . $this->animal_id, 'animal.list']);
  }
}
