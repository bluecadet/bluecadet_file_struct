<?php

namespace Drupal\bluecadet_file_struct\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ValidDir constraint.
 */
class DirConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {

    $field_label = $value->getFieldDefinition()->getLabel();
    foreach ($value as $item) {

      // Check for starts with Scheme.
      $scheme_pattern = "/^(public:\/\/|private:\/\/)/i";

      if (!preg_match_all($scheme_pattern, $item->value)) {
        $this->context->addViolation($constraint->notStartWithScheme, ['%field' => $field_label, '%value' => $item->value]);
      }

      // Check if ends with a "/".
      $end_slash_pattern = "/\/$/i";

      if (preg_match_all($end_slash_pattern, $item->value) && ($item->value != "public://" && $item->value != "private://")) {
        $this->context->addViolation($constraint->notEndWithSlash, ['%field' => $field_label, '%value' => $item->value]);
      }

      // Check if using proper chars.
      $bad_chars_pattern = "/^(public:\/\/|private:\/\/)([a-zA-Z0-9-_\/]*)([a-zA-Z0-9-_]+)$/";
      if (!preg_match_all($bad_chars_pattern, $item->value)) {
        $this->context->addViolation($constraint->usesImproperChars, ['%field' => $field_label, '%value' => $item->value]);
      }
    }
  }

}
