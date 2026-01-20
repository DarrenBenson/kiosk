# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| Latest | Yes |

## Reporting a Vulnerability

**Do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via:

- Email: Create a private security advisory on GitHub, or contact the maintainer directly

Include the following information:

- Type of vulnerability
- Full paths of affected source files
- Location of the affected code (tag/branch/commit or direct URL)
- Step-by-step reproduction instructions
- Proof-of-concept or exploit code (if possible)
- Impact assessment

## Response Timeline

- **Initial response**: Within 48 hours
- **Status update**: Within 7 days
- **Resolution target**: Within 30 days

## Disclosure Policy

- We will acknowledge receipt of your report
- We will confirm the vulnerability and determine its impact
- We will release a fix and publicly disclose the issue

## Security Measures

- API keys and credentials are stored in `config.php` (not tracked in git)
- All API endpoints validate the `KIOSK_APP` constant
- Cache files are excluded from version control

## Contact

Open a [GitHub Security Advisory](https://github.com/DarrenBenson/kiosk/security/advisories/new) for private vulnerability reports.
