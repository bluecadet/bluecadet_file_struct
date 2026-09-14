<?php

namespace Drupal\Tests\bluecadet_file_struct\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests update status alter behavior.
 *
 * @group bluecadet_file_struct
 */
class UpdateStatusAlterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'bluecadet_file_struct'];

  /**
   * Tests projects that are not targeted remain unchanged.
   */
  public function testNonTargetProjectUnchanged(): void {
    $projects = [
      'example_module' => [
        'name' => 'example_module',
        'project_type' => 'module',
        'status' => 1,
      ],
    ];

    $expected = $projects;

    bluecadet_file_struct_update_status_alter($projects);

    $this->assertSame($expected, $projects);
  }

}
