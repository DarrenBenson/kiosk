---
name: test-skill
description: /test - Deploy to test GUID and verify before production (kiosk)
---

# Test Skill

Quick test deployment to verify changes before production.

## When to Use

- "Test this before deploying"
- "Can we preview these changes?"
- "Check if this works on the server"
- When user wants to verify changes work remotely

## Instructions

1. **Deploy to GUID** - Run `.claude/skills/deploy-skill/scripts/deploy.sh -g -a`
2. **Note the test URL** - Shown in output
3. **Verify functionality**:
   - Check main page loads: `curl -sL "https://kiosk.deskpoint.com/[guid]/"`
   - Test APIs: `curl -sL "https://kiosk.deskpoint.com/[guid]/api/bins.php"`
   - Check browser if needed
4. **Report results** to user
5. **Clean up** - Delete test directory when done

## Arguments

| Argument | Description | Default |
|----------|-------------|---------|
| `[guid]` | Specific test ID to use | auto-generate |

## Examples

```bash
# Quick test (auto GUID)
.claude/skills/deploy-skill/scripts/deploy.sh -g -a

# Named test
.claude/skills/deploy-skill/scripts/deploy.sh -g preview -a

# Test single file
.claude/skills/deploy-skill/scripts/deploy.sh -g -a api/bins.php
```

## Verification Commands

```bash
# Check main page
curl -sL "https://kiosk.deskpoint.com/[guid]/" | grep -o '<title>.*</title>'

# Check bins API
curl -sL "https://kiosk.deskpoint.com/[guid]/api/bins.php"

# Check weather API
curl -sL "https://kiosk.deskpoint.com/[guid]/api/weather.php" | head -c 200

# Check stocks API
curl -sL "https://kiosk.deskpoint.com/[guid]/api/stocks.php" | head -c 200
```

## Cleanup

```bash
ssh jumpbox "source ~/.ssh/agent.env && ssh webserver1 'rm -rf /DockerData/docker/WebSites/kiosk/[guid]'"
```

## See Also

- `/deploy` - Full deployment command
- `deploy-skill` - Detailed deployment instructions
