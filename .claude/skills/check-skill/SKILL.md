---
name: check-skill
description: /check - Verify kiosk APIs are responding correctly (kiosk)
---

# Check Skill

Verify production APIs are responding with valid data.

## When to Use

- "Are the APIs working?"
- "Check if the site is up"
- "Verify the deployment worked"
- After deploying changes
- When troubleshooting issues

## Instructions

1. **Determine scope** - Check all APIs or specific one based on arguments
2. **Run curl checks** for each API:
   ```bash
   curl -sL "https://kiosk.deskpoint.com/api/bins.php"
   curl -sL "https://kiosk.deskpoint.com/api/weather.php" | head -c 200
   curl -sL "https://kiosk.deskpoint.com/api/stocks.php" | head -c 200
   ```
3. **Validate responses** - Check for valid JSON structure
4. **Report status** for each API

## Arguments

| Argument | Description | Default |
|----------|-------------|---------|
| `api` | Specific API to check (bins, weather, stocks) | all |

## Expected Responses

| API | Expected Structure |
|-----|-------------------|
| bins | `{"date":"...","bins":{...},"isToday":...}` |
| weather | `{"current":{...},"hourly":[...]}` |
| stocks | `{"stocks":[...],"currency":"GBP"}` |

## Examples

```bash
# Check all APIs
curl -sL "https://kiosk.deskpoint.com/api/bins.php"
curl -sL "https://kiosk.deskpoint.com/api/weather.php" | head -c 200
curl -sL "https://kiosk.deskpoint.com/api/stocks.php" | head -c 200

# Check main page loads
curl -sL "https://kiosk.deskpoint.com/" | grep -o '<title>.*</title>'
```

## Error Indicators

| Response | Meaning |
|----------|---------|
| Empty response | API not accessible or config missing |
| PHP error in output | Syntax error or missing dependency |
| `{"error":...}` | API-level error (check message) |
| Connection timeout | Server unreachable |

## See Also

- `/deploy` - Deploy changes
- `/test` - Test before production
