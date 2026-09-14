<?php

namespace Drupal\Tests\bluecadet_file_struct\Kernel;

use Drupal\bluecadet_file_struct\Form\BlucadetFileStructSettings;
use Drupal\Core\Form\FormState;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests the BlucadetFileStructSettings settings form.
 *
 * @group bluecadet_file_struct
 */
class BlucadetFileStructSettingsTest extends KernelTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'file',
    'image',
    'media',
    'taxonomy',
    'bluecadet_utilities',
    'bluecadet_file_struct',
  ];

  /**
   * The media type under test.
   *
   * @var \Drupal\media\Entity\MediaType
   */
  protected MediaType $mediaType;

  /**
   * The form under test.
   *
   * @var \Drupal\bluecadet_file_struct\Form\BlucadetFileStructSettings
   */
  protected BlucadetFileStructSettings $form;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installEntitySchema('media');
    $this->installConfig(['field', 'system', 'media', 'bluecadet_file_struct']);

    $this->mediaType = $this->createMediaType('file', ['id' => 'document']);

    FieldStorageConfig::create([
      'field_name' => 'field_directory',
      'entity_type' => 'media',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_directory',
      'entity_type' => 'media',
      'bundle' => $this->mediaType->id(),
    ])->save();

    $this->form = BlucadetFileStructSettings::create($this->container);
  }

  /**
   * Tests that only string fields on media bundles are offered as options.
   */
  public function testBuildFormOffersOnlyStringMediaFields(): void {
    $form_state = new FormState();
    $form = $this->form->buildForm([], $form_state);

    $this->assertArrayHasKey('media_field', $form);
    $this->assertArrayHasKey('field_directory', $form['media_field']['#options']);
    // field_media_file is a File entity reference field (not a string
    // field), so it should not be offered as an option.
    $this->assertArrayNotHasKey('field_media_file', $form['media_field']['#options']);
  }

  /**
   * Tests that submitting the form saves the selected field to config.
   */
  public function testSubmitFormSavesConfig(): void {
    $form = [];
    $form_state = new FormState();
    $form_state->setValue('media_field', 'field_directory');

    $this->form->submitForm($form, $form_state);

    $this->assertSame('field_directory', $this->config('bluecadet_file_struct.settings')->get('media_field'));
  }

}
