# InteractEmbed family alignment contract

This document defines the pre-1.0 product contract between
`block_interactembed` and `mod_interactembed`.

## Shared behaviour

Both plugins must preserve the same behaviour for:

- URL and iframe sanitisation and provider conversion;
- HTML5 File Manager options, ZIP validation and entry-point resolution;
- the included local guide and its 17-screen FR/EN/ES parity;
- responsive ratios, covers, card/inline display and click-to-load;
- lazy loading, full screen, external fallback and same-site Embed code;
- strict, standard and trusted sandbox semantics;
- administration defaults, source policies, generic-domain rules, remote
  consent and archive limits;
- short-lived signed package delivery, CSP and `nosniff` protection;
- backup and restore of instance configuration and Files API content;
- no automatic email or notification delivery.

Changes to one of these behaviours require a corresponding change or an
explicitly documented exception in the other plugin.

## Intentional Moodle-context differences

| Concern | Block | Activity |
|---|---|---|
| Moodle context | `context_block` and parent-page access | `context_module`, enrolment, visibility and availability |
| Placement | Front page, Dashboard, course and other permitted pages | Course section and activity navigation |
| Storage | Serialised block instance configuration | Normalised activity and progress tables |
| Completion | None | Viewed and explicit content-completed rules |
| Gradebook | None | Optional bounded score scaled to the configured maximum |
| Reports | None | Capability- and group-aware learner report |
| Personal records | None stored by the block | Progress status, score and timestamps with full Privacy API |
| Events | Block lifecycle only | Course-module viewed and progress-updated events |
| Course reset | Not applicable | Deletes learner progress and resets grades |
| Trusted package | Site configuration authority | Dedicated module capability |
| Default source | URL-first in the block | File Manager/package first, by product decision |
| Language packs | English source strings in the package; FR/ES through AMOS | English source strings in the package; FR/ES through AMOS |

## Progress protocol unique to the activity

The player issues a per-display nonce through an `interactembed:init` message.
Compatible content echoes that nonce in an `interactembed:progress` version 1
message. The parent validates the exact iframe window, origin when available,
version, status, nonce and bounded score. The AJAX endpoint then validates the
Moodle session key, authenticated session nonce, course module, availability
and `mod/interactembed:submitprogress` capability.

Package resources use short-lived URLs signed for the module context, active
revision and file-area item. Their cookieless endpoint permits credentialless
CORS so Standard and Strict packages with opaque origins can fetch relative
JSON without `allow-same-origin`. HTML responses also receive a CSP sandbox
matching the selected profile, including during direct navigation, while every
resource receives `nosniff` and `Referrer-Policy: no-referrer`.

Standard never grants `allow-same-origin`. Trusted is an explicit manager-only
choice guarded by the `mod/interactembed:trustpackage` `RISK_XSS` capability and
is reserved for audited packages. Sandboxed popups remain available in Standard
and Trusted, but they cannot escape the sandbox.

The block does not implement this protocol because it must not create learner
completion or grade data.

## Release gate

Before either 1.0 release:

1. shared classes and settings are compared after namespace/context
   normalisation;
2. all language-key and guide-screen parity checks pass;
3. Moodle Code Checker, PHPDoc, Mustache, ESLint, Stylelint, PHPUnit and plugin
   validation pass on every supported branch in its CI matrix;
4. install, upgrade, backup/restore and real package delivery are exercised on
   the oldest and newest supported Moodle versions;
5. every remaining difference appears in the intentional-differences table.
