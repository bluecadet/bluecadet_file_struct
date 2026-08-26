CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * FAQ
 * Maintainers
 * Changelog


INTRODUCTION
------------

This module lets you designate a text field on Media entities to hold a
target storage directory. Whenever a Media entity with that field set is
saved, the module moves its underlying file to that directory -- so file
storage location can be driven by editorial/content data (e.g. organizing
uploads by exhibition, department, or year) instead of Drupal's default
flat/date-based file paths.

Current functionality:

 * Pick which string field on Media entities acts as the "directory" field
   (settings form; only string-type fields are offered as options).
 * On save, if that field has a value and it differs from the file's current
   location, move the file there, creating the directory if needed.
 * Validate that field's value is a well-formed directory URI: starts with
   `public://` or `private://`, does not end in a slash, and uses only
   alphanumeric characters, `-`, and `_`.

Not yet implemented: the module depends on Drupal core's Taxonomy module and
its config schema declares `public_vocab`/`private_vocab` settings, which
suggests an original intent to derive the directory structure from a
taxonomy vocabulary (e.g. term hierarchy -> folder path). No code currently
reads either config key -- only the flat string-field workflow above is
wired up.


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

Configuration can be found at:

Admin > Configuration > System > Bluecadet Utilities > Bluecadet File
Struct (`/admin/config/system/bluecadet-utilities/bluecadet-file-struct`).

Select the string field on Media entities that should hold each item's
target directory. Editors then enter a value like `public://exhibitions/2026`
on that field; the module handles moving the file on save.


MAINTAINERS
-----------

Current maintainers:

 * Pete Inge (pingevt) - https://www.drupal.org/user/411339

This project has been sponsored by:

 * Bluecadet - https://www.bluecadet.com/


CHANGELOG
---------

# Unreleased

 -
