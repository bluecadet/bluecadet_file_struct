<?php

namespace Drupal\Tests\bluecadet_file_struct\Unit;

use Drupal\bluecadet_file_struct\Plugin\Validation\Constraint\DirConstraint;
use Drupal\bluecadet_file_struct\Plugin\Validation\Constraint\DirConstraintValidator;
use Drupal\Core\Field\FieldDefinitionInterface;
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

    // validate()'s $value parameter is untyped, and the method only calls
    // getFieldDefinition() and iterates over $value -- so a minimal double
    // covering just those two things is enough. Deliberately not mocking
    // FieldItemListInterface itself: it has 50+ abstract methods, requires
    // bare \Traversable without declaring getIterator() (that comes from
    // \IteratorAggregate, added separately by concrete implementations),
    // and PHPUnit's mock generation for it proved fragile across PHPUnit
    // versions -- addMethods() is deprecated in newer PHPUnit and adding it
    // to stub getIterator() caused mock generation to fail outright in CI
    // ("contains 53 abstract methods and must therefore be declared
    // abstract"). A hand-rolled double sidesteps all of that.
    $field_list = new class($item, $field_definition) implements \IteratorAggregate {

      public function __construct(
        private readonly object $item,
        private readonly FieldDefinitionInterface $fieldDefinition,
      ) {}

      /**
       * {@inheritdoc}
       */
      public function getFieldDefinition(): FieldDefinitionInterface {
        return $this->fieldDefinition;
      }

      /**
       * {@inheritdoc}
       */
      public function getIterator(): \Iterator {
        return new \ArrayIterator([$this->item]);
      }

    };

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
