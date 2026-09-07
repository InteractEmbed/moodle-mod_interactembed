# Change log

## 0.9.0 beta9

- Lets opaque-origin package frames fetch their own JSON and other signed resources
  through credentialless CORS while keeping Moodle cookies disabled.
- Adds `nosniff`, `no-referrer` and an explicit cross-origin resource policy to every
  signed package response, with profile-matched CSP sandboxing on HTML documents.
- Keeps Creator publications on Standard without `allow-same-origin`; Trusted remains
  an explicit `RISK_XSS` capability-gated profile for audited packages only.
- Removes `allow-popups-to-escape-sandbox` from every profile while retaining ordinary
  sandboxed popups where Standard or Trusted content needs them.
- Rejects progress messages with the wrong frame, origin, nonce, version, status,
  property schema or numeric score types before contacting Moodle.
- Restricts the signed package endpoint to GET, HEAD and OPTIONS requests.

## 0.9.0 beta8

- Adds concise cover-image format, dimension, weight and crop guidance directly
  below the file picker in English, French and Spanish.
- Aligns the authoring guidance with the companion block and Creator media advice.

## 0.9.0 beta7

- Aligns the full-screen fallback with the companion block: dialog semantics,
  accessible label, keyboard focus containment and reliable Escape handling.
- Prevents one player from stealing focus when a different player exits native
  full-screen mode on the same page.
- Confirms Creator rc7 Consumer API v1 imports from course, site and ancestor
  category scopes while refusing sibling-course and unrelated-category content.

## 0.9.0 beta6

- Applies archive file-count, total-size, depth, path-safety and duplicate-path
  limits to unpacked File Manager projects and Creator snapshots as well as ZIPs.
- Verifies the Creator Consumer API v1 canonical SHA-256 hash before and after
  copying an immutable snapshot into the activity-owned file area.
- Removes every inactive partial revision if validation, copy, hash or entry-point
  discovery fails, leaving the active package untouched.
- Hardens the optional Creator connector against absent, incomplete or
  incompatible API implementations; the activity remains fully autonomous.
- Adds regression coverage for canonical hashes, falsified snapshots, copied
  nested assets and unpacked package limits.

## 0.9.0 beta5

- Adds an optional InteractEmbed Library origin inside the existing HTML5 package source.
- Copies immutable Creator snapshots into an inactive local revision, validates paths, limits, entry point and canonical hash, then activates the independent copy.
- Removes the invalid empty default from the `coveralt` XMLDB field for clean Moodle 4.5 installations.
- Keeps rendering, backup/restore and editing fully operational when the Creator is absent.

## 0.9.0 beta

- Aligns package, URL/iframe, guide, presentation, security and administration
  behaviour with the companion `block_interactembed` plugin.
- Adds Moodle Gradebook, custom completion, learner progress, group-aware
  reports, events, Privacy API, backup/restore and same-site embed support.
- Validated on IOMAD 4.5 and Moodle 5.2.1+ during development.
