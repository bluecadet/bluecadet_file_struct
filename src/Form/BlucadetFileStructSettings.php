<?php

namespace Drupal\bluecadet_file_struct\Form;

use Drupal\bluecadet_utilities\DrupalStateTrait;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Bluecadet Utility Settings Form.
 */
class BlucadetFileStructSettings extends ConfigFormBase {

  // use DrupalStateTrait;
  use MessengerTrait;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'bluecadet_file_struct.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bluecadet_file_struct_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Drupal Entity Field Manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * Get Entity Field Manager.
   */
  private function entityFieldManager() {
    if (!$this->entityFieldManager) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager'); // phpcs:ignore
    }
    return $this->entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // ksm($this->entityFieldManager()->getFieldMap());

    $config = $this->config(static::SETTINGS);

    $form['#tree'] = TRUE;

    $options = ['' => "- choose -"];

    // Set up fields.
    $field_map = $this->entityFieldManager()->getFieldMap();
    foreach ($field_map['media'] as $field => $field_data) {
      if ($field_data['type'] == "string") {
        $options[$field] = $field . " (" . implode(",", $field_data['bundles']) . ")";
      }
    }

    $form['media_field'] = [
      '#type' => 'select',
      '#title' => 'Media Field',
      '#description' => $this->t('The Media field that is a Taxonomy Term Reference to the directory structure.'),
      '#options' => $options,
      '#default_value' => $config->get('media_field'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      ->set('media_field', $form_state->getValue('media_field'))
      ->save();

    parent::submitForm($form, $form_state);

    // $this->messenger()->addMessage($msg);
  }

}
