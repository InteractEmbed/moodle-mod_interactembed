# InteractEmbed activity 1.0.0 release record

Date: 2026-09-07

## Release metadata

- Component: `mod_interactembed`
- Moodle build number: `2026090701`
- Release: `1.0.0`
- Maturity: `MATURITY_STABLE`
- Supported Moodle branches: 4.0 through 5.2 inclusive
- Minimum Moodle build: 4.0 (`2022041900`)
- Required additional plugins: none
- Optional companions: `block_interactembed` and
  `local_interactembedcreator` Consumer API v1
- Licence: GNU GPL v3 or later

## Validated release scope

The stable release includes package and unpacked-project import, safe external
embeds, the local trilingual guide, responsive presentation, covers, accessible
full screen, sandbox profiles, signed package delivery, grading, completion,
progress, group-aware reports, Privacy API, course reset, events, backup and
restore, and site administration settings. It sends no automatic email or
Moodle notification.

The Marketplace archive contains Moodle's English source strings only. The
complete French and international Spanish interface translations are retained
outside the plugin archive as AMOS submission packages. The self-contained
interactive guide remains natively trilingual and selects English, French or
Spanish from the active Moodle language while retaining its own language
selector.

The pre-1.0 functional recipe passed 47 assertions on Moodle 5.2 and
IOMAD/Moodle 4.5. The coordinated general course backup/restore recipe passed
11 assertions on each site. After deleting both source and restored courses,
the Creator rc17 lifecycle audit found zero orphaned projects on both sites.

The public Activity CI baseline passed eight combinations from Moodle 4.0 with
PHP 7.4 through Moodle 5.2 with PHP 8.4, using MariaDB and PostgreSQL. The
optional companion Block 1.0.0 commit `f404516` passed its public CI matrix.
The optional Creator 1.0.0 commit `bd0522b` also passed its six-job public
matrix; its lifecycle release candidate had passed the Moodle 5.2 suite of 15
tests and 100 assertions before the stable metadata release.

## Security and privacy

HTML5 package paths, archive limits and entry points are validated before an
inactive revision can become active. Package resources use short-lived signed
URLs without Moodle cookies. Standard and Strict sandbox profiles retain an
opaque origin; Trusted is restricted by a dedicated `RISK_XSS` capability.
HTML responses receive a matching CSP sandbox and all package responses use
credentialless CORS, `nosniff` and `Referrer-Policy: no-referrer`.

The activity stores learner progress, optional bounded scores and interaction
timestamps only when compatible content sends a valid progress message. These
records are declared and managed through Moodle's Privacy API. External embeds
may contact the teacher-selected provider; the plugin itself requires no
external service, account, API key or subscription.

## Distribution

The release archive must contain one top-level `interactembed/` directory and
is built reproducibly from the signed release commit with `git archive`. Its
SHA-256 is published with the GitHub Release asset. Moodle Marketplace
submission remains a separate maintainer action.
