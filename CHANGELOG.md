# Changelog

## 0.1.0

The first public version.

- Sends each uploaded file to the Kelpgate scanning service during Drupal file validation, and rejects infected files with a validation error.
- A failure policy decides what happens when the scanner cannot give a verdict: `block` (the default) or `allow`.
- Detections are written to the `kelpgate` log channel with the file's SHA-256.
- The client secret is read from `settings.php` or Drupal state and is never stored in exported configuration.
- Setting names and environment variables match the other Kelpgate clients and the WordPress plugin ([client standard](https://github.com/KelpGate/.github/blob/main/docs/client-standard.md)): `max_mb` (not `max_scan_mb`), and `KELPGATE_API_URL`, `KELPGATE_TOKEN_URL`, `KELPGATE_AUTH_MODE`, `KELPGATE_CLIENT_ID`, `KELPGATE_CLIENT_SECRET`, `KELPGATE_TOKEN`, `KELPGATE_FAIL_ACTION`, `KELPGATE_MAX_MB` and `KELPGATE_TIMEOUT_SECONDS` can force each setting.
- Every scan sends an `Idempotency-Key` (one per scan), so a repeated request, such as the retry after a refreshed token, is not counted against the plan's quota twice. There is nothing to configure ([client standard](https://github.com/KelpGate/.github/blob/main/docs/client-standard.md#idempotency)).
- Code quality: cache keys use SHA-256 instead of MD5, and the code follows the Drupal coding standard (no errors; the remaining warnings are the 80-column limit and `\Drupal::` service calls).
- Scans uploads on Drupal 11. Drupal 11 no longer invokes `hook_file_validate()`, so earlier builds installed on Drupal 11 but never scanned. On Drupal 10.2 and later the module now scans in a subscriber to `FileValidationEvent`; `hook_file_validate()` remains for Drupal 9.5 to 10.1 and does nothing where the event exists, so Drupal 10.2 to 10.x, which invoke both, scan once.
- Each upload is sent to the service once. Drupal validates a file again when the form holding it is saved and whenever content that references it is validated, which used to send every accepted upload twice and re-scan existing attachments on every edit. Permanent files are no longer re-scanned, and a clean verdict is remembered for six hours by the file's SHA-256. A file accepted without a verdict under `fail_action: allow` is scanned again when the form is saved.
- Supports Drupal 9.5, 10 and 11. Verified on Drupal 9.5, 10.1, 10.3, 10.6 and 11.4; see [Compatibility](README.md#compatibility).
- Install with `git clone` into `modules/contrib/kelpgate`, or with Composer from the GitHub repository until the module is on Packagist.
