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

  // The message that will be shown.
  public $notStartWithScheme = '%field: "%value" - Needs to start with proper scheme, public:// or private://';

  public $notEndWithSlash = '%field: "%value" - Should not end in a slash';

  public $usesImproperChars = '%field: "%value" - uses improper chars for a directory name, please use alphanumeric and - or _';

}
