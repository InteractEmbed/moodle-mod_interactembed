# Validation report — InteractEmbed activity 0.9.0-beta6

Date: 1 September 2026
Component: `mod_interactembed`

## Scope

This candidate strengthens the optional Creator Consumer API v1 integration
without adding a runtime dependency. A Creator publication is copied into an
inactive activity-owned revision and becomes autonomous only after complete
validation. File Manager packages, URL/iframe content and the included guide
remain available when the Creator is absent.

## Integrity and failure behaviour

- ZIPs, unpacked packages and Creator snapshots share file-count, total-size,
  path-safety, directory-depth and duplicate-path checks.
- Creator snapshots must provide a valid lowercase SHA-256 canonical hash.
- The source collection is verified before copying and the destination
  collection is verified again after copying.
- Any validation, copy, hash or entry-point failure deletes the inactive
  revision and does not replace the active content.
- `manifest.json` and `interactembed-manifest.json` are optional; a valid HTML
  entry point is the only package-level document required by the consumer.

## Automated evidence

| Check | Result |
|---|---|
| Moodle/IOMAD 4.5.13, PHP 8.3.30, MySQL 8.4.11 | 62 tests, 201 assertions, passed |
| Moodle 4.5 clean PHPUnit installation | XMLDB and plugin installation passed |
| IOMAD 4.5 beta5-to-beta6 CLI upgrade | Passed |
| Moodle 5.2.1+ beta5-to-beta6 CLI upgrade | Passed |
| Creator Consumer API | Version 1 contract retained; no runtime dependency |
| Real Library activity creation on IOMAD 4.5 | 6 files copied, `index.html` located, cleanup passed |
| Real Library activity creation on Moodle 5.2 | 6 files copied, `index.html` located, cleanup passed |
| Falsified hash regression | Refused; inactive destination cleaned |
| Nested asset copy regression | Passed |
| Unpacked count, size, depth and duplicate limits | Passed |
| IOMAD 4.5 ZIP import and atomic revision update | Passed; temporary activity cleaned |
| Moodle 5.2 progress, Gradebook and event integration | Passed; invalid payloads refused |
| PHP syntax | Passed with PHP 7.4.33 and PHP 8.3.30 |
| Moodle CodeSniffer | 0 errors, 0 warnings |
| Moodle 5.2 XMLDB schema | Passed |
| JavaScript syntax | Passed |
| English/French/Spanish language parity | 142 keys in each language |

All live integration checks create only temporary records or file revisions and
explicitly remove them after verification. The distributable ZIP checksum is
recorded in the release report beside the archive, because embedding an archive
checksum inside that same archive would be self-referential.

## Release status

This is a pre-1.0 development candidate. It does not declare a stable 1.0
release and is intended for controlled validation before publication.
