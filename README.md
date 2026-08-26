# Bluecadet File Structure

A Drupal module that lets you designate a text field on Media entities as a target storage directory, then automatically moves each entity's underlying file there on save.

## Requirements

- Drupal 10.5+ or Drupal 11.2+
- PHP 8.2 or higher

## Versions

### 1.x Branch

- **1.1.x**: Drupal 10.5+/11.2+ support (PHP 8.2+)
- **1.0.x**: Drupal 9/10 support (original release)

## Includes

- Settings form to pick which string field on Media entities acts as the "directory" field (only string-type fields on `media` bundles are offered as options)
- On save, if that field has a value and it differs from the file's current location, moves the file there, creating the directory if needed
- `ValidDir` constraint that validates the field's value is a well-formed directory URI: starts with `public://` or `private://`, does not end in a slash, and uses only alphanumeric characters, `-`, and `_`

**Not yet implemented:** `config/schema/bluecadet_file_struct.schema.yml` also declares `public_vocab`/`private_vocab` settings, and the module depends on Drupal core's Taxonomy module -- both suggest an original design where the directory structure would be derived from a taxonomy vocabulary (e.g. term hierarchy -> folder path). No code currently reads either config key; only the flat string-field workflow above is wired up.

## Not using Composer

If you are not using composer, you can delete all unneeded files.

- composer.json

## Using Composer

If you are using composer to manage Drupal modules, make sure you add custom
location for this module to be downloaded to. You must add the installer types
line as well as the location for the module.

```json
  ...
  "installer-types": ["custom-drupal-module"],
  "installer-paths": {
    "web/core": ["type:drupal-core"],
    "web/modules/contrib/{$name}": ["type:drupal-module"],
    "web/modules/custom/{$name}": ["type:custom-drupal-module"],
    "web/profiles/contrib/{$name}": ["type:drupal-profile"],
    "web/themes/contrib/{$name}": ["type:drupal-theme"],
    "drush/contrib/{$name}": ["type:drupal-drush"]
  },
  ...
```

## Testing

This module includes automated tests that run via GitHub Actions against Drupal 10.5.x-11.3.x (see `.github/workflows/drupal-tests-and-standards.yml` for the exact PHP/MariaDB matrix).

### Test Plan

#### Automated Tests (GitHub Actions)

The CI pipeline runs the following for each Drupal version:

1. **PHPCS** - Drupal coding standards validation
2. **DrupalPractice** - Best practices validation
3. **PHPStan** - Drupal-aware static analysis (deprecation/API checks, level 2)
4. **PHPUnit** - automated tests

#### Current coverage

Only `tests/src/Unit/DirConstraintValidatorTest.php` exists today, covering the `ValidDir` constraint's validation logic. Kernel coverage for the media-presave file-move behavior and Functional coverage for the settings form are not yet written.

## Changelog

### 1.1.x

- Added Drupal 11 compatibility (`drupal/core: ^10.5 || ^11.2`, PHP 8.2+)
- Adopted the reusable GitHub Actions workflow architecture (PHPStan, dynamic module name, least-privilege permissions)
- Upgraded build tooling to bldr 2.0.0-alpha and Node 20
- Fixed D11-incompatible entity typing and the removed `file_move()` function
- Added initial Unit test coverage

### 1.0.x

- Initial release: configure a Media field as a target directory, move files there on save, validate directory format
