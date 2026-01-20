---
name: deploy-skill
description: /deploy - Deploy kiosk files to production or test environment (kiosk)
---

# Deploy Skill

Deploy kiosk application files to webserver1 via jumpbox.

## When to Use

- "Deploy this to production"
- "Push these changes to the server"
- "Update the live site"
- "Deploy to test first"
- When user wants to ship code changes

## Instructions

When deploying:

1. **Check what changed** - Review modified files with `git status`
2. **Validate PHP syntax** - Run on server if no local PHP
3. **Deploy to test first** - Use `-g` flag for GUID test deployment
4. **Verify test deployment** - Check via `https://kiosk.deskpoint.com/[guid]/`
5. **Deploy to production** - Use `-b -a` for backup + full deploy
6. **Clean up test** - Delete GUID directory after verification

## Arguments

| Argument | Description | Default |
|----------|-------------|---------|
| `files` | Specific files to deploy | - |
| `-a, --all` | Deploy all project files | false |
| `-b, --backup` | Create backup before deploy | false |
| `-g, --guid [id]` | Deploy to test GUID | auto-generate |

## Examples

```bash
# Deploy single file
.claude/skills/deploy-skill/scripts/deploy.sh api/bins.php

# Test deployment with auto GUID
.claude/skills/deploy-skill/scripts/deploy.sh -g -a

# Test with specific GUID
.claude/skills/deploy-skill/scripts/deploy.sh -g mytest -a

# Production deploy with backup
.claude/skills/deploy-skill/scripts/deploy.sh -b -a

# Deploy multiple files
.claude/skills/deploy-skill/scripts/deploy.sh api/bins.php scripts/bins.js style/bins.css
```

## Server Details

| Item | Value |
|------|-------|
| Server | webserver1 via jumpbox |
| Production path | `/DockerData/docker/WebSites/kiosk/` |
| Test URL | `https://kiosk.deskpoint.com/[guid]/` |
| Backups | `/DockerData/docker/WebSites/backups/` |

## Cleanup

After test deployment, delete with:

```bash
ssh jumpbox "source ~/.ssh/agent.env && ssh webserver1 'rm -rf /DockerData/docker/WebSites/kiosk/[guid]'"
```

## See Also

- `/test` - Quick test deployment command
- `/kiosk-help` - All available commands
