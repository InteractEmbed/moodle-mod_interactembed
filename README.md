# InteractEmbed for Moodle

InteractEmbed is a Moodle activity for validated, sandboxed HTML5 packages and
privacy-aware external embeds.

Stable release 1.0 combines the validated feature set described below. It can
be installed and used independently: neither the companion InteractEmbed Block
nor InteractEmbed Creator is required. When a compatible Creator Consumer API
v1 is available, teachers may additionally copy an immutable publication from
the course, category or site library into an activity-owned snapshot.

Development release 0.3 supports:

- one ZIP archive or an unpacked project selected with Moodle File Manager;
- safe archive validation and automatic `index.html` discovery;
- atomic package revisions;
- a sandboxed responsive player;
- short-lived signed package URLs independent of Moodle session cookies;
- English, French and Spanish interfaces;
- standard Moodle visibility, availability and view-completion controls.

Development release 0.4 also includes the optional, local and trilingual
InteractEmbed guide as a content source. It doubles as a user guide and a
working HTML5 activity model, without making external connections.

Development release 0.5 adds safe web-address and iframe sources, including
smart conversion for YouTube, Google Workspace, Canva, Genially, Padlet and
Wayground, with administrator-controlled generic-domain support.

Development releases 0.6 and 0.7 add the shared responsive player, cover
images, display ratios, compact-card and click-to-load modes, lazy loading,
accessible full screen, sandbox profiles, and standard Site administration
defaults, policies, domain controls and archive limits.

Development release 0.8 adds Moodle activity backup, restore and duplication
for the instance record, active immutable package revision and cover image.

Pre-release 0.9 beta 3 adds optional Gradebook integration, explicit content
completion, per-learner progress, group-aware reports, events, full Privacy API
support and an optional access-controlled same-site embed code. No notification
or email is sent by the plugin.

Pre-release 0.9 beta 6 adds the optional InteractEmbed Library origin. When the
compatible Creator plugin is present, an author can select a publication from
the current course. The activity validates its immutable snapshot and canonical
hash, then makes an independent local copy. The activity does not require the
Creator and continues to support File Manager packages, external embeds and the
included guide when it is absent.

ZIP archives, unpacked projects and Creator snapshots share the same safety
rules: configurable file-count and total-size limits, safe relative paths,
bounded directory depth, unique paths and an existing HTML entry point. A
manifest is optional; autonomous HTML5 packages are not required to provide one.

Pre-release 0.9 beta 9 lets sandboxed Creator and generic HTML5 packages fetch
their own signed resources without granting them Moodle's origin. The package
endpoint does not use Moodle cookies, permits credentialless CORS, prevents the
signed URL from leaking through referrers, and applies `nosniff` plus a CSP
sandbox to HTML documents even when they are opened directly.

Standard is the default package profile and never includes `allow-same-origin`.
Trusted includes it only for audited packages, is never an administrative
default, is hidden from unauthorised editors, and is enforced server-side by
the dedicated `mod/interactembed:trustpackage` capability marked `RISK_XSS`.

## Progress protocol for HTML5 content

After receiving `{type: 'interactembed:init', version: 1, nonce}` from its
parent, content may report progress back to that parent:

```javascript
window.parent.postMessage({
    type: 'interactembed:progress',
    version: 1,
    nonce: receivedNonce,
    status: 'completed', // Or 'inprogress'.
    score: 8,            // Optional; both score fields must be present.
    scoremax: 10
}, '*');
```

Moodle verifies the exact iframe window, expected or explicitly opaque origin,
exact version-1 property schema and types, unique display nonce, session key,
activity access and capability. Completion is monotonic. Scores must be finite,
non-negative and no greater than `scoremax`. No progress or grade is generated
when content sends no valid message.

Install the `interactembed` directory in `mod/interactembed`, then run the
standard Moodle upgrade process.

Target compatibility is Moodle 4.0 through Moodle 5.2. IOMAD 4.5 is included
in the primary validation environments.

## Development and support

- [Source repository](https://github.com/InteractEmbed/moodle-mod_interactembed)
- [Issue tracker](https://github.com/InteractEmbed/moodle-mod_interactembed/issues)
- [Private vulnerability reporting](https://github.com/InteractEmbed/moodle-mod_interactembed/security/advisories/new)

See `CONTRIBUTING.md` for contribution expectations and `SECURITY.md` for the
security-reporting policy.
