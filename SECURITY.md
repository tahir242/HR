# Security Policy

## Supported Versions

This repository does not currently define versioned releases. Apply security fixes to the active default branch unless a release policy is added.

## Sensitive Configuration

Never commit real database credentials, SSO application IDs, production URLs, cache files, logs, or uploaded documents. Use `config.example.php` as the template and keep the local `config.php` private.

If a secret was committed to Git history, rotate the secret immediately. Removing it from the latest commit is not enough once it has been pushed.

## Reporting a Vulnerability

Report vulnerabilities privately to the repository owner or internal system maintainer. Include:

- Affected file or endpoint
- Steps to reproduce
- Expected impact
- Suggested fix, if known
