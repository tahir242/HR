# Contributing

## Local Workflow

1. Create a branch for the change.
2. Keep environment-specific changes out of Git, especially `config.php`.
3. Run PHP syntax checks on changed PHP files.
4. Include a short summary of behavior changes in the pull request.

## Code Style

- Follow the existing PHP style in nearby files.
- Keep changes focused on the requested behavior.
- Avoid committing generated logs, cache files, uploads, or local editor files.

## Pull Requests

Before opening a pull request, confirm:

- No secrets are included.
- No runtime files are included.
- Changed PHP files pass `php -l`.
- Setup or deployment changes are documented in `README.md`.
