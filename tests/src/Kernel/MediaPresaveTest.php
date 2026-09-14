<?php

namespace Drupal\Tests\bluecadet_file_struct\Kernel;

use Drupal\Core\File\FileSystemInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media\Entity\Media;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests bluecadet_file_struct_media_presave().
 *
 * @group bluecadet_file_struct
 */
class MediaPresaveTest extends KernelTestBase {

  use MediaTypeCreationTrait;
  use UserCreationTrait;

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
    $this->installSchema('file', ['file_usage']);
    $this->installConfig(['field', 'system', 'media', 'bluecadet_file_struct']);

    // File/media entity access checks require a current user.
    $this->setUpCurrentUser([], [], TRUE);

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
   * Creates a real file on disk and its File entity.
   */
  protected function createFile(string $uri, string $contents = 'test'): File {
    \Drupal::service('file_system')->prepareDirectory(
      dirname($uri),
      FileSystemInterface::CREATE_DIRECTORY
    );
    file_put_contents($uri, $contents);

    $file = File::create(['uri' => $uri]);
    $file->setPermanent();
    $file->save();

    return $file;
  }

  /**
   * Creates a media entity referencing $file, with the given directory value.
   */
  protected function createMedia(File $file, ?string $directory): Media {
    $media = Media::create([
      'bundle' => $this->mediaType->id(),
      'name' => 'Test media',
      'field_media_file' => $file->id(),
      'field_directory' => $directory,
    ]);
    $media->save();

    return $media;
  }

  /**
   * Tests that a file gets moved when the configured directory differs.
   */
  public function testFileMovedWhenDirectoryDiffers(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_directory')
      ->save();

    $file = $this->createFile('public://original.txt');
    $media = $this->createMedia($file, 'public://target-dir');

    $moved_file = File::load($media->get('field_media_file')->target_id);
    $this->assertSame('public://target-dir/original.txt', $moved_file->getFileUri());
    $this->assertFileExists($moved_file->getFileUri());
  }

  /**
   * Tests that nothing happens when the file is already at the target.
   */
  public function testNoOpWhenAlreadyAtTarget(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_directory')
      ->save();

    $file = $this->createFile('public://existing-dir/original.txt');
    $original_uri = $file->getFileUri();
    $media = $this->createMedia($file, 'public://existing-dir');

    $unchanged_file = File::load($media->get('field_media_file')->target_id);
    $this->assertSame($original_uri, $unchanged_file->getFileUri());
  }

  /**
   * Tests that an unwritable/invalid target scheme leaves the file in place.
   */
  public function testFileUnchangedWhenTargetDirectoryCannotBePrepared(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_directory')
      ->save();

    $file = $this->createFile('public://original.txt');
    $media = $this->createMedia($file, 'invalid-scheme://nowhere');

    $unchanged_file = File::load($media->get('field_media_file')->target_id);
    $this->assertSame('public://original.txt', $unchanged_file->getFileUri());
  }

  /**
   * Tests that nothing happens when no directory value is set on the field.
   */
  public function testNoOpWhenDirectoryFieldEmpty(): void {
    $this->config('bluecadet_file_struct.settings')
      ->set('media_field', 'field_directory')
      ->save();

    $file = $this->createFile('public://original.txt');
    $media = $this->createMedia($file, NULL);

    $unchanged_file = File::load($media->get('field_media_file')->target_id);
    $this->assertSame('public://original.txt', $unchanged_file->getFileUri());
  }

  /**
   * Tests that nothing happens when no directory field is configured at all.
   */
  public function testNoOpWhenNoMediaFieldConfigured(): void {
    // bluecadet_file_struct.settings:media_field is NULL by default (see
    // config/install/bluecadet_file_struct.settings.yml) -- hasField('')
    // is FALSE, so the hook returns before touching the file.
    $file = $this->createFile('public://original.txt');
    $media = $this->createMedia($file, 'public://target-dir');

    $unchanged_file = File::load($media->get('field_media_file')->target_id);
    $this->assertSame('public://original.txt', $unchanged_file->getFileUri());
  }

}
