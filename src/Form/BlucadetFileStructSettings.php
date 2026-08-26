<?php

namespace Drupal\bluecadet_file_struct\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Bluecadet Utility Settings Form.
 */
class BlucadetFileStructSettings extends ConfigFormBase {

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
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityFieldManager = $container->get('entity_field.manager');
    return $instance;
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
    $field_map = $this->entityFieldManager->getFieldMap();
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
