# Final pre-1.0 validation

Date: 2026-09-07

## Candidate

- Component: `mod_interactembed`
- Release: `0.9.0-beta10`
- Version: `2026090700`
- Candidate version commit: `8728dee`
- Last fully published and green commit: `dbb9225`
- Moodle support declaration: 4.0 through 5.2
- Maturity remains `MATURITY_BETA`; no 1.0 tag or stable release was created.

The beta10 version bump records the compatibility and quality corrections already
validated on `dbb9225`; it does not alter the database schema or the shared
Block/Activity/Creator consumer contract.

## Distribution artifact

- Archive: `mod_interactembed-0.9.0-beta10.zip`
- Required root directory: `interactembed/`
- ZIP integrity test: passed
- SHA-256: `6ddb1b085bbd1ba6921249f1843f6b40ffec60a2038f6bfb767ab89835b6bc75`

## Environments

The same CLI recipe was executed without browser automation on:

- Moodle 5.2.1+ (Build 20260807), fresh installation from the beta10 ZIP;
- IOMAD/Moodle 4.5.13 (Build 20260810), upgrade from beta9 to beta10.

Both sites recorded plugin version `2026090700`. All temporary courses, users,
activities, files, grades, Creator projects and publications were removed and
their absence was verified after each successful recipe.

## Functional recipe results

All 47 assertions passed on both environments:

- one uploaded ZIP was validated, extracted and activated as an immutable
  package revision;
- `index.html`, nested JSON and an auxiliary `README.txt` were preserved;
- a cover image and accessible alternative text were stored through Moodle's
  Files API;
- Student, Editing teacher and Manager permissions matched the declared
  capabilities, including the Manager-only Trusted profile;
- report population respected the selected Moodle group;
- explicit completion was monotonic and updated Moodle completion;
- score 4/5 was scaled to grade 8/10 and an invalid 6/5 score was rejected;
- card mode, click-to-load, responsive 16:9 presentation and accessible cover
  data were exported by the player;
- the same-site embed code remained management-only and access controlled;
- Moodle duplication completed through backup/restore, remapped the immutable
  package item, preserved package and cover files, and did not copy learner
  progress;
- English, French and Spanish activity strings and guide data were present;
- a course-scoped Creator publication appeared in the selector, its canonical
  hash survived the independent copy, and withdrawing the source publication
  did not modify the existing activity;
- the uploaded ZIP path remained autonomous and did not rely on Creator data.

## Package endpoint and iframe security

Real HTTP requests, without Moodle cookies and with an opaque `Origin: null`,
passed on both sites:

- signed HTML and nested JSON returned HTTP 200;
- `Access-Control-Allow-Origin: *` enabled credentialless opaque-origin fetches;
- HTML included a response-level CSP sandbox and Standard did not include
  `allow-same-origin`;
- responses set no Moodle session cookie and included `nosniff` and
  `Referrer-Policy: no-referrer`;
- OPTIONS returned 204;
- POST returned 405 with `Allow: GET, HEAD, OPTIONS`;
- a forged package signature returned 404.

The three Node regression recipes also passed for keyboard-accessible full
screen and focus containment, message source/origin/nonce/schema validation,
and signed embed resizing bounds.

## Moodle Plugin CI

The public run for `dbb9225` passed all eight jobs:

- Moodle 4.0 / PHP 7.4 / MariaDB;
- Moodle 4.1 / PHP 8.0 / MariaDB;
- Moodle 4.5 / PHP 8.1 / MariaDB;
- Moodle 4.5 / PHP 8.3 / PostgreSQL;
- Moodle 5.0 / PHP 8.2 / MariaDB;
- Moodle 5.1 / PHP 8.4 / MariaDB;
- Moodle 5.2 / PHP 8.3 / MariaDB;
- Moodle 5.2 / PHP 8.4 / PostgreSQL.

Each job passed installation, PHP lint, Moodle CodeSniffer with zero warnings,
PHPDoc, plugin validation, upgrade savepoints, Mustache, Grunt/ESLint/Stylelint
and PHPUnit with fail-on-warning enabled.

## Cross-plugin coordination

Course deletion with the category recycle bin enabled exposed a Creator issue,
not an Activity issue. The Creator course-backup plugin passes an invalid
`sqlparam` condition to `backup_nested_element::set_source_table()`. Moodle then
throws `base_element_struct_exception` while trying to archive a course for the
recycle bin. Deleting individual InteractEmbed activities succeeds, and the
Activity's own backup/restore recipe passes.

This finding is resolved on Creator `0.9.0-rc14`, commit `428e56a`. The Creator
now passes the context id as a supported SQL parameter. Its coordinated Moodle
5.2 recipe completed a real general backup and restore into a new course with
11 assertions; the complete Creator suite passed 13 tests and 93 assertions,
and its six-job public CI matrix is green.

## Readiness assessment

The Activity itself is ready for a coordinated 1.0 release decision: its
supported matrix, install/upgrade path, package security, roles, progress,
grades, completion, reports, multilingual UI, autonomous ZIP path,
backup/restore and Creator consumer integration all pass.

The cross-plugin backup blocker is therefore lifted. Final stable maturity and
the `v1.0.0` tag remain a separate coordinated release decision; neither is
created by this validation.
