/kiosk-help - Show available commands and usage

## Commands

| Command | Description |
|---------|-------------|
| `/deploy` | Deploy files to production |
| `/test` | Deploy to test GUID |
| `/check` | Verify APIs are working |

## Quick Start

```
/test           # Deploy to test environment
/check          # Verify APIs responding
/deploy -b -a   # Backup + deploy production
```

## Skills

Full documentation in `.claude/skills/`:

| Skill | Description |
|-------|-------------|
| `deploy-skill` | Deployment workflow and script |
| `test-skill` | Test deployment and verification |
| `check-skill` | API health checking |

## URLs

| Environment | URL |
|-------------|-----|
| Production | https://kiosk.deskpoint.com |
| Test | https://kiosk.deskpoint.com/[guid]/ |
