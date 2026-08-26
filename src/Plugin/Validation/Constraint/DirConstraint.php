<?php

namespace Drupal\bluecadet_file_struct\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted value is a valid directory.
 *
 * @Constraint(
 *   id = "ValidDir",
 *   label = @Translation("Valid Dir", context = "Validation"),
 *   type = "string"
 * )
 */
class DirConstraint extends Constraint {

  /**
   * The message shown when the value doesn't start with a valid scheme.
   *
   * @var string
   */
  public $notStartWithScheme = '%field: "%value" - Needs to start with proper scheme, public:// or private://';

  /**
   * The message shown when the value ends with a slash.
   *
   * @var string
   */
  public $notEndWithSlash = '%field: "%value" - Should not end in a slash';

  /**
   * The message shown when the value uses improper characters.
   *
   * @var string
   */
  public $usesImproperChars = '%field: "%value" - uses improper chars for a directory name, please use alphanumeric and - or _';

}
