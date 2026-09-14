<?php

namespace Drupal\Tests\bluecadet_file_struct\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests bluecadet_file_struct_entity_bundle_field_info_alter().
 *
 * @group bluecadet_file_struct
 */
class EntityBundleFieldInfoAlterTest extends KernelTestBase {

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
  }

  /**
   * Returns the field definitions for the test media bundle, cache-cleared.
   */
  protected function getFieldDefinitions(): array {
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
    return \Drupal::service('entity_field.manager')
      ->getFieldDefinitions('media', $this->mediaType->id());
  }

  /**
   * Tests the constraint is added when the configured field exists.
   */
  public function testConstraintAddedWhenFieldConfigured(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_directory')
      ->save();

    $definitions = $this->getFieldDefinitions();
    $this->assertArrayHasKey('ValidDir', $definitions['field_directory']->getConstraints());
  }

  /**
   * Tests no constraint is added when no field is configured.
   */
  public function testNoConstraintWhenNoFieldConfigured(): void {
    // bluecadet_file_struct.settings:media_field is NULL by default.
    $definitions = $this->getFieldDefinitions();
    $this->assertArrayNotHasKey('ValidDir', $definitions['field_directory']->getConstraints());
  }

  /**
   * Tests no error and no constraint when the configured field doesn't exist.
   */
  public function testNoErrorWhenConfiguredFieldMissing(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_does_not_exist')
      ->save();

    $definitions = $this->getFieldDefinitions();
    $this->assertArrayNotHasKey('ValidDir', $definitions['field_directory']->getConstraints());
  }

}
