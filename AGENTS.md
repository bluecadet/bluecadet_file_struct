# AGENTS.md

Guidance for AI coding agents (Claude Code, Copilot, Codex, etc.) working in this repository.

## What this is

A Drupal contrib-style module (`bluecadet_file_struct`) that lets a site admin designate a text field on Media entities as a "target directory" field. Whenever a Media entity with that field set is saved, the module moves the entity's underlying file to that directory -- so file storage location can be driven by editorial/content data instead of Drupal's default flat/date-based file paths.

Requires Drupal 10.6+/11.3+ and PHP 8.2+. This is a library consumed by other Drupal sites via Composer (package type `custom-drupal-module`), so it does not run standalone -- testing and running require a full Drupal installation (see below). Depends on `bluecadet/bluecadet_utilities`.

**Not fully implemented:** `config/schema/bluecadet_file_struct.schema.yml` declares `public_vocab`/`private_vocab` config keys, and the module depends on Drupal core's Taxonomy module -- both point at an original design where the directory structure would be derived from a taxonomy vocabulary (e.g. term hierarchy -> folder path). No code currently reads either config key. Only the simpler flat-string-field workflow described above is wired up.

## Architecture

- `bluecadet_file_struct.module` -- the entire runtime behavior, as two hook implementations:
  - `bluecadet_file_struct_media_presave(MediaInterface $media)` -- reads the configured field name from `bluecadet_file_struct.settings:media_field`. If the media entity has that field and it holds a value, resolves the entity's actual file (via its source field, e.g. `field_media_image`) and compares the file's current URI against the configured target directory. If they differ, creates the target directory if needed and moves the file there via the `file.repository` service.
  - `bluecadet_file_struct_entity_bundle_field_info_alter()` -- for the `media` entity type, attaches the `ValidDir` constraint to whichever field is configured as the "directory" field, so its value gets format-validated on entity save.
- `src/Plugin/Validation/Constraint/DirConstraint.php` + `DirConstraintValidator.php` -- the `ValidDir` constraint plugin. Validates that a field value starts with `public://` or `private://`, does not end in a slash, and uses only alphanumeric characters, `-`, and `_`. The three checks are not mutually exclusive: a value missing its scheme, or ending in a slash, also fails the "proper characters" pattern (which requires both a valid scheme prefix and a non-slash ending), so those cases raise two violations rather than one -- see `tests/src/Unit/DirConstraintValidatorTest.php` for the exact matrix.
- `src/Form/BlucadetFileStructSettings.php` -- the admin settings form at `/admin/config/system/bluecadet-utilities/bluecadet-file-struct`. Lists every string-type field defined on `media` bundles (via the injected `EntityFieldManagerInterface`) as the field-selection options, and saves the chosen field name to `bluecadet_file_struct.settings:media_field`.

## PHP testing and standards

This module cannot be tested in isolation -- it must live inside a Drupal installation at `modules/bluecadet/bluecadet_file_struct`. Run all commands below from the Drupal root, not this repo's root.

```bash
# PHPUnit -- all suites (unit/kernel/functional per phpunit.xml)
vendor/bin/phpunit --bootstrap core/tests/bootstrap.php \
  -c modules/bluecadet/bluecadet_file_struct/phpunit.xml \
  modules/bluecadet/bluecadet_file_struct

# Single test file
vendor/bin/phpunit --bootstrap core/tests/bootstrap.php \
  -c modules/bluecadet/bluecadet_file_struct/phpunit.xml \
  modules/bluecadet/bluecadet_file_struct/tests/src/Unit/DirConstraintValidatorTest.php

# Coding standards (Drupal + DrupalPractice)
vendor/bin/phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme,css,info,txt \
  modules/bluecadet/bluecadet_file_struct
vendor/bin/phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme,css,info,txt \
  modules/bluecadet/bluecadet_file_struct

# PHPStan (Drupal deprecation/API checks, level 2)
vendor/bin/phpstan analyse --configuration modules/bluecadet/bluecadet_file_struct/phpstan.neon.dist \
  modules/bluecadet/bluecadet_file_struct
```

Tests live in `tests/src/Unit` (the validator's regex logic) and `tests/src/Kernel` (the media-presave hook, the bundle field-info alter, and the settings form). There is no Functional coverage.

## CI

`.github/workflows/drupal-tests-and-standards.yml` calls the reusable `.github/workflows/drupal-test-runner.yml`, which clones Drupal core fresh, symlinks this module in via a path repository, then runs PHPCS, PHPStan, and PHPUnit. It runs on push/PR to `1.x`, monthly on a schedule, and via manual `workflow_dispatch` (which lets you target a specific Drupal core branch, PHP version, MariaDB version, and PHPUnit path). The matrix lives in `.github/drupal-ci.yml`: PR builds test the latest release of each supported major (10.6.x, 11.4.x); push/schedule/dispatch test every supported minor plus the next in development (10.6.x, 11.3.x, 11.4.x, 11.5.x, 12.0.x). The 12.0.x cell is expected to be red until Drupal 12 support lands. When Drupal ships or retires a minor, update `drupal-ci.yml` and the Testing line in `README.md`. This architecture, and the module's D11 support itself, were ported over from `bluecadet_ajax_content`.

## Versioning

Both `bluecadet_file_struct.info.yml` and `package.json` (and `package-lock.json`) carry the module version. Don't edit them by hand: from a clean tree, run `./node_modules/.bin/set-version -v <version> -c` (from `@bluecadet/drops`). `-c` runs `git add .`, commits `chore(release): bump version to X` and creates a lightweight tag. Call the binary directly; `npx set-version -v ...` can misparse `-v`. Before tagging, update the changelog in `README.md` and the `extra.bluecadet-package-manager` `recommended` entry in `composer.json` (the package manager reads it from the highest tag). The update check itself runs from `bluecadet_utilities`' `hook_update_status_alter()`, which lists this module.

## Keeping this file current

When you make a change that affects the Architecture section above -- a new hook implementation, a new field/constraint, or a change to the test or CI structure -- update that section in the same change. Don't let this file drift from the code it describes.
