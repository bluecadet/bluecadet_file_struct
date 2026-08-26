<?php

namespace Drupal\Tests\bluecadet_file_struct\Unit;

use Drupal\bluecadet_file_struct\Plugin\Validation\Constraint\DirConstraint;
use Drupal\bluecadet_file_struct\Plugin\Validation\Constraint\DirConstraintValidator;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @coversDefaultClass \Drupal\bluecadet_file_struct\Plugin\Validation\Constraint\DirConstraintValidator
 * @group bluecadet_file_struct
 */
class DirConstraintValidatorTest extends UnitTestCase {

  /**
   * Runs the validator against a single directory value.
   *
   * @return string[]
   *   The violation messages (constraint property values) raised, in order.
   */
  protected function violationsFor(string $value, DirConstraint $constraint): array {
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $field_definition->method('getLabel')->willReturn('Directory');

    $item = (object) ['value' => $value];

    $field_list = $this->createMock(FieldItemListInterface::class);
    $field_list->method('getFieldDefinition')->willReturn($field_definition);
    $field_list->method('getIterator')->willReturn(new \ArrayIterator([$item]));

    $violations = [];
    $context = $this->createMock(ExecutionContextInterface::class);
    $context->method('addViolation')
      ->willReturnCallback(function ($message) use (&$violations) {
        $violations[] = $message;
      });

    $validator = new DirConstraintValidator();
    $validator->initialize($context);
    $validator->validate($field_list, $constraint);

    return $violations;
  }

  /**
   * @covers ::validate
   * @dataProvider providerValues
   */
  public function testValidate(string $value, array $expected_properties): void {
    $constraint = new DirConstraint();
    $expected_messages = array_map(
      fn (string $property) => $constraint->{$property},
      $expected_properties
    );

    $this->assertSame($expected_messages, $this->violationsFor($value, $constraint));
  }

  /**
   * Data provider for testValidate().
   *
   * The three checks aren't mutually exclusive: a value missing its scheme,
   * or ending in a slash, also fails the "proper chars" pattern (which
   * requires the string to both start with the scheme and end without a
   * slash), so those cases raise two violations rather than one.
   */
  public static function providerValues(): array {
    return [
      'valid path' => ['public://some-valid_path', []],
      'missing scheme' => [
        'not-a-scheme/path',
        ['notStartWithScheme', 'usesImproperChars'],
      ],
      'trailing slash' => [
        'public://some/path/',
        ['notEndWithSlash', 'usesImproperChars'],
      ],
      'improper characters' => [
        'public://some path',
        ['usesImproperChars'],
      ],
    ];
  }

}
