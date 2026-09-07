# Security policy

## Supported versions

Security fixes are prepared for the latest published InteractEmbed activity
version on supported Moodle 4.0 through 5.2 installations.

## Reporting a vulnerability

Do not publish exploitable details in a public issue. Use
[GitHub private vulnerability reporting](https://github.com/InteractEmbed/moodle-mod_interactembed/security/advisories/new)
and include the affected version, safe reproduction steps and expected impact.

Never include real learner data, credentials, session cookies, grades or
private HTML5 packages in a report.

## Package isolation

HTML5 package files are delivered by a short-lived URL signed for one activity
context, active revision and private file-area item. The delivery endpoint
defines `NO_MOODLE_COOKIES`; its CORS response is therefore credentialless and
does not expose an authenticated Moodle response. `Referrer-Policy: no-referrer`
prevents the bearer URL from being sent to links or remote assets.

Strict and Standard deliberately omit `allow-same-origin`, so package documents
run with an opaque origin. Standard is sufficient for Creator publications
because their relative JSON fetches use the signed endpoint's CORS response.
Trusted adds `allow-same-origin` only for explicitly audited packages and is
protected by `mod/interactembed:trustpackage`, a manager-only `RISK_XSS`
capability. The server normalises forged Trusted submissions back to Standard.

Every HTML package response includes a CSP `sandbox` matching the stored
profile and `frame-ancestors 'self'`. This response policy remains effective
when someone opens the package document directly rather than through the
activity iframe.
